# FormFlow Pro - Accessibility Standards & Testing

## Overview

FormFlow Pro is committed to creating accessible enrollment and scheduling forms that comply with **WCAG 2.1 Level AA** standards. This document outlines our accessibility commitments, standards followed, and testing procedures.

## Accessibility Commitment

FormFlow Pro follows the Web Content Accessibility Guidelines (WCAG) 2.1 Level AA to ensure our forms are usable by everyone, including people with disabilities. We are committed to:

- Making forms perceivable, operable, understandable, and robust
- Supporting keyboard navigation for all interactive elements
- Providing proper semantic HTML and ARIA labels
- Ensuring proper color contrast ratios (4.5:1 for text)
- Testing with actual assistive technologies

## WordPress Admin Accessibility

FormFlow Pro follows **WordPress Admin Accessibility Standards**:

- All admin pages are tested with screen readers (NVDA, JAWS)
- Forms include proper fieldsets and legends
- Error messages are announced to screen reader users
- Keyboard users can access all functionality via Tab navigation
- Focus indicators are clearly visible

## Form Accessibility Features

FormFlow Pro forms are built with accessibility as a core feature:

### Labels & Form Structure

All form inputs include properly associated labels:

```php
<div class="isf-field isf-field-required">
    <label for="utility_no" class="isf-label">
        <?php esc_html_e('Utility Account Number', 'formflow'); ?>
        <span class="isf-required">*</span>
    </label>
    <input type="text" id="utility_no" name="utility_no" required>
</div>
```

- Every `<input>` has a corresponding `<label>` with matching `for` attribute
- Labels include visual indicator of required fields
- Form sections use `<fieldset>` and `<legend>` for grouping

### ARIA Support

Forms include ARIA attributes for enhanced accessibility:

```php
<!-- Error messages announced to screen readers -->
<div class="isf-alert isf-alert-error" role="alert">
    <span class="isf-alert-message">
        <?php echo esc_html($error_message); ?>
    </span>
</div>

<!-- Loading states announced -->
<div role="status" aria-live="polite" aria-busy="true">
    <?php esc_html_e('Validating account information...', 'formflow'); ?>
</div>
```

- `role="alert"` for error messages (announced immediately)
- `aria-live="polite"` for status updates
- `aria-busy="true"` for loading states
- `aria-label` for icon-only buttons
- `aria-labelledby` for complex components

### Keyboard Navigation

All FormFlow forms are fully keyboard accessible:

- Tab navigation between form fields
- Enter key to submit forms
- Arrow keys for multi-select components
- Escape key to close modals
- Focus indicators clearly visible on all interactive elements

### Semantic HTML

Forms use proper semantic markup:

```php
<!-- Proper heading hierarchy -->
<h2 class="isf-step-title">Step Title</h2>

<!-- Proper list markup for radio/checkbox groups -->
<fieldset class="isf-fieldset">
    <legend>Device Options</legend>
    <ul class="isf-options">
        <li>
            <label>
                <input type="radio" name="device_type" value="thermostat">
                Thermostat
            </label>
        </li>
    </ul>
</fieldset>
```

### Color Contrast

FormFlow Pro meets WCAG AA color contrast requirements:

- Text to background: minimum 4.5:1 ratio
- Large text (18pt+ or 14pt+ bold): minimum 3:1 ratio
- UI components: minimum 3:1 ratio
- Not relying on color alone to convey information

### Multi-Step Form Accessibility

FormFlow's multi-step enrollment forms include:

- Current step clearly labeled (e.g., "Step 1 of 5")
- Progress indicators with proper semantics
- Back/Next buttons clearly labeled
- Form data preserved across steps
- Estimated time to complete shown upfront

### Error Messages

Error handling is accessible:

```php
<!-- Error announced to screen readers -->
<div class="isf-alert isf-alert-error" role="alert">
    <?php echo wp_kses_post($error); ?>
</div>

<!-- Associated with form field -->
<input type="text" id="utility_no" aria-describedby="utility_no_error">
<div id="utility_no_error" class="isf-error">
    <?php esc_html_e('Account number invalid', 'formflow'); ?>
</div>
```

## Testing Process

### Automated Testing

Run the automated accessibility scanner:

```bash
# Run local accessibility check
bash scripts/a11y-check.sh

# Or run the Node.js scanner directly
node scripts/accessibility-scan.cjs
```

The scanner checks for:
- Images without alt attributes
- Form inputs without associated labels
- Missing ARIA attributes on interactive elements
- Tables without headers
- Links without descriptive text
- Missing lang attributes
- Buttons without accessible labels

### Manual Testing

Manual testing is required for complete accessibility verification:

1. **Screen Reader Testing**
   - NVDA (Windows) - Free, open source
   - JAWS (Windows) - Industry standard
   - VoiceOver (macOS/iOS) - Built-in
   - TalkBack (Android) - Built-in

