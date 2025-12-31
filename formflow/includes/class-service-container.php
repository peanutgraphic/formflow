<?php
/**
 * Service Container for Dependency Injection
 *
 * Provides a centralized service registry that enables loose coupling
 * between components. Services can be registered with custom factories,
 * allowing different implementations for different use cases.
 *
 * @package FormFlow
 * @since 2.6.0
 */

namespace ISF;

defined('ABSPATH') || exit;

/**
 * Class ServiceContainer
 *
 * A lightweight dependency injection container that allows:
 * - Service registration with factory functions
 * - Lazy loading of services
 * - Service overriding for testing or customization
 * - Health status monitoring
 */
class ServiceContainer {

    /**
     * Singleton instance
     */
    private static ?ServiceContainer $instance = null;

    /**
     * Registered service factories
     */
    private array $factories = [];

    /**
     * Instantiated service instances
     */
    private array $instances = [];

    /**
     * Service aliases (alternative names for services)
     */
    private array $aliases = [];

    /**
     * Service initialization status
     */
    private array $initialized = [];

    /**
     * Get the singleton container instance
     */
    public static function instance(): ServiceContainer {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor for singleton
     */
    private function __construct() {
        $this->register_default_services();
    }

    /**
     * Register default FormFlow services
     */
    private function register_default_services(): void {
        // Address Validator Service
        $this->register('address_validator', function() {
            return \ISF\Services\AddressValidator::instance();
        });

        // Geocoding Service
        $this->register('geocoding', function() {
            return \ISF\Services\GeocodingService::instance();
        });

        // Form Builder
        $this->register('form_builder', function() {
            return \ISF\Builder\FormBuilder::instance();
        });

        // Form Renderer
        $this->register('form_renderer', function() {
            return new \ISF\Builder\FormRenderer();
        });

        // Conditional Logic Engine
        $this->register('conditional_logic', function() {
            return new \ISF\Builder\ConditionalLogic();
        });

        // Program Manager
        $this->register('program_manager', function() {
            return \ISF\Programs\ProgramManager::instance();
        });

        // Cross-Sell Engine
        $this->register('cross_sell', function() {
            return \ISF\Programs\CrossSellEngine::instance();
        });

        // Appointment Bundler
        $this->register('appointment_bundler', function() {
            return \ISF\Programs\AppointmentBundler::instance();
        });

        /**
         * Filter to register additional services
         *
         * Allows third-party code to add services to the container.
         *
         * @param ServiceContainer $container The service container
         */
        do_action('isf_register_services', $this);
    }

    /**
     * Register a service factory
     *
     * @param string   $name    Service name
     * @param callable $factory Factory function that returns the service
     * @param bool     $force   Whether to override existing registration
     */
    public function register(string $name, callable $factory, bool $force = false): void {
        if (isset($this->factories[$name]) && !$force) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[FormFlow ServiceContainer] Service '{$name}' already registered. Use force=true to override.");
            }
            return;
        }

        $this->factories[$name] = $factory;

