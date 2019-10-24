<?php

namespace Dokan\DevTools\CLI;

use Dokan\DevTools\Faker;

/**
* Dokan Follow Store CLI Commands
*
* @since 1.0.0
*/
class SPMV extends CLI {

    protected $base_command = 'dokan spmv';

    /**
    * Class constructor
    *
    * @since 1.0.0
    */
    public function __construct() {
        $this->add_command( 'generate', 'generate' );
        $this->add_command( 'remove-duplicates', 'remove_duplicates' );
    }

    private function is_module_active() {
        if ( dokan_pro_is_module_active( 'single-product-multiple-vendor/single-product-multiple-vendor.php' ) ) {
            return true;
        }

        $this->error( 'Single Product Multiple Vendor module is not active.' );
    }

    /**
     * Generate duplicate products from existing products
     *
     * ## EXAMPLES
     *
     *     # Randomize data
     *     $ wp dokan spmv generate
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function generate() {
        $this->is_module_active();

        $products = wc_get_products( [
            'parent' => 0,
            'limit'  => -1,
        ] );

        $faker = Faker::get();

        // Take 15% of existing products
        $random_products = $faker->randomElements( $products, absint( ceil( .15 * count( $products ) ) ) );

        if ( empty( $random_products ) ) {
            $this->error( 'No product found to randomize data.' );
        }

        // Iterate over these random products, duplicate and assigned them to random vendors
        $count    = count( $random_products );
        $message  = sprintf( 'Radomizing duplicate data for %d products', $count );
        $progress = $this->make_progress_bar( $message, $count );

        $vendors    = dokan()->vendor->all( [ 'number' => -1 ] );
        $duplicator = \Dokan_SPMV_Product_Duplicator::instance();

        // add_filter( 'dokan_cloned_product_status', function () {
        //     return 'publish';
        // } );

        foreach ( $random_products as $product ) {
            $product_vendor = dokan_get_vendor_by_product( $product->get_id() );
            $other_vendors  = array_filter( $vendors, function ( $other_vendor ) use ( $product_vendor ) {
                return $other_vendor->get_id() !== $product_vendor->get_id();
            } );

            // Take maximum 3 vendors and assigned them duplicated product
            $random_vendors = $faker->randomElements( $other_vendors, $faker->numberBetween( 1, 3 ) );

            foreach ( $random_vendors as $vendor ) {
                $product_id = $duplicator->clone_product( $product->get_id(), $vendor->get_id() );
                $product = wc_get_product( $product_id );
                $name = $product->get_name();
                $pattern = '/^#(\d+)/';

                if ( preg_match( $pattern , $name, $matches ) ) {
                    $no = absint( $matches[1] );
                    $new_no = '#' . ++$no . ' ';

                    $name = preg_replace( $pattern, $new_no, $name );
                } else {
                    $name = '#1 ' . $name;
                }

                $product->set_name( $name );
                $product->save();
            }

            $progress->tick();
        }

        $progress->finish();

        $this->success( 'Randomized SPMV data successfully.' );
    }

    public function remove_duplicates() {
        global $wpdb;

        $product_maps = $wpdb->get_results( "select * from {$wpdb->prefix}dokan_product_map order by product_id asc" );

        if ( empty( $product_maps ) ) {
            $this->error( 'No duplicate product found.' );
        }

        $maps = [];

        $count    = count( $product_maps );
        $message  = sprintf( 'Removing SPMV duplicate for %d products', $count );
        $progress = $this->make_progress_bar( $message, $count );

        foreach ( $product_maps as $product_map ) {
            $maps[ $product_map->map_id ][] = $product_map->product_id;
        }

        foreach ( $maps as $map ) {
            unset( $map[0] );
            $progress->tick();

            if ( ! empty( $map ) ) {
                foreach ( $map as $product_id ) {
                    wp_delete_post( $product_id, true );
                    $progress->tick();
                }
            }
        }

        $progress->finish();

        $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}dokan_product_map" );

        $this->success( 'Removed SPMV random data successfully.' );
    }
}
