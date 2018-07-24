<?php

namespace Dokan\DevTools;

use Dokan\DevTools\CLI\Vendor;
use Dokan\DevTools\Traits\Hooker;

class Hooks {

	use Hooker;

	public function __construct() {
		$this->add_action( 'dokan_loaded', 'after_dokan_loaded' );
	}

	public function after_dokan_loaded() {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            new CLI\Vendor();
        }
	}
}
