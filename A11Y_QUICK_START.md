# Accessibility Pipeline - Quick Start Guide

## For Developers

### Before You Commit

Always run the accessibility check locally:

```bash
bash scripts/a11y-check.sh
```

This will:
- Scan all PHP templates for WCAG violations
- Show errors (red), warnings (yellow), info (blue)
- Exit with code 0 if no critical issues, 1 if errors found

### What Gets Checked

1. **Images** - Must have alt text (unless decorative with `alt=""`)
2. **Form Fields** - Input elements must have associated `<label>`
3. **ARIA** - Interactive elements have proper ARIA attributes
4. **Tables** - Data tables must have `<thead>` or `<th>` headers
5. **Links** - Must have descriptive text (not "click here")
6. **Buttons** - Must have text content or `aria-label`
7. **SVG** - Must have title, aria-label, or `aria-hidden="true"`

### Common Fixes

#### Image Missing Alt Text
```php
// Before (error)
<img src="device.png">

// After (fixed)
<img src="device.png" alt="Smart Thermostat Device">

// Or if decorative
<img src="spacer.png" alt="" aria-hidden="true">
```

#### Form Input Without Label
```php
// Before (warning)
<input type="email" id="email" name="email">

// After (fixed)
<label for="email">Email Address</label>
<input type="email" id="email" name="email">
```

#### Button Without Accessible Text
```php
// Before (error)
<button class="close-btn">
  <svg><!-- icon --></svg>
</button>

// After (fixed)
<button class="close-btn" aria-label="Close modal">
  <svg aria-hidden="true"><!-- icon --></svg>
</button>
```

#### Table Without Headers
```php
// Before (warning)
<table>
  <tbody>
    <tr><td>Data 1</td><td>Data 2</td></tr>
  </tbody>
</table>

// After (fixed)
<table>
  <thead>
    <tr>
      <th scope="col">Column 1</th>
      <th scope="col">Column 2</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>Data 1</td><td>Data 2</td></tr>
  </tbody>
</table>
```

### Run Tests Locally

```bash
# Quick scan
bash scripts/a11y-check.sh

# Run accessibility tests
composer test -- --testsuite=accessibility

# Run all tests (PHP + accessibility)
npm test
```

### For CI/CD

The GitHub Actions workflow runs automatically on:
- Every PR to main/develop
- Every push to main

**Important:** Accessibility errors will block merge. Fix them before pushing.

## For Product Managers

### Why This Matters

- Legal: WCAG 2.1 AA is now a standard compliance requirement
- Users: ~15% of users have some form of disability
- Business: Inaccessible sites are a liability
- Quality: Accessibility correlates with code quality

### Standards

**WCAG 2.1 Level AA** means:
- Works with keyboard only
- Works with screen readers
- Good color contrast (4.5:1)
- Proper semantic HTML
- Clear error messages
- Accessible forms with labels
- Proper heading hierarchy

## For QA/Testing

### Manual Testing Checklist

Before deploying any form changes:

1. **Keyboard Navigation**
   - [ ] Can navigate form using only Tab key
   - [ ] Tab order is logical
   - [ ] Can't get stuck in form ("keyboard trap")

2. **Screen Reader (use NVDA or VoiceOver)**
   - [ ] All labels are announced
   - [ ] Required fields are announced as required
   - [ ] Error messages are announced
   - [ ] Form instructions are clear

3. **Visual**
   - [ ] Text is readable (no color alone conveys info)
   - [ ] Focus indicators are visible
   - [ ] All images have alt text

4. **Mobile/Zoom**
   - [ ] Touch targets are 44x44px minimum
   - [ ] Page works at 200% zoom
   - [ ] No horizontal scroll needed

### Browser Extensions for Testing

- **Chrome:** axe DevTools
- **Firefox:** WAVE
- **Safari:** Accessibility Inspector (built-in)
- **All:** NVDA (Windows) or VoiceOver (Mac)

## Support & Questions

- **Quick Question?** Check `docs/ACCESSIBILITY.md`
- **Found an Issue?** Report in GitHub Issues or accessibility@peanutgraphic.com
- **Need Help Fixing?** See fix examples above or check docs/

## Key Files

| File | Purpose |
|------|---------|
| `docs/ACCESSIBILITY.md` | Complete accessibility guide |
| `scripts/a11y-check.sh` | Quick scan before committing |
| `scripts/accessibility-scan.cjs` | The actual scanner script |
| `tests/accessibility/` | Automated test suite |
| `.github/workflows/accessibility.yml` | CI/CD automation |
| `A11Y_QUICK_START.md` | This file |

## Commands Reference

```bash
# Quick check (before commit)
bash scripts/a11y-check.sh

# Run scanner directly
node scripts/accessibility-scan.cjs

# Run accessibility unit tests
composer test -- --testsuite=accessibility

# Run all tests
npm test

# Check git status
git status

# View accessibility issues found
node scripts/accessibility-scan.cjs 2>&1 | grep -E "error|Error" | head -20
```

## Remember

Accessibility isn't an afterthought—it's built in from the start. Every form, every button, every image counts.

Thanks for helping make FormFlow Pro accessible to everyone!
