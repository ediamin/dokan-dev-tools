<?php

namespace Dokan\DevTools;

use Dokan\DevTools\CLI\Dokan;
use Dokan\DevTools\CLI\FollowStore;
use Dokan\DevTools\CLI\Module;
use Dokan\DevTools\CLI\Product;
use Dokan\DevTools\CLI\ReportAbuse;
use Dokan\DevTools\CLI\SPMV;
use Dokan\DevTools\CLI\Vendor;
use Dokan\DevTools\Traits\Hooker;

class Hooks {

    use Hooker;

    public function __construct() {
        $this->add_action( 'dokan_loaded', 'after_dokan_loaded' );
        $this->add_filter( 'appsero_is_local', 'is_local_server' );
        $this->add_filter( 'dokan_vendor_analytics_client_id', 'vendor_analytics_client_id' );
        $this->add_filter( 'dokan_vendor_analytics_redirect_uri', 'vendor_analytics_redirect_uri' );
        $this->add_filter( 'dokan_vendor_analytics_refresh_token_url', 'vendor_analytics_refresh_token_url' );
        $this->add_action( 'dokan_admin_menu', 'elementor_icons', 99999 );
        $this->add_action( 'woocommerce_before_order_object_save', 'before_order_object_save' );
    }

    public function after_dokan_loaded() {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            $this->add_action( 'wp_loaded', 'load_cli_commands' );
        }
    }

    public function load_cli_commands() {
        new Dokan();
        new Vendor();
        new Product();
        new Module();
        new FollowStore();
        new ReportAbuse();
        new SPMV();
    }

    public function is_local_server( $is_local ) {
        return defined('DOKAN_IS_LOCAL_SERVER') ? DOKAN_IS_LOCAL_SERVER : $is_local;
    }

    public function vendor_analytics_client_id() {
        return '202372397247-2lepfoftrkcj70f28v4j5g04kgark6fs.apps.googleusercontent.com';
    }

    public function vendor_analytics_redirect_uri() {
        return 'https://198ea01c.ngrok.io/vendor-analytics/redirect';
    }

    public function vendor_analytics_refresh_token_url() {
        return 'https://198ea01c.ngrok.io/vendor-analytics/refresh-token';
    }

    public function elementor_icons() {
        add_submenu_page( 'dokan', __( 'Elementor Icons', 'dokan' ), __( 'Elementor Icons', 'dokan' ), 'manage_options', 'dokan-elementor-icons', function () {
            wp_enqueue_style( 'dokan-style' );
            include_once DOKAN_DEVTOOLS_VIEWS . '/elementor-icons.php';
        } );
    }

    public function before_order_object_save( $order ) {
        if ( defined( 'WP_CLI' ) && WP_CLI && ! $order->get_id() ) {
            $this->add_action( 'woocommerce_order_status_' . $order->get_status(), 'maybe_split_orders' );

            $vendors = dokan_get_sellers_by( $order );

            if ( count( $vendors ) > 1 ) {
                $order->update_meta_data( 'has_sub_order', true );
            }
        }
    }

    public function maybe_split_orders( $order_id ) {
        dokan()->orders->maybe_split_orders( $order_id );
    }
}
