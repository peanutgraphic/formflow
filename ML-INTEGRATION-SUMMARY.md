# FormFlow Pro - ML Form Completion Prediction Integration

**Status:** Complete - Ready for production use

## Overview

This integration wires the form completion prediction ML module into FormFlow Pro WordPress plugin. The ML service is a microservice running at `http://127.0.0.1:8100` that predicts form completion likelihood and provides optimization recommendations.

## Files Created

### 1. `/includes/ml/class-form-prediction.php`

**Namespace:** `ISF\ML\FormPrediction`

Core ML service integration class with the following methods:

#### `__construct()`
- Reads ML service configuration from `peanut_ml_settings` WordPress option (shared across all Peanut plugins)
- Initializes `$service_url` and `$api_key`
- Sanitizes service URL (removes trailing slashes)

#### `predict_completion(array $form_data): ?array`
- Sends form field data to ML service at `POST /form/predict` endpoint
- Returns prediction array containing:
  - `completion_probability`: float (0-1)
  - `risk_score`: float (0-1)
  - `recommendations`: array of suggested optimizations
- Implements 15-minute transient cache to avoid redundant calls
- Returns `null` on failure (graceful degradation)

#### `train_model(): bool`
- Triggers model retraining via `POST /form/train` endpoint
- Uses accumulated form submission data from database
- Returns `true` if training successfully initiated, `false` otherwise
- Called weekly via cron event `peanut_ml_formflow_train`

#### `is_available(): bool`
- Performs health check via `GET /health` endpoint
- Implements 2-minute transient cache to minimize overhead
- Returns boolean status of ML service availability
- Safe to call frequently due to caching

#### `make_request(string $method, string $endpoint, array $data = []): ?array` (private)
- Shared HTTP helper for all service requests
- Uses WordPress `wp_remote_post()` / `wp_remote_get()`
- Automatically adds authentication header: `X-ML-API-Key`
- Handles JSON encoding/decoding
- Returns decoded response array or null on failure
- Comprehensive error logging to `error_log()`

**Error Handling:**
- All failures logged with `error_log()` prefixed with `[FormFlow ML]`
- Returns `null` on any error - never throws exceptions
- Form submission flow continues normally even if ML service is unavailable

## 2. `/includes/ml/class-form-prediction-api.php`

**Namespace:** `ISF\ML\FormPredictionApi`

REST API controller exposing ML functionality to WordPress:

### Routes

#### `GET /formflow/v1/ml/health`
- Public endpoint (requires `read` capability)
- Returns service health status and availability
- Response: `{ "status": "healthy|unavailable", "service_available": bool, "timestamp": string }`
- Status code: 200 (healthy) or 503 (unavailable)

#### `POST /formflow/v1/ml/predict`
- Public endpoint (requires `read` capability)
- **Parameters:**
  - `form_data` (object, required): Form fields to analyze
- Sanitizes all input using `sanitize_text_field()`, etc.
- Skips sensitive fields (password, secret, token, api_key)
- Response: `{ "prediction": {...}, "timestamp": string }`
- Status code: 200 (success) or 503 (service unavailable)

#### `POST /formflow/v1/ml/train`
- Admin-only endpoint (requires `manage_options` capability)
- No parameters
- Triggers model retraining process
- Response: `{ "status": "training_initiated", "message": "...", "timestamp": string }`
- Status code: 202 (Accepted) on success, 500 or 403 on failure

### Methods

#### `register_routes(): void`
- Called during `rest_api_init` hook
- Registers all three REST routes above
- Includes parameter validation and permissions

#### `check_health(WP_REST_Request $request): WP_REST_Response`
- Delegates to FormPrediction::is_available()

#### `predict(WP_REST_Request $request): WP_REST_Response|WP_Error`
- Validates and sanitizes form_data parameter
- Calls FormPrediction::predict_completion()
- Returns prediction results or error response

#### `train(WP_REST_Request $request): WP_REST_Response|WP_Error`
- Verifies admin capability
- Calls FormPrediction::train_model()
- Returns success status or error

#### `permission_read(): bool` / `permission_admin(): bool`
- Permission callbacks for REST routes
- `permission_read()`: checks `current_user_can('read')`
- `permission_admin()`: checks `current_user_can('manage_options')`

#### `sanitize_form_data(array $form_data): array` (private)
- Recursively sanitizes form data
- Removes sensitive fields (passwords, tokens, API keys)
- Handles strings, numbers, booleans, arrays
- Skips unsupported types

## Files Modified

### 1. `formflow.php`

**Change:** Updated autoloader class map (lines 51-61)

Added entries to support PSR-4 autoloading for ML namespace:
```php
'ISF\\ML\\FormPrediction' => 'ml/class-form-prediction.php',
'ISF\\ML\\FormPredictionApi' => 'ml/class-form-prediction-api.php',
```

While the generic PSR-4 handler can load these via the standard convention, the class map ensures they're found immediately without directory traversal.

### 2. `includes/class-plugin.php`

**Changes:**

