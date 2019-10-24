<?php

namespace Dokan\DevTools\CLI;

use Dokan\DevTools\Faker;
use WC_Coupon;
use WC_Customer;
use WP_User_Query;
use WP_Query;

/**
* Dokan Follow Store CLI Commands
*
* @since 1.0.0
*/
class FollowStore extends CLI {

    protected $base_command = 'dokan follow-store';

    private $vendors = [];

    /**
    * Class constructor
    *
    * @since 1.0.0
    */
    public function __construct() {
        $this->add_command( 'randomize', 'randomize' );
        $this->add_command( 'purge', 'purge' );
        $this->add_command( 'test-email-template', 'test_email_template' );
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
                dokan_follow_store_toggle_status( $vendor->id, $customer->ID );
            }

            $progress->tick();
        }

        $progress->finish();

        $this->success( 'Randomized Follow Store data successfully.' );
    }

    /**
     * Remove all Dokan Follow Store related data
     *
     * ## EXAMPLES
     *
     *     # Purge all data
     *     $ wp dokan follow-store purge
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function purge() {
        global $wpdb;

        // Module related tables
        $tables = [
            'dokan_follow_store_followers',
        ];

        foreach ( $tables as $table ) {
            $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $table );
        }

        $this->success( 'Purged all Follow Store related data.' );
    }

    public function test_email_template( $args ) {
        list( $follower_id ) = $args;

        $this->is_module_active();

        $yesterday = date( 'Y-m-d', strtotime( '-500 hours', current_time( 'timestamp' ) ) );
        $from      = $yesterday . ' 00:00:00';
        // $to        = $yesterday . ' 23:59:59';
        $to        = '2018-12-30' . ' 23:59:59';

        $args = array(
            'page'  => 1,
            'from'  => $from,
            'to'    => $to,
        );

        $follower = get_user_by( 'ID', $follower_id );

        $vendors_ids = $this->get_following_vendors( $follower_id );

        $vendors  = array();

        if ( ! empty( $vendors_ids ) ) {
            foreach ( $vendors_ids as $vendor_id ) {
                if ( array_key_exists( $vendor_id , $this->vendors ) ) {
                    $vendor = $this->vendors[ $vendor_id ];
                } else {
                    $vendor = dokan()->vendor->get( $vendor_id );
                    $this->vendors[ $vendor_id ] = $vendor;
                }

                if ( empty( $vendor->id ) ) {
                    continue;
                }

                $vendor->products = $this->get_vendor_new_products( $vendor_id, $args );
                $vendor->coupons  = $this->get_vendor_new_coupons( $follower, $vendor_id, $args );

                $vendors[] = $vendor;
            }

            do_action( 'dokan_follow_store_send_update_email', $follower, $vendors );
        }
    }

    private function get_following_vendors( $follower_id ) {
        global $wpdb;

        return $wpdb->get_col( $wpdb->prepare(
              "select vendor_id"
            . " from {$wpdb->prefix}dokan_follow_store_followers"
            . " where unfollowed_at is null"
            . "     and follower_id = %d",
            $follower_id
        ) );
    }

    private function get_vendor_new_products( $vendor_id, $args ) {
        $query_args = array(
            'post_type'   => 'product',
            'author'      => $vendor_id,
            'post_status' => 'publish',
            'posts_per_page' => 3,
            'date_query'  => array(
                'after'     => $args['from'],
                'before'    => $args['to'],
                'inclusive' => true,
            ),
        );

        return new WP_Query( $query_args );
    }

    private function get_vendor_new_coupons( $follower, $vendor_id, $args ) {
        $customer = new WC_Customer( $follower->ID );
        $customer_emails  = array_unique(
            array_filter(
                array_map(
                    'strtolower', array_map(
                        'sanitize_email', array(
                            $customer->get_billing_email(),
                            $follower->user_email,
                        )
                    )
                )
            )
        );

        $query_args = array(
            'post_type'   => 'shop_coupon',
            'author'      => $vendor_id,
            'post_status' => 'publish',
            'date_query'  => array(
                'after'     => $args['from'],
                'before'    => $args['to'],
                'inclusive' => true,
            ),
            'nopaging'    => true,
        );

        $new_coupons = new WP_Query( $query_args );

        $coupons = array();

        if ( $new_coupons->have_posts() ) {
            foreach ( $new_coupons->posts as $post ) {
                $coupon = new WC_Coupon( $post->ID );

                if ( dokan_follower_can_user_coupon( $customer_emails, $coupon ) ) {
                    $coupons[] = $coupon;
                }
            }
        }

        return $coupons;
    }
}
