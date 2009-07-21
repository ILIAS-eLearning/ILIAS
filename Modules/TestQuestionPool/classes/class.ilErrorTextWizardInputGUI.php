<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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
* This class represents a key value pair wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilErrorTextWizardInputGUI extends ilTextInputGUI
{
	protected $values = array();
	protected $key_size = 20;
	protected $value_size = 20;
	protected $key_maxlength = 255;
	protected $value_maxlength = 255;
	protected $key_name = "";
	protected $value_name = "";
	
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
		$this->values = array();
		if (is_array($a_value))
		{
			include_once "./Modules/TestQuestionPool/classes/class.assAnswerErrorText.php";
			if (is_array($a_value['key']))
			{
				foreach ($a_value['key'] as $idx => $key)
				{
					array_push($this->values, new assAnswerErrorText($key, $a_value['value'][$idx], str_replace(",", ".", $a_value['points'][$idx])));
				}
			}
		}
	}

	/**
	* Set key size.
	*
	* @param	integer	$a_size	Key size
	*/
	function setKeySize($a_size)
	{
		$this->key_size = $a_size;
	}

	/**
	* Get key size.
	*
	* @return	integer	Key size
	*/
	function getKeySize()
	{
		return $this->key_size;
	}
	
	/**
	* Set value size.
	*
	* @param	integer	$a_size	value size
	*/
	function setValueSize($a_size)
	{
		$this->value_size = $a_size;
	}

	/**
	* Get value size.
	*
	* @return	integer	value size
	*/
	function getValueSize()
	{
		return $this->value_size;
	}
	
	/**
	* Set key maxlength.
	*
	* @param	integer	$a_size	Key maxlength
	*/
	function setKeyMaxlength($a_maxlength)
	{
		$this->key_maxlength = $a_maxlength;
	}

	/**
	* Get key maxlength.
	*
	* @return	integer	Key maxlength
	*/
	function getKeyMaxlength()
	{
		return $this->key_maxlength;
	}
	
	/**
	* Set value maxlength.
	*
	* @param	integer	$a_size	value maxlength
	*/
	function setValueMaxlength($a_maxlength)
	{
		$this->value_maxlength = $a_maxlength;
	}

	/**
	* Get value maxlength.
	*
	* @return	integer	value maxlength
	*/
	function getValueMaxlength()
	{
		return $this->value_maxlength;
	}
	
	/**
	* Set value name.
	*
	* @param	string	$a_name	value name
	*/
	function setValueName($a_name)
	{
		$this->value_name = $a_name;
	}

	/**
	* Get value name.
	*
	* @return	string	value name
	*/
	function getValueName()
	{
		return $this->value_name;
	}
	
	/**
	* Set key name.
	*
	* @param	string	$a_name	value name
	*/
	function setKeyName($a_name)
	{
		$this->key_name = $a_name;
	}

	/**
	* Get key name.
	*
	* @return	string	value name
	*/
	function getKeyName()
	{
		return $this->key_name;
	}
	
	/**
	* Set Values
	*
	* @param	array	$a_value	Value
	*/
	function setValues($a_values)
	{
		$this->values = $a_values;
	}

	/**
	* Get Values
	*
	* @return	array	Values
	*/
	function getValues()
	{
		return $this->values;
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$foundvalues = $_POST[$this->getPostVar()];
		if (is_array($foundvalues))
		{
			// check answers
			if (is_array($foundvalues['key']) && is_array($foundvalues['value']))
			{
				foreach ($foundvalues['key'] as $val)
				{
					if ($this->getRequired() && (strlen($val)) == 0) 
					{
						$this->setAlert($lng->txt("msg_input_is_required"));
						return FALSE;
					}
				}
				foreach ($foundvalues['value'] as $val)
				{
					if ($this->getRequired() && (strlen($val)) == 0) 
					{
						$this->setAlert($lng->txt("msg_input_is_required"));
						return FALSE;
					}
				}
				foreach ($foundvalues['points'] as $val)
				{
					if ($this->getRequired() && (strlen($val)) == 0) 
					{
						$this->setAlert($lng->txt("msg_input_is_required"));
						return FALSE;
					}
					if (!is_numeric(str_replace(",", ".", $val)))
					{
						$this->setAlert($lng->txt("form_msg_numeric_value_required"));
						return FALSE;
					}
				}
			}
			else
			{
				if ($this->getRequired())
				{
					$this->setAlert($lng->txt("msg_input_is_required"));
					return FALSE;
				}
			}
		}
		else
		{
			if ($this->getRequired())
			{
				$this->setAlert($lng->txt("msg_input_is_required"));
				return FALSE;
			}
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
		
		$tpl = new ilTemplate("tpl.prop_errortextwizardinput.html", true, true, "Modules/TestQuestionPool");
		$i = 0;
		foreach ($this->values as $value)
		{
			if (is_object($value))
			{
				if (strlen($value->text_wrong))
				{
					$tpl->setCurrentBlock("prop_key_propval");
					$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->text_wrong));
					$tpl->parseCurrentBlock();
				}
				if (strlen($value->text_correct))
				{
					$tpl->setCurrentBlock("prop_value_propval");
					$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->text_correct));
					$tpl->parseCurrentBlock();
				}
				if (strlen($value->points))
				{
					$tpl->setCurrentBlock("prop_points_propval");
					$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->points));
					$tpl->parseCurrentBlock();
				}
			}

			$tpl->setCurrentBlock("row");
			$class = ($i % 2 == 0) ? "even" : "odd";
			if ($i == 0) $class .= " first";
			if ($i == count($this->values)-1) $class .= " last";
			$tpl->setVariable("ROW_CLASS", $class);
			$tpl->setVariable("ROW_NUMBER", $i);
			
			$tpl->setVariable("KEY_SIZE", $this->getKeySize());
			$tpl->setVariable("KEY_ID", $this->getPostVar() . "[key][$i]");
			$tpl->setVariable("KEY_MAXLENGTH", $this->getKeyMaxlength());

			$tpl->setVariable("VALUE_SIZE", $this->getValueSize());
			$tpl->setVariable("VALUE_ID", $this->getPostVar() . "[value][$i]");
			$tpl->setVariable("VALUE_MAXLENGTH", $this->getValueMaxlength());

			$tpl->setVariable("POST_VAR", $this->getPostVar());

			$tpl->parseCurrentBlock();

			$i++;
		}
		$tpl->setVariable("ELEMENT_ID", $this->getPostVar());
		$tpl->setVariable("KEY_TEXT", $this->getKeyName());
		$tpl->setVariable("VALUE_TEXT", $this->getValueName());
		$tpl->setVariable("POINTS_TEXT", $lng->txt('points'));

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
	}
}
