<?php

// function to check if the well pad post is already created for a user
function pad_exists_for_user( $user_id, $post_title ) {
	$query = new WP_Query(array(
		'post_type'      => 'well_pad',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'title'          => $post_title,
		'meta_query' => array(
			'relation' 		=> 'AND',
			array(
				'key'     	=> 'userid',
				'value'   	=> $user_id,
				'compare' 	=> '=',
			),
		),
	));
	$post = $query->get_posts();
	return $post;
}

// handle water request submissions
function create_new_post() {
    // Early guard: if not POST (e.g., mirrored GET to admin-post), send user somewhere safe
    if ( strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' ) {
        // Prefer the correct mode using request hint, cookie, or referer
        $watersharing_enabled = (bool) get_option('watersharing_toggle');
        $watertrading_enabled = (bool) get_option('watertrading_toggle');
        $ws_prod = absint( get_option('production_dashboard_page', 0) );
        $ws_cons = absint( get_option('consumption_dashboard_page', 0) );
        $wt_prod = absint( get_option('wt_production_dashboard_page', 0) );
        $wt_cons = absint( get_option('wt_consumption_dashboard_page', 0) );

        $mode = isset($_REQUEST['mode']) ? sanitize_key($_REQUEST['mode']) : '';
        if ($mode !== 'watertrading' && $mode !== 'watersharing') {
            $mode = isset($_COOKIE['ws_last_mode']) ? sanitize_key($_COOKIE['ws_last_mode']) : '';
        }
        if ($mode !== 'watertrading' && $mode !== 'watersharing') {
            $ref = wp_get_referer();
            if ($ref) {
                $ref = (string) $ref;
                if ( ($wt_prod && strpos($ref, get_permalink($wt_prod)) !== false) || ($wt_cons && strpos($ref, get_permalink($wt_cons)) !== false) ) {
                    $mode = 'watertrading';
                } elseif ( ($ws_prod && strpos($ref, get_permalink($ws_prod)) !== false) || ($ws_cons && strpos($ref, get_permalink($ws_cons)) !== false) ) {
                    $mode = 'watersharing';
                }
            }
        }

        $fallback = home_url('/');
        if ($mode === 'watertrading' && $watertrading_enabled) {
            $fallback = $wt_prod ? get_permalink($wt_prod) : ( $wt_cons ? get_permalink($wt_cons) : $fallback );
        } elseif ($watersharing_enabled) {
            $fallback = $ws_prod ? get_permalink($ws_prod) : ( $ws_cons ? get_permalink($ws_cons) : $fallback );
        } elseif ($watertrading_enabled) {
            $fallback = $wt_prod ? get_permalink($wt_prod) : ( $wt_cons ? get_permalink($wt_cons) : $fallback );
        }
        nocache_headers();
        if ( ob_get_length() ) { ob_end_clean(); }
        if ( headers_sent() ) {
            $safe_url = esc_url( $fallback );
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url=' . esc_attr( $safe_url ) . '"><script>window.location.replace(' . json_encode( $safe_url ) . ');</script></head><body><p>Redirecting... <a href="' . esc_attr( $safe_url ) . '">Continue</a></p></body></html>';
            exit;
        }
        wp_safe_redirect( $fallback, 303 );
        exit;
    }

    // Verify nonce
    if ( ! isset($_POST['watersharing_nonce']) || ! wp_verify_nonce($_POST['watersharing_nonce'], 'create_water_request') ) {
        wp_die('Invalid request');
    }

    // Build an idempotency key that reflects this specific form payload (not just the nonce)
    $nonce_value = (string) $_POST['watersharing_nonce'];
    $payload = $_POST;
    unset($payload['watersharing_nonce']);
    // Also ignore common non-functional fields if present
    unset($payload['_wp_http_referer']);
    ksort($payload);
    $payload_str = wp_json_encode($payload);
    $idemp_key = 'ws_nonce_used_' . md5( $nonce_value . '|' . get_current_user_id() . '|' . $payload_str );

    // Idempotency: bail out if this specific payload was already processed for this user (prevents duplicate posts in multi-pane)
    if ( get_transient( $idemp_key ) ) {
        // Compute the same redirect URL logic used below
        $post_type = isset($_POST['post_type']) ? sanitize_key($_POST['post_type']) : '';
        $watersharing_prod_redirect_id = absint( get_option('production_dashboard_page', 0) );
        $watersharing_cons_redirect_id = absint( get_option('consumption_dashboard_page', 0) );
        $watertrading_prod_redirect_id = absint( get_option('wt_production_dashboard_page', 0) );
        $watertrading_cons_redirect_id = absint( get_option('wt_consumption_dashboard_page', 0) );

        if(isset($_POST['redirect_success']) && !empty($_POST['redirect_success'])) {
            $redirect_path = wp_parse_url( sanitize_text_field($_POST['redirect_success']), PHP_URL_PATH );
            $redirect_path = ltrim( (string) $redirect_path, '/' );
            $redirect_url = home_url( $redirect_path ? '/' . $redirect_path : '/' );
        } else {
            $redirect_url = home_url();
            switch ($post_type) {
                case 'share_supply':
                    $redirect_url = $watersharing_prod_redirect_id ? get_permalink($watersharing_prod_redirect_id) : home_url();
                    break;
                case 'share_demand':
                    $redirect_url = $watersharing_cons_redirect_id ? get_permalink($watersharing_cons_redirect_id) : home_url();
                    break;
                case 'trade_supply':
                    $redirect_url = $watertrading_prod_redirect_id ? get_permalink($watertrading_prod_redirect_id) : home_url();
                    break;
                case 'trade_demand':
                    $redirect_url = $watertrading_cons_redirect_id ? get_permalink($watertrading_cons_redirect_id) : home_url();
                    break;
            }
        }

        // Remember mode briefly to guide any mirrored admin-post hits
        $mode = (strpos($post_type, 'trade_') === 0) ? 'watertrading' : 'watersharing';
        @setcookie('ws_last_mode', $mode, time() + 300, '/');

        // Redirect now
        nocache_headers();
        if ( ob_get_length() ) { ob_end_clean(); }
        if ( headers_sent() ) {
            $safe_url = esc_url( $redirect_url );
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url=' . esc_attr( $safe_url ) . '"><script>window.location.replace(' . json_encode( $safe_url ) . ');</script></head><body><p>Redirecting... <a href="' . esc_attr( $safe_url ) . '">Continue</a></p></body></html>';
            exit;
        }
        wp_safe_redirect( $redirect_url, 303 );
        exit;
    }

    // Basic validation
	if (empty($_POST) || !isset($_POST['post_type'])) {
		wp_die('Invalid form submission');
	}

	// Retrieve form data with defaults
	$well_name = isset($_POST['well_name']) ? sanitize_text_field($_POST['well_name']) : 'UNKWN';
	$post_type = isset($_POST['post_type']) ? sanitize_key($_POST['post_type']) : '';

	// Constrain to expected post types only (prevent tampering) and honor feature toggles
	$watersharing_enabled = (bool) get_option('watersharing_toggle');
	$watertrading_enabled = (bool) get_option('watertrading_toggle');
	$allowed_post_types = array();
	if ( $watersharing_enabled ) {
		$allowed_post_types[] = 'share_supply';
		$allowed_post_types[] = 'share_demand';
	}
	if ( $watertrading_enabled ) {
		$allowed_post_types[] = 'trade_supply';
		$allowed_post_types[] = 'trade_demand';
	}
	$allowed_post_types = apply_filters('watersharing_allowed_post_types', $allowed_post_types);
	if ( ! in_array( $post_type, $allowed_post_types, true ) ) {
		wp_die('Invalid post type');
	}

	// Capability check for creating this post type
	$pto = get_post_type_object( $post_type );
	if ( ! $pto ) {
		wp_die('Invalid post type');
	}
	$required_cap = isset($pto->cap->create_posts) ? $pto->cap->create_posts : ( isset($pto->cap->edit_posts) ? $pto->cap->edit_posts : 'edit_posts' );
	if ( ! current_user_can( $required_cap ) ) {
		wp_die('You are not allowed to create this request');
	}
	
	// Validate required fields
	if (empty($post_type) || empty($well_name)) {
		wp_die('Missing required fields');
	}

	// set the title (use posted start_date if provided; sanitize/normalize; else fallback)
	$pad = $well_name;
	if ( isset($_POST['start_date']) && $_POST['start_date'] !== '' ) {
		$start_date_raw = sanitize_text_field( wp_unslash( $_POST['start_date'] ) );
		$parsed = strtotime( $start_date_raw );
		if ( $parsed ) {
			$date_for_title = gmdate( 'Ymd', $parsed );
		} else {
			// Fallback: strip non-digits and attempt to use first 8 chars as Ymd
			$digits = preg_replace('/[^0-9]/', '', $start_date_raw);
			$date_for_title = ( strlen($digits) >= 8 ) ? substr($digits, 0, 8) : current_time('Ymd');
		}
	} else {
		$date_for_title = current_time('Ymd');
	}
	$timestamp = current_time('His');
	$title = $pad . ' ' . $date_for_title . ' ' . $timestamp;

	$new_post = array(
		'post_title'    => $title,
		'post_status'   => 'publish',
		'post_type'     => $post_type
	);
	
	$post_id = wp_insert_post( $new_post );

	// Check if post creation failed
	if (is_wp_error($post_id) || !$post_id) {
		error_log('Failed to create post: ' . (is_wp_error($post_id) ? $post_id->get_error_message() : 'Unknown error'));
		wp_die('Failed to create request. Please try again.');
	}

	// Note: Post meta is automatically saved by the save_post hook in types-taxonomies.php

	// Create well pad if it doesn't exist
	if( empty( pad_exists_for_user( get_current_user_id(), $well_name ) ) ) {
		//new post for pads
		$new_pad_post = array(
			'post_title'    => $well_name,
			'post_type' => 'well_pad',
			'post_status' => 'publish',
		);
		$pad_post_id = wp_insert_post($new_pad_post);

		// save the user ID on the well pad record
		if ($pad_post_id && !is_wp_error($pad_post_id)) {
			update_post_meta( $pad_post_id, 'userid', get_current_user_id() );
		}
	}

	// Set post status
	if( $post_id ) {
		update_post_meta( $post_id, 'status', 'open' );
	}

	// Determine redirect URL - check form first, then Plugin Settings, and fallback to home() for success; For error, look in form or fallback to home()
	$watersharing_prod_redirect_id = absint( get_option('production_dashboard_page', 0) );
	$watersharing_cons_redirect_id = absint( get_option('consumption_dashboard_page', 0) );
	$watertrading_prod_redirect_id = absint( get_option('wt_production_dashboard_page', 0) );
	$watertrading_cons_redirect_id = absint( get_option('wt_consumption_dashboard_page', 0) );

	// Optional per-form redirect path (relative to site home)
    if(isset($_POST['redirect_success']) && !empty($_POST['redirect_success'])) {
        $redirect_path = wp_parse_url( sanitize_text_field($_POST['redirect_success']), PHP_URL_PATH );
        $redirect_path = ltrim( (string) $redirect_path, '/' );
        $redirect_url = home_url( $redirect_path ? '/' . $redirect_path : '/' );
    } else {
        $redirect_url = home_url();
        switch ($post_type) {
            case 'share_supply':
                $redirect_url = $watersharing_prod_redirect_id ? get_permalink($watersharing_prod_redirect_id) : home_url();
                break;
            case 'share_demand':
                $redirect_url = $watersharing_cons_redirect_id ? get_permalink($watersharing_cons_redirect_id) : home_url();
                break;
            case 'trade_supply':
                $redirect_url = $watertrading_prod_redirect_id ? get_permalink($watertrading_prod_redirect_id) : home_url();
                break;
            case 'trade_demand':
                $redirect_url = $watertrading_cons_redirect_id ? get_permalink($watertrading_cons_redirect_id) : home_url();
                break;
        }
    }
    if(isset($_POST['redirect_failure']) && !empty($_POST['redirect_failure'])) {
        $redirect_failure_path = wp_parse_url( sanitize_text_field($_POST['redirect_failure']), PHP_URL_PATH );
        $redirect_failure_path = ltrim( (string) $redirect_failure_path, '/' );
        $redirect_failure_url = home_url( $redirect_failure_path ? '/' . $redirect_failure_path : '/' );
    }
	
	// Log the redirect for debugging
	error_log("Redirecting to: " . $redirect_url);
	
	// Mark this payload as processed briefly to avoid duplicate posts (key includes payload hash)
    set_transient( $idemp_key, 1, MINUTE_IN_SECONDS );

    // Prepare for redirect
    // Remember mode briefly to guide any mirrored admin-post hits
    $mode = (strpos($post_type, 'trade_') === 0) ? 'watertrading' : 'watersharing';
    @setcookie('ws_last_mode', $mode, time() + 300, '/');

	nocache_headers();
	if ( ob_get_length() ) { ob_end_clean(); }

	if ( headers_sent() ) {
		// HTML fallback with JS + meta refresh + link
		$safe_url = esc_url( $redirect_url );
		echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url=' . esc_attr( $safe_url ) . '"><script>window.location.replace(' . json_encode( $safe_url ) . ');</script></head><body><p>Redirecting... <a href="' . esc_attr( $safe_url ) . '">Continue</a></p></body></html>';
		exit;
	}

	wp_safe_redirect( $redirect_url, 303 );
    exit;
}
add_action('admin_post_create_water_request', 'create_new_post');


