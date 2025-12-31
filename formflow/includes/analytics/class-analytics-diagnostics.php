<?php
/**
 * Analytics Diagnostics
 *
 * Health checks and diagnostics for the analytics module.
 * Verifies that all analytics components are properly configured and working.
 */

namespace ISF\Analytics;

if (!defined('ABSPATH')) {
    exit;
}

class AnalyticsDiagnostics {

    /**
     * Run all analytics diagnostics
     *
     * @return array Diagnostic results
     */
    public function run_all_diagnostics(): array {
        return [
            'database' => $this->check_database_tables(),
            'visitor_tracking' => $this->check_visitor_tracking(),
            'gtm_integration' => $this->check_gtm_integration(),
            'handoff_tracking' => $this->check_handoff_tracking(),
            'webhook_endpoint' => $this->check_webhook_endpoint(),
            'js_assets' => $this->check_js_assets(),
            'instance_config' => $this->check_instance_configuration(),
        ];
    }

    /**
     * Get overall health status
     *
     * @return array Status summary
     */
    public function get_health_status(): array {
        $diagnostics = $this->run_all_diagnostics();

        $total = 0;
        $passed = 0;
        $warnings = 0;
        $failures = 0;
        $issues = [];

        foreach ($diagnostics as $category => $checks) {
            foreach ($checks as $check) {
                $total++;
                if ($check['status'] === 'pass') {
                    $passed++;
                } elseif ($check['status'] === 'warning') {
                    $warnings++;
                    $issues[] = $check;
                } else {
                    $failures++;
                    $issues[] = $check;
                }
            }
        }

        $overall = 'healthy';
        if ($failures > 0) {
            $overall = 'critical';
        } elseif ($warnings > 0) {
            $overall = 'warning';
        }

        return [
            'status' => $overall,
            'total_checks' => $total,
            'passed' => $passed,
            'warnings' => $warnings,
            'failures' => $failures,
            'issues' => $issues,
            'diagnostics' => $diagnostics,
        ];
    }

    /**
     * Check database tables exist and have correct structure
     */
    private function check_database_tables(): array {
        global $wpdb;

        $results = [];
        $required_tables = [
            'wp_isf_visitors' => ['id', 'visitor_id', 'first_seen_at', 'last_seen_at'],
            'wp_isf_touches' => ['id', 'visitor_id', 'touch_type', 'utm_source', 'created_at'],
            'wp_isf_handoffs' => ['id', 'instance_id', 'handoff_token', 'status', 'created_at'],
            'wp_isf_external_completions' => ['id', 'instance_id', 'source', 'processed', 'created_at'],
        ];

        foreach ($required_tables as $table => $columns) {
            $full_table = $wpdb->prefix . str_replace('wp_', '', $table);
            $table_exists = $wpdb->get_var($wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $full_table
            )) === $full_table;

            if (!$table_exists) {
                $results[] = [
                    'name' => sprintf(__('Database table: %s', 'formflow'), $table),
                    'status' => 'fail',
                    'message' => __('Table does not exist. Try deactivating and reactivating the plugin.', 'formflow'),
                    'category' => 'database',
                ];
                continue;
            }

            // Check columns exist
            $actual_columns = $wpdb->get_col("DESCRIBE {$full_table}");
            $missing_columns = array_diff($columns, $actual_columns);

            if (!empty($missing_columns)) {
                $results[] = [
                    'name' => sprintf(__('Database table: %s', 'formflow'), $table),
                    'status' => 'warning',
                    'message' => sprintf(
                        __('Missing columns: %s. Table may need updating.', 'formflow'),
                        implode(', ', $missing_columns)
                    ),
                    'category' => 'database',
                ];
            } else {
                $results[] = [
                    'name' => sprintf(__('Database table: %s', 'formflow'), $table),
                    'status' => 'pass',
                    'message' => __('Table exists with correct structure', 'formflow'),
                    'category' => 'database',
                ];
            }
        }

