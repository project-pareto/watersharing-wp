<?php
/**
* @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
*/

if ( ! is_user_logged_in() ) {
	return;
}

?>

<div class='watersharing-blocks full-width'>
	<?php echo buildRequestTable('share_supply'); ?>
</div>
