<?php

namespace Dokan\DevTools\Modules\Geolocation;

use Dokan\DevTools\Faker;
use Dokan\DevTools\Traits\Hooker;

class Geolocation {

    use Hooker;

    public function __construct() {
        $this->add_action( 'dokan_dev_cli_vendor_generated', 'add_vendor_geolocation_data' );
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

        $profile_settings = [
            'location'     => $lat . ',' . $long,
            'find_address' => $address,
        ];

        update_user_meta( $store_id, 'dokan_profile_settings', $profile_settings );
    }
}
