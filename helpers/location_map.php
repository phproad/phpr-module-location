<?php

class Location_Map
{
	// Example obj: User, Service_Provider
	// Must contain street_addr, city, state and country values
	public static function get_directions($to_obj, $from_obj = null)
	{
		$to_array = array();
		if ($to_obj->street_addr) $to_array[] = $to_obj->street_addr;
		if ($to_obj->city)        $to_array[] = $to_obj->city;
		if ($to_obj->zip)         $to_array[] = $to_obj->zip;
		if ($to_obj->state)       $to_array[] = $to_obj->state->name;
		if ($to_obj->country)     $to_array[] = $to_obj->country->name;
		$to_string = '&daddr='.urlencode(implode(' ', $to_array));

		if ($from_obj)
		{
			$from_array = array();
			if ($from_obj->street_addr) $from_array[] = $from_obj->street_addr;
			if ($from_obj->city)        $from_array[] = $from_obj->city;
			if ($from_obj->zip)         $from_array[] = $from_obj->zip;
			if ($from_obj->state)       $from_array[] = $from_obj->state->name;
			if ($from_obj->country)     $from_array[] = $from_obj->country->name;
			$from_string = '&saddr='.urlencode(implode(' ', $from_array));
		}
		else
			$from_string = '&saddr=';

		return htmlentities("http://maps.google.com/maps?f=q&amp;hl=en&amp;".$from_string.$to_string);
	}

	public static function get_map($obj)
	{
		$address_array = array();
		if ($obj->street_addr) $address_array[] = $obj->street_addr;
		if ($obj->city)        $address_array[] = $obj->city;
		if ($obj->zip)         $address_array[] = $obj->zip;
		if ($obj->state)       $address_array[] = $obj->state->name;
		if ($obj->country)     $address_array[] = $obj->country->name;
		$address_string = 'q='.urlencode(implode(' ', $address_array));
		return htmlentities("http://maps.google.com/?".$address_string);
	}


}
