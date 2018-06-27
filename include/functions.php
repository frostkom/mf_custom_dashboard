<?php

function init_dashboard()
{
	$labels = array(
		'name' => _x(__("Dashboard", 'lang_dashboard'), 'post type general name'),
		'singular_name' => _x(__("Dashboard", 'lang_dashboard'), 'post type singular name'),
		'menu_name' => __("Dashboard", 'lang_dashboard')
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'show_in_menu' => false,
		'show_in_nav_menus' => false,
		'exclude_from_search' => true,
		'supports' => array('title', 'editor'),
		'hierarchical' => true,
		'has_archive' => false,
		'capability_type' => 'page',
	);

	register_post_type('mf_custom_dashboard', $args);
}

function settings_custom_dashboard()
{
	$options_area = __FUNCTION__;

	add_settings_section($options_area, "", $options_area."_callback", BASE_OPTIONS_PAGE);

	$arr_settings = array();
	$arr_settings['setting_panel_heading'] = __("Heading", 'lang_dashboard');
	$arr_settings['setting_remove_widgets'] = __("Remove Widgets", 'lang_dashboard');
	$arr_settings['setting_panel_hide_empty_containers'] = __("Hide Empty Containers", 'lang_dashboard');

	show_settings_fields(array('area' => $options_area, 'settings' => $arr_settings));
}

function settings_custom_dashboard_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);

	echo settings_header($setting_key, __("Dashboard", 'lang_dashboard'));
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

	if(is_array($arr_widgets))
	{
		foreach($arr_widgets as $key => $value)
		{
			$arr_data[$key] = ($value != '' ? $value : $key);
		}

		echo show_select(array('data' => $arr_data, 'name' => $setting_key."[]", 'value' => $option));
	}

	else
	{
		echo "<em>".__("There are no widgets to remove", 'lang_dashboard')."</em>";
	}
}

function setting_panel_hide_empty_containers_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);
	$option = get_option($setting_key, 'yes');

	echo show_select(array('data' => get_yes_no_for_select(), 'name' => $setting_key, 'value' => $option));
}

function menu_dashboard()
{
	$menu_root = 'mf_custom_dashboard/';
	$menu_start = $menu_root.'list/index.php';
	$menu_capability = 'edit_pages';

	$menu_title = __("Customize", 'lang_dashboard');

	add_submenu_page("index.php", $menu_title, $menu_title, $menu_capability, "edit.php?post_type=mf_custom_dashboard");
}

function disable_default_custom_dashboard()
{
	$option = get_option('setting_remove_widgets');

	if(is_array($option))
	{
		foreach($option as $widget)
		{
			remove_meta_box($widget, 'dashboard', 'core');
			/*remove_meta_box($widget, 'dashboard', 'advanced');
			remove_meta_box($widget, 'dashboard', 'normal');
			remove_meta_box($widget, 'dashboard', 'side');*/
		}
	}
}

function widget_custom_dashboard($post, $args)
{
	global $wpdb;

	$result = $wpdb->get_results($wpdb->prepare("SELECT ID, post_content FROM ".$wpdb->posts." WHERE post_type = 'mf_custom_dashboard' AND ID = '%d'", $args['args']));

	foreach($result as $r)
	{
		$post_id = $r->ID;
		$post_content = trim(apply_filters('the_content', $r->post_content));

		if($post_content != '')
		{
			echo $post_content;

			if(IS_ADMIN)
			{
				echo "<a href='".admin_url("post.php?post=".$post_id."&action=edit")."' class='editable'><i class='fa fa-lg fa-edit'></i></a>";
			}
		}
	}
}