2. **Keyboard Navigation**
   - Use Tab to navigate all interactive elements
   - Verify focus indicators are visible
   - Test logical tab order through form steps
   - Ensure no keyboard traps

3. **Browser Testing**
   - Chrome with axe DevTools extension
   - Firefox with WAVE extension
   - Safari with Accessibility Inspector

### CI/CD Integration

The GitHub Actions workflow (`/.github/workflows/accessibility.yml`) automatically:

- Scans PHP templates on every PR
- Runs accessibility unit tests
- Comments on PRs with scan results
- Blocks merge if critical issues found

Run locally before committing:

```bash
# Check accessibility
bash scripts/a11y-check.sh

# Run tests
composer test
```

## Known Limitations

### JavaScript-Heavy Features

Some FormFlow features use JavaScript for enhanced UX. These are tested to ensure:

- Progressive enhancement (forms work without JavaScript)
- Proper ARIA attributes for dynamic content
- Keyboard accessibility maintained

### Third-Party Integrations

FormFlow integrates with external APIs (CRM, scheduling, etc.). Accessibility:

- Does not extend to external systems' interfaces
- Is maintained in the FormFlow-provided form UI
- Should be tested end-to-end by integrating customer

### Mobile Accessibility

FormFlow forms are responsive and mobile-accessible:

- Touch targets at least 44x44px
- Zoom/magnification supported
- Mobile screen readers supported
- No horizontal scrolling required

## Reporting Accessibility Issues

Found an accessibility issue? Please report it:

1. **Open a GitHub Issue** with:
   - Description of the issue
   - Steps to reproduce
   - Affected form/feature
   - Assistive technology used

2. **For Security Issues**, see SECURITY.md

3. **Customer Support**: Contact support@peanutgraphic.com with:
   - Screenshot or video
   - Form URL
   - Device/browser info
   - How it affects your users

## Accessibility Standards Reference

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/) - Official web accessibility standard
- [WordPress Accessibility Handbook](https://developer.wordpress.org/plugins/security/sanitizing-input/) - WP-specific guidance
- [ARIA Authoring Practices](https://www.w3.org/WAI/ARIA/apg/) - ARIA implementation guide
- [WebAIM](https://webaim.org/) - Web accessibility information
- [WAI-ARIA](https://www.w3.org/TR/wai-aria-1.2/) - ARIA specification

## Development Guidelines

When adding new features to FormFlow:

### Required Checklist

- [ ] All form inputs have associated labels
- [ ] All images have alt text (or `alt=""` if decorative)
- [ ] Interactive elements are keyboard accessible
- [ ] Color is not the only method of conveyance
- [ ] Text has sufficient contrast (4.5:1 minimum)
- [ ] Proper semantic HTML used (`<button>`, `<label>`, `<fieldset>`)
- [ ] Error messages announced to screen readers
- [ ] Focus indicators clearly visible
- [ ] Run `bash scripts/a11y-check.sh` before committing
- [ ] Manual testing with screen reader
- [ ] Pass CI accessibility checks

### Code Examples

#### Accessible Form Field

```php
<div class="isf-field isf-field-required">
    <label for="email" class="isf-label">
        <?php esc_html_e('Email Address', 'formflow'); ?>
        <span class="isf-required" aria-label="<?php esc_attr_e('required', 'formflow'); ?>">*</span>
    </label>
    <input
        type="email"
        id="email"
        name="email"
        required
        aria-required="true"
        aria-describedby="email_help"
    >
    <div id="email_help" class="isf-help-text">
        <?php esc_html_e('We will send a confirmation link to this address.', 'formflow'); ?>
    </div>
</div>
```

#### Accessible Button

```php
<button type="submit" class="isf-btn isf-btn-primary">
    <?php esc_html_e('Continue to Next Step', 'formflow'); ?>
</button>

<!-- Icon-only button needs aria-label -->
<button type="button" class="isf-btn-close" aria-label="<?php esc_attr_e('Close modal', 'formflow'); ?>">
    <svg aria-hidden="true"><!-- SVG icon --></svg>
</button>
```

#### Accessible Alert

```php
<div class="isf-alert isf-alert-error" role="alert">
    <span class="isf-alert-icon" aria-hidden="true">
        <svg><!-- Error icon --></svg>
    </span>
    <span class="isf-alert-message">
        <?php echo wp_kses_post($error_message); ?>
    </span>
</div>
```

## Version History

| Version | Changes |
|---------|---------|
| 2.8.4+ | Accessibility CI pipeline, automated scanning, WCAG 2.1 AA compliance |
| 2.8.0+ | ARIA support, keyboard navigation, form accessibility improvements |
| 2.0.0+ | WCAG 2.1 Level A baseline compliance |

## Contact & Support

For accessibility questions or feedback:

- **Email**: accessibility@peanutgraphic.com
- **GitHub Issues**: [Report Issue](https://github.com/peanutgraphic/formflow/issues)
- **Documentation**: See `/docs` directory
