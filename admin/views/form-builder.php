<?php
/**
 * Form Builder Admin View
 *
 * @package FormFlow
 */

defined('ABSPATH') || exit;

// Get instance ID from URL
$instance_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
$instance = null;
$schema = null;

if ($instance_id) {
    global $wpdb;
    $table = $wpdb->prefix . ISF_TABLE_INSTANCES;
    $instance = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $instance_id));

    if ($instance) {
        $settings = json_decode($instance->settings, true) ?: [];
        $schema = $settings['form_schema'] ?? null;
    }
}

// Get form builder instance
$builder = new \ISF\Builder\FormBuilder();
$field_types = $builder->get_field_types();

?>
<div class="wrap isf-admin-wrap">
    <h1 class="wp-heading-inline">
        <?php if ($instance): ?>
            <?php esc_html_e('Edit Form:', 'formflow'); ?> <?php echo esc_html($instance->name); ?>
        <?php else: ?>
            <?php esc_html_e('Visual Form Builder', 'formflow'); ?>
        <?php endif; ?>
    </h1>

    <?php if ($instance): ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=isf-instance-editor&id=' . $instance_id)); ?>" class="page-title-action">
            <?php esc_html_e('Back to Instance', 'formflow'); ?>
        </a>
    <?php endif; ?>

    <hr class="wp-header-end">

    <?php if (!$instance_id): ?>
        <div class="notice notice-warning">
            <p><?php esc_html_e('Please select a form instance to edit, or create a new one from the dashboard.', 'formflow'); ?></p>
        </div>

        <div class="isf-card">
            <h2><?php esc_html_e('Select Form Instance', 'formflow'); ?></h2>
            <p><?php esc_html_e('Choose an existing form instance to open in the visual builder:', 'formflow'); ?></p>

            <?php
            global $wpdb;
            $table = $wpdb->prefix . ISF_TABLE_INSTANCES;
            $instances = $wpdb->get_results("SELECT id, name, slug, form_type, is_active FROM {$table} ORDER BY name ASC");
            ?>

            <?php if ($instances): ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Name', 'formflow'); ?></th>
                            <th><?php esc_html_e('Slug', 'formflow'); ?></th>
                            <th><?php esc_html_e('Type', 'formflow'); ?></th>
                            <th><?php esc_html_e('Status', 'formflow'); ?></th>
                            <th><?php esc_html_e('Actions', 'formflow'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($instances as $inst): ?>
                            <tr>
                                <td><strong><?php echo esc_html($inst->name); ?></strong></td>
                                <td><code><?php echo esc_html($inst->slug); ?></code></td>
                                <td><?php echo esc_html(ucfirst($inst->form_type)); ?></td>
                                <td>
                                    <?php if ($inst->is_active): ?>
                                        <span class="isf-status isf-status-active"><?php esc_html_e('Active', 'formflow'); ?></span>
                                    <?php else: ?>
                                        <span class="isf-status isf-status-inactive"><?php esc_html_e('Inactive', 'formflow'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=isf-form-builder&id=' . $inst->id)); ?>" class="button button-primary">
                                        <?php esc_html_e('Open Builder', 'formflow'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php esc_html_e('No form instances found.', 'formflow'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=isf-instance-editor')); ?>" class="button button-primary">
                    <?php esc_html_e('Create New Instance', 'formflow'); ?>
                </a>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <!-- Form Builder Container -->
        <div id="isf-form-builder"></div>

        <script type="text/javascript">
            var isf_builder = {
                ajax_url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                nonce: '<?php echo esc_attr(wp_create_nonce('isf_builder_nonce')); ?>',
                instance_id: <?php echo absint($instance_id); ?>,
                schema: <?php echo $schema ? wp_json_encode($schema) : 'null'; ?>,
                field_types: <?php echo wp_json_encode($field_types); ?>,
                strings: {
                    save: '<?php echo esc_js(__('Save Form', 'formflow')); ?>',
                    saving: '<?php echo esc_js(__('Saving...', 'formflow')); ?>',
                    saved: '<?php echo esc_js(__('Saved!', 'formflow')); ?>',
                    error: '<?php echo esc_js(__('Error saving form', 'formflow')); ?>',
                    unsaved: '<?php echo esc_js(__('You have unsaved changes. Are you sure you want to leave?', 'formflow')); ?>',
                    delete_field: '<?php echo esc_js(__('Are you sure you want to delete this field?', 'formflow')); ?>',
                    delete_step: '<?php echo esc_js(__('Are you sure you want to delete this step and all its fields?', 'formflow')); ?>'
                }
            };
        </script>

    <?php endif; ?>
</div>
