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
require_once "./assessment/classes/class.assAnswerOrdering.php";

define ("OQ_PICTURES", 0);
define ("OQ_TERMS", 1);

/**
* Class for ordering questions
*
* ASS_OrderingQuestion is a class for ordering questions.
*
* @author		Helmut Schottm�ller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assOrderingQuestion.php
* @modulegroup   Assessment
*/
class ASS_OrderingQuestion extends ASS_Question
{
	/**
	* The question text
	*
	* The question text of the ordering question.
	*
	* @var string
	*/
	var $question;

	/**
	* The possible answers of the ordering question
	*
	* $answers is an array of the predefined answers of the ordering question
	*
	* @var array
	*/
	var $answers;

	/**
	* Type of ordering question
	*
	* There are two possible types of ordering questions: Ordering terms (=1)
	* and Ordering pictures (=0).
	*
	* @var integer
	*/
	var $ordering_type;

	/**
	* Points for solving the ordering question
	*
	* Enter the number of points the user gets when he/she enters the correct order of the ordering
	* question. This value overrides the point values of single answers when set different
	* from zero.
	*
	* @var double
	*/
	var $points;

	/**
	* ASS_OrderingQuestion constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_OrderingQuestion object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $materials An uri to additional materials
	* @param string $question The question string of the ordering test
	* @param points double The points for solving the ordering question
	* @access public
	*/
	function ASS_OrderingQuestion (
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$points = 0.0,
		$ordering_type = OQ_TERMS
	)
	{
		$this->ASS_Question($title, $comment, $author, $owner);
		$this->answers = array();
		$this->question = $question;
		$this->points = $points;
		$this->ordering_type = $ordering_type;
	}

