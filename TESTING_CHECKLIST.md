# FormFlow - Analytics Module Testing Checklist

## Pre-Testing Setup

1. [ ] Activate the plugin on a WordPress test site
2. [ ] Verify plugin activates without PHP errors
3. [ ] Check that all database tables are created (WP Admin > ISF > Tools > Diagnostics)

## Database Tables Verification

Check that these tables exist in the database:
- [ ] `wp_isf_visitors`
- [ ] `wp_isf_touches`
- [ ] `wp_isf_handoffs`
- [ ] `wp_isf_external_completions`

## 1. Visitor Tracking Tests

### 1.1 Cookie-based Visitor ID
- [ ] Visit a page with an ISF form (incognito/private mode)
- [ ] Check browser cookies for `isf_visitor` cookie
- [ ] Verify cookie contains 32-character hex string
- [ ] Close browser, reopen, visit same page - verify same visitor ID

### 1.2 Visitor Record Creation
- [ ] Query `wp_isf_visitors` table for new visitor record
- [ ] Verify `first_seen_at` timestamp is correct
- [ ] Verify `device_info` JSON contains browser/OS/mobile flag
- [ ] Verify `first_touch` JSON contains any UTM params from URL

### 1.3 UTM Parameter Capture
- [ ] Visit form page with URL: `?utm_source=test&utm_medium=email&utm_campaign=testing`
- [ ] Check visitor's `first_touch` contains all three UTM params
- [ ] Test with `gclid`, `fbclid`, `msclkid` parameters

## 2. Touch Recording Tests

### 2.1 Page View Touch
- [ ] Visit a page with ISF form
- [ ] Check `wp_isf_touches` for `page_view` record
- [ ] Verify `visitor_id` matches cookie value

### 2.2 Form Interaction Touches
- [ ] View an enrollment form - check for `form_view` touch
- [ ] Start filling the form (interact with first field) - check for `form_start` touch
- [ ] Complete form submission - check for `form_complete` touch

### 2.3 UTM Attribution on Touches
- [ ] Visit form with UTM params, complete form
- [ ] Verify `form_complete` touch has UTM values populated
- [ ] Verify referrer info captured for non-direct visits

## 3. Attribution Calculator Tests

### 3.1 First Touch Attribution
- [ ] Go to Attribution Report page
- [ ] Select "First Touch" model
- [ ] Verify conversions attributed to first traffic source

### 3.2 Last Touch Attribution
- [ ] Select "Last Touch" model
- [ ] Verify conversions attributed to last traffic source

### 3.3 Multi-Touch Attribution
- [ ] Test Linear model (equal credit split)
- [ ] Test Time Decay model (more credit to recent touches)
- [ ] Test Position-Based model (40/20/40 split)

### 3.4 Channel Performance Report
- [ ] Verify channels show unique visitors
- [ ] Verify conversion rates are calculated correctly
- [ ] Test with different date ranges

## 4. Handoff Tracking Tests

### 4.1 Handoff Creation
- [ ] Configure an instance with handoff destination URL
- [ ] Use `[isf_enroll_button external="URL"]` shortcode
- [ ] Click button and verify redirect
- [ ] Check `wp_isf_handoffs` for new record with `redirected` status

### 4.2 Handoff Token
- [ ] Verify destination URL includes `isf_ref` parameter
- [ ] Token should be 32-character hex string

### 4.3 Handoff Completion
- [ ] Use webhook endpoint to mark handoff complete:
  ```bash
  curl -X POST https://yoursite.com/wp-json/isf/v1/completions/webhook \
    -H "Content-Type: application/json" \
    -d '{"isf_ref": "TOKEN", "status": "completed", "account_number": "12345"}'
  ```
- [ ] Verify handoff status changed to `completed`
- [ ] Verify `completed_at` timestamp set

### 4.4 Handoff Stats
- [ ] View handoff statistics in attribution report
- [ ] Verify completion rate calculation is accurate

## 5. External Completions Tests

