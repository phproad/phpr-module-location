<?

class Location_Countries extends Admin_Controller
{
	public $implement = 'Db_List_Behavior, Db_Form_Behavior';
	public $list_model_class = 'Location_Country';
	public $list_record_url = null;
	public $list_handle_row_click = false;
	public $list_top_partial = null;

	public $list_custom_head_cells = null;
	public $list_custom_body_cells = null;

	public $form_model_class = 'Location_Country';
	public $form_preview_title = 'Page';
	public $form_create_title = 'New Country';
	public $form_edit_title = 'Edit Country';
	public $form_not_found_message = 'Country not found';
	public $form_redirect = null;
	public $form_create_save_redirect = null;

	public $form_edit_save_flash = 'The country has been successfully saved';
	public $form_create_save_flash = 'The country has been successfully added';
	public $form_edit_delete_flash = 'The country has been successfully deleted';
	public $form_edit_save_auto_timestamp = true;

	public $list_search_enabled = true;
	public $list_search_fields = array('name', 'code', 'code_3', 'code_iso_numeric');
	public $list_search_prompt = 'find countries by name or code';

	protected $global_handlers = array(
		'on_load_country_state_form',
		'on_save_country_state',
		'on_update_country_state_list',
		'on_delete_country_state'
	);

	public function __construct()
	{
		parent::__construct();
		$this->app_menu = 'system';
		$this->app_page = 'settings';
		$this->app_module_name = $this->form_edit_title;
		$this->form_redirect = url('location/countries');
		$this->list_record_url = url('location/countries/edit');
		$this->list_top_partial = 'country_selectors';

		$this->list_custom_body_cells = PATH_SYSTEM.'/modules/db/behaviors/db_list_behavior/partials/_list_body_cb.htm';
		$this->list_custom_head_cells = PATH_SYSTEM.'/modules/db/behaviors/db_list_behavior/partials/_list_head_cb.htm';
	}

	public function index()
	{
		$this->app_page_title = 'Countries';
	}

	protected function index_on_load_toggle_countries_form()
	{
		try
		{
			$country_ids = post('list_ids', array());

			if (!count($country_ids))
				throw new Phpr_ApplicationException('Please select countries to enable or disable.');

			$this->view_data['country_count'] = count($country_ids);
		}
		catch (Exception $ex)
		{
			$this->handle_page_error($ex);
		}

		$this->display_partial('enable_disable_country_form');
	}

	protected function index_on_apply_countries_enabled_status()
	{
		$country_ids = post('list_ids', array());

		$enabled = post('enabled');

		foreach ($country_ids as $country_id)
		{
			$country = Location_Country::create()->find($country_id);
			if ($country)
				$country->update_states($enabled);
		}

		$this->on_list_reload();
	}

	protected function on_load_country_state_form()
	{
		try
		{
			$id = post('state_id');
			$state = $id ? Location_State::create()->find($id) : Location_State::create();
			if (!$state)
				throw new Phpr_ApplicationException('State not found');

			$state->init_form_fields();

			$this->view_data['state'] = $state;
			$this->view_data['session_key'] = post('edit_session_key');
			$this->view_data['state_id'] = post('state_id');
		}
		catch (Exception $ex)
		{
			$this->handle_page_error($ex);
		}

		$this->display_partial('country_state_form');
	}

	protected function on_save_country_state($countryId)
	{
		try
		{
			$id = post('state_id');
			$state = $id ? Location_State::create()->find($id) : Location_State::create();
			if (!$state)
				throw new Phpr_ApplicationException('State not found');

			$country = $this->init_country($countryId);

			$state->init_columns();
			$state->init_form_fields();
			$state->save(post('Location_State'), $this->form_get_edit_session_key());

			if (!$id)
				$country->states->add($state, post('country_session_key'));
		}
		catch (Exception $ex)
		{
			Phpr::$response->ajax_report_exception($ex, true, true);
		}
	}

	protected function on_update_country_state_list($countryId)
	{
		try
		{
			$this->view_data['form_model'] = $this->init_country($countryId);
			$this->display_partial('country_states_list');
		}
		catch (Exception $ex)
		{
			Phpr::$response->ajax_report_exception($ex, true, true);
		}
	}

	protected function on_delete_country_state($countryId)
	{
		try
		{
			$country = $this->view_data['form_model'] = $this->init_country($countryId);

			$id = post('state_id');
			$state = $id ? Location_State::create()->find($id) : Location_State::create();

			if ($state)
			{
				$state->check_in_use();
				$country->states->delete($state, $this->form_get_edit_session_key());
				$state->delete();
			}

			$this->view_data['form_model'] = $country;
			$this->display_partial('country_states_list');
		}
		catch (Exception $ex)
		{
			Phpr::$response->ajax_report_exception($ex, true, true);
		}
	}

	private function init_country($id = null)
	{
		$obj = $id == null ? Location_Country::create() : Location_Country::create()->find($id);
		if ($obj)
		{
			$obj->init_columns();
			$obj->init_form_fields();
		}
		else if ($id != null)
		{
			throw new Phpr_ApplicationException('Country not found');
		}

		return $obj;
	}

	public function list_get_row_class($model)
	{
		if ($model instanceof Location_Country)
		{
			$result = 'country_' . ($model->enabled ? 'enabled' : 'disabled') . ' ';

			$enabled_flag = null;
			if (!$model->enabled)
				$enabled_flag = 'disabled';
			elseif (!$model->enabled)
				$enabled_flag = 'special';

			return $result . $enabled_flag;
		}
	}

}

