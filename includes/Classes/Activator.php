<?php

namespace vTables\Classes;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ACTIVATOR CLASS
 * @since 1.0.0
 */
class Activator {

    /**
     * MIGRATE DATABASES
     */
    public function vtables_migrate_databases($network_wide = false) {
        global $wpdb;
        if ($network_wide) {
            // Retrieve all site IDs from this network (WordPress >= 4.6 provides easy to use functions for that).
            if (function_exists('get_sites') && function_exists('get_current_network_id')) {
                $site_ids = get_sites(array('fields' => 'ids', 'network_id' => get_current_network_id()));
            } else {
                $site_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs WHERE site_id = $wpdb->siteid;");
            }
            // Install the plugin for all these sites.
            foreach ($site_ids as $site_id) {
                switch_to_blog($site_id);
                $this->vtables_migrate();
                restore_current_blog();
            }
        } else {
            $this->vtables_migrate();
        }
    }

    /**
     * MIGRATE
     */
    private function vtables_migrate() {
      $this->vtables_create_main_table();
    }

    /**
     * CREATE MAIN TABLE
     */
    public function vtables_create_main_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'vtables';
        $sql = "CREATE TABLE $table_name (
            id int(10) NOT NULL AUTO_INCREMENT,
            title text NULL DEFAULT NULL,
            settings json NULL DEFAULT NULL,
            file_size text NULL DEFAULT NULL,
            external_source text NULL DEFAULT NULL,
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            source longtext NULL DEFAULT NULL,
            PRIMARY KEY (id)
            ) $charset_collate;";

        $this->vtables_run_sql($sql, $table_name);
        $this->vtables_add_new_column($table_name, 'table_type', 'TEXT');
        $this->vtables_add_new_column($table_name, 'woocommerce_categories', 'JSON');
    }

    /**
     * RUN SQL
     */
    private function vtables_run_sql($sql, $tableName) {
        global $wpdb;
        if ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    /**
     * ADD NEW COLUMN
     */
    function vtables_add_new_column($tableName, $tableColumn, $columnType) {
      if ($columnType === 'JSON') {
        $create_ddl = "ALTER TABLE $tableName ADD $tableColumn JSON;";
      } else {
        $create_ddl = "ALTER TABLE $tableName ADD $tableColumn TEXT NULL DEFAULT NULL;";
      }

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      maybe_add_column($tableName, $tableColumn, $create_ddl);
    }
}
