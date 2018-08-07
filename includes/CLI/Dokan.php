<?php

namespace Dokan\DevTools\CLI;

/**
* Dokan core related commands
*
* @since 1.0.0
*/
class Dokan extends CLI {

    protected $base_command = 'dokan';

    /**
    * Class constructor
    *
    * @since 1.0.0
    */
    public function __construct() {
        $this->add_command( 'cli commands', 'cli_commands' );
    }

    /**
     * List all available Dokan CLI commands
     *
     * ## EXAMPLES
     *
     *     # Typical command
     *     $ wp dokan cli commands
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function cli_commands() {
        $wp_commands = \WP_CLI::get_root_command();

        foreach ( $wp_commands->get_subcommands() as $wp_subcommand ) {
            if ( 'dokan' === $wp_subcommand->get_name() ) {
                $dokan_commands = $wp_subcommand;
                break;
            }
        }

        $list = [];

        foreach ( $dokan_commands->get_subcommands() as $command ) {
            $command_name = $command->get_name();

            foreach ( $command->get_subcommands() as $subcommand ) {
                $subcommand_name = $subcommand->get_name();

                $list[] = [
                    'Name'      => 'wp dokan ' . $command_name . ' ' . $subcommand_name,
                    'Options'   => $subcommand->get_synopsis()
                ];
            }
        }

        \WP_CLI\Utils\format_items( 'table', $list, array( 'Name', 'Options' ) );
    }
}
