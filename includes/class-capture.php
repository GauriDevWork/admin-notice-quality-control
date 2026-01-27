<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ANQC_Capture {

    private static $notices = [];

    public static function init() {
        add_action( 'admin_notices', [ __CLASS__, 'capture_notices' ], 0 );
        add_action( 'network_admin_notices', [ __CLASS__, 'capture_notices' ], 0 );
        add_action( 'user_admin_notices', [ __CLASS__, 'capture_notices' ], 0 );
    }

    public static function capture_notices() {
        ob_start();
        add_action( 'shutdown', [ __CLASS__, 'collect_buffer' ], 0 );
    }

    public static function collect_buffer() {
        if ( ob_get_level() === 0 ) {
            return;
        }

        $html = ob_get_clean();
        if ( empty( $html ) ) {
            return;
        }

        self::parse_notices( $html );

        // Re-print original notices so admin UI is unchanged
        echo $html;
    }

    private static function parse_notices( $html ) {
        $screen    = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        $screen_id = $screen ? $screen->id : 'unknown';

        preg_match_all(
            '/<div[^>]+class=["\'][^"\']*(notice|error|updated)[^"\']*["\'][^>]*>.*?<\/div>/is',
            $html,
            $matches
        );

        foreach ( $matches[0] as $notice_html ) {
            self::$notices[] = [
                'screen'   => $screen_id,
                'severity' => self::detect_severity( $notice_html ),
                'classes'  => self::extract_classes( $notice_html ),
                'message'  => trim( wp_strip_all_tags( $notice_html ) ),
            ];
        }
        if ( ! empty( self::$notices ) ) {
            self::store_notices( self::$notices );
        }
    }

    private static function extract_classes( $html ) {
        if ( preg_match( '/class=["\']([^"\']+)["\']/', $html, $match ) ) {
            return sanitize_text_field( $match[1] );
        }
        return '';
    }

    private static function detect_severity( $html ) {
        if ( strpos( $html, 'notice-error' ) !== false || strpos( $html, 'error' ) !== false ) {
            return 'error';
        }
        if ( strpos( $html, 'notice-warning' ) !== false ) {
            return 'warning';
        }
        if ( strpos( $html, 'notice-success' ) !== false || strpos( $html, 'updated' ) !== false ) {
            return 'success';
        }
        return 'info';
    }

    public static function get_notices() {
        $stored = get_transient( 'anqc_notices' );
        return is_array( $stored ) ? $stored : [];
    }

    private static function store_notices( $notices ) {
        set_transient( 'anqc_notices', $notices, 5 * MINUTE_IN_SECONDS );
    }
}
