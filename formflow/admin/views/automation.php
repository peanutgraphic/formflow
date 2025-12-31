<?php
/**
 * Combined Automation View
 *
 * Displays Webhooks and Reports in a tabbed interface.
 *
 * @package FormFlow
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include breadcrumbs partial
require_once ISF_PLUGIN_DIR . 'admin/views/partials/breadcrumbs.php';

// Base URL for tabs
$base_url = admin_url('admin.php?page=isf-automation');
?>

<div class="wrap isf-admin-wrap">
    <?php isf_breadcrumbs(['Dashboard' => 'isf-dashboard'], __('Automation', 'formflow')); ?>

    <h1><?php esc_html_e('Automation', 'formflow'); ?></h1>

    <!-- Tab Navigation -->
    <nav class="nav-tab-wrapper">
        <a href="<?php echo esc_url(add_query_arg('tab', 'webhooks', $base_url)); ?>"
           class="nav-tab <?php echo ($tab === 'webhooks') ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-rest-api"></span>
            <?php esc_html_e('Webhooks', 'formflow'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'reports', $base_url)); ?>"
           class="nav-tab <?php echo ($tab === 'reports') ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-email-alt"></span>
            <?php esc_html_e('Scheduled Reports', 'formflow'); ?>
        </a>
    </nav>

    <div class="isf-tab-content">
        <?php if ($tab === 'webhooks') : ?>
            <!-- Webhooks Tab -->
            <?php include ISF_PLUGIN_DIR . 'admin/views/tabs/automation-webhooks.php'; ?>

        <?php elseif ($tab === 'reports') : ?>
            <!-- Reports Tab -->
            <?php include ISF_PLUGIN_DIR . 'admin/views/tabs/automation-reports.php'; ?>

        <?php endif; ?>
    </div>
</div>
