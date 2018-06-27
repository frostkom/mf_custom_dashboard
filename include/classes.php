<?php

class mf_custom_dashboard
{
	function __construct()
	{

	}

	function admin_init()
	{
		global $pagenow;

		if($pagenow == 'index.php')
		{
			$plugin_include_url = plugin_dir_url(__FILE__);
			$plugin_version = get_plugin_version(__FILE__);

			$user_data = get_userdata(get_current_user_id());
			$user_display_name = $user_data->first_name != '' ? $user_data->first_name : ucfirst($user_data->display_name);

			$setting_panel_heading = get_option('setting_panel_heading');
			$setting_remove_widgets = get_option('setting_remove_widgets');
			$setting_panel_hide_empty_containers = get_option('setting_panel_hide_empty_containers');

			if($setting_panel_heading != '')
			{
				$setting_panel_heading = str_replace("[name]", $user_display_name, $setting_panel_heading);
			}

			mf_enqueue_style('style_custom_dashboard', $plugin_include_url."style_wp.css", $plugin_version);
			mf_enqueue_script('script_custom_dashboard', $plugin_include_url."script_wp.js", array('panel_heading' => $setting_panel_heading, 'remove_widgets' => $setting_remove_widgets, 'hide_empty_containers' => $setting_panel_hide_empty_containers), $plugin_version);
		}
	}
}