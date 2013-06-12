<?php

class Location_Actions extends Cms_Action_Base
{

	// Locality functions
	// 
	
	public function on_state_dropdown()
	{
		if ($country_id = post('country_id'))
			$states = Location_State::get_name_list($country_id);
		else
			$states = array();

		$this->data['states'] = $states;
		$this->data['control_name'] = $control_name = post('control_name');
		$this->data['control_id'] = $control_id = post('control_id');
		$this->data['current_state'] = $current_state = post('current_state');

		// Produce some HTML, in case a partial is not used
		$extra = ($control_id) ? 'id="'.$control_id.'"' : '';
		echo form_dropdown($control_name, $states, '', $extra,  __('-- Select --', true));
	}

	// Service actions
	// 

	public function on_validate_address()
	{
		$valid = false;

		// Look for address value
		$possible_array_values = array(null, 'Provider', 'Request', 'Post');

		foreach ($possible_array_values as $val)
		{
			if ($val === null)
				$address = post('address');
			else
				$address = post_array($val, 'address');

			if ($address)
				break;
		}

		if (!$address)
			die("Could not find address");

		try
		{
			$result = Location_Geocode::from_address($address);
			$valid = ($result->getZipcode() && $result->getCountryCode());
		}
		catch (Exception $ex)
		{
			$valid = false;
			echo $ex->getMessage();
		}

		if ($valid)
			echo "true";
		else
			echo __('Please provide a valid location',true);
	}

}