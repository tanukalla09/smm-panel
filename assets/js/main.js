// Order modal & preset amount buttons
document.addEventListener('DOMContentLoaded', function () {
    const orderModal = document.getElementById('orderModal');
    if (orderModal) {
        orderModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const rate = parseFloat(btn.dataset.rate);
            const min = parseInt(btn.dataset.min, 10);
            const max = parseInt(btn.dataset.max, 10);

            document.getElementById('orderServiceId').value = btn.dataset.id;
            document.getElementById('orderServiceName').textContent = btn.dataset.name;

            const qtyInput = document.getElementById('orderQuantity');
            qtyInput.min = min;
            qtyInput.max = max;
            qtyInput.value = min;
            document.getElementById('orderQtyHint').textContent = 'Min: ' + min.toLocaleString() + ' — Max: ' + max.toLocaleString();

            function updateCharge() {
                const qty = parseInt(qtyInput.value, 10) || 0;
                const charge = (rate / 1000) * qty;
                document.getElementById('orderCharge').textContent = '$' + charge.toFixed(2);
            }
            qtyInput.oninput = updateCharge;
            updateCharge();
        });
    }

    document.querySelectorAll('.preset-amount').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.querySelector('input[name="amount"]');
            if (input) input.value = parseFloat(btn.dataset.amount).toFixed(2);
        });
    });

    // Admin analytics charts
    const chartDataEl = document.getElementById('adminChartData');
    if (chartDataEl && typeof Chart !== 'undefined') {
        const payload = JSON.parse(chartDataEl.textContent || '{}');
        const labels = payload.labels || [];
        const chartDefaults = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1E293B',
                    titleFont: { family: 'Inter', weight: '600' },
                    bodyFont: { family: 'Inter' },
                    padding: 12,
                    cornerRadius: 8
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Inter', size: 12 }, color: '#64748B' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { font: { family: 'Inter', size: 12 }, color: '#64748B' }
                }
            }
        };

        const ordersEl = document.getElementById('ordersChart');
        if (ordersEl) {
            new Chart(ordersEl, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Orders',
                        data: payload.orders || [],
                        backgroundColor: 'rgba(79, 70, 229, 0.75)',
                        hoverBackgroundColor: '#4F46E5',
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 48
                    }]
                },
                options: {
                    ...chartDefaults,
                    plugins: {
                        ...chartDefaults.plugins,
                        tooltip: {
                            ...chartDefaults.plugins.tooltip,
                            callbacks: {
                                label: function (ctx) {
                                    return ' ' + ctx.parsed.y + ' order' + (ctx.parsed.y !== 1 ? 's' : '');
                                }
                            }
                        }
                    },
                    scales: {
                        ...chartDefaults.scales,
                        y: {
                            ...chartDefaults.scales.y,
                            ticks: {
                                ...chartDefaults.scales.y.ticks,
                                stepSize: 1,
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        const revenueEl = document.getElementById('revenueChart');
        if (revenueEl) {
            new Chart(revenueEl, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue',
                        data: payload.revenue || [],
                        borderColor: '#06B6D4',
                        backgroundColor: 'rgba(6, 182, 212, 0.1)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#06B6D4',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    ...chartDefaults,
                    plugins: {
                        ...chartDefaults.plugins,
                        tooltip: {
                            ...chartDefaults.plugins.tooltip,
                            callbacks: {
                                label: function (ctx) {
                                    return ' $' + ctx.parsed.y.toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        ...chartDefaults.scales,
                        y: {
                            ...chartDefaults.scales.y,
                            ticks: {
                                ...chartDefaults.scales.y.ticks,
                                callback: function (v) { return '$' + v; }
                            }
                        }
                    }
                }
            });
        }
    }
});
