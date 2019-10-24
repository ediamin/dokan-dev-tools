<?php

namespace Dokan\DevTools\CLI;

/**
* Dokan Module CLI Commands
*
* @since 1.0.0
*/
class Module extends CLI {

    protected $base_command = 'dokan module';

    /**
    * Class constructor
    *
    * @since 1.0.0
    */
    public function __construct() {
        $this->add_command( 'activate', 'activate' );
        $this->add_command( 'deactivate', 'deactivate' );
        $this->add_command( 'toggle', 'toggle' );
        $this->add_command( 'list', 'moduleList' );
    }

    /**
     * Get module file
     *
     * @since 1.0.0
     *
     * @param string $module
     *
     * @return string
     */
    private function get_module_file( $module ) {
        $module_file = $module . '/' . $module . '.php';

        if ( ! file_exists( DOKAN_PRO_INC . '/modules/' . $module_file ) ) {
            $this->error( 'Module file not exists' );
        }

        return $module_file;
    }

    /**
     * Activate a Dokan module
     *
     * ## OPTIONS
     *
     * [<module_name>]
     * : Module directory name
     *
     * ## EXAMPLES
     *
     *     # Activate a module
     *     $ wp dokan module activate geolocation
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function activate( $args ) {
        if ( empty( $args ) ) {
            $this->error( 'Module name is missing!' );
        }

        list( $module ) = $args;

        $module_file = $this->get_module_file( $module );

        if ( dokan_pro_is_module_active( $module_file ) ) {
            $this->warning( sprintf( "Module '%s' is already active", $module ) );
            $this->success( 'Module already activated' );
            exit;
        }

        $activate = dokan_pro_activate_module( $module_file );

        if ( is_wp_error( $activate ) ) {
            $this->error( $activate->get_error_message() );
        }

        $this->success( sprintf( "Module '%s' activated", $module ) );
    }

    /**
     * Dectivate a Dokan module
     *
     * ## OPTIONS
     *
     * [<module_name>]
     * : Module directory name
     *
     * ## EXAMPLES
     *
     *     # Dectivate a module
     *     $ wp dokan module deactivate geolocation
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function deactivate( $args ) {
        if ( empty( $args ) ) {
            $this->error( 'Module name is missing!' );
        }

        list( $module ) = $args;

        $module_file = $this->get_module_file( $module );

        if ( dokan_pro_is_module_inactive( $module_file ) ) {
            $this->warning( sprintf( "Module '%s' isn't active", $module ) );
            $this->success( 'Module already deactivated' );
            exit;
        }

        $deactivate = dokan_pro_deactivate_module( $module_file );

        if ( is_wp_error( $deactivate ) ) {
            $this->error( $deactivate->get_error_message() );
        }

        $this->success( sprintf( "Module '%s' deactivated", $module ) );
    }

    /**
     * Toggles a module's activation state
     *
     * ## OPTIONS
     *
     * [<module_name>]
     * : Module directory name
     *
     * ## EXAMPLES
     *
     *     # Geolocation is currently activated
     *     $ wp dokan module toggle gelocation
     *     Module 'geolocation' deactivated.
     *
     *     # Geolocation is currently deactivated
     *     $ wp dokan module toggle gelocation
     *     Module 'geolocation' activated.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function toggle( $args ) {
        if ( empty( $args ) ) {
            $this->error( 'Module name is missing!' );
        }

        list( $module ) = $args;

        $module_file = $this->get_module_file( $module );

        if ( dokan_pro_is_module_active( $module_file ) ) {
            $toggle = dokan_pro_deactivate_module( $module_file );
            $message = "Module '%s' deactivated";
        } else {
            $toggle = dokan_pro_activate_module( $module_file );
            $message = "Module '%s' activated";
        }

        if ( is_wp_error( $toggle ) ) {
            $this->error( $toggle->get_error_message() );
        }

        $this->success( sprintf( $message, $module ) );
    }

    /**
     * Gets a list of Dokan modules
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function moduleList() {
        $modules = dokan_pro_get_modules();
        $actives = dokan_pro_get_active_modules();

        if ( empty( $modules ) ) {
            $this->error( 'No module found' );
        }

        $list = [];

        foreach ( $modules as $module_file => $module_data ) {
            $module = explode( '/', $module_file );

            $list[] = [
                'Name'    => $module[0],
                'Title'   => $module_data['name'],
                'Version' => $module_data['version'],
                'Status'  => in_array( $module_file, $actives ) ? 'active' : 'inactive',
            ];
        }

        \WP_CLI\Utils\format_items( 'table', $list, array( 'Name', 'Title', 'Version', 'Status' ) );
    }
}
