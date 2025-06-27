<?php
session_start();
include("connection.php");
include("functions.php");

$warning_message = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $user_name = $_POST['user_name'];
    $password = $_POST['password'];

    if (!empty($user_name) && !empty($password) && !is_numeric($user_name)) {
        $query = "SELECT * FROM users WHERE user_name = '$user_name' LIMIT 1";
        $result = mysqli_query($con, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);

            if ($user_data['password'] === $password) {
                $_SESSION['user_id'] = $user_data['user_id'];
                header("Location: index.php");
                die;
            }
        }
        $warning_message = "Username sau parola incorecte!";
    } else {
        $warning_message = "Introduceți date valide!";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autentificare - SPS Vet</title>
    <style>
        body {
            font-family: 'Segoe UI', Helvetica, sans-serif;
            background: linear-gradient(135deg, #dfe9f3, #ffffff);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 30px 25px;
            width: 320px;
            text-align: center;
        }

        h1 {
            font-size: 24px;
            color: #5c6bc0;
            margin-bottom: 20px;
        }

        .warning {
            color: #d9534f;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .input-group {
            display: flex;
            align-items: center;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 0 10px;
            margin: 10px 0;
            background: #fafafa;
        }

        .input-group span {
            font-size: 16px;
            color: #666;
            margin-right: 8px;
        }

        .input-group input {
            border: none;
            outline: none;
            padding: 10px 5px;
            width: 100%;
            background: transparent;
            font-size: 14px;
        }

        button {
            background-color: #5c6bc0;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        button:hover {
            background-color: #3f51b5;
        }

        .footer {
            margin-top: 20px;
            font-size: 13px;
            color: #777;
        }

        .footer a {
            color: #5c6bc0;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1> Logare</h1>
        <?php if (!empty($warning_message)): ?>
            <div class="warning"><?php echo $warning_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="#">
            <div class="input-group">
                <span>👤</span>
                <input type="text" name="user_name" placeholder="Nume utilizator" required>
            </div>
            <div class="input-group">
                <span>🔒</span>
                <input type="password" name="password" placeholder="Parolă" required>
            </div>
            <button type="submit">Logare</button>
        </form>
        <div class="footer">
            Nu ai un cont? <a href="signup.php">Înregistrează-te</a>
        </div>
    </div>
</body>
</html>
