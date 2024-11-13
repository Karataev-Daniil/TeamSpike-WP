// Set the scale size
const RADIUS = 40; // Radius of the semicircle
const MAX_VALUE = 10; // Maximum value for the scale

// Function to draw the progress on canvas
function drawProgress(input, canvasId) {
    var canvas = document.getElementById(canvasId);
    var ctx = canvas.getContext('2d');
    var value = parseInt(input.value);
    var maxValue = parseInt(input.max);

    // Clear the canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Parameters for center and radius
    var centerX = canvas.width / 2; // Center by width
    var centerY = canvas.height; // Center by height
    var radius = RADIUS; // Radius

    // Angle for filling (semi-circle or half-circle)
    var angle = (value / maxValue) * Math.PI;

    // Color depending on the value
    ctx.fillStyle = getColor(value, maxValue);

    // Draw the semi-circle
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, Math.PI, Math.PI + angle, false); // From 180 degrees (PI) to 180 + angle
    ctx.lineTo(centerX, centerY); // Bottom
    ctx.fill();
    ctx.closePath();

    // Draw the outer circle
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, 0, Math.PI * 2, false); // Full circle
    ctx.strokeStyle = '#ddd'; // Circle color
    ctx.lineWidth = 2;
    ctx.stroke();
    ctx.closePath();

    // Update the rating text separately
    var ratingDisplay = document.getElementById(canvasId.replace('_canvas', '_rating'));
    if (ratingDisplay) {
        ratingDisplay.textContent = value; // Update text
    }
}

function getColor(value, maxValue) {
    // Change color based on the value
    var ratio = value / maxValue;
    var red = Math.floor((1 - ratio) * 255);
    var green = Math.floor(ratio * 255);
    return 'rgb(' + red + ', ' + green + ', 0)'; // Gradient from red to green
}

function updateValue(button, fieldId) {
    const input = button.parentElement.querySelector('input[type="number"]');
    let value = parseInt(input.value);
    
    if (button.classList.contains('plus')) {
        value++;
    } else if (button.classList.contains('minus')) {
        value--;
    }

    // Limit value from 1 to 10
    if (value < 1) value = 1;
    if (value > 10) value = 10;

    input.value = value;

    // Update the Canvas based on the new value
    drawProgress(input, fieldId + '_canvas');
}


