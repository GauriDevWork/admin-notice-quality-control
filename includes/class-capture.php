<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ANQC_Capture {

    private static $notices = [];

    public static function init() {
        add_action( 'admin_notices', [ __CLASS__, 'capture_notice' ], 0 );
        add_action( 'network_admin_notices', [ __CLASS__, 'capture_notice' ], 0 );
        add_action( 'user_admin_notices', [ __CLASS__, 'capture_notice' ], 0 );
    }

    public static function capture_notice() {
        global $wp_filter;

        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        $screen_id = $screen ? $screen->id : 'unknown';

        // Mark that notices exist on this screen
        self::$notices[] = [
            'screen'   => $screen_id,
            'severity' => 'Detected',
            'classes'  => 'admin_notice',
            'html'     => 'Admin notice rendered on this screen',
        ];
    }

    public static function get_notices() {
        return self::$notices;
    }
}
