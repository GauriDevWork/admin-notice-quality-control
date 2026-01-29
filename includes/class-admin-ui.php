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
            'Admin Notice QC',
            'Notice QC',
            'manage_options',
            'admin-notice-qc',
            [ $this, 'render_page' ],
            'dashicons-warning',
            80
        );
    }

    public function render_page() {
        $notices = ANQC_Capture::get_notices();
        ?>
        <div class="wrap">
            <h1>Admin Notice Quality Control</h1>
            <p>This page lists all admin notices detected across admin screens.</p>

            <?php if ( empty( $notices ) ) : ?>
                <p><em>No admin notices detected yet.</em></p>
                <p>Visit Dashboard, Plugins, or Updates page and refresh this screen.</p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Screen</th>
                            <th>Severity</th>
                            <th>Classes</th>
                            <th>Message</th>
                            <th>Score</th>
                            <th>Issues</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $notices as $notice ) : ?>
                            <tr>
                                <td><?php echo esc_html( $notice['screen'] ); ?></td>
                                <td><?php echo esc_html( ucfirst( $notice['severity'] ) ); ?></td>
                                <td><code><?php echo esc_html( $notice['classes'] ); ?></code></td>
                                <td><?php echo esc_html( $notice['message'] ); ?></td>
                                <td><?php echo esc_html( $notice['score'] ); ?></td>
                                <td>
                                    <?php
                                    if ( empty( $notice['issues'] ) ) {
                                        echo '✅ Good';
                                    } else {
                                        echo esc_html( implode( ', ', $notice['issues'] ) );
                                    }
                                    ?>
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
