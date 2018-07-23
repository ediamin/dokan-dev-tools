<?php
/**
 * Plugin Name: Dokan Dev Tools
 * Plugin URI: https://github.com/ediamin
 * Description: An open source job dokan_dev_tools plugin for WordPress
 * Version: 1.0.0
 * Author: Edi Amin
 * Author URI: https://github.com/ediamin
 * Text Domain: dokan-dev-tools
 * Domain Path: /i18n/languages/
 */

// Do not call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_exists( 'Dokan\DevTools\DevTools' ) || require_once __DIR__.'/vendor/autoload.php';

use Dokan\DevTools\DevTools;

define( 'DOKAN_DEVTOOLS_FILE', __FILE__ );
define( 'DOKAN_DEVTOOLS_PATH', dirname( DOKAN_DEVTOOLS_FILE ) );

DevTools::init();
