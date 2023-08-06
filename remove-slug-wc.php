<?php

/**
 * Plugin Name: Remove WooCommerce Base
 * Text Domain: remove-slug-wc
 */

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	die();
}

include_once plugin_dir_path( __FILE__ ) . 'inc/class-base-remove-wc.php';

/**
 * Instantiate when WooCommerce plugin active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    Base_Remove_WC::instance();
}

