<?php
/**
 * Calendar Integration Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['calendar_integration'] ?? [];
$providers = \ISF\CalendarIntegration::get_providers();
?>

<table class="form-table isf-feature-config-table">
    <tr>
        <th scope="row">
            <label for="calendar_provider"><?php esc_html_e('Calendar Provider', 'formflow'); ?></label>
        </th>
        <td>
            <select id="calendar_provider" name="settings[features][calendar_integration][provider]">
                <?php foreach ($providers as $key => $provider): ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($settings['provider'] ?? 'google', $key); ?>>
                        <?php echo esc_html($provider['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description">
                <?php esc_html_e('iCal generates downloadable files; Google/Outlook create events automatically.', 'formflow'); ?>
            </p>
        </td>
    </tr>

    <tr class="isf-calendar-oauth">
        <th scope="row">
            <label for="calendar_id"><?php esc_html_e('Calendar ID', 'formflow'); ?></label>
        </th>
        <td>
            <input type="text" id="calendar_id" name="settings[features][calendar_integration][calendar_id]"
                   class="regular-text" value="<?php echo esc_attr($settings['calendar_id'] ?? ''); ?>"
                   placeholder="primary">
            <p class="description"><?php esc_html_e('Leave blank to use primary calendar', 'formflow'); ?></p>
        </td>
    </tr>

    <tr class="isf-calendar-oauth">
        <th scope="row">
            <label for="calendar_credentials"><?php esc_html_e('API Credentials', 'formflow'); ?></label>
        </th>
        <td>
            <textarea id="calendar_credentials" name="settings[features][calendar_integration][api_credentials]"
                      class="large-text" rows="4" placeholder='{"client_id":"...","client_secret":"...","refresh_token":"..."}'><?php echo esc_textarea($settings['api_credentials'] ?? ''); ?></textarea>
            <p class="description">
                <?php esc_html_e('JSON credentials from Google Cloud Console or Azure AD. Will be encrypted.', 'formflow'); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row"><?php esc_html_e('Event Settings', 'formflow'); ?></th>
        <td>
            <fieldset>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][calendar_integration][create_events]" value="1"
                           <?php checked($settings['create_events'] ?? true); ?>>
                    <?php esc_html_e('Create calendar events for appointments', 'formflow'); ?>
                </label>
                <br>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][calendar_integration][send_invites]" value="1"
                           <?php checked($settings['send_invites'] ?? false); ?>>
                    <?php esc_html_e('Send calendar invites to customers', 'formflow'); ?>
                </label>
            </fieldset>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="calendar_title_template"><?php esc_html_e('Event Title', 'formflow'); ?></label>
        </th>
        <td>
            <input type="text" id="calendar_title_template" name="settings[features][calendar_integration][event_title_template]"
                   class="large-text" value="<?php echo esc_attr($settings['event_title_template'] ?? '{program_name} - {customer_name}'); ?>">
            <p class="description">
                <?php esc_html_e('Available variables: {program_name}, {customer_name}, {device_type}', 'formflow'); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="calendar_description"><?php esc_html_e('Event Description', 'formflow'); ?></label>
        </th>
        <td>
            <textarea id="calendar_description" name="settings[features][calendar_integration][event_description_template]"
                      class="large-text" rows="4"><?php echo esc_textarea($settings['event_description_template'] ?? ''); ?></textarea>
            <p class="description">
                <?php esc_html_e('Leave blank for default description with customer details.', 'formflow'); ?>
            </p>
        </td>
    </tr>
</table>

<div class="isf-info-box">
    <p><strong><?php esc_html_e('Setup Instructions:', 'formflow'); ?></strong></p>
    <p><strong>Google Calendar:</strong></p>
    <ol>
        <li><?php esc_html_e('Go to Google Cloud Console', 'formflow'); ?></li>
        <li><?php esc_html_e('Create OAuth 2.0 credentials', 'formflow'); ?></li>
        <li><?php esc_html_e('Enable Google Calendar API', 'formflow'); ?></li>
        <li><?php esc_html_e('Generate refresh token and paste JSON credentials above', 'formflow'); ?></li>
    </ol>
    <p><strong>Microsoft Outlook:</strong></p>
    <ol>
        <li><?php esc_html_e('Register app in Azure AD', 'formflow'); ?></li>
        <li><?php esc_html_e('Add Calendars.ReadWrite permission', 'formflow'); ?></li>
        <li><?php esc_html_e('Generate refresh token and paste JSON credentials above', 'formflow'); ?></li>
    </ol>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var providerSelect = document.getElementById('calendar_provider');

    function updateFields() {
        var needsOauth = providerSelect.value !== 'ical';
        document.querySelectorAll('.isf-calendar-oauth').forEach(function(row) {
            row.style.display = needsOauth ? '' : 'none';
        });
    }

    providerSelect.addEventListener('change', updateFields);
    updateFields();
});
</script>
