<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilFilterGUI
 *
 * @author: Nadia Ahmad <nahmad@databay.de>
 * @version $Id:
 */
class ilFilterGUI
{
	protected $filters = array();
	protected $optional_filters = array();
	protected $filter_cmd = 'setFilter';
	protected $reset_cmd = 'resetFilter';
	protected $filter_cols = 5;

	protected $disable_filter_hiding = false;
	protected $selected_filter = false;

	protected $filters_determined = false;
	
	protected $filter_id = '';
	

	const FILTER_TEXT = 1;
	const FILTER_SELECT = 2;
	const FILTER_DATE = 3;
	const FILTER_LANGUAGE = 4;
	const FILTER_NUMBER_RANGE = 5;
	const FILTER_DATE_RANGE = 6;
	const FILTER_DURATION_RANGE = 7;
	const FILTER_DATETIME_RANGE = 8;

	
	public function __construct($a_parent_obj, $a_parent_cmd = "")
	{
		global $ilCtrl;
		
		$this->parent_obj = $a_parent_obj;
		$this->parent_cmd = $a_parent_cmd;
		$this->tpl = new ilTemplate("tpl.filter.html", true, true,	"Services/Payment");

		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj, $a_parent_cmd));
	}
	
	/**
	 * @return string
	 */
	public function getFilterCmd()
	{
		return $this->filter_cmd;
	}

	/**
	 * @param string $filter_cmd
	 */
	public function setFilterCmd($filter_cmd)
	{
		$this->filter_cmd = $filter_cmd;
	}

	/**
	 * @return int
	 */
	public function getFilterCols()
	{
		return $this->filter_cols;
	}

	/**
	 * @param int $filter_cols
	 */
	public function setFilterCols($filter_cols)
	{
		$this->filter_cols = $filter_cols;
	}

	/**
	 * @return array
	 */
	public function getFilters()
	{
		return $this->filters;
	}

	/**
	 * @param array $filters
	 */
	public function setFilters($filters)
	{
		$this->filters = $filters;
	}

	/**
	 * @return array
	 */
	public function getOptionalFilters()
	{
		return $this->optional_filters;
	}

	/**
	 * @param array $optional_filters
	 */
	public function setOptionalFilters($optional_filters)
	{
		$this->optional_filters = $optional_filters;
	}

	/**
	 * @return string
	 */
	public function getResetCmd()
	{
		return $this->reset_cmd;
	}

	/**
	 * @param string $reset_cmd
	 */
	public function setResetCmd($reset_cmd)
	{
		$this->reset_cmd = $reset_cmd;
	}

	/**
	 * @return boolean
	 */
	public function isDisableFilterHiding()
	{
		return $this->disable_filter_hiding;
	}

	/**
	 * @param boolean $disable_filter_hiding
	 */
	public function setDisableFilterHiding($disable_filter_hiding)
	{
		$this->disable_filter_hiding = $disable_filter_hiding;
	}

	/**
	 * @return boolean
	 */
	public function isFiltersDetermined()
	{
		return $this->filters_determined;
	}

	/**
	 * @param boolean $filters_determined
	 */
	public function setFiltersDetermined($filters_determined)
	{
		$this->filters_determined = $filters_determined;
	}

	/**
	 * @return boolean
	 */
	public function isSelectedFilter()
	{
		return $this->selected_filter;
	}

	/**
	 * @param boolean $selected_filter
	 */
	public function setSelectedFilter($selected_filter)
	{
		$this->selected_filter = $selected_filter;
	}

	/**
	 * Is given filter selected?
	 *
	 * @param	string	$a_col column name
	 * @return	boolean
	 */
	function isFilterSelected($a_col)
	{
		return $this->selected_filter[$a_col];
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

	
	
	public function getId()
	{
		return 'shop';
	}
	/**
	 * Add filter item. Filter items are property form inputs that implement
	 * the ilTableFilterItem interface
	 * @param      $a_input_item
	 * @param bool $a_optional
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
		if($this->restore_filter_values &&
			array_key_exists($a_input_item->getFieldId(), $this->restore_filter_values))
		{
			$this->setFilterValue($a_input_item, $this->restore_filter_values[$a_input_item->getFieldId()]);
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
	 * @param bool $a_optionals
	 * @return array
	 */
	final function getFilterItems($a_optionals = false)
	{
		if (!$a_optionals)
		{
			return $this->filters;
		}
		return $this->optional_filters;
	}

	/**
	 * @param $a_post_var
	 * @return bool
	 */
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
	 * Load table property
	 *
	 * @param	string	$type
	 * @return	mixed
	 */
	function loadProperty($type)
	{
		global $ilUser;

		if(is_object($ilUser) && $this->getId() != "" && $ilUser->getId() != ANONYMOUS_USER_ID)
		{
			include_once("./Services/Table/classes/class.ilTablePropertiesStorage.php");
			$tab_prop = new ilTablePropertiesStorage();

			return $tab_prop->getProperty($this->getId(), $ilUser->getId(), $type);
		}
	}


	/**
	 * Set Form action parameter.
	 *
	 * @param	string	$a_form_action	Form action
	 * @param	bool	$a_multipart	Form multipart status
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
	 * Init filter. Overwrite this to initialize all filter input property
	 * objects.
	 */
	function initFilter()
	{
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
	
	public function getFilterId()
	{
		return $this->filter_id;
	}
	public function setFilterId($filter_id) 
	{
		$this->filter_id = $filter_id;
	}
	
	public function getHtml()
	{
		global $lng,$ilUser;
		
		$this->tpl->setCurrentBlock("tbl_form_header");
		$this->tpl->setVariable("FORMACTION", $this->getFormAction());
		$this->tpl->setVariable("FORMNAME", $this->getFilterId());
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("filter_activation");
		$this->tpl->setVariable("TXT_ACTIVATE_FILTER", $lng->txt("show_filter"));
		$this->tpl->setVariable("FILA_ID", $this->getId());
		
		if ($this->getId() != "")
		{
			$this->tpl->setVariable("SAVE_URLA", "./ilias.php?baseClass=ilTablePropertiesStorage&table_id=".
				$this->getId()."&cmd=showFilter&user_id=".$ilUser->getId());
			$this->tpl->parseCurrentBlock();
		}
		
		if (!$this->getDisableFilterHiding())
		{
			$this->tpl->setCurrentBlock("filter_deactivation");

			if ($this->getId() != "")
			{
				$this->tpl->setVariable("TXT_HIDE", $lng->txt("hide_filter"));

				$this->tpl->setVariable("SAVE_URL", "./ilias.php?baseClass=ilTablePropertiesStorage&table_id=".
					$this->getId()."&cmd=hideFilter&user_id=".$ilUser->getId());
				$this->tpl->setVariable("FILD_ID", $this->getId());
			}
			$this->tpl->parseCurrentBlock();

		}

		$this->renderFilter();
		$this->tpl->touchBlock("tbl_form_footer");
		return $this->tpl->get();
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
//				$item->setValueByArray($_POST);
				$item->clearFromSession();
			}
		}
		foreach ($opt_filter as $item)
		{
			if ($item->checkInput())
			{
//				$item->setValueByArray($_POST);
				$item->clearFromSession();
			}
		}

		// #13209
		unset($_REQUEST["tbltplcrt"]);
		unset($_REQUEST["tbltpldel"]);
	}

}