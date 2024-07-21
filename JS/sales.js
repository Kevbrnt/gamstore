$(document).ready(function () {
    $.ajax({
        url: 'get_sales_data.php',
        method: 'GET',
        success: function (data) {
            const salesData = JSON.parse(data);

            const labels = salesData.map(item => item.date);
            const sales = salesData.map(item => item.sales);

            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Nombre de ventes',
                        data: sales,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    });
});