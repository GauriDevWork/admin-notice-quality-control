<?php
/*
Plugin Name: Admin Notice Quality Control
Description: Inspect and analyze WordPress admin notices without modifying plugin behavior.
Version: 0.1.0
Author: Your Name
License: GPLv2 or later
Text Domain: admin-notice-quality-control
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
