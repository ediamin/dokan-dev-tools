<?php

namespace Dokan\DevTools\CLI;

use Dokan\DevTools\Faker;
use WP_User_Query;

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
        $this->add_command( 'delete', 'delete' );
    }

    public function generate( $args, $assoc_args ) {
        $faker = Faker::get();

        $count = 10;
        $generated = 0;

        if ( ! empty( $assoc_args['count'] ) ) {
            $count = $assoc_args['count'];
        }

        $message = sprintf( 'Generating %d vendors', $count );

        $progress = $this->make_progress_bar( $message, $count );

        for ( $i = 0; $i < $count; $i++ ) {
            $email = $faker->safeEmail;

            $userdata = [
                'user_login' => $email,
                'user_email' => $email,
                'user_pass'  => ',lpmkonji',
                'first_name' => $faker->firstName,
                'last_name'  => $faker->lastName,
                'role'       => 'seller'
            ];

            $vendor = wp_insert_user( $userdata );

            if ( ! is_wp_error( $vendor ) ) {
                ++$generated;
            }

            $progress->tick();
        }

        $progress->finish();

        $message = sprintf( 'Generated %d vendors', $generated );

        $this->success( $message );
    }

    public function delete( $args, $assoc_args ) {
        $args = [
            'role' => 'seller'
        ];

        $vendors = new WP_User_Query( $args );

        if ( ! $vendors->get_total() ) {
            $this->error( 'No vendor found' );

        } else {
            $count = $vendors->get_total();
            $deleted = 0;

            $message  = sprintf( 'Deleting %d vendors', $count );
            $progress = $this->make_progress_bar( $message, $count );

            $results = $vendors->get_results();

            foreach ( $results as $vendor ) {
                if ( wp_delete_user( $vendor->ID ) ) {
                    ++$deleted;
                }

                $progress->tick();
            }

            $progress->finish();

            $message = sprintf( 'Deleted %d vendors', $deleted );

            $this->success( $message );
        }
    }
}
