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
	protected $filter_cols = 4;
	protected $ext_sort = false;
	protected $ext_seg = false;
	
	protected $mi_sel_buttons = null;
	
	/**
	* Constructor
	*
	*/
	public function __construct($a_parent_obj, $a_parent_cmd = "")
	{
		global $ilUser;
		
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
		
		if (is_object($ilUser))
		{
			$this->setLimit($ilUser->getPref("hits_per_page"));
		}
		$this->setIsDataTable(true);
		$this->setEnableNumInfo(true);
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
	*/
	final public function setFormAction($a_form_action)
	{
		$this->form_action = $a_form_action;
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
	function addCommandButton($a_cmd, $a_text, $a_onclick = '')
	{
		$this->buttons[] = array("cmd" => $a_cmd, "text" => $a_text, 'onclick' => $a_onclick);
	}

	/**
	* Add Selection List + Command button
	*
	* @param	string	selection input variable name
	* @param	array	selection options ("value" => text")
	* @param	string	command
	* @param	string	button text
	*/
	function addSelectionButton($a_sel_var, $a_options, $a_cmd, $a_text, $a_default_selection = '')
	{
		$this->sel_buttons[] = array("sel_var" => $a_sel_var, "options" => $a_options, "selected" => $a_default_selection, "cmd" => $a_cmd, "text" => $a_text);
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
	}
	
	
	
	/**
	* Add command for closing table.
	*
	* @param	string	$a_link		closing link
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
	* Add a column to the header.
	*
	* @param	string		Text
	* @param	string		Sort field name (corresponds to data array field)
	* @param	string		Width string
	*/
	final public function addColumn($a_text, $a_sort_field = "", $a_width = "",
		$a_is_checkbox_action_column = false, $a_class = "")
	{
		$this->column[] = array(
			"text" => $a_text,
			"sort_field" => $a_sort_field,
			"width" => $a_width,
			"is_checkbox_action_column" => $a_is_checkbox_action_column,
			"class" => $a_class
			);
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
		if (is_object($ilUser) && $ilUser->prefs["screen_reader_optimization"])
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
		
		foreach ($this->column as $column)
		{
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
						ilUtil::img(ilUtil::getImagePath("spacer.gif"), $lng->txt("action")));
				}
				
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
				$this->tpl->setVariable("IMG_ORDER_DIR",ilUtil::getImagePath($this->order_direction."_order.gif"));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("tbl_header_cell");
			$this->tpl->setVariable("TBL_HEADER_CELL", $column["text"]);
			
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
	function determineOffsetAndOrder()
	{
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

		$nav = explode(":", $this->nav_value);
		
		// $nav[0] is order by
		$this->setOrderField(($nav[0] != "") ? $nav[0] : $this->getDefaultOrderField());
		$this->setOrderDirection(($nav[1] != "") ? $nav[1] : $this->getDefaultOrderDirection());
		$this->setOffset($nav[2]);
	}
	
	
	/**
	* Get HTML
	*/
	final public function getHTML()
	{
		global $lng, $ilCtrl, $ilUser;
		
		$this->prepareOutput();
		
		if (is_object($ilCtrl))
		{
			$ilCtrl->saveParameter($this->getParentObject(), $this->getNavParameter());
		}
		
		if(!$this->enabled['content'])
		{
			return $this->render();
		}

		$this->determineOffsetAndOrder();
		
		if (!$this->getExternalSegmentation())
		{
			$this->setMaxCount(count($this->row_data));
		}
		
		$this->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		
		// sort
		$data = $this->getData();
		if (!$this->getExternalSorting())
		{
			$data = ilUtil::sortArray($data, $this->getOrderField(),
				$this->getOrderDirection(), $this->numericOrdering($this->getOrderField()));
		}

		// slice
		if (!$this->getExternalSegmentation())
		{
			$data = array_slice($data, $this->getOffset(), $this->getLimit());
		}
		
		// fill rows
		if(count($data) > 0)
		{
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
		
		// set form action		
		if ($this->form_action != "")
		{
			$hash = "";
			if (is_object($ilUser) && $ilUser->prefs["screen_reader_optimization"])
			{
				$hash = "#".$this->getTopAnchor();
			}

			$this->tpl->setCurrentBlock("tbl_form_header");
			$this->tpl->setVariable("FORMACTION", $this->getFormAction().$hash);
			$this->tpl->setVariable("FORMNAME", $this->getFormName());
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("tbl_form_footer");
		}
		
		$this->fillFooter();
				
		$this->fillHiddenRow();
				
		$this->fillActionRow();

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
		global $lng;
		
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
		
		$this->renderFilter();
		
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
				$this->tpl->setVariable("SRC_IMG_LINK",ilUtil::getImagePath("icon_close2.gif"));
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
		global $lng;
		
		$filter = $this->getFilterItems();
		$opt_filter = $this->getFilterItems(true);

		if (count($filter) == 0 && count($opt_filter) == 0)
		{
			return;
		}

		// render standard filter (always shown)
		if (count($filter) > 0)
		{
			$ccnt = 0;
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
			if ($ccnt < $this->getFilterCols())
			{
				for($i = $ccnt; $i<=$this->getFilterCols(); $i++)
				{
					$this->tpl->touchBlock("filter_empty_cell");
				}
			}
			$this->tpl->setCurrentBlock("filter_row");
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setVariable("TXT_FILTER", $lng->txt("filter"));
			$this->tpl->setVariable("CMD_APPLY", $this->filter_cmd);
			$this->tpl->setVariable("TXT_APPLY", $lng->txt("apply_filter"));

			$this->tpl->setCurrentBlock("filter_section");
			$this->tpl->parseCurrentBlock();
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
		global $lng, $ilCtrl;

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
			if (!$this->getDisplayAsBlock())
			{
				$linkbar = $this->getLinkbar("1");
				$this->tpl->setCurrentBlock("tbl_footer_linkbar");
				$this->tpl->setVariable("LINKBAR", $linkbar);
				$this->tpl->parseCurrentBlock();
			}
			$linkbar = true;
			$footer = true;
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
			if ($numinfo != "" || $linkbar != "")
			{
				if ($numinfo != "" && $this->getEnableNumInfo())
				{
					$this->tpl->setCurrentBlock("top_numinfo");
					$this->tpl->setVariable("NUMINFO", $numinfo);
					$this->tpl->parseCurrentBlock();
				}
				if ($linkbar != "")
				{
					$linkbar = $this->getLinkbar("2");
					$this->tpl->setCurrentBlock("top_linkbar");
					$this->tpl->setVariable("LINKBAR", $linkbar);
					$this->tpl->parseCurrentBlock();
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
		if (is_object($ilUser) && $ilUser->prefs["screen_reader_optimization"])
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
			// previous link
			if ($this->custom_prev_next && $this->custom_prev != "")
			{
				$LinkBar .= "<a href=\"".$this->custom_prev.$hash."\">".$layout_prev."&nbsp;</a>";
			}
			else if ($this->getOffset() >= 1 && !$this->custom_prev_next)
			{
				$prevoffset = $this->getOffset() - $this->getLimit();
				$LinkBar .= "<a href=\"".$link.$prevoffset.$hash."\">".$layout_prev."&nbsp;</a>";
			}
			else
			{
				$LinkBar .= '<span class="ilTableFootLight">'.$layout_prev."&nbsp;</span>";
			}
			
			// current value
			if ($a_num == "1")
			{
				$LinkBar .= '<input type="hidden" name="'.$this->getNavParameter().
					'" value="'.$this->getOrderField().":".$this->getOrderDirection().":".$this->getOffset().'" />';
			}

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
				if ($newoffset == $this->getOffset())
				{
				//	$LinkBar .= "[".$i."] ";
				}
				else
				{
				//	$LinkBar .= '<a '.$layout_link.' href="'.
				//		$link.$newoffset.'">['.$i.']</a> ';
				}
			}
			
			// show next link (if not last page)
			if ($this->custom_prev_next && $this->custom_next != "")
			{
				if ($LinkBar != "")
					$LinkBar .= "<span> | </span>";
				$LinkBar .= "<a href=\"".$this->custom_next.$hash."\">&nbsp;".$layout_next."</a>";
			}
			else if (! ( ($this->getOffset() / $this->getLimit())==($pages-1) ) && ($pages!=1) &&
				!$this->custom_prev_next)
			{
				if ($LinkBar != "")
					$LinkBar .= "<span> | </span>"; 
				$newoffset = $this->getOffset() + $this->getLimit();
				$LinkBar .= "<a href=\"".$link.$newoffset.$hash."\">&nbsp;".$layout_next."</a>";
			}
			else
			{
				if ($LinkBar != "")
					$LinkBar .= "<span > | </span>"; 
				$LinkBar .= '<span class="ilTableFootLight">&nbsp;'.$layout_next."</span>";
			}
			
			if (count($offset_arr) && !$this->getDisplayAsBlock() && !$this->custom_prev_next)
			{				
				$LinkBar .= "&nbsp;&nbsp;&nbsp;&nbsp;".
					'<label for="tab_page_sel_'.$a_num.'">'.$lng->txt("select_page").'</label> '.
					ilUtil::formSelect($this->nav_value,
					$this->getNavParameter().$a_num, $offset_arr, false, true, 0, "ilEditSelect",
					array("id" => "tab_page_sel_".$a_num)).
					' <input class="ilEditSubmit" type="submit" name="cmd['.$this->parent_cmd.']" value="'.
					$lng->txt("ok").'" /> ';
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
				if (strlen($button['onclick']))
				{
					$this->tpl->setCurrentBlock('cmdonclick');
					$this->tpl->setVariable('CMD_ONCLICK', $button['onclick']);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("plain_button");
				$this->tpl->setVariable("PBTN_NAME", $button["cmd"]);
				$this->tpl->setVariable("PBTN_VALUE", $button["text"]);
				$this->tpl->parseCurrentBlock();
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
			}
			$arrow = true;
			$action_row = true;
		}
		
		
		if (count($this->multi) > 1 && $this->dataExists())
		{
			$this->tpl->setCurrentBlock("tbl_cmd_select");
			$sel = array();
			foreach ($this->multi as $mc)
			{
				$sel[$mc["cmd"]] = $mc["text"];
			}
			$this->tpl->setVariable("SELECT_CMDS",
				ilUtil::formSelect("", "selected_cmd", $sel, false, true));
			$this->tpl->setVariable("TXT_EXECUTE", $lng->txt("execute"));
			$this->tpl->setVariable('SELECT_CMD','select_cmd');
			$this->tpl->parseCurrentBlock();
			$arrow = true;
			$action_row = true;
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
		}

		if ($arrow)
		{
			$this->tpl->setCurrentBlock("tbl_action_img_arrow");
			$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->setVariable("ALT_ARROW", $lng->txt("action"));
			$this->tpl->parseCurrentBlock();
		}
		
		if ($action_row)
		{
			$this->tpl->setCurrentBlock("tbl_action_row");
			$this->tpl->setVariable("COLUMN_COUNTS", $this->getColumnCount());
			$this->tpl->parseCurrentBlock();
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
}
?>
