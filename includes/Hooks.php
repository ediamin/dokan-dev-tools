<?php

namespace Dokan\DevTools;

use Dokan\DevTools\CLI\Dokan;
use Dokan\DevTools\CLI\FollowStore;
use Dokan\DevTools\CLI\Module;
use Dokan\DevTools\CLI\Product;
use Dokan\DevTools\CLI\Vendor;
use Dokan\DevTools\Traits\Hooker;

class Hooks {

    use Hooker;

    public function __construct() {
        $this->add_action( 'dokan_loaded', 'after_dokan_loaded' );
        $this->add_filter( 'appsero_is_local', 'is_local_server' );
    }

    public function after_dokan_loaded() {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            new Dokan();
            new Vendor();
            new Product();
            new Module();
            new FollowStore();
        }
    }

    public function is_local_server( $is_local ) {
        return defined('DOKAN_IS_LOCAL_SERVER') ? DOKAN_IS_LOCAL_SERVER : $is_local;
    }
}
