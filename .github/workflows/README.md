# GitHub Actions Workflows

This directory contains automated CI/CD workflows for FormFlow Pro.

## Accessibility Workflow

**File:** `accessibility.yml`

Automated accessibility testing that runs on every pull request and push to main branch.

### What it does

1. Scans PHP templates for common accessibility anti-patterns
2. Runs accessibility unit tests via PHPUnit
3. Checks WCAG 2.1 Level AA compliance
4. Comments on PRs with scan results
5. Blocks merge if critical issues found

### How to run locally

Before pushing changes, run the accessibility check:

```bash
# Run the automated scanner
bash scripts/a11y-check.sh

# Or run Node.js script directly
node scripts/accessibility-scan.cjs

# Run accessibility tests
composer test -- --testsuite=accessibility
```

### Triggering the workflow

- **Automatic:** On every PR and push to main branch
- **Manual:** Click "Run workflow" on GitHub Actions page

### What gets checked

- Images without alt attributes
- Form inputs without labels
- Missing ARIA attributes
- Tables without headers
- Non-descriptive link text
- Buttons without text/labels
- SVG accessibility
- HTML lang attributes
- Color-only information conveyance

### Fixing issues

See `/docs/ACCESSIBILITY.md` for detailed guidelines on fixing accessibility issues.

### CI Status

The accessibility workflow is **required to pass** before merging to main.

If the workflow fails:

1. Run `bash scripts/a11y-check.sh` locally
2. Review the issues reported
3. Fix issues following `/docs/ACCESSIBILITY.md` guidelines
4. Commit and push fixes
5. Workflow will re-run automatically
