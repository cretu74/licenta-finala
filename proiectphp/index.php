<?php
session_start();
include("connection.php");
include("functions.php");

$user_data = check_login($con); // Poate fi și admin sau utilizator normal

$azi = date('Y-m-d');
$luna = date('m');
$an = date('Y');

// Programări
$programari_azi = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM programari WHERE data = '$azi'"))['total'];
$programari_sapt = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM programari WHERE WEEK(data, 1) = WEEK(CURDATE(), 1) AND YEAR(data) = YEAR(CURDATE())"))['total'];
$programari_luna = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM programari WHERE MONTH(data) = $luna AND YEAR(data) = $an"))['total'];

// Încasări
$incasari_azi = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(s.pret) AS total FROM plati p JOIN servicii s ON p.serviciu_id = s.id WHERE DATE(p.data_plata) = '$azi'"))['total'] ?? 0;
$incasari_sapt = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(s.pret) AS total FROM plati p JOIN servicii s ON p.serviciu_id = s.id WHERE WEEK(p.data_plata, 1) = WEEK(CURDATE(), 1) AND YEAR(p.data_plata) = YEAR(CURDATE())"))['total'] ?? 0;
$incasari_luna = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(s.pret) AS total FROM plati p JOIN servicii s ON p.serviciu_id = s.id WHERE MONTH(p.data_plata) = $luna AND YEAR(p.data_plata) = $an"))['total'] ?? 0;

// Statusuri pe zi/săptămână/lună
function get_statusuri($cond, $con) {
    $rez = ['anulată' => 0, 'confirmată' => 0, 'finalizată' => 0];
    $sql = "SELECT status, COUNT(*) AS total FROM programari WHERE $cond GROUP BY status";
    $q = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($q)) {
        $rez[$row['status']] = $row['total'];
    }
    return $rez;
}
$status_azi = get_statusuri("data = '$azi'", $con);
$status_sapt = get_statusuri("WEEK(data, 1) = WEEK(CURDATE(), 1) AND YEAR(data) = YEAR(CURDATE())", $con);
$status_luna = get_statusuri("MONTH(data) = $luna AND YEAR(data) = $an", $con);

// Programări de azi
$lista_programari = mysqli_query($con, "
    SELECT p.interval_orar, p.status, pa.nume AS pacient, m.nume AS medic
    FROM programari p
    JOIN pacienti pa ON p.pacient_id = pa.id
    JOIN medici m ON p.medic_id = m.id
    WHERE p.data = '$azi'
    ORDER BY p.interval_orar ASC
");
?>

<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>SPS Vet - Dashboard</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Helvetica, sans-serif;
      background: linear-gradient(to bottom right, #f4f0ff, #ffffff);
      background-image: url('imagine.png');
      background-size: cover;
    }

    .dashboard {
      display: flex;
      justify-content: center;
      gap: 40px;
      padding: 40px 0;
      flex-wrap: wrap;
    }

    .card {
      background-color: white;
      border-radius: 12px;
      padding: 25px;
      width: 280px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      text-align: left;
    }

    .card h4 {
      margin-bottom: 15px;
      font-size: 16px;
      color: #333;
    }

    .line {
      margin: 6px 0;
      font-size: 15px;
    }

    .orange { color: #f57c00; }
    .purple { color: #ab47bc; }
    .green { color: #43a047; }

    .status-grid {
      display: flex;
      justify-content: space-between;
      gap: 20px;
    }

    .status-group {
      width: 90px;
      font-size: 14px;
      text-align: center;
    }

    .tabel-programari {
      max-width: 900px;
      margin: 40px auto;
      background: white;
      border-radius: 10px;
      padding: 25px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .tabel-programari h3 {
      color: #5c6bc0;
      margin-bottom: 20px;
      font-size: 18px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 15px;
    }

    th, td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #5c6bc0;
      color: white;
      text-align: left;
    }
  </style>
</head>
<body>
<?php include("header.php"); ?>

<div class="dashboard">
  <div class="card">
    <h4>📋 PROGRAMĂRI</h4>
    <div class="line">📅 Astăzi: <b><?= $programari_azi ?></b></div>
    <div class="line">🗓️ Săptămână: <b><?= $programari_sapt ?></b></div>
    <div class="line">📆 Lună: <b><?= $programari_luna ?></b></div>
  </div>

  <div class="card">
    <h4>💰 Încasări</h4>
    <div class="line">📅 Astăzi: <b><?= $incasari_azi ?> lei</b></div>
    <div class="line">🗓️ Săptămână: <b><?= $incasari_sapt ?> lei</b></div>
    <div class="line">📆 Lună: <b><?= $incasari_luna ?> lei</b></div>
  </div>

  <div class="card">
    <h4>📊 Status Programări</h4>
    <div class="status-grid">
      <div class="status-group">
        <div class="line orange">🔸 <?= $status_azi['anulată'] ?></div>
        <div class="line purple">🟣 <?= $status_azi['confirmată'] ?></div>
        <div class="line green">✅ <?= $status_azi['finalizată'] ?></div>
        <div class="line" style="font-size: 13px;">azi</div>
      </div>
      <div class="status-group">
        <div class="line orange">🔸 <?= $status_sapt['anulată'] ?></div>
        <div class="line purple">🟣 <?= $status_sapt['confirmată'] ?></div>
        <div class="line green">✅ <?= $status_sapt['finalizată'] ?></div>
        <div class="line" style="font-size: 13px;">săpt.</div>
      </div>
      <div class="status-group">
        <div class="line orange">🔸 <?= $status_luna['anulată'] ?></div>
        <div class="line purple">🟣 <?= $status_luna['confirmată'] ?></div>
        <div class="line green">✅ <?= $status_luna['finalizată'] ?></div>
        <div class="line" style="font-size: 13px;">lună</div>
      </div>
    </div>
  </div>
</div>

<!-- TABEL PROGRAMĂRI ASTĂZI -->
<div class="tabel-programari">
  <h3>📅 Programări pentru <b>astăzi</b></h3>
  <table>
    <tr><th>Medic</th><th>Pacient</th><th>Ora</th><th>Status</th></tr>
    <?php while ($row = mysqli_fetch_assoc($lista_programari)): ?>
      <tr>
        <td><?= htmlspecialchars($row['medic']) ?></td>
        <td><?= htmlspecialchars($row['pacient']) ?></td>
        <td><?= htmlspecialchars($row['interval_orar']) ?></td>
        <td>
          <?php
            $status = $row['status'];
            if ($status == 'anulată') echo "<span style='color:#f57c00;'>🔸 Anulată</span>";
            elseif ($status == 'confirmată') echo "<span style='color:#ab47bc;'>🟣 Confirmată</span>";
            elseif ($status == 'finalizată') echo "<span style='color:#43a047;'>✅ Finalizată</span>";
            else echo htmlspecialchars($status);
          ?>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>

</body>
</html>
