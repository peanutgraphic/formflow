<?php
/**
 * Tests for AttributionCalculator
 */

namespace ISF\Tests\Unit\Analytics;

use ISF\Tests\Unit\TestCase;
use ISF\Analytics\AttributionCalculator;
use Brain\Monkey\Functions;

class AttributionCalculatorTest extends TestCase
{
    private $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the Database class
        $this->mockDatabase();

        // Mock wpdb for query operations
        $this->mockWpdb();
    }

    /**
     * Test that first touch attribution gives 100% credit to first touch
     */
    public function testFirstTouchAttributionGivesFullCreditToFirstTouch(): void
    {
        $journey = [
            ['id' => 1, 'utm_source' => 'google', 'utm_medium' => 'cpc', 'referrer_domain' => null, 'created_at' => '2024-01-01 10:00:00'],
            ['id' => 2, 'utm_source' => 'facebook', 'utm_medium' => 'social', 'referrer_domain' => null, 'created_at' => '2024-01-02 10:00:00'],
            ['id' => 3, 'utm_source' => 'email', 'utm_medium' => 'newsletter', 'referrer_domain' => null, 'created_at' => '2024-01-03 10:00:00'],
        ];

        $credits = $this->invokeCalculateCredits($journey, AttributionCalculator::MODEL_FIRST_TOUCH);

        $this->assertArrayHasKey(1, $credits);
        $this->assertEquals(1.0, $credits[1]);
        $this->assertCount(1, $credits);
    }

    /**
     * Test that last touch attribution gives 100% credit to last touch
     */
    public function testLastTouchAttributionGivesFullCreditToLastTouch(): void
    {
        $journey = [
            ['id' => 1, 'utm_source' => 'google', 'utm_medium' => 'cpc', 'referrer_domain' => null, 'created_at' => '2024-01-01 10:00:00'],
            ['id' => 2, 'utm_source' => 'facebook', 'utm_medium' => 'social', 'referrer_domain' => null, 'created_at' => '2024-01-02 10:00:00'],
            ['id' => 3, 'utm_source' => 'email', 'utm_medium' => 'newsletter', 'referrer_domain' => null, 'created_at' => '2024-01-03 10:00:00'],
        ];

        $credits = $this->invokeCalculateCredits($journey, AttributionCalculator::MODEL_LAST_TOUCH);

        $this->assertArrayHasKey(3, $credits);
        $this->assertEquals(1.0, $credits[3]);
        $this->assertCount(1, $credits);
    }

    /**
     * Test that linear attribution splits credit equally
     */
    public function testLinearAttributionSplitsCreditEqually(): void
    {
        $journey = [
            ['id' => 1, 'utm_source' => 'google', 'utm_medium' => 'cpc', 'referrer_domain' => null, 'created_at' => '2024-01-01 10:00:00'],
            ['id' => 2, 'utm_source' => 'facebook', 'utm_medium' => 'social', 'referrer_domain' => null, 'created_at' => '2024-01-02 10:00:00'],
            ['id' => 3, 'utm_source' => 'email', 'utm_medium' => 'newsletter', 'referrer_domain' => null, 'created_at' => '2024-01-03 10:00:00'],
        ];

        $credits = $this->invokeCalculateCredits($journey, AttributionCalculator::MODEL_LINEAR);

        $this->assertCount(3, $credits);
        $expectedCredit = 1.0 / 3;
        foreach ($credits as $credit) {
            $this->assertEqualsWithDelta($expectedCredit, $credit, 0.0001);
        }

        // Total credits should sum to 1
        $this->assertEqualsWithDelta(1.0, array_sum($credits), 0.0001);
    }

    /**
     * Test position-based attribution (40/20/40 split)
     */
    public function testPositionBasedAttributionWith40_20_40Split(): void
    {
        $journey = [
            ['id' => 1, 'utm_source' => 'google', 'utm_medium' => 'cpc', 'referrer_domain' => null, 'created_at' => '2024-01-01 10:00:00'],
            ['id' => 2, 'utm_source' => 'facebook', 'utm_medium' => 'social', 'referrer_domain' => null, 'created_at' => '2024-01-02 10:00:00'],
            ['id' => 3, 'utm_source' => 'twitter', 'utm_medium' => 'social', 'referrer_domain' => null, 'created_at' => '2024-01-03 10:00:00'],
            ['id' => 4, 'utm_source' => 'email', 'utm_medium' => 'newsletter', 'referrer_domain' => null, 'created_at' => '2024-01-04 10:00:00'],
        ];

        $credits = $this->invokeCalculateCredits($journey, AttributionCalculator::MODEL_POSITION_BASED);

        // First touch gets 40%
        $this->assertEqualsWithDelta(0.4, $credits[1], 0.0001);

        // Last touch gets 40%
        $this->assertEqualsWithDelta(0.4, $credits[4], 0.0001);

        // Middle touches split 20% (2 touches = 10% each)
        $this->assertEqualsWithDelta(0.1, $credits[2], 0.0001);
        $this->assertEqualsWithDelta(0.1, $credits[3], 0.0001);

        // Total should sum to 1
        $this->assertEqualsWithDelta(1.0, array_sum($credits), 0.0001);
    }

    /**
     * Test position-based with only 2 touches splits 50/50
     */
    public function testPositionBasedAttributionWithTwoTouchesSplits50_50(): void
    {
        $journey = [
            ['id' => 1, 'utm_source' => 'google', 'utm_medium' => 'cpc', 'referrer_domain' => null, 'created_at' => '2024-01-01 10:00:00'],
            ['id' => 2, 'utm_source' => 'email', 'utm_medium' => 'newsletter', 'referrer_domain' => null, 'created_at' => '2024-01-02 10:00:00'],
        ];

        $credits = $this->invokeCalculateCredits($journey, AttributionCalculator::MODEL_POSITION_BASED);

        $this->assertEqualsWithDelta(0.5, $credits[1], 0.0001);
        $this->assertEqualsWithDelta(0.5, $credits[2], 0.0001);
    }

    /**
     * Test single touch gets 100% credit regardless of model
     */
    public function testSingleTouchGetsFullCreditInAnyModel(): void
    {
        $journey = [
            ['id' => 1, 'utm_source' => 'google', 'utm_medium' => 'cpc', 'referrer_domain' => null, 'created_at' => '2024-01-01 10:00:00'],
        ];

        $models = [
            AttributionCalculator::MODEL_FIRST_TOUCH,
            AttributionCalculator::MODEL_LAST_TOUCH,
            AttributionCalculator::MODEL_LINEAR,
            AttributionCalculator::MODEL_TIME_DECAY,
            AttributionCalculator::MODEL_POSITION_BASED,
        ];

        foreach ($models as $model) {
            $credits = $this->invokeCalculateCredits($journey, $model);
            $this->assertCount(1, $credits, "Model {$model} should return 1 credit entry");
            $this->assertEquals(1.0, $credits[1], "Model {$model} should give 100% credit");
        }
    }

    /**
     * Test time decay gives more credit to recent touches
     */
    public function testTimeDecayGivesMoreCreditToRecentTouches(): void
    {
        // Touch 3 is most recent (same time as conversion)
        // Touch 1 is oldest
        $journey = [
            ['id' => 1, 'utm_source' => 'google', 'utm_medium' => 'cpc', 'referrer_domain' => null, 'created_at' => '2024-01-01 10:00:00'],
            ['id' => 2, 'utm_source' => 'facebook', 'utm_medium' => 'social', 'referrer_domain' => null, 'created_at' => '2024-01-08 10:00:00'],
            ['id' => 3, 'utm_source' => 'email', 'utm_medium' => 'newsletter', 'referrer_domain' => null, 'created_at' => '2024-01-15 10:00:00'],
        ];

        $credits = $this->invokeCalculateCredits($journey, AttributionCalculator::MODEL_TIME_DECAY);

        // Most recent touch should have highest credit
        $this->assertGreaterThan($credits[2], $credits[3]);
        $this->assertGreaterThan($credits[1], $credits[2]);

        // Total should sum to 1
        $this->assertEqualsWithDelta(1.0, array_sum($credits), 0.0001);
    }

    /**
     * Test empty journey returns empty credits
     */
    public function testEmptyJourneyReturnsEmptyCredits(): void
    {
        $credits = $this->invokeCalculateCredits([], AttributionCalculator::MODEL_FIRST_TOUCH);
        $this->assertEmpty($credits);
    }

    /**
     * Test journey with only non-attributable touches falls back to first page view
     */
    public function testJourneyWithOnlyPageViewsFallsBackToFirst(): void
    {
        $journey = [
            ['id' => 1, 'utm_source' => null, 'utm_medium' => null, 'referrer_domain' => null, 'touch_type' => 'page_view', 'created_at' => '2024-01-01 10:00:00'],
            ['id' => 2, 'utm_source' => null, 'utm_medium' => null, 'referrer_domain' => null, 'touch_type' => 'page_view', 'created_at' => '2024-01-02 10:00:00'],
        ];

        $credits = $this->invokeCalculateCredits($journey, AttributionCalculator::MODEL_FIRST_TOUCH);

        $this->assertArrayHasKey(1, $credits);
        $this->assertEquals(1.0, $credits[1]);
    }

    /**
     * Test referrer_domain is used when utm_source is null
     */
    public function testReferrerDomainUsedWhenUtmSourceIsNull(): void
    {
        $journey = [
            ['id' => 1, 'utm_source' => null, 'utm_medium' => null, 'referrer_domain' => 'google.com', 'created_at' => '2024-01-01 10:00:00'],
            ['id' => 2, 'utm_source' => 'facebook', 'utm_medium' => 'social', 'referrer_domain' => null, 'created_at' => '2024-01-02 10:00:00'],
        ];

        $credits = $this->invokeCalculateCredits($journey, AttributionCalculator::MODEL_LINEAR);

        // Both touches should be attributable
        $this->assertCount(2, $credits);
        $this->assertArrayHasKey(1, $credits);
        $this->assertArrayHasKey(2, $credits);
    }

    /**
     * Test median calculation with odd number of values
     */
    public function testCalculateMedianWithOddValues(): void
    {
        $median = $this->invokeCalculateMedian([1, 3, 5, 7, 9]);
        $this->assertEquals(5, $median);
    }

    /**
     * Test median calculation with even number of values
     */
    public function testCalculateMedianWithEvenValues(): void
    {
        $median = $this->invokeCalculateMedian([1, 2, 3, 4]);
        $this->assertEquals(2.5, $median);
    }

    /**
     * Test median calculation with empty array
     */
    public function testCalculateMedianWithEmptyArray(): void
    {
        $median = $this->invokeCalculateMedian([]);
        $this->assertEquals(0, $median);
    }

    /**
     * Helper method to invoke private calculate_credits method
     */
    private function invokeCalculateCredits(array $journey, string $model): array
    {
        $calculator = new AttributionCalculator();
        $reflection = new \ReflectionClass($calculator);
        $method = $reflection->getMethod('calculate_credits');
        $method->setAccessible(true);

        return $method->invoke($calculator, $journey, $model);
    }

    /**
     * Helper method to invoke private calculate_median method
     */
    private function invokeCalculateMedian(array $values): float
    {
        $calculator = new AttributionCalculator();
        $reflection = new \ReflectionClass($calculator);
        $method = $reflection->getMethod('calculate_median');
        $method->setAccessible(true);

        return $method->invoke($calculator, $values);
    }
}
