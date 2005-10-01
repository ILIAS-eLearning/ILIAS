<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/
require_once "./assessment/classes/class.assQuestion.php";
require_once "./assessment/classes/class.ilQTIUtils.php";

define("TEXT_QUESTION_IDENTIFIER", "TEXT QUESTION");

/**
* Class for text questions
*
* ASS_TextQuestion is a class for text questions
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assTextQuestion.php
* @modulegroup   Assessment
*/
class ASS_TextQuestion extends ASS_Question
{
	/**
	* Question string
	*
	* The question string of the text question
	*
	* @var string
	*/
	var $question;

	/**
	* Maximum number of characters of the answertext
	*
	* Maximum number of characters of the answertext
	*
	* @var integer
	*/
	var $maxNumOfChars;

	/**
	* ASS_TextQuestion constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_TextQuestion object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the text question
	* @access public
	* @see ASS_Question:ASS_Question()
	*/
	function ASS_TextQuestion(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""
	  )
	{
		$this->ASS_Question($title, $comment, $author, $owner);
		$this->question = $question;
		$this->maxNumOfChars = 0;
		$this->points = 0;
	}

	/**
	* Returns true, if a multiple choice question is complete for use
	*
	* Returns true, if a multiple choice question is complete for use
	*
	* @return boolean True, if the multiple choice question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question))
		{
			return true;
		}
			else
		{
			return false;
		}
	}

	/**
	* Returns a QTI xml representation of the question
	*
	* Returns a QTI xml representation of the question and sets the internal
	* domxml variable with the DOM XML representation of the QTI xml representation
	*
	* @return string The QTI xml representation of the question
	* @access public
	*/
	function to_xml($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false)
	{
		if (!empty($this->domxml))
		{
			$this->domxml->free();
		}
		$xml_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xml_header .= "<questestinterop></questestinterop>\n";
		$this->domxml = domxml_open_mem($xml_header);
		$root = $this->domxml->document_element();
		// qti ident
		$qtiIdent = $this->domxml->create_element("item");
		$qtiIdent->set_attribute("ident", "il_".IL_INST_ID."_qst_".$this->getId());
		$qtiIdent->set_attribute("title", $this->getTitle());
		$root->append_child($qtiIdent);
		// add question description
		$qtiComment = $this->domxml->create_element("qticomment");
		$qtiCommentText = $this->domxml->create_text_node($this->getComment());
		$qtiComment->append_child($qtiCommentText);
		$qtiIdent->append_child($qtiComment);
		// add estimated working time
		$qtiDuration = $this->domxml->create_element("duration");
		$workingtime = $this->getEstimatedWorkingTime();
		$qtiDurationText = $this->domxml->create_text_node(sprintf("P0Y0M0DT%dH%dM%dS", $workingtime["h"], $workingtime["m"], $workingtime["s"]));
		$qtiDuration->append_child($qtiDurationText);
		$qtiIdent->append_child($qtiDuration);
		// add ILIAS specific metadata
		$qtiItemmetadata = $this->domxml->create_element("itemmetadata");
		$qtiMetadata = $this->domxml->create_element("qtimetadata");
		
		$qtiMetadatafield = $this->domxml->create_element("qtimetadatafield");
		$qtiFieldlabel = $this->domxml->create_element("fieldlabel");
		$qtiFieldlabelText = $this->domxml->create_text_node("ILIAS_VERSION");
		$qtiFieldlabel->append_child($qtiFieldlabelText);
		$qtiFieldentry = $this->domxml->create_element("fieldentry");
		$qtiFieldentryText = $this->domxml->create_text_node($this->ilias->getSetting("ilias_version"));
		$qtiFieldentry->append_child($qtiFieldentryText);
		$qtiMetadatafield->append_child($qtiFieldlabel);
		$qtiMetadatafield->append_child($qtiFieldentry);
		$qtiMetadata->append_child($qtiMetadatafield);

		$qtiMetadatafield = $this->domxml->create_element("qtimetadatafield");
		$qtiFieldlabel = $this->domxml->create_element("fieldlabel");
		$qtiFieldlabelText = $this->domxml->create_text_node("QUESTIONTYPE");
		$qtiFieldlabel->append_child($qtiFieldlabelText);
		$qtiFieldentry = $this->domxml->create_element("fieldentry");
		$qtiFieldentryText = $this->domxml->create_text_node(TEXT_QUESTION_IDENTIFIER);
		$qtiFieldentry->append_child($qtiFieldentryText);
		$qtiMetadatafield->append_child($qtiFieldlabel);
		$qtiMetadatafield->append_child($qtiFieldentry);
		$qtiMetadata->append_child($qtiMetadatafield);
		
		$qtiMetadatafield = $this->domxml->create_element("qtimetadatafield");
		$qtiFieldlabel = $this->domxml->create_element("fieldlabel");
		$qtiFieldlabelText = $this->domxml->create_text_node("AUTHOR");
		$qtiFieldlabel->append_child($qtiFieldlabelText);
		$qtiFieldentry = $this->domxml->create_element("fieldentry");
		$qtiFieldentryText = $this->domxml->create_text_node($this->getAuthor());
		$qtiFieldentry->append_child($qtiFieldentryText);
		$qtiMetadatafield->append_child($qtiFieldlabel);
		$qtiMetadatafield->append_child($qtiFieldentry);
		$qtiMetadata->append_child($qtiMetadatafield);
		
		$qtiItemmetadata->append_child($qtiMetadata);
		$qtiIdent->append_child($qtiItemmetadata);
		
		// PART I: qti presentation
		$qtiPresentation = $this->domxml->create_element("presentation");
		$qtiPresentation->set_attribute("label", $this->getTitle());
		// add flow to presentation
		$qtiFlow = $this->domxml->create_element("flow");
		// add material with question text to presentation
		$qtiMaterial = $this->domxml->create_element("material");
		$qtiMatText = $this->domxml->create_element("mattext");
		$qtiMatTextText = $this->domxml->create_text_node($this->get_question());
		$qtiMatText->append_child($qtiMatTextText);
		$qtiMaterial->append_child($qtiMatText);
		$qtiFlow->append_child($qtiMaterial);
		// add information on response rendering
		$qtiResponseStr = $this->domxml->create_element("response_str");
		$qtiResponseStr->set_attribute("ident", "TEXT");
		$qtiResponseStr->set_attribute("rcardinality", "Ordered");
		$qtiRenderFib = $this->domxml->create_element("render_fib");
		$qtiRenderFib->set_attribute("fibtype", "String");
		$qtiRenderFib->set_attribute("prompt", "Box");
		$qtiResponseLabel = $this->domxml->create_element("response_label");
		$qtiResponseLabel->set_attribute("ident", "A");
		$qtiRenderFib->append_child($qtiResponseLabel);
		$qtiResponseStr->append_child($qtiRenderFib);
		if ($this->getMaxNumOfChars() > 0)
		{
			$qtiRenderFib->set_attribute("maxchars", $this->getMaxNumOfChars());
		}
		$solution = $this->getSuggestedSolution(0);
		if (count($solution))
		{
			if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches))
			{
				$qtiMaterial = $this->domxml->create_element("material");
				$qtiMaterial->set_attribute("label", "suggested_solution");
				$qtiMatText = $this->domxml->create_element("mattext");
				$intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
				if (strcmp($matches[1], "") != 0)
				{
					$intlink = $solution["internal_link"];
				}
				$qtiMatTextText = $this->domxml->create_text_node($intlink);
				$qtiMatText->append_child($qtiMatTextText);
				$qtiMaterial->append_child($qtiMatText);
				$qtiResponseStr->append_child($qtiMaterial);
			}
		}
		$qtiFlow->append_child($qtiResponseStr);
		$qtiPresentation->append_child($qtiFlow);
		$qtiIdent->append_child($qtiPresentation);
		// PART II: qti resprocessing
		$qtiResprocessing = $this->domxml->create_element("resprocessing");
		$qtiResprocessing->set_attribute("scoremodel", "HumanRater");
		$qtiOutcomes = $this->domxml->create_element("outcomes");
		$qtiDecvar = $this->domxml->create_element("decvar");
		$qtiDecvar->set_attribute("varname", "WritingScore");
		$qtiDecvar->set_attribute("vartype", "Integer");
		$qtiDecvar->set_attribute("minvalue", "0");
		$qtiDecvar->set_attribute("maxvalue", $this->getPoints());
		$qtiOutcomes->append_child($qtiDecvar);
		$qtiResprocessing->append_child($qtiOutcomes);

		$qtiOther = $this->domxml->create_element("other");
		$qtiOtherText = $this->domxml->create_text_node("tutor rated");
		$qtiOther->append_child($qtiOtherText);
		$qtiConditionVar = $this->domxml->create_element("conditionvar");
		$qtiConditionVar->append_child($qtiOther);
		$qtiRespCondition = $this->domxml->create_element("respcondition");
		$qtiRespCondition->append_child($qtiConditionVar);
		$qtiResprocessing->append_child($qtiRespCondition);

		$qtiIdent->append_child($qtiResprocessing);

		$xml = $this->domxml->dump_mem(true);
		if (!$a_include_header)
		{
			$pos = strpos($xml, "?>");
			$xml = substr($xml, $pos + 2);
		}
		return $xml;
	}

	/**
	* Saves a ASS_TextQuestion object to a database
	*
	* Saves a ASS_TextQuestion object to a database
	*
	* @param object $db A pear DB object
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		global $ilias;

		$complete = 0;
		if ($this->isComplete())
		{
			$complete = 1;
		}
		$db = & $ilias->db;

		$estw_time = $this->getEstimatedWorkingTime();
		$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);

		if ($original_id)
		{
			$original_id = $db->quote($original_id);
		}
		else
		{
			$original_id = "NULL";
		}

		if ($this->id == -1)
		{
			// Neuen Datensatz schreiben
			$now = getdate();
			$question_type = $this->getQuestionType();
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, points, question_text, working_time, maxNumOfChars, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($question_type),
				$db->quote($this->obj_id),
				$db->quote($this->title),
				$db->quote($this->comment),
				$db->quote($this->author),
				$db->quote($this->owner),
				$db->quote($this->getPoints() . ""),
				$db->quote($this->question),
				$db->quote($estw_time),
				$db->quote($this->getMaxNumOfChars()),
				$db->quote("$complete"),
				$db->quote($created),
				$original_id
			);
			$result = $db->query($query);
			
			if ($result == DB_OK)
			{
				$this->id = $this->ilias->db->getLastInsertId();

				// create page object of question
				$this->createPageObject();

				// Falls die Frage in einen Test eingefügt werden soll, auch diese Verbindung erstellen
				if ($this->getTestId() > 0)
				{
					$this->insertIntoTest($this->getTestId());
				}
			}
		}
		else
		{
			// Vorhandenen Datensatz aktualisieren
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, points = %s, question_text = %s, working_time=%s, maxNumOfChars = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title),
				$db->quote($this->comment),
				$db->quote($this->author),
				$db->quote($this->getPoints() . ""),
				$db->quote($this->question),
				$db->quote($estw_time),
				$db->quote($this->getMaxNumOfChars()),
				$db->quote("$complete"),
				$db->quote($this->id)
			);
			$result = $db->query($query);
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a ASS_TextQuestion object from a database
	*
	* Loads a ASS_TextQuestion object from a database
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the text question in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilias;

		$db = & $ilias->db;
		$query = sprintf("SELECT * FROM qpl_questions WHERE question_id = %s",
		$db->quote($question_id));
		$result = $db->query($query);
		if (strcmp(strtolower(get_class($result)), db_result) == 0)
		{
			if ($result->numRows() == 1)
			{
				$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->id = $question_id;
				$this->title = $data->title;
				$this->comment = $data->comment;
				$this->solution_hint = $data->solution_hint;
				$this->original_id = $data->original_id;
				$this->obj_id = $data->obj_fi;
				$this->author = $data->author;
				$this->owner = $data->owner;
				$this->question = $data->question_text;
				$this->maxNumOfChars = $data->maxNumOfChars;
				$this->points = $data->points;
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}
		}
		parent::loadFromDb($question_id);
	}

	/**
	* Duplicates an ASS_TextQuestion
	*
	* Duplicates an ASS_TextQuestion
	*
	* @access public
	*/
	function duplicate($for_test = true, $title = "", $author = "", $owner = "")
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		// duplicate the question in database
		$clone = $this;
		include_once ("./assessment/classes/class.assQuestion.php");
		$original_id = ASS_Question::_getOriginalId($this->id);
		$clone->id = -1;
		if ($title)
		{
			$clone->setTitle($title);
		}

		if ($author)
		{
			$clone->setAuthor($author);
		}
		if ($owner)
		{
			$clone->setOwner($owner);
		}

		if ($for_test)
		{
			$clone->saveToDb($original_id);
		}
		else
		{
			$clone->saveToDb();
		}

		// copy question page content
		$clone->copyPageOfQuestion($original_id);

		return $clone->id;
	}

	/**
	* Gets the text question
	*
	* Gets the question string of the ASS_TextQuestion object
	*
	* @return string The question string of the ASS_TextQuestion object
	* @access public
	* @see $question
	*/
	function get_question()
	{
		return $this->question;
	}

	/**
	* Sets the text question
	*
	* Sets the question string of the ASS_TextQuestion object
	*
	* @param string $question A string containing the text question
	* @access public
	* @see $question
	*/
	function set_question($question = "")
	{
		$this->question = $question;
	}

	/**
	* Gets the maximum number of characters for the text solution
	*
	* Gets the maximum number of characters for the text solution
	*
	* @return integer The maximum number of characters for the text solution
	* @access public
	* @see $maxNumOfChars
	*/
	function getMaxNumOfChars()
	{
		if (strcmp($this->maxNumOfChars, "") == 0)
		{
			return 0;
		}
		else
		{
			return $this->maxNumOfChars;
		}
	}

	/**
	* Sets the maximum number of characters for the text solution
	*
	* Sets the maximum number of characters for the text solution
	*
	* @param integer $maxchars The maximum number of characters for the text solution
	* @access public
	* @see $maxNumOfChars
	*/
	function setMaxNumOfChars($maxchars = 0)
	{
		$this->maxNumOfChars = $maxchars;
	}

	/**
	* Returns the maximum points, a learner can reach answering the question
	*
	* Returns the maximum points, a learner can reach answering the question
	*
	* @access public
	* @see $points
	*/
	function getMaximumPoints()
	{
		return $this->points;
	}

	/**
	* Sets the points, a learner has reached answering the question
	*
	* Sets the points, a learner has reached answering the question
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @param integer $points The points the user has reached answering the question
	* @return boolean true on success, otherwise false
	* @access public
	*/
	function setReachedPoints($user_id, $test_id, $points)
	{
		if (($points > 0) && ($points <= $this->getPoints()))
		{
			$query = sprintf("UPDATE tst_test_result SET points = %s WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
				$this->ilias->db->quote($points . ""),
				$this->ilias->db->quote($user_id . ""),
				$this->ilias->db->quote($test_id . ""),
				$this->ilias->db->quote($this->getId() . "")
			);
			$result = $this->ilias->db->query($query);
			return true;
		}
			else
		{
			return false;
		}
	}

	/**
	* Sets the points, a learner has reached answering the question
	* Additionally objective results are updated
	*
	* Sets the points, a learner has reached answering the question
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @param integer $points The points the user has reached answering the question
	* @return boolean true on success, otherwise false
	* @access public
	*/
	function _setReachedPoints($user_id, $test_id, $question_id, $points, $maxpoints)
	{
		global $ilDB;
		
		if (($points > 0) && ($points <= $maxpoints))
		{
			$query = sprintf("UPDATE tst_test_result SET points = %s WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
				$ilDB->quote($points . ""),
				$ilDB->quote($user_id . ""),
				$ilDB->quote($test_id . ""),
				$ilDB->quote($question_id . "")
			);
			$result = $this->ilias->db->query($query);

			// finally update objective result
			include_once 'course/classes/class.ilCourseObjectiveResult.php';

			ilCourseObjectiveResult::_updateUserResult($user_id,$question_id,$points);


			return true;
		}
			else
		{
			return false;
		}
	}
	
	/**
	* Returns the points, a learner has reached answering the question
	*
	* Returns the points, a learner has reached answering the question
	* The points are calculated from the given answers including checks
	* for all special scoring options in the test container.
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @access public
	*/
	function calculateReachedPoints($user_id, $test_id)
	{
		global $ilDB;

		$points = 0;
		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
			$this->ilias->db->quote($user_id),
			$this->ilias->db->quote($test_id),
			$this->ilias->db->quote($this->getId())
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			if ($row["points"])
			{
				$points = $row["points"];
			}
		}

		// check for special scoring options in test
		$query = sprintf("SELECT * FROM tst_tests WHERE test_id = %s",
			$ilDB->quote($test_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			if ($row["count_system"] == 1)
			{
				if ($points != $this->getMaximumPoints())
				{
					$points = 0;
				}
			}
		}
		else
		{
			$points = 0;
		}
		return $points;
	}

	/**
	* Returns the evaluation data, a learner has entered to answer the question
	*
	* Returns the evaluation data, a learner has entered to answer the question
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @access public
	*/
	function getReachedInformation($user_id, $test_id)
	{
		$found_values = array();
		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
		$this->ilias->db->quote($user_id),
		$this->ilias->db->quote($test_id),
		$this->ilias->db->quote($this->getId())
		);
		$result = $this->ilias->db->query($query);
		$user_result = array();
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_result = array(
				"value" => $data->value1
			);
		}
		return $user_result;
	}

	/**
	* Saves the learners input of the question to the database
	*
	* Saves the learners input of the question to the database
	*
	* @param integer $test_id The database id of the test containing this question
  * @return boolean Indicates the save status (true if saved successful, false otherwise)
	* @access public
	* @see $answers
	*/
	function saveWorkingData($test_id, $limit_to = LIMIT_NO_LIMIT)
	{
		global $ilDB;
		global $ilUser;

		$db =& $ilDB->db;

		$query = sprintf("DELETE FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
			$db->quote($ilUser->id),
			$db->quote($test_id),
			$db->quote($this->getId())
		);
		$result = $db->query($query);

		$text = ilUtil::stripSlashes($_POST["TEXT"]);
		if ($this->getMaxNumOfChars())
		{
			$text = substr($text, 0, $this->getMaxNumOfChars()); 
		}
		$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL, NULL)",
			$db->quote($ilUser->id),
			$db->quote($test_id),
			$db->quote($this->getId()),
			$db->quote($text)
		);
		$result = $db->query($query);
    parent::saveWorkingData($test_id);
		return true;
	}

	function syncWithOriginal()
	{
		global $ilias;
		if ($this->original_id)
		{
			$complete = 0;
			if ($this->isComplete())
			{
				$complete = 1;
			}
			$db = & $ilias->db;
	
			$estw_time = $this->getEstimatedWorkingTime();
			$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time=%s, maxNumOfChars = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title. ""),
				$db->quote($this->comment. ""),
				$db->quote($this->author. ""),
				$db->quote($this->question. ""),
				$db->quote($estw_time. ""),
				$db->quote($this->maxNumOfChars. ""),
				$db->quote($complete. ""),
				$db->quote($this->original_id. "")
			);
			$result = $db->query($query);

			parent::syncWithOriginal();
		}
	}

	function createRandomSolution($test_id, $user_id)
	{
	}

	/**
	* Returns the question type of the question
	*
	* Returns the question type of the question
	*
	* @return integer The question type of the question
	* @access public
	*/
	function getQuestionType()
	{
		return 8;
	}
}

?>
