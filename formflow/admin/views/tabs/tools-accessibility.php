<?php
/**
 * Tools Tab: Accessibility (ADA/WCAG Compliance)
 *
 * @package FormFlow
 */

if (!defined('ABSPATH')) {
    exit;
}

$accessibility = \ISF\Accessibility::instance();
$settings = $accessibility->get_setting('enabled') ? $accessibility : null;

// Handle form submission
if (isset($_POST['isf_save_accessibility']) && check_admin_referer('isf_accessibility_nonce')) {
    $new_settings = [
        'enabled' => !empty($_POST['accessibility']['enabled']),
        'skip_links' => !empty($_POST['accessibility']['skip_links']),
        'focus_indicators' => !empty($_POST['accessibility']['focus_indicators']),
        'aria_live_regions' => !empty($_POST['accessibility']['aria_live_regions']),
        'high_contrast_mode' => !empty($_POST['accessibility']['high_contrast_mode']),
        'large_text_mode' => !empty($_POST['accessibility']['large_text_mode']),
        'reduce_motion' => !empty($_POST['accessibility']['reduce_motion']),
        'keyboard_navigation' => !empty($_POST['accessibility']['keyboard_navigation']),
        'screen_reader_hints' => !empty($_POST['accessibility']['screen_reader_hints']),
        'error_announcements' => !empty($_POST['accessibility']['error_announcements']),
        'progress_announcements' => !empty($_POST['accessibility']['progress_announcements']),
        'minimum_touch_target' => intval($_POST['accessibility']['minimum_touch_target'] ?? 44),
        'focus_visible_style' => sanitize_text_field($_POST['accessibility']['focus_visible_style'] ?? 'outline'),
    ];

    $accessibility->update_settings($new_settings);
    add_settings_error('isf_settings', 'accessibility_saved', __('Accessibility settings saved.', 'formflow'), 'success');
}

settings_errors('isf_settings');

$current = [
    'enabled' => $accessibility->get_setting('enabled', true),
    'skip_links' => $accessibility->get_setting('skip_links', true),
    'focus_indicators' => $accessibility->get_setting('focus_indicators', true),
    'aria_live_regions' => $accessibility->get_setting('aria_live_regions', true),
    'high_contrast_mode' => $accessibility->get_setting('high_contrast_mode', false),
    'large_text_mode' => $accessibility->get_setting('large_text_mode', false),
    'reduce_motion' => $accessibility->get_setting('reduce_motion', false),
    'keyboard_navigation' => $accessibility->get_setting('keyboard_navigation', true),
    'screen_reader_hints' => $accessibility->get_setting('screen_reader_hints', true),
    'error_announcements' => $accessibility->get_setting('error_announcements', true),
    'progress_announcements' => $accessibility->get_setting('progress_announcements', true),
    'minimum_touch_target' => $accessibility->get_setting('minimum_touch_target', 44),
    'focus_visible_style' => $accessibility->get_setting('focus_visible_style', 'outline'),
];
?>

