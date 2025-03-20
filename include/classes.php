<?php

class mf_custom_dashboard
{
	var $post_type = 'mf_custom_dashboard';
	var $meta_prefix = 'mf_cd_';

	function __construct(){}

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

	function init()
	{
		load_plugin_textdomain('lang_dashboard', false, str_replace("/include", "", dirname(plugin_basename(__FILE__)))."/lang/");

		// Post types
		#######################
		register_post_type($this->post_type, array(
			'labels' => array(
				'name' => _x(__("Dashboard", 'lang_dashboard'), 'post type general name'),
				'singular_name' => _x(__("Dashboard", 'lang_dashboard'), 'post type singular name'),
				'menu_name' => __("Dashboard", 'lang_dashboard')
			),
			'public' => false, // Previously true but changed to hide in sitemap.xml
			'show_ui' => true,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'exclude_from_search' => true,
			'supports' => array('title', 'editor'),
			'hierarchical' => true,
			'has_archive' => false,
			'capability_type' => 'page',
		));
		#######################
	}

	function settings_custom_dashboard()
	{
		$options_area = __FUNCTION__;

		add_settings_section($options_area, "", array($this, $options_area."_callback"), BASE_OPTIONS_PAGE);

		$arr_settings = array();
		$arr_settings['setting_panel_heading'] = __("Heading", 'lang_dashboard');
		$arr_settings['setting_remove_widgets'] = __("Remove Widgets", 'lang_dashboard');
		$arr_settings['setting_panel_hide_empty_containers'] = __("Hide Empty Containers", 'lang_dashboard');

		show_settings_fields(array('area' => $options_area, 'object' => $this, 'settings' => $arr_settings));
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
				$arr_data[$key] = ($value != '' ? strip_tags($value) : $key);
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

	function admin_init()
	{
		global $pagenow;

		if($pagenow == 'index.php')
		{
			$plugin_include_url = plugin_dir_url(__FILE__);

			$user_data = get_userdata(get_current_user_id());
			$user_display_name = $user_data->first_name != '' ? $user_data->first_name : ucfirst($user_data->display_name);

			$setting_panel_heading = get_option('setting_panel_heading');
			$setting_remove_widgets = get_option('setting_remove_widgets');
			$setting_panel_hide_empty_containers = get_option('setting_panel_hide_empty_containers');

			if($setting_panel_heading != '')
			{
				$setting_panel_heading = str_replace("[name]", $user_display_name, $setting_panel_heading);
			}

			mf_enqueue_style('style_custom_dashboard', $plugin_include_url."style_wp.css");
			mf_enqueue_script('script_custom_dashboard', $plugin_include_url."script_wp.js", array('panel_heading' => $setting_panel_heading, 'remove_widgets' => $setting_remove_widgets, 'hide_empty_containers' => $setting_panel_hide_empty_containers));
		}
	}

	function admin_menu()
	{
		$menu_root = 'mf_custom_dashboard/';
		$menu_start = $menu_root.'list/index.php';
		$menu_capability = 'edit_pages';

		$menu_title = __("Customize", 'lang_dashboard');

		add_submenu_page("index.php", $menu_title, $menu_title, $menu_capability, "edit.php?post_type=".$this->post_type);
	}

	function disable_default()
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

	function display_widget($post, $args)
	{
		global $wpdb;

		$result = $wpdb->get_results($wpdb->prepare("SELECT ID, post_content FROM ".$wpdb->posts." WHERE post_type = %s AND ID = '%d'", $this->post_type, $args['args']));

		foreach($result as $r)
		{
			$post_id = $r->ID;
			$post_content = trim(apply_filters('the_content', $r->post_content));

			if($post_content != '')
			{
				echo $post_content;

				if(IS_ADMINISTRATOR)
				{
					echo "<a href='".admin_url("post.php?post=".$post_id."&action=edit")."' class='editable'><i class='far fa-edit fa-lg'></i></a>";
				}
			}
		}
	}

	function add_widget()
	{
		global $wp_meta_boxes, $wpdb;

		if(IS_ADMINISTRATOR && is_array($wp_meta_boxes['dashboard']))
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
					if(is_array($widgets_2))
					{
						foreach($widgets_2 as $key => $value)
						{
							if(!isset($arr_widgets[$key]))
							{
								if(isset($value['title']) && $value['title'] != '')
								{
									$arr_widgets[$key] = $value['title'];
								}
							}
						}
					}

					/*else
					{
						do_log("add_widget() -> Not an array: ".var_export($widgets_1, true)." -> ".var_export($widgets_2, true));
					}*/
				}
			}

