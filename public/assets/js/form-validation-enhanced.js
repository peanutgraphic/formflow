/**
 * Enhanced Form Validation & Accessibility
 *
 * Provides inline validation, better error messages, accessibility improvements,
 * and progressive enhancement for FormFlow forms.
 *
 * @package FormFlow
 * @since 2.8.0
 */

(function($) {
    'use strict';

    class EnhancedFormValidator {
        constructor(form) {
            this.$form = $(form);
            this.errors = {};
            this.touched = {};
            this.currentStep = 1;
            this.totalSteps = this.$form.find('.isf-step').length;

            this.init();
        }

        init() {
            this.setupAccessibility();
            this.bindEvents();
            this.initializeProgress();
        }

        /**
         * Setup accessibility features
         */
        setupAccessibility() {
            // Add ARIA labels to form
            this.$form.attr({
                'role': 'form',
                'aria-label': this.$form.data('form-title') || 'Form',
                'novalidate': 'novalidate' // We'll handle validation
            });

            // Add ARIA attributes to fields
            this.$form.find('[required]').each((i, field) => {
                const $field = $(field);
                const $wrapper = $field.closest('.isf-field-wrapper');
                const fieldId = $field.attr('id') || 'field_' + i;
                const errorId = fieldId + '_error';
                const descId = fieldId + '_desc';

                // Ensure field has ID
                if (!$field.attr('id')) {
                    $field.attr('id', fieldId);
                }

                // Link label properly
                const $label = $wrapper.find('label');
                if ($label.length && !$label.attr('for')) {
                    $label.attr('for', fieldId);
                }

                // Add aria-required
                $field.attr('aria-required', 'true');

                // Add aria-describedby for errors and descriptions
                const ariaDescribedBy = [];

                const $description = $wrapper.find('.isf-field-description');
                if ($description.length) {
                    $description.attr('id', descId);
                    ariaDescribedBy.push(descId);
                }

                // Error container
                if (!$wrapper.find('.isf-field-error').length) {
                    $wrapper.append(`<div class="isf-field-error" id="${errorId}" role="alert" aria-live="polite" aria-atomic="true"></div>`);
                }
                ariaDescribedBy.push(errorId);

                if (ariaDescribedBy.length) {
                    $field.attr('aria-describedby', ariaDescribedBy.join(' '));
                }
            });

            // Add role to error messages
            this.$form.find('.isf-form-errors').attr('role', 'alert').attr('aria-live', 'assertive');

            // Add keyboard navigation
            this.setupKeyboardNavigation();
        }

        /**
         * Setup keyboard navigation
         */
        setupKeyboardNavigation() {
            // Tab order management
            this.$form.on('keydown', (e) => {
                // Ctrl/Cmd + Enter to submit
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    this.handleSubmit(e);
                }
            });

            // Focus management for steps
            this.$form.find('.isf-btn-next, .isf-btn-prev').attr('role', 'button');
        }

        /**
         * Initialize progress bar
         */
        initializeProgress() {
            if (this.totalSteps <= 1) return;

            const $progress = this.$form.find('.isf-progress-bar');
            if (!$progress.length) {
                this.$form.prepend(`
                    <div class="isf-form-progress" role="progressbar" aria-valuemin="0" aria-valuemax="${this.totalSteps}" aria-valuenow="${this.currentStep}">
                        <div class="isf-progress-bar">
                            <div class="isf-progress-fill" style="width: ${(this.currentStep / this.totalSteps) * 100}%"></div>
                        </div>
                        <div class="isf-progress-text">Step ${this.currentStep} of ${this.totalSteps}</div>
                    </div>
                `);
            }
        }

        /**
         * Update progress bar
         */
        updateProgress() {
            const percentage = (this.currentStep / this.totalSteps) * 100;
            this.$form.find('.isf-progress-fill').css('width', percentage + '%');
            this.$form.find('.isf-progress-text').text(`Step ${this.currentStep} of ${this.totalSteps}`);
            this.$form.find('.isf-form-progress').attr('aria-valuenow', this.currentStep);
        }

        /**
         * Bind form events
         */
        bindEvents() {
            // Real-time validation on blur
            this.$form.on('blur', 'input, select, textarea', (e) => {
                const $field = $(e.target);
                this.touched[$field.attr('name')] = true;
                this.validateField($field);
            });

            // Clear error on input
            this.$form.on('input', 'input, select, textarea', (e) => {
                const $field = $(e.target);
                if (this.errors[$field.attr('name')]) {
                    this.clearFieldError($field);
                }
            });

            // Step navigation
            this.$form.on('click', '.isf-btn-next', (e) => {
                e.preventDefault();
                this.handleNext();
            });

            this.$form.on('click', '.isf-btn-prev', (e) => {
                e.preventDefault();
                this.handlePrev();
            });

            // Form submission
            this.$form.on('submit', (e) => {
                e.preventDefault();
                this.handleSubmit(e);
            });
        }

        /**
         * Validate individual field
         */
        validateField($field) {
            const fieldName = $field.attr('name');
            const value = $field.val();
            const rules = this.getValidationRules($field);
            let error = null;

            // Required validation
            if (rules.required && !value) {
                error = rules.messages.required || 'This field is required';
            }

            // Email validation
            if (rules.email && value && !this.isValidEmail(value)) {
                error = rules.messages.email || 'Please enter a valid email address';
            }

            // Phone validation
            if (rules.phone && value && !this.isValidPhone(value)) {
                error = rules.messages.phone || 'Please enter a valid phone number';
            }

            // Min length
            if (rules.minLength && value && value.length < rules.minLength) {
                error = rules.messages.minLength || `Minimum ${rules.minLength} characters required`;
            }

            // Max length
            if (rules.maxLength && value && value.length > rules.maxLength) {
                error = rules.messages.maxLength || `Maximum ${rules.maxLength} characters allowed`;
            }

            // Pattern matching
            if (rules.pattern && value && !new RegExp(rules.pattern).test(value)) {
                error = rules.messages.pattern || 'Invalid format';
            }

            // Min value (for number inputs)
            if (rules.min !== undefined && value && parseFloat(value) < rules.min) {
                error = rules.messages.min || `Minimum value is ${rules.min}`;
            }

            // Max value (for number inputs)
            if (rules.max !== undefined && value && parseFloat(value) > rules.max) {
                error = rules.messages.max || `Maximum value is ${rules.max}`;
            }

            if (error) {
                this.setFieldError($field, error);
                this.errors[fieldName] = error;
            } else {
                this.clearFieldError($field);
                delete this.errors[fieldName];
            }

            return !error;
        }

        /**
         * Get validation rules for a field
         */
        getValidationRules($field) {
            const rules = {
                required: $field.attr('required') !== undefined,
                email: $field.attr('type') === 'email',
                phone: $field.hasClass('isf-field-phone'),
                pattern: $field.attr('pattern'),
                minLength: parseInt($field.attr('minlength')),
                maxLength: parseInt($field.attr('maxlength')),
                min: parseFloat($field.attr('min')),
                max: parseFloat($field.attr('max')),
                messages: {}
            };

            // Get custom error messages from data attributes
            const customMessages = $field.data('error-messages');
            if (customMessages) {
                rules.messages = customMessages;
            }

            return rules;
        }

        /**
         * Set field error
         */
        setFieldError($field, message) {
            const $wrapper = $field.closest('.isf-field-wrapper');
            const errorId = $field.attr('id') + '_error';

            $wrapper.addClass('isf-field-has-error');
            $field.attr('aria-invalid', 'true');

            const $error = $wrapper.find('.isf-field-error');
            $error.text(message).attr('id', errorId);

            // Announce error to screen readers
            this.announceToScreenReader(message);
        }

        /**
         * Clear field error
         */
        clearFieldError($field) {
            const $wrapper = $field.closest('.isf-field-wrapper');

            $wrapper.removeClass('isf-field-has-error');
            $field.attr('aria-invalid', 'false');

            const $error = $wrapper.find('.isf-field-error');
            $error.text('');
        }

        /**
         * Validate current step
         */
        validateStep() {
            const $currentStep = this.$form.find('.isf-step').eq(this.currentStep - 1);
            const $fields = $currentStep.find('input:visible, select:visible, textarea:visible').filter('[required]');
            let isValid = true;

            $fields.each((i, field) => {
                if (!this.validateField($(field))) {
                    isValid = false;
                }
            });

            return isValid;
        }

        /**
         * Handle next button
         */
        handleNext() {
            if (!this.validateStep()) {
                this.focusFirstError();
                return;
            }

            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
                this.showStep(this.currentStep);
                this.updateProgress();
                this.announceStepChange();
            }
        }

        /**
         * Handle previous button
         */
        handlePrev() {
            if (this.currentStep > 1) {
                this.currentStep--;
                this.showStep(this.currentStep);
                this.updateProgress();
                this.announceStepChange();
            }
        }

        /**
         * Show specific step
         */
        showStep(stepNumber) {
            const $steps = this.$form.find('.isf-step');
            $steps.removeClass('active').hide();
            $steps.eq(stepNumber - 1).addClass('active').fadeIn(300);

            // Update navigation buttons
            this.$form.find('.isf-btn-prev').toggle(stepNumber > 1);
            this.$form.find('.isf-btn-next').toggle(stepNumber < this.totalSteps);
            this.$form.find('.isf-btn-submit').toggle(stepNumber === this.totalSteps);

            // Focus first field in step
            setTimeout(() => {
                $steps.eq(stepNumber - 1).find('input, select, textarea').first().focus();
            }, 350);
        }

        /**
         * Handle form submission
         */
        handleSubmit(e) {
            // Validate all fields
            const $allFields = this.$form.find('input:visible, select:visible, textarea:visible').filter('[required]');
            let isValid = true;

            $allFields.each((i, field) => {
                if (!this.validateField($(field))) {
                    isValid = false;
                }
            });

            if (!isValid) {
                this.focusFirstError();
                this.showFormError('Please correct the errors above');
                return false;
            }

            // Show loading state
            this.showLoadingState();

            // Submit form via AJAX
            this.submitForm();

            return false;
        }

        /**
         * Submit form via AJAX
         */
        submitForm() {
            const formData = new FormData(this.$form[0]);

            $.ajax({
                url: this.$form.attr('action') || window.location.href,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    this.onSubmitSuccess(response);
                },
                error: (xhr, status, error) => {
                    this.onSubmitError(error);
                }
            });
        }

        /**
         * Handle submit success
         */
        onSubmitSuccess(response) {
            this.hideLoadingState();
            this.$form.find('.isf-form-content').html(`
                <div class="isf-success-message" role="status" aria-live="polite">
                    <div class="isf-success-icon">
                        <span class="dashicons dashicons-yes-alt"></span>
                    </div>
                    <h3>Thank You!</h3>
                    <p>${response.message || 'Your form has been submitted successfully.'}</p>
                </div>
            `);

            // Announce to screen readers
            this.announceToScreenReader('Form submitted successfully');

            // Trigger custom event
            this.$form.trigger('isf:submit:success', [response]);
        }

        /**
         * Handle submit error
         */
        onSubmitError(error) {
            this.hideLoadingState();
            this.showFormError('There was an error submitting the form. Please try again.');
            this.announceToScreenReader('Form submission failed. Please try again.');

            // Trigger custom event
            this.$form.trigger('isf:submit:error', [error]);
        }

        /**
         * Show loading state
         */
        showLoadingState() {
            this.$form.find('.isf-btn-submit').prop('disabled', true).addClass('isf-loading');
            this.$form.find('.isf-btn-submit').html(`
                <span class="isf-spinner"></span>
                <span>Submitting...</span>
            `);
        }

        /**
         * Hide loading state
         */
        hideLoadingState() {
            this.$form.find('.isf-btn-submit').prop('disabled', false).removeClass('isf-loading');
            this.$form.find('.isf-btn-submit').text('Submit');
        }

        /**
         * Show form-level error
         */
        showFormError(message) {
            let $errorContainer = this.$form.find('.isf-form-errors');
            if (!$errorContainer.length) {
                $errorContainer = $('<div class="isf-form-errors" role="alert" aria-live="assertive"></div>');
                this.$form.prepend($errorContainer);
            }
            $errorContainer.html(`
                <div class="isf-error-message">
                    <span class="dashicons dashicons-warning"></span>
                    <span>${message}</span>
                </div>
            `).fadeIn();
        }

        /**
         * Focus first error field
         */
        focusFirstError() {
            const $firstError = this.$form.find('.isf-field-has-error').first();
            if ($firstError.length) {
                const $field = $firstError.find('input, select, textarea').first();
                $field.focus();

                // Scroll to error with offset for fixed headers
                $('html, body').animate({
                    scrollTop: $firstError.offset().top - 100
                }, 500);
            }
        }

        /**
         * Announce step change to screen readers
         */
        announceStepChange() {
            this.announceToScreenReader(`Now on step ${this.currentStep} of ${this.totalSteps}`);
        }

        /**
         * Announce message to screen readers
         */
        announceToScreenReader(message) {
            let $announcer = $('#isf-sr-announcer');
            if (!$announcer.length) {
                $announcer = $('<div id="isf-sr-announcer" class="sr-only" role="status" aria-live="polite" aria-atomic="true"></div>');
                $('body').append($announcer);
            }
            $announcer.text(message);
        }

        /**
         * Email validation
         */
        isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        /**
         * Phone validation
         */
        isValidPhone(phone) {
            // Basic phone validation - can be enhanced
            return /^[\d\s\-\(\)\+\.]+$/.test(phone) && phone.replace(/\D/g, '').length >= 10;
        }
    }

    // Initialize enhanced validation on all FormFlow forms
    $(document).ready(function() {
        $('.isf-form').each(function() {
            new EnhancedFormValidator(this);
        });
    });

    // Expose class for external use
    window.ISFEnhancedFormValidator = EnhancedFormValidator;

})(jQuery);
