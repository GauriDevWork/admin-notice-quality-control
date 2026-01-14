<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ANQC_Admin_UI {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu() {
        add_management_page(
            __( 'Admin Notices', 'admin-notice-quality-control' ),
            __( 'Admin Notices', 'admin-notice-quality-control' ),
            'manage_options',
            'admin-notice-quality-control',
            [ $this, 'render_page' ]
        );
    }

    public function render_page() {
        $notices = ANQC_Capture::get_notices();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Admin Notices Overview', 'admin-notice-quality-control' ); ?></h1>

            <?php if ( empty( $notices ) ) : ?>
                <p><?php esc_html_e( 'No admin notices detected on this page load.', 'admin-notice-quality-control' ); ?></p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Screen', 'admin-notice-quality-control' ); ?></th>
                            <th><?php esc_html_e( 'CSS Classes', 'admin-notice-quality-control' ); ?></th>
                            <th><?php esc_html_e( 'Preview', 'admin-notice-quality-control' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $notices as $notice ) : ?>
                            <tr>
                                <td><?php echo esc_html( $notice['screen'] ); ?></td>
                                <td><?php echo esc_html( $notice['classes'] ); ?></td>
                                <td>
                                    <code style="display:block; max-width:600px; white-space:pre-wrap;">
                                        <?php echo esc_html( wp_strip_all_tags( $notice['html'] ) ); ?>
                                    </code>
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
