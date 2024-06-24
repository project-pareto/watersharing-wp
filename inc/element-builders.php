<?php

//dynamic function to create the form fields
function buildMetaField( $type = "", $name = "", $label = "", $value = "", $options = "" )
{

	$html = "<div class='custom-meta-field'>";
	if( !empty( $label ) ) {
		$label = esc_html($label) . ':';
		$html .= "<label class='input-label' for='$name'>$label</label>";
	}

	( !empty( $name ) ) ? $name = esc_html( $name ) : "";

	switch( $type ) {
		case 'input':
			$html .= "<input type='$options' name='$name' id='$name' class='meta-box-input' value='$value'>";
			break;

		case 'select':
			$html .= "<select name='$name' id='$name' class='meta-box-select'>";

			if( !empty ( $options ) && is_array( $options ) ) {
				$html .= "<option value=''>-- Please select --</option>";
				foreach( $options as $option_value => $label ) {
					$selected = selected( $value, $option_value, false );
					$html .= "<option value='$option_value' $selected>$label</option>";
				}
			}

			$html .= "</select>";
			break;

		case 'textarea':
			$html .= "<textarea name='$name' id='$name' class='meta-box-textarea'>$value</textarea>";
			break;

		default:
			break;
	}

	$html .= "</div>";

	echo $html;

}


// function to build out request form fields
function buildFormField( $id = "", $label = "", $type = 'text', $required = "", $placeholder = "", $acf_key = "", $class = "", $readOnly = '' ) {
	if ($type) {
		switch ($type) {
			case 'text':
				$input = "<input type='text' class='form-control$class' id='$id' name='$id' placeholder='$placeholder' $required $readOnly>";
				break;

			case 'number':
				$input = "<input type='number' class='form-control$class' id='$id' name='$id' placeholder='$placeholder' $required $readOnly>";
				break;

			case 'date':
				$input = "
					<div class='watersharing-row no-margin-bottom'>
						<div class='watersharing-date-col start-dp'>
							<input type='date' class='form-control placeholder-toggle start-dp-wrapper inital$class' id='start_date' name='start_date' $required>
						</div>
						<div class='watersharing-date-sep'>—</div>
						<div class='watersharing-date-col end-dp'>
							<input type='date' class='form-control placeholder-toggle end-dp-wrapper inital$class' id='end_date' name='end_date' $required disabled>
						</div>
					</div>
				";
				break;

			case 'pads':
				$pads = new WP_Query(
					array(
						'no_found_watersharing-watersharing-rows'			=> false,
						'update_post_meta_cache'	=> false,
						'update_post_term_cache'	=> false,
						'post_type'				=> 'well_pad',
						'post_status'				=> 'publish',
						'posts_per_page'			=> -1,
						'fields'					=> 'ids',
						'meta_key'          		=> 'userid',
						'meta_value' 				=> get_current_user_id()
					)
				);
				$options = "<option value='newPad'>Create A New Pad</option>";
				if( !empty( $pads->posts ) ) {
					foreach( $pads->posts as $pad ) {
						$title = get_the_title( $pad );
						$lat = get_post_meta( $pad, 'latitude', true );
						$long = get_post_meta( $pad, 'longitude', true );

						$options .= "<option value='$pad' data-lat='$lat' data-long='$long' data-title='$title'>$title</option>";
					}
				}

				$input = "<select class='form-select placeholder-toggle inital$class' id='$id' name='$id' $required>$options</select>";
				break;

			default:
				$input = "";
		}
	}

	($required === 'required') ? $label_required = "<span class='required'>*</span>" : $label_required = "";

	$html = "
		<div class='watersharing-row'>
			<label for='$id' class='watersharing-form-label'>$label$label_required</label>
			<div class='watersharing-input-col'>
				$input
			</div>
		</div>
	";

	return $html;
}


