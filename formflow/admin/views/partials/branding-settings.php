<?php
/**
 * Branding Settings Partial
 *
 * Admin settings page for white-label branding configuration.
 *
 * @package FormFlow
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$branding = \ISF\Branding::instance();
$settings = $branding->get_all();
$field_definitions = \ISF\Branding::get_field_definitions();
?>

<div class="isf-branding-settings">
    <h2><?php esc_html_e('White-Label Branding', 'formflow'); ?></h2>
    <p class="description">
        <?php esc_html_e('Customize the look and feel of the plugin to match your brand.', 'formflow'); ?>
    </p>

    <form method="post" action="" id="isf-branding-form">
        <?php wp_nonce_field('isf_save_branding', 'isf_branding_nonce'); ?>

        <?php foreach ($field_definitions as $section_key => $section): ?>
            <div class="isf-settings-section">
                <h3><?php echo esc_html($section['label']); ?></h3>

                <table class="form-table">
                    <?php foreach ($section['fields'] as $field_key => $field): ?>
                        <tr>
                            <th scope="row">
                                <label for="branding_<?php echo esc_attr($field_key); ?>">
                                    <?php echo esc_html($field['label']); ?>
                                </label>
                            </th>
                            <td>
                                <?php
                                $value = $settings[$field_key] ?? '';
                                $field_id = 'branding_' . $field_key;
                                $field_name = 'branding[' . $field_key . ']';

                                switch ($field['type']):
                                    case 'text':
                                    case 'url':
                                    case 'email':
                                        ?>
                                        <input type="<?php echo esc_attr($field['type']); ?>"
                                               id="<?php echo esc_attr($field_id); ?>"
                                               name="<?php echo esc_attr($field_name); ?>"
                                               value="<?php echo esc_attr($value); ?>"
                                               class="regular-text">
                                        <?php
                                        break;

                                    case 'textarea':
                                        ?>
                                        <textarea id="<?php echo esc_attr($field_id); ?>"
                                                  name="<?php echo esc_attr($field_name); ?>"
                                                  class="large-text"
                                                  rows="3"><?php echo esc_textarea($value); ?></textarea>
                                        <?php
                                        break;

                                    case 'color':
                                        ?>
                                        <input type="color"
                                               id="<?php echo esc_attr($field_id); ?>"
                                               name="<?php echo esc_attr($field_name); ?>"
                                               value="<?php echo esc_attr($value); ?>"
                                               class="isf-color-picker">
                                        <input type="text"
                                               value="<?php echo esc_attr($value); ?>"
                                               class="isf-color-hex small-text"
                                               data-target="<?php echo esc_attr($field_id); ?>">
                                        <?php
                                        break;

                                    case 'checkbox':
                                        ?>
                                        <label>
                                            <input type="checkbox"
                                                   id="<?php echo esc_attr($field_id); ?>"
                                                   name="<?php echo esc_attr($field_name); ?>"
                                                   value="1"
                                                   <?php checked($value, true); ?>>
                                            <?php esc_html_e('Enabled', 'formflow'); ?>
                                        </label>
                                        <?php
                                        break;

                                    case 'select':
                                        ?>
                                        <select id="<?php echo esc_attr($field_id); ?>"
                                                name="<?php echo esc_attr($field_name); ?>">
                                            <?php foreach ($field['options'] as $opt_value => $opt_label): ?>
                                                <option value="<?php echo esc_attr($opt_value); ?>"
                                                        <?php selected($value, $opt_value); ?>>
                                                    <?php echo esc_html($opt_label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php
                                        break;

                                    default:
                                        ?>
                                        <input type="text"
                                               id="<?php echo esc_attr($field_id); ?>"
                                               name="<?php echo esc_attr($field_name); ?>"
                                               value="<?php echo esc_attr($value); ?>"
                                               class="regular-text">
                                        <?php
                                endswitch;

                                if (!empty($field['description'])):
                                    ?>
                                    <p class="description"><?php echo esc_html($field['description']); ?></p>
                                    <?php
                                endif;
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endforeach; ?>

        <div class="isf-settings-section">
            <h3><?php esc_html_e('Preview', 'formflow'); ?></h3>
            <div class="isf-branding-preview">
                <div class="isf-preview-header" id="isf-preview-header">
                    <span class="isf-preview-logo" id="isf-preview-logo"></span>
                    <span class="isf-preview-name" id="isf-preview-name"><?php echo esc_html($settings['plugin_name']); ?></span>
                </div>
                <div class="isf-preview-button" id="isf-preview-button">
                    <?php esc_html_e('Sample Button', 'formflow'); ?>
                </div>
                <div class="isf-preview-powered-by" id="isf-preview-powered-by">
                    <?php echo esc_html($settings['powered_by_text']); ?>
                </div>
            </div>
        </div>

        <p class="submit">
            <button type="submit" class="button button-primary" name="save_branding">
                <?php esc_html_e('Save Branding Settings', 'formflow'); ?>
            </button>
            <button type="button" class="button" id="isf-reset-branding">
                <?php esc_html_e('Reset to Defaults', 'formflow'); ?>
            </button>
        </p>
    </form>
</div>

<style>
.isf-branding-settings .isf-settings-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    margin-bottom: 20px;
}

.isf-branding-settings .isf-settings-section h3 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.isf-color-picker {
    width: 50px;
    height: 34px;
    padding: 0;
    border: 1px solid #ccc;
    cursor: pointer;
    vertical-align: middle;
}

.isf-color-hex {
    margin-left: 10px !important;
    vertical-align: middle;
}

.isf-branding-preview {
    background: #f5f5f5;
    padding: 30px;
    border-radius: 4px;
    text-align: center;
}

.isf-preview-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin-bottom: 20px;
    padding: 15px;
    background: #fff;
    border-radius: 4px;
}

.isf-preview-logo img {
    max-height: 40px;
    width: auto;
}

.isf-preview-name {
    font-size: 20px;
    font-weight: 600;
}

.isf-preview-button {
    display: inline-block;
    padding: 12px 24px;
    color: #fff;
    border-radius: 4px;
    font-weight: 500;
    margin-bottom: 20px;
    cursor: default;
}

.isf-preview-powered-by {
    font-size: 12px;
    color: #666;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Sync color picker with hex input
    $('.isf-color-picker').on('input change', function() {
        var hex = $(this).val();
        var target = $(this).attr('id');
        $('input.isf-color-hex[data-target="' + target + '"]').val(hex);
        updatePreview();
    });

    $('.isf-color-hex').on('input change', function() {
        var hex = $(this).val();
        var target = $(this).data('target');
        if (/^#[0-9A-F]{6}$/i.test(hex)) {
            $('#' + target).val(hex);
            updatePreview();
        }
    });

    // Live preview updates
    $('#branding_plugin_name').on('input', function() {
        $('#isf-preview-name').text($(this).val() || 'Plugin Name');
    });

    $('#branding_logo_url').on('input', function() {
        var url = $(this).val();
        if (url) {
            $('#isf-preview-logo').html('<img src="' + url + '" alt="">');
        } else {
            $('#isf-preview-logo').html('');
        }
    });

    $('#branding_primary_color').on('input change', function() {
        updatePreview();
    });

    $('#branding_powered_by_text').on('input', function() {
        $('#isf-preview-powered-by').text($(this).val());
    });

    $('#branding_show_powered_by').on('change', function() {
        if ($(this).is(':checked')) {
            $('#isf-preview-powered-by').show();
        } else {
            $('#isf-preview-powered-by').hide();
        }
    });

    function updatePreview() {
        var primaryColor = $('#branding_primary_color').val();
        $('#isf-preview-button').css('background-color', primaryColor);
    }

    // Reset to defaults
    $('#isf-reset-branding').on('click', function() {
        if (confirm('<?php echo esc_js(__('Are you sure you want to reset all branding settings to defaults?', 'formflow')); ?>')) {
            // Reset form to defaults
            var defaults = <?php echo json_encode(\ISF\Branding::get_defaults()); ?>;
            $.each(defaults, function(key, value) {
                var $field = $('#branding_' + key);
                if ($field.length) {
                    if ($field.is(':checkbox')) {
                        $field.prop('checked', value);
                    } else {
                        $field.val(value);
                    }
                    $field.trigger('change');
                }
            });
        }
    });

    // Initialize preview
    updatePreview();
});
</script>
