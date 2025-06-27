<?php
session_start();
include("connection.php");
include("header.php");

function zi_romana($data) {
  $zile = ['Duminică','Luni','Marți','Miercuri','Joi','Vineri','Sâmbătă'];
  return $zile[date('w', strtotime($data))];
}

// filtrare afisare formular
$ascunde_formulare = isset($_GET['formulare']) && $_GET['formulare'] == 'ascunse';

$where = "WHERE 1=1";
$luni = date('Y-m-d', strtotime('monday this week'));
$duminica = date('Y-m-d', strtotime('sunday this week'));
if (isset($_GET['saptamana']) && $_GET['saptamana'] == 'curenta') {
  $where .= " AND p.data BETWEEN '$luni' AND '$duminica'";
}
if (!empty($_GET['pacient'])) {
  $pacient = mysqli_real_escape_string($con, $_GET['pacient']);
  $where .= " AND pa.nume LIKE '%$pacient%'";
}
if (!empty($_GET['medic'])) {
  $medic = mysqli_real_escape_string($con, $_GET['medic']);
  $where .= " AND (m.nume LIKE '%$medic%' OR m.prenume LIKE '%$medic%')";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adauga'])) {
  $pacient_id = $_POST['pacient_id'];
  $medic_id = $_POST['medic_id'];
  $data = $_POST['data'];
  $interval = $_POST['interval_orar'];
  $motiv = $_POST['motiv'];

  $check_medic = mysqli_query($con, "SELECT id FROM programari WHERE medic_id = $medic_id AND data = '$data' AND interval_orar = '$interval'");
  $check_pacient = mysqli_query($con, "SELECT id FROM programari WHERE pacient_id = $pacient_id AND data = '$data' AND interval_orar = '$interval'");

  if (mysqli_num_rows($check_medic) > 0) {
    $_SESSION['mesaj_eroare'] = "Medicul are deja o programare în acest interval.";
  } elseif (mysqli_num_rows($check_pacient) > 0) {
    $_SESSION['mesaj_eroare'] = "Pacientul este deja programat în acest interval.";
  } else {
    $stmt = $con->prepare("INSERT INTO programari (pacient_id, medic_id, data, interval_orar, motiv) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $pacient_id, $medic_id, $data, $interval, $motiv);
    $stmt->execute();
  }

  header("Location: programari.php");
  exit();
}


if (isset($_GET['confirma'])) {
  $id = (int)$_GET['confirma'];
  mysqli_query($con, "UPDATE programari SET status = 'confirmată' WHERE id = $id");
  header("Location: programari.php");
  exit();
}
if (isset($_GET['anuleaza'])) {
  $id = (int)$_GET['anuleaza'];
  mysqli_query($con, "UPDATE programari SET status = 'anulată' WHERE id = $id");
  header("Location: programari.php");
  exit();
}
if (isset($_GET['finalizeaza'])) {
  $id = (int)$_GET['finalizeaza'];
  $check = mysqli_query($con, "SELECT status FROM programari WHERE id = $id");
  $row = mysqli_fetch_assoc($check);
  if ($row['status'] == 'confirmată') {
    mysqli_query($con, "UPDATE programari SET status = 'finalizată' WHERE id = $id");
  }
  header("Location: programari.php");
  exit();
}
if (isset($_GET['sterge'])) {
  $id = (int)$_GET['sterge'];
  mysqli_query($con, "DELETE FROM programari WHERE id = $id");
  header("Location: programari.php");
  exit();
}

$programari = mysqli_query($con, "
  SELECT p.*, pa.nume AS pacient_nume, m.nume AS medic_nume, m.prenume AS medic_prenume
  FROM programari p
  JOIN pacienti pa ON p.pacient_id = pa.id
  JOIN medici m ON p.medic_id = m.id
  $where
  ORDER BY p.data, p.interval_orar
");
?>

<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Programări</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(to bottom right, #f4f0ff, #ffffff);
 margin: 0; }
    .top-bar {
      background: white;
      padding: 16px 30px;
      text-align: center;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .top-bar h2 { margin: 0; color: #5c6bc0; }
    .btn {
      padding: 6px 12px;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      text-decoration: none;
      color: white;
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn.blue { background: #5c6bc0; }
    .btn.red { background: #e53935; }
    .btn.orange { background: #ff9800; }
    .btn.green { background: #4caf50; }
    .container {
      max-width: 1100px;
      margin: 30px auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: center;
    }
    th {  background: #5c6bc0;
    color: white; }
    tr:hover { background: #f9f9fc; }
    .center-btns { text-align: center; margin-bottom: 20px; }
  </style>
</head>
<body>

<div class="top-bar">
  <h2><i class="fas fa-calendar-check"></i> Programări</h2>
</div>

<div class="container">
  <div class="center-btns">
    <a href="programari.php?saptamana=curenta" class="btn blue"><i class="fas fa-calendar-week"></i> Săptămâna curentă</a>
    <a href="programari.php" class="btn red"><i class="fas fa-rotate-left"></i> Resetează</a>
    <a href="programari.php?<?= $ascunde_formulare ? '' : 'formulare=ascunse' ?>" class="btn blue">
      <i class="fas fa-bars"></i> <?= $ascunde_formulare ? 'Arată formularele' : 'Ascunde formularele' ?>
    </a>
  </div>

  <?php if (!$ascunde_formulare): ?>
  <form method="GET" style="margin-bottom: 20px; display:flex; gap:10px; flex-wrap:wrap">
    <input type="text" name="pacient" placeholder="Pacient" value="<?= $_GET['pacient'] ?? '' ?>">
    <input type="text" name="medic" placeholder="Medic" value="<?= $_GET['medic'] ?? '' ?>">
    <button type="submit" class="btn blue"><i class="fas fa-filter"></i> Caută</button>
  </form>

  <form method="POST" style="margin-bottom: 20px; display:flex; gap:10px; flex-wrap:wrap">
    <select name="pacient_id" required>
      <option value="">-- Pacient --</option>
      <?php
      $pacienti = mysqli_query($con, "SELECT id, nume FROM pacienti");
      while ($p = mysqli_fetch_assoc($pacienti)) {
        echo "<option value='{$p['id']}'>{$p['nume']}</option>";
      }
      ?>
    </select>
    <select name="medic_id" required>
      <option value="">-- Medic --</option>
      <?php
      $medici = mysqli_query($con, "SELECT id, nume, prenume FROM medici");
      while ($m = mysqli_fetch_assoc($medici)) {
        echo "<option value='{$m['id']}'>{$m['nume']} {$m['prenume']}</option>";
      }
      ?>
    </select>
    <input type="date" name="data" required>
    <select name="interval_orar" required>
      <option value="">-- Interval orar --</option>
      <option>09:00 - 10:00</option>
      <option>10:00 - 11:00</option>
      <option>11:00 - 12:00</option>
      <option>13:00 - 14:00</option>
      <option>14:00 - 15:00</option>
    </select>
    <input type="text" name="motiv" placeholder="Motiv (opțional)">
    <button type="submit" name="adauga" class="btn blue"><i class="fas fa-plus"></i> Adaugă</button>
  </form>
  <?php if (isset($_SESSION['mesaj_eroare'])): ?>
  <div style="color: red; text-align: center; margin-bottom: 10px;">
    <?= $_SESSION['mesaj_eroare']; unset($_SESSION['mesaj_eroare']); ?>
  </div>
<?php endif; ?>

  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>Zi</th>
        <th>Data</th>
        <th>Interval</th>
        <th>Pacient</th>
        <th>Medic</th>
        <th>Status</th>
        <th>Motiv</th>
        <th>Acțiuni</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($p = mysqli_fetch_assoc($programari)): ?>
      <tr>
        <td><?= zi_romana($p['data']) ?></td>
        <td><?= date("d.m.Y", strtotime($p['data'])) ?></td>
        <td><?= htmlspecialchars($p['interval_orar']) ?></td>
        <td><?= htmlspecialchars($p['pacient_nume']) ?></td>
        <td><?= htmlspecialchars($p['medic_nume'] . ' ' . $p['medic_prenume']) ?></td>
        <td><?= ucfirst($p['status']) ?></td>
        <td><?= htmlspecialchars($p['motiv']) ?></td>
        <td>
          <div style="display: flex; gap: 4px; flex-wrap: wrap; justify-content:center">
            <a href="?confirma=<?= $p['id'] ?>" class="btn blue" title="Confirmă">Confirmă</a>
            <a href="?anuleaza=<?= $p['id'] ?>" class="btn orange" title="Anulează">Anulează</a>
            <?php if ($p['status'] == 'confirmată'): ?>
              <a href="?finalizeaza=<?= $p['id'] ?>" class="btn green" title="Finalizează">Finalizează</a>
            <?php endif; ?>
            <a href="?sterge=<?= $p['id'] ?>" class="btn red" title="Șterge" onclick="return confirm('Sigur vrei să ștergi?')">Șterge</a>
          </div>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>