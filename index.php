<?php
/*
Plugin Name: MF Custom Dashboard
Plugin URI: github.com/frostkom/mf_custom_dashboard
Description: 
Version: 1.1.7
Author: Martin Fors
Author URI: http://frostkom.se
*/

if(is_admin())
{
	include_once("include/functions.php");

	load_plugin_textdomain('lang_dashboard', false, dirname(plugin_basename(__FILE__))."/lang/");

	register_activation_hook(__FILE__, 'activate_dashboard');

	add_action('init', 'init_dashboard');
	add_action('admin_menu', 'disable_default_custom_dashboard');
	add_action('wp_dashboard_setup', 'add_widget_custom_dashboard');
	add_action('rwmb_meta_boxes', 'meta_boxes_custom_dashboard');

	function activate_dashboard()
	{
		require_plugin("meta-box/meta-box.php", "Meta Box");
	}
}