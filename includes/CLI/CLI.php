<?php

namespace Dokan\DevTools\CLI;

/**
 * Base CLI class contains wrapper APIs
 *
 * @since 1.0.0
 */
abstract class CLI extends \WP_CLI_Command {

	protected $base_command;

	/**
	 * Register a command to WP-CLI
	 *
	 * @since 1.0.0
	 *
	 * @param string $name     Command excluding initial `base`. For 'wp base reset', name should be `reset`
	 * @param string $callable The callable hook method
	 * @param array  $args     See documentation for `WP_CLI::add_command` $args param
	 *
	 * @return void
	 */
	protected function add_command( $name, $callable, $args = [] ) {
		\WP_CLI::add_command( $this->base_command . ' ' . $name, [ $this, $callable ], $args );
	}

	/**
	 * Wrapper for CLI colorize api
	 *
	 * @since 1.0.0
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	protected function info( $message ) {
		echo \WP_CLI::colorize( "%c{$message}%n\n" );
	}

	/**
	 * Wrapper for CLI log api
	 *
	 * @since 1.0.0
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	protected function log( $message ) {
		\WP_CLI::log( $message );
	}

	/**
	 * Wrapper for CLI error api
	 *
	 * @since 1.0.0
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	protected function error( $message ) {
		\WP_CLI::error( $message );
	}

	/**
	 * Wrapper for CLI success api
	 *
	 * @since 1.0.0
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	protected function success( $message ) {
		\WP_CLI::success( $message );
	}

	/**
	 * Wrapper for CLI warning api
	 *
	 * @since 1.0.0
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	protected function warning( $message ) {
		echo \WP_CLI::colorize( "%YWarning:%n {$message}\n" );
	}

	/**
	 * Wrapper for CLI runcommand api
	 *
	 * @since 1.0.0
	 *
	 * @param string $command wp cli command without initial `wp` word
	 * @param array  $options  Configuration options for command execution.
	 *
	 * @return void
	 */
	protected function run( $command, $options = [] ) {
		\WP_CLI::runcommand( $command, $options );
	}

	/**
	 * Wrapper for make_progress_bar utility api
	 *
	 * @since 1.0.0
	 *
	 * @param string  $message Text to display before the progress bar.
	 * @param integer $count   Total number of ticks to be performed.
	 *
	 * @return object cli\progress\Bar|WP_CLI\NoOp
	 */
	protected function make_progress_bar( $message, $count ) {
		return \WP_CLI\Utils\make_progress_bar( $message, $count );
	}

	/**
	 * A prompt method to confirm an action
	 *
	 * @since 1.0.0
	 *
	 * @param string $message
	 * @param array  $assoc_args
	 *
	 * @return boolean
	 */
	protected function confirm( $message ) {
		echo \WP_CLI::colorize( "%YWarning:%n {$message} [y/n] " );

		$input = fgets( STDIN );
		$input = strtolower( trim( $input ) );

		if ( ! in_array( $input , [ 'y', 'n' ] ) ) {
			echo \WP_CLI::colorize( "%RError:%n Type 'y' or 'n' and then press enter\n" );
			return $this->confirm( $message );
		}

		return ( 'y' === $input ) ? true : false;
	}
}
