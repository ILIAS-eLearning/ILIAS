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

		$this->saveQuestionDataToDb($original_id);

		// save additional data
	
		$affectedRows = $ilDB->manipulateF("DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s", 
			array("integer"),
			array($this->getId())
		);
		$affectedRows = $ilDB->manipulateF("INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, maxsize, allowedextensions) VALUES (%s, %s, %s)", 
			array("integer", "float", "text"),
			array(
				$this->getId(),
				(strlen($this->getMaxSize())) ? $this->getMaxSize() : NULL,
				(strlen($this->getAllowedExtensions())) ? $this->getAllowedExtensions() : NULL
			)
		);
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
		$result = $ilDB->queryF("SELECT qpl_questions.*, " . $this->getAdditionalTableName() . ".* FROM qpl_questions LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = qpl_questions.question_id WHERE qpl_questions.question_id = %s",
			array("integer"),
			array($question_id)
		);
		if ($result->numRows() == 1)
		{
			$data = $ilDB->fetchAssoc($result);
			$this->setId($question_id);
			$this->setTitle($data["title"]);
			$this->setComment($data["description"]);
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
			ilUtil::sendInfo($this->lng->txt("form_msg_file_size_exceeds"), true);
			return false;
		}

		// error handling
		if ($error > 0)
		{
			switch ($error)
			{
				case UPLOAD_ERR_INI_SIZE:
					ilUtil::sendInfo($this->lng->txt("form_msg_file_size_exceeds"), true);
					return false;
					break;
					 
				case UPLOAD_ERR_FORM_SIZE:
					ilUtil::sendInfo($this->lng->txt("form_msg_file_size_exceeds"), true);
					return false;
					break;
	
				case UPLOAD_ERR_PARTIAL:
					ilUtil::sendInfo($this->lng->txt("form_msg_file_partially_uploaded"), true);
					return false;
					break;
	
				case UPLOAD_ERR_NO_FILE:
					ilUtil::sendInfo($this->lng->txt("form_msg_file_no_upload"), true);
					return false;
					break;
	 
				case UPLOAD_ERR_NO_TMP_DIR:
					ilUtil::sendInfo($this->lng->txt("form_msg_file_missing_tmp_dir"), true);
					return false;
					break;
					 
				case UPLOAD_ERR_CANT_WRITE:
					ilUtil::sendInfo($this->lng->txt("form_msg_file_cannot_write_to_disk"), true);
					return false;
					break;
	 
				case UPLOAD_ERR_EXTENSION:
					ilUtil::sendInfo($this->lng->txt("form_msg_file_upload_stopped_ext"), true);
					return false;
					break;
			}
		}
		
		// check suffixes
		if (strlen($suffix) && count($this->getAllowedExtensionsArray()))
		{
			if (!in_array(strtolower($suffix), $this->getAllowedExtensionsArray()))
			{
				ilUtil::sendInfo($this->lng->txt("form_msg_file_wrong_file_type"), true);
				return false;
			}
		}
		
		// virus handling
		if (strlen($temp_name))
		{
			$vir = ilUtil::virusHandling($temp_name, $filename);
			if ($vir[0] == false)
			{
				ilUtil::sendInfo($this->lng->txt("form_msg_file_virus_found")."<br />".$vir[1], true);
				return false;
			}
		}
		return true;
	}

	/**
	* Returns the filesystem path for file uploads
	*/
	protected function getFileUploadPath($test_id, $active_id, $question_id = null)
	{
		if (is_null($question_id)) $question_id = $this->getId();
		return CLIENT_WEB_DIR . "/assessment/tst_$test_id/$active_id/$question_id/files/";
	}

	/**
	* Returns the file upload path for web accessible files of a question
	*
	* @access public
	*/
	function getFileUploadPathWeb($test_id, $active_id, $question_id = null)
	{
		if (is_null($question_id)) $question_id = $this->getId();
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/tst_$test_id/$active_id/$question_id/files/";
		return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
	}

	/**
	* Returns the uploaded files for an active user in a given pass
	*
	* @return array Results
	*/
	public function getUploadedFiles($active_id, $pass = null)
	{
		global $ilDB;
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}
		$result = $ilDB->queryF("SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s ORDER BY tstamp",
			array("integer", "integer", "integer"),
			array($active_id, $this->getId(), $pass)
		);
		$found = array();
		while ($data = $ilDB->fetchAssoc($result))
		{
			array_push($found, $data);
		}
		return $found;
	}
	
	/**
	* Returns the web accessible uploaded files for an active user in a given pass
	*
	* @return array Results
	*/
	public function getUploadedFilesForWeb($active_id, $pass)
	{
		global $ilDB;
		
		$found = $this->getUploadedFiles($active_id, $pass);
		$result = $ilDB->queryF("SELECT test_fi FROM tst_active WHERE active_id = %s",
			array('integer'),
			array($active_id)
		);
		if ($result->numRows() == 1)
		{
			$row = $ilDB->fetchAssoc($result);
			$test_id = $row["test_fi"];
			$path = $this->getFileUploadPathWeb($test_id, $active_id);
			foreach ($found as $idx => $data)
			{
				$found[$idx]['webpath'] = $path;
			}
		}
		return $found;
	}

	/**
	* Delete uploaded files
	*
  * @param array Array with ID's of the file datasets
	*/
	protected function deleteUploadedFiles($files, $test_id, $active_id)
	{
		global $ilDB;
		
		$pass = null;
		$active_id = null;
		foreach ($files as $solution_id)
		{
			$result = $ilDB->queryF("SELECT * FROM tst_solutions WHERE solution_id = %s",
				array("integer"),
				array($solution_id)
			);
			if ($result->numRows() == 1)
			{
				$data = $ilDB->fetchAssoc($result);
				$pass = $data['pass'];
				$active_id = $data['active_fi'];
				@unlink($this->getFileUploadPath($test_id, $active_id) . $data['value1']);
			}
		}
		foreach ($files as $solution_id)
		{
			$affectedRows = $ilDB->manipulateF("DELETE FROM tst_solutions WHERE solution_id = %s", 
				array("integer"),
				array($solution_id)
			);
		}
	}
	
	/**
	* Return the maximum allowed file size as string
	*
  * @return string The number of bytes of the maximum allowed file size
	*/
	public function getMaxFilesizeAsString()
	{
		$size = $this->getMaxFilesizeInBytes();
		if ($size < 1024)
		{
			$max_filesize = sprintf("%.1f Bytes",$size);
		}
		else if ($size < 1024*1024)
		{
			$max_filesize = sprintf("%.1f KB",$size/1024);
		}
		else
		{
			$max_filesize = sprintf("%.1f MB",$size/1024/1024);
		}
		
		return $max_filesize;
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

		$result = $ilDB->queryF("SELECT test_fi FROM tst_active WHERE active_id = %s",
			array('integer'),
			array($active_id)
		);
		$test_id = 0;
		if ($result->numRows() == 1)
		{
			$row = $ilDB->fetchAssoc($result);
			$test_id = $row["test_fi"];
		}

		$entered_values = false;
		if (strcmp($_POST['cmd']['gotoquestion'], $this->lng->txt('delete')) == 0)
		{
			$deletefiles = $_POST['file'];
			if (is_array($deletefiles) && count($deletefiles) > 0)
			{
				$this->deleteUploadedFiles($deletefiles, $test_id, $active_id);
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt('no_checkbox'), true);
			}
		}
		else
		{
			if (strlen($_FILES["upload"]["tmp_name"]))
			{
				if ($this->checkUpload())
				{
					if (!@file_exists($this->getFileUploadPath($test_id, $active_id))) ilUtil::makeDirParents($this->getFileUploadPath($test_id, $active_id));
					$version = time();
					$filename_arr = pathinfo($_FILES["upload"]["name"]);
					$extension = $filename_arr["extension"];
					$newfile = "file_" . $active_id . "_" . $pass . "_" . $version . "." . $extension;
					ilUtil::moveUploadedFile($_FILES["upload"]["tmp_name"], $_FILES["upload"]["name"], $this->getFileUploadPath($test_id, $active_id) . $newfile);
					$next_id = $ilDB->nextId('tst_solutions');
					$affectedRows = $ilDB->manipulateF("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s)", 
						array("integer","integer", "integer", "text", "text", "integer","integer"),
						array(
							$next_id,
							$active_id, 
							$this->getId(),
							$newfile,
							$_FILES["upload"]["name"],
							$pass,
							time()
						)
					);
					$entered_values = true;
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
		return "qpl_qst_fileupload";
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
		$i = 1;
		$solutions = $this->getSolutionValues($active_id, $pass);
		foreach ($solutions as $solution)
		{
			$worksheet->writeString($startrow + $i, 0, ilExcelUtils::_convert_text($this->lng->txt("result")), $format_bold);
			if (strlen($solution["value1"]))
			{
				$worksheet->write($startrow + $i, 1, ilExcelUtils::_convert_text($solution["value1"]));
				$worksheet->write($startrow + $i, 2, ilExcelUtils::_convert_text($solution["value2"]));
			}
			$i++;
		}
		return $startrow + $i + 1;
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
	
	/**
	* Checks if file uploads exist for a given test and the original id of the question
	*
	* @return boolean TRUE if file uploads exist, FALSE otherwise
	*/
	public function hasFileUploads($test_id)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT tst_solutions.solution_id FROM tst_solutions, tst_active, qpl_questions WHERE tst_solutions.active_fi = tst_active.active_id AND tst_solutions.question_fi = qpl_questions.question_id AND qpl_questions.original_id = %s AND tst_active.test_fi = %s",
			array("integer", "integer"),
			array($this->getId(), $test_id)
		);
		if ($result->numRows() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	* Generates a ZIP file containing all file uploads for a given test and the original id of the question
	*/
	public function getFileUploadZIPFile($test_id)
	{
		global $ilDB, $ilLog;
		$result = $ilDB->queryF("SELECT tst_solutions.solution_id, tst_solutions.pass, tst_solutions.active_fi, tst_solutions.question_fi, tst_solutions.value1, tst_solutions.value2, tst_solutions.tstamp FROM tst_solutions, tst_active, qpl_questions WHERE tst_solutions.active_fi = tst_active.active_id AND tst_solutions.question_fi = qpl_questions.question_id AND qpl_questions.original_id = %s AND tst_active.test_fi = %s ORDER BY tst_solutions.active_fi, tst_solutions.tstamp",
			array("integer", "integer"),
			array($this->getId(), $test_id)
		);
		$zipfile = ilUtil::ilTempnam() . ".zip";
		$tempdir = ilUtil::ilTempnam();
		if ($result->numRows())
		{
			$userdata = array();
			$data .= "<html><head>";
			$data .= '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />';
			$data .= '<style>
			 table { border: 1px #333 solid; border-collapse:collapse;}	
			 td, th { border: 1px #333 solid; padding: 0.25em;}	
			 th { color: #fff; background-color: #666;}
			</style>
			';
			$data .= "<title>" . $this->getTitle() . "</title></head><body>\n";
			$data .= "<h1>" . $this->getTitle() . "</h1>\n";
			$data .= "<table><thead>\n";
			$data .= "<tr><th>" . $this->lng->txt("name") . "</th><th>" . $this->lng->txt("filename") . "</th><th>" . $this->lng->txt("pass") . "</th><th>" . $this->lng->txt("location") . "</th><th>" . $this->lng->txt("date") . "</th></tr></thead><tbody>\n";
			while ($row = $ilDB->fetchAssoc($result))
			{
				ilUtil::makeDirParents($tempdir . "/" . $row["active_fi"]."/".$row["question_fi"]);
				@copy($this->getFileUploadPath($test_id, $row["active_fi"], $row["question_fi"]) . $row["value1"], $tempdir . "/" . $row["active_fi"]."/".$row["question_fi"] . "/" . $row["value1"]);
				if (!array_key_exists($row["active_fi"], $userdata))
				{
					include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
					$userdata[$row["active_fi"]] = ilObjTestAccess::_getParticipantData($row["active_fi"]);
				}
				$data .= "<tr><td>".$userdata[$row["active_fi"]]."</td><td><a href=\"".$row["active_fi"]."/".$row["question_fi"]."/".$row["value1"]."\" target=\"_blank\">".$row["value2"]."</a></td><td>".$row["pass"]."</td><td>".$row["active_fi"]."/".$row["question_fi"]."/".$row["value1"]."</td>";
				$data .= "<td>" . ilFormat::fmtDateTime(ilFormat::unixtimestamp2datetime($row["tstamp"]), $this->lng->txt("lang_dateformat"), $this->lng->txt("lang_timeformat"), "datetime", FALSE) . "</td>";
				$data .= "</tr>\n";
			}
			$data .= "</tbody></table>\n";
			$data .= "</body></html>\n";

			$indexfile = $tempdir . "/index.html";
			$fh = fopen($indexfile, 'w');
			fwrite($fh, $data);
			fclose($fh);
		}
		ilUtil::zip($tempdir, $zipfile);
		ilUtil::delDir($tempdir);
		ilUtil::deliverFile($zipfile, ilUtil::getASCIIFilename($this->getTitle().".zip"), "application/zip", false, true);
	}
}

?>
