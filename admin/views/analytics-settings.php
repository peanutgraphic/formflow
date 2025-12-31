<?php
/**
 * Analytics Settings View
 *
 * Admin interface for configuring analytics and tracking settings.
 *
 * @var array $settings Current plugin settings
 * @var array $instances Available form instances
 */

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

// Include help tooltip function
require_once ISF_PLUGIN_DIR . 'admin/views/partials/help-tooltip.php';

// Handle form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_analytics_settings'])) {
    if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'isf_analytics_settings')) {
        $message = __('Security check failed. Please try again.', 'formflow');
        $message_type = 'error';
    } else {
        $current_settings = get_option('isf_settings', []);

        // Analytics settings
        $analytics_settings = [
            'analytics_enabled' => isset($_POST['analytics_enabled']),
            'track_page_views' => isset($_POST['track_page_views']),
            'visitor_cookie_days' => (int) ($_POST['visitor_cookie_days'] ?? 365),
            'use_fingerprinting' => isset($_POST['use_fingerprinting']),
            'cookie_consent_mode' => sanitize_text_field($_POST['cookie_consent_mode'] ?? 'always'),
            'gtm_enabled' => isset($_POST['gtm_enabled']),
            'gtm_container_id' => sanitize_text_field($_POST['gtm_container_id'] ?? ''),
            'ga4_enabled' => isset($_POST['ga4_enabled']),
            'ga4_measurement_id' => sanitize_text_field($_POST['ga4_measurement_id'] ?? ''),
            'clarity_enabled' => isset($_POST['clarity_enabled']),
            'clarity_project_id' => sanitize_text_field($_POST['clarity_project_id'] ?? ''),
            'handoff_tracking_enabled' => isset($_POST['handoff_tracking_enabled']),
        ];

        $new_settings = array_merge($current_settings, $analytics_settings);
        update_option('isf_settings', $new_settings);

        $settings = $new_settings;
        $message = __('Settings saved successfully.', 'formflow');
        $message_type = 'success';
    }
}

// Default values
$defaults = [
    'analytics_enabled' => true,
    'track_page_views' => true,
    'visitor_cookie_days' => 365,
    'use_fingerprinting' => false,
    'cookie_consent_mode' => 'always',
    'gtm_enabled' => false,
    'gtm_container_id' => '',
    'ga4_enabled' => false,
    'ga4_measurement_id' => '',
    'clarity_enabled' => false,
    'clarity_project_id' => '',
    'handoff_tracking_enabled' => true,
];

$settings = wp_parse_args($settings, $defaults);
?>

