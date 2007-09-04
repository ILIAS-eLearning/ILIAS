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
* Class for matching questions
*
* assMatchingQuestion is a class for matching questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assMatchingQuestion extends assQuestion
{
	/**
	* The possible matching pairs of the matching question
	*
	* $matchingpairs is an array of the predefined matching pairs of the matching question
	*
	* @var array
	*/
	var $matchingpairs;

	/**
	* Type of matching question
	*
	* There are two possible types of matching questions: Matching terms and definitions (=1)
	* and Matching terms and pictures (=0).
	*
	* @var integer
	*/
	var $matching_type;

	/**
	* assMatchingQuestion constructor
	*
	* The constructor takes possible arguments an creates an instance of the assMatchingQuestion object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the matching question
	* @access public
	*/
	function assMatchingQuestion (
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$matching_type = MT_TERMS_DEFINITIONS
	)
	{
		$this->assQuestion($title, $comment, $author, $owner, $question);
		$this->matchingpairs = array();
		$this->matching_type = $matching_type;
	}

	/**
	* Returns true, if a matching question is complete for use
	*
	* Returns true, if a matching question is complete for use
	*
	* @return boolean True, if the matching question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and (count($this->matchingpairs)) and ($this->getMaximumPoints() > 0))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Saves a assMatchingQuestion object to a database
	*
	* Saves a assMatchingQuestion object to a database (experimental)
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

		// cleanup RTE images which are not inserted into the question text
		include_once("./Services/RTE/classes/class.ilRTE.php");
		if ($this->id == -1)
		{
			// Neuen Datensatz schreiben
			$now = getdate();
			$question_type = $this->getQuestionTypeID();
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, working_time, points, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($question_type. ""),
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title. ""),
				$ilDB->quote($this->comment. ""),
				$ilDB->quote($this->author. ""),
				$ilDB->quote($this->owner. ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->question, 0)),
				$ilDB->quote($estw_time. ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($complete. ""),
				$ilDB->quote($created. ""),
				$original_id
			);

			$result = $ilDB->query($query);
			if ($result == DB_OK)
			{
				$this->id = $ilDB->getLastInsertId();
				$query = sprintf("INSERT INTO qpl_question_matching (question_fi, shuffle, matching_type) VALUES (%s, %s, %s)",
					$ilDB->quote($this->id . ""),
					$ilDB->quote($this->shuffle . ""),
					$ilDB->quote($this->matching_type. "")
				);
				$ilDB->query($query);

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
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time=%s, points = %s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title. ""),
				$ilDB->quote($this->comment. ""),
				$ilDB->quote($this->author. ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->question, 0)),
				$ilDB->quote($estw_time. ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($complete. ""),
				$ilDB->quote($this->id. "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_matching SET shuffle = %s, matching_type = %s WHERE question_fi = %s",
				$ilDB->quote($this->shuffle . ""),
				$ilDB->quote($this->matching_type. ""),
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
		}

		if ($result == DB_OK)
		{
			// Antworten schreiben
			// alte Antworten löschen
			$query = sprintf("DELETE FROM qpl_answer_matching WHERE question_fi = %s",
				$ilDB->quote($this->id)
			);
			$result = $ilDB->query($query);

			// Anworten wegschreiben
			foreach ($this->matchingpairs as $key => $value)
			{
				$matching_obj = $this->matchingpairs[$key];
				$query = sprintf("INSERT INTO qpl_answer_matching (answer_id, question_fi, answertext, points, aorder, matchingtext, matching_order) VALUES (NULL, %s, %s, %s, %s, %s, %s)",
					$ilDB->quote($this->id),
					$ilDB->quote($matching_obj->getTerm() . ""),
					$ilDB->quote($matching_obj->getPoints() . ""),
					$ilDB->quote($matching_obj->getTermId() . ""),
					$ilDB->quote($matching_obj->getDefinition() . ""),
					$ilDB->quote($matching_obj->getDefinitionId() . "")
				);
				$matching_result = $ilDB->query($query);
			}
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a assMatchingQuestion object from a database
	*
	* Loads a assMatchingQuestion object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilDB;

    $query = sprintf("SELECT qpl_questions.*, qpl_question_matching.* FROM qpl_questions, qpl_question_matching WHERE question_id = %s AND qpl_questions.question_id = qpl_question_matching.question_fi",
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
				$this->comment = $data->comment;
				$this->author = $data->author;
				$this->solution_hint = $data->solution_hint;
				$this->obj_id = $data->obj_fi;
				$this->original_id = $data->original_id;
				$this->owner = $data->owner;
				$this->matching_type = $data->matching_type;
				include_once("./Services/RTE/classes/class.ilRTE.php");
				$this->question = ilRTE::_replaceMediaObjectImageSrc($data->question_text, 1);
				$this->points = $data->points;
				$this->shuffle = $data->shuffle;
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}

			$query = sprintf("SELECT * FROM qpl_answer_matching WHERE question_fi = %s ORDER BY answer_id ASC",
				$ilDB->quote($question_id)
			);
			$result = $ilDB->query($query);
			include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatching.php";
			if (strcmp(strtolower(get_class($result)), db_result) == 0)
			{
				while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
				{
					array_push($this->matchingpairs, new ASS_AnswerMatching($data->answertext, $data->points, $data->aorder, $data->matchingtext, $data->matching_order));
				}
			}
		}
		parent::loadFromDb($question_id);
	}

	/**
	* Adds an answer to the question
	*
	* Adds an answer to the question
	*
	* @access public
	*/
	function addMatchingPair($answertext, $points, $answerorder, $matchingtext, $matchingorder)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatching.php";
		array_push($this->matchingpairs, new ASS_AnswerMatching($answertext, $points, $answerorder, $matchingtext, $matchingorder));
	}
	
	
	/**
	* Duplicates an assMatchingQuestion
	*
	* Duplicates an assMatchingQuestion
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
	* Copies an assMatchingQuestion
	*
	* Copies an assMatchingQuestion
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
		if ($title)
		{
			$clone->setTitle($title);
		}
		$source_questionpool = $this->getObjId();
		$clone->setObjId($target_questionpool);
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
		if ($this->get_matching_type() == MT_TERMS_PICTURES)
		{
			$imagepath = $this->getImagePath();
			$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			foreach ($this->matchingpairs as $answer)
			{
				$filename = $answer->getPicture();
				if (!copy($imagepath_original . $filename, $imagepath . $filename))
				{
					print "image could not be duplicated!!!! ";
				}
				if (!copy($imagepath_original . $filename . ".thumb.jpg", $imagepath . $filename . ".thumb.jpg"))
				{
					print "image thumbnail could not be duplicated!!!! ";
				}
			}
		}
	}

	function copyImages($question_id, $source_questionpool)
	{
		if ($this->get_matching_type() == MT_TERMS_PICTURES)
		{
			$imagepath = $this->getImagePath();
			$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
			$imagepath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $imagepath_original);
			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			foreach ($this->matchingpairs as $answer)
			{
				$filename = $answer->getPicture();
				if (!copy($imagepath_original . $filename, $imagepath . $filename))
				{
					print "image could not be duplicated!!!! ";
				}
				if (!copy($imagepath_original . $filename . ".thumb.jpg", $imagepath . $filename . ".thumb.jpg"))
				{
					print "image thumbnail could not be duplicated!!!! ";
				}
			}
		}
	}

	/**
	* Sets the matching question type
	*
	* Sets the matching question type
	*
	* @param integer $matching_type The question matching type
	* @access public
	* @see $matching_type
	*/
	function setMatchingType($matching_type = MT_TERMS_DEFINITIONS)
	{
		$this->matching_type = $matching_type;
	}

	/**
	* Returns the matching question type
	*
	* Returns the matching question type
	*
	* @return integer The matching question type
	* @access public
	* @see $matching_type
	*/
	function get_matching_type()
	{
		return $this->matching_type;
	}

	function getMatchingType()
	{
		return $this->matching_type;
	}

	/**
	* Adds an matching pair for an matching question
	*
	* Adds an matching pair for an matching choice question. The students have to fill in an order for the matching pair.
	* The matching pair is an ASS_AnswerMatching object that will be created and assigned to the array $this->matchingpairs.
	*
	* @param string $answertext The answer text
	* @param string $matchingtext The matching text of the answer text
	* @param double $points The points for selecting the matching pair (even negative points can be used)
	* @param integer $order A possible display order of the matching pair
	* @access public
	* @see $matchingpairs
	* @see ASS_AnswerMatching
	*/
	function add_matchingpair(
		$term = "",
		$picture_or_definition = "",
		$points = 0.0,
		$term_id = 0,
		$picture_or_definition_id = 0
	)
	{
		// append answer
		if ($term_id == 0)
		{
			$term_id = $this->get_random_id();
		}

		if ($picture_or_definition_id == 0)
		{
			$picture_or_definition_id = $this->get_random_id();
		}
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatching.php";
		$matchingpair = new ASS_AnswerMatching($term, $points, $term_id, $picture_or_definition, $picture_or_definition_id);
		array_push($this->matchingpairs, $matchingpair);
	}

	/**
	* Returns a matching pair
	*
	* Returns a matching pair with a given index. The index of the first
	* matching pair is 0, the index of the second matching pair is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th matching pair
	* @return object ASS_AnswerMatching-Object
	* @access public
	* @see $matchingpairs
	*/
	function get_matchingpair($index = 0)
	{
		return $this->getMatchingPair($index);
	}
	
	/**
	* Returns a matching pair
	*
	* Returns a matching pair with a given index. The index of the first
	* matching pair is 0, the index of the second matching pair is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th matching pair
	* @return object ASS_AnswerMatching-Object
	* @access public
	* @see $matchingpairs
	*/
	function getMatchingPair($index = 0)
	{
		if ($index < 0)
		{
			return NULL;
		}
		if (count($this->matchingpairs) < 1)
		{
			return NULL;
		}
		if ($index >= count($this->matchingpairs))
		{
			return NULL;
		}
		return $this->matchingpairs[$index];
	}

	/**
	* Deletes a matching pair
	*
	* Deletes a matching pair with a given index. The index of the first
	* matching pair is 0, the index of the second matching pair is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th matching pair
	* @access public
	* @see $matchingpairs
	*/
	function delete_matchingpair($index = 0)
	{
		if ($index < 0)
		{
			return;
		}
		if (count($this->matchingpairs) < 1)
		{
			return;
		}
		if ($index >= count($this->matchingpairs))
		{
			return;
		}
		unset($this->matchingpairs[$index]);
		$this->matchingpairs = array_values($this->matchingpairs);
	}

	/**
	* Deletes all matching pairs
	*
	* Deletes all matching pairs
	*
	* @access public
	* @see $matchingpairs
	*/
	function flush_matchingpairs()
	{
		$this->matchingpairs = array();
	}

	/**
	* Returns the number of matching pairs
	*
	* Returns the number of matching pairs
	*
	* @return integer The number of matching pairs of the matching question
	* @access public
	* @see $matchingpairs
	*/
	function get_matchingpair_count()
	{
		return count($this->matchingpairs);
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
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if (strcmp($data->value1, "") != 0)
			{
				array_push($found_value1, $data->value1);
				array_push($found_value2, $data->value2);
			}
		}
		$points = 0;
		foreach ($found_value2 as $key => $value)
		{
			foreach ($this->matchingpairs as $answer_key => $answer_value)
			{
				if (($answer_value->getDefinitionId() == $value) and ($answer_value->getTermId() == $found_value1[$key]))
				{
					$points += $answer_value->getPoints();
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
		foreach ($this->matchingpairs as $key => $value)
		{
			if ($value->getPoints() > 0)
			{
				$points += $value->getPoints();
			}
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
			$image_filename = str_replace(" ", "_", $image_filename);
			$imagepath = $this->getImagePath();
			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			//if (!move_uploaded_file($image_tempfilename, $imagepath . $image_filename))
			if (!ilUtil::moveUploadedFile($image_tempfilename, $image_filename, $imagepath.$image_filename))
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
					ilUtil::convertImage($imagepath.$image_filename, $thumbpath, "JPEG", 100);
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
		$matching_values = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^sel_matching_(\d+)/", $key, $matches))
			{
				if ((strcmp($value, "") != 0) && ($value != -1))
				{
					array_push($matching_values, $value);
				}
			}
		}
		$check_matching = array_flip($matching_values);
		if (count($check_matching) != count($matching_values))
		{
			// duplicate matching values!!!
			$result = false;
			ilUtil::sendInfo($this->lng->txt("duplicate_matching_values_selected"), TRUE);
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
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/^sel_matching_(\d+)/", $key, $matches))
				{
					if (!(preg_match("/initial_value_\d+/", $value)))
					{
						if ($value > -1) // -1 is the unselected value in the non javascript version
						{
							$entered_values++;
							$query = sprintf("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
								$ilDB->quote($active_id),
								$ilDB->quote($this->getId()),
								$ilDB->quote(trim($value)),
								$ilDB->quote(trim($matches[1])),
								$ilDB->quote($pass . "")
							);
							$result = $ilDB->query($query);
						}
					}
				}
			}
			$saveWorkingDataResult = true;
		}
		if ($entered_values)
		{
			include_once ("./classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
			}
		}
		else
		{
			include_once ("./classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_not_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
			}
		}
    parent::saveWorkingData($active_id, $pass);
		return $saveWorkingDataResult;
	}

	function get_random_id()
	{
		mt_srand((double)microtime()*1000000);
		$random_number = mt_rand(1, 100000);
		$found = FALSE;
		while ($found)
		{
			$found = FALSE;
			foreach ($this->matchingpairs as $key => $value)
			{
				if (($value->getTermId() == $random_number) || ($value->getDefinitionId() == $random_number))
				{
					$found = TRUE;
					$random_number++;
				}
			}
		}
		return $random_number;
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
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, working_time=%s, points = %s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title. ""),
				$ilDB->quote($this->comment. ""),
				$ilDB->quote($this->author. ""),
				$ilDB->quote($this->question. ""),
				$ilDB->quote($estw_time. ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($complete. ""),
				$ilDB->quote($this->original_id. "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_matching SET shuffle = %, matching_type = %s WHERE question_fi = %s",
				$ilDB->quote($this->shuffle . ""),
				$ilDB->quote($this->matching_type. ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);

			if ($result == DB_OK)
			{
				// write answers
				// delete old answers
				$query = sprintf("DELETE FROM qpl_answer_matching WHERE question_fi = %s",
					$ilDB->quote($this->original_id)
				);
				$result = $ilDB->query($query);
	
				foreach ($this->matchingpairs as $key => $value)
				{
					$matching_obj = $this->matchingpairs[$key];
					$query = sprintf("INSERT INTO qpl_answer_matching (answer_id, question_fi, answertext, points, aorder, matchingtext, matching_order) VALUES (NULL, %s, %s, %s, %s, %s, %s)",
						$ilDB->quote($this->original_id . ""),
						$ilDB->quote($matching_obj->getTerm() . ""),
						$ilDB->quote($matching_obj->getPoints() . ""),
						$ilDB->quote($matching_obj->getTermId() . ""),
						$ilDB->quote($matching_obj->getDefinition() . ""),
						$ilDB->quote($matching_obj->getDefinitionId() . "")
					);
					$matching_result = $ilDB->query($query);
				}
			}
			parent::syncWithOriginal();
		}
	}

	function pc_array_shuffle($array) {
		$i = count($array);
		mt_srand((double)microtime()*1000000);
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
	* Sets the shuffle flag
	*
	* Sets the shuffle flag
	*
	* @param boolean $shuffle A flag indicating whether the answers are shuffled or not
	* @access public
	* @see $shuffle
	*/
	function setShuffle($shuffle)
	{
		switch ($shuffle)
		{
			case 0:
			case 1:
			case 2:
			case 3:
				$this->shuffle = $shuffle;
				break;
			default:
				$this->shuffle = 1;
				break;
		}
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
		return "assMatchingQuestion";
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
		return "qpl_question_matching";
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
		return "qpl_answer_matching";
	}

	/**
	* Collects all text in the question which could contain media objects
	* which were created with the Rich Text Editor
	*/
	function getRTETextWithMediaObjects()
	{
		return parent::getRTETextWithMediaObjects();
	}
	
	/**
	* Returns the matchingpairs array
	*/
	function &getMatchingPairs()
	{
		return $this->matchingpairs;
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
