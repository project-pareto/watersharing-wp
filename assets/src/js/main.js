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

	// alert user of changing request status
	$(document).on('click', '.post-status-submit', function(event) {
		var postAction = document.getElementById('post_action').value;

		if( postAction === '' ) {
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

})(jQuery);
