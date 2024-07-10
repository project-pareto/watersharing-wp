(function($) {

	// format number only inputs
	$('.geoentry').on('input', function() {
		var value = $(this).val();
		// Remove any non-numeric characters except period and hyphen
		value = value.replace(/[^0-9.-]/g, '');
		// Remove leading zeros
		value = value.replace(/^0+/, '');

		$(this).val(value);
	});

	// unlock row management action
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

	$(document).on( 'click', 'input.watersharing-input-row', function() {
		var type = $(this).data('watershare-type');
		updateSubmitButton( type );
	})


	// alert user of changing request status
	$(document).on('click', '.post-status-submit', function(event) {

		var input = $(this);
		var postAction = $(this).prev('select').val();

		if( postAction === '' || postAction === null ) {
			event.preventDefault();
			alert('Please select an action');
		}
		else {
			if (!confirm('Are you sure you want to ' + postAction + ' your request(s)?')) {
				event.preventDefault();
			}
		}
	});

	// date range select valuation
	$(document).on('input', '#start_date', function(){
		var startDateInput = $(this);
		var endDateInput = $(this).parent().parent().find('.end-dp #end_date');

		$(endDateInput).val("");
		$(endDateInput).attr("min", $(startDateInput).val());
		$(endDateInput).prop("disabled", false);
	});

	$(document).on('blur', '#end_date', function(){
		var endDateValue = $(this).val();
		var startDateValue = $(this).parent().parent().find('.start-dp #start_date').val();

		if (endDateValue < startDateValue) {
			alert( 'End date cannot be before the start date.' );
			endDateInput.val("");
		}
	});

	//Location Select Validation
	$(document).on('blur', 'input#longitude', function() {
		var longitudeInput = $(this);
		var latitudeInput = longitudeInput.closest('form').find('input#latitude');
	
		var longitudeValue = parseFloat(longitudeInput.val());
		var latitudeValue = parseFloat(latitudeInput.val());
	
		if (!isNaN(longitudeValue) && !isNaN(latitudeValue)) {
			if (longitudeValue < -124.785543 || longitudeValue > -66.945554 
				|| latitudeValue < 24.446667 || latitudeValue > 49.382812) {
				alert('Coordinates must fall within continental USA');
				longitudeInput.val('');
				latitudeInput.val('');
			}
		}
	});	

	// toggle match details display
	$(document).on('click', '.toggle-row', function() {
		if( $(this).closest("tr").next("tr").hasClass('show') ) {
			$(this).toggleClass("active");
			$(this).closest("tr").next("tr").removeClass("show");
			$(this).closest("tr").next("tr").addClass("collapsing");
			var tdElement = $(this);
			setTimeout( function(element) {
				$(element).closest("tr").next("tr").removeClass("collapsing");
				$(element).closest("tr").next("tr").addClass("collapse");
		    }, 400, tdElement );
		}
		else {
			$('.toggle-row').each(function() {
				if( $(this).closest("tr").next("tr").hasClass('show') ) {
					$(this).toggleClass("active");
					$(this).closest("tr").next("tr").removeClass("show");
					$(this).closest("tr").next("tr").addClass("collapsing");
					var tdElement = $(this);
					setTimeout( function(element) {
						$(element).closest("tr").next("tr").removeClass("collapsing");
				 		$(element).closest("tr").next("tr").addClass("collapse");
				    }, 400, tdElement );
				}
			})

			$(this).toggleClass("active");
			$(this).closest("tr").next("tr").removeClass("collapse");
			$(this).closest("tr").next("tr").addClass("collapsing");
			var tdElement = $(this);
			setTimeout( function(element) {
				$(element).closest("tr").next("tr").removeClass("collapsing");
			 	$(element).closest("tr").next("tr").addClass("show");
		    }, 100, tdElement );
		}
	});

	// Bind change event to the select element
	$(document).on( 'change', 'select[name="well_pad"]', function() {
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

		var userConfirmed = confirm( 'Are you sure you want to ' + params.interaction + ' this match?' );

		if( userConfirmed ) {
			ajaxMatch( params );
		}

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

	$(document).ready( function() {
		sortTables();
	})

})(jQuery);
