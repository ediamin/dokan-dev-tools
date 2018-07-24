<?php

namespace Dokan\DevTools\CLI;

/**
* Dokan Vendor CLI Commands
*
* @since 1.0.0
*/
class Vendor extends CLI {

	protected $base_command = 'dokan vendor';

	/**
	* Class constructor
	*
	* @since 1.0.0
	*/
	public function __construct() {
		$this->add_command( 'generate', 'generate' );
	}

	public function generate() {
        $count = 10;

        if ( ! empty( $assoc_args['count'] ) ) {
            $count = $assoc_args['count'];
        }

        $message = sprintf( 'Generating %d posts', $count );
        $progress = $this->make_progress_bar( $message, $count );

        for ( $i = 0; $i < $count; $i++ ) {
            // generate post


            $progress->tick();
        }

        $progress->finish();

        $message = sprintf( 'Created %d posts', $count );

        $this->success( $message );
	}
}
