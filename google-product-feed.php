<?php

/**
 * I am Chucky, the killer doll! And I dig it!
 */
namespace Chucky;

/*
Plugin Name: Google Product Feed for WooCommerce
Plugin URI: https://github.com/nostrzak/google-product-feed
Description: Google Product Feed for Google Merchants integration
Version: 1.0.0
Author: Łukasz Wojciechowski
Author URI: https://github.com/nostrzak
License: GPL2
*/

if (!defined('ABSPATH')) exit;

define('Chucky\__ASSETS__', __DIR__ . '/assets');
define('Chucky\__BASEFILE__', __FILE__);

// Autoloader
require_once __DIR__ . '/src/Chucky/Tool/ClassLoader.php';

$loader = new Tool\ClassLoader();
$loader->addPrefix('Chucky', __DIR__ . '/src');
$loader->register();

// Check if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', get_option('active_plugins'))) {

    // Admin
    if(is_admin()) {
        $admin = new Controller\Admin();
        $admin->run();
    }

    // Feed 
    $feed = new Controller\Feed();
    $feed->run();
}
