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
		<p>This plugin recreates the water management features of <a href='https://share.producedwater.org'>share.producedwater.org</a>, a water management prototype website, developed as part of a collaboration between the Ground Water Protection Council and the US Department of Energy (DOE). The watermanagement tool provides all of the features to collect information about produced water avaialability and needs from users, and suggests mutually beneficial trades that minimize transportation distances between matched users.</p>
		<hr/>
		
		<form method='post' action='options.php'>
            ";
    settings_fields('watermanagement_settings_group');
    do_settings_sections('watermanagement_settings_group'); 
    echo "
            <h2>Choose Model</h2>
            <p>Decide which model you would like to have enabled.
			This will hide the tab associated with the model as well
			as it's gutenberg blocks.</p>
            <h3>Enable Sharing</h3>
            $watersharing_checkbox
			<h3>Enable Trading</h3>
            $watertrading_checkbox
            $submit
        </form>
		
		<hr/>

		<h2>Setup Guide</h2>
		<p>This plugin creates the necessary posts types, request 
		forms, and dashboards to collect and store the users 
		produced water information, it will then create an export JSON 
		file of the data which can be used to compute a
		match. The blocks needed to add the aforementioned forms and 
		dashboards(along with some additional data viewing blocks for
		trading) to your site can be found and used in the block
		editor and show up as follows:</p>
		
		<div style='display: flex;'>
			<img src=\"../wp-content/plugins/watersharing-wp/assets/img/trading_blocks.png\" 
			class = \"instructional-images\"' />	
			<img src=\"../wp-content/plugins/watersharing-wp/assets/img/sharing_blocks.png\" 
			class = \"instructional-images\"' />
		</div>
		
		<p>For detailed guidance on how to use the block editor, 
		please visit 
		<a href='https://developer.wordpress.org/block-editor/'>
		the official Wordpress documentation.</a></p>
		
		<p>Functionality to match users together is provided by a 
		separate Python script that will have to be installed
		on your server separately. You can find this script with 
		instructions in the 
		<a href='https://github.com/project-pareto/watersharing'>
		Git Repo</a>.

		<h2>Design Guidelines</h2>
		<p>In order for this plugin to function correctly, the site 
		manager must make sure that they are placing the correct 
		blocks on their site. For both trading and sharing, the site 
		must have, at minimum both(supply/demand) forms and requests 
		dashboards. Without these, the users cannot sufficiently
		add requests and approve/deny them. Below is an example of
		the sharing and trading blocks with the ones that are
		absolutely necessary for their respective models to function
		highlighted. </p>

		<div style='display: flex;'>
			<img src=\"../wp-content/plugins/watersharing-wp/assets/img/trading_blocks_req.png\" 
			class = \"instructional-images\"' />	
			<img src=\"../wp-content/plugins/watersharing-wp/assets/img/sharing_blocks_req.png\" 
			class = \"instructional-images\"' />
		</div>

		<hr/>
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