function add_widget_custom_dashboard()
{
	global $wp_meta_boxes, $wpdb;

	if(IS_ADMIN && is_array($wp_meta_boxes['dashboard']))
	{
		$option = get_option('dashboard_registered_widget');

		$arr_widgets = array();

		if(is_array($option))
		{
			foreach($option as $key => $value)
			{
				if(!is_array($value))
				{
					if(!isset($arr_widgets[$key]) || $value != '')
					{
						$arr_widgets[$key] = $value;
					}
				}
			}
		}

		foreach($wp_meta_boxes['dashboard'] as $widgets_1)
		{
			foreach($widgets_1 as $widgets_2)
			{
				foreach($widgets_2 as $key => $value)
				{
					if(!isset($arr_widgets[$key]) || $value['title'] != '')
					{
						$arr_widgets[$key] = $value['title'];
					}
				}
			}
		}

		update_option('dashboard_registered_widget', $arr_widgets, 'no');
	}

	$meta_prefix = "mf_cd_";

	$result = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->posts." WHERE post_type = 'mf_custom_dashboard' AND post_status = 'publish' ORDER BY menu_order ASC");

	foreach($result as $r)
	{
		$post_id = $r->ID;
		$post_title = $r->post_title;

		$post_permission = get_post_meta($post_id, $meta_prefix.'permission', true);

		if($post_permission == '' || current_user_can($post_permission))
		{
			$post_column = get_post_meta($post_id, $meta_prefix.'column', true);
			$post_priority = get_post_meta($post_id, $meta_prefix.'priority', true);

			if($post_column != '' && $post_priority != '')
			{
				add_meta_box("custom_dashboard_widget_".$post_id, $post_title, 'widget_custom_dashboard', 'dashboard', $post_column, $post_priority, $post_id);
			}

			else
			{
				wp_add_dashboard_widget("custom_dashboard_widget_".$post_id, $post_title, 'widget_custom_dashboard', '', $post_id);
			}
		}
	}
}

function get_columns_for_select()
{
	return array(
		'' => "-- ".__("Choose Here", 'lang_dashboard')." --",
		'normal' => __("Left", 'lang_dashboard'),
		'side' => __("Right", 'lang_dashboard'),
	);
}

function get_priority_for_select()
{
	return array(
		'' => "-- ".__("Choose Here", 'lang_dashboard')." --",
		'high' => __("High", 'lang_dashboard'),
		'default' => __("Default", 'lang_dashboard'),
		'low' => __("Low", 'lang_dashboard'),
	);
}

function meta_boxes_custom_dashboard($meta_boxes)
{
	$meta_prefix = "mf_cd_";

	$meta_boxes[] = array(
		'id' => $meta_prefix.'settings',
		'title' => __("Settings", 'lang_dashboard'),
		'post_types' => array('mf_custom_dashboard'),
		'context' => 'side',
		'priority' => 'low',
		'fields' => array(
			array(
				'name' => __("Lowest Permission", 'lang_dashboard'),
				'id' => $meta_prefix.'permission',
				'type' => 'select',
				'options' => get_roles_for_select(array('add_choose_here' => true)),
			),
			array(
				'name' => __("Column", 'lang_dashboard'),
				'id' => $meta_prefix.'column',
				'type' => 'select',
				'options' => get_columns_for_select(),
			),
			array(
				'name' => __("Priority", 'lang_dashboard'),
				'id' => $meta_prefix.'priority',
				'type' => 'select',
				'options' => get_priority_for_select(),
			),
		)
	);

	return $meta_boxes;
}

function column_header_custom_dashboard($cols)
{
	unset($cols['date']);

	$cols['permission'] = __("Lowest Permission", 'lang_dashboard');
	$cols['column'] = __("Column", 'lang_dashboard');
	$cols['priority'] = __("Priority", 'lang_dashboard');

	return $cols;
}

function column_cell_custom_dashboard($col, $id)
{
	$meta_prefix = "mf_cd_";

	$post_meta = get_post_meta($id, $meta_prefix.$col, true);

	if($post_meta != '')
	{
		switch($col)
		{
			case 'permission':
				$arr_roles = get_roles_for_select(array('add_choose_here' => false));

				echo $arr_roles[$post_meta];
			break;

			case 'column':
				$arr_columns = get_columns_for_select();

				echo $arr_columns[$post_meta];
			break;

			case 'priority':
				$arr_priority = get_priority_for_select();

				echo $arr_priority[$post_meta];
			break;
		}
	}
}