<?php
/**
 * Tests for TouchRecorder
 */

namespace ISF\Tests\Unit\Analytics;

use ISF\Tests\Unit\TestCase;
use ISF\Analytics\TouchRecorder;
use ISF\Analytics\VisitorTracker;
use Brain\Monkey\Functions;
use Mockery;

class TouchRecorderTest extends TestCase
{
    private $visitorTracker;
    private $wpdb;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset superglobals
        $_GET = [];
        $_SERVER = [
            'HTTP_HOST' => 'example.com',
            'REQUEST_URI' => '/enroll',
        ];

        // Mock visitor tracker
        $this->visitorTracker = Mockery::mock(VisitorTracker::class);
        $this->visitorTracker->shouldReceive('get_visitor_id')
            ->andReturn('abc123def456789012345678901234ab');

        // Mock wpdb
        $this->wpdb = $this->mockWpdb([
            'insert' => true,
            'insert_id' => 1,
            'get_results' => [],
        ]);
    }

    /**
     * Test valid touch types are accepted
     */
    public function testValidTouchTypesAreAccepted(): void
    {
        $validTypes = ['page_view', 'form_view', 'form_start', 'form_complete', 'handoff', 'return_visit'];

        foreach ($validTypes as $type) {
            $recorder = new TouchRecorder($this->visitorTracker);
            $result = $recorder->record_touch($type, 1);

            $this->assertIsInt($result, "Touch type '{$type}' should be accepted");
            $this->assertEquals(1, $result);
        }
    }

    /**
     * Test invalid touch type returns false
     */
    public function testInvalidTouchTypeReturnsFalse(): void
    {
        $recorder = new TouchRecorder($this->visitorTracker);
        $result = $recorder->record_touch('invalid_type', 1);

        $this->assertFalse($result);
    }

    /**
     * Test record_touch returns false when no visitor ID
     */
    public function testRecordTouchReturnsFalseWhenNoVisitor(): void
    {
        $visitorTracker = Mockery::mock(VisitorTracker::class);
        $visitorTracker->shouldReceive('get_visitor_id')->andReturn(null);

        $recorder = new TouchRecorder($visitorTracker);
        $result = $recorder->record_touch('page_view', 1);

        $this->assertFalse($result);
    }

    /**
     * Test UTM parameters are captured from request
     */
    public function testUtmParametersCapturedFromRequest(): void
    {
        $_GET = [
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'winter_promo',
            'utm_term' => 'energy savings',
            'utm_content' => 'banner_v2',
        ];

        $recorder = new TouchRecorder($this->visitorTracker);

        $reflection = new \ReflectionClass($recorder);
        $method = $reflection->getMethod('get_attribution_from_request');
        $method->setAccessible(true);

        $attribution = $method->invoke($recorder);

        $this->assertEquals('google', $attribution['utm_source']);
        $this->assertEquals('cpc', $attribution['utm_medium']);
        $this->assertEquals('winter_promo', $attribution['utm_campaign']);
        $this->assertEquals('energy savings', $attribution['utm_term']);
        $this->assertEquals('banner_v2', $attribution['utm_content']);
    }

    /**
     * Test click IDs are captured from request
     */
    public function testClickIdsCapturedFromRequest(): void
    {
        $_GET = [
            'gclid' => 'google_click_123',
            'fbclid' => 'facebook_click_456',
            'msclkid' => 'microsoft_click_789',
            'dclid' => 'doubleclick_123',
        ];

        $recorder = new TouchRecorder($this->visitorTracker);

        $reflection = new \ReflectionClass($recorder);
        $method = $reflection->getMethod('get_attribution_from_request');
        $method->setAccessible(true);

        $attribution = $method->invoke($recorder);

        $this->assertEquals('google_click_123', $attribution['gclid']);
        $this->assertEquals('facebook_click_456', $attribution['fbclid']);
        $this->assertEquals('microsoft_click_789', $attribution['msclkid']);
        $this->assertEquals('doubleclick_123', $attribution['dclid']);
    }

    /**
     * Test promo code captured from request
     */
    public function testPromoCodeCapturedFromRequest(): void
    {
        $_GET['promo'] = 'SAVE20';

        $recorder = new TouchRecorder($this->visitorTracker);

        $reflection = new \ReflectionClass($recorder);
        $method = $reflection->getMethod('get_attribution_from_request');
        $method->setAccessible(true);

        $attribution = $method->invoke($recorder);

        $this->assertEquals('SAVE20', $attribution['promo_code']);
    }

    /**
     * Test external referrer is captured
     */
    public function testExternalReferrerCaptured(): void
    {
        $_SERVER['HTTP_REFERER'] = 'https://www.google.com/search?q=test';

        $recorder = new TouchRecorder($this->visitorTracker);

        $reflection = new \ReflectionClass($recorder);
        $method = $reflection->getMethod('get_attribution_from_request');
        $method->setAccessible(true);

        $attribution = $method->invoke($recorder);

        $this->assertEquals('https://www.google.com/search?q=test', $attribution['referrer']);
        $this->assertEquals('google.com', $attribution['referrer_domain']);
    }

    /**
     * Test internal referrer is ignored
     */
    public function testInternalReferrerIgnored(): void
    {
        $_SERVER['HTTP_REFERER'] = 'http://example.com/other-page';

        $recorder = new TouchRecorder($this->visitorTracker);

        $reflection = new \ReflectionClass($recorder);
        $method = $reflection->getMethod('get_attribution_from_request');
        $method->setAccessible(true);

        $attribution = $method->invoke($recorder);

        $this->assertArrayNotHasKey('referrer', $attribution);
        $this->assertArrayNotHasKey('referrer_domain', $attribution);
    }

    /**
     * Test record_page_view calls record_touch correctly
     */
    public function testRecordPageViewCallsRecordTouch(): void
    {
        $recorder = new TouchRecorder($this->visitorTracker);
        $result = $recorder->record_page_view(1);

        $this->assertEquals(1, $result);
    }

    /**
     * Test record_form_view calls record_touch correctly
     */
    public function testRecordFormViewCallsRecordTouch(): void
    {
        $recorder = new TouchRecorder($this->visitorTracker);
        $result = $recorder->record_form_view(1);

        $this->assertEquals(1, $result);
    }

    /**
     * Test record_form_start includes extra data
     */
    public function testRecordFormStartIncludesExtraData(): void
    {
        $recorder = new TouchRecorder($this->visitorTracker);
        $result = $recorder->record_form_start(1, ['step' => 1, 'field' => 'account_number']);

        $this->assertEquals(1, $result);
    }

    /**
     * Test record_form_complete includes extra data
     */
    public function testRecordFormCompleteIncludesExtraData(): void
    {
        $recorder = new TouchRecorder($this->visitorTracker);
        $result = $recorder->record_form_complete(1, ['submission_id' => 123]);

        $this->assertEquals(1, $result);
    }

    /**
     * Test record_handoff includes destination info
     */
    public function testRecordHandoffIncludesDestinationInfo(): void
    {
        $recorder = new TouchRecorder($this->visitorTracker);
        $result = $recorder->record_handoff(1, 'https://external.com/enroll', 'token123');

        $this->assertEquals(1, $result);
    }

    /**
     * Test conversion rate calculation with valid values
     */
    public function testConversionRateCalculation(): void
    {
        $recorder = new TouchRecorder($this->visitorTracker);

        $reflection = new \ReflectionClass($recorder);
        $method = $reflection->getMethod('calculate_rate');
        $method->setAccessible(true);

        // 50 out of 100 = 50%
        $rate = $method->invoke($recorder, 100, 50);
        $this->assertEquals(50.0, $rate);

        // 25 out of 200 = 12.5%
        $rate = $method->invoke($recorder, 200, 25);
        $this->assertEquals(12.5, $rate);
    }

    /**
     * Test conversion rate is 0 when from value is 0
     */
    public function testConversionRateIsZeroWhenFromIsZero(): void
    {
        $recorder = new TouchRecorder($this->visitorTracker);

        $reflection = new \ReflectionClass($recorder);
        $method = $reflection->getMethod('calculate_rate');
        $method->setAccessible(true);

        $rate = $method->invoke($recorder, 0, 10);
        $this->assertEquals(0.0, $rate);
    }

    /**
     * Test get_funnel_data returns expected structure
     */
    public function testGetFunnelDataReturnsExpectedStructure(): void
    {
        $this->wpdb->shouldReceive('get_results')
            ->andReturn([
                ['touch_type' => 'page_view', 'count' => '100'],
                ['touch_type' => 'form_view', 'count' => '80'],
                ['touch_type' => 'form_start', 'count' => '60'],
                ['touch_type' => 'form_complete', 'count' => '40'],
                ['touch_type' => 'handoff', 'count' => '10'],
            ]);

        $recorder = new TouchRecorder($this->visitorTracker);
        $funnel = $recorder->get_funnel_data(1, '2024-01-01', '2024-01-31');

        $this->assertArrayHasKey('page_views', $funnel);
        $this->assertArrayHasKey('form_views', $funnel);
        $this->assertArrayHasKey('form_starts', $funnel);
        $this->assertArrayHasKey('form_completes', $funnel);
        $this->assertArrayHasKey('handoffs', $funnel);
        $this->assertArrayHasKey('conversion_rates', $funnel);

        $this->assertEquals(100, $funnel['page_views']);
        $this->assertEquals(80, $funnel['form_views']);
        $this->assertEquals(60, $funnel['form_starts']);
        $this->assertEquals(40, $funnel['form_completes']);
        $this->assertEquals(10, $funnel['handoffs']);
    }

    /**
     * Test domain extraction strips www prefix
     */
    public function testDomainExtractionStripsWwwPrefix(): void
    {
        $recorder = new TouchRecorder($this->visitorTracker);

        $reflection = new \ReflectionClass($recorder);
        $method = $reflection->getMethod('extract_domain');
        $method->setAccessible(true);

        $domain = $method->invoke($recorder, 'https://www.example.com/page');
        $this->assertEquals('example.com', $domain);

        $domain = $method->invoke($recorder, 'https://example.com/page');
        $this->assertEquals('example.com', $domain);
    }

    /**
     * Test domain extraction handles invalid URLs
     */
    public function testDomainExtractionHandlesInvalidUrls(): void
    {
        $recorder = new TouchRecorder($this->visitorTracker);

        $reflection = new \ReflectionClass($recorder);
        $method = $reflection->getMethod('extract_domain');
        $method->setAccessible(true);

        $domain = $method->invoke($recorder, 'not-a-valid-url');
        $this->assertEquals('', $domain);
    }

    /**
     * Test cleanup_old_touches removes old records
     */
    public function testCleanupOldTouchesRemovesOldRecords(): void
    {
        $this->wpdb->shouldReceive('query')
            ->once()
            ->andReturn(100);

        $recorder = new TouchRecorder($this->visitorTracker);
        $deleted = $recorder->cleanup_old_touches(365);

        $this->assertEquals(100, $deleted);
    }

    /**
     * Test get_visitor_touches decodes JSON fields
     */
    public function testGetVisitorTouchesDecodesJsonFields(): void
    {
        $this->wpdb->shouldReceive('get_results')
            ->andReturn([
                [
                    'id' => 1,
                    'touch_type' => 'form_view',
                    'touch_data' => '{"step":1,"field":"account"}',
                ],
                [
                    'id' => 2,
                    'touch_type' => 'form_complete',
                    'touch_data' => '{"submission_id":123}',
                ],
            ]);

        $recorder = new TouchRecorder($this->visitorTracker);
        $touches = $recorder->get_visitor_touches('visitor123');

        $this->assertIsArray($touches[0]['touch_data']);
        $this->assertEquals(1, $touches[0]['touch_data']['step']);
        $this->assertIsArray($touches[1]['touch_data']);
        $this->assertEquals(123, $touches[1]['touch_data']['submission_id']);
    }

    /**
     * Test get_visitor_touches handles null JSON
     */
    public function testGetVisitorTouchesHandlesNullJson(): void
    {
        $this->wpdb->shouldReceive('get_results')
            ->andReturn([
                [
                    'id' => 1,
                    'touch_type' => 'page_view',
                    'touch_data' => null,
                ],
            ]);

        $recorder = new TouchRecorder($this->visitorTracker);
        $touches = $recorder->get_visitor_touches('visitor123');

        $this->assertIsArray($touches[0]['touch_data']);
        $this->assertEmpty($touches[0]['touch_data']);
    }
}
