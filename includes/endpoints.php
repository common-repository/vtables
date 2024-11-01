<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * GET ALL TABLES.
 */
add_action( 'rest_api_init', function () {
  register_rest_route( 'vtables/v1', '/tables', array(
    'methods' => 'GET',
    'callback' => 'vtables_get_all_handler',
    'args' => [
      'page' => [
        'description' => 'Current page',
        'type' => "integer",
      ],
      'per_page' => [
        'description' => 'Items per page',
        'type' => "integer",
      ],
      'search' => [
        'description' => 'Search items',
        'type' => "string",
      ]
    ],
    'permission_callback' => '__return_true',
  ) );
} );

function vtables_get_all_handler( WP_REST_Request $data ) {
  global $wpdb;

  $table_name = $wpdb->prefix . 'vtables';
  $limit = $data['per_page'];
  $offset = ($data['page'] - 1) * $data['per_page'];
  $search = $data['search'] ?? '';
  $keyword = $wpdb->esc_like($search);
  $orderby = isset($data['orderby']) ? $data['orderby'] : 'created_at';
  $order = isset($data['order']) && in_array($data['order'], ['ASC', 'DESC']) ? $data['order'] : 'DESC';

  $search_by_title_query = $wpdb->prepare(
    "SELECT * FROM $table_name WHERE title LIKE %s",
    '%' . $keyword . '%'
  );

  $pagination_query = $wpdb->prepare(
    "SELECT * FROM $table_name WHERE title LIKE %s ORDER BY $orderby $order LIMIT $limit OFFSET $offset",
    '%' . $keyword . '%'
  );

  $total = $wpdb->get_results($search_by_title_query);
  $results = $wpdb->get_results($pagination_query);

  if (!$results) {
    new WP_Error( 'TABLES_NOT_FOUND', 'Tables Not found.', array( 'status' => 404 ) );
  }

  foreach ($results as $item) {
    $item->settings = json_decode($item->settings);
    $item->source = json_decode($item->source);
  }

  $response = array();
  $response['tables'] = $results;
  $response['total'] = count($total);

  return new WP_REST_Response(
    array(
      'success' => !!$results,
      'data' => $response,
    )
  );
}


/**
 * GET TABLE BY ID.
 */
add_action( 'rest_api_init', function () {
  register_rest_route( 'vtables/v1', '/tables/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'vtables_get_by_id_handler',
    'permission_callback' => '__return_true',
  ) );
} );

function vtables_get_by_id_handler( WP_REST_Request $data ) {
  global $wpdb;
  $table_name = $wpdb->prefix . 'vtables';
  $id = $data['id'];

  $query = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id);
  $results = $wpdb->get_results($query);

  if (empty($results)) {
    return new WP_Error( 'TABLE_NOT_FOUND', 'Table by ID not found.', array( 'status' => 404 ) );
  }

  $results[0]->settings = json_decode($results[0]->settings);
  $results[0]->source = json_decode($results[0]->source);
  $response = $results[0];

  return new WP_REST_Response(
    array(
      'success' => !!$results,
      'data' => $response,
    )
  );
}


/**
 * CREATE TABLE.
 */
add_action( 'rest_api_init', function () {
  register_rest_route( 'vtables/v1', '/tables', array(
    'methods' => 'POST',
    'callback' => 'vtables_create_record_handler',
    'permission_callback' => 'vtables_admin_permission_callback',
  ) );
} );

function vtables_create_record_handler( WP_REST_Request $data ) {
  global $wpdb;
  $table_name = $wpdb->prefix . 'vtables';
  $title = $data['title'];
  $source = json_encode($data['source']);
  $file_size = $data['file_size'];
  $external_source = $data['external_source'];
  $settings = json_encode($data['settings']);
  $table_type = $data['table_type'];
  $woocommerce_categories = json_encode($data['woocommerce_categories']);

  if ($title) {
    $query = $wpdb->prepare(
      "SELECT * FROM $table_name WHERE title = %s",
      addslashes($title)
    );
    $row_exists = $wpdb->get_results($query);
    $title = $row_exists ? $title . '-copy' : $title;
  }

  $results = $wpdb->insert($table_name,
    array(
      'title' => $title,
      'source' => $source,
      'file_size' => $file_size,
      'external_source' => $external_source,
      'settings' => $settings,
      'table_type' => $table_type,
      'woocommerce_categories' => $woocommerce_categories,
    )
  );

  $response = array();
  $response['id'] = $wpdb->insert_id;

  return new WP_REST_Response(
    array(
      'success' => !!$results,
      'data' => $response,
    )
  );
}


