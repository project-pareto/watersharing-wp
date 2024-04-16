<?php
/**
* @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
*/

?>

<div class='watersharing-blocks full-width'>
	<?php echo buildRequestTable('water_demand'); ?>
</div>

<script>
function updateSubmitButton( type = '' ) {
  var checkboxes = document.querySelectorAll('input[type="checkbox"]');
  var submitButton = document.getElementById( type + '-status-submit');

  for (var i = 0; i < checkboxes.length; i++) {
	if (checkboxes[i].checked) {
	  submitButton.disabled = false;
	  return;
	}
  }

  submitButton.disabled = true;
}

// heres all the magic
(function($){
	function sortTables() {
		$('table.tablesorter').each(function() {
			var tableID = $(this).attr('id');

			$('#' + tableID).tablesorter().bind("sortEnd", function(e,t) {
				$('#' + tableID + ' .row-summary').each(function() {
					var rowSummary = $(this)
					var rowIdentifer = rowSummary.data('row-number')
					var rowExpander = $('#' + tableID + " .row-expanded[data-row-number='" + rowIdentifer + "']")
					rowSummary.after(rowExpander)
				})
			});
		});
	}

	$(document).ready(function() {
		sortTables();
	});


	$(document).on('click', 'a.approval', function() {
		var dataTable = '#' + $(this).data('table');
		dataTable = $(dataTable).parent();

		var params = [];
		params.lookupid = $(this).data('lookup')
		params.matchid = $(this).data('match');
		params.parentid = $(this).data('parent');
		params.interaction = $(this).data('action');
		params.interactiontype = $(this).data('match-type');
		params.dataTable = dataTable;
		params.rowOpen = $(this).parents().eq(7).data('row-number');

		if (!confirm('Are you sure you want to ' + params.interaction + ' this match?')) {
			event.preventDefault();
		}

		ajaxMatch( params );
	})

	function ajaxMatch(params) {
		$.ajax({
			url: '/wp-admin/admin-ajax.php',
			type: 'POST',
			data: {
				action			:	'ajax_approval',
				lookup_record	:	params.lookupid,
				parent_record	:	params.parentid,
				match_record	:	params.matchid,
				action_status	:	params.interaction,
				action_type		:	params.interactiontype
			},
			dataType: 'json',


			beforeSend: function(xhr) {
				$(params.dataTable).html('');
			},
			success: function(output) {
				console.log( output );
				$(params.dataTable).html(output);
				sortTables();
			},
			complete: function(xhr) {
			},
			error: function(result) {
			}

		})
	}
})(jQuery);
</script>
