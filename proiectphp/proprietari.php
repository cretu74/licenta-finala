<?php
include("connection.php");
include("functions.php");
session_start();

$showFiltru = isset($_GET['filtru']);
$showAdauga = isset($_GET['adauga']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adauga'])) {
    $stmt = $con->prepare("INSERT INTO proprietari (nume, prenume, adresa, telefon, email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $_POST['nume'], $_POST['prenume'], $_POST['adresa'], $_POST['telefon'], $_POST['email']);
    $stmt->execute();
    header("Location: proprietari.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sterge'])) {
    $id = (int)$_POST['sterge'];
    mysqli_query($con, "DELETE FROM proprietari WHERE id = $id");
    header("Location: proprietari.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salveaza'])) {
    $stmt = $con->prepare("UPDATE proprietari SET nume=?, prenume=?, adresa=?, telefon=?, email=? WHERE id=?");
    $stmt->bind_param("sssssi", $_POST['nume'], $_POST['prenume'], $_POST['adresa'], $_POST['telefon'], $_POST['email'], $_POST['id']);
    $stmt->execute();
    header("Location: proprietari.php");
    exit();
}

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : null;

$nume_filter = $_GET['nume'] ?? '';
$prenume_filter = $_GET['prenume'] ?? '';
$animal_filter = $_GET['animal'] ?? '';

$where = [];
if ($nume_filter !== '') $where[] = "p.nume LIKE '%" . mysqli_real_escape_string($con, $nume_filter) . "%'";
if ($prenume_filter !== '') $where[] = "p.prenume LIKE '%" . mysqli_real_escape_string($con, $prenume_filter) . "%'";
if ($animal_filter !== '') $where[] = "(SELECT COUNT(*) FROM pacienti WHERE pacienti.proprietar_id = p.id AND pacienti.nume LIKE '%" . mysqli_real_escape_string($con, $animal_filter) . "%') > 0";

$filter_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";
$proprietari = mysqli_query($con, "SELECT * FROM proprietari p $filter_sql ORDER BY nume ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Proprietari</title>
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
        h1 {
            text-align: center;
            color: #5c6bc0;
        }
        .btn {
            padding: 8px 14px;
            background: #5c6bc0;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-danger {
            background: #e53935;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .toggle-links {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 20px;
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
            background-color: #f9f9fc;
        }
    </style>
</head>
<body>
<?php include("header.php"); ?>
<div class="container">
    <h1><i class="fas fa-users"></i> Proprietari</h1>

    <div class="toggle-links">
        <a class="btn" href="proprietari.php?filtru=1"><i class="fas fa-filter"></i> Caută proprietar</a>
        <a class="btn" href="proprietari.php?adauga=1"><i class="fas fa-user-plus"></i> Adaugă proprietar</a>
        <a class="btn btn-danger" href="proprietari.php"><i class="fas fa-rotate-left"></i> Resetează</a>
    </div>

    <?php if ($showFiltru): ?>
    <form method="GET" class="form-grid" style="margin-bottom:30px">
        <div>
            <label>Nume</label>
            <input type="text" name="nume" value="<?= htmlspecialchars($nume_filter) ?>">
        </div>
        <div>
            <label>Prenume</label>
            <input type="text" name="prenume" value="<?= htmlspecialchars($prenume_filter) ?>">
        </div>
        <div>
            <label>Animal</label>
            <input type="text" name="animal" value="<?= htmlspecialchars($animal_filter) ?>">
        </div>
        <div style="grid-column: span 2;">
            <button class="btn" type="submit"><i class="fas fa-search"></i> Caută</button>
        </div>
    </form>
    <?php endif; ?>

    <?php if ($showAdauga): ?>
    <form method="POST" style="margin-bottom:30px">
        <div class="form-grid">
            <div><label>Nume *</label><input type="text" name="nume" required></div>
            <div><label>Prenume *</label><input type="text" name="prenume" required></div>
            <div><label>Adresă</label><textarea name="adresa"></textarea></div>
            <div><label>Telefon</label><input type="text" name="telefon"></div>
            <div><label>Email</label><input type="email" name="email"></div>
        </div>
        <button class="btn" name="adauga"><i class="fas fa-check-circle"></i> Salvează</button>
    </form>
    <?php endif; ?>

    <table>
        <thead>
        <tr>
            <th>Nume complet</th>
            <th>Adresă</th>
            <th>Telefon</th>
            <th>Email</th>
            <th>Pacienți</th>
            <th>Acțiuni</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($prop = mysqli_fetch_assoc($proprietari)):
            $id = $prop['id'];
            $pacienti = mysqli_query($con, "SELECT nume FROM pacienti WHERE proprietar_id = $id");
            $lista_pacienti = [];
            while ($row = mysqli_fetch_assoc($pacienti)) {
                $lista_pacienti[] = htmlspecialchars($row['nume']);
            }
        ?>
        <tr>
            <?php if ($edit_id == $id): ?>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <td><input type="text" name="nume" value="<?= htmlspecialchars($prop['nume']) ?>" required><br>
                        <input type="text" name="prenume" value="<?= htmlspecialchars($prop['prenume']) ?>" required></td>
                    <td><input type="text" name="adresa" value="<?= htmlspecialchars($prop['adresa']) ?>"></td>
                    <td><input type="text" name="telefon" value="<?= htmlspecialchars($prop['telefon']) ?>"></td>
                    <td><input type="email" name="email" value="<?= htmlspecialchars($prop['email']) ?>"></td>
                    <td><?= $lista_pacienti ? implode(", ", $lista_pacienti) : '<em>Niciun pacient</em>' ?></td>
                    <td>
                        <button class="btn" name="salveaza">Salvează</button>
                        <a href="proprietari.php" class="btn btn-danger">Renunță</a>
                    </td>
                </form>
            <?php else: ?>
                <td><?= htmlspecialchars($prop['nume'] . ' ' . $prop['prenume']) ?></td>
                <td><?= htmlspecialchars($prop['adresa']) ?></td>
                <td><?= htmlspecialchars($prop['telefon']) ?></td>
                <td><?= htmlspecialchars($prop['email']) ?></td>
                <td><?= $lista_pacienti ? implode(", ", $lista_pacienti) : '<em>Niciun pacient</em>' ?></td>
                <td>
                    <a class="btn" href="proprietari.php?edit=<?= $id ?>"><i class="fas fa-edit"></i> Editează</a>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="sterge" value="<?= $id ?>">
                        <button class="btn btn-danger" onclick="return confirm('Esti sigur ca vrei sa stergi acest proprietar?');"><i class="fas fa-trash"></i> Șterge</button>
                    </form>
                </td>
            <?php endif; ?>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
