<?php
/**
 * Import Completions View
 *
 * Admin interface for importing external completions from CSV files.
 *
 * @var array $instances Available form instances
 * @var array $import_history Recent import history
 */

if (!defined('ABSPATH')) {
    exit;
}

// SECURITY: Check user capabilities
if (!current_user_can('manage_options')) {
    wp_die(
        esc_html__('You do not have sufficient permissions to access this page.', 'formflow'),
        esc_html__('Permission Denied', 'formflow'),
        ['response' => 403]
    );
}

// Handle file upload and processing
$step = isset($_GET['step']) ? sanitize_text_field($_GET['step']) : 'upload';
$error_message = '';
$success_message = '';
$preview_data = null;
$mapping = [];
$file_path = '';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'isf_import_completions')) {
        $error_message = __('Security check failed. Please try again.', 'formflow');
    } else {
        require_once ISF_PLUGIN_DIR . 'includes/analytics/class-completion-importer.php';
        $importer = new \ISF\Analytics\CompletionImporter();

        // Step 1: File Upload
        if (isset($_POST['upload_csv']) && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error_message = __('File upload failed. Please try again.', 'formflow');
            } elseif (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'csv') {
                $error_message = __('Please upload a CSV file.', 'formflow');
            } else {
                // Move to temp location
                $upload_dir = wp_upload_dir();
                $temp_dir = $upload_dir['basedir'] . '/isf-imports/';
                if (!file_exists($temp_dir)) {
                    wp_mkdir_p($temp_dir);
                    file_put_contents($temp_dir . '.htaccess', 'deny from all');
                }

                $temp_file = $temp_dir . 'import_' . wp_generate_uuid4() . '.csv';
                if (move_uploaded_file($file['tmp_name'], $temp_file)) {
                    // Store file path in transient
                    set_transient('isf_import_file_' . get_current_user_id(), $temp_file, HOUR_IN_SECONDS);

                    // Redirect to mapping step
                    wp_redirect(admin_url('admin.php?page=isf-import-completions&step=mapping'));
                    exit;
                } else {
                    $error_message = __('Could not save uploaded file.', 'formflow');
                }
            }
        }

        // Step 2: Field Mapping - Save and Preview
        if (isset($_POST['preview_import'])) {
            $file_path = get_transient('isf_import_file_' . get_current_user_id());
            if (!$file_path || !file_exists($file_path)) {
                $error_message = __('Import session expired. Please upload the file again.', 'formflow');
                $step = 'upload';
            } else {
                $mapping = isset($_POST['mapping']) ? array_map('sanitize_text_field', $_POST['mapping']) : [];
                $instance_id = isset($_POST['instance_id']) ? (int) $_POST['instance_id'] : 0;

                // Store mapping in transient
                set_transient('isf_import_mapping_' . get_current_user_id(), [
                    'mapping' => $mapping,
                    'instance_id' => $instance_id,
                ], HOUR_IN_SECONDS);

                // Do dry run
                $field_mapping = [];
                foreach ($mapping as $index => $field) {
                    if (!empty($field)) {
                        $field_mapping[(int) $index] = $field;
                    }
                }

                $preview_data = $importer->parse_csv_preview($file_path);
                $results = $importer->import_csv($file_path, $field_mapping, $instance_id, [
                    'dry_run' => true,
                ]);

                if (!$results['success']) {
                    $error_message = $results['error'];
                } else {
                    $step = 'confirm';
                }
            }
        }

        // Step 3: Confirm Import
        if (isset($_POST['confirm_import'])) {
            $file_path = get_transient('isf_import_file_' . get_current_user_id());
            $import_settings = get_transient('isf_import_mapping_' . get_current_user_id());

            if (!$file_path || !file_exists($file_path) || !$import_settings) {
                $error_message = __('Import session expired. Please upload the file again.', 'formflow');
                $step = 'upload';
            } else {
                $mapping = $import_settings['mapping'];
                $instance_id = $import_settings['instance_id'];

                $field_mapping = [];
                foreach ($mapping as $index => $field) {
                    if (!empty($field)) {
                        $field_mapping[(int) $index] = $field;
                    }
                }

                $results = $importer->import_csv($file_path, $field_mapping, $instance_id, [
                    'dry_run' => false,
                    'match_handoffs' => true,
                ]);

                // Cleanup
                unlink($file_path);
                delete_transient('isf_import_file_' . get_current_user_id());
                delete_transient('isf_import_mapping_' . get_current_user_id());

                if ($results['success']) {
                    $success_message = sprintf(
                        __('Import complete: %d records imported, %d matched to handoffs, %d skipped.', 'formflow'),
                        $results['imported'],
                        $results['matched'],
                        $results['skipped']
                    );
                    $step = 'complete';
                } else {
                    $error_message = $results['error'];
                }
            }
        }
    }
}

