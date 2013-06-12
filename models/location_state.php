<?php

class Location_State extends Db_ActiveRecord
{
	public $table_name = 'location_states';
	
	public $calculated_columns = array(
		'country_state_name'=>array('sql' => "concat(location_countries.name, '/', location_states.name)", 'type'=>db_text, 'join'=>array('location_countries'=>'location_countries.id=location_states.country_id'))
	);
	
	protected static $id_cache = array();
	protected static $object_list = array();
	protected static $name_list = array();
	
	public function define_columns($context = null)
	{
		$this->define_column('code', 'Code')->validation()->fn('trim')->required()->fn('mb_strtoupper');
		$this->define_column('name', 'Name')->order('asc')->validation()->fn('trim')->required();
		$this->define_column('country_state_name', 'Full Name')->invisible();
	}

	public function define_form_fields($context = null)
	{
		$this->add_form_field('code')->comment('Enter a unique code to identify this state.','above');
		$this->add_form_field('name');
	}

	public function before_delete($id = null)
	{
		$bind = array('id'=>$this->id);
		$in_use = Db_Helper::scalar('select count(*) from users where state_id=:id', $bind);
		
		if ($in_use)
			throw new Phpr_ApplicationException("Cannot delete this state because it is in use by a user.");
	}
	
	public static function find_by_id($id)
	{
		if (array_key_exists($id, self::$id_cache))
			return self::$id_cache[$id];
			
		return self::$id_cache[$id] = self::create(true)->find($id);
	}
	
	// $model->find_all()->as_array() would usually work here
	public static function get_object_list($country_id)
	{
		if (array_key_exists($country_id, self::$object_list))
			return self::$object_list[$country_id];

		$records = Db_Helper::object_array('select * from location_states where country_id=:country_id order by name', array('country_id'=>$country_id));
		$result = array();

		foreach ($records as $state) {
			$result[$state->id] = $state;
		}

		return self::$object_list[$country_id] = $result;
	}
	
	public static function get_name_list($country_id)
	{
		if (array_key_exists($country_id, self::$name_list))
			return self::$name_list[$country_id];
		
		$states = self::get_object_list($country_id);
		$result = array();

		foreach ($states as $id=>$state) {
			$result[$id] = $state->name;
		}
			
		return self::$name_list[$country_id] = $result;
	}
}

