<?php
session_start();
include("connection.php");
include("header.php");

function zi_romana($data) {
  $zile = ['Duminică','Luni','Marți','Miercuri','Joi','Vineri','Sâmbătă'];
  return $zile[date('w', strtotime($data))];
}

// Dropdown
$medici_lista = mysqli_query($con, "SELECT id, nume, prenume FROM medici ORDER BY nume, prenume");
$pacienti_lista = mysqli_query($con, "SELECT id, nume FROM pacienti ORDER BY nume");

$where = "WHERE 1=1";
if (!empty($_GET['status'])) {
  $status = mysqli_real_escape_string($con, $_GET['status']);
  $where .= " AND p.status = '$status'";
}
if (!empty($_GET['pacient'])) {
  $pacient = mysqli_real_escape_string($con, $_GET['pacient']);
  $where .= " AND pa.id = '$pacient'";
}
if (!empty($_GET['medic'])) {
  $medic = mysqli_real_escape_string($con, $_GET['medic']);
  $where .= " AND m.id = '$medic'";
}
if (!empty($_GET['de_la']) && !empty($_GET['pana_la'])) {
  $de_la = mysqli_real_escape_string($con, $_GET['de_la']);
  $pana_la = mysqli_real_escape_string($con, $_GET['pana_la']);
  $where .= " AND p.data BETWEEN '$de_la' AND '$pana_la'";
}

$query = "
  SELECT p.*, pa.nume AS pacient_nume, m.nume AS medic_nume, m.prenume AS medic_prenume
  FROM programari p
  JOIN pacienti pa ON p.pacient_id = pa.id
  JOIN medici m ON p.medic_id = m.id
  $where
  ORDER BY p.data DESC
";
$rezultat = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Raport programări</title>
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
    input[type="text"], input[type="date"], select {
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
    tfoot td {
      font-weight: bold;
      background: #f7f7f7;
    }
  </style>
</head>
<body>
<div class="container">
  <h2><i class="fas fa-calendar-check"></i> Raport programări</h2>
  <div style="text-align: center; margin-bottom: 20px;">
    <a href="export_raport_programari.php?<?= http_build_query($_GET) ?>" 
       class="btn" 
       style="background:#5c6bc0; color:white; padding:8px 14px; border-radius:6px; text-decoration:none;" 
       target="_blank">
        <i class="fas fa-file-pdf"></i> Exportă PDF
    </a>
</div>


  <form method="GET">
    <div>
      <label>Status</label>
      <select name="status">
        <option value="">-- Toate --</option>
        <option value="neconfirmată" <?= ($_GET['status'] ?? '') == 'neconfirmată' ? 'selected' : '' ?>>Neconfirmată</option>
        <option value="confirmată" <?= ($_GET['status'] ?? '') == 'confirmată' ? 'selected' : '' ?>>Confirmată</option>
        <option value="finalizată" <?= ($_GET['status'] ?? '') == 'finalizată' ? 'selected' : '' ?>>Finalizată</option>
        <option value="anulată" <?= ($_GET['status'] ?? '') == 'anulată' ? 'selected' : '' ?>>Anulată</option>
      </select>
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
      <label>De la</label>
      <input type="date" name="de_la" value="<?= htmlspecialchars($_GET['de_la'] ?? '') ?>">
    </div>
    <div>
      <label>Până la</label>
      <input type="date" name="pana_la" value="<?= htmlspecialchars($_GET['pana_la'] ?? '') ?>">
    </div>

    <div style="grid-column: span 2; display: flex; gap: 10px;">
      <button type="submit" class="btn"><i class="fas fa-filter"></i> Filtrează</button>
      <a href="raport_programari.php" class="btn btn-danger"><i class="fas fa-rotate-left"></i> Resetează</a>
    </div>
  </form>

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
      </tr>
    </thead>
    <tbody>
      <?php
        $total = 0;
        $statistici = ['neconfirmată' => 0, 'confirmată' => 0, 'finalizată' => 0, 'anulată' => 0];
        while ($p = mysqli_fetch_assoc($rezultat)):
          $total++;
          $statut = strtolower($p['status']);
          if (isset($statistici[$statut])) {
              $statistici[$statut]++;
          }
      ?>
      <tr>
        <td><?= zi_romana($p['data']) ?></td>
        <td><?= date("d.m.Y", strtotime($p['data'])) ?></td>
        <td><?= htmlspecialchars($p['interval_orar']) ?></td>
        <td><?= htmlspecialchars($p['pacient_nume']) ?></td>
        <td><?= htmlspecialchars($p['medic_nume'] . ' ' . $p['medic_prenume']) ?></td>
        <td><?= ucfirst($p['status']) ?></td>
        <td><?= htmlspecialchars($p['motiv']) ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="7">
          Total programări afișate: <?= $total ?> |
          Neconfirmate: <?= $statistici['neconfirmată'] ?> |
          Confirmate: <?= $statistici['confirmată'] ?> |
          Finalizate: <?= $statistici['finalizată'] ?> |
          Anulate: <?= $statistici['anulată'] ?>
        </td>
      </tr>
    </tfoot>
  </table>
</div>
</body>
</html>
