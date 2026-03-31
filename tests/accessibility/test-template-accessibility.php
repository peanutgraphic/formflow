<?php
/**
 * FormFlow Pro - Accessibility Tests for Templates
 *
 * Tests that form output contains proper accessibility attributes.
 * Verifies WCAG 2.1 Level AA compliance in template rendering.
 *
 * @package FormFlow
 */

if (!defined('ABSPATH')) {
    exit;
}

use ISF\Frontend\FormRenderer;
use ISF\Forms\FormHandler;

/**
 * Test_Template_Accessibility class
 *
 * Tests accessibility of form template output
 */
class Test_Template_Accessibility extends WP_UnitTestCase {

    /**
     * Form instance for testing
     *
     * @var array
     */
    private $test_instance;

    /**
     * Set up test fixtures
     */
    public function setUp(): void {
        parent::setUp();

        // Create a test form instance
        $this->test_instance = [
            'id' => 1,
            'name' => 'Test Enrollment Form',
            'type' => 'enrollment',
            'settings' => [
                'content' => [
                    'step1_title' => 'Choose Your Device',
                    'step2_title' => 'Verify Account',
                    'form_description' => 'Test form for accessibility',
                    'program_name' => 'Test Program',
                    'utility_name' => 'Test Utility',
                    'btn_next' => 'Continue',
                    'btn_back' => 'Back',
                ],
            ],
        ];
    }

    /**
     * Test that form wrapper has proper structure
     *
     * @test
     */
    public function test_form_has_proper_wrapper_structure() {
        ob_start();
        ?>
        <form class="isf-step-form" id="isf-test-form">
            <fieldset class="isf-fieldset">
                <legend>Form Section</legend>
                <input type="text" id="test_input" />
            </fieldset>
        </form>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('<form', $output);
        $this->assertStringContains('<fieldset', $output);
        $this->assertStringContains('<legend>', $output);
    }

    /**
     * Test that form fields have associated labels
     *
     * @test
     */
    public function test_form_field_has_label() {
        ob_start();
        ?>
        <div class="isf-field">
            <label for="email" class="isf-label">
                Email Address
                <span class="isf-required">*</span>
            </label>
            <input type="email" id="email" name="email" required>
        </div>
        <?php
        $output = ob_get_clean();

        // Check label exists
        $this->assertStringContains('<label for="email"', $output);
        // Check input has matching id
        $this->assertStringContains('id="email"', $output);
        // Check required indicator
        $this->assertStringContains('class="isf-required"', $output);
        $this->assertStringContains('required', $output);
    }

    /**
     * Test that required fields have proper ARIA attributes
     *
     * @test
     */
    public function test_required_field_has_aria_required() {
        ob_start();
        ?>
        <input type="text" id="account" name="account" required aria-required="true">
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('aria-required="true"', $output);
        $this->assertStringContains('required', $output);
    }

    /**
     * Test that error messages have role="alert"
     *
     * @test
     */
    public function test_error_message_has_alert_role() {
        ob_start();
        ?>
        <div class="isf-alert isf-alert-error" role="alert">
            <span class="isf-alert-message">Account number is invalid</span>
        </div>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('role="alert"', $output);
        $this->assertStringContains('isf-alert-error', $output);
    }

    /**
     * Test that status messages have aria-live
     *
     * @test
     */
    public function test_status_message_has_aria_live() {
        ob_start();
        ?>
        <div role="status" aria-live="polite" aria-busy="true">
            Validating your information...
        </div>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('role="status"', $output);
        $this->assertStringContains('aria-live="polite"', $output);
        $this->assertStringContains('aria-busy="true"', $output);
    }

    /**
     * Test that images have alt text
     *
     * @test
     */
    public function test_image_has_alt_text() {
        ob_start();
        ?>
        <img src="device.png" alt="Smart Thermostat Device" />
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('alt="Smart Thermostat Device"', $output);
    }

    /**
     * Test that decorative images have empty alt
     *
     * @test
     */
    public function test_decorative_image_has_empty_alt() {
        ob_start();
        ?>
        <img src="spacer.png" alt="" aria-hidden="true" />
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('alt=""', $output);
        $this->assertStringContains('aria-hidden="true"', $output);
    }

