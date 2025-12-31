/**
 * Form Analytics Charts
 *
 * Provides Chart.js integration for form analytics visualization.
 *
 * @package FormFlow
 * @since 2.8.0
 */

(function($) {
    'use strict';

    const ISFAnalyticsCharts = {
        charts: {},

        /**
         * Initialize analytics charts
         */
        init: function() {
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                return;
            }

            // Set global Chart.js defaults
            this.setChartDefaults();

            // Initialize all charts
            this.initSubmissionTrendChart();
            this.initFieldCompletionChart();
            this.initConversionFunnelChart();
            this.initDeviceBreakdownChart();
            this.initTopSourcesChart();

            // Bind export buttons
            this.bindExportButtons();

            // Refresh charts on window resize
            $(window).on('resize', this.debounce(() => {
                Object.values(this.charts).forEach(chart => {
                    if (chart && chart.resize) {
                        chart.resize();
                    }
                });
            }, 250));
        },

        /**
         * Set global Chart.js defaults
         */
        setChartDefaults: function() {
            Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';
            Chart.defaults.font.size = 13;
            Chart.defaults.color = '#4a5568';
            Chart.defaults.plugins.legend.display = true;
            Chart.defaults.plugins.legend.position = 'bottom';
            Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(45, 55, 72, 0.95)';
            Chart.defaults.plugins.tooltip.padding = 12;
            Chart.defaults.plugins.tooltip.cornerRadius = 6;
            Chart.defaults.plugins.tooltip.titleFont = { size: 14, weight: '600' };
            Chart.defaults.plugins.tooltip.bodyFont = { size: 13 };
        },

        /**
         * Initialize submission trend line chart
         */
        initSubmissionTrendChart: function() {
            const ctx = document.getElementById('isf-submission-trend-chart');
            if (!ctx) return;

            // Sample data - replace with actual data from backend
            const data = this.getSubmissionTrendData();

            this.charts.submissionTrend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Submissions',
                        data: data.submissions,
                        borderColor: '#2271b1',
                        backgroundColor: 'rgba(34, 113, 177, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#2271b1',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }, {
                        label: 'Completions',
                        data: data.completions,
                        borderColor: '#48bb78',
                        backgroundColor: 'rgba(72, 187, 120, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#48bb78',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            },
                            grid: {
                                color: '#e2e8f0'
                            }
                        }
                    }
                }
            });
        },

        /**
         * Initialize field completion bar chart
         */
        initFieldCompletionChart: function() {
            const ctx = document.getElementById('isf-field-completion-chart');
            if (!ctx) return;

            const data = this.getFieldCompletionData();

            this.charts.fieldCompletion = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Completion Rate (%)',
                        data: data.completionRates,
                        backgroundColor: data.completionRates.map(rate => {
                            if (rate >= 80) return '#48bb78';
                            if (rate >= 60) return '#ecc94b';
                            return '#fc8181';
                        }),
                        borderWidth: 0,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Completion: ' + context.parsed.x + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            },
                            grid: {
                                color: '#e2e8f0'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        },

        /**
         * Initialize conversion funnel chart
         */
        initConversionFunnelChart: function() {
            const ctx = document.getElementById('isf-conversion-funnel-chart');
            if (!ctx) return;

            const data = this.getConversionFunnelData();

            this.charts.conversionFunnel = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Users',
                        data: data.values,
                        backgroundColor: [
                            '#2271b1',
                            '#4299e1',
                            '#63b3ed',
                            '#90cdf4',
                            '#48bb78'
                        ],
                        borderWidth: 0,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = data.values[0];
                                    const percentage = ((context.parsed.y / total) * 100).toFixed(1);
                                    return context.parsed.y.toLocaleString() + ' users (' + percentage + '%)';
                                },
                                afterLabel: function(context) {
                                    if (context.dataIndex > 0) {
                                        const previous = data.values[context.dataIndex - 1];
                                        const current = context.parsed.y;
                                        const dropoff = ((previous - current) / previous * 100).toFixed(1);
                                        return 'Drop-off: ' + dropoff + '%';
                                    }
                                    return '';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            },
                            grid: {
                                color: '#e2e8f0'
                            }
                        }
                    }
                }
            });
        },

        /**
         * Initialize device breakdown doughnut chart
         */
        initDeviceBreakdownChart: function() {
            const ctx = document.getElementById('isf-device-breakdown-chart');
            if (!ctx) return;

            const data = this.getDeviceBreakdownData();

            this.charts.deviceBreakdown = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: [
                            '#2271b1',
                            '#48bb78',
                            '#ecc94b',
                            '#fc8181',
                            '#9f7aea'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        return data.labels.map((label, i) => {
                                            const value = data.datasets[0].data[i];
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return {
                                                text: label + ' (' + percentage + '%)',
                                                fillStyle: data.datasets[0].backgroundColor[i],
                                                hidden: false,
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        },

        /**
         * Initialize top sources chart
         */
        initTopSourcesChart: function() {
            const ctx = document.getElementById('isf-top-sources-chart');
            if (!ctx) return;

            const data = this.getTopSourcesData();

            this.charts.topSources = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Submissions',
                        data: data.values,
                        backgroundColor: '#2271b1',
                        borderWidth: 0,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Submissions: ' + context.parsed.x.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: '#e2e8f0'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        },

        /**
         * Bind export chart buttons
         */
        bindExportButtons: function() {
            $('.isf-export-chart').on('click', function(e) {
                e.preventDefault();
                const chartId = $(this).data('chart');
                const chart = ISFAnalyticsCharts.charts[chartId];

                if (chart) {
                    ISFAnalyticsCharts.exportChartAsImage(chart, chartId);
                }
            });
        },

        /**
         * Export chart as image
         */
        exportChartAsImage: function(chart, name) {
            const url = chart.toBase64Image();
            const link = document.createElement('a');
            link.download = 'formflow-' + name + '-' + Date.now() + '.png';
            link.href = url;
            link.click();
        },

        /**
         * Get submission trend data (sample - replace with real data)
         */
        getSubmissionTrendData: function() {
            // This would normally come from the server
            return {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                submissions: [120, 145, 167, 189, 201, 224, 242, 268, 291, 315, 342, 378],
                completions: [98, 121, 142, 159, 175, 198, 215, 237, 261, 285, 308, 341]
            };
        },

        /**
         * Get field completion data (sample - replace with real data)
         */
        getFieldCompletionData: function() {
            return {
                labels: ['Email', 'Phone', 'Address', 'Account Number', 'Program Selection', 'Signature'],
                completionRates: [98, 95, 87, 72, 85, 91]
            };
        },

        /**
         * Get conversion funnel data (sample - replace with real data)
         */
        getConversionFunnelData: function() {
            return {
                labels: ['Form Viewed', 'Started', 'Step 2 Reached', 'Step 3 Reached', 'Completed'],
                values: [1000, 750, 600, 450, 380]
            };
        },

        /**
         * Get device breakdown data (sample - replace with real data)
         */
        getDeviceBreakdownData: function() {
            return {
                labels: ['Desktop', 'Mobile', 'Tablet', 'Other'],
                values: [450, 320, 125, 45]
            };
        },

        /**
         * Get top sources data (sample - replace with real data)
         */
        getTopSourcesData: function() {
            return {
                labels: ['Direct', 'Google Ads', 'Email Campaign', 'Social Media', 'Referral'],
                values: [320, 245, 198, 142, 87]
            };
        },

        /**
         * Debounce function
         */
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        // Load Chart.js if not already loaded
        if (typeof Chart === 'undefined' && typeof ISFAnalyticsConfig !== 'undefined' && ISFAnalyticsConfig.chartjs_url) {
            $.getScript(ISFAnalyticsConfig.chartjs_url, function() {
                ISFAnalyticsCharts.init();
            });
        } else if (typeof Chart !== 'undefined') {
            ISFAnalyticsCharts.init();
        }
    });

    // Expose for external use
    window.ISFAnalyticsCharts = ISFAnalyticsCharts;

})(jQuery);
