<?php

namespace Dokan\DevTools\CLI;

use Dokan\DevTools\Faker;
use Dokan\DevTools\Generator\Customer;
use Dokan\DevTools\Generator\Product as ProductGenerator;
use function Stringy\create as stringy;

class ReportAbuse extends CLI {

    protected $base_command = 'dokan report-abuse';

    /**
    * Class constructor
    *
    * @since 1.0.0
    */
    public function __construct() {
        $this->add_command( 'generate reasons', 'generate_reasons' );
        $this->add_command( 'generate reports', 'generate_reports' );
        $this->add_command( 'clean', 'clean' );
    }

    /**
     * Check if module is active or not
     *
     * @since 1.0.0
     *
     * @return bool
     */
    private function is_module_active() {
        if ( dokan_pro_is_module_active('report-abuse/report-abuse.php') ) {
            return true;
        }

        $this->error( 'Report Abuse module is not active.' );
    }

    /**
     * Randomly generate report abuse reasons
     *
     * ## EXAMPLES
     *
     *     # Randomize data
     *     $ wp dokan report-abuse generate reasons 20
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function generate_reasons( $args = [], $assoc_args = [] ) {
        $this->is_module_active();

        $count = array_pop( $args );
        $count = ! empty( $count ) || ( $count > 5 ) ? $count : 5;

        $message  = sprintf( 'Generating %d reasons', $count );

        $faker = Faker::get();
        $reasons = $faker->sentences( $count );
        array_push( $reasons, 'Other' );

        $option = get_option( 'dokan_report_abuse', [] );
        $option['abuse_reasons'] = [];

        $progress = $this->make_progress_bar( $message, $count );

        foreach ( $reasons as $reason ) {
            $option['abuse_reasons'][] = [
                'id'    => sprintf( '%s', stringy( $reason )->underscored() ),
                'value' => $reason
            ];

            $progress->tick();
        }

        $progress->finish();

        update_option( 'dokan_report_abuse', $option, true );

        $this->success( sprintf( 'Generated %d report abuse reasons', $count ) );

        return $option;
    }

    /**
     * Randomly generate report abuse reports
     *
     * ## EXAMPLES
     *
     *     # Randomize data
     *     $ wp dokan report-abuse generate reports 20
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function generate_reports( $args, $assoc_args ) {
        $this->is_module_active();

        $count = array_pop( $args );
        $count = ! empty( $count ) || ( $count > 20 ) ? $count : 20;

        $send_email = ! empty( $assoc_args['send-email'] ) ? true : false;

        $faker = Faker::get();

        $option = get_option( 'dokan_report_abuse', [] );

        if ( empty( $option['abuse_reasons'] ) ) {
            $option = $this->generate_reasons();
        }

        $abuse_reasons = $option['abuse_reasons'];

        $message  = sprintf( 'Generating %d abuse reports', $count );
        $progress = $this->make_progress_bar( $message, $count );

        $products  = ProductGenerator::get_existing_product_ids( 50 );
        $customers = Customer::get_existing_customer_ids( 30 );

        $generated = 0;

        for ( $i = 0; $i < $count; $i++ ) {
            $reason = $faker->randomElement( $abuse_reasons );

            $args = [
                'reason'     => $reason['value'],
                'product_id' => $faker->randomElement( $products ),
            ];

            $use_logged_in_user = $faker->randomElement( [ true, false, true ] );

            if ( $use_logged_in_user ) {
                $args['customer_id'] = $faker->randomElement( $customers );
            } else {
                $args['customer_name']  = $faker->name();
                $args['customer_email'] = $faker->safeEmail();
            }

            if ( $faker->randomElement( [ true, false, true, false, false ] ) ) {
                $args['description'] = $faker->realText();
            }

            $report = dokan_report_abuse_create_report( $args );

            if ( ! is_wp_error( $report ) ) {
                ++$generated;

                if ( $send_email ) {
                    do_action( 'dokan_report_abuse_send_admin_email', $report );
                }
            }

            $progress->tick();
        }

        $progress->finish();

        $this->success( sprintf( 'Generated %d abuse reports', $generated ) );
    }

    /**
     * Clean up module related data
     *
     * ## EXAMPLES
     *
     *     # Clean up data
     *     $ wp dokan report-abuse clean
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function clean() {
        $this->is_module_active();

        global $wpdb;

        $tables = [
            'dokan_report_abuse_reports'
        ];

        foreach ( $tables as $table ) {
            $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $table );
        }

        delete_option( 'dokan_report_abuse' );

        $this->success( 'All data related to Dokan Report Abuse module has been clean.' );
    }
}