	/**
	* Returns true, if a ordering question is complete for use
	*
	* Returns true, if a ordering question is complete for use
	*
	* @return boolean True, if the ordering question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and (count($this->answers)))
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
	function to_xml($a_include_header = true, $a_include_binary = true, $a_shuffle = false)
	{
		if (!empty($this->domxml))
		{
			$this->domxml->free();
		}
		$xml_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<questestinterop></questestinterop>\n";
		$this->domxml = domxml_open_mem($xml_header);
		$root = $this->domxml->document_element();
		// qti ident
		$qtiIdent = $this->domxml->create_element("item");
		$qtiIdent->set_attribute("ident", $this->getId());
		$qtiIdent->set_attribute("title", $this->getTitle());
		$root->append_child($qtiIdent);
		// add qti comment
		$qtiComment = $this->domxml->create_element("qticomment");
		$qtiCommentText = $this->domxml->create_text_node($this->getComment());
		$qtiComment->append_child($qtiCommentText);
		$qtiIdent->append_child($qtiComment);
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
		// add answers to presentation
		$qtiResponseLid = $this->domxml->create_element("response_lid");
		if ($this->get_ordering_type() == OQ_PICTURES)
		{
			$qtiResponseLid->set_attribute("ident", "OQP");
			$qtiResponseLid->set_attribute("rcardinality", "Ordered");
		}
			else
		{
			$qtiResponseLid->set_attribute("ident", "OQT");
			$qtiResponseLid->set_attribute("rcardinality", "Ordered");
		}
		$qtiRenderChoice = $this->domxml->create_element("render_choice");
		// shuffle output
		if ($this->getShuffle())
		{
			$qtiRenderChoice->set_attribute("shuffle", "yes");
		}
		else
		{
			$qtiRenderChoice->set_attribute("shuffle", "no");
		}

		// shuffle
		$akeys = array_keys($this->answers);
		if ($this->getshuffle() && $a_shuffle)
		{
			$akeys = $this->pcArrayShuffle($akeys);
		}

		// add answers
		foreach ($akeys as $index)
		{
			$answer = $this->answers[$index];

			$qtiResponseLabel = $this->domxml->create_element("response_label");
			$qtiResponseLabel->set_attribute("ident", $index);
			$qtiMaterial = $this->domxml->create_element("material");
			if ($this->get_ordering_type() == OQ_PICTURES)
			{
				$qtiMatImage = $this->domxml->create_element("matimage");
				$qtiMatImage->set_attribute("imagtype", "image/jpeg");
				$qtiMatImage->set_attribute("label", $answer->get_answertext());
				$qtiMatImage->set_attribute("embedded", "base64");
				$imagepath = $this->getImagePath() . $answer->get_answertext();
				$fh = fopen($imagepath, "rb");
				if ($fh == false)
				{
					global $ilErr;
					$ilErr->raiseError($this->lng->txt("error_open_image_file"), $ilErr->WARNING);
					return;
				}
				$imagefile = fread($fh, filesize($imagepath));
				fclose($fh);
				$base64 = base64_encode($imagefile);
				$qtiBase64Data = $this->domxml->create_text_node($base64);
				$qtiMatImage->append_child($qtiBase64Data);
				$qtiMaterial->append_child($qtiMatImage);
			}
			else
			{
				$qtiMatText = $this->domxml->create_element("mattext");
				$qtiMatTextText = $this->domxml->create_text_node($answer->get_answertext());
				$qtiMatText->append_child($qtiMatTextText);
				$qtiMaterial->append_child($qtiMatText);
			}
			$qtiResponseLabel->append_child($qtiMaterial);
			$qtiRenderChoice->append_child($qtiResponseLabel);
		}
		$qtiResponseLid->append_child($qtiRenderChoice);
		$qtiFlow->append_child($qtiResponseLid);
		$qtiPresentation->append_child($qtiFlow);
		$qtiIdent->append_child($qtiPresentation);

		// PART II: qti resprocessing
		$qtiResprocessing = $this->domxml->create_element("resprocessing");
		$qtiOutcomes = $this->domxml->create_element("outcomes");
		$qtiDecvar = $this->domxml->create_element("decvar");
		$qtiOutcomes->append_child($qtiDecvar);
		$qtiResprocessing->append_child($qtiOutcomes);
		// add response conditions
		foreach ($this->answers as $index => $answer)
		{
			$qtiRespcondition = $this->domxml->create_element("respcondition");
			$qtiRespcondition->set_attribute("continue", "Yes");
			// qti conditionvar
			$qtiConditionvar = $this->domxml->create_element("conditionvar");
			$qtiVarequal = $this->domxml->create_element("varequal");
			if ($this->get_ordering_type() == OQ_PICTURES)
			{
				$qtiVarequal->set_attribute("respident", "OQP");
			}
				else
			{
				$qtiVarequal->set_attribute("respident", "OQT");
			}
			$qtiVarequal->set_attribute("index", $answer->get_solution_order());
			$qtiVarequalText = $this->domxml->create_text_node($index);
			$qtiVarequal->append_child($qtiVarequalText);
			$qtiConditionvar->append_child($qtiVarequal);
			// qti setvar
			$qtiSetvar = $this->domxml->create_element("setvar");
			$qtiSetvar->set_attribute("action", "Add");
			$qtiSetvarText = $this->domxml->create_text_node($answer->get_points());
			$qtiSetvar->append_child($qtiSetvarText);
			// qti displayfeedback
			$qtiDisplayfeedback = $this->domxml->create_element("displayfeedback");
			$qtiDisplayfeedback->set_attribute("feedbacktype", "Response");
			$qtiDisplayfeedback->set_attribute("linkrefid", "link_$index");
			$qtiRespcondition->append_child($qtiConditionvar);
			$qtiRespcondition->append_child($qtiSetvar);
			$qtiRespcondition->append_child($qtiDisplayfeedback);
			$qtiResprocessing->append_child($qtiRespcondition);
		}
		$qtiIdent->append_child($qtiResprocessing);

		// PART III: qti itemfeedback
		foreach ($this->answers as $index => $answer)
		{
			$qtiItemfeedback = $this->domxml->create_element("itemfeedback");
			$qtiItemfeedback->set_attribute("ident", "link_$index");
			$qtiItemfeedback->set_attribute("view", "All");
			// qti flow_mat
			$qtiFlowmat = $this->domxml->create_element("flow_mat");
			$qtiMaterial = $this->domxml->create_element("material");
			$qtiMattext = $this->domxml->create_element("mattext");
			// Insert response text for right/wrong answers here!!!
			$qtiMattextText = $this->domxml->create_text_node("");
			$qtiMattext->append_child($qtiMattextText);
			$qtiMaterial->append_child($qtiMattext);
			$qtiFlowmat->append_child($qtiMaterial);
			$qtiItemfeedback->append_child($qtiFlowmat);
			$qtiIdent->append_child($qtiItemfeedback);
		}

		$xml = $this->domxml->dump_mem(true);
		if (!$a_include_header)
		{
			$pos = strpos($xml, "?>");
			$xml = substr($xml, $pos + 2);
		}
//echo htmlentities($xml);
		return $xml;

	}


	/**
	* Saves a ASS_OrderingQuestion object to a database
	*
	* Saves a ASS_OrderingQuestion object to a database (experimental)
	*
	* @param object $db A pear DB object
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		global $ilias;

		$db =& $ilias->db;
		$complete = 0;
		if ($this->isComplete())
		{
			$complete = 1;
		}

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
			$question_type = 5;
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, working_time, ordering_type, points, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($question_type),
				$db->quote($this->obj_id),
				$db->quote($this->title),
				$db->quote($this->comment),
				$db->quote($this->author),
				$db->quote($this->owner),
				$db->quote($this->question),
				$db->quote($estw_time),
				$db->quote($this->ordering_type),
				$db->quote($this->points),
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

				// Falls die Frage in einen Test eingef�gt werden soll, auch diese Verbindung erstellen
				if ($this->getTestId() > 0)
				{
					$this->insertIntoTest($this->getTestId());
				}
			}
		}
		else
		{
			// Vorhandenen Datensatz aktualisieren
			$query = sprintf("UPDATE qpl_questions SET title = %s, comment = %s, author = %s, question_text = %s, working_time = %s, ordering_type = %s, points = %s, complete = %s WHERE question_id = %s",
				$db->quote($this->title),
				$db->quote($this->comment),
				$db->quote($this->author),
				$db->quote($this->question),
				$db->quote($estw_time),
				$db->quote($this->ordering_type),
				$db->quote($this->points),
				$db->quote("$complete"),
				$db->quote($this->id)
			);
			$result = $db->query($query);
		}
		if ($result == DB_OK)
		{
			// saving material uris in the database
			$this->saveMaterialsToDb();

			// Antworten schreiben
			// alte Antworten l�schen
			$query = sprintf("DELETE FROM qpl_answers WHERE question_fi = %s",
				$db->quote($this->id)
			);
			$result = $db->query($query);

			// Anworten wegschreiben
			foreach ($this->answers as $key => $value)
			{
				$answer_obj = $this->answers[$key];
				$query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, answertext, points, aorder, solution_order, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
					$db->quote($this->id),
					$db->quote($answer_obj->get_answertext() . ""),
					$db->quote($answer_obj->get_points() . ""),
					$db->quote($answer_obj->get_order() . ""),
					$db->quote($answer_obj->get_solution_order() . "")
				);
				$answer_result = $db->query($query);
			}
		}
	}

	/**
	* Loads a ASS_OrderingQuestion object from a database
	*
	* Loads a ASS_OrderingQuestion object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilias;
		$db =& $ilias->db;

		$query = sprintf("SELECT * FROM qpl_questions WHERE question_id = %s",
			$db->quote($question_id)
		);
		$result = $db->query($query);
		if (strcmp(strtolower(get_class($result)), db_result) == 0)
		{
			if ($result->numRows() == 1)
			{
				$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->id = $question_id;
				$this->title = $data->title;
				$this->obj_id = $data->obj_fi;
				$this->comment = $data->comment;
				$this->author = $data->author;
				$this->owner = $data->owner;
				$this->question = $data->question_text;
				$this->ordering_type = $data->ordering_type;
				$this->points = $data->points;
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}

			// loads materials uris from database
			$this->loadMaterialFromDb($question_id);

			$query = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s ORDER BY aorder ASC",
				$db->quote($question_id)
			);
			$result = $db->query($query);
			if (strcmp(strtolower(get_class($result)), db_result) == 0)
			{
				while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
				{
					array_push($this->answers, new ASS_AnswerOrdering($data->answertext, $data->points, $data->aorder, $data->solution_order));
				}
			}
		}
	}

	/**
	* Duplicates an ASS_OrderingQuestion
	*
	* Duplicates an ASS_OrderingQuestion
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
		$original_id = $this->id;
		if ($original_id <= 0)
		{
			$original_id = "";
		}
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

		// duplicate the materials
		$clone->duplicateMaterials($original_id);
		// duplicate the image
		$clone->duplicateImages($original_id);
		return $clone->id;
	}

	function duplicateImages($question_id)
	{
		if ($this->get_ordering_type() == OQ_PICTURES)
		{
			$imagepath = $this->getImagePath();
			$imagepath_original = preg_replace("/([^\d])$this->id([^\d])/", "\${1}$question_id\${2}", $imagepath);
			if (!file_exists($imagepath)) {
				ilUtil::makeDirParents($imagepath);
			}
			foreach ($this->answers as $answer)
			{
				$filename = $answer->get_answertext();
				if (!copy($imagepath_original . $filename, $imagepath . $filename)) {
					print "image could not be duplicated!!!! ";
				}
				if (!copy($imagepath_original . $filename . ".thumb.jpg", $imagepath . $filename . ".thumb.jpg")) {
					print "image thumbnail could not be duplicated!!!! ";
				}
			}
		}
	}

	/**
	* Sets the ordering question text
	*
	* Sets the ordering question text
	*
	* @param string $question The question text
	* @access public
	* @see $question
	*/
	function set_question($question = "")
	{
		$this->question = $question;
	}

