<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilIdentifiedMultiValuesInputGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Services/Form
 */
abstract class ilMultipleTextsInputGUI extends ilIdentifiedMultiValuesInputGUI
{
	protected $allowMove = false;
	
	/**
	 * Constructor
	 *
	 * @param	string	$a_title	Title
	 * @param	string	$a_postvar	Post Variable
	 */
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->validationRegexp = "";
	}
	
	/**
	 * Set allow move
	 *
	 * @param	boolean	$a_allow_move Allow move
	 */
	function setAllowMove($a_allow_move)
	{
		$this->allowMove = $a_allow_move;
	}
	
	/**
	 * Get allow move
	 *
	 * @return	boolean	Allow move
	 */
	function getAllowMove()
	{
		return $this->allowMove;
	}
	
	/**
	 * @param mixed $value
	 * @return string $content
	 */
	abstract protected function fetchContentFromValue($value);
	
	/**
	 * Check input, strip slashes etc. set alert, if input is not ok.
	 *
	 * @return	boolean		Input ok, true/false
	 */
	function onCheckInput()
	{
		global $lng;
		
		$submittedElements = $_POST[$this->getPostVar()];
		
		if( !is_array($submittedElements) && $this->getRequired() )
		{
			$this->setAlert($lng->txt("msg_input_is_required"));
			return false;
		}
		
		foreach($submittedElements as $submittedValue)
		{
			$submittedContent = $this->fetchContentFromValue($submittedValue);
			
			if ($this->getRequired() && trim($submittedContent) == "")
			{
				$this->setAlert($lng->txt('msg_input_is_required'));
				return false;
			}
			
			if( strlen($this->getValidationRegexp()) )
			{
				if( !preg_match($this->getValidationRegexp(), $submittedValue->getContent()) )
				{
					$this->setAlert($lng->txt('msg_wrong_format'));
					return false;
				}
			}
		}

		return $this->checkSubItemsInput();
	}
	
	/**
	 * @return string
	 */
	public function render()
	{
		$tpl = new ilTemplate("tpl.prop_multi_text_inp.html", true, true, "Services/Form");
		$i = 0;
		foreach ($this->getMultiValues() as $value)
		{
			if (strlen($value))
			{
				$tpl->setCurrentBlock("prop_text_propval");
				$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value));
				$tpl->parseCurrentBlock();
			}
			if ($this->getAllowMove())
			{
				$tpl->setCurrentBlock("move");
				$tpl->setVariable("CMD_UP", $this->buildMultiValueSubmitVar($i, 'up'));
				$tpl->setVariable("CMD_DOWN", $this->buildMultiValueSubmitVar($i, 'down'));
				$tpl->setVariable("ID", $this->buildMultiValueFieldId($i));
				include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
				$tpl->setVariable("UP_BUTTON", ilGlyphGUI::get(ilGlyphGUI::UP));
				$tpl->setVariable("DOWN_BUTTON", ilGlyphGUI::get(ilGlyphGUI::DOWN));
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock("row");
			$tpl->setVariable("POST_VAR", $this->buildMultiValuePostVar($i));
			$tpl->setVariable("ID", $this->buildMultiValueFieldId($i));
			$tpl->setVariable("SIZE", $this->getSize());
			$tpl->setVariable("MAXLENGTH", $this->getMaxLength());
			
			if ($this->getDisabled())
			{
				$tpl->setVariable("DISABLED",
					" disabled=\"disabled\"");
			}
			else
			{
				$tpl->setVariable("CMD_ADD", $this->buildMultiValueSubmitVar($i, 'add'));
				$tpl->setVariable("CMD_REMOVE", $this->buildMultiValueSubmitVar($i, 'remove'));
				include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
				$tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
				$tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
			}
			
			$tpl->parseCurrentBlock();
			$i++;
		}
		$tpl->setVariable("ELEMENT_ID", $this->getFieldId());
		
		if (!$this->getDisabled())
		{
			$globalTpl = $GLOBALS['DIC'] ? $GLOBALS['DIC']['tpl'] : $GLOBALS['tpl'];
			$globalTpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
			$globalTpl->addJavascript("./Services/Form/js/ServiceFormIdentifiedWizardInputExtend.js");
			$globalTpl->addJavascript("./Services/Form/js/ServiceFormIdentifiedTextWizardInputConcrete.js");
		}
		
		return $tpl->get();
	}
	
	protected function getMultiValueKeyByPosition($positionIndex)
	{
		$keys = array_keys($this->getMultiValues());
		return $keys[$positionIndex];
	}
}
