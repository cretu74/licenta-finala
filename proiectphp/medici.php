<?php
include("connection.php");
include("functions.php");
session_start();

// Formulare vizibile?
$showFiltru = isset($_GET['filtru']);
$showAdauga = isset($_GET['adauga']);

// Adaugă medic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adauga'])) {
    $stmt = $con->prepare("INSERT INTO medici (nume, prenume, telefon, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $_POST['nume'], $_POST['prenume'], $_POST['telefon'], $_POST['email']);
    $stmt->execute();
    header("Location: medici.php");
    exit();
}

// Șterge medic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sterge'])) {
    $id = (int)$_POST['sterge'];
    mysqli_query($con, "DELETE FROM medici WHERE id = $id");
    header("Location: medici.php");
    exit();
}

// Salvează modificare
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salveaza'])) {
    $stmt = $con->prepare("UPDATE medici SET nume=?, prenume=?, telefon=?, email=? WHERE id=?");
    $stmt->bind_param("ssssi", $_POST['nume'], $_POST['prenume'], $_POST['telefon'], $_POST['email'], $_POST['id']);
    $stmt->execute();
    header("Location: medici.php");
    exit();
}

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$nume_filter = $_GET['nume'] ?? '';
$prenume_filter = $_GET['prenume'] ?? '';

$where = [];
if ($nume_filter !== '') $where[] = "nume LIKE '%" . mysqli_real_escape_string($con, $nume_filter) . "%'";
if ($prenume_filter !== '') $where[] = "prenume LIKE '%" . mysqli_real_escape_string($con, $prenume_filter) . "%'";

$filter_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";
$medici = mysqli_query($con, "SELECT * FROM medici $filter_sql ORDER BY nume ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Medici</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(to bottom right, #f4f0ff, #ffffff);
 margin: 0; padding: 0; }
        .container {
            max-width: 1100px; margin: 40px auto; background: white;
            padding: 30px; border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h1 { text-align: center; color: #5c6bc0; }
        .btn {
            padding: 8px 14px; border: none; border-radius: 4px;
            cursor: pointer; color: white; text-decoration: none;
            background: #5c6bc0;
        }
        .btn-danger { background: #e53935; }
        .form-grid {
            display: grid; grid-template-columns: repeat(2, 1fr);
            gap: 20px; margin-bottom: 20px;
        }
        .toggle-links {
            display: flex; gap: 10px; margin-bottom: 20px;
            justify-content: center;
        }
        input, textarea {
            width: 100%; padding: 8px;
            border-radius: 6px; border: 1px solid #ccc;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th, td {
            padding: 12px; text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {  background: #5c6bc0;
    color: white; }
        tr:hover { background-color: #f9f9fc; }
    </style>
</head>
<body>
<?php include("header.php"); ?>
<div class="container">
    <h1><i class="fas fa-user-md"></i> Medici</h1>

    <div class="toggle-links">
        <a class="btn" href="medici.php?filtru=1"><i class="fas fa-filter"></i> Caută medic</a>
        <a class="btn" href="medici.php?adauga=1"><i class="fas fa-user-plus"></i> Adaugă medic</a>
        <a class="btn btn-danger" href="medici.php"><i class="fas fa-rotate-left"></i> Resetează</a>
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
                <div><label>Telefon</label><input type="text" name="telefon"></div>
                <div><label>Email</label><input type="email" name="email"></div>
            </div>
            <button class="btn" name="adauga"><i class="fas fa-check-circle"></i> Salvează</button>
        </form>
    <?php endif; ?>

    <table>
        <thead>
        <tr>
            <th>Nume</th>
            <th>Prenume</th>
            <th>Telefon</th>
            <th>Email</th>
            <th>Acțiuni</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($medic = mysqli_fetch_assoc($medici)):
            $id = $medic['id'];
        ?>
        <tr>
            <?php if ($edit_id == $id): ?>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <td><input type="text" name="nume" value="<?= htmlspecialchars($medic['nume']) ?>" required></td>
                    <td><input type="text" name="prenume" value="<?= htmlspecialchars($medic['prenume']) ?>" required></td>
                    <td><input type="text" name="telefon" value="<?= htmlspecialchars($medic['telefon']) ?>"></td>
                    <td><input type="email" name="email" value="<?= htmlspecialchars($medic['email']) ?>"></td>
                    <td>
                        <button class="btn" name="salveaza">Salvează</button>
                        <a href="medici.php" class="btn btn-danger">Renunță</a>
                    </td>
                </form>
            <?php else: ?>
                <td><?= htmlspecialchars($medic['nume']) ?></td>
                <td><?= htmlspecialchars($medic['prenume']) ?></td>
                <td><?= htmlspecialchars($medic['telefon']) ?></td>
                <td><?= htmlspecialchars($medic['email']) ?></td>
                <td>
                    <a class="btn" href="medici.php?edit=<?= $id ?>"><i class="fas fa-edit"></i> Editează</a>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="sterge" value="<?= $id ?>">
                        <button class="btn btn-danger" onclick="return confirm('Esti sigur ca vrei sa stergi acest medic?');"><i class="fas fa-trash"></i> Șterge</button>
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