        return $results;
    }

    /**
     * Check visitor tracking functionality
     */
    private function check_visitor_tracking(): array {
        $results = [];

        // Check if visitor tracker class exists and can be instantiated
        try {
            $tracker = new VisitorTracker();
            $results[] = [
                'name' => __('Visitor Tracker Class', 'formflow'),
                'status' => 'pass',
                'message' => __('Class loaded successfully', 'formflow'),
                'category' => 'visitor_tracking',
            ];
        } catch (\Exception $e) {
            $results[] = [
                'name' => __('Visitor Tracker Class', 'formflow'),
                'status' => 'fail',
                'message' => $e->getMessage(),
                'category' => 'visitor_tracking',
            ];
            return $results;
        }

        // Check if cookies can be set (by verifying the visitor_id logic)
        $visitor_id = $tracker->get_visitor_id();
        if (!empty($visitor_id)) {
            $results[] = [
                'name' => __('Visitor ID Generation', 'formflow'),
                'status' => 'pass',
                'message' => sprintf(__('Visitor ID available: %s...', 'formflow'), substr($visitor_id, 0, 8)),
                'category' => 'visitor_tracking',
            ];
        } else {
            $results[] = [
                'name' => __('Visitor ID Generation', 'formflow'),
                'status' => 'warning',
                'message' => __('No visitor ID found. This is normal for admin/CLI requests.', 'formflow'),
                'category' => 'visitor_tracking',
            ];
        }

        // Check if analytics is enabled globally
        $settings = get_option('isf_settings', []);
        $analytics_enabled = $settings['analytics_enabled'] ?? true;

        $results[] = [
            'name' => __('Analytics Enabled (Global)', 'formflow'),
            'status' => $analytics_enabled ? 'pass' : 'warning',
            'message' => $analytics_enabled
                ? __('Analytics is enabled globally', 'formflow')
                : __('Analytics is disabled. Enable in Analytics Settings.', 'formflow'),
            'category' => 'visitor_tracking',
        ];

        return $results;
    }

    /**
     * Check GTM integration
     */
    private function check_gtm_integration(): array {
        $results = [];
        $settings = get_option('isf_settings', []);

        // GTM Status
        $gtm_enabled = $settings['gtm_enabled'] ?? false;
        $gtm_container_id = $settings['gtm_container_id'] ?? '';

        if ($gtm_enabled) {
            if (!empty($gtm_container_id)) {
                // Validate container ID format
                if (preg_match('/^GTM-[A-Z0-9]+$/', $gtm_container_id)) {
                    $results[] = [
                        'name' => __('GTM Container ID', 'formflow'),
                        'status' => 'pass',
                        'message' => sprintf(__('Valid container ID: %s', 'formflow'), $gtm_container_id),
                        'category' => 'gtm_integration',
                    ];
                } else {
                    $results[] = [
                        'name' => __('GTM Container ID', 'formflow'),
                        'status' => 'fail',
                        'message' => __('Invalid container ID format. Should be GTM-XXXXXXX.', 'formflow'),
                        'category' => 'gtm_integration',
                    ];
                }
            } else {
                $results[] = [
                    'name' => __('GTM Container ID', 'formflow'),
                    'status' => 'warning',
                    'message' => __('GTM enabled but no container ID set. Events will push to dataLayer only.', 'formflow'),
                    'category' => 'gtm_integration',
                ];
            }
        } else {
            $results[] = [
                'name' => __('GTM Integration', 'formflow'),
                'status' => 'warning',
                'message' => __('GTM is not enabled. Enable in Analytics Settings for enhanced tracking.', 'formflow'),
                'category' => 'gtm_integration',
            ];
        }

        // GA4 Status
        $ga4_enabled = $settings['ga4_enabled'] ?? false;
        $ga4_measurement_id = $settings['ga4_measurement_id'] ?? '';

        if ($ga4_enabled && !empty($ga4_measurement_id)) {
            if (preg_match('/^G-[A-Z0-9]+$/', $ga4_measurement_id)) {
                $results[] = [
                    'name' => __('GA4 Measurement ID', 'formflow'),
                    'status' => 'pass',
                    'message' => sprintf(__('Valid measurement ID: %s', 'formflow'), $ga4_measurement_id),
                    'category' => 'gtm_integration',
                ];
            } else {
                $results[] = [
                    'name' => __('GA4 Measurement ID', 'formflow'),
                    'status' => 'fail',
                    'message' => __('Invalid measurement ID format. Should be G-XXXXXXXXX.', 'formflow'),
                    'category' => 'gtm_integration',
                ];
            }
        }

        // Clarity Status
        $clarity_enabled = $settings['clarity_enabled'] ?? false;
        $clarity_project_id = $settings['clarity_project_id'] ?? '';

        if ($clarity_enabled && !empty($clarity_project_id)) {
            $results[] = [
                'name' => __('Microsoft Clarity', 'formflow'),
                'status' => 'pass',
                'message' => sprintf(__('Project ID configured: %s', 'formflow'), $clarity_project_id),
                'category' => 'gtm_integration',
            ];
        }

        if (empty($results)) {
            $results[] = [
                'name' => __('External Analytics', 'formflow'),
                'status' => 'warning',
                'message' => __('No external analytics configured. Consider enabling GTM or GA4.', 'formflow'),
                'category' => 'gtm_integration',
            ];
        }

        return $results;
    }

    /**
     * Check handoff tracking
     */
    private function check_handoff_tracking(): array {
        $results = [];
        $settings = get_option('isf_settings', []);

        $handoff_enabled = $settings['handoff_tracking_enabled'] ?? true;

        $results[] = [
            'name' => __('Handoff Tracking (Global)', 'formflow'),
            'status' => $handoff_enabled ? 'pass' : 'warning',
            'message' => $handoff_enabled
                ? __('Handoff tracking is enabled', 'formflow')
                : __('Handoff tracking is disabled globally', 'formflow'),
            'category' => 'handoff_tracking',
        ];

        // Check for instances with handoff configured
        global $wpdb;
        $table = $wpdb->prefix . 'isf_instances';
        $instances = $wpdb->get_results("SELECT id, name, settings FROM {$table} WHERE is_active = 1", ARRAY_A);

        $handoff_instances = [];
        foreach ($instances as $instance) {
            $inst_settings = json_decode($instance['settings'] ?? '{}', true);
            if (!empty($inst_settings['handoff']['enabled']) && !empty($inst_settings['handoff']['destination_url'])) {
                $handoff_instances[] = $instance['name'];
            }
        }

        if (!empty($handoff_instances)) {
            $results[] = [
                'name' => __('Instances with Handoff', 'formflow'),
                'status' => 'pass',
                'message' => sprintf(
                    __('Configured on: %s', 'formflow'),
                    implode(', ', $handoff_instances)
                ),
                'category' => 'handoff_tracking',
            ];
        } else {
            $results[] = [
                'name' => __('Instances with Handoff', 'formflow'),
                'status' => 'warning',
                'message' => __('No instances have handoff tracking configured.', 'formflow'),
                'category' => 'handoff_tracking',
            ];
        }

        // Check handoff tracker class
        try {
            $tracker = new HandoffTracker();
            $results[] = [
                'name' => __('Handoff Tracker Class', 'formflow'),
                'status' => 'pass',
                'message' => __('Class loaded successfully', 'formflow'),
                'category' => 'handoff_tracking',
            ];
        } catch (\Exception $e) {
            $results[] = [
                'name' => __('Handoff Tracker Class', 'formflow'),
                'status' => 'fail',
                'message' => $e->getMessage(),
                'category' => 'handoff_tracking',
            ];
        }

        return $results;
    }

    /**
     * Check webhook endpoint availability
     */
    private function check_webhook_endpoint(): array {
        $results = [];

        // Check if REST API is available
        $rest_url = rest_url('isf/v1/completions/webhook');

        $results[] = [
            'name' => __('Webhook Endpoint URL', 'formflow'),
            'status' => 'pass',
            'message' => $rest_url,
            'category' => 'webhook_endpoint',
        ];

        // Check if REST routes are registered
        $server = rest_get_server();
        $routes = $server->get_routes();

        $webhook_route = '/isf/v1/completions/webhook';
        if (isset($routes[$webhook_route])) {
            $results[] = [
                'name' => __('Webhook Route Registration', 'formflow'),
                'status' => 'pass',
                'message' => __('Route is registered and accessible', 'formflow'),
                'category' => 'webhook_endpoint',
            ];
        } else {
            $results[] = [
                'name' => __('Webhook Route Registration', 'formflow'),
                'status' => 'fail',
                'message' => __('Route not registered. Check if CompletionReceiver is loaded.', 'formflow'),
                'category' => 'webhook_endpoint',
            ];
        }

        // Check handoff route
        $handoff_route = '/isf/v1/handoff';
        if (isset($routes[$handoff_route])) {
            $results[] = [
                'name' => __('Handoff Route Registration', 'formflow'),
                'status' => 'pass',
                'message' => __('Route is registered and accessible', 'formflow'),
                'category' => 'webhook_endpoint',
            ];
        } else {
            $results[] = [
                'name' => __('Handoff Route Registration', 'formflow'),
                'status' => 'fail',
                'message' => __('Route not registered. Check if HandoffEndpoint is loaded.', 'formflow'),
                'category' => 'webhook_endpoint',
            ];
        }

        return $results;
    }

    /**
     * Check JavaScript assets
     */
    private function check_js_assets(): array {
        $results = [];

        $js_file = ISF_PLUGIN_DIR . 'public/assets/js/analytics-integration.js';

        if (file_exists($js_file)) {
            $results[] = [
                'name' => __('Analytics JavaScript', 'formflow'),
                'status' => 'pass',
                'message' => __('File exists and is readable', 'formflow'),
                'category' => 'js_assets',
            ];

            // Check file size
            $size = filesize($js_file);
            if ($size > 100) {
                $results[] = [
                    'name' => __('JavaScript File Size', 'formflow'),
                    'status' => 'pass',
                    'message' => sprintf(__('%s bytes', 'formflow'), number_format($size)),
                    'category' => 'js_assets',
                ];
            } else {
                $results[] = [
                    'name' => __('JavaScript File Size', 'formflow'),
                    'status' => 'warning',
                    'message' => __('File appears to be empty or very small', 'formflow'),
                    'category' => 'js_assets',
                ];
            }
        } else {
            $results[] = [
                'name' => __('Analytics JavaScript', 'formflow'),
                'status' => 'fail',
                'message' => __('File not found. Check plugin installation.', 'formflow'),
                'category' => 'js_assets',
            ];
        }

        return $results;
    }

    /**
     * Check instance configuration
     */
    private function check_instance_configuration(): array {
        $results = [];

        global $wpdb;
        $table = $wpdb->prefix . 'isf_instances';
        $instances = $wpdb->get_results("SELECT id, name, settings FROM {$table} WHERE is_active = 1", ARRAY_A);

        if (empty($instances)) {
            $results[] = [
                'name' => __('Active Instances', 'formflow'),
                'status' => 'warning',
                'message' => __('No active form instances found', 'formflow'),
                'category' => 'instance_config',
            ];
            return $results;
        }

        $analytics_count = 0;
        foreach ($instances as $instance) {
            if (\ISF\FeatureManager::is_enabled($instance, 'visitor_analytics')) {
                $analytics_count++;
            }
        }

        if ($analytics_count > 0) {
            $results[] = [
                'name' => __('Instances with Visitor Analytics', 'formflow'),
                'status' => 'pass',
                'message' => sprintf(
                    __('%d of %d active instances have analytics enabled', 'formflow'),
                    $analytics_count,
                    count($instances)
                ),
                'category' => 'instance_config',
            ];
        } else {
            $results[] = [
                'name' => __('Instances with Visitor Analytics', 'formflow'),
                'status' => 'warning',
                'message' => __('No instances have Visitor Analytics enabled. Enable in instance Features tab.', 'formflow'),
                'category' => 'instance_config',
            ];
        }

        return $results;
    }

    /**
     * Get human-readable status label
     *
     * @param string $status Status code
     * @return string Translated label
     */
    public static function get_status_label(string $status): string {
        $labels = [
            'pass' => __('Pass', 'formflow'),
            'warning' => __('Warning', 'formflow'),
            'fail' => __('Fail', 'formflow'),
            'healthy' => __('Healthy', 'formflow'),
            'critical' => __('Critical', 'formflow'),
        ];

        return $labels[$status] ?? $status;
    }
}
