<?php

// import JSON match file to update Match Lookup and Request records
function import_json_data() {

    $import_folder_path = WATERSHARING_PLUGIN_PATH . 'io/import/';
    $json_files = glob($import_folder_path . '*.json');

    // Check if there are any JSON files
    if(empty($json_files)) {
        // No JSON files found
        return;
    }

    // Sort the JSON files by modified time (newest to oldest)
    usort($json_files, function ($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    // Get the path to the most recent JSON file
    $json_file_path = $json_files[0];

    // Get the JSON data
    $json_data = file_get_contents($json_file_path);

    // Decode the JSON data into an associative array
    $data = json_decode($json_data, true);

    // Check if the decoding was successful
    if($data === null) {
        // Failed to decode JSON
        return;
    }

    foreach($data as $item) {
        $title = $item['From operator'] . ' ' . $item['From index'] . ' - ' . $item['To operator'] . ' ' . $item['To index'];

        // Check if a post with the same title already exists
        $existing_post = new WP_Query(
            array(
                'post_type' => 'matched_requests',
                'post_status' => 'any',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'title' => $title
            )
        );

        if($existing_post->have_posts()) {
            continue;
        }

        // create new post
        $new_post = array(
            'post_type' => 'matched_requests',
            'post_status' => 'publish',
            'post_title' => $title,
        );
        $post_id = wp_insert_post($new_post);

        if($post_id) {
            // set status to 'open'
            update_post_meta($post_id, 'match_status', 'open');
            update_post_meta($post_id, 'matched_rate', $item['value']);
            update_post_meta($post_id, 'producer_request', $item['From index']);
            update_post_meta($post_id, 'consumption_request', $item['To index']);
        }

        // email notifications
        $prod_post = get_post($item['From index']);
        $prod_email = get_the_author_meta('user_email', $prod_post->post_author);
        $prod_subject = 'Your' . $prod_post->post_title . ' request has a match!';
        $prod_message = 'A match has been found for your request. Please log back into the <a href="http://share.producedwater.org/" to view your matches';
        wp_mail($prod_email, $prod_subject, $prod_message);

        $cons_post = get_post($item['To index']);
        $cons_email = get_the_author_meta('user_email', $cons_post->post_author);
        $cons_subject = 'Your' . $cons_post->post_title . ' request has a match!';
        $cons_message = 'A match has been found for your request. Please log back into the <a href="http://share.producedwater.org/" to view your matches';
        wp_mail($cons_email, $cons_subject, $cons_message);
    }

    // Delete the JSON file after processing
    unlink($json_file_path);
}
add_action('init', 'import_json_data');


// export requests as JSON for use with the PARETO matching Python script
function export_to_pareto( $post_id ) {
    //check for water request post_types
    $post_type = get_post_type($post_id);
    if($post_type != 'share_supply' && $post_type != 'share_demand') {
        return;
    }

    // only run if this post is published
    if(get_post_status($post_id) != 'publish') {
        return;
    }

    $posttypes = array(array('key' => 'Producers', 'posts' => 'share_supply'), array('key' => 'Consumers', 'posts' => 'share_demand'));
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
                $item_array = [];
                $well = $lat = $long = $start = $end = $rate = $max = "";
                $author = get_the_author_meta('display_name', get_post_field('post_author', $item));

                // get the record details
                $well = get_post_meta($item, 'well_name', true);
                $lat = (float)get_post_meta($item, 'latitude', true);
                $long = (float)get_post_meta($item, 'longitude', true);

                $start = get_post_meta($item, 'start_date', true);
                $end = get_post_meta($item, 'end_date', true);

                $rate = (float)get_post_meta($item, 'rate_bpd', true);
                $max = get_post_meta($item, 'transport_radius', true);
                $max = $max !== '' ? (int)$max : '';

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
        'post_type'                    => 'share_supply',
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

    $json_data = json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );

    $file_name = 'pareto_matching_' . date('c') . '.json';
    $file_path = WATERSHARING_PLUGIN_PATH . 'io/export/' . $file_name;
    $file_saved = file_put_contents( $file_path, $json_data );

}
add_action( 'export_share_supply_records', 'export_to_pareto', 20 );

// create the 'export_share_supply_records' cron to trigger the export script
function schedule_export_share_supply_records( $post_id ) {
    $post = get_post( $post_id );
    if( $post->post_type === 'share_supply' || $post->post_type === 'share_demand' ) {
        // Schedule the export function to run after a delay
        wp_schedule_single_event( time() + 3, 'export_share_supply_records', array( $post_id ) );
    }
}
add_action( 'save_post', 'schedule_export_share_supply_records' );
