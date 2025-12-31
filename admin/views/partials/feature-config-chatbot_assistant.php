<?php
/**
 * Chatbot Assistant Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['chatbot_assistant'] ?? [];
$providers = \ISF\ChatbotAssistant::get_providers();
?>

<table class="form-table isf-feature-config-table">
    <tr>
        <th scope="row">
            <label for="chatbot_provider"><?php esc_html_e('AI Provider', 'formflow'); ?></label>
        </th>
        <td>
            <select id="chatbot_provider" name="settings[features][chatbot_assistant][provider]">
                <?php foreach ($providers as $key => $provider): ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($settings['provider'] ?? 'custom', $key); ?>>
                        <?php echo esc_html($provider['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description">
                <?php esc_html_e('Built-in uses a rule-based knowledge base. AI providers give more natural responses.', 'formflow'); ?>
            </p>
        </td>
    </tr>

    <tr class="isf-chatbot-ai">
        <th scope="row">
            <label for="chatbot_api_key"><?php esc_html_e('API Key', 'formflow'); ?></label>
        </th>
        <td>
            <input type="password" id="chatbot_api_key" name="settings[features][chatbot_assistant][api_key]"
                   class="regular-text" value="<?php echo esc_attr($settings['api_key'] ?? ''); ?>">
            <p class="description"><?php esc_html_e('OpenAI API key or Dialogflow credentials. Will be encrypted.', 'formflow'); ?></p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="chatbot_name"><?php esc_html_e('Bot Name', 'formflow'); ?></label>
        </th>
        <td>
            <input type="text" id="chatbot_name" name="settings[features][chatbot_assistant][bot_name]"
                   class="regular-text" value="<?php echo esc_attr($settings['bot_name'] ?? 'EnergyWise Assistant'); ?>">
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="chatbot_welcome"><?php esc_html_e('Welcome Message', 'formflow'); ?></label>
        </th>
        <td>
            <textarea id="chatbot_welcome" name="settings[features][chatbot_assistant][welcome_message]"
                      class="large-text" rows="2"><?php echo esc_textarea($settings['welcome_message'] ?? 'Hi! I can help you with the enrollment process. What questions do you have?'); ?></textarea>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="chatbot_position"><?php esc_html_e('Widget Position', 'formflow'); ?></label>
        </th>
        <td>
            <select id="chatbot_position" name="settings[features][chatbot_assistant][position]">
                <option value="bottom-right" <?php selected($settings['position'] ?? 'bottom-right', 'bottom-right'); ?>>
                    <?php esc_html_e('Bottom Right', 'formflow'); ?>
                </option>
                <option value="bottom-left" <?php selected($settings['position'] ?? 'bottom-right', 'bottom-left'); ?>>
                    <?php esc_html_e('Bottom Left', 'formflow'); ?>
                </option>
            </select>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="chatbot_auto_open"><?php esc_html_e('Auto-Open Delay', 'formflow'); ?></label>
        </th>
        <td>
            <select id="chatbot_auto_open" name="settings[features][chatbot_assistant][auto_open_delay]">
                <option value="0" <?php selected($settings['auto_open_delay'] ?? 0, 0); ?>>
                    <?php esc_html_e('Disabled', 'formflow'); ?>
                </option>
                <option value="5" <?php selected($settings['auto_open_delay'] ?? 0, 5); ?>>
                    <?php esc_html_e('5 seconds', 'formflow'); ?>
                </option>
                <option value="10" <?php selected($settings['auto_open_delay'] ?? 0, 10); ?>>
                    <?php esc_html_e('10 seconds', 'formflow'); ?>
                </option>
                <option value="30" <?php selected($settings['auto_open_delay'] ?? 0, 30); ?>>
                    <?php esc_html_e('30 seconds', 'formflow'); ?>
                </option>
            </select>
            <p class="description"><?php esc_html_e('Automatically open chat widget after this delay', 'formflow'); ?></p>
        </td>
    </tr>

    <tr>
        <th scope="row"><?php esc_html_e('Show On Steps', 'formflow'); ?></th>
        <td>
            <?php
            $available_steps = $settings['available_on_steps'] ?? [1, 2, 3, 4, 5];
            $step_names = [
                1 => __('Program Selection', 'formflow'),
                2 => __('Account Validation', 'formflow'),
                3 => __('Customer Info', 'formflow'),
                4 => __('Scheduling', 'formflow'),
                5 => __('Confirmation', 'formflow'),
            ];
            ?>
            <fieldset>
                <?php foreach ($step_names as $step => $name): ?>
                    <label class="isf-checkbox-label">
                        <input type="checkbox" name="settings[features][chatbot_assistant][available_on_steps][]"
                               value="<?php echo $step; ?>" <?php checked(in_array($step, $available_steps)); ?>>
                        <?php echo esc_html("Step {$step}: {$name}"); ?>
                    </label>
                    <br>
                <?php endforeach; ?>
            </fieldset>
        </td>
    </tr>
</table>

<div class="isf-info-box">
    <p><strong><?php esc_html_e('Knowledge Base:', 'formflow'); ?></strong></p>
    <p><?php esc_html_e('The built-in chatbot includes pre-configured responses for common questions about:', 'formflow'); ?></p>
    <ul>
        <li><?php esc_html_e('Program benefits and how it works', 'formflow'); ?></li>
        <li><?php esc_html_e('Account number location', 'formflow'); ?></li>
        <li><?php esc_html_e('Cycling levels explanation', 'formflow'); ?></li>
        <li><?php esc_html_e('Scheduling and installation process', 'formflow'); ?></li>
        <li><?php esc_html_e('Privacy and security', 'formflow'); ?></li>
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var providerSelect = document.getElementById('chatbot_provider');

    function updateFields() {
        var needsApi = providerSelect.value !== 'custom';
        document.querySelectorAll('.isf-chatbot-ai').forEach(function(row) {
            row.style.display = needsApi ? '' : 'none';
        });
    }

    providerSelect.addEventListener('change', updateFields);
    updateFields();
});
</script>
