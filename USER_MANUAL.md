# FormFlow - User Manual

**Version:** 2.3.0
**Last Updated:** December 13, 2025

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard Overview](#dashboard-overview)
3. [Creating Form Instances](#creating-form-instances)
4. [Instance Editor Wizard](#instance-editor-wizard)
5. [Managing Submissions](#managing-submissions)
6. [Analytics & Reporting](#analytics--reporting)
7. [Marketing Attribution](#marketing-attribution)
8. [Handoff Tracking](#handoff-tracking)
9. [GTM/GA4 Integration](#gtmga4-integration)
10. [Scheduling & Capacity](#scheduling--capacity)
11. [Email Configuration](#email-configuration)
12. [Feature Toggles](#feature-toggles)
13. [Webhooks & Automation](#webhooks--automation)
14. [Settings & Tools](#settings--tools)
15. [Embedding Forms](#embedding-forms)
16. [Troubleshooting](#troubleshooting)
17. [Glossary](#glossary)

---

## Getting Started

### Installation

1. Upload the `formflow` folder to `/wp-content/plugins/`
2. Activate the plugin through **Plugins > Installed Plugins**
3. Navigate to **FF Forms** in the WordPress admin sidebar

### Initial Configuration

Before creating forms, configure your encryption key in `wp-config.php`:

```php
define('ISF_ENCRYPTION_KEY', 'your-32-character-secret-key');
```

**Important:** Use a unique, random 32-character string. This key encrypts sensitive data like API passwords.

### First-Time Setup Checklist

- [ ] Add encryption key to wp-config.php
- [ ] Navigate to FF Forms > Tools > Settings
- [ ] Configure global rate limiting preferences
- [ ] Create your first form instance

---

## Dashboard Overview

The Dashboard (**FF Forms > Dashboard**) is your central hub for managing all form instances.

### Quick Actions Bar

Located at the top of the dashboard:

| Action | Description |
|--------|-------------|
| **Add New** | Create a new form instance |
| **View Analytics** | Jump to analytics dashboard |
| **Export Data** | Export submission data |
| **Run Diagnostics** | System health check |

### Form Instances Table

Lists all your form instances with:

- **Form Name** - Click to edit
- **Utility** - Which utility this form serves
- **Type** - Enrollment or Scheduler
- **Status** - Active/Inactive toggle
- **Submissions** - Total count (click for details)
- **Actions** - Edit, Preview, Duplicate, Delete

### Drag-and-Drop Reordering

Drag form instances using the handle (0) on the left to reorder. The order is saved automatically.

### Status Indicators

| Icon | Meaning |
|------|---------|
| =� Green | Active and accepting submissions |
| =� Yellow | Test mode enabled |
| =4 Red | Inactive or maintenance mode |
| � Warning | Configuration issues detected |

---

## Creating Form Instances

### Step 1: Start the Wizard

Click **Add New** from the dashboard or navigate to **FF Forms > Add New**.

### Step 2: Complete the Wizard Steps

The instance editor uses a 7-step wizard:

1. **Basics** - Name, slug, utility, form type
2. **API** - Endpoint, password, test/demo modes
3. **Fields** - Configure which fields appear
4. **Scheduling** - Blocked dates, capacity limits
5. **Content** - Customize text and messages
6. **Email** - Email templates and settings
7. **Features** - Enable/disable advanced features

### Quick Edit Mode

Toggle **Quick Edit** in the header to see all settings on one page. Useful for experienced users making quick changes.

---

## Instance Editor Wizard

### Step 1: Basics

| Field | Description | Required |
|-------|-------------|----------|
| **Form Name** | Display name for admin reference | Yes |
| **Slug** | URL-friendly identifier for shortcode | Yes |
| **Utility** | Select utility (auto-fills API endpoint) | Yes |
| **Form Type** | Enrollment (full flow) or Scheduler (booking only) | Yes |
| **Default State** | Pre-selected state in forms | No |
| **Support Phone** | Phone number shown in error messages | No |

**Shortcode Preview:** `[isf_form instance="your-slug"]`

### Step 2: API Configuration

| Field | Description |
|-------|-------------|
| **API Endpoint** | PowerPortal IntelliSOURCE API URL |
| **API Password** | Encrypted and stored securely |
| **Test Mode** | Marks submissions as test data |
| **Demo Mode** | Uses mock data (no API calls) |

**Test API Connection:** Click the button to verify your credentials work.

#### Demo Mode Accounts

When Demo Mode is enabled, use these test accounts:

| Account # | ZIP | Behavior |
|-----------|-----|----------|
| 1234567890 | 19801 | Valid account |
| TEST123456 | 21201 | Valid account (any TEST* works) |
| 0000000001 | 00000 | Always valid |
| 9999999999 | 12345 | Invalid account |

### Step 3: Form Fields

Configure which fields appear on the customer information step:

| Field | Default | Description |
|-------|---------|-------------|
| Phone Number | Visible, Required | Customer contact number |
| Email Address | Visible, Required | For confirmation emails |
| Street Address | Visible, Required | Service address |
| City | Visible, Required | Service city |
| State | Visible, Required | Service state |
| ZIP Code | Visible, Required | Service ZIP |
| Promo Code | Hidden | Optional promotional code |

**Reorder Fields:** Drag using the handle (0) to change the order fields appear on the form.

### Step 4: Scheduling

#### Blocked Dates

Add dates when appointments are unavailable:

1. Click **Add Blocked Date**
2. Select the date
3. Enter a label (e.g., "Christmas Day")
4. Repeat for additional dates

**Reorder:** Drag to change display order.

#### Capacity Limits

Override API-provided slot limits:

| Time Slot | Description |
|-----------|-------------|
| Morning | 8 AM - 11 AM |
| Mid-Day | 11 AM - 2 PM |
| Afternoon | 2 PM - 5 PM |
| Evening | 5 PM - 8 PM |

Set to `0` to block a time slot entirely.

#### Scheduled Maintenance

Schedule a maintenance window:

1. Enable maintenance mode
2. Set start and end datetime
3. Enter a message for users

### Step 5: Content

Customize all text displayed on the form. Organized into tabs:

#### General Tab
- Form title
- Form description
- Program name

#### Steps Tab
- Step 1-5 titles

#### Help Tab
- Account number help text
- ZIP code help text
- Scheduling help text

#### Errors Tab
- Validation error message
- Scheduling error message
- Submission error message
- General error message

**Placeholder:** Use `{phone}` to insert support phone number.

#### Buttons Tab
- Next button text
- Back button text
- Submit button text
- Verify button text

#### Terms Tab
- Terms title
- Terms introduction
- Terms content (WYSIWYG editor)
- Terms footer
- Checkbox label

### Step 6: Email Settings

| Field | Description |
|-------|-------------|
| **Send Confirmation Email** | Enable/disable emails from this site |
| **From Email** | Sender email address |
| **CC Emails** | Additional recipients (comma-separated) |

#### Email Template

Customize the confirmation email with placeholders:

| Placeholder | Description |
|-------------|-------------|
| `{name}` | Customer full name |
| `{email}` | Customer email |
| `{phone}` | Customer phone |
| `{address}` | Full address |
| `{device}` | Device type |
| `{date}` | Appointment date |
| `{time}` | Appointment time |
| `{confirmation_number}` | Confirmation code |
| `{program_name}` | Program name |

### Step 7: Features

Enable/disable advanced features per instance. See [Feature Toggles](#feature-toggles) for details.

---

## Managing Submissions

Navigate to **FF Forms > Data > Submissions**

### Viewing Submissions

The submissions table shows:

- Confirmation number
- Customer name
- Status (In Progress, Completed, Failed, Abandoned)
- Device type
- Created date
- Actions

### Filtering

Filter submissions by:
- Instance (form)
- Status
- Date range
- Search (name, email, account)

### Submission Details

Click a submission to view:

- Customer information
- Form data
- API responses
- Timeline of events
- Related logs

### Bulk Actions

Select multiple submissions for:
- Export to CSV
- Mark as test data
- Delete (permanent)

### Statuses Explained

| Status | Description |
|--------|-------------|
| **In Progress** | Customer started but hasn't completed |
| **Completed** | Successfully submitted to API |
| **Failed** | API submission failed |
| **Abandoned** | No activity for 24+ hours |

---

## Analytics & Reporting

Navigate to **FF Forms > Data > Analytics**

### Dashboard Metrics

- **Total Submissions** - All-time count
- **Completed** - Successfully submitted
- **Completion Rate** - Percentage completed
- **Avg. Time to Complete** - Average duration

### Funnel Analysis

Visual representation of drop-off at each step:

```
Step 1: Program Selection  ���������������� 100%
Step 2: Account Validation ��������������   87%
Step 3: Customer Info      ������������     75%
Step 4: Scheduling         ����������       62%
Step 5: Confirmation       ��������         50%
```

### Time-Based Reports

- Daily submissions chart
- Weekly trends
- Monthly comparison
- Year-over-year analysis

### Export Options

- CSV export
- Excel export
- PDF reports (scheduled)

---

## Marketing Attribution

Navigate to **FF Forms > Data > Attribution**

Marketing attribution helps you understand which campaigns, channels, and touchpoints drive enrollments.

### Enabling Visitor Tracking

Before using attribution features, enable visitor tracking:

1. Edit your form instance
2. Go to **Features** tab
3. Enable **Visitor Analytics**
4. Configure settings:
   - **Track Page Views** - Record all pages with ISF content
   - **Cookie Duration** - How long to track visitors (default: 365 days)
   - **Fingerprinting** - Optional browser fingerprinting for cross-device tracking

### Attribution Models

The plugin supports 5 attribution models:

| Model | How It Works | Best For |
|-------|--------------|----------|
| **First Touch** | 100% credit to first touchpoint | Understanding awareness channels |
| **Last Touch** | 100% credit to last touchpoint | Understanding conversion triggers |
| **Linear** | Equal credit to all touchpoints | Valuing entire journey |
| **Time Decay** | More credit to recent touchpoints | Short sales cycles |
| **Position Based** | 40% first, 40% last, 20% middle | Balanced view |

### Attribution Report Features

#### Campaign Performance
- View enrollments by source/medium/campaign
- Conversion rates per channel
- Cost-per-acquisition (if cost data provided)

#### Visitor Journeys
- See complete path from first visit to enrollment
- Identify common paths to conversion
- Find drop-off points in the journey

#### Top Campaigns
- Ranked list of best-performing campaigns
- Click-through and conversion metrics
- Time-to-conversion averages

### Setting Up UTM Tracking

Use UTM parameters in your marketing links:

```
https://yoursite.com/enroll/?utm_source=email&utm_medium=newsletter&utm_campaign=spring2025
```

**UTM Parameters:**
| Parameter | Purpose | Example |
|-----------|---------|---------|
| `utm_source` | Traffic source | `google`, `facebook`, `email` |
| `utm_medium` | Marketing medium | `cpc`, `banner`, `newsletter` |
| `utm_campaign` | Campaign name | `spring2025`, `holiday_promo` |
| `utm_term` | Paid keywords | `energy savings` |
| `utm_content` | Ad variation | `blue_button`, `hero_image` |

### Tracked Ad Click IDs

The plugin automatically captures:
- `gclid` - Google Ads
- `fbclid` - Facebook/Meta Ads
- `msclkid` - Microsoft/Bing Ads

---

## Handoff Tracking

Handoff tracking lets you attribute enrollments even when customers are redirected to external systems (like the IntelliSOURCE platform).

### How Handoff Tracking Works

1. **Customer clicks enroll button** on your site
2. **Plugin generates tracking token** with attribution data
3. **Customer redirected** to external enrollment system
4. **External system sends completion** back via webhook or redirect
5. **Plugin matches completion** to original handoff, preserving attribution

### Enabling Handoff Tracking

1. Edit your form instance
2. Go to **Features** tab
3. Enable **Handoff Tracking**
4. Configure:
   - **Destination URL** - External enrollment system URL
   - **Append Account Param** - Include account number in redirect
   - **Append UTM Params** - Pass through UTM parameters
   - **Show Interstitial** - Display message before redirect
   - **Interstitial Message** - Custom redirect message

### Using the Enroll Button Shortcode

Instead of linking directly to external systems, use the tracked button:

```
[isf_enroll_button instance="pepco-dc"]
```

**Options:**
| Attribute | Default | Description |
|-----------|---------|-------------|
| `text` | "Enroll Now" | Button text |
| `class` | (none) | Additional CSS classes |
| `external` | (from settings) | Override destination URL |

### Receiving Completions

#### Option 1: Webhook (Recommended)

Configure your external system to POST completions:

```
POST /wp-json/isf/v1/completions/webhook

Headers:
  X-ISF-Signature: sha256=<hmac_signature>
  Content-Type: application/json

Body:
{
  "instance_id": 1,
  "handoff_token": "token-from-redirect",
  "account_number": "1234567890",
  "customer_email": "customer@example.com",
  "external_id": "EXT-12345",
  "completion_type": "enrollment"
}
```

**Security:** Use HMAC signature verification. Configure the secret in **FF Forms > Tools > Analytics Settings**.

#### Option 2: CSV Import

For batch imports:

1. Navigate to **FF Forms > Data > Import Completions**
2. Upload CSV file
3. Map columns to fields
4. Preview and confirm
5. Review import results

**CSV Format:**
```csv
account_number,email,external_id,completion_date
1234567890,john@example.com,EXT-001,2025-12-13
0987654321,jane@example.com,EXT-002,2025-12-12
```

### Handoff Report

View handoff statistics at **FF Forms > Data > Attribution > Handoffs**:

- Total handoffs
- Completion rate
- Unmatched completions
- Average time to completion

---

## GTM/GA4 Integration

Integrate with Google Tag Manager and Google Analytics 4 for enhanced tracking.

### Setting Up GTM

1. Navigate to **FF Forms > Tools > Analytics Settings**
2. Enable GTM Integration
3. Enter your GTM Container ID (GTM-XXXXXXX)
4. Save changes

The plugin automatically:
- Injects GTM container code
- Initializes `dataLayer` with visitor/instance data
- Pushes form events to `dataLayer`

### Events Pushed to dataLayer

| Event | Trigger | Data Included |
|-------|---------|---------------|
| `isf_form_view` | Form becomes visible | instance, visitor_id |
| `isf_form_start` | First field interaction | instance, visitor_id |
| `isf_form_step` | Step navigation | step number, step name |
| `isf_form_complete` | Successful submission | device type, instance |
| `isf_handoff` | External redirect | destination URL |

### GTM Configuration

In Google Tag Manager, create:

**Triggers:**
- Custom Event trigger for each `isf_*` event

**Variables:**
- Data Layer Variables for `isf_instance`, `isf_visitor_id`, `isf_step`, etc.

**Tags:**
- GA4 Event tags mapping to each trigger

### GA4 Measurement Protocol

For server-side tracking (more accurate):

1. Get your GA4 Measurement ID (G-XXXXXXX)
2. Create API Secret in GA4:
   - Admin > Data Streams > Select stream
   - Measurement Protocol API secrets
   - Create new secret
3. Configure in **FF Forms > Tools > Analytics Settings**:
   - GA4 Measurement ID
   - GA4 API Secret

**Server-side events sent:**
- `enrollment_complete`
- `enrollment_handoff`

### Microsoft Clarity

For session recording and heatmaps:

1. Create Clarity project at clarity.microsoft.com
2. Copy Project ID
3. Enter in **FF Forms > Tools > Analytics Settings**
4. Save

Clarity will automatically track form interactions.

---

## Scheduling & Capacity

### How Scheduling Works

1. Customer validates account
2. Plugin fetches available slots from API
3. Blocked dates are removed
4. Capacity limits applied
5. Customer selects date/time
6. Appointment booked via API

### Managing Blocked Dates

**Via Instance Editor:**
1. Edit instance > Scheduling tab
2. Add dates with labels
3. Save changes

**Common Blocked Dates:**
- Federal holidays
- Company holidays
- Training days
- Maintenance windows

### Capacity Management Feature

Enable per-instance capacity controls:

1. Edit instance > Features
2. Enable "Capacity Management"
3. Configure:
   - Daily cap
   - Per-slot limits
   - Waitlist options

### Waitlist

When enabled, full slots show "Join Waitlist" option:
- Customers receive email when slot opens
- First-come, first-served basis
- Configurable notification timing

---

## Email Configuration

### Global Email Settings

**FF Forms > Tools > Settings > Email**

- Default from name
- Default from email
- Email footer text
- Logo for emails

### Per-Instance Settings

Each instance can override global settings:

- Custom from email
- CC recipients
- Custom templates

### Email Types

| Email | Trigger | Customizable |
|-------|---------|--------------|
| Enrollment Confirmation | Successful submission | Yes |
| Appointment Confirmation | Booking complete | Yes |
| Appointment Reminder | 24 hours before | Yes |
| Reschedule Confirmation | Appointment changed | Yes |
| Cancellation Confirmation | Appointment cancelled | Yes |
| Admin Notification | New submission | Partially |

### Testing Emails

1. Enable Test Mode on instance
2. Submit a test enrollment
3. Check email delivery
4. Review email content

---

## Feature Toggles

Each feature can be enabled per form instance.

### Form Experience

#### Inline Validation
Real-time validation as users type:
- Email format checking
- Phone number formatting
- ZIP code validation
- Success/error indicators

#### Auto-Save Drafts
Automatically save progress:
- Server-side backup
- localStorage fallback
- Resume prompt on return
- Configurable interval

#### Spanish Translation
Bilingual support:
- Language toggle on form
- Browser auto-detection
- Complete translations

### Notifications

#### SMS Notifications
Text message alerts:
- Enrollment confirmation
- Appointment reminders
- Requires Twilio account

#### Team Notifications
Slack/Teams alerts:
- New enrollments
- Failed submissions
- Daily digest

#### Email Digest
Summary emails:
- Daily or weekly
- Enrollment counts
- Conversion rates

### Scheduling

#### Appointment Self-Service
Customer appointment management:
- Reschedule via email link
- Cancel appointments
- Configurable deadlines

#### Capacity Management
Advanced slot controls:
- Daily caps
- Per-slot limits
- Waitlist

### Analytics

#### UTM Tracking
Marketing attribution:
- Campaign tracking
- Source/medium/content
- Attribution reports

#### Visitor Analytics
Cross-session visitor tracking:
- First-party cookie identification
- Journey reconstruction
- Multi-touch attribution
- GTM/GA4 integration

#### Handoff Tracking
External enrollment tracking:
- Token-based redirect tracking
- Completion matching
- Attribution preservation
- Webhook or CSV import

#### A/B Testing
Form variation testing:
- Multiple variants
- Traffic splitting
- Conversion tracking

### Integrations

#### Document Upload
File attachments:
- Photo uploads
- PDF documents
- Configurable limits

#### CRM Integration
External sync:
- Salesforce
- HubSpot
- Zoho
- Custom API

### Security

#### CAPTCHA Protection
Spam prevention:
- reCAPTCHA v2/v3
- hCaptcha
- Configurable triggers

#### Fraud Detection
Risk scoring:
- Duplicate detection
- IP velocity checks
- Device fingerprinting

---

## Webhooks & Automation

Navigate to **FF Forms > Automation**

### Webhooks Tab

Send data to external services on form events.

#### Creating a Webhook

1. Click **Add Webhook**
2. Enter destination URL
3. Select events to trigger
4. Configure options
5. Save

#### Available Events

| Event | Description |
|-------|-------------|
| `account.validated` | Account validation success |
| `enrollment.completed` | Form submitted |
| `enrollment.failed` | Submission failed |
| `appointment.scheduled` | Booking confirmed |
| `form.abandoned` | Form abandoned |

#### Webhook Payload

```json
{
  "event": "enrollment.completed",
  "timestamp": "2025-12-13T10:30:00Z",
  "instance_id": 1,
  "data": {
    "submission_id": 123,
    "confirmation_number": "EWR-ABC12345",
    "customer_name": "John Doe",
    "email": "john@example.com"
  }
}
```

#### Security

- Webhooks include signature header
- Verify with shared secret
- HTTPS required

### Reports Tab

Schedule automated reports:

1. Click **Add Scheduled Report**
2. Select report type
3. Choose frequency (daily/weekly/monthly)
4. Enter recipients
5. Save

---

## Settings & Tools

Navigate to **FF Forms > Tools**

### Settings Tab

#### General Settings
- Rate limiting configuration
- Session timeout
- Data retention period

#### Rate Limiting Presets

| Preset | Requests/Minute | Use Case |
|--------|-----------------|----------|
| Strict | 30 | High-traffic protection |
| Normal | 60 | Balanced |
| Relaxed | 120 | Multi-step forms (default) |
| Very Relaxed | 200 | High-volume periods |

### Diagnostics Tab

Run system health checks:

- **PHP Requirements** - Version, extensions, memory
- **WordPress Requirements** - Version, SSL, permalinks
- **Database** - Table integrity, connections
- **Security** - Encryption, nonces, rate limiting
- **API** - Per-instance connectivity tests

#### Running Diagnostics

1. Click **Run Full Diagnostics**
2. Review results
3. Address any failures
4. Export results for support

### Compliance Tab

GDPR and data management tools:

#### Data Retention
Configure automatic data deletion:
- Submission data retention (days)
- Log retention (days)
- Abandoned session cleanup

#### GDPR Requests
Handle data requests:
- Export customer data
- Delete customer data
- Request tracking

---

## Embedding Forms

### Shortcode (Recommended)

Add to any WordPress page or post:

```
[isf_form instance="your-slug"]
```

**Options:**
- `class` - Additional CSS classes

### iframe Embed (External Sites)

For non-WordPress sites:

1. Edit instance > Features
2. Enable "Embeddable Form"
3. Copy the iframe code
4. Paste into external site

```html
<iframe
  src="https://yoursite.com/isf-embed/TOKEN"
  width="100%"
  height="800"
  frameborder="0">
</iframe>
```

### JavaScript Embed

For dynamic embedding:

```html
<div data-isf-token="your-token"></div>
<script src="https://yoursite.com/wp-content/plugins/formflow/public/assets/js/embed.js"></script>
```

---

## Troubleshooting

### Form Not Displaying

**Check:**
1. Instance slug matches shortcode
2. Instance is Active (not inactive)
3. No JavaScript errors in browser console
4. Form not in maintenance mode

### Account Validation Failing

**Check:**
1. API endpoint URL is correct
2. API password is valid
3. Test API connection button works
4. Account number format matches utility

### Scheduling Not Loading

**Check:**
1. Account was validated first
2. API connectivity is working
3. Blocked dates aren't blocking all dates
4. Capacity limits aren't set to zero

### Emails Not Sending

**Check:**
1. "Send Confirmation Email" is enabled
2. From email is valid
3. WordPress can send emails (test with another plugin)
4. Check spam folder

### Rate Limiting Issues (429 Errors)

**Check:**
1. Rate limit preset (try "Relaxed")
2. Multiple users sharing IP (office, university)
3. Temporarily disable for testing
4. Check diagnostics for rate limit status

### API Connection Errors

**Check:**
1. Run diagnostics > API tests
2. Verify SSL certificate is valid
3. Check firewall isn't blocking outbound
4. API endpoint is accessible

### Common Error Codes

| Code | Meaning | Solution |
|------|---------|----------|
| `03` | Already enrolled | Direct to scheduler |
| `21` | Medical condition | Show acknowledgment |
| `02` (status) | Previous enrollment | Check with customer |
| `-1` | Slot unavailable | Refresh and retry |

---

## Glossary

| Term | Definition |
|------|------------|
| **Instance** | A configured form for a specific utility |
| **Slug** | URL-friendly identifier used in shortcodes |
| **Enrollment** | Full multi-step signup process |
| **Scheduler** | Appointment-only booking flow |
| **DCU** | Direct Control Unit (outdoor switch device) |
| **Cycling Level** | Percentage of time device can be controlled |
| **Nonce** | Security token preventing CSRF attacks |
| **Transient** | Temporary cached data |
| **AJAX** | Asynchronous requests without page reload |
| **Webhook** | HTTP callback on events |
| **GDPR** | General Data Protection Regulation |
| **UTM** | Urchin Tracking Module (marketing parameters) |
| **CAPTCHA** | Challenge to prevent automated submissions |
| **Attribution** | Crediting marketing channels for conversions |
| **Touchpoint** | Customer interaction with marketing content |
| **Handoff** | Redirect to external enrollment system |
| **GTM** | Google Tag Manager |
| **GA4** | Google Analytics 4 |
| **dataLayer** | JavaScript array for passing data to GTM |
| **Measurement Protocol** | Server-side GA4 event tracking API |
| **Visitor ID** | Anonymous identifier for cross-session tracking |
| **First Touch** | First marketing interaction before conversion |
| **Last Touch** | Final marketing interaction before conversion |

---

## Support

For technical support:

1. Run diagnostics and export results
2. Note the error message or behavior
3. Contact Peanut Graphic support

---

## Document History

| Version | Date | Changes |
|---------|------|---------|
| 2.3.0 | Dec 13, 2025 | Added Marketing Attribution, Handoff Tracking, GTM/GA4 Integration sections |
| 2.2.0 | Dec 13, 2025 | Initial manual creation |

---

*FormFlow is developed by Peanut Graphic.*
