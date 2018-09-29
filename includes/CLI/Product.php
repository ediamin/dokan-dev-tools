<?php

namespace Dokan\DevTools\CLI;

use Dokan\DevTools\Faker;
use WP_Query;
use WP_User_Query;

/**
* Dokan Product CLI Commands
*
* @since 1.0.0
*/
class Product extends CLI {

    protected $base_command = 'dokan product';

    /**
    * Class constructor
    *
    * @since 1.0.0
    */
    public function __construct() {
        $this->add_command( 'generate', 'generate' );
        $this->add_command( 'create', 'create' );
        $this->add_command( 'delete', 'delete' );
    }

    /**
     * Generate wc products randomly distributed to Dokan Vendors
     *
     * ## OPTIONS
     *
     * [--count=<no_of_products>]
     * : No of products. Default is 10.
     *
     * ## EXAMPLES
     *
     *     # Generate 10 products
     *     $ wp dokan product generate
     *
     *     # Generate 20 products
     *     $ wp dokan product generate --count=20
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function generate( $args, $assoc_args ) {
        if ( ! class_exists( '\WC\SmoothGenerator\Generator\Product' ) ) {
            $this->error( 'Please install and activate wc-smooth-generator plugin first. Get it here: https://github.com/woocommerce/wc-smooth-generator' );
        }

        $faker = Faker::get();

        $count = 10;
        $generated = 0;

        if ( ! empty( $assoc_args['count'] ) ) {
            $count = $assoc_args['count'];
        }

        $message = sprintf( 'Generating %d products', $count );

        $progress = $this->make_progress_bar( $message, $count );

        $args = [
            'role' => 'seller'
        ];

        $vendor_query = new WP_User_Query( $args );

        if ( ! $vendor_query->get_total() ) {
            $this->error( 'No vendor found' );

        } else {
            $vendors = $vendor_query->get_results();

            for ( $i = 0; $i < $count; $i++ ) {
                $vendor = $faker->randomElement( $vendors );

                $product = \WC\SmoothGenerator\Generator\Product::generate();

                $args = [
                    'ID' => $product->get_id(),
                    'post_author' => $vendor->ID,
                ];

                $updated = wp_update_post( $args, true );

                if ( $updated ) {
                    do_action( 'dokan_dev_cli_product_generated', $product, $vendor );

                    ++$generated;
                }

                $progress->tick();
            }

            $progress->finish();

            $message = sprintf( 'Generated %d products', $generated );

            $this->success( $message );
        }
    }

    /**
     * Create a single product for a given vendor id
     *
     * ## OPTIONS
     *
     * <vendor_id>
     * : Vendor id
     *
     * ## EXAMPLES
     *
     *     # Create a product for vendor id 10
     *     $ wp dokan product create 10
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function create( $args ) {
        list( $vendor_id ) = $args;

        $faker = Faker::get();

        $vendor = dokan()->vendor->get( $vendor_id );

        if ( ! $vendor->id ) {
            $this->error( 'Invalid vendor_id' );
        }

        $current_user = wp_set_current_user( $vendor_id );

        $categories = get_categories( [
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'parent' => 0,
        ] );

        $category = $faker->randomElement( $categories );

        $product_args = [
            'post_title' => $faker->sentence( 6, true ),
            'product_cat' => $category->term_id,
            '_price' => 100,
            '_regular_price' => 100,
            '_sale_price' => '',
        ];

        $product = dokan_save_product( $product_args );

        if ( is_wp_error( $product ) ) {
            $this->error( $product->get_error_message() );
        }

        $this->success( sprintf( 'Created a new product for vendor %s', $vendor->data->data->display_name ) );
    }

    /**
     * Delete all products
     *
     * ## EXAMPLES
     *
     *     # Delete all products
     *     $ wp dokan product delete
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function delete( $args, $assoc_args ) {
        $args = [
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ];

        $query = new WP_Query( $args );

        if ( empty( $query->posts ) ) {
            $this->error( 'No product found' );

        } else {
            $count = count( $query->posts );
            $deleted = 0;

            $message  = sprintf( 'Deleting %d products', $count );
            $progress = $this->make_progress_bar( $message, $count );

            foreach ( $query->posts as $post ) {
                $product = new \WC_Product( $post->ID );

                if ( $product->delete( true ) ) {
                    ++$deleted;
                }

                $progress->tick();
            }

            $progress->finish();

            $message = sprintf( 'Deleted %d products', $deleted );

            $this->success( $message );
        }
    }
}