<div class="wrap isf-analytics-settings">
    <h1>
        <?php esc_html_e('Analytics Settings', 'formflow'); ?>
        <?php isf_help_tooltip('Configure global analytics and tracking settings. Per-instance settings can be configured in each form\'s Features tab.'); ?>
    </h1>

    <!-- Quick Start Guide -->
    <div class="isf-quick-start-card">
        <h3><span class="dashicons dashicons-lightbulb"></span> <?php esc_html_e('Quick Start Guide', 'formflow'); ?></h3>
        <div class="isf-quick-start-steps">
            <div class="isf-step">
                <span class="isf-step-number">1</span>
                <div class="isf-step-content">
                    <strong><?php esc_html_e('Enable Analytics', 'formflow'); ?></strong>
                    <p><?php esc_html_e('Turn on visitor tracking below', 'formflow'); ?></p>
                </div>
            </div>
            <div class="isf-step">
                <span class="isf-step-number">2</span>
                <div class="isf-step-content">
                    <strong><?php esc_html_e('Configure GTM (Optional)', 'formflow'); ?></strong>
                    <p><?php esc_html_e('Connect to Google Tag Manager for enhanced tracking', 'formflow'); ?></p>
                </div>
            </div>
            <div class="isf-step">
                <span class="isf-step-number">3</span>
                <div class="isf-step-content">
                    <strong><?php esc_html_e('Add UTM Parameters', 'formflow'); ?></strong>
                    <p><?php esc_html_e('Use ?utm_source=...&utm_campaign=... in marketing links', 'formflow'); ?></p>
                </div>
            </div>
            <div class="isf-step">
                <span class="isf-step-number">4</span>
                <div class="isf-step-content">
                    <strong><?php esc_html_e('View Reports', 'formflow'); ?></strong>
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=isf-attribution')); ?>">
                            <?php esc_html_e('Go to Attribution Report →', 'formflow'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <form method="post">
        <?php wp_nonce_field('isf_analytics_settings'); ?>

        <!-- Visitor Tracking -->
        <div class="isf-settings-card">
            <h2>
                <?php esc_html_e('Visitor Tracking', 'formflow'); ?>
                <?php isf_help_tooltip('Visitor tracking uses first-party cookies to identify users across sessions. This data powers the attribution reports.'); ?>
            </h2>
            <p class="description">
                <?php esc_html_e('Configure how visitor data is collected and stored.', 'formflow'); ?>
            </p>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Enable Analytics', 'formflow'); ?>
                        <?php isf_help_tooltip('Master switch for all visitor tracking. When disabled, no visitor data is collected.'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="analytics_enabled" value="1"
                                <?php checked($settings['analytics_enabled']); ?>>
                            <?php esc_html_e('Enable visitor and marketing attribution tracking', 'formflow'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('When enabled, the plugin will track visitor sessions and marketing touchpoints.', 'formflow'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Track Page Views', 'formflow'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="track_page_views" value="1"
                                <?php checked($settings['track_page_views']); ?>>
                            <?php esc_html_e('Record all page views with UTM parameters', 'formflow'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Track every page view, not just form interactions. Provides richer attribution data.', 'formflow'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="visitor_cookie_days"><?php esc_html_e('Cookie Duration', 'formflow'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="visitor_cookie_days" id="visitor_cookie_days"
                            value="<?php echo esc_attr($settings['visitor_cookie_days']); ?>"
                            min="1" max="730" style="width: 80px;">
                        <?php esc_html_e('days', 'formflow'); ?>
                        <p class="description">
                            <?php esc_html_e('How long to remember visitors. Longer durations provide better cross-session attribution.', 'formflow'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Browser Fingerprinting', 'formflow'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="use_fingerprinting" value="1"
                                <?php checked($settings['use_fingerprinting']); ?>>
                            <?php esc_html_e('Use browser fingerprinting for visitor identification', 'formflow'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Helps identify visitors who clear cookies. May have privacy implications.', 'formflow'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cookie_consent_mode"><?php esc_html_e('Cookie Consent', 'formflow'); ?></label>
                    </th>
                    <td>
                        <select name="cookie_consent_mode" id="cookie_consent_mode">
                            <option value="always" <?php selected($settings['cookie_consent_mode'], 'always'); ?>>
                                <?php esc_html_e('Always track (no consent required)', 'formflow'); ?>
                            </option>
                            <option value="opt_in" <?php selected($settings['cookie_consent_mode'], 'opt_in'); ?>>
                                <?php esc_html_e('Wait for consent (GDPR mode)', 'formflow'); ?>
                            </option>
                            <option value="opt_out" <?php selected($settings['cookie_consent_mode'], 'opt_out'); ?>>
                                <?php esc_html_e('Track unless opted out', 'formflow'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php esc_html_e('Configure how cookie consent affects tracking. For GDPR compliance, use opt-in mode.', 'formflow'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- External Analytics -->
        <div class="isf-settings-card">
            <h2><?php esc_html_e('External Analytics Integration', 'formflow'); ?></h2>
            <p class="description">
                <?php esc_html_e('Push events to Google Tag Manager, GA4, or Microsoft Clarity.', 'formflow'); ?>
            </p>

            <table class="form-table">
                <!-- GTM -->
                <tr>
                    <th scope="row"><?php esc_html_e('Google Tag Manager', 'formflow'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="gtm_enabled" value="1" id="gtm_enabled"
                                <?php checked($settings['gtm_enabled']); ?>>
                            <?php esc_html_e('Push events to dataLayer', 'formflow'); ?>
                        </label>
                    </td>
                </tr>
                <tr class="gtm-setting">
                    <th scope="row">
                        <label for="gtm_container_id"><?php esc_html_e('GTM Container ID', 'formflow'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="gtm_container_id" id="gtm_container_id"
                            value="<?php echo esc_attr($settings['gtm_container_id']); ?>"
                            placeholder="GTM-XXXXXXX" class="regular-text">
                        <p class="description">
                            <?php esc_html_e('Optional. If your theme already loads GTM, leave blank. Events will still push to dataLayer.', 'formflow'); ?>
                        </p>
                    </td>
                </tr>

                <!-- GA4 -->
                <tr>
                    <th scope="row"><?php esc_html_e('Google Analytics 4', 'formflow'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="ga4_enabled" value="1" id="ga4_enabled"
                                <?php checked($settings['ga4_enabled']); ?>>
                            <?php esc_html_e('Send events directly to GA4 (server-side)', 'formflow'); ?>
                        </label>
                    </td>
                </tr>
                <tr class="ga4-setting">
                    <th scope="row">
                        <label for="ga4_measurement_id"><?php esc_html_e('Measurement ID', 'formflow'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ga4_measurement_id" id="ga4_measurement_id"
                            value="<?php echo esc_attr($settings['ga4_measurement_id']); ?>"
                            placeholder="G-XXXXXXXXXX" class="regular-text">
                        <p class="description">
                            <?php esc_html_e('Your GA4 Measurement ID for server-side event tracking.', 'formflow'); ?>
                        </p>
                    </td>
                </tr>

                <!-- Clarity -->
                <tr>
                    <th scope="row"><?php esc_html_e('Microsoft Clarity', 'formflow'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="clarity_enabled" value="1" id="clarity_enabled"
                                <?php checked($settings['clarity_enabled']); ?>>
                            <?php esc_html_e('Enable Clarity session recording', 'formflow'); ?>
                        </label>
                    </td>
                </tr>
                <tr class="clarity-setting">
                    <th scope="row">
                        <label for="clarity_project_id"><?php esc_html_e('Project ID', 'formflow'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="clarity_project_id" id="clarity_project_id"
                            value="<?php echo esc_attr($settings['clarity_project_id']); ?>"
                            placeholder="xxxxxxxxxx" class="regular-text">
                        <p class="description">
                            <?php esc_html_e('Your Microsoft Clarity project ID.', 'formflow'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Handoff Tracking -->
        <div class="isf-settings-card">
            <h2><?php esc_html_e('Handoff Tracking', 'formflow'); ?></h2>
            <p class="description">
                <?php esc_html_e('Track users who are redirected to external enrollment systems.', 'formflow'); ?>
            </p>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable Handoff Tracking', 'formflow'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="handoff_tracking_enabled" value="1"
                                <?php checked($settings['handoff_tracking_enabled']); ?>>
                            <?php esc_html_e('Track redirects to external enrollment URLs', 'formflow'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('When enabled, external enrollment links will include a tracking token for attribution.', 'formflow'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <h3><?php esc_html_e('Instance Handoff Destinations', 'formflow'); ?></h3>
            <p class="description">
                <?php esc_html_e('Configure external enrollment URLs for each form instance in the instance editor.', 'formflow'); ?>
            </p>

            <?php if (!empty($instances)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Instance', 'formflow'); ?></th>
                            <th><?php esc_html_e('Enrollment Mode', 'formflow'); ?></th>
                            <th><?php esc_html_e('External URL', 'formflow'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($instances as $instance):
                            $instance_settings = json_decode($instance['settings'] ?? '{}', true);
                            $handoff_url = $instance_settings['handoff']['destination_url'] ?? '';
                            $handoff_enabled = !empty($instance_settings['handoff']['enabled']);
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($instance['name']); ?></strong>
                                    <br><code><?php echo esc_html($instance['slug']); ?></code>
                                </td>
                                <td>
                                    <?php if ($handoff_enabled): ?>
                                        <span class="isf-badge isf-badge-external">
                                            <?php esc_html_e('External Handoff', 'formflow'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="isf-badge isf-badge-internal">
                                            <?php esc_html_e('Internal Form', 'formflow'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($handoff_url): ?>
                                        <code><?php echo esc_html($handoff_url); ?></code>
                                    <?php else: ?>
                                        <em><?php esc_html_e('Not configured', 'formflow'); ?></em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=isf-instance&id=' . $instance['id'])); ?>" class="button button-small">
                                        <?php esc_html_e('Edit', 'formflow'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><em><?php esc_html_e('No form instances configured.', 'formflow'); ?></em></p>
            <?php endif; ?>
        </div>

        <!-- Events Reference -->
        <div class="isf-settings-card">
            <h2><?php esc_html_e('GTM/GA4 Event Reference', 'formflow'); ?></h2>
            <p class="description">
                <?php esc_html_e('Events pushed to the dataLayer for tracking in GTM or GA4.', 'formflow'); ?>
            </p>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Event Name', 'formflow'); ?></th>
                        <th><?php esc_html_e('Trigger', 'formflow'); ?></th>
                        <th><?php esc_html_e('Data', 'formflow'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>isf_page_view</code></td>
                        <td><?php esc_html_e('Any page with ISF content', 'formflow'); ?></td>
                        <td>visitor_id, instance, utm_*</td>
                    </tr>
                    <tr>
                        <td><code>isf_form_view</code></td>
                        <td><?php esc_html_e('Form container visible', 'formflow'); ?></td>
                        <td>visitor_id, instance</td>
                    </tr>
                    <tr>
                        <td><code>isf_form_start</code></td>
                        <td><?php esc_html_e('First field interaction', 'formflow'); ?></td>
                        <td>visitor_id, instance</td>
                    </tr>
                    <tr>
                        <td><code>isf_form_step</code></td>
                        <td><?php esc_html_e('Step transition', 'formflow'); ?></td>
                        <td>visitor_id, instance, step, step_name</td>
                    </tr>
                    <tr>
                        <td><code>isf_form_complete</code></td>
                        <td><?php esc_html_e('Form submission success', 'formflow'); ?></td>
                        <td>visitor_id, instance, device_type</td>
                    </tr>
                    <tr>
                        <td><code>isf_handoff</code></td>
                        <td><?php esc_html_e('Redirect to external', 'formflow'); ?></td>
                        <td>visitor_id, instance, destination</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="submit">
            <button type="submit" name="save_analytics_settings" class="button button-primary">
                <?php esc_html_e('Save Settings', 'formflow'); ?>
            </button>
        </p>
    </form>

    <!-- Analytics Diagnostics -->
    <div class="isf-settings-card isf-diagnostics-card">
        <h2>
            <?php esc_html_e('Analytics Health Check', 'formflow'); ?>
            <?php isf_help_tooltip('Run diagnostics to verify all analytics components are properly configured and working.'); ?>
        </h2>

        <?php
        $diagnostics = new \ISF\Analytics\AnalyticsDiagnostics();
        $health = $diagnostics->get_health_status();
        ?>

        <div class="isf-health-summary isf-health-<?php echo esc_attr($health['status']); ?>">
            <div class="isf-health-icon">
                <?php if ($health['status'] === 'healthy'): ?>
                    <span class="dashicons dashicons-yes-alt"></span>
                <?php elseif ($health['status'] === 'warning'): ?>
                    <span class="dashicons dashicons-warning"></span>
                <?php else: ?>
                    <span class="dashicons dashicons-dismiss"></span>
                <?php endif; ?>
            </div>
            <div class="isf-health-info">
                <strong><?php echo esc_html(\ISF\Analytics\AnalyticsDiagnostics::get_status_label($health['status'])); ?></strong>
                <p>
                    <?php printf(
                        esc_html__('%d passed, %d warnings, %d failures', 'formflow'),
                        $health['passed'],
                        $health['warnings'],
                        $health['failures']
                    ); ?>
                </p>
            </div>
        </div>

        <?php if (!empty($health['issues'])): ?>
        <div class="isf-issues-list">
            <h4><?php esc_html_e('Issues to Address', 'formflow'); ?></h4>
            <ul>
                <?php foreach ($health['issues'] as $issue): ?>
                    <li class="isf-issue isf-issue-<?php echo esc_attr($issue['status']); ?>">
                        <span class="dashicons dashicons-<?php echo $issue['status'] === 'warning' ? 'warning' : 'no'; ?>"></span>
                        <strong><?php echo esc_html($issue['name']); ?>:</strong>
                        <?php echo esc_html($issue['message']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <details class="isf-diagnostics-details">
            <summary><?php esc_html_e('View Full Diagnostics', 'formflow'); ?></summary>

            <?php foreach ($health['diagnostics'] as $category => $checks): ?>
                <div class="isf-diagnostics-category">
                    <h4><?php echo esc_html(ucwords(str_replace('_', ' ', $category))); ?></h4>
                    <table class="widefat striped">
                        <tbody>
                            <?php foreach ($checks as $check): ?>
                                <tr class="isf-check-<?php echo esc_attr($check['status']); ?>">
                                    <td style="width: 30px;">
                                        <?php if ($check['status'] === 'pass'): ?>
                                            <span class="dashicons dashicons-yes" style="color: #46b450;"></span>
                                        <?php elseif ($check['status'] === 'warning'): ?>
                                            <span class="dashicons dashicons-warning" style="color: #ffb900;"></span>
                                        <?php else: ?>
                                            <span class="dashicons dashicons-no" style="color: #dc3232;"></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo esc_html($check['name']); ?></strong></td>
                                    <td><?php echo esc_html($check['message']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </details>
    </div>
</div>

<style>
/* Quick Start Guide */
.isf-quick-start-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
    padding: 20px 25px;
    margin: 20px 0;
    color: #fff;
}

.isf-quick-start-card h3 {
    margin: 0 0 15px;
    color: #fff;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.isf-quick-start-card h3 .dashicons {
    font-size: 20px;
}

.isf-quick-start-steps {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.isf-quick-start-steps .isf-step {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    flex: 1;
    min-width: 200px;
    background: rgba(255,255,255,0.15);
    border-radius: 6px;
    padding: 12px;
}

.isf-step-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: rgba(255,255,255,0.3);
    border-radius: 50%;
    font-weight: 600;
    font-size: 14px;
    flex-shrink: 0;
}

.isf-step-content strong {
    display: block;
    font-size: 13px;
    margin-bottom: 4px;
}

.isf-step-content p {
    margin: 0;
    font-size: 12px;
    opacity: 0.9;
}

.isf-step-content a {
    color: #fff;
    text-decoration: underline;
}

.isf-analytics-settings .isf-settings-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-top: 20px;
}

.isf-analytics-settings .isf-settings-card h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.isf-analytics-settings .isf-settings-card h3 {
    margin-top: 25px;
}

.isf-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.isf-badge-internal {
    background: #e7f3ff;
    color: #2271b1;
}

.isf-badge-external {
    background: #fef0e7;
    color: #b35900;
}

.gtm-setting,
.ga4-setting,
.clarity-setting {
    display: none;
}

.gtm-setting.visible,
.ga4-setting.visible,
.clarity-setting.visible {
    display: table-row;
}

/* Health Check Diagnostics */
.isf-health-summary {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.isf-health-healthy {
    background: #ecfdf5;
    border: 1px solid #6ee7b7;
}

.isf-health-warning {
    background: #fffbeb;
    border: 1px solid #fcd34d;
}

.isf-health-critical {
    background: #fef2f2;
    border: 1px solid #fca5a5;
}

.isf-health-icon .dashicons {
    font-size: 36px;
    width: 36px;
    height: 36px;
}

.isf-health-healthy .isf-health-icon .dashicons {
    color: #059669;
}

.isf-health-warning .isf-health-icon .dashicons {
    color: #d97706;
}

.isf-health-critical .isf-health-icon .dashicons {
    color: #dc2626;
}

.isf-health-info strong {
    display: block;
    font-size: 16px;
    margin-bottom: 4px;
}

.isf-health-info p {
    margin: 0;
    color: #6b7280;
}

.isf-issues-list {
    background: #fef9e7;
    border: 1px solid #f0c36d;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
}

.isf-issues-list h4 {
    margin: 0 0 10px;
    font-size: 13px;
}

.isf-issues-list ul {
    margin: 0;
    padding: 0;
    list-style: none;
}

.isf-issues-list li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 6px 0;
    font-size: 13px;
}

.isf-issues-list .dashicons {
    flex-shrink: 0;
    margin-top: 2px;
}

.isf-issue-warning .dashicons {
    color: #d97706;
}

.isf-issue-fail .dashicons {
    color: #dc2626;
}

.isf-diagnostics-details {
    margin-top: 20px;
}

.isf-diagnostics-details summary {
    cursor: pointer;
    color: #2271b1;
    font-weight: 500;
}

.isf-diagnostics-category {
    margin-top: 15px;
}

.isf-diagnostics-category h4 {
    margin: 0 0 8px;
    font-size: 13px;
    color: #1d2327;
}

.isf-diagnostics-category table {
    margin-bottom: 15px;
}
</style>

<script>
jQuery(document).ready(function($) {
    function toggleSettings() {
        $('.gtm-setting').toggleClass('visible', $('#gtm_enabled').is(':checked'));
        $('.ga4-setting').toggleClass('visible', $('#ga4_enabled').is(':checked'));
        $('.clarity-setting').toggleClass('visible', $('#clarity_enabled').is(':checked'));
    }

    $('#gtm_enabled, #ga4_enabled, #clarity_enabled').on('change', toggleSettings);
    toggleSettings();
});
</script>
