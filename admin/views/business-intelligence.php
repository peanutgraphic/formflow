<?php
/**
 * Business Intelligence Dashboard View
 *
 * Advanced analytics and reporting interface
 *
 * @package FormFlow
 * @since 2.0.0
 */

namespace ISF\Admin\Views;

if (!defined('ABSPATH')) {
    exit;
}

// SECURITY: Check user capabilities
if (!current_user_can('manage_options')) {
    wp_die(
        esc_html__('You do not have sufficient permissions to access this page.', 'formflow'),
        esc_html__('Permission Denied', 'formflow'),
        ['response' => 403]
    );
}

// Get BI instance
$bi = new \ISF\Platform\BusinessIntelligence();

// Get current tab
$current_tab = sanitize_text_field($_GET['tab'] ?? 'dashboard');

// Default date range
$default_start = date('Y-m-d', strtotime('-30 days'));
$default_end = date('Y-m-d');
$start_date = sanitize_text_field($_GET['start_date'] ?? $default_start);
$end_date = sanitize_text_field($_GET['end_date'] ?? $default_end);
$period = sanitize_text_field($_GET['period'] ?? '30d');
$instance_id = !empty($_GET['instance_id']) ? intval($_GET['instance_id']) : null;

// Get instances for filter
global $wpdb;
$instances = $wpdb->get_results(
    "SELECT id, name, utility FROM {$wpdb->prefix}isf_instances WHERE status = 'active' ORDER BY name",
    ARRAY_A
);

// Get data based on tab
$kpi_data = [];
$enrollment_data = [];
$funnel_data = [];
$attribution_data = [];
$temporal_data = [];
$geographic_data = [];
$program_data = [];
$saved_reports = [];

if ($current_tab === 'dashboard' || $current_tab === 'enrollments') {
    $kpi_data = $bi->get_kpi_dashboard(['period' => $period, 'instance_id' => $instance_id]);
    $enrollment_data = $bi->get_enrollment_analytics([
        'start_date' => $start_date,
        'end_date' => $end_date,
        'instance_id' => $instance_id
    ]);
}

if ($current_tab === 'funnel') {
    $funnel_data = $bi->get_conversion_funnel([
        'start_date' => $start_date,
        'end_date' => $end_date,
        'instance_id' => $instance_id
    ]);
}

if ($current_tab === 'attribution') {
    $attribution_data = $bi->get_attribution_analytics([
        'start_date' => $start_date,
        'end_date' => $end_date,
        'instance_id' => $instance_id
    ]);
}

if ($current_tab === 'temporal') {
    $temporal_data = $bi->get_temporal_analytics([
        'start_date' => $start_date,
        'end_date' => $end_date,
        'instance_id' => $instance_id
    ]);
}

if ($current_tab === 'geographic') {
    $geographic_data = $bi->get_geographic_analytics([
        'start_date' => $start_date,
        'end_date' => $end_date,
        'instance_id' => $instance_id
    ]);
}

if ($current_tab === 'programs') {
    $program_data = $bi->get_program_analytics([
        'start_date' => $start_date,
        'end_date' => $end_date
    ]);
}

if ($current_tab === 'reports') {
    $saved_reports = $bi->get_saved_reports(get_current_user_id());
}

$tabs = [
    'dashboard' => __('Dashboard', 'formflow'),
    'enrollments' => __('Enrollments', 'formflow'),
    'funnel' => __('Conversion Funnel', 'formflow'),
    'attribution' => __('Attribution', 'formflow'),
    'temporal' => __('Time Analysis', 'formflow'),
    'geographic' => __('Geographic', 'formflow'),
    'programs' => __('Programs', 'formflow'),
    'reports' => __('Saved Reports', 'formflow')
];
?>

