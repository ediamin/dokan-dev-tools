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
        $this->add_command( 'delete', 'delete' );
    }

    public function generate( $args, $assoc_args ) {
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
                    do_action( 'dokan_dev_cli_product_generated', $product );

                    ++$generated;
                }

                $progress->tick();
            }

            $progress->finish();

            $message = sprintf( 'Generated %d products', $generated );

            $this->success( $message );
        }
    }

    public function delete( $args, $assoc_args ) {
        $args = [
            'post_type' => 'product',
            'number' => -1,
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
