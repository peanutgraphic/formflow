# FormFlow Pro - Architecture Documentation

## Overview

FormFlow Pro (FormFlow) uses a **two-tier white-label architecture** that separates the core platform from API-specific integrations. This allows the same codebase to power:

1. **IntelliSource utility enrollments** (original use case)
2. **White-labeled enrollment platforms** for other industries
3. **Custom integrations** with any backend API

## Directory Structure

```
formflow/
├── formflow.php      # Main plugin file
├── includes/                    # Core platform code
│   ├── class-plugin.php         # Main orchestrator
│   ├── class-branding.php       # White-label configuration
│   ├── class-hooks.php          # Hook documentation/constants
│   ├── class-feature-manager.php
│   ├── class-security.php
│   ├── class-encryption.php
│   ├── api/
│   │   ├── interface-api-connector.php   # Connector contract
│   │   ├── class-connector-registry.php  # Connector management
│   │   └── [generic response classes]
│   ├── forms/
│   │   ├── class-form-handler.php
│   │   └── class-email-handler.php
│   ├── database/
│   │   └── class-database.php
│   └── [feature handlers]
│
├── connectors/                  # API connectors (modular)
│   └── intellisource/           # Bundled IntelliSource connector
│       ├── loader.php
│       ├── class-intellisource-connector.php
│       ├── class-intellisource-field-mapper.php
│       └── class-intellisource-xml-parser.php
│
├── admin/                       # Admin interface
│   ├── class-admin.php
│   └── views/
│
└── public/                      # Frontend forms
    ├── class-public.php
    ├── templates/
    └── assets/
```

## Core Components

### 1. Connector Interface (`interface-api-connector.php`)

All API connectors must implement `ApiConnectorInterface`:

```php
interface ApiConnectorInterface {
    // Identity
    public function get_id(): string;
    public function get_name(): string;
    public function get_description(): string;
    public function get_version(): string;

    // Configuration
    public function get_config_fields(): array;
    public function validate_config(array $config): array;
    public function test_connection(array $config): array;

    // API Operations
    public function validate_account(array $data, array $config): AccountValidationResult;
    public function submit_enrollment(array $form_data, array $config): EnrollmentResult;
    public function get_schedule_slots(array $data, array $config): SchedulingResult;
    public function book_appointment(array $data, array $config): BookingResult;

    // Field Mapping
    public function map_fields(array $form_data, string $type): array;

    // Features & Presets
    public function get_supported_features(): array;
    public function supports(string $feature): bool;
    public function get_presets(): array;
}
```

### 2. Connector Registry (`class-connector-registry.php`)

Manages connector registration and lifecycle:

```php
// Get registry instance
$registry = isf_connectors();

// Register a connector
$registry->register(new MyConnector());

// Get a connector
$connector = $registry->get('my-connector');

// Get all connectors
$all = $registry->get_all();

// Get connector options for dropdowns
$options = $registry->get_options();
```

### 3. Branding System (`class-branding.php`)

White-label configuration:

```php
// Get branding instance
$branding = isf_branding();

// Get settings
$name = $branding->get('plugin_name');
$color = $branding->get('primary_color');

// Update settings
$branding->update([
    'plugin_name' => 'My Platform',
    'primary_color' => '#FF5722',
]);
$branding->save();

// Get custom CSS
$css = $branding->get_custom_css();

// Get email header/footer HTML
$header = $branding->get_email_header();
$footer = $branding->get_email_footer();
```

### 4. Hooks System (`class-hooks.php`)

Documented extensibility points:

```php
use ISF\Hooks;

// Use hook constants
add_filter(Hooks::FORM_DATA, function($data, $instance_id) {
    // Modify form data
    return $data;
}, 10, 2);

add_action(Hooks::ENROLLMENT_COMPLETED, function($submission_id, $instance_id, $form_data) {
    // React to enrollment
}, 10, 3);

// Get all hooks
$all_hooks = Hooks::get_all();

// Get hooks by category
$form_hooks = Hooks::get_category('Forms');
```

## Creating a Custom Connector

### Step 1: Create connector directory

```
my-plugin/
└── connectors/
    └── my-api/
        ├── loader.php
        ├── class-my-api-connector.php
        └── class-my-api-client.php
```

### Step 2: Implement the interface

