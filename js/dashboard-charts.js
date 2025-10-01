document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de producción por región
    const regionCtx = document.getElementById('regionProductionChart').getContext('2d');
    new Chart(regionCtx, {
        type: 'bar',
        data: {
            labels: chartData.regions,
            datasets: [{
                label: 'Producción (bpd)',
                data: chartData.regionProduction,
                backgroundColor: [
                    '#3498db', '#2ecc71', '#e67e22', '#9b59b6', '#1abc9c'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Barriles por día (bpd)' }
                }
            }
        }
    });

    // Gráfico de empresas mixtas
    const mixedCtx = document.getElementById('mixedProductionChart').getContext('2d');
    new Chart(mixedCtx, {
        type: 'doughnut',
        data: {
            labels: chartData.regions,
            datasets: [{
                label: 'Producción (bpd)',
                data: chartData.mixedProduction,
                backgroundColor: [
                    '#D71920', '#2c3e50', '#3498db', '#e67e22', '#2ecc71', '#9b59b6'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
});