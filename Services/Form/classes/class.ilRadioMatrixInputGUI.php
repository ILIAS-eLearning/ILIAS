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
* This class represents a selection list property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilRadioMatrixInputGUI extends ilFormPropertyGUI
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
		$this->setType("radiomatrix");
	}

	/**
	* Set Options.
	*
	* @param	array	$a_options	Options. Array ("value" => "option_html")
	*/
	function setOptions($a_options)
	{
		$this->options = $a_options;
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
		return $this->checkSubItemsInput();
	}

	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		$tpl = new ilTemplate("tpl.prop_radiomatrix.html", true, true, "Services/Form");
		
		
		foreach($this->getOptions() as $option_value => $option_html)
		{
			$tpl->touchBlock("row_start");
			$tpl->touchBlock("item");
			
			$tpl->setCurrentBlock("option_start");
			$tpl->setVariable("VAL_RADIO_OPTION", $option_value);
			$tpl->setVariable("POST_VAR", $this->getPostVar());
			if ($option_value == $this->getValue())
			{
				$tpl->setVariable("CHK_RADIO_OPTION",
					'checked="checked"');
			}
			if ($this->getDisabled())
			{
				$tpl->setVariable("DISABLED",
					" disabled=\"disabled\"");
			}
			$tpl->setVariable("TXT_RADIO_OPTION", $option_html);
			$tpl->parseCurrentBlock();
			$tpl->touchBlock("item");
			
			$tpl->touchBlock("row_end");
			$tpl->touchBlock("item");
		}
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
	}

}
