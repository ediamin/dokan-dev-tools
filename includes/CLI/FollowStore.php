<?php

namespace Dokan\DevTools\CLI;

use Dokan\DevTools\Faker;
use WP_User_Query;

/**
* Dokan Follow Store CLI Commands
*
* @since 1.0.0
*/
class FollowStore extends CLI {

    protected $base_command = 'dokan follow-store';

    /**
    * Class constructor
    *
    * @since 1.0.0
    */
    public function __construct() {
        $this->add_command( 'randomize', 'randomize' );
    }

    private function is_module_active() {
        if ( dokan_pro_is_module_active('follow-store/follow-store.php') ) {
            return true;
        }

        $this->error( 'Follow Store module is not active.' );
    }

    /**
     * Radomize following stores data
     *
     * ## EXAMPLES
     *
     *     # Randomize data
     *     $ wp dokan follow-store randomize
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function randomize() {
        $this->is_module_active();

        $vendors = dokan()->vendor->all();

        if ( empty( $vendors ) ) {
            $this->error( "You don't have any vendor. Create some vendors first." );
        }

        $customers = new WP_User_Query( [
            'role'   => 'customer',
            'number' => -1,
        ] );

        $customers = $customers->get_results();

        if ( empty( $customers ) ) {
            $this->error( "You don't have any customer. Create some customers first." );
        }

        $faker = Faker::get();

        $count    = count( $vendors );
        $message  = sprintf( 'Radomizing data for %d vendors', $count );
        $progress = $this->make_progress_bar( $message, $count );

        foreach ( $vendors as $vendor ) {
            $random_customers = $faker->randomElements( $customers, $faker->numberBetween( 0, count( $customers ) ) );

            foreach ( $random_customers as $customer ) {
                dokan_follow_store_toggle_status( $customer->ID, $vendor );
            }

            $progress->tick();
        }

        $progress->finish();

        $this->success( 'Randomized Follow Store data successfully.' );
    }
}
