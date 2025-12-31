<?php
/**
 * Visitor Analytics Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['visitor_analytics'] ?? [];
?>

<table class="form-table isf-feature-config-table">
    <tr>
        <th scope="row"><?php esc_html_e('Tracking Options', 'formflow'); ?></th>
        <td>
            <fieldset>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][visitor_analytics][track_page_views]" value="1"
                           <?php checked($settings['track_page_views'] ?? true); ?>>
                    <?php esc_html_e('Track all page views', 'formflow'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Record every page view for attribution, not just form interactions.', 'formflow'); ?>
                </p>
                <br>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][visitor_analytics][use_fingerprinting]" value="1"
                           <?php checked($settings['use_fingerprinting'] ?? false); ?>>
                    <?php esc_html_e('Use browser fingerprinting', 'formflow'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Helps identify visitors who clear cookies. May have privacy implications.', 'formflow'); ?>
                </p>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="visitor_cookie_days"><?php esc_html_e('Cookie Duration', 'formflow'); ?></label>
        </th>
        <td>
            <input type="number" name="settings[features][visitor_analytics][visitor_cookie_days]" id="visitor_cookie_days"
                   value="<?php echo esc_attr($settings['visitor_cookie_days'] ?? 365); ?>"
                   min="1" max="730" class="small-text">
            <?php esc_html_e('days', 'formflow'); ?>
            <p class="description">
                <?php esc_html_e('How long to remember visitors for cross-session attribution.', 'formflow'); ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Google Tag Manager', 'formflow'); ?></th>
        <td>
            <label class="isf-checkbox-label">
                <input type="checkbox" name="settings[features][visitor_analytics][gtm_enabled]" value="1"
                       id="gtm_enabled_feature"
                       <?php checked($settings['gtm_enabled'] ?? false); ?>>
                <?php esc_html_e('Push events to dataLayer', 'formflow'); ?>
            </label>
            <p class="description">
                <?php esc_html_e('Events will be pushed to window.dataLayer for GTM triggers.', 'formflow'); ?>
            </p>
        </td>
    </tr>
    <tr class="gtm-container-row" style="<?php echo empty($settings['gtm_enabled']) ? 'display:none;' : ''; ?>">
        <th scope="row">
            <label for="gtm_container_id_feature"><?php esc_html_e('GTM Container ID', 'formflow'); ?></label>
        </th>
        <td>
            <input type="text" name="settings[features][visitor_analytics][gtm_container_id]" id="gtm_container_id_feature"
                   value="<?php echo esc_attr($settings['gtm_container_id'] ?? ''); ?>"
                   placeholder="GTM-XXXXXXX" class="regular-text">
            <p class="description">
                <?php esc_html_e('Optional. Leave blank if your theme already loads GTM.', 'formflow'); ?>
            </p>
        </td>
    </tr>
</table>

<p class="description" style="margin-top: 15px;">
    <a href="<?php echo esc_url(admin_url('admin.php?page=isf-analytics-settings')); ?>" class="button button-secondary">
        <?php esc_html_e('Global Analytics Settings', 'formflow'); ?>
    </a>
    <span style="margin-left: 10px;">
        <?php esc_html_e('Configure global settings like GA4 and Microsoft Clarity.', 'formflow'); ?>
    </span>
</p>

<script>
jQuery(function($) {
    $('#gtm_enabled_feature').on('change', function() {
        $('.gtm-container-row').toggle($(this).is(':checked'));
    });
});
</script>
