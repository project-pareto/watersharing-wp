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
    // Verify nonce
    if ( ! isset($_POST['watersharing_nonce']) || ! wp_verify_nonce($_POST['watersharing_nonce'], 'create_water_request') ) {
        wp_die('Invalid request');
    }
	
	// Basic validation
	if (empty($_POST) || !isset($_POST['post_type'])) {
		wp_die('Invalid form submission');
	}

	// Retrieve form data with defaults
	$well_name = isset($_POST['well_name']) ? sanitize_text_field($_POST['well_name']) : 'UNKWN';
	$post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';
	
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
	
    wp_safe_redirect( $redirect_url );
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



?>
