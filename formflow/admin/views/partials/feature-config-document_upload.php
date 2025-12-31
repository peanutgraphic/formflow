<?php
/**
 * Document Upload Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['document_upload'] ?? [];
$allowed_types = $settings['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'pdf'];
if (is_string($allowed_types)) {
    $allowed_types = explode(',', $allowed_types);
}
?>

<table class="form-table isf-feature-config-table">
    <tr>
        <th scope="row">
            <label for="upload_max_files"><?php esc_html_e('Maximum Files', 'formflow'); ?></label>
        </th>
        <td>
            <input type="number" id="upload_max_files" name="settings[features][document_upload][max_files]"
                   class="small-text" min="1" max="10" value="<?php echo esc_attr($settings['max_files'] ?? 3); ?>">
            <p class="description"><?php esc_html_e('Maximum number of files a customer can upload', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="upload_max_size"><?php esc_html_e('Maximum File Size', 'formflow'); ?></label>
        </th>
        <td>
            <input type="number" id="upload_max_size" name="settings[features][document_upload][max_file_size_mb]"
                   class="small-text" min="1" max="50" value="<?php echo esc_attr($settings['max_file_size_mb'] ?? 10); ?>">
            <span>MB</span>
            <p class="description"><?php esc_html_e('Maximum size per file in megabytes', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Allowed File Types', 'formflow'); ?></th>
        <td>
            <fieldset>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][document_upload][allowed_types][]" value="jpg"
                           <?php checked(in_array('jpg', $allowed_types)); ?>>
                    JPG/JPEG
                </label>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][document_upload][allowed_types][]" value="png"
                           <?php checked(in_array('png', $allowed_types)); ?>>
                    PNG
                </label>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][document_upload][allowed_types][]" value="gif"
                           <?php checked(in_array('gif', $allowed_types)); ?>>
                    GIF
                </label>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][document_upload][allowed_types][]" value="pdf"
                           <?php checked(in_array('pdf', $allowed_types)); ?>>
                    PDF
                </label>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][document_upload][allowed_types][]" value="doc"
                           <?php checked(in_array('doc', $allowed_types)); ?>>
                    DOC
                </label>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][document_upload][allowed_types][]" value="docx"
                           <?php checked(in_array('docx', $allowed_types)); ?>>
                    DOCX
                </label>
            </fieldset>
            <p class="description"><?php esc_html_e('File types customers are allowed to upload', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Required Upload', 'formflow'); ?></th>
        <td>
            <label class="isf-checkbox-label">
                <input type="checkbox" name="settings[features][document_upload][required]" value="1"
                       <?php checked($settings['required'] ?? false); ?>>
                <?php esc_html_e('Require at least one document to be uploaded', 'formflow'); ?>
            </label>
            <p class="description"><?php esc_html_e('If enabled, form cannot be submitted without uploading a file', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="upload_step"><?php esc_html_e('Display On Step', 'formflow'); ?></label>
        </th>
        <td>
            <select id="upload_step" name="settings[features][document_upload][upload_step]">
                <option value="2" <?php selected($settings['upload_step'] ?? 3, 2); ?>>
                    <?php esc_html_e('Step 2 - Account Validation', 'formflow'); ?>
                </option>
                <option value="3" <?php selected($settings['upload_step'] ?? 3, 3); ?>>
                    <?php esc_html_e('Step 3 - Customer Information', 'formflow'); ?>
                </option>
                <option value="4" <?php selected($settings['upload_step'] ?? 3, 4); ?>>
                    <?php esc_html_e('Step 4 - Scheduling', 'formflow'); ?>
                </option>
            </select>
            <p class="description"><?php esc_html_e('Which form step to show the upload field on', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="upload_storage"><?php esc_html_e('Storage Location', 'formflow'); ?></label>
        </th>
        <td>
            <select id="upload_storage" name="settings[features][document_upload][storage]">
                <option value="local" <?php selected($settings['storage'] ?? 'local', 'local'); ?>>
                    <?php esc_html_e('Local (WordPress Uploads)', 'formflow'); ?>
                </option>
            </select>
            <p class="description"><?php esc_html_e('Where uploaded files are stored', 'formflow'); ?></p>
        </td>
    </tr>
</table>

<div class="isf-info-box">
    <p><strong><?php esc_html_e('Security:', 'formflow'); ?></strong></p>
    <ul>
        <li><?php esc_html_e('Files are stored in a protected directory with .htaccess restrictions', 'formflow'); ?></li>
        <li><?php esc_html_e('MIME type verification prevents disguised files', 'formflow'); ?></li>
        <li><?php esc_html_e('Filenames are randomized to prevent guessing', 'formflow'); ?></li>
        <li><?php esc_html_e('Orphaned uploads are automatically cleaned up after 24 hours', 'formflow'); ?></li>
    </ul>
</div>
