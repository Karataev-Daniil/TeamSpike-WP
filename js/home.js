// Настраиваем размеры шкалы
const RADIUS = 40; // Радиус полукруга
const MAX_VALUE = 10; // Максимальное значение для шкалы

// Функция для рисования прогресса на canvas
function drawProgress(value, canvasId) {
    var canvas = document.getElementById(canvasId);
    var ctx = canvas.getContext('2d');

    // Очищаем canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Параметры центра и радиуса
    var centerX = canvas.width / 2; // Центр по ширине
    var centerY = canvas.height; // Центр по высоте
    var radius = RADIUS; // Радиус

    // Угол для заполнения (полукруг)
    var angle = (value / MAX_VALUE) * Math.PI; // Угол заполнения в радианах

    // Цвет в зависимости от значения
    ctx.fillStyle = getColor(value, MAX_VALUE);

    // Рисуем полукруг
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, Math.PI, Math.PI + angle, false); // Полукруг от 180 до 180 + угол
    ctx.lineTo(centerX, centerY); // Снизу
    ctx.fill();
    ctx.closePath();

    // Рисуем окружность
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, 0, Math.PI * 2, false);
    ctx.strokeStyle = '#ddd'; // Цвет окружности
    ctx.lineWidth = 2;
    ctx.stroke();
    ctx.closePath();

    // Обновляем текст с рейтингом отдельно
    var ratingDisplay = document.getElementById(canvasId.replace('_canvas', '_rating'));
    if (ratingDisplay) {
        ratingDisplay.textContent = value; // Обновляем текст
    }
}

function getColor(value, maxValue) {
    // Меняем цвет в зависимости от значения
    var ratio = value / maxValue;
    var red = Math.floor((1 - ratio) * 255);
    var green = Math.floor(ratio * 255);
    return 'rgb(' + red + ', ' + green + ', 0)'; // Градиент от красного к зеленому
}