<div class="wrap isf-business-intelligence">
    <h1>
        <?php esc_html_e('Business Intelligence', 'formflow'); ?>
        <span class="isf-beta-badge" style="background: #9b59b6; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 11px; vertical-align: middle; margin-left: 10px;">BETA</span>
    </h1>

    <!-- Date Range & Filters -->
    <div class="isf-filters-bar" style="background: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
        <form method="get" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap; flex: 1;">
            <input type="hidden" name="page" value="formflow-bi">
            <input type="hidden" name="tab" value="<?php echo esc_attr($current_tab); ?>">

            <div class="isf-filter-group">
                <label style="font-size: 12px; color: #666; display: block; margin-bottom: 3px;"><?php esc_html_e('Period', 'formflow'); ?></label>
                <select name="period" id="period-select" style="min-width: 140px;">
                    <option value="7d" <?php selected($period, '7d'); ?>><?php esc_html_e('Last 7 Days', 'formflow'); ?></option>
                    <option value="30d" <?php selected($period, '30d'); ?>><?php esc_html_e('Last 30 Days', 'formflow'); ?></option>
                    <option value="90d" <?php selected($period, '90d'); ?>><?php esc_html_e('Last 90 Days', 'formflow'); ?></option>
                    <option value="ytd" <?php selected($period, 'ytd'); ?>><?php esc_html_e('Year to Date', 'formflow'); ?></option>
                    <option value="custom" <?php selected($period, 'custom'); ?>><?php esc_html_e('Custom Range', 'formflow'); ?></option>
                </select>
            </div>

            <div class="isf-filter-group isf-custom-dates" style="display: <?php echo $period === 'custom' ? 'flex' : 'none'; ?>; gap: 10px; align-items: center;">
                <div>
                    <label style="font-size: 12px; color: #666; display: block; margin-bottom: 3px;"><?php esc_html_e('From', 'formflow'); ?></label>
                    <input type="date" name="start_date" value="<?php echo esc_attr($start_date); ?>">
                </div>
                <div>
                    <label style="font-size: 12px; color: #666; display: block; margin-bottom: 3px;"><?php esc_html_e('To', 'formflow'); ?></label>
                    <input type="date" name="end_date" value="<?php echo esc_attr($end_date); ?>">
                </div>
            </div>

            <div class="isf-filter-group">
                <label style="font-size: 12px; color: #666; display: block; margin-bottom: 3px;"><?php esc_html_e('Instance', 'formflow'); ?></label>
                <select name="instance_id" style="min-width: 180px;">
                    <option value=""><?php esc_html_e('All Instances', 'formflow'); ?></option>
                    <?php foreach ($instances as $inst): ?>
                    <option value="<?php echo intval($inst['id']); ?>" <?php selected($instance_id, $inst['id']); ?>>
                        <?php echo esc_html($inst['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="button"><?php esc_html_e('Apply', 'formflow'); ?></button>
        </form>

        <div class="isf-export-actions">
            <button type="button" class="button" id="export-btn">
                <span class="dashicons dashicons-download" style="vertical-align: middle;"></span>
                <?php esc_html_e('Export', 'formflow'); ?>
            </button>
        </div>
    </div>

    <nav class="nav-tab-wrapper">
        <?php foreach ($tabs as $tab_id => $tab_label): ?>
            <a href="<?php echo esc_url(add_query_arg(['tab' => $tab_id, 'period' => $period])); ?>"
               class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($tab_label); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="tab-content" style="margin-top: 20px;">

        <?php if ($current_tab === 'dashboard'): ?>
        <!-- Dashboard Overview -->

        <!-- KPI Cards -->
        <div class="isf-kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
            <?php if (!empty($kpi_data['kpis'])): foreach ($kpi_data['kpis'] as $key => $kpi): ?>
            <div class="isf-kpi-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 5px;">
                    <?php echo esc_html($kpi['label']); ?>
                </div>
                <div style="font-size: 32px; font-weight: bold; color: #333; line-height: 1.2;">
                    <?php
                    switch ($kpi['format']) {
                        case 'percentage':
                            echo esc_html($kpi['value']) . '%';
                            break;
                        case 'duration':
                            echo esc_html($kpi['value'] > 0 ? gmdate('i:s', $kpi['value']) : '0:00');
                            break;
                        case 'decimal':
                            echo esc_html(number_format($kpi['value'], 1));
                            break;
                        default:
                            echo esc_html(number_format($kpi['value']));
                    }
                    ?>
                </div>
                <?php if ($kpi['change']): ?>
                <div style="margin-top: 8px; font-size: 13px;">
                    <span style="color: <?php echo $kpi['change']['sentiment'] === 'positive' ? '#28a745' : ($kpi['change']['sentiment'] === 'negative' ? '#dc3545' : '#666'); ?>;">
                        <?php if ($kpi['change']['direction'] === 'up'): ?>
                            <span class="dashicons dashicons-arrow-up-alt" style="font-size: 14px; width: 14px; height: 14px;"></span>
                        <?php elseif ($kpi['change']['direction'] === 'down'): ?>
                            <span class="dashicons dashicons-arrow-down-alt" style="font-size: 14px; width: 14px; height: 14px;"></span>
                        <?php endif; ?>
                        <?php echo esc_html($kpi['change']['value']); ?>%
                    </span>
                    <span style="color: #999; margin-left: 5px;"><?php esc_html_e('vs previous', 'formflow'); ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <!-- Charts Row -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">
            <!-- Enrollment Trend Chart -->
            <div class="isf-chart-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 20px;"><?php esc_html_e('Enrollment Trend', 'formflow'); ?></h3>
                <div id="enrollment-trend-chart" style="height: 300px;">
                    <canvas id="enrollment-chart"></canvas>
                </div>
            </div>

            <!-- Status Breakdown -->
            <div class="isf-chart-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 20px;"><?php esc_html_e('Status Breakdown', 'formflow'); ?></h3>
                <div id="status-chart" style="height: 300px;">
                    <canvas id="status-pie-chart"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Stats Table -->
        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 15px;"><?php esc_html_e('Recent Activity', 'formflow'); ?></h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Period', 'formflow'); ?></th>
                        <th><?php esc_html_e('Total', 'formflow'); ?></th>
                        <th><?php esc_html_e('Completed', 'formflow'); ?></th>
                        <th><?php esc_html_e('Pending', 'formflow'); ?></th>
                        <th><?php esc_html_e('Failed', 'formflow'); ?></th>
                        <th><?php esc_html_e('Completion Rate', 'formflow'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($enrollment_data['timeline'])): foreach (array_slice(array_reverse($enrollment_data['timeline']), 0, 10) as $row): ?>
                    <tr>
                        <td><strong><?php echo esc_html($row['period']); ?></strong></td>
                        <td><?php echo intval($row['total_enrollments']); ?></td>
                        <td style="color: #28a745;"><?php echo intval($row['completed']); ?></td>
                        <td style="color: #ffc107;"><?php echo intval($row['pending']); ?></td>
                        <td style="color: #dc3545;"><?php echo intval($row['failed']); ?></td>
                        <td>
                            <?php
                            $rate = $row['total_enrollments'] > 0
                                ? round(($row['completed'] / $row['total_enrollments']) * 100, 1)
                                : 0;
                            ?>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="flex: 1; background: #eee; border-radius: 3px; height: 8px;">
                                    <div style="width: <?php echo $rate; ?>%; background: #28a745; height: 100%; border-radius: 3px;"></div>
                                </div>
                                <span style="min-width: 45px;"><?php echo $rate; ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #666;">
                            <?php esc_html_e('No enrollment data for this period.', 'formflow'); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($current_tab === 'funnel'): ?>
        <!-- Conversion Funnel -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Funnel Visualization -->
            <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 20px;"><?php esc_html_e('Conversion Funnel', 'formflow'); ?></h3>

                <?php if (!empty($funnel_data['funnel'])): ?>
                <div class="isf-funnel">
                    <?php
                    $max_sessions = $funnel_data['funnel'][0]['sessions'] ?? 1;
                    foreach ($funnel_data['funnel'] as $index => $step):
                        $width = ($step['sessions'] / $max_sessions) * 100;
                        $color = $index === 0 ? '#0073aa' : ($index === count($funnel_data['funnel']) - 1 ? '#28a745' : '#6c757d');
                    ?>
                    <div class="funnel-step" style="margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span style="font-weight: 600;">Step <?php echo intval($step['step']); ?></span>
                            <span><?php echo number_format($step['sessions']); ?> sessions</span>
                        </div>
                        <div style="background: #f0f0f0; border-radius: 4px; height: 30px; position: relative;">
                            <div style="background: <?php echo esc_attr($color); ?>; width: <?php echo $width; ?>%; height: 100%; border-radius: 4px; transition: width 0.3s;"></div>
                            <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; color: <?php echo $width > 50 ? '#fff' : '#333'; ?>;">
                                <?php echo round($step['conversion_rate'], 1); ?>%
                            </span>
                        </div>
                        <?php if ($step['dropoff_rate'] > 0): ?>
                        <div style="font-size: 11px; color: #dc3545; margin-top: 3px;">
                            <span class="dashicons dashicons-arrow-down" style="font-size: 11px; width: 11px; height: 11px;"></span>
                            <?php echo round($step['dropoff_rate'], 1); ?>% dropoff
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="text-align: center; color: #666; padding: 40px 0;">
                    <?php esc_html_e('No funnel data available.', 'formflow'); ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- Funnel Metrics -->
            <div>
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">
                    <h3 style="margin: 0 0 15px;"><?php esc_html_e('Overall Metrics', 'formflow'); ?></h3>

                    <?php if (!empty($funnel_data['overall'])): ?>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="padding: 15px; background: #f8f9fa; border-radius: 6px;">
                            <div style="font-size: 11px; color: #666; text-transform: uppercase;"><?php esc_html_e('Total Starts', 'formflow'); ?></div>
                            <div style="font-size: 24px; font-weight: bold;"><?php echo number_format($funnel_data['overall']['total_starts']); ?></div>
                        </div>
                        <div style="padding: 15px; background: #f8f9fa; border-radius: 6px;">
                            <div style="font-size: 11px; color: #666; text-transform: uppercase;"><?php esc_html_e('Total Completions', 'formflow'); ?></div>
                            <div style="font-size: 24px; font-weight: bold; color: #28a745;"><?php echo number_format($funnel_data['overall']['total_completions']); ?></div>
                        </div>
                        <div style="padding: 15px; background: #e8f5e9; border-radius: 6px; grid-column: span 2;">
                            <div style="font-size: 11px; color: #666; text-transform: uppercase;"><?php esc_html_e('Overall Conversion Rate', 'formflow'); ?></div>
                            <div style="font-size: 32px; font-weight: bold; color: #28a745;"><?php echo round($funnel_data['overall']['overall_conversion_rate'], 1); ?>%</div>
                        </div>
                    </div>

                    <?php if ($funnel_data['overall']['biggest_dropoff_step']): ?>
                    <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 6px; border-left: 4px solid #ffc107;">
                        <strong><?php esc_html_e('Optimization Opportunity:', 'formflow'); ?></strong><br>
                        <?php printf(
                            __('Step %d has the highest drop-off rate at %s%%. Consider optimizing this step.', 'formflow'),
                            $funnel_data['overall']['biggest_dropoff_step'],
                            round($funnel_data['overall']['biggest_dropoff_rate'], 1)
                        ); ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Step Details Table -->
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 15px;"><?php esc_html_e('Step Details', 'formflow'); ?></h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Step', 'formflow'); ?></th>
                                <th><?php esc_html_e('Sessions', 'formflow'); ?></th>
                                <th><?php esc_html_e('Avg Time', 'formflow'); ?></th>
                                <th><?php esc_html_e('Conversion', 'formflow'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($funnel_data['funnel'])): foreach ($funnel_data['funnel'] as $step): ?>
                            <tr>
                                <td><strong>Step <?php echo intval($step['step']); ?></strong></td>
                                <td><?php echo number_format($step['sessions']); ?></td>
                                <td><?php echo round($step['avg_time_on_step'], 1); ?>s</td>
                                <td><?php echo round($step['conversion_rate'], 1); ?>%</td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php elseif ($current_tab === 'attribution'): ?>
        <!-- Attribution Analytics -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- By Source -->
            <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 20px;"><?php esc_html_e('By Source', 'formflow'); ?></h3>
                <canvas id="source-chart" style="max-height: 250px;"></canvas>
                <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Source', 'formflow'); ?></th>
                            <th><?php esc_html_e('Enrollments', 'formflow'); ?></th>
                            <th><?php esc_html_e('%', 'formflow'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($attribution_data['by_source'])): foreach ($attribution_data['by_source'] as $source): ?>
                        <tr>
                            <td><strong><?php echo esc_html($source['source']); ?></strong></td>
                            <td><?php echo number_format($source['enrollments']); ?></td>
                            <td><?php echo round($source['percentage'], 1); ?>%</td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- By Medium -->
            <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 20px;"><?php esc_html_e('By Medium', 'formflow'); ?></h3>
                <canvas id="medium-chart" style="max-height: 250px;"></canvas>
                <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Medium', 'formflow'); ?></th>
                            <th><?php esc_html_e('Enrollments', 'formflow'); ?></th>
                            <th><?php esc_html_e('%', 'formflow'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($attribution_data['by_medium'])): foreach ($attribution_data['by_medium'] as $medium): ?>
                        <tr>
                            <td><strong><?php echo esc_html($medium['medium']); ?></strong></td>
                            <td><?php echo number_format($medium['enrollments']); ?></td>
                            <td><?php echo round($medium['percentage'], 1); ?>%</td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- By Campaign -->
            <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 20px;"><?php esc_html_e('Top Campaigns', 'formflow'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Campaign', 'formflow'); ?></th>
                            <th style="width: 100px;"><?php esc_html_e('Enrollments', 'formflow'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($attribution_data['by_campaign'])): foreach (array_slice($attribution_data['by_campaign'], 0, 10) as $campaign): ?>
                        <tr>
                            <td><strong><?php echo esc_html($campaign['campaign']); ?></strong></td>
                            <td><?php echo number_format($campaign['enrollments']); ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- By Device -->
            <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 20px;"><?php esc_html_e('By Device', 'formflow'); ?></h3>
                <canvas id="device-chart" style="max-height: 200px;"></canvas>
            </div>
        </div>

        <?php elseif ($current_tab === 'temporal'): ?>
        <!-- Temporal Analysis -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Insights -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 8px; color: #fff; grid-column: span 2;">
                <h3 style="margin: 0 0 20px; color: #fff;"><?php esc_html_e('Peak Activity Insights', 'formflow'); ?></h3>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                    <div style="text-align: center;">
                        <div style="font-size: 36px; font-weight: bold;"><?php echo esc_html($temporal_data['insights']['peak_hour'] ?? 'N/A'); ?></div>
                        <div style="opacity: 0.9; font-size: 13px;"><?php esc_html_e('Peak Hour', 'formflow'); ?></div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 36px; font-weight: bold;"><?php echo esc_html($temporal_data['insights']['peak_day'] ?? 'N/A'); ?></div>
                        <div style="opacity: 0.9; font-size: 13px;"><?php esc_html_e('Peak Day', 'formflow'); ?></div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 36px; font-weight: bold;"><?php echo number_format($temporal_data['insights']['peak_hour_enrollments'] ?? 0); ?></div>
                        <div style="opacity: 0.9; font-size: 13px;"><?php esc_html_e('Enrollments at Peak Hour', 'formflow'); ?></div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 36px; font-weight: bold;"><?php echo number_format($temporal_data['insights']['peak_day_enrollments'] ?? 0); ?></div>
                        <div style="opacity: 0.9; font-size: 13px;"><?php esc_html_e('Enrollments on Peak Day', 'formflow'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Hour of Day -->
            <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 20px;"><?php esc_html_e('By Hour of Day', 'formflow'); ?></h3>
                <canvas id="hourly-chart" style="height: 250px;"></canvas>
            </div>

            <!-- Day of Week -->
            <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 20px;"><?php esc_html_e('By Day of Week', 'formflow'); ?></h3>
                <canvas id="daily-chart" style="height: 250px;"></canvas>
            </div>

            <!-- Weekly Trend -->
            <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); grid-column: span 2;">
                <h3 style="margin: 0 0 20px;"><?php esc_html_e('Weekly Trend', 'formflow'); ?></h3>
                <canvas id="weekly-chart" style="height: 200px;"></canvas>
            </div>
        </div>

        <?php elseif ($current_tab === 'geographic'): ?>
        <!-- Geographic Analysis -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 20px;"><?php esc_html_e('Geographic Distribution', 'formflow'); ?></h3>
                <div id="geo-map" style="height: 400px; background: #f5f5f5; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #666;">
                    <?php esc_html_e('Map visualization requires additional setup', 'formflow'); ?>
                </div>
            </div>

            <div>
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 15px;"><?php esc_html_e('Top Locations', 'formflow'); ?></h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php echo esc_html(ucfirst($geographic_data['granularity'] ?? 'Location')); ?></th>
                                <th style="width: 80px;"><?php esc_html_e('Count', 'formflow'); ?></th>
                                <th style="width: 60px;">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($geographic_data['distribution'])): foreach (array_slice($geographic_data['distribution'], 0, 15) as $loc): ?>
                            <tr>
                                <td><strong><?php echo esc_html($loc['location']); ?></strong></td>
                                <td><?php echo number_format($loc['enrollments']); ?></td>
                                <td><?php echo round($loc['percentage'], 1); ?>%</td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: #666;"><?php esc_html_e('No geographic data', 'formflow'); ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php elseif ($current_tab === 'programs'): ?>
        <!-- Program Performance -->
        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">
            <h3 style="margin: 0 0 20px;"><?php esc_html_e('Program Performance', 'formflow'); ?></h3>

            <?php if (!empty($program_data['summary'])): ?>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
                <div style="padding: 15px; background: #f8f9fa; border-radius: 6px; text-align: center;">
                    <div style="font-size: 28px; font-weight: bold;"><?php echo intval($program_data['summary']['total_programs']); ?></div>
                    <div style="font-size: 12px; color: #666;"><?php esc_html_e('Programs', 'formflow'); ?></div>
                </div>
                <div style="padding: 15px; background: #f8f9fa; border-radius: 6px; text-align: center;">
                    <div style="font-size: 28px; font-weight: bold;"><?php echo number_format($program_data['summary']['total_enrollments']); ?></div>
                    <div style="font-size: 12px; color: #666;"><?php esc_html_e('Total Enrollments', 'formflow'); ?></div>
                </div>
                <div style="padding: 15px; background: #e8f5e9; border-radius: 6px; text-align: center;">
                    <div style="font-size: 14px; font-weight: bold; color: #28a745;"><?php echo esc_html($program_data['summary']['best_performing'] ?? 'N/A'); ?></div>
                    <div style="font-size: 12px; color: #666;"><?php esc_html_e('Best Performing', 'formflow'); ?></div>
                </div>
                <div style="padding: 15px; background: #fff3cd; border-radius: 6px; text-align: center;">
                    <div style="font-size: 14px; font-weight: bold; color: #856404;"><?php echo esc_html($program_data['summary']['worst_performing'] ?? 'N/A'); ?></div>
                    <div style="font-size: 12px; color: #666;"><?php esc_html_e('Needs Attention', 'formflow'); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Program', 'formflow'); ?></th>
                        <th><?php esc_html_e('Utility', 'formflow'); ?></th>
                        <th><?php esc_html_e('Total', 'formflow'); ?></th>
                        <th><?php esc_html_e('Completed', 'formflow'); ?></th>
                        <th><?php esc_html_e('Pending', 'formflow'); ?></th>
                        <th><?php esc_html_e('Completion Rate', 'formflow'); ?></th>
                        <th><?php esc_html_e('Avg Time', 'formflow'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($program_data['programs'])): foreach ($program_data['programs'] as $program): ?>
                    <tr>
                        <td><strong><?php echo esc_html($program['program']); ?></strong></td>
                        <td><?php echo esc_html($program['utility'] ?? '-'); ?></td>
                        <td><?php echo number_format($program['total_enrollments']); ?></td>
                        <td style="color: #28a745;"><?php echo number_format($program['completed']); ?></td>
                        <td style="color: #ffc107;"><?php echo number_format($program['pending']); ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex: 1; max-width: 100px; background: #eee; border-radius: 3px; height: 8px;">
                                    <div style="width: <?php echo $program['completion_rate']; ?>%; background: <?php echo $program['completion_rate'] >= 80 ? '#28a745' : ($program['completion_rate'] >= 50 ? '#ffc107' : '#dc3545'); ?>; height: 100%; border-radius: 3px;"></div>
                                </div>
                                <span><?php echo round($program['completion_rate'], 1); ?>%</span>
                            </div>
                        </td>
                        <td><?php echo esc_html($program['avg_completion_time']); ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                            <?php esc_html_e('No program data available.', 'formflow'); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($current_tab === 'reports'): ?>
        <!-- Saved Reports -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0;"><?php esc_html_e('Saved Reports', 'formflow'); ?></h2>
            <button type="button" class="button button-primary" id="create-report-btn">
                <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span>
                <?php esc_html_e('Create Report', 'formflow'); ?>
            </button>
        </div>

        <div style="background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Report Name', 'formflow'); ?></th>
                        <th><?php esc_html_e('Type', 'formflow'); ?></th>
                        <th><?php esc_html_e('Created By', 'formflow'); ?></th>
                        <th><?php esc_html_e('Last Updated', 'formflow'); ?></th>
                        <th><?php esc_html_e('Actions', 'formflow'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($saved_reports)): foreach ($saved_reports as $report): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($report['name']); ?></strong>
                            <?php if ($report['is_public']): ?>
                            <span class="isf-badge" style="background: #e3f2fd; color: #1565c0; padding: 1px 6px; border-radius: 3px; font-size: 10px; margin-left: 5px;">
                                <?php esc_html_e('Public', 'formflow'); ?>
                            </span>
                            <?php endif; ?>
                            <?php if ($report['description']): ?>
                            <br><small style="color: #666;"><?php echo esc_html($report['description']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html(ucfirst($report['type'])); ?></td>
                        <td><?php echo esc_html($report['created_by_name'] ?? 'Unknown'); ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($report['updated_at']))); ?></td>
                        <td>
                            <button type="button" class="button button-small run-report" data-report-id="<?php echo intval($report['id']); ?>">
                                <?php esc_html_e('Run', 'formflow'); ?>
                            </button>
                            <button type="button" class="button button-small schedule-report" data-report-id="<?php echo intval($report['id']); ?>">
                                <?php esc_html_e('Schedule', 'formflow'); ?>
                            </button>
                            <button type="button" class="button button-small edit-report" data-report-id="<?php echo intval($report['id']); ?>">
                                <?php esc_html_e('Edit', 'formflow'); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 60px;">
                            <span class="dashicons dashicons-chart-bar" style="font-size: 48px; width: 48px; height: 48px; color: #666;"></span>
                            <h3><?php esc_html_e('No Saved Reports', 'formflow'); ?></h3>
                            <p style="color: #666;"><?php esc_html_e('Create custom reports to track your key metrics.', 'formflow'); ?></p>
                            <button type="button" class="button button-primary" id="create-first-report-btn">
                                <?php esc_html_e('Create Your First Report', 'formflow'); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
