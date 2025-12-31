<?php
/**
 * Admin View: Schedule Availability
 *
 * Displays available scheduling slots and promo codes from the API.
 */

if (!defined('ABSPATH')) {
    exit;
}

$time_labels = [
    'am' => '8:00 AM - 11:00 AM',
    'md' => '11:00 AM - 2:00 PM',
    'pm' => '2:00 PM - 5:00 PM',
    'ev' => '5:00 PM - 8:00 PM'
];
?>

<div class="wrap isf-admin-wrap">
    <h1><?php esc_html_e('Schedule Availability', 'formflow'); ?></h1>

    <!-- Instance Selector -->
    <div class="isf-card">
        <form method="get" action="">
            <input type="hidden" name="page" value="isf-scheduling">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="instance_id"><?php esc_html_e('Select Form Instance', 'formflow'); ?></label>
                    </th>
                    <td>
                        <select name="instance_id" id="instance_id" onchange="this.form.submit()">
                            <option value=""><?php esc_html_e('-- Select Instance --', 'formflow'); ?></option>
                            <?php foreach ($instances as $inst) : ?>
                                <option value="<?php echo esc_attr($inst['id']); ?>"
                                    <?php selected($instance_id, $inst['id']); ?>>
                                    <?php echo esc_html($inst['name']); ?>
                                    <?php if (!$inst['is_active']) echo ' (Inactive)'; ?>
                                    <?php if ($inst['settings']['demo_mode'] ?? false) echo ' [DEMO]'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <?php if ($instance) : ?>
        <div class="isf-scheduling-layout">
            <!-- Promo Codes Card -->
            <div class="isf-card">
                <h2><?php esc_html_e('Promotional Codes', 'formflow'); ?></h2>
                <?php if (!empty($promo_codes)) : ?>
                    <p class="description"><?php esc_html_e('These codes are available from the API for "How did you hear about us?" dropdown:', 'formflow'); ?></p>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Code', 'formflow'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($promo_codes as $code) : ?>
                                <tr>
                                    <td><code><?php echo esc_html($code); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="isf-empty-state">
                        <span class="dashicons dashicons-info"></span>
                        <?php esc_html_e('No promo codes available or unable to fetch from API.', 'formflow'); ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Schedule Availability Card -->
            <div class="isf-card isf-card-wide">
                <h2><?php esc_html_e('Available Installation Slots', 'formflow'); ?></h2>

                <?php
                // Get blocked dates and capacity limits
                $scheduling_settings = $instance['settings']['scheduling'] ?? [];
                $blocked_dates = [];
                $blocked_dates_labels = [];
                if (!empty($scheduling_settings['blocked_dates'])) {
                    foreach ($scheduling_settings['blocked_dates'] as $blocked) {
                        if (!empty($blocked['date'])) {
                            $blocked_dates[] = $blocked['date'];
                            if (!empty($blocked['label'])) {
                                $blocked_dates_labels[$blocked['date']] = $blocked['label'];
                            }
                        }
                    }
                }
                $capacity_limits = $scheduling_settings['capacity_limits'] ?? [];
                $capacity_enabled = !empty($capacity_limits['enabled']);
                ?>

                <?php if (!empty($blocked_dates)) : ?>
                    <div class="notice notice-warning inline" style="margin: 10px 0;">
                        <p>
                            <strong><?php esc_html_e('Blocked dates:', 'formflow'); ?></strong>
                            <?php
                            $blocked_display = [];
                            foreach ($blocked_dates as $bd) {
                                $label = $blocked_dates_labels[$bd] ?? '';
                                $formatted = date('M j, Y', strtotime($bd));
                                $blocked_display[] = $label ? "{$formatted} ({$label})" : $formatted;
                            }
                            echo esc_html(implode(', ', $blocked_display));
                            ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($capacity_enabled) : ?>
                    <div class="notice notice-info inline" style="margin: 10px 0;">
                        <p>
                            <strong><?php esc_html_e('Custom capacity limits active:', 'formflow'); ?></strong>
                            <?php
                            $limits_display = [];
                            $slot_names = ['am' => 'AM', 'md' => 'Mid-Day', 'pm' => 'PM', 'ev' => 'Evening'];
                            foreach (['am', 'md', 'pm', 'ev'] as $slot) {
                                if (isset($capacity_limits[$slot]) && $capacity_limits[$slot] !== '') {
                                    $limits_display[] = $slot_names[$slot] . ': ' . (int)$capacity_limits[$slot];
                                }
                            }
                            echo esc_html(implode(', ', $limits_display) ?: 'None');
                            ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (isset($schedule_data['error'])) : ?>
                    <div class="notice notice-error inline">
                        <p><?php echo esc_html($schedule_data['error']); ?></p>
                    </div>
                <?php elseif (!empty($schedule_data['slots'])) : ?>
                    <p class="description">
                        <?php esc_html_e('Showing available slots from', 'formflow'); ?>
                        <strong><?php echo esc_html($start_date); ?></strong>
                        <?php esc_html_e('onwards. Numbers indicate available capacity.', 'formflow'); ?>
                    </p>

                    <div class="isf-schedule-table-wrap">
                        <table class="widefat striped isf-schedule-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Date', 'formflow'); ?></th>
                                    <th class="isf-slot-col"><?php echo esc_html($time_labels['am']); ?></th>
                                    <th class="isf-slot-col"><?php echo esc_html($time_labels['md']); ?></th>
                                    <th class="isf-slot-col"><?php echo esc_html($time_labels['pm']); ?></th>
                                    <th class="isf-slot-col"><?php echo esc_html($time_labels['ev']); ?></th>
                                    <th class="isf-slot-col"><?php esc_html_e('Total', 'formflow'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedule_data['slots'] as $slot) : ?>
                                    <?php
                                    $date = $slot['date'];
                                    $normalized_date = date('Y-m-d', strtotime($date));
                                    $is_blocked = in_array($normalized_date, $blocked_dates);
                                    $blocked_label = $blocked_dates_labels[$normalized_date] ?? '';
                                    $times = $slot['times'] ?? [];
                                    $total = 0;

                                    $am_cap = $times['am']['capacity'] ?? 0;
                                    $md_cap = $times['md']['capacity'] ?? 0;
                                    $pm_cap = $times['pm']['capacity'] ?? 0;
                                    $ev_cap = $times['ev']['capacity'] ?? 0;

                                    // Apply capacity limits for display
                                    if ($capacity_enabled) {
                                        if (isset($capacity_limits['am']) && $capacity_limits['am'] !== '') {
                                            $am_cap = min($am_cap, (int)$capacity_limits['am']);
                                        }
                                        if (isset($capacity_limits['md']) && $capacity_limits['md'] !== '') {
                                            $md_cap = min($md_cap, (int)$capacity_limits['md']);
                                        }
                                        if (isset($capacity_limits['pm']) && $capacity_limits['pm'] !== '') {
                                            $pm_cap = min($pm_cap, (int)$capacity_limits['pm']);
                                        }
                                        if (isset($capacity_limits['ev']) && $capacity_limits['ev'] !== '') {
                                            $ev_cap = min($ev_cap, (int)$capacity_limits['ev']);
                                        }
                                    }

                                    $total = $am_cap + $md_cap + $pm_cap + $ev_cap;
                                    ?>
                                    <tr <?php echo $is_blocked ? 'class="isf-row-blocked"' : ''; ?>>
                                        <td>
                                            <strong><?php echo esc_html($slot['formatted_date'] ?? $date); ?></strong>
                                            <?php if ($is_blocked) : ?>
                                                <br><span class="isf-blocked-date-label"><?php echo esc_html($blocked_label ?: __('Blocked', 'formflow')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($is_blocked) : ?>
                                            <td colspan="5" class="isf-slot-col isf-slot-blocked" style="text-align: center;">
                                                <?php esc_html_e('Blocked', 'formflow'); ?>
                                            </td>
                                        <?php else : ?>
                                            <td class="isf-slot-col <?php echo $am_cap > 0 ? 'isf-slot-available' : 'isf-slot-unavailable'; ?>">
                                                <?php echo $am_cap > 0 ? esc_html($am_cap) : '&mdash;'; ?>
                                            </td>
                                            <td class="isf-slot-col <?php echo $md_cap > 0 ? 'isf-slot-available' : 'isf-slot-unavailable'; ?>">
                                                <?php echo $md_cap > 0 ? esc_html($md_cap) : '&mdash;'; ?>
                                            </td>
                                            <td class="isf-slot-col <?php echo $pm_cap > 0 ? 'isf-slot-available' : 'isf-slot-unavailable'; ?>">
                                                <?php echo $pm_cap > 0 ? esc_html($pm_cap) : '&mdash;'; ?>
                                            </td>
                                            <td class="isf-slot-col <?php echo $ev_cap > 0 ? 'isf-slot-available' : 'isf-slot-unavailable'; ?>">
                                                <?php echo $ev_cap > 0 ? esc_html($ev_cap) : '&mdash;'; ?>
                                            </td>
                                            <td class="isf-slot-col isf-slot-total">
                                                <?php echo esc_html($total); ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="isf-schedule-legend">
                        <span class="isf-legend-item">
                            <span class="isf-legend-dot isf-slot-available"></span>
                            <?php esc_html_e('Available', 'formflow'); ?>
                        </span>
                        <span class="isf-legend-item">
                            <span class="isf-legend-dot isf-slot-unavailable"></span>
                            <?php esc_html_e('No Availability', 'formflow'); ?>
                        </span>
                        <?php if (!empty($blocked_dates)) : ?>
                        <span class="isf-legend-item">
                            <span class="isf-legend-dot isf-slot-blocked"></span>
                            <?php esc_html_e('Blocked Date', 'formflow'); ?>
                        </span>
                        <?php endif; ?>
                    </div>

                <?php else : ?>
                    <p class="isf-empty-state">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <?php esc_html_e('No scheduling slots available or unable to fetch from API.', 'formflow'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

    <?php else : ?>
        <div class="isf-card">
            <p class="isf-empty-state">
                <span class="dashicons dashicons-info"></span>
                <?php esc_html_e('Please select a form instance to view scheduling availability.', 'formflow'); ?>
            </p>
        </div>
    <?php endif; ?>
</div>
