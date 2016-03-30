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
	$option = get_option('setting_remove_widgets');

	if(is_array($option))
	{
		foreach($option as $widget)
		{
			remove_meta_box($widget, 'dashboard', 'core');
		}
	}
}

function custom_dashboard_widget($post, $args)
{
	global $wpdb;

	$result = $wpdb->get_results($wpdb->prepare("SELECT post_content FROM ".$wpdb->posts." WHERE post_type = 'mf_custom_dashboard' AND ID = '%d'", $args['args']));

	foreach($result as $r)
	{
		$post_content = $r->post_content;

		echo apply_filters('the_content', $post_content);
	}
}

function add_widget_custom_dashboard()
{
	global $wp_meta_boxes, $wpdb;

	if(IS_ADMIN && is_array($wp_meta_boxes['dashboard']))
	{
		$option = get_option('dashboard_registered_widget');

		$arr_widgets = array();

		foreach($wp_meta_boxes['dashboard'] as $widgets_1)
		{
			foreach($widgets_1 as $widgets_2)
			{
				foreach($widgets_2 as $key => $value)
				{
					$arr_widgets[$key] = $value['title'];
				}
			}
		}

		if(is_array($option))
		{
			foreach($option as $key => $value)
			{
				if(!is_array($value))
				{
					$arr_widgets[$key] = $value;
				}
			}
		}

		update_option('dashboard_registered_widget', $arr_widgets);
	}

	$user_data = get_userdata(get_current_user_id());

	$setting_panel_heading = get_option('setting_panel_heading');
	$setting_panel_heading = str_replace("[name]", $user_data->first_name, $setting_panel_heading);

	mf_enqueue_script('script_custom_dashboard', plugin_dir_url(__FILE__)."script_wp.js", array('panel_heading' => $setting_panel_heading));

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
				'options' => get_roles_for_select(array('add_choose_here' => true)),
				//'multiple' => false,
			),
		)
	);

	return $meta_boxes;
}

function settings_custom_dashboard()
{
	$options_area = __FUNCTION__;

	add_settings_section($options_area, "", $options_area."_callback", BASE_OPTIONS_PAGE);

	$arr_settings = array();

	$arr_settings["setting_panel_heading"] = __("Heading", 'lang_dashboard');
	$arr_settings["setting_remove_widgets"] = __("Remove widgets", 'lang_dashboard');

	foreach($arr_settings as $handle => $text)
	{
		add_settings_field($handle, $text, $handle."_callback", BASE_OPTIONS_PAGE, $options_area);

		register_setting(BASE_OPTIONS_PAGE, $handle);
	}
}

function settings_custom_dashboard_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);

	echo settings_header($setting_key, __("Custom Dashboard", 'lang_dashboard'));
}

function setting_panel_heading_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);
	$option = get_option($setting_key);

	echo show_textfield(array('name' => $setting_key, 'value' => $option, 'placeholder' => __("Welcome", 'lang_dashboard')." [name]"));
}

function setting_remove_widgets_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);
	$option = get_option($setting_key);

	$arr_widgets = get_option('dashboard_registered_widget');

	$arr_data = array();

	foreach($arr_widgets as $key => $value)
	{
		$arr_data[$key] = $value;
	}

	echo show_select(array('data' => $arr_data, 'name' => $setting_key."[]", 'compare' => $option));
}