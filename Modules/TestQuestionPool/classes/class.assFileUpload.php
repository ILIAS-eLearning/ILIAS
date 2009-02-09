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
* Class for file upload questions
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assFileUpload extends assQuestion
{
	protected $maxsize;
	protected $allowedextensions;
	
	/**
	* assFileUpload constructor
	*
	* The constructor takes possible arguments an creates an instance of the assFileUpload object.
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
	* Saves a assFileUpload object to a database
	*
	*/
	public function saveToDb($original_id = "")
	{
		global $ilDB;

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
		$statement = $ilDB->prepareManip("INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, maxsize, allowedextensions) VALUES (?, ?, ?)", 
			array("integer", "float", "text")
		);
		$data = array(
			$this->getId(),
			(strlen($this->getMaxSize())) ? $this->getMaxSize() : NULL,
			(strlen($this->getAllowedExtensions())) ? $this->getAllowedExtensions() : NULL
		);
		$affectedRows = $ilDB->execute($statement, $data);
		parent::saveToDb();
	}

	/**
	* Loads a assFileUpload object from a database
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the question in the database
	*/
	public function loadFromDb($question_id)
	{
		global $ilDB;
		$statement = $ilDB->prepare("SELECT qpl_questions.*, " . $this->getAdditionalTableName() . ".* FROM qpl_questions, " . $this->getAdditionalTableName() . " WHERE qpl_questions.question_id = ? AND qpl_questions.question_id = " . $this->getAdditionalTableName() . ".question_fi",
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
			$this->setMaxSize($data["maxsize"]);
			$this->setAllowedExtensions($data["allowedextensions"]);
		}
		parent::loadFromDb($question_id);
	}

	/**
	* Duplicates an assFileUpload
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
	* Copies an assFileUpload object
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
		return $this->getPoints();
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
		
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}
		$points = 0;
		$points = parent::calculateReachedPoints($active_id, $pass = NULL, $points);
		return $points;
	}
	
	/**
	* Check file upload
	*
	* @return	boolean Input ok, true/false
	*/	
	function checkUpload()
	{
		global $lng;

		$this->lng->loadLanguageModule("form");
		// remove trailing '/'
		while (substr($_FILES["upload"]["name"],-1) == '/')
		{
			$_FILES["upload"]["name"] = substr($_FILES["upload"]["name"],0,-1);
		}

		$filename = $_FILES["upload"]["name"];
		$filename_arr = pathinfo($_FILES["upload"]["name"]);
		$suffix = $filename_arr["extension"];
		$mimetype = $_FILES["upload"]["type"];
		$size_bytes = $_FILES["upload"]["size"];
		$temp_name = $_FILES["upload"]["tmp_name"];
		$error = $_FILES["upload"]["error"];
		
		if ($size_bytes > $this->getMaxFilesizeInBytes())
		{
			ilUtil::sendInfo($lng->txt("form_msg_file_size_exceeds"), true);
			return false;
		}

		// error handling
		if ($error > 0)
		{
			switch ($error)
			{
				case UPLOAD_ERR_INI_SIZE:
					ilUtil::sendInfo($lng->txt("form_msg_file_size_exceeds"), true);
					return false;
					break;
					 
				case UPLOAD_ERR_FORM_SIZE:
					ilUtil::sendInfo($lng->txt("form_msg_file_size_exceeds"), true);
					return false;
					break;
	
				case UPLOAD_ERR_PARTIAL:
					ilUtil::sendInfo($lng->txt("form_msg_file_partially_uploaded"), true);
					return false;
					break;
	
				case UPLOAD_ERR_NO_FILE:
					ilUtil::sendInfo($lng->txt("form_msg_file_no_upload"), true);
					return false;
					break;
	 
				case UPLOAD_ERR_NO_TMP_DIR:
					ilUtil::sendInfo($lng->txt("form_msg_file_missing_tmp_dir"), true);
					return false;
					break;
					 
				case UPLOAD_ERR_CANT_WRITE:
					ilUtil::sendInfo($lng->txt("form_msg_file_cannot_write_to_disk"), true);
					return false;
					break;
	 
				case UPLOAD_ERR_EXTENSION:
					ilUtil::sendInfo($lng->txt("form_msg_file_upload_stopped_ext"), true);
					return false;
					break;
			}
		}
		
		// check suffixes
		if (strlen($suffix) && count($this->getAllowedExtensionsArray()))
		{
			if (!in_array(strtolower($suffix), $this->getAllowedExtensionsArray()))
			{
				ilUtil::sendInfo($lng->txt("form_msg_file_wrong_file_type"), true);
				return false;
			}
		}
		
		// virus handling
		if (strlen($temp_name))
		{
			$vir = ilUtil::virusHandling($temp_name, $filename);
			if ($vir[0] == false)
			{
				ilUtil::sendInfo($lng->txt("form_msg_file_virus_found")."<br />".$vir[1], true);
				return false;
			}
		}
		
		return true;
	}

	/**
	* Returns the filesystem path for file uploads
	*/
	protected function getFileUploadPath()
	{
		return ilUtil::getDataDir()."/qpl_data/$this->obj_id/$this->id/";
	}
	
	/**
	* Returns the uploaded files for an active user in a given pass
	*
	* @return array Results
	*/
	public function getUploadedFiles($active_id, $pass)
	{
		global $ilDB;
		$statement = $ilDB->prepare("SELECT *, TIMESTAMP+0 AS timestamp14 FROM tst_solutions WHERE active_fi = ? AND pass = ? ORDER BY timestamp14",
			array("integer", "integer")
		);
		$result = $ilDB->execute($statement, array($active_id, $pass));
		$found = array();
		while ($data = $ilDB->fetchAssoc($result))
		{
			array_push($found, $data);
		}
		return $found;
	}
	
	/**
	* Delete uploaded files
	*
  * @param array Array with ID's of the file datasets
	*/
	protected function deleteUploadedFiles($files)
	{
		global $ilDB;
		
		$pass = null;
		$active_id = null;
		foreach ($files as $solution_id)
		{
			$statement = $ilDB->prepare("SELECT * FROM tst_solutions WHERE solution_id = ?",
				array("integer")
			);
			$result = $ilDB->execute($statement, array($solution_id));
			if ($result->numRows() == 1)
			{
				$data = $ilDB->fetchAssoc($result);
				$pass = $data['pass'];
				$active_id = $data['active_fi'];
				@unlink($this->getFileUploadPath() . $data['value1']);
			}
		}
		foreach ($files as $solution_id)
		{
			$statement = $ilDB->prepareManip("DELETE FROM tst_solutions WHERE solution_id = ?", 
				array("integer")
			);
			$data = array($solution_id);
			$affectedRows = $ilDB->execute($statement, $data);
		}
	}
	
	/**
	* Return the maximum allowed file size in bytes
	*
  * @return integer The number of bytes of the maximum allowed file size
	*/
	public function getMaxFilesizeInBytes()
	{
		if (strlen($this->getMaxSize()))
		{
			return $this->getMaxSize();
		}
		else
		{
			// get the value for the maximal uploadable filesize from the php.ini (if available)
			$umf = get_cfg_var("upload_max_filesize");
			// get the value for the maximal post data from the php.ini (if available)
			$pms = get_cfg_var("post_max_size");

			//convert from short-string representation to "real" bytes
			$multiplier_a=array("K"=>1024, "M"=>1024*1024, "G"=>1024*1024*1024);

			$umf_parts=preg_split("/(\d+)([K|G|M])/", $umf, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
			$pms_parts=preg_split("/(\d+)([K|G|M])/", $pms, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

			if (count($umf_parts) == 2) { $umf = $umf_parts[0]*$multiplier_a[$umf_parts[1]]; }
			if (count($pms_parts) == 2) { $pms = $pms_parts[0]*$multiplier_a[$pms_parts[1]]; }

			// use the smaller one as limit
			$max_filesize = min($umf, $pms);

			if (!$max_filesize) $max_filesize=max($umf, $pms);
			return $max_filesize;
		}
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

		$entered_values = false;

		if (strcmp($_POST['cmd']['gotoquestion'], $this->lng->txt('delete')) == 0)
		{
			$deletefiles = $_POST['file'];
			if (is_array($deletefiles) && count($deletefiles) > 0)
			{
				$this->deleteUploadedFiles($deletefiles);
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt('no_checkbox'), true);
			}
		}
		else
		{
			if ($this->checkUpload())
			{
				if (!@file_exists($this->getFileUploadPath())) ilUtil::makeDirParents($this->getFileUploadPath());
				$version = time();
				$filename_arr = pathinfo($_FILES["upload"]["name"]);
				$extension = $filename_arr["extension"];
				$newfile = "file_" . $active_id . "_" . $pass . "_" . $version . "." . $extension;
				ilUtil::moveUploadedFile($_FILES["upload"]["tmp_name"], $_FILES["upload"]["name"], $this->getFileUploadPath() . $newfile);
				$statement = $ilDB->prepareManip("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, ?, ?, ?, ?, ?, NULL)", 
					array("integer", "integer", "text", "text", "integer")
				);
				$data = array(
					$active_id, 
					$this->getId(),
					$newfile,
					$_FILES["upload"]["name"],
					$pass
				);
				$affectedRows = $ilDB->execute($statement, $data);
				$entered_values = true;
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

	/**
	* Returns the question type of the question
	*
	* @return integer The question type of the question
	*/
	public function getQuestionType()
	{
		return "assFileUpload";
	}
	
	/**
	* Returns the name of the additional question data table in the database
	*
	* @return string The additional table name
	*/
	public function getAdditionalTableName()
	{
		return "qpl_question_fileupload";
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
		include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assFileUploadImport.php";
		$import = new assFileUploadImport($this);
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
		include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assFileUploadExport.php";
		$export = new assFileUploadExport($this);
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
	
	/**
	* Get max file size
	*
	* @return double Max file size
	*/
	public function getMaxSize()
	{
		return $this->maxsize;
	}
	
	/**
	* Set max file size
	*
	* @param double $a_value Max file size
	*/
	public function setMaxSize($a_value)
	{
		$this->maxsize = $a_value;
	}
	
	/**
	* Get allowed file extensions
	*
	* @return array Allowed file extensions
	*/
	public function getAllowedExtensionsArray()
	{
		if (strlen($this->allowedextensions))
		{
			return split(",", $this->allowedextensions);
		}
		return array();
	}
	
	/**
	* Get allowed file extensions
	*
	* @return string Allowed file extensions
	*/
	public function getAllowedExtensions()
	{
		return $this->allowedextensions;
	}
	
	/**
	* Set allowed file extensions
	*
	* @param string $a_value Allowed file extensions
	*/
	public function setAllowedExtensions($a_value)
	{
		$this->allowedextensions = strtolower(trim($a_value));
	}
	
	/**
	* Object getter
	*/
	protected function __get($value)
	{
		switch ($value)
		{
			case "maxsize":
				return $this->getMaxSize();
				break;
			case "allowedextensions":
				return $this->getAllowedExtensions();
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
			case "maxsize":
				$this->setMaxSize($value);
				break;
			case "allowedextensions":
				$this->setAllowedExtensions($value);
				break;
			default:
				parent::__set($key, $value);
				break;
		}
	}
}

?>