// Load data for mapping step
if ($step === 'mapping') {
    require_once ISF_PLUGIN_DIR . 'includes/analytics/class-completion-importer.php';
    $importer = new \ISF\Analytics\CompletionImporter();

    $file_path = get_transient('isf_import_file_' . get_current_user_id());
    if (!$file_path || !file_exists($file_path)) {
        $error_message = __('Import session expired. Please upload the file again.', 'formflow');
        $step = 'upload';
    } else {
        $preview_data = $importer->parse_csv_preview($file_path);
        if (is_wp_error($preview_data)) {
            $error_message = $preview_data->get_error_message();
            $step = 'upload';
        } else {
            $mapping = $importer->auto_map_headers($preview_data['headers']);
        }
    }
}

// Get available fields
$available_fields = [
    'account_number' => __('Account Number', 'formflow') . ' *',
    'customer_email' => __('Customer Email', 'formflow'),
    'external_id' => __('External ID', 'formflow'),
    'completion_type' => __('Completion Type', 'formflow'),
    'completion_date' => __('Completion Date', 'formflow'),
    'handoff_token' => __('Handoff Token', 'formflow'),
    'first_name' => __('First Name', 'formflow'),
    'last_name' => __('Last Name', 'formflow'),
    'phone' => __('Phone', 'formflow'),
    'address' => __('Address', 'formflow'),
    'city' => __('City', 'formflow'),
    'state' => __('State', 'formflow'),
    'zip' => __('ZIP Code', 'formflow'),
];
?>

