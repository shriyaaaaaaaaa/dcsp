 <?php
include('includes/db_connect.php');

$sql = "SELECT id, org_name, address, org_type FROM sub_admin WHERE approval = 1";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
 <title>Approved Places</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            background: url('img/college.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }

        .container-boxes {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;    
            align-items: center;
            padding: 40px;
            min-height: 100vh;
        }

        .place-box {
            background-color: rgba(189, 172, 172, 0.65);
            border-radius: 25px;
            padding: 20px;
            margin: 15px;
            width: 250px;
            height: 200px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s;
            cursor: pointer;
        }

        .place-box:hover {
            transform: scale(1.05);
        }

        .place-icon {
            font-size: 40px;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .place-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .place-address {
            font-size: 14px;
            color: #333;
        }
    </style></head>
<body>
<div class="container-boxes">
  <?php
  function getOrgIcon($type) {
    switch (strtolower($type)) {
      case 'university': return 'school';
      case 'college': return 'account_balance';
      case 'school': return 'class';
      case 'organization': return 'business';
      default: return 'location_city';
    }
  }

  while ($row = $result->fetch_assoc()):
    $id = $row['id'];
    $slug = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($row['org_name'])) . "_{$id}";
    $tableExists = $conn->query("SHOW TABLES LIKE '$slug'")->num_rows > 0;
    $icon = getOrgIcon($row['org_type']);
  ?>
    <div class="place-box" onclick="onOrgClick(<?= $id ?>, <?= $tableExists ? 'true' : 'false' ?>, '<?= addslashes($row['org_name']) ?>')">
      <div class="material-icons place-icon"><?= $icon ?></div>
      <div class="place-title"><?= htmlspecialchars($row['org_name']) ?></div>
      <div class="place-address"><?= htmlspecialchars($row['address']) ?></div>
    </div>
  <?php endwhile; ?>
</div>

<script>
function onOrgClick(id, exists, name) {
  if (!exists) {
    alert(`Waiting for ${name} to upload member list.`);
  } else {
    window.location.href = `t_register.php?org_id=${id}`;
  }
}
</script>
</body>
</html>

