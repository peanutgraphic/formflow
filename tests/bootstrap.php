<?php
/**
 * PHPUnit Bootstrap File
 *
 * Sets up the testing environment for FormFlow plugin.
 */

// Composer autoloader
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
    die("Please run 'composer install' before running tests.\n");
}
require_once $autoloader;

// Load Brain Monkey for mocking WordPress functions
require_once dirname(__DIR__) . '/vendor/brain/monkey/inc/patchwork-loader.php';

// Define plugin constants for testing
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}

if (!defined('ISF_PLUGIN_DIR')) {
    define('ISF_PLUGIN_DIR', dirname(__DIR__) . '/');
}

if (!defined('ISF_PLUGIN_URL')) {
    define('ISF_PLUGIN_URL', 'http://example.com/wp-content/plugins/intellisource-forms/');
}

if (!defined('ISF_VERSION')) {
    define('ISF_VERSION', '2.3.0');
}

// Define table constants
if (!defined('ISF_TABLE_INSTANCES')) {
    define('ISF_TABLE_INSTANCES', 'isf_instances');
}
if (!defined('ISF_TABLE_SUBMISSIONS')) {
    define('ISF_TABLE_SUBMISSIONS', 'isf_submissions');
}
if (!defined('ISF_TABLE_LOGS')) {
    define('ISF_TABLE_LOGS', 'isf_logs');
}
if (!defined('ISF_TABLE_ANALYTICS')) {
    define('ISF_TABLE_ANALYTICS', 'isf_analytics');
}
if (!defined('ISF_TABLE_VISITORS')) {
    define('ISF_TABLE_VISITORS', 'isf_visitors');
}
if (!defined('ISF_TABLE_TOUCHES')) {
    define('ISF_TABLE_TOUCHES', 'isf_touches');
}
if (!defined('ISF_TABLE_HANDOFFS')) {
    define('ISF_TABLE_HANDOFFS', 'isf_handoffs');
}
if (!defined('ISF_TABLE_EXTERNAL_COMPLETIONS')) {
    define('ISF_TABLE_EXTERNAL_COMPLETIONS', 'isf_external_completions');
}

// WordPress constants
if (!defined('DAY_IN_SECONDS')) {
    define('DAY_IN_SECONDS', 86400);
}
if (!defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 3600);
}
if (!defined('COOKIEPATH')) {
    define('COOKIEPATH', '/');
}
if (!defined('COOKIE_DOMAIN')) {
    define('COOKIE_DOMAIN', '');
}
