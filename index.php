<?php
/*
Plugin Name: MF Custom Dashboard
Plugin URI: 
Version: 1.1.2
Author: Martin Fors
Author URI: www.frostkom.se
*/

register_activation_hook(__FILE__, 'activate_dashboard');

add_action('init', 'init_dashboard');
add_action('admin_menu', 'disable_default_custom_dashboard');
add_action('wp_dashboard_setup', 'add_widget_custom_dashboard');
add_action('rwmb_meta_boxes', 'meta_boxes_custom_dashboard');

load_plugin_textdomain('lang_dashboard', false, dirname(plugin_basename(__FILE__))."/lang/");

include("include/functions.php");

function activate_dashboard()
{
	require_plugin("meta-box/meta-box.php", "Meta Box");
}