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
        $this->add_command( 'list', 'module_list' );
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

        list( $module_id ) = $args;

        if ( dokan_pro()->module->is_active( $module_id ) ) {
            $this->warning( sprintf( "Module '%s' is already active", $module_id ) );
            $this->success( 'Module already activated' );
            exit;
        }

        dokan_pro()->module->activate_modules( [ $module_id ] );

        $this->success( sprintf( "Module '%s' activated", $module_id ) );
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

        list( $module_id ) = $args;

        if ( ! dokan_pro()->module->is_active( $module_id ) ) {
            $this->warning( sprintf( "Module '%s' isn't active", $module_id ) );
            $this->success( 'Module already deactivated' );
            exit;
        }

        $deactivate = dokan_pro()->module->deactivate_modules( [ $module_id ] );

        $this->success( sprintf( "Module '%s' deactivated", $module_id ) );
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

        list( $module_id ) = $args;

        if ( dokan_pro()->module->is_active( $module_id ) ) {
            $toggle = dokan_pro()->module->deactivate_modules( [ $module_id ] );
            $message = "Module '%s' deactivated";
        } else {
            $toggle = dokan_pro()->module->activate_modules( [ $module_id ] );
            $message = "Module '%s' activated";
        }

        $this->success( sprintf( $message, $module_id ) );
    }

    /**
     * Gets a list of Dokan modules
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function module_list() {
        $modules = dokan_pro()->module->get_all_modules();
        $actives = dokan_pro()->module->get_active_modules();

        if ( empty( $modules ) ) {
            $this->error( 'No module found' );
        }

        $list = [];

        foreach ( $modules as $module_file => $module_data ) {
            $module = explode( '/', $module_file );

            $list[] = [
                'ID'     => $module[0],
                'Name'   => $module_data['name'],
                'Status' => in_array( $module_file, $actives ) ? 'active' : 'inactive',
            ];
        }

        \WP_CLI\Utils\format_items( 'table', $list, array( 'ID', 'Name', 'Status' ) );
    }
}
