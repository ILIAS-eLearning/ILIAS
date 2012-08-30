<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents a non editable value in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilNonEditableValueGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem
{
	protected $type;
	protected $value;
	protected $title;
	protected $info;
	protected $section_icon;
	protected $disable_escaping;
	
	/**
	* Constructor
	*
	* @param
	*/
	function __construct($a_title = "", $a_id = "", $a_disable_escaping = false)
	{
		parent::__construct($a_title, $a_id);
		$this->setTitle($a_title);
		$this->setType("non_editable_value");
		$this->disable_escaping = (bool)$a_disable_escaping;
	}
	
	function checkInput()
	{
		$_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		return $this->checkSubItemsInput();
	}

	/**
	* Set Type.
	*
	* @param	string	$a_type	Type
	*/
	function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	* Get Type.
	*
	* @return	string	Type
	*/
	function getType()
	{
		return $this->type;
	}
	
	/**
	* Set Title.
	*
	* @param	string	$a_title	Title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get Title.
	*
	* @return	string	Title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* Set Information Text.
	*
	* @param	string	$a_info	Information Text
	*/
	function setInfo($a_info)
	{
		$this->info = $a_info;
	}

	/**
	* Get Information Text.
	*
	* @return	string	Information Text
	*/
	function getInfo()
	{
		return $this->info;
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
	* render
	*/
	function render()
	{
		$tpl = new ilTemplate("tpl.non_editable_value.html", true, true, "Services/Form");
		if ($this->getPostVar() != "")
		{
			$tpl->setCurrentBlock("hidden");
			$tpl->setVariable('NON_EDITABLE_ID',$this->getPostVar());
			$tpl->setVariable("HVALUE", ilUtil::prepareFormOutput($this->getValue()));
			$tpl->parseCurrentBlock();
		}
		$value = $this->getValue();
		if(!$this->disable_escaping)
		{
			$value = ilUtil::prepareFormOutput($value);
		}
		$tpl->setVariable("VALUE", $value);
		$tpl->setVariable("ID", $this->getFieldId());
		$tpl->parseCurrentBlock();
		
		return $tpl->get();
	}
	
	/**
	* Insert property html
	*
	*/
	function insert(&$a_tpl)
	{
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $this->render());
		$a_tpl->parseCurrentBlock();
	}
	
	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		if (isset($a_values[$this->getPostVar()]))
		{
			$this->setValue($a_values[$this->getPostVar()]);
		}
		foreach($this->getSubItems() as $item)
		{
			$item->setValueByArray($a_values);
		}
	}
	
	/**
	* Get HTML for table filter
	*/
	function getTableFilterHTML()
	{
		$html = $this->render();
		return $html;
	}
}
