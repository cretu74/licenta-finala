<?php
session_start();
include("connection.php");
include("header.php");

// Liste dropdown
$medici_lista = mysqli_query($con, "SELECT id, nume, prenume FROM medici ORDER BY nume, prenume");
$pacienti_lista = mysqli_query($con, "SELECT id, nume FROM pacienti ORDER BY nume");

// Filtrare
$where = "WHERE 1=1";

if (!empty($_GET['serviciu'])) {
    $serviciu = mysqli_real_escape_string($con, $_GET['serviciu']);
    $where .= " AND s.denumire LIKE '%$serviciu%'";
}

if (!empty($_GET['pacient'])) {
    $pacient = mysqli_real_escape_string($con, $_GET['pacient']);
    $where .= " AND pa.id = '$pacient'";
}

if (!empty($_GET['medic'])) {
    $medic = mysqli_real_escape_string($con, $_GET['medic']);
    $where .= " AND m.id = '$medic'";
}

if (!empty($_GET['suma'])) {
    $suma = (float)$_GET['suma'];
    $where .= " AND p.suma = $suma";
}

if (!empty($_GET['de_la']) && !empty($_GET['pana_la'])) {
    $de_la = mysqli_real_escape_string($con, $_GET['de_la']);
    $pana_la = mysqli_real_escape_string($con, $_GET['pana_la']);
    $where .= " AND p.data_plata BETWEEN '$de_la' AND '$pana_la'";
}

$query = "
    SELECT p.*, s.denumire AS serviciu, pa.nume AS pacient, m.nume AS medic_nume, m.prenume AS medic_prenume
    FROM plati p
    JOIN servicii s ON p.serviciu_id = s.id
    JOIN pacienti pa ON s.pacient_id = pa.id
    JOIN medici m ON s.medic_id = m.id
    $where
    ORDER BY p.data_plata DESC
";
$rezultat = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Raport incasări</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom right, #f4f0ff, #ffffff);
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 1100px;
      margin: 40px auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #5c6bc0;
      margin-bottom: 30px;
    }
    form {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 10px;
      align-items: end;
      margin-bottom: 20px;
    }
    input[type="text"], input[type="number"], input[type="date"], select {
      padding: 8px;
      border-radius: 6px;
      border: 1px solid #ccc;
      width: 100%;
    }
    .btn {
      padding: 8px 14px;
      background: #5c6bc0;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-danger {
      background: #e53935;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: left;
    }
    th {
       background: #5c6bc0;
    color: white;;
    }
    tr:hover {
      background-color: #f9f9fc;
    }
    .total {
      text-align: right;
      font-weight: bold;
    }
  </style>
</head>
<body>
<div class="container">
  <h2><i class="fas fa-cash-register"></i> Raport incasări</h2>
 <div style="text-align: center; margin-bottom: 20px;">
  <a href="export_raport_incasari.php?<?= http_build_query($_GET) ?>" class="btn" style="background:#5c6bc0; color:white; padding:8px 14px; border-radius:6px; text-decoration:none;" target="_blank">
    <i class="fas fa-file-pdf"></i> Exportă PDF
  </a>
</div>


  <form method="GET">
    <div>
      <label>Serviciu</label>
      <input type="text" name="serviciu" value="<?= htmlspecialchars($_GET['serviciu'] ?? '') ?>">
    </div>

    <div>
      <label>Pacient</label>
      <select name="pacient">
        <option value="">-- Toți pacienții --</option>
        <?php while ($p = mysqli_fetch_assoc($pacienti_lista)): ?>
          <option value="<?= $p['id'] ?>" <?= ($_GET['pacient'] ?? '') == $p['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($p['nume']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div>
      <label>Medic</label>
      <select name="medic">
        <option value="">-- Toți medicii --</option>
        <?php while ($m = mysqli_fetch_assoc($medici_lista)): ?>
          <option value="<?= $m['id'] ?>" <?= ($_GET['medic'] ?? '') == $m['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($m['nume'] . ' ' . $m['prenume']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div>
      <label>Sumă exactă</label>
      <input type="number" step="0.01" name="suma" value="<?= htmlspecialchars($_GET['suma'] ?? '') ?>">
    </div>

    <div>
      <label>De la</label>
      <input type="date" name="de_la" value="<?= htmlspecialchars($_GET['de_la'] ?? '') ?>">
    </div>
    <div>
      <label>Până la</label>
      <input type="date" name="pana_la" value="<?= htmlspecialchars($_GET['pana_la'] ?? '') ?>">
    </div>
    <div style="grid-column: span 2; display: flex; gap: 10px;">
      <button type="submit" class="btn"><i class="fas fa-filter"></i> Filtrează</button>
      <a href="raport_incasari.php" class="btn btn-danger"><i class="fas fa-rotate-left"></i> Resetează</a>
    </div>
  </form>

  <table>
    <thead>
      <tr>
        <th>Denumire serviciu</th>
        <th>Pacient</th>
        <th>Medic</th>
        <th>Sumă</th>
        <th>Data plății</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $total = 0;
      while ($r = mysqli_fetch_assoc($rezultat)):
          $total += (float)$r['suma'];
      ?>
      <tr>
        <td><?= htmlspecialchars($r['serviciu']) ?></td>
        <td><?= htmlspecialchars($r['pacient']) ?></td>
        <td><?= htmlspecialchars($r['medic_nume'] . ' ' . $r['medic_prenume']) ?></td>
        <td><?= number_format($r['suma'], 2) ?> lei</td>
        <td><?= date("d.m.Y", strtotime($r['data_plata'])) ?></td>
      </tr>
      <?php endwhile; ?>
      <tr>
        <td colspan="3" class="total">Total:</td>
        <td><strong><?= number_format($total, 2) ?> lei</strong></td>
        <td></td>
      </tr>
    </tbody>
  </table>
</div>
</body>
</html>
