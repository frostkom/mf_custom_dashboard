<?php

function init_dashboard()
{
	$labels = array(
		'name' => _x(__('Custom Dashboard', 'lang_dashboard'), 'post type general name'),
		'singular_name' => _x(__('Custom Dashboard', 'lang_dashboard'), 'post type singular name'),
		'menu_name' => __("Custom Dashboard", 'lang_dashboard')
	);

	$args = array(
		'labels' => $labels,
		'public' => false,
		'show_ui' => true,
		'menu_position' => 99,
		'supports' => array('title', 'editor'),
		'hierarchical' => true,
		'has_archive' => false,
		'menu_icon' => 'dashicons-dashboard',
		'capability_type' => 'page',
	);

	register_post_type('mf_custom_dashboard', $args);
}

function disable_default_custom_dashboard()
{
	remove_meta_box('dashboard_activity', 'dashboard', 'core');
	remove_meta_box('dashboard_right_now', 'dashboard', 'core');
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'core');
	remove_meta_box('dashboard_incoming_links', 'dashboard', 'core');
	remove_meta_box('dashboard_plugins', 'dashboard', 'core');

	remove_meta_box('dashboard_quick_press', 'dashboard', 'core');
	remove_meta_box('dashboard_recent_drafts', 'dashboard', 'core');
	remove_meta_box('dashboard_primary', 'dashboard', 'core');
	remove_meta_box('dashboard_secondary', 'dashboard', 'core');

	//remove_meta_box('custom-contact-forms-dashboard', 'dashboard', 'normal');
	//remove_meta_box('wpfb-add-file-widget', 'dashboard', 'normal');
}

function custom_dashboard_widget($post, $args)
{
	global $wpdb;

	$result = $wpdb->get_results("SELECT post_content FROM ".$wpdb->posts." WHERE post_type = 'mf_custom_dashboard' AND ID = '".$args['args']."'");

	foreach($result as $r)
	{
		$post_content = $r->post_content;

		echo apply_filters('the_content', $post_content);
	}
}

function add_widget_custom_dashboard()
{
	global $wpdb;

	mf_enqueue_script('script_custom_dashboard', plugin_dir_url(__FILE__)."script_wp.js");

	$meta_prefix = "mf_cd_";

	$result = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->posts." WHERE post_type = 'mf_custom_dashboard' AND post_status = 'publish' ORDER BY menu_order ASC");

	foreach($result as $r)
	{
		$post_id = $r->ID;
		$post_title = $r->post_title;

		$post_permission = get_post_meta($post_id, $meta_prefix."permission", true);

		if($post_permission == '' || current_user_can($post_permission))
		{
			wp_add_dashboard_widget("custom_dashboard_widget_".$post_id, $post_title, 'custom_dashboard_widget', '', $post_id);
		}
	}
}

function meta_boxes_custom_dashboard($meta_boxes)
{
	global $wpdb;

	$meta_prefix = "mf_cd_";

	$roles = get_all_roles();

	$arr_permission = array(
		'' => "-- ".__("Choose here", 'lang_dashboard')." --"
	);

	foreach($roles as $key => $value)
	{
		$key = get_role_first_capability($key);

		$arr_permission[$key] = __($value);
	}

	$meta_boxes[] = array(
		'id' => $meta_prefix."settings",
		'title' => __('Settings', 'lang_dashboard'),
		'pages' => array('mf_custom_dashboard'),
		'context' => 'side',
		'priority' => 'low',
		'fields' => array(
			array(
				'name' => __('Lowest Permission', 'lang_dashboard'),
				'id' => $meta_prefix."permission",
				'type' => 'select',
				'options' => $arr_permission,
				//'multiple' => false,
			),
		)
	);

	return $meta_boxes;
}