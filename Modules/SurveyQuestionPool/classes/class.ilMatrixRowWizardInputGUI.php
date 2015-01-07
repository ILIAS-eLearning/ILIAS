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
* This class represents a survey question category wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de> 
* @version $Id: class.ilMatrixRowWizardInputGUI.php 23642 2010-04-26 12:31:50Z hschottm $
* @ingroup	ServicesForm
*/
class ilMatrixRowWizardInputGUI extends ilTextInputGUI
{
	protected $values = array();
	protected $allowMove = false;
	protected $show_wizard = false;
	protected $show_save_phrase = false;
	protected $categorytext;
	protected $labeltext;
	protected $use_other_answer;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		global $lng;
		$this->show_wizard = false;
		$this->show_save_phrase = false;
		$this->categorytext = $lng->txt('row_text');
		$this->use_other_answer = false;
	}
	
	public function getUseOtherAnswer()
	{
		return $this->use_other_answer;
	}
	
	public function setUseOtherAnswer($a_value)
	{
		$this->use_other_answer = ($a_value) ? true : false;
	}
	
	/**
	* Set Value.
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyCategories.php";
		$this->values = new SurveyCategories();
		if (is_array($a_value))
		{
			if (is_array($a_value['answer']))
			{
				foreach ($a_value['answer'] as $index => $value)
				{
					$this->values->addCategory($value, $a_value['other'][$index]);
				}
			}
		}
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
	
	function setShowWizard($a_value)
	{
		$this->show_wizard = $a_value;
	}
	
	function getShowWizard()
	{
		return $this->show_wizard;
	}
	
	public function setCategoryText($a_text)
	{
		$this->categorytext = $a_text;
	}
	
	public function getCategoryText()
	{
		return $this->categorytext;
	}
	
	public function setLabelText($a_text)
	{
		$this->labeltext = $a_text;
	}
	
	public function getLabelText()
	{
		return $this->labeltext;
	}
	
	function setShowSavePhrase($a_value)
	{
		$this->show_save_phrase = $a_value;
	}
	
	function getShowSavePhrase()
	{
		return $this->show_save_phrase;
	}
	
	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		if (is_array($_POST[$this->getPostVar()])) $_POST[$this->getPostVar()] = ilUtil::stripSlashesRecursive($_POST[$this->getPostVar()]);
		$foundvalues = $_POST[$this->getPostVar()];
		if (is_array($foundvalues))
		{
			// check answers
			if (is_array($foundvalues['answer']))
			{
				foreach ($foundvalues['answer'] as $idx => $answervalue)
				{
					if (((strlen($answervalue)) == 0) && ($this->getRequired() && (!$foundvalues['other'][$idx])))
					{
						$this->setAlert($lng->txt("msg_input_is_required"));
						return FALSE;
					}
				}
			}
		}
		else
		{
			$this->setAlert($lng->txt("msg_input_is_required"));
			return FALSE;
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
		
		$tpl = new ilTemplate("tpl.prop_matrixrowwizardinput.html", true, true, "Modules/SurveyQuestionPool");
		$i = 0;
		if (is_object($this->values))
		{
			for ($i = 0; $i < $this->values->getCategoryCount(); $i++)
			{
				$cat = $this->values->getCategory($i);
				$tpl->setCurrentBlock("prop_text_propval");
				$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($cat->title));
				$tpl->parseCurrentBlock();
				$tpl->setCurrentBlock("prop_label_propval");
				$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($cat->label));
				$tpl->parseCurrentBlock();

				if ($this->getUseOtherAnswer())
				{
					$tpl->setCurrentBlock("other_answer_checkbox");
					$tpl->setVariable("POST_VAR", $this->getPostVar());
					$tpl->setVariable("OTHER_ID", $this->getPostVar() . "[other][$i]");
					$tpl->setVariable("ROW_NUMBER", $i);
					if ($cat->other)
					{
						$tpl->setVariable("CHECKED_OTHER", ' checked="checked"');
					}
					$tpl->parseCurrentBlock();
				}

				if ($this->getAllowMove())
				{
					$tpl->setCurrentBlock("move");
					$tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][$i]");
					$tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][$i]");
					$tpl->setVariable("ID", $this->getPostVar() . "[$i]");
					include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
					$tpl->setVariable("UP_BUTTON", ilGlyphGUI::get(ilGlyphGUI::UP));
					$tpl->setVariable("DOWN_BUTTON", ilGlyphGUI::get(ilGlyphGUI::DOWN));					
					$tpl->parseCurrentBlock();
				}
				
				$tpl->setCurrentBlock("row");				
				$tpl->setVariable("POST_VAR", $this->getPostVar());
				$tpl->setVariable("ROW_NUMBER", $i);
				$tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
				$tpl->setVariable("ID_LABEL", $this->getPostVar() . "[label][$i]");
				$tpl->setVariable("SIZE", $this->getSize());
				$tpl->setVariable("SIZE_LABEL", 15);
				$tpl->setVariable("MAXLENGTH", $this->getMaxLength());
				if ($this->getDisabled())
				{
					$tpl->setVariable("DISABLED", " disabled=\"disabled\"");
					$tpl->setVariable("DISABLED_LABEL", " disabled=\"disabled\"");
				}

				$tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
				$tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
				include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
				$tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
				$tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));				
				$tpl->parseCurrentBlock();
			}
		}

		if ($this->getShowWizard())
		{
			$tpl->setCurrentBlock("wizard");
			$tpl->setVariable("CMD_WIZARD", 'cmd[wizard' . $this->getFieldId() . ']');
			$tpl->setVariable("WIZARD_BUTTON", ilUtil::getImagePath('wizard.svg'));
			$tpl->setVariable("WIZARD_TEXT", $lng->txt('add_phrase'));
			$tpl->parseCurrentBlock();
		}
		
		if ($this->getShowSavePhrase())
		{
			$tpl->setCurrentBlock('savephrase');
			$tpl->setVariable("POST_VAR", $this->getPostVar());
			$tpl->setVariable("VALUE_SAVE_PHRASE", $lng->txt('save_phrase'));
			$tpl->parseCurrentBlock();
		}
		
		if ($this->getUseOtherAnswer())
		{
			$tpl->setCurrentBlock('other_answer_title');
			$tpl->setVariable("OTHER_TEXT", $lng->txt('use_other_answer'));
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("ELEMENT_ID", $this->getPostVar());
		$tpl->setVariable("ANSWER_TEXT", $this->getCategoryText());
		$tpl->setVariable("LABEL_TEXT", $this->getLabelText());
		$tpl->setVariable("ACTIONS_TEXT", $lng->txt('actions'));
	
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
		
		global $tpl;
		$tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
		$tpl->addJavascript("./Modules/SurveyQuestionPool/templates/default/matrixrowwizard.js");
	}
}