function createAndDownloadCsv() {
    // Verify nonce
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'download_csv') ) {
        wp_die('Invalid request');
    }
    // Get CSV data from POST request
    $csv_data = isset($_POST['csv_data']) ? json_decode(stripslashes($_POST['csv_data']), true) : [];

    // Exit if no data is found
    if (empty($csv_data)) {
        exit('No data available');
    }

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="trades_data.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Add CSV headers
    fputcsv($output, ['Date', 'Volume Traded', 'Matched']);

    // Write each row of csv_data
    foreach ($csv_data as $data_row) {
        $matched = $data_row['matched'] ? 'true' : 'false';
        fputcsv($output, [$data_row['date'], $data_row['volume'], $matched]);
    }

    // Close output stream
    fclose($output);
    exit;
}

add_action('wp_ajax_download_csv', 'createAndDownloadCsv');

function my_custom_scripts() {
    // Ensure the script is already enqueued before localizing
    if (wp_script_is('watersharing-scripts', 'enqueued')) {
        // Localize the script with the AJAX URL and nonces
        wp_localize_script('watersharing-scripts', 'my_ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonces' => array(
                'ajax_approval' => wp_create_nonce('ajax_approval'),
                'download_csv' => wp_create_nonce('download_csv'),
                'download_latest_summary' => wp_create_nonce('download_latest_summary')
            )
        ));
    }
}
add_action('wp_enqueue_scripts', 'my_custom_scripts');


