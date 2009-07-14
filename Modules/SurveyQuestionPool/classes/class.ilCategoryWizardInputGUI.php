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
* @version $Id$
* @ingroup	ServicesForm
*/
class ilCategoryWizardInputGUI extends ilTextInputGUI
{
	protected $values = array();
	protected $allowMove = false;
	protected $disabled_scale = true;
	protected $show_wizard = false;
	protected $show_save_phrase = false;
	protected $categorytext;
	protected $show_neutral_category = false;
	protected $neutral_category;
	protected $neutral_category_title;
	protected $neutral_category_scale;
	
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
		$this->categorytext = $lng->txt('answer');
	}
	
	protected function calcNeutralCategoryScale()
	{
		if (is_object($this->values))
		{
			return $this->values->getCategoryCount()+1;
		}
		else
		{
			return 99;
		}
	}
	
	public function setShowNeutralCategory($a_value)
	{
		$this->show_neutral_category = $a_value;
	}
	
	public function getShowNeutralCategory()
	{
		return $this->show_neutral_category;
	}
	
	public function setNeutralCategory($a_text)
	{
		$this->neutral_category = $a_text;
	}
	
	public function getNeutralCategory()
	{
		return $this->neutral_category;
	}
	
	public function setNeutralCategoryScale($a_scale)
	{
		$this->neutral_category_scale = $a_scale;
	}
	
	public function getNeutralCategoryScale()
	{
		return $this->neutral_category_scale;
	}
	
	public function setNeutralCategoryTitle($a_title)
	{
		$this->neutral_category_title = $a_title;
	}
	
	public function getNeutralCategoryTitle()
	{
		return $this->neutral_category_title;
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
					$this->values->addCategory($value);
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
	
	function setShowSavePhrase($a_value)
	{
		$this->show_save_phrase = $a_value;
	}
	
	function getShowSavePhrase()
	{
		return $this->show_save_phrase;
	}
	
	function getDisabledScale()
	{
		return $this->disabled_scale;
	}
	
	function setDisabledScale($a_value)
	{
		$this->disabled_scale = $a_value;
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
			if (is_array($foundvalues['answer']))
			{
				foreach ($foundvalues['answer'] as $answervalue)
				{
					if (((strlen($answervalue)) == 0) && ($this->getRequired()))
					{
						$this->setAlert($lng->txt("msg_input_is_required"));
						return FALSE;
					}
				}
			}
			// check scales
			/*
			if (is_array($foundvalues['scale']))
			{
				foreach ($foundvalues['scale'] as $scale)
				{
					if ((strlen($scale)) == 0) 
					{
						$this->setAlert($lng->txt("msg_input_is_required"));
						return FALSE;
					}
				}
			}
			*/
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
		
		$tpl = new ilTemplate("tpl.prop_categorywizardinput.html", true, true, "Modules/SurveyQuestionPool");
		$i = 0;
		if (is_object($this->values))
		{
			for ($i = 0; $i < $this->values->getCategoryCount(); $i++)
			{
				$tpl->setCurrentBlock("prop_text_propval");
				$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->values->getCategory($i)));
				$tpl->parseCurrentBlock();
				$tpl->setCurrentBlock("prop_scale_propval");
				$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->values->getScale($i)));
				$tpl->parseCurrentBlock();

				if ($this->getAllowMove())
				{
					$tpl->setCurrentBlock("move");
					$tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][$i]");
					$tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][$i]");
					$tpl->setVariable("ID", $this->getPostVar() . "[$i]");
					$tpl->setVariable("UP_BUTTON", ilUtil::getImagePath('a_up.gif'));
					$tpl->setVariable("DOWN_BUTTON", ilUtil::getImagePath('a_down.gif'));
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("row");
				$class = ($i % 2 == 0) ? "even" : "odd";
				if ($i == 0) $class .= " first";
				if ($i == $this->values->getCategoryCount()-1) $class .= " last";
				$tpl->setVariable("ROW_CLASS", $class);
				$tpl->setVariable("POST_VAR", $this->getPostVar());
				$tpl->setVariable("ROW_NUMBER", $i);
				$tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
				$tpl->setVariable("SIZE", $this->getSize());
				$tpl->setVariable("MAXLENGTH", $this->getMaxLength());
				if ($this->getDisabled())
				{
					$tpl->setVariable("DISABLED", " disabled=\"disabled\"");
				}

				$tpl->setVariable("SCALE_ID", $this->getPostVar() . "[scale][$i]");
				if ($this->getDisabledScale())
				{
					$tpl->setVariable("DISABLED_SCALE", " disabled=\"disabled\"");
				}

				$tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
				$tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
				$tpl->setVariable("ADD_BUTTON", ilUtil::getImagePath('edit_add.png'));
				$tpl->setVariable("REMOVE_BUTTON", ilUtil::getImagePath('edit_remove.png'));
				$tpl->parseCurrentBlock();
			}
		}

		if ($this->getShowWizard())
		{
			$tpl->setCurrentBlock("wizard");
			$tpl->setVariable("CMD_WIZARD", 'cmd[wizard' . $this->getFieldId() . ']');
			$tpl->setVariable("WIZARD_BUTTON", ilUtil::getImagePath('wizard.png'));
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
		
		if ($this->getShowNeutralCategory())
		{
			if (strlen($this->getNeutralCategory()))
			{
				$tpl->setCurrentBlock("prop_text_neutral_propval");
				$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getNeutralCategory()));
				$tpl->parseCurrentBlock();
			}
			if (strlen($this->getNeutralCategoryTitle()))
			{
				$tpl->setCurrentBlock("neutral_category_title");
				$tpl->setVariable("CATEGORY_TITLE", ilUtil::prepareFormOutput($this->getNeutralCategoryTitle()));
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock("prop_scale_neutral_propval");
			$scale = (strlen($this->getNeutralCategoryScale())) ? $this->getNeutralCategoryScale() : $this->calcNeutralCategoryScale();
			$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($scale));
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock('neutral_row');
			$tpl->setVariable("POST_VAR", $this->getPostVar());
			$tpl->setVariable("ID", $this->getPostVar() . "_neutral");
			$tpl->setVariable("SIZE", $this->getSize());
			$tpl->setVariable("MAXLENGTH", $this->getMaxLength());
			if ($this->getDisabled())
			{
				$tpl->setVariable("DISABLED", " disabled=\"disabled\"");
			}
			$tpl->setVariable("SCALE_ID", $this->getPostVar() . "_neutral_scale");
			if ($this->getDisabledScale())
			{
				$tpl->setVariable("DISABLED_SCALE", " disabled=\"disabled\"");
			}
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("ELEMENT_ID", $this->getPostVar());
		$tpl->setVariable("ANSWER_TEXT", $this->getCategoryText());
		$tpl->setVariable("SCALE_TEXT", $lng->txt('scale'));
		$tpl->setVariable("ACTIONS_TEXT", $lng->txt('actions'));

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
		
		global $tpl;
		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::initDomEvent();
		$tpl->addJavascript("./Modules/SurveyQuestionPool/templates/default/categorywizard.js");
	}
}
