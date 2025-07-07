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

	// Retrieve form data
	$well_name = $_POST['well_name'];
	$post_type = $_POST['post_type'];

	// set the title
	$post_type_prefix = ($post_type === 'share_supply') ? 'PRD' : 'CSM';
	$author_name = wp_get_current_user()->display_name;
	(isset($_POST['well_name'])) ? $pad = $_POST['well_name'] : $pad = 'UNKWN';
	(isset($_POST['start_date'])) ? $date = $_POST['start_date'] : $date = current_time('mdY');
	$timestamp = current_time('His');
	$title = $pad . ' ' . $date . ' ' . $timestamp;


	$new_post = array(
		'post_title'    => $title,
		'post_status'   => 'publish',
		'post_type'     => $post_type
	);
	$post_id = wp_insert_post( $new_post );


	if( empty( pad_exists_for_user( get_current_user_id(), $well_name ) ) ) {

		//new post for pads
		$new_pad_post = array(
			'post_title'    => $well_name,
			'post_type' => 'well_pad',
			'post_status' => 'publish',
		);
		$pad_post_id = wp_insert_post($new_pad_post);

		// save the user ID on the well pad record
		update_post_meta( $pad_post_id, 'userid', get_current_user_id() );
	}

	if( $post_id ) {
		update_post_meta( $post_id, 'status', 'open' );
	}

	if( $post_type === 'share_supply' ) {
		$production_dashboard_page_id = get_option('production_dashboard_page');

		if ($production_dashboard_page_id) {
		    $redirect_url = get_permalink($production_dashboard_page_id);
		} else {
		    $redirect_url = home_url();
		}
	}
	elseif($post_type === 'share_demand'){
		$consumption_dashboard_page_id = get_option('consumption_dashboard_page');

		if ($consumption_dashboard_page_id) {
		    $redirect_url = get_permalink($consumption_dashboard_page_id);
		} else {
		    $redirect_url = home_url();
		}
	}
	elseif( $post_type === 'trade_supply' ) {
		$wt_production_dashboard_page_id = get_option('wt_production_dashboard_page');

		if ($wt_production_dashboard_page_id) {
		    $redirect_url = get_permalink($wt_production_dashboard_page_id);
		} else {
		    $redirect_url = home_url();
		}
	}
	elseif($post_type === 'trade_demand'){
		$wt_consumption_dashboard_page_id = get_option('wt_consumption_dashboard_page');

		if ($wt_consumption_dashboard_page_id) {
		    $redirect_url = get_permalink($wt_consumption_dashboard_page_id);
		} else {
		    $redirect_url = home_url();
		}
	}
	
    wp_redirect( $redirect_url );
    exit;
}
add_action('admin_post_create_water_request', 'create_new_post');
// add_action('admin_post_nopriv_create_water_request', 'create_new_post');

function createAndDownloadCsv() {
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
add_action('wp_ajax_nopriv_download_csv', 'createAndDownloadCsv');

function my_custom_scripts() {
    // Ensure the script is already enqueued before localizing
    if (wp_script_is('watersharing-scripts', 'enqueued')) {
        // Localize the script with the AJAX URL
        wp_localize_script('watersharing-scripts', 'my_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }
}
add_action('wp_enqueue_scripts', 'my_custom_scripts');


function download_latest_summary_file() {
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
add_action('wp_ajax_nopriv_download_latest_summary', 'download_latest_summary_file');



?>
