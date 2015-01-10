<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Table/classes/class.ilTableGUI.php");

/**
* Class ilTable2GUI
*
* @author	Alex Killing <alex.killing@gmx.de>
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id: class.ilTableGUI.php 12818 2006-12-10 13:14:43Z akill $
*
* @ingroup ServicesTable
*/
class ilTable2GUI extends ilTableGUI
{
	protected $close_command = "";
	private $unique_id;
	private $headerHTML;
	protected $top_anchor = "il_table_top";
	protected $filters = array();
	protected $optional_filters = array();
	protected $filter_cmd = 'applyFilter';
	protected $reset_cmd = 'resetFilter';
	protected $filter_cols = 5;
	protected $ext_sort = false;
	protected $ext_seg = false;
	protected $context = "";
	
	protected $mi_sel_buttons = null;
	protected $disable_filter_hiding = false;
	protected $selected_filter = false;
	protected $top_commands = true;
	protected $selectable_columns = array();
	protected $selected_column = array();
	protected $show_templates = false;
	protected $show_rows_selector = true; // JF, 2014-10-27

	protected $nav_determined= false;
	protected $limit_determined = false;
	protected $filters_determined = false;
	protected $columns_determined = false;
	protected $open_form_tag = true;
	protected $close_form_tag = true;

	protected $export_formats;
	protected $export_mode;
	protected $print_mode;
	
	protected $enable_command_for_all;
	protected $restore_filter; // [bool]
	protected $restore_filter_values; // [bool]

	protected $sortable_fields = array();
	/**
	 * @var bool
	 */
	protected $prevent_double_submission = true;

	/**
	 * @var string
	 */
	protected $row_selector_label;

	const FILTER_TEXT = 1;
	const FILTER_SELECT = 2;
	const FILTER_DATE = 3;
	const FILTER_LANGUAGE = 4;
	const FILTER_NUMBER_RANGE = 5;
	const FILTER_DATE_RANGE = 6;
	const FILTER_DURATION_RANGE = 7;
	const FILTER_DATETIME_RANGE = 8;

	const EXPORT_EXCEL = 1;
	const EXPORT_CSV = 2;
	
	const ACTION_ALL_LIMIT = 1000;
	
	/**
	* Constructor
	*
	*/
	public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
	{
		global $lng;

		parent::__construct(0, false);
		$this->unique_id = md5(uniqid());
		$this->parent_obj = $a_parent_obj;
		$this->parent_cmd = $a_parent_cmd;
		$this->buttons = array();
		$this->header_commands = array();
		$this->multi = array();
		$this->hidden_inputs = array();
		$this->formname = "table_" . $this->unique_id;
		$this->tpl = new ilTemplate("tpl.table2.html", true, true, "Services/Table");

		$lng->loadLanguageModule('tbl');

		if(!$a_template_context)
		{
			$a_template_context = $this->getId();
		}
		$this->setContext($a_template_context);

		// activate export mode
		if(isset($_GET[$this->prefix."_xpt"]))
		{
	       $this->export_mode = (int)$_GET[$this->prefix."_xpt"];
		}

		// template handling
		if(isset($_GET[$this->prefix."_tpl"]))
        {
			$this->restoreTemplate($_GET[$this->prefix."_tpl"]);
		}

		$this->determineLimit();
		$this->setIsDataTable(true);
		$this->setEnableNumInfo(true);
		$this->determineSelectedColumns();
	}

	/**
	 * Set open form tag
	 *
	 * @param	boolean	open form tag
	 */
	function setOpenFormTag($a_val)
	{
		$this->open_form_tag = $a_val;
	}

	/**
	 * Get open form tag
	 *
	 * @return	boolean	open form tag
	 */
	function getOpenFormTag()
	{
		return $this->open_form_tag;
	}

	/**
	 * Set close form tag
	 *
	 * @param	boolean	close form tag
	 */
	function setCloseFormTag($a_val)
	{
		$this->close_form_tag = $a_val;
	}

	/**
	 * Get close form tag
	 *
	 * @return	boolean	close form tag
	 */
	function getCloseFormTag()
	{
		return $this->close_form_tag;
	}

	/**
	 * Determine the limit
	 */
	function determineLimit()
	{
		global $ilUser;

		if ($this->limit_determined)
		{
			return;
		}

		$limit = 0;
		if (isset($_GET[$this->prefix."_trows"]))
		{
			$this->storeProperty("rows", $_GET[$this->prefix."_trows"]);
			$limit = $_GET[$this->prefix."_trows"];
			$this->resetOffset();
		}

		if ($limit == 0)
		{
			$rows = $this->loadProperty("rows");
			if ($rows > 0)
			{
				$limit = $rows;
			}
			else
			{
				if (is_object($ilUser))
				{
					$limit = $ilUser->getPref("hits_per_page");
				}
				else
				{
					$limit = 40;
				}
			}
		}

		$this->setLimit($limit);
		$this->limit_determined = true;
	}
	
	/**
	 * Get selectable columns
	 *
	 * @return		array	key: column id, val: true/false -> default on/off
	 */
	function getSelectableColumns()
	{
		return array();
	}

	/**
	 * Determine selected columns
	 */
	function determineSelectedColumns()
	{
		if ($this->columns_determined)
		{
			return;
		}
	
		$old_sel = $this->loadProperty("selfields");
		
		$stored = false;
		if ($old_sel != "")
		{
			$sel_fields =
				@unserialize($old_sel);
			$stored = true;
		}
		if(!is_array($sel_fields))
		{
			$stored = false;
			$sel_fields = array();
		}
		
		$this->selected_columns = array();
		$set = false;
		foreach ($this->getSelectableColumns() as $k => $c)
		{
			$this->selected_column[$k] = false;
			
			$new_column = ($sel_fields[$k] === NULL);

			if ($_POST["tblfsh".$this->getId()])
			{
				$set = true;
				if (is_array($_POST["tblfs".$this->getId()]) && in_array($k, $_POST["tblfs".$this->getId()]))
				{
					$this->selected_column[$k] = true;
				}
			}
			else if ($stored && !$new_column)	// take stored values
			{
				$this->selected_column[$k] = $sel_fields[$k]; 
			}
			else	// take default values
			{
				if ($new_column)
				{
					$set = true;
				}			
				if ($c["default"])
				{
					$this->selected_column[$k] = true;
				}			
			}
		}
		
		if ($old_sel != serialize($this->selected_column) && $set)
		{
			$this->storeProperty("selfields", serialize($this->selected_column));
		}

		$this->columns_determined = true;
	}
	
	/**
	 * Is given column selected?
	 *
	 * @param	string	column name
	 * @return	boolean
	 */
	function isColumnSelected($a_col)
	{
		return $this->selected_column[$a_col];
	}
	
	/**
	 * Get selected columns
	 *
	 * @param
	 * @return
	 */
	function getSelectedColumns()
	{
		$scol = array();
		foreach ($this->selected_column as $k => $v)
		{
			if ($v)
			{
				$scol[$k] = $k;
			}
		}
		return $scol;
	}
	
	/**
	 * Execute command.
	 */
	function &executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
			
