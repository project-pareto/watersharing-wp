<?php

/* This file registeres the custom cost types and meta fields for the water sharing plugin */

// create custom POST TYPES
function register_watermanagement_posttypes() {

	// Production Records for Water Share Requests
	$ws_suplabels = array(
		'name'			=> __('Production'),
		'singular_name'	=> __('Share Water Production'),
		'edit_item'		=> __('Edit Water Production Request'),
		'update_item'	=> __('Update Water Production Request'),
		'add_new_item'	=> __('Add New Water Production Request')
	);

	$ws_supargs = array(
		'labels'		=> $ws_suplabels,
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

	// Production Records for Water Trade Requests
	$wt_suplabels = array(
		'name'			=> __('Production'),
		'singular_name'	=> __('Trade Water Production'),
		'edit_item'		=> __('Edit Water Production Request'),
		'update_item'	=> __('Update Water Production Request'),
		'add_new_item'	=> __('Add New Water Production Request')
	);

	$wt_supargs = array(
		'labels'		=> $wt_suplabels,
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
	

	register_post_type('share_supply', $ws_supargs);
	register_post_type('trade_supply', $wt_supargs);

	// Consumption Recordes for Water Sharing Requests
	$ws_demlabels = array(
		'name'			=> __('Consumption'),
		'singular_name'	=> __('Share Water Consumption'),
		'edit_item'		=> __('Edit Water Consumption Request'),
		'update_item'	=> __('Update Water Consumption Request'),
		'add_new_item'	=> __('Add New Water Consumption Request')
	);

	$ws_demargs = array(
		'labels'		=> $ws_demlabels,
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

	$wt_demlabels = array(
		'name'			=> __('Consumption'),
		'singular_name'	=> __('Share Water Consumption'),
		'edit_item'		=> __('Edit Water Consumption Request'),
		'update_item'	=> __('Update Water Consumption Request'),
		'add_new_item'	=> __('Add New Water Consumption Request')
	);

	$wt_demargs = array(
		'labels'		=> $wt_demlabels,
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

	register_post_type('share_demand', $ws_demargs);
	register_post_type('trade_demand', $wt_demargs);

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

	register_post_type('matched_shares', $mreargs); 
	register_post_type('matched_trades', $mreargs);


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
add_action('init', 'register_watermanagement_posttypes');


// expose users as authors to the water request records
function register_watermanagement_userrecords() {
	add_post_type_support( 'share_supply', 'author' );
	add_post_type_support( 'trade_supply', 'author' );
	add_post_type_support( 'share_demand', 'author' );
	add_post_type_support( 'trade_demand', 'author' );
}
add_action('init', 'register_watermanagement_userrecords');


// create meta fields for water sharing records
function register_watersharing_metafields() {

	// create the meta box for water requests
	add_meta_box( 'waterRequestsFields', 'Water Requests Fields', 'watersharing_requests_fields', array('share_supply', 'share_demand'), 'normal', 'high' );

	// create the meta box for well pads
	add_meta_box( 'wellPadFields', 'Well Pad Fields', 'watermanagement_wellpad_fields', 'well_pad', 'normal', 'high' );

	// create the meta box for match lookups
	add_meta_box( 'matchLookupFields', 'Match Lookup Fields', 'watersharing_match_fields', 'matched_shares', 'normal', 'high' );
}
add_action('add_meta_boxes', 'register_watersharing_metafields');

// create meta fields for water sharing records
function register_watertrading_metafields() {

	// create the meta box for water requests
	add_meta_box( 'tradeRequestsFields', 'Trade Requests Fields', 'watertrading_requests_fields', array('trade_supply', 'trade_demand'), 'normal', 'high' );

	// create the meta box for well pads
	add_meta_box( 'wellPadFields', 'Well Pad Fields', 'watermanagement_wellpad_fields', 'well_pad', 'normal', 'high' ); 

	// create the meta box for match lookups
	add_meta_box( 'matchLookupFields', 'Match Lookup Fields', 'watertrading_match_fields', 'matched_trades', 'normal', 'high' );
}
add_action('add_meta_boxes', 'register_watertrading_metafields');

// function to build out individual meta fields for the water sharing request records
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
	$matches = get_posts(array( 'numberposts' => -1,  'post_type' => 'matched_shares', 'fields' => 'ids' ) );
	if( $matches ) {
		foreach( $matches as $match ) {
			$matchlookup[$match] = get_the_title( $match );
		}
	}
	buildMetaField( 'select', 'share_request', 'Match Lookup Record', get_post_meta( $post->ID, 'share_request', true ), $matchlookup );
}

// function to build out individual meta fields for the water trading request records
function watertrading_requests_fields( $post ) {
	buildMetaField( 'select', 'status', 'Record Status', get_post_meta( $post->ID, 'status', true ), array( 'open' => 'Open', 'pending' => 'Pending', 'matched' => 'Matched', 'closed' => 'Closed' ) );
	buildMetaField( 'input', 'well_name', 'Well Name', get_post_meta( $post->ID, 'well_name', true ), 'text' );
	
	echo "<div class='meta-field-group-inline'>";
	buildMetaField( 'input', 'latitude', 'Latitude', get_post_meta( $post->ID, 'latitude', true ), 'text' );
	buildMetaField( 'input', 'longitude', 'Longitude', get_post_meta( $post->ID, 'longitude', true ), 'text' );
	echo "</div>";

	echo "<div class='meta-field-group-inline'>";
	buildMetaField( 'input', 'start_date', 'Start Date', get_post_meta( $post->ID, 'start_date', true ), 'date' );
	buildMetaField( 'input', 'end_date', 'End Date', get_post_meta( $post->ID, 'end_date', true ), 'date' );
	echo "</div>";

	echo "<div class='meta-field-group-inline'>";
	buildMetaField( 'input', 'rate_bpd', 'Rate (BPD)', get_post_meta( $post->ID, 'rate_bpd', true ), 'text' );
	echo "</div>";

	// buildMetaField( 'input', 'site_compatibility', 'Site Compatibility', get_post_meta($post->ID, 'site_compatibility', true), array( 'trucks' => 'Trucks', 'pipelines' => 'Pipelines') );
	buildMetaField( 'input', 'can_accept_trucks', 'I Can Accept trucks', get_post_meta($post->ID, "can_accept_trucks", true), 'checkbox');
	buildMetaField( 'input', 'can_accept_layflats', 'I Can Accept pipes', get_post_meta($post->ID, "can_accept_layflats", true), 'checkbox');


	buildMetaField( 'input', 'bid_type', 'Bid Type', get_post_meta($post->ID, 'bid_type', true), array( 'Willing to pay' => 'Willing to pay up to', 'Want to be paid' => 'Paid at least') );
	buildMetaField( 'input', 'bid_amount', 'Bid Amount', get_post_meta( $post->ID, 'bid_amount', true), 'text');
	buildMetaField( 'select', 'bid_units', 'Bid Units', get_post_meta( $post->ID, 'bid_units', true), array( 'USD/day' => 'USD/day', 'USD/bbl.day' => 'USD/bbl.day') );
	
	echo "<div class='meta-field-group-inline'>";
	buildMetaField( 'input', 'truck_transport_radius', 'Truck Transport Radius', get_post_meta( $post->ID, 'truck_transport_radius', 'true'), 'text');
	buildMetaField( 'input', 'truck_transport_bid', 'Truck Transport Bid',get_post_meta( $post->ID, 'truck_transport_bid', 'true'), 'text');
	buildMetaField( 'input', 'truck_capacity', 'Truck Capacity',get_post_meta( $post->ID, 'truck_capacity', 'true'), 'text');
	echo "</div>";
	
	echo "<div class='meta-field-group-inline'>";	
	buildMetaField( 'input', 'layflats_transport_radius', 'Layflats Transport Radius',get_post_meta( $post->ID, 'layflats_transport_radius', 'true'), 'text');
	buildMetaField( 'input', 'layflats_transport_bid', 'Layflats Transport Bid',get_post_meta( $post->ID, 'layflats_transport_bid', 'true'), 'text');
	buildMetaField( 'input', 'layflats_capacity', 'Layflats Capacity',get_post_meta( $post->ID, 'layflats_capacity', 'true'), 'text');
	echo "</div>";

	//Quality Disclosures

	echo "<label class = 'input-label-group'>TSS</label>";
	echo "<div class='meta-field-group-inline'>";
	// buildMetaField( 'select', 'tss_measure_units', 'Units', get_post_meta( $post->ID, 'tss_measure_units', true), array('ppm') );
	buildMetaField( 'select', 'tss_limit', 'Limit', get_post_meta( $post->ID, 'tss_limit', true), array( 'gt' => 'Greater Than Value', 'lt' => 'Less Than Value') );
	buildMetaField( 'input', 'tss_measure_value', 'Value (ppm)', get_post_meta( $post->ID, 'tss_measure_value', true), 'text');
	echo "</div>";
	
	echo "<label class = 'input-label-group'>TDS</label>";
	echo "<div class='meta-field-group-inline'>";
	// buildMetaField( 'select', 'tds_measure_units', 'Units', get_post_meta( $post->ID, 'tds_measure_units', true), array('ppm') );
	buildMetaField( 'select', 'tds_limit', 'Limit', get_post_meta( $post->ID, 'tds_limit', true), array( 'gt' => 'Greater Than Value', 'lt' => 'Less Than Value'));
	buildMetaField( 'input', 'tds_measure_value', 'Value (ppm)', get_post_meta( $post->ID, 'tds_measure_value', true), 'text');
	echo "</div>";

	echo "<label class = 'input-label-group'>Chloride</label>";
	echo "<div class='meta-field-group-inline'>";
	// buildMetaField( 'select', 'chloride_measure_units', 'Units', get_post_meta( $post->ID, 'chloride_measure_units', true), array('ppm') );
	buildMetaField( 'select', 'chloride_limit', 'Limit', get_post_meta( $post->ID, 'chloride_limit', true), array( 'gt' => 'Greater Than Value', 'lt' => 'Less Than Value') );
	buildMetaField( 'input', 'chloride_measure_value', 'Value (ppm)', get_post_meta( $post->ID, 'chloride_measure_value', true), 'text');
	echo "</div>";

	echo "<label class = 'input-label-group'>Barium</label>";
	echo "<div class='meta-field-group-inline'>";
	// buildMetaField( 'select', 'barium_measure_units', 'Units', get_post_meta( $post->ID, 'barium_measure_units', true), array('ppm') );
	buildMetaField( 'select', 'barium_limit', 'Limit', get_post_meta( $post->ID, 'barium_limit', true), array( 'gt' => 'Greater Than Value', 'lt' => 'Less Than Value') );
	buildMetaField( 'input', 'barium_measure_value', 'Value (ppm)', get_post_meta( $post->ID, 'barium_measure_value', true), 'text');
	echo "</div>";

	echo "<label class = 'input-label-group'>Calcium</label>";
	echo "<div class='meta-field-group-inline'>";
	// buildMetaField( 'select', 'calciumcarbonate_measure_units', 'Units', get_post_meta( $post->ID, 'calciumcarbonate_measure_units', true), array('ppm') );
	buildMetaField( 'select', 'calciumcarbonate_limit', 'Limit', get_post_meta( $post->ID, 'calciumcarbonate_limit', true), array( 'gt' => 'Greater Than Value', 'lt' => 'Less Than Value') );
	buildMetaField( 'input', 'calciumcarbonate_measure_value', 'Value (ppm)', get_post_meta( $post->ID, 'calciumcarbonate_measure_value', true), 'text');
	echo "</div>";

	echo "<label class = 'input-label-group'>Iron</label>";
	echo "<div class='meta-field-group-inline'>";
	// buildMetaField( 'select', 'iron_measure_units', 'Units', get_post_meta( $post->ID, 'iron_measure_units', true), array('ppm') );
	buildMetaField( 'select', 'iron_limit', 'Limit', get_post_meta( $post->ID, 'iron_limit', true), array( 'gt' => 'Greater Than Value', 'lt' => 'Less Than Value') );
	buildMetaField( 'input', 'iron_measure_value', 'Value (ppm)', get_post_meta( $post->ID, 'iron_measure_value', true), 'text');
	echo "</div>";

	echo "<label class = 'input-label-group'>Boron</label>";
	echo "<div class='meta-field-group-inline'>";
	// buildMetaField( 'select', 'boron_measure_units', 'Units', get_post_meta( $post->ID, 'boron_measure_units', true), array('ppm') );
	buildMetaField( 'select', 'boron_limit', 'Limit', get_post_meta( $post->ID, 'boron_limit', true), array( 'gt' => 'Greater Than Value', 'lt' => 'Less Than Value') );
	buildMetaField( 'input', 'boron_measure_value', 'Value (ppm)', get_post_meta( $post->ID, 'boron_measure_value', true), 'text');
	echo "</div>";

	echo "<label class = 'input-label-group'>Hydrogen Sulfide</label>";
	echo "<div class='meta-field-group-inline'>";
	// buildMetaField( 'select', 'hydrogensulfide_measure_units', 'Units', get_post_meta( $post->ID, 'hydrogensulfide_measure_units', true), array('ppm') );
	buildMetaField( 'select', 'hydrogensulfide_limit', 'Limit', get_post_meta( $post->ID, 'hydrogensulfide_limit', true), array( 'gt' => 'Greater Than Value', 'lt' => 'Less Than Value') );
	buildMetaField( 'input', 'hydrogensulfide_measure_value', 'Value (ppm)', get_post_meta( $post->ID, 'hydrogensulfide_measure_value', true), 'text');
	echo "</div>";

	echo "<label class = 'input-label-group'>Norm</label>";
	echo "<div class='meta-field-group-inline'>";
	// buildMetaField( 'select', 'norm_measure_units', 'Units', get_post_meta( $post->ID, 'norm_measure_units', true), array('ppm') );
	buildMetaField( 'select', 'norm_limit', 'Limit', get_post_meta( $post->ID, 'norm_limit', true), array( 'gt' => 'Greater Than Value', 'lt' => 'Less Than Value') );
	buildMetaField( 'input', 'norm_measure_value', 'Value (ppm)', get_post_meta( $post->ID, 'norm_measure_value', true), 'text');
	echo "</div>";

	$matchlookup = [];
	$matches = get_posts(array( 'numberposts' => -1,  'post_type' => 'matched_trades', 'fields' => 'ids' ) );
	if( $matches ) {
		foreach( $matches as $match ) {
			$matchlookup[$match] = get_the_title( $match );
		}
	}
	buildMetaField( 'select', 'trade_request', 'Match Lookup Record', get_post_meta( $post->ID, 'trade_request', true ), $matchlookup );
}

// function to build out individual meta fields for the well pad records
function watermanagement_wellpad_fields( $post ) {
	buildMetaField( 'input', 'latitude', 'Location Latitude', get_post_meta( $post->ID, 'latitude', true ), 'text' );
	buildMetaField( 'input', 'longitude', 'Location Longitude', get_post_meta( $post->ID, 'longitude', true ), 'text' );
	buildMetaField( 'input', 'userid', 'User ID', get_post_meta( $post->ID, 'userid', true ), 'text' );
}

// function to build out individual meta fields for sharing match lookup records
function watersharing_match_fields( $post ) {

	$share_producerlookup = [];
	$producers = get_posts( array( 'numberposts' => -1, 'post_type' => 'share_supply', 'post_status' => 'publish', 'fields' => 'ids' ) );
	if( $producers ) {
		foreach( $producers as $producer ) {
			$share_producerlookup[$producer] = get_the_title( $producer );
		}
	}

	//Register Sharing Production Fields 
	buildMetaField( 'select', 'producer_request', 'Production Request Record', get_post_meta( $post->ID, 'producer_request', true ), $share_producerlookup );
	buildMetaField( 'select', 'producer_approval', 'Production Request Approval Status', get_post_meta( $post->ID, 'producer_approval', true ), array('none' => 'None', 'approve' => 'Approved', 'decline' => 'Decline' ) );

	$consumerlookup = [];
	$consumers = get_posts(array('numberposts' => -1, 'post_type' => 'share_demand', 'post_status' => 'publish', 'fields' => 'ids'));
	if ($consumers) {
		foreach ($consumers as $consumer) {
			$consumerlookup[$consumer] = get_the_title($consumer);
		}
	}

	//Register Sharing Consumption Fields
	buildMetaField('select', 'consumption_request', 'Consumption Request Record', get_post_meta($post->ID, 'consumption_request', true), $consumerlookup);
	buildMetaField('select', 'consumption_approval', 'Consumption Request Approval Status', get_post_meta($post->ID, 'consumption_approval', true), array('none' => 'None', 'approve' => 'Approved', 'decline' => 'Decline'));

	buildMetaField('input', 'matched_distance', 'Matched Distance', get_post_meta($post->ID, 'matched_distance', true), 'text');
	buildMetaField('input', 'matched_rate', 'Matched Rate', get_post_meta($post->ID, 'matched_rate', true), 'text');
	buildMetaField( 'input', 'disposal_avoided', 'Disposal Avoided', get_post_meta( $post->ID, 'disposal_avoided', true ), 'text' );
	buildMetaField( 'select', 'match_status', 'Match Status', get_post_meta( $post->ID, 'match_status', true ), array( 'open' => 'Open', 'pending' => 'Pending', 'approved' => 'Approved', 'decline' => 'Decline' ) );
}

// function to build out individual meta fields for trading match lookup records
function watertrading_match_fields( $post ) {

	$trade_producerlookup = [];
	$producers = get_posts( array( 'numberposts' => -1, 'post_type' => 'trade_supply', 'post_status' => 'publish', 'fields' => 'ids' ) );
	if( $producers ) {
		foreach( $producers as $producer ) {
			$trade_producerlookup[$producer] = get_the_title( $producer );
		}
	}

	buildMetaField( 'select', 'producer_trade', 'Production Trade Request Record', get_post_meta( $post->ID, 'producer_trade', true ), $trade_producerlookup );
	buildMetaField( 'select', 'producer_trade_approval', 'Production Trade Request Approval Status', get_post_meta( $post->ID, 'producer_trade_approval', true ), array('none' => 'None', 'approve' => 'Approved', 'decline' => 'Decline' ) );

	$trade_consumerlookup = [];
	$consumers = get_posts(array('numberposts' => -1, 'post_type' => 'trade_demand', 'post_status' => 'publish', 'fields' => 'ids'));
	if ($consumers) {
		foreach ($consumers as $consumer) {
			$trade_consumerlookup[$consumer] = get_the_title($consumer);
		}
	}

	buildMetaField('select', 'consumption_trade', 'Consumption Trade Request Record', get_post_meta($post->ID, 'consumption_trade', true), $trade_consumerlookup);
	buildMetaField('select', 'consumption_trade_approval', 'Consumption Trade Request Approval Status', get_post_meta($post->ID, 'consumption_trade_approval', true), array('none' => 'None', 'approve' => 'Approved', 'decline' => 'Decline'));

	buildMetaField('input', 'matched_distance', 'Matched Distance', get_post_meta($post->ID, 'matched_distance', true), 'text');
	buildMetaField('input', 'matched_rate', 'Matched Rate', get_post_meta($post->ID, 'matched_rate', true), 'text');
	buildMetaField( 'input', 'disposal_avoided', 'Disposal Avoided', get_post_meta( $post->ID, 'disposal_avoided', true ), 'text' );
	buildMetaField('input', 'total_volume', 'Total Volume', get_post_meta($post->ID, 'total_volume', true), 'text');
	buildMetaField('input', 'total_value', 'Total Value', get_post_meta($post->ID, 'total_value', true), 'text');
	buildMetaField( 'select', 'match_status', 'Match Status', get_post_meta( $post->ID, 'match_status', true ), array( 'open' => 'Open', 'pending' => 'Pending', 'approved' => 'Approved', 'decline' => 'Decline' ) );
}

//function to build out custom fields for the user
function watermanagement_user_fields( $user ) {
	$company = get_user_meta( $user->ID, 'company_name', true );
	buildMetaField( 'input', 'company_name', 'Company Name', esc_attr( $company ), 'text' );

	$phone_number_value = get_user_meta( $user->ID, 'phone_number', true );
	buildMetaField('input', 'phone_number', 'Contact Phone Number', esc_attr( $phone_number_value ), 'text' );
}
add_action('show_user_profile', 'watermanagement_user_fields');
add_action('edit_user_profile', 'watermanagement_user_fields');

// arrays of each post types fields to perform sanitization
$custom_metafields = array(
	'share_supply' => array(
		'status' 			=> 'sanitize_text_field',
		'well_name' 		=> 'sanitize_text_field',
		'latitude' 			=> 'sanitize_text_field',
		'longitude' 		=> 'sanitize_text_field',
		'start_date' 		=> 'sanitize_text_field',
		'end_date' 			=> 'sanitize_text_field',
		'rate_bpd' 			=> 'sanitize_text_field',
		'transport_radius' 	=> 'sanitize_text_field',
		'water_quality' 	=> 'sanitize_text_field',
		'share_request' 	=> 'sanitize_text_field',
		'decline_set' 		=> 'sanitize_text_field'
	),
	'trade_supply' => array(
		'status' 			=> 'sanitize_text_field',
		'well_name' 		=> 'sanitize_text_field',
		'latitude' 			=> 'sanitize_text_field',
		'longitude' 		=> 'sanitize_text_field',
		'start_date' 		=> 'sanitize_text_field',
		'end_date' 			=> 'sanitize_text_field',
		'rate_bpd' 			=> 'sanitize_text_field',
		'transport_radius' 	=> 'sanitize_text_field',
		'water_quality' 	=> 'sanitize_text_field',
		'can_accept_trucks' => 'sanitize_text_field',
		'can_accept_layflats' => 'sanitize_text_field',
		'can_accept_trucks' => 'sanitize_text_field',
		'can_accept_pipes'  => 'sanitize_text_field',
 		'trade_request' 	=> 'sanitize_text_field',
		'decline_set' 		=> 'sanitize_text_field',
		'bid_type' 		    => 'sanitize_text_field',
		'bid_amount' 		=> 'sanitize_text_field',
		'bid_units' 		=> 'sanitize_text_field',
		'can_deliver' 		=> 'sanitize_text_field',
		'truck_transport_radius' 	=> 'sanitize_text_field',
		'truck_transport_bid' 		=> 'sanitize_text_field',
		'truck_capacity' 	=> 'sanitize_text_field',
		'layflats_transport_radius' => 'sanitize_text_field',
		'layflats_transport_bid'    => 'sanitize_text_field',
		'layflats_capacity' => 'sanitize_text_field',
		'quality_disclosures' 		=> 'sanitize_text_field',
		'tss_measure_units'    => 'sanitize_text_field',
		'tss_limit'    => 'sanitize_text_field',
		'tss_measure_value'    => 'sanitize_text_field',
		'tds_measure_units'     => 'sanitize_text_field',
		'tds_limit'   => 'sanitize_text_field',
		'tds_measure_value'     => 'sanitize_text_field',
		'chloride_measure_units'     => 'sanitize_text_field',
		'chloride_limit'   => 'sanitize_text_field',
		'chloride_measure_value'     => 'sanitize_text_field',
		'barium_measure_units'     => 'sanitize_text_field',
		'barium_limit'   => 'sanitize_text_field',
		'barium_measure_value'     => 'sanitize_text_field',
		'calciumcarbonate_measure_units'     => 'sanitize_text_field',
		'calciumcarbonate_limit'   => 'sanitize_text_field',
		'calciumcarbonate_measure_value'     => 'sanitize_text_field',
		'iron_measure_units'     => 'sanitize_text_field',
		'iron_limit'   => 'sanitize_text_field',
		'iron_measure_value'     => 'sanitize_text_field',
		'boron_measure_units'     => 'sanitize_text_field',
		'boron_limit'   => 'sanitize_text_field',
		'boron_measure_value'     => 'sanitize_text_field',
		'hydrogensulfide_measure_units'     => 'sanitize_text_field',
		'hydrogensulfide_limit'   => 'sanitize_text_field',
		'hydrogensulfide_measure_value'     => 'sanitize_text_field',
		'norm_measure_units'     => 'sanitize_text_field',
		'norm_limit'   => 'sanitize_text_field',
		'norm_measure_value'     => 'sanitize_text_field',
	),
	'share_demand' => array(
		'status' 			=> 'sanitize_text_field',
		'well_name' 		=> 'sanitize_text_field',
		'latitude' 			=> 'sanitize_text_field',
		'longitude' 		=> 'sanitize_text_field',
		'start_date' 		=> 'sanitize_text_field',
		'end_date' 			=> 'sanitize_text_field',
		'rate_bpd' 			=> 'sanitize_text_field',
		'transport_radius' 	=> 'sanitize_text_field',
		'water_quality' 	=> 'sanitize_text_field',
		'share_request' 	=> 'sanitize_text_field',
		'decline_set' 		=> 'sanitize_text_field'
	),
	'trade_demand' => array(
		'status' 			=> 'sanitize_text_field',
		'well_name' 		=> 'sanitize_text_field',
		'latitude' 			=> 'sanitize_text_field',
		'longitude' 		=> 'sanitize_text_field',
		'start_date' 		=> 'sanitize_text_field',
		'end_date' 			=> 'sanitize_text_field',
		'rate_bpd' 			=> 'sanitize_text_field',
		'transport_radius' 	=> 'sanitize_text_field',
		'water_quality' 	=> 'sanitize_text_field',
		'can_accept_trucks' => 'sanitize_text_field',
		'can_accept_layflats' => 'sanitize_text_field',
		'can_accept_trucks' => 'sanitize_text_field',
		'can_accept_pipes' 	=> 'sanitize_text_field',
 		'trade_request' 	=> 'sanitize_text_field',
		'decline_set' 		=> 'sanitize_text_field',
		'bid_type' 		    => 'sanitize_text_field',
		'bid_amount' 		=> 'sanitize_text_field',
		'bid_units' 		=> 'sanitize_text_field',
		'can_deliver' 		=> 'sanitize_text_field',
		'truck_transport_radius' 	=> 'sanitize_text_field',
		'truck_transport_bid' 		=> 'sanitize_text_field',
		'truck_capacity' 	=> 'sanitize_text_field',
		'layflats_transport_radius' => 'sanitize_text_field',
		'layflats_transport_bid'    => 'sanitize_text_field',
		'layflats_capacity' => 'sanitize_text_field',
		'quality_disclosures' 	=> 'sanitize_text_field',
		'tss_measure_units'     => 'sanitize_text_field',
		'tss_limit'   => 'sanitize_text_field',
		'tss_measure_value'     => 'sanitize_text_field',
		'tds_measure_units'     => 'sanitize_text_field',
		'tds_limit'   => 'sanitize_text_field',
		'tds_measure_value'     => 'sanitize_text_field',
		'chloride_measure_units'     => 'sanitize_text_field',
		'chloride_limit'   => 'sanitize_text_field',
		'chloride_measure_value'     => 'sanitize_text_field',
		'barium_measure_units'  => 'sanitize_text_field',
		'barium_limit'=> 'sanitize_text_field',
		'barium_measure_value'  => 'sanitize_text_field',
		'calciumcarbonates_measure_units'     => 'sanitize_text_field',
		'calciumcarbonates_limit'   => 'sanitize_text_field',
		'calciumcarbonates_measure_value'     => 'sanitize_text_field',
		'iron_measure_units'    => 'sanitize_text_field',
		'iron_limit'  => 'sanitize_text_field',
		'iron_measure_value'    => 'sanitize_text_field',
		'boron_measure_units'   => 'sanitize_text_field',
		'boron_limit' => 'sanitize_text_field',
		'boron_measure_value'   => 'sanitize_text_field',
		'hydrogensulfide_measure_units'     => 'sanitize_text_field',
		'hydrogensulfide_limit'   => 'sanitize_text_field',
		'hydrogensulfide_measure_value'     => 'sanitize_text_field',
		'norm_measure_units'    => 'sanitize_text_field',
		'norm_limit'  => 'sanitize_text_field',
		'norm_measure_value'    => 'sanitize_text_field',
	),

	'well_pad' => array(
		'latitude' 			=> 'sanitize_text_field',
		'longitude' 		=> 'sanitize_text_field',
		'userid' 			=> 'sanitize_text_field'
	),

	'matched_shares' => array(
		'producer_request' 		=> 'sanitize_text_field',
		'producer_approval' 	=> 'sanitize_text_field',
		'consumption_request'	=> 'santitize_text_field',
		'consumption_approval' 	=> 'santitize_text_field',
		'matched_distance' 		=> 'santitize_text_field',
		'matched_rate' 			=> 'sanitize_text_field',
		'disposal_avoided' 		=> 'santize_text_field',
		'total_value'			=> 'sanitize_text_field',
		'total_volume'			=> 'sanitize_text_field',
		'match_status' 			=> 'sanitize_text_field',
	),

	'matched_trades' => array(
		'producer_trade'        => 'sanitize_text_field',
		'producer_trade_approval' 	=> 'sanitize_text_field',
		'consumption_trade'	=> 'santitize_text_field',
		'consumption_trade_approval' 	=> 'santitize_text_field',
		'matched_distance' 		=> 'santitize_text_field',
		'matched_rate' 			=> 'sanitize_text_field',
		'disposal_avoided' 		=> 'santize_text_field',
		'total_value'			=> 'sanitize_text_field',
		'total_volume'			=> 'sanitize_text_field',
		'match_status' 			=> 'sanitize_text_field',
	)
);

// Single save function that handles all the post types
function watermanagement_save_metafields( $post_id, $post ) {

	global $custom_metafields;
	$post_type = $post->post_type;

	// Check if the current post type has custom fields defined
	if( array_key_exists( $post_type, $custom_metafields ) ) {

		$metafields = $custom_metafields[ $post_type ];

		foreach( $metafields as $field => $sanitizer ) {
			if( isset( $_POST[$field] ) ) {
				// Save the field if it exists in $_POST
				update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[$field] ) ) );
			} elseif ( $field == 'can_accept_trucks' || $field == 'can_accept_layflats' ) {
				// If the field is a checkbox and not set in $_POST, set it to '0'
				update_post_meta( $post_id, $field, '0' );
			}
		}
	}
}
add_action( 'save_post', 'watermanagement_save_metafields', 10, 2 );


//dynamically save user fields
function watermanagement_save_userfields( $user_id ) {
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	}

	update_user_meta( $user_id, 'phone_number', $_POST['phone_number'] );
	update_user_meta( $user_id, 'company_name', $_POST['company_name'] );

}
add_action( 'personal_options_update', 'watermanagement_save_userfields' );
add_action( 'edit_user_profile_update', 'watermanagement_save_userfields' );
