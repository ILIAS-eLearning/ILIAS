<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This class represents a captcha input in a property form.
 *
 * @author Alex Killing <alex.killing@gmx.de> 
 * @version $Id$
 * @ingroup	ServicesCaptcha
 */
class ilCaptchaInputGUI extends ilFormPropertyGUI
{
	protected $value;
	
	/**
	 * Constructor
	 *
	 * @param	string	$a_title	Title
	 * @param	string	$a_postvar	Post Variable
	 */
	function __construct($a_title = "", $a_postvar = "")
	{
		global $lng;
		
		parent::__construct($a_title, $a_postvar);
		$this->setType("captcha");
		$lng->loadLanguageModule("cptch");
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
		
		include_once("./Services/Captcha/classes/class.ilSecurImage.php");
		$si = new ilSecurImage();
		if (!$si->check($_POST[$this->getPostVar()]))
		{
			$this->setAlert($lng->txt("cptch_wrong_input"));

			return false;
		}

		return true;
	}

	/**
	 * Render output
	 */
	function render()
	{
		global $lng;
		
		$tpl = new ilTemplate("tpl.prop_captchainput.html", true, true, "Services/Captcha");
				if (strlen($this->getValue()))
		{
			$tpl->setCurrentBlock("prop_text_propval");
			$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
			$tpl->parseCurrentBlock();
		}

		include_once("./Services/Captcha/classes/class.ilSecurImageUtil.php");
		$tpl->setVariable("IMAGE_SCRIPT",
			ilSecurImageUtil::getImageScript());
		
		$tpl->setVariable("POST_VAR", $this->getPostVar());
		$tpl->setVariable("TXT_CONSTR_PROP", $lng->txt("cont_constrain_proportions"));
		
//		$GLOBALS["tpl"]->addJavascript("./Services/MediaObjects/js/ServiceMediaObjectPropWidthHeight.js");

		return $tpl->get();
	}

	/**
	 * Insert property html
	 */
	function insert(&$a_tpl)
	{
		$html = $this->render();

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $html);
		$a_tpl->parseCurrentBlock();
	}

	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		global $ilUser;
		
		$this->setValue($a_values[$this->getPostVar()]);
	}

}