	/**
	* Sets the ordering question type
	*
	* Sets the ordering question type
	*
	* @param integer $ordering_type The question ordering type
	* @access public
	* @see $ordering_type
	*/
	function set_ordering_type($ordering_type = OQ_TERMS)
	{
		$this->ordering_type = $ordering_type;
	}

	/**
	* Returns the question text
	*
	* Returns the question text
	*
	* @return string The question text string
	* @access public
	* @see $question
	*/
	function get_question()
	{
		return $this->question;
	}

	/**
	* Returns the ordering question type
	*
	* Returns the ordering question type
	*
	* @return integer The ordering question type
	* @access public
	* @see $ordering_type
	*/
	function get_ordering_type()
	{
		return $this->ordering_type;
	}

	/**
	* Adds an answer for an ordering question
	*
	* Adds an answer for an ordering choice question. The students have to fill in an order for the answer.
	* The answer is an ASS_AnswerOrdering object that will be created and assigned to the array $this->answers.
	*
	* @param string $answertext The answer text
	* @param double $points The points for selecting the answer (even negative points can be used)
	* @param integer $order A possible display order of the answer
	* @param integer $solution_order An unique integer value representing the correct
	* order of that answer in the solution of a question
	* @access public
	* @see $answers
	* @see ASS_AnswerOrdering
	*/
	function add_answer(
		$answertext = "",
		$points = 0.0,
		$order = 0,
		$solution_order = 0
	)
	{
		$found = -1;
		foreach ($this->answers as $key => $value)
		{
			if ($value->get_order() == $order)
			{
				$found = $order;
			}
		}
		if ($found >= 0)
		{
			// Antwort einf�gen
			$answer = new ASS_AnswerOrdering($answertext, $points, $found, $solution_order);
			array_push($this->answers, $answer);
			for ($i = $found + 1; $i < count($this->answers); $i++)
			{
				$this->answers[$i] = $this->answers[$i-1];
			}
			$this->answers[$found] = $answer;
		}
		else
		{
			// Anwort anh�ngen
			$answer = new ASS_AnswerOrdering($answertext, $points,
				count($this->answers), $solution_order);
			array_push($this->answers, $answer);
		}
	}

