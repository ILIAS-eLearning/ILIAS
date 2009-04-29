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

include_once("./Services/Table/interfaces/interface.ilTableFilterItem.php");
include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");

/**
* This class represents a multi selection list property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilMultiSelectInputGUI extends ilFormPropertyGUI implements ilTableFilterItem
{
	protected $options;
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
		$this->setType("multi_select");
		$this->setValue(array());
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
		return $this->options;
	}

	/**
	* Set Value.
	*
	* @param	array 		array with all activated selections
	*/
	function setValue($a_array)
	{
		$this->value = $a_array;
	}

	/**
	* Get Value.
	*
	* @return	array 		array with all activated selections
	*/
	function getValue()
	{
		return is_array($this->value) ? $this->value : array();
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
		
		if (is_array($_POST[$this->getPostVar()]))
		{
			foreach ($_POST[$this->getPostVar()] as $k => $v)
			{
				$_POST[$this->getPostVar()][$k] = 
					ilUtil::stripSlashes($v);
			}
		}
		else
		{
			$_POST[$this->getPostVar()] = array();
		}
		if ($this->getRequired() && count($_POST[$this->getPostVar()]) == 0)
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		return true;
	}

	/**
	* Render item
	*/
	function render()
	{
		$tpl = new ilTemplate("tpl.prop_multi_select.html", true, true, "Services/Form");
		$values = $this->getValue();

		foreach($this->getOptions() as $option_value => $option_text)
		{
			$tpl->setCurrentBlock("item");
			if ($this->getDisabled())
			{
				$tpl->setVariable("DISABLED",
					" disabled=\"disabled\"");
			}
			if (in_array($option_value, $values))
			{
				$tpl->setVariable("CHECKED",
					" checked=\"checked\"");
			}

			$tpl->setVariable("VAL", ilUtil::prepareFormOutput($option_value));
			$tpl->setVariable("ID_VAL", ilUtil::prepareFormOutput($option_value));
			$tpl->setVariable("IID", $this->getFieldId());
			$tpl->setVariable("TXT_OPTION", $option_text);
			$tpl->setVariable("POST_VAR", $this->getPostVar());
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("ID", $this->getFieldId());
		
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

}