**A. load_dependencies() method (added lines)**
```php
// ML/Prediction classes
require_once ISF_PLUGIN_DIR . 'includes/ml/class-form-prediction.php';
require_once ISF_PLUGIN_DIR . 'includes/ml/class-form-prediction-api.php';
```

Ensures ML classes are available during plugin initialization.

**B. register_cron_handlers() method (added line)**
```php
add_action('peanut_ml_formflow_train', [$this, 'train_ml_model']);
```

Registers handler for weekly cron event.

**C. register_analytics_rest_routes() method (added lines)**
```php
// Register ML prediction API routes
$prediction_api = new ML\FormPredictionApi();
$prediction_api->register_routes();
```

Registers REST API routes at `rest_api_init` hook.

**D. ensure_cron_events_scheduled() method (added lines)**
```php
if (!wp_next_scheduled('peanut_ml_formflow_train')) {
    wp_schedule_event(time(), 'weekly', 'peanut_ml_formflow_train');
}
```

Ensures weekly training event is scheduled if missing.

**E. train_ml_model() method (new)**
```php
public function train_ml_model(): void
```

Cron job handler that:
- Creates FormPrediction instance
- Checks ML service availability
- Triggers model training
- Logs results to error_log

### 3. `includes/class-activator.php`

**Change:** schedule_cron_events() method (added lines ~710)

```php
// ML model training - run weekly
if (!wp_next_scheduled('peanut_ml_formflow_train')) {
    wp_schedule_event(time(), 'weekly', 'peanut_ml_formflow_train');
}
```

Registers weekly cron schedule for model training on plugin activation.

## Configuration

### ML Service Settings

Settings are read from WordPress option `peanut_ml_settings` (shared across all Peanut plugins):

```php
$settings = get_option('peanut_ml_settings', []);
// Expected keys:
// - 'ml_service_url': string (default: 'http://127.0.0.1:8100')
// - 'ml_api_key': string (required for operation)
```

This option should be managed by Peanut Suite or a central configuration plugin.

## Integration Points

### With FormFlow Analytics
- ML training uses form submission data from FormFlow database
- Completion predictions can inform analytics dashboards
- Training event complements existing cron jobs (logs, retention, etc.)

### With Fraud Detection
- ML predictions can be used to enhance fraud risk scoring
- Both systems use similar form data structures
- Both handle errors gracefully and log failures

### With REST API
- ML endpoints follow FormFlow REST conventions
- Uses `formflow/v1` namespace (not separate prefix)
- Respects WordPress capabilities system
- Input sanitization matches FormFlow standards

## Cron Job

**Event:** `peanut_ml_formflow_train`
**Schedule:** Weekly
**Handler:** `ISF\Plugin::train_ml_model()`

Triggers:
- On plugin activation (Activator::schedule_cron_events)
- On plugin init (Plugin::ensure_cron_events_scheduled - reschedule if missing)

Execution:
- Checks ML service availability
- Calls FormPrediction::train_model()
- Logs results to error_log

## Error Handling & Logging

All errors logged to `error_log()` with `[FormFlow ML]` prefix:
- ML API key not configured
- ML service URL not configured
- HTTP request failures
- JSON parsing errors
- Service timeouts
- Training job failures

All logging includes context (endpoint, status code, response summary).

## Security

- **API Key:** Stored in WordPress option, transmitted in custom header
- **Input Sanitization:** All form data sanitized before transmission
- **Sensitive Fields:** Password, token, secret, api_key fields removed before sending
- **REST Permissions:** Proper capability checks (read vs manage_options)
- **HTTPS:** sslverify=true by default in WordPress requests
- **Timeout:** 10-second timeout to prevent hanging requests

## Testing

### REST API Health Check
```bash
curl https://site.com/wp-json/formflow/v1/ml/health
```

### Test Prediction
```bash
curl -X POST https://site.com/wp-json/formflow/v1/ml/predict \
  -H "Content-Type: application/json" \
  -d '{"form_data": {"fields": ["name", "email"], "field_count": 2}}'
```

### Manual Training (Admin)
```bash
curl -X POST https://site.com/wp-json/formflow/v1/ml/train \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN"
```

## Production Checklist

- [ ] Verify ML service is running at correct URL
- [ ] Configure `peanut_ml_settings` with service URL and API key
- [ ] Test REST endpoints work and return valid responses
- [ ] Verify weekly cron event appears in WordPress cron list
- [ ] Check error_log for any initialization errors
- [ ] Monitor first training run (check logs)
- [ ] Verify predictions are cached properly

## Code Quality

- **Standards:** Follows WordPress coding standards and FormFlow conventions
- **PHPDoc:** All public methods have complete documentation
- **Error Handling:** Graceful degradation - never breaks form flow
- **Performance:** Transient caching (15 min predictions, 2 min health)
- **Testing:** Compatible with existing FormFlow test suite
- **Backwards Compatibility:** No breaking changes to FormFlow APIs

## Notes

- ML module is optional - plugin works without it if service unavailable
- Service integration is lean (FormFlow handles just the API layer, service handles ML logic)
- Weekly training can be manually triggered via REST API by admins
- Configuration can be updated without plugin reactivation
- All infrastructure-level error handling via WordPress native functions
