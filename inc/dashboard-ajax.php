<?php

// this is the ajax function
function ajax_request_approval() {

	// assign passed variables from the ajax script
	$data = $_POST;

	( $data['action_type'] === 'share_demand' ) ? $user_interaction = 'producer_approval' : $user_interaction = 'consumption_approval';
	( $data['action_type'] === 'share_demand' ) ? $matched_nonaction = 'consumption_approval' : $matched_nonaction = 'producer_approval';
	( $data['action_type'] === 'share_demand' ) ? $table = 'share_supply' : $table = 'share_demand';

	if( $data['action_status'] === 'approve' ) {

		// check that the match did not 'decline'
		if( get_post_meta( $data['lookup_record'], $matched_nonaction, true ) !== 'decline' ) {

			// update the lookup record
			if( get_post_meta( $data['lookup_record'], $user_interaction, true ) !== 'approve' ) {
				update_post_meta( $data['lookup_record'], $user_interaction, 'approve');
			}

			// check if the other user has approved
			if( get_post_meta( $data['lookup_record'], $matched_nonaction, true ) === 'approve' ) {
				// if matched update both records to matched status
				update_post_meta( $data['parent_record'], 'status', 'matched' );
				update_post_meta( $data['match_record'], 'status', 'matched' );
				update_post_meta( $data['lookup_record'], 'match_status', 'approved' );
			}
			else {
				// if only one approval update match record to pending
				update_post_meta( $data['parent_record'], 'status', 'pending' );

				if( get_post_meta( $data['lookup_record'], 'match_status', true ) !== 'pending' ) {
					update_post_meta( $data['lookup_record'], 'match_status', 'pending' );
				}
			}
		}

	}
	elseif( $data['action_status'] === 'decline' ) {

		// update the lookup record
		if( get_post_meta( $data['lookup_record'], $user_interaction, true ) !== 'decline' ) {
			update_post_meta( $data['lookup_record'], $user_interaction, 'decline');
		}

		if( get_post_meta( $data['lookup_record'], 'match_status', true ) !== 'declined' ) {
			update_post_meta( $data['parent_record'], 'match_status', 'declined' );
		}

		// update this record to open state
		update_post_meta( $data['parent_record'], 'status', 'open' );
		update_post_meta( $data['match_record'], 'status', 'open' );
		update_post_meta( $data['lookup_record'], 'match_status', 'decline' );

		// add to decline to the 'do not match' set on the production record
		/*
		( $data['action_type'] === 'share_demand' ) ? $set_post = $data['parent_record'] : $set_post = $data['match_record'];
		( $data['action_type'] === 'share_demand' ) ? $decline_post = $data['match_record'] : $decline_post = $data['parent_record'];

		$declines = get_post_meta( $set_post, 'decline_set', true );
		$declines[] = $decline_post;
		update_post_meta( $set_post, 'decline_set', $declines );
		*/
	}

	// build out the results and return to ajax script
	$results = buildRequestTable( $table );

	echo json_encode( $results );
	wp_die();

}
add_action( 'wp_ajax_nopriv_ajax_approval', 'ajax_request_approval' );
add_action( 'wp_ajax_ajax_approval', 'ajax_request_approval' );

?>
