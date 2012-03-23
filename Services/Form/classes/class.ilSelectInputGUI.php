<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/interfaces/interface.ilTableFilterItem.php");
include_once("./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
include_once 'Services/UIComponent/Toolbar/interfaces/interface.ilToolbarItem.php';

/**
* This class represents a selection list property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilSelectInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem, ilToolbarItem
{
	protected $cust_attr = array();
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
		$this->setType("select");
	}

	/**
	* Set Options.
	*
	* @param	array	$a_options	Options. Array ("value" => "option_text")
	*/
	function setOptions($a_options)
	{
		$this->options = $a_options;
	}

	/**
	* Get Options.
	*
	* @return	array	Options. Array ("value" => "option_text")
	*/
	function getOptions()
	{
		return $this->options ? $this->options : array();
	}

	/**
	* Set Value.
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		if($this->getMulti() && is_array($a_value))
		{						
			$this->setMultiValues($a_value);	
			$a_value = array_shift($a_value);		
		}	
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
	
	public function setMulti($a_multi)
	{
		$this->multi = (bool)$a_multi;
	}
	
	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{		
		$var = $this->getPostVar();	
		if($this->getMulti() && substr($var, -2) == "[]")
		{
			$var = substr($var, 0, -2);		
		}	
		$value = $a_values[$var];	
		$this->setValue($value);
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;

		$valid = true;
		if ($this->getRequired())
		{
			if(!$this->getMulti())
			{
				$_POST[$this->getPostVar()] =
					ilUtil::stripSlashes($_POST[$this->getPostVar()]);
				if(trim($_POST[$this->getPostVar()]) == "")
				{
					$valid = false;
				}
			}
			else
			{
				$var = str_replace("[]", "", $this->getPostVar());
				if(!sizeof($_POST[$var]))
				{
					$valid = false;
				}
			}
		}
		if (!$valid)
		{
			$this->setAlert($lng->txt("msg_input_is_required"));
			return false;
		}
		return $this->checkSubItemsInput();
	}
	
	public function addCustomAttribute($a_attr)
	{
		$this->cust_attr[] = $a_attr;
	}
	
	public function getCustomAttributes()
	{
		return (array) $this->cust_attr;
	}

	/**
	* Render item
	*/
	function render($a_mode = "")
	{
		$tpl = new ilTemplate("tpl.prop_select.html", true, true, "Services/Form");
		
		foreach($this->getCustomAttributes() as $attr)
		{
			$tpl->setCurrentBlock('cust_attr');
			$tpl->setVariable('CUSTOM_ATTR',$attr);
			$tpl->parseCurrentBlock();
		}
		
		// determin value to select. Due to accessibility reasons we
		// should always select a value (per default the first one)
		$first = true;
		foreach($this->getOptions() as $option_value => $option_text)
		{
			if ($first)
			{
				$sel_value = $option_value;
			}
			$first = false;
			if ((string) $option_value == (string) $this->getValue())
			{
				$sel_value = $option_value;
			}
		}
		foreach($this->getOptions() as $option_value => $option_text)
		{
			$tpl->setCurrentBlock("prop_select_option");
			$tpl->setVariable("VAL_SELECT_OPTION", $option_value);
			if((string) $sel_value == (string) $option_value)
			{
				$tpl->setVariable("CHK_SEL_OPTION",
					'selected="selected"');
			}
			$tpl->setVariable("TXT_SELECT_OPTION", $option_text);
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("ID", $this->getFieldId());
		if ($this->getDisabled())
		{
			$tpl->setVariable("DISABLED",
				" disabled=\"disabled\"");
		}
		if ($this->getDisabled())
		{
			$tpl->setVariable("DISABLED",
				" disabled=\"disabled\"");
			$tpl->setVariable("HIDDEN_INPUT",
				$this->getHiddenTag($this->getPostVar(), $this->getValue()));
		}
		else
		{
			$tpl->setVariable("POST_VAR", $this->getPostVar());
		}

		return $tpl->get();
	}
	
	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $this->render());
		$a_tpl->parseCurrentBlock();
	}

	/**
	* Get HTML for table filter
	*/
	function getTableFilterHTML()
	{
		$html = $this->render();
		return $html;
	}

	/**
	* Get HTML for toolbar
	*/
	function getToolbarHTML()
	{
		$html = $this->render("toolbar");
		return $html;
	}

}
