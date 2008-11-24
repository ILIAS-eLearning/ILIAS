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
* This class represents a password property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilPasswordInputGUI extends ilSubEnabledFormPropertyGUI
{
	protected $value;
	protected $size = 20;
	protected $max_length = 40;
	protected $validateauthpost = "";
	protected $requiredonauth = false;
	
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
	* Set Retype Value.
	*
	* @param	string	$a_retypevalue	Retype Value
	*/
	function setRetypeValue($a_retypevalue)
	{
		$this->retypevalue = $a_retypevalue;
	}

	/**
	* Get Retype Value.
	*
	* @return	string	Retype Value
	*/
	function getRetypeValue()
	{
		return $this->retypevalue;
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
	* Set Validate required status against authentication POST var.
	*
	* @param	string	$a_validateauthpost		POST var
	*/
	function setValidateAuthPost($a_validateauthpost)
	{
		$this->validateauthpost = $a_validateauthpost;
	}

	/**
	* Get Validate required status against authentication POST var.
	*
	* @return	string		POST var
	*/
	function getValidateAuthPost()
	{
		return $this->validateauthpost;
	}

	/**
	* Set input required, if authentication mode allows password setting.
	*
	* @param	boolean	$a_requiredonauth		require input
	*/
	function setRequiredOnAuth($a_requiredonauth)
	{
		$this->requiredonauth = $a_requiredonauth;
	}

	/**
	* Get input required, if authentication mode allows password setting.
	*
	* @return	boolean		require input
	*/
	function getRequiredOnAuth()
	{
		return $this->requiredonauth;
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
		$_POST[$this->getPostVar()."_retype"] = ilUtil::stripSlashes($_POST[$this->getPostVar()."_retype"]);
		if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		if ($this->getValidateAuthPost() != "")
		{
			$auth = ilAuthUtils::_getAuthMode($_POST[$this->getValidateAuthPost()]);

			// check, if password is required dependent on auth mode
			if ($this->getRequiredOnAuth() && ilAuthUtils::_allowPasswordModificationByAuthMode($auth)
				&& trim($_POST[$this->getPostVar()]) == "")
			{
				$this->setAlert($lng->txt("form_password_required_for_auth"));
	
				return false;
			}
			
			// check, if password is allowed to be set for given auth mode
			if (trim($_POST[$this->getPostVar()]) != "" &&
				!ilAuthUtils::_allowPasswordModificationByAuthMode($auth))
			{
				$this->setAlert($lng->txt("form_password_not_allowed_for_auth"));
	
				return false;
			}
		}
		if ($_POST[$this->getPostVar()] != $_POST[$this->getPostVar()."_retype"])
		{
			$this->setAlert($lng->txt("passwd_not_match"));

			return false;
		}
		if (!ilUtil::isPassword($_POST[$this->getPostVar()],$custom_error) && $_POST[$this->getPostVar()] != "")
		{
			if($custom_error != '') $this->setAlert($custom_error);
			else $this->setAlert($lng->txt("passwd_invalid"));

			return false;
		}
		
		return $this->checkSubItemsInput();
	}

	/**
	* Insert property html
	*/
	function insert(&$a_tpl)
	{
		global $lng;
		
		if (strlen($this->getValue()))
		{
			$a_tpl->setCurrentBlock("prop_password_propval");
			$a_tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
			$a_tpl->parseCurrentBlock();
		}
		$a_tpl->setCurrentBlock("prop_password");
		$a_tpl->setVariable("TXT_RETYPE", $lng->txt("form_retype_password"));
		$a_tpl->setVariable("POST_VAR", $this->getPostVar());
		$a_tpl->setVariable("ID", $this->getFieldId());
		$retype_value = ($this->getRetypeValue() != "")
			? $this->getRetypeValue()
			: $this->getValue();
		$a_tpl->setVariable("PROPERTY_RETYPE_VALUE", ilUtil::prepareFormOutput($retype_value));
		$a_tpl->setVariable("SIZE", $this->getSize());
		$a_tpl->setVariable("MAXLENGTH", $this->getMaxLength());
		if ($this->getDisabled())
		{
			$a_tpl->setVariable("DISABLED",
				" disabled=\"disabled\"");
		}
		$a_tpl->parseCurrentBlock();
	}
}
