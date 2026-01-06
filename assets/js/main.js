// 初始化雷达图
function initRadarChart(canvas, labels, data) {
    new Chart(canvas, {
        type: 'radar',
        data: {
            labels: labels,
            datasets: [{
                label: '评分',
                data: data,
                backgroundColor: 'rgba(102, 126, 234, 0.2)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 3,
                pointBackgroundColor: 'rgba(102, 126, 234, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 10,
                    ticks: {
                        stepSize: 2,
                        font: {
                            size: 12
                        },
                        color: '#999'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    angleLines: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    pointLabels: {
                        font: {
                            size: 14,
                            weight: '500'
                        },
                        color: '#333'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// 初始化卡片中的小雷达图
function initCardRadarCharts() {
    const canvases = document.querySelectorAll('.restaurant-card .radar-chart');
    canvases.forEach(canvas => {
        if (canvas.dataset.scores) {
            const scores = JSON.parse(canvas.dataset.scores);
            initRadarChart(canvas, ['口味', '价格', '包装', '速度'], scores);
        }
    });
}

// 初始化详情页的大雷达图
function initDetailRadarChart() {
    const canvas = document.querySelector('.radar-container .radar-chart');
    if (canvas && canvas.dataset.scores) {
        const scores = JSON.parse(canvas.dataset.scores);
        initRadarChart(canvas, ['口味', '价格', '包装', '速度'], scores);
    }
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    initCardRadarCharts();
    initDetailRadarChart();
});
