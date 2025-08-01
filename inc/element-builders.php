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
            if ($options === 'checkbox') {
                $checked = $value ? 'checked' : '';
                $html .= "
                <div class='meta-box-checkbox'> 
                    <input type='checkbox' name='$name' id='$name' class='meta-box-input' value='1' $checked>
                </div>";
            } elseif (is_array($options) && !empty($options)) {
				$html .= "<div class=meta-box-radio>";
				foreach ($options as $option_value => $option_label) {
                    $checked = checked($value, $option_value, false);
					$option_label = ucwords($option_label);
                    $html .= "
                    <div class='meta-radio-select'>
                        <label>
                            <input type='radio' name='$name' id='$name-$option_value' value='$option_value' $checked> $option_label
                        </label>
                    </div>";
                }
				$html .= "</div>";
            } else {
                $html .= "<input type='$options' name='$name' id='$name' class='meta-box-input' value='$value'>";
            }
            break;

        case 'select':
            $html .= "<select name='$name' id='$name' class='meta-box-select'>";

            if( !empty ( $options ) && is_array( $options ) ) {
                $html .= "<option value=''>-- Please select --</option>";
                foreach( $options as $option_value => $label ) {
                    $selected = selected( $value, $option_value, false );
                    $html .= "<option value=$option_value $selected>$label</option>";
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
$first_accordion = false;
function buildFormField( $id = "", $label = "", $type = 'text', $required = "", $parameters = "", $placeholder = "",$acf_key = "", $class = "", $readOnly = '', $dataset = [] ) {
	if ($type) {
		// cfdump($type, 'TYPE');
		switch ($type) {
			case 'text':
				$input = "<input type='text' class='form-control $class' id='$id' name='$id' placeholder='$placeholder' $required $readOnly>";
				break;

			case 'number':
				$input = "<input type='number' class='form-control $class' id='$id' name='$id' placeholder='$placeholder' $required $readOnly $parameters>";
				break;
			
			case 'date':
				$input = "
					<div class='watersharing-row columns-grow-w-dash'>
						<div class='watersharing-date-col start-dp'>
							<input type='date' class='form-control placeholder-toggle start-dp-wrapper inital$class' id='start_date' name='start_date' title='Start Date' $required>
						</div>
						<div class='watersharing-date-sep'>—</div>
						<div class='watersharing-date-col end-dp'>
							<input type='date' class='form-control placeholder-toggle end-dp-wrapper inital$class' id='end_date' name='end_date' title='End Date' $required disabled>
						</div>
					</div>
				";
				break;

			case 'multi_column':
				$input = "";
				if(!empty($dataset)){
					foreach($dataset as $set){
						$input .= "<div class='watersharing-input-col $class'>" . buildFormField(
							$set['id'],
							$set['label'],
							$set['type'],
							$set['required'],
							$set['parameters'],
							$set['placeholder'],
							$set['acf_key'],
							$set['class'],
							$set['readonly'],
							isset($set['dataset']) ? $set['dataset'] : [] 
						) . "</div>"; 
					}
					
				}
				$input = "
					<div class='watersharing-row no-margin-bottom $class'>
						$input
					</div>
				";
				break;
			
			case 'radio':
				$input = "";
				if(!empty($dataset)){
					$id_lower = strtolower(str_replace(' ', '_', $id));
					foreach($dataset as $set){
						$set_lower = strtolower(str_replace(' ', '_', $set));
						$radio_label = ucwords($set);
						$input .= "
							<div class='meta-radio-select'>
								<input type='radio' name='$id_lower' id='$set_lower' value='$set' required='$required' class='radio-button'>
									<label for='$set_lower'>
										$radio_label
									</label>	
							</div>
							";
					}
				}
				$input = "
					<div class='custom-meta-field $class'>
						<div class=meta-box-radio>
							$input
						</div>
					</div>
				";
				break;

			case 'checkbox':
				$id_spaced = ucfirst(strtolower(str_replace('_', ' ', $id)));
				$input = "
					<div class='meta-box-checkbox'> 
						<input type='checkbox' name='$id' id='$id' class='meta-box-input checkbox' value='1'>
						<label for='$id' class='spot150'>
							$label
						</label>	
					</div>
					";
					
				break;
			
			case 'select':
				$input = "";
				if(!empty($dataset)){
					foreach($dataset as $set){
						//Logic For < or > to make selections more readable
						if($set == "lt" || $set == "gt"){
							$constraint = ($set == "lt") ? "Less than": "Greater than";
							$input .= "<option value=$set>$constraint</option>";
						}
						else{
							$input .= "<option value=$set>$set</option>";
						}
					}
				}
				$input = "
					<select name='$id' id='$id' class='user-select $class'>
						<option value='' selected hidden disabled>--Select--</option>
						$input
					</select>
				";
				break;
			
			case 'accordion':
				global $first_accordion;
				$id_lower = strtolower(str_replace(' ', '-', $id));
				if($first_accordion==false) {
					$first_accordion = $id_lower;
				}
				$button_class = $first_accordion == $id_lower ? 'accordion-button' : 'accordion-button collapsed';
				$state_class = $first_accordion == $id_lower ? 'show show-initial' : '';
				// cfdump($state_class);
				$aria_expanded = $first_accordion == $id_lower ? 'true' : 'false';

				$a_accordion_intros = [
					'Quality Disclosures' => 'Use this optional section to declare quality properties associated with your request; either quality associated with water you have or requirements for water you need. You may populate some or all of the fields provided. Doing so can help refine the matches you receive but is not required.',
				];
				$intro_text = $a_accordion_intros[$id] ?? '';
				$intro_text_markup = $intro_text ? "<div class='accordion-intro-text'>$intro_text</div>" : '';
			
				$input = "";
				if(!empty($dataset)){
					foreach($dataset as $set){
						$input .= "$set";
					}
				}
				$input = "
					<div class='watersharing-row'>			
						<div class='watersharing-input-col accordion'>
							<div class='qd-accordion'>
								<div class='accordion' id='$id_lower'>
									<div class='accordion-item'>
										<label id='$id_lower-label' class='watersharing-form-label no-right-padding accordion'>
										<button id='$id_lower-button' class='$button_class' type='button' data-bs-toggle='collapse' data-bs-target='#collapse-$class' aria-expanded='$aria_expanded' aria-controls='collapse-$class'>
											<strong>$label</strong>
										</button>
										</label>
										<div id='collapse-$class' class='accordion-collapse collapse $state_class' aria-labelledby='$id_lower-label'>
											$intro_text_markup
											<div class='accordion-body'>
												$input
											</div>
										</div>
									</div>
								</div>
							</div>
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
				$options = "<option value='newPad'>Create A New Site</option>";
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

	str_contains($class,'toggle') ? $checkbox = "<input type='checkbox' name='$id-checkbox' id='$id-checkbox' class='meta-box-input checkbox $class' value='1'>": $checkbox = "";
	if (strpos($label, 'Can Provide Trucks') !== false) {
		// cfdump($label, 'Label');
		// cfdump($type, 'Type');
		// cfdump($class, 'class');
		// cfdump($input, 'input');
	}
	$add_label = (!empty($label) && $type != "accordion" && $type != "checkbox");
	($add_label) ?
	$html = "
		<div class='watersharing-row'>
			<label class='watersharing-form-label spot255'>$checkbox<p>$label$label_required</p></label>
			<div class='watersharing-input-col'>
				$input
			</div>
		</div>
	": $html = 
		"
			<div class='watersharing-input-col no-label'>
				$input
			</div>
		";

	return $html;
}

function qdBuilder($names = []){
	$qd = "";
	foreach($names as $name){
		$name_lower = strtolower(str_replace(' ', '', $name));
		$qd_array[] = ["id" => $name_lower."_limit", "label" => "", "type" => "select", "required" => "", "parameters" => "","placeholder" => "", "acf_key" => "", "class" => "", "readonly" => "", "dataset" => ["gt", "lt"]];
		$qd_array[] = ["id" => $name_lower."_measure_value", "label" => "", "type" => "number", "required" => "", "parameters" => " min='0'", "placeholder" => "Value (ppm)", "acf_key" => "", "class" => "watertrading blocks input", "readonly" => ""];
		$qd .= buildFormField('quality_disclosure', $name, 'multi_column', '', '', '', '', 'two-col', '', $qd_array);
		$qd_array = [];
	}
	return $qd;
}

// function to build out request form
function buildRequestForm($type = "", $title = "") {
	$html = "";

	$supply_demand = ($type === 'share_supply' || $type === 'trade_supply') ? 'supply' : 'demand';

	#Trade Specific Fields
	$trade = ($type === 'trade_supply' || $type === 'trade_demand');


	// Set up the fields for the form
	$well_pad = buildFormField('well_pad', '<span tabindex="0" data-tt-length="xlarge" data-tt-pos="up-left" aria-label="Select an existing Custody Transfer Point (CTP) or define a new one. Newly created CTPs will be saved for future reference. A CTP can be a well location, a pipeline hub, or other location where water may be exchanged."><i class="fa-solid fa-circle-info"></i></span> CTP (Wellpad, Pipeline Riser, etc.)', 'pads', '', 'Create A New Site' );
	$well_name = buildFormField('well_name', '<span tabindex="0" data-tt-length="xlarge" data-tt-pos="up-left" aria-label="Name of the CTP. This name will be used to identify this point for you."><i class="fa-solid fa-circle-info"></i></span> CTP Identifier', 'text', 'required', '', 'Site Name');
	$input_array = [];	
	$input_array[] = ["id" => "latitude", "label" => "", "type" => "number", "required" => "required", "placeholder" => "Latitude", "parameters" => "step='any' title='Latitude'", "acf_key" => "", "class" => "", "readonly" => ""];
	$input_array[] = ["id" => "longitude", "label" => "", "type" => "number", "required" => "required", "placeholder" => "Longitude", "parameters" => "step='any' title='Longitude'", "acf_key" => "", "class" => "", "readonly" => ""];
	$latlong = buildFormField("coordinates", "<span tabindex='0' data-tt-length='xlarge' data-tt-pos='up-left' aria-label='Enter latitude and longitude coordinates here. AquaTrade uses the WGS 84 coordinate system, the same system used by Google Maps and other popular mapping software.'><i class='fa-solid fa-circle-info'></i></span> CTP Geospatial Coordinates", "multi_column", "required", "", "", "", "two-col columns-grow", "", $input_array);
	$sites_array = [];
	$sites_array[] = ["id" => "can_accept_trucks", "label" => "I can accept trucks on-site", "type" => "checkbox", "required" => "", "parameters" => "", "placeholder" => "", "acf_key" => "", "class" => "", "readonly" => ""];
	$sites_array[] = ["id" => "can_accept_layflats", "label" => "I can accept layflat pipelines on-site", "type" => "checkbox", "required" => "", "parameters" => "", "placeholder" => "", "acf_key" => "", "class" => "", "readonly" => ""];
	$site_compatibility = buildFormField('site_compatibility', 'I Can Accept Transport', 'multi_column', 'required', 'data-js-tag-outer-label="ok"', '', '', 'two-col large-outer-label no-bottom-margin', '', $sites_array);
	$dates = buildFormField('date_range', '<span tabindex="0" data-tt-length="xlarge" data-tt-pos="up-left" aria-label="Select the dates between which you will have or need water. The date range is inclusive."><i class="fa-solid fa-circle-info"></i></span> Date Range', 'date', 'required');
	$rate = buildFormField('rate_bpd', '<span tabindex="0" data-tt-length="xlarge" data-tt-pos="up-left" aria-label="Enter the rate at which can provide or accept water in barrels per day (bpd). Numeric entries only; no commas, etc."><i class="fa-solid fa-circle-info"></i></span> Water Availability Rate (bpd)', 'number', 'required', ' min="0"','Rate in barrels per day', '', ' ' . $type . '-rate_bpd');
	
	// Bid Info
	$bid_type = $trade ? buildFormField('bid_type', 'Bid Type', 'radio', 'required', '', '', '', 'large-outer-label', '', ['Willing to pay', 'Want to be paid']): "";
	$bid_array = [];
	$bid_array[] = ["id" => "bid_amount", "label" => "", "type" => "number", "required" => "required", "parameters" => "step = '.01' min='0'", "placeholder" => "Bid Amount", "acf_key" => "", "class" => ' ' . $type . '-bid_amount', "readonly" => ""];
	$bid_units = ["USD/day", "USD/bbl.day"];
	$bid_array[] = ["id" => "bid_units", "label" => "", "type" => "select", "required" => "required", "parameters" => "","placeholder" => "Bid Units", "acf_key" => "", "class" => ' ' . $type . '-bid_units', "readonly" => "", "dataset" => $bid_units];
	$bid_info = $trade ? buildFormField("bid_info", "<span tabindex='0' data-tt-length='xlarge' data-tt-pos='up-left' aria-label='Enter a bid to provide or accept water. Enter positive values only, decimal values are allowed (e.g., 1.99). Do not enter other punctuation (commas, etc.). Use the dropdown menu to select units of either USD per barrel or USD total. The form will also show you whichever you did not specify.'><i class='fa-solid fa-circle-info'></i></span> Bid", "multi_column", "required", "", "", "", "two-col columns-grow", "", $bid_array): "";

	$bid_totals = [];
	$bid_totals_row = '';
	if($trade){
		// $bid_totals[] = buildFormField("bid_total", "<span tabindex='0' data-tt-length='xlarge' data-tt-pos='up-left' aria-label='Calculated total value of your bid, in USD.'><i class='fa-solid fa-circle-info'></i></span> Total Value", "text", "", "","0", "", ' ' . $type . '-totalval', "readonly");
		$bid_totals[] = ["id" => "bid_total", "label" => "<span tabindex='0' data-tt-length='xlarge' data-tt-pos='up-left' aria-label='Calculated total value of your bid, in USD.'><i class='fa-solid fa-circle-info'></i></span> Total Value", "type" => "text", "required" => "", "parameters" => "", "placeholder" => "", "acf_key" => "", "class" => '' . $type . '-totalval', "readonly" => ""];
		$bid_totals[] = ["id" => "bid_specific_total", "label" => "<span tabindex='0' data-tt-length='xlarge' data-tt-pos='up-left' aria-label='Calculated value of your bid, in USD per barrel.'><i class='fa-solid fa-circle-info'></i></span> Barrel Value", "type" => "text", "required" => "", "parameters" => "", "placeholder" => "", "acf_key" => "", "class" => '' . $type . '-specval', "readonly" => ""];

		$bid_totals_row = buildFormField("bid-totals-row", "", "multi_column", "", "", "", "", "two-col subs-stack columns-grow", "", $bid_totals);
		// $bid_totals_row = buildFormField"id" => ("bid-totals-row", "label" => "", "type" => "multi_column", "required" => "", "parameters" => "", "placeholder" => "", "acf_key" => "", "class" => "two-col", "readonl"", );
	}

	$primary_info_fields = [$well_pad, $well_name, $latlong, $site_compatibility, $dates, $rate, $bid_type,	$bid_info, $bid_totals_row];
	$primary_information = buildFormField('Primary Information', '<span class=button-label>Primary Information</span>', 'accordion', '', '', '', '', $type . '-pi', '', $primary_info_fields);




	$share = ($type === 'share_supply');


	// Can Provide Transport (optional) Accordion
	
	$transport_col_class = $share ? 'two-col' : 'three-col';
	//Trucks
	$trucks_array[] = ["id" => "truck_transport_radius", "label" => "", "type" => "number", "required" => "", "parameters" => " min='0'", "placeholder" => "Range (mi)", "acf_key" => "", "class" => "watertrading blocks input $type-truck-input", "readonly" => ""];
	if($trade){
		$trucks_array[] = ["id" => "truck_transport_bid", "label" => "", "type" => "number", "required" => "", "parameters" => "step = '.01'", "placeholder" => "Bid (USD/bbl)", "acf_key" => "", "class" => "watertrading blocks input $type-truck-input", "readonly" => ""];
	}
	$trucks_array[] = ["id" => "truck_capacity", "label" => "", "type" => "number", "required" => "", "parameters" => " min='0'","placeholder" => "Capacity (bbl)", "acf_key" => "", "class" => "watertrading blocks input $type-truck-input", "readonly" => ""];
	$trucks = buildFormField('trucks', 'Can Provide Trucks', 'multi_column', '', '','', '', "$transport_col_class toggle $type-trucks-checkbox", '', $trucks_array);

	//Layflats
	$layflats_array[] = ["id" => "layflats_transport_radius", "label" => "", "type" => "number", "required" => "", "parameters" => " min='0'", "placeholder" => "Range (mi)", "acf_key" => "", "class" => "watertrading blocks input $type-layflat-input", "readonly" => ""];
	if($trade){
		$layflats_array[] = ["id" => "layflats_transport_bid", "label" => "", "type" => "number", "required" => "", "parameters" => "step = '.01'", "placeholder" => "Bid (USD/bbl)", "acf_key" => "", "class" => "watertrading blocks input $type-layflat-input", "readonly" => ""];
	}
	$layflats_array[] = ["id" => "layflats_capacity", "label" => "", "type" => "number", "required" => "", "parameters" => " min='0'", "placeholder" => "Capacity (bbl)", "acf_key" => "", "class" => "watertrading blocks input $type-layflat-input", "readonly" => ""];
	$layflats = buildFormField('layflats', 'Can Provide Layflats', 'multi_column', '', '', '', '', "$transport_col_class toggle $type-layflats-checkbox", '', $layflats_array );

	$delivery = buildFormField('Delivery', '<span class=button-label>Can Provide Transport</span> <span class=font-normal-weight>(optional)</span>', 'accordion', '', '', '', '', $type . '-delivery', '', [$trucks,$layflats]);





	//Quality Disclosures | Quality Requirements Accordion
	$qd = qdBuilder(['TSS','TDS', 'Chloride', 'Barium', 'Calcium Carbonate', 'Iron', 'Boron', 'Hydrogen Sulfide', 'NORM']);
	$qd_array = [$qd];
	$quality_accord_label = $supply_demand === 'supply' ? 'Quality Disclosures' : 'Quality Requirements';
	$quality_disclosures = buildFormField('Quality Disclosures', '<span class=button-label>' . $quality_accord_label . '</span> <span class=font-normal-weight>(optional)</span>', 'accordion', '', '', '', '', $type . '-qd', '', $qd_array);




	$action = esc_url( admin_url('admin-post.php') );
	error_log("Form action URL: $action");
	error_log("[POST Data] " . print_r($_POST, true));
	$form = "
	<form action='$action' method='POST' id='create-post-form' class='watersharing-form'>
		<input type='hidden' name='action' value='create_water_request'>
		<input type='hidden' name='redirect_success' value='/dashboard'>
		<input type='hidden' name='redirect_failure' value='/404'>
		$primary_information
		$delivery
		$quality_disclosures
		<input type='hidden' name='post_type' value='$type'>
		<div class='watersharing-row'>
			<div class='watersharing-input-col submit-column'>
				<button type='submit' class='watersharing-submit-button create-post-submit-button'>Submit</button>
			</div>
		</div>
	</form>
	";


	$html = "
		<div class='watersharing-card-wrap'>
			<div class='watersharing-card-inner'>
				<div class='watersharing-card-header'>
					<i class='fa-regular fa-map'></i> <h1 class='h4 watersharing-card-title'>$title</h1>
				</div>
				<div class='watersharing-card-body'>
				$form
				</div>
			</div>
		</div>
	";


	return $html;
}

function getTwoWeekIntervalsYTD() {
    // Set the date format
    $dateFormat = 'Y-m-d';
    $today = new DateTime();
    $dates = [];
    
    // Start from today and go back 1 year in 2 week intervals
    for ($i = 0; $i < 26; $i++) {
        // Calculate the Monday for this iteration
        $date = clone $today;
        $date->modify('-' . ($i * 14) . ' days'); // Go back 14 days (2 weeks) for each iteration
        
        // Isolating mondays
		if ($i !== 0) {
            $date->modify('last monday');
        }

        $dates[] = $date->format($dateFormat);
    }

    // Reverse the array so the most recent Monday is last
    return array_reverse($dates);
}

function get_related_post_ids($user_id) {
    $related_post_ids = [];

    // Query 'matched_trades' posts where the 'producer_trade' or 'consumption_trade' references a post by the current user
    $args = array(
        'post_type' => 'matched_trades',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'producer_trade',
                'value' => '', 
                'compare' => '!=', 
            ),
            array(
                'key' => 'consumption_trade',
                'value' => '', 
                'compare' => '!=',
            ),
        ),
        'fields' => 'ids' // Only return post IDs
    );

    // Perform the query to get all relevant 'matched_trades' posts
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        foreach ($query->posts as $post_id) {
            $producer_trade_id = get_post_meta($post_id, 'producer_trade', true);
            $consumption_trade_id = get_post_meta($post_id, 'consumption_trade', true);

            // Check if the author of the referenced posts matches the current user
            if (get_post_field('post_author', $producer_trade_id) == $user_id || get_post_field('post_author', $consumption_trade_id) == $user_id) {
                $related_post_ids[] = $post_id;
            }
        }
    }

    return $related_post_ids;
}

function buildKpiTable($type = "", $title = ""){
	$current_user_id = get_current_user_id();
	$author_check = strpos($type, 'personal') == true;

	$query_args = array(
		'no_found_rows'				=> false,
		'update_post_meta_cache'	=> false,
		'update_post_term_cache'	=> false,
		'post_type' =>  'matched_trades',
		'posts_per_page'			=> -1,
		'fields'					=> 'ids'
	);

	if ($author_check) {
        // Get the posts that are referenced by 'producer_trade' and 'consumption_trade'
        $related_post_ids = get_related_post_ids($current_user_id);

        if (!empty($related_post_ids)) {
            // Filter the 'matched_trades' posts based on the referenced post IDs
            $query_args['post__in'] = $related_post_ids;
        } else {
            // If no related posts, return an empty array (no posts)
            $query_args['post__in'] = array(0);
        }
    }

	$query = new WP_Query($query_args);

	$data = $query->get_posts();

	$trades_proposed = 0;
	$total_matches = 0;
	$volume_proposed = 0;
	$total_volume = 0;

	// iterate through each row and get match data 
	if( !empty( $data ) ) {
		foreach( $data as $post_id ) {
			// You can get the post object if needed
			$post = get_post($post_id);
			$post_date = $post->post_date;
			
			$trade_volume = get_post_meta( $post_id, 'total_volume', true );
			$total_value = get_post_meta( $post_id, 'total_value', true );
			$consumption_trade_approval = get_post_meta( $post_id, 'consumption_trade_approval', true);
			$producer_trade_approval = get_post_meta( $post_id, 'producer_trade_approval', true);

			if($consumption_trade_approval == 'approve' && $producer_trade_approval == 'approve'){
				$total_matches++;
				$total_volume += (float) $trade_volume;
			}
			
			$volume_proposed += (float) $trade_volume;
			$trades_proposed++;

			$request_data[] = array(
				'volume' => (float) $trade_volume,
				'date'   => date('Y-m-d', strtotime($post_date)),
				'matched' => ($consumption_trade_approval == 'approve' && $producer_trade_approval == 'approve')
			);
		}
	} else {
		$request_data[] = array(
			'volume' => '',
			'date'   => '',
			'matched' => ''
		);
	}
	$volume = [];
	$datesList = getTwoWeekIntervalsYTD();
	$chart_data= [];

	// Fill in volumes for dates running YTD
	foreach ($datesList as $index => $date) {
		// Determine the next date in the list (or set an end date if it's the last element)
		$next_date = isset($datesList[$index + 1]) 
			? $datesList[$index + 1] 
			: date('Y-m-d', strtotime("$date + 14 days"));
	
		$sumVolume = 0;
	
		// Calculate the sum of volumes for the current interval if request_data is not empty
		if (!empty($request_data)) {
			$sumVolume = array_reduce($request_data, function($carry, $data) use ($date, $next_date) {
                // Only include volume if it falls within the date range and both parties approved the trade
                if ($data['date'] >= $date && $data['date'] < $next_date && $data['matched']) {
                    return $carry + $data['volume'];
                }
                return $carry;
            }, 0);
		}
	
		// Assign volume 0 for each date if request_data is empty
		$volume[$date] = $sumVolume;
	
		// Add the sum and the current date to the chart data array
		$chart_data[] = [
			'volume' => (float) $sumVolume, 
			'date'   => $date
		];
	}
	// Sort request_data by date, if it's not empty
	if (!empty($request_data)) {
		usort($request_data, function($a, $b) {
			return strtotime($a['date']) - strtotime($b['date']);
		});
	}

	$request_data_json = json_encode($request_data);

	// Encode the chart data as JSON
	$chart_data_json = json_encode($chart_data);

	//Format data
	$total_matches = number_format($total_matches);
	$total_volume = number_format($total_volume);
	$trades_proposed = number_format($trades_proposed);

	$stat_button = $author_check ? "
    <button class='watersharing-submit-button' style='margin-top: 8px;' onclick='downloadCsv(adminUrl, volumeData)'>Download My Stats</button>
    <script>
        const adminUrl = '" . admin_url('admin-ajax.php') . "';
        const volumeData = $request_data_json;
    </script>
	" : "";

	$html = "";

	if(strpos($type, 'kpi_proposed') !== false){
		$kpi_stats = "
			<div class='watersharing-kpi-block'>
				<div class='watersharing-col watersharing-match-col'>
					<div class='watersharing-row'>
						<div>
							<strong>Total trades proposed</strong>
						</div>
					</div>
				</div>
				<div class='watersharing-col-third watersharing-contact'>
					<span class='heading'>$trades_proposed trades</span>
				</div>
			</div>
		";
	}

	else if(strpos($type, 'kpi_volume') !== false){
		$kpi_stats = "
			<div class='watersharing-kpi-block'>
				<div class='watersharing-col watersharing-match-col'>
					<div class='watersharing-row'>
						<div>
							<strong>Total volume traded to date</strong>
						</div>
					</div>
				</div>
				<div class='watersharing-col-third watersharing-contact'>
					<span class='heading'>$total_volume bbl</span>
				</div>
			</div>
		";
	}

	else if(strpos($type, 'kpi_totalTrade') !== false){
		$kpi_stats = "
			<div class='watersharing-kpi-block'>
				<div class='watersharing-col watersharing-match-col'>
					<div class='watersharing-row'>
						<div>
							<strong>Total trades to date</strong>
						</div>
					</div>
				</div>
				<div class='watersharing-col-third watersharing-contact'>
					<span class='heading'>$total_matches trades</span>
				</div>
			</div>
		";
	}

	else if(strpos($type, 'kpi_statChart') !== false){
		
		$block_id = uniqid('chart_' . $type);

		$kpi_stats = "
			<div class='chart-container' data-block-id='{$block_id}' data-chart-data='{$chart_data_json}'>
				<canvas class='chart' id='stat-chart-{$block_id}'></canvas>
				<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
				$stat_button
			</div>
		";
	}
	$html = "$kpi_stats";

	return $html;
}


// function to lookup matches from the match_request record
function lookupMatches( $post_id = '', $post_type = '' ) {
	if($post_type === 'share_supply'){
		$post_type = 'producer_request';
	}
	elseif($post_type === 'share_demand'){
		$post_type = 'consumption_request';
	}
	elseif($post_type === 'trade_supply'){
		$post_type = 'producer_trade';
	}
	elseif($post_type === 'trade_demand'){
		$post_type = 'consumption_trade';
	}

	$query = new WP_Query(
		array(
			'no_found_rows'				=> false,
			'update_post_meta_cache'	=> false,
			'update_post_term_cache'	=> false,
			'post_type' => (strpos($post_type, 'request') !== false) ? 'matched_shares' : 'matched_trades',
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
	$watersharing_enabled = get_option('watersharing_toggle', 0);
	$watertrading_enabled = get_option('watertrading_toggle', 0);
	
	// Redirect IDs on creation
	$watersharing_prod_redirect_id = get_option('production_dashboard_page', '');
	$watersharing_cons_redirect_id = get_option('consumption_dashboard_page', '');
	$watertrading_prod_redirect_id = get_option('wt_production_dashboard_page', '');
	$watertrading_cons_redirect_id = get_option('wt_consumption_dashboard_page', '');

	// Send-To feature requires both toggles to be enabled
	$watersharing_enabled = empty($watersharing_enabled) ? 0 : $watersharing_enabled;
	$watertrading_enabled = empty($watertrading_enabled) ? 0 : $watertrading_enabled;
	$send_to_enabled = $watersharing_enabled && $watertrading_enabled;

	// cfdump($watersharing_enabled, 'watersharing_enabled');
	// cfdump($watertrading_enabled, 'watertrading_enabled');
	// cfdump($send_to_enabled, 'send_to_enabled');

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
			$toggle_disabled = $send_to_enabled ? " copy-to-only" : " disabled";

			$lookups = lookupMatches( $post, $type );
			if( $lookups ) {
				$count = 0;

				// build out the match record view
				foreach( $lookups as $lookup ) {
					$count++;

					// ( $type === 'share_supply' ) ? $user_interaction = 'producer_approval' : $user_interaction = 'consumption_approval';
					if($type === 'share_supply'){
						$user_interaction = 'producer_approval';
					}
					elseif($type === 'share_demand'){
						$user_interaction = 'consumption_approval';
					}
					elseif($type === 'trade_supply'){
						$user_interaction = 'producer_trade_approval';
					}
					elseif($type === 'trade_demand'){
						$user_interaction = 'consumption_trade_approval';
					}
					$user_action = get_post_meta( $lookup, $user_interaction, true );
					$avoided = get_post_meta( $lookup, 'disposal_avoided', true );
					$fullfilled = get_post_meta( $lookup, 'matched_rate', true );
					$lookup_distance = get_post_meta( $lookup, 'matched_distance', true );
					$lookup_status = get_post_meta( $lookup, 'match_status', true );

					$total_value = get_post_meta( $lookup, 'total_value', true);
					$total_volume = get_post_meta( $lookup, 'total_volume', true);

					$consumer = get_post_meta( $lookup, 'consumption_trade', true);
					$producer = get_post_meta( $lookup, 'producer_trade', true);
					if($consumer && $producer){
						$trade_csv = ($producer . '-' . $consumer);
					}

					#Share Conditions
					if($type === 'share_supply') { 
						$match_type = 'consumption_request'; 
						$match_post_type = 'share_supply';
					}
					elseif($type === 'share_demand') {
						$match_type = 'producer_request'; 
						$match_post_type = 'share_demand';
					}

					#Trade Conditions
					if($type === 'trade_supply') {
						$match_type = 'consumption_trade';
						$match_post_type = 'trade_supply';
					}
					elseif($type === 'trade_demand') {
						$match_type = 'producer_trade'; 
						$match_post_type = 'trade_demand';
					}

					$match_record = get_post_meta( $lookup, $match_type, true );
					$match_id = $match_record;

					//Get the author ID for the post
					$author_id = get_post_field( 'post_author', $match_record );

					//Get the company name or email from the author's user meta
					$match_op = get_the_author_meta( 'company_name', $author_id ) 
					? get_the_author_meta( 'company_name', $author_id ) 
					: get_the_author_meta( 'nickname', $author_id );


					$match_start = get_post_meta( $match_record, 'start_date', true );
					( $match_start ) ? $match_start = DateTime::createFromFormat('Y-m-d', $match_start)->format('m/d/Y') : "";
					$match_end = get_post_meta( $match_record, 'end_date', true );
					( $match_end ) ? $match_end = DateTime::createFromFormat('Y-m-d', $match_end)->format('m/d/Y') : "";
					$match_range = "$match_start - $match_end";

					$approve_actions = "
						<div class='match-cell match-approval'>
							<a class='watersharing-match-action approval approve-action' onclick='void(0)' data-lookup='$lookup' data-parent='$post' data-match='$match_id' data-match-type='$match_post_type' data-action='approve' data-table='$type-RequestTable'>Approve <i class='fa-solid fa-thumbs-up'></i></a>
							<a class='watersharing-match-action approval decline-action' onclick='void(0)' data-lookup='$lookup' data-parent='$post' data-match='$match_id' data-match-type='$match_post_type' data-action='decline' data-table='$type-RequestTable'>Decline <i class='fa-solid fa-thumbs-down'></i></a>
						</div>
						";
					$summation = $approve_actions;

					if ($user_action) {
						if ($user_action === 'approve') {
							$approve_actions = '<!-- User Has Approved -->';
						}

						if ($user_action === 'decline') {
							$approve_actions = '<!-- User Has Declined -->';
						}
					}

					// check if match is approved
					if ($lookup_status === 'approved') {

						$name = get_userdata( get_post_field( 'post_author', $match_record ) )->first_name . ' ' . get_userdata( get_post_field( 'post_author', $match_record ) )->last_name;
						$raw_phone = get_user_meta( get_post_field( 'post_author', $match_record ), 'phone_number', true );
						$sanitized_phone = preg_replace('/[^0-9+]/', '', $raw_phone);
						$email = get_userdata( get_post_field( 'post_author', $match_record ) )->user_email;
						
						$contact = "
								<div class='match-cell match-contact'>
									<strong class='heading'>Contact Information:</strong>
									<span>$name</span>
									<span><a href='tel:$sanitized_phone'>$raw_phone</a></span>
									<span><a href='mailto:$email'>$email</a></span>
								</div>
							";
						$summation = "<span class='status-message-matched'>Operator Matched!</span>";
					} else {
						// $summation = "PENDING";
						$contact = "";
					}

					//Added logic for trading
					( $type === 'share_demand' || $type === 'trade_demand') ? $avoid_label = "Sourced Water Saved (bbl)" : $avoid_label = "Disposal Avoided (bbl)";
					
					if($total_value){$total_value = number_format($total_value);}
					if($total_volume){$total_volume = number_format($total_volume);}

					(strpos($type,'share') !== false) ? $field1 = '' : $field1 = "<div class='match-cell match-field-1 watersharing-col-half'><strong>Total Value:</strong> $total_value USD</div>"; // Do not show dates in the details (its on the row headers)
					(strpos($type,'share') !== false) ? $field2 = "<div class='match-cell match-field-2 match-rate match-fullfilled-rate watersharing-col-half'><strong>Rate (bpd):</strong> $fullfilled</div>" : $field2 = "<div class='match-cell match-field-2 match-total-volume watersharing-col'><strong>Total Volume:</strong> $total_volume bbl</div>";
					(strpos($type,'share') !== false) ? $field3 = "
					<div class='match-cell match-lookup-distance watersharing-col-half'>
						<strong>Distance (miles):</strong> $lookup_distance
					</div>"
					:$field3 = "<div class='match-cell match-buttons'><button class='watersharing-submit-button download-summary-btn' 
					data-trade-csv='" . esc_attr($trade_csv) . "'>Download Detailed Summary <i class='fa-solid fa-download'></i></button> <button class='watersharing-submit-button send-to-btn'>Send to Portal <i class='fa-solid fa-circle-arrow-right'></i></button></div>";
					
					(strpos($type,'share') !== false) ? $avoid_field = 
					"<div class='match-cell match-avoid-field watersharing-col-half'>
						<strong>$avoid_label:</strong> $avoided
					</div>"
					: $avoid_field = "";
					

					$match_rows .= "
							<div>
								<div class='watersharing-match-block'>
									<div class='match-detail'>
										<div class='watersharing-row'>
											<div class='match-cell match-operator watersharing-col-half'>
												<strong>Matched Operator:</strong> $match_op
											</div>
											$field1
											$field2
											$field3
											$avoid_field
											$contact
										</div>
									</div>
									<div class='match-summation'>
										$summation
									</div>
								</div>
							</div>
						";

					$match_prompt = "<span class='matches matched'><i class='fa-solid fa-bullseye'></i><strong>$count</strong> Matches Found</span>";
					$toggle_disabled = "";
				}
			} else {
				$match_rows = "<div class='watersharing-match-block'><div class='match-detail'><div class='match-cell match-send-to watersharing-col-half'><button class='watersharing-submit-button send-to-btn'>Send to Portal <i class='fa-solid fa-circle-arrow-right'></i></button></div></div><div class='match-summation'><span class='status-message-not-matched'>Not Matched</span></div></div>";
			}
			
			$rate = number_format($rate);

			( isset( get_post_meta( $post, 'status', true )['value'] ) && get_post_meta( $post, 'status', true ) === 'closed' ) ? $row_class = " closed" : $row_class = "";
			$rows .= "
					<tr class='watersharing-request-row$row_class' data-row-number='row-$number'>
						<td class='align-middle hide-on-mobile check-cell'><input class='watersharing-input-row' type='checkbox' name='post_ids[]' value='$post' data-watershare-type='$type' /></td>
						<td class='align-middle'><strong class='label show-on-mobile'>Pad Name: </strong>$well</td>
						<td class='align-middle'><strong class='label show-on-mobile'>Date Range: </strong>$range</td>
						<td class='align-middle'><strong class='label show-on-mobile'>Status: </strong>$status</td>
						<td class='align-middle'><strong class='label show-on-mobile'>Rate (bbp): </strong>$rate</td>
						<td class='align-middle'><strong class='label show-on-mobile'>Match Found? </strong>$match_prompt</td>
						<td class='align-middle text-center dashboard-action-td'>
							<a class='watersharing-match-action toggle-row$toggle_disabled' data-toggle-disabled='$toggle_disabled'>
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
						<td class='dashboard-row-inner-dt' colspan='7'>
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
		<div id='loading-indicator' class = '$type-loading-indicator'></div>
		<form id='$type-status-form' method='post' action='$action?action=change_post_status'>
			<table class='watersharing-table tablesorter' id='$type-RequestTable'>
				<thead>
					<tr>
						<th class='nosort'></th>
						<th>Pad Name</th>
						<th>Date Range</th>
						<th>Status</th>
						<th>Rate (bpd)</th>
						<th>Matches Found</th>
						<th class='nosort' width='50px' data-sort='false'></th>
					</tr>
				</thead>
				<tbody>
					$rows
				</tbody>
			</table>

			<div class='table-action-row'>
				<div class='hide-on-mobile'>
					<select name='post_action' id='post_action' class='user-select select-request-action'>
						<option value='' selected hidden disabled>Manage Selection</option>
						<option value='close'>Close Request(s)</option>
						<option value='delete'>Delete Request(s)</option>
					</select>
					<input id='$type-status-submit' type='submit' name='submit' class='watersharing-secondary-button post-status-submit' value='Apply' disabled/>
				</div>
				<div class='pagination-outer-container'>
					<!-- pagination here -->
				</div>
			</div>
		</form>
	";

	return $table;
}

?>
