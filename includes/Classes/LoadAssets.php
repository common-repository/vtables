<?php

namespace vTables\Classes;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * GET HASHED VUE FILES
 */
function vtables_get_hashed_file($name, $type) {
  $folder = VTABLES_DIR . 'dist/spa/assets/';
  $files = list_files($folder);
  $matchedFiles = [];

  // Filter and store all matching files
  foreach ( $files as $file ) {
      if ( is_file( $file ) && strpos(basename($file), $name) !== false ) {
          $filetype = wp_check_filetype($file);
          if ($filetype['ext'] === $type) {
              $matchedFiles[] = $file;
          }
      }
  }

  // If multiple files match, sort them to get a consistent result
  if (count($matchedFiles) > 1) {
      usort($matchedFiles, function($a, $b) {
          return strcmp(basename($a), basename($b));
      });
  }

  // Return the first matched file
  return isset($matchedFiles[0]) ? basename($matchedFiles[0]) : null;
}

/**
 * LOAD ASSETS CLASS
 */
class LoadAssets {
  public function add_type_attribute( $tag, $handle, $src ) {
    if ( 'vtables-index' !== $handle ) {
      return $tag;
    }

    $tag = str_replace('<script ', '<script type="module" ', $tag);
    return $tag;
  }

  public function vtables_enqueue_assets() {
    $env_file_path = VTABLES_DIR . 'env.php';

    if (file_exists($env_file_path)) {
      include($env_file_path);
      $vtables_mode = constant('VTABLES_MODE');
    }

    if (isset($vtables_mode) && $vtables_mode === 'dev') { // dev mode

      wp_enqueue_style('vtables-material-icons', VTABLES_URL . 'public/css/material-icons-font-dist.css', false, null);
      wp_enqueue_script('vtables-script-boot', constant('VTABLES_BASE_URL') . '/.quasar/client-entry.js', array('jquery'), null, true);

    } else { // prod mode

      wp_enqueue_style( 'vtables-material-icons', VTABLES_URL . 'dist/spa/css/material-icons-font-dist.css', false, null);

      $app_css = VTABLES_URL . 'dist/spa/assets/' . vtables_get_hashed_file('index', 'css');
      wp_enqueue_style( 'vtables-index', $app_css, false, null);

      wp_localize_script(
        'wp-api',
        'WP_API_Settings',
        array(
          'nonce' => wp_create_nonce('wp_rest'),
          'base_url' => rest_url(),
        )
      );
      wp_enqueue_script('wp-api');

      $app_js = VTABLES_URL . 'dist/spa/assets/' . vtables_get_hashed_file('index', 'js');
      wp_enqueue_script( 'vtables-index', $app_js, array('jquery'), VTABLES_VERSION, false);

      add_filter( 'script_loader_tag', array( $this, 'add_type_attribute' ), 10, 3 );

    }
  }
}

