<?php
/*
Plugin Name: MF Custom Dashboard
Plugin URI: https://github.com/frostkom/mf_custom_dashboard
Description:
Version: 3.4.19
Licence: GPLv2 or later
Author: Martin Fors
Author URI: https://martinfors.se
Text Domain: lang_dashboard
Domain Path: /lang

Depends: Meta Box, MF Base
GitHub Plugin URI: frostkom/mf_custom_dashboard
*/

if(!function_exists('is_plugin_active') || function_exists('is_plugin_active') && is_plugin_active("mf_base/index.php") && is_admin())
{
	include_once("include/classes.php");

	$obj_custom_dashboard = new mf_custom_dashboard();

	register_activation_hook(__FILE__, 'activate_dashboard');
	register_uninstall_hook(__FILE__, 'uninstall_dashboard');

	add_action('init', array($obj_custom_dashboard, 'init'));
	add_action('admin_init', array($obj_custom_dashboard, 'settings_custom_dashboard'));
	add_action('admin_init', array($obj_custom_dashboard, 'admin_init'), 0);
	add_action('admin_menu', array($obj_custom_dashboard, 'admin_menu'));

	add_action('admin_menu', array($obj_custom_dashboard, 'disable_default'), 999);
	add_action('wp_dashboard_setup', array($obj_custom_dashboard, 'add_widget'), 999);
	add_action('rwmb_meta_boxes', array($obj_custom_dashboard, 'rwmb_meta_boxes'));

	add_filter('manage_mf_custom_dashboard_posts_columns', array($obj_custom_dashboard, 'column_header'), 5);
	add_action('manage_mf_custom_dashboard_posts_custom_column', array($obj_custom_dashboard, 'column_cell'), 5, 2);

	add_filter('filter_last_updated_post_types', array($obj_custom_dashboard, 'filter_last_updated_post_types'), 10, 2);

	function activate_dashboard()
	{
		require_plugin("meta-box/meta-box.php", "Meta Box");
	}

	function uninstall_dashboard()
	{
		include_once("include/classes.php");

		$obj_custom_dashboard = new mf_custom_dashboard();

		mf_uninstall_plugin(array(
			'post_types' => array($obj_custom_dashboard->post_type),
			'options' => array('setting_panel_heading', 'setting_remove_widgets', 'setting_panel_hide_empty_containers', 'setting_panel_quote'),
		));
	}
}