function download_latest_summary_file() {
    // Verify nonce
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'download_latest_summary') ) {
        wp_die('Invalid request');
    }
    // Ensure no output is sent
    if (ob_get_length()) {
        ob_end_clean();
    }
    header_remove(); // Clear any headers sent by other processes

    // Get the current user ID
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    // Retrieve the trade_csv value from the request
    $trade_csv = isset($_POST['trade_csv']) ? sanitize_text_field($_POST['trade_csv']) : '';

    // Define the base directory with an absolute path
    $base_dir = realpath(__DIR__ . '/../io/watertrading/import/match-detail/') . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR;

    // Verify the base directory exists
    if (!is_dir($base_dir)) {
        error_log("Base directory does not exist: $base_dir");
        echo json_encode(["error" => "Directory not found"]);
        wp_die();
    }

    // Attempt to find the match file
    $original_file = $base_dir . $trade_csv . '.csv';
    $latestFile = '';

    if (file_exists($original_file)) {
        $latestFile = $original_file;
    } else {
        // Reverse the trade_csv if the match was not initially found
        $parts = explode('-', $trade_csv);
        if (count($parts) === 2) { // Ensure it has the expected format
            $reversed_csv = $parts[1] . '-' . $parts[0];

            $reversed_file = $base_dir . $reversed_csv . '.csv';

            if (file_exists($reversed_file)) {
                $latestFile = $reversed_file; // If the reversed file exists, use it
            }
        }
    }


    // Output the file or an error
    if ($latestFile) {
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv'); // Set content type for CSV
        header('Content-Disposition: attachment; filename=' . basename($latestFile));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($latestFile));
        flush(); // Clear output buffer to prevent additional output
        readfile($latestFile);
        exit;
    } else {
        error_log("No file found in directory: $base_dir");
        echo ("Error: Data not found for this match");
        wp_die();
    }
}


