<?php

class Location_Country extends Db_ActiveRecord
{
    public $table_name = 'location_countries';

    public $enabled = 1;

    protected static $object_list = null;
    protected static $name_list = null;
    protected static $id_cache = array();

    public $belongs_to = array(
        'currency' => array('class_name' => 'Location_Currency', 'foreign_key' => 'currency_id'),
    );

    public $has_many = array(
        'states' => array('class_name' => 'Location_State', 'foreign_key' => 'country_id', 'order' => 'location_states.name', 'delete' => true),
    );

    public function define_columns($context = null)
    {
        $this->define_column('name', 'Name')->order('asc')->validation()->fn('trim')->required();
        $this->define_column('code', '2-digit ISO country code')->validation()->fn('trim')->required()->max_length(2, '2-digit ISO country code must contain exactly 2 letters.')->regexp('/^[a-z]{2}$/i', 'Country code must contain 2 Latin letters')->fn('mb_strtoupper');
        $this->define_column('code_3', '3-digit ISO country code')->validation()->fn('trim')->required()->max_length(3, '3-digit ISO country code must contain exactly 3 letters.')->regexp('/^[a-z]{3}$/i', 'Country code must contain 3 Latin letters')->fn('mb_strtoupper');
        $this->define_column('code_iso_numeric', 'Numeric ISO country code')->validation()->fn('trim')->required()->max_length(3, 'Numeric ISO country code must contain exactly 3 digits.')->regexp('/^[0-9]{3}$/i', 'Country code must contain 3 digits')->fn('mb_strtoupper');

        $this->define_column('enabled', 'Enabled')->validation();
        $this->define_relation_column('currency', 'currency', 'Default Currency', db_varchar, '@alpha_code');

        $front_end = Db_ActiveRecord::$execution_context == 'front-end';
        if (!$front_end)
            $this->define_multi_relation_column('states', 'states', 'States', "@name")->invisible();
    }

    public function define_form_fields($context = null)
    {
        $this->add_form_field('name')->tab('Country');
        $this->add_form_field('code')->tab('Country');
        $this->add_form_field('code_3', 'left')->tab('Country');
        $this->add_form_field('code_iso_numeric', 'right')->tab('Country');
        $this->add_form_field('enabled')->tab('Country')->comment('Disabled countries are not displayed anywhere.', 'above');
        $this->add_form_field('states')->tab('States');
        $this->add_form_field('currency')->tab('Currency');
    }

    public function before_delete($id = null)
    {
        $bind = array('id' => $this->id);
        $in_use = Db_Helper::scalar('select count(*) from users where country_id=:id', $bind);

        if ($in_use)
            throw new Phpr_ApplicationException("Cannot delete country because it is in use.");
    }

    public static function get_list($country_id = null)
    {
        $obj = new self(null, array('no_column_init' => true, 'no_validation' => true));
        $obj->order('name')->where('enabled = 1');

        if (strlen($country_id))
            $obj->or_where('id=?', $country_id);

        return $obj->find_all();
    }

    public function update_states($enabled)
    {
        if ($this->enabled != $enabled) {
            $this->enabled = $enabled;
            $this->save();
        }
    }

    public static function get_object_list($default = -1)
    {
        if (self::$object_list && !$default)
            return self::$object_list;

        $records = Db_Helper::object_array('select * from location_countries where enabled=1 or id=:id order by name', array('id' => $default));
        $result = array();
        foreach ($records as $country) {
            $result[$country->id] = $country;
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

        $countries = self::get_object_list();
        $result = array();
        foreach ($countries as $id => $country) {
            $result[$id] = $country->name;
        }

        return self::$name_list = $result;
    }

    public static function find_by_id($id)
    {
        if (array_key_exists($id, self::$id_cache))
            return self::$id_cache[$id];

        return self::$id_cache[$id] = self::create(true)->find($id);
    }

    public static function get_default_country_id()
    {
        $countries = Location_Country::get_name_list();
        if (is_array($countries))
            return key($countries);

        return null;
    }

    public function get_currency_options($key_value = -1)
    {
        if ($key_value != -1) {
            if (!strlen($key_value))
                return null;

            $obj = Location_Currency::create()->find($key_value);
            return $obj ? $obj->name : null;
        }

        $currencies_array = Location_Currency::get_name_list();
        $currencies_array[null] = 'Undefined';
        return $currencies_array;
    }

}
