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
* Class for Mathematik Online based questions
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assFlashQuestion extends assQuestion
{
	private $width;
	private $height;
	private $paramters;
	private $applet;

	/**
	* assFlashQuestion constructor
	*
	* The constructor takes possible arguments an creates an instance of the assFlashQuestion object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the single choice question
	* @access public
	* @see assQuestion:assQuestion()
	*/
	function assFlashQuestion(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""
	  )
	{
		$this->assQuestion($title, $comment, $author, $owner, $question);
		$this->parameters = array();
		$this->width = 540;
		$this->height = 400;
		$this->applet = "";
	}
	
	/**
	* Returns true, if a single choice question is complete for use
	*
	* @return boolean True, if the single choice question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and ($this->getMaximumPoints() > 0) and (strlen($this->getApplet())))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Saves a assFlashQuestion object to a database
	*
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		global $ilDB, $ilLog;

		$complete = "0";
		if ($this->isComplete())
		{
			$complete = "1";
		}
		$estw_time = $this->getEstimatedWorkingTime();
		$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);

		include_once("./Services/RTE/classes/class.ilRTE.php");
		if ($this->id == -1)
		{
			// Neuen Datensatz schreiben
			$now = getdate();
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			
			$statement = $ilDB->prepareManip("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, points, working_time, complete, created, original_id, TIMESTAMP) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)", 
				array("integer", "integer", "text", "text", "text", "integer", "text", "float", "time", "text", "timestamp")
			);
			$data = array(
				$this->getQuestionTypeID(), 
				$this->getObjId(), 
				$this->getTitle(), 
				$this->getComment(), 
				$this->getAuthor(), 
				$this->getOwner(), 
				ilRTE::_replaceMediaObjectImageSrc($this->question, 0), 
				$this->getMaximumPoints(),
				$estw_time,
				$complete,
				$created,
				($original_id) ? $original_id : NULL
			);
			$affectedRows = $ilDB->execute($statement, $data);
			$this->setId($ilDB->getLastInsertId());
			// create page object of question
			$this->createPageObject();

			if ($this->getTestId() > 0)
			{
				$this->insertIntoTest($this->getTestId());
			}
		}
		else
		{
			// Vorhandenen Datensatz aktualisieren
			$statement = $ilDB->prepareManip("UPDATE qpl_questions SET obj_fi = ?, title = ?, comment = ?, author = ?, question_text = ?, points = ?, working_time=?, complete = ? WHERE question_id = ?", 
				array("integer", "text", "text", "text", "text", "float", "time", "text", "integer")
			);
			$data = array(
				$this->getObjId(), 
				$this->getTitle(), 
				$this->getComment(), 
				$this->getAuthor(), 
				ilRTE::_replaceMediaObjectImageSrc($this->question, 0), 
				$this->getMaximumPoints(),
				$estw_time,
				$complete,
				$this->getId()
			);
			$affectedRows = $ilDB->execute($statement, $data);
		}
		// save additional data
		$statement = $ilDB->prepareManip("DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = ?", 
			array("integer")
		);
		$data = array($this->getId());
		$affectedRows = $ilDB->execute($statement, $data);
		$statement = $ilDB->prepareManip("INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, width, height, applet, params) VALUES (?, ?, ?, ?, ?)", 
			array("integer", "integer", "integer", "text")
		);
		$data = array(
			$this->getId(),
			(strlen($this->getWidth())) ? $this->getWidth() : 550,
			(strlen($this->getHeight())) ? $this->getHeight() : 400,
			$this->getApplet(),
			serialize($this->getParameters())
		);
		if ($_SESSION["flash_upload_filename"])
		{
			$path = $this->getFlashPath();
			ilUtil::makeDirParents($path);
			@rename($_SESSION["flash_upload_filename"], $path . $this->getApplet());
			unset($_SESSION["flash_upload_filename"]);
		}
		$affectedRows = $ilDB->execute($statement, $data);

		parent::saveToDb();
	}

	/**
	* Loads a assFlashQuestion object from a database
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilDB;
		$statement = $ilDB->prepare("SELECT qpl_questions.* FROM qpl_questions WHERE question_id = ?",
			array("integer")
		);
		$result = $ilDB->execute($statement, array($question_id));
		if ($result->numRows() == 1)
		{
			$data = $ilDB->fetchAssoc($result);
			$this->setId($question_id);
			$this->setTitle($data["title"]);
			$this->setComment($data["comment"]);
			$this->setSuggestedSolution($data["solution_hint"]);
			$this->setOriginalId($data["original_id"]);
			$this->setObjId($data["obj_fi"]);
			$this->setAuthor($data["author"]);
			$this->setOwner($data["owner"]);
			$this->setPoints($data["points"]);

			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
			$this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
			// load additional data
			$statement = $ilDB->prepare("SELECT * FROM " . $this->getAdditionalTableName() . " WHERE question_fi = ?",
				array("integer")
			);
			$result = $ilDB->execute($statement, array($question_id));
			if ($result->numRows() == 1)
			{
				$data = $ilDB->fetchAssoc($result);
				$this->setWidth($data["width"]);
				$this->setHeight($data["height"]);
				$this->setApplet($data["applet"]);
				$this->parameters = unserialize($data["params"]);
				if (!is_array($this->parameters)) $this->clearParameters();
				unset($_SESSION["flash_upload_filename"]);
			}
		}
		parent::loadFromDb($question_id);
	}

	/**
	* Duplicates an assFlashQuestion
	*
	* Duplicates an assFlashQuestion
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
	* Copies an assFlashQuestion object
	*
	* Copies an assFlashQuestion object
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
		$statement = $ilDB->prepare("SELECT * FROM tst_solutions WHERE active_fi = ? AND question_fi = ? AND pass = ?",
			array("integer", "integer", "integer")
		);
		$result = $ilDB->execute($statement, array($active_id, $this->getId(), $pass));

		$points = 0;
		while ($data = $ilDB->fetchAssoc($result))
		{
			$points += $data["points"];
		}

		$points = parent::calculateReachedPoints($active_id, $pass = NULL, $points);
		return $points;
	}
	
	function sendToHost($url, $data, $optional_headers = null)
	{
		$params = array('http' => array(
			'method' => 'POST',
			'content' => $data
		));
		if ($optional_headers !== null) 
		{
			$params['http']['header'] = $optional_headers;
		}
		$ctx = stream_context_create($params);
		$fp = @fopen($url, 'rb', false, $ctx);
		if (!$fp) 
		{
			throw new Exception("Problem with $url, $php_errormsg");
		}
		$response = @stream_get_contents($fp);
		if ($response === false) 
		{
			throw new Exception("Problem reading data from $url, $php_errormsg");
		}
		return $response;
	}
	
	function deleteApplet()
	{
		@unlink($this->getFlashPath() . $this->getApplet());
		$this->applet = "";
	}
	
	/**
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

		if (is_null($pass))
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$pass = ilObjTest::_getPass($active_id);
		}
		$entered_values = FALSE;
		// get all post parameters
		$params = array();
		foreach ($_POST as $key => $value)
		{
			if (is_array($value))
			{
				foreach ($value as $arraykey => $arrayvalue)
				{
					if (is_array($arrayvalue))
					{
						foreach ($arrayvalue as $subkey => $subvalue)
						{
							array_push($params, urlencode($key . "[" . $arraykey . "]" . "[" . $subkey . "]") . "=" . urlencode($subvalue));
						}
					}
					else
					{
						array_push($params, urlencode($key . "[" . $arraykey . "]") . "=" . urlencode($arrayvalue));
					}
				}
			}
			else
			{
				array_push($params, urlencode($key) . "=" . urlencode($value));
			}
		}

		$statement = $ilDB->prepareManip("DELETE FROM tst_solutions WHERE active_fi = ? AND question_fi = ? AND pass = ? AND value1 != '0'", 
			array("integer", "integer", "integer")
		);
		$data = array($active_id, $this->getId(), $pass);
		$affectedRows = $ilDB->execute($statement, $data);

		foreach ($_POST as $key => $value)
		{
			if (is_array($value))
			{
				if (strpos($key, "interaufg") !== FALSE)
				{
					foreach ($value as $arraykey => $arrayvalue)
					{
						if (is_array($arrayvalue))
						{
							foreach ($arrayvalue as $subkey => $subvalue)
							{
								$entered_values = TRUE;
								$statement = $ilDB->prepareManip("INSERT INTO tst_solutions (active_fi, pass, question_fi, value1, value2) VALUES (?, ?, ?, ?, ?)", 
									array("integer", "integer", "integer", "text", "text")
								);
								$data = array($active_id, $pass, $this->getId(), $key . "[" . $arraykey . "]" . "[" . $subkey . "]", $subvalue);
								$affectedRows = $ilDB->execute($statement, $data);
							}
						}
						else if (strlen($arrayvalue))
						{
							$entered_values = TRUE;
							$statement = $ilDB->prepareManip("INSERT INTO tst_solutions (active_fi, pass, question_fi, value1, value2) VALUES (?, ?, ?, ?, ?)", 
								array("integer", "integer", "integer", "text", "text")
							);
							$data = array($active_id, $pass, $this->getId(), $key . "[" . $arraykey . "]", $arrayvalue);
							$affectedRows = $ilDB->execute($statement, $data);
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
		if (strpos($result, "error"))
		{
			ilUtil::sendInfo("Beim Ermitteln der Punktezahl von Mathematik Online ist ein Fehler aufgetreten: " . ilUtil::prepareFormOutput($result), TRUE);
			return FALSE;
		}
		else
		{
			return TRUE;
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
		return "assFlashQuestion";
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
		return "qpl_question_flash";
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
		return "";
	}
	
	/**
	* Deletes datasets from answers tables
	*
	* @param integer $question_id The question id which should be deleted in the answers table
	* @access public
	*/
	function deleteAnswers($question_id)
	{
	}

	/**
	* Collects all text in the question which could contain media objects
	* which were created with the Rich Text Editor
	*/
	function getRTETextWithMediaObjects()
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
	* @access public
	*/
	public function setExportDetailsXLS(&$worksheet, $startrow, $active_id, $pass, &$format_title, &$format_bold)
	{
		return $startrow;
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
	* @access public
	*/
	function fromXML(&$item, &$questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
	{
		include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assFlashQuestionImport.php";
		$import = new assFlashQuestionImport($this);
		$import->fromXML($item, $questionpool_id, $tst_id, $tst_object, $question_counter, $import_mapping);
	}
	
	/**
	* Returns a QTI xml representation of the question and sets the internal
	* domxml variable with the DOM XML representation of the QTI xml representation
	*
	* @return string The QTI xml representation of the question
	* @access public
	*/
	function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
	{
		include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assFlashQuestionExport.php";
		$export = new assFlashQuestionExport($this);
		return $export->toXML($a_include_header, $a_include_binary, $a_shuffle, $test_output, $force_image_references);
	}

	/**
	* Returns the best solution for a given pass of a participant
	*
	* @return array An associated array containing the best solution
	* @access public
	*/
	public function getBestSolution($active_id, $pass)
	{
		$user_solution = array();
		return $user_solution;
	}
	
	public function setHeight($a_height)
	{
		if (!$a_height) $a_height = 400;
		$this->height = $a_height;
	}
	
	public function getHeight()
	{
		return $this->height;
	}

	public function setWidth($a_width)
	{
		if (!$a_width) $a_width = 550;
		$this->width = $a_width;
	}
	
	public function getWidth()
	{
		return $this->width;
	}
	
	public function setApplet($a_applet)
	{
		$this->applet = $a_applet;
	}
	
	public function getApplet()
	{
		return $this->applet;
	}
	
	public function addParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}
	
	public function removeParameter($name)
	{
		unset($this->parameters[$name]);
	}
	
	public function clearParameters()
	{
		$this->parameters = array();
	}
	
	public function getParameters()
	{
		return $this->parameters;
	}
}

?>
