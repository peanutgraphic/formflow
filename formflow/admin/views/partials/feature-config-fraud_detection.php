<?php
/**
 * Fraud Detection Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['fraud_detection'] ?? [];
?>

<table class="form-table isf-feature-config-table">
    <tr>
        <th scope="row"><?php esc_html_e('Detection Checks', 'formflow'); ?></th>
        <td>
            <fieldset>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][fraud_detection][check_duplicate_accounts]" value="1"
                           <?php checked($settings['check_duplicate_accounts'] ?? true); ?>>
                    <?php esc_html_e('Check for duplicate account submissions', 'formflow'); ?>
                </label>
                <br>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][fraud_detection][check_ip_velocity]" value="1"
                           <?php checked($settings['check_ip_velocity'] ?? true); ?>>
                    <?php esc_html_e('Check IP submission velocity', 'formflow'); ?>
                </label>
                <br>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][fraud_detection][check_device_fingerprint]" value="1"
                           <?php checked($settings['check_device_fingerprint'] ?? true); ?>>
                    <?php esc_html_e('Check device fingerprint', 'formflow'); ?>
                </label>
                <br>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][fraud_detection][check_email_domain]" value="1"
                           <?php checked($settings['check_email_domain'] ?? true); ?>>
                    <?php esc_html_e('Check for disposable email domains', 'formflow'); ?>
                </label>
            </fieldset>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="fraud_ip_threshold"><?php esc_html_e('IP Threshold', 'formflow'); ?></label>
        </th>
        <td>
            <input type="number" id="fraud_ip_threshold" name="settings[features][fraud_detection][ip_threshold_per_hour]"
                   class="small-text" min="1" max="100" value="<?php echo esc_attr($settings['ip_threshold_per_hour'] ?? 5); ?>">
            <?php esc_html_e('submissions per hour per IP', 'formflow'); ?>
            <p class="description"><?php esc_html_e('Flag if more submissions than this from same IP', 'formflow'); ?></p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="fraud_risk_threshold"><?php esc_html_e('Risk Score Threshold', 'formflow'); ?></label>
        </th>
        <td>
            <input type="number" id="fraud_risk_threshold" name="settings[features][fraud_detection][risk_score_threshold]"
                   class="small-text" min="0" max="100" value="<?php echo esc_attr($settings['risk_score_threshold'] ?? 70); ?>">
            <span>/100</span>
            <p class="description"><?php esc_html_e('Submissions above this score trigger the configured action', 'formflow'); ?></p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="fraud_action"><?php esc_html_e('Action on High Risk', 'formflow'); ?></label>
        </th>
        <td>
            <select id="fraud_action" name="settings[features][fraud_detection][action_on_high_risk]">
                <option value="flag" <?php selected($settings['action_on_high_risk'] ?? 'flag', 'flag'); ?>>
                    <?php esc_html_e('Flag for Review (allow submission)', 'formflow'); ?>
                </option>
                <option value="block" <?php selected($settings['action_on_high_risk'] ?? 'flag', 'block'); ?>>
                    <?php esc_html_e('Block Submission', 'formflow'); ?>
                </option>
                <option value="challenge" <?php selected($settings['action_on_high_risk'] ?? 'flag', 'challenge'); ?>>
                    <?php esc_html_e('Show CAPTCHA Challenge', 'formflow'); ?>
                </option>
            </select>
        </td>
    </tr>

    <tr>
        <th scope="row"><?php esc_html_e('Notifications', 'formflow'); ?></th>
        <td>
            <label class="isf-checkbox-label">
                <input type="checkbox" name="settings[features][fraud_detection][notify_on_fraud]" value="1"
                       <?php checked($settings['notify_on_fraud'] ?? true); ?>>
                <?php esc_html_e('Send notification when high-risk submission detected', 'formflow'); ?>
            </label>
            <p class="description"><?php esc_html_e('Uses Team Notifications if enabled, otherwise sends email', 'formflow'); ?></p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="fraud_blocked_domains"><?php esc_html_e('Blocked Email Domains', 'formflow'); ?></label>
        </th>
        <td>
            <?php
            $blocked = $settings['blocked_email_domains'] ?? [];
            if (is_array($blocked)) {
                $blocked = implode("\n", $blocked);
            }
            ?>
            <textarea id="fraud_blocked_domains" name="settings[features][fraud_detection][blocked_email_domains]"
                      class="regular-text" rows="4" placeholder="tempmail.com&#10;fakeemail.com"><?php echo esc_textarea($blocked); ?></textarea>
            <p class="description"><?php esc_html_e('One domain per line. In addition to built-in disposable email list.', 'formflow'); ?></p>
        </td>
    </tr>
</table>

<div class="isf-info-box">
    <p><strong><?php esc_html_e('Risk Score Calculation:', 'formflow'); ?></strong></p>
    <ul>
        <li><strong><?php esc_html_e('Duplicate Account:', 'formflow'); ?></strong> +30 <?php esc_html_e('points', 'formflow'); ?></li>
        <li><strong><?php esc_html_e('IP Velocity:', 'formflow'); ?></strong> +25 <?php esc_html_e('points', 'formflow'); ?></li>
        <li><strong><?php esc_html_e('Suspicious Fingerprint:', 'formflow'); ?></strong> +20 <?php esc_html_e('points', 'formflow'); ?></li>
        <li><strong><?php esc_html_e('VPN/Proxy Detected:', 'formflow'); ?></strong> +20 <?php esc_html_e('points', 'formflow'); ?></li>
        <li><strong><?php esc_html_e('Disposable Email:', 'formflow'); ?></strong> +15 <?php esc_html_e('points', 'formflow'); ?></li>
        <li><strong><?php esc_html_e('Data Inconsistency:', 'formflow'); ?></strong> +15 <?php esc_html_e('points', 'formflow'); ?></li>
        <li><strong><?php esc_html_e('Bot Behavior:', 'formflow'); ?></strong> +25 <?php esc_html_e('points', 'formflow'); ?></li>
    </ul>
    <p><?php esc_html_e('Scores are multiplied by severity when multiple triggers are detected.', 'formflow'); ?></p>
</div>
