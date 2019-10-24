<?php

namespace Dokan\DevTools\Generator;

class Customer {

    public static function get_existing_customer_ids( $limit = 5 ) {
        $user_ids = get_users( [
            'number'  => $limit * 2,
            'orderby' => 'email',
            'role'    => 'customer',
            'fields'  => 'ids',
        ] );

        if ( ! $user_ids ) {
            return [];
        }

        shuffle( $user_ids );

        return array_slice( $user_ids, 0, max( count( $user_ids ), $limit ) );
    }
}
