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
class ilNumberRangeInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem
{
	protected $value;
	protected $maxlength = 200;
	protected $size = 40;
	protected $suffix;
	protected $label;
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
	* Set suffix.
	*
	* @param	array	$a_value	suffix
	*/
	function setSuffix($a_value)
	{
		if(!is_array($a_value) || implode("", array_keys($a_value)) !== "01")
	    {
			$a_value = NULL;
		}
		$this->suffix = $a_value;
	}

	/**
	* Get suffix.
	*
	* @return	string	suffix
	*/
	function getSuffix()
	{
		return $this->suffix;
	}

	/**
	* Set Value.
	*
	* @param	array	$a_value	Value
	*/
	function setValue($a_value)
	{
		if(is_array($a_value) && implode("", array_keys($a_value)) === "01")
		{
			foreach($a_value as $idx => $value)
			{
				$a_value[$idx] = str_replace(',', '.', $value);
			}
		}
		else
	    {
			$a_value = NULL;
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
	* Set Label.
	*
	* @param	array	$a_value	Label
	*/
	function setLabel($a_value)
	{
		if(!is_array($a_value) || implode("", array_keys($a_value)) !== "01")
	    {
			$a_value = NULL;
		}
		$this->label = $a_value;
	}

	/**
	* Get Label.
	*
	* @return	string	Label
	*/
	function getLabel()
	{
		return $this->label;
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

		$min = $this->getMinValue();
		$max = $this->getMaxValue();
		for($loop = 0; $loop < 2; $loop++)
		{
			$_POST[$this->getPostVar()][$loop] = ilUtil::stripSlashes($_POST[$this->getPostVar()][$loop]);
			$value = trim($_POST[$this->getPostVar()][$loop]);

			if ($this->getRequired() && $value == "")
			{
				$this->setAlert($lng->txt("msg_input_is_required"));

				return false;
			}
			else if ($value !== "")
			{
				if (!is_numeric(str_replace(',', '.', $value)))
				{
					$this->setAlert($lng->txt("form_msg_numeric_value_required"));

					return false;
				}

				if (is_array($min) && $min[$loop] !== NULL &&
					$value < $min[$loop])
				{
					$this->setAlert($lng->txt("form_msg_value_too_low"));

					return false;
				}

				if (is_array($max) && $max[$loop] !== NULL &&
					$value > $max[$loop])
				{
					$this->setAlert($lng->txt("form_msg_value_too_high"));

					return false;
				}
			}
		}

		// comparison
		$value1 = $_POST[$this->getPostVar()][0];
		$value2 = $_POST[$this->getPostVar()][1];
		if($value1 && $value2 && $value1 > $value)
		{
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
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}

	/**
	* Render item
	*/
	function render()
	{
		global $lng;

		$tpl = new ilTemplate("tpl.prop_number_range.html", true, true, "Services/Form");

		$value = $this->getValue();
		if (is_array($value))
		{
			if ($value[0])
			{
				$tpl->setCurrentBlock("prop_number_range_propval1");
				$tpl->setVariable("PROPERTY_VALUE1", ilUtil::prepareFormOutput($value[0]));
				$tpl->parseCurrentBlock();
			}
			if ($value[1])
			{
				$tpl->setCurrentBlock("prop_number_range_propval2");
				$tpl->setVariable("PROPERTY_VALUE2", ilUtil::prepareFormOutput($value[1]));
				$tpl->parseCurrentBlock();
			}
		}

		$label = $this->getLabel();
		if (is_array($label))
		{
			if ($label[0])
			{
				$tpl->setCurrentBlock("prop_number_range_label1");
				$tpl->setVariable("LABEL1", $label[0]);
				$tpl->setVariable("ID", $this->getFieldId());
				$tpl->parseCurrentBlock();
			}
			if ($label[1])
			{
				$tpl->setCurrentBlock("prop_number_range_label2");
				$tpl->setVariable("LABEL2", $label[1]);
				$tpl->setVariable("ID", $this->getFieldId());
				$tpl->parseCurrentBlock();
			}
		}

		$tpl->setCurrentBlock("prop_number_range");

		$tpl->setVariable("POST_VAR", $this->getPostVar());
		$tpl->setVariable("ID", $this->getFieldId());
		$tpl->setVariable("SIZE", $this->getSize());
		$tpl->setVariable("MAXLENGTH", $this->getMaxLength());
		if ($this->getDisabled())
		{
			$tpl->setVariable("DISABLED",
				" disabled=\"disabled\"");
		}

        $suffix = $this->getSuffix();
		if (is_array($suffix))
		{
			if ($suffix[0])
			{
				$tpl->setVariable("INPUT_SUFFIX1", $suffix[0]);
			}
			if ($suffix[1])
			{
				$tpl->setVariable("INPUT_SUFFIX2", $suffix[1]);
			}
		}

		// constraints
		$min = $this->getMinValue();
		$max = $this->getMaxValue();
		for($loop = 0; $loop < 2; $loop++)
		{
			$constraints = "";
			if ($this->getDecimals() > 0)
			{
				$constraints = $lng->txt("form_format").": ###.".str_repeat("#", $this->getDecimals());
				$delim = ", ";
			}
			if (is_array($min) && $min[$loop] !== NULL)
			{
				$constraints.= $delim.$lng->txt("form_min_value").": ".$min[$loop];
				$delim = ", ";
			}
			if (is_array($max) && $max[$loop] !== NULL)
			{
				$constraints.= $delim.$lng->txt("form_max_value").": ".$max[$loop];
				$delim = ", ";
			}
			if ($constraints != "")
			{
				$tpl->setVariable("TXT_NUMBER_CONSTRAINTS".($loop+1), $constraints);
			}
		}
		
		$tpl->parseCurrentBlock();

		return $tpl->get();
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