```php
// class-my-api-connector.php
namespace MyPlugin\Connectors\MyApi;

use ISF\Api\ApiConnectorInterface;
use ISF\Api\AccountValidationResult;
use ISF\Api\EnrollmentResult;
use ISF\Api\SchedulingResult;
use ISF\Api\BookingResult;

class MyApiConnector implements ApiConnectorInterface {

    public function get_id(): string {
        return 'my-api';
    }

    public function get_name(): string {
        return __('My Custom API', 'my-plugin');
    }

    public function get_description(): string {
        return __('Integration with My Custom API', 'my-plugin');
    }

    public function get_version(): string {
        return '1.0.0';
    }

    public function get_config_fields(): array {
        return [
            'api_url' => [
                'label' => __('API URL', 'my-plugin'),
                'type' => 'url',
                'required' => true,
            ],
            'api_key' => [
                'label' => __('API Key', 'my-plugin'),
                'type' => 'password',
                'required' => true,
                'encrypted' => true,
            ],
        ];
    }

    public function validate_config(array $config): array {
        $errors = [];
        if (empty($config['api_url'])) {
            $errors[] = __('API URL is required', 'my-plugin');
        }
        if (empty($config['api_key'])) {
            $errors[] = __('API Key is required', 'my-plugin');
        }
        return $errors;
    }

    public function test_connection(array $config): array {
        try {
            // Test API connection
            return ['success' => true, 'message' => 'Connected'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function validate_account(array $data, array $config): AccountValidationResult {
        // Implement account validation
        return new AccountValidationResult([
            'is_valid' => true,
            'customer_data' => ['name' => 'John Doe'],
        ]);
    }

    public function submit_enrollment(array $form_data, array $config): EnrollmentResult {
        // Implement enrollment submission
        return new EnrollmentResult([
            'success' => true,
            'confirmation_number' => 'ABC123',
        ]);
    }

    public function get_schedule_slots(array $data, array $config): SchedulingResult {
        // Implement scheduling
        return new SchedulingResult([
            'success' => true,
            'slots' => [],
        ]);
    }

    public function book_appointment(array $data, array $config): BookingResult {
        // Implement booking
        return new BookingResult([
            'success' => true,
        ]);
    }

    public function map_fields(array $form_data, string $type = 'enrollment'): array {
        // Map internal field names to API parameter names
        return $form_data;
    }

    public function get_supported_features(): array {
        return ['account_validation', 'enrollment'];
    }

    public function supports(string $feature): bool {
        return in_array($feature, $this->get_supported_features());
    }

    public function get_presets(): array {
        return []; // Or return preset configurations
    }
}
```

### Step 3: Create the loader

```php
// loader.php
namespace MyPlugin\Connectors\MyApi;

if (!defined('ABSPATH')) exit;

function register_connector($registry): void {
    require_once __DIR__ . '/class-my-api-connector.php';
    $registry->register(new MyApiConnector());
}

add_action('isf_register_connectors', __NAMESPACE__ . '\\register_connector');
```

### Step 4: Register from your plugin

```php
// In your main plugin file
add_action('isf_connectors_loaded', function() {
    require_once __DIR__ . '/connectors/my-api/loader.php';
});
```

## White-Label Configuration

### Via Filter (code-based)

```php
add_filter('isf_branding_settings', function($settings) {
    return array_merge($settings, [
        // Identity
        'plugin_name' => 'Acme Enrollments',
        'plugin_short_name' => 'Acme',
        'plugin_description' => 'Enrollment platform for Acme Corp',

        // Company
        'company_name' => 'Acme Corporation',
        'company_url' => 'https://acme.com',
        'support_email' => 'support@acme.com',

        // Visual
        'primary_color' => '#E91E63',
        'secondary_color' => '#9C27B0',
        'logo_url' => 'https://acme.com/logo.png',
        'menu_icon' => 'dashicons-clipboard',

        // Attribution
        'show_powered_by' => false,
    ]);
});
```

### Via Admin Settings

Navigate to **FormFlow > Settings > Branding** to configure:
- Plugin name and description
- Company information
- Colors and logos
- Email branding
- Attribution text

## Data Flow

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   Frontend      │     │   Core          │     │   Connector     │
│   Form          │────▶│   Platform      │────▶│   (IntelliSrc)  │
└─────────────────┘     └─────────────────┘     └─────────────────┘
        │                       │                       │
        │  Form Data            │  Mapped Data          │  API Call
        │                       │                       │
        ▼                       ▼                       ▼
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   Validation    │     │   Feature       │     │   External      │
│   & Security    │     │   Processing    │     │   API           │
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

1. **Frontend Form** collects user input
2. **Core Platform** validates, applies features (fraud detection, etc.)
3. **Connector** maps fields and communicates with external API
4. **Response** flows back through the chain

## Backward Compatibility

The IntelliSource connector is bundled and auto-registered, ensuring existing installations continue to work without changes. The `Utilities` class and existing API classes are still available but deprecated in favor of the connector system.

## Best Practices

1. **Use hook constants**: `Hooks::ENROLLMENT_COMPLETED` instead of string literals
2. **Implement all interface methods**: Even if returning empty results
3. **Validate configuration**: Return meaningful error messages
4. **Log appropriately**: Use the database logging system
5. **Handle errors gracefully**: Return result objects with error details
6. **Support presets**: If your connector serves multiple accounts

## Migration Guide (1.x to 2.0)

### For Plugin Users
No action required. The bundled IntelliSource connector maintains full backward compatibility.

### For Developers
If you were extending the plugin:

1. Replace `ISF\Api\ApiClient` usage with connector interface
2. Use `isf_connectors()->get('intellisource')` to get the connector
3. Replace `ISF\Utilities` calls with connector presets
4. Update any direct API class references

The legacy classes remain available but are deprecated.
