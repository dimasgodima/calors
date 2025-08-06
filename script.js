let totalCalories = 0;
let goal = 1800;
let qrScanner = null;
let products = [];
const today = new Date().toISOString().split('T')[0];

// Инициализация
window.onload = () => {
  loadGoal();
  loadProducts();
  checkNewDay();
  loadHistory();
  updateDisplay();
}

// ----------------------
// Цель на день
// ----------------------
document.getElementById('goal-input').addEventListener('change', (e) => {
  goal = parseInt(e.target.value) || 0;
  localStorage.setItem('goal', goal);
  updateDisplay();
});

function loadGoal() {
  const storedGoal = localStorage.getItem('goal');
  if (storedGoal) {
    goal = parseInt(storedGoal);
    document.getElementById('goal-input').value = goal;
  } else {
    document.getElementById('goal-input').value = goal;
  }
}

// ----------------------
// Продукты
// ----------------------
function addProduct(event) {
  event.preventDefault();

  const name = document.getElementById('name').value;
  const grams = parseFloat(document.getElementById('grams').value);
  const kcalPer100g = parseFloat(document.getElementById('kcalPer100g').value);

  if (isNaN(grams) || isNaN(kcalPer100g)) {
    alert("Введите граммы и калорийность.");
    return;
  }

  const calories = Math.round((grams / 100) * kcalPer100g);
  const product = { name, grams, kcalPer100g, calories, barcode: document.getElementById('barcode').value };
  products.push(product);
  totalCalories += calories;

  saveProducts();
  renderProducts();
  updateDisplay();
  clearForm();
}

function renderProducts() {
  const tbody = document.getElementById('product-list');
  tbody.innerHTML = '';

  products.forEach((p, index) => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${p.name}</td>
      <td>${p.grams}</td>
      <td>${p.calories}</td>
      <td><button class="delete-btn">Удалить</button></td>
    `;
    row.querySelector('.delete-btn').addEventListener('click', () => {
      totalCalories -= p.calories;
      products.splice(index, 1);
      saveProducts();
      renderProducts();
      updateDisplay();
    });
    tbody.appendChild(row);
  });
}

function clearForm() {
  document.getElementById('name').value = '';
  document.getElementById('grams').value = '';
  document.getElementById('kcalPer100g').value = '';
  document.getElementById('barcode').value = '';
}

function saveProducts() {
  localStorage.setItem('products', JSON.stringify(products));
  localStorage.setItem('totalCalories', totalCalories);
  localStorage.setItem('today', today);
}

function loadProducts() {
  const storedProducts = JSON.parse(localStorage.getItem('products') || '[]');
  products = storedProducts;
  totalCalories = parseInt(localStorage.getItem('totalCalories') || '0');
  renderProducts();
}

// ----------------------
// Обновление дисплея
// ----------------------
function updateDisplay() {
  document.getElementById('consumed').textContent = totalCalories;
  document.getElementById('remaining').textContent = goal - totalCalories;
}

// ----------------------
// Сканер штрих-кода
// ----------------------
function startScanner() {
  const scannerDiv = document.getElementById("scanner");

  if (qrScanner) {
    qrScanner.stop().then(() => {
      scannerDiv.innerHTML = '';
      qrScanner = null;
    });
    return;
  }

  qrScanner = new Html5Qrcode("scanner");
  qrScanner.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: { width: 250, height: 100 } },
    async (decodedText) => {
      document.getElementById("barcode").value = decodedText;
      await fetchProductData(decodedText);
      qrScanner.stop().then(() => {
        document.getElementById("scanner").innerHTML = "";
        qrScanner = null;
      });
    },
    (errorMessage) => { console.warn("Сканирование неудачно: ", errorMessage); }
  ).catch((err) => { console.error("Ошибка при запуске сканера:", err); alert("Не удалось запустить сканер."); });
}

async function fetchProductData(barcode) {
  try {
    const response = await fetch(`https://world.openfoodfacts.org/api/v0/product/${barcode}.json`);
    const data = await response.json();

    if (data.status === 1) {
      const product = data.product;
      const name = product.product_name || "Неизвестный продукт";
      const kcalPer100g = product.nutriments["energy-kcal_100g"] || 0;

      document.getElementById("name").value = name;
      document.getElementById("kcalPer100g").value = kcalPer100g;
    } else {
      alert("Продукт не найден.");
    }
  } catch (error) {
    console.error("Ошибка при получении данных о продукте:", error);
    alert("Ошибка подключения к базе продуктов.");
  }
}

// ----------------------
// Новый день
// ----------------------
function checkNewDay() {
  const lastDate = localStorage.getItem('today');
  if (lastDate && lastDate !== today) {
    saveHistory(lastDate, products, totalCalories);
    products = [];
    totalCalories = 0;
    saveProducts();
    renderProducts();
    loadHistory();
    updateDisplay();
  }
}

function resetDay() {
  saveHistory(today, products, totalCalories);
  products = [];
  totalCalories = 0;
  saveProducts();
  renderProducts();
  updateDisplay();
  loadHistory();
}

// ----------------------
// История
// ----------------------
function saveHistory(date, productsList, calories) {
  if (!productsList.length) return;
  const history = JSON.parse(localStorage.getItem('history') || '[]');
  const dayEntry = { date, totalCalories: calories, goal, products: productsList };
  history.unshift(dayEntry);
  localStorage.setItem('history', JSON.stringify(history));
}

function loadHistory() {
  const history = JSON.parse(localStorage.getItem('history') || '[]');
  const historyDiv = document.getElementById('history');
  historyDiv.innerHTML = '';
  history.forEach(h => {
    const div = document.createElement('div');
    div.classList.add('history-card');
    div.innerHTML = `<strong>${h.date}</strong> — Съедено: ${h.totalCalories} / ${h.goal} ккал`;
    historyDiv.appendChild(div);
  });
}
