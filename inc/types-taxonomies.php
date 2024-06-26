<?php

/* This file registeres the custom cost types and meta fields for the water sharing plugin */

// create custom POST TYPES
function register_watersharing_posttypes() {

	// Production Records for Water Requests
	$suplabels = array(
		'name'			=> __('Production'),
		'singular_name'	=> __('Water Production'),
		'edit_item'		=> __('Edit Water Production Request'),
		'update_item'	=> __('Update Water Production Request'),
		'add_new_item'	=> __('Add New Water Production Request')
	);

	$supargs = array(
		'labels'		=> $suplabels,
		'public'		=> true,
		'show_in_menu'	=> false,
		'has_archive'	=> true,
		'menu_icon'		=> 'dashicons-location',
		'menu_position'	=> 5,
		'hierarchical'	=> true,
		'supports'		=> array('title'),
		'rewrites'		=> array('slug' => 'production', 'with_front' => true),
		'publicly_queryable' => false
	);

	register_post_type('water_supply', $supargs);

	// Consumption Recordes for Water Requests
	$demlabels = array(
		'name'			=> __('Consumption'),
		'singular_name'	=> __('Water Consumption'),
		'edit_item'		=> __('Edit Water Consumption Request'),
		'update_item'	=> __('Update Water Consumption Request'),
		'add_new_item'	=> __('Add New Water Consumption Request')
	);

	$demargs = array(
		'labels'		=> $demlabels,
		'public'		=> true,
		'show_in_menu'	=> false,
		'has_archive'	=> false,
		'menu_icon'		=> 'dashicons-location',
		'menu_position'	=> 6,
		'hierarchical'	=> true,
		'supports'		=> array('title'),
		'rewrites'		=> array('slug' => 'consumption', 'with_front' => true),
		'publicly_queryable' => false
	);

	register_post_type('water_demand', $demargs);

	// Match Lookup Records for Water Sharing
	$mrelabels = array(
		'name'			=> __('Match Lookup'),
		'singular_name'	=> __('Match Lookup'),
		'edit_item'		=> __('Edit Match Lookup'),
		'update_item'	=> __('Update Match Lookup'),
		'add_new_item'	=> __('Add New Match Lookup')
	);

	$mreargs = array(
		'labels'		=> $mrelabels,
		'public'		=> true,
		'show_in_menu'	=> false,
		'has_archive'	=> false,
		'menu_icon'		=> 'dashicons-location',
		'menu_position'	=> 6,
		'hierarchical'	=> true,
		'supports'		=> array('title'),
		'rewrites'		=> array('slug' => 'matches', 'with_front' => false),
		'publicly_queryable' => false
	);

	register_post_type('matched_requests', $mreargs);


	// Well pad records for Water Sharing
	$welllabels = array(
		'name'			=> __('Well Pads'),
		'singular_name'	=> __('Well Pad'),
		'edit_item'		=> __('Edit Well Pad'),
		'update_item'	=> __('Update Well Pad'),
		'add_new_item'	=> __('Add Well Pad')
	);

	$wellargs = array(
		'labels'		=> $welllabels,
		'public'		=> true,
		'show_in_menu'	=> false,
		'has_archive'	=> false,
		'menu_icon'		=> 'dashicons-location',
		'menu_position'	=> 6,
		'hierarchical'	=> true,
		'supports'		=> array('title'),
		'rewrites'		=> array('slug' => 'wellpads', 'with_front' => false),
		'publicly_queryable' => false
	);

	register_post_type('well_pad', $wellargs);
}
add_action('init', 'register_watersharing_posttypes');


// expose users as authors to the water request records
function register_watersharing_userrecords() {
	add_post_type_support( 'water_supply', 'author' );
	add_post_type_support( 'water_demand', 'author' );
}
add_action('init', 'register_watersharing_userrecords');


// create meta fields for water sharing records
function register_watersharing_metafields() {

	// create the meta box for water requests
	add_meta_box( 'waterRequestsFields', 'Water Requests Fields', 'watersharing_requests_fields', array('water_supply', 'water_demand'), 'normal', 'high' );

	// create the meta box for well pads
	add_meta_box( 'wellPadFields', 'Well Pad Fields', 'watersharing_wellpad_fields', 'well_pad', 'normal', 'high' );

	// create the meta box for match lookups
	add_meta_box( 'matchLookupFields', 'Match Lookup Fields', 'watersharing_match_fields', 'matched_requests', 'normal', 'high' );
}
add_action('add_meta_boxes', 'register_watersharing_metafields');