        // Clear cached instance if overriding
        if ($force && isset($this->instances[$name])) {
            unset($this->instances[$name]);
            unset($this->initialized[$name]);
        }
    }

    /**
     * Register an alias for a service
     *
     * @param string $alias   The alias name
     * @param string $service The real service name
     */
    public function alias(string $alias, string $service): void {
        $this->aliases[$alias] = $service;
    }

    /**
     * Get a service by name
     *
     * @param string $name Service name
     * @return mixed The service instance
     * @throws \InvalidArgumentException If service is not registered
     */
    public function get(string $name) {
        // Resolve alias
        $resolved_name = $this->aliases[$name] ?? $name;

        // Return cached instance if available
        if (isset($this->instances[$resolved_name])) {
            return $this->instances[$resolved_name];
        }

        // Check if factory exists
        if (!isset($this->factories[$resolved_name])) {
            throw new \InvalidArgumentException(
                sprintf('Service "%s" is not registered in the container.', $name)
            );
        }

        // Create and cache instance
        try {
            $this->instances[$resolved_name] = call_user_func($this->factories[$resolved_name], $this);
            $this->initialized[$resolved_name] = true;

            /**
             * Fires when a service is instantiated
             *
             * @param string $name     Service name
             * @param mixed  $instance The service instance
             */
            do_action('isf_service_created', $resolved_name, $this->instances[$resolved_name]);

            return $this->instances[$resolved_name];

        } catch (\Throwable $e) {
            $this->initialized[$resolved_name] = false;

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(sprintf(
                    '[FormFlow ServiceContainer] Failed to create service "%s": %s',
                    $resolved_name,
                    $e->getMessage()
                ));
            }

            /**
             * Fires when service creation fails
             *
             * @param string     $name      Service name
             * @param \Throwable $exception The exception that occurred
             */
            do_action('isf_service_creation_failed', $resolved_name, $e);

            throw $e;
        }
    }

    /**
     * Check if a service is registered
     *
     * @param string $name Service name
     * @return bool
     */
    public function has(string $name): bool {
        $resolved_name = $this->aliases[$name] ?? $name;
        return isset($this->factories[$resolved_name]);
    }

    /**
     * Check if a service has been instantiated
     *
     * @param string $name Service name
     * @return bool
     */
    public function isInstantiated(string $name): bool {
        $resolved_name = $this->aliases[$name] ?? $name;
        return isset($this->instances[$resolved_name]);
    }

    /**
     * Set a pre-instantiated service (for testing or custom implementations)
     *
     * @param string $name     Service name
     * @param mixed  $instance The service instance
     */
    public function set(string $name, $instance): void {
        $this->instances[$name] = $instance;
        $this->initialized[$name] = true;

        // Remove factory if setting instance directly
        if (isset($this->factories[$name])) {
            unset($this->factories[$name]);
        }
    }

    /**
     * Remove a service (useful for testing)
     *
     * @param string $name Service name
     */
    public function remove(string $name): void {
        unset($this->factories[$name]);
        unset($this->instances[$name]);
        unset($this->initialized[$name]);
    }

    /**
     * Get list of all registered services
     *
     * @return array Service names
     */
    public function getRegisteredServices(): array {
        return array_keys($this->factories);
    }

    /**
     * Get health status of all services
     *
     * @return array Health status for each service
     */
    public function getHealthStatus(): array {
        $status = [];

        foreach ($this->factories as $name => $factory) {
            $service_status = [
                'registered' => true,
                'instantiated' => isset($this->instances[$name]),
                'initialized' => $this->initialized[$name] ?? false,
                'healthy' => true,
            ];

            // Check if service has its own health check
            if (isset($this->instances[$name])) {
                $instance = $this->instances[$name];
                if (method_exists($instance, 'get_health_status')) {
                    try {
                        $service_status['service_health'] = $instance->get_health_status();
                    } catch (\Throwable $e) {
                        $service_status['healthy'] = false;
                        $service_status['error'] = $e->getMessage();
                    }
                }
            }

            $status[$name] = $service_status;
        }

        return $status;
    }

    /**
     * Reset the container (primarily for testing)
     */
    public function reset(): void {
        $this->instances = [];
        $this->initialized = [];
        // Keep factories registered
    }

    /**
     * Magic getter for convenient access
     *
     * @param string $name Property name (service name)
     * @return mixed
     */
    public function __get(string $name) {
        return $this->get($name);
    }
}

/**
 * Helper function to get the service container
 *
 * @return ServiceContainer
 */
function container(): ServiceContainer {
    return ServiceContainer::instance();
}

/**
 * Helper function to get a service from the container
 *
 * @param string $name Service name
 * @return mixed The service instance
 */
function service(string $name) {
    return ServiceContainer::instance()->get($name);
}