/**
 * SAVE TABLE CHANGES.
 */
add_action( 'rest_api_init', function () {
  register_rest_route( 'vtables/v1', '/source', array(
    'methods' => 'PUT',
    'callback' => 'vtables_save_table_changes_handler',
    'permission_callback' => 'vtables_admin_permission_callback',
  ) );
} );

function vtables_save_table_changes_handler( WP_REST_Request $data ) {
  global $wpdb;
  $table_name = $wpdb->prefix . 'vtables';
  $id = $data['id'];
  $title = $data['title'];
  $source = $data['source'];
  $file_size = $data['fileSize'];
  $sourceJSON = json_encode($data['source']);

  // Update only title
  if ($title && empty($source)) {
    $results = $wpdb->update(
      $table_name,
      array(
        'title' => $title,
        'updated_at' => date('Y-m-d h:i:s'),
      ),
      array( 'id' => $data['id'] ),
    );

    return new WP_REST_Response(
      array(
        'success' => !!$results,
        'data' => $results,
      )
    );
  }

  // Update source
  $results = $wpdb->update(
    $table_name,
    array(
      'title' => $title,
      'file_size' => $file_size,
      'source' => $sourceJSON,
      'updated_at' => date('Y-m-d h:i:s'),
    ),
    array( 'id' => $data['id'] ),
    array( '%s', '%d', '%s', '%s' ),
    array( '%d' )
  );

  $query = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id);
  $updated_table = $wpdb->get_results($query)[0];
  $updated_table->source = json_decode($updated_table->source);
  $response = $updated_table;

  return new WP_REST_Response(
    array(
      'success' => !!$results,
      'data' => $response,
    )
  );
}


/**
 * SAVE TABLE SETTINGS.
 */
add_action( 'rest_api_init', function () {
  register_rest_route( 'vtables/v1', '/settings', array(
    'methods' => 'PUT',
    'callback' => 'vtables_save_table_settings_handler',
    'permission_callback' => 'vtables_admin_permission_callback',
  ) );
} );

function vtables_save_table_settings_handler( WP_REST_Request $data ) {
  global $wpdb;

  $table_name = $wpdb->prefix . 'vtables';
  $settings = $data['settings'];
  $settingsJSON = json_encode($data['settings']);

  $results = $wpdb->update(
    $table_name,
    array(
      'settings' => $settingsJSON,
      'updated_at' => date('Y-m-d h:i:s'),
    ),
    array( 'id' => $data['id'] ),
    array( '%s', '%s' ),
    array( '%d' )
  );

  $response = array();
  $response['success'] = true;
  $response['updated_at'] = date('Y-m-d h:i:s');

  return new WP_REST_Response(
    array(
      'success' => !!$results,
      'data' => $response,
    )
  );
}



/**
 * DELETE TABLE.
 */
add_action( 'rest_api_init', function () {
  register_rest_route( 'vtables/v1', '/tables', array(
    'methods' => 'DELETE',
    'callback' => 'vtables_delete_record_handler',
    'permission_callback' => 'vtables_admin_permission_callback',
  ) );
} );

function vtables_delete_record_handler( WP_REST_Request $data ) {
  global $wpdb;
  $table_name = $wpdb->prefix . 'vtables';
  $results = $wpdb->delete(
    $table_name,
    array('id' => $data['id']),
  );

  $response = array();
  $response['id'] = $data['id'];

  return new WP_REST_Response(
    array(
      'success' => !!$results,
      'data' => $response,
    )
  );
}


/**
 * CUSTOM PERMISSION CALLBACK: accessible only by administrator.
 */
function vtables_admin_permission_callback() {
  if (
    current_user_can('administrator') ||
    current_user_can('editor') ||
    current_user_can('shop_manager')
  ) {
    return true;
  }
  return new WP_Error(
    'rest_forbidden',
    'You do not have permissions to access this endpoint.',
    array('status' => 401)
  );
}
