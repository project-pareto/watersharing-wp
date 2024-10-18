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

	//Code for accordion component
	$(document).ready(function () {
		// Function to show the collapse
		function showCollapse($element, $button) {
		  if (!$element.hasClass('collapsing') && !$element.hasClass('show')) {
			$element
			  .removeClass('collapse')
			  .addClass('collapsing') // Start collapsing animation
			  .css('height', 0); // Initial height is set to 0
			
			// Trigger a reflow to allow the height change to animate
			$element[0].offsetHeight;
	  
			// Get the scroll height to animate to
			var height = $element[0].scrollHeight;
			$element
			  .css('height', height + 'px') // Set the height for transition
			  .one('transitionend', function () {
				// Cleanup after transition ends
				$element.removeClass('collapsing').addClass('collapse show').css('height', '');
			  });
			  $button.removeClass('collapsed').attr('aria-expanded', true); // Remove collapsed state
		  }
		}
	  
		// Function to hide the collapse
		function hideCollapse($element, $button) {
		  if (!$element.hasClass('collapsing') && $element.hasClass('show')) {
			$element
			  .css('height', $element[0].scrollHeight + 'px') // Set height to current scroll height
			  .removeClass('collapse show')
			  .addClass('collapsing'); // Start collapsing animation
	  
			// Trigger a reflow
			$element[0].offsetHeight;
	  
			$element
			  .css('height', 0) // Animate to height of 0
			  .one('transitionend', function () {
				// Cleanup after transition ends
				$element.removeClass('collapsing').addClass('collapse').css('height', '');
			  });
			  $button.addClass('collapsed').attr('aria-expanded', false); // Add collapsed state
		  }
		}
	  
		// Add event listeners for all accordion buttons
		$('.accordion-button').on('click', function () {
			var targetSelector = $(this).attr('data-bs-target');
			var $target = $(targetSelector);
			var $button = $(this); 
		
			if ($target.hasClass('show')) {
				hideCollapse($target, $button); 
			} else {
				showCollapse($target, $button); 
			}
		});
		

		//Checkbox For disabling truck fields
		$('#trucks-checkbox').change(function() {
			// Check if the checkbox is checked
			if ($(this).is(':checked')) {
				// Enable the input fields if the checkbox is checked
				$('#truck_transport_radius, #truck_transport_bid, #truck_capacity').prop('disabled', false);
			} else {
				// Disable the input fields if the checkbox is unchecked
				$('#truck_transport_radius, #truck_transport_bid, #truck_capacity').prop('disabled', true);
			}
		});
	
		// Initially disable the inputs when the page loads
		$('#truck_transport_radius, #truck_transport_bid, #truck_capacity').prop('disabled', true);

		//Checkbox For disabling layflat fields
		$('#layflats-checkbox').change(function() {
			// Check if the checkbox is checked
			if ($(this).is(':checked')) {
				// Enable the input fields if the checkbox is checked
				$('#layflats_transport_radius, #layflats_transport_bid, #layflats_capacity').prop('disabled', false);
			} else {
				// Disable the input fields if the checkbox is unchecked
				$('#layflats_transport_radius, #layflats_transport_bid, #layflats_capacity').prop('disabled', true);
			}
		});
	
		// Initially disable the inputs when the page loads
		$('#layflats_transport_radius, #layflats_transport_bid, #layflats_capacity').prop('disabled', true);

		//Calculating total / specific bid value
		$("input[name='bid_amount'], input[name='rate_bpd'], #bid_units").change(function(){
			var $bid = $("input[name='bid_amount']");
			var $rate = $("input[name='rate_bpd']");
			var $units = $("#bid_units");
			
			var bid = parseInt($bid.val(), 10);
			var rate = parseInt($rate.val(), 10);
			var unitsValue = $units.val();
			if (!isNaN(bid) && !isNaN(rate) && unitsValue != null) {
				if(unitsValue == "USD/bbl.day"){
					$("#bid_total").val(bid * rate);
					$("#bid_specific_total").val(bid);
				}
				else{
					$("#bid_total").val(bid);
					$("#bid_specific_total").val(bid / rate);
				}
			} else {
				$("#bid_total").val('');
				$("#bid_specific_total").val('');
			}
		});
			
	});

	document.addEventListener('DOMContentLoaded', function() {
		const ctx = document.getElementById('stat-chart').getContext('2d');
	  
		new Chart(ctx, {
		  type: 'line',
		  data: {
			labels: ['1/1/2024', '2/1/2024', '3/1/2024', '4/1/2024', '5/1/2024', '6/1/2024'],
			datasets: [{
			  label: 'Ongoing trades',
			  data: [12, 19, 3, 5, 2, 3],
			  borderWidth: 1
			}]
		  },
		  options: {
			scales: {
			  y: {
				beginAtZero: true
			  }
			}
		  }
		});
	});
})(jQuery);
