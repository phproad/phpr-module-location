<?php

class Location_Module extends Core_Module_Base
{

	protected function set_module_info()
	{
		return new Core_Module_Detail(
			"Location",
			"Reigonal module",
			"PHPRoad",
			"http://phproad.com/"
		);
	}
	
	public function build_admin_settings($settings)
	{
		$settings->add('/location/setup', 'Location Settings', 'Location related settings', '/modules/location/assets/images/location_config.png', 70);
		$settings->add('/location/countries', 'Countries', 'Set up available locations', '/modules/location/assets/images/country_config.png', 70);
	}

}
