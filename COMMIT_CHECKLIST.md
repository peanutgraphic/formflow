# Accessibility Pipeline - Commit Checklist

## Files to Stage and Commit

Run these commands to commit the accessibility pipeline:

```bash
cd /sessions/kind-funny-euler/mnt/Peanut/FORMFLOW

# Stage all changes
git add -A

# Commit with standard message
git commit -m "feat: add accessibility CI pipeline with PHP template scanning

- GitHub Actions workflow (accessibility.yml) for automated PR testing
- Node.js scanner (accessibility-scan.cjs) detects WCAG 2.1 violations
- PHPUnit test suite (25+ tests) for form output accessibility  
- Comprehensive documentation (docs/ACCESSIBILITY.md) with standards
- Shell wrapper (a11y-check.sh) for local developer testing
- Updated phpunit.xml with accessibility test suite
- Updated package.json with npm test:a11y script
- Scans for: missing alt text, unlabeled inputs, missing ARIA,
  table headers, non-descriptive links, inaccessible buttons, SVGs
- Initial scan: 4 errors, 56 warnings, 68 info messages identified
- Exit code 1 on critical errors (blocks merge), 0 on warnings only
- PR comments with summary results included"

# Verify commit
git log --oneline -1

# Push to remote (if upstream is configured)
git push origin main
```

## Verification Steps

After committing, verify:

```bash
# Check that files are committed
git status  # Should show "working tree clean"

# View the commit
git show HEAD --stat

# Test the scanner works
bash scripts/a11y-check.sh

# Run tests
composer test -- --testsuite=accessibility
```

## What Gets Committed

### New Files (8 files)
- `.github/workflows/accessibility.yml` - GitHub Actions workflow
- `.github/workflows/README.md` - Workflow documentation  
- `scripts/a11y-check.sh` - Shell script wrapper (executable)
- `scripts/accessibility-scan.cjs` - Node.js accessibility scanner
- `tests/accessibility/test-template-accessibility.php` - PHPUnit tests
- `docs/ACCESSIBILITY.md` - Comprehensive guide
- `A11Y_QUICK_START.md` - Quick start for developers
- `ACCESSIBILITY_PIPELINE_SUMMARY.md` - Implementation summary

### Modified Files (2 files)
- `package.json` - Added `test:a11y` script
- `phpunit.xml` - Added accessibility test suite

### Not Committed (Documentation only)
- `COMMIT_CHECKLIST.md` - This file
- `ACCESSIBILITY_PIPELINE_SUMMARY.md` - Already captured in commit
- `A11Y_QUICK_START.md` - Already captured in commit

## Post-Commit Steps

1. GitHub Actions will automatically run on the next PR
2. Monitor CI/CD for accessibility checks
3. Address identified accessibility issues (4 errors, 56 warnings)
4. Follow guidelines in `docs/ACCESSIBILITY.md` for fixes
5. Re-run `bash scripts/a11y-check.sh` to validate fixes

## Total Changes

- **1292 lines** of code and documentation
- **388 lines** accessibility scanner
- **565 lines** accessibility tests  
- **339 lines** accessibility documentation
- **2 files modified** (package.json, phpunit.xml)

## Success Criteria

After committing, verify:

- [ ] GitHub shows clean working tree: `git status` shows nothing
- [ ] Last commit message mentions accessibility pipeline
- [ ] `bash scripts/a11y-check.sh` runs successfully
- [ ] `composer test -- --testsuite=accessibility` passes
- [ ] `.github/workflows/accessibility.yml` exists
- [ ] Tests exist in `tests/accessibility/`
- [ ] Documentation exists in `docs/ACCESSIBILITY.md`

## Questions?

See:
- `A11Y_QUICK_START.md` - Developer quick reference
- `docs/ACCESSIBILITY.md` - Complete guide
- `ACCESSIBILITY_PIPELINE_SUMMARY.md` - Implementation details
