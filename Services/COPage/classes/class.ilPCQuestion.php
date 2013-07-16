<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCQuestion
*
* Assessment Question of ilPageObject
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCQuestion extends ilPageContent
{
	var $dom;
	var $q_node;			// node of Paragraph element
	
	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("pcqst");
	}

	/**
	* Set node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->q_node =& $a_node->first_child();		//... and this the Question
	}

	/**
	* Set Question Reference.
	*
	* @param	string	$a_questionreference	Question Reference
	*/
	function setQuestionReference($a_questionreference)
	{
		if (is_object($this->q_node))
		{
			$this->q_node->set_attribute("QRef", $a_questionreference);
		}
	}

	/**
	* Get Question Reference.
	*
	* @return	string	Question Reference
	*/
	function getQuestionReference()
	{
		if (is_object($this->q_node))
		{
			return $this->q_node->get_attribute("QRef", $a_questionreference);
		}
		return false;
	}

	/**
	* Create Question Element
	*/
	function create(&$a_pg_obj, $a_hier_id)
	{
		$this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
		$this->q_node = $this->dom->create_element("Question");
		$this->q_node = $this->node->append_child($this->q_node);
		$this->q_node->set_attribute("QRef", "");
	}
	
	/**
	 * Copy question from pool into page
	 *
	 * @param
	 * @return
	 */
	function copyPoolQuestionIntoPage($a_q_id, $a_hier_id)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
		$question = assQuestion::_instanciateQuestion($a_q_id);
		$duplicate_id = $question->copyObject(0, $question->getTitle());
		$duplicate = assQuestion::_instanciateQuestion($duplicate_id);
		$duplicate->setObjId(0);
		
		// we remove everything not supported by the non-tiny self
		// assessment question editor
		$q = $duplicate->getQuestion();

		// we try to save all latex tags
		$try = true;
		$ls = '<span class="latex">';
		$le = '</span>';
		while ($try)
		{
			// search position of start tag
			$pos1 = strpos($q, $ls);
			if (is_int($pos1))
			{
				$pos2 = strpos($q, $le, $pos1);
				if (is_int($pos2))
				{
					// both found: replace end tag
					$q = substr($q, 0, $pos2)."[/tex]".substr($q, $pos2+7);
					$q = substr($q, 0, $pos1)."[tex]".substr($q, $pos1+20);
				}
				else
				{
					$try = false;
				}
			}
			else
			{
				$try = false;
			}
		}
		
		$tags = assQuestionGUI::getSelfAssessmentTags();
		$tstr = "";
		foreach ($tags as $t)
		{
			$tstr.="<".$t.">";
		}
		$q = ilUtil::secureString($q, true, $tstr);
		// self assessment uses nl2br, not p
		$duplicate->setQuestion($q);
		
		$duplicate->saveQuestionDataToDb();
		
		$this->q_node->set_attribute("QRef", "il__qst_".$duplicate_id);
	}
	
	/**
	 * Get lang vars needed for editing
	 * @return array array of lang var keys
	 */
	static function getLangVars()
	{
		return array("ed_insert_pcqst", "empty_question", "pc_qst");
	}

	
}
?>
