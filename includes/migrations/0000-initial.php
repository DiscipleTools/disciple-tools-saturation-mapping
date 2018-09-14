<?php
declare(strict_types=1);

require_once( 'abstract.php' );

/**
 * Class DT_Saturation_Mapping_Migration_0000
 */
class DT_Saturation_Mapping_Migration_0000 extends DT_Saturation_Mapping_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        /**
         * Install tables
         */
        global $wpdb;
        $expected_tables = $this->get_expected_tables();
        foreach ( $expected_tables as $name => $table) {
            $rv = $wpdb->query( $table ); // WPCS: unprepared SQL OK
            if ( $rv == false ) {
                throw new Exception( "Got error when creating table $name: $wpdb->last_error" );
            }
        }

        /**
         * Install initial country, admin1, and admin2 data
         */
        $wpdb->dt_geonames = $wpdb->prefix . 'dt_geonames';
        require_once( DT_Saturation_Mapping::get_instance()->dir_path . 'install/installer.php' );
        DT_Saturation_Mapping_Installer::install_world_admin_set();

        $role = get_role( 'administrator' );
        if ( !empty( $role ) ) {
            $role->add_cap( 'manage_dt' ); // gives access to dt plugin options
        }

        /**
         * Setup variables
         */
        update_option( 'dt_saturation_mapping_pd', 5000, false );

        /**
         * Initialize partner profile
         */
        $partner_profile = [
            'partner_name' => get_option( 'blogname' ),
            'partner_description' => get_option( 'blogdescription' ),
            'partner_id' => DT_Saturation_Mapping::get_unique_public_key(),
        ];
        update_option( 'dt_site_partner_profile', $partner_profile, false );
    }

    /**
     * @throws \Exception  Got error when dropping table $name.
     */
    public function down() {
        global $wpdb;
        $expected_tables = $this->get_expected_tables();
        foreach ( $expected_tables as $name => $table) {
            $rv = $wpdb->query( "DROP TABLE `{$name}`" ); // WPCS: unprepared SQL OK
            if ( $rv == false ) {
                throw new Exception( "Got error when dropping table $name: $wpdb->last_error" );
            }
        }

        delete_option( 'dt_saturation_mapping_pd' );
        delete_option( 'dt_site_partner_profile' );
    }

    /**
     * @return array
     */
    public function get_expected_tables(): array {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        return array(
            "{$wpdb->prefix}dt_geonames" =>
                "CREATE TABLE `{$wpdb->prefix}dt_geonames` (
                  `geonameid` bigint(11) unsigned NOT NULL,
                  `name` varchar(200) DEFAULT NULL,
                  `asciiname` varchar(200) DEFAULT NULL,
                  `alternatenames` varchar(10000) DEFAULT NULL,
                  `latitude` float DEFAULT NULL,
                  `longitude` float DEFAULT NULL,
                  `feature_class` char(1) DEFAULT NULL,
                  `feature_code` varchar(10) DEFAULT NULL,
                  `country_code` char(2) DEFAULT NULL,
                  `cc2` varchar(100) DEFAULT NULL,
                  `admin1_code` varchar(20) DEFAULT NULL,
                  `admin2_code` varchar(80) DEFAULT NULL,
                  `admin3_code` varchar(20) DEFAULT NULL,
                  `admin4_code` varchar(20) DEFAULT NULL,
                  `population` int(11) DEFAULT NULL,
                  `elevation` int(80) DEFAULT NULL,
                  `dem` varchar(80) DEFAULT NULL,
                  `timezone` varchar(40) DEFAULT NULL,
                  `modification_date` date DEFAULT NULL,
                  PRIMARY KEY (`geonameid`),
                  FULLTEXT KEY `feature_class` (`feature_class`),
                  FULLTEXT KEY `feature_code` (`feature_code`),
                  FULLTEXT KEY `country_code` (`country_code`),
                  FULLTEXT KEY `admin1_code` (`admin1_code`),
                  FULLTEXT KEY `admin2_code` (`admin2_code`)
                ) $charset_collate;",
            "{$wpdb->prefix}dt_geonames_polygons" =>
                "CREATE TABLE `{$wpdb->prefix}dt_geonames_polygons` (
                  `geonameid` bigint(11) unsigned NOT NULL,
                  `geoJSON` longtext,
                  PRIMARY KEY (`geonameid`)
                ) $charset_collate;",
            "{$wpdb->prefix}dt_network_reports" =>
                "CREATE TABLE `{$wpdb->prefix}dt_network_reports` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `partner_id` varchar(11) NOT NULL DEFAULT '',
                  `location_name` varchar(200) DEFAULT NULL,
                  `geonameid` int(11) NOT NULL DEFAULT '0',
                  `longitude` float NOT NULL,
                  `latitude` float NOT NULL,
                  `total_contacts` int(7) NOT NULL DEFAULT '0',
                  `total_groups` int(7) NOT NULL DEFAULT '0',
                  `total_users` int(7) NOT NULL DEFAULT '0',
                  `new_contacts` int(7) NOT NULL DEFAULT '0',
                  `new_groups` int(7) NOT NULL DEFAULT '0',
                  `new_users` int(7) NOT NULL DEFAULT '0',
                  `date` date NOT NULL,
                  `raw_response` longtext NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `geonameid` (`geonameid`),
                  KEY `partner_id` (`partner_id`),
                  KEY `longitude` (`longitude`),
                  KEY `latitude` (`latitude`),
                  KEY `date` (`date`)
                )  $charset_collate;",
            "{$wpdb->prefix}dt_network_reportmeta" =>
                "CREATE TABLE `{$wpdb->prefix}dt_network_reportmeta` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `report_id` int(11) NOT NULL,
                  `meta_key` varchar(255) NOT NULL DEFAULT '',
                  `meta_value` longtext,
                  PRIMARY KEY (`id`),
                  KEY `report_id` (`report_id`),
                  KEY `meta_key` (`meta_key`)
                ) $charset_collate;",
        );
    }

    /**
     * Test function
     */
    public function test() {
        $this->test_expected_tables();
    }

}
