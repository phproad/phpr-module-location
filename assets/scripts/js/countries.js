function select_countries(select_type) {
	switch (select_type)
	{
		case 'all':
			jQuery('#listLocation_Countries_index_list_body tr td.checkbox input').each(function(){ jQuery(this).cb_check(); });
		break;
		case 'none':
			jQuery('#listLocation_Countries_index_list_body tr td.checkbox input').each(function(){ jQuery(this).cb_uncheck(); });
		break;
		case 'enabled':
			jQuery('#listLocation_Countries_index_list_body tr.country_enabled td.checkbox input').each(function(){ jQuery(this).cb_check(); });
		break;
		case 'disabled':
			jQuery('#listLocation_Countries_index_list_body tr.country_disabled td.checkbox input').each(function(){ jQuery(this).cb_check(); });
		break;
	}
	
	return false;
}

function enable_disable_selected() {
	if (jQuery('#listLocation_Countries_index_list_body tr td.checkbox input:checked') == 0)
	{
		alert('Please select countries to enable or disable.');
		return false;
	}
	
	new PopupForm('index_on_load_toggle_countries_form', {
		ajaxFields: $('listLocation_Countries_index_list_body').getForm()
	});

	return false;
}