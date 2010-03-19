<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents an advanced selection list property in a property form.
* It can hold graphical selection items, uses javascript and falls back
* to a normal selection list, when javascript is disabled.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilAdvSelectInputGUI extends ilFormPropertyGUI
{
	protected $options = array();
	protected $value;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("advselect");
	}

	/**
	* Add an Options.
	*
	* @param	array	$a_options	Options. Array ("value" => "option_html")
	*/
	function addOption($a_value, $a_text, $a_html = "")
	{
		$this->options[$a_value] = array("value" => $a_value,
			"txt" => $a_text, "html" => $a_html);
	}

	/**
	* Get Options.
	*
	* @return	array	Options. Array ("value" => "option_html")
	*/
	function getOptions()
	{
		return $this->options;
	}

	/**
	* Set Value.
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		$this->value = $a_value;
	}

	/**
	* Get Value.
	*
	* @return	string	Value
	*/
	function getValue()
	{
		return $this->value;
	}
	
	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		$this->setValue($a_values[$this->getPostVar()]);
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$_POST[$this->getPostVar()] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		return true;
	}

	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$selection = new ilAdvancedSelectionListGUI();
		$selection->setFormSelectMode($this->getPostVar(), "", false,
			"", "", "",
			"", "", "", "");
		$selection->setId($this->getPostVar());
		$selection->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
		$selection->setSelectedValue($this->getValue());
		$selection->setUseImages(false);
		$selection->setOnClickMode(ilAdvancedSelectionListGUI::ON_ITEM_CLICK_FORM_SELECT);

		foreach($this->getOptions() as $option)
		{
			$selection->addItem($option["txt"], $option["value"], "",
				"", $option["value"], "", $option["html"]);
			if ($this->getValue() == $option["value"])
			{
				$selection->setListTitle($option["txt"]);
			}
		}
		
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $selection->getHTML());
		$a_tpl->parseCurrentBlock();
	}

}
