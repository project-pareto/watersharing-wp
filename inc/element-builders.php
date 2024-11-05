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
function buildFormField( $id = "", $label = "", $type = 'text', $required = "", $parameters = "", $placeholder = "",$acf_key = "", $class = "", $readOnly = '', $dataset = [] ) {
	if ($type) {
		switch ($type) {
			case 'text':
				$input = "<input type='text' class='form-control$class' id='$id' name='$id' placeholder='$placeholder' $required $readOnly>";
				break;

			case 'number':
				$input = "<input type='number' class='form-control$class' id='$id' name='$id' placeholder='$placeholder' $required $readOnly $parameters>";
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
						$input .= "
							<div class='meta-radio-select'>
								<input type='radio' name='$id_lower' id='$set_lower' value='$set_lower' class = 'radio-button'>
									<label>
										$set
									</label>	
							</div>
							";
					}
				}
				$input = "
					<div class='custom-meta-field'>
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
						<label>
							$id_spaced
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
				$input = "";
				if(!empty($dataset)){
					foreach($dataset as $set){
						$input .= "$set";
					}
				}
				$id_lower = strtolower(str_replace(' ', '-', $id));
				$input = "
					<div class='watersharing-row'>			
						<div class='watersharing-input-col accordion'>
							<div class='qd-accordion'>
								<div class='accordion' id='$id_lower'>
									<div class='accordion-item'>
										<label class='watersharing-form-label accordion'>
										<button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse-$id_lower' aria-expanded='false' aria-controls='collapse-$id_lower'>
											<strong>$id</strong>
										</button>
										</label>
										<div id='collapse-$id_lower' class='accordion-collapse collapse' aria-labelledby='headingOne'>
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

	str_contains($class,'toggle') ? $checkbox = "<input type='checkbox' name='$id-checkbox' id='$id-checkbox' class='meta-box-input checkbox' value='1'>": $checkbox = "";
	
	!empty($label) ?
	$html = "
		<div class='watersharing-row'>
			
			<label for='$id' class='watersharing-form-label'>$checkbox<p>$label$label_required</p></label>
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
		$qd_array[] = ["id" => $name_lower."_measure_value", "label" => "", "type" => "number", "required" => "", "parameters" => "", "placeholder" => "Value(ppm)", "acf_key" => "", "class" => "watertrading blocks input", "readonly" => ""];
		$qd .= buildFormField('quality_disclosure', $name, 'multi_column', '', '', '', '', 'two-col', '', $qd_array);
		$qd_array = [];
	}
	return $qd;
}

