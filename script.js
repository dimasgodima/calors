let totalCalories = 0;
const goal = 1800;

function updateDisplay() {
  document.getElementById('consumed').textContent = totalCalories;
  document.getElementById('remaining').textContent = goal - totalCalories;
}

function addProduct(event) {
  event.preventDefault();
  const name = document.getElementById('name').value;
  const grams = parseFloat(document.getElementById('grams').value);
  const kcalPer100g = parseFloat(document.getElementById('kcalPer100g').value);

  const calories = Math.round((grams / 100) * kcalPer100g);
  totalCalories += calories;
  updateDisplay();

  const row = document.createElement('tr');
  row.innerHTML = `<td>${name}</td><td>${grams}</td><td>${calories}</td>`;
  document.getElementById('product-list').appendChild(row);

  document.getElementById('name').value = '';
  document.getElementById('grams').value = '';
  document.getElementById('kcalPer100g').value = '';
}

function resetDay() {
  totalCalories = 0;
  updateDisplay();
  document.getElementById('product-list').innerHTML = '';
  document.getElementById('barcode').value = '';
}


function startScanner() {
  const scannerEl = document.getElementById('scanner');
  Quagga.init({
    inputStream: {
      type: 'LiveStream',
      target: scannerEl,
      constraints: {
        facingMode: 'environment' // задняя камера
      }
    },
    decoder: {
      readers: ['ean_reader'] // EAN-13 (стандартные штрих-коды)
    }
  }, err => {
    if (err) {
      console.error(err);
      return;
    }
    Quagga.start();
  });

  Quagga.onDetected(data => {
    const code = data.codeResult.code;
    document.getElementById('barcode').value = code;
    Quagga.stop();
    scannerEl.innerHTML = '';
  });
}