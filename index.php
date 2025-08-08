<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Калории — трекер</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel='stylesheet' href='style.css'> 
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body>
<header>
  <img src="https://i.imgur.com/g64f8to.png" alt="Логотип"> <!-- Логотип сайта -->
  <div id="menu"> <!-- Меню -->
    <ul>
      <li><a href="/">Трекер </a></li>
      <li><a href="/about">О проекте</a></li>
      <li><a href="/about">Настройки</a></li>
      <li><a href="/contacts">Регистрация</a></li>
    </ul>
  </div>
</header>

<style>

header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 40px;
  background: #ffffff; /* Цвет фона шапки */
  box-shadow: 0 2px 10px rgba(0,0,0,0.05); /* Лёгкая тень */
}

header img {
  width: 40px; /* Сделал крупнее для читаемости */
  height: auto;
}

#menu ul {
  display: flex;
  list-style: none;
  margin: 0;
  padding: 0;
  gap: 25px; /* Расстояние между пунктами */
}

#menu li a {
  text-decoration: none;
  font-size: 16px;
  font-weight: 500;
  color: #333;
  transition: color 0.3s ease, border-bottom 0.3s ease;
  padding-bottom: 3px;
}

#menu li a:hover {
  color: #007BFF; /* Синий при наведении */
  border-bottom: 2px solid #007BFF; /* Подчёркивание при наведении */
}

</style>







  <h1>Добро пожаловать, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>
  <h1>Твой трекер Калорий</h1>




  <label>
    Цель на день:
    <input type="number" id="goal-input" placeholder="Введите норму ккал">
  </label>
  <p>Съедено: <strong id="consumed">0</strong> ккал</p>
  <p>Осталось: <strong id="remaining">0</strong> ккал</p>

  <button onclick="startScanner()">Сканировать штрих-код</button>
  <div id="scanner" style="width: 100%; max-width: 400px; margin: 10px auto;"></div>

  <form onsubmit="addProduct(event)">
    <input type="text" id="barcode" placeholder="Штрих-код" readonly>
    <input type="text" id="name" placeholder="Название продукта" required>
    <input type="number" id="grams" placeholder="Граммы" required>
    <input type="number" id="kcalPer100g" placeholder="Ккал на 100г" required>
    <button type="submit">Добавить</button>
  </form>

  <table>
    <thead>
      <tr><th>Продукт</th><th>Граммы</th><th>Ккал</th><th>Действие</th></tr>
    </thead>    
    <tbody id="product-list"></tbody>
  </table>

  <button onclick="resetDay()">Очистить день</button>

  <h2>История</h2>
  <div id="history"></div>

  <script src="script.js"></script>
</body>
</html>