// function to build out request form
function buildRequestForm($type = "", $title = "") {
	$html = "";

	// Set up the fields for the form
	$well_pad = buildFormField( 'well_pad', 'Well Pads', 'pads', '', 'Create A New Pad' );
	$well_name = buildFormField('well_name', 'Pad Name', 'text', 'required', '', 'Pad Name');

	$input_array = [];	
	$input_array[] = ["id" => "latitude", "label" => "", "type" => "number", "required" => "required", "placeholder" => "latitude", "parameters" => "step = 'any'", "acf_key" => "", "class" => "", "readonly" => ""];
	$input_array[] = ["id" => "longitude", "label" => "", "type" => "number", "required" => "required", "placeholder" => "longitude", "parameters" => "step = 'any'", "acf_key" => "", "class" => "", "readonly" => ""];
	$latlong = buildFormField("coordinates", "Coordinates", "multi_column", "required", "", "", "", "two-col", "", 
	$input_array);

	$dates = buildFormField('date_range', 'Date Range', 'date', 'required');
	$rate = buildFormField('rate_bpd', 'Rate (bpd)', 'number', 'required', 'Rate in barrels per day');

	$supply = ($type === 'share_supply' || $type === 'trade_supply');
	$supply ? $transport = buildFormField('transport_radius', 'Transport Radius (mi)', 'number', 'required', '', 'Range in miles') : $transport = "";


	#Trade Specific Fields
	$trade = ($type === 'trade_supply' || $type === 'trade_demand');

	$sites_array = [];
	$sites_array[] = ["id" => "can_accept_trucks", "label" => "", "type" => "checkbox", "required" => "", "parameters" => "", "placeholder" => "", "acf_key" => "", "class" => "", "readonly" => ""];
	$sites_array[] = ["id" => "can_accept_layflats", "label" => "", "type" => "checkbox", "required" => "", "parameters" => "", "placeholder" => "", "acf_key" => "", "class" => "", "readonly" => ""];
	$trade ? $site_compatibility = buildFormField('site_compatibility', 'Site Compatibility', 'multi_column', 'required', '', '', '', 'two-col', '', $sites_array): $site_compatibility = "";
	 
	$trade ? $bid_type = buildFormField('bid_type', 'Bid Type', 'radio', 'required', '', '', '', '', '', ['Up to', 'At least']): $bid_type = "";

	$bid_array = [];
	$bid_array[] = ["id" => "bid_amount", "label" => "", "type" => "number", "required" => "required", "parameters" => "", "placeholder" => "Bid Amount", "acf_key" => "", "class" => "", "readonly" => ""];
	$bid_units = ["USD/day", "USD/bbl.day"];
	$bid_array[] = ["id" => "bid_units", "label" => "", "type" => "select", "required" => "required", "parameters" => "","placeholder" => "Bid Units", "acf_key" => "", "class" => "", "readonly" => "", "dataset" => $bid_units];
	$trade ? $bid_info =  buildFormField("bid_info", "Bid", "multi_column", "required", "", "", "", "two-col", "", $bid_array): $bid_info = "";

	$trade ? $bid_total = buildFormField("bid_total", "Total Value", "text", "", "","0", "", "", "readonly"): $bid_total = "";
	$trade ? $bid_specific_total = buildFormField("bid_specific_total", "Specific Value", "text", "", "", "0", "", "", "readonly"): $bid_specific_total = "";

	//Trucks
	$trucks_array[] = ["id" => "truck_transport_radius", "label" => "", "type" => "number", "required" => "", "parameters" => "", "placeholder" => "Radius", "acf_key" => "", "class" => "watertrading blocks input", "readonly" => ""];
	$trucks_array[] = ["id" => "truck_transport_bid", "label" => "", "type" => "number", "required" => "", "parameters" => "", "placeholder" => "Bid", "acf_key" => "", "class" => "watertrading blocks input", "readonly" => ""];
	$trucks_array[] = ["id" => "truck_capacity", "label" => "", "type" => "number", "required" => "", "parameters" => "","placeholder" => "Capacity", "acf_key" => "", "class" => "watertrading blocks input", "readonly" => ""];
	$trucks = buildFormField('trucks', 'Trucks', 'multi_column', '', '','', '', 'three-col toggle', '', $trucks_array);

	//Layflats
	$layflats_array[] = ["id" => "layflats_transport_radius", "label" => "", "type" => "number", "required" => "", "parameters" => "", "placeholder" => "Radius", "acf_key" => "", "class" => "watertrading blocks input", "readonly" => ""];
	$layflats_array[] = ["id" => "layflats_transport_bid", "label" => "", "type" => "number", "required" => "", "parameters" => "", "placeholder" => "Bid", "acf_key" => "", "class" => "watertrading blocks input", "readonly" => ""];
	$layflats_array[] = ["id" => "layflats_capacity", "label" => "", "type" => "number", "required" => "", "parameters" => "", "placeholder" => "Capacity", "acf_key" => "", "class" => "watertrading blocks input", "readonly" => ""];
	$layflats = buildFormField('layflats', 'Layflats', 'multi_column', '', '', '', '', 'three-col toggle', '', $layflats_array );

	$trade ? $delivery = buildFormField('Delivery', '', 'accordion', '', '', '', '', '', '', [$trucks,$layflats]): $delivery = '';

	//Quality Disclosures
	$trade ? $qd = qdBuilder(['TSS','TDS', 'Chloride', 'Barium', 'Calcium Carbonate', 'Iron', 'Boron', 'Hydrogen Sulfide', 'NORM']): $qd = "";
	$qd_array = [$qd];
	$trade ? $quality_disclosures = buildFormField('Quality Disclosures', '', 'accordion', '', '', '', '', '', '', $qd_array): $quality_disclosures = "";
	
	$water_quality = buildFormField('water_quality', 'Water Quality', 'text', '');

	$action = esc_url( admin_url('admin-post.php') );
	error_log("Form action URL: $action");
	error_log("[POST Data] " . print_r($_POST, true));
	$form = "
	<form action='$action' method='POST' id='create-post-form' class='watersharing-form'>
		<input type='hidden' name='action' value='create_water_request'>
		<input type='hidden' name='redirect_success' value='/dashboard'>
		<input type='hidden' name='redirect_failure' value='/404'>
		$well_pad
		$well_name
		$latlong
		$site_compatibility
		$delivery
		$dates
		$rate
		$transport
		$bid_type
		$bid_info
		$bid_total
		$bid_specific_total

		<div class='watersharing-section-break'>
			<div class='watersharing-info-text'>Optional fields:</div>
		</div>
		$water_quality
		$quality_disclosures
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

function buildKpiTable($type = "", $title = ""){
	$current_user_id = get_current_user_id();
	$author_check = strpos($type, 'personal') == true;

	$query_args = array(
		'no_found_rows'				=> false,
		'update_post_meta_cache'	=> false,
		'update_post_term_cache'	=> false,
		'post_type' =>  'matched_trades',
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
	);

	if ($author_check) {
		$query_args['author'] = $current_user_id;
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
				return ($data['date'] >= $date && $data['date'] < $next_date) 
					? $carry + $data['volume'] 
					: $carry;
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
					<span class='heading'>$total_volume Mbbl</span>
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
		
		$kpi_stats = "
			<div class = 'chart-container'>
				<canvas class = 'chart' id='stat-chart'></canvas>
				<script>
					const chartData = $chart_data_json;
				</script>
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

					//Added logic for trading
					( $type === 'share_demand' || $type === 'trade_demand') ? $avoid_label = "Sourced Water Saved (bbl)" : $avoid_label = "Disposal Avoided (bbl)";

					(strpos($type,'share') !== false) ? $field1 = "<strong>Dates:</strong> $match_range": $field1 = "<strong>Total Value:</strong> $total_value USD";
					(strpos($type,'share') !== false) ? $field2 = "<strong>Rate (bpd):</strong> $fullfilled": $field2 = "<strong>Total Volume:</strong> $total_volume bbl";
					(strpos($type,'share') !== false) ? $field3 = "
					<div class='watersharing-col-half watersharing-match-col'>
						<strong>Distance (miles):</strong> $lookup_distance
					</div>"
					:$field3 = "<button class='watersharing-submit-button download-summary-btn' style='margin-top: 8px;'>Download Detailed Summary</button>";
					
					(strpos($type,'share') !== false) ? $avoid_field = 
					"<div class='watersharing-col-half watersharing-match-col'>
						<strong>$avoid_label:</strong> $avoided
					</div>"
					: $avoid_field = "";
					

					$match_rows .= "
							<div>
								<div class='watersharing-row watersharing-match-block'>
									<div class='watersharing-match-detail' style = 'padding-right: 10px;'>
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
												$field1
											</div>
											<div class='watersharing-col-half watersharing-match-col'>
												$field2
											</div>
											$field3
											$avoid_field
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