		switch($next_class)
		{
			case 'ilformpropertydispatchgui':
				include_once './Services/Form/classes/class.ilFormPropertyDispatchGUI.php';
				$form_prop_dispatch = new ilFormPropertyDispatchGUI();
				$this->initFilter();
				$item = $this->getFilterItemByPostVar($_GET["postvar"]);
				$form_prop_dispatch->setItem($item);
				return $ilCtrl->forwardCommand($form_prop_dispatch);
				break;

		}
		return false;
	}

	/**
	* Reset offset
	*/
	function resetOffset($a_in_determination = false)
	{
		if (!$this->nav_determined && !$a_in_determination)
		{
			$this->determineOffsetAndOrder();
		}
		$this->nav_value = $this->getOrderField().":".$this->getOrderDirection().":0";
		$_GET[$this->getNavParameter()] =
			$_POST[$this->getNavParameter()."1"] =
			$this->nav_value;
//echo $this->nav_value;
		$this->setOffset(0);
	}
	
	/**
	* Init filter. Overwrite this to initialize all filter input property
	* objects.
	*/
	function initFilter()
	{
	}
	
	/**
	* Get parent object
	*
	* @return	object		parent GUI object
	*/
	public function getParentObject()
	{
	 	return $this->parent_obj;
	}
	
	/**
	* Get parent command
	*
	* @return	string		get parent gui object default command
	*/
	public function getParentCmd()
	{
	 	return $this->parent_cmd;
	}

	/**
	* Set top anchor
	*
	* @param	string	top anchor
	*/
	function setTopAnchor($a_val)
	{
		$this->top_anchor = $a_val;
	}
	
	/**
	* Get top anchor
	*
	* @return	string	top anchor
	*/
	function getTopAnchor()
	{
		return $this->top_anchor;
	}
	
	/**
	* Set text for an empty table.
	*
	* @param	string	$a_text	Text
	*/
	function setNoEntriesText($a_text)
	{
		$this->noentriestext = $a_text;
	}

	/**
	* Get text for an empty table.
	*
	* @return	string	Text
	*/
	function getNoEntriesText()
	{
		return $this->noentriestext;
	}

	/**
	* Set is data table 
	*
	* @param	boolean		is data table
	*/
	function setIsDataTable($a_val)
	{
		$this->datatable = $a_val;
	}
	
	/**
	* Get is data table
	*
	* @return	boolean		is data table
	*/
	function getIsDataTable()
	{
		return $this->datatable;
	}
	
	/**
	* Set Enable Title.
	*
	* @param	boolean	$a_enabletitle	Enable Title
	*/
	function setEnableTitle($a_enabletitle)
	{
		$this->enabled["title"] = $a_enabletitle;
	}

	/**
	* Get Enable Title.
	*
	* @return	boolean	Enable Title
	*/
	function getEnableTitle()
	{
		return $this->enabled["title"];
	}

	/**
	* Set Enable Header.
	*
	* @param	boolean	$a_enableheader	Enable Header
	*/
	function setEnableHeader($a_enableheader)
	{
		$this->enabled["header"] = $a_enableheader;
	}

	/**
	* Get Enable Header.
	*
	* @return	boolean	Enable Header
	*/
	function getEnableHeader()
	{
		return $this->enabled["header"];
	}

	/**
	* Set enable num info
	*
	* @param	boolean		enable number of records info
	*/
	function setEnableNumInfo($a_val)
	{
		$this->num_info = $a_val;
	}
	
	/**
	* Get enable num info
	*
	* @return	boolean		enable number of records info
	*/
	function getEnableNumInfo()
	{
		return $this->num_info;
	}
	
	/**
	* Set title and title icon
	*/
	final public function setTitle($a_title, $a_icon = 0, $a_icon_alt = 0)
	{
		parent::setTitle($a_title, $a_icon, $a_icon_alt);
	}
	
	/**
	* Set description
	*
	* @param	string description
	*/
	function setDescription($a_val)
	{
		$this->description = $a_val;
	}
	
	/**
	* Get description
	*
	* @return	string	description
	*/
	function getDescription()
	{
		return $this->description;
	}
	
	/**
	* set order column
	*
	* @param	string	(array) field name for ordering
	*/
	function setOrderField($a_order_field)
	{
		$this->order_field = $a_order_field;
	}

	function getOrderField()
	{
		return $this->order_field;
	}

	final public function setData($a_data)
	{
		// check column names against given data (to ensure proper sorting)
		if(DEVMODE && 
			$this->enabled["header"] && $this->enabled["sort"] && 
			$this->columns_determined && is_array($this->column) && 
			is_array($a_data) && sizeof($a_data) && !$this->getExternalSorting())
		{
			$check = $a_data;
			$check = array_keys(array_shift($check));			
			foreach($this->column as $col)
			{
				if($col["sort_field"] && !in_array($col["sort_field"], $check))
				{
					$invalid[] = $col["sort_field"];
				}
			}
			
			// this triggers an error, if some columns are not set for some rows
			// which may just be a representation of "null" values, e.g.
			// ilAdvancedMDValues:queryForRecords works that way.
/*			if(sizeof($invalid))
			{
				trigger_error("The following columns are defined as sortable but".
					" cannot be found in the given data: ".implode(", ", $invalid).
					". Sorting will not work properly.", E_USER_WARNING);
			}*/
		}
		
		$this->row_data = $a_data;
	}
	
	final public function getData()
	{
		return $this->row_data;
	}
	
	final public function dataExists()
	{
		if (is_array($this->row_data))
		{
			if (count($this->row_data) > 0)
			{
				return true;
			}
		}
		return false;
	}

	final public function setPrefix($a_prefix)
	{
		$this->prefix = $a_prefix;
	}
	
	final public function getPrefix()
	{
		return $this->prefix;
	}
	
	/**
	* Add filter item. Filter items are property form inputs that implement
	* the ilTableFilterItem interface
	*/
	final function addFilterItem($a_input_item, $a_optional = false)
	{
		$a_input_item->setParent($this);
		if (!$a_optional)
		{
			$this->filters[] = $a_input_item;
		}
		else
		{
			$this->optional_filters[] = $a_input_item;
		}
		
		// restore filter values (from stored view)
		if($this->restore_filter)
		{			
			if(array_key_exists($a_input_item->getFieldId(), $this->restore_filter_values))
			{
				$this->setFilterValue($a_input_item, $this->restore_filter_values[$a_input_item->getFieldId()]);
			}
			else
			{				
				$this->setFilterValue($a_input_item, null); // #14949
			}
		}
	}

	/**
	 * Add filter by standard type
	 * 
	 * @param	string	$id
	 * @param	int		$type
	 * @param	bool	$a_optional
	 * @param	string	$caption
	 * @return	object
	 */
	function addFilterItemByMetaType($id, $type = self::FILTER_TEXT, $a_optional = false, $caption = NULL)
	{
		global $lng;

		if(!$caption)
		{
			$caption = $lng->txt($id);
		}

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

		switch($type)
		{
			case self::FILTER_SELECT:
				include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
				$item = new ilSelectInputGUI($caption, $id);
				break;

			case self::FILTER_DATE:
				include_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");
				$item = new ilDateTimeInputGUI($caption, $id);
				$item->setMode(ilDateTimeInputGUI::MODE_INPUT);
				break;

			case self::FILTER_TEXT:
				include_once("./Services/Form/classes/class.ilTextInputGUI.php");
				$item = new ilTextInputGUI($caption, $id);
				$item->setMaxLength(64);
				$item->setSize(20);
				// $item->setSubmitFormOnEnter(true);
				break;

			case self::FILTER_LANGUAGE:
				$lng->loadLanguageModule("meta");
				include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
				$item = new ilSelectInputGUI($caption, $id);
				$options = array("" => $lng->txt("trac_all"));
				foreach ($lng->getInstalledLanguages() as $lang_key)
				{
					$options[$lang_key] = $lng->txt("meta_l_".$lang_key);
				}
				$item->setOptions($options);
				break;

			case self::FILTER_NUMBER_RANGE:
				include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
				include_once("./Services/Form/classes/class.ilNumberInputGUI.php");
				$item = new ilCombinationInputGUI($caption, $id);
				$combi_item = new ilNumberInputGUI("", $id."_from");
				$item->addCombinationItem("from", $combi_item, $lng->txt("from"));
				$combi_item = new ilNumberInputGUI("", $id."_to");
				$item->addCombinationItem("to", $combi_item, $lng->txt("to"));
				$item->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
				$item->setMaxLength(7);
				$item->setSize(20);
				break;

			case self::FILTER_DATE_RANGE:
				include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
				include_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");
				$item = new ilCombinationInputGUI($caption, $id);
				$combi_item = new ilDateTimeInputGUI("", $id."_from");				
				$item->addCombinationItem("from", $combi_item, $lng->txt("from"));
				$combi_item = new ilDateTimeInputGUI("", $id."_to");
				$item->addCombinationItem("to", $combi_item, $lng->txt("to"));
				$item->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
				$item->setMode(ilDateTimeInputGUI::MODE_INPUT);
				break;
			
			case self::FILTER_DATETIME_RANGE:
				include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
				include_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");
				$item = new ilCombinationInputGUI($caption, $id);
				$combi_item = new ilDateTimeInputGUI("", $id."_from");
				$combi_item->setShowTime(true);
				$item->addCombinationItem("from", $combi_item, $lng->txt("from"));
				$combi_item = new ilDateTimeInputGUI("", $id."_to");
				$combi_item->setShowTime(true);
				$item->addCombinationItem("to", $combi_item, $lng->txt("to"));
				$item->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
				$item->setMode(ilDateTimeInputGUI::MODE_INPUT);
				break;

			case self::FILTER_DURATION_RANGE:
				$lng->loadLanguageModule("form");
				include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
				include_once("./Services/Form/classes/class.ilDurationInputGUI.php");
				$item = new ilCombinationInputGUI($caption, $id);
				$combi_item = new ilDurationInputGUI("", $id."_from");
				$combi_item->setShowMonths(false);
				$combi_item->setShowDays(true);
				$combi_item->setShowSeconds(true);
				$item->addCombinationItem("from", $combi_item, $lng->txt("from"));
				$combi_item = new ilDurationInputGUI("", $id."_to");
				$combi_item->setShowMonths(false);
				$combi_item->setShowDays(true);
				$combi_item->setShowSeconds(true);
				$item->addCombinationItem("to", $combi_item, $lng->txt("to"));
				$item->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
				break;
			
			default:
				return false;
		}

		$this->addFilterItem($item, $a_optional);
	    $item->readFromSession();
		return $item;
	}
	
	/**
	* Get filter items
	*/
	final function getFilterItems($a_optionals = false)
	{
		if (!$a_optionals)
		{
			return $this->filters;
		}
		return $this->optional_filters;
	}
	
	final function getFilterItemByPostVar($a_post_var)
	{
		foreach ($this->getFilterItems() as $item)
		{
			if ($item->getPostVar() == $a_post_var)
			{
				return $item;
			}
		}
		foreach ($this->getFilterItems(true) as $item)
		{
			if ($item->getPostVar() == $a_post_var)
			{
				return $item;
			}
		}
		return false;
	}

	/**
	* Set filter columns
	*
	* @param	int		number of filter columns
	*/
	function setFilterCols($a_val)
	{
		$this->filter_cols = $a_val;
	}
	
	/**
	* Get filter columns
	*
	* @return	int		number of filter columns
	*/
	function getFilterCols()
	{
		return $this->filter_cols;
	}
	
	/**
	* Set disable filter hiding
	*
	* @param	boolean			disable filter hiding
	*/
	function setDisableFilterHiding($a_val = true)
	{
		$this->disable_filter_hiding = $a_val;
	}
	
	/**
	* Get disable filter hiding		
	*
	* @return	boolean
	*/
	function getDisableFilterHiding()
	{
		return $this->disable_filter_hiding;
	}

	/**
	 * Is given filter selected?
	 *
	 * @param	string	column name
	 * @return	boolean
	 */
	function isFilterSelected($a_col)
	{
		return $this->selected_filter[$a_col];
	}

	/**
	 * Get selected filters
	 *
	 * @param
	 * @return
	 */
	function getSelectedFilters()
	{
		$sfil = array();
		foreach ($this->selected_filter as $k => $v)
		{
			if ($v)
			{
				$sfil[$k] = $k;
			}
		}
		return $sfil;
	}

	/**
	 * Determine selected filters
	 *
	 * @param
	 * @return
	 */
	function determineSelectedFilters()
	{
		if ($this->filters_determined)
		{
			return;
		}

		$old_sel = $this->loadProperty("selfilters");
		$stored = false;
		if ($old_sel != "")
		{
			$sel_filters =
				@unserialize($old_sel);
			$stored = true;
		}
		if(!is_array($sel_filters))
		{
			$stored = false;
			$sel_filters = array();
		}

		$this->selected_filter = array();
		$set = false;
		foreach ($this->getFilterItems(true) as $item)
		{
			$k = $item->getPostVar();
			
			$this->selected_filter[$k] = false;

			if ($_POST["tblfsf".$this->getId()])
			{
				$set = true;
				if (is_array($_POST["tblff".$this->getId()]) && in_array($k, $_POST["tblff".$this->getId()]))
				{
					$this->selected_filter[$k] = true;
				}
				else
				{
					$item->setValue(NULL);
					$item->writeToSession();
				}
			}
			else if ($stored)	// take stored values
			{
				$this->selected_filter[$k] = $sel_filters[$k];
			}
		}

		if ($old_sel != serialize($this->selected_filter) && $set)
		{
			$this->storeProperty("selfilters", serialize($this->selected_filter));
		}

		$this->filters_determined = true;
	}

	/**
	* Set custom previous/next links
	*/
	function setCustomPreviousNext($a_prev_link, $a_next_link)
	{
		$this->custom_prev_next = true;
		$this->custom_prev = $a_prev_link;
		$this->custom_next = $a_next_link;
	}
	
	/**
	* Set Form action parameter.
	*
	* @param	string	$a_form_action	Form action
	* @param	bvool	$a_multipart	Form multipart status
	*/
	final public function setFormAction($a_form_action, $a_multipart = false)
	{
		$this->form_action = $a_form_action;
		$this->form_multipart = (bool)$a_multipart;
	}
	
	/**
	* Get Form action parameter.
	*
	* @return	string	Form action
	*/
	final public function getFormAction()
	{
		return $this->form_action;
	}
	
	/**
	* Set Form name.
	*
	* @param	string	$a_formname	Form name
	*/
	function setFormName($a_formname)
	{
		$this->formname = $a_formname;
	}

	/**
	* Get Form name.
	*
	* @return	string	Form name
	*/
	function getFormName()
	{
		return $this->formname;
	}
	
	/**
	* Set id
	*
	* @param	string	element id
	*/
	function setId($a_val)
	{
		$this->id = $a_val;
		if ($this->getPrefix() == "")
		{
			$this->setPrefix($a_val);
		}
	}
	
	/**
	* Get element id
	*
	* @return	string	id
	*/
	function getId()
	{
		return $this->id;
	}

	/**
	* Set display as block
	*
	* @param	boolean	display as block
	*/
	function setDisplayAsBlock($a_val)
	{
		$this->display_as_block = $a_val;
	}
	
	/**
	* Get display as block
	*
	* @return	boolean		display as block
	*/
	function getDisplayAsBlock()
	{
		return $this->display_as_block;
	}
	
	/**
	* Get the name of the checkbox that should be toggled with a select all button
	*
	* @return	string	name of the checkbox
	*/
	function getSelectAllCheckbox()
	{
		return $this->select_all_checkbox;
	}
	
	/**
	* Set the name of the checkbox that should be toggled with a select all button
	*
	* @param	string	$a_select_all_checkbox name of the checkbox
	*/
	function setSelectAllCheckbox($a_select_all_checkbox)
	{
		$this->select_all_checkbox = $a_select_all_checkbox;
	}

	/**
	* Set external sorting
	*
	* @param	boolean		data is sorted externally
	*/
	function setExternalSorting($a_val)
	{
		$this->ext_sort = $a_val;
	}
	
	/**
	* Get external sorting
	*
	* @return	boolean		data is sorted externally
	*/
	function getExternalSorting()
	{
		return $this->ext_sort;
	}
	
	/**
	* Set filter command
	*
	* @param	string		filter command
	*/
	function setFilterCommand($a_val)
	{
		$this->filter_cmd = $a_val;
	}

	/**
	* Get filter command
	*
	* @return	string		filter command
	*/
	function getFilterCommand()
	{
		return $this->filter_cmd;
	}

	/**
	* Set reset filter command
	*
	* @param	string		reset command
	*/
	function setResetCommand($a_val)
	{
		$this->reset_cmd = $a_val;
	}

	/**
	* Get reset filter command
	*
	* @return	string		reset command
	*/
	function getResetCommand()
	{
		return $this->reset_cmd;
	}

	/**
	* Set external segmentation
	*
	* @param	boolean		data is segmented externally
	*/
	function setExternalSegmentation($a_val)
	{
		$this->ext_seg = $a_val;
	}
	
	/**
	* Get external segmentation
	*
	* @return	boolean		data is segmented externally
	*/
	function getExternalSegmentation()
	{
		return $this->ext_seg;
	}
	
	/**
	* Set row template.
	*
	* @param	string	$a_template			Template file name.
	* @param	string	$a_template_dir		Service/Module directory.
	*/
	final public function setRowTemplate($a_template, $a_template_dir = "")
	{
		$this->row_template = $a_template;
		$this->row_template_dir = $a_template_dir;
	}
	
	/**
	* Set Default order field.
	*
	* @param	string	$a_defaultorderfield	Default order field
	*/
	function setDefaultOrderField($a_defaultorderfield)
	{
		$this->defaultorderfield = $a_defaultorderfield;
	}

	/**
	* Get Default order field.
	*
	* @return	string	Default order field
	*/
	function getDefaultOrderField()
	{
		return $this->defaultorderfield;
	}

	/**
	* Set Default order direction.
	*
	* @param	string	$a_defaultorderdirection	Default order direction
	*/
	function setDefaultOrderDirection($a_defaultorderdirection)
	{
		$this->defaultorderdirection = $a_defaultorderdirection;
	}

	/**
	* Get Default order direction.
	*
	* @return	string	Default order direction
	*/
	function getDefaultOrderDirection()
	{
		return $this->defaultorderdirection;
	}
	
	/*
	* Removes all command buttons from the table
	*
	* @access	public
	*/
	public function clearCommandButtons()
	{
		$this->buttons = array();
	}
	
	/**
	* Add Command button
	*
	* @param	string	Command
	* @param	string	Text
	*/
	function addCommandButton($a_cmd, $a_text, $a_onclick = '', $a_id = "", $a_class = null)
	{
		$this->buttons[] = array("cmd" => $a_cmd, "text" => $a_text, 'onclick' => $a_onclick,
			"id" => $a_id, "class" => $a_class);
	}
	
	/**
	 * Add Command button instance
	 * 
	 * @param ilButton $a_button
	 */
	function addCommandButtonInstance(ilButton $a_button)
	{
		$this->buttons[] = $a_button;
	}

	/**
	* Add Selection List + Command button
	*
	* @param	string	selection input variable name
	* @param	array	selection options ("value" => text")
	* @param	string	command
	* @param	string	button text
	* 
	* @deprecated
	*/
	function addSelectionButton($a_sel_var, $a_options, $a_cmd, $a_text, $a_default_selection = '')
	{
echo "ilTabl2GUI->addSelectionButton() has been deprecated with 4.2. Please try to move the drop-down to ilToolbarGUI.";
//		$this->sel_buttons[] = array("sel_var" => $a_sel_var, "options" => $a_options, "selected" => $a_default_selection, "cmd" => $a_cmd, "text" => $a_text);
	}
	
	/**
	* Add Selection List + Command button
	* for selected items
	*
	* @param	string	selection input variable name
	* @param	array	selection options ("value" => text")
	* @param	string	command
	* @param	string	button text
	*/
	public function addMultiItemSelectionButton($a_sel_var, $a_options, $a_cmd, $a_text, $a_default_selection = '')
	{
		$this->mi_sel_buttons[] = array("sel_var" => $a_sel_var, "options" => $a_options, "selected" => $a_default_selection, "cmd" => $a_cmd, "text" => $a_text);
		$this->addHiddenInput("cmd_sv[".$a_cmd."]", $a_sel_var);
	}
	
	
	
	/**
	* Add command for closing table.
	*
	* @param	string	$a_link		closing link
	 * @deprecated
	*/
	function setCloseCommand($a_link)
	{
		$this->close_command = $a_link;
	}

	/**
	* Add Command button
	*
	* @param	string	Command
	* @param	string	Text
	*/
	function addMultiCommand($a_cmd, $a_text)
	{
		$this->multi[] = array("cmd" => $a_cmd, "text" => $a_text);
	}

	/**
	* Add Hidden Input field
	*
	* @param	string	Name
	* @param	string	Value
	*/
	public function addHiddenInput($a_name, $a_value)
	{
		$this->hidden_inputs[] = array("name" => $a_name, "value" => $a_value);
	}
	
	/**
	* Add Header Command (Link)  (Image needed for now)
	*
	* @param	string	href
	* @param	string	text
	*/
	function addHeaderCommand($a_href, $a_text, $a_target = "", $a_img = "")
	{
		$this->header_commands[] = array("href" => $a_href, "text" => $a_text,
			"target" => $a_target, "img" => $a_img);
	}

	/**
	* Set top commands (display command buttons on top of table, too)
	*
	* @param	boolean		top commands true/false
	*/
	function setTopCommands($a_val)
	{
		$this->top_commands = $a_val;
	}
	
	/**
	* Get top commands (display command buttons on top of table, too)
	*
	* @return	boolean		top commands true/false
	*/
	function getTopCommands()
	{
		return $this->top_commands;
	}
	
	/**
	 * Add a column to the header.
	 *
	 * @param	string		Text
	 * @param	string		Sort field name (corresponds to data array field)
	 * @param	string		Width string
	 */
	final public function addColumn($a_text, $a_sort_field = "", $a_width = "",
		$a_is_checkbox_action_column = false, $a_class = "", $a_tooltip = "")
	{
		$this->column[] = array(
			"text" => $a_text,
			"sort_field" => $a_sort_field,
			"width" => $a_width,
			"is_checkbox_action_column" => $a_is_checkbox_action_column,
			"class" => $a_class,
			"tooltip" => $a_tooltip
			);
		if ($a_sort_field != "")
		{
			$this->sortable_fields[] = $a_sort_field;
		}
		$this->column_count = count($this->column);
	}
	
	
	final public function getNavParameter()
	{
		return $this->prefix."_table_nav";
	}
	
	function setOrderLink($sort_field, $order_dir)
	{
		global $ilCtrl, $ilUser;
		
		$hash = "";
		if (is_object($ilUser) && $ilUser->getPref("screen_reader_optimization"))
		{
			$hash = "#".$this->getTopAnchor();
		}

		$old = $_GET[$this->getNavParameter()];
		
		// set order link
		$ilCtrl->setParameter($this->parent_obj,
			$this->getNavParameter(),
			$sort_field.":".$order_dir.":".$this->offset);
		$this->tpl->setVariable("TBL_ORDER_LINK",
			$ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd).$hash);
		
		// set old value of nav variable
		$ilCtrl->setParameter($this->parent_obj,
			$this->getNavParameter(), $old);
	}

	function fillHeader()
	{
		global $lng;
		
		$allcolumnswithwidth = true;
		foreach ((array) $this->column as $idx => $column)
		{
			if (!strlen($column["width"])) 
			{
				$allcolumnswithwidth = false;
			}
			else if($column["width"] == "1")
			{
				// IE does not like 1 but seems to work with 1%
				$this->column[$idx]["width"] = "1%";
			}
		}
		if ($allcolumnswithwidth)
		{
			foreach ((array) $this->column as $column)
			{
				$this->tpl->setCurrentBlock("tbl_colgroup_column");
				$this->tpl->setVariable("COLGROUP_COLUMN_WIDTH", $column["width"]);
				$this->tpl->parseCurrentBlock();
			}
		}
		$ccnt = 0;
		foreach ((array) $this->column as $column)
		{
			$ccnt++;
			
			//tooltip
			if ($column["tooltip"] != "")
			{
				include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
				ilTooltipGUI::addTooltip("thc_".$this->getId()."_".$ccnt, $column["tooltip"]);
			}
			if (!$this->enabled["sort"] || $column["sort_field"] == "" || $column["is_checkbox_action_column"])
			{
				$this->tpl->setCurrentBlock("tbl_header_no_link");
				if ($column["width"] != "")
				{
					$this->tpl->setVariable("TBL_COLUMN_WIDTH_NO_LINK"," width=\"".$column["width"]."\"");
				}
				if (!$column["is_checkbox_action_column"])
				{
					$this->tpl->setVariable("TBL_HEADER_CELL_NO_LINK",
						$column["text"]);
				}
				else
				{
					$this->tpl->setVariable("TBL_HEADER_CELL_NO_LINK",
						ilUtil::img(ilUtil::getImagePath("spacer.png"), $lng->txt("action")));
				}
				$this->tpl->setVariable("HEAD_CELL_NL_ID", "thc_".$this->getId()."_".$ccnt);
				
				if ($column["class"] != "")
				{
					$this->tpl->setVariable("TBL_HEADER_CLASS"," " . $column["class"]);
				}
				$this->tpl->parseCurrentBlock();
				$this->tpl->touchBlock("tbl_header_th");
				continue;
			}
			if (($column["sort_field"] == $this->order_field) && ($this->order_direction != ""))
			{
				$this->tpl->setCurrentBlock("tbl_order_image");
				if ($this->order_direction == "asc")
				{
					$this->tpl->setVariable("ORDER_CLASS", "glyphicon glyphicon-arrow-up");
				}
				else
				{
					$this->tpl->setVariable("ORDER_CLASS", "glyphicon glyphicon-arrow-down");
				}
				$this->tpl->setVariable("IMG_ORDER_ALT", $this->lng->txt("change_sort_direction"));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("tbl_header_cell");
			$this->tpl->setVariable("TBL_HEADER_CELL", $column["text"]);
			$this->tpl->setVariable("HEAD_CELL_ID", "thc_".$this->getId()."_".$ccnt);
			
			// only set width if a value is given for that column
			if ($column["width"] != "")
			{
				$this->tpl->setVariable("TBL_COLUMN_WIDTH"," width=\"".$column["width"]."\"");
			}

			$lng_sort_column = $this->lng->txt("sort_by_this_column");
			$this->tpl->setVariable("TBL_ORDER_ALT",$lng_sort_column);
		
			$order_dir = "asc";

			if ($column["sort_field"] == $this->order_field)
			{ 
				$order_dir = $this->sort_order;

				$lng_change_sort = $this->lng->txt("change_sort_direction");
				$this->tpl->setVariable("TBL_ORDER_ALT",$lng_change_sort);
			}
		
			if ($column["class"] != "")
			{
				$this->tpl->setVariable("TBL_HEADER_CLASS"," " . $column["class"]);
			}
			$this->setOrderLink($column["sort_field"], $order_dir);
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("tbl_header_th");
		}
		
		$this->tpl->setCurrentBlock("tbl_header");
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Anything that must be done before HTML is generated
	*/
	protected function prepareOutput()
	{
	}
	
	
	/**
	* Determine offset and order
	*/
	function determineOffsetAndOrder($a_omit_offset = false)
	{
		global $ilUser;

		if ($this->nav_determined)
		{
			return true;
		}

		if ($_POST[$this->getNavParameter()."1"] != "")
		{
			if ($_POST[$this->getNavParameter()."1"] != $_POST[$this->getNavParameter()])
			{
				$this->nav_value = $_POST[$this->getNavParameter()."1"];
			}
			else if ($_POST[$this->getNavParameter()."2"] != $_POST[$this->getNavParameter()])
			{
				$this->nav_value = $_POST[$this->getNavParameter()."2"];
			}
		}
		elseif($_GET[$this->getNavParameter()])
		{
			$this->nav_value = $_GET[$this->getNavParameter()];
		}
		elseif($_SESSION[$this->getNavParameter()] != "")
		{
			$this->nav_value = $_SESSION[$this->getNavParameter()];
		}
		
		if ($this->nav_value == "" && $this->getId() != "" && $ilUser->getId() != ANONYMOUS_USER_ID)
		{
			$order = $this->loadProperty("order");
			if (in_array($order, $this->sortable_fields))
			{
				$direction = $this->loadProperty("direction");
			}
			else
			{
				$direction = $this->getDefaultOrderDirection();
			}
			// get order and direction from db
			$this->nav_value =
				$order.":".
				$direction.":".
				$this->loadProperty("offset");
		}
		$nav = explode(":", $this->nav_value);
		
		// $nav[0] is order by
		$this->setOrderField(($nav[0] != "") ? $nav[0] : $this->getDefaultOrderField());
		$this->setOrderDirection(($nav[1] != "") ? $nav[1] : $this->getDefaultOrderDirection());

		if (!$a_omit_offset)
		{
			// #8904: offset must be discarded when no limit is given
			if(!$this->getExternalSegmentation() && $this->limit_determined && $this->limit == 9999)
			{
				$this->resetOffset(true);
			}
			else if (!$this->getExternalSegmentation() && $nav[2] >= $this->max_count)
			{
				$this->resetOffset(true);
			}
			else
			{
				$this->setOffset($nav[2]);
			}
		}

		if (!$a_omit_offset)
		{
			$this->nav_determined = true;
		}
	}
	
	function storeNavParameter()
	{
		if ($this->getOrderField() != "")
		{
			$this->storeProperty("order", $this->getOrderField());
		}
		if ($this->getOrderDirection() != "")
		{
			$this->storeProperty("direction", $this->getOrderDirection());
		}
//echo "-".$this->getOffset()."-";
		if ($this->getOffset() !== "")
		{
			$this->storeProperty("offset", $this->getOffset());
		}
	}
	
	
	/**
	* Get HTML
	*/
	final public function getHTML()
	{
		global $lng, $ilCtrl, $ilUser;

		if($this->getExportMode())
		{
			$this->exportData($this->getExportMode(), true);
		}
		
		$this->prepareOutput();
		
		if (is_object($ilCtrl) && $this->getId() == "")
		{
			$ilCtrl->saveParameter($this->getParentObject(), $this->getNavParameter());
		}
				
		if(!$this->getPrintMode())
		{
			// set form action
			if ($this->form_action != "" && $this->getOpenFormTag())
			{
				$hash = "";
				if (is_object($ilUser) && $ilUser->getPref("screen_reader_optimization"))
				{
					$hash = "#".$this->getTopAnchor();
				}

				if((bool)$this->form_multipart)
				{
					$this->tpl->touchBlock("form_multipart_bl");
				}

				if($this->getPreventDoubleSubmission())
				{
					$this->tpl->touchBlock("pdfs");
				}

				$this->tpl->setCurrentBlock("tbl_form_header");
				$this->tpl->setVariable("FORMACTION", $this->getFormAction().$hash);
				$this->tpl->setVariable("FORMNAME", $this->getFormName());				
				$this->tpl->parseCurrentBlock();
			}

			if ($this->form_action != "" && $this->getCloseFormTag())
			{
				$this->tpl->touchBlock("tbl_form_footer");
			}
		}
		
		if(!$this->enabled['content'])
		{
			return $this->render();
		}

		if (!$this->getExternalSegmentation())
		{
			$this->setMaxCount(count($this->row_data));
		}

		$this->determineOffsetAndOrder();
		
		$this->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		
		$data = $this->getData();
		if($this->dataExists())
		{
			// sort
			if (!$this->getExternalSorting() && $this->enabled["sort"])
			{
				$data = ilUtil::sortArray($data, $this->getOrderField(),
					$this->getOrderDirection(), $this->numericOrdering($this->getOrderField()));
			}

			// slice
			if (!$this->getExternalSegmentation())
			{
				$data = array_slice($data, $this->getOffset(), $this->getLimit());
			}
		}
		
		// fill rows
		if($this->dataExists())
		{
			if($this->getPrintMode())
			{
				ilDatePresentation::setUseRelativeDates(false);
			}

			$this->tpl->addBlockFile("TBL_CONTENT", "tbl_content", $this->row_template,
				$this->row_template_dir);
	
			foreach($data as $set)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$this->css_row = ($this->css_row != "tblrow1")
					? "tblrow1"
					: "tblrow2";
				$this->tpl->setVariable("CSS_ROW", $this->css_row);

				$this->fillRow($set);
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			// add standard no items text (please tell me, if it messes something up, alex, 29.8.2008)
			$no_items_text = (trim($this->getNoEntriesText()) != '')
				? $this->getNoEntriesText()
				: $lng->txt("no_items");

			$this->css_row = ($this->css_row != "tblrow1")
					? "tblrow1"
					: "tblrow2";				
			
			$this->tpl->setCurrentBlock("tbl_no_entries");
			$this->tpl->setVariable('TBL_NO_ENTRY_CSS_ROW', $this->css_row);
			$this->tpl->setVariable('TBL_NO_ENTRY_COLUMN_COUNT', $this->column_count);
			$this->tpl->setVariable('TBL_NO_ENTRY_TEXT', trim($no_items_text));
			$this->tpl->parseCurrentBlock();			
		}


		if(!$this->getPrintMode())
		{
			$this->fillFooter();

			$this->fillHiddenRow();

			$this->fillActionRow();

			$this->storeNavParameter();
		}
		
		return $this->render();
	}

	/**
	* Should this field be sorted numeric?
	*
	* @return	boolean		numeric ordering; default is false
	*/
	function numericOrdering($a_field)
	{
		return false;
	}
	
	/**
	* render table
	* @access	public
	*/
	function render()
	{
		global $lng, $ilCtrl;

		$this->tpl->setVariable("CSS_TABLE",$this->getStyle("table"));
		$this->tpl->setVariable("DATA_TABLE", (int) $this->getIsDataTable());
		if ($this->getId() != "")
		{
			$this->tpl->setVariable("ID", 'id="'.$this->getId().'"');
		}
		
		// description
		if ($this->getDescription() != "")
		{
			$this->tpl->setCurrentBlock("tbl_header_description");
			$this->tpl->setVariable("TBL_DESCRIPTION", $this->getDescription());
			$this->tpl->parseCurrentBlock();
		}

		if(!$this->getPrintMode())
		{
			$this->renderFilter();
		}
		
		if ($this->getDisplayAsBlock())
		{
			$this->tpl->touchBlock("outer_start_1");
			$this->tpl->touchBlock("outer_end_1");
		}
		else
		{
			$this->tpl->touchBlock("outer_start_2");
			$this->tpl->touchBlock("outer_end_2");
		}
		
		// table title and icon
		if ($this->enabled["title"] && ($this->title != ""
			|| $this->icon != "" || count($this->header_commands) > 0 ||
			$this->headerHTML != "" || $this->close_command != ""))
		{
			if ($this->enabled["icon"])
			{
				$this->tpl->setCurrentBlock("tbl_header_title_icon");
				$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath($this->icon));
				$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->icon_alt);
				$this->tpl->parseCurrentBlock();
			}

			if(!$this->getPrintMode())
			{
				foreach($this->header_commands as $command)
				{
					if ($command["img"] != "")
					{
						$this->tpl->setCurrentBlock("tbl_header_img_link");
						if ($command["target"] != "")
						{
							$this->tpl->setVariable("TARGET_IMG_LINK",
								'target="'.$command["target"].'"');
						}
						$this->tpl->setVariable("ALT_IMG_LINK", $command["text"]);
						$this->tpl->setVariable("HREF_IMG_LINK", $command["href"]);
						$this->tpl->setVariable("SRC_IMG_LINK",
							$command["img"]);
						$this->tpl->parseCurrentBlock();
					}
					else
					{
						$this->tpl->setCurrentBlock("head_cmd");
						$this->tpl->setVariable("TXT_HEAD_CMD", $command["text"]);
						$this->tpl->setVariable("HREF_HEAD_CMD", $command["href"]);
						$this->tpl->parseCurrentBlock();
					}
				}
			}
			
			if (isset ($this->headerHTML)) {
				$this->tpl->setCurrentBlock("tbl_header_html");
				$this->tpl->setVariable ("HEADER_HTML", $this->headerHTML);
			    $this->tpl->parseCurrentBlock();
			}
			
			// close command
			if ($this->close_command != "")
			{
				$this->tpl->setCurrentBlock("tbl_header_img_link");
				$this->tpl->setVariable("ALT_IMG_LINK",$lng->txt("close"));
				$this->tpl->setVariable("HREF_IMG_LINK",$this->close_command);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("tbl_header_title");
			$this->tpl->setVariable("TBL_TITLE",$this->title);
			$this->tpl->setVariable("TOP_ANCHOR",$this->getTopAnchor());
			if ($this->getDisplayAsBlock())
			{
				$this->tpl->setVariable("BLK_CLASS", "Block");
			}
			$this->tpl->parseCurrentBlock();
		}

		// table header
		if ($this->enabled["header"])
		{
			$this->fillHeader();
		}

		$this->tpl->touchBlock("tbl_table_end");

		return $this->tpl->get();
	}

	/**
	* Render Filter section
	*/
	private function renderFilter()
	{
		global $lng, $tpl;
		
		$filter = $this->getFilterItems();
		$opt_filter = $this->getFilterItems(true);

		$tpl->addJavascript("./Services/Table/js/ServiceTable.js");
		
		if (count($filter) == 0 && count($opt_filter) == 0)
		{
			return;
		}

		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initConnection();

		$ccnt = 0;

		// render standard filter
		if (count($filter) > 0)
		{
			foreach ($filter as $item)
			{
				if ($ccnt >= $this->getFilterCols())
				{
					$this->tpl->setCurrentBlock("filter_row");
					$this->tpl->parseCurrentBlock();
					$ccnt = 0;
				}
				$this->tpl->setCurrentBlock("filter_item");
				$this->tpl->setVariable("OPTION_NAME",
					$item->getTitle());
				$this->tpl->setVariable("F_INPUT_ID",
					$item->getFieldId());
				$this->tpl->setVariable("INPUT_HTML",
					$item->getTableFilterHTML());
				$this->tpl->parseCurrentBlock();
				$ccnt++;
			}
		}
		
		// render optional filter
		if (count($opt_filter) > 0)
		{
			$this->determineSelectedFilters();

			foreach ($opt_filter as $item)
			{
				if($this->isFilterSelected($item->getPostVar()))
				{
					if ($ccnt >= $this->getFilterCols())
					{
						$this->tpl->setCurrentBlock("filter_row");
						$this->tpl->parseCurrentBlock();
						$ccnt = 0;
					}
					$this->tpl->setCurrentBlock("filter_item");
					$this->tpl->setVariable("OPTION_NAME",
						$item->getTitle());
					$this->tpl->setVariable("F_INPUT_ID",
						$item->getFieldId());
					$this->tpl->setVariable("INPUT_HTML",
						$item->getTableFilterHTML());
					$this->tpl->parseCurrentBlock();
					$ccnt++;
				}
			}
		
			// filter selection
			$items = array();
			foreach ($opt_filter as $item)
			{
				$k = $item->getPostVar();
				$items[$k] = array("txt" => $item->getTitle(),
					"selected" => $this->isFilterSelected($k));
			}

			include_once("./Services/UIComponent/CheckboxListOverlay/classes/class.ilCheckboxListOverlayGUI.php");
			$cb_over = new ilCheckboxListOverlayGUI("tbl_filters_".$this->getId());
			$cb_over->setLinkTitle($lng->txt("optional_filters"));
			$cb_over->setItems($items);

			$cb_over->setFormCmd($this->getParentCmd());
			$cb_over->setFieldVar("tblff".$this->getId());
			$cb_over->setHiddenVar("tblfsf".$this->getId());

			$cb_over->setSelectionHeaderClass("ilTableMenuItem");
			$this->tpl->setCurrentBlock("filter_select");

			// apply should be the first submit because of enter/return, inserting hidden submit
			$this->tpl->setVariable("HIDDEN_CMD_APPLY", $this->filter_cmd);

			$this->tpl->setVariable("FILTER_SELECTOR", $cb_over->getHTML());
		    $this->tpl->parseCurrentBlock();
		}

		// if any filter
		if($ccnt > 0 || count($opt_filter) > 0)
		{
			$this->tpl->setVariable("TXT_FILTER", $lng->txt("filter"));
			
			if($ccnt > 0)
			{
				if ($ccnt < $this->getFilterCols())
				{
					for($i = $ccnt; $i<=$this->getFilterCols(); $i++)
					{
						$this->tpl->touchBlock("filter_empty_cell");
					}
				}
				$this->tpl->setCurrentBlock("filter_row");
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("filter_buttons");				
				$this->tpl->setVariable("CMD_APPLY", $this->filter_cmd);
				$this->tpl->setVariable("TXT_APPLY", $lng->txt("apply_filter"));
				$this->tpl->setVariable("CMD_RESET", $this->reset_cmd);
				$this->tpl->setVariable("TXT_RESET", $lng->txt("reset_filter"));
			}
			else if(count($opt_filter) > 0)
			{
				$this->tpl->setCurrentBlock("optional_filter_hint");
				$this->tpl->setVariable('TXT_OPT_HINT', $lng->txt('optional_filter_hint'));
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("filter_section");
			$this->tpl->setVariable("FIL_ID", $this->getId());
			$this->tpl->parseCurrentBlock();
			
			// (keep) filter hidden?
			if ($this->loadProperty("filter") != 1)
			{
				if (!$this->getDisableFilterHiding())
				{
					$this->tpl->setCurrentBlock("filter_hidden");
					$this->tpl->setVariable("FI_ID", $this->getId());
					$this->tpl->parseCurrentBlock();
				}
			}
		}
	}
	
	/**
	* Write filter values to session
	*/
	public function writeFilterToSession()
	{
		global $lng;
		
		$filter = $this->getFilterItems();
		$opt_filter = $this->getFilterItems(true);
		
		foreach ($filter as $item)
		{
			if ($item->checkInput())
			{
				$item->setValueByArray($_POST);
				$item->writeToSession();
			}
		}
		foreach ($opt_filter as $item)
		{
			if ($item->checkInput())
			{
				$item->setValueByArray($_POST);
				$item->writeToSession();
			}
		}
		
		// #13209
		unset($_REQUEST["tbltplcrt"]);
		unset($_REQUEST["tbltpldel"]);	
	}

	/**
	* Reset filter
	*/
	public function resetFilter()
	{
		global $lng;
		
		$filter = $this->getFilterItems();
		$opt_filter = $this->getFilterItems(true);

		foreach ($filter as $item)
		{
			if ($item->checkInput())
			{
				$item->setValueByArray($_POST);
				$item->clearFromSession();
			}
		}
		foreach ($opt_filter as $item)
		{
			if ($item->checkInput())
			{
				$item->setValueByArray($_POST);
				$item->clearFromSession();
			}
		}
		
		// #13209
		unset($_REQUEST["tbltplcrt"]);
		unset($_REQUEST["tbltpldel"]);	
	}

	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*
	* @param	array	$a_set		data array
	*/
	protected function fillRow($a_set)
	{
		foreach ($a_set as $key => $value)
		{
			$this->tpl->setVariable("VAL_".strtoupper($key), $value);
		}
	}
	
	/**
	* Fill footer row
	*/
	function fillFooter()
	{
		global $lng, $ilCtrl, $ilUser;

		$footer = false;
		
		// select all checkbox
		if ((strlen($this->getFormName())) && (strlen($this->getSelectAllCheckbox())) && $this->dataExists())
		{
			$this->tpl->setCurrentBlock("select_all_checkbox");
			$this->tpl->setVariable("SELECT_ALL_TXT_SELECT_ALL", $lng->txt("select_all"));
			$this->tpl->setVariable("SELECT_ALL_CHECKBOX_NAME", $this->getSelectAllCheckbox());
			$this->tpl->setVariable("SELECT_ALL_FORM_NAME", $this->getFormName());
			$this->tpl->setVariable("CHECKBOXNAME", "chb_select_all_" . $this->unique_id);
			$this->tpl->parseCurrentBlock();
		}
		
		// table footer numinfo
		if ($this->enabled["numinfo"] && $this->enabled["footer"])
		{
			$start = $this->offset + 1;				// compute num info
			if (!$this->dataExists())
			{
				$start = 0;
			}
			$end = $this->offset + $this->limit;
			
			if ($end > $this->max_count or $this->limit == 0)
			{
				$end = $this->max_count;
			}
			
			if ($this->max_count > 0)
			{
				if ($this->lang_support)
				{
					$numinfo = "(".$start." - ".$end." ".strtolower($this->lng->txt("of"))." ".$this->max_count.")";
				}
				else
				{
					$numinfo = "(".$start." - ".$end." of ".$this->max_count.")";
				}
			}
			if ($this->max_count > 0)
			{
				if ($this->getEnableNumInfo())
				{
					$this->tpl->setCurrentBlock("tbl_footer_numinfo");
					$this->tpl->setVariable("NUMINFO", $numinfo);
					$this->tpl->parseCurrentBlock();
				}
			}
			$footer = true;
		}

		// table footer linkbar
		if ($this->enabled["linkbar"] && $this->enabled["footer"] && $this->limit  != 0
			 && $this->max_count > 0)
		{
			$layout = array(
							"link"	=> $this->footer_style,
							"prev"	=> $this->footer_previous,
							"next"	=> $this->footer_next,
							);
			//if (!$this->getDisplayAsBlock())
			//{
				$linkbar = $this->getLinkbar("1");
				$this->tpl->setCurrentBlock("tbl_footer_linkbar");
				$this->tpl->setVariable("LINKBAR", $linkbar);
				$this->tpl->parseCurrentBlock();
				$linkbar = true;
			//}
			$footer = true;
		}
		
		// column selector
		if (count($this->getSelectableColumns()) > 0)
		{
			$items = array();
			foreach ($this->getSelectableColumns() as $k => $c)
			{
				$items[$k] = array("txt" => $c["txt"],
					"selected" => $this->isColumnSelected($k));
			}
			include_once("./Services/UIComponent/CheckboxListOverlay/classes/class.ilCheckboxListOverlayGUI.php");
			$cb_over = new ilCheckboxListOverlayGUI("tbl_".$this->getId());
			$cb_over->setLinkTitle($lng->txt("columns"));
			$cb_over->setItems($items);
			//$cb_over->setUrl("./ilias.php?baseClass=ilTablePropertiesStorage&table_id=".
			//		$this->getId()."&cmd=saveSelectedFields&user_id=".$ilUser->getId());
			$cb_over->setFormCmd($this->getParentCmd());
			$cb_over->setFieldVar("tblfs".$this->getId());
			$cb_over->setHiddenVar("tblfsh".$this->getId());
			$cb_over->setSelectionHeaderClass("ilTableMenuItem");
			$column_selector = $cb_over->getHTML();
			$footer = true;
		}

		if($this->getShowTemplates() && is_object($ilUser))
		{
			// template handling
			if(isset($_REQUEST["tbltplcrt"]) && $_REQUEST["tbltplcrt"])
			{
				if($this->saveTemplate($_REQUEST["tbltplcrt"]))
				{
					ilUtil::sendSuccess($lng->txt("tbl_template_created"));
				}
			}
			else if(isset($_REQUEST["tbltpldel"]) && $_REQUEST["tbltpldel"])
			{
				if($this->deleteTemplate($_REQUEST["tbltpldel"]))
				{
					ilUtil::sendSuccess($lng->txt("tbl_template_deleted"));
				}
			}

			$create_id = "template_create_overlay_".$this->getId();
			$delete_id = "template_delete_overlay_".$this->getId();
			$list_id = "template_stg_".$this->getId();

			include_once("./Services/Table/classes/class.ilTableTemplatesStorage.php");
			$storage = new ilTableTemplatesStorage();
			$templates = $storage->getNames($this->getContext(), $ilUser->getId());
			
			include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");

			// form to delete template
			if(sizeof($templates))
			{
				$overlay = new ilOverlayGUI($delete_id);
				$overlay->setTrigger($list_id."_delete");
				$overlay->setAnchor("ilAdvSelListAnchorElement_".$list_id);
				$overlay->setAutoHide(false);
				$overlay->add();

				$lng->loadLanguageModule("form");
				$this->tpl->setCurrentBlock("template_editor_delete_item");
				$this->tpl->setVariable("TEMPLATE_DELETE_OPTION_VALUE", "");
				$this->tpl->setVariable("TEMPLATE_DELETE_OPTION", "- ".$lng->txt("form_please_select")." -");
				$this->tpl->parseCurrentBlock();
				foreach($templates as $name)
				{
					$this->tpl->setVariable("TEMPLATE_DELETE_OPTION_VALUE", $name);
					$this->tpl->setVariable("TEMPLATE_DELETE_OPTION", $name);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("template_editor_delete");
				$this->tpl->setVariable("TEMPLATE_DELETE_ID", $delete_id);
				$this->tpl->setVariable("TXT_TEMPLATE_DELETE", $lng->txt("tbl_template_delete"));
				$this->tpl->setVariable("TXT_TEMPLATE_DELETE_SUBMIT", $lng->txt("delete"));
				$this->tpl->setVariable("TEMPLATE_DELETE_CMD", $this->parent_cmd);
				$this->tpl->parseCurrentBlock();
			}


			// form to save new template
			$overlay = new ilOverlayGUI($create_id);
			$overlay->setTrigger($list_id."_create");
			$overlay->setAnchor("ilAdvSelListAnchorElement_".$list_id);
			$overlay->setAutoHide(false);
			$overlay->add();

			$this->tpl->setCurrentBlock("template_editor");
			$this->tpl->setVariable("TEMPLATE_CREATE_ID", $create_id);
			$this->tpl->setVariable("TXT_TEMPLATE_CREATE", $lng->txt("tbl_template_create"));
			$this->tpl->setVariable("TXT_TEMPLATE_CREATE_SUBMIT", $lng->txt("save"));
			$this->tpl->setVariable("TEMPLATE_CREATE_CMD", $this->parent_cmd);
			$this->tpl->parseCurrentBlock();

			// load saved template
			include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
			$alist = new ilAdvancedSelectionListGUI();
			$alist->setId($list_id);
			$alist->addItem($lng->txt("tbl_template_create"), "create", "#");
			if(sizeof($templates))
			{
				$alist->addItem($lng->txt("tbl_template_delete"), "delete", "#");
				foreach($templates as $name)
				{
					$ilCtrl->setParameter($this->parent_obj, $this->prefix."_tpl", urlencode($name));
					$alist->addItem($name, $name, $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
					$ilCtrl->setParameter($this->parent_obj, $this->prefix."_tpl", "");
				}
			}
			$alist->setListTitle($lng->txt("tbl_templates"));
			$alist->setStyle(ilAdvancedSelectionListGUI::STYLE_LINK_BUTTON);
			$this->tpl->setVariable("TEMPLATE_SELECTOR", "&nbsp;".$alist->getHTML());
		}

		if ($footer)
		{
			$this->tpl->setCurrentBlock("tbl_footer");
			$this->tpl->setVariable("COLUMN_COUNT", $this->getColumnCount());
			if ($this->getDisplayAsBlock())
			{
				$this->tpl->setVariable("BLK_CLASS", "Block");
			}
			$this->tpl->parseCurrentBlock();
			
			// top navigation, if number info or linkbar given
			if ($numinfo != "" || $linkbar != "" || $column_selector != "" ||
				count($this->filters) > 0 || count($this->optional_filters) > 0)
			{
				if (is_object($ilUser) && (count($this->filters) || count($this->optional_filters)))
				{
					$this->tpl->setCurrentBlock("filter_activation");
					$this->tpl->setVariable("TXT_ACTIVATE_FILTER", $lng->txt("show_filter"));
					$this->tpl->setVariable("FILA_ID", $this->getId());
					if ($this->getId() != "")
					{
						$this->tpl->setVariable("SAVE_URLA", "./ilias.php?baseClass=ilTablePropertiesStorage&table_id=".
							$this->getId()."&cmd=showFilter&user_id=".$ilUser->getId());
					}
					$this->tpl->parseCurrentBlock();

					
					if (!$this->getDisableFilterHiding())
					{
						$this->tpl->setCurrentBlock("filter_deactivation");
						$this->tpl->setVariable("TXT_HIDE", $lng->txt("hide_filter"));
						if ($this->getId() != "")
						{
							$this->tpl->setVariable("SAVE_URL", "./ilias.php?baseClass=ilTablePropertiesStorage&table_id=".
								$this->getId()."&cmd=hideFilter&user_id=".$ilUser->getId());
							$this->tpl->setVariable("FILD_ID", $this->getId());
						}
						$this->tpl->parseCurrentBlock();
					}
					
				}
				
				if ($numinfo != "" && $this->getEnableNumInfo())
				{
					$this->tpl->setCurrentBlock("top_numinfo");
					$this->tpl->setVariable("NUMINFO", $numinfo);
					$this->tpl->parseCurrentBlock();
				}
				if ($linkbar != "" && !$this->getDisplayAsBlock())
				{
					$linkbar = $this->getLinkbar("2");
					$this->tpl->setCurrentBlock("top_linkbar");
					$this->tpl->setVariable("LINKBAR", $linkbar);
					$this->tpl->parseCurrentBlock();
				}
				
				// column selector
				$this->tpl->setVariable("COLUMN_SELECTOR", $column_selector);
				
				// row selector
				if ($this->getShowRowsSelector() && 
					is_object($ilUser) &&
					$this->getId() &&
					$this->getLimit() < 9999) // JF, 2014-10-27
				{
					include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
					$alist = new ilAdvancedSelectionListGUI();
					$alist->setStyle(ilAdvancedSelectionListGUI::STYLE_LINK_BUTTON);
					$alist->setId("sellst_rows_".$this->getId());
					$hpp = ($ilUser->getPref("hits_per_page") != 9999)
						? $ilUser->getPref("hits_per_page")
						: $lng->txt("unlimited");
	
					$options = array(0 => $lng->txt("default")." (".$hpp.")",5 => 5, 10 => 10, 15 => 15, 20 => 20,
									 30 => 30, 40 => 40, 50 => 50,
									 100 => 100, 200 => 200, 400 => 400, 800 => 800);
					foreach ($options as $k => $v)
					{
						$ilCtrl->setParameter($this->parent_obj, $this->prefix."_trows", $k);
						$alist->addItem($v, $k, $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
						$ilCtrl->setParameter($this->parent_obj, $this->prefix."_trows", "");
					}
					$alist->setListTitle($this->getRowSelectorLabel() ? $this->getRowSelectorLabel() : $lng->txt("rows"));
					$this->tpl->setVariable("ROW_SELECTOR", $alist->getHTML());
				}

				// export
				if(sizeof($this->export_formats) && $this->dataExists())
				{				
					include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
					$alist = new ilAdvancedSelectionListGUI();
					$alist->setStyle(ilAdvancedSelectionListGUI::STYLE_LINK_BUTTON);
					$alist->setId("sellst_xpt");
					foreach($this->export_formats as $format => $caption_lng_id)
					{
						$ilCtrl->setParameter($this->parent_obj, $this->prefix."_xpt", $format);
						$url = $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd);
						$ilCtrl->setParameter($this->parent_obj, $this->prefix."_xpt", "");
						$alist->addItem($lng->txt($caption_lng_id), $format, $url);
					}
					$alist->setListTitle($lng->txt("export"));
					$this->tpl->setVariable("EXPORT_SELECTOR", "&nbsp;".$alist->getHTML());
				}
				
				$this->tpl->setCurrentBlock("top_navigation");
				$this->tpl->setVariable("COLUMN_COUNT", $this->getColumnCount());
				if ($this->getDisplayAsBlock())
				{
					$this->tpl->setVariable("BLK_CLASS", "Block");
				}
				$this->tpl->parseCurrentBlock();
			}
		}
	}
	
	/**
	* Get previous/next linkbar.
	*
	* @author Sascha Hofmann <shofmann@databay.de>
	*
	* @return	array	linkbar or false on error
	*/
	function getLinkbar($a_num)
	{
		global $ilCtrl, $lng, $ilUser;

		$hash = "";
		if (is_object($ilUser) && $ilUser->getPref("screen_reader_optimization"))
		{
			$hash = "#".$this->getTopAnchor();
		}

		$link = $ilCtrl->getLinkTargetByClass(get_class($this->parent_obj), $this->parent_cmd).
			"&".$this->getNavParameter()."=".
			$this->getOrderField().":".$this->getOrderDirection().":";
		
		$LinkBar = "";
		$layout_prev = $lng->txt("previous");
		$layout_next = $lng->txt("next");
		
		// if more entries then entries per page -> show link bar
		if ($this->max_count > $this->getLimit() || $this->custom_prev_next)
		{
			$sep = "<span>&nbsp;&nbsp;|&nbsp;&nbsp;</span>";

			// calculate number of pages
			$pages = intval($this->max_count / $this->getLimit());

			// add a page if a rest remains
			if (($this->max_count % $this->getLimit()))
				$pages++;
			
			// links to other pages
			$offset_arr = array();
			for ($i = 1 ;$i <= $pages ; $i++)
			{
				$newoffset = $this->getLimit() * ($i-1);

				$nav_value = $this->getOrderField().":".$this->getOrderDirection().":".$newoffset;
				$offset_arr[$nav_value] = $i;
			}
									
			$sep = "<span>&nbsp;&nbsp;&nbsp;&nbsp;</span>";
			
			// previous link
			if ($this->custom_prev_next && $this->custom_prev != "")
			{
				if ($LinkBar != "")
					$LinkBar .= $sep;
				$LinkBar .= "<a href=\"".$this->custom_prev.$hash."\">".$layout_prev."</a>";
			}
			else if ($this->getOffset() >= 1 && !$this->custom_prev_next)
			{
				if ($LinkBar != "")
					$LinkBar .= $sep;
				$prevoffset = $this->getOffset() - $this->getLimit();
				$LinkBar .= "<a href=\"".$link.$prevoffset.$hash."\">".$layout_prev."</a>";
			}
			else
			{
				if ($LinkBar != "")
					$LinkBar .= $sep;
				$LinkBar .= '<span class="ilTableFootLight">'.$layout_prev."</span>";
			}
			
			// current value
			if ($a_num == "1")
			{
				$LinkBar .= '<input type="hidden" name="'.$this->getNavParameter().
					'" value="'.$this->getOrderField().":".$this->getOrderDirection().":".$this->getOffset().'" />';
			}
			
			$sep = "<span>&nbsp;&nbsp;|&nbsp;&nbsp;</span>";

			// show next link (if not last page)
			if ($this->custom_prev_next && $this->custom_next != "")
			{
				if ($LinkBar != "")
					$LinkBar .= $sep;
				$LinkBar .= "<a href=\"".$this->custom_next.$hash."\">".$layout_next."</a>";
			}
			else if (! ( ($this->getOffset() / $this->getLimit())==($pages-1) ) && ($pages!=1) &&
				!$this->custom_prev_next)
			{
				if ($LinkBar != "")
					$LinkBar .= $sep; 
				$newoffset = $this->getOffset() + $this->getLimit();
				$LinkBar .= "<a href=\"".$link.$newoffset.$hash."\">".$layout_next."</a>";
			}
			else
			{
				if ($LinkBar != "")
					$LinkBar .= $sep; 
				$LinkBar .= '<span class="ilTableFootLight">'.$layout_next."</span>";
			}
			
			$sep = "<span>&nbsp;&nbsp;&nbsp;&nbsp;</span>";
			
			if (count($offset_arr) && !$this->getDisplayAsBlock() && !$this->custom_prev_next)
			{				
				if ($LinkBar != "")
					$LinkBar .= $sep;
				$LinkBar .= "".
					'<label for="tab_page_sel_'.$a_num.'">'.$lng->txt("page").'</label> '.
					ilUtil::formSelect($this->nav_value,
					$this->getNavParameter().$a_num, $offset_arr, false, true, 0, "small",
					array("id" => "tab_page_sel_".$a_num,
						"onchange" => "ilTablePageSelection(this, 'cmd[".$this->parent_cmd."]')"));
					//' <input class="submit" type="submit" name="cmd['.$this->parent_cmd.']" value="'.
					//$lng->txt("ok").'" />';
			}

			return $LinkBar;
		}
		else
		{
			return false;
		}
	}
	
	function fillHiddenRow()
	{
		$hidden_row = false;
		if(count($this->hidden_inputs))
		{
			foreach ($this->hidden_inputs as $hidden_input)
			{
				$this->tpl->setCurrentBlock("tbl_hidden_field");
				$this->tpl->setVariable("FIELD_NAME", $hidden_input["name"]);
				$this->tpl->setVariable("FIELD_VALUE", $hidden_input["value"]);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("tbl_hidden_row");
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* Fill Action Row.
	*/
	function fillActionRow()
	{
		global $lng;
		
		// action row
		$action_row = false;
		$arrow = false;
		
		// add selection buttons
		if (count($this->sel_buttons) > 0)
		{
			foreach ($this->sel_buttons as $button)
			{
				$this->tpl->setCurrentBlock("sel_button");
				$this->tpl->setVariable("SBUTTON_SELECT", 
					ilUtil::formSelect($button["selected"], $button["sel_var"],
						$button["options"], false, true));
				$this->tpl->setVariable("SBTN_NAME", $button["cmd"]);
				$this->tpl->setVariable("SBTN_VALUE", $button["text"]);
				$this->tpl->parseCurrentBlock();
				
				if ($this->getTopCommands())
				{
					$this->tpl->setCurrentBlock("sel_top_button");
					$this->tpl->setVariable("SBUTTON_SELECT", 
						ilUtil::formSelect($button["selected"], $button["sel_var"],
							$button["options"], false, true));
					$this->tpl->setVariable("SBTN_NAME", $button["cmd"]);
					$this->tpl->setVariable("SBTN_VALUE", $button["text"]);
					$this->tpl->parseCurrentBlock();
				}
			}
			$buttons = true;
			$action_row = true;
		}
		$this->sel_buttons[] = array("options" => $a_options, "cmd" => $a_cmd, "text" => $a_text);
		
		// add buttons
		if (count($this->buttons) > 0)
		{
			foreach ($this->buttons as $button)
			{
				if(!is_array($button))
				{
					if($button instanceof ilButton)
					{	
						$this->tpl->setVariable('BUTTON_OBJ', $button->render());	
						
						// this will remove id - should be unique
						$button = clone $button;
																		
						$this->tpl->setVariable('BUTTON_TOP_OBJ', $button->render());
					}
					continue;
				}
				
				if (strlen($button['onclick']))
				{
					$this->tpl->setCurrentBlock('cmdonclick');
					$this->tpl->setVariable('CMD_ONCLICK', $button['onclick']);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("plain_button");
				if ($button["id"] != "")
				{
					$this->tpl->setVariable("PBID", ' id="'.$button["id"].'" ');
				}				
				if ($button["class"] != "")
				{
					$this->tpl->setVariable("PBBT_CLASS", ' '.$button["class"]);
				}
				$this->tpl->setVariable("PBTN_NAME", $button["cmd"]);
				$this->tpl->setVariable("PBTN_VALUE", $button["text"]);
				$this->tpl->parseCurrentBlock();
				
				if ($this->getTopCommands())
				{
					if (strlen($button['onclick']))
					{
						$this->tpl->setCurrentBlock('top_cmdonclick');
						$this->tpl->setVariable('CMD_ONCLICK', $button['onclick']);
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("plain_top_button");
					$this->tpl->setVariable("PBTN_NAME", $button["cmd"]);
					$this->tpl->setVariable("PBTN_VALUE", $button["text"]);
					if ($button["class"] != "")
					{
						$this->tpl->setVariable("PBBT_CLASS", ' '.$button["class"]);
					}
					$this->tpl->parseCurrentBlock();
				}
			}
			
			$buttons = true;
			$action_row = true;
		}
		
		// multi selection
		if(count($this->mi_sel_buttons))
		{
			foreach ($this->mi_sel_buttons as $button)
			{
				$this->tpl->setCurrentBlock("mi_sel_button");
				$this->tpl->setVariable("MI_BUTTON_SELECT", 
					ilUtil::formSelect($button["selected"], $button["sel_var"],
						$button["options"], false, true));
				$this->tpl->setVariable("MI_BTN_NAME", $button["cmd"]);
				$this->tpl->setVariable("MI_BTN_VALUE", $button["text"]);
				$this->tpl->parseCurrentBlock();
				
				if ($this->getTopCommands())
				{
					$this->tpl->setCurrentBlock("mi_top_sel_button");
					$this->tpl->setVariable("MI_BUTTON_SELECT", 
						ilUtil::formSelect($button["selected"], $button["sel_var"]."_2",
							$button["options"], false, true));
					$this->tpl->setVariable("MI_BTN_NAME", $button["cmd"]);
					$this->tpl->setVariable("MI_BTN_VALUE", $button["text"]);
					$this->tpl->parseCurrentBlock();
				}

			}
			$arrow = true;
			$action_row = true;
		}
		
		
		if (count($this->multi) > 1 && $this->dataExists())
		{
			if($this->enable_command_for_all && $this->max_count <= self::getAllCommandLimit())
			{
				$this->tpl->setCurrentBlock("tbl_cmd_select_all");
				$this->tpl->setVariable("TXT_SELECT_CMD_ALL", $lng->txt("all_objects"));
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("tbl_cmd_select");
			$sel = array();
			foreach ($this->multi as $mc)
			{
				$sel[$mc["cmd"]] = $mc["text"];
			}
			$this->tpl->setVariable("SELECT_CMDS",
				ilUtil::formSelect("", "selected_cmd", $sel, false, true));
			$this->tpl->setVariable("TXT_EXECUTE", $lng->txt("execute"));
			$this->tpl->parseCurrentBlock();
			$arrow = true;
			$action_row = true;
			
			if ($this->getTopCommands())
			{
				if($this->enable_command_for_all && $this->max_count <= self::getAllCommandLimit())
				{
					$this->tpl->setCurrentBlock("tbl_top_cmd_select_all");
					$this->tpl->setVariable("TXT_SELECT_CMD_ALL", $lng->txt("all_objects"));
					$this->tpl->parseCurrentBlock();
				}
				
				$this->tpl->setCurrentBlock("tbl_top_cmd_select");
				$sel = array();
				foreach ($this->multi as $mc)
				{
					$sel[$mc["cmd"]] = $mc["text"];
				}
				$this->tpl->setVariable("SELECT_CMDS",
					ilUtil::formSelect("", "selected_cmd2", $sel, false, true));
				$this->tpl->setVariable("TXT_EXECUTE", $lng->txt("execute"));
				$this->tpl->parseCurrentBlock();
			}			
		}
		elseif(count($this->multi) == 1  && $this->dataExists())
		{
			$this->tpl->setCurrentBlock("tbl_single_cmd");
			$sel = array();
			foreach ($this->multi as $mc)
			{
				$cmd = $mc['cmd'];
				$txt = $mc['text'];
			}
			$this->tpl->setVariable("TXT_SINGLE_CMD",$txt);
			$this->tpl->setVariable("SINGLE_CMD",$cmd);
			$this->tpl->parseCurrentBlock();
			$arrow = true;
			$action_row = true;
			
			if ($this->getTopCommands())
			{
				$this->tpl->setCurrentBlock("tbl_top_single_cmd");
				$sel = array();
				foreach ($this->multi as $mc)
				{
					$cmd = $mc['cmd'];
					$txt = $mc['text'];
				}
				$this->tpl->setVariable("TXT_SINGLE_CMD",$txt);
				$this->tpl->setVariable("SINGLE_CMD",$cmd);
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($arrow)
		{
			$this->tpl->setCurrentBlock("tbl_action_img_arrow");
			$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
			$this->tpl->setVariable("ALT_ARROW", $lng->txt("action"));
			$this->tpl->parseCurrentBlock();

			if ($this->getTopCommands())
			{
				$this->tpl->setCurrentBlock("tbl_top_action_img_arrow");
				$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_upright.svg"));
				$this->tpl->setVariable("ALT_ARROW", $lng->txt("action"));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if ($action_row)
		{
			$this->tpl->setCurrentBlock("tbl_action_row");
			$this->tpl->parseCurrentBlock();
			if ($this->getTopCommands())
			{
				$this->tpl->setCurrentBlock("tbl_top_action_row");
				$this->tpl->parseCurrentBlock();
			}
		}
	}
	
	/**
	 * set header html
	 *
	 * @param string $html
	 */
	public function setHeaderHTML($html) 
	{
	    $this->headerHTML = $html;
	}

	/**
	 * Store table property
	 *
	 * @param	string	$type
	 * @param	mixed	$value
	 */
	function storeProperty($type, $value)
	{
		global $ilUser;

		if(is_object($ilUser) && $this->getId() != "")
		{
			include_once("./Services/Table/classes/class.ilTablePropertiesStorage.php");
			$tab_prop = new ilTablePropertiesStorage();

			$tab_prop->storeProperty($this->getId(), $ilUser->getId(), $type, $value);
		}
	}

	/**
	 * Load table property
	 *
	 * @param	string	$type
	 * @return	mixed
	 */
	function loadProperty($type)
    {
		global $ilUser;

		if(is_object($ilUser) && $this->getId() != "")
		{
			include_once("./Services/Table/classes/class.ilTablePropertiesStorage.php");
			$tab_prop = new ilTablePropertiesStorage();

			return $tab_prop->getProperty($this->getId(), $ilUser->getId(), $type);
		}
	}

    /**
	 * get current settings for order, limit, columns and filter
	 *
	 * @return array
	 */
	public function getCurrentState()
	{
		$this->determineOffsetAndOrder();
		$this->determineLimit();
		$this->determineSelectedColumns();
		$this->determineSelectedFilters();

		// "filter" show/hide is not saved

		$result = array();
		$result["order"] = $this->getOrderField();
		$result["direction"] = $this->getOrderDirection();
		$result["offset"] = $this->getOffset();
		$result["rows"] = $this->getLimit();		
		$result["selfilters"] = $this->getSelectedFilters();
		
		// #9514 - $this->getSelectedColumns() will omit deselected, leading to 
		// confusion on restoring template
		$result["selfields"] = $this->selected_column;  
		
		// gather filter values
		if($this->filters)
		{
			foreach($this->filters as $item)
			{
				$result["filter_values"][$item->getFieldId()] = $this->getFilterValue($item);
			}
		}
		if($this->optional_filters && $result["selfilters"])
		{
			foreach($this->optional_filters as $item)
			{
				if(in_array($item->getFieldId(), $result["selfilters"]))
				{
					$result["filter_values"][$item->getFieldId()] = $this->getFilterValue($item);
				}
			}
		}

		return $result;
	}

	/**
	 * Get current filter value
	 *
	 * @param ilFormPropertyGUI $a_item
	 * @return mixed
	 */
	protected function getFilterValue(ilFormPropertyGUI $a_item)
	{
		if(method_exists($a_item, "getChecked"))
		{
			return $a_item->getChecked();
		}
		else if(method_exists($a_item, "getValue"))
		{
			return $a_item->getValue();
		}
		else if(method_exists($a_item, "getDate"))
		{
			return $a_item->getDate()->get(IL_CAL_DATE);
		}
	}

	/**
	 * Set current filter value
	 *
	 * @param ilFormPropertyGUI $a_item
	 * @param mixed $a_value
	 */
	protected function SetFilterValue(ilFormPropertyGUI $a_item, $a_value)
	{
		if(method_exists($a_item, "setChecked"))
		{
			$a_item->setChecked($a_value);
		}
		else if(method_exists($a_item, "setValue"))
		{
			$a_item->setValue($a_value);
		}
		else if(method_exists($a_item, "setDate"))
		{
			$a_item->setDate(new ilDate($a_value, IL_CAL_DATE));
		}
		$a_item->writeToSession();
	}

	/**
	 * Set context
	 *
	 * @param	string	$id
	 */
	public function setContext($id)
	{
		if(trim($id))
		{
			$this->context = $id;
		}
	}

	/**
	 * Get context
	 *
	 * @return	string
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Toggle rows-per-page selector
	 *
	 * @param	bool	$a_value
	 */
	public function setShowRowsSelector($a_value)
	{
		$this->show_rows_selector = (bool)$a_value;
	}

	/**
	 * Get rows-per-page selector state
	 *
	 * @return	bool
	 */
	public function getShowRowsSelector()
	{
		return $this->show_rows_selector;
	}

	/**
	 * Toggle templates
	 *
	 * @param	bool	$a_value
	 */
	public function setShowTemplates($a_value)
	{
		$this->show_templates = (bool)$a_value;
	}
	
	/**
	 * Get template state
	 *
	 * @return	bool
	 */
	public function getShowTemplates()
	{
		return $this->show_templates;
	}

	/**
	 * Restore state from template
	 *
	 * @param	string	$a_name
	 * @return	bool
	 */
	public function restoreTemplate($a_name)
	{
		global $ilUser;
		
		$a_name = ilUtil::stripSlashes($a_name);

		if(trim($a_name) && $this->getContext() != "" && is_object($ilUser) && $ilUser->getId() != ANONYMOUS_USER_ID)
		{
			include_once("./Services/Table/classes/class.ilTableTemplatesStorage.php");
			$storage = new ilTableTemplatesStorage();

			$data = $storage->load($this->getContext(), $ilUser->getId(), $a_name);
			if(is_array($data))
			{
				foreach($data as $property => $value)
				{
					$this->storeProperty($property, $value);
				}
			}

			$data["filter_values"] = unserialize($data["filter_values"]);
			if($data["filter_values"])
			{
				$this->restore_filter_values = $data["filter_values"];
			}
			
			$this->restore_filter = true;

			return true;
		}
		return false;
	}

	/**
	 * Save current state as template
	 *
	 * @param	string	$a_name
	 * @return	bool
	 */
	public function saveTemplate($a_name)
	{
		global $ilUser;
		
		$a_name = ilUtil::prepareFormOutput($a_name, true);
		
		if(trim($a_name) && $this->getContext() != "" && is_object($ilUser) && $ilUser->getId() != ANONYMOUS_USER_ID)
		{
			include_once("./Services/Table/classes/class.ilTableTemplatesStorage.php");
			$storage = new ilTableTemplatesStorage();

			$state = $this->getCurrentState();
			$state["filter_values"] = serialize($state["filter_values"]);
			$state["selfields"] = serialize($state["selfields"]);
			$state["selfilters"] = serialize($state["selfilters"]);

			$storage->store($this->getContext(), $ilUser->getId(), $a_name, $state);
			return true;
		}
		return false;
	}

	/**
	 * Delete template
	 *
	 * @param	string	$a_name
	 * @return	bool
	 */
	public function deleteTemplate($a_name)
	{
		global $ilUser;
		
		$a_name = ilUtil::prepareFormOutput($a_name, true);

		if(trim($a_name) && $this->getContext() != "" && is_object($ilUser) && $ilUser->getId() != ANONYMOUS_USER_ID)
		{
			include_once("./Services/Table/classes/class.ilTableTemplatesStorage.php");
			$storage = new ilTableTemplatesStorage();
			$storage->delete($this->getContext(), $ilUser->getId(), $a_name);
			return true;
		}
		return false;
	}

	/**
	* Get limit.
	*/
	function getLimit()
	{
		if($this->getExportMode() || $this->getPrintMode())
		{
			return 9999;
		}
		return parent::getLimit();
	}

	/**
	* Get offset.
	*/
	function getOffset()
	{
		if($this->getExportMode() || $this->getPrintMode())
		{
			return 0;
		}
		return parent::getOffset();
	}

	/**
	 * Set available export formats
	 *
	 * @param	array	$formats
	 */
    public function setExportFormats(array $formats)
    {		
		$this->export_formats = array();
		
		// #11339
		$valid = array(self::EXPORT_EXCEL => "tbl_export_excel",
			self::EXPORT_CSV => "tbl_export_csv");
		
		foreach($formats as $format)
	    {
		   if(array_key_exists($format, $valid))
		   {
				$this->export_formats[$format] = $valid[$format];
		   }
		}
	}

	/**
	 * Toogle print mode
	 * @param	bool	$a_value
	 */
	public function setPrintMode($a_value = false)
    {
		$this->print_mode = (bool)$a_value;
	}

	/**
	 * Get print mode
	 * @return	bool	$a_value
	 */
	public function getPrintMode()
    {
		return $this->print_mode;
	}

	/**
	 * Was export activated?
	 *
	 * @return	int
	 */
	public function getExportMode()
    {
		return $this->export_mode;
	}

	 /**
	 * Export and optionally send current table data
	 *
	 * @param	int	$format
	 */
	public function exportData($format, $send = false)
	{
		if($this->dataExists())
		{
			// #9640: sort
			if (!$this->getExternalSorting() && $this->enabled["sort"])
			{				
				$this->determineOffsetAndOrder(true);
				
				$this->row_data = ilUtil::sortArray($this->row_data, $this->getOrderField(),
					$this->getOrderDirection(), $this->numericOrdering($this->getOrderField()));
			}
			
			$filename = "export";

			switch($format)
			{
				case self::EXPORT_EXCEL:
					include_once "./Services/Excel/classes/class.ilExcelUtils.php";
					include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
					$adapter = new ilExcelWriterAdapter($filename.".xls", $send);
					$workbook = $adapter->getWorkbook();
					$worksheet = $workbook->addWorksheet();
					$row = 0;
					
					ob_start();
					$this->fillMetaExcel($worksheet, $row); // row must be increment in fillMetaExcel()! (optional method)
					
					// #14813
					$pre = $row;
					$this->fillHeaderExcel($worksheet, $row); // row should NOT be incremented in fillHeaderExcel()! (required method)
					if($pre == $row)
					{
						$row++; 
					}
					
					foreach($this->row_data as $set)
					{						
						$this->fillRowExcel($worksheet, $row, $set);
						$row++; // #14760
					}
					ob_end_clean();

					$workbook->close();					
					break;

				case self::EXPORT_CSV:
					include_once "./Services/Utilities/classes/class.ilCSVWriter.php";
				    $csv = new ilCSVWriter();
					$csv->setSeparator(";");

					ob_start();
					$this->fillMetaCSV($csv);
					$this->fillHeaderCSV($csv);
					foreach($this->row_data as $set)
					{
						$this->fillRowCSV($csv, $set);
					}
					ob_end_clean();

					if($send)
					{
						$filename .= ".csv";
						header("Content-type: text/comma-separated-values");
						header("Content-Disposition: attachment; filename=\"".$filename."\"");
						header("Expires: 0");
						header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
						header("Pragma: public");
						echo $csv->getCSVString();

					}
					else
					{
						file_put_contents($filename, $csv->getCSVString());
					}
					break;
			}
		   		   
			if($send)
			{
				exit();
			}
		}
	}

	/**
	 * Add meta information to excel export. Likely to
	 * be overwritten by derived class.
	 *
	 * @param	object	$a_worksheet	current sheet
	 * @param	int		$a_row			row counter
	 */
	protected function fillMetaExcel($worksheet, &$a_row)
	{

	}

	/**
	 * Excel Version of Fill Header. Likely to
	 * be overwritten by derived class.
	 *
	 * @param	object	$a_worksheet	current sheet
	 * @param	int		$a_row			row counter
	 */
	protected function fillHeaderExcel($worksheet, &$a_row)
	{
		$col = 0;
		foreach ($this->column as $column)
		{
			$title = strip_tags($column["text"]);
			if($title)
			{
				$worksheet->write($a_row, $col, $title);
				$col++;
			}
		}		
	}

	/**
	* Excel Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*
	* @param	object	$a_worksheet	current sheet
	* @param	int		$a_row			row counter
	* @param	array	$a_set			data array
	*/
	protected function fillRowExcel($a_worksheet, &$a_row, $a_set)
	{
		$col = 0;
		foreach ($a_set as $key => $value)
		{
			if(is_array($value))
			{
				$value = implode(', ', $value);
			}
			$a_worksheet->write($a_row, $col, strip_tags($value));
			$col++;
		}
	}

	/**
	 * Add meta information to csv export. Likely to
	 * be overwritten by derived class.
	 *
	 * @param	object	$a_csv			current file
	 */
	protected function fillMetaCSV($a_csv)
	{
		
	}

	/**
	 * CSV Version of Fill Header. Likely to
	 * be overwritten by derived class.
	 *
	 * @param	object	$a_csv			current file
	 */
	protected function fillHeaderCSV($a_csv)
	{
		foreach ($this->column as $column)
		{
			$title = strip_tags($column["text"]);
			if($title)
			{
				$a_csv->addColumn($title);
			}
		}
		$a_csv->addRow();
	}

	/**
	* CSV Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*
	* @param	object	$a_csv			current file
	* @param	array	$a_set			data array
	*/
	protected function fillRowCSV($a_csv, $a_set)
	{
		foreach ($a_set as $key => $value)
		{
			if(is_array($value))
			{
				$value = implode(', ', $value);
			}
			$a_csv->addColumn(strip_tags($value));
		}
		$a_csv->addRow();
	}
		
	/**
	 * Enable actions for all entries in current result
	 * 
	 * @param bool $a_value 
	 */
	public function setEnableAllCommand($a_value)
	{
		$this->enable_command_for_all = (bool)$a_value;
	}
	
	/**
	 * Get maximum number of entries to enable actions for all 
	 *
	 * @return int 
	 */
	public static function getAllCommandLimit()
	{
		global $ilClientIniFile;
		
		$limit = $ilClientIniFile->readVariable("system", "TABLE_ACTION_ALL_LIMIT");
		if(!$limit)
		{
			$limit = self::ACTION_ALL_LIMIT;
		}
		
		return $limit;
	}

	/**
	 * @param string $row_selector_label
	 */
	public function setRowSelectorLabel($row_selector_label)
	{
		$this->row_selector_label = $row_selector_label;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRowSelectorLabel()
	{
		return $this->row_selector_label;
	}

	/**
	 * Set prevent double submission
	 *
	 * @param bool $a_val prevent double submission
	 */
	public function setPreventDoubleSubmission($a_val)
	{
		$this->prevent_double_submission = $a_val;
	}

	/**
	 * Get prevent double submission
	 *
	 * @return bool prevent double submission
	 */
	public function getPreventDoubleSubmission()
	{
		return $this->prevent_double_submission;
	}
}

?>