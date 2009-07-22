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
* Class for error text questions
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assErrorText extends assQuestion
{
	protected $errortext;
	protected $textsize;
	protected $errordata;
	protected $points_wrong;
	
	/**
	* assErorText constructor
	*
	* The constructor takes possible arguments an creates an instance of the assOrderingHorizontal object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the single choice question
	* @see assQuestion:__construct()
	*/
	function __construct(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""
	)
	{
		parent::__construct($title, $comment, $author, $owner, $question);
		$this->errortext = "";
		$this->textsize = 100.0;
		$this->errordata = array();
	}
	
	/**
	* Returns true, if a single choice question is complete for use
	*
	* @return boolean True, if the single choice question is complete for use, otherwise false
	*/
	public function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and ($this->getMaximumPoints() > 0))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Saves a the object to the database
	*
	*/
	public function saveToDb($original_id = "")
	{
		global $ilDB;

		$estw_time = $this->getEstimatedWorkingTime();
		$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);

		include_once("./Services/RTE/classes/class.ilRTE.php");
		if ($this->id == -1)
		{
			$next_id = $ilDB->nextId('qpl_questions');
			$affectedRows = $ilDB->manipulateF("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, description, author, owner, question_text, points, working_time, created, original_id, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", 
				array("integer","integer", "integer", "text", "text", "text", "integer", "text", "float", "time", "integer","integer","integer"),
				array(
					$next_id,
					$this->getQuestionTypeID(), 
					$this->getObjId(), 
					$this->getTitle(), 
					$this->getComment(), 
					$this->getAuthor(), 
					$this->getOwner(), 
					ilRTE::_replaceMediaObjectImageSrc($this->getQuestion(), 0), 
					$this->getMaximumPoints(),
					$estw_time,
					time(),
					($original_id) ? $original_id : NULL,
					time()
				)
			);
			$this->setId($next_id);
			// create page object of question
			$this->createPageObject();
		}
		else
		{
			$affectedRows = $ilDB->manipulateF("UPDATE qpl_questions SET obj_fi = %s, title = %s, description = %s, author = %s, question_text = %s, points = %s, working_time=%s, tstamp = %s WHERE question_id = %s", 
				array("integer", "text", "text", "text", "text", "float", "time", "integer", "integer"),
				array(
					$this->getObjId(), 
					$this->getTitle(), 
					$this->getComment(), 
					$this->getAuthor(), 
					ilRTE::_replaceMediaObjectImageSrc($this->getQuestion(), 0), 
					$this->getMaximumPoints(),
					$estw_time,
					time(),
					$this->getId()
				)
			);
		}
		// save additional data
		$affectedRows = $ilDB->manipulateF("DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s", 
			array("integer"),
			array($this->getId())
		);

		$affectedRows = $ilDB->manipulateF("INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, errortext, textsize, points_wrong) VALUES (%s, %s, %s, %s)", 
			array("integer", "text", "float", "float"),
			array(
				$this->getId(),
				$this->getErrorText(),
				$this->getTextSize(),
				$this->getPointsWrong()
			)
		);
	
		$affectedRows = $ilDB->manipulateF("DELETE FROM qpl_a_errortext WHERE question_fi = %s",
			array('integer'),
			array($this->getId())
		);

		$sequence = 0;
		foreach ($this->errordata as $object)
		{
			$next_id = $ilDB->nextId('qpl_a_errortext');
			$affectedRows = $ilDB->manipulateF("INSERT INTO qpl_a_errortext (answer_id, question_fi, text_wrong, text_correct, points, sequence) VALUES (%s, %s, %s, %s, %s, %s)",
				array('integer','integer','text','text','float', 'integer'),
				array(
					$next_id,
					$this->getId(),
					$object->text_wrong,
					$object->text_correct,
					$object->points,
					$sequence++
				)
			);
		}
		
		parent::saveToDb();
	}

	/**
	* Loads the object from the database
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	*/
	public function loadFromDb($question_id)
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT qpl_questions.*, " . $this->getAdditionalTableName() . ".* FROM qpl_questions LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = qpl_questions.question_id WHERE qpl_questions.question_id = %s",
			array("integer"),
			array($question_id)
		);
		if ($result->numRows() == 1)
		{
			$data = $ilDB->fetchAssoc($result);
			$this->setId($question_id);
			$this->setObjId($data["obj_fi"]);
			$this->setTitle($data["title"]);
			$this->setComment($data["description"]);
			$this->setOriginalId($data["original_id"]);
			$this->setAuthor($data["author"]);
			$this->setPoints($data["points"]);
			$this->setOwner($data["owner"]);
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
			$this->setErrorText($data["errortext"]);
			$this->setTextSize($data["textsize"]);
			$this->setPointsWrong($data["points_wrong"]);
			$this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
		}

		$result = $ilDB->queryF("SELECT * FROM qpl_a_errortext WHERE question_fi = %s ORDER BY sequence ASC",
			array('integer'),
			array($question_id)
		);
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerErrorText.php";
		if ($result->numRows() > 0)
		{
			while ($data = $ilDB->fetchAssoc($result))
			{
				array_push($this->errordata, new assAnswerErrorText($data["text_wrong"], $data["text_correct"], $data["points"]));
			}
		}

		parent::loadFromDb($question_id);
	}

	/**
	* Duplicates the object
	*/
	public function duplicate($for_test = true, $title = "", $author = "", $owner = "")
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

		$clone->onDuplicate($this_id);
		return $clone->id;
	}

	/**
	* Copies an object
	*/
	public function copyObject($target_questionpool, $title = "")
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

		$clone->onCopy($this->getObjId(), $this->getId());

		return $clone->id;
	}

	/**
	* Returns the maximum points, a learner can reach answering the question
	*
	* @see $points
	*/
	public function getMaximumPoints()
	{
		$maxpoints = 0.0;
		foreach ($this->errordata as $object)
		{
			if ($object->points > 0) $maxpoints += $object->points;
		}
		return $maxpoints;
	}

	/**
	* Returns the points, a learner has reached answering the question
	* The points are calculated from the given answers including checks
	* for all special scoring options in the test container.
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	*/
	public function calculateReachedPoints($active_id, $pass = NULL)
	{
		global $ilDB;
		
		$found_values = array();
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}
		$result = $ilDB->queryF("SELECT value1 FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			array('integer','integer','integer'),
			array($active_id, $this->getId(), $pass)
		);
		while ($row = $ilDB->fetchAssoc($result))
		{
			array_push($found_values, $row['value1']);
		}
		$points = $this->getPointsForSelectedPositions($found_values);
		$points = parent::calculateReachedPoints($active_id, $pass = NULL, $points);
		return $points;
	}
	
	/**
	* Saves the learners input of the question to the database
	*
	* @param integer $test_id The database id of the test containing this question
  * @return boolean Indicates the save status (true if saved successful, false otherwise)
	* @see $answers
	*/
	public function saveWorkingData($active_id, $pass = NULL)
	{
		global $ilDB;
		global $ilUser;

		if (is_null($pass))
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$pass = ilObjTest::_getPass($active_id);
		}

		$affectedRows = $ilDB->manipulateF("DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			array('integer','integer','integer'),
			array($active_id, $this->getId(), $pass)
		);

		$entered_values = false;
		if (strlen($_POST["qst_" . $this->getId()]))
		{
			$selected = split(",", $_POST["qst_" . $this->getId()]);
			foreach ($selected as $position)
			{
				$next_id = $ilDB->nextId('tst_solutions');
				$query = $ilDB->manipulateF("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, tstamp) VALUES (%s, %s, %s, %s, NULL, %s, %s)",
					array('integer','integer','integer','text','integer','integer'),
					array(
						$next_id,
						$active_id,
						$this->getId(),
						$position,
						$pass,
						time()
					)
				);
			}
			$entered_values = true;
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

	/**
	* Returns the question type of the question
	*
	* @return integer The question type of the question
	*/
	public function getQuestionType()
	{
		return "assErrorText";
	}
	
	/**
	* Returns the name of the additional question data table in the database
	*
	* @return string The additional table name
	*/
	public function getAdditionalTableName()
	{
		return "qpl_qst_errortext";
	}
	
	/**
	* Returns the name of the answer table in the database
	*
	* @return string The answer table name
	*/
	public function getAnswerTableName()
	{
		return "";
	}
	
	/**
	* Deletes datasets from answers tables
	*
	* @param integer $question_id The question id which should be deleted in the answers table
	*/
	public function deleteAnswers($question_id)
	{
	}

	/**
	* Collects all text in the question which could contain media objects
	* which were created with the Rich Text Editor
	*/
	public function getRTETextWithMediaObjects()
	{
		$text = parent::getRTETextWithMediaObjects();
		return $text;
	}

	/**
	* Creates an Excel worksheet for the detailed cumulated results of this question
	*
	* @param object $worksheet Reference to the parent excel worksheet
	* @param object $startrow Startrow of the output in the excel worksheet
	* @param object $active_id Active id of the participant
	* @param object $pass Test pass
	* @param object $format_title Excel title format
	* @param object $format_bold Excel bold format
	* @param array $eval_data Cumulated evaluation data
	*/
	public function setExportDetailsXLS(&$worksheet, $startrow, $active_id, $pass, &$format_title, &$format_bold)
	{
		include_once ("./classes/class.ilExcelUtils.php");
		$worksheet->writeString($startrow, 0, ilExcelUtils::_convert_text($this->lng->txt($this->getQuestionType())), $format_title);
		$worksheet->writeString($startrow, 1, ilExcelUtils::_convert_text($this->getTitle()), $format_title);
		return $startrow + 1;
	}
	
	/**
	* Creates a question from a QTI file
	*
	* Receives parameters from a QTI parser and creates a valid ILIAS question object
	*
	* @param object $item The QTI item object
	* @param integer $questionpool_id The id of the parent questionpool
	* @param integer $tst_id The id of the parent test if the question is part of a test
	* @param object $tst_object A reference to the parent test object
	* @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
	* @param array $import_mapping An array containing references to included ILIAS objects
	*/
	public function fromXML(&$item, &$questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
	{
		include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assErrorTextImport.php";
		$import = new assErrorTextImport($this);
		$import->fromXML($item, $questionpool_id, $tst_id, $tst_object, $question_counter, $import_mapping);
	}
	
	/**
	* Returns a QTI xml representation of the question and sets the internal
	* domxml variable with the DOM XML representation of the QTI xml representation
	*
	* @return string The QTI xml representation of the question
	*/
	public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
	{
		include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assErrorTextExport.php";
		$export = new assErrorTextExport($this);
		return $export->toXML($a_include_header, $a_include_binary, $a_shuffle, $test_output, $force_image_references);
	}

	/**
	* Returns the best solution for a given pass of a participant
	*
	* @return array An associated array containing the best solution
	*/
	public function getBestSolution($active_id, $pass)
	{
		$user_solution = array();
		return $user_solution;
	}
	
	public function getErrorsFromText($a_text = "")
	{
		if (strlen($a_text) == 0) $a_text = $this->getErrorText();
		preg_match_all("/#([^\s]+)/is", $a_text, $matches);
		if (is_array($matches[1]))
		{
			return $matches[1];
		}
		else
		{
			return array();
		}
	}
	
	public function setErrorData($a_data)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerErrorText.php";
		$temp = $this->errordata;
		$this->errordata = array();
		foreach ($a_data as $error)
		{
			$text_correct = "";
			$points = 0.0;
			foreach ($temp as $object)
			{
				if (strcmp($object->text_wrong, $error) == 0)
				{
					$text_correct = $object->text_correct;
					$points = $object->points;
					continue;
				}
			}
			array_push($this->errordata, new assAnswerErrorText($error, $text_correct, $points));
		}
	}
	
	public function createErrorTextOutput($selections = null, $graphicalOutput = false, $correct_solution = false)
	{
		$counter = 0;
		$errorcounter = 0;
		if (!is_array($selections)) $selections = array();
		$textarray = preg_split("/[\n\r]+/", $this->getErrorText());
		foreach ($textarray as $textidx => $text)
		{
			$items = preg_split("/\s+/", $text);
			foreach ($items as $idx => $item)
			{
				if (strpos($item, '#') === 0)
				{
					$item = ilStr::substr($item, 1, ilStr::strlen($item));
					if ($correct_solution)
					{
						$errorobject = $this->errordata[$errorcounter];
						if (is_object($errorobject))
						{
							$item = $errorobject->text_correct;
						}
						$errorcounter++;
					}
				}
				$class = '';
				$img = '';
				if (in_array($counter, $selections))
				{
					$class = ' class="sel"';
					if ($graphicalOutput)
					{
						if ($this->getPointsForSelectedPositions(array($counter)) >= 0)
						{
							$img = ' <img src="' . ilUtil::getImagePath("icon_ok.gif") . '" alt="' . $this->lng->txt("answer_is_right") . '" title="' . $this->lng->txt("answer_is_right") . '" /> ';
						}
						else
						{
							$img = ' <img src="' . ilUtil::getImagePath("icon_not_ok.gif") . '" alt="' . $this->lng->txt("answer_is_wrong") . '" title="' . $this->lng->txt("answer_is_wrong") . '" /> ';
						}
					}
				}
				$items[$idx] = '<a' . $class . ' href="#HREF" onclick="javascript: return false;">' . ilUtil::prepareFormOutput($item) . '</a>' . $img;
				$counter++;
			}
			$textarray[$textidx] = '<p>' . join($items, " ") . '</p>';
		}
		return join($textarray, "\n");
	}
	
	public function getBestSelection()
	{
		$words = array();
		$counter = 0;
		$errorcounter = 0;
		$textarray = preg_split("/[\n\r]+/", $this->getErrorText());
		foreach ($textarray as $textidx => $text)
		{
			$items = preg_split("/\s+/", $text);
			foreach ($items as $word)
			{
				$points = $this->getPointsWrong();
				if (strpos($word, '#') === 0)
				{
					$errorobject = $this->errordata[$errorcounter];
					if (is_object($errorobject))
					{
						$points = $errorobject->points;
					}
					$errorcounter++;
				}
				$words[$counter] = array("word" => $word, "points" => $points);
				$counter++;
			}
		}
		$selections = array();
		foreach ($words as $idx => $word)
		{
			if ($word['points'] > 0)
			{
				array_push($selections, $idx);
			}
		}
		return $selections;
	}
	
	protected function getPointsForSelectedPositions($positions)
	{
		$words = array();
		$counter = 0;
		$errorcounter = 0;
		$textarray = preg_split("/[\n\r]+/", $this->getErrorText());
		foreach ($textarray as $textidx => $text)
		{
			$items = preg_split("/\s+/", $text);
			foreach ($items as $word)
			{
				$points = $this->getPointsWrong();
				if (strpos($word, '#') === 0)
				{
					$errorobject = $this->errordata[$errorcounter];
					if (is_object($errorobject))
					{
						$points = $errorobject->points;
					}
					$errorcounter++;
				}
				$words[$counter] = array("word" => $word, "points" => $points);
				$counter++;
			}
		}
		$total = 0;
		foreach ($positions as $position)
		{
			$total += $words[$position]['points'];
		}
		return $total;
	}
	
	/**
	* Flush error data
	*/
	public function flushErrorData()
	{
		$this->errordata = array();
	}
	
	public function addErrorData($text_wrong, $text_correct, $points)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerErrorText.php";
		array_push($this->errordata, new assAnswerErrorText($text_wrong, $text_correct, $points));
	}
	
	/**
	* Get error data
	*
	* @return array Error data
	*/
	public function getErrorData()
	{
		return $this->errordata;
	}
	
	/**
	* Get error text
	*
	* @return string Error text
	*/
	public function getErrorText()
	{
		return $this->errortext;
	}
	
	/**
	* Set error text
	*
	* @param string $a_value Error text
	*/
	public function setErrorText($a_value)
	{
		$this->errortext = $a_value;
	}
	
	/**
	* Set text size in percent
	*
	* @return double Text size in percent
	*/
	public function getTextSize()
	{
		return $this->textsize;
	}
	
	/**
	* Set text size in percent
	*
	* @param double $a_value text size in percent
	*/
	public function setTextSize($a_value)
	{
		$this->textsize = $a_value;
	}
	
	/**
	* Get wrong points
	*
	* @return double Points for wrong selection
	*/
	public function getPointsWrong()
	{
		return $this->points_wrong;
	}
	
	/**
	* Set wrong points
	*
	* @param double $a_value Points for wrong selection
	*/
	public function setPointsWrong($a_value)
	{
		$this->points_wrong = $a_value;
	}
	
	/**
	* Object getter
	*/
	protected function __get($value)
	{
		switch ($value)
		{
			case "errortext":
				return $this->getErrorText();
				break;
			case "textsize":
				return $this->getTextSize();
				break;
			case "points_wrong":
				return $this->getPointsWrong();
				break;
			default:
				return parent::__get($value);
				break;
		}
	}

	/**
	* Object setter
	*/
	protected function __set($key, $value)
	{
		switch ($key)
		{
			case "errortext":
				$this->setErrorText($value);
				break;
			case "textsize":
				$this->setTextSize($value);
				break;
			case "points_wrong":
				$this->setPointsWrong($value);
				break;
			default:
				parent::__set($key, $value);
				break;
		}
	}
}

?>
