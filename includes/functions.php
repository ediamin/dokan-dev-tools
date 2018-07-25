<?php

/**
 * Log last wpdb query
 *
 * @since 1.0.0
 *
 * @param bool $echo
 *
 * @return void
 */
function dokan_dev_last_query( $echo = false ) {
    global $wpdb;

    dokan_dev_format_query( $wpdb->last_query, $echo );
}

/**
 * Format the whitespace in a SQL string to make it easier to read
 *
 * @since 1.0.0
 *
 * @param string $query
 * @param bool   $echo
 *
 * @return void
 */
function dokan_dev_format_query( $query, $echo = false ) {
    if ( $echo ) {
        echo SqlFormatter::format( $query );
    } else {
        error_log( print_r( "\n" . SqlFormatter::format( $query, false ), true ) );
    }
}
