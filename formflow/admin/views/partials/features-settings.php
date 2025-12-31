<?php
/**
 * Features Settings Partial
 *
 * Per-instance feature toggles and configuration.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current feature settings
$features = \ISF\FeatureManager::get_features($instance ?? []);
$available_features = \ISF\FeatureManager::get_available_features();
$features_by_category = \ISF\FeatureManager::get_features_by_category();
?>

<div class="isf-card isf-features-card">
    <h2><?php esc_html_e('Features', 'formflow'); ?></h2>
    <p class="description"><?php esc_html_e('Enable or disable features for this form instance. Click "Configure" to adjust feature-specific settings.', 'formflow'); ?></p>

    <?php foreach ($features_by_category as $category_key => $category) : ?>
        <div class="isf-feature-category">
            <h3><?php echo esc_html($category['label']); ?></h3>

            <div class="isf-feature-list">
                <?php foreach ($category['features'] as $feature_key => $feature_meta) : ?>
                    <?php
                    $feature_settings = $features[$feature_key] ?? [];
                    $is_enabled = !empty($feature_settings['enabled']);
                    $requires_config = !empty($feature_meta['requires_config']);
                    ?>
                    <div class="isf-feature-item <?php echo $is_enabled ? 'isf-feature-enabled' : ''; ?>">
                        <div class="isf-feature-toggle">
                            <label class="isf-toggle">
                                <input type="checkbox"
                                       name="settings[features][<?php echo esc_attr($feature_key); ?>][enabled]"
                                       value="1"
                                       <?php checked($is_enabled); ?>
                                       class="isf-feature-checkbox"
                                       data-feature="<?php echo esc_attr($feature_key); ?>">
                                <span class="isf-toggle-slider"></span>
                            </label>
                        </div>

                        <div class="isf-feature-info">
                            <div class="isf-feature-name">
                                <span class="dashicons dashicons-<?php echo esc_attr($feature_meta['icon']); ?>"></span>
                                <?php echo esc_html($feature_meta['name']); ?>
                            </div>
                            <div class="isf-feature-description">
                                <?php echo esc_html($feature_meta['description']); ?>
                            </div>
                        </div>

                        <div class="isf-feature-actions">
                            <button type="button" class="button isf-configure-feature"
                                    data-feature="<?php echo esc_attr($feature_key); ?>"
                                    <?php echo !$is_enabled ? 'disabled' : ''; ?>>
                                <?php esc_html_e('Configure', 'formflow'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Configuration Panel (hidden by default) -->
                    <div class="isf-feature-config" id="isf-config-<?php echo esc_attr($feature_key); ?>" style="display: none;">
                        <?php include __DIR__ . "/feature-config-{$feature_key}.php"; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
