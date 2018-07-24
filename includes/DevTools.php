<?php

namespace Dokan\DevTools;

/**
 * The main plugin class
 *
 * @since 1.0.0
 */
final class DevTools {

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * Holds the class instance
     *
     * @var object
     *
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Initializes the DevTools
     *
     * Insures that only one instance of `DevTools` exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 1.0.0
     *
     * @return object
     */
    public static function init() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof DevTools ) ) {
            self::$instance = new DevTools();
            self::$instance->boot();
        }

        return self::$instance;
    }

    /**
     * Setup the plugin
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function boot() {
        $this->define_constants();
        $this->includes();
        $this->class_instances();
    }

    /**
     * Define plugin constants
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_DEVTOOLS_VERSION', $this->version );
        define( 'DOKAN_DEVTOOLS_INCLUDES', DOKAN_DEVTOOLS_PATH . '/includes' );
        define( 'DOKAN_DEVTOOLS_URL', plugins_url( '', DOKAN_DEVTOOLS_FILE ) );
        define( 'DOKAN_DEVTOOLS_ASSETS', DOKAN_DEVTOOLS_URL . '/assets' );
        define( 'DOKAN_DEVTOOLS_VIEWS', DOKAN_DEVTOOLS_PATH . '/views' );
    }

    /**
     * Include plugin files
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function includes() {
        include_once DOKAN_DEVTOOLS_INCLUDES . '/functions.php';
    }

    /**
     * Make plugin related class instances
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function class_instances() {
    	new Hooks();
    }
}
