<?php
/*
Plugin Name: Admin Notice Quality Control
Description: Inspect and analyze admin notices across WordPress admin screens.
Version: 0.1.0
Author: WebTechee
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