	/**
	* Returns an ordering answer
	*
	* Returns an ordering answer with a given index. The index of the first
	* answer is 0, the index of the second answer is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th answer
	* @return object ASS_AnswerOrdering-Object
	* @access public
	* @see $answers
	*/
	function get_answer($index = 0)
	{
		if ($index < 0) return NULL;
		if (count($this->answers) < 1) return NULL;
		if ($index >= count($this->answers)) return NULL;
		return $this->answers[$index];
	}

	/**
	* Deletes an answer
	*
	* Deletes an answer with a given index. The index of the first
	* answer is 0, the index of the second answer is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th answer
	* @access public
	* @see $answers
	*/
	function delete_answer($index = 0)
	{
		if ($index < 0)
		{
			return;
		}
		if (count($this->answers) < 1)
		{
			return;
		}
		if ($index >= count($this->answers))
		{
			return;
		}
		unset($this->answers[$index]);
		$this->answers = array_values($this->answers);
		for ($i = 0; $i < count($this->answers); $i++)
		{
			if ($this->answers[$i]->get_order() > $index)
			{
				$this->answers[$i]->set_order($i);
			}
		}
	}

	/**
	* Deletes all answers
	*
	* Deletes all answers
	*
	* @access public
	* @see $answers
	*/
	function flush_answers()
	{
		$this->answers = array();
	}

	/**
	* Returns the number of answers
	*
	* Returns the number of answers
	*
	* @return integer The number of answers of the ordering question
	* @access public
	* @see $answers
	*/
	function get_answer_count()
	{
		return count($this->answers);
	}

