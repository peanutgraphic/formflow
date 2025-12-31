/**
 * Advanced Form Fields - JavaScript Functionality
 *
 * Handles interactivity for advanced field types:
 * - Likert Scale
 * - Slider
 * - reCAPTCHA v3
 * - Repeater
 * - Star Rating
 * - Date Range
 * - Address Autocomplete
 * - Number Stepper
 * - Color Picker
 *
 * @package FormFlow
 * @since 2.7.0
 */

(function($) {
    'use strict';

    const ISFAdvancedFields = {
        /**
         * Initialize all advanced fields
         */
        init: function() {
            this.initSliders();
            this.initStarRatings();
            this.initNumberSteppers();
            this.initColorPickers();
            this.initRepeaters();
            this.initDateRangePickers();
            this.initAddressAutocomplete();
            this.initRecaptcha();
            this.initLikertScales();
        },

        /**
         * Initialize slider fields
         */
        initSliders: function() {
            $('.isf-slider').each(function() {
                const $slider = $(this);
                const $output = $slider.siblings('.isf-slider-value');
                const prefix = $slider.data('prefix') || '';
                const suffix = $slider.data('suffix') || '';

                $slider.on('input change', function() {
                    const value = $(this).val();
                    if ($output.length) {
                        $output.text(prefix + value + suffix);
                    }
                });
            });
        },

        /**
         * Initialize star rating fields
         */
        initStarRatings: function() {
            $('.isf-star-rating').each(function() {
                const $rating = $(this);
                const $stars = $rating.find('.isf-star');

                // Highlight stars on hover
                $stars.on('mouseenter', function() {
                    const value = $(this).data('value');
                    $stars.each(function() {
                        if ($(this).data('value') <= value) {
                            $(this).addClass('isf-star-hover');
                        } else {
                            $(this).removeClass('isf-star-hover');
                        }
                    });
                });

                $rating.on('mouseleave', function() {
                    $stars.removeClass('isf-star-hover');
                });

                // Update visual state when selected
                $rating.find('.isf-star-input').on('change', function() {
                    const value = $(this).val();
                    $stars.each(function() {
                        if ($(this).data('value') <= value) {
                            $(this).addClass('isf-star-selected');
                        } else {
                            $(this).removeClass('isf-star-selected');
                        }
                    });
                });
            });
        },

        /**
         * Initialize number stepper fields
         */
        initNumberSteppers: function() {
            $('.isf-number-stepper').each(function() {
                const $stepper = $(this);
                const $input = $stepper.find('.isf-stepper-input');
                const $decrease = $stepper.find('.isf-stepper-decrease');
                const $increase = $stepper.find('.isf-stepper-increase');

                const min = parseFloat($input.attr('min')) || 0;
                const max = parseFloat($input.attr('max')) || 100;
                const step = parseFloat($input.attr('step')) || 1;

                $decrease.on('click', function() {
                    let value = parseFloat($input.val()) || 0;
                    value = Math.max(min, value - step);
                    $input.val(value).trigger('change');
                });

                $increase.on('click', function() {
                    let value = parseFloat($input.val()) || 0;
                    value = Math.min(max, value + step);
                    $input.val(value).trigger('change');
                });
            });
        },

        /**
         * Initialize color picker fields
         */
        initColorPickers: function() {
            $('.isf-color-picker-wrapper').each(function() {
                const $wrapper = $(this);
                const $colorInput = $wrapper.find('.isf-color-input');
                const $textInput = $wrapper.find('.isf-color-text');
                const $presets = $wrapper.find('.isf-color-preset-btn');

                // Sync color input with text input
                $colorInput.on('input change', function() {
                    $textInput.val($(this).val().toUpperCase());
                });

                // Sync text input with color input
                $textInput.on('input', function() {
                    const value = $(this).val();
                    if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                        $colorInput.val(value);
                    }
                });

                // Handle preset clicks
                $presets.on('click', function() {
                    const color = $(this).data('color');
                    $colorInput.val(color).trigger('change');
                    $textInput.val(color.toUpperCase());
                });
            });
        },

        /**
         * Initialize repeater fields
         */
        initRepeaters: function() {
            $('.isf-repeater').each(function() {
                const $repeater = $(this);
                const $items = $repeater.find('.isf-repeater-items');
                const $addBtn = $repeater.find('.isf-repeater-add');
                const $template = $repeater.find('.isf-repeater-template');
                const minItems = parseInt($repeater.data('min')) || 1;
                const maxItems = parseInt($repeater.data('max')) || 10;

                let itemIndex = $items.find('.isf-repeater-item').length;

                // Add item
                $addBtn.on('click', function() {
                    if (itemIndex >= maxItems) {
                        alert('Maximum number of items reached.');
                        return;
                    }

                    const templateHtml = $template.html().replace(/__INDEX__/g, itemIndex);
                    $items.append(templateHtml);
                    itemIndex++;

                    // Reinitialize fields in new item
                    ISFAdvancedFields.init();
                });

                // Remove item (delegated event)
                $items.on('click', '.isf-repeater-remove', function() {
                    if (itemIndex <= minItems) {
                        alert('Minimum number of items required.');
                        return;
                    }

                    $(this).closest('.isf-repeater-item').remove();
                    itemIndex--;
                });
            });
        },

        /**
         * Initialize date range pickers
         */
        initDateRangePickers: function() {
            $('.isf-date-range-wrapper').each(function() {
                const $wrapper = $(this);
                const $start = $wrapper.find('.isf-date-range-start');
                const $end = $wrapper.find('.isf-date-range-end');
                const $presets = $wrapper.find('.isf-date-preset');

                // Ensure end date is not before start date
                $start.on('change', function() {
                    const startDate = $(this).val();
                    if ($end.val() && startDate > $end.val()) {
                        $end.val(startDate);
                    }
                    $end.attr('min', startDate);
                });

                $end.on('change', function() {
                    const endDate = $(this).val();
                    if ($start.val() && endDate < $start.val()) {
                        $start.val(endDate);
                    }
                    $start.attr('max', endDate);
                });

                // Handle preset clicks
                $presets.on('click', function() {
                    const preset = $(this).data('preset');
                    const today = new Date();
                    let startDate, endDate;

                    switch(preset) {
                        case 'today':
                            startDate = endDate = formatDate(today);
                            break;
                        case 'yesterday':
                            const yesterday = new Date(today);
                            yesterday.setDate(yesterday.getDate() - 1);
                            startDate = endDate = formatDate(yesterday);
                            break;
                        case 'last7':
                            endDate = formatDate(today);
                            const last7 = new Date(today);
                            last7.setDate(last7.getDate() - 7);
                            startDate = formatDate(last7);
                            break;
                        case 'last30':
                            endDate = formatDate(today);
                            const last30 = new Date(today);
                            last30.setDate(last30.getDate() - 30);
                            startDate = formatDate(last30);
                            break;
                        case 'thismonth':
                            startDate = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                            endDate = formatDate(today);
                            break;
                    }

                    $start.val(startDate);
                    $end.val(endDate);
                });
            });

            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }
        },

        /**
         * Initialize address autocomplete (Google Places)
         */
        initAddressAutocomplete: function() {
            $('.isf-address-autocomplete').each(function() {
                const $input = $(this);
                const apiKey = $input.data('api-key');
                const countries = ($input.data('countries') || 'us').split(',');

                if (!apiKey) {
                    console.warn('FormFlow: Google Places API key not configured for address autocomplete');
                    return;
                }

                // Load Google Places API if not already loaded
                if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                    const script = document.createElement('script');
                    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places`;
                    script.async = true;
                    script.onload = function() {
                        initAutocomplete($input[0], countries);
                    };
                    document.head.appendChild(script);
                } else {
                    initAutocomplete($input[0], countries);
                }
            });

            function initAutocomplete(input, countries) {
                const autocomplete = new google.maps.places.Autocomplete(input, {
                    types: ['address'],
                    componentRestrictions: { country: countries }
                });

                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();
                    // Place data is automatically filled in the input
                    $(input).trigger('place_selected', [place]);
                });
            }
        },

        /**
         * Initialize reCAPTCHA v3
         */
        initRecaptcha: function() {
            $('.isf-recaptcha-badge').each(function() {
                const $badge = $(this);
                const siteKey = $badge.data('sitekey');
                const action = $badge.data('action');
                const $tokenInput = $badge.siblings('.isf-recaptcha-token');

                if (!siteKey) {
                    return;
                }

                // Load reCAPTCHA script if not already loaded
                if (typeof grecaptcha === 'undefined') {
                    const script = document.createElement('script');
                    script.src = `https://www.google.com/recaptcha/api.js?render=${siteKey}`;
                    script.async = true;
                    script.onload = function() {
                        executeRecaptcha(siteKey, action, $tokenInput);
                    };
                    document.head.appendChild(script);
                } else {
                    executeRecaptcha(siteKey, action, $tokenInput);
                }
            });

            function executeRecaptcha(siteKey, action, $tokenInput) {
                grecaptcha.ready(function() {
                    grecaptcha.execute(siteKey, { action: action }).then(function(token) {
                        $tokenInput.val(token);
                    });
                });
            }
        },

        /**
         * Initialize Likert scale fields
         */
        initLikertScales: function() {
            $('.isf-likert-scale').each(function() {
                const $scale = $(this);
                const $options = $scale.find('.isf-likert-option');

                // Add hover effect
                $options.on('mouseenter', function() {
                    $(this).find('.isf-likert-box').addClass('isf-likert-hover');
                });

                $options.on('mouseleave', function() {
                    $(this).find('.isf-likert-box').removeClass('isf-likert-hover');
                });

                // Update selected state
                $scale.find('.isf-likert-radio').on('change', function() {
                    $options.find('.isf-likert-box').removeClass('isf-likert-selected');
                    $(this).siblings('.isf-likert-box').addClass('isf-likert-selected');
                });
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        ISFAdvancedFields.init();
    });

    // Expose for external use
    window.ISFAdvancedFields = ISFAdvancedFields;

})(jQuery);
