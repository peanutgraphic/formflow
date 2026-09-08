<?php
/**
 * Regression tests for the appointment confirmation screens.
 *
 * The success templates echoed the raw IntelliSOURCE time-slot code straight
 * into the "Time" row, so a customer who booked 11:00 AM - 2:00 PM was shown
 * "MD" on the confirmation page (the internal code for Midday, which also
 * happens to read as the state abbreviation for Maryland). These tests pin the
 * shared code -> customer-facing range helper the templates now use.
 */

namespace ISF\Tests\Unit;

use ISF\Utilities;
use PHPUnit\Framework\TestCase;

class ConfirmationTimeSlotDisplayTest extends TestCase
{
    /**
     * @dataProvider slotProvider
     */
    public function test_code_renders_as_customer_facing_range(string $code, string $expected): void
    {
        $this->assertSame($expected, Utilities::getTimeSlotDisplay($code));
    }

    public static function slotProvider(): array
    {
        return [
            'morning' => ['AM', '8:00 AM - 11:00 AM'],
            'midday' => ['MD', '11:00 AM - 2:00 PM'],
            'afternoon' => ['PM', '2:00 PM - 5:00 PM'],
            'evening' => ['EV', '5:00 PM - 8:00 PM'],
        ];
    }

    /**
     * The scheduler front end posts lowercase codes; the API has been seen to
     * return either case. Both must resolve rather than falling through.
     */
    public function test_lowercase_codes_resolve(): void
    {
        $this->assertSame('11:00 AM - 2:00 PM', Utilities::getTimeSlotDisplay('md'));
        $this->assertSame('5:00 PM - 8:00 PM', Utilities::getTimeSlotDisplay('ev'));
    }

    /**
     * The bug itself: "MD" must never survive to the screen as a bare code.
     */
    public function test_midday_code_is_not_echoed_verbatim(): void
    {
        $this->assertNotSame('MD', Utilities::getTimeSlotDisplay('MD'));
    }

    /**
     * An unknown code should degrade to something harmless rather than fatal,
     * but must not be mistaken for a real slot.
     */
    public function test_unknown_code_falls_back_to_empty_string(): void
    {
        $this->assertSame('', Utilities::getTimeSlotDisplay('ZZ'));
        $this->assertSame('', Utilities::getTimeSlotDisplay(''));
    }
}