// function to build out individual meta fields for the water request records
function watersharing_requests_fields( $post ) {
	buildMetaField( 'select', 'status', 'Record Status', get_post_meta( $post->ID, 'status', true ), array( 'open' => 'Open', 'pending' => 'Pending', 'matched' => 'Matched', 'closed' => 'Closed' ) );
	buildMetaField( 'input', 'well_name', 'Well Name', get_post_meta( $post->ID, 'well_name', true ), 'text' );
	buildMetaField( 'input', 'latitude', 'Latitude', get_post_meta( $post->ID, 'latitude', true ), 'text' );
	buildMetaField( 'input', 'longitude', 'Longitude', get_post_meta( $post->ID, 'longitude', true ), 'text' );
	buildMetaField( 'input', 'start_date', 'Start Date', get_post_meta( $post->ID, 'start_date', true ), 'date' );
	buildMetaField( 'input', 'end_date', 'End Date', get_post_meta( $post->ID, 'end_date', true ), 'date' );
	buildMetaField( 'input', 'rate_bpd', 'Rate (BPD)', get_post_meta( $post->ID, 'rate_bpd', true ), 'text' );
	buildMetaField( 'input', 'transport_radius', 'Transport Radius', get_post_meta( $post->ID, 'transport_radius', true ), 'text' );
	buildMetaField( 'input', 'water_quality', 'Water Quality', get_post_meta( $post->ID, 'water_quality', true ), 'text' );

	$matchlookup = [];
	$matches = get_posts(array( 'numberposts' => -1,  'post_type' => 'matched_requests', 'fields' => 'ids' ) );
	if( $matches ) {
		foreach( $matches as $match ) {
			$matchlookup[$match] = get_the_title( $match );
		}
	}
	buildMetaField( 'select', 'match_request', 'Match Lookup Record', get_post_meta( $post->ID, 'match_request', true ), $matchlookup );
}

// function to build out individual meta fields for the well pad records
function watersharing_wellpad_fields( $post ) {
	buildMetaField( 'input', 'latitude', 'Location Latitude', get_post_meta( $post->ID, 'latitude', true ), 'text' );
	buildMetaField( 'input', 'longitude', 'Location Longitude', get_post_meta( $post->ID, 'longitude', true ), 'text' );
	buildMetaField( 'input', 'userid', 'User ID', get_post_meta( $post->ID, 'userid', true ), 'text' );
}

// function to build out individual meta fields for match lookup records
function watersharing_match_fields( $post ) {

	$producerlookup = [];
	$producers = get_posts( array( 'numberposts' => -1, 'post_type' => 'water_supply', 'post_status' => 'publish', 'fields' => 'ids' ) );
	if( $producers ) {
		foreach( $producers as $producer ) {
			$producerlookup[$producer] = get_the_title( $producer );
		}
	}
	buildMetaField( 'select', 'producer_request', 'Production Request Record', get_post_meta( $post->ID, 'producer_request', true ), $producerlookup );
	buildMetaField( 'select', 'producer_approval', 'Production Request Approval Status', get_post_meta( $post->ID, 'producer_approval', true ), array('none' => 'None', 'approve' => 'Approved', 'decline' => 'Decline' ) );

	$consumerlookup = [];
	$consumers = get_posts(array('numberposts' => -1, 'post_type' => 'water_demand', 'post_status' => 'publish', 'fields' => 'ids'));
	if ($consumers) {
		foreach ($consumers as $consumer) {
			$consumerlookup[$consumer] = get_the_title($consumer);
		}
	}
	buildMetaField('select', 'consumption_request', 'Consumption Request Record', get_post_meta($post->ID, 'consumption_request', true), $consumerlookup);
	buildMetaField('select', 'consumption_approval', 'Consumption Request Approval Status', get_post_meta($post->ID, 'consumption_approval', true), array('none' => 'None', 'approve' => 'Approved', 'decline' => 'Decline'));

	buildMetaField('input', 'matched_distance', 'Matched Distance', get_post_meta($post->ID, 'matched_distance', true), 'text');
	buildMetaField('input', 'matched_rate', 'Matched Rate', get_post_meta($post->ID, 'matched_rate', true), 'text');
	buildMetaField( 'input', 'disposal_avoided', 'Disposal Avoided', get_post_meta( $post->ID, 'disposal_avoided', true ), 'text' );
	buildMetaField( 'select', 'match_status', 'Match Status', get_post_meta( $post->ID, 'match_status', true ), array( 'open' => 'Open', 'pending' => 'Pending', 'approved' => 'Approved', 'decline' => 'Decline' ) );
}