	/**
	* Gets the points
	*
	* Gets the points for entering the correct order of the ASS_OrderingQuestion object
	*
	* @return double The points for entering the correct order of the ordering question
	* @access public
	* @see $points
	*/
	function get_points()
	{
		return $this->points;
	}

	/**
	* Sets the points
	*
	* Sets the points for entering the correct order of the ASS_OrderingQuestion object
	*
	* @param points double The points for entering the correct order of the ordering question
	* @access public
	* @see $points
	*/
	function set_points($points = 0.0)
	{
		$this->points = $points;
	}

	/**
	* Returns the maximum solution order
	*
	* Returns the maximum solution order of all ordering answers
	*
	* @return integer The maximum solution order of all ordering answers
	* @access public
	* @see $points
	*/
	function get_max_solution_order()
	{
		if (count($this->answers) == 0)
		{
			$max = 0;
		}
		else
		{
			$max = $this->answers[0]->get_solution_order();
		}
		foreach ($this->answers as $key => $value)
		{
			if ($value->get_solution_order() > $max)
			{
				$max = $value->get_solution_order();
			}
		}
		return $max;
	}

	/**
	* Returns the points, a learner has reached answering the question
	*
	* Returns the points, a learner has reached answering the question
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @access public
	*/
	function getReachedPoints($user_id, $test_id)
	{
		$found_value1 = array();
		$found_value2 = array();
		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
			$this->ilias->db->quote($user_id),
			$this->ilias->db->quote($test_id),
			$this->ilias->db->quote($this->getId())
		);
		$result = $this->ilias->db->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($found_value1, $data->value1);
			array_push($found_value2, $data->value2);
		}
		$points = 0;
		foreach ($found_value1 as $key => $value)
		{
			if ($this->answers[$value]->get_solution_order() == $found_value2[$key])
			{
				$points += $this->answers[$value]->get_points();
			}
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
		$found_value1 = array();
		$found_value2 = array();
		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
			$this->ilias->db->quote($user_id),
			$this->ilias->db->quote($test_id),
			$this->ilias->db->quote($this->getId())
		);
		$result = $this->ilias->db->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($found_value1, $data->value1);
			array_push($found_value2, $data->value2);
		}
		$counter = 1;
		$user_result = array();
		foreach ($found_value1 as $key => $value)
		{
			$solution = array(
				"order" => "$counter",
				"points" => 0,
				"true" => 0,
				"value" => "",
			);
			if ($this->answers[$value]->get_solution_order() == $found_value2[$key])
			{
				$points += $this->answers[$value]->get_points();
				$solution["points"] = $this->answers[$value]->get_points();
				$solution["value"] = $this->answers[$value]->get_answertext();
				$solution["true"] = 1;
			}
			$counter++;
			array_push($user_result, $solution);
		}
		return $user_result;
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
		$points = 0;
		foreach ($this->answers as $key => $value)
		{
			$points += $value->get_points();
		}
		return $points;
	}

	/**
	* Sets the image file
	*
	* Sets the image file and uploads the image to the object's image directory.
	*
	* @param string $image_filename Name of the original image file
	* @param string $image_tempfilename Name of the temporary uploaded image file
	* @access public
	*/
	function set_image_file($image_filename, $image_tempfilename = "")
	{
		if (!empty($image_tempfilename))
		{
			$imagepath = $this->getImagePath();
			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			if (!move_uploaded_file($image_tempfilename, $imagepath . $image_filename))
			{
				print "image not uploaded!!!! ";
			}
			else
			{
				$thumbpath = $imagepath . $image_filename . "." . "thumb.jpg";
				ilUtil::convertImage($imagepath.$image_filename, $thumbpath, "JPEG", 100);
			}
		}
	}

	/**
	* Saves the learners input of the question to the database
	*
	* Saves the learners input of the question to the database
	*
	* @param integer $test_id The database id of the test containing this question
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

		foreach ($_POST as $key => $value)
		{
			if (preg_match("/order_(\d+)/", $key, $matches))
			{
				$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
					$db->quote($ilUser->id),
					$db->quote($test_id),
					$db->quote($this->getId()),
					$db->quote($matches[1]),
					$db->quote($value)
				);
				$result = $db->query($query);
			}
		}
	//    parent::saveWorkingData($limit_to);
	}
}

?>
