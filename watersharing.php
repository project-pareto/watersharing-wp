<?php

/**
 * watersharing PARETO Water Share Plugin
 *
 * @package           WordPress Plugin
 * @author            Troy Web Consulting
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Water Sharing
 * Plugin URI:        https://troyweb.com
 * Description:       Plugin recreates the basic features of <strong>share.producedwater.org</strong>, a produced water management prototype developed as part of a collaboration between the <strong>Ground Water Protection Council (watersharing)</strong> and the <strong>US Department of Energy (DOE)</strong>. Water management was created to collect information about produced water availability and needs from users, and suggests mutually beneficial trades that minimize transportation distances between users.
 * Version:           0.1.0
 * Author:            Troy Web Consulting
 * Author URI:        https://troyweb.com
 * License:           GPL v2 or later
 */



// register plugin hook
register_activation_hook(__FILE__, 'watersharing_plugin_activate');
function watersharing_plugin_activate()
{
	flush_rewrite_rules();
}

// deregister plugin hook
register_deactivation_hook(__FILE__, 'watersharing_plugin_deactivate');
function watersharing_plugin_deactivate()
{
	flush_rewrite_rules();
}

//define wordpre plugin directory path
define('WATERSHARING_PLUGIN_PATH', plugin_dir_path(__FILE__));

// enqueue plugin styles and scripts
function watersharing_plugin_enqueue()
{
	wp_enqueue_style( 'fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css', array(), false );
	wp_enqueue_style('watersharing-styles', plugins_url('assets/dist/css/watersharing.min.css', __FILE__), array(), filemtime(plugin_dir_path(__FILE__) . 'assets/dist/css/watersharing.min.css') );
	wp_enqueue_style('datatables-styles', 'https://cdn.datatables.net/v/bs5/dt-1.13.4/datatables.min.css', array(), null);

 	wp_enqueue_script('jquery');
	wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/v/bs5/dt-1.13.4/datatables.min.js', array('jquery'), null, false);
	wp_enqueue_script('watersharing-scripts', plugins_url('assets/dist/js/watersharing.min.js', __FILE__), array('jquery'), null, false);
	wp_enqueue_script('tablesort', plugins_url('assets/dist/libs/tablesort.js', __FILE__), array('jquery'), null, false);
}
add_action('wp_enqueue_scripts', 'watersharing_plugin_enqueue');

// enqueue the styles and scripts for WP Admin
function watersharing_admin_enqueue( $hook ) {
	wp_enqueue_style('watersharing-styles', plugins_url('assets/dist/css/watersharing-admin.min.css', __FILE__), array(), filemtime(plugin_dir_path(__FILE__) . 'assets/dist/css/watersharing.min.css') );
}
add_action( 'admin_enqueue_scripts', 'watersharing_admin_enqueue' );

// setup the settings page
function watersharing_menu() {

	require_once( plugin_dir_path( __FILE__ ) . 'inc/watersharing-settings.php' );

	add_menu_page(
        'Watersharing',
        'Watersharing',
        'edit_posts',
		'watersharing-settings',
		'watersharing_settings_page',
        'dashicons-location',
        6
    );

	add_submenu_page(
		'watersharing-settings',
		'Watersharing Settings',
		'Settings',
		'edit_posts',
		'watersharing-settings',
	);

	add_submenu_page(
		'watersharing-settings',
		'Production (Have Water) Requests',
		'Production',
		'edit_posts',
		'edit.php?post_type=water_supply',
	);

	add_submenu_page(
		'watersharing-settings',
		'Consumption (Need Water) Requests',
		'Consumption',
		'edit_posts',
		'edit.php?post_type=water_demand',
	);

	add_submenu_page(
		'watersharing-settings',
		'Match Lookup',
		'Match Lookup',
		'edit_posts',
		'edit.php?post_type=matched_requests',
	);
}

// Water Management Menu - Contains Settings with Trading / Sharing Toggle
function watermanagement_menu() {

	require_once( plugin_dir_path( __FILE__ ) . 'inc/watermanagement-settings.php' );

	add_menu_page(
        'WaterManagement',
        'WaterManagement',
        'edit_posts',
		'watermanagement-settings',
		'watermanagement_settings_page',
        'dashicons-location',
        5
    );

	add_submenu_page(
		'watermanagement-settings',
		'Watermanagement Settings',
		'Settings',
		'edit_posts',
		'watermanagement-settings',
	);
	
	add_submenu_page(
		'watermanagement-settings',
		'Well Pads',
		'Well Pads',
		'edit_posts',
		'edit.php?post_type=well_pad',
	);
}
$watersharing_toggle = get_option('watersharing_toggle');
if($watersharing_toggle){
	add_action( 'admin_menu', 'watersharing_menu' );
}
add_action( 'admin_menu', 'watermanagement_menu' );

// require plugin files
require_once( 'inc/types-taxonomies.php' );
require_once( 'inc/element-builders.php' );
require_once( 'inc/dashboard-ajax.php' );
require_once( 'inc/watersharing-functions.php' );
require_once( 'inc/watersharing-settings.php' );
require_once( 'inc/watersharing-json-exporter.php' );

// define custom block categories
function register_custom_guttenberg_categories( $categories ) {

	$categories[] = array(
		'slug'	=> 'watersharing',
		'title'	=> 'Watersharing Blocks'
	);

	return $categories;
}
if ( version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
	add_filter( 'block_categories_all', 'register_custom_guttenberg_categories' );
} else {
	add_filter( 'block_categories', 'register_custom_guttenberg_categories' );
}

// register guttenberg blocks
function register_watersharing_blocks()
{
    $blocks_dir = __DIR__ . '/blocks/build/';
    $block_directories = array_filter( glob( $blocks_dir . '*' ), 'is_dir' );

    foreach ( $block_directories as $block_dir ) {
        register_block_type( $block_dir );
    }
}
if($watersharing_toggle){
	add_action('init', 'register_watersharing_blocks');
}

// handle request dashboard post updates
function change_post_status_callback() {
	if( isset( $_POST['post_ids'] ) && is_array( $_POST['post_ids'] ) ) {
		$post_action = isset($_POST['post_action']) ? $_POST['post_action'] : '';

		foreach( $_POST['post_ids'] as $post_id ) {
			if( $post_action === 'delete' ) {
				wp_trash_post($post_id);
			}
			elseif( $post_action === 'close' ) {
				update_post_meta( $post_id, 'status', 'closed' );
			}
		}
	}

	// Redirect back to the previous page after the action is completed
	wp_safe_redirect(wp_get_referer() ? wp_get_referer() : home_url());
	exit;
}
add_action('admin_post_change_post_status', 'change_post_status_callback');
add_action('admin_post_nopriv_change_post_status', 'change_post_status_callback');
