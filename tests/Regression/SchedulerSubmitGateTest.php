<?php
/**
 * Regression guard for the scheduler's submit gate.
 *
 * The scheduler's "Confirm Appointment" button ships disabled because that
 * flow has no skip path. Nothing ever cleared the attribute, so picking a date
 * and a time left the customer on a fully populated screen with a dead button
 * and no way to finish booking.
 *
 * There is no JS test runner in this repo, so this pins the template/JS
 * contract statically: any submit button that ships disabled must carry the
 * marker the slot handler looks for, and the handler must both release and
 * re-gate it.
 */

namespace ISF\Tests\Regression;

use PHPUnit\Framework\TestCase;

class SchedulerSubmitGateTest extends TestCase
{
    private const MARKER = 'data-requires-timeslot';

    private function pluginPath(string $relative): string
    {
        return dirname(__DIR__, 2) . '/' . $relative;
    }

    private function js(): string
    {
        return file_get_contents($this->pluginPath('public/assets/js/enrollment.js'));
    }

    /**
     * Every scheduling step that ships its submit button disabled must mark it,
     * otherwise no JS path will ever enable it.
     */
    public function test_disabled_submit_buttons_carry_the_release_marker(): void
    {
        $templates = [
            'includes/intellisource/templates/scheduler/step-2-schedule.php',
            'includes/intellisource/templates/enrollment/step-4-schedule.php',
        ];

        foreach ($templates as $template) {
            $markup = file_get_contents($this->pluginPath($template));

            preg_match_all('/<button\b[^>]*\bisf-btn-next\b[^>]*>/s', $markup, $matches);
            $this->assertNotEmpty($matches[0], "No submit button found in {$template}");

            foreach ($matches[0] as $button) {
                // Only buttons that ship disabled need the marker; a button
                // that starts enabled (the skippable enrollment step) is fine.
                if (!preg_match('/\bdisabled\b/', $button)) {
                    continue;
                }

                $this->assertStringContainsString(
                    self::MARKER,
                    $button,
                    "Disabled submit button in {$template} has no way to be enabled"
                );
            }
        }
    }

    /**
     * The scheduler specifically must still gate its button - if someone drops
     * the disabled attribute the flow would submit with no slot chosen.
     */
    public function test_scheduler_button_ships_gated(): void
    {
        $markup = file_get_contents(
            $this->pluginPath('includes/intellisource/templates/scheduler/step-2-schedule.php')
        );

        $this->assertMatchesRegularExpression(
            '/<button\b[^>]*\bisf-btn-next\b[^>]*\bdisabled\b/s',
            $markup,
            'Scheduler submit button must ship disabled until a slot is chosen'
        );
    }

    /**
     * The slot handler must release the marked button.
     */
    public function test_slot_selection_releases_the_marked_button(): void
    {
        $this->assertMatchesRegularExpression(
            '/\[' . preg_quote(self::MARKER, '/') . '\][^\n]*prop\(\s*[\'"]disabled[\'"]\s*,\s*false\s*\)/',
            $this->js(),
            'Selecting a time slot must enable the gated submit button'
        );
    }

    /**
     * Changing the date clears the slot, so the button must be re-gated -
     * otherwise a customer could submit a date with a stale or absent time.
     */
    public function test_changing_the_date_re_gates_the_button(): void
    {
        $this->assertMatchesRegularExpression(
            '/\[' . preg_quote(self::MARKER, '/') . '\][^\n]*prop\(\s*[\'"]disabled[\'"]\s*,\s*true\s*\)/',
            $this->js(),
            'Reloading time slots must re-disable the gated submit button'
        );
    }
}
