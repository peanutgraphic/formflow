<?php
/**
 * Handoff Tracking Feature Configuration
 *
 * Configure external enrollment redirects with attribution tracking.
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['handoff_tracking'] ?? [];
?>

<div class="isf-handoff-notice notice notice-info inline" style="margin: 10px 0;">
    <p>
        <strong><?php esc_html_e('External Handoff Mode', 'formflow'); ?></strong><br>
        <?php esc_html_e('When enabled, this form instance will redirect visitors to an external enrollment URL instead of using the built-in form. Attribution data is preserved across the redirect.', 'formflow'); ?>
    </p>
</div>

<table class="form-table isf-feature-config-table">
    <tr>
        <th scope="row">
            <label for="handoff_destination_url"><?php esc_html_e('Destination URL', 'formflow'); ?></label>
        </th>
        <td>
            <input type="url" name="settings[features][handoff_tracking][destination_url]" id="handoff_destination_url"
                   value="<?php echo esc_attr($settings['destination_url'] ?? ''); ?>"
                   placeholder="https://intellisource.example.com/enroll"
                   class="large-text">
            <p class="description">
                <?php esc_html_e('The external URL where visitors will be redirected to complete enrollment.', 'formflow'); ?>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('URL Parameters', 'formflow'); ?></th>
        <td>
            <fieldset>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][handoff_tracking][append_account_param]" value="1"
                           <?php checked($settings['append_account_param'] ?? true); ?>>
                    <?php esc_html_e('Append account number (if known)', 'formflow'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Adds ?account=XXXXX if the visitor has already entered their account.', 'formflow'); ?>
                </p>
                <br>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][handoff_tracking][append_utm_params]" value="1"
                           <?php checked($settings['append_utm_params'] ?? true); ?>>
                    <?php esc_html_e('Forward UTM parameters', 'formflow'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Passes along utm_source, utm_medium, utm_campaign, etc.', 'formflow'); ?>
                </p>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Interstitial Page', 'formflow'); ?></th>
        <td>
            <label class="isf-checkbox-label">
                <input type="checkbox" name="settings[features][handoff_tracking][show_interstitial]" value="1"
                       id="handoff_show_interstitial"
                       <?php checked($settings['show_interstitial'] ?? false); ?>>
                <?php esc_html_e('Show brief interstitial before redirect', 'formflow'); ?>
            </label>
            <p class="description">
                <?php esc_html_e('Displays a message for 2-3 seconds before redirecting.', 'formflow'); ?>
            </p>
        </td>
    </tr>
    <tr class="interstitial-message-row" style="<?php echo empty($settings['show_interstitial']) ? 'display:none;' : ''; ?>">
        <th scope="row">
            <label for="handoff_interstitial_message"><?php esc_html_e('Interstitial Message', 'formflow'); ?></label>
        </th>
        <td>
            <textarea name="settings[features][handoff_tracking][interstitial_message]" id="handoff_interstitial_message"
                      class="large-text" rows="2"
                      placeholder="<?php esc_attr_e('Redirecting you to complete your enrollment...', 'formflow'); ?>"><?php echo esc_textarea($settings['interstitial_message'] ?? ''); ?></textarea>
        </td>
    </tr>
</table>

<div class="isf-handoff-shortcode" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
    <h4 style="margin-top: 0;"><?php esc_html_e('Usage', 'formflow'); ?></h4>
    <p><?php esc_html_e('Use the enroll button shortcode to create tracked links:', 'formflow'); ?></p>
    <code style="display: block; padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 3px;">
        [isf_enroll_button instance="<?php echo esc_attr($instance['slug'] ?? 'your-instance'); ?>"]
    </code>
    <p class="description" style="margin-top: 10px;">
        <?php esc_html_e('This will automatically use the configured handoff URL when External Handoff is enabled.', 'formflow'); ?>
    </p>
</div>

<script>
jQuery(function($) {
    $('#handoff_show_interstitial').on('change', function() {
        $('.interstitial-message-row').toggle($(this).is(':checked'));
    });
});
</script>