			update_option('dashboard_registered_widget', $arr_widgets, false);
		}

		$result = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title FROM ".$wpdb->posts." WHERE post_type = %s AND post_status = %s ORDER BY menu_order ASC", $this->post_type, 'publish'));

		foreach($result as $r)
		{
			$post_id = $r->ID;
			$post_title = $r->post_title;

			$post_permission = get_post_meta($post_id, $this->meta_prefix.'permission', true);

			if($post_permission == '' || current_user_can($post_permission))
			{
				/*$post_column = get_post_meta($post_id, $this->meta_prefix.'column', true);
				$post_priority = get_post_meta($post_id, $this->meta_prefix.'priority', true);

				if($post_column != '' && $post_priority != '')
				{
					add_meta_box("custom_dashboard_widget_".$post_id, $post_title, array($this, 'display_widget'), 'dashboard', $post_column, $post_priority, $post_id);
				}

				else
				{*/
					wp_add_dashboard_widget("custom_dashboard_widget_".$post_id, $post_title, array($this, 'display_widget'), '', $post_id);
				//}
			}
		}
	}

	function rwmb_meta_boxes($meta_boxes)
	{
		$meta_boxes[] = array(
			'id' => $this->meta_prefix.'settings',
			'title' => __("Settings", 'lang_dashboard'),
			'post_types' => array($this->post_type),
			'context' => 'side',
			'priority' => 'low',
			'fields' => array(
				array(
					'name' => __("Lowest Permission", 'lang_dashboard'),
					'id' => $this->meta_prefix.'permission',
					'type' => 'select',
					'options' => get_roles_for_select(array('add_choose_here' => true)),
				),
				/*array(
					'name' => __("Column", 'lang_dashboard'),
					'id' => $this->meta_prefix.'column',
					'type' => 'select',
					'options' => $this->get_columns_for_select(),
				),
				array(
					'name' => __("Priority", 'lang_dashboard'),
					'id' => $this->meta_prefix.'priority',
					'type' => 'select',
					'options' => $this->get_priority_for_select(),
				),*/
			)
		);

		return $meta_boxes;
	}

	function column_header($cols)
	{
		unset($cols['date']);

		$cols['permission'] = __("Lowest Permission", 'lang_dashboard');
		/*$cols['column'] = __("Column", 'lang_dashboard');
		$cols['priority'] = __("Priority", 'lang_dashboard');*/

		return $cols;
	}

	function column_cell($col, $post_id)
	{
		$post_meta = get_post_meta($post_id, $this->meta_prefix.$col, true);

		if($post_meta != '')
		{
			switch($col)
			{
				case 'permission':
					$arr_roles = get_roles_for_select(array('add_choose_here' => false));

					echo $arr_roles[$post_meta];
				break;

				/*case 'column':
					$arr_columns = $this->get_columns_for_select();

					echo $arr_columns[$post_meta];
				break;

				case 'priority':
					$arr_priority = $this->get_priority_for_select();

					echo $arr_priority[$post_meta];
				break;*/
			}
		}
	}

	function filter_last_updated_post_types($array, $type)
	{
		$array[] = $this->post_type;

		return $array;
	}
}