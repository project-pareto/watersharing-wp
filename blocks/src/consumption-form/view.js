/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any
 * JavaScript running in the front-end, then you should delete this file and remove
 * the `viewScript` property from `block.json`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */

/* eslint-disable no-console */
(function($) {
	// Bind change event to the select element
	$('select[name="well_pad"]').on('change', function() {
		// Get the selected option value
		var selectedValue = $(this).val();

		// Get additional data attributes from the selected option
		var selectedLat = $(this).find(':selected').data('lat');
		var selectedLong = $(this).find(':selected').data('long');
		var selectedTitle = $(this).find(':selected').data('title');

		$('input#well_name').val(selectedTitle);
		$('input#latitude').val(selectedLat);
		$('input#longitude').val(selectedLong);

		//add read only class after updating value
		$('input#well_name').addClass('readonly');
		$('input#latitude').addClass('readonly');
		$('input#longitude').addClass('readonly');

	});
})(jQuery);
/* eslint-enable no-console */
