<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");

/**
* This class represents a text area property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilTextAreaMultiLangInputGUI extends ilFormPropertyGUI
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
		parent::__construct($a_title, $a_postvar);
		$this->setType("textarea_ml");
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
		$_POST[$this->getPostVar()] = self::removeProhibitedCharacters($_POST[$this->getPostVar()]);

		if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}

		return true;
	}

	/**
	 * Insert property html
	 */
	function insert($a_tpl)
	{
		global $DIC;

		$lng = $DIC->language();

		$ttpl = new ilTemplate("tpl.prop_textarea_ml.html", true, true, "Services/Form");
		
		if (!$this->getDisabled())
		{
			$ttpl->setVariable("POST_VAR",
				$this->getPostVar());
		}
		$ttpl->setVariable("ID", $this->getFieldId());
		if ($this->getDisabled())
		{
			$ttpl->setVariable('DISABLED','disabled="disabled" ');
		}
		$ttpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));

		if($this->getRequired())
		{
			$ttpl->setVariable("REQUIRED", "required=\"required\"");
		}

		if ($this->getDisabled())
		{
			$ttpl->setVariable("HIDDEN_INPUT",
				$this->getHiddenTag($this->getPostVar(), $this->getValue()));
		}

		// modal
		$factory = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();

		$modal = $factory->modal()->roundtrip($this->getTitle(), $factory->legacy($this->getSubForm()));
		$button1 = $factory->button()->shy($lng->txt("form_edit_for_other_languages"), '#')
			->withOnClick($modal->getShowSignal());

		$button = $renderer->render([$button1, $modal]);

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $ttpl->get().$button);
		$a_tpl->parseCurrentBlock();
	}

	/**
	 * Init sub form.
	 */
	public function getSubForm()
	{
		global $DIC;

		$lng = $DIC->language();

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setCloseTag(false);
		$form->setOpenTag(false);

		$form->setTitle($this->getTitle());

		//
		$ti = new ilTextAreaInputGUI($lng->txt("meta_l_de"), $this->getPostVar()."_de");
		$ti->setRows(10);
		$form->addItem($ti);

		//
		$ti = new ilTextAreaInputGUI($lng->txt("meta_l_de"), $this->getPostVar()."_en");
		$ti->setRows(10);
		$form->addItem($ti);


		return $form->getHTML();
	}
}
