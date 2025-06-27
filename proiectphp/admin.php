<?php
session_start();
include("connection.php");
include("header.php");

$user_data = check_login($con);

// Adăugare utilizator
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $stmt = $con->prepare("INSERT INTO users (user_id, user_name, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['user_id'], $_POST['user_name'], $_POST['password']);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Editare utilizator
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    $stmt = $con->prepare("UPDATE users SET user_id = ?, user_name = ?, password = ? WHERE id = ?");
    $stmt->bind_param("sssi", $_POST['user_id'], $_POST['user_name'], $_POST['password'], $_POST['id']);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Ștergere utilizator
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    mysqli_query($con, "DELETE FROM users WHERE id = $id");
    header("Location: admin.php");
    exit();
}

// Selectare utilizatori
$rezultat = mysqli_query($con, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Administrare utilizatori</title>
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
            margin-bottom: 20px;
        }
        .form-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .form-row input {
            flex: 1;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
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
        .btn.cancel {
            background: #999;
        }
        .btn.red {
            background: #e53935;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
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
<div class="container">
    <h2><i class="fas fa-user-shield"></i> Administrare utilizatori</h2>

    <form method="POST">
        <div class="form-row">
            <input type="text" name="user_id" placeholder="User ID" required>
            <input type="text" name="user_name" placeholder="Username" required>
            <input type="password" name="password" placeholder="Parolă" required>
        </div>
        <button type="submit" name="add_user" class="btn"><i class="fas fa-user-plus"></i> Adaugă utilizator</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Username</th>
                <th>Parolă</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($r = mysqli_fetch_assoc($rezultat)): ?>
            <?php if (isset($_GET['edit']) && $_GET['edit'] == $r['id']): ?>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><input name="user_id" value="<?= htmlspecialchars($r['user_id']) ?>"></td>
                        <td><input name="user_name" value="<?= htmlspecialchars($r['user_name']) ?>"></td>
                        <td><input name="password" value="<?= htmlspecialchars($r['password']) ?>"></td>
                        <td>
                            <button type="submit" name="save_user" class="btn"><i class="fas fa-save"></i> Salvează</button>
                            <a href="admin.php" class="btn cancel"><i class="fas fa-times"></i> Renunță</a>
                        </td>
                    </tr>
                </form>
            <?php else: ?>
                <tr>
                    <td><?= $r['id'] ?></td>
                    <td><?= htmlspecialchars($r['user_id']) ?></td>
                    <td><?= htmlspecialchars($r['user_name']) ?></td>
                    <td><?= htmlspecialchars($r['password']) ?></td>
                    <td>
                        <a href="admin.php?edit=<?= $r['id'] ?>" class="btn"><i class="fas fa-edit"></i> Editare</a>
                        <a href="admin.php?delete_id=<?= $r['id'] ?>" class="btn red" onclick="return confirm('Ștergi utilizatorul?')"><i class="fas fa-trash"></i> Șterge</a>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
