<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ANQC_Capture {

    private static $notices = [];
    private static $buffer_started = false;

    public static function init() {
        add_action( 'admin_notices', [ __CLASS__, 'capture_notices' ], 0 );
        add_action( 'network_admin_notices', [ __CLASS__, 'capture_notices' ], 0 );
        add_action( 'user_admin_notices', [ __CLASS__, 'capture_notices' ], 0 );
    }

    public static function capture_notices() {
        if ( self::$buffer_started ) {
            return;
        }

        self::$buffer_started = true;
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

            $classes  = self::extract_classes( $notice_html );
            $message  = trim( wp_strip_all_tags( $notice_html ) );
            $severity = self::detect_severity( $classes );

            $analysis = self::analyze_notice( $classes, $message, $severity );

            $source = self::detect_source();

            self::$notices[] = [
                'screen'    => $screen_id,
                'severity'  => $severity,
                'classes'   => $classes,
                'message'   => $message,
                'score'     => $analysis['score'],
                'issues'    => $analysis['issues'],
                'source'    => $source['type'],
                'source_id' => $source['identifier'],
                'file'      => $source['file'],
                'confidence'=> $source['confidence'],
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

    private static function detect_severity( $classes ) {
        if ( strpos( $classes, 'notice-error' ) !== false ) {
            return 'error';
        }
        if ( strpos( $classes, 'notice-warning' ) !== false ) {
            return 'warning';
        }
        if ( strpos( $classes, 'notice-success' ) !== false || strpos( $classes, 'updated' ) !== false ) {
            return 'success';
        }
        return 'info';
    }

    private static function detect_source() {

        $trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );

        foreach ( $trace as $step ) {

            if ( empty( $step['file'] ) ) {
                continue;
            }

            $file = wp_normalize_path( $step['file'] );

            // Plugin
            if ( strpos( $file, wp_normalize_path( WP_PLUGIN_DIR ) ) !== false ) {
                $relative = str_replace( wp_normalize_path( WP_PLUGIN_DIR ) . '/', '', $file );
                $parts    = explode( '/', $relative );

                return [
                    'type'      => 'plugin',
                    'identifier'=> $parts[0],
                    'file'      => $relative,
                    'confidence'=> 'high',
                ];
            }

            // Theme
            if ( strpos( $file, wp_normalize_path( get_theme_root() ) ) !== false ) {
                $relative = str_replace( wp_normalize_path( get_theme_root() ) . '/', '', $file );
                $parts    = explode( '/', $relative );

                return [
                    'type'      => 'theme',
                    'identifier'=> $parts[0],
                    'file'      => $relative,
                    'confidence'=> 'high',
                ];
            }
        }

        return [
            'type'       => 'core',
            'identifier' => 'wordpress',
            'file'       => '',
            'confidence' => 'low',
        ];
    }


    private static function analyze_notice( $classes, $message, $severity ) {

        $score  = 100;
        $issues = [];

        // Misused error severity
        if ( $severity === 'error' && stripos( $message, 'error' ) === false ) {
            $score -= 25;
            $issues[] = 'Error severity without real error';
        }

        // Not dismissible
        if ( strpos( $classes, 'is-dismissible' ) === false ) {
            $score -= 15;
            $issues[] = 'Not dismissible';
        }

        // Vague text
        if ( strlen( $message ) < 20 ) {
            $score -= 10;
            $issues[] = 'Vague or low-information message';
        }

        // Inline styles (bad practice)
        if ( strpos( $classes, 'style=' ) !== false ) {
            $score -= 10;
            $issues[] = 'Inline styles used';
        }

        return [
            'score'  => max( 0, $score ),
            'issues' => $issues,
        ];
    }

    public static function get_notices() {
        $stored = get_transient( 'anqc_notices' );
        return is_array( $stored ) ? $stored : [];
    }

    private static function store_notices( $notices ) {
        set_transient( 'anqc_notices', $notices, 10 * MINUTE_IN_SECONDS );
    }

    public static function get_source_summary() {

        $notices = self::get_notices();
        $summary = [];

        foreach ( $notices as $notice ) {

            $key = $notice['source'] . ':' . $notice['source_id'];

            if ( ! isset( $summary[ $key ] ) ) {
                $summary[ $key ] = [
                    'source'     => $notice['source'],
                    'id'         => $notice['source_id'],
                    'count'      => 0,
                    'totalScore' => 0,
                    'issues'     => 0,
                ];
            }

            $summary[ $key ]['count']++;
            $summary[ $key ]['totalScore'] += $notice['score'];

            if ( ! empty( $notice['issues'] ) ) {
                $summary[ $key ]['issues'] += count( $notice['issues'] );
            }
        }

        foreach ( $summary as &$item ) {
            $item['averageScore'] = round( $item['totalScore'] / $item['count'] );
        }

        return $summary;
    }

}
