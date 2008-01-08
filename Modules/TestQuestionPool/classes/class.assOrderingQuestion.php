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

include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for ordering questions
*
* assOrderingQuestion is a class for ordering questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assOrderingQuestion extends assQuestion
{
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
	* assOrderingQuestion constructor
	*
	* The constructor takes possible arguments an creates an instance of the assOrderingQuestion object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the ordering test
	* @access public
	*/
	function assOrderingQuestion (
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$ordering_type = OQ_TERMS
	)
	{
		$this->assQuestion($title, $comment, $author, $owner, $question);
		$this->answers = array();
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
		if (($this->title) and ($this->author) and ($this->question) and (count($this->answers)) and ($this->getMaximumPoints() > 0))
		{
			return true;
		}
			else
		{
			return false;
		}
	}

	/**
	* Saves a assOrderingQuestion object to a database
	*
	* Saves a assOrderingQuestion object to a database (experimental)
	*
	* @param object $db A pear DB object
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		global $ilDB;

		$complete = 0;
		if ($this->isComplete())
		{
			$complete = 1;
		}

		$estw_time = $this->getEstimatedWorkingTime();
		$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);

		if ($original_id)
		{
			$original_id = $ilDB->quote($original_id);
		}
		else
		{
			$original_id = "NULL";
		}

		include_once("./Services/RTE/classes/class.ilRTE.php");
		if ($this->id == -1)
		{
			// Neuen Datensatz schreiben
			$now = getdate();
			$question_type = $this->getQuestionTypeID();
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, working_time, points, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($question_type . ""),
				$ilDB->quote($this->obj_id . ""),
				$ilDB->quote($this->title . ""),
				$ilDB->quote($this->comment . ""),
				$ilDB->quote($this->author . ""),
				$ilDB->quote($this->owner . ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->question, 0)),
				$ilDB->quote($estw_time . ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($complete . ""),
				$ilDB->quote($created . ""),
				$original_id
			);
			$result = $ilDB->query($query);
			if ($result == DB_OK)
			{
				$this->id = $ilDB->getLastInsertId();
				$query = sprintf("INSERT INTO qpl_question_ordering (question_fi, ordering_type) VALUES (%s, %s)",
					$ilDB->quote($this->id . ""),
					$ilDB->quote($this->ordering_type . "")
				);
				$ilDB->query($query);

				// create page object of question
				$this->createPageObject();

				if ($this->getTestId() > 0)
				{
					$this->insertIntoTest($this->getTestId());
				}
			}
		}
		else
		{
			// Vorhandenen Datensatz aktualisieren
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time = %s, points = %s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title . ""),
				$ilDB->quote($this->comment . ""),
				$ilDB->quote($this->author . ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->question, 0)),
				$ilDB->quote($estw_time . ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($complete . ""),
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_ordering SET ordering_type = %s WHERE question_fi = %s",
				$ilDB->quote($this->ordering_type . ""),
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
		}
		if ($result == DB_OK)
		{
			// Antworten schreiben
			// alte Antworten löschen
			$query = sprintf("DELETE FROM qpl_answer_ordering WHERE question_fi = %s",
				$ilDB->quote($this->id)
			);
			$result = $ilDB->query($query);

			// Anworten wegschreiben
			foreach ($this->answers as $key => $value)
			{
				$answer_obj = $this->answers[$key];
				$query = sprintf("INSERT INTO qpl_answer_ordering (answer_id, question_fi, answertext, points, aorder, solution_order) VALUES (NULL, %s, %s, %s, %s, %s)",
					$ilDB->quote($this->id),
					$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($answer_obj->getAnswertext(), 0)),
					$ilDB->quote($answer_obj->getPoints() . ""),
					$ilDB->quote($answer_obj->getOrder() . ""),
					$ilDB->quote($answer_obj->getSolutionOrder() . "")
				);
				$answer_result = $ilDB->query($query);
			}
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a assOrderingQuestion object from a database
	*
	* Loads a assOrderingQuestion object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilDB;

    $query = sprintf("SELECT qpl_questions.*, qpl_question_ordering.* FROM qpl_questions, qpl_question_ordering WHERE question_id = %s AND qpl_questions.question_id = qpl_question_ordering.question_fi",
			$ilDB->quote($question_id)
		);
		$result = $ilDB->query($query);
		if (strcmp(strtolower(get_class($result)), db_result) == 0)
		{
			if ($result->numRows() == 1)
			{
				$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->id = $question_id;
				$this->title = $data->title;
				$this->obj_id = $data->obj_fi;
				$this->comment = $data->comment;
				$this->original_id = $data->original_id;
				$this->author = $data->author;
				$this->owner = $data->owner;
				include_once("./Services/RTE/classes/class.ilRTE.php");
				$this->question = ilRTE::_replaceMediaObjectImageSrc($data->question_text, 1);
				$this->solution_hint = $data->solution_hint;
				$this->ordering_type = $data->ordering_type;
				$this->points = $data->points;
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}

			$query = sprintf("SELECT * FROM qpl_answer_ordering WHERE question_fi = %s ORDER BY aorder ASC",
				$ilDB->quote($question_id)
			);
			$result = $ilDB->query($query);
			include_once "./Modules/TestQuestionPool/classes/class.assAnswerOrdering.php";
			if (strcmp(strtolower(get_class($result)), db_result) == 0)
			{
				while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
				{
					include_once("./Services/RTE/classes/class.ilRTE.php");
					$data->answertext = ilRTE::_replaceMediaObjectImageSrc($data->answertext, 1);
					array_push($this->answers, new ASS_AnswerOrdering($data->answertext, $data->points, $data->aorder, $data->solution_order));
				}
			}
		}
		parent::loadFromDb($question_id);
	}
	
	/**
	* Duplicates an assOrderingQuestion
	*
	* Duplicates an assOrderingQuestion
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
		$this_id = $this->getId();
		$clone = $this;
		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		$original_id = assQuestion::_getOriginalId($this->id);
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
		$clone->copyPageOfQuestion($this_id);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($this_id);
		// duplicate the generic feedback
		$clone->duplicateFeedbackGeneric($this_id);

		// duplicate the image
		$clone->duplicateImages($this_id);
		return $clone->id;
	}

	/**
	* Copies an assOrderingQuestion object
	*
	* Copies an assOrderingQuestion object
	*
	* @access public
	*/
	function copyObject($target_questionpool, $title = "")
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		// duplicate the question in database
		$clone = $this;
		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		$original_id = assQuestion::_getOriginalId($this->id);
		$clone->id = -1;
		$source_questionpool = $this->getObjId();
		$clone->setObjId($target_questionpool);
		if ($title)
		{
			$clone->setTitle($title);
		}
		
		$clone->saveToDb();

		// copy question page content
		$clone->copyPageOfQuestion($original_id);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($original_id);
		// duplicate the generic feedback
		$clone->duplicateFeedbackGeneric($original_id);

		// duplicate the image
		$clone->copyImages($original_id, $source_questionpool);
		return $clone->id;
	}
	
	function duplicateImages($question_id)
	{
		if ($this->getOrderingType() == OQ_PICTURES)
		{
			$imagepath = $this->getImagePath();
			$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
			if (!file_exists($imagepath)) {
				ilUtil::makeDirParents($imagepath);
			}
			foreach ($this->answers as $answer)
			{
				$filename = $answer->getAnswertext();
				if (!copy($imagepath_original . $filename, $imagepath . $filename)) {
					print "image could not be duplicated!!!! ";
				}
				if (!copy($imagepath_original . $filename . ".thumb.jpg", $imagepath . $filename . ".thumb.jpg")) {
					print "image thumbnail could not be duplicated!!!! ";
				}
			}
		}
	}

	function copyImages($question_id, $source_questionpool)
	{
		if ($this->getOrderingType() == OQ_PICTURES)
		{
			$imagepath = $this->getImagePath();
			$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
			$imagepath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $imagepath_original);
			if (!file_exists($imagepath)) {
				ilUtil::makeDirParents($imagepath);
			}
			foreach ($this->answers as $answer)
			{
				$filename = $answer->getAnswertext();
				if (!copy($imagepath_original . $filename, $imagepath . $filename)) {
					print "image could not be copied!!!! ";
				}
				if (!copy($imagepath_original . $filename . ".thumb.jpg", $imagepath . $filename . ".thumb.jpg")) {
					print "image thumbnail could not be copied!!!! ";
				}
			}
		}
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
	function setOrderingType($ordering_type = OQ_TERMS)
	{
		$this->ordering_type = $ordering_type;
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
	function getOrderingType()
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
	function addAnswer(
		$answertext = "",
		$points = 0.0,
		$order = 0,
		$solution_order = 0
	)
	{
		$found = -1;
		foreach ($this->answers as $key => $value)
		{
			if ($value->getOrder() == $order)
			{
				$found = $order;
			}
		}
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerOrdering.php";
		if ($found >= 0)
		{
			// Antwort einfügen
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
			// Anwort anhängen
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
	function getAnswer($index = 0)
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
	function deleteAnswer($index = 0)
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
			if ($this->answers[$i]->getOrder() > $index)
			{
				$this->answers[$i]->setOrder($i);
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
	function flushAnswers()
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
	function getAnswerCount()
	{
		return count($this->answers);
	}

	/**
	* Returns the maximum solution order
	*
	* Returns the maximum solution order of all ordering answers
	*
	* @return integer The maximum solution order of all ordering answers
	* @access public
	*/
	function getMaxSolutionOrder()
	{
		if (count($this->answers) == 0)
		{
			$max = 0;
		}
		else
		{
			$max = $this->answers[0]->getSolutionOrder();
		}
		foreach ($this->answers as $key => $value)
		{
			if ($value->getSolutionOrder() > $max)
			{
				$max = $value->getSolutionOrder();
			}
		}
		return $max;
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
	function calculateReachedPoints($active_id, $pass = NULL)
	{
		global $ilDB;
		
		$found_value1 = array();
		$found_value2 = array();
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}
		$query = sprintf("SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		$user_order = array();
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if ((strcmp($data->value1, "") != 0) && (strcmp($data->value2, "") != 0))
			{
				$user_order[$data->value2] = $data->value1;
			}
		}
		ksort($user_order);
		$user_order = array_values($user_order);
		$answer_order = array();
		foreach ($this->answers as $key => $answer)
		{
			$answer_order[$answer->getSolutionOrder()] = $key;
		}
		ksort($answer_order);
		$answer_order = array_values($answer_order);
		$points = 0;
		foreach ($answer_order as $index => $answer_id)
		{
			if (strcmp($user_order[$index], "") != 0)
			{
				if ($answer_id == $user_order[$index])
				{
					$points += $this->answers[$answer_id]->getPoints();
				}
			}
		}

		$points = parent::calculateReachedPoints($active_id, $pass = NULL, $points);
		return $points;
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
			$points += $value->getPoints();
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
	* @return integer An errorcode if the image upload fails, 0 otherwise
	* @access public
	*/
	function setImageFile($image_filename, $image_tempfilename = "")
	{
		$result = 0;
		if (!empty($image_tempfilename))
		{
			$imagepath = $this->getImagePath();
			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			if (!ilUtil::moveUploadedFile($image_tempfilename,$image_filename, $imagepath.$image_filename))
			{
				$result = 2;
			}
			else
			{
				include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
				$mimetype = ilObjMediaObject::getMimeType($imagepath . $image_filename);
				if (!preg_match("/^image/", $mimetype))
				{
					unlink($imagepath . $image_filename);
					$result = 1;
				}
				else
				{
					// create thumbnail file
					$thumbpath = $imagepath . $image_filename . "." . "thumb.jpg";
					ilUtil::convertImage($imagepath.$image_filename, $thumbpath, strtoupper($extension), 100);
				}
			}
		}
		return $result;
	}

	/**
	* Checks the data to be saved for consistency
	*
	* Checks the data to be saved for consistency
	*
  * @return boolean True, if the check was ok, False otherwise
	* @access public
	* @see $answers
	*/
	function checkSaveData()
	{
		$result = true;
		if ($this->getOutputType() == OUTPUT_JAVASCRIPT)
		{
			if (strlen($_POST["orderresult"]))
			{
				return $result;
			}
		}
		$order_values = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^order_(\d+)/", $key, $matches))
			{
				if (strcmp($value, "") != 0)
				{
					array_push($order_values, $value);
				}
			}
		}
		$check_order = array_flip($order_values);
		if (count($check_order) != count($order_values))
		{
			// duplicate order values!!!
			$result = false;
			ilUtil::sendInfo($this->lng->txt("duplicate_order_values_entered"), TRUE);
		}
		return $result;
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
	function saveWorkingData($active_id, $pass = NULL)
	{
		global $ilDB;
		global $ilUser;

		$saveWorkingDataResult = $this->checkSaveData();
		$entered_values = 0;
		if ($saveWorkingDataResult)
		{
			if (is_null($pass))
			{
				include_once "./Modules/Test/classes/class.ilObjTest.php";
				$pass = ilObjTest::_getPass($active_id);
			}

			$query = sprintf("DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($this->getId() . ""),
				$ilDB->quote($pass . "")
			);
			$result = $ilDB->query($query);
			if ($this->getOutputType() == OUTPUT_JAVASCRIPT)
			{
				$orderresult = $_POST["orderresult"];
				if (strlen($orderresult))
				{
					$orderarray = explode(":", $orderresult);
					$ordervalue = 1;
					foreach ($orderarray as $index)
					{
						$query = sprintf("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
							$ilDB->quote($active_id . ""),
							$ilDB->quote($this->getId() . ""),
							$ilDB->quote(trim($index) . ""),
							$ilDB->quote(trim($ordervalue) . ""),
							$ilDB->quote($pass . "")
						);
						$result = $ilDB->query($query);
						$ordervalue++;
						$entered_values++;
					}
				}
			}
			else
			{
				foreach ($_POST as $key => $value)
				{
					if (preg_match("/^order_(\d+)/", $key, $matches))
					{
						if (!(preg_match("/initial_value_\d+/", $value)))
						{
							if (strlen($value))
							{
								$query = sprintf("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
									$ilDB->quote($active_id . ""),
									$ilDB->quote($this->getId() . ""),
									$ilDB->quote($matches[1] . ""),
									$ilDB->quote($value . ""),
									$ilDB->quote($pass . "")
								);
								$result = $ilDB->query($query);
								$entered_values++;
							}
						}
					}
				}
			}
		}
		if ($entered_values)
		{
			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
			}
		}
		else
		{
			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_not_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
			}
		}
    parent::saveWorkingData($active_id, $pass);
		return $saveWorkingDataResult;
	}

	function syncWithOriginal()
	{
		global $ilDB;
		
		if ($this->original_id)
		{
			$complete = 0;
			if ($this->isComplete())
			{
				$complete = 1;
			}
			$estw_time = $this->getEstimatedWorkingTime();
			$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time = %s, points = %s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title . ""),
				$ilDB->quote($this->comment . ""),
				$ilDB->quote($this->author . ""),
				$ilDB->quote($this->question . ""),
				$ilDB->quote($estw_time . ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($complete . ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_ordering SET ordering_type = %s WHERE question_fi = %s",
				$ilDB->quote($this->ordering_type . ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);

			if ($result == DB_OK)
			{
				// write ansers
				// delete old answers
				$query = sprintf("DELETE FROM qpl_answer_ordering WHERE question_fi = %s",
					$ilDB->quote($this->original_id)
				);
				$result = $ilDB->query($query);
	
				foreach ($this->answers as $key => $value)
				{
					$answer_obj = $this->answers[$key];
					$query = sprintf("INSERT INTO qpl_answer_ordering (answer_id, question_fi, answertext, points, aorder, solution_order) VALUES (NULL, %s, %s, %s, %s, %s)",
						$ilDB->quote($this->original_id . ""),
						$ilDB->quote($answer_obj->getAnswertext() . ""),
						$ilDB->quote($answer_obj->getPoints() . ""),
						$ilDB->quote($answer_obj->getOrder() . ""),
						$ilDB->quote($answer_obj->getSolutionOrder() . "")
					);
					$answer_result = $ilDB->query($query);
				}
			}
			parent::syncWithOriginal();
		}
	}

	function pc_array_shuffle($array) {
		mt_srand((double)microtime()*1000000);
		$i = count($array);
		while(--$i) 
		{
			$j = mt_rand(0, $i);
			if ($i != $j) 
			{
				// swap elements
				$tmp = $array[$j];
				$array[$j] = $array[$i];
				$array[$i] = $tmp;
			}
		}
		return $array;
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
		return "assOrderingQuestion";
	}

	/**
	* Returns the name of the additional question data table in the database
	*
	* Returns the name of the additional question data table in the database
	*
	* @return string The additional table name
	* @access public
	*/
	function getAdditionalTableName()
	{
		return "qpl_question_ordering";
	}

	/**
	* Returns the name of the answer table in the database
	*
	* Returns the name of the answer table in the database
	*
	* @return string The answer table name
	* @access public
	*/
	function getAnswerTableName()
	{
		return "qpl_answer_ordering";
	}

	/**
	* Collects all text in the question which could contain media objects
	* which were created with the Rich Text Editor
	*/
	function getRTETextWithMediaObjects()
	{
		$text = parent::getRTETextWithMediaObjects();
		foreach ($this->answers as $index => $answer)
		{
			$answer_obj = $this->answers[$index];
			$text .= $answer_obj->getAnswertext();
		}
		return $text;
	}
	
	/**
	* Returns the answers array
	*/
	function &getAnswers()
	{
		return $this->answers;
	}

	/**
	* Returns true if the question type supports JavaScript output
	*
	* Returns true if the question type supports JavaScript output
	*
	* @return boolean TRUE if the question type supports JavaScript output, FALSE otherwise
	* @access public
	*/
	function supportsJavascriptOutput()
	{
		return TRUE;
	}
}

?>
