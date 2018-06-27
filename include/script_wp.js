jQuery(function($)
{
	var dom_h1 = $('.wrap > h1');

	if(script_custom_dashboard.panel_heading != '')
	{
		dom_h1.text(script_custom_dashboard.panel_heading);
	}

	$('#dashboard-widgets .inside').each(function()
	{
		var dom_obj = $(this),
			dom_content = dom_obj.html().trim();

		if(dom_content == '')
		{
			dom_obj.parent('.postbox').remove();
		}
	});

	var count_temp = script_custom_dashboard.remove_widgets.length;

	for(var i = 0; i < count_temp; i++)
	{
		var dom_id = script_custom_dashboard.remove_widgets[i];

		$('#' + dom_id).remove();
	}

	if(script_custom_dashboard.hide_empty_containers == 'yes')
	{
		var dom_obj = $("#dashboard-widgets");

		dom_obj.find(".empty-container").parent(".postbox-container").remove();
		
		if(dom_obj.children(".postbox-container").length == 1)
		{
			dom_obj.addClass('hide_empty_containers');
		}
	}

	if($('#dashboard-widgets .inside').length == 0)
	{
		if(script_custom_dashboard.panel_heading != '' && $('#welcome-panel').length == 0 || $('#welcome-panel').hasClass('hidden'))
		{
			dom_h1.addClass('align_center');

			$('#dashboard-widgets-wrap').hide();
		}
	}
});