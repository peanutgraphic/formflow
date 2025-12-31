<?php
/**
 * License Settings Tab
 *
 * License activation, deactivation, and status display.
 *
 * @package FormFlow
 */

if (!defined('ABSPATH')) {
    exit;
}

$license = \ISF\LicenseManager::instance();
$status = $license->get_license_status();
$is_pro = $license->is_pro();
$is_dev_mode = $license->is_admin_testing_mode();

// Handle form submissions
$message = '';
$message_type = '';

if (isset($_POST['isf_license_action']) && check_admin_referer('isf_license_action')) {
    $action = sanitize_text_field($_POST['isf_license_action']);

    if ($action === 'activate') {
        $key = sanitize_text_field($_POST['license_key'] ?? '');
        $result = $license->activate_license($key);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';

        // Refresh status after activation
        if ($result['success']) {
            $status = $license->get_license_status();
            $is_pro = $license->is_pro();
            $is_dev_mode = $license->is_admin_testing_mode();
        }
    } elseif ($action === 'deactivate') {
        $result = $license->deactivate_license();
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';

        // Refresh status after deactivation
        $status = $license->get_license_status();
        $is_pro = $license->is_pro();
        $is_dev_mode = $license->is_admin_testing_mode();
    } elseif ($action === 'check') {
        $license->check_license_status();
        $status = $license->get_license_status();
        $message = __('License status refreshed.', 'formflow');
        $message_type = 'info';
    }
}

// Status badge classes
$status_class = 'isf-status-inactive';
$status_label = __('Inactive', 'formflow');

if ($is_dev_mode) {
    $status_class = 'isf-status-dev';
    $status_label = __('Development Mode', 'formflow');
} elseif ($status['status'] === 'active') {
    $status_class = 'isf-status-active';
    $status_label = __('Active', 'formflow');
} elseif ($status['status'] === 'expired') {
    $status_class = 'isf-status-expired';
    $status_label = __('Expired', 'formflow');
}
?>

<?php if ($message) : ?>
    <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
        <p><?php echo esc_html($message); ?></p>
    </div>
<?php endif; ?>

