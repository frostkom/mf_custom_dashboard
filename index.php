<?php
/*
Plugin Name: MF Custom Dashboard
Plugin URI: https://github.com/frostkom/mf_custom_dashboard
Description: 
Version: 3.3.25
Licence: GPLv2 or later
Author: Martin Fors
Author URI: https://frostkom.se
Text Domain: lang_dashboard
Domain Path: /lang

Depends: Meta Box, MF Base
GitHub Plugin URI: frostkom/mf_custom_dashboard
*/

if(is_admin())
{
	include_once("include/classes.php");
	include_once("include/functions.php");

	$obj_custom_dashboard = new mf_custom_dashboard();

	register_activation_hook(__FILE__, 'activate_dashboard');
	register_uninstall_hook(__FILE__, 'uninstall_dashboard');

	add_action('init', 'init_dashboard');
	add_action('admin_init', 'settings_custom_dashboard');
	add_action('admin_init', array($obj_custom_dashboard, 'admin_init'), 0);
	add_action('admin_menu', 'menu_dashboard');

	add_action('admin_menu', 'disable_default_custom_dashboard', 999);
	add_action('wp_dashboard_setup', 'add_widget_custom_dashboard', 999);
	add_action('rwmb_meta_boxes', 'meta_boxes_custom_dashboard');

	add_filter('manage_mf_custom_dashboard_posts_columns', 'column_header_custom_dashboard', 5);
	add_action('manage_mf_custom_dashboard_posts_custom_column', 'column_cell_custom_dashboard', 5, 2);

	load_plugin_textdomain('lang_dashboard', false, dirname(plugin_basename(__FILE__))."/lang/");

	function activate_dashboard()
	{
		require_plugin("meta-box/meta-box.php", "Meta Box");
	}

	function uninstall_dashboard()
	{
		mf_uninstall_plugin(array(
			'post_types' => array('mf_custom_dashboard'),
			'options' => array('setting_panel_heading', 'setting_remove_widgets', 'setting_panel_hide_empty_containers', 'setting_panel_quote'),
		));
	}
}