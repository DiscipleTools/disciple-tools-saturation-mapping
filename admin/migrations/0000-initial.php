<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

require_once( 'abstract.php' );

/**
 * Class DT_Network_Dashboard_Migration_0000
 */
class DT_Network_Dashboard_Migration_0000 extends DT_Network_Dashboard_Migration {
    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        // create role and add cap
        if ( get_role( 'network_dashboard_viewer' ) ) {
            remove_role( 'network_dashboard_viewer' );
        }
        add_role(
            'network_dashboard_viewer',
            __( 'Network Dashboard Viewer' ),
            array(
                'view_network_dashboard' => true
            )
        );

        // extend cap to strategist role
        if ( get_role( 'strategist' ) ) {
            $strategist = get_role( 'strategist' );
            $strategist->add_cap( 'view_network_dashboard' );
        }

        // extend cap to strategist role
        if ( get_role( 'dt_admin' ) ) {
            $strategist = get_role( 'dt_admin' );
            $strategist->add_cap( 'view_network_dashboard' );
        }

    }

    /**
     * @throws \Exception  Got error when dropping table $name.
     */
    public function down() {
        remove_role( 'network_dashboard_viewer' );
        delete_option( 'dt_network_dashboard_population' );
    }

    /**
     * Test function
     */
    public function test() {
    }

}
