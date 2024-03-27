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
	$post_type_prefix = ($post_type === 'water_supply') ? 'PRD' : 'CSM';
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

	if( $post_type === 'water_supply' ) {
		$production_dashboard_page_id = get_option('production_dashboard_page');

		if ($production_dashboard_page_id) {
		    $redirect_url = get_permalink($production_dashboard_page_id);
		} else {
		    $redirect_url = home_url();
		}
	}
	else {
		$consumption_dashboard_page_id = get_option('consumption_dashboard_page');

		// Get the permalink of the page using its ID
		if ($consumption_dashboard_page_id) {
		    $redirect_url = get_permalink($consumption_dashboard_page_id);
		} else {
		    $redirect_url = home_url();
		}
	}

    wp_redirect( $redirect_url );
    exit;
}
add_action('admin_post_create_water_request', 'create_new_post');

?>