// function to build out request form
function buildRequestForm($type = "", $title = "") {
	$html = "";

	// setup the fields for the form
	$well_pad = buildFormField( 'well_pad', 'Well Pads', 'pads', '', 'Create A New Pad' );
	$well_name = buildFormField('well_name', 'Pad Name', 'text', 'required', 'Pad Name');
	$latitude = buildFormField('latitude', 'Latitude', 'text', 'required', 'Latitude', '', ' geoentry',);
	$longitude = buildFormField('longitude', 'Longitude', 'text', 'required', 'Longitude', '', ' geoentry',);
	$dates = buildFormField('date_range', 'Date Range', 'date', 'required');
	$rate = buildFormField('rate_bpd', 'Rate (bpd)', 'number', 'required', 'Rate in barrels per day');
	($type === 'water_supply') ? $transport = buildFormField('transport_radius', 'Transport Radius (mi)', 'number', 'required', 'Range in miles') : $transport = "";
	$water_quality = buildFormField('water_quality', 'Water Quality', 'text', '');

	$action = esc_url( admin_url('admin-post.php') );
	$form = "
	<form action='$action' method='POST' id='create-post-form' class='watersharing-form'>
		<input type='hidden' name='action' value='create_water_request'>
		<input type='hidden' name='redirect_success' value='/dashboard'>
		<input type='hidden' name='redirect_failure' value='/404'>
		$well_pad
		$well_name
		$latitude
		$longitude
		$dates
		$rate
		$transport
		<div class='watersharing-section-break'>
			<div class='watersharing-info-text'>Optional fields:</div>
		</div>
		$water_quality
		<input type='hidden' name='post_type' value='$type'>
		<div class='watersharing-row'>
			<label class='watersharing-form-label'></label>
			<div class='watersharing-input-col'>
				<button type='submit' class='watersharing-submit-button'>Submit</button>
			</div>
		</div>
	</form>
	";


	$html = "
		<div class='watersharing-card-wrap'>
			<div class='watersharing-card-inner'>
				<div class='watersharing-card-header'>
					<span class='watersharing-card-title'>$title</span>
				</div>
				<div class='watersharing-card-body'>
				$form
				</div>
			</div>
		</div>
	";


	return $html;
}


// function to lookup matches from the match_request record
function lookupMatches( $post_id = '', $post_type = '' ) {
	( $post_type === 'water_supply' ) ? $post_type = 'producer_request' : $post_type = 'consumption_request';

	// query for the matches
	$query = new WP_Query(
		array(
			'no_found_rows'				=> false,
			'update_post_meta_cache'	=> false,
			'update_post_term_cache'	=> false,
			'post_type'					=> 'matched_requests',
			'posts_per_page'			=> -1,
			'fields'					=> 'ids',
			'meta_query'				=> array(
				'relation'		=> 'AND',
				array(
					'key'		=> $post_type,
					'value'		=> $post_id,
					'compare'	=> 'LIKE'
				),
				array(
					'key'		=> 'match_status',
					'value'		=> 'decline',
					'compare'	=> 'NOT IN'
				)
			)
		)
	);

	if ( $query->posts ) {
		return $query->posts;
	}
	wp_reset_postdata();
}