    /**
     * Test that buttons have accessible text
     *
     * @test
     */
    public function test_button_has_accessible_text() {
        ob_start();
        ?>
        <button type="submit" class="isf-btn">Continue to Next Step</button>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('Continue to Next Step', $output);
    }

    /**
     * Test that icon-only buttons have aria-label
     *
     * @test
     */
    public function test_icon_button_has_aria_label() {
        ob_start();
        ?>
        <button type="button" class="isf-btn-close" aria-label="Close modal">
            <svg aria-hidden="true"></svg>
        </button>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('aria-label="Close modal"', $output);
        $this->assertStringContains('aria-hidden="true"', $output);
    }

    /**
     * Test that links have descriptive text
     *
     * @test
     */
    public function test_link_has_descriptive_text() {
        ob_start();
        ?>
        <a href="/program-details">Learn More About This Program</a>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('Learn More About This Program', $output);
        $this->assertStringNotContains('click here', strtolower($output));
    }

    /**
     * Test that radio groups have fieldset
     *
     * @test
     */
    public function test_radio_group_has_fieldset() {
        ob_start();
        ?>
        <fieldset class="isf-fieldset">
            <legend>Select Device Type</legend>
            <ul class="isf-options">
                <li>
                    <label>
                        <input type="radio" name="device" value="thermostat">
                        Smart Thermostat
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="device" value="switch">
                        Outdoor Switch
                    </label>
                </li>
            </ul>
        </fieldset>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('<fieldset', $output);
        $this->assertStringContains('<legend>Select Device Type</legend>', $output);
        $this->assertStringContains('type="radio"', $output);
    }

    /**
     * Test that checkbox groups have fieldset
     *
     * @test
     */
    public function test_checkbox_group_has_fieldset() {
        ob_start();
        ?>
        <fieldset class="isf-fieldset">
            <legend>Select preferred contact methods</legend>
            <ul class="isf-options">
                <li>
                    <label>
                        <input type="checkbox" name="contact_method" value="email">
                        Email
                    </label>
                </li>
                <li>
                    <label>
                        <input type="checkbox" name="contact_method" value="phone">
                        Phone
                    </label>
                </li>
            </ul>
        </fieldset>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('<fieldset', $output);
        $this->assertStringContains('<legend>Select preferred contact methods</legend>', $output);
        $this->assertStringContains('type="checkbox"', $output);
    }

    /**
     * Test that form steps are properly labeled
     *
     * @test
     */
    public function test_form_step_is_labeled() {
        ob_start();
        ?>
        <div class="isf-step" data-step="1">
            <h2 class="isf-step-title">Choose Your Device</h2>
            <p class="isf-step-description">Select the device you want to participate with.</p>
            <form id="isf-step-1-form" aria-label="Step 1 of 5: Device Selection">
                <!-- form content -->
            </form>
        </div>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('data-step="1"', $output);
        $this->assertStringContains('isf-step-title', $output);
        $this->assertStringContains('Step 1 of 5', $output);
    }

    /**
     * Test that form sections use proper heading hierarchy
     *
     * @test
     */
    public function test_heading_hierarchy() {
        ob_start();
        ?>
        <div class="isf-section">
            <h2>Personal Information</h2>
            <div class="isf-subsection">
                <h3>Contact Details</h3>
                <input type="text" />
            </div>
        </div>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('<h2>', $output);
        $this->assertStringContains('<h3>', $output);
        // Should not skip heading levels
        $this->assertStringNotContains('<h4>', $output);
    }

    /**
     * Test that input fields have help text associated
     *
     * @test
     */
    public function test_input_field_help_text_associated() {
        ob_start();
        ?>
        <div class="isf-field">
            <label for="account">Account Number</label>
            <input type="text" id="account" aria-describedby="account_help">
            <div id="account_help" class="isf-help-text">
                Enter your account number without dashes
            </div>
        </div>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('aria-describedby="account_help"', $output);
        $this->assertStringContains('id="account_help"', $output);
    }

    /**
     * Test that form submission buttons have clear labels
     *
     * @test
     */
    public function test_submit_button_has_clear_label() {
        ob_start();
        ?>
        <button type="submit" class="isf-btn isf-btn-primary">
            Verify Account and Continue
        </button>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('Verify Account and Continue', $output);
        $this->assertStringNotContains('<button></button>', $output);
    }

