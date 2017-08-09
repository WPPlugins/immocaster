jQuery(document).ready(function(){
	
	/* Suggestion for regions in metabox */
	jQuery(".immocaster_mb_resultlist_region").click(function() {
		jQuery(this).attr('value','');
	});
	jQuery(".immocaster_mb_resultlist_region").autocomplete({
			source: window.location.pathname+'?immocaster_ajax=autoregion&term=berl'
	})
	
	/* Disable-Function for regions */
	jQuery(".immocaster_mb_resultlist_all_regions").click(function() {
		if(jQuery(".immocaster_mb_resultlist_all_regions").attr('checked'))
		{
			jQuery('.immocaster_mb_resultlist_region').attr('disabled','disabled');
		}
		else
		{
			jQuery('.immocaster_mb_resultlist_region').removeAttr('disabled');
		}
	});
	
	/* Suggestion for regions in teaser-widget */
	jQuery(".immocaster_ajax_region_autocomplete").click(function() {
		jQuery(this).attr('value','');
	});
	setInterval(function()
	{
    	jQuery(".immocaster_ajax_region_autocomplete").autocomplete({
			source: window.location.pathname+'?immocaster_ajax=autoregion&term=berl'
		})
	}, 3000);
	
});