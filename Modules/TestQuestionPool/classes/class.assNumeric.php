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
* Class for numeric questions
*
* assNumeric is a class for numeric questions. To solve a numeric
* question, a learner has to enter a numerical value in a defined range
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author   Nina Gharib <nina@wgserve.de>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assNumeric extends assQuestion
{
	/**
	* The defined ranges with the associated points for entering a value in the correct range
	*
	* $ranges is an array of the defined ranges of the numeric question
	*
	* @var array
	*/
	var $ranges;
	
	/**
	* The maximum number of characters for the numeric input field
	*
	* The maximum number of characters for the numeric input field
	*
	* @var integer
	*/
	var $maxchars;

	/**
	* assNumeric constructor
	*
	* The constructor takes possible arguments an creates an instance of the assNumeric object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the numeric question
	* @access public
	* @see assQuestion:assQuestion()
	*/
	function assNumeric(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""
	  )
	{
		$this->assQuestion($title, $comment, $author, $owner, $question);
		$this->ranges = array();
		$this->maxchars = 6;
	}

	/**
	* Returns true, if a numeric question is complete for use
	*
	* Returns true, if a numeric question is complete for use
	*
	* @return boolean True, if the numeric question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and (count($this->ranges)) and ($this->getMaximumPoints() > 0))
		{
			return true;
		}
			else
		{
			return false;
		}
	}

	/**
	* Saves a assNumeric object to a database
	*
	* Saves a assNumeric object to a database (experimental)
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
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, points, working_time, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($question_type),
				$ilDB->quote($this->obj_id),
				$ilDB->quote($this->title),
				$ilDB->quote($this->comment),
				$ilDB->quote($this->author),
				$ilDB->quote($this->owner),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->question, 0)),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($estw_time),
				$ilDB->quote("$complete"),
				$ilDB->quote($created),
				$original_id
			);
			$result = $ilDB->query($query);
			
			if ($result == DB_OK)
			{
				$this->id = $ilDB->getLastInsertId();
				$query = sprintf("INSERT INTO qpl_question_numeric (question_fi, maxNumOfChars) VALUES (%s, %s)",
					$ilDB->quote($this->id . ""),
					$ilDB->quote($this->getMaxChars() . "")
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
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, points = %s, working_time=%s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title),
				$ilDB->quote($this->comment),
				$ilDB->quote($this->author),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->question, 0)),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($estw_time),
				$ilDB->quote("$complete"),
				$ilDB->quote($this->id)
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_numeric SET maxNumOfChars = %s WHERE question_fi = %s",
				$ilDB->quote($this->getMaxChars() . ""),
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
		}
		if ($result == DB_OK)
		{
			// Write Ranges to the database
			
			// 1. delete old ranges
			$query = sprintf("DELETE FROM qpl_numeric_range WHERE question_fi = %s",
				$ilDB->quote($this->id)
			);
			$result = $ilDB->query($query);

			// 2. write ranges
			foreach ($this->ranges as $key => $range)
			{
				$query = sprintf("INSERT INTO qpl_numeric_range (range_id, question_fi, lowerlimit, upperlimit, points, aorder, lastchange) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($this->id),
				$ilDB->quote($range->getLowerLimit()),
				$ilDB->quote($range->getUpperLimit() . ""),
				$ilDB->quote($range->getPoints() . ""),
				$ilDB->quote($range->getOrder() . "")
				);
				$answer_result = $ilDB->query($query);
			}
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a assNumeric object from a database
	*
	* Loads a assNumeric object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilDB;
		
    $query = sprintf("SELECT qpl_questions.*, qpl_question_numeric.* FROM qpl_questions, qpl_question_numeric WHERE question_id = %s AND qpl_questions.question_id = qpl_question_numeric.question_fi",
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
				$this->solution_hint = $data->solution_hint;
				$this->original_id = $data->original_id;
				$this->obj_id = $data->obj_fi;
				$this->author = $data->author;
				$this->owner = $data->owner;
				$this->points = $data->points;
				include_once("./Services/RTE/classes/class.ilRTE.php");
				$this->question = ilRTE::_replaceMediaObjectImageSrc($data->question_text, 1);
				$this->maxchars = $data->maxNumOfChars;
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}

			$query = sprintf("SELECT * FROM qpl_numeric_range WHERE question_fi = %s ORDER BY aorder ASC",
				$ilDB->quote($question_id)
			);

			$result = $ilDB->query($query);

			include_once "./Modules/TestQuestionPool/classes/class.assNumericRange.php";
			if (strcmp(strtolower(get_class($result)), db_result) == 0)
			{
				while ($data = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					array_push($this->ranges, new assNumericRange($data["lowerlimit"], $data["upperlimit"], $data["points"], $data["aorder"]));
				}
			}
		}
		parent::loadFromDb($question_id);
	}

	/**
	* Adds a range to the numeric question
	*
	* Adds a range to the numeric question. An assNumericRange object will be
	* created and assigned to the array $this->ranges
	*
	* @param double $lowerlimit The lower limit of the range
	* @param double $upperlimit The upper limit of the range
	* @param double $points The points for entering a number in the correct range
	* @param integer $order The display order of the range
	* @access public
	* @see $ranges
	* @see assNumericalRange
	*/
	function addRange(
		$lowerlimit = 0.0,
		$upperlimit = 0.0,
		$points = 0.0,
		$order = 0
	)
	{
		$found = -1;
		foreach ($this->ranges as $key => $range)
		{
			if ($range->getOrder() == $order)
			{
				$found = $order;
			}
		}
		include_once "./Modules/TestQuestionPool/classes/class.assNumericRange.php";
		if ($found >= 0)
		{
			// insert range
			$range = new assNumericRange($lowerlimit, $upperlimit, $points, $found);
			array_push($this->ranges, $range);
			for ($i = $found + 1; $i < count($this->ranges); $i++)
			{
				$this->ranges[$i] = $this->ranges[$i-1];
			}
			$this->ranges[$found] = $range;
		}
		else
		{
			// append range
			$range = new assNumericRange($lowerlimit, $upperlimit, $points, count($this->ranges));
			array_push($this->ranges, $range);
		}
	}

	/**
	* Duplicates an assNumericQuestion
	*
	* Duplicates an assNumericQuestion
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

		return $clone->id;
	}

	/**
	* Copies an assNumeric object
	*
	* Copies an assNumeric object
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

		return $clone->id;
	}
	
	/**
	* Returns the number of ranges
	*
	* Returns the number of ranges
	*
	* @return integer The number of ranges of the numeric question
	* @access public
	* @see $ranges
	*/
	function getRangeCount()
	{
		return count($this->ranges);
	}

	/**
	* Returns a range
	*
	* Returns a range with a given index. The index of the first
	* range is 0, the index of the second range is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th range
	* @return object assNumericelRange-Object containing the range
	* @access public
	* @see $ranges
	*/
	function getRange($index = 0)
	{
		if ($index < 0) return NULL;
		if (count($this->ranges) < 1) return NULL;
		if ($index >= count($this->ranges)) return NULL;

		return $this->ranges[$index];
	}

	/**
	* Deletes a range
	*
	* Deletes a range with a given index. The index of the first
	* range is 0, the index of the second range is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th range
	* @access public
	* @see $ranges
	*/
	function deleteRange($index = 0)
	{
		if ($index < 0) return;
		if (count($this->ranges) < 1) return;
		if ($index >= count($this->ranges)) return;
		unset($this->ranges[$index]);
		$this->ranges = array_values($this->ranges);
		for ($i = 0; $i < count($this->ranges); $i++)
		{
			if ($this->ranges[$i]->getOrder() > $index)
			{
				$this->ranges[$i]->setOrder($i);
			}
		}
	}

	/**
	* Deletes all ranges
	*
	* Deletes all ranges
	*
	* @access public
	* @see $ranges
	*/
	function flushRanges()
	{
		$this->ranges = array();
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
		$max = 0;
		foreach ($this->ranges as $key => $range) 
		{
			if ($range->getPoints() > $max)
			{
				$max = $range->getPoints();
			}
		}
		return $max;
	}

	/**
	* Returns the range with the maximum points, a learner can reach answering the question
	*
	* Returns the range with the maximum points, a learner can reach answering the question
	*
	* @access public
	* @see $points
	*/
	function getBestRange()
	{
		$max = 0;
		$bestrange = NULL;
		foreach ($this->ranges as $key => $range) 
		{
			if ($range->getPoints() > $max)
			{
				$max = $range->getPoints();
				$bestrange = $range;
			}
		}
		return $bestrange;
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
		
		$found_values = array();
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
		$data = $result->fetchRow(DB_FETCHMODE_ASSOC);
		
		$enteredvalue = $data["value1"];
		//if (!is_numeric($enteredvalue)) return 0;
		$points = 0;
		foreach ($this->ranges as $key => $range)
		{
			if ($points == 0)
			{
				if ($range->contains($enteredvalue))
				{
					$points = $range->getPoints();
				}
			}
		}

		$points = parent::calculateReachedPoints($active_id, $pass = NULL, $points);
		return $points;
	}
	
	/**
	* Saves the learners input of the question to the database
	*
	* Saves the learners input of the question to the database
	*
	* @param integer $test_id The database id of the test containing this question
  * @return boolean Indicates the save status (true if saved successful, false otherwise)
	* @access public
	* @see $ranges
	*/
	function saveWorkingData($active_id, $pass = NULL)
	{
		global $ilDB;
		global $ilUser;

		if (is_null($pass))
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$pass = ilObjTest::_getPass($active_id);
		}
		$entered_values = 0;
		$numeric_result = str_replace(",",".",$_POST["numeric_result"]);
		$query = sprintf("SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		$update = $row->solution_id;
		if ($update)
		{
			if (strlen($numeric_result))
			{
				$query = sprintf("UPDATE tst_solutions SET value1 = %s WHERE solution_id = %s",
					$ilDB->quote(trim($numeric_result)),
					$ilDB->quote($update)
				);
				$result = $ilDB->query($query);
				$entered_values++;
			}
			else
			{
				$query = sprintf("DELETE FROM tst_solutions WHERE solution_id = %s",
					$ilDB->quote($update)
				);
				$result = $ilDB->query($query);
			}
		}
		else
		{
			if (strlen($numeric_result))
			{
				$query = sprintf("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL, %s, NULL)",
					$ilDB->quote($active_id),
					$ilDB->quote($this->getId()),
					$ilDB->quote(trim($numeric_result)),
					$ilDB->quote($pass . "")
				);
				$result = $ilDB->query($query);
				$entered_values++;
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
		return true;
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
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, points = %s, working_time=%s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title. ""),
				$ilDB->quote($this->comment. ""),
				$ilDB->quote($this->author. ""),
				$ilDB->quote($this->question. ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($estw_time. ""),
				$ilDB->quote($complete. ""),
				$ilDB->quote($this->original_id. "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_numeric SET maxNumOfChars = %s WHERE question_fi = %s",
				$ilDB->quote($this->getMaxChars() . ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);

			if ($result == DB_OK)
			{
				// Write Ranges to the database
				
				// 1. delete old ranges
				$query = sprintf("DELETE FROM qpl_numeric_range WHERE question_fi = %s",
					$ilDB->quote($this->original_id)
				);
				$result = $ilDB->query($query);
	
				// 2. write ranges
				foreach ($this->ranges as $key => $range)
				{
					$query = sprintf("INSERT INTO qpl_numeric_range (range_id, question_fi, lowerlimit, upperlimit, points, aorder, lastchange) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
					$ilDB->quote($this->original_id),
					$ilDB->quote($range->getLowerLimit()),
					$ilDB->quote($range->getUpperLimit() . ""),
					$ilDB->quote($range->getPoints() . ""),
					$ilDB->quote($range->getOrder() . "")
					);
					$answer_result = $ilDB->query($query);
				}
			}
			parent::syncWithOriginal();
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
		return "assNumeric";
	}
	
	/**
	* Returns the maximum number of characters for the numeric input field
	*
	* Returns the maximum number of characters for the numeric input field
	*
	* @return integer The maximum number of characters
	* @access public
	*/
	function getMaxChars()
	{
		return $this->maxchars;
	}
	
	/**
	* Sets the maximum number of characters for the numeric input field
	*
	* Sets the maximum number of characters for the numeric input field
	*
	* @param integer $maxchars The maximum number of characters
	* @access public
	*/
	function setMaxChars($maxchars)
	{
		$this->maxchars = $maxchars;
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
		return "qpl_question_numeric";
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
	* Returns the ranges array
	*/
	function &getRanges()
	{
		return $this->ranges;
	}
}

?>
