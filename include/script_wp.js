jQuery(function($)
{
	$('#dashboard-widgets .inside').each(function()
	{
		var dom_obj = $(this),
			dom_content = dom_obj.html().trim();

		if(dom_content == '')
		{
			dom_obj.parent('.postbox').remove();
		}
	});
});