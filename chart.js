// chart.js
async function renderChart(canvasId, activoId) {
    const ctx = document.getElementById(canvasId).getContext('2d');

    try {
        const res = await fetch(`historico.php?id=${activoId}`);
        const data = await res.json();

        if (!data || data.length === 0) {
            ctx.font = "14px Arial";
            ctx.fillStyle = "gray";
            ctx.fillText("Sin datos históricos", 10, 30);
            return;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.fecha),
                datasets: [{
                    label: "Precio",
                    data: data.map(d => d.precio),
                    borderColor: 'blue',
                    backgroundColor: 'rgba(0,0,255,0.1)',
                    fill: true,
                    tension: 0.2,   // 🔹 suaviza pero menos exagerado
                    pointRadius: 0, // 🔹 oculta puntos
                    cubicInterpolationMode: 'monotone' // 🔹 curva natural
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        type: 'time',
                        time: { unit: 'day' },
                        ticks: { maxTicksLimit: 10 }
                    },
                    y: { beginAtZero: false }
                }
            }
        });
    } catch (error) {
        console.error("Error cargando gráfico:", error);
        ctx.font = "14px Arial";
        ctx.fillStyle = "red";
        ctx.fillText("Error cargando gráfico", 10, 30);
    }
}
