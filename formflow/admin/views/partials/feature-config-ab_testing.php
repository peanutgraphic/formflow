<?php
/**
 * A/B Testing Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['ab_testing'] ?? [];
$variations = $settings['variations'] ?? [];
if (empty($variations)) {
    $variations = \ISF\ABTesting::get_default_variations();
}
?>

<table class="form-table isf-feature-config-table">
    <tr>
        <th scope="row">
            <label for="ab_track_by"><?php esc_html_e('Track By', 'formflow'); ?></label>
        </th>
        <td>
            <select id="ab_track_by" name="settings[features][ab_testing][track_by]">
                <option value="session" <?php selected($settings['track_by'] ?? 'session', 'session'); ?>>
                    <?php esc_html_e('Session', 'formflow'); ?>
                </option>
                <option value="cookie" <?php selected($settings['track_by'] ?? 'session', 'cookie'); ?>>
                    <?php esc_html_e('Cookie (persistent)', 'formflow'); ?>
                </option>
            </select>
            <p class="description"><?php esc_html_e('Session: variation may change on return visits. Cookie: user sees same variation across visits.', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="ab_goal"><?php esc_html_e('Conversion Goal', 'formflow'); ?></label>
        </th>
        <td>
            <select id="ab_goal" name="settings[features][ab_testing][goal]">
                <option value="enrollment_completed" <?php selected($settings['goal'] ?? 'enrollment_completed', 'enrollment_completed'); ?>>
                    <?php esc_html_e('Enrollment Completed', 'formflow'); ?>
                </option>
                <option value="appointment_scheduled" <?php selected($settings['goal'] ?? 'enrollment_completed', 'appointment_scheduled'); ?>>
                    <?php esc_html_e('Appointment Scheduled', 'formflow'); ?>
                </option>
                <option value="step_3_reached" <?php selected($settings['goal'] ?? 'enrollment_completed', 'step_3_reached'); ?>>
                    <?php esc_html_e('Step 3 Reached', 'formflow'); ?>
                </option>
            </select>
            <p class="description"><?php esc_html_e('What counts as a successful conversion', 'formflow'); ?></p>
        </td>
    </tr>
</table>

<h4><?php esc_html_e('Variations', 'formflow'); ?></h4>
<p class="description"><?php esc_html_e('Configure different versions of your form to test. Traffic will be split between variations based on weight.', 'formflow'); ?></p>

<div id="isf-ab-variations">
    <?php foreach ($variations as $i => $variation): ?>
        <div class="isf-ab-variation" data-index="<?php echo $i; ?>">
            <div class="isf-ab-variation-header">
                <strong><?php echo esc_html($variation['name'] ?? 'Variation ' . ($i + 1)); ?></strong>
                <?php if (!empty($variation['is_control'])): ?>
                    <span class="isf-badge"><?php esc_html_e('Control', 'formflow'); ?></span>
                <?php endif; ?>
                <button type="button" class="button-link isf-toggle-variation"><?php esc_html_e('Edit', 'formflow'); ?></button>
            </div>
            <div class="isf-ab-variation-settings" style="display:none;">
                <table class="form-table">
                    <tr>
                        <th>
                            <label><?php esc_html_e('ID', 'formflow'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="settings[features][ab_testing][variations][<?php echo $i; ?>][id]"
                                   value="<?php echo esc_attr($variation['id'] ?? ''); ?>" class="regular-text"
                                   <?php echo $i === 0 ? 'readonly' : ''; ?>>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php esc_html_e('Name', 'formflow'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="settings[features][ab_testing][variations][<?php echo $i; ?>][name]"
                                   value="<?php echo esc_attr($variation['name'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php esc_html_e('Traffic Weight', 'formflow'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="settings[features][ab_testing][variations][<?php echo $i; ?>][weight]"
                                   value="<?php echo esc_attr($variation['weight'] ?? 50); ?>" class="small-text" min="0" max="100">%
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php esc_html_e('Is Control', 'formflow'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="settings[features][ab_testing][variations][<?php echo $i; ?>][is_control]"
                                   value="1" <?php checked($variation['is_control'] ?? false); ?>>
                        </td>
                    </tr>
                </table>

                <h5><?php esc_html_e('Modifications', 'formflow'); ?></h5>
                <p class="description"><?php esc_html_e('Leave blank to use default form values', 'formflow'); ?></p>

                <?php $mods = $variation['modifications'] ?? []; ?>
                <table class="form-table">
                    <tr>
                        <th>
                            <label><?php esc_html_e('Heading Override', 'formflow'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="settings[features][ab_testing][variations][<?php echo $i; ?>][modifications][heading]"
                                   value="<?php echo esc_attr($mods['heading'] ?? ''); ?>" class="large-text">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php esc_html_e('Subheading Override', 'formflow'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="settings[features][ab_testing][variations][<?php echo $i; ?>][modifications][subheading]"
                                   value="<?php echo esc_attr($mods['subheading'] ?? ''); ?>" class="large-text">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php esc_html_e('Button Text Override', 'formflow'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="settings[features][ab_testing][variations][<?php echo $i; ?>][modifications][button_text]"
                                   value="<?php echo esc_attr($mods['button_text'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label><?php esc_html_e('CSS Class', 'formflow'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="settings[features][ab_testing][variations][<?php echo $i; ?>][modifications][css_class]"
                                   value="<?php echo esc_attr($mods['css_class'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>

                <?php if ($i > 1): ?>
                    <button type="button" class="button isf-remove-variation"><?php esc_html_e('Remove Variation', 'formflow'); ?></button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<p>
    <button type="button" class="button" id="isf-add-variation"><?php esc_html_e('Add Variation', 'formflow'); ?></button>
</p>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var variationIndex = <?php echo count($variations); ?>;
    var container = document.getElementById('isf-ab-variations');

    // Toggle variation settings
    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('isf-toggle-variation')) {
            var settings = e.target.closest('.isf-ab-variation').querySelector('.isf-ab-variation-settings');
            settings.style.display = settings.style.display === 'none' ? 'block' : 'none';
        }
        if (e.target.classList.contains('isf-remove-variation')) {
            e.target.closest('.isf-ab-variation').remove();
        }
    });

    // Add new variation
    document.getElementById('isf-add-variation').addEventListener('click', function() {
        var html = '<div class="isf-ab-variation" data-index="' + variationIndex + '">' +
            '<div class="isf-ab-variation-header">' +
            '<strong>New Variation</strong>' +
            '<button type="button" class="button-link isf-toggle-variation">Edit</button>' +
            '</div>' +
            '<div class="isf-ab-variation-settings">' +
            '<table class="form-table">' +
            '<tr><th><label>ID</label></th><td><input type="text" name="settings[features][ab_testing][variations][' + variationIndex + '][id]" value="variant_' + String.fromCharCode(97 + variationIndex) + '" class="regular-text"></td></tr>' +
            '<tr><th><label>Name</label></th><td><input type="text" name="settings[features][ab_testing][variations][' + variationIndex + '][name]" value="Variant ' + String.fromCharCode(65 + variationIndex) + '" class="regular-text"></td></tr>' +
            '<tr><th><label>Traffic Weight</label></th><td><input type="number" name="settings[features][ab_testing][variations][' + variationIndex + '][weight]" value="50" class="small-text" min="0" max="100">%</td></tr>' +
            '<tr><th><label>Is Control</label></th><td><input type="checkbox" name="settings[features][ab_testing][variations][' + variationIndex + '][is_control]" value="1"></td></tr>' +
            '</table>' +
            '<h5>Modifications</h5>' +
            '<table class="form-table">' +
            '<tr><th><label>Heading Override</label></th><td><input type="text" name="settings[features][ab_testing][variations][' + variationIndex + '][modifications][heading]" class="large-text"></td></tr>' +
            '<tr><th><label>Subheading Override</label></th><td><input type="text" name="settings[features][ab_testing][variations][' + variationIndex + '][modifications][subheading]" class="large-text"></td></tr>' +
            '<tr><th><label>Button Text Override</label></th><td><input type="text" name="settings[features][ab_testing][variations][' + variationIndex + '][modifications][button_text]" class="regular-text"></td></tr>' +
            '<tr><th><label>CSS Class</label></th><td><input type="text" name="settings[features][ab_testing][variations][' + variationIndex + '][modifications][css_class]" class="regular-text"></td></tr>' +
            '</table>' +
            '<button type="button" class="button isf-remove-variation">Remove Variation</button>' +
            '</div></div>';
        container.insertAdjacentHTML('beforeend', html);
        variationIndex++;
    });
});
</script>

<style>
.isf-ab-variation {
    background: #fff;
    border: 1px solid #c3c4c7;
    margin-bottom: 10px;
    padding: 10px 15px;
}
.isf-ab-variation-header {
    display: flex;
    align-items: center;
    gap: 10px;
}
.isf-badge {
    background: #2271b1;
    color: #fff;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 3px;
}
.isf-ab-variation-settings {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #c3c4c7;
}
.isf-ab-variation-settings .form-table th {
    width: 150px;
    padding: 10px 10px 10px 0;
}
.isf-ab-variation-settings .form-table td {
    padding: 10px 0;
}
</style>
