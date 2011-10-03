<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
		$duplicate_id = $question->duplicate(false);
		$duplicate = assQuestion::_instanciateQuestion($duplicate_id);
		$duplicate->setObjId(0);
		
		// we remove everything not supported by the non-tiny self
		// assessment question editor
		$q = $duplicate->getQuestion();
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
	
}
?>
