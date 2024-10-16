<?php

// create the settings page
function watertrading_settings_page() {
	// Get currently saved values (if any)
	$production_dashboard_page = get_option('production_dashboard_page');
	$consumption_dashboard_page = get_option('consumption_dashboard_page');

	$prod_form = wp_dropdown_pages(array(
		'name'             	=> 'production_dashboard_page',
		'selected'         	=> $production_dashboard_page,
		'show_option_none' 	=> 'Select a page',
		'echo'				=> 0
	));

	$cons_form = wp_dropdown_pages(array(
		'name'             	=> 'consumption_dashboard_page',
		'selected'         	=> $consumption_dashboard_page,
		'show_option_none' 	=> 'Select a page',
		'echo'				=> 0
	));

	$submit = get_submit_button( 'Update Settings' );

	echo "
	<div class='wrap'>
		<h1>Watertrading Plugin Settings</h1>
		<p>This plugin recreates the watertrading features of <a href='https://share.producedwater.org'>share.producedwater.org</a>, a water management prototype website, developed as part of a collaboration between teh Ground Water Protection Council (watersharing) and the US Department of Energy (DOE). The watersharing tool provides all of the features to collect information about produced water avaialability and needs from users, and suggests mutually beneficial trades that minimize transportation distances between matched users.</p>
		<p>This plugin creates the necessary posts types, request forms, and dashboard to collect and store the users produced water information, it will then create an export JSON file of the data which can be used to compute a match.</p>
		<p>Functionality to match users together is provided by a separate Python script that will have to be installed on your server separately. You can find this script with instructions in the <a href='https://github.com/project-pareto/watersharing'>Git Repo</a>.
		<hr/>
		<form method='post' action=''>
			<h2>Request Submission Settings</h2>
			<p>Select which page you want to redirect a user to once they have submitted the request form. Recommended to select the page with the corresponding dashboard block, this way a user will be directed to their own dashboard of requests once they submit.</p>
			<h3>Production Request Form Redirect</h3>
			$prod_form
			<br>
			<h3>Consumption Request Form Redirect</h3>
			$cons_form
			$submit
		</form>
	</div>
	";
}

// save the settings fields
function save_watertrading_settings() {
    if (isset($_POST['production_dashboard_page'])) {
        update_option('production_dashboard_page', $_POST['production_dashboard_page']);
    }
    if (isset($_POST['consumption_dashboard_page'])) {
        update_option('consumption_dashboard_page', $_POST['consumption_dashboard_page']);
    }
}
add_action('admin_init', 'save_watersharing_settings');

?>
