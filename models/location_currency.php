<?php

class Location_Currency extends Db_ActiveRecord
{
	public $table_name = 'location_currencies';
	
	public $enabled = 1;
	
	protected static $object_list = null;
	protected static $name_list = null;
	protected static $id_cache = array();

	public $has_many = array();

	public function define_columns($context = null)
	{
		$this->define_column('currency_name', 'Name')->order('asc')->validation()->fn('trim')->required();
		$this->define_column('alpha_code', 'ISO 4217 currency code')->validation()->fn('trim')->required()->max_length(3, '3-digit ISO country code must contain exactly 3 letters.')->regexp('/^[a-z]{3}$/i', 'Currency code must contain 3 Latin letters')->fn('mb_strtoupper');
		$this->define_column('enabled', 'Enabled')->validation();
		

	}

	public function define_form_fields($context = null)
	{
		$this->add_form_field('currency_name')->tab('Currency');
		$this->add_form_field('alpha_code')->tab('Currency');
		$this->add_form_field('enabled')->tab('Currency')->comment('Disabled currencies are not displayed anywhere.', 'above');

	}

    public function before_delete($id = null)
    {
        $bind = array('id'=>$this->id);
        $in_use = Db_Helper::scalar('select count(*) from location_countries where currency_id=:id', $bind);

        if ($in_use)
            throw new Phpr_ApplicationException("Cannot delete currency because it has been assigned to a country as default currency.");
    }
	
	public static function get_list($currency_id = null)
	{
		$obj = new self(null, array('no_column_init'=>true, 'no_validation'=>true));
		$obj->order('currency_name')->where('enabled = 1');
		
		if (strlen($currency_id))
			$obj->or_where('id=?', $currency_id);
			
		return $obj->find_all();
	}
	
	public function update_states($enabled)
	{
		if ($this->enabled != $enabled)
		{
			$this->enabled = $enabled;
			$this->save();
		}
	}

	public static function get_object_list($default = -1)
	{
		if (self::$object_list && !$default)
			return self::$object_list;

		$records = Db_Helper::object_array('select * from location_currencies where enabled=1 or id=:id order by alpha_code', array('id' => $default));
		$result = array();
		foreach ($records as $currency) {
			$result[$currency->id] = $currency;
		}

		if (!$default)
			return self::$object_list = $result;
		else 
			return $result;
	}

	public static function get_name_list()
	{
		if (self::$name_list)
			return self::$name_list;
		
		$currencies = self::get_object_list();
		$result = array();
		foreach ($currencies as $id=>$currency) {
			$result[$id] = $currency->alpha_code." - ".$currency->currency_name;
		}
			
		return self::$name_list = $result;
	}
	
	public static function find_by_id($id)
	{
		if (array_key_exists($id, self::$id_cache))
			return self::$id_cache[$id];
			
		return self::$id_cache[$id] = self::create(true)->find($id);
	}

	public static function get_default_currency_id()
	{
		$currencies = Location_Currency::get_name_list();
		if (is_array($currencies))
			return key($currencies);

		return null;
	}

}
