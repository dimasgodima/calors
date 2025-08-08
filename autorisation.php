<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = "127.0.0.1";
$user = "root";
$pass = "";
$db = "calories";
$port = 3306;

$mysqli = new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors[] = "Email обязателен.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email некорректен.";
    }
    if (empty($password)) {
        $errors[] = "Пароль обязателен.";
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT user_id, name, email, password_hash FROM Users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Проверка пароля
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['password'] = $password; // Для теста, на проде лучше убрать
                header("Location: index.php");
                exit;
            } else {
                $errors[] = "Неверный пароль.";
            }
        } else {
            $errors[] = "Пользователь не найден.";
        }
        $stmt->close();
    }
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Вход</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 320px;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px 8px;
            margin: 8px 0 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            background: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            color: #721c24;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<form method="POST" action="">
    <h2>Вход</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
    <input type="password" name="password" placeholder="Пароль" required />
    <button type="submit">Войти</button>
</form>

</body>
</html>
