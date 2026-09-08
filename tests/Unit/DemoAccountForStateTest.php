<?php
/**
 * Regression tests for demo-mode account selection.
 *
 * The demo banner hardcoded account 1234567890 / ZIP 20001 on every form
 * instance. That account is a Washington, DC record, so following the banner's
 * own instructions on a Maryland program produced a scheduler screen headed
 * "Delmarva Maryland Residential Scheduler" above the address
 * "123 Main Street, Washington, DC 20001". The banner now advertises the demo
 * account whose state matches the instance's utility.
 */

namespace ISF\Tests\Unit;

use ISF\Api\MockApiClient;

class DemoAccountForStateTest extends TestCase
{
    /**
     * @dataProvider stateProvider
     */
    public function test_demo_account_matches_requested_state(string $state, string $expected_account): void
    {
        $account = MockApiClient::get_demo_account_for_state($state);

        $this->assertIsArray($account);
        $this->assertSame($expected_account, $account['account']);
        $this->assertSame($state, $account['state']);
    }

    public static function stateProvider(): array
    {
        return [
            'maryland' => ['MD', '9876543210'],
            'delaware' => ['DE', '5555555555'],
            'district of columbia' => ['DC', '1234567890'],
        ];
    }

    /**
     * The bug: a Maryland instance must not be handed the DC record.
     */
    public function test_maryland_is_not_given_the_dc_account(): void
    {
        $account = MockApiClient::get_demo_account_for_state('MD');

        $this->assertNotSame('1234567890', $account['account']);
        $this->assertNotSame('Washington', $account['city']);
    }

    /**
     * The advertised ZIP must actually validate against the advertised account,
     * otherwise the banner sends demo users into a dead end.
     */
    public function test_advertised_zip_validates_against_advertised_account(): void
    {
        $this->mockWpdb(['insert' => 1, 'insert_id' => 1, 'get_row' => null, 'get_var' => null]);

        foreach (['MD', 'DE', 'DC'] as $state) {
            $demo = MockApiClient::get_demo_account_for_state($state);

            $client = new MockApiClient(null);
            $result = $client->validate_account($demo['account'], $demo['zip']);

            $this->assertTrue(
                $result->is_valid(),
                sprintf('Demo account advertised for %s failed validation', $state)
            );
        }
    }

    /**
     * An unrecognised state should still yield a usable account rather than
     * blanking the banner.
     */
    public function test_unknown_state_falls_back_to_a_usable_account(): void
    {
        $account = MockApiClient::get_demo_account_for_state('ZZ');

        $this->assertIsArray($account);
        $this->assertNotEmpty($account['account']);
        $this->assertNotEmpty($account['zip']);
    }
}
