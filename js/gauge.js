(function(){
    function drawGauge(ctx, value, options = {}) {
        const {
            width = ctx.canvas.width,
            height = ctx.canvas.height,
            maxValue = 10,
            bgColor = '#eee',
            lowColor = '#FF4E42',
            midColor = '#FFCD3C',
            highColor = '#00C853',
            lineWidth = Math.min(width, height) * 0.1,
            textColor = '#333',
        } = options;

        const radius = Math.min(width, height) * 0.8;
        const cx = width / 2;
        const cy = height;

        function getColorByValue(val) {
            if (val < 3) return lowColor;
            if (val < 7) return midColor;
            return highColor;
        }

        ctx.clearRect(0, 0, width, height);

        ctx.beginPath();
        ctx.arc(cx, cy, radius / 2, Math.PI, 0, false);
        ctx.lineWidth = lineWidth;
        ctx.strokeStyle = bgColor;
        ctx.stroke();
        ctx.closePath();

        let percent = Math.min(Math.max(value / maxValue, 0), 1);
        let endAngle = Math.PI + percent * Math.PI;
        let color = getColorByValue(value);

        ctx.beginPath();
        ctx.arc(cx, cy, radius / 2, Math.PI, endAngle, false);
        ctx.lineWidth = lineWidth;
        ctx.strokeStyle = color;
        ctx.shadowColor = color;
        ctx.shadowBlur = 6;
        ctx.stroke();
        ctx.closePath();

        // Текст
        ctx.font = 'bold 17px Inter, Arial, sans-serif';
        ctx.fillStyle = textColor;
        ctx.textAlign = 'center';
        ctx.shadowBlur = 0;
        ctx.fillText(value.toFixed(1), cx, cy - radius * 0.01);
    }


    function animateGauge(canvas, targetValue, duration = 1000, options = {}) {
        const ctx = canvas.getContext('2d');
        const width = canvas.width;
        const height = canvas.height;

        let startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            let elapsed = timestamp - startTime;
            let progress = Math.min(elapsed / duration, 1);
            let currentValue = targetValue * progress;

            drawGauge(ctx, currentValue, { ...options, width, height });

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        }

        requestAnimationFrame(step);
    }

    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('canvas.admin-gauge').forEach(canvas => {
            const val = parseFloat(canvas.dataset.value);
            if (!isNaN(val)) {
                animateGauge(canvas, val, 1200);
            }
        });
    });

    window.drawGauge = drawGauge;
    window.animateGauge = animateGauge;
})();