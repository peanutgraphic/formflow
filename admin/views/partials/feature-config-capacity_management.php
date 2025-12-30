<?php
/**
 * Capacity Management Feature Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = $features['capacity_management'] ?? [];
$blackout_dates = $settings['blackout_dates'] ?? [];
if (is_string($blackout_dates)) {
    $blackout_dates = json_decode($blackout_dates, true) ?? [];
}
?>

<table class="form-table isf-feature-config-table">
    <tr>
        <th scope="row">
            <label for="daily_cap"><?php esc_html_e('Daily Cap', 'formflow'); ?></label>
        </th>
        <td>
            <input type="number" id="daily_cap" name="settings[features][capacity_management][daily_cap]"
                   class="small-text" min="0" value="<?php echo esc_attr($settings['daily_cap'] ?? 0); ?>">
            <p class="description"><?php esc_html_e('Maximum appointments per day (0 = unlimited)', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="per_slot_cap"><?php esc_html_e('Per-Slot Cap', 'formflow'); ?></label>
        </th>
        <td>
            <input type="number" id="per_slot_cap" name="settings[features][capacity_management][per_slot_cap]"
                   class="small-text" min="0" value="<?php echo esc_attr($settings['per_slot_cap'] ?? 0); ?>">
            <p class="description"><?php esc_html_e('Maximum appointments per time slot (0 = unlimited)', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Blackout Dates', 'formflow'); ?></th>
        <td>
            <div id="isf-blackout-dates">
                <?php if (!empty($blackout_dates)): ?>
                    <?php foreach ($blackout_dates as $i => $blackout): ?>
                        <div class="isf-blackout-item">
                            <?php if (!empty($blackout['date'])): ?>
                                <input type="date" name="settings[features][capacity_management][blackout_dates][<?php echo $i; ?>][date]"
                                       value="<?php echo esc_attr($blackout['date']); ?>">
                                <input type="text" name="settings[features][capacity_management][blackout_dates][<?php echo $i; ?>][reason]"
                                       value="<?php echo esc_attr($blackout['reason'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Reason (optional)', 'formflow'); ?>">
                            <?php elseif (!empty($blackout['start'])): ?>
                                <input type="date" name="settings[features][capacity_management][blackout_dates][<?php echo $i; ?>][start]"
                                       value="<?php echo esc_attr($blackout['start']); ?>">
                                <span>to</span>
                                <input type="date" name="settings[features][capacity_management][blackout_dates][<?php echo $i; ?>][end]"
                                       value="<?php echo esc_attr($blackout['end']); ?>">
                                <input type="text" name="settings[features][capacity_management][blackout_dates][<?php echo $i; ?>][reason]"
                                       value="<?php echo esc_attr($blackout['reason'] ?? ''); ?>"
                                       placeholder="<?php esc_attr_e('Reason (optional)', 'formflow'); ?>">
                            <?php endif; ?>
                            <button type="button" class="button isf-remove-blackout">&times;</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="isf-blackout-actions">
                <button type="button" class="button" id="isf-add-blackout-date">
                    <?php esc_html_e('Add Single Date', 'formflow'); ?>
                </button>
                <button type="button" class="button" id="isf-add-blackout-range">
                    <?php esc_html_e('Add Date Range', 'formflow'); ?>
                </button>
            </div>
            <p class="description"><?php esc_html_e('Dates when scheduling is not available (holidays, etc.)', 'formflow'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Waitlist', 'formflow'); ?></th>
        <td>
            <fieldset>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][capacity_management][enable_waitlist]" value="1"
                           <?php checked($settings['enable_waitlist'] ?? false); ?>>
                    <?php esc_html_e('Enable waitlist when slots are full', 'formflow'); ?>
                </label>
                <br>
                <label class="isf-checkbox-label">
                    <input type="checkbox" name="settings[features][capacity_management][waitlist_notification]" value="1"
                           <?php checked($settings['waitlist_notification'] ?? true); ?>>
                    <?php esc_html_e('Notify waitlist when slots become available', 'formflow'); ?>
                </label>
            </fieldset>
        </td>
    </tr>
</table>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var blackoutIndex = <?php echo count($blackout_dates); ?>;
    var container = document.getElementById('isf-blackout-dates');

    document.getElementById('isf-add-blackout-date').addEventListener('click', function() {
        var html = '<div class="isf-blackout-item">' +
            '<input type="date" name="settings[features][capacity_management][blackout_dates][' + blackoutIndex + '][date]">' +
            '<input type="text" name="settings[features][capacity_management][blackout_dates][' + blackoutIndex + '][reason]" placeholder="<?php esc_attr_e('Reason (optional)', 'formflow'); ?>">' +
            '<button type="button" class="button isf-remove-blackout">&times;</button>' +
            '</div>';
        container.insertAdjacentHTML('beforeend', html);
        blackoutIndex++;
    });

    document.getElementById('isf-add-blackout-range').addEventListener('click', function() {
        var html = '<div class="isf-blackout-item">' +
            '<input type="date" name="settings[features][capacity_management][blackout_dates][' + blackoutIndex + '][start]">' +
            '<span>to</span>' +
            '<input type="date" name="settings[features][capacity_management][blackout_dates][' + blackoutIndex + '][end]">' +
            '<input type="text" name="settings[features][capacity_management][blackout_dates][' + blackoutIndex + '][reason]" placeholder="<?php esc_attr_e('Reason (optional)', 'formflow'); ?>">' +
            '<button type="button" class="button isf-remove-blackout">&times;</button>' +
            '</div>';
        container.insertAdjacentHTML('beforeend', html);
        blackoutIndex++;
    });

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('isf-remove-blackout')) {
            e.target.closest('.isf-blackout-item').remove();
        }
    });
});
</script>