add_action('wp_ajax_download_latest_summary', 'download_latest_summary_file');

// Global guard: redirect bare admin-post.php hits without an action to a safe dashboard
add_action('admin_init', 'ws_guard_empty_admin_post');
function ws_guard_empty_admin_post() {
    // Only handle admin-post.php within admin and when no action is provided
    if ( ! is_admin() ) { return; }
    global $pagenow;
    if ( $pagenow !== 'admin-post.php' ) { return; }
    $action = isset($_REQUEST['action']) ? (string) $_REQUEST['action'] : '';
    if ( $action !== '' ) { return; }

    $watersharing_enabled = (bool) get_option('watersharing_toggle');
    $watertrading_enabled = (bool) get_option('watertrading_toggle');
    $ws_prod = absint( get_option('production_dashboard_page', 0) );
    $ws_cons = absint( get_option('consumption_dashboard_page', 0) );
    $wt_prod = absint( get_option('wt_production_dashboard_page', 0) );
    $wt_cons = absint( get_option('wt_consumption_dashboard_page', 0) );

    // Infer mode: request > cookie > referer
    $mode = isset($_REQUEST['mode']) ? sanitize_key($_REQUEST['mode']) : '';
    if ($mode !== 'watertrading' && $mode !== 'watersharing') {
        $mode = isset($_COOKIE['ws_last_mode']) ? sanitize_key($_COOKIE['ws_last_mode']) : '';
    }
    if ($mode !== 'watertrading' && $mode !== 'watersharing') {
        $ref = wp_get_referer();
        if ($ref) {
            $ref = (string) $ref;
            if ( ($wt_prod && strpos($ref, get_permalink($wt_prod)) !== false) || ($wt_cons && strpos($ref, get_permalink($wt_cons)) !== false) ) {
                $mode = 'watertrading';
            } elseif ( ($ws_prod && strpos($ref, get_permalink($ws_prod)) !== false) || ($ws_cons && strpos($ref, get_permalink($ws_cons)) !== false) ) {
                $mode = 'watersharing';
            }
        }
    }

    $fallback = home_url('/');
    if ($mode === 'watertrading' && $watertrading_enabled) {
        $fallback = $wt_prod ? get_permalink($wt_prod) : ( $wt_cons ? get_permalink($wt_cons) : $fallback );
    } elseif ($watersharing_enabled) {
        $fallback = $ws_prod ? get_permalink($ws_prod) : ( $ws_cons ? get_permalink($ws_cons) : $fallback );
    } elseif ($watertrading_enabled) {
        $fallback = $wt_prod ? get_permalink($wt_prod) : ( $wt_cons ? get_permalink($wt_cons) : $fallback );
    }

    // Redirect with strong fallbacks
    nocache_headers();
    if ( ob_get_length() ) { ob_end_clean(); }
    if ( headers_sent() ) {
        $safe_url = esc_url( $fallback );
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url=' . esc_attr( $safe_url ) . '"><script>window.location.replace(' . json_encode( $safe_url ) . ');</script></head><body><p>Redirecting... <a href="' . esc_attr( $safe_url ) . '">Continue</a></p></body></html>';
        exit;
    }
    wp_safe_redirect( $fallback, 303 );
    exit;
}
