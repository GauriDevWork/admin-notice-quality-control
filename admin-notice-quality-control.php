<?php
/*
Plugin Name: Admin Notice Quality Control
Description: Analyze and evaluate the quality of WordPress admin notices.
Version: 0.1.0
Author: Webtechee
License: GPLv2 or later
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ANQC_PATH', plugin_dir_path( __FILE__ ) );

require_once ANQC_PATH . 'includes/class-capture.php';
require_once ANQC_PATH . 'includes/class-admin-ui.php';

add_action( 'plugins_loaded', function () {
    ANQC_Capture::init();
    new ANQC_Admin_UI();
});

/**
 * Test notice (safe, removable)
 */
add_action( 'admin_notices', function () {
    echo '<div class="notice notice-warning"><p>ANQC test notice</p></div>';
    echo '<div class="notice notice-warning"><p>ANQC test notice 123</p></div>';

});