// function to build out a table of requests for a user
function buildRequestTable( $type = '' ) {
	$rows = "";

	// query for the requsts
	$query = new WP_Query(
		array(
			'no_found_rows'				=> false,
			'update_post_meta_cache'	=> false,
			'update_post_term_cache'	=> false,
			'author'					=> get_current_user_id(),
			'post_type'					=> $type,
			'post_status'				=> 'publish',
			'posts_per_page'			=> -1,
			'fields'					=> 'ids',
			'meta_key'          		=> 'status',
			'orderby'           		=> 'meta_value',
			'order'            			=> 'DESC'
		)
	);

	$data = $query->get_posts();

	// iterate through each row
	if( !empty( $data ) ) {
		$number = 1;
		foreach( $data as $post ) {
			( get_post_meta( $post, 'well_name', true ) ) ? $well = get_post_meta( $post, 'well_name', true ) : $well = "";
			( get_post_meta( $post, 'status', true ) ) ? $status = "<span class='status-" . get_post_meta( $post, 'status', true ) . "'>" . get_post_meta( $post, 'status', true ) . "</span>" : $status = "";

			$start = get_post_meta( $post, 'start_date', true );
			( $start ) ? $start = DateTime::createFromFormat('Y-m-d', $start)->format('m/d/Y') : "";
			$end = get_post_meta( $post, 'end_date', true );
			( $end ) ? $end = DateTime::createFromFormat('Y-m-d', $end)->format('m/d/Y') : "";
			$range = "$start - $end";

			( get_post_meta( $post, 'rate_bpd', true ) ) ? $rate = get_post_meta( $post, 'rate_bpd', true ) : $rate = "";

			// check for matches
			$match_rows = "";
			$match_prompt = "<span class='matches no-match'><i class='fa-solid fa-bullseye'></i>Not Found</span>";
			$toggle_disabled = " disabled";

			$lookups = lookupMatches( $post, $type );
			if( $lookups ) {
				$count = 0;

				// build out the match record view
				foreach( $lookups as $lookup ) {
					$count++;

					( $type === 'water_supply' ) ? $user_interaction = 'producer_approval' : $user_interaction = 'consumption_approval';
					$user_action = get_post_meta( $lookup, $user_interaction, true );
					$avoided = get_post_meta( $lookup, 'disposal_avoided', true );
					$fullfilled = get_post_meta( $lookup, 'matched_rate', true );
					$lookup_distance = get_post_meta( $lookup, 'matched_distance', true );
					$lookup_status = get_post_meta( $lookup, 'match_status', true );
					( $type === 'water_supply' ) ? $match_type = 'consumption_request' : $match_type = 'producer_request';
					( $type === 'water_supply' ) ? $match_post_type = 'water_demand' : $match_post_type = 'water_supply';

					$match_record = get_post_meta( $lookup, $match_type, true );
					$match_id = $match_record;
					$match_op = get_the_author_meta( $match_record, 'company_name', true );

					$match_start = get_post_meta( $match_record, 'start_date', true );
					( $match_start ) ? $match_start = DateTime::createFromFormat('Y-m-d', $match_start)->format('m/d/Y') : "";
					$match_end = get_post_meta( $match_record, 'end_date', true );
					( $match_end ) ? $match_end = DateTime::createFromFormat('Y-m-d', $match_end)->format('m/d/Y') : "";
					$match_range = "$match_start - $match_end";

					$approve_actions = "
							<a class='watersharing-match-action approval approve-action' onclick='void(0)' data-lookup='$lookup' data-parent='$post' data-match='$match_id' data-match-type='$match_post_type' data-action='approve' data-table='$type-RequestTable'><i class='fa-solid fa-thumbs-up'></i> Approve</a>
							<a class='watersharing-match-action approval decline-action' onclick='void(0)' data-lookup='$lookup' data-parent='$post' data-match='$match_id' data-match-type='$match_post_type' data-action='decline' data-table='$type-RequestTable'><i class='fa-solid fa-thumbs-down'></i> Decline</a>
						";

					if ($user_action) {
						if ($user_action === 'approve') {
							$approve_actions = "
									<a class='watersharing-match-action approval approve-action checked'><i class='fa-solid fa-thumbs-up'></i> Approve</a>
									<a class='watersharing-match-action approval decline-action disabled'><i class='fa-solid fa-thumbs-down'></i> Decline</a>
								";
						}

						if ($user_action === 'decline') {
							$approve_actions = "
									<a class='watersharing-match-action approval approve-action disabled'><i class='fa-solid fa-thumbs-up'></i> Approve</a>
									<a class='watersharing-match-action approval decline-action checked'><i class='fa-solid fa-thumbs-down'></i> Decline</a>
								";
						}
					}

					// check if match is approved
					if ($lookup_status === 'approved') {

						$name = get_userdata( get_post_field( 'post_author', $match_record ) )->first_name . ' ' . get_userdata( get_post_field( 'post_author', $match_record ) )->last_name;
						$phone = get_user_meta( get_post_field( 'post_author', $match_record ), 'phone_number', true );
						$email = get_userdata( get_post_field( 'post_author', $match_record ) )->user_email;

						$contact = "
								<div class='watersharing-col-third watersharing-contact'>
									<span class='heading'>Contact Information</span>
									<span>$name</<span>
									<span><a href='tel:$phone'>$phone</a></span>
									<span><a href='mailto:$email'>$email</a></span>
								</div>
							";
					} else {
						$contact = "
								<div class='watersharing-col-third watersharing-no-contact'>
									<div class='no-contact'>
										<span>Approval Pending</span>
									</div>
								</div>
							";
					}

					( $type === 'water_demand' ) ? $avoid_label = "Sourced Water Saved (bbl)" : $avoid_label = "Disposal Avoided (bbl)"

					$match_rows .= "
							<div>
								<div class='watersharing-row watersharing-match-block'>
									<div class='watersharing-match-detail'>
										<div class='watersharing-row'>
											<div class='watersharing-col watersharing-match-col'>
												<div class='watersharing-row'>
													<div class='watersharing-col-half'>
														<strong>Matched Operator:</strong> $match_op
													</div>
													<div class='watersharing-col-half'>
														$approve_actions
													</div>
												</div>
											</div>
											<div class='watersharing-col-half watersharing-match-col'>
												<strong>Dates:</strong> $match_range
											</div>
											<div class='watersharing-col-half watersharing-match-col'>
												<strong>Distance (miles):</strong> $lookup_distance
											</div>
											<div class='watersharing-col-half watersharing-match-col'>
												<strong>Rate (bpd):</strong> $fullfilled
											</div>
											<div class='watersharing-col-half watersharing-match-col'>
												<strong>$avoid_label:</strong> $avoided
											</div>
										</div>
									</div>
									$contact
								</div>
							</div>
						";

					$match_prompt = "<span class='matches matched'><i class='fa-solid fa-bullseye'></i><strong>$count</strong> Matches Found</span>";
					$toggle_disabled = "";
				}
			}

			( isset( get_post_meta( $post, 'status', true )['value'] ) && get_post_meta( $post, 'status', true ) === 'closed' ) ? $row_class = " closed" : $row_class = "";
			$rows .= "
					<tr class='watersharing-request-row$row_class' data-row-number='row-$number'>
						<td class='align-middle hide-on-mobile'><input class='watersharing-input-row' type='checkbox' name='post_ids[]' value='$post' data-watershare-type='$type' /></td>
						<td class='align-middle'><strong class='label show-on-mobile'>Pad Name: </strong>$well</td>
						<td class='align-middle'><strong class='label show-on-mobile'>Date Range: </strong>$range</td>
						<td class='align-middle'><strong class='label show-on-mobile'>Status: </strong>$status</td>
						<td class='align-middle'><strong class='label show-on-mobile'>Rate (bbp): </strong>$rate</td>
						<td class='align-middle'><strong class='label show-on-mobile'>Match Found? </strong>$match_prompt</td>
						<td class='align-middle text-center'>
							<a class='watersharing-match-action toggle-row$toggle_disabled'>
								<i class='fa fa-chevron-right'></i>
							</a>
						</td>
					</tr>
					<tr class='watersharing-request-detail collapse' data-row-number='row-$number'>
						<td class='align-middle d-none'><input class='watersharing-input-row' type='checkbox' name='post_ids[]' value='$post' data-watershare-type='$type' /></td>
						<td class='align-middle d-none'><strong class='label show-on-mobile'>Pad Name: </strong>$well</td>
						<td class='align-middle d-none'><strong class='label show-on-mobile'>Date Range: </strong>$range</td>
						<td class='align-middle d-none'><strong class='label show-on-mobile'>Status: </strong>$status</td>
						<td class='align-middle d-none'><strong class='label show-on-mobile'>Rate (bbp): </strong>$rate</td>
						<td class='align-middle d-none'><strong class='label show-on-mobile'>Match Found? </strong>$match_prompt</td>
						<td class='align-middle d-none'></td>
						<td colspan='7'>
							$match_rows
						</td>
					</tr>
				";

			$number++;
		}
	}

	wp_reset_postdata();

	// build out the table
	$action = admin_url('admin-post.php');

	$table = "
		<form id='$type-status-form' method='post' action='$action?action=change_post_status'>
			<table class='watersharing-table tablesorter' id='$type-RequestTable'>
				<thead>
					<tr>
						<th class='nosort'></th>
						<th>Pad Name</th>
						<th>Date Range</th>
						<th>Status</th>
						<th>Rate (bpd)</th>
						<th>Match Found?</th>
						<th class='nosort' width='50px' data-sort='false'></th>
					</tr>
				</thead>
				<tbody>
					$rows
				</tbody>
			</table>

			<div class='hide-on-mobile'>
				<select name='post_action' id='post_action' class='user-select'>
					<option value='' selected hidden disabled>Manage Selection</option>
					<option value='close'>Close Request(s)</option>
					<option value='delete'>Delete Request(s)</option>
				</select>
				<input id='$type-status-submit' type='submit' name='submit' class='watersharing-submit-button post-status-submit' value='Apply' disabled/>
			</div>
		</form>
	";

	return $table;
}

?>
