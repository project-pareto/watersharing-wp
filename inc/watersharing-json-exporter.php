<?php

// import JSON match file to update Match Lookup and Request records
function import_json_data() {

    $share_import_folder_path = WATERSHARING_PLUGIN_PATH . '/io/watersharing/import/';
    $trade_import_folder_path = WATERSHARING_PLUGIN_PATH . '/io/watertrading/import/';
    $ws_json_files = glob($share_import_folder_path . '*_matches*');
    $wt_json_files = glob($trade_import_folder_path . '*_matches*');

    // Check if there are any JSON files
    if(empty($ws_json_files) && empty($wt_json_files)) {
        // No JSON files found
        return;
    }

    // Sort the water sharing files if available
    if(!empty($ws_json_files)) {
        usort($ws_json_files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        // Get the path to the most recent water sharing JSON file
        $ws_json_file_path = $ws_json_files[0];
        // Get the JSON data for water sharing
        $ws_json_data = file_get_contents($ws_json_file_path);
        // Decode the water sharing JSON data into an associative array
        $ws_data = json_decode($ws_json_data, true);
        // Check if decoding was successful for water sharing
        if ($ws_data !== null) {
            process_water_management_data($ws_data, 'share');
            // Delete the water sharing JSON file after processing
            unlink($ws_json_file_path);
        }
    }

    // Sort the water trading files if available
    if(!empty($wt_json_files)) {
        usort($wt_json_files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        // Get the path to the most recent water trading JSON file
        $wt_json_file_path = $wt_json_files[0];
        // Get the JSON data for water trading
        $wt_json_data = file_get_contents($wt_json_file_path);
        // Decode the water trading JSON data into an associative array
        $wt_data = json_decode($wt_json_data, true);
        // Check if decoding was successful for water trading
        if ($wt_data !== null) {
            process_water_management_data($wt_data, 'trade');
            // Delete the water trading JSON file after processing
            unlink($wt_json_file_path);
        }
    }

    // Delete the JSON file after processing
    unlink($ws_json_file_path);
    unlink($wt_json_file_path);
}

function process_water_management_data($data, $type) {

    // Check if we're dealing with 'share' or 'trade' type data
    if ($type == 'share') {
        // Iterate over each item in the array, assuming $data is a list of shares
        foreach ($data as $item) {
            $title = $item['From operator'] . ' ' . $item['From index'] . ' - ' . $item['To operator'] . ' ' . $item['To index'];
            $post_type = 'matched_shares';

            // Check if a post with the same title already exists
            $existing_post = new WP_Query([
                'post_type' => $post_type,
                'post_status' => 'any',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'title' => $title
            ]);

            if ($existing_post->have_posts()) {
                continue;
            }

            // Create new post
            $new_post = [
                'post_type' => $post_type,
                'post_status' => 'publish',
                'post_title' => $title,
            ];
            $post_id = wp_insert_post($new_post);

            if ($post_id) {
                update_post_meta($post_id, 'match_status', 'open');
                update_post_meta($post_id, 'matched_rate', $item['Rate']);
                update_post_meta($post_id, 'producer_request', $item['From index']);
                update_post_meta($post_id, 'consumption_request', $item['To index']);
            }

            // Send email notifications
            send_match_email($item['From index'], $item['To index']);
        }

    } else if ($type == 'trade') {
        foreach (['Demand'] as $trade_type) {
            if (!isset($data[$trade_type])) continue;

            foreach ($data[$trade_type] as $item) {
                // Extract 'from' and 'to' indexes from 'Pair Index' format: "<From>-<To>"
                list($from, $to) = explode("-", $item['Pair Index']);

                $title = $item['Pair Index'];  // Title is now the entire Pair Index string
                $post_type = 'matched_trades';

                // Check if a post with the same title already exists
                $existing_post = new WP_Query([
                    'post_type' => $post_type,
                    'post_status' => 'any',
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'title' => $title
                ]);

                if ($existing_post->have_posts()) {
                    continue;
                }

                // Calculate total volume and total bid based on trade type
                $total_volume = $trade_type == 'Supply' ? $item['Supply Rate (bpd)'] : $item['Demand Rate (bpd)'];
                $total_bid = $trade_type == 'Supply' ? $item['Supplier Bid (USD/bbl)'] : $item['Consumer Bid (USD/bbl)'];

                // Create new post
                $new_post = [
                    'post_type' => $post_type,
                    'post_status' => 'publish',
                    'post_title' => $title,
                ];
                $post_id = wp_insert_post($new_post);

                if ($post_id) {
                    // Save match metadata
                    update_post_meta($post_id, 'match_status', 'open');
                    update_post_meta($post_id, 'total_volume', $total_volume);
                    update_post_meta($post_id, 'total_value', $total_bid);
                    update_post_meta($post_id, 'producer_trade', $to);
                    update_post_meta($post_id, 'consumption_trade', $from);
                }

                // Send email notifications for the match
                send_match_email($from, $to);
            }
        }
    }
}

// Email notification function
function send_match_email($from_index, $to_index) {
    $prod_post = get_post($from_index);
    $prod_email = get_the_author_meta('user_email', $prod_post->post_author);
    $prod_subject = 'Your ' . $prod_post->post_title . ' request has a match!';
    $prod_message = 'A match has been found for your request. Please log back into http://share.producedwater.org/ to view your matches';
    wp_mail($prod_email, $prod_subject, $prod_message);

    $cons_post = get_post($to_index);
    $cons_email = get_the_author_meta('user_email', $cons_post->post_author);
    $cons_subject = 'Your ' . $cons_post->post_title . ' request has a match!';
    $cons_message = 'A match has been found for your request. Please log back into http://share.producedwater.org/ to view your matches';
    wp_mail($cons_email, $cons_subject, $cons_message);
}

add_action('init', 'import_json_data');


// export requests as JSON for use with the PARETO matching Python script
function export_to_pareto( $post_id ) {
    //check for water request post_types
    $post_type = get_post_type($post_id);
    if($post_type != 'share_supply' && $post_type != 'share_demand'  && $post_type != 'trade_supply'  && $post_type != 'trade_demand') {
        return;
    }

    // only run if this post is published
    if(get_post_status($post_id) != 'publish') {
        return;
    }

    if(strpos($post_type,'share') !== false) {
        $posttypes = array(array('key' => 'Producers', 'posts' => 'share_supply'), array('key' => 'Consumers', 'posts' => 'share_demand'));
    }
    else{
        $posttypes = array(array('key' => 'Producers', 'posts' => 'trade_supply'), array('key' => 'Consumers', 'posts' => 'trade_demand')); 
    }
    $data = [];

    foreach($posttypes as $posts) {
        $data[$posts['key']] = array();

        $query = new WP_Query(array(
            'no_found_rows'                => false,
            'update_post_meta_cache'    => false,
            'update_post_term_cache'    => false,
            'post_type'                    => $posts['posts'],
            'post_status'                => 'publish',
            'meta_query'                => array(
                'relation'        => 'AND',
                array(
                    'key'        => 'status',
                    'value'        => 'open',
                    'compare'    => 'LIKE'
                )
            ),
            'posts_per_page'            => -1,
            'fields'                    => 'ids',
        ));

        $items = $query->get_posts();
        if(!empty($items)) {
            foreach($items as $item) {
            $query_post_type = get_post_type($item);
            $bpd_rate = ($query_post_type == 'trade_demand') ? "Demand Rate (bpd)":  "Supply Rate (bpd)";
            $bid = ($query_post_type == 'trade_demand') ? "Consumer Bid (USD/bbl)": "Supplier Bid (USD/bbl)";

                $item_array = [];
                $well = $lat = $long = $start = $end = $rate = $max = "";
                $author = get_the_author_meta('display_name', get_post_field('post_author', $item));
                $author_id = (string)get_the_author_meta('ID', get_post_field('post_author', $item));

                // get the record details
                $well = get_post_meta($item, 'well_name', true);
                $lat = (float)get_post_meta($item, 'latitude', true);
                $long = (float)get_post_meta($item, 'longitude', true);

                $start = get_post_meta($item, 'start_date', true);
                $end = get_post_meta($item, 'end_date', true);

                $rate = (float)get_post_meta($item, 'rate_bpd', true);
                $max = get_post_meta($item, 'transport_radius', true);
                $max = $max !== '' ? (int)$max : '';

                //get trade record details
                // $site_compatibility = get_post_meta($item, 'site_compatibility', true);
                $can_accept_trucks= (get_post_meta($item, 'can_accept_trucks', true) == "0") ? false: true;
                $can_accept_layflats = (get_post_meta($item, 'can_accept_layflats', true) == "0") ? false: true;

                $bid_type = get_post_meta($item, 'bid_type', true);
                $bid_amount = (float)get_post_meta($item, 'bid_amount', true);
                $bid_units = get_post_meta($item, 'bid_units', true);

                $truck_transport_radius = (float)get_post_meta($item, 'truck_transport_radius', true);
                $truck_transport_bid = (float)get_post_meta($item, 'truck_transport_bid', true);
                $truck_capacity = (float)get_post_meta($item, 'truck_capacity', true);

                $layflats_transport_radius = (float)get_post_meta($item, 'layflats_transport_radius', true);
                $layflats_transport_bid = (float)get_post_meta($item, 'layflats_transport_bid', true);
                $layflats_capacity = (float)get_post_meta($item, 'layflats_capacity', true);

                $tss_limit = get_post_meta($item, 'tss_limit', true);
                $tss_measure_value = (float)get_post_meta($item, 'tss_measure_value', true);

                $tds_limit = get_post_meta($item, 'tds_limit', true);
                $tds_measure_value = (float)get_post_meta($item, 'tds_measure_value', true);

                $chloride_limit = get_post_meta($item, 'chloride_limit', true);
                $chloride_measure_value = (float)get_post_meta($item, 'chloride_measure_value', true);

                $barium_limit = get_post_meta($item, 'barium_limit', true);
                $barium_measure_value = (float)get_post_meta($item, 'barium_measure_value', true);

                $calciumcarbonate_limit = get_post_meta($item, 'calciumcarbonate_limit', true);
                $calciumcarbonate_measure_value = (float)get_post_meta($item, 'calciumcarbonate_measure_value', true);

                $iron_limit = get_post_meta($item, 'iron_limit', true);
                $iron_measure_value = (float)get_post_meta($item, 'iron_measure_value', true);

                $boron_limit = get_post_meta($item, 'boron_limit', true);
                $boron_measure_value = (float)get_post_meta($item, 'boron_measure_value', true);

                $hydrogensulfide_limit = get_post_meta($item, 'hydrogensulfide_limit', true);
                $hydrogensulfide_measure_value = (float)get_post_meta($item, 'hydrogensulfide_measure_value', true);

                $norm_limit = get_post_meta($item, 'norm_limit', true);
                $norm_measure_value = (float)get_post_meta($item, 'norm_measure_value', true);
                
                if(strpos($post_type,'share') !== false){
                    $item_array = array(
                        'Index'            => $item,
                        'Operator'         => $author,
                        'Wellpad'        => $well,
                        'Longitude'        => $long,
                        'Latitude'        => $lat,
                        'Start Date'    => $start,
                        'End Date'        => $end,
                        'Rate'            => $rate,
                        'Max Transport'    => $max,
                    );
                }
                else{
                    $item_array = array(
                        'Index'            => (string) $item,
                        'Operator'         => $author,
                        'UserID'        => (string)$author_id,
                        'Wellpad'        => $well,
                        'Longitude'        => $long,
                        'Latitude'        => $lat,
                        'Start Date'    => $start,
                        'End Date'        => $end,
                        $bpd_rate            => $rate,
                        $bid             => $bid_amount,
                        'Bid Type'              => $bid_type,
                        'Trucks Accepted'    => $can_accept_trucks,
                        'Pipes Accepted'    => $can_accept_layflats,
                        'Truck Max Dist (mi)'=> $truck_transport_radius,  
                        'Trucking Capacity (bpd)'        => $truck_capacity,  
                        'Truck Transport Bid (USD/bbl)'   => $truck_transport_bid,
                        // 'Max Transport'    => $max, 
                        'Pipe Max Dist (mi)' => $layflats_transport_radius,
                        'Pipeline Capacity (bpd)'     => $layflats_capacity,
                        'Pipe Transport Bid (USD/bbl)'=> $layflats_transport_bid,
                        // 'Bid Units'             => $bid_units,
                        'TSS'     => $tss_measure_value,
                        'TDS'     => $tds_measure_value,
                        'Chloride'=> $chloride_measure_value,
                        'Barium'  => $barium_measure_value,
                        'Calcium carbonates' => $calciumcarbonate_measure_value,
                        'Iron'    => $iron_measure_value,
                        'Boron'   => $boron_measure_value,
                        'Hydrogen Sulfide' => $hydrogensulfide_measure_value,
                        'NORM'    => $norm_measure_value,
                        'TSS Constraint'             => $tss_limit,          
                        'TDS Constraint'             => $tds_limit,            
                        'Chloride Constraint'        => $chloride_limit,       
                        'Barium Constraint'          => $barium_limit,        
                        'Calcium carbonates Constraint'=> $calciumcarbonate_limit,
                        'Iron Constraint'            => $iron_limit,          
                        'Boron Constraint'           => $boron_limit,     
                        'Hydrogen Sulfide Constraint'=> $hydrogensulfide_limit,
                        'NORM Constraint'            => $norm_limit,
                    );
                }
                array_push($data[$posts['key']], $item_array);
            }
        }

        wp_reset_postdata();
    }

    // // get the 'do not match' list
    $exarray = array();
    $excludes = new WP_Query(array(
        'no_found_rows'                => false,
        'update_post_meta_cache'    => false,
        'update_post_term_cache'    => false,
        'post_type'                    => array('share_supply', 'trade_supply'),
        'post_status'                => 'publish',
        'meta_query'                => array(
            'relation'        => 'AND',
            array(
                'key'        => 'status',
                'value'        => 'open',
                'compare'    => 'LIKE'
            )
        ),
        'posts_per_page'            => -1,
        'fields'                    => 'ids',
    ));


    $items = $excludes->get_posts();
    if(!empty($items)) {
        $data['Restrictions'] = array();
        foreach($items as $item) {

            $this_op = get_user_meta(get_post_field('post_author', $item), 'company_name', true);
            $this_pad = get_post_meta($item, 'well_name', true);
            $decline_set = get_post_meta($item, 'decline_set', true);
            if($decline_set) {
                foreach($decline_set as $decline) {
                    $decline_op = get_user_meta(get_post_field('post_author', $decline), 'company_name', true);
                    $decline_pad = get_post_meta($decline, 'well_name', true);
                    $decline_array = array($this_op, $this_pad, $decline_op, $decline_pad);

                    array_push($data['Restrictions'], $decline_array);
                }
            }
        }
        wp_reset_postdata();
    }

    // Check if both Producers and Consumers are populated
    if (!empty($data['Producers']) && !empty($data['Consumers'])) {
        $json_data = json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );

        // Determine file path based on post type
        if(strpos($post_type,'share') !== false){
            $file_name = 'pareto_sharing_' . date('c') . '.json';
            $file_path = WATERSHARING_PLUGIN_PATH . 'io/watersharing/export/' . $file_name;
        }
        else{
            $file_name = 'pareto_trading_' . date('c') . '.json';
            $file_path = WATERSHARING_PLUGIN_PATH . 'io/watertrading/export/' . $file_name;
        }
        $file_saved = file_put_contents( $file_path, $json_data );
    }
}
// add_action( 'export_share_supply_records', 'export_to_pareto', 20 );

function on_post_meta_added( $meta_id, $post_id, $meta_key, $meta_value ) {
    // Ensure the post is published and the post type is one of the target types
    $post_status = get_post_status($post_id);
    $post_type = get_post_type($post_id);

    if (in_array($post_type, ['share_supply', 'share_demand', 'trade_supply', 'trade_demand']) 
    && $post_status == 'publish' && $meta_key == 'status') {
        // Proceed with export, no need to check if meta value has changed unless necessary
        export_to_pareto($post_id);
    }
}
add_action('added_post_meta', 'on_post_meta_added', 10, 4);