jQuery(document).ready(function($) {
    // Period select handler
    $('#period-select').on('change', function() {
        if ($(this).val() === 'custom') {
            $('.isf-custom-dates').show();
        } else {
            $('.isf-custom-dates').hide();
        }
    });

    // Chart.js configurations
    <?php if ($current_tab === 'dashboard' && !empty($enrollment_data['timeline'])): ?>
    // Enrollment Trend Chart
    const enrollmentCtx = document.getElementById('enrollment-chart');
    if (enrollmentCtx) {
        new Chart(enrollmentCtx, {
            type: 'line',
            data: {
                labels: <?php echo wp_json_encode(array_column($enrollment_data['timeline'], 'period')); ?>,
                datasets: [{
                    label: '<?php esc_html_e('Enrollments', 'formflow'); ?>',
                    data: <?php echo wp_json_encode(array_column($enrollment_data['timeline'], 'total_enrollments')); ?>,
                    borderColor: '#0073aa',
                    backgroundColor: 'rgba(0, 115, 170, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Status Pie Chart
    const statusCtx = document.getElementById('status-pie-chart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['<?php esc_html_e('Completed', 'formflow'); ?>', '<?php esc_html_e('Pending', 'formflow'); ?>', '<?php esc_html_e('Failed', 'formflow'); ?>'],
                datasets: [{
                    data: [
                        <?php echo intval($enrollment_data['summary']['completed'] ?? 0); ?>,
                        <?php echo intval($enrollment_data['summary']['pending'] ?? 0); ?>,
                        <?php echo intval($enrollment_data['summary']['failed'] ?? 0); ?>
                    ],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    <?php endif; ?>

    <?php if ($current_tab === 'attribution' && !empty($attribution_data)): ?>
    // Source Chart
    const sourceCtx = document.getElementById('source-chart');
    if (sourceCtx) {
        new Chart(sourceCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo wp_json_encode(array_column($attribution_data['by_source'] ?? [], 'source')); ?>,
                datasets: [{
                    data: <?php echo wp_json_encode(array_column($attribution_data['by_source'] ?? [], 'enrollments')); ?>,
                    backgroundColor: ['#0073aa', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // Device Chart
    const deviceCtx = document.getElementById('device-chart');
    if (deviceCtx) {
        new Chart(deviceCtx, {
            type: 'pie',
            data: {
                labels: <?php echo wp_json_encode(array_column($attribution_data['by_device'] ?? [], 'device')); ?>,
                datasets: [{
                    data: <?php echo wp_json_encode(array_column($attribution_data['by_device'] ?? [], 'enrollments')); ?>,
                    backgroundColor: ['#667eea', '#764ba2', '#f093fb']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
    <?php endif; ?>

    <?php if ($current_tab === 'temporal' && !empty($temporal_data)): ?>
    // Hourly Chart
    const hourlyCtx = document.getElementById('hourly-chart');
    if (hourlyCtx) {
        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo wp_json_encode(array_map(fn($h) => sprintf('%02d:00', $h['hour']), $temporal_data['by_hour'] ?? [])); ?>,
                datasets: [{
                    label: '<?php esc_html_e('Enrollments', 'formflow'); ?>',
                    data: <?php echo wp_json_encode(array_column($temporal_data['by_hour'] ?? [], 'enrollments')); ?>,
                    backgroundColor: '#667eea'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    // Daily Chart
    const dailyCtx = document.getElementById('daily-chart');
    if (dailyCtx) {
        new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo wp_json_encode(array_column($temporal_data['by_day'] ?? [], 'day_name')); ?>,
                datasets: [{
                    label: '<?php esc_html_e('Enrollments', 'formflow'); ?>',
                    data: <?php echo wp_json_encode(array_column($temporal_data['by_day'] ?? [], 'enrollments')); ?>,
                    backgroundColor: '#764ba2'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    // Weekly Chart
    const weeklyCtx = document.getElementById('weekly-chart');
    if (weeklyCtx) {
        new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: <?php echo wp_json_encode(array_column($temporal_data['by_week'] ?? [], 'week_start')); ?>,
                datasets: [{
                    label: '<?php esc_html_e('Enrollments', 'formflow'); ?>',
                    data: <?php echo wp_json_encode(array_column($temporal_data['by_week'] ?? [], 'enrollments')); ?>,
                    borderColor: '#0073aa',
                    backgroundColor: 'rgba(0, 115, 170, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    <?php endif; ?>

    // Export button
    $('#export-btn').on('click', function() {
        var format = prompt('<?php esc_html_e('Export format (csv, json, pdf):', 'formflow'); ?>', 'csv');
        if (format) {
            // Would trigger AJAX export
            alert('<?php esc_html_e('Export functionality coming soon!', 'formflow'); ?>');
        }
    });
});
</script>

<style>
.isf-business-intelligence .nav-tab-wrapper {
    margin-bottom: 0;
}

.isf-business-intelligence .isf-kpi-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.isf-business-intelligence .isf-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.isf-beta-badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
</style>
