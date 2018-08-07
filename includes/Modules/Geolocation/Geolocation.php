<?php

namespace Dokan\DevTools\Modules\Geolocation;

use Dokan\DevTools\Faker;
use Dokan\DevTools\Traits\Hooker;

class Geolocation {

    use Hooker;

    public function __construct() {
        $this->add_action( 'dokan_dev_cli_vendor_generated', 'add_vendor_geolocation_data' );
        $this->add_action( 'dokan_dev_cli_product_generated', 'add_product_geolocation_data', 10, 2 );
    }

    public function add_vendor_geolocation_data( $store_id ) {
        $faker = Faker::get();

        $lat     = $faker->latitude( 23.70, 23.90 );
        $long    = $faker->longitude( 90.25, 90.50 );
        $address = $faker->address;

        update_usermeta( $store_id, 'geo_latitude', $lat );
        update_usermeta( $store_id, 'geo_longitude', $long );
        update_usermeta( $store_id, 'geo_public', 1 );
        update_usermeta( $store_id, 'geo_address', $address );

        $profile_settings = get_user_meta( $store_id, 'dokan_profile_settings', true );

        if ( ! is_array( $profile_settings ) ) {
            $profile_settings = [];
        }

        $profile_settings['location']     = $lat . ',' . $long;
        $profile_settings['find_address'] = $address;

        update_user_meta( $store_id, 'dokan_profile_settings', $profile_settings );
    }

    public function add_product_geolocation_data( $product, $vendor ) {
        $faker = Faker::get();

        $rand_digit = $faker->randomDigit;

        $use_store_settings = ( $rand_digit % 2 === 0 ) ? 'yes' : 'no';

        $store_id      = $vendor->ID;
        $geo_latitude  = get_user_meta( $store_id, 'geo_latitude', true );
        $geo_longitude = get_user_meta( $store_id, 'geo_longitude', true );
        $geo_public    = get_user_meta( $store_id, 'geo_public', true );
        $geo_address   = get_user_meta( $store_id, 'geo_address', true );

        $post_id = $product->get_id();
        update_post_meta( $post_id, '_dokan_geolocation_use_store_settings', $use_store_settings );

        if ( 'yes' !== $use_store_settings ) {
            $geo_latitude  = $faker->latitude( 23.70, 23.90 );
            $geo_longitude = $faker->longitude( 90.25, 90.50 );
            $geo_address   = $faker->address;
        }

        update_post_meta( $post_id, 'geo_latitude', $geo_latitude );
        update_post_meta( $post_id, 'geo_longitude', $geo_longitude );
        update_post_meta( $post_id, 'geo_public', $geo_public );
        update_post_meta( $post_id, 'geo_address', $geo_address );
    }
}
