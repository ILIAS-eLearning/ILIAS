<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
* This class represents a tag list property in a property form.
*
* @author Guido Vollbach <gvollbach@databay.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilTagInputGUI extends ilSubEnabledFormPropertyGUI
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
	
	
	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{		
		$this->setValue($a_values[$this->getPostVar()]);
		foreach($this->getSubItems() as $item)
		{
			$item->setValueByArray($a_values);
		}
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
		if(!$this->getMulti())
		{
			$_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
			if($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
			{
				$valid = false;
			}
		}
		else
		{
			foreach($_POST[$this->getPostVar()] as $idx => $value)
			{
				$_POST[$this->getPostVar()][$idx] = ilUtil::stripSlashes($value);
			}		
			$_POST[$this->getPostVar()] = array_unique($_POST[$this->getPostVar()]);

			if($this->getRequired() && !trim(implode("", $_POST[$this->getPostVar()])))
			{
				$valid = false;
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
	function render($a_mode = "", $js_load = false)
	{
		$tpl = new ilTemplate("tpl.prop_tag.html", true, true, "Services/Form");
		$tpl->setVariable('JAVASCRIPT_TAGS','./Services/Form/js/bootstrap-tagsinput_2015_25_03.js');
		$tpl->setVariable('JAVASCRIPT_TYPEAHEAD','./Services/Form/js/typeahead_0.11.1.js');
		$tpl->setVariable('CSS','./Services/Form/css/bootstrap-tagsinput_2015_25_03.css');

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
			$tpl->setVariable("VAL_SELECT_OPTION", ilUtil::prepareFormOutput($option_text));
			if((string) $sel_value == (string) $option_text)
			{
				$tpl->setVariable("CHK_SEL_OPTION",
					'selected="selected"');
			}
			$tpl->setVariable("TXT_SELECT_OPTION", $option_text);
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("ID", 'taggable');//$this->getFieldId());
		
		$postvar = $this->getPostVar();		
		if($this->getMulti() && substr($postvar, -2) != "[]")
		{
			$postvar .= "[]";
		}
					
			$tpl->setVariable("POST_VAR", $postvar);
		
		// multi icons
		if($this->getMulti() && !$a_mode && !$this->getDisabled())
		{
			$tpl->touchBlock("inline_in_bl");
			$tpl->setVariable("MULTI_ICONS", $this->getMultiIconsHTML());			
		}
		if(!$js_load)
		{
			$tpl->setCurrentBlock("initilize_on_page_load");
			$tpl->parseCurrentBlock();
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
	

}
