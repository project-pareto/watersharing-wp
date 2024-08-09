<?php

// create the settings page
function watermanagement_settings_page() {
	// Get currently saved values (if any)
	$watersharing_toggle = get_option('watersharing_toggle', 0);
	$watertrading_toggle = get_option('watertrading_toggle', 0);

	$watersharing_checkbox = '<input type="checkbox" name="watersharing_toggle" value="1" ' . checked(1, $watersharing_toggle, false) . ' />';
	$watertrading_checkbox = '<input type="checkbox" name="watertrading_toggle" value="1" ' . checked(1, $watertrading_toggle, false) . ' />';

	$submit = get_submit_button( 'Update Settings' );

	echo "
	<div class='wrap'>
		<h1>Water Management Plugin Settings</h1>
		<p>This plugin recreates the water management features of <a href='https://share.producedwater.org'>share.producedwater.org</a>, a water management prototype website, developed as part of a collaboration between teh Ground Water Protection Council (watermanagement) and the US Department of Energy (DOE). The watermanagement tool provides all of the features to collect information about produced water avaialability and needs from users, and suggests mutually beneficial trades that minimize transportation distances between matched users.</p>
		<p>This plugin creates the necessary posts types, request forms, and dashboard to collect and store the users produced water information, it will then create an export JSON file of the data which can be used to compute a match.</p>
		<p>Functionality to match users together is provided by a separate Python script that will have to be installed on your server separately. You can find this script with instructions in the <a href='https://github.com/project-pareto/watersharing'>Git Repo</a>.
		<hr/>
		<form method='post' action='options.php'>
            ";
    settings_fields('watermanagement_settings_group');
    do_settings_sections('watermanagement_settings_group'); 
    echo "
            <h2>Choose Model</h2>
            <p>Decide which model you would like to have enabled</p>
            <h3>Enable Sharing</h3>
            $watersharing_checkbox
			<h3>Enable Trading</h3>
            $watertrading_checkbox
            $submit
        </form>
	</div>
	";
}

// save the settings fields
function save_watermanagement_settings() {
    register_setting('watermanagement_settings_group', 'watersharing_toggle');
	register_setting('watermanagement_settings_group', 'watertrading_toggle');
}
add_action('admin_init', 'save_watermanagement_settings');

?>
