<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ANQC_Admin_UI {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu() {
        add_menu_page(
            __( 'Admin Notice Quality Control', 'admin-notice-qc' ),
            __( 'Notice QC', 'admin-notice-qc' ),
            'manage_options',
            'admin-notice-qc',
            [ $this, 'render_page' ],
            'dashicons-warning',
            80
        );
    }

    public function render_page() {

        $screen  = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        $notices = class_exists( 'ANQC_Capture' ) ? ANQC_Capture::get_notices() : [];
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Admin Notice Quality Control', 'admin-notice-qc' ); ?></h1>

            <p>
                <?php esc_html_e(
                    'This page displays all admin notices rendered on the current admin screen. Notices are captured in a read-only manner and are not modified.',
                    'admin-notice-qc'
                ); ?>
            </p>

            <!-- Helper / How to test -->
            <div class="notice notice-info">
                <p>
                    <?php esc_html_e(
                        'Admin notices are screen-specific. To test capture, reload this page while a plugin or theme outputs an admin notice on this same screen.',
                        'admin-notice-qc'
                    ); ?>
                </p>
            </div>

            <!-- Screen debug info -->
            <?php if ( $screen ) : ?>
                <p>
                    <strong><?php esc_html_e( 'Current screen:', 'admin-notice-qc' ); ?></strong>
                    <code><?php echo esc_html( $screen->id ); ?></code>
                </p>
            <?php endif; ?>

            <hr />

            <?php if ( empty( $notices ) ) : ?>

                <p>
                    <?php esc_html_e(
                        'No admin notices were detected on this screen.',
                        'admin-notice-qc'
                    ); ?>
                </p>

                <p>
                    <?php esc_html_e(
                        'Try visiting another admin page (Dashboard, Plugins, Post Editor) and then reload this page while a notice is visible.',
                        'admin-notice-qc'
                    ); ?>
                </p>

            <?php else : ?>

                <h2><?php esc_html_e( 'Captured Admin Notices', 'admin-notice-qc' ); ?></h2>

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Screen', 'admin-notice-qc' ); ?></th>
                            <th><?php esc_html_e( 'Classes', 'admin-notice-qc' ); ?></th>
                            <th><?php esc_html_e( 'Notice HTML', 'admin-notice-qc' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $notices as $notice ) : ?>
                            <tr>
                                <td>
                                    <code><?php echo esc_html( $notice['screen'] ); ?></code>
                                </td>
                                <td>
                                    <code><?php echo esc_html( $notice['classes'] ); ?></code>
                                </td>
                                <td>
                                    <div style="max-width:700px; white-space:pre-wrap;">
                                        <?php
                                        echo wp_kses_post( $notice['html'] );
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php endif; ?>

        </div>
        <?php
    }
}
