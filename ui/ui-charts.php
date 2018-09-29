<?php

/**
 * Class DT_Zume_Hooks
 */
class DT_Network_Dashboard_Hooks
{

    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Build hook classes
     */
    public function __construct() {
        new DT_Network_Dashboard_UI_Charts();
    }
}
DT_Network_Dashboard_Hooks::instance();

/**
 * Empty class for now..
 * Class DT_Zume_Hook_Base
 */
abstract class DT_Network_Dashboard_Base
{
    public function __construct() {
    }
}

class DT_Network_Dashboard_UI_Charts extends DT_Network_Dashboard_Base
{
    /**
     * This filter adds a menu item to the metrics
     *
     * @param $content
     *
     * @return string
     */
    public function menu( $content ) {
        $content .= '
              <li><a href="'. site_url( '/network/' ) .'#network_dashboard_overview" onclick="show_network_dashboard_overview()">' .  esc_html__( 'Overview' ) . '</a></li>
              <li><a href="'. site_url( '/network/' ) .'#network_tree" onclick="show_network_tree()">' .  esc_html__( 'Tree' ) . '</a></li>
              <li><a href="'. site_url( '/network/' ) .'#network_map" onclick="show_network_map()">' .  esc_html__( 'Map' ) . '</a></li>
              <li><a href="'. site_url( '/network/' ) .'#network_map_map" onclick="show_network_map_map()">' .  esc_html__( 'Map' ) . '</a></li>
              <li><a href="'. site_url( '/network/' ) .'#network_side_tree" onclick="show_network_side_tree()">' .  esc_html__( 'Side Tree' ) . '</a></li>
              <li><a href="'. site_url( '/network/' ) .'#report_sync" onclick="show_report_sync()">' .  esc_html__( 'Report Sync' ) . '</a></li>';
        return $content;
    }

    /**
     * Load scripts for the plugin
     */
    public function scripts() {
        wp_enqueue_script( 'dt_network_dashboard_script', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'ui-charts.js', [
            'jquery',
            'jquery-ui-core',
        ], filemtime( plugin_dir_path( __DIR__ ) . 'ui/ui-charts.js' ), true );
        wp_enqueue_script( 'jquery-ui-autocomplete' );

        wp_localize_script(
            'dt_network_dashboard_script', 'wpApiNetworkDashboard', [
                'root' => esc_url_raw( rest_url() ),
                'plugin_uri' => plugin_dir_url( __DIR__ ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'map_key' => dt_get_option( 'map_key' ),
                'spinner' => '<img src="'. plugin_dir_url( __FILE__ ) . '/spinner.svg" width="12px" />',
                'spinner_large' => '<img src="'. plugin_dir_url( __FILE__ ) . '/spinner.svg" width="24px" />',
                'stats' => [
                    'table' => DT_Network_Dashboard_Stats::get_location_table(),
                    'tree' => DT_Network_Dashboard_Stats::get_location_tree(),
                    'map' => DT_Network_Dashboard_Stats::get_location_map(),
                    'side_tree' => DT_Network_Dashboard_Stats::get_location_side_tree(),
                    'level_tree' => DT_Network_Dashboard_Stats::get_locations_level_tree(),
                    'report_sync' => DT_Network_Dashboard_Stats::get_site_link_list(),
                ],
                'translations' => [
                    "sm_title" => __( "Network Dashboard", "dt_zume" ),
                ]
            ]
        );
    }

    public function add_url( $template_for_url ) {
        $template_for_url['network'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function top_nav_desktop() {
        if ( current_user_can( 'view_any_contacts' ) || current_user_can( 'view_project_metrics' ) ) {
            ?><li><a href="<?php echo esc_url( site_url( '/network/' ) ); ?>"><?php esc_html_e( "Network" ); ?></a></li><?php
        }
    }

    // Enqueue maps and charts for standard metrics
    public function enqueue_google() {
        /* phpcs:ignore WordPress.WP.EnqueuedResourceParameters */
        wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', [], false );
        /* phpcs:ignore WordPress.WP.EnqueuedResourceParameters */
        wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . dt_get_option( 'map_key' ), array(), null, true );
    }

    public function __construct() {

        if ( current_user_can( 'view_any_contacts' ) || current_user_can( 'view_project_metrics' ) ) {

            add_action( 'dt_top_nav_desktop', [ $this, 'top_nav_desktop' ] );
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_google' ], 10 );

            if ( isset( $_SERVER["SERVER_NAME"] ) ) {
                $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) );
                if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                    $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
                }
            }
            $url_path = trim( str_replace( get_site_url(), "", $url ), '/' );

            if ( 'network' === substr( $url_path, '0', 7 ) ) {

                add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
                add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 99 );

                if ( 'network' === $url_path ) {
                    add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
                }
            }
        } // end admin only test
    }
}