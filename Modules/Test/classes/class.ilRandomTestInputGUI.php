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
* This class represents a random test input property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilRandomTestInputGUI extends ilSubEnabledFormPropertyGUI
{
	protected $values = array();
	protected $equal_points = false;
	protected $question_count = true;
	protected $random_pools = array();
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setRequired(true);
	}

	/**
	* Set Value.
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		$this->values = array();
		include_once "./Modules/Test/classes/class.ilRandomTestData.php";
		if (is_array($a_value['qpl']))
		{
			foreach ($a_value['qpl'] as $idx => $qpl)
			{
				array_push($this->values, new ilRandomTestData($a_value['count'][$idx], $qpl));
			}
		}
	}
	
	public function setValueByArray($a_values)
	{
		$this->setValue($a_values[$this->getPostVar()]);
	}

	/**
	* Set usage of equal points
	*
	* @param	boolean	$a_value	Usage of equal points
	*/
	function setUseEqualPointsOnly($a_value)
	{
		$this->equal_points = $a_value;
	}

	/**
	* Get usage of equal points
	*
	* @return	boolean	Usage of equal points
	*/
	function getUseEqualPointsOnly()
	{
		return $this->equal_points;
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
	* Set random question pools
	*
	* @param	array	$a_values	Value
	*/
	function setRandomQuestionPools($a_values)
	{
		$this->random_pools = $a_values;
	}

	/**
	* Get usage of question count
	*
	* @param	boolean	$a_value	Usage of question count
	*/
	function setUseQuestionCount($a_value)
	{
		$this->question_count = $a_value;
	}

	/**
	* Get usage of question count
	*
	* @return	boolean	Usage of question count
	*/
	function getUseQuestionCount()
	{
		return $this->question_count;
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
			if (is_array($foundvalues['count']))
			{
				// check question count
				if ($this->getUseQuestionCount())
				{
					foreach ($foundvalues['count'] as $idx => $answervalue)
					{
						if ((strlen($answervalue)) == 0) 
						{
							$this->setAlert($lng->txt("msg_input_is_required"));
							return FALSE;
						}
						if (!is_numeric($answervalue)) 
						{
							$this->setAlert($lng->txt("form_msg_numeric_value_required"));
							return FALSE;
						}
						if ($answervalue < 1) 
						{
							$this->setAlert($lng->txt("msg_question_count_too_low"));
							return FALSE;
						}
						if ($answervalue > $this->random_pools[$foundvalues['qpl'][$idx]]['count']) 
						{
							$this->setAlert($lng->txt("tst_random_selection_question_count_too_high"));
							return FALSE;
						}
					}
				}
			}
			else
			{
				if ($this->getUseQuestionCount())
				{
					$this->setAlert($lng->txt("msg_question_count_too_low"));
					return FALSE;
				}
			}
			// check pool selection
			if (is_array($foundvalues['qpl']))
			{
				foreach ($foundvalues['qpl'] as $qpl)
				{
					if ($qpl < 1)
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
		
		$tpl = new ilTemplate("tpl.prop_randomtestinput.html", true, true, "Modules/Test");
		$i = 0;
		$pools = $this->random_pools;
		foreach ($this->values as $value)
		{
			if (array_key_exists($value->qpl, $this->random_pools)) unset($pools[$value->qpl]);
		}
		foreach ($this->values as $value)
		{
			if ($this->getUseQuestionCount())
			{
				if (is_object($value))
				{
					$tpl->setCurrentBlock("prop_text_propval");
					$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->count));
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock('question_count');
				$tpl->setVariable("SIZE", 3);
				$tpl->setVariable("QUESTION_COUNT_ID", $this->getPostVar() . "[count][$i]");
				$tpl->setVariable("QUESTION_COUNT_ROW_NUMBER", $i);
				$tpl->setVariable("POST_VAR", $this->getPostVar());
				$tpl->setVariable("MAXLENGTH", 5);
				if ($this->getDisabled())
				{
					$tpl->setVariable("DISABLED_QUESTION_COUNT", " disabled=\"disabled\"");
				}
				$tpl->parseCurrentBlock();
			}

			$tpl->setCurrentBlock("option");
			$tpl->setVariable("OPTION_VALUE", 0);
			$tpl->setVariable("OPTION_TEXT", ilUtil::prepareFormOutput($lng->txt('select_questionpool_option')));
			$tpl->parseCurrentBlock();
			foreach ($this->random_pools as $qpl => $pool)
			{
				if (($value->qpl == $qpl) || (array_key_exists($qpl, $pools)))
				{
					$tpl->setCurrentBlock("option");
					if ($value->qpl == $qpl)
					{
						$tpl->setVariable("OPTION_SELECTED", ' selected="selected"');
					}
					$tpl->setVariable("OPTION_VALUE", $qpl);
					$tpl->setVariable("OPTION_TEXT", ilUtil::prepareFormOutput($pool['title']));
					$tpl->parseCurrentBlock();
				}
			}

			$tpl->setCurrentBlock("row");
			$class = ($i % 2 == 0) ? "even" : "odd";
			if ($i == 0) $class .= " first";
			if ($i == count($this->values)-1) $class .= " last";
			$tpl->setVariable("ROW_CLASS", $class);
			$tpl->setVariable("POST_VAR", $this->getPostVar());
			$tpl->setVariable("ROW_NUMBER", $i);
			$tpl->setVariable("ID", $this->getPostVar() . "[$i]");
			$tpl->setVariable("CMD_ADD", "cmd[add" . $this->getPostVar() . "][$i]");
			$tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getPostVar() . "][$i]");
			$tpl->setVariable("ADD_BUTTON", ilUtil::getImagePath('edit_add.png'));
			$tpl->setVariable("REMOVE_BUTTON", ilUtil::getImagePath('edit_remove.png'));
			$tpl->parseCurrentBlock();
			$i++;
		}
		$tpl->setVariable("ELEMENT_ID", $this->getPostVar());

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
		
//		global $tpl;
//		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
//		ilYuiUtil::initDomEvent();
//		$tpl->addJavascript("./Modules/TestQuestionPool/templates/default/singlechoicewizard.js");
	}
}
