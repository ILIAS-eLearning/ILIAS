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

	/**
	 * After page has been updated (or created)
	 *
	 * @param object page object
	 * @param DOMDocument $a_domdoc dom document
	 * @param string xml
	 * @param bool true on creation, otherwise false
	 */
	static function afterPageUpdate($a_page, DOMDocument $a_domdoc, $a_xml, $a_creation)
	{
		global $ilDB;
		
		include_once("./Services/COPage/classes/class.ilInternalLink.php");
		
		$ilDB->manipulateF("DELETE FROM page_question WHERE page_parent_type = %s ".
			" AND page_id = %s AND page_lang = %s", array("text", "integer", "text"),
			array($a_page->getParentType(), $a_page->getId(), $a_page->getLanguage()));

		$xpath = new DOMXPath($a_domdoc);
		$nodes = $xpath->query('//Question');	
		$q_ids = array();
		foreach ($nodes as $node)
		{
			$q_ref = $node->getAttribute("QRef");

			$inst_id = ilInternalLink::_extractInstOfTarget($q_ref);
			if (!($inst_id > 0))
			{
				$q_id = ilInternalLink::_extractObjIdOfTarget($q_ref);
				if ($q_id > 0)
				{
					$q_ids[$q_id] = $q_id;
				}
			}
		}
		foreach($q_ids as $qid)
		{
			$ilDB->manipulateF("INSERT INTO page_question (page_parent_type, page_id, page_lang, question_id)".
				" VALUES (%s,%s,%s,%s)",
				array("text", "integer", "text", "integer"),
				array($a_page->getParentType(), $a_page->getId(), $a_page->getLanguage(), $qid));
		}

	}
	
	/**
	 * Before page is being deleted
	 *
	 * @param object page object
	 */
	static function beforePageDelete($a_page)
	{
		global $ilDB;
		
		$ilDB->manipulateF("DELETE FROM page_question WHERE page_parent_type = %s ".
			" AND page_id = %s AND page_lang = %s", array("text", "integer", "text"),
			array($a_page->getParentType(), $a_page->getId(), $a_page->getLanguage()));
	}
	
	/**
	 * Get all questions of a page
	 */
	static function _getQuestionIdsForPage($a_parent_type, $a_page_id, $a_lang = "-")
	{
		global $ilDB;

		$res = $ilDB->queryF("SELECT * FROM page_question WHERE page_parent_type = %s ".
			" AND page_id = %s AND page_lang = %s",
			array("text", "integer", "text"),
			array($a_parent_type, $a_page_id, $a_lang));
		$q_ids = array();
		while ($rec = $ilDB->fetchAssoc($res))
		{
			$q_ids[] = $rec["question_id"];
		}

		return $q_ids;
	}

	/**
	 * Get page for question id
	 *
	 * @param
	 * @return array
	 */
	function _getPageForQuestionId($a_q_id, $a_parent_type = "")
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM page_question ".
			" WHERE question_id = ".$ilDB->quote($a_q_id, "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			if ($a_parent_type == "" || $rec["page_parent_type"] == $a_parent_type)
			{
				return array("page_id" => $rec["page_id"], "parent_type" => $rec["page_parent_type"]);
			}
		}
		return false;
	}


}
?>
