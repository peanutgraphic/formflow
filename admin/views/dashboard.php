<?php
/**
 * Admin Dashboard View
 *
 * Displays overview of form instances and statistics.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap isf-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('FormFlow', 'formflow'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=isf-instance-editor')); ?>" class="page-title-action">
        <?php esc_html_e('Add New Form', 'formflow'); ?>
    </a>
    <hr class="wp-header-end">

    <!-- Quick Actions Bar -->
    <div class="isf-quick-actions-bar">
        <div class="isf-quick-actions-left">
            <a href="<?php echo esc_url(admin_url('admin.php?page=isf-instance-editor')); ?>" class="button button-primary">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php esc_html_e('New Form', 'formflow'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=isf-data&tab=submissions')); ?>" class="button">
                <span class="dashicons dashicons-list-view"></span>
                <?php esc_html_e('View Data', 'formflow'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=isf-data&tab=analytics')); ?>" class="button">
                <span class="dashicons dashicons-chart-bar"></span>
                <?php esc_html_e('Analytics', 'formflow'); ?>
            </a>
            <?php if (!empty($instances)) : ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=isf-test')); ?>" class="button">
                <span class="dashicons dashicons-visibility"></span>
                <?php esc_html_e('Test Forms', 'formflow'); ?>
            </a>
            <?php endif; ?>
        </div>
        <div class="isf-quick-actions-right">
            <a href="<?php echo esc_url(admin_url('admin.php?page=isf-tools&tab=diagnostics')); ?>" class="button button-link isf-quick-link">
                <span class="dashicons dashicons-heart"></span>
                <?php esc_html_e('Diagnostics', 'formflow'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=isf-tools&tab=settings')); ?>" class="button button-link isf-quick-link">
                <span class="dashicons dashicons-admin-generic"></span>
                <?php esc_html_e('Settings', 'formflow'); ?>
            </a>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="isf-dashboard-grid">
        <!-- All Time Stats -->
        <div class="isf-stats-section">
            <h3 class="isf-stats-heading"><?php esc_html_e('All Time', 'formflow'); ?></h3>
            <div class="isf-stats-cards">
                <div class="isf-stat-card">
                    <div class="isf-stat-number"><?php echo esc_html($stats['total']); ?></div>
                    <div class="isf-stat-label"><?php esc_html_e('Total Submissions', 'formflow'); ?></div>
                </div>
                <div class="isf-stat-card isf-stat-success">
                    <div class="isf-stat-number"><?php echo esc_html($stats['completed']); ?></div>
                    <div class="isf-stat-label"><?php esc_html_e('Completed', 'formflow'); ?></div>
                </div>
                <div class="isf-stat-card isf-stat-warning">
                    <div class="isf-stat-number"><?php echo esc_html($stats['in_progress']); ?></div>
                    <div class="isf-stat-label"><?php esc_html_e('In Progress', 'formflow'); ?></div>
                </div>
                <div class="isf-stat-card isf-stat-info">
                    <div class="isf-stat-number"><?php echo esc_html($stats['completion_rate']); ?>%</div>
                    <div class="isf-stat-label"><?php esc_html_e('Completion Rate', 'formflow'); ?></div>
                </div>
            </div>
        </div>

        <!-- Today's Stats -->
        <div class="isf-stats-section isf-stats-today">
            <h3 class="isf-stats-heading"><?php esc_html_e('Today', 'formflow'); ?></h3>
            <div class="isf-stats-cards isf-stats-cards-small">
                <div class="isf-stat-card">
                    <div class="isf-stat-number"><?php echo esc_html($today_stats['total']); ?></div>
                    <div class="isf-stat-label"><?php esc_html_e('Submissions', 'formflow'); ?></div>
                </div>
                <div class="isf-stat-card isf-stat-success">
                    <div class="isf-stat-number"><?php echo esc_html($today_stats['completed']); ?></div>
                    <div class="isf-stat-label"><?php esc_html_e('Completed', 'formflow'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Health Status -->
    <?php if (!empty($instances)) : ?>
    <div class="isf-card isf-api-health-card">
        <div class="isf-card-header">
            <h2><?php esc_html_e('API Status', 'formflow'); ?></h2>
            <button type="button" id="isf-refresh-health" class="button button-small">
                <span class="dashicons dashicons-update"></span>
                <?php esc_html_e('Check Now', 'formflow'); ?>
            </button>
        </div>

        <div id="isf-api-health-status" class="isf-api-health-grid">
            <?php if ($api_health) : ?>
                <?php foreach ($instances as $instance) :
                    $health = $api_health[$instance['id']] ?? null;
                    $status_class = 'unknown';
                    $status_label = __('Unknown', 'formflow');
                    $latency = '';

                    if ($health) {
                        $status_class = $health['status'];
                        switch ($health['status']) {
                            case 'healthy':
                                $status_label = __('Healthy', 'formflow');
                                break;
                            case 'degraded':
                                $status_label = __('Degraded', 'formflow');
                                break;
                            case 'slow':
                                $status_label = __('Slow', 'formflow');
                                break;
                            case 'error':
                                $status_label = __('Error', 'formflow');
                                break;
                            case 'demo':
                                $status_label = __('Demo Mode', 'formflow');
                                break;
                            case 'unconfigured':
                                $status_label = __('Not Configured', 'formflow');
                                break;
                        }
                        if (!empty($health['latency_ms'])) {
                            $latency = $health['latency_ms'] . 'ms';
                        }
                    }
                ?>
                <div class="isf-api-health-item" data-instance-id="<?php echo esc_attr($instance['id']); ?>">
                    <div class="isf-api-health-indicator isf-health-<?php echo esc_attr($status_class); ?>"></div>
                    <div class="isf-api-health-info">
                        <div class="isf-api-health-name"><?php echo esc_html($instance['name']); ?></div>
                        <div class="isf-api-health-details">
                            <span class="isf-api-health-status"><?php echo esc_html($status_label); ?></span>
                            <?php if ($latency) : ?>
                                <span class="isf-api-health-latency"><?php echo esc_html($latency); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($health['error'])) : ?>
                        <div class="isf-api-health-error" title="<?php echo esc_attr($health['error']); ?>">
                            <span class="dashicons dashicons-warning"></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="isf-api-health-empty">
                    <p><?php esc_html_e('Click "Check Now" to test API connections.', 'formflow'); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($api_health) : ?>
        <div class="isf-api-health-footer">
            <span class="isf-api-health-legend">
                <span class="isf-health-dot isf-health-healthy"></span> <?php esc_html_e('Healthy', 'formflow'); ?>
                <span class="isf-health-dot isf-health-degraded"></span> <?php esc_html_e('Degraded', 'formflow'); ?>
                <span class="isf-health-dot isf-health-error"></span> <?php esc_html_e('Error', 'formflow'); ?>
                <span class="isf-health-dot isf-health-demo"></span> <?php esc_html_e('Demo', 'formflow'); ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

    <!-- API Usage / Rate Limit Monitoring -->
    <div class="isf-card isf-api-usage-card">
        <div class="isf-card-header">
            <h2><?php esc_html_e('API Usage', 'formflow'); ?></h2>
            <div class="isf-card-actions">
                <select id="isf-api-usage-period" class="isf-select-small">
                    <option value="hour"><?php esc_html_e('Last Hour', 'formflow'); ?></option>
                    <option value="day" selected><?php esc_html_e('Last 24 Hours', 'formflow'); ?></option>
                    <option value="week"><?php esc_html_e('Last 7 Days', 'formflow'); ?></option>
                    <option value="month"><?php esc_html_e('Last 30 Days', 'formflow'); ?></option>
                </select>
                <button type="button" id="isf-refresh-api-usage" class="button button-small">
                    <span class="dashicons dashicons-update"></span>
                </button>
            </div>
        </div>

        <div id="isf-api-usage-content" class="isf-api-usage-grid">
            <div class="isf-api-usage-loading">
                <span class="spinner is-active"></span>
                <?php esc_html_e('Loading API usage data...', 'formflow'); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form Instances Table -->
    <div class="isf-card">
        <div class="isf-card-header">
            <h2><?php esc_html_e('Form Instances', 'formflow'); ?></h2>
            <p class="description" style="margin: 0;"><?php esc_html_e('Drag rows to reorder forms', 'formflow'); ?></p>
        </div>

        <?php if (empty($instances)) : ?>
            <div class="isf-empty-state">
                <span class="dashicons dashicons-forms"></span>
                <p><?php esc_html_e('No form instances yet.', 'formflow'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=isf-instance-editor')); ?>" class="button button-primary">
                    <?php esc_html_e('Create Your First Form', 'formflow'); ?>
                </a>
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped isf-sortable-table" id="isf-instances-table">
                <thead>
                    <tr>
                        <th class="column-drag" style="width: 30px;"></th>
                        <th class="column-name"><?php esc_html_e('Name', 'formflow'); ?></th>
                        <th class="column-shortcode"><?php esc_html_e('Shortcode', 'formflow'); ?></th>
                        <th class="column-utility"><?php esc_html_e('Utility', 'formflow'); ?></th>
                        <th class="column-stats"><?php esc_html_e('Stats', 'formflow'); ?></th>
                        <th class="column-status"><?php esc_html_e('Status', 'formflow'); ?></th>
                        <th class="column-actions"><?php esc_html_e('Actions', 'formflow'); ?></th>
                    </tr>
                </thead>
                <tbody id="isf-instances-sortable">
                    <?php foreach ($instances as $instance) :
                        $inst_stats = $instance_stats[$instance['id']] ?? ['total' => 0, 'completed' => 0];
                    ?>
                        <tr class="isf-sortable-row" data-instance-id="<?php echo esc_attr($instance['id']); ?>">
                            <td class="column-drag">
                                <span class="isf-drag-handle" title="<?php esc_attr_e('Drag to reorder', 'formflow'); ?>">
                                    <span class="dashicons dashicons-menu"></span>
                                </span>
                            </td>
                            <td class="column-name">
                                <strong>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=isf-instance-editor&id=' . $instance['id'])); ?>">
                                        <?php echo esc_html($instance['name']); ?>
                                    </a>
                                </strong>
                                <div class="row-actions">
                                    <span class="isf-form-type"><?php echo esc_html(ucfirst($instance['form_type'])); ?></span>
                                </div>
                            </td>
                            <td class="column-shortcode">
                                <code class="isf-shortcode" onclick="navigator.clipboard.writeText(this.innerText)" title="<?php esc_attr_e('Click to copy', 'formflow'); ?>">
                                    [isf_form instance="<?php echo esc_attr($instance['slug']); ?>"]
                                </code>
                            </td>
                            <td class="column-utility">
                                <?php echo esc_html(ucwords(str_replace('_', ' ', $instance['utility']))); ?>
                            </td>
                            <td class="column-stats">
                                <span class="isf-mini-stat" title="<?php esc_attr_e('Completed / Total', 'formflow'); ?>">
                                    <span class="isf-mini-stat-completed"><?php echo esc_html($inst_stats['completed']); ?></span>
                                    <span class="isf-mini-stat-separator">/</span>
                                    <span class="isf-mini-stat-total"><?php echo esc_html($inst_stats['total']); ?></span>
                                </span>
                                <?php if ($inst_stats['total'] > 0) : ?>
                                    <div class="isf-mini-progress">
                                        <div class="isf-mini-progress-bar" style="width: <?php echo esc_attr($inst_stats['completion_rate'] ?? 0); ?>%;"></div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="column-status">
                                <?php if ($instance['is_active']) : ?>
                                    <span class="isf-status isf-status-active">
                                        <?php esc_html_e('Active', 'formflow'); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="isf-status isf-status-inactive">
                                        <?php esc_html_e('Inactive', 'formflow'); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($instance['form_type'] === 'external') : ?>
                                    <span class="isf-status isf-status-external" title="<?php esc_attr_e('Redirects to external enrollment platform', 'formflow'); ?>">
                                        <?php esc_html_e('External', 'formflow'); ?>
                                    </span>
                                <?php elseif ($instance['settings']['demo_mode'] ?? false) : ?>
                                    <span class="isf-status isf-status-demo">
                                        <?php esc_html_e('Demo', 'formflow'); ?>
                                    </span>
                                <?php elseif ($instance['test_mode']) : ?>
                                    <span class="isf-status isf-status-test">
                                        <?php esc_html_e('Test', 'formflow'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="column-actions">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=isf-instance-editor&id=' . $instance['id'])); ?>" class="button button-small">
                                    <?php esc_html_e('Edit', 'formflow'); ?>
                                </a>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=isf-data&tab=analytics&instance_id=' . $instance['id'])); ?>" class="button button-small">
                                    <?php esc_html_e('Analytics', 'formflow'); ?>
                                </a>
                                <button type="button" class="button button-small button-link-delete isf-delete-instance" data-id="<?php echo esc_attr($instance['id']); ?>">
                                    <?php esc_html_e('Delete', 'formflow'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Recent Submissions -->
    <?php if (!empty($recent_submissions)) : ?>
    <div class="isf-card">
        <div class="isf-card-header">
            <h2><?php esc_html_e('Recent Submissions', 'formflow'); ?></h2>
            <a href="<?php echo esc_url(admin_url('admin.php?page=isf-logs')); ?>" class="button button-small">
                <?php esc_html_e('View All', 'formflow'); ?>
            </a>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="column-date"><?php esc_html_e('Date', 'formflow'); ?></th>
                    <th class="column-form"><?php esc_html_e('Form', 'formflow'); ?></th>
                    <th class="column-customer"><?php esc_html_e('Customer', 'formflow'); ?></th>
                    <th class="column-device"><?php esc_html_e('Device', 'formflow'); ?></th>
                    <th class="column-status"><?php esc_html_e('Status', 'formflow'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_submissions as $submission) :
                    $form_data = $submission['form_data'] ?? [];
                    $customer_name = trim(($form_data['first_name'] ?? '') . ' ' . ($form_data['last_name'] ?? ''));
                    if (empty($customer_name)) {
                        $customer_name = $submission['account_number'] ?? __('Anonymous', 'formflow');
                    }
                    $device_type = $form_data['device_type'] ?? '';
                    $device_label = $device_type === 'thermostat' ? __('Thermostat', 'formflow') : ($device_type === 'dcu' ? __('Outdoor Switch', 'formflow') : '—');

                    // Find instance name
                    $instance_name = '—';
                    foreach ($instances as $inst) {
                        if ($inst['id'] == $submission['instance_id']) {
                            $instance_name = $inst['name'];
                            break;
                        }
                    }
                ?>
                    <tr>
                        <td class="column-date">
                            <span title="<?php echo esc_attr($submission['created_at']); ?>">
                                <?php echo esc_html(human_time_diff(strtotime($submission['created_at']), current_time('timestamp')) . ' ' . __('ago', 'formflow')); ?>
                            </span>
                        </td>
                        <td class="column-form">
                            <?php echo esc_html($instance_name); ?>
                        </td>
                        <td class="column-customer">
                            <?php echo esc_html($customer_name); ?>
                        </td>
                        <td class="column-device">
                            <?php echo esc_html($device_label); ?>
                        </td>
                        <td class="column-status">
                            <?php
                            $status_class = 'isf-status-' . ($submission['status'] ?? 'in_progress');
                            $status_label = ucfirst(str_replace('_', ' ', $submission['status'] ?? 'in_progress'));
                            ?>
                            <span class="isf-status <?php echo esc_attr($status_class); ?>">
                                <?php echo esc_html($status_label); ?>
                            </span>
                            <?php if (!empty($submission['is_test_data'])) : ?>
                                <span class="isf-status isf-status-test"><?php esc_html_e('Test', 'formflow'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($instances)) : ?>
    <!-- Shortcode Generator -->
    <div class="isf-card isf-shortcode-generator">
        <h2><?php esc_html_e('Shortcode Generator', 'formflow'); ?></h2>
        <p class="description"><?php esc_html_e('Generate a shortcode to embed a form on any page or post.', 'formflow'); ?></p>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="isf-gen-instance"><?php esc_html_e('Select Form', 'formflow'); ?></label>
                </th>
                <td>
                    <select id="isf-gen-instance" class="regular-text">
                        <?php foreach ($instances as $instance) : ?>
                            <option value="<?php echo esc_attr($instance['slug']); ?>">
                                <?php echo esc_html($instance['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="isf-gen-class"><?php esc_html_e('Custom CSS Class', 'formflow'); ?></label>
                </th>
                <td>
                    <input type="text" id="isf-gen-class" class="regular-text" placeholder="<?php esc_attr_e('Optional', 'formflow'); ?>">
                    <p class="description"><?php esc_html_e('Add a custom CSS class for styling.', 'formflow'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Generated Shortcode', 'formflow'); ?></label>
                </th>
                <td>
                    <div class="isf-generated-shortcode-wrap">
                        <code id="isf-generated-shortcode" class="isf-shortcode-display">[isf_form instance="<?php echo esc_attr($instances[0]['slug'] ?? ''); ?>"]</code>
                        <button type="button" id="isf-copy-shortcode" class="button">
                            <span class="dashicons dashicons-clipboard"></span>
                            <?php esc_html_e('Copy', 'formflow'); ?>
                        </button>
                    </div>
                    <p class="description isf-copy-success" id="isf-copy-success" style="display:none; color:#46b450;">
                        <?php esc_html_e('Copied to clipboard!', 'formflow'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <script>
    jQuery(document).ready(function($) {
        function updateShortcode() {
            var instance = $('#isf-gen-instance').val();
            var cssClass = $('#isf-gen-class').val().trim();

            var shortcode = '[isf_form instance="' + instance + '"';
            if (cssClass) {
                shortcode += ' class="' + cssClass + '"';
            }
            shortcode += ']';

            $('#isf-generated-shortcode').text(shortcode);
        }

        $('#isf-gen-instance, #isf-gen-class').on('input change', updateShortcode);

        $('#isf-copy-shortcode').on('click', function() {
            var shortcode = $('#isf-generated-shortcode').text();
            navigator.clipboard.writeText(shortcode).then(function() {
                $('#isf-copy-success').fadeIn().delay(2000).fadeOut();
            });
        });

        // Also allow clicking the shortcode itself to copy
        $('#isf-generated-shortcode').on('click', function() {
            navigator.clipboard.writeText($(this).text()).then(function() {
                $('#isf-copy-success').fadeIn().delay(2000).fadeOut();
            });
        });
    });
    </script>
    <?php endif; ?>

    <!-- Quick Start Guide -->
    <div class="isf-card isf-quick-start">
        <h2><?php esc_html_e('Quick Start Guide', 'formflow'); ?></h2>
        <ol>
            <li><?php esc_html_e('Create a new form instance by clicking "Add New Form"', 'formflow'); ?></li>
            <li><?php esc_html_e('Select your utility from the dropdown (API settings will auto-fill)', 'formflow'); ?></li>
            <li><?php esc_html_e('Enter your API password and test the connection', 'formflow'); ?></li>
            <li><?php esc_html_e('Copy the shortcode and paste it into any page or post', 'formflow'); ?></li>
        </ol>
    </div>
</div>