//function to build out custom fields for the user
function watersharing_user_fields( $user ) {
	$company = get_user_meta( $user->ID, 'company_name', true );
	buildMetaField( 'input', 'company_name', 'Company Name', esc_attr( $company ), 'text' );

	$phone_number_value = get_user_meta( $user->ID, 'phone_number', true );
	buildMetaField('input', 'phone_number', 'Contact Phone Number', esc_attr( $phone_number_value ), 'text' );
}
add_action('show_user_profile', 'watersharing_user_fields');
add_action('edit_user_profile', 'watersharing_user_fields');

// arrays of each post types fields to perform sanitization
$custom_metafields = array(
	'water_supply' => array(
		'status' 			=> 'sanitize_text_field',
		'well_name' 		=> 'sanitize_text_field',
		'latitude' 			=> 'sanitize_text_field',
		'longitude' 		=> 'sanitize_text_field',
		'start_date' 		=> 'sanitize_text_field',
		'end_date' 			=> 'sanitize_text_field',
		'rate_bpd' 			=> 'sanitize_text_field',
		'transport_radius' 	=> 'sanitize_text_field',
		'water_quality' 	=> 'sanitize_text_field',
		'match_lookup' 		=> 'sanitize_text_field',
		'decline_set' 		=> 'sanitize_text_field'
	),
	'water_demand' => array(
		'status' 			=> 'sanitize_text_field',
		'well_name' 		=> 'sanitize_text_field',
		'latitude' 			=> 'sanitize_text_field',
		'longitude' 		=> 'sanitize_text_field',
		'start_date' 		=> 'sanitize_text_field',
		'end_date' 			=> 'sanitize_text_field',
		'rate_bpd' 			=> 'sanitize_text_field',
		'transport_radius' 	=> 'sanitize_text_field',
		'water_quality' 	=> 'sanitize_text_field',
		'match_lookup' 		=> 'sanitize_text_field',
		'decline_set' 		=> 'sanitize_text_field'
	),

	'well_pad' => array(
		'latitude' 			=> 'sanitize_text_field',
		'longitude' 		=> 'sanitize_text_field',
		'userid' 			=> 'sanitize_text_field'
	),

	'matched_requests' => array(
		'producer_request' 		=> 'sanitize_text_field',
		'producer_approval' 	=> 'sanitize_text_field',
		'consumption_request'	=> 'santitize_text_field',
		'consumption_approval' 	=> 'santitize_text_field',
		'matched_distance' 		=> 'santitize_text_field',
		'matched_rate' 			=> 'sanitize_text_field',
		'disposal_avoided' 		=> 'santize_text_field',
		'match_status' 			=> 'sanitize_text_field',
	)
);

// Single save function that handles all the post types
function watersharing_save_metafields( $post_id, $post ) {

	global $custom_metafields;
	$post_type = $post->post_type;

	// Check if the current post type has custom fields defined
	if( array_key_exists( $post_type, $custom_metafields ) ) {

		$metafields = $custom_metafields[ $post_type ];

		foreach( $metafields as $field => $sanitizer ) {
			if( isset( $_POST[$field] ) ) {
				update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[$field] ) ) );
			}
		}
	}
}
add_action( 'save_post', 'watersharing_save_metafields', 10, 2 );


//dynamically save user fields
function watersharing_save_userfields( $user_id ) {
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	}

	update_user_meta( $user_id, 'phone_number', $_POST['phone_number'] );
	update_user_meta( $user_id, 'company_name', $_POST['company_name'] );

}
add_action( 'personal_options_update', 'watersharing_save_userfields' );
add_action( 'edit_user_profile_update', 'watersharing_save_userfields' );
