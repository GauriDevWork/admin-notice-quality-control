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
            __( 'Admin Notice Quality Control', 'admin-notice-quality-control' ),
            __( 'Notice QC', 'admin-notice-quality-control' ),
            'manage_options',
            'admin-notice-qc',
            [ $this, 'render_page' ],
            'dashicons-warning',
            80
        );
    }

    public function render_page() {

        $notices = ANQC_Capture::get_notices();
        $summary = ANQC_Capture::get_source_summary();

        // Sort worst offenders first (lowest score)
        usort( $summary, function ( $a, $b ) {
            return $a['averageScore'] <=> $b['averageScore'];
        } );
        ?>
        <div class="wrap">

            <h1><?php esc_html_e( 'Admin Notice Quality Control', 'admin-notice-quality-control' ); ?></h1>

            <p>
                <?php esc_html_e(
                    'This page analyzes WordPress admin notices and highlights plugins or themes that negatively impact admin UI quality.',
                    'admin-notice-quality-control'
                ); ?>
            </p>

            <!-- ===================== -->
            <!-- Worst Offenders Table -->
            <!-- ===================== -->

            <h2><?php esc_html_e( 'Worst Admin Notice Offenders', 'admin-notice-quality-control' ); ?></h2>

            <?php if ( empty( $summary ) ) : ?>
                <p>
                    <?php esc_html_e(
                        'No notice data collected yet. Visit Dashboard, Plugins, or Updates pages first.',
                        'admin-notice-quality-control'
                    ); ?>
                </p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Source', 'admin-notice-quality-control' ); ?></th>
                            <th><?php esc_html_e( 'Identifier', 'admin-notice-quality-control' ); ?></th>
                            <th><?php esc_html_e( 'Notices', 'admin-notice-quality-control' ); ?></th>
                            <th><?php esc_html_e( 'Avg Score', 'admin-notice-quality-control' ); ?></th>
                            <th><?php esc_html_e( 'Issues', 'admin-notice-quality-control' ); ?></th>
                            <th><?php esc_html_e( 'Verdict', 'admin-notice-quality-control' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $summary as $row ) : ?>
                            <tr>
                                <td><?php echo esc_html( ucfirst( $row['source'] ) ); ?></td>
                                <td><?php echo esc_html( $row['id'] ); ?></td>
                                <td><?php echo esc_html( $row['count'] ); ?></td>
                                <td><?php echo esc_html( $row['averageScore'] ); ?>/100</td>
                                <td><?php echo esc_html( $row['issues'] ); ?></td>
                                <td>
                                    <?php
                                    if ( $row['averageScore'] < 50 ) {
                                        echo '🚨 ' . esc_html__( 'Critical', 'admin-notice-quality-control' );
                                    } elseif ( $row['averageScore'] < 75 ) {
                                        echo '⚠️ ' . esc_html__( 'Needs improvement', 'admin-notice-quality-control' );
                                    } else {
                                        echo '✅ ' . esc_html__( 'Good', 'admin-notice-quality-control' );
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- ================= -->
            <!-- Detailed Notices -->
            <!-- ================= -->

            <h2 style="margin-top: 40px;">
                <?php esc_html_e( 'Captured Admin Notices', 'admin-notice-quality-control' ); ?>
            </h2>

            <?php if ( empty( $notices ) ) : ?>
                <p>
                    <?php esc_html_e(
                        'No admin notices detected yet. Visit other admin pages to collect data.',
                        'admin-notice-quality-control'
                    ); ?>
                </p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Screen', 'admin-notice-quality-control' ); ?></th>
                            <th><?php esc_html_e( 'Severity', 'admin-notice-quality-control' ); ?></th>
                            <th><?php esc_html_e( 'Score', 'admin-notice-quality-control' ); ?></th>
                            <th><?php esc_html_e( 'Source', 'admin-notice-quality-control' ); ?></th>
                            <th><?php esc_html_e( 'Message', 'admin-notice-quality-control' ); ?></th>
                            <th><?php esc_html_e( 'Issues', 'admin-notice-quality-control' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $notices as $notice ) : ?>
                            <tr>
                                <td><?php echo esc_html( $notice['screen'] ); ?></td>
                                <td><?php echo esc_html( ucfirst( $notice['severity'] ) ); ?></td>
                                <td><?php echo esc_html( $notice['score'] ); ?>/100</td>
                                <td>
                                    <?php
                                    echo esc_html(
                                        ucfirst( $notice['source'] ) . ' / ' . $notice['source_id']
                                    );
                                    ?>
                                </td>
                                <td><?php echo esc_html( $notice['message'] ); ?></td>
                                <td>
                                    <?php
                                    if ( empty( $notice['issues'] ) ) {
                                        echo '✅';
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