<!-- WCAG Compliance Status -->
<div class="isf-card">
    <h2>
        <span class="dashicons dashicons-universal-access-alt" style="color: #2271b1;"></span>
        <?php esc_html_e('WCAG 2.1 AA Compliance Status', 'formflow'); ?>
    </h2>

    <?php $compliance = $accessibility->get_compliance_status(); ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px; margin-top: 15px;">
        <?php foreach ($compliance as $key => $check) :
            $status_color = $check['status'] === 'pass' ? '#00a32a' : ($check['status'] === 'warning' ? '#dba617' : '#d63638');
            $status_icon = $check['status'] === 'pass' ? 'yes-alt' : ($check['status'] === 'warning' ? 'warning' : 'dismiss');
        ?>
        <div style="display: flex; align-items: flex-start; gap: 10px; padding: 12px; background: #f9f9f9; border-radius: 4px; border-left: 3px solid <?php echo $status_color; ?>;">
            <span class="dashicons dashicons-<?php echo $status_icon; ?>" style="color: <?php echo $status_color; ?>; margin-top: 2px;"></span>
            <div>
                <strong style="display: block; margin-bottom: 3px;"><?php echo esc_html($check['name']); ?></strong>
                <span style="font-size: 12px; color: #646970;"><?php echo esc_html($check['description']); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<form method="post">
    <?php wp_nonce_field('isf_accessibility_nonce'); ?>

    <div class="isf-card">
        <h2><?php esc_html_e('Accessibility Features', 'formflow'); ?></h2>
        <p class="description">
            <?php esc_html_e('Configure accessibility features to ensure forms comply with ADA requirements and WCAG 2.1 AA guidelines.', 'formflow'); ?>
        </p>

        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Enable Accessibility', 'formflow'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="accessibility[enabled]" value="1" <?php checked($current['enabled']); ?>>
                        <?php esc_html_e('Enable all accessibility features', 'formflow'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Master switch for WCAG 2.1 AA compliance features.', 'formflow'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="isf-card">
        <h2><?php esc_html_e('Visual Accessibility', 'formflow'); ?></h2>

        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Focus Indicators', 'formflow'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="accessibility[focus_indicators]" value="1" <?php checked($current['focus_indicators']); ?>>
                        <?php esc_html_e('Enhanced focus indicators for keyboard users', 'formflow'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Shows clear visual focus on form elements when navigating with keyboard.', 'formflow'); ?>
                    </p>

                    <div style="margin-top: 10px;">
                        <label for="focus_style"><?php esc_html_e('Focus style:', 'formflow'); ?></label>
                        <select name="accessibility[focus_visible_style]" id="focus_style">
                            <option value="outline" <?php selected($current['focus_visible_style'], 'outline'); ?>><?php esc_html_e('Outline (default)', 'formflow'); ?></option>
                            <option value="ring" <?php selected($current['focus_visible_style'], 'ring'); ?>><?php esc_html_e('Ring/Glow', 'formflow'); ?></option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('High Contrast Mode', 'formflow'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="accessibility[high_contrast_mode]" value="1" <?php checked($current['high_contrast_mode']); ?>>
                        <?php esc_html_e('Enable high contrast colors', 'formflow'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Increases color contrast for users with low vision. Meets WCAG 4.5:1 contrast ratio.', 'formflow'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Large Text Mode', 'formflow'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="accessibility[large_text_mode]" value="1" <?php checked($current['large_text_mode']); ?>>
                        <?php esc_html_e('Increase font sizes', 'formflow'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Increases text size by 25% for easier reading.', 'formflow'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Reduce Motion', 'formflow'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="accessibility[reduce_motion]" value="1" <?php checked($current['reduce_motion']); ?>>
                        <?php esc_html_e('Disable animations and transitions', 'formflow'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('For users with vestibular disorders. Also respects OS-level "prefers-reduced-motion" setting.', 'formflow'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Touch Target Size', 'formflow'); ?></th>
                <td>
                    <input type="number" name="accessibility[minimum_touch_target]" value="<?php echo esc_attr($current['minimum_touch_target']); ?>" min="24" max="64" class="small-text"> px
                    <p class="description">
                        <?php esc_html_e('Minimum size for touch targets. WCAG 2.5.5 requires 44px minimum.', 'formflow'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="isf-card">
        <h2><?php esc_html_e('Screen Reader Support', 'formflow'); ?></h2>

        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('ARIA Live Regions', 'formflow'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="accessibility[aria_live_regions]" value="1" <?php checked($current['aria_live_regions']); ?>>
                        <?php esc_html_e('Enable live regions for dynamic announcements', 'formflow'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Announces form changes, errors, and progress to screen readers automatically.', 'formflow'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Screen Reader Hints', 'formflow'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="accessibility[screen_reader_hints]" value="1" <?php checked($current['screen_reader_hints']); ?>>
                        <?php esc_html_e('Add descriptive hints for screen reader users', 'formflow'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Provides additional context and instructions visible only to screen readers.', 'formflow'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Error Announcements', 'formflow'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="accessibility[error_announcements]" value="1" <?php checked($current['error_announcements']); ?>>
                        <?php esc_html_e('Announce validation errors to screen readers', 'formflow'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Immediately announces form errors so users know to correct them.', 'formflow'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Progress Announcements', 'formflow'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="accessibility[progress_announcements]" value="1" <?php checked($current['progress_announcements']); ?>>
                        <?php esc_html_e('Announce step progress changes', 'formflow'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Announces "Step X of Y" when navigating multi-step forms.', 'formflow'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="isf-card">
        <h2><?php esc_html_e('Keyboard Navigation', 'formflow'); ?></h2>

        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Skip Links', 'formflow'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="accessibility[skip_links]" value="1" <?php checked($current['skip_links']); ?>>
                        <?php esc_html_e('Add skip-to-form links', 'formflow'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Allows keyboard users to skip directly to the form content.', 'formflow'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Enhanced Navigation', 'formflow'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="accessibility[keyboard_navigation]" value="1" <?php checked($current['keyboard_navigation']); ?>>
                        <?php esc_html_e('Enhanced keyboard navigation', 'formflow'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('Additional keyboard shortcuts for navigating forms.', 'formflow'); ?>
                    </p>
                    <div style="margin-top: 10px; padding: 10px; background: #f0f0f1; border-radius: 4px; font-size: 13px;">
                        <strong><?php esc_html_e('Keyboard Shortcuts:', 'formflow'); ?></strong>
                        <ul style="margin: 5px 0 0 20px;">
                            <li><kbd>Tab</kbd> / <kbd>Shift+Tab</kbd> — <?php esc_html_e('Navigate between fields', 'formflow'); ?></li>
                            <li><kbd>Enter</kbd> — <?php esc_html_e('Move to next field', 'formflow'); ?></li>
                            <li><kbd>Alt+←</kbd> / <kbd>Alt+→</kbd> — <?php esc_html_e('Previous/Next step', 'formflow'); ?></li>
                            <li><kbd>Alt+H</kbd> — <?php esc_html_e('Show keyboard help', 'formflow'); ?></li>
                        </ul>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Info Box -->
    <div class="isf-card" style="background: linear-gradient(135deg, #e7f3ff 0%, #f0f6fc 100%); border-color: #2271b1;">
        <h3 style="margin-top: 0; display: flex; align-items: center; gap: 8px;">
            <span class="dashicons dashicons-info" style="color: #2271b1;"></span>
            <?php esc_html_e('About ADA & WCAG Compliance', 'formflow'); ?>
        </h3>
        <p>
            <?php esc_html_e('FormFlow implements WCAG 2.1 Level AA guidelines to help ensure your forms are accessible to users with disabilities. This includes:', 'formflow'); ?>
        </p>
        <ul style="margin-left: 20px;">
            <li><?php esc_html_e('Perceivable: Text alternatives, color contrast, resizable text', 'formflow'); ?></li>
            <li><?php esc_html_e('Operable: Keyboard accessible, skip links, no seizure-inducing content', 'formflow'); ?></li>
            <li><?php esc_html_e('Understandable: Readable text, predictable behavior, error handling', 'formflow'); ?></li>
            <li><?php esc_html_e('Robust: Compatible with assistive technologies', 'formflow'); ?></li>
        </ul>
        <p style="margin-bottom: 0; font-size: 13px; color: #646970;">
            <?php
            printf(
                esc_html__('Learn more about %sWCAG 2.1 guidelines%s and %sADA requirements%s.', 'formflow'),
                '<a href="https://www.w3.org/WAI/WCAG21/quickref/" target="_blank">',
                '</a>',
                '<a href="https://www.ada.gov/resources/web-guidance/" target="_blank">',
                '</a>'
            );
            ?>
        </p>
    </div>

    <p class="submit">
        <input type="submit" name="isf_save_accessibility" class="button button-primary" value="<?php esc_attr_e('Save Accessibility Settings', 'formflow'); ?>">
    </p>
</form>

<style>
kbd {
    background: #f0f0f1;
    border: 1px solid #ccc;
    border-radius: 3px;
    padding: 2px 6px;
    font-family: monospace;
    font-size: 12px;
}
</style>
