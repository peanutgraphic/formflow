<?php
/**
 * Accessibility (ADA Compliance)
 *
 * Implements WCAG 2.1 AA compliance features for forms.
 *
 * @package FormFlow
 */

namespace ISF;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Accessibility
 *
 * Provides ADA/WCAG 2.1 AA compliance features.
 */
class Accessibility {

    /**
     * Singleton instance
     */
    private static ?Accessibility $instance = null;

    /**
     * Accessibility settings
     */
    private array $settings = [];

    /**
     * Get singleton instance
     */
    public static function instance(): Accessibility {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct() {
        $this->settings = get_option('isf_accessibility_settings', $this->get_default_settings());
    }

    /**
     * Initialize accessibility hooks
     */
    public function init(): void {
        // Add accessibility attributes to form fields
        add_filter('isf_field_attributes', [$this, 'add_aria_attributes'], 10, 3);

        // Add skip links
        add_action('isf_before_form', [$this, 'render_skip_link']);

        // Add live region for announcements
        add_action('isf_after_form', [$this, 'render_live_region']);

        // Enqueue accessibility scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_accessibility_assets']);

        // Add accessibility info to admin
        add_action('isf_admin_form_settings', [$this, 'render_accessibility_settings']);
    }

    /**
     * Get default accessibility settings
     */
    public function get_default_settings(): array {
        return [
            'enabled' => true,
            'skip_links' => true,
            'focus_indicators' => true,
            'aria_live_regions' => true,
            'high_contrast_mode' => false,
            'large_text_mode' => false,
            'reduce_motion' => false,
            'keyboard_navigation' => true,
            'screen_reader_hints' => true,
            'error_announcements' => true,
            'progress_announcements' => true,
            'minimum_touch_target' => 44, // pixels (WCAG 2.5.5)
            'focus_visible_style' => 'outline', // outline, ring, or custom
            'color_contrast_ratio' => 4.5, // WCAG AA standard
        ];
    }

    /**
     * Check if accessibility features are enabled
     */
    public function is_enabled(): bool {
        return !empty($this->settings['enabled']);
    }

    /**
     * Get a specific setting
     */
    public function get_setting(string $key, $default = null) {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Update settings
     */
    public function update_settings(array $settings): bool {
        $this->settings = array_merge($this->get_default_settings(), $settings);
        return update_option('isf_accessibility_settings', $this->settings);
    }

    /**
     * Add ARIA attributes to form fields
     */
    public function add_aria_attributes(array $attributes, string $field_type, array $field): array {
        if (!$this->is_enabled()) {
            return $attributes;
        }

        // Add aria-label if no label element
        if (empty($attributes['aria-label']) && !empty($field['label'])) {
            $attributes['aria-label'] = $field['label'];
        }

        // Add aria-required for required fields
        if (!empty($field['required'])) {
            $attributes['aria-required'] = 'true';
            $attributes['required'] = 'required';
        }

        // Add aria-invalid for fields with errors
        if (!empty($field['has_error'])) {
            $attributes['aria-invalid'] = 'true';
        }

        // Add aria-describedby for help text
        if (!empty($field['help_text'])) {
            $help_id = $field['id'] . '_help';
            $attributes['aria-describedby'] = $help_id;
        }

        // Add aria-describedby for error messages
        if (!empty($field['error_id'])) {
            $existing = $attributes['aria-describedby'] ?? '';
            $attributes['aria-describedby'] = trim($existing . ' ' . $field['error_id']);
        }

        // Add autocomplete attributes for common fields
        $autocomplete_map = [
            'first_name' => 'given-name',
            'last_name' => 'family-name',
            'email' => 'email',
            'phone' => 'tel',
            'address' => 'street-address',
            'city' => 'address-level2',
            'state' => 'address-level1',
            'zip' => 'postal-code',
            'zipcode' => 'postal-code',
            'account_number' => 'off',
        ];

        $field_name = strtolower($field['name'] ?? '');
        foreach ($autocomplete_map as $pattern => $autocomplete) {
            if (strpos($field_name, $pattern) !== false) {
                $attributes['autocomplete'] = $autocomplete;
                break;
            }
        }

        return $attributes;
    }

    /**
     * Render skip link for keyboard navigation
     */
    public function render_skip_link(array $instance): void {
        if (!$this->is_enabled() || !$this->get_setting('skip_links')) {
            return;
        }
        ?>
        <a href="#isf-form-<?php echo esc_attr($instance['id']); ?>" class="isf-skip-link">
            <?php esc_html_e('Skip to form', 'formflow'); ?>
        </a>
        <?php
    }

    /**
     * Render live region for screen reader announcements
     */
    public function render_live_region(array $instance): void {
        if (!$this->is_enabled() || !$this->get_setting('aria_live_regions')) {
            return;
        }
        ?>
        <div id="isf-live-region-<?php echo esc_attr($instance['id']); ?>"
             class="isf-sr-only"
             role="status"
             aria-live="polite"
             aria-atomic="true">
        </div>
        <div id="isf-alert-region-<?php echo esc_attr($instance['id']); ?>"
             class="isf-sr-only"
             role="alert"
             aria-live="assertive"
             aria-atomic="true">
        </div>
        <?php
    }

    /**
     * Enqueue accessibility assets
     */
    public function enqueue_accessibility_assets(): void {
        if (!$this->is_enabled()) {
            return;
        }

        wp_enqueue_script(
            'isf-accessibility',
            ISF_PLUGIN_URL . 'public/assets/js/accessibility.js',
            ['jquery'],
            ISF_VERSION,
            true
        );

        wp_localize_script('isf-accessibility', 'ISFAccessibility', [
            'enabled' => true,
            'settings' => $this->settings,
            'strings' => [
                'loading' => __('Loading, please wait...', 'formflow'),
                'step_progress' => __('Step %1$d of %2$d: %3$s', 'formflow'),
                'form_error' => __('Form has errors. Please review and correct.', 'formflow'),
                'field_error' => __('%s: %s', 'formflow'),
                'field_valid' => __('%s is valid', 'formflow'),
                'required_field' => __('This field is required', 'formflow'),
                'form_submitted' => __('Form submitted successfully', 'formflow'),
                'navigating_to_step' => __('Navigating to step %d', 'formflow'),
            ],
        ]);

        // Add inline styles for accessibility
        wp_add_inline_style('isf-forms', $this->get_accessibility_css());
    }

    /**
     * Get accessibility CSS
     */
    public function get_accessibility_css(): string {
        $css = '';

        // Screen reader only class
        $css .= '
        .isf-sr-only {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
        }

        .isf-sr-only-focusable:focus,
        .isf-sr-only-focusable:active {
            position: static !important;
            width: auto !important;
            height: auto !important;
            overflow: visible !important;
            clip: auto !important;
            white-space: normal !important;
        }
        ';

        // Skip link styles
        if ($this->get_setting('skip_links')) {
            $css .= '
            .isf-skip-link {
                position: absolute;
                top: -40px;
                left: 0;
                background: #1d2327;
                color: #fff;
                padding: 8px 16px;
                z-index: 100000;
                text-decoration: none;
                font-weight: 600;
                border-radius: 0 0 4px 0;
                transition: top 0.2s ease;
            }

            .isf-skip-link:focus {
                top: 0;
                outline: 2px solid #2271b1;
                outline-offset: 2px;
            }
            ';
        }

        // Focus indicators
        if ($this->get_setting('focus_indicators')) {
            $focus_style = $this->get_setting('focus_visible_style', 'outline');

            if ($focus_style === 'outline') {
                $css .= '
                .isf-form-container *:focus {
                    outline: 2px solid #2271b1;
                    outline-offset: 2px;
                }

                .isf-form-container *:focus:not(:focus-visible) {
                    outline: none;
                }

                .isf-form-container *:focus-visible {
                    outline: 2px solid #2271b1;
                    outline-offset: 2px;
                }
                ';
            } elseif ($focus_style === 'ring') {
                $css .= '
                .isf-form-container *:focus {
                    outline: none;
                    box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.4);
                }

                .isf-form-container *:focus:not(:focus-visible) {
                    box-shadow: none;
                }

                .isf-form-container *:focus-visible {
                    box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.4);
                }
                ';
            }
        }

        // High contrast mode
        if ($this->get_setting('high_contrast_mode')) {
            $css .= '
            .isf-form-container {
                --isf-text-color: #000;
                --isf-bg-color: #fff;
                --isf-border-color: #000;
                --isf-link-color: #0000EE;
                --isf-error-color: #c00;
                --isf-success-color: #006400;
            }

            .isf-form-container input,
            .isf-form-container select,
            .isf-form-container textarea {
                border: 2px solid #000 !important;
                background: #fff !important;
                color: #000 !important;
            }

            .isf-form-container .isf-btn {
                border: 2px solid #000 !important;
            }
            ';
        }

        // Large text mode
        if ($this->get_setting('large_text_mode')) {
            $css .= '
            .isf-form-container {
                font-size: 1.25rem !important;
            }

            .isf-form-container input,
            .isf-form-container select,
            .isf-form-container textarea {
                font-size: 1.25rem !important;
                padding: 12px 16px !important;
            }

            .isf-form-container label {
                font-size: 1.125rem !important;
            }

            .isf-form-container .isf-btn {
                font-size: 1.125rem !important;
                padding: 14px 24px !important;
            }
            ';
        }

        // Reduced motion
        if ($this->get_setting('reduce_motion')) {
            $css .= '
            .isf-form-container,
            .isf-form-container * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            ';
        }

        // Respect user preferences
        $css .= '
        @media (prefers-reduced-motion: reduce) {
            .isf-form-container,
            .isf-form-container * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        @media (prefers-contrast: high) {
            .isf-form-container input,
            .isf-form-container select,
            .isf-form-container textarea {
                border: 2px solid currentColor !important;
            }
        }
        ';

        // Minimum touch target size (WCAG 2.5.5)
        $touch_target = $this->get_setting('minimum_touch_target', 44);
        $css .= "
        .isf-form-container input[type='checkbox'],
        .isf-form-container input[type='radio'] {
            min-width: {$touch_target}px;
            min-height: {$touch_target}px;
        }

        .isf-form-container .isf-btn,
        .isf-form-container button {
            min-height: {$touch_target}px;
            min-width: {$touch_target}px;
        }

        @media (pointer: coarse) {
            .isf-form-container input,
            .isf-form-container select,
            .isf-form-container textarea {
                min-height: {$touch_target}px;
            }
        }
        ";

        // Error styling with sufficient contrast
        $css .= '
        .isf-form-container .isf-field-error {
            color: #c00;
            font-weight: 500;
        }

        .isf-form-container .isf-field-error::before {
            content: "⚠ ";
        }

        .isf-form-container input[aria-invalid="true"],
        .isf-form-container select[aria-invalid="true"],
        .isf-form-container textarea[aria-invalid="true"] {
            border-color: #c00 !important;
            box-shadow: 0 0 0 1px #c00;
        }

        .isf-form-container .isf-field-success::before {
            content: "✓ ";
            color: #006400;
        }
        ';

        return $css;
    }

    /**
     * Render accessibility settings in admin
     */
    public function render_accessibility_settings(): void {
        ?>
        <div class="isf-card">
            <h2><?php esc_html_e('Accessibility (ADA/WCAG 2.1)', 'formflow'); ?></h2>
            <p class="description">
                <?php esc_html_e('Configure accessibility features to ensure forms are usable by people with disabilities.', 'formflow'); ?>
            </p>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable Accessibility Features', 'formflow'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="accessibility[enabled]" value="1"
                                   <?php checked($this->get_setting('enabled')); ?>>
                            <?php esc_html_e('Enable WCAG 2.1 AA compliance features', 'formflow'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Visual Features', 'formflow'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="accessibility[focus_indicators]" value="1"
                                       <?php checked($this->get_setting('focus_indicators')); ?>>
                                <?php esc_html_e('Enhanced focus indicators', 'formflow'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="accessibility[high_contrast_mode]" value="1"
                                       <?php checked($this->get_setting('high_contrast_mode')); ?>>
                                <?php esc_html_e('High contrast mode', 'formflow'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="accessibility[large_text_mode]" value="1"
                                       <?php checked($this->get_setting('large_text_mode')); ?>>
                                <?php esc_html_e('Large text mode', 'formflow'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="accessibility[reduce_motion]" value="1"
                                       <?php checked($this->get_setting('reduce_motion')); ?>>
                                <?php esc_html_e('Reduce motion/animations', 'formflow'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Screen Reader Support', 'formflow'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="accessibility[aria_live_regions]" value="1"
                                       <?php checked($this->get_setting('aria_live_regions')); ?>>
                                <?php esc_html_e('ARIA live regions for announcements', 'formflow'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="accessibility[screen_reader_hints]" value="1"
                                       <?php checked($this->get_setting('screen_reader_hints')); ?>>
                                <?php esc_html_e('Screen reader hints and instructions', 'formflow'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="accessibility[error_announcements]" value="1"
                                       <?php checked($this->get_setting('error_announcements')); ?>>
                                <?php esc_html_e('Announce errors to screen readers', 'formflow'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="accessibility[progress_announcements]" value="1"
                                       <?php checked($this->get_setting('progress_announcements')); ?>>
                                <?php esc_html_e('Announce step progress', 'formflow'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Navigation', 'formflow'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="accessibility[skip_links]" value="1"
                                       <?php checked($this->get_setting('skip_links')); ?>>
                                <?php esc_html_e('Skip links for keyboard users', 'formflow'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="accessibility[keyboard_navigation]" value="1"
                                       <?php checked($this->get_setting('keyboard_navigation')); ?>>
                                <?php esc_html_e('Enhanced keyboard navigation', 'formflow'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Generate accessible form field HTML
     */
    public function render_field(array $field): string {
        $id = esc_attr($field['id'] ?? uniqid('isf_field_'));
        $name = esc_attr($field['name'] ?? '');
        $type = $field['type'] ?? 'text';
        $label = $field['label'] ?? '';
        $value = $field['value'] ?? '';
        $required = !empty($field['required']);
        $help_text = $field['help_text'] ?? '';
        $error = $field['error'] ?? '';
        $placeholder = $field['placeholder'] ?? '';

        $help_id = $id . '_help';
        $error_id = $id . '_error';

        // Build attributes
        $attrs = [
            'id' => $id,
            'name' => $name,
            'type' => $type,
        ];

        if ($required) {
            $attrs['required'] = 'required';
            $attrs['aria-required'] = 'true';
        }

        if ($placeholder) {
            $attrs['placeholder'] = $placeholder;
        }

        $describedby = [];
        if ($help_text) {
            $describedby[] = $help_id;
        }
        if ($error) {
            $describedby[] = $error_id;
            $attrs['aria-invalid'] = 'true';
        }
        if ($describedby) {
            $attrs['aria-describedby'] = implode(' ', $describedby);
        }

        // Apply filters
        $attrs = apply_filters('isf_field_attributes', $attrs, $type, $field);

        // Build attribute string
        $attr_string = '';
        foreach ($attrs as $key => $val) {
            $attr_string .= ' ' . esc_attr($key) . '="' . esc_attr($val) . '"';
        }

        ob_start();
        ?>
        <div class="isf-field-wrapper" data-field="<?php echo esc_attr($name); ?>">
            <label for="<?php echo $id; ?>" class="isf-label">
                <?php echo esc_html($label); ?>
                <?php if ($required) : ?>
                    <span class="isf-required" aria-hidden="true">*</span>
                    <span class="isf-sr-only"><?php esc_html_e('(required)', 'formflow'); ?></span>
                <?php endif; ?>
            </label>

            <?php if ($type === 'textarea') : ?>
                <textarea<?php echo $attr_string; ?>><?php echo esc_textarea($value); ?></textarea>
            <?php elseif ($type === 'select') : ?>
                <select<?php echo $attr_string; ?>>
                    <?php foreach ($field['options'] ?? [] as $opt_value => $opt_label) : ?>
                        <option value="<?php echo esc_attr($opt_value); ?>" <?php selected($value, $opt_value); ?>>
                            <?php echo esc_html($opt_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <input<?php echo $attr_string; ?> value="<?php echo esc_attr($value); ?>">
            <?php endif; ?>

            <?php if ($help_text) : ?>
                <p id="<?php echo $help_id; ?>" class="isf-help-text">
                    <?php echo esc_html($help_text); ?>
                </p>
            <?php endif; ?>

            <?php if ($error) : ?>
                <p id="<?php echo $error_id; ?>" class="isf-field-error" role="alert">
                    <?php echo esc_html($error); ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get WCAG compliance status
     */
    public function get_compliance_status(): array {
        $checks = [];

        // Check ARIA support
        $checks['aria_support'] = [
            'name' => __('ARIA Support', 'formflow'),
            'status' => $this->get_setting('aria_live_regions') ? 'pass' : 'warning',
            'description' => __('ARIA live regions for dynamic content announcements', 'formflow'),
        ];

        // Check keyboard navigation
        $checks['keyboard_nav'] = [
            'name' => __('Keyboard Navigation', 'formflow'),
            'status' => $this->get_setting('keyboard_navigation') ? 'pass' : 'warning',
            'description' => __('Full keyboard accessibility without mouse', 'formflow'),
        ];

        // Check focus indicators
        $checks['focus_visible'] = [
            'name' => __('Focus Indicators', 'formflow'),
            'status' => $this->get_setting('focus_indicators') ? 'pass' : 'fail',
            'description' => __('Visible focus indicators for interactive elements', 'formflow'),
        ];

        // Check skip links
        $checks['skip_links'] = [
            'name' => __('Skip Links', 'formflow'),
            'status' => $this->get_setting('skip_links') ? 'pass' : 'warning',
            'description' => __('Skip navigation links for screen readers', 'formflow'),
        ];

        // Check touch targets
        $touch_target = $this->get_setting('minimum_touch_target', 44);
        $checks['touch_targets'] = [
            'name' => __('Touch Targets', 'formflow'),
            'status' => $touch_target >= 44 ? 'pass' : 'warning',
            'description' => sprintf(__('Minimum touch target size: %dpx (WCAG requires 44px)', 'formflow'), $touch_target),
        ];

        // Check error handling
        $checks['error_handling'] = [
            'name' => __('Error Announcements', 'formflow'),
            'status' => $this->get_setting('error_announcements') ? 'pass' : 'warning',
            'description' => __('Errors announced to screen readers', 'formflow'),
        ];

        return $checks;
    }
}

/**
 * Helper function to get accessibility instance
 */
function isf_accessibility(): Accessibility {
    return Accessibility::instance();
}