<div class="isf-license-page">
    <div class="isf-pods-grid">
        <!-- License Status Pod -->
        <div class="isf-pod isf-pod-primary">
            <div class="isf-pod-header">
                <h3>
                    <span class="dashicons dashicons-admin-network"></span>
                    <?php esc_html_e('License Status', 'formflow'); ?>
                </h3>
                <span class="isf-license-status-badge <?php echo esc_attr($status_class); ?>">
                    <?php echo esc_html($status_label); ?>
                </span>
            </div>
            <div class="isf-pod-body">
                <?php if ($is_pro) : ?>
                    <div class="isf-license-active-info">
                        <div class="isf-license-tier">
                            <span class="isf-tier-badge isf-tier-<?php echo esc_attr($status['tier']); ?>">
                                <?php echo esc_html($status['tier_label']); ?>
                            </span>
                        </div>

                        <div class="isf-license-details">
                            <div class="isf-license-detail">
                                <span class="isf-detail-label"><?php esc_html_e('License Key', 'formflow'); ?></span>
                                <span class="isf-detail-value">
                                    <code><?php echo esc_html($license->get_license_key_masked()); ?></code>
                                </span>
                            </div>

                            <?php if (!$is_dev_mode) : ?>
                                <div class="isf-license-detail">
                                    <span class="isf-detail-label"><?php esc_html_e('Expires', 'formflow'); ?></span>
                                    <span class="isf-detail-value"><?php echo esc_html($status['expires_label']); ?></span>
                                </div>

                                <div class="isf-license-detail">
                                    <span class="isf-detail-label"><?php esc_html_e('Site Activations', 'formflow'); ?></span>
                                    <span class="isf-detail-value">
                                        <?php echo esc_html($status['activations_used']); ?> / <?php echo esc_html($status['activations_limit']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($is_dev_mode) : ?>
                            <div class="isf-pod-info-box" style="background: #fff8e5; border-color: #dba617;">
                                <p>
                                    <span class="dashicons dashicons-warning" style="color: #dba617;"></span>
                                    <?php esc_html_e('Development mode is active. All Pro features are unlocked for testing.', 'formflow'); ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <form method="post" class="isf-license-actions">
                            <?php wp_nonce_field('isf_license_action'); ?>

                            <button type="submit" name="isf_license_action" value="check" class="button">
                                <span class="dashicons dashicons-update"></span>
                                <?php esc_html_e('Refresh Status', 'formflow'); ?>
                            </button>

                            <button type="submit" name="isf_license_action" value="deactivate" class="button"
                                    onclick="return confirm('<?php esc_attr_e('Are you sure you want to deactivate your license?', 'formflow'); ?>');">
                                <?php esc_html_e('Deactivate License', 'formflow'); ?>
                            </button>
                        </form>
                    </div>
                <?php else : ?>
                    <div class="isf-license-inactive-info">
                        <p><?php esc_html_e('Enter your license key to unlock Pro features and receive automatic updates.', 'formflow'); ?></p>

                        <form method="post" class="isf-license-form">
                            <?php wp_nonce_field('isf_license_action'); ?>

                            <div class="isf-license-input-group">
                                <input type="text"
                                       name="license_key"
                                       placeholder="<?php esc_attr_e('XXXX-XXXX-XXXX-XXXX', 'formflow'); ?>"
                                       class="isf-license-input"
                                       required>
                                <button type="submit" name="isf_license_action" value="activate" class="button button-primary">
                                    <?php esc_html_e('Activate License', 'formflow'); ?>
                                </button>
                            </div>
                        </form>

                        <p class="isf-license-help">
                            <?php
                            printf(
                                esc_html__('Don\'t have a license? %s', 'formflow'),
                                '<a href="https://peanutgraphic.com/formflow/pricing" target="_blank">' .
                                esc_html__('Get one here', 'formflow') .
                                '</a>'
                            );
                            ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Plan Comparison Pod -->
        <div class="isf-pod">
            <div class="isf-pod-header">
                <h3>
                    <span class="dashicons dashicons-cart"></span>
                    <?php esc_html_e('Available Plans', 'formflow'); ?>
                </h3>
            </div>
            <div class="isf-pod-body">
                <div class="isf-plans-grid">
                    <?php
                    $tiers = $license->get_license_tiers();
                    foreach ($tiers as $tier_key => $tier) :
                        $is_current = ($status['tier'] === $tier_key) && $is_pro;
                    ?>
                        <div class="isf-plan-card <?php echo $is_current ? 'isf-plan-current' : ''; ?>">
                            <div class="isf-plan-header">
                                <h4><?php echo esc_html($tier['name']); ?></h4>
                                <?php if ($is_current) : ?>
                                    <span class="isf-current-badge"><?php esc_html_e('Current', 'formflow'); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="isf-plan-price">
                                <?php if ($tier['price'] === 0) : ?>
                                    <span class="isf-price-amount"><?php esc_html_e('Free', 'formflow'); ?></span>
                                <?php else : ?>
                                    <span class="isf-price-amount">$<?php echo esc_html($tier['price']); ?></span>
                                    <span class="isf-price-period">/<?php esc_html_e('year', 'formflow'); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="isf-plan-sites">
                                <?php
                                printf(
                                    esc_html(_n('%d site', '%d sites', $tier['sites'], 'formflow')),
                                    $tier['sites']
                                );
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="isf-plan-cta">
                    <a href="https://peanutgraphic.com/formflow/pricing" target="_blank" class="button button-primary">
                        <?php esc_html_e('View Full Feature Comparison', 'formflow'); ?>
                        <span class="dashicons dashicons-external"></span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Pro Features Pod -->
        <div class="isf-pod isf-pod-full">
            <div class="isf-pod-header">
                <h3>
                    <span class="dashicons dashicons-star-filled"></span>
                    <?php esc_html_e('Pro Features', 'formflow'); ?>
                </h3>
            </div>
            <div class="isf-pod-body">
                <div class="isf-features-grid">
                    <?php
                    $pro_features = $license->get_pro_features();
                    foreach ($pro_features as $feature_key => $feature_label) :
                        $has_feature = $license->has_feature($feature_key);
                    ?>
                        <div class="isf-feature-item <?php echo $has_feature ? 'isf-feature-unlocked' : 'isf-feature-locked'; ?>">
                            <span class="dashicons <?php echo $has_feature ? 'dashicons-yes-alt' : 'dashicons-lock'; ?>"></span>
                            <span class="isf-feature-name"><?php echo esc_html($feature_label); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Updates Pod -->
        <div class="isf-pod">
            <div class="isf-pod-header">
                <h3>
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e('Updates', 'formflow'); ?>
                </h3>
            </div>
            <div class="isf-pod-body">
                <div class="isf-update-info">
                    <div class="isf-license-detail">
                        <span class="isf-detail-label"><?php esc_html_e('Current Version', 'formflow'); ?></span>
                        <span class="isf-detail-value"><strong><?php echo esc_html(ISF_VERSION); ?></strong></span>
                    </div>

                    <?php
                    $update_plugins = get_site_transient('update_plugins');
                    $has_update = isset($update_plugins->response[\ISF\Updater::PLUGIN_FILE]);
                    ?>

                    <?php if ($has_update) : ?>
                        <div class="isf-pod-info-box" style="background: #d4edda; border-color: #00a32a;">
                            <p>
                                <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                                <?php
                                printf(
                                    esc_html__('Version %s is available!', 'formflow'),
                                    esc_html($update_plugins->response[\ISF\Updater::PLUGIN_FILE]->new_version)
                                );
                                ?>
                            </p>
                        </div>

                        <?php if ($is_pro) : ?>
                            <a href="<?php echo esc_url(admin_url('plugins.php')); ?>" class="button button-primary">
                                <?php esc_html_e('Update Now', 'formflow'); ?>
                            </a>
                        <?php else : ?>
                            <p class="isf-update-notice">
                                <?php esc_html_e('A valid license is required to download updates.', 'formflow'); ?>
                            </p>
                        <?php endif; ?>
                    <?php else : ?>
                        <p class="isf-up-to-date">
                            <span class="dashicons dashicons-yes"></span>
                            <?php esc_html_e('You are running the latest version.', 'formflow'); ?>
                        </p>
                    <?php endif; ?>

                    <p style="margin-top: 15px;">
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('plugins.php?formflow_check_update=1'), 'formflow_check_update')); ?>" class="button">
                            <?php esc_html_e('Check for Updates', 'formflow'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Support Pod -->
        <div class="isf-pod">
            <div class="isf-pod-header">
                <h3>
                    <span class="dashicons dashicons-sos"></span>
                    <?php esc_html_e('Support', 'formflow'); ?>
                </h3>
            </div>
            <div class="isf-pod-body">
                <p><?php esc_html_e('Need help with FormFlow? We\'re here to assist you.', 'formflow'); ?></p>

                <ul class="isf-support-links">
                    <li>
                        <a href="https://peanutgraphic.com/formflow/docs" target="_blank">
                            <span class="dashicons dashicons-book"></span>
                            <?php esc_html_e('Documentation', 'formflow'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="https://peanutgraphic.com/support" target="_blank">
                            <span class="dashicons dashicons-email"></span>
                            <?php esc_html_e('Contact Support', 'formflow'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="https://peanutgraphic.com/formflow/changelog" target="_blank">
                            <span class="dashicons dashicons-list-view"></span>
                            <?php esc_html_e('Changelog', 'formflow'); ?>
                        </a>
                    </li>
                </ul>

                <?php if ($is_pro && ($status['tier'] === 'agency')) : ?>
                    <div class="isf-pod-info-box" style="background: #f0f6fc; border-color: #2271b1;">
                        <p>
                            <span class="dashicons dashicons-star-filled" style="color: #2271b1;"></span>
                            <?php esc_html_e('As an Agency license holder, you have access to priority support.', 'formflow'); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* License Page Styles */
.isf-license-page {
    margin-top: 20px;
}

.isf-license-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.isf-status-active {
    background: #d4edda;
    color: #155724;
}

.isf-status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.isf-status-expired {
    background: #fff3cd;
    color: #856404;
}

.isf-status-dev {
    background: #e7f3ff;
    color: #0073aa;
}

.isf-license-tier {
    margin-bottom: 20px;
}

.isf-tier-badge {
    display: inline-block;
    padding: 8px 20px;
    border-radius: 4px;
    font-size: 18px;
    font-weight: 600;
}

.isf-tier-free {
    background: #f0f0f1;
    color: #50575e;
}

.isf-tier-pro {
    background: linear-gradient(135deg, #2271b1, #135e96);
    color: #fff;
}

.isf-tier-agency {
    background: linear-gradient(135deg, #8e44ad, #9b59b6);
    color: #fff;
}

.isf-license-details {
    display: grid;
    gap: 12px;
    margin-bottom: 20px;
}

.isf-license-detail {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f1;
}

.isf-detail-label {
    color: #646970;
    font-size: 13px;
}

.isf-detail-value {
    font-weight: 500;
}

.isf-license-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.isf-license-actions .button .dashicons {
    margin-right: 4px;
    font-size: 16px;
    line-height: 1.4;
}

.isf-license-form {
    margin: 20px 0;
}

.isf-license-input-group {
    display: flex;
    gap: 10px;
}

.isf-license-input {
    flex: 1;
    padding: 8px 12px;
    font-size: 14px;
    font-family: monospace;
    letter-spacing: 1px;
}

.isf-license-help {
    margin-top: 15px;
    color: #646970;
}

/* Plans Grid */
.isf-plans-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.isf-plan-card {
    text-align: center;
    padding: 20px 15px;
    border: 1px solid #dcdcde;
    border-radius: 6px;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.isf-plan-card:hover {
    border-color: #2271b1;
}

.isf-plan-current {
    border-color: #2271b1;
    background: #f0f6fc;
}

.isf-plan-header {
    position: relative;
}

.isf-plan-header h4 {
    margin: 0 0 10px;
    font-size: 16px;
}

.isf-current-badge {
    position: absolute;
    top: -25px;
    right: -10px;
    background: #2271b1;
    color: #fff;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 10px;
    text-transform: uppercase;
}

.isf-plan-price {
    margin-bottom: 10px;
}

.isf-price-amount {
    font-size: 24px;
    font-weight: 700;
    color: #1d2327;
}

.isf-price-period {
    font-size: 13px;
    color: #646970;
}

.isf-plan-sites {
    font-size: 13px;
    color: #646970;
}

.isf-plan-cta {
    text-align: center;
    padding-top: 15px;
    border-top: 1px solid #f0f0f1;
}

.isf-plan-cta .button .dashicons {
    font-size: 14px;
    margin-left: 4px;
    vertical-align: middle;
}

/* Features Grid */
.isf-features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 10px;
}

.isf-feature-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 4px;
}

.isf-feature-unlocked {
    background: #d4edda;
}

.isf-feature-unlocked .dashicons {
    color: #00a32a;
}

.isf-feature-locked {
    background: #f0f0f1;
    color: #646970;
}

.isf-feature-locked .dashicons {
    color: #a7aaad;
}

/* Support Links */
.isf-support-links {
    list-style: none;
    margin: 15px 0;
    padding: 0;
}

.isf-support-links li {
    margin-bottom: 10px;
}

.isf-support-links a {
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.isf-support-links a:hover {
    text-decoration: underline;
}

.isf-up-to-date {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #00a32a;
}

.isf-update-notice {
    color: #d63638;
}

/* Responsive */
@media (max-width: 782px) {
    .isf-plans-grid {
        grid-template-columns: 1fr;
    }

    .isf-license-input-group {
        flex-direction: column;
    }

    .isf-license-actions {
        flex-direction: column;
    }
}
</style>
