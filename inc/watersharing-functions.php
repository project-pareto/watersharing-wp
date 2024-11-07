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
	$user_id = get_current_user_id();
	$dir = __DIR__ . '/../io/watertrading/import/match-detail/' . $user_id;
    // $dir = __DIR__ . '/../io/watertrading/import/match-detail';
    $files = glob($dir . '/*');
    $latestFile = '';

    if ($files) {
        // Get the latest file based on modification time
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $latestFile = $files[0];
    }

    if ($latestFile) {
		// Set headers to force download
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream'); // Adjust to specific file type if needed
		header('Content-Disposition: attachment; filename=' . basename($latestFile));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($latestFile));
		flush(); // Clear output buffer to prevent additional output
		readfile($latestFile);
		exit;
	} else {
		echo json_encode(["error" => "File not found"]);
		wp_die();
	}
    
    exit;
}

add_action('wp_ajax_download_latest_summary', 'download_latest_summary_file');
add_action('wp_ajax_nopriv_download_latest_summary', 'download_latest_summary_file');


?>
