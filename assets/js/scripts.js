// Check if there’s a canvas with id 'clickChart' for analytics
document.addEventListener('DOMContentLoaded', () => {
    const chartElement = document.getElementById('clickChart');
    if (chartElement) {
        fetch('../dashboard/chart-data.php')
            .then(response => response.json())
            .then(data => {
                const ctx = chartElement.getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Clicks per Link',
                            data: data.clicks,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            });
    }
});