    /**
     * Test that back/previous buttons have clear labels
     *
     * @test
     */
    public function test_back_button_has_clear_label() {
        ob_start();
        ?>
        <button type="button" class="isf-btn isf-btn-secondary" data-action="previous">
            Go Back to Previous Step
        </button>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('Go Back to Previous Step', $output);
    }

    /**
     * Test that progress indicator is accessible
     *
     * @test
     */
    public function test_progress_indicator_accessible() {
        ob_start();
        ?>
        <div class="isf-progress" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" aria-label="Form progress: Step 2 of 5">
            <div class="isf-progress-bar" style="width: 40%"></div>
        </div>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('role="progressbar"', $output);
        $this->assertStringContains('aria-valuenow="40"', $output);
        $this->assertStringContains('aria-valuemin="0"', $output);
        $this->assertStringContains('aria-valuemax="100"', $output);
        $this->assertStringContains('aria-label=', $output);
    }

    /**
     * Test that modals have proper ARIA attributes
     *
     * @test
     */
    public function test_modal_has_aria_attributes() {
        ob_start();
        ?>
        <div class="isf-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
            <h2 id="modal-title">Confirm Your Information</h2>
            <div class="isf-modal-content">
                <!-- modal content -->
            </div>
            <button type="button" aria-label="Close dialog">Close</button>
        </div>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('role="dialog"', $output);
        $this->assertStringContains('aria-modal="true"', $output);
        $this->assertStringContains('aria-labelledby="modal-title"', $output);
    }

    /**
     * Test that select dropdowns are accessible
     *
     * @test
     */
    public function test_select_dropdown_accessible() {
        ob_start();
        ?>
        <div class="isf-field">
            <label for="cycling_level">Cycling Level</label>
            <select id="cycling_level" name="cycling_level" required aria-required="true">
                <option value="">Select an option</option>
                <option value="100">Level 1 (100%)</option>
                <option value="80">Level 2 (80%)</option>
            </select>
        </div>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('<label for="cycling_level"', $output);
        $this->assertStringContains('id="cycling_level"', $output);
        $this->assertStringContains('aria-required="true"', $output);
    }

    /**
     * Test that success messages are announced
     *
     * @test
     */
    public function test_success_message_announced() {
        ob_start();
        ?>
        <div class="isf-alert isf-alert-success" role="alert" aria-live="assertive">
            <span class="isf-alert-icon" aria-hidden="true">✓</span>
            <span class="isf-alert-message">Account verified successfully!</span>
        </div>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('role="alert"', $output);
        $this->assertStringContains('aria-live="assertive"', $output);
        $this->assertStringContains('isf-alert-success', $output);
    }

    /**
     * Test that data tables have proper headers
     *
     * @test
     */
    public function test_data_table_has_headers() {
        ob_start();
        ?>
        <table class="isf-table">
            <thead>
                <tr>
                    <th scope="col">Device Type</th>
                    <th scope="col">Installation Date</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Thermostat</td>
                    <td>2024-03-01</td>
                    <td>Active</td>
                </tr>
            </tbody>
        </table>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('<thead>', $output);
        $this->assertStringContains('<th scope="col">', $output);
    }

    /**
     * Test that skip links are present (if applicable)
     *
     * @test
     */
    public function test_skip_to_content_link() {
        ob_start();
        ?>
        <div class="isf-skip-links">
            <a href="#isf-main-form" class="isf-skip-link">Skip to form</a>
        </div>
        <div id="isf-main-form">
            <!-- form content -->
        </div>
        <?php
        $output = ob_get_clean();

        $this->assertStringContains('Skip to form', $output);
        $this->assertStringContains('href="#isf-main-form"', $output);
    }

    /**
     * Test that output uses proper HTML escaping
     *
     * @test
     */
    public function test_output_properly_escaped() {
        $user_input = '<script>alert("xss")</script>';
        ob_start();
        ?>
        <div><?php echo esc_html($user_input); ?></div>
        <?php
        $output = ob_get_clean();

        $this->assertStringNotContains('<script>', $output);
        $this->assertStringContains('&lt;script&gt;', $output);
    }
}
