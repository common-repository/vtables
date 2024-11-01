<?php

/**
 * Plugin Name: vTables
 * Description: Create stunning, interactive tables effortlessly. No coding required. The ultimate WordPress plugin, lets you design powerful tables in a few clicks. Transform your data visualization now.
 * Author: vTables
 * Author URI: https://vtables.pro
 * Version: 0.1.9
 * License: GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define('VTABLES_URL', plugin_dir_url(__FILE__));
define('VTABLES_DIR', plugin_dir_path(__FILE__));
define('VTABLES_VERSION', '0.1.9');

class vTables {
  public function vtables_boot() {
    $this->vtables_add_module_to_script();
    $this->vtables_load_classes();
    $this->vtables_add_upgrade_link();
    $this->vtables_register_shortcodes();
    $this->vtables_activate_plugin();
    $this->vtables_render_menu();
    $this->vtables_db_endpoints();
  }

  /**
   * ADD MODULE TO SCRIPT HOOK
   */
  public function vtables_add_module_to_script() {
    add_filter('script_loader_tag', array($this, 'vtables_add_module_to_script_handler'), 10, 3);
  }

  /**
   * ADD MODULE TO SCRIPT HANDLER
   */
  function vtables_add_module_to_script_handler($tag, $handle, $src) {
    if ($handle === 'vtables-script-boot') {
      $tag = str_replace('<script ', '<script type="module" id="vtables-script-boot" ', $tag);
    }
    return $tag;
  }

  /**
   * LOAD CLASSES
   */
  public function vtables_load_classes() {
    require VTABLES_DIR . 'includes/autoload.php';
  }

  /**
   * ADD LINK TO UPGRADE HOOK
   */
  public function vtables_add_upgrade_link() {
    add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'vtables_upgrade_link_handler') );
  }

  /**
   * ADD LINK TO UPGRADE HANDLER
   */
  function vtables_upgrade_link_handler( array $links ) {
    $settings_link = '<b><a target="_blank" href="https://vtables.pro">' . __('Download Pro Add-on for free', 'vtables') . '</a></b>';
      $links[] = $settings_link;
    return $links;
  }

  /**
   * RENDER PLUGIN MENU
   */
  public function vtables_render_menu() {
    add_action('admin_menu', function () {
      if (!current_user_can('manage_options')) {
        return;
      }
      global $submenu;
      add_menu_page(
        'vTables',
        'vTables',
        'manage_options',
        'vtables',
        array($this, 'vtables_render_admin_page'),
        'dashicons-media-spreadsheet',
        25
      );
    });
  }

  /**
   * RENDER ADMIN PAGE
   */
  public function vtables_render_admin_page() {
    $loadAssets = new \vTables\Classes\LoadAssets();
    $loadAssets->vtables_enqueue_assets();

    $WP_API = apply_filters('vTables/admin_app_vars', array(
      'assets_url' => VTABLES_URL . 'dist/assets/',
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce( 'wp_rest' ),
      'base_url' => rest_url(),
    ));

    wp_localize_script('vtables-script-boot', 'WP_API_Settings', $WP_API);

    echo '<div id="q-app"></div>';
  }

  /**
   * REGISTER SHORTCODES
   */
  public function vtables_register_shortcodes() {
    global $shortcode_count;
    $shortcode_count = 0;

    // render shortcode
    function vtables_shortcode_render_handler($atts = []) {
      global $wpdb;
      global $shortcode_count;
      $id = isset($atts['id']) ? intval($atts['id']) : 0;

      // render specific cell value
      $row_number = isset($atts['row']) ? intval($atts['row']) : 0;
      $col_name = isset($atts['column']) ? sanitize_text_field($atts['column']) : '';

      if ($row_number > 0 && !empty($col_name)) {
        $col_name_snakecase = strtolower(str_replace(' ', '_', $col_name));
        $table_name = $wpdb->prefix . 'vtables';
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id);
        $results = $wpdb->get_results($query);

        if (empty($results)) {
          return 'Value not found';
        }

        $source = json_decode($results[0]->source);
        $rows = $source->rows;
        $row_index = $row_number - 1;

        if (!isset($rows[$row_index])) {
          return 'Row not found.';
        }

        $cell_value = isset($rows[$row_index]->{$col_name_snakecase}) ? $rows[$row_index]->{$col_name_snakecase} : '';

        return $cell_value;
      }


      // render table
      $shortcode_count++;

      ob_start();

      ?>

      <div class="vtables-iframe-wrapper">
        <iframe
          name="<?php echo esc_attr($shortcode_count); ?>"
          data-id="<?php echo esc_attr($id); ?>"
          data-count="<?php echo esc_attr($shortcode_count); ?>"
          data-baseurl="<?php echo esc_url(rest_url()); ?>"
          src="<?php echo esc_url(VTABLES_URL . 'includes/shortcode.html'); ?>"
          style="width: 100%; height: 150px; border: 0 none;"
        ></iframe>
      <div>


      <?php

      return ob_get_clean();
    }
    add_shortcode( 'vtables', 'vtables_shortcode_render_handler' );
  }

  /**
   * ACTIVATE PLUGIN
   */
  public function vtables_activate_plugin() {
    register_activation_hook(__FILE__, function ($newWorkWide) {
      require_once(VTABLES_DIR . 'includes/Classes/Activator.php');
      $activator = new \vTables\Classes\Activator();
      $activator->vtables_migrate_databases($newWorkWide);
    });
  }

  /**
   * ENDPOINTS TO DB TABLE
   */
  public function vtables_db_endpoints() {
    register_rest_field(
      'user',
      'user_email',
      [
        'get_callback' => static function (array $user): string {
          return get_userdata($user['id'])->user_email;
        },
      ]
    );

    include VTABLES_DIR . 'includes/endpoints.php';
  }

}

if (
  in_array( 'vtables.pro/vtables-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )
) {
  // Do nothing as Pro version installed and activated.
} else {
  (new vTables())->vtables_boot();
}





