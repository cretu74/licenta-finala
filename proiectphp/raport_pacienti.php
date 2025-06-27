<?php
session_start();
include("connection.php");
include("header.php");

$where = "WHERE 1=1";

// Filtre
if (!empty($_GET['nume'])) {
    $nume = mysqli_real_escape_string($con, $_GET['nume']);
    $where .= " AND p.nume = '" . $nume . "'";
}
if (!empty($_GET['specie'])) {
    $specie = mysqli_real_escape_string($con, $_GET['specie']);
    $where .= " AND p.specie LIKE '%$specie%'";
}
if (!empty($_GET['proprietar'])) {
    $proprietar = mysqli_real_escape_string($con, $_GET['proprietar']);
    $where .= " AND CONCAT(pr.nume, ' ', pr.prenume) = '" . $proprietar . "'";
}
if (!empty($_GET['de_la']) && !empty($_GET['pana_la'])) {
    $de_la = mysqli_real_escape_string($con, $_GET['de_la']);
    $pana_la = mysqli_real_escape_string($con, $_GET['pana_la']);
    $where .= " AND p.data_inregistrare BETWEEN '$de_la' AND '$pana_la'";
}

// Preluare pacienți
$query = "
    SELECT p.id, p.nume AS pacient_nume, p.specie, p.rasa, p.data_inregistrare,
           pr.nume AS prop_nume, pr.prenume AS prop_prenume
    FROM pacienti p
    JOIN proprietari pr ON p.proprietar_id = pr.id
    $where
    ORDER BY p.data_inregistrare DESC
";
$rezultat = mysqli_query($con, $query);

// Preluare nume distincte
$pacienti_lista = mysqli_query($con, "SELECT DISTINCT nume FROM pacienti ORDER BY nume ASC");
$proprietari_lista = mysqli_query($con, "SELECT DISTINCT CONCAT(nume, ' ', prenume) AS nume_complet FROM proprietari ORDER BY nume, prenume");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Raport pacienți</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(to bottom right, #f4f0ff, #ffffff);
 margin: 0; padding: 0; }
        .container { max-width: 1100px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #5c6bc0; margin-bottom: 30px; }
        form { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; align-items: end; }
        select, input[type="text"], input[type="date"] { padding: 8px; border-radius: 6px; border: 1px solid #ccc; width: 100%; }
        .btn { padding: 8px 14px; background: #5c6bc0; color: white; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; text-align: center; }
        .btn-danger { background: #e53935; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th {  background: #5c6bc0;
    color: white; }
        tr:hover { background-color: #f9f9fc; }
    </style>
</head>
<body>
<div class="container">
    <h2><i class="fas fa-paw"></i> Raport pacienți</h2>
    <div style="text-align: center; margin-bottom: 20px;">
  <a href="export_raport_pacienti.php?<?= http_build_query($_GET) ?>" class="btn" target="_blank">
    <i class="fas fa-file-pdf"></i> Exportă PDF
  </a>
</div>


    <!-- Form filtre -->
    <form method="GET">
        <div>
            <label>Nume</label>
            <select name="nume">
                <option value="">-- Toate --</option>
                <?php while ($row = mysqli_fetch_assoc($pacienti_lista)): ?>
                    <option value="<?= $row['nume'] ?>" <?= ($_GET['nume'] ?? '') == $row['nume'] ? 'selected' : '' ?>><?= $row['nume'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label>Specie</label>
            <input type="text" name="specie" value="<?= htmlspecialchars($_GET['specie'] ?? '') ?>">
        </div>
        <div>
            <label>Proprietar</label>
            <select name="proprietar">
                <option value="">-- Toți --</option>
                <?php while ($row = mysqli_fetch_assoc($proprietari_lista)): ?>
                    <option value="<?= $row['nume_complet'] ?>" <?= ($_GET['proprietar'] ?? '') == $row['nume_complet'] ? 'selected' : '' ?>><?= $row['nume_complet'] ?></option>
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
            <a href="raport_pacienti.php" class="btn btn-danger"><i class="fas fa-rotate-left"></i> Resetează</a>
        </div>
    </form>

    <!-- Tabel rezultate -->
    <table>
        <thead>
            <tr>
                <th>Nume pacient</th>
                <th>Specie</th>
                <th>Rasă</th>
                <th>Proprietar</th>
                <th>Data înregistrării</th>
                <th>Servicii</th>
                <th>Plăți</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $total_pacienti = 0;
        $total_servicii = 0;
        $total_plati = 0;
        while ($row = mysqli_fetch_assoc($rezultat)):
            $total_pacienti++;
            $pacient_id = $row['id'];
            $nr_servicii = mysqli_num_rows(mysqli_query($con, "SELECT id FROM servicii WHERE pacient_id = $pacient_id"));
            $nr_plati = mysqli_num_rows(mysqli_query($con, "SELECT p.id FROM plati p JOIN servicii s ON p.serviciu_id = s.id WHERE s.pacient_id = $pacient_id"));
            $total_servicii += $nr_servicii;
            $total_plati += $nr_plati;
        ?>
            <tr>
                <td><?= htmlspecialchars($row['pacient_nume']) ?></td>
                <td><?= htmlspecialchars($row['specie']) ?></td>
                <td><?= htmlspecialchars($row['rasa']) ?></td>
                <td><?= $row['prop_nume'] . ' ' . $row['prop_prenume'] ?></td>
                <td><?= date("d.m.Y", strtotime($row['data_inregistrare'])) ?></td>
                <td><?= $nr_servicii ?></td>
                <td><?= $nr_plati ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right;"><strong>Total:</strong></td>
                <td><strong><?= $total_servicii ?></strong></td>
                <td><strong><?= $total_plati ?></strong></td>
            </tr>
        </tfoot>
    </table>
    <p style="margin-top: 20px;"><strong>Număr total pacienți afișați:</strong> <?= $total_pacienti ?></p>
</div>
</body>
</html>
