<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/session_check.php'; // ensures teacher logged in

if (!isset($_SESSION['teacher_id'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Not authenticated']);
    exit;
}

header('Content-Type: application/json');

// Fetch teacher org_id
$teacher_key = $_SESSION['teacher_id'];
$stmt = $conn->prepare("SELECT org_id FROM teacher WHERE reg_no = ? OR id = ? LIMIT 1");
$stmt->bind_param("si", $teacher_key, $teacher_key);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['ok'=>false,'error'=>'Teacher not found']);
    exit;
}
$org_id = intval($res->fetch_assoc()['org_id']);
$stmt->close();

// Fetch classes for this organization (classes.sub_admin_id == teacher.org_id)
$q = $conn->prepare("
  SELECT id, class_name, subjects, start_time, end_time
  FROM classes
  WHERE sub_admin_id = ?
");
$q->bind_param("i", $org_id);
$q->execute();
$rs = $q->get_result();
$classes = [];
while ($row = $rs->fetch_assoc()) {
    $classes[] = [
        'id' => (int)$row['id'],
        'class_name' => $row['class_name'],
        'subjects' => array_values(array_filter(array_map('trim', explode(',', (string)$row['subjects'])))),
        'start_time' => $row['start_time'],
        'end_time' => $row['end_time'],
    ];
}
$q->close();

if (empty($classes)) {
    echo json_encode(['ok' => false, 'error' => 'No classes defined for this organization']);
    exit;
}

// Fetch approved teachers for this organization
$tq = $conn->prepare("SELECT id, name, email, subjects, available FROM teacher WHERE org_id = ? AND tick = '1'");
$tq->bind_param("i", $org_id);
$tq->execute();
$tr = $tq->get_result();
$teachers = [];
while ($row = $tr->fetch_assoc()) {
    $subjects = array_values(array_filter(array_map('trim', explode(',', (string)$row['subjects']))));
    $available = array_values(array_filter(array_map('trim', explode(',', (string)$row['available']))));
    $teachers[] = [
        'id' => intval($row['id']),
        'name' => $row['name'],
        'email' => $row['email'],
        'subjects' => $subjects,
        'available' => $available,
    ];
}
$tq->close();

if (empty($teachers)) {
    echo json_encode(['ok' => false, 'error' => 'No approved teachers found (tick=1)']);
    exit;
}

// ---------- Time Helper Functions ----------
function toMinutes($hhmm) {
    list($h, $m) = array_map('intval', explode(':', $hhmm));
    return $h*60 + $m;
}

function timeRangeToTuple($range) {
    $range = trim($range);
    if (strpos($range, '-') !== false) {
        list($s, $e) = array_map('trim', explode('-', $range, 2));
    } else {
        $s = $range;
        $e = date('H:i', strtotime($range . ' +1 hour')); // default 60min
    }
    return array($s, $e);
}

function overlaps($aStart, $aEnd, $bStart, $bEnd) {
    return max($aStart, $bStart) < min($aEnd, $bEnd);
}

function periodize($start_time, $end_time, $stepMin = 60) {
    $s = toMinutes($start_time);
    $e = toMinutes($end_time);
    $out = [];
    for ($t = $s; $t + $stepMin <= $e; $t += $stepMin) {
        $sH = str_pad(intval($t / 60), 2, '0', STR_PAD_LEFT);
        $sM = str_pad($t % 60, 2, '0', STR_PAD_LEFT);
        $eH = str_pad(intval(($t + $stepMin) / 60), 2, '0', STR_PAD_LEFT);
        $eM = str_pad(($t + $stepMin) % 60, 2, '0', STR_PAD_LEFT);
        $out[] = "$sH:$sM-$eH:$eM";
    }
    return $out;
}

function teacherCanTeach($teacher, $subject) {
    foreach ($teacher['subjects'] as $s) {
        if ($s !== '' && stripos($subject, $s) !== false) return true;
    }
    return false;
}

function teacherAvailableAt($teacher, $slot) {
    list($s, $e) = timeRangeToTuple($slot);
    $sMin = toMinutes($s);
    $eMin = toMinutes($e);
    foreach ($teacher['available'] as $r) {
        if ($r === '') continue;
        list($rs, $re) = timeRangeToTuple($r);
        if (overlaps($sMin, $eMin, toMinutes($rs), toMinutes($re))) return true;
    }
    return false;
}

// ---------- GA Helper Functions (Procedural) ----------

function randomFeasibleGene($slot, $subject, $teachers) {
    $candidates = [];
    foreach ($teachers as $t) {
        if (teacherCanTeach($t, $subject) && teacherAvailableAt($t, $slot)) {
            $candidates[] = $t['name'];
        }
    }
    if (empty($candidates)) return null;
    return $candidates[array_rand($candidates)];
}

function initPopulation($classSlots, $subjects, $teachers, $popSize) {
    $population = [];
    $nSlots = count($classSlots);
    $pool = [];
    while (count($pool) < $nSlots) {
        $pool = array_merge($pool, $subjects);
    }
    $pool = array_slice($pool, 0, $nSlots);

    for ($i = 0; $i < $popSize; $i++) {
        $chrom = [];
        $perm = $pool;
        shuffle($perm);
        for ($j = 0; $j < $nSlots; $j++) {
            $slot = $classSlots[$j];
            $subject = $perm[$j];
            $teacher = randomFeasibleGene($slot, $subject, $teachers);
            $chrom[] = ['time' => $slot, 'subject' => $subject, 'teacher' => $teacher];
        }
        $population[] = $chrom;
    }
    return $population;
}

function fitness($chrom, $teachers) {
    $penalty = 0;
    $seenAtSlot = [];
    foreach ($chrom as $gene) {
        $slot = $gene['time'];
        $subject = $gene['subject'];
        $teacher = $gene['teacher'];

        if ($teacher === null) {
            $penalty += 100;
            continue;
        }

        // avoid same teacher twice in same class slot
        if (!isset($seenAtSlot[$slot])) {
            $seenAtSlot[$slot] = [];
        }
        if (in_array($teacher, $seenAtSlot[$slot], true)) {
            $penalty += 20;
        } else {
            $seenAtSlot[$slot][] = $teacher;
        }

        // check teacher capability & availability
        $t = null;
        foreach ($teachers as $cand) {
            if ($cand['name'] === $teacher) {
                $t = $cand;
                break;
            }
        }
        if (!$t) {
            $penalty += 20;
            continue;
        }
        if (!teacherCanTeach($t, $subject)) $penalty += 20;
        if (!teacherAvailableAt($t, $slot)) $penalty += 20;
    }
    return -$penalty; // higher is better
}

function selectParent($population, $teachers) {
    $popCount = count($population);
    $tournamentSize = min(3, $popCount);
    $choices = array_rand($population, $tournamentSize);
    if (!is_array($choices)) $choices = array($choices);
    
    $best = null;
    $bestFit = -INF;
    foreach ($choices as $idx) {
        $cand = $population[$idx];
        $fit = fitness($cand, $teachers);
        if ($fit > $bestFit) {
            $bestFit = $fit;
            $best = $cand;
        }
    }
    return $best;
}

function crossover($p1, $p2) {
    $n = count($p1);
    $cut = rand(1, max(1, $n - 2));
    $child = [];
    for ($i = 0; $i < $n; $i++) {
        $child[] = ($i < $cut) ? $p1[$i] : $p2[$i];
    }
    return $child;
}

function mutate($chrom, $mutationRate, $classSlots, $teachers) {
    $n = count($chrom);
    if ($n < 2) return $chrom;
    
    // Swap mutation
    if (mt_rand() / mt_getrandmax() < $mutationRate) {
        $i = rand(0, $n - 1);
        $j = rand(0, $n - 1);
        if ($i !== $j) {
            $tmp = $chrom[$i];
            $chrom[$i] = $chrom[$j];
            $chrom[$j] = $tmp;
        }
    }
    
    // Teacher reassignment mutation
    if (mt_rand() / mt_getrandmax() < $mutationRate) {
        $k = rand(0, $n - 1);
        $slot = $chrom[$k]['time'];
        $subject = $chrom[$k]['subject'];
        $t = randomFeasibleGene($slot, $subject, $teachers);
        if ($t !== null) {
            $chrom[$k]['teacher'] = $t;
        }
    }
    
    return $chrom;
}

function evolve($population, $classSlots, $subjects, $teachers, $generations, $elitism, $mutationRate, $popSize) {
    for ($g = 0; $g < $generations; $g++) {
        // Sort population by fitness (best first)
        usort($population, function($a, $b) use ($teachers) {
            return fitness($b, $teachers) <=> fitness($a, $teachers);
        });
        
        $newPop = [];
        
        // Elitism: keep best individuals
        for ($i = 0; $i < $elitism && $i < count($population); $i++) {
            $newPop[] = $population[$i];
        }
        
        // Generate offspring
        while (count($newPop) < $popSize) {
            $p1 = selectParent($population, $teachers);
            $p2 = selectParent($population, $teachers);
            $child = crossover($p1, $p2);
            $child = mutate($child, $mutationRate, $classSlots, $teachers);
            $newPop[] = $child;
        }
        
        $population = $newPop;
    }
    
    // Final sort and return best
    usort($population, function($a, $b) use ($teachers) {
        return fitness($b, $teachers) <=> fitness($a, $teachers);
    });
    
    return $population[0];
}

// ---------- Build schedules and save ----------
$inserted = 0;
$errors = [];

// GA parameters
$popSize = 60;
$mutationRate = 0.10;
$generations = 150;
$elitism = 3;

foreach ($classes as $cls) {
    $slots = periodize($cls['start_time'], $cls['end_time'], 60);
    if (empty($slots)) {
        $errors[] = "Class {$cls['class_name']}: invalid time window.";
        continue;
    }

    // Initialize population
    $population = initPopulation($slots, $cls['subjects'], $teachers, $popSize);
    
    // Evolve to find best schedule
    $best = evolve($population, $slots, $cls['subjects'], $teachers, $generations, $elitism, $mutationRate, $popSize);

    // Sort by time
    usort($best, function($a, $b) {
        $sa = explode('-', $a['time'])[0];
        $sb = explode('-', $b['time'])[0];
        return strcmp($sa, $sb);
    });

    // Delete existing schedule for this class
    $del = $conn->prepare("DELETE FROM schedule WHERE org_id = ? AND class_name = ?");
    $del->bind_param("is", $org_id, $cls['class_name']);
    $del->execute();
    $del->close();

    // Insert new schedule
    $json = json_encode($best, JSON_UNESCAPED_UNICODE);
    $ins = $conn->prepare("INSERT INTO schedule (org_id, class_name, schedule_json) VALUES (?, ?, ?)");
    $ins->bind_param("iss", $org_id, $cls['class_name'], $json);
    if (!$ins->execute()) {
        $errors[] = "DB insert failed for {$cls['class_name']}: " . $conn->error;
    } else {
        $inserted++;
    }
    $ins->close();
}

echo json_encode(['ok' => empty($errors), 'inserted' => $inserted, 'errors' => $errors]);