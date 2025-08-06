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

	// Location Select Validation
	$(document).on('blur', 'input#longitude, input#latitude', function() {
		var form = $(this).closest('form');
		var longitudeInput = form.find('input#longitude');
		var latitudeInput = form.find('input#latitude');
		
		var longitudeValue = parseFloat(longitudeInput.val());
		var latitudeValue = parseFloat(latitudeInput.val());

		// Check if both longitude and latitude values are valid numbers
		if (!isNaN(longitudeValue) && !isNaN(latitudeValue)) {
			if (longitudeValue < -124.785543 || longitudeValue > -66.945554 ||
				latitudeValue < 24.446667 || latitudeValue > 49.382812) {
				alert('Coordinates must fall within continental USA');
				longitudeInput.val('');
				latitudeInput.val('');
			}
		}
	});

	$(document).on('click', '.create-post-submit-button', function(event) {
		const $form = $(this).closest('form');
		const $requiredFields = $form.find('[required]');
		const $emptyRequired = $requiredFields.filter(function() {
			return $(this).val().trim() === '';
		});
		
		if ($emptyRequired.length > 0) {
			alert('Please fill in all required fields in the Primary Information section.');
		}
		return; // intentionally allowing form submit to trigger the browser's client-side validation ui (form wont actually submit with empty required)
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
	$(document).on('change', 'select[name="well_pad"]', function() {
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
		// Show the loading indicator based on the table type
		var type = params.dataTable.find('table').attr('id').split('-')[0]; // Gets 'trade_supply' or 'trade_demand'
		$('.' + type + '-loading-indicator').show();

		$.ajax({
			url: '/wp-admin/admin-ajax.php',
			type: 'POST',
			data: {
				action			: 'ajax_approval',
				lookup_record	: params.lookupid,
				parent_record	: params.parentid,
				match_record	: params.matchid,
				action_status	: params.interaction,
				action_type		: params.interactiontype
			},
			dataType: 'json',

			beforeSend: function(xhr) {
				$(params.dataTable).html('');
			},
			success: function(output) {
				console.log(output);
				$(params.dataTable).html(output);
				sortTables();
			},
			complete: function(xhr) {
				// Hide the loading indicator after the content loads
				$('.' + type + '-loading-indicator').hide();
			},
			error: function(result) {
				// Hide the loading indicator in case of error
				$('.' + type + '-loading-indicator').hide();
			}
		});
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
	});

	function runMoveMes() {
		// console.log('Running moveMes function');
		const $appendTos = jQuery('[data-append-to]');
		$appendTos.each(function() {
			const $ele = jQuery(this);
			const targetSelector = $ele.data('append-to');
			const $target = jQuery(targetSelector);

			if ($target.length) {
				$ele.appendTo($target);
				$ele.removeAttr('data-append-to');
				$ele.addClass('js-moved');
			} else {
				console.warn(`Target element ${targetSelector} not found for`, $ele);
			}
		});
	}

	function runDomMutators() {
		// console.log('Running runDomMutators function');
		runMoveMes();
	}

	function updateSendToForm(sPid, tableType){
		console.log('actual typeof sPid:', typeof sPid);
		console.log('tableType:', tableType);

		// this function finds the send-to form information for this pid and finds form fields in the send-to form with names matching the keys in the data and fills the fields found with matching names to its keys
		console.log('Updating send-to form for PID:', sPid, 'in table:', tableType);
		
		// Check if we have the global send-to data
		if (typeof window.sendToData === 'undefined') {
			console.warn('window.sendToData is not defined');
			return;
		}
		
		// Check if we have data for this table type
		if (!window.sendToData[tableType]) {
			console.warn('No data found for table type:', tableType);
			console.warn('Available table types:', Object.keys(window.sendToData));
			return;
		}
		
		// Debug: log available keys and their types for this table
		console.log('Available sendToData keys for', tableType, ':', Object.keys(window.sendToData[tableType]));
		console.log('Looking for key:', sPid, 'as string:', String(sPid), 'as number:', Number(sPid));
		
		// Try both string and numeric lookups within the correct namespace
		let formData = window.sendToData[tableType][sPid] || window.sendToData[tableType][String(sPid)] || window.sendToData[tableType][Number(sPid)];
		
		if (!formData) {
			console.warn('No send-to data found for PID:', sPid, 'in table:', tableType);
			console.warn('Available keys:', Object.keys(window.sendToData[tableType]));
			return;
		}
		
		console.log('Form data for PID', sPid, ':', formData);
		
		// Find the send-to dialog form
		const sendToDialog = document.querySelector('.send-to-dialog');
		if (!sendToDialog) {
			console.warn('Send-to dialog not found');
			return;
		}
		
		// Iterate through each field in the form data
		Object.keys(formData).forEach(fieldName => {
			const fieldValue = formData[fieldName];
			
			// Skip the original pid field - it shouldn't be in the form except as cloned_from
			if (fieldName === 'pid') {
				return;
			}
			
			// Look for form fields with matching names (including hidden fields)
			const field = sendToDialog.querySelector(`[name="${fieldName}"]`);
			
			if (field) {
				if (field.type === 'checkbox') {
					// Handle checkboxes
					field.checked = fieldValue == '1' || fieldValue === true || fieldValue === 'true';
				} else if (field.type === 'radio') {
					// Handle radio buttons - find the one with matching value
					const radioGroup = sendToDialog.querySelectorAll(`[name="${fieldName}"]`);
					radioGroup.forEach(radio => {
						if (radio.value === fieldValue) {
							radio.checked = true;
						}
					});
				} else if (field.tagName.toLowerCase() === 'select') {
					// Handle select dropdowns
					field.value = fieldValue;
				} else {
					// Handle text inputs, hidden fields, textareas, etc.
					field.value = fieldValue || '';
				}
				
				console.log(`Set field ${fieldName} to:`, fieldValue);
			} else {
				console.log(`Field ${fieldName} not found in form`);
			}
		});
	}

	function setupSendToDialogs() {
		console.log('Setting up Send-to dialogs');
		const sendToDialog = document.querySelector(".send-to-dialog");
		const sendToButtons = document.querySelectorAll(".send-to-btn");

		if( sendToDialog && sendToButtons.length) {
			sendToButtons.forEach(function(sendToButton) {
				const sPid = sendToButton.getAttribute('data-pid');
				const tableType = sendToButton.getAttribute('data-table-type');
				// console.log('sendToButton',sendToButton);
				// console.log('sPid',sPid, 'tableType:', tableType);
				sendToButton.addEventListener('click', function() {
					console.log('Opening dialog for PID:', sPid, 'tableType:', tableType);
					updateSendToForm(sPid, tableType);
					sendToDialog.showModal();
				});
			});
			sendToDialog.querySelector(".dialog-closer").onclick = function () {
				// console.log('Closing dialog');
				sendToDialog.close();
			};
		}

	}

	$(document).ready(function () {
		console.log('READY');
		runDomMutators();

		setupSendToDialogs();

		// Function to show the collapse
		function showCollapse($element, $button) {
			if (!$element.hasClass('collapsing') && (!$element.hasClass('show') && !$element.hasClass('show-initial'))) {
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

		function hideOtherAccordions($target) {
			const $otherAccordions = $('.accordion-collapse').not($target);

			$otherAccordions.each(function() {
				const $otherAccordion = $(this);
				const $otherButton = $('[data-bs-target="#' + $otherAccordion.attr('id') + '"]');
				if($otherAccordion && $otherButton) {
					hideCollapse($otherAccordion, $otherButton);
				}
			});
		}		
	  
		// Function to hide the collapse
		function hideCollapse($element, $button) {
			if (!$element.hasClass('collapsing') || $element.hasClass('show-initial') ) {
				$element
				.css('height', $element[0].scrollHeight + 'px') // Set height to current scroll height
				.removeClass('collapse show show-initial')
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

			if ($target.hasClass('show') || $target.hasClass('show-initial')) {
				hideCollapse($target, $button); 
			} else {
				showCollapse($target, $button);
				hideOtherAccordions($target);
			}
		});
		
		// Checkbox for disabling fields by class and proximity
		$('.checkbox').change(function() {
			const isChecked = $(this).is(':checked');
			
			// Find related input fields within the same container as the checkbox
			$(this).closest('.watersharing-row')
				.find('input[type="number"]')
				.prop('disabled', !isChecked);
		});

		// Initially disable specific inputs on page load
		$('.checkbox').each(function() {
			const relatedInputPrefix = $(this).attr('class').split(' ').find(cls => cls.includes('trade_supply') || cls.includes('trade_demand'));

			if (relatedInputPrefix) {
				$(this).closest('.watersharing-row')
					.find(`input[type="number"][class*="${relatedInputPrefix.split('-')[0]}"]`)
					.prop('disabled', true);
			}
		});

		//Calculating total / specific bid value
		$(".trade_supply-bid_amount, .trade_supply-rate_bpd, .trade_supply-bid_units, .trade_demand-bid_amount, .trade_demand-rate_bpd, .trade_demand-bid_units").change(function(){
			// console.log('Input changed:', this);
			// Determine the prefix based on the triggered element's class
			var prefix = $(this).hasClass("trade_supply-bid_amount") || $(this).hasClass("trade_supply-rate_bpd") || $(this).hasClass("trade_supply-bid_units") ? "trade_supply-" : "trade_demand-";
			// console.log('Prefix determined:', prefix);
			// Use the prefix to find the respective elements within the same group
			var $bid = $("." + prefix + "bid_amount");
			var $rate = $("." + prefix + "rate_bpd");
			var $units = $("." + prefix + "bid_units");
			var $total = $("." + prefix + "totalval");
			var $specificTotal = $("." + prefix + "specval");
		
			// Parse input values
			var bid = parseFloat($bid.val());
    		var rate = parseFloat($rate.val());
			var unitsValue = $units.val();
		
			if (!isNaN(bid) && !isNaN(rate) && unitsValue != null) {
				if (unitsValue == "USD/bbl.day") {
					$total.val(formatNumber((bid * rate).toFixed(2))); 
					$specificTotal.val(formatNumber(bid.toFixed(2))); 
				} else {
					$total.val(formatNumber(bid.toFixed(2))); 
					$specificTotal.val(formatNumber((bid / rate).toFixed(2))); 
				}
			} else {
				$total.val('');
				$specificTotal.val('');
			}
		});		

		// Function to format numbers with thousands separator
		function formatNumber(num) {
			return num.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		}
	});

	document.addEventListener('DOMContentLoaded', function() {
		// Loop through each chart container
		document.querySelectorAll('.chart-container').forEach(container => {
			// Get unique blockId and chart data from data attributes
			const blockId = container.getAttribute('data-block-id');
			const chartData = JSON.parse(container.getAttribute('data-chart-data'));  // Parse JSON string to object
	
			// Select the specific canvas for this block
			const chartElement = document.getElementById(`stat-chart-${blockId}`);
	
			if (chartElement && chartData) {
				const ctx = chartElement.getContext('2d');
	
				// Extract data for the chart
				const volumes = chartData.map(trade => trade.volume);
				const dates = chartData.map(trade => trade.date);
	
				new Chart(ctx, {
					type: 'line',
					data: {
						labels: dates,
						datasets: [{
							label: 'Ongoing trades',
							data: volumes,
							borderWidth: 1
						}]
					},
					options: {
						scales: {
							y: {
								beginAtZero: true,
								title: {
									display: true,
									text: 'Volume(bbl)'
								}
							}
						},
						plugins: {
							legend: {
								display: false
							},
							title: {
								display: true,
								text: "Ongoing Trades",
								font: {
									size: 20,
									weight: 'bold'
								}
							},
							tooltip: {
								enabled: false
							}
						},
						responsive:true,
						maintainAspectRatio: false
					}
				});
			}
		});

		$(document).on('click', '.download-summary-btn', function(e) {
			e.preventDefault();
		
			const tradeCsv = $(this).data('tradeCsv'); 
		
			$.ajax({
				url: my_ajax_object.ajax_url,
				method: 'POST',
				data: {
					action: 'download_latest_summary',
					trade_csv: tradeCsv // Pass the trade_csv value
				},
				xhrFields: {
					responseType: 'blob' // Ensure response is treated as binary blob
				},
				success: function(response) {
					if (response instanceof Blob && response.size) {
						const url = window.URL.createObjectURL(response);
						const a = document.createElement('a');
						a.style.display = 'none';
						a.href = url;
						a.download = tradeCsv + ' Summary.csv';
						document.body.appendChild(a);
						a.click();
						window.URL.revokeObjectURL(url);
					} else {
						console.error("Invalid or empty Blob response:", response);
						alert("The file could not be downloaded.");
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.error("AJAX error:", textStatus, errorThrown);
					alert("Could not download the file. May be missing or empty, please try again.");
				}
			});
		});
	(jQuery);
});


	window.downloadCsv = function(adminUrl, volumeData) {
		const formData = new FormData();
		formData.append('action', 'download_csv');
		formData.append('csv_data', JSON.stringify(volumeData));
	
		fetch(adminUrl, {
			method: 'POST',
			body: formData
		})
		.then(response => {
			if (response.ok) {
				return response.blob();
			}
			throw new Error('Network response was not ok.');
		})
		.then(blob => {
			const url = window.URL.createObjectURL(blob);
			const a = document.createElement('a');
			a.style.display = 'none';
			a.href = url;
			a.download = 'trades_data.csv';
			document.body.appendChild(a);
			a.click();
			window.URL.revokeObjectURL(url);
		})
		.catch(error => console.error('There was an error with the download:', error));
	};
})(jQuery);


