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
$teacher_reg = $_SESSION['teacher_id'];
$stmt = $conn->prepare("SELECT org_id FROM teacher WHERE reg_no = ?");
$stmt->bind_param("s", $teacher_reg);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['ok' => false, 'error' => 'Teacher not found']);
    exit;
}
$org_id = intval($res->fetch_assoc()['org_id']);
$stmt->close();

// Fetch classes for this organization (classes.sub_admin_id is org id)
$q = $conn->prepare("SELECT id, class_name, subjects, start_time, end_time FROM classes WHERE sub_admin_id = ?");
$q->bind_param("i", $org_id);
$q->execute();
$rs = $q->get_result();
$classes = [];
while ($row = $rs->fetch_assoc()) {
    $classes[] = [
        'id' => intval($row['id']),
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

// ---------- time helpers ----------
function toMinutes($hhmm) {
    [$h, $m] = array_map('intval', explode(':', $hhmm));
    return $h*60 + $m;
}
function timeRangeToTuple($range) {
    $range = trim($range);
    if (strpos($range, '-') !== false) {
        [$s, $e] = array_map('trim', explode('-', $range, 2));
    } else {
        $s = $range;
        $e = date('H:i', strtotime($range . ' +1 hour')); // default 60min
    }
    return [$s, $e];
}
function overlaps($aStart,$aEnd,$bStart,$bEnd) {
    return max($aStart,$bStart) < min($aEnd,$bEnd);
}
function periodize($start_time, $end_time, $stepMin=60) {
    $s = toMinutes($start_time);
    $e = toMinutes($end_time);
    $out = [];
    for ($t=$s; $t+$stepMin <= $e; $t += $stepMin) {
        $sH = str_pad(intval($t/60),2,'0',STR_PAD_LEFT);
        $sM = str_pad($t%60,2,'0',STR_PAD_LEFT);
        $eH = str_pad(intval(($t+$stepMin)/60),2,'0',STR_PAD_LEFT);
        $eM = str_pad(($t+$stepMin)%60,2,'0',STR_PAD_LEFT);
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
    [$s, $e] = timeRangeToTuple($slot);
    $sMin = toMinutes($s);
    $eMin = toMinutes($e);
    foreach ($teacher['available'] as $r) {
        if ($r === '') continue;
        [$rs, $re] = timeRangeToTuple($r);
        if (overlaps($sMin, $eMin, toMinutes($rs), toMinutes($re))) return true;
    }
    return false;
}

// ---------- GA ----------
class GA {
    public $population = [];
    public $popSize;
    public $mutationRate;
    public $classSlots;      // timeslots strings
    public $subjects;        // subjects repeated to fill slots
    public $teachers;

    public function __construct($classSlots, $subjects, $teachers, $popSize=60, $mutationRate=0.08) {
        $this->classSlots = $classSlots;
        $this->subjects   = $subjects;
        $this->teachers   = $teachers;
        $this->popSize    = $popSize;
        $this->mutationRate = $mutationRate;
        $this->initPopulation();
    }

    private function randomFeasibleGene($slot, $subject) {
        $candidates = [];
        foreach ($this->teachers as $t) {
            if (teacherCanTeach($t, $subject) && teacherAvailableAt($t, $slot)) {
                $candidates[] = $t['name'];
            }
        }
        if (empty($candidates)) return null;
        return $candidates[array_rand($candidates)];
    }

    private function initPopulation() {
        $nSlots = count($this->classSlots);
        $pool = [];
        while (count($pool) < $nSlots) $pool = array_merge($pool, $this->subjects);
        $pool = array_slice($pool, 0, $nSlots);

        for ($i=0; $i<$this->popSize; $i++) {
            $chrom = [];
            $perm = $pool;
            shuffle($perm);
            for ($j=0; $j<$nSlots; $j++) {
                $slot = $this->classSlots[$j];
                $subject = $perm[$j];
                $teacher = $this->randomFeasibleGene($slot, $subject);
                $chrom[] = ['time'=>$slot, 'subject'=>$subject, 'teacher'=>$teacher];
            }
            $this->population[] = $chrom;
        }
    }

    public function fitness($chrom) {
        $penalty = 0;
        $seenAtSlot = [];
        foreach ($chrom as $gene) {
            $slot = $gene['time'];
            $subject = $gene['subject'];
            $teacher = $gene['teacher'];

            if ($teacher === null) { $penalty += 100; continue; } // infeasible

            // avoid same teacher twice in same class slot (guard)
            $seenAtSlot[$slot] = $seenAtSlot[$slot] ?? [];
            if (in_array($teacher, $seenAtSlot[$slot], true)) $penalty += 20;
            else $seenAtSlot[$slot][] = $teacher;

            // check teacher capability & availability
            $t = null;
            foreach ($this->teachers as $cand) if ($cand['name'] === $teacher) { $t = $cand; break; }
            if (!$t) { $penalty += 20; continue; }
            if (!teacherCanTeach($t, $subject)) $penalty += 20;
            if (!teacherAvailableAt($t, $slot)) $penalty += 20;
        }
        return -$penalty; // higher is better
    }

    private function selectParent() {
        $choices = array_rand($this->population, min(3, count($this->population)));
        if (!is_array($choices)) $choices = [$choices];
        $best = null; $bestFit = -INF;
        foreach ($choices as $idx) {
            $cand = $this->population[$idx];
            $fit = $this->fitness($cand);
            if ($fit > $bestFit) { $bestFit = $fit; $best = $cand; }
        }
        return $best;
    }

    private function crossover($p1, $p2) {
        $n = count($p1);
        $cut = rand(1, max(1, $n-2));
        $child = [];
        for ($i=0; $i<$n; $i++) $child[] = ($i < $cut) ? $p1[$i] : $p2[$i];
        return $child;
    }

    private function mutate(&$chrom) {
        $n = count($chrom);
        if ($n < 2) return;
        if (mt_rand() / mt_getrandmax() < $this->mutationRate) {
            $i = rand(0,$n-1); $j = rand(0,$n-1);
            if ($i !== $j) { $tmp=$chrom[$i]; $chrom[$i]=$chrom[$j]; $chrom[$j]=$tmp; }
        }
        if (mt_rand() / mt_getrandmax() < $this->mutationRate) {
            $k = rand(0,$n-1);
            $slot = $chrom[$k]['time'];
            $subject = $chrom[$k]['subject'];
            $t = $this->randomFeasibleGene($slot, $subject);
            if ($t !== null) $chrom[$k]['teacher'] = $t;
        }
    }

    public function evolve($generations=150, $elitism=2) {
        for ($g=0; $g<$generations; $g++) {
            usort($this->population, fn($a,$b)=> $this->fitness($b) <=> $this->fitness($a));
            $newPop = [];
            for ($i=0; $i<$elitism && $i<count($this->population); $i++) $newPop[] = $this->population[$i];
            while (count($newPop) < $this->popSize) {
                $p1 = $this->selectParent();
                $p2 = $this->selectParent();
                $child = $this->crossover($p1, $p2);
                $this->mutate($child);
                $newPop[] = $child;
            }
            $this->population = $newPop;
        }
        usort($this->population, fn($a,$b)=> $this->fitness($b) <=> $this->fitness($a));
        return $this->population[0];
    }
}

// ---------- Build schedules and save ----------
$inserted = 0;
$errors = [];

foreach ($classes as $cls) {
    $slots = periodize($cls['start_time'], $cls['end_time'], 60);
    if (empty($slots)) { $errors[] = "Class {$cls['class_name']}: invalid time window."; continue; }

    $ga = new GA($slots, $cls['subjects'], $teachers, 60, 0.10);
    $best = $ga->evolve(150, 3);

    // sort by time
    usort($best, function($a,$b){
        $sa = explode('-', $a['time'])[0];
        $sb = explode('-', $b['time'])[0];
        return strcmp($sa, $sb);
    });

    // replace existing
    $del = $conn->prepare("DELETE FROM schedule WHERE org_id = ? AND class_name = ?");
    $del->bind_param("is", $org_id, $cls['class_name']);
    $del->execute();
    $del->close();

    $json = json_encode($best, JSON_UNESCAPED_UNICODE);
    $ins = $conn->prepare("INSERT INTO schedule (org_id, class_name, schedule_json) VALUES (?, ?, ?)");
    $ins->bind_param("iss", $org_id, $cls['class_name'], $json);
    if (!$ins->execute()) { $errors[] = "DB insert failed for {$cls['class_name']}: " . $conn->error; }
    else { $inserted++; }
    $ins->close();
}

echo json_encode(['ok' => empty($errors), 'inserted' => $inserted, 'errors' => $errors]);