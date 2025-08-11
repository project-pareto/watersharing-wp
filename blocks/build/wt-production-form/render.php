<?php
/**
* @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
*/

if ( ! is_user_logged_in() ) {
	return;
}

?>

<div class='watertrading-blocks'>
	<?php echo buildRequestForm('trade_supply', 'I Have Water'); ?>
</div>
