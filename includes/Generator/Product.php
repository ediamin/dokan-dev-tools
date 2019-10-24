<?php

namespace Dokan\DevTools\Generator;

use WC\SmoothGenerator\Generator\Product as WCSGProduct;

class Product extends WCSGProduct {

    public static function get_existing_product_ids( $limit = 5 ) {
        return parent::get_existing_product_ids( $limit );
    }
}
