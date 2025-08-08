<?php
// registration.php
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
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Чистим и проверяем данные
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Валидация
    if (empty($name)) {
        $errors[] = "Имя обязательно.";
    }
    if (empty($email)) {
        $errors[] = "Email обязателен.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email некорректен.";
    }
    if (empty($password)) {
        $errors[] = "Пароль обязателен.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Пароль должен быть минимум 6 символов.";
    }

    if (empty($errors)) {
        // Проверяем, нет ли уже такого email в базе
        $stmt = $mysqli->prepare("SELECT user_id FROM Users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Пользователь с таким email уже зарегистрирован.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        // Хешируем пароль
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Вставляем в базу
        $stmt = $mysqli->prepare("INSERT INTO Users (name, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $passwordHash);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Ошибка при регистрации: " . $stmt->error;
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
    <title>Регистрация</title>
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
        input[type="text"], input[type="email"], input[type="password"] {
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
        .success {
            background: #d4edda;
            padding: 10px;
            border-radius: 5px;
            color: #155724;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<form method="POST" action="">
    <h2>Регистрация</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success">Регистрация прошла успешно! Теперь вы можете войти.</div>
    <?php endif; ?>

    <input type="text" name="name" placeholder="Имя" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required />
    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
    <input type="password" name="password" placeholder="Пароль (мин. 6 символов)" required />

    <button type="submit">Зарегистрироваться</button>

    <a href="http://localhost/www/autorisation.php">Уже есть аккаунт?</a>
</form>

</body>
</html>