<div class="wrap isf-import-completions">
    <h1><?php esc_html_e('Import Completions', 'formflow'); ?></h1>

    <?php if ($error_message): ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="notice notice-success">
            <p><?php echo esc_html($success_message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Progress Steps -->
    <div class="isf-import-steps">
        <div class="step <?php echo $step === 'upload' ? 'active' : ($step !== 'upload' ? 'completed' : ''); ?>">
            <span class="step-number">1</span>
            <span class="step-label"><?php esc_html_e('Upload', 'formflow'); ?></span>
        </div>
        <div class="step-connector"></div>
        <div class="step <?php echo $step === 'mapping' ? 'active' : (in_array($step, ['confirm', 'complete']) ? 'completed' : ''); ?>">
            <span class="step-number">2</span>
            <span class="step-label"><?php esc_html_e('Map Fields', 'formflow'); ?></span>
        </div>
        <div class="step-connector"></div>
        <div class="step <?php echo $step === 'confirm' ? 'active' : ($step === 'complete' ? 'completed' : ''); ?>">
            <span class="step-number">3</span>
            <span class="step-label"><?php esc_html_e('Confirm', 'formflow'); ?></span>
        </div>
        <div class="step-connector"></div>
        <div class="step <?php echo $step === 'complete' ? 'active' : ''; ?>">
            <span class="step-number">4</span>
            <span class="step-label"><?php esc_html_e('Complete', 'formflow'); ?></span>
        </div>
    </div>

    <?php if ($step === 'upload'): ?>
        <!-- Step 1: Upload -->
        <div class="isf-card">
            <h2><?php esc_html_e('Upload CSV File', 'formflow'); ?></h2>
            <p class="description">
                <?php esc_html_e('Upload a CSV file containing completion data from an external enrollment system.', 'formflow'); ?>
            </p>

            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('isf_import_completions'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="csv_file"><?php esc_html_e('CSV File', 'formflow'); ?></label>
                        </th>
                        <td>
                            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                            <p class="description">
                                <?php esc_html_e('Maximum file size: 10MB. File must be in CSV format.', 'formflow'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" name="upload_csv" class="button button-primary">
                        <?php esc_html_e('Upload & Continue', 'formflow'); ?>
                    </button>
                </p>
            </form>

            <hr>

            <h3><?php esc_html_e('Required Fields', 'formflow'); ?></h3>
            <ul>
                <li><strong><?php esc_html_e('Account Number', 'formflow'); ?></strong> - <?php esc_html_e('Customer account number (required)', 'formflow'); ?></li>
            </ul>

            <h3><?php esc_html_e('Optional Fields', 'formflow'); ?></h3>
            <ul>
                <li><strong><?php esc_html_e('Customer Email', 'formflow'); ?></strong> - <?php esc_html_e('Used for matching to visitors', 'formflow'); ?></li>
                <li><strong><?php esc_html_e('Handoff Token', 'formflow'); ?></strong> - <?php esc_html_e('If available, enables exact matching', 'formflow'); ?></li>
                <li><strong><?php esc_html_e('Completion Date', 'formflow'); ?></strong> - <?php esc_html_e('When the enrollment was completed', 'formflow'); ?></li>
                <li><strong><?php esc_html_e('External ID', 'formflow'); ?></strong> - <?php esc_html_e('ID from the external system', 'formflow'); ?></li>
            </ul>
        </div>

    <?php elseif ($step === 'mapping' && $preview_data): ?>
        <!-- Step 2: Map Fields -->
        <div class="isf-card">
            <h2><?php esc_html_e('Map CSV Columns to Fields', 'formflow'); ?></h2>
            <p class="description">
                <?php printf(
                    esc_html__('Your file contains %d rows. Map each column to the appropriate field.', 'formflow'),
                    $preview_data['total_rows']
                ); ?>
            </p>

            <form method="post">
                <?php wp_nonce_field('isf_import_completions'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="instance_id"><?php esc_html_e('Form Instance', 'formflow'); ?></label>
                        </th>
                        <td>
                            <select name="instance_id" id="instance_id" required>
                                <option value=""><?php esc_html_e('Select instance...', 'formflow'); ?></option>
                                <?php foreach ($instances as $instance): ?>
                                    <option value="<?php echo esc_attr($instance['id']); ?>">
                                        <?php echo esc_html($instance['name'] . ' (' . $instance['slug'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <h3><?php esc_html_e('Column Mapping', 'formflow'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 30%;"><?php esc_html_e('CSV Column', 'formflow'); ?></th>
                            <th style="width: 25%;"><?php esc_html_e('Map To Field', 'formflow'); ?></th>
                            <th><?php esc_html_e('Sample Values', 'formflow'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preview_data['headers'] as $index => $header): ?>
                            <tr>
                                <td><strong><?php echo esc_html($header); ?></strong></td>
                                <td>
                                    <select name="mapping[<?php echo esc_attr($index); ?>]" class="field-mapping">
                                        <option value=""><?php esc_html_e('-- Skip --', 'formflow'); ?></option>
                                        <?php foreach ($available_fields as $field_key => $field_label): ?>
                                            <option value="<?php echo esc_attr($field_key); ?>"
                                                <?php selected(isset($mapping[$index]) ? $mapping[$index] : '', $field_key); ?>>
                                                <?php echo esc_html($field_label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="sample-values">
                                    <?php
                                    $samples = [];
                                    foreach ($preview_data['preview'] as $row) {
                                        if (isset($row[$index]) && !empty($row[$index])) {
                                            $samples[] = esc_html(substr($row[$index], 0, 30));
                                        }
                                    }
                                    echo implode(', ', array_slice($samples, 0, 3));
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p class="submit">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=isf-import-completions')); ?>" class="button">
                        <?php esc_html_e('Back', 'formflow'); ?>
                    </a>
                    <button type="submit" name="preview_import" class="button button-primary">
                        <?php esc_html_e('Preview Import', 'formflow'); ?>
                    </button>
                </p>
            </form>
        </div>

    <?php elseif ($step === 'confirm' && isset($results)): ?>
        <!-- Step 3: Confirm -->
        <div class="isf-card">
            <h2><?php esc_html_e('Confirm Import', 'formflow'); ?></h2>

            <div class="isf-import-summary">
                <div class="summary-item">
                    <span class="number"><?php echo esc_html($results['imported']); ?></span>
                    <span class="label"><?php esc_html_e('Records to Import', 'formflow'); ?></span>
                </div>
                <div class="summary-item">
                    <span class="number"><?php echo esc_html($results['matched']); ?></span>
                    <span class="label"><?php esc_html_e('Matched to Handoffs', 'formflow'); ?></span>
                </div>
                <div class="summary-item">
                    <span class="number"><?php echo esc_html($results['skipped']); ?></span>
                    <span class="label"><?php esc_html_e('Will Be Skipped', 'formflow'); ?></span>
                </div>
            </div>

            <?php if (!empty($results['errors'])): ?>
                <div class="isf-import-errors">
                    <h4><?php esc_html_e('Warnings', 'formflow'); ?></h4>
                    <ul>
                        <?php foreach (array_slice($results['errors'], 0, 10) as $error): ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                        <?php if (count($results['errors']) > 10): ?>
                            <li><em><?php printf(
                                esc_html__('... and %d more', 'formflow'),
                                count($results['errors']) - 10
                            ); ?></em></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post">
                <?php wp_nonce_field('isf_import_completions'); ?>
                <p class="submit">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=isf-import-completions&step=mapping')); ?>" class="button">
                        <?php esc_html_e('Back to Mapping', 'formflow'); ?>
                    </a>
                    <button type="submit" name="confirm_import" class="button button-primary">
                        <?php esc_html_e('Confirm & Import', 'formflow'); ?>
                    </button>
                </p>
            </form>
        </div>

    <?php elseif ($step === 'complete'): ?>
        <!-- Step 4: Complete -->
        <div class="isf-card">
            <h2><?php esc_html_e('Import Complete', 'formflow'); ?></h2>

            <div class="isf-success-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>

            <p class="description" style="text-align: center;">
                <?php echo esc_html($success_message); ?>
            </p>

            <p class="submit" style="text-align: center;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=isf-import-completions')); ?>" class="button button-primary">
                    <?php esc_html_e('Import Another File', 'formflow'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=isf-attribution')); ?>" class="button">
                    <?php esc_html_e('View Attribution Report', 'formflow'); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>

    <!-- Export Completions -->
    <div class="isf-card" style="margin-top: 20px;">
        <h2><?php esc_html_e('Export Completions', 'formflow'); ?></h2>
        <p class="description">
            <?php esc_html_e('Export external completion data and handoff tracking records.', 'formflow'); ?>
        </p>

        <form method="get" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" target="_blank" class="isf-export-form">
            <input type="hidden" name="action" value="isf_export_completions">
            <?php wp_nonce_field('isf_export_completions', '_wpnonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="export_instance_id"><?php esc_html_e('Form Instance', 'formflow'); ?></label>
                    </th>
                    <td>
                        <select name="instance_id" id="export_instance_id">
                            <option value=""><?php esc_html_e('All Instances', 'formflow'); ?></option>
                            <?php foreach ($instances as $inst): ?>
                                <option value="<?php echo esc_attr($inst['id']); ?>">
                                    <?php echo esc_html($inst['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="export_type"><?php esc_html_e('Export Type', 'formflow'); ?></label>
                    </th>
                    <td>
                        <select name="export_type" id="export_type">
                            <option value="completions"><?php esc_html_e('External Completions', 'formflow'); ?></option>
                            <option value="handoffs"><?php esc_html_e('Handoff Records', 'formflow'); ?></option>
                            <option value="unmatched"><?php esc_html_e('Unmatched Completions', 'formflow'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php esc_html_e('Date Range', 'formflow'); ?></label>
                    </th>
                    <td>
                        <input type="date" name="date_from" value="<?php echo esc_attr(date('Y-m-d', strtotime('-30 days'))); ?>">
                        <?php esc_html_e('to', 'formflow'); ?>
                        <input type="date" name="date_to" value="<?php echo esc_attr(date('Y-m-d')); ?>">
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">
                    <span class="dashicons dashicons-download" style="vertical-align: middle; margin-top: -2px;"></span>
                    <?php esc_html_e('Export CSV', 'formflow'); ?>
                </button>
            </p>
        </form>
    </div>

    <!-- Import History -->
    <?php if (!empty($import_history)): ?>
        <div class="isf-card" style="margin-top: 20px;">
            <h2><?php esc_html_e('Recent Imports', 'formflow'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Date', 'formflow'); ?></th>
                        <th><?php esc_html_e('Details', 'formflow'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($import_history as $history): ?>
                        <tr>
                            <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($history['created_at']))); ?></td>
                            <td><?php echo esc_html($history['message']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.isf-import-completions .isf-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-top: 20px;
}

.isf-import-steps {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px 0;
    margin-bottom: 10px;
}

.isf-import-steps .step {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
}

.isf-import-steps .step.active {
    color: #2271b1;
    font-weight: 600;
}

.isf-import-steps .step.completed {
    color: #00a32a;
}

.isf-import-steps .step-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.isf-import-steps .step.active .step-number {
    background: #2271b1;
    color: #fff;
}

.isf-import-steps .step.completed .step-number {
    background: #00a32a;
    color: #fff;
}

.isf-import-steps .step-connector {
    width: 40px;
    height: 2px;
    background: #ddd;
    margin: 0 10px;
}

.sample-values {
    color: #666;
    font-size: 12px;
    font-style: italic;
}

.isf-import-summary {
    display: flex;
    justify-content: center;
    gap: 40px;
    padding: 30px 0;
}

.isf-import-summary .summary-item {
    text-align: center;
}

.isf-import-summary .number {
    display: block;
    font-size: 36px;
    font-weight: 600;
    color: #2271b1;
}

.isf-import-summary .label {
    color: #666;
}

.isf-import-errors {
    background: #fff8e5;
    border: 1px solid #ffb900;
    border-radius: 4px;
    padding: 15px;
    margin: 20px 0;
}

.isf-import-errors h4 {
    margin: 0 0 10px;
    color: #996800;
}

.isf-import-errors ul {
    margin: 0;
    padding-left: 20px;
}

.isf-import-errors li {
    color: #666;
    font-size: 13px;
}

.isf-success-icon {
    text-align: center;
    padding: 20px 0;
}

.isf-success-icon .dashicons {
    font-size: 64px;
    width: 64px;
    height: 64px;
    color: #00a32a;
}

.field-mapping {
    width: 100%;
}
</style>
