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
* This class represents a key value pair wizard property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilMatchingPairWizardInputGUI extends ilTextInputGUI
{
	protected $pairs = array();
	protected $allowMove = false;
	protected $terms = array();
	protected $definitions = array();
	
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
		$this->pairs = array();
		$this->terms = array();
		$this->definitions = array();
		if (is_array($a_value))
		{
			include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php";
			include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php";
			include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php";
			if (is_array($a_value['term']))
			{
				foreach ($a_value['term'] as $idx => $term)
				{
					array_push($this->pairs, new assAnswerMatchingPair(new assAnswerMatchingTerm('', '', $term), new assAnswerMatchingDefinition('', '', $a_value['definition'][$idx]), $a_value['points'][$idx]));
				}
			}
			$term_ids = split(",", $a_value['term_id']);
			foreach ($term_ids as $id)
			{
				array_push($this->terms, new assAnswerMatchingTerm('', '', $id));
			}
			$definition_ids = split(",", $a_value['definition_id']);
			foreach ($definition_ids as $id)
			{
				array_push($this->definitions, new assAnswerMatchingDefinition('', '', $id));
			}
		}
	}

	/**
	* Set terms.
	*
	* @param	array	$a_terms	Terms
	*/
	function setTerms($a_terms)
	{
		$this->terms = $a_terms;
	}

	/**
	* Set definitions.
	*
	* @param	array	$a_definitions	Definitions
	*/
	function setDefinitions($a_definitions)
	{
		$this->definitions = $a_definitions;
	}

	/**
	* Set pairs.
	*
	* @param	array	$a_pairs	Pairs
	*/
	function setPairs($a_pairs)
	{
		$this->pairs = $a_pairs;
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
			if (is_array($foundvalues['term']))
			{
				foreach ($foundvalues['term'] as $val)
				{
					if ($this->getRequired() && $val < 1) 
					{
						$this->setAlert($lng->txt("msg_input_is_required"));
						return FALSE;
					}
				}
				foreach ($foundvalues['definition'] as $val)
				{
					if ($this->getRequired() && $val < 1) 
					{
						$this->setAlert($lng->txt("msg_input_is_required"));
						return FALSE;
					}
				}
				$max = 0;
				foreach ($foundvalues['points'] as $val)
				{
					if ($val > 0) $max += $val;
					if ($this->getRequired() && (strlen($val)) == 0) 
					{
						$this->setAlert($lng->txt("msg_input_is_required"));
						return FALSE;
					}
				}
				if ($max <= 0)
				{
					$this->setAlert($lng->txt("enter_enough_positive_points"));
					return FALSE;
				}
			}
			else
			{
				if ($this->getRequired())
				{
					$this->setAlert($lng->txt("msg_input_is_required"));
					return FALSE;
				}
			}
		}
		else
		{
			if ($this->getRequired())
			{
				$this->setAlert($lng->txt("msg_input_is_required"));
				return FALSE;
			}
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
		
		$tpl = new ilTemplate("tpl.prop_matchingpairinput.html", true, true, "Modules/TestQuestionPool");
		$i = 0;

		foreach ($this->pairs as $pair)
		{
			$counter = 1;
			$tpl->setCurrentBlock("option_term");
			$tpl->setVariable("TEXT_OPTION", ilUtil::prepareFormOutput($lng->txt('please_select')));
			$tpl->setVariable("VALUE_OPTION", 0);
			$tpl->parseCurrentBlock();
			foreach ($this->terms as $term)
			{
				$tpl->setCurrentBlock("option_term");
				$tpl->setVariable("VALUE_OPTION", ilUtil::prepareFormOutput($term->identifier));
				$tpl->setVariable("TEXT_OPTION", $lng->txt('term') . " " . $counter);
				if ($pair->term->identifier == $term->identifier) $tpl->setVariable('SELECTED_OPTION', ' selected="selected"');
				$tpl->parseCurrentBlock();
				$counter++;
			}
			$counter = 1;
			$tpl->setCurrentBlock("option_definition");
			$tpl->setVariable("TEXT_OPTION", ilUtil::prepareFormOutput($lng->txt('please_select')));
			$tpl->setVariable("VALUE_OPTION", 0);
			$tpl->parseCurrentBlock();
			foreach ($this->definitions as $definition)
			{
				$tpl->setCurrentBlock("option_definition");
				$tpl->setVariable("VALUE_OPTION", ilUtil::prepareFormOutput($definition->identifier));
				$tpl->setVariable("TEXT_OPTION", $lng->txt('definition') . " " . $counter);
				if ($pair->definition->identifier == $definition->identifier) $tpl->setVariable('SELECTED_OPTION', ' selected="selected"');
				$tpl->parseCurrentBlock();
				$counter++;
			}

			if (strlen($pair->points))
			{
				$tpl->setCurrentBlock('points_value');
				$tpl->setVariable('POINTS_VALUE', $pair->points);
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
			$tpl->setVariable("ROW_NUMBER", $i);
			
			$tpl->setVariable("ID", $this->getPostVar() . "[$i]");
			$tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
			$tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
			$tpl->setVariable("ADD_BUTTON", ilUtil::getImagePath('edit_add.png'));
			$tpl->setVariable("REMOVE_BUTTON", ilUtil::getImagePath('edit_remove.png'));

			$tpl->setVariable("POST_VAR", $this->getPostVar());

			$tpl->parseCurrentBlock();

			$i++;
		}
		
		$tpl->setCurrentBlock('term_ids');
		$ids = array();
		foreach ($this->terms as $term)
		{
			array_push($ids, $term->identifier);
		}
		$tpl->setVariable("POST_VAR", $this->getPostVar());
		$tpl->setVariable("TERM_IDS", join($ids, ","));
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock('definition_ids');
		$ids = array();
		foreach ($this->definitions as $definition)
		{
			array_push($ids, $definition->identifier);
		}
		$tpl->setVariable("POST_VAR", $this->getPostVar());
		$tpl->setVariable("DEFINITION_IDS", join($ids, ","));
		$tpl->parseCurrentBlock();
		
		$tpl->setVariable("ELEMENT_ID", $this->getPostVar());
		$tpl->setVariable("TEXT_POINTS", $lng->txt('points'));
		$tpl->setVariable("TEXT_DEFINITION", $lng->txt('definition'));
		$tpl->setVariable("TEXT_TERM", $lng->txt('term'));

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
		
		global $tpl;
		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::initDomEvent();
		$tpl->addJavascript("./Modules/TestQuestionPool/templates/default/matchingpairwizard.js");
	}
}
