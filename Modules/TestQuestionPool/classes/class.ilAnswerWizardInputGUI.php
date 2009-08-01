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
* This class represents a single choice wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilAnswerWizardInputGUI extends ilTextInputGUI
{
	protected $values = array();
	protected $allowMove = false;
	protected $singleline = true;
	protected $qstObject = null;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setSize('25');
		$this->validationRegexp = "";
	}

	/**
	* Set Value.
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		$this->values = array();
		if (is_array($a_value))
		{
			if (is_array($a_value['answer']))
			{
				foreach ($a_value['answer'] as $index => $value)
				{
					include_once "./Modules/TestQuestionPool/classes/class.assAnswerBinaryStateImage.php";
					$answer = new ASS_AnswerBinaryStateImage($value, $a_value['points'][$index], $index, 1, $a_value['imagename'][$index]);
					array_push($this->values, $answer);
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
	* Set singleline
	*
	* @param	boolean	$a_value	Value
	*/
	function setSingleline($a_value)
	{
		$this->singleline = $a_value;
	}

	/**
	* Get singleline
	*
	* @return	boolean	Value
	*/
	function getSingleline()
	{
		return $this->singleline;
	}

	/**
	* Set question object
	*
	* @param	object	$a_value	test object
	*/
	function setQuestionObject($a_value)
	{
		$this->qstObject =& $a_value;
	}

	/**
	* Get question object
	*
	* @return	object	Value
	*/
	function getQuestionObject()
	{
		return $this->qstObject;
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
				foreach ($foundvalues['answer'] as $aidx => $answervalue)
				{
					if ((strlen($answervalue)) == 0)
					{
						$this->setAlert($lng->txt("msg_input_is_required"));
						return FALSE;
					}
				}
			}
			// check points
			$max = 0;
			if (is_array($foundvalues['points']))
			{
				foreach ($foundvalues['points'] as $points)
				{
					if ($points > $max) $max = $points;
					if (((strlen($points)) == 0) || (!is_numeric($points))) 
					{
						$this->setAlert($lng->txt("form_msg_numeric_value_required"));
						return FALSE;
					}
				}
			}
			if ($max == 0)
			{
				$this->setAlert($lng->txt("enter_enough_positive_points"));
				return false;
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
		
		$tpl = new ilTemplate("tpl.prop_answerwizardinput.html", true, true, "Modules/TestQuestionPool");
		$i = 0;
		foreach ($this->values as $value)
		{
			if ($this->getSingleline())
			{
				if (is_object($value))
				{
					$tpl->setCurrentBlock("prop_text_propval");
					$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getAnswertext()));
					$tpl->parseCurrentBlock();
					$tpl->setCurrentBlock("prop_points_propval");
					$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getPoints()));
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock('singleline');
				$tpl->setVariable("SIZE", $this->getSize());
				$tpl->setVariable("SINGLELINE_ID", $this->getPostVar() . "[answer][$i]");
				$tpl->setVariable("SINGLELINE_ROW_NUMBER", $i);
				$tpl->setVariable("SINGLELINE_POST_VAR", $this->getPostVar());
				$tpl->setVariable("MAXLENGTH", $this->getMaxLength());
				if ($this->getDisabled())
				{
					$tpl->setVariable("DISABLED_SINGLELINE", " disabled=\"disabled\"");
				}
				$tpl->parseCurrentBlock();
			}
			else if (!$this->getSingleline())
			{
				if (is_object($value))
				{
					$tpl->setCurrentBlock("prop_points_propval");
					$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getPoints()));
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock('multiline');
				$tpl->setVariable("PROPERTY_VALUE", $this->qstObject->prepareTextareaOutput($value->getAnswertext()));
				$tpl->setVariable("MULTILINE_ID", $this->getPostVar() . "[answer][$i]");
				$tpl->setVariable("MULTILINE_ROW_NUMBER", $i);
				$tpl->setVariable("MULTILINE_POST_VAR", $this->getPostVar());
				if ($this->getDisabled())
				{
					$tpl->setVariable("DISABLED_MULTILINE", " disabled=\"disabled\"");
				}
				$tpl->parseCurrentBlock();
			}
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
			if ($i == count($this->values)-1) $class .= " last";
			$tpl->setVariable("ROW_CLASS", $class);
			$tpl->setVariable("POST_VAR", $this->getPostVar());
			$tpl->setVariable("ROW_NUMBER", $i);
			$tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
			$tpl->setVariable("POINTS_ID", $this->getPostVar() . "[points][$i]");
			$tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
			$tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
			if ($this->getDisabled())
			{
				$tpl->setVariable("DISABLED_POINTS", " disabled=\"disabled\"");
			}
			$tpl->setVariable("ADD_BUTTON", ilUtil::getImagePath('edit_add.png'));
			$tpl->setVariable("REMOVE_BUTTON", ilUtil::getImagePath('edit_remove.png'));
			$tpl->parseCurrentBlock();
			$i++;
		}

		$tpl->setVariable("ELEMENT_ID", $this->getPostVar());
		$tpl->setVariable("ANSWER_TEXT", $lng->txt('answer_text'));
		$tpl->setVariable("POINTS_TEXT", $lng->txt('points'));
		$tpl->setVariable("COMMANDS_TEXT", $lng->txt('actions'));

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
		
		global $tpl;
		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::initDomEvent();
		$tpl->addJavascript("./Modules/TestQuestionPool/templates/default/answerwizard.js");
	}
}
