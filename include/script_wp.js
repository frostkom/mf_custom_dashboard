jQuery(function($)
{
	if(script_custom_dashboard.panel_heading != '')
	{
		$('.wrap > h1').text(script_custom_dashboard.panel_heading);
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

	if($('#dashboard-widgets .inside').length == 0)
	{
		$('#dashboard-widgets .empty-container').parent('.postbox-container').remove();

		if(script_custom_dashboard.panel_heading != '' && $('#welcome-panel').length == 0 || $('#welcome-panel').hasClass('hidden'))
		{
			$('.wrap > h1').addClass('align_center');
		}
	}
});