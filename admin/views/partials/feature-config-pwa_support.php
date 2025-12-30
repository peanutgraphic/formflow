<?php
/**
 * PWA Support Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['pwa_support'] ?? [];
?>

<table class="form-table isf-feature-config-table">
    <tr>
        <th scope="row">
            <label for="pwa_app_name"><?php esc_html_e('App Name', 'formflow'); ?></label>
        </th>
        <td>
            <input type="text" id="pwa_app_name" name="settings[features][pwa_support][app_name]"
                   class="regular-text" value="<?php echo esc_attr($settings['app_name'] ?? 'EnergyWise Enrollment'); ?>">
            <p class="description"><?php esc_html_e('Full name shown when app is installed', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="pwa_short_name"><?php esc_html_e('Short Name', 'formflow'); ?></label>
        </th>
        <td>
            <input type="text" id="pwa_short_name" name="settings[features][pwa_support][app_short_name]"
                   class="regular-text" maxlength="12" value="<?php echo esc_attr($settings['app_short_name'] ?? 'EnergyWise'); ?>">
            <p class="description"><?php esc_html_e('Short name for home screen (max 12 characters)', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="pwa_theme_color"><?php esc_html_e('Theme Color', 'formflow'); ?></label>
        </th>
        <td>
            <input type="color" id="pwa_theme_color" name="settings[features][pwa_support][theme_color]"
                   value="<?php echo esc_attr($settings['theme_color'] ?? '#0073aa'); ?>">
            <p class="description"><?php esc_html_e('Browser theme and status bar color', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="pwa_bg_color"><?php esc_html_e('Background Color', 'formflow'); ?></label>
        </th>
        <td>
            <input type="color" id="pwa_bg_color" name="settings[features][pwa_support][background_color]"
                   value="<?php echo esc_attr($settings['background_color'] ?? '#ffffff'); ?>">
            <p class="description"><?php esc_html_e('Splash screen background color', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Offline Support', 'formflow'); ?></th>
        <td>
            <fieldset>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][pwa_support][enable_offline]" value="1"
                           <?php checked($settings['enable_offline'] ?? true); ?>>
                    <?php esc_html_e('Enable offline mode', 'formflow'); ?>
                </label>
                <br>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][pwa_support][cache_forms]" value="1"
                           <?php checked($settings['cache_forms'] ?? true); ?>>
                    <?php esc_html_e('Cache form assets for offline use', 'formflow'); ?>
                </label>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Install Prompt', 'formflow'); ?></th>
        <td>
            <label class="isf-checkbox-label">
                <input type="checkbox" name="settings[features][pwa_support][show_install_prompt]" value="1"
                       <?php checked($settings['show_install_prompt'] ?? true); ?>>
                <?php esc_html_e('Show "Add to Home Screen" prompt', 'formflow'); ?>
            </label>
            <p class="description"><?php esc_html_e('Displays a banner prompting users to install the app', 'formflow'); ?></p>
        </td>
    </tr>
</table>

<div class="isf-info-box">
    <p><strong><?php esc_html_e('PWA Features:', 'formflow'); ?></strong></p>
    <ul>
        <li><?php esc_html_e('Installable on mobile home screens', 'formflow'); ?></li>
        <li><?php esc_html_e('Works offline with cached form data', 'formflow'); ?></li>
        <li><?php esc_html_e('Native app-like experience', 'formflow'); ?></li>
        <li><?php esc_html_e('Background sync for submissions', 'formflow'); ?></li>
    </ul>
</div>
