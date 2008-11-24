<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* This class represents a number property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilNumberInputGUI extends ilSubEnabledFormPropertyGUI
{
	protected $value;
	protected $maxlength = 200;
	protected $size = 40;
	protected $minvalue = false;
	protected $maxvalue = false;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
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
	* Set Max Length.
	*
	* @param	int	$a_maxlength	Max Length
	*/
	function setMaxLength($a_maxlength)
	{
		$this->maxlength = $a_maxlength;
	}

	/**
	* Get Max Length.
	*
	* @return	int	Max Length
	*/
	function getMaxLength()
	{
		return $this->maxlength;
	}

	/**
	* Set Size.
	*
	* @param	int	$a_size	Size
	*/
	function setSize($a_size)
	{
		$this->size = $a_size;
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
	* Get Size.
	*
	* @return	int	Size
	*/
	function getSize()
	{
		return $this->size;
	}
	
	/**
	* Set Minimum Value.
	*
	* @param	float	$a_minvalue	Minimum Value
	*/
	function setMinValue($a_minvalue)
	{
		$this->minvalue = $a_minvalue;
	}

	/**
	* Get Minimum Value.
	*
	* @return	float	Minimum Value
	*/
	function getMinValue()
	{
		return $this->minvalue;
	}

	/**
	* Set Maximum Value.
	*
	* @param	float	$a_maxvalue	Maximum Value
	*/
	function setMaxValue($a_maxvalue)
	{
		$this->maxvalue = $a_maxvalue;
	}

	/**
	* Get Maximum Value.
	*
	* @return	float	Maximum Value
	*/
	function getMaxValue()
	{
		return $this->maxvalue;
	}

	/**
	* Set Decimal Places.
	*
	* @param	int	$a_decimals	Decimal Places
	*/
	function setDecimals($a_decimals)
	{
		$this->decimals = $a_decimals;
	}

	/**
	* Get Decimal Places.
	*
	* @return	int	Decimal Places
	*/
	function getDecimals()
	{
		return $this->decimals;
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		
		if (trim($_POST[$this->getPostVar()]) != "" &&
			! is_numeric($_POST[$this->getPostVar()]))
		{
			$this->setAlert($lng->txt("form_msg_numeric_value_required"));

			return false;
		}

		if (trim($_POST[$this->getPostVar()]) != "" &&
			$this->getMinValue() !== false &&
			$_POST[$this->getPostVar()] < $this->getMinValue())
		{
			$this->setAlert($lng->txt("form_msg_value_too_low"));

			return false;
		}

		if (trim($_POST[$this->getPostVar()]) != "" &&
			$this->getMaxValue() !== false &&
			$_POST[$this->getPostVar()] > $this->getMaxValue())
		{
			$this->setAlert($lng->txt("form_msg_value_too_high"));

			return false;
		}
		
		return $this->checkSubItemsInput();
	}

	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		global $lng;
		
		if (strlen($this->getValue()))
		{
			$a_tpl->setCurrentBlock("prop_number_propval");
			$a_tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
			$a_tpl->parseCurrentBlock();
		}
		$a_tpl->setCurrentBlock("prop_number");
		
		$a_tpl->setVariable("POST_VAR", $this->getPostVar());
		$a_tpl->setVariable("ID", $this->getFieldId());
		$a_tpl->setVariable("SIZE", $this->getSize());
		$a_tpl->setVariable("MAXLENGTH", $this->getMaxLength());
		if ($this->getDisabled())
		{
			$a_tpl->setVariable("DISABLED",
				" disabled=\"disabled\"");
		}
		
		// constraints
		if ($this->getDecimals() > 0)
		{
			$constraints = $lng->txt("form_format").": ###.".str_repeat("#", $this->getDecimals());
			$delim = ", ";
		}
		if ($this->getMinValue() !== false)
		{
			$constraints.= $delim.$lng->txt("form_min_value").": ".$this->getMinValue();
			$delim = ", ";
		}
		if ($this->getMaxValue() !== false)
		{
			$constraints.= $delim.$lng->txt("form_max_value").": ".$this->getMaxValue();
			$delim = ", ";
		}
		if ($constraints != "")
		{
			$a_tpl->setVariable("TXT_NUMBER_CONSTRAINTS", $constraints);
		}
		
		$a_tpl->parseCurrentBlock();
	}
}