### 5.1 Webhook Reception
- [ ] Send test webhook to `/wp-json/isf/v1/completions/webhook`
- [ ] Verify record created in `wp_isf_external_completions`
- [ ] Test with different field naming conventions:
  - `account_number` / `accountNumber` / `utility_no`
  - `email` / `customer_email`
  - `external_id` / `confirmation_number` / `id`

### 5.2 Webhook Security
- [ ] Test without signature header (should fail if required)
- [ ] Test with invalid signature (should fail)
- [ ] Test with valid HMAC signature (should succeed)

### 5.3 CSV Import
- [ ] Go to Import Completions page
- [ ] Upload test CSV with completion data
- [ ] Verify field mapping works correctly
- [ ] Check import results (imported count, matched count, errors)

### 5.4 Handoff Matching
- [ ] Import completion that matches existing handoff
- [ ] Verify `handoff_id` populated in completion record
- [ ] Verify handoff marked as completed

## 6. GTM/GA4 Integration Tests

### 6.1 DataLayer Events
- [ ] Enable GTM in Analytics Settings
- [ ] Open browser console, monitor `dataLayer` array
- [ ] Visit form page - check for `isf_form_view` event
- [ ] Start form - check for `isf_form_start` event
- [ ] Progress through steps - check for `isf_form_step` events
- [ ] Complete form - check for `isf_form_complete` event

### 6.2 GTM Container Integration
- [ ] Add valid GTM container ID
- [ ] Verify GTM container loaded on frontend
- [ ] Verify events flow to GTM

### 6.3 GA4 Direct Integration
- [ ] Add GA4 measurement ID
- [ ] Use browser's GA4 debug mode
- [ ] Verify events received by GA4

## 7. Export Functionality Tests

### 7.1 Attribution Exports
- [ ] Export Channel Performance CSV
- [ ] Export Campaigns CSV
- [ ] Export Handoffs CSV
- [ ] Export Full Report CSV
- [ ] Verify all CSVs open correctly in Excel

### 7.2 Completions Export
- [ ] Export external completions
- [ ] Export unmatched completions
- [ ] Verify date filtering works

### 7.3 Report Generator
- [ ] Schedule a report that includes attribution data
- [ ] Verify email contains attribution summary
- [ ] Verify CSV attachment includes attribution data

## 8. Analytics Diagnostics Tests

### 8.1 Health Check
- [ ] Go to Analytics Settings page
- [ ] View Analytics Health Check section
- [ ] Verify all database tables show "Pass"
- [ ] Verify visitor tracking shows correct status

### 8.2 Instance Configuration Check
- [ ] Check that instances with analytics enabled are listed
- [ ] Verify GTM integration status is correct
- [ ] Verify handoff tracking status is accurate

## 9. Admin UI Tests

### 9.1 Attribution Report Page
- [ ] Date range selector works
- [ ] Instance selector works
- [ ] Model selector changes data
- [ ] Export buttons function correctly

### 9.2 Analytics Settings Page
- [ ] Save global analytics settings
- [ ] Toggle GTM/GA4/Clarity settings
- [ ] Verify Quick Start Guide displays

### 9.3 Import Completions Page
- [ ] File upload works
- [ ] Field mapping interface shows correctly
- [ ] Import preview displays

## 10. Security Tests

### 10.1 Permission Checks
- [ ] Test AJAX endpoints as non-admin user (should fail)
- [ ] Test REST endpoints without authentication (should fail where required)
- [ ] Verify nonce validation on all forms

### 10.2 Input Validation
- [ ] Test form inputs with XSS payloads (should be sanitized)
- [ ] Test SQL injection attempts (should be prevented)
- [ ] Test with malformed JSON in webhooks

## 11. Performance Tests

### 11.1 Large Dataset Handling
- [ ] Create 1000+ visitor records
- [ ] Test attribution calculation speed
- [ ] Test export functionality with large datasets

### 11.2 Database Query Efficiency
- [ ] Monitor query count on admin pages
- [ ] Check for N+1 query issues in reports

## Notes

Document any issues found:

| Issue | Severity | Steps to Reproduce | Status |
|-------|----------|-------------------|--------|
|       |          |                   |        |

---

Last Updated: December 2024
Version: 2.3.0
