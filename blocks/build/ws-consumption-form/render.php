<?php
/**
* @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
*/

if ( ! is_user_logged_in() ) {
	return;
}

?>

<div class='watersharing-blocks'>
	<?php echo buildRequestForm('share_demand', 'I Need Water'); ?>
</div>
