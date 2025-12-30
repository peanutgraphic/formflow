<?php
/**
 * Data Tab: Analytics
 *
 * @package FormFlow
 */

if (!defined('ABSPATH')) {
    exit;
}

// Use variables from parent render_data() method
$instance_id = $filters['instance_id'];
$date_from = $filters['date_from'] ?: date('Y-m-d', strtotime('-30 days'));
$date_to = $filters['date_to'] ?: date('Y-m-d');
$show_test = isset($_GET['show_test']) ? (bool)$_GET['show_test'] : false;
?>

<div class="isf-analytics" id="isf-analytics-content">
    <div class="isf-analytics-header" style="margin-bottom: 20px;">
        <div class="isf-analytics-actions">
            <button type="button" class="button" id="isf-export-pdf">
                <span class="dashicons dashicons-pdf"></span>
                <?php esc_html_e('Export PDF', 'formflow'); ?>
            </button>
            <button type="button" class="button button-secondary isf-clear-analytics" data-instance="<?php echo esc_attr($instance_id); ?>">
                <span class="dashicons dashicons-trash"></span>
                <?php esc_html_e('Clear Analytics', 'formflow'); ?>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="isf-analytics-filters">
        <form method="get" action="" id="isf-analytics-form">
            <input type="hidden" name="page" value="isf-data">
            <input type="hidden" name="tab" value="analytics">

            <div class="isf-filter-row">
                <label for="instance_id"><?php esc_html_e('Form:', 'formflow'); ?></label>
                <select name="instance_id" id="instance_id">
                    <option value=""><?php esc_html_e('All Forms', 'formflow'); ?></option>
                    <?php foreach ($instances as $inst) : ?>
                        <option value="<?php echo esc_attr($inst['id']); ?>" <?php selected($instance_id, $inst['id']); ?>>
                            <?php echo esc_html($inst['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label class="isf-checkbox-inline">
                    <input type="checkbox" name="show_test" value="1" <?php checked($show_test); ?>>
                    <?php esc_html_e('Include Test Data', 'formflow'); ?>
                </label>
            </div>

            <div class="isf-filter-row isf-date-filters">
                <div class="isf-date-presets">
                    <span class="isf-preset-label"><?php esc_html_e('Quick:', 'formflow'); ?></span>
                    <button type="button" class="button button-small isf-date-preset" data-preset="today"><?php esc_html_e('Today', 'formflow'); ?></button>
                    <button type="button" class="button button-small isf-date-preset" data-preset="yesterday"><?php esc_html_e('Yesterday', 'formflow'); ?></button>
                    <button type="button" class="button button-small isf-date-preset" data-preset="last7"><?php esc_html_e('Last 7 Days', 'formflow'); ?></button>
                    <button type="button" class="button button-small isf-date-preset" data-preset="last30"><?php esc_html_e('Last 30 Days', 'formflow'); ?></button>
                    <button type="button" class="button button-small isf-date-preset" data-preset="thismonth"><?php esc_html_e('This Month', 'formflow'); ?></button>
                    <button type="button" class="button button-small isf-date-preset" data-preset="lastmonth"><?php esc_html_e('Last Month', 'formflow'); ?></button>
                </div>

                <div class="isf-date-custom">
                    <label for="date_from"><?php esc_html_e('From:', 'formflow'); ?></label>
                    <input type="date" name="date_from" id="date_from" value="<?php echo esc_attr($date_from); ?>">

                    <label for="date_to"><?php esc_html_e('To:', 'formflow'); ?></label>
                    <input type="date" name="date_to" id="date_to" value="<?php echo esc_attr($date_to); ?>">

                    <button type="submit" class="button button-primary"><?php esc_html_e('Apply', 'formflow'); ?></button>
                </div>
            </div>
        </form>
    </div>

    <!-- Test Data Management -->
    <?php if (!empty($test_counts['submissions']) || !empty($test_counts['analytics'])) : ?>
    <div class="isf-test-data-notice notice notice-warning">
        <p>
            <strong><?php esc_html_e('Test Data:', 'formflow'); ?></strong>
            <?php
            printf(
                esc_html__('%d test submissions and %d test analytics records found.', 'formflow'),
                $test_counts['submissions'],
                $test_counts['analytics']
            );
            ?>
            <?php if (!$show_test) : ?>
                <em><?php esc_html_e('(excluded from stats below)', 'formflow'); ?></em>
            <?php endif; ?>
            <button type="button" class="button button-small isf-delete-test-data" data-instance="<?php echo esc_attr($instance_id); ?>">
                <?php esc_html_e('Delete All Test Data', 'formflow'); ?>
            </button>
        </p>
    </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="isf-section-header">
        <h2><?php esc_html_e('Overview', 'formflow'); ?></h2>
        <p class="isf-section-description"><?php esc_html_e('Key performance metrics for your enrollment forms during the selected date range.', 'formflow'); ?></p>
    </div>
    <div class="isf-analytics-summary">
        <div class="isf-stat-card">
            <span class="isf-stat-value"><?php echo esc_html(number_format($summary['total_started'] ?? 0)); ?></span>
            <span class="isf-stat-label"><?php esc_html_e('Forms Started', 'formflow'); ?></span>
            <span class="isf-stat-help"><?php esc_html_e('Total unique sessions that began the enrollment process', 'formflow'); ?></span>
        </div>
        <div class="isf-stat-card isf-stat-success">
            <span class="isf-stat-value"><?php echo esc_html(number_format($summary['total_completed'] ?? 0)); ?></span>
            <span class="isf-stat-label"><?php esc_html_e('Completed', 'formflow'); ?></span>
            <span class="isf-stat-help"><?php esc_html_e('Enrollments successfully submitted', 'formflow'); ?></span>
        </div>
        <div class="isf-stat-card isf-stat-warning">
            <span class="isf-stat-value"><?php echo esc_html(number_format($summary['total_abandoned'] ?? 0)); ?></span>
            <span class="isf-stat-label"><?php esc_html_e('Abandoned', 'formflow'); ?></span>
            <span class="isf-stat-help"><?php esc_html_e('Users who left without completing enrollment', 'formflow'); ?></span>
        </div>
        <div class="isf-stat-card">
            <span class="isf-stat-value"><?php echo esc_html($summary['completion_rate'] ?? 0); ?>%</span>
            <span class="isf-stat-label"><?php esc_html_e('Completion Rate', 'formflow'); ?></span>
            <span class="isf-stat-help"><?php esc_html_e('Percentage of started forms that were completed', 'formflow'); ?></span>
        </div>
        <div class="isf-stat-card">
            <span class="isf-stat-value"><?php echo esc_html($summary['avg_completion_time_formatted'] ?? '—'); ?></span>
            <span class="isf-stat-label"><?php esc_html_e('Avg. Time to Complete', 'formflow'); ?></span>
            <span class="isf-stat-help"><?php esc_html_e('Average duration from start to submission', 'formflow'); ?></span>
        </div>
    </div>

    <!-- Visual Charts Section -->
    <div class="isf-charts-section">
        <div class="isf-chart-container">
            <h2><?php esc_html_e('Enrollment Funnel', 'formflow'); ?></h2>
            <p class="isf-chart-description"><?php esc_html_e('Visual representation of user progression through each step.', 'formflow'); ?></p>
            <div class="isf-funnel-chart" id="isf-funnel-chart">
                <?php if (!empty($funnel)) : ?>
                    <?php
                    $max_entered = $funnel[0]['sessions_entered'] ?? 1;
                    foreach ($funnel as $index => $step) :
                        $width_pct = ($step['sessions_entered'] / max($max_entered, 1)) * 100;
                        $colors = ['#4285f4', '#34a853', '#fbbc04', '#ea4335', '#673ab7'];
                        $color = $colors[$index % count($colors)];
                    ?>
                    <div class="isf-funnel-step">
                        <div class="isf-funnel-bar-visual" style="width: <?php echo esc_attr($width_pct); ?>%; background-color: <?php echo esc_attr($color); ?>;">
                            <span class="isf-funnel-step-label">
                                <?php echo esc_html($step['step'] . '. ' . $step['step_name']); ?>
                            </span>
                            <span class="isf-funnel-step-value"><?php echo esc_html(number_format($step['sessions_entered'])); ?></span>
                        </div>
                        <?php if ($step['drop_off_rate'] > 0) : ?>
                        <div class="isf-funnel-dropoff">
                            <span class="isf-dropoff-arrow">&darr;</span>
                            <span class="isf-dropoff-text"><?php echo esc_html($step['drop_off_rate']); ?>% <?php esc_html_e('drop-off', 'formflow'); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p class="isf-no-data"><?php esc_html_e('No funnel data available.', 'formflow'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="isf-chart-container">
            <h2><?php esc_html_e('Daily Trends (Last 30 Days)', 'formflow'); ?></h2>
            <p class="isf-chart-description"><?php esc_html_e('Track enrollment activity over time.', 'formflow'); ?></p>
            <?php if (!empty($daily)) : ?>
            <canvas id="isf-daily-chart" height="250"></canvas>
            <?php else : ?>
            <p class="isf-no-data"><?php esc_html_e('No daily data available.', 'formflow'); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Funnel Analysis Table -->
    <div class="isf-analytics-section">
        <h2><?php esc_html_e('Step-by-Step Funnel', 'formflow'); ?></h2>
        <p class="description"><?php esc_html_e('Detailed breakdown of user progression.', 'formflow'); ?></p>

        <?php if (!empty($funnel)) : ?>
            <table class="widefat isf-funnel-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Step', 'formflow'); ?></th>
                        <th><?php esc_html_e('Users Entered', 'formflow'); ?></th>
                        <th><?php esc_html_e('Drop-off Rate', 'formflow'); ?></th>
                        <th><?php esc_html_e('Avg. Time', 'formflow'); ?></th>
                        <th><?php esc_html_e('Funnel', 'formflow'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $max_entered = $funnel[0]['sessions_entered'] ?? 1;
                    foreach ($funnel as $step) :
                        $funnel_width = ($step['sessions_entered'] / max($max_entered, 1)) * 100;
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($step['step']); ?>.</strong>
                                <?php echo esc_html($step['step_name']); ?>
                            </td>
                            <td><?php echo esc_html(number_format($step['sessions_entered'])); ?></td>
                            <td>
                                <?php if ($step['drop_off_rate'] > 0) : ?>
                                    <span class="isf-dropoff-badge"><?php echo esc_html($step['drop_off_rate']); ?>%</span>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($step['avg_time_formatted'] ?? '—'); ?></td>
                            <td>
                                <div class="isf-funnel-bar" style="width: <?php echo esc_attr($funnel_width); ?>%;"></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p class="isf-no-data"><?php esc_html_e('No funnel data available for the selected period.', 'formflow'); ?></p>
        <?php endif; ?>
    </div>

    <!-- Device Analytics -->
    <?php if (!empty($devices)) : ?>
    <div class="isf-analytics-section">
        <h2><?php esc_html_e('Device Selection', 'formflow'); ?></h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e('Device Type', 'formflow'); ?></th>
                    <th><?php esc_html_e('Enrollments', 'formflow'); ?></th>
                    <th><?php esc_html_e('Percentage', 'formflow'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_devices = array_sum(array_column($devices, 'count'));
                foreach ($devices as $device) :
                    $pct = $total_devices > 0 ? round(($device['count'] / $total_devices) * 100, 1) : 0;
                ?>
                    <tr>
                        <td><?php echo esc_html(ucfirst($device['device_type'])); ?></td>
                        <td><?php echo esc_html(number_format($device['count'])); ?></td>
                        <td><?php echo esc_html($pct); ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($daily)) : ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
jQuery(document).ready(function($) {
    var ctx = document.getElementById('isf-daily-chart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($daily, 'date')); ?>,
                datasets: [{
                    label: '<?php echo esc_js(__('Started', 'formflow')); ?>',
                    data: <?php echo json_encode(array_column($daily, 'started')); ?>,
                    borderColor: '#4285f4',
                    backgroundColor: 'rgba(66, 133, 244, 0.1)',
                    tension: 0.3,
                    fill: true
                }, {
                    label: '<?php echo esc_js(__('Completed', 'formflow')); ?>',
                    data: <?php echo json_encode(array_column($daily, 'completed')); ?>,
                    borderColor: '#34a853',
                    backgroundColor: 'rgba(52, 168, 83, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Date presets
    $('.isf-date-preset').on('click', function() {
        var preset = $(this).data('preset');
        var today = new Date();
        var from, to;

        switch(preset) {
            case 'today':
                from = to = today.toISOString().split('T')[0];
                break;
            case 'yesterday':
                var yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                from = to = yesterday.toISOString().split('T')[0];
                break;
            case 'last7':
                to = today.toISOString().split('T')[0];
                var last7 = new Date(today);
                last7.setDate(last7.getDate() - 7);
                from = last7.toISOString().split('T')[0];
                break;
            case 'last30':
                to = today.toISOString().split('T')[0];
                var last30 = new Date(today);
                last30.setDate(last30.getDate() - 30);
                from = last30.toISOString().split('T')[0];
                break;
            case 'thismonth':
                from = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                to = today.toISOString().split('T')[0];
                break;
            case 'lastmonth':
                from = new Date(today.getFullYear(), today.getMonth() - 1, 1).toISOString().split('T')[0];
                to = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().split('T')[0];
                break;
        }

        $('#date_from').val(from);
        $('#date_to').val(to);
        $('#isf-analytics-form').submit();
    });
});
</script>
<?php endif; ?>
