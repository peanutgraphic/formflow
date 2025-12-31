/**
 * Visual Conditional Logic Builder
 *
 * Provides a flowchart-style UI for building conditional logic rules.
 *
 * @package FormFlow
 * @since 2.7.0
 */

(function($) {
    'use strict';

    const ISFConditionalLogicBuilder = {
        /**
         * Initialize the builder
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Open conditional logic builder modal
            $(document).on('click', '.isf-open-conditional-logic', function(e) {
                e.preventDefault();
                const fieldId = $(this).data('field-id');
                ISFConditionalLogicBuilder.openBuilder(fieldId);
            });

            // Add new rule
            $(document).on('click', '.isf-add-rule', function(e) {
                e.preventDefault();
                ISFConditionalLogicBuilder.addRule();
            });

            // Add new condition to rule
            $(document).on('click', '.isf-add-condition', function(e) {
                e.preventDefault();
                const ruleIndex = $(this).data('rule-index');
                ISFConditionalLogicBuilder.addCondition(ruleIndex);
            });

            // Remove rule
            $(document).on('click', '.isf-remove-rule', function(e) {
                e.preventDefault();
                const ruleIndex = $(this).data('rule-index');
                ISFConditionalLogicBuilder.removeRule(ruleIndex);
            });

            // Remove condition
            $(document).on('click', '.isf-remove-condition', function(e) {
                e.preventDefault();
                const ruleIndex = $(this).data('rule-index');
                const conditionIndex = $(this).data('condition-index');
                ISFConditionalLogicBuilder.removeCondition(ruleIndex, conditionIndex);
            });

            // Save conditional logic
            $(document).on('click', '.isf-save-conditional-logic', function(e) {
                e.preventDefault();
                ISFConditionalLogicBuilder.saveLogic();
            });

            // Close modal
            $(document).on('click', '.isf-close-logic-modal', function(e) {
                e.preventDefault();
                ISFConditionalLogicBuilder.closeBuilder();
            });
        },

        /**
         * Open the builder modal
         */
        openBuilder: function(fieldId) {
            const modal = `
                <div class="isf-logic-modal-overlay">
                    <div class="isf-logic-modal">
                        <div class="isf-logic-modal-header">
                            <h2>Conditional Logic Builder</h2>
                            <button class="isf-close-logic-modal" aria-label="Close">&times;</button>
                        </div>
                        <div class="isf-logic-modal-body">
                            <div class="isf-logic-info">
                                <p>Create rules to show/hide, enable/disable, or make fields required based on other field values.</p>
                            </div>
                            <div class="isf-logic-canvas" id="isf-logic-canvas" data-field-id="${fieldId}">
                                <div class="isf-logic-rules">
                                    <!-- Rules will be added here -->
                                </div>
                                <button class="button isf-add-rule">+ Add Rule</button>
                            </div>
                        </div>
                        <div class="isf-logic-modal-footer">
                            <button class="button button-secondary isf-close-logic-modal">Cancel</button>
                            <button class="button button-primary isf-save-conditional-logic">Save Logic</button>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modal);
            this.loadExistingRules(fieldId);
        },

        /**
         * Load existing rules for a field
         */
        loadExistingRules: function(fieldId) {
            // This would normally load from the form schema
            // For now, we'll just show an empty state
            console.log('Loading rules for field:', fieldId);
        },

        /**
         * Add a new rule
         */
        addRule: function() {
            const $rulesContainer = $('.isf-logic-rules');
            const ruleIndex = $rulesContainer.find('.isf-logic-rule').length;

            const ruleHtml = this.getRuleTemplate(ruleIndex);
            $rulesContainer.append(ruleHtml);
        },

        /**
         * Get rule template HTML
         */
        getRuleTemplate: function(index) {
            return `
                <div class="isf-logic-rule" data-rule-index="${index}">
                    <div class="isf-logic-rule-header">
                        <h4>Rule ${index + 1}</h4>
                        <button class="button-link isf-remove-rule" data-rule-index="${index}">
                            <span class="dashicons dashicons-no"></span>
                        </button>
                    </div>
                    <div class="isf-logic-rule-body">
                        <div class="isf-logic-conditions">
                            <div class="isf-logic-label">When</div>
                            <div class="isf-logic-conditions-list">
                                ${this.getConditionTemplate(index, 0)}
                            </div>
                            <div class="isf-logic-condition-logic">
                                <select class="isf-condition-logic" data-rule-index="${index}">
                                    <option value="and">All conditions match (AND)</option>
                                    <option value="or">Any condition matches (OR)</option>
                                </select>
                            </div>
                            <button class="button button-small isf-add-condition" data-rule-index="${index}">
                                + Add Condition
                            </button>
                        </div>
                        <div class="isf-logic-action">
                            <div class="isf-logic-label">Then</div>
                            <select class="isf-action-type" data-rule-index="${index}">
                                <option value="show">Show this field</option>
                                <option value="hide">Hide this field</option>
                                <option value="enable">Enable this field</option>
                                <option value="disable">Disable this field</option>
                                <option value="require">Make this field required</option>
                                <option value="unrequire">Make this field optional</option>
                                <option value="set_value">Set field value</option>
                                <option value="clear_value">Clear field value</option>
                            </select>
                            <input type="text" class="isf-action-value" placeholder="Value (if applicable)" style="display:none;">
                        </div>
                    </div>
                </div>
            `;
        },

        /**
         * Get condition template HTML
         */
        getConditionTemplate: function(ruleIndex, conditionIndex) {
            return `
                <div class="isf-logic-condition" data-rule-index="${ruleIndex}" data-condition-index="${conditionIndex}">
                    <div class="isf-condition-node">
                        <select class="isf-condition-field">
                            <option value="">Select field...</option>
                            <!-- Fields would be populated dynamically -->
                        </select>
                        <select class="isf-condition-operator">
                            <option value="equals">Equals</option>
                            <option value="not_equals">Does not equal</option>
                            <option value="contains">Contains</option>
                            <option value="not_contains">Does not contain</option>
                            <option value="starts_with">Starts with</option>
                            <option value="ends_with">Ends with</option>
                            <option value="greater_than">Greater than</option>
                            <option value="less_than">Less than</option>
                            <option value="is_empty">Is empty</option>
                            <option value="is_not_empty">Is not empty</option>
                        </select>
                        <input type="text" class="isf-condition-value" placeholder="Value">
                        ${conditionIndex > 0 ? `
                        <button class="button-link isf-remove-condition" data-rule-index="${ruleIndex}" data-condition-index="${conditionIndex}">
                            <span class="dashicons dashicons-no"></span>
                        </button>
                        ` : ''}
                    </div>
                    ${conditionIndex < 5 ? '<div class="isf-condition-connector"></div>' : ''}
                </div>
            `;
        },

        /**
         * Add condition to a rule
         */
        addCondition: function(ruleIndex) {
            const $conditionsList = $(`.isf-logic-rule[data-rule-index="${ruleIndex}"] .isf-logic-conditions-list`);
            const conditionIndex = $conditionsList.find('.isf-logic-condition').length;

            if (conditionIndex >= 5) {
                alert('Maximum of 5 conditions per rule.');
                return;
            }

            const conditionHtml = this.getConditionTemplate(ruleIndex, conditionIndex);
            $conditionsList.append(conditionHtml);
        },

        /**
         * Remove a rule
         */
        removeRule: function(ruleIndex) {
            if (!confirm('Are you sure you want to remove this rule?')) {
                return;
            }

            $(`.isf-logic-rule[data-rule-index="${ruleIndex}"]`).remove();
            this.reindexRules();
        },

        /**
         * Remove a condition
         */
        removeCondition: function(ruleIndex, conditionIndex) {
            $(`.isf-logic-condition[data-rule-index="${ruleIndex}"][data-condition-index="${conditionIndex}"]`).remove();
            this.reindexConditions(ruleIndex);
        },

        /**
         * Reindex rules after removal
         */
        reindexRules: function() {
            $('.isf-logic-rule').each(function(index) {
                $(this).attr('data-rule-index', index);
                $(this).find('.isf-logic-rule-header h4').text('Rule ' + (index + 1));
                $(this).find('[data-rule-index]').attr('data-rule-index', index);
            });
        },

        /**
         * Reindex conditions after removal
         */
        reindexConditions: function(ruleIndex) {
            $(`.isf-logic-rule[data-rule-index="${ruleIndex}"] .isf-logic-condition`).each(function(index) {
                $(this).attr('data-condition-index', index);
                $(this).find('[data-condition-index]').attr('data-condition-index', index);
            });
        },

        /**
         * Save conditional logic
         */
        saveLogic: function() {
            const fieldId = $('#isf-logic-canvas').data('field-id');
            const rules = [];

            $('.isf-logic-rule').each(function() {
                const $rule = $(this);
                const ruleIndex = $rule.data('rule-index');

                const conditions = [];
                $rule.find('.isf-logic-condition').each(function() {
                    const $condition = $(this);
                    conditions.push({
                        field: $condition.find('.isf-condition-field').val(),
                        operator: $condition.find('.isf-condition-operator').val(),
                        value: $condition.find('.isf-condition-value').val()
                    });
                });

                const logic = $rule.find('.isf-condition-logic').val();
                const action = $rule.find('.isf-action-type').val();

                rules.push({
                    conditions: conditions,
                    logic: logic,
                    action: action
                });
            });

            console.log('Saving logic for field:', fieldId, rules);

            // This would normally save to the form schema via AJAX
            alert('Conditional logic saved! (This is a demo implementation)');
            this.closeBuilder();
        },

        /**
         * Close the builder modal
         */
        closeBuilder: function() {
            $('.isf-logic-modal-overlay').remove();
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        ISFConditionalLogicBuilder.init();
    });

    // Expose for external use
    window.ISFConditionalLogicBuilder = ISFConditionalLogicBuilder;

})(jQuery);
