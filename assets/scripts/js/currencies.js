function select_currencies(select_type) {
	switch (select_type)
	{
		case 'all':
			jQuery('#listLocation_Currencies_index_list_body tr td.list-checkbox input').each(function(){ jQuery(this).cb_check(); });
		break;
		case 'none':
			jQuery('#listLocation_Currencies_index_list_body tr td.list-checkbox input').each(function(){ jQuery(this).cb_uncheck(); });
		break;
		case 'enabled':
			jQuery('#listLocation_Currencies_index_list_body tr.country_enabled td.list-checkbox input').each(function(){ jQuery(this).cb_check(); });
		break;
		case 'disabled':
			jQuery('#listLocation_Currencies_index_list_body tr.country_disabled td.list-checkbox input').each(function(){ jQuery(this).cb_check(); });
		break;
	}
	
	return false;
}

function enable_disable_selected() {
	if (jQuery('#listLocation_Currencies_index_list_body tr td.list-checkbox input:checked') == 0)
	{
		alert('Please select currencies to enable or disable.');
		return false;
	}
	
	new PopupForm('index_on_load_toggle_currencies_form', {
		ajaxFields: $('#listLocation_Currencies_index_list_body').getForm()
	});

	return false;
}
