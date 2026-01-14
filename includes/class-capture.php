<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ANQC_Capture {

    private static $notices = [];

    /**
     * Register hooks
     */
    public static function init() {
        // Start buffering before notices output
        add_action( 'all_admin_notices', [ __CLASS__, 'start_buffer' ], 0 );

        // End buffering after admin page is rendered
        add_action( 'admin_print_footer_scripts', [ __CLASS__, 'end_buffer' ], 0 );
    }

    /**
     * Start output buffering
     */
    public static function start_buffer() {
        ob_start();
    }

    /**
     * End output buffering, parse notices, and restore output
     */
    public static function end_buffer() {
        if ( ob_get_level() === 0 ) {
            return;
        }

        $output = ob_get_clean();

        if ( empty( $output ) ) {
            return;
        }

        self::parse_notices( $output );

        // Re-print output so WordPress behaves normally
        echo $output;
    }

    /**
     * Parse admin notices from HTML
     */
    private static function parse_notices( $html ) {
        $screen    = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        $screen_id = $screen ? $screen->id : 'unknown';

        if ( preg_match_all(
            '/<div[^>]+class=["\'][^"\']*(notice|error|updated)[^"\']*["\'][^>]*>.*?<\/div>/is',
            $html,
            $matches
        ) ) {
            foreach ( $matches[0] as $notice_html ) {
                self::$notices[] = [
                    'html'    => $notice_html,
                    'screen'  => $screen_id,
                    'classes' => self::extract_classes( $notice_html ),
                ];
            }
        }
    }

    /**
     * Extract CSS classes from notice HTML
     */
    private static function extract_classes( $html ) {
        if ( preg_match( '/class=["\']([^"\']+)["\']/', $html, $match ) ) {
            return $match[1];
        }
        return '';
    }

    /**
     * Expose captured notices
     */
    public static function get_notices() {
        return self::$notices;
    }
}
