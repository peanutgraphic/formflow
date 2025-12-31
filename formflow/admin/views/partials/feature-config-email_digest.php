<?php
/**
 * Email Digest Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['email_digest'] ?? [];
?>

<table class="form-table isf-feature-config-table">
    <tr>
        <th scope="row">
            <label for="digest_frequency"><?php esc_html_e('Frequency', 'formflow'); ?></label>
        </th>
        <td>
            <select id="digest_frequency" name="settings[features][email_digest][frequency]">
                <option value="daily" <?php selected($settings['frequency'] ?? 'daily', 'daily'); ?>>
                    <?php esc_html_e('Daily', 'formflow'); ?>
                </option>
                <option value="weekly" <?php selected($settings['frequency'] ?? 'daily', 'weekly'); ?>>
                    <?php esc_html_e('Weekly (Mondays)', 'formflow'); ?>
                </option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="digest_recipients"><?php esc_html_e('Recipients', 'formflow'); ?></label>
        </th>
        <td>
            <input type="text" id="digest_recipients" name="settings[features][email_digest][recipients]"
                   class="large-text" value="<?php echo esc_attr($settings['recipients'] ?? ''); ?>"
                   placeholder="admin@example.com, manager@example.com">
            <p class="description"><?php esc_html_e('Comma-separated list of email addresses', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="digest_send_time"><?php esc_html_e('Send Time', 'formflow'); ?></label>
        </th>
        <td>
            <input type="time" id="digest_send_time" name="settings[features][email_digest][send_time]"
                   value="<?php echo esc_attr($settings['send_time'] ?? '08:00'); ?>">
            <p class="description"><?php esc_html_e('Time of day to send the digest (server timezone)', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Include Comparison', 'formflow'); ?></th>
        <td>
            <label class="isf-checkbox-label">
                <input type="checkbox" name="settings[features][email_digest][include_comparison]" value="1"
                       <?php checked($settings['include_comparison'] ?? true); ?>>
                <?php esc_html_e('Show comparison to previous period', 'formflow'); ?>
            </label>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Test Digest', 'formflow'); ?></th>
        <td>
            <input type="email" id="digest_test_email" class="regular-text"
                   placeholder="<?php esc_attr_e('your@email.com', 'formflow'); ?>">
            <button type="button" class="button" id="isf-test-digest">
                <?php esc_html_e('Send Test Digest', 'formflow'); ?>
            </button>
            <span id="isf-digest-test-result"></span>
        </td>
    </tr>
</table>
