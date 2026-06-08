/* assets/js/charts.js */

document.addEventListener('DOMContentLoaded', () => {
    initFinancialChart();
});

function initFinancialChart() {
    const ctx = document.getElementById('financialChart');
    if (!ctx) return;

    // Get current values from the dashboard metrics if available (to sync with actual data)
    let totalSavingsVal = 3400000; // default fallback
    let totalLoansVal = 5000000;   // default fallback

    // Parse values from dashboard card texts (remove Rp, dots, and space)
    const cards = document.querySelectorAll('.widget-card');
    cards.forEach(card => {
        const label = card.querySelector('.widget-label')?.textContent;
        const valStr = card.querySelector('.widget-value')?.textContent;
        if (label && valStr) {
            const num = parseInt(valStr.replace(/[^0-9]/g, ''), 10);
            if (!isNaN(num)) {
                if (label.includes('Simpanan')) totalSavingsVal = num;
                if (label.includes('Pinjaman')) totalLoansVal = num;
            }
        }
    });

    // Create beautiful gradients
    const ctx2d = ctx.getContext('2d');
    
    // Gradient Red (for Loans)
    const gradientRed = ctx2d.createLinearGradient(0, 0, 0, 300);
    gradientRed.addColorStop(0, 'rgba(201, 42, 42, 0.4)');
    gradientRed.addColorStop(1, 'rgba(201, 42, 42, 0.0)');

    // Gradient Gold (for Savings)
    const gradientGold = ctx2d.createLinearGradient(0, 0, 0, 300);
    gradientGold.addColorStop(0, 'rgba(229, 169, 60, 0.3)');
    gradientGold.addColorStop(1, 'rgba(229, 169, 60, 0.0)');

    // Month labels
    const labels = ['Januari', 'Februari', 'Maret', 'April', 'Mei (Skrg)'];

    // Simulated historical data trending up to the current value
    const savingsTrend = [
        Math.round(totalSavingsVal * 0.4),
        Math.round(totalSavingsVal * 0.6),
        Math.round(totalSavingsVal * 0.75),
        Math.round(totalSavingsVal * 0.9),
        totalSavingsVal
    ];

    const loansTrend = [
        Math.round(totalLoansVal * 0.3),
        Math.round(totalLoansVal * 0.5),
        Math.round(totalLoansVal * 0.8),
        Math.round(totalLoansVal * 0.95),
        totalLoansVal
    ];

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Simpanan Anggota (Rp)',
                    data: savingsTrend,
                    borderColor: '#E5A93C', // Gold
                    backgroundColor: gradientGold,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#E5A93C',
                    pointHoverRadius: 7
                },
                {
                    label: 'Total Pinjaman Beredar (Rp)',
                    data: loansTrend,
                    borderColor: '#C92A2A', // Crimson Red
                    backgroundColor: gradientRed,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#C92A2A',
                    pointHoverRadius: 7
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            family: 'Plus Jakarta Sans',
                            weight: 500
                        },
                        color: '#606770'
                    }
                },
                tooltip: {
                    padding: 12,
                    titleFont: {
                        family: 'Outfit',
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        family: 'Plus Jakarta Sans',
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label = label.split(' (')[0] + ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.04)'
                    },
                    ticks: {
                        font: {
                            family: 'Plus Jakarta Sans',
                            size: 11
                        },
                        color: '#8D949E',
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'Plus Jakarta Sans',
                            size: 11
                        },
                        color: '#8D949E'
                    }
                }
            }
        }
    });
}
