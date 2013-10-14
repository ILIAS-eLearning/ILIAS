<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	protected $preselection = false;
	protected $maxlength = false;

	/**
	 * @var bool Flag whether the html autocomplete attribute should be set to "off" or not
	 */
	protected $autocomplete_disabled = true;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setRetype(true);
		$this->setSkipSyntaxCheck(false);
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
	* Set retype on/off
	*
	* @param	boolean		retype
	*/
	function setRetype($a_val)
	{
		$this->retype = $a_val;
	}
	
	/**
	* Get retype on/off
	*
	* @return	boolean		retype
	*/
	function getRetype()
	{
		return $this->retype;
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
	* Set preselection
	*
	* @param	boolean		preselection of five passwords true/false
	*/
	function setPreSelection($a_val)
	{
		$this->preselection = $a_val;
	}
	
	/**
	* Get preselection
	*
	* @return	boolean		preselection of five passwords true/false
	*/
	function getPreSelection()
	{
		return $this->preselection;
	}
	
	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		$this->setValue($a_values[$this->getPostVar()]);
		$this->setRetypeValue($a_values[$this->getPostVar()."_retype"]);
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
	* Set skip syntax check
	*
	* @param	boolean		skip syntax check
	*/
	function setSkipSyntaxCheck($a_val)
	{
		$this->skip_syntax_check = $a_val;
	}
	
	/**
	* Get skip syntax check
	*
	* @return	boolean		skip syntax check
	*/
	function getSkipSyntaxCheck()
	{
		return $this->skip_syntax_check;
	}
	
	/**
	* Set autocomplete
	*
	* @param	bool	$a_value	Value
	*/
	function setDisableHtmlAutoComplete($a_value)
	{
		$this->autocomplete_disabled = (bool)$a_value;
	}

	/**
	* Get autocomplete
	*
	* @return	bool	Value
	*/
	function isHtmlAutoCompleteDisabled()
	{
		return $this->autocomplete_disabled;
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
		if ($this->getRetype() && !$this->getPreSelection() &&
			($_POST[$this->getPostVar()] != $_POST[$this->getPostVar()."_retype"]))
		{
			$this->setAlert($lng->txt("passwd_not_match"));

			return false;
		}
		if (!$this->getSkipSyntaxCheck() &&
			!ilUtil::isPassword($_POST[$this->getPostVar()],$custom_error) &&
			$_POST[$this->getPostVar()] != "")
		{
			if($custom_error != '') $this->setAlert($custom_error);
			else $this->setAlert($lng->txt("passwd_invalid"));

			return false;
		}
		
		return $this->checkSubItemsInput();
	}

	/**
	* Render item
	*/
	function render()
	{
		global $lng;
		
		$ptpl = new ilTemplate("tpl.prop_password.html", true, true, "Services/Form");
		
		if (!$this->getPreSelection())
		{
			if ($this->getRetype())
			{
				$ptpl->setCurrentBlock("retype");
				$ptpl->setVariable("RSIZE", $this->getSize());
				$ptpl->setVariable("RID", $this->getFieldId());
				$ptpl->setVariable("RMAXLENGTH", $this->getMaxLength());
				$ptpl->setVariable("RPOST_VAR", $this->getPostVar());
				
				if($this->isHtmlAutoCompleteDisabled())
				{
					$ptpl->setVariable("RAUTOCOMPLETE", "autocomplete=\"off\"");
				}
				
				// this is creating an "auto entry" in the setup, if the retype is missing
				/*$retype_value = ($this->getRetypeValue() != "")
					? $this->getRetypeValue()
					: $this->getValue();*/
				$retype_value = $this->getRetypeValue();
				$ptpl->setVariable("PROPERTY_RETYPE_VALUE", ilUtil::prepareFormOutput($retype_value));
				if ($this->getDisabled())
				{
					$ptpl->setVariable("RDISABLED",
						" disabled=\"disabled\"");
				}
				$ptpl->setVariable("TXT_RETYPE", $lng->txt("form_retype_password"));
				$ptpl->parseCurrentBlock();
			}
			
			if (strlen($this->getValue()))
			{
				$ptpl->setCurrentBlock("prop_password_propval");
				$ptpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
				$ptpl->parseCurrentBlock();
			}
			$ptpl->setVariable("POST_VAR", $this->getPostVar());
			$ptpl->setVariable("ID", $this->getFieldId());
			$ptpl->setVariable("SIZE", $this->getSize());
			$ptpl->setVariable("MAXLENGTH", $this->getMaxLength());
			if ($this->getDisabled())
			{
				$ptpl->setVariable("DISABLED",
					" disabled=\"disabled\"");
			}
			if($this->isHtmlAutoCompleteDisabled())
			{
				$ptpl->setVariable("AUTOCOMPLETE", "autocomplete=\"off\"");
			}
		}
		else
		{
			// preselection
			$passwd_list = ilUtil::generatePasswords(5);
			foreach ($passwd_list as $passwd)
			{
				$i++;
				$ptpl->setCurrentBlock("select_input");
				$ptpl->setVariable("POST_VAR", $this->getPostVar());
				$ptpl->setVariable("OP_ID", $this->getPostVar()."_".$i);
				$ptpl->setVariable("VAL_RADIO_OPTION", $passwd);
				$ptpl->setVariable("TXT_RADIO_OPTION", $passwd);
				$ptpl->parseCurrentBlock();
			}

		}
		return $ptpl->get();
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
}
?>