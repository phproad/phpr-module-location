<?

class Location_Currencies extends Admin_Controller
{
	public $implement = 'Db_List_Behavior, Db_Form_Behavior';
	public $list_model_class = 'Location_Currency';
	public $list_record_url = null;
	public $list_handle_row_click = false;
	public $list_top_partial = null;

	public $list_custom_head_cells = null;
	public $list_custom_body_cells = null;

	public $form_model_class = 'Location_Currency';
	public $form_preview_title = 'Page';
	public $form_create_title = 'New Currency';
	public $form_edit_title = 'Edit Currency';
	public $form_not_found_message = 'Currency not found';
	public $form_redirect = null;
	public $form_create_save_redirect = null;

	public $form_edit_save_flash = 'The currency has been successfully saved';
	public $form_create_save_flash = 'The currency has been successfully added';
	public $form_edit_delete_flash = 'The currency has been successfully deleted';
	public $form_edit_save_auto_timestamp = true;

	public $list_search_enabled = true;
	public $list_search_fields = array('currency_name', 'alpha_code');
	public $list_search_prompt = 'find currencies by name or code';

	protected $global_handlers = array();

	public function __construct()
	{
		parent::__construct();
		$this->app_menu = 'system';
		$this->app_page = 'settings';
		$this->app_module_name = $this->form_edit_title;
		$this->form_redirect = url('location/currencies');
		$this->list_record_url = url('location/currencies/edit');
		$this->list_top_partial = 'currency_selectors';

		$this->list_custom_body_cells = PATH_SYSTEM.'/modules/db/behaviors/list_behavior/partials/_list_body_cb.htm';
		$this->list_custom_head_cells = PATH_SYSTEM.'/modules/db/behaviors/list_behavior/partials/_list_head_cb.htm';
	}

	public function index()
	{
		$this->app_page_title = 'Currencies';
	}

	protected function index_on_load_toggle_currencies_form()
	{
		try
		{
			$currency_ids = post('list_ids', array());

			if (!count($currency_ids))
				throw new Phpr_ApplicationException('Please select currencies to enable or disable.');

			$this->view_data['currency_count'] = count($currency_ids);
		}
		catch (Exception $ex)
		{
			$this->handle_page_error($ex);
		}

		$this->display_partial('enable_disable_currency_form');
	}

	protected function index_on_apply_currencies_enabled_status()
	{
		$currency_ids = post('list_ids', array());

		$enabled = post('enabled');

		foreach ($currency_ids as $currency_id)
		{
			$currency = Location_Currency::create()->find($currency_id);
			if ($currency)
				$currency->update_states($enabled);
		}

		$this->on_list_reload();
	}


	private function init_currency($id = null)
	{
		$obj = $id == null ? Location_Currency::create() : Location_Currency::create()->find($id);
		if ($obj)
		{
			$obj->init_columns();
			$obj->init_form_fields();
		}
		else if ($id != null)
		{
			throw new Phpr_ApplicationException('Currency not found');
		}

		return $obj;
	}

	public function list_get_row_class($model)
	{
		if ($model instanceof Location_Currency)
		{
			$result = 'currency_' . ($model->enabled ? 'enabled' : 'disabled') . ' ';

			$enabled_flag = null;
			if (!$model->enabled)
				$enabled_flag = 'disabled';
			elseif (!$model->enabled)
				$enabled_flag = 'special';

			return $result . $enabled_flag;
		}
	}

}

