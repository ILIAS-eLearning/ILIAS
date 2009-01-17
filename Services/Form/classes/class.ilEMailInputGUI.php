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
* This class represents a email property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilEMailInputGUI extends ilFormPropertyGUI
{
	protected $value;
	protected $size = 30;
	protected $max_length = 80;
	
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
		
		$_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		if (!ilUtil::is_email($_POST[$this->getPostVar()]) &&
			trim($_POST[$this->getPostVar()]) != "")
		{
			$this->setAlert($lng->txt("email_not_valid"));

			return false;
		}
		
		
		return true;
	}

	/**
	* Insert property html
	*/
	function insert(&$a_tpl)
	{
		global $lng;
		
		$a_tpl->setCurrentBlock("prop_email");
		$a_tpl->setVariable("POST_VAR", $this->getPostVar());
		$a_tpl->setVariable("ID", $this->getFieldId());
		$a_tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
		$a_tpl->setVariable("SIZE", $this->size);
		$a_tpl->setVariable("MAXLENGTH", $this->maxlength);
		if ($this->getDisabled())
		{
			$a_tpl->setVariable("DISABLED",
				" disabled=\"disabled\"");
		}
		$a_tpl->parseCurrentBlock();
	}
}
