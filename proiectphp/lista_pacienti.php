<?php
include("connection.php");
include("functions.php");
session_start();

$eroare = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adauga'])) {
    $numar_fisa = mysqli_real_escape_string($con, $_POST['numar_fisa']);
    $verifica = mysqli_query($con, "SELECT id FROM pacienti WHERE numar_fisa = '$numar_fisa'");
    if (mysqli_num_rows($verifica) > 0) {
        $_SESSION['eroare_fisa'] = "Numărul de fișă există deja!";
        header("Location: lista_pacienti.php");
        exit();
    }

    $stmt = $con->prepare("INSERT INTO pacienti (numar_fisa, data_inregistrare, proprietar_id, nume, specie, rasa, sex, greutate, culoare, varsta, microcip, boli_cronice) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssssssss",
        $_POST['numar_fisa'], $_POST['data_inregistrare'], $_POST['proprietar_id'], $_POST['nume'],
        $_POST['specie'], $_POST['rasa'], $_POST['sex'], $_POST['greutate'],
        $_POST['culoare'], $_POST['varsta'], $_POST['microcip'], $_POST['boli_cronice']
    );
    $stmt->execute();
    header("Location: lista_pacienti.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sterge'])) {
    $id = (int)$_POST['sterge'];
    mysqli_query($con, "DELETE FROM pacienti WHERE id = $id");
    header("Location: lista_pacienti.php");
    exit();
}

$nume = $_GET['nume'] ?? '';
$proprietar = $_GET['proprietar'] ?? '';
$nr_fisa = $_GET['numar_fisa'] ?? '';
$show = $_GET['show'] ?? '';

$where = [];
if ($nume !== '') $where[] = "p.nume LIKE '%" . mysqli_real_escape_string($con, $nume) . "%'";
if ($proprietar !== '') $where[] = "CONCAT(pr.nume, ' ', pr.prenume) LIKE '%" . mysqli_real_escape_string($con, $proprietar) . "%'";
if ($nr_fisa !== '') $where[] = "p.numar_fisa LIKE '%" . mysqli_real_escape_string($con, $nr_fisa) . "%'";

$filter_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";
$result = mysqli_query($con, "
    SELECT p.*, pr.nume AS prop_nume, pr.prenume AS prop_prenume 
    FROM pacienti p 
    LEFT JOIN proprietari pr ON p.proprietar_id = pr.id 
    $filter_sql 
    ORDER BY p.numar_fisa ASC
");
$pacienti = mysqli_fetch_all($result, MYSQLI_ASSOC);
$proprietari = mysqli_query($con, "SELECT id, nume, prenume FROM proprietari ORDER BY nume ASC");

if (isset($_SESSION['eroare_fisa'])) {
    $eroare = $_SESSION['eroare_fisa'];
    unset($_SESSION['eroare_fisa']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pacienți</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: linear-gradient(to bottom right, #f4f0ff, #ffffff);
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #5c6bc0;
        }
        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 4px;
            background: #5c6bc0;
            color: white;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-danger {
          background: #e53935;
        }
        .form-section {
            margin: 20px 0;
        }
        form input, form select {
            width: 100%;
            padding: 8px;
            border: 1px solid #bbb;
            border-radius: 20px;
        }
        .grid-4 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #5c6bc0;
    color: white;;
        }
        tr:hover {
            background-color: #f1f1fa;
        }
        .alert {
            padding: 12px;
            background-color: #ffdddd;
            color: #a33;
            border: 1px solid #f5c2c2;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .top-buttons {
            display: flex;
            justify-content: start;
            gap: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<?php include("header.php"); ?>
<div class="container">
    <h1><i class="fas fa-paw"></i> Pacienți</h1>

    <?php if (!empty($eroare)): ?>
        <div class="alert"><i class="fas fa-exclamation-circle"></i> <?= $eroare ?></div>
    <?php endif; ?>

    <div class="top-buttons">
        <a href="?show=filtre" class="btn"><i class="fas fa-filter"></i> Caută pacienți</a>
        <a href="?show=adauga" class="btn"><i class="fas fa-plus-circle"></i> Adaugă pacient</a>
        <a href="lista_pacienti.php" class="btn btn-danger"><i class="fas fa-rotate-left"></i> Resetează</a>
    </div>

    <?php if ($show === 'filtre'): ?>
        <div class="form-section">
            <form method="GET" class="grid-4">
                <input type="hidden" name="show" value="filtre">
                <div><label>Nr. fișă</label><input type="text" name="numar_fisa" value="<?= htmlspecialchars($nr_fisa) ?>"></div>
                <div><label>Proprietar</label><input type="text" name="proprietar" value="<?= htmlspecialchars($proprietar) ?>"></div>
                <div><label>Nume pacient</label><input type="text" name="nume" value="<?= htmlspecialchars($nume) ?>"></div>
                <div style="display:flex; gap:10px; align-items:flex-end;">
                    <button type="submit" class="btn"><i class="fas fa-search"></i> Caută</button>
                </div>
            </form>
        </div>
    <?php elseif ($show === 'adauga'): ?>
        <div class="form-section">
            <form method="POST" class="grid-4">
                <div><label>Nr. fișă *</label><input name="numar_fisa" required></div>
                <div><label>Data înregistrare *</label><input type="date" name="data_inregistrare" required></div>
                <div>
                    <label>Proprietar *</label>
                    <select name="proprietar_id" required>
                        <option value="">-- Selectează --</option>
                        <?php while ($row = mysqli_fetch_assoc($proprietari)): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nume'] . ' ' . $row['prenume']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div><label>Nume animal *</label><input name="nume" required></div>
                <div><label>Specie</label><input name="specie"></div>
                <div><label>Rasă</label><input name="rasa"></div>
                <div><label>Sex</label>
                    <select name="sex">
                        <option value="M">M</option>
                        <option value="F">F</option>
                    </select>
                </div>
                <div><label>Greutate</label><input name="greutate"></div>
                <div><label>Culoare</label><input name="culoare"></div>
                <div><label>Vârstă</label><input name="varsta"></div>
                <div><label>Microcip</label><input name="microcip"></div>
                <div><label>Boli cronice</label><input name="boli_cronice"></div>
                <div style="grid-column: span 2;">
                    <button type="submit" name="adauga" class="btn" style="margin-top: 10px;"><i class="fas fa-check-circle"></i> Adaugă</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Nr. fișă</th>
                <th>Nume</th>
                <th>Specie</th>
                <th>Rasă</th>
                <th>Proprietar</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pacienti as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['numar_fisa']) ?></td>
                <td><?= htmlspecialchars($p['nume']) ?></td>
                <td><?= htmlspecialchars($p['specie']) ?></td>
                <td><?= htmlspecialchars($p['rasa']) ?></td>
                <td><?= htmlspecialchars($p['prop_nume'] . ' ' . $p['prop_prenume']) ?></td>
                <td>
                    <a class="btn" href="pacienti.php?id=<?= $p['id'] ?>"><i class="fas fa-info-circle"></i> Detalii</a>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="sterge" value="<?= $p['id'] ?>">
                        <button class="btn btn-danger" onclick="return confirm('Ștergi pacientul?')"><i class="fas fa-trash-alt"></i> Șterge</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
