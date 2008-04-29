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

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Basic class for all assessment question types
*
* The assQuestion class defines and encapsulates basic methods and attributes
* for assessment question types to be used for all parent classes.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assQuestion
{
	/**
	* Question id
	*
	* A unique question id
	*
	* @var integer
	*/
	var $id;

	/**
	* Question title
	*
	* A title string to describe the question
	*
	* @var string
	*/
	var $title;

	/**
	* Question comment
	*
	* A comment string to describe the question more detailed as the title
	*
	* @var string
	*/
	var $comment;

	/**
	* Question owner/creator
	*
	* A unique positive numerical ID which identifies the owner/creator of the question.
	* This can be a primary key from a database table for example.
	*
	* @var integer
	*/
	var $owner;

	/**
	* Contains the name of the author
	*
	* A text representation of the authors name. The name of the author must
	* not necessary be the name of the owner.
	*
	* @var string
	*/
	var $author;

	/**
	* The question text
	*
	* The question text
	*
	* @var string
	*/
  var $question;

	/**
	* The maximum available points for the question
	*
	* Contains the calculated maximum available points for the
	* question. This should be caculated normally be a method of
	* one of the derived classes
	*
	* @var integer
	*/
	var $points;

	/**
	* Contains estimates working time on a question (HH MM SS)
	*
	* Contains estimates working time on a question (HH MM SS)
	*
	* @var array
	*/
	var $est_working_time;

	/**
	* Indicates whether the answers will be shuffled or not
	*
	* Indicates whether the answers will be shuffled or not
	*
	* @var array
	*/
	var $shuffle;

	/**
	* The database id of a test in which the question is contained
	*
	* The database id of a test in which the question is contained
	*
	* @var integer
	*/
	var $test_id;

	/**
	* Object id of the container object
	*
	* Object id of the container object
	*
	* @var double
	*/
	var $obj_id;

	/**
	* The reference to the ILIAS class
	*
	* The reference to the ILIAS class
	*
	* @var object
	*/
	var $ilias;

	/**
	* The reference to the Template class
	*
	* The reference to the Template class
	*
	* @var object
	*/
	var $tpl;

	/**
	* The reference to the Language class
	*
	* The reference to the Language class
	*
	* @var object
	*/
	var $lng;

	/**
	* The domxml representation of the question in qti
	*
	* The domxml representation of the question in qti
	*
	* @var object
	*/
	var $domxml;

	/**
	* Contains the output type of a question
	*
	* Contains the output type of a question
	*
	* @var integer
	*/
	var $outputType;

	var $suggested_solutions;
	/**
	* assQuestion constructor
	*
	* The constructor takes possible arguments an creates an instance of the assQuestion object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @access public
	*/
	function assQuestion(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""
	)
	{
		global $ilias;
		global $lng;
		global $tpl;

		$this->ilias =& $ilias;
		$this->lng =& $lng;
		$this->tpl =& $tpl;

		$this->title = $title;
		$this->comment = $comment;
		$this->author = $author;
		$this->setQuestion($question);
		if (!$this->author)
		{
			$this->author = $this->ilias->account->fullname;
		}
		$this->owner = $owner;
		if ($this->owner == -1)
		{
			$this->owner = $this->ilias->account->id;
		}
		$this->id = -1;
		$this->test_id = -1;
		$this->suggested_solutions = array();
		$this->shuffle = 1;
		$this->setEstimatedWorkingTime(0,1,0);
		$this->outputType = OUTPUT_HTML;
		register_shutdown_function(array(&$this, '_assQuestion'));
	}

	function _assQuestion()
	{
		if (!empty($this->domxml))
		{
			$this->domxml->free();
		}
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
		include_once "./Modules/TestQuestionPool/classes/import/qti12/class." . $this->getQuestionType() . "Import.php";
		$classname = $this->getQuestionType() . "Import";
		$import = new $classname($this);
		$import->fromXML($item, $questionpool_id, $tst_id, $tst_object, $question_counter, $import_mapping);
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
	function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
	{
		include_once "./Modules/TestQuestionPool/classes/export/qti12/class." . $this->getQuestionType() . "Export.php";
		$classname = $this->getQuestionType() . "Export";
		$export = new $classname($this);
		return $export->toXML($a_include_header, $a_include_binary, $a_shuffle, $test_output, $force_image_references);
	}

	/**
	* Returns true, if a question is complete for use
	*
	* Returns true, if a question is complete for use
	*
	* @return boolean True, if the question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		return false;
	}

	/**
	* Returns TRUE if the question title exists in the database
	*
	* Returns TRUE if the question title exists in the database
	*
	* @param string $title The title of the question
	* @return boolean The result of the title check
	* @access public
	*/
	function questionTitleExists($questionpool_id, $title)
	{
		global $ilDB;
		
		$query = sprintf("SELECT * FROM qpl_questions WHERE obj_fi = %s AND title = %s",
			$ilDB->quote($questionpool_id . ""),
			$ilDB->quote($title)
			);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			return TRUE;
		}
		return FALSE;
	}

	/**
	* Sets the title string
	*
	* Sets the title string of the assQuestion object
	*
	* @param string $title A title string to describe the question
	* @access public
	* @see $title
	*/
	function setTitle($title = "")
	{
		$this->title = $title;
	}

	/**
	* Sets the id
	*
	* Sets the id of the assQuestion object
	*
	* @param integer $id A unique integer value
	* @access public
	* @see $id
	*/
	function setId($id = -1)
	{
		$this->id = $id;
	}

	/**
	* Sets the test id
	*
	* Sets the test id of the assQuestion object
	*
	* @param integer $id A unique integer value
	* @access public
	* @see $test_id
	*/
	function setTestId($id = -1)
	{
		$this->test_id = $id;
	}

	/**
	* Sets the comment
	*
	* Sets the comment string of the assQuestion object
	*
	* @param string $comment A comment string to describe the question
	* @access public
	* @see $comment
	*/
	function setComment($comment = "")
	{
		$this->comment = $comment;
	}

	/**
	* Sets the output type
	*
	* Sets the output type
	*
	* @param integer $outputType The output type of the question
	* @access public
	* @see $outputType
	*/
	function setOutputType($outputType = OUTPUT_HTML)
	{
		$this->outputType = $outputType;
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
	function setShuffle($shuffle = true)
	{
		if ($shuffle)
		{
			$this->shuffle = 1;
		}
			else
		{
			$this->shuffle = 0;
		}
	}

	/**
	* Sets the estimated working time of a question
	*
	* Sets the estimated working time of a question
	*
	* @param integer $hour Hour
	* @param integer $min Minutes
	* @param integer $sec Seconds
	* @access public
	* @see $comment
	*/
	function setEstimatedWorkingTime($hour=0, $min=0, $sec=0)
	{
		$this->est_working_time = array("h" => (int)$hour, "m" => (int)$min, "s" => (int)$sec);
	}

	/**
	* returns TRUE if the key occurs in an array
	*
	* returns TRUE if the key occurs in an array
	*
	* @param string $arraykey A key to an element in array
	* @param array $array An array to be searched
	* @access public
	*/
	function keyInArray($searchkey, $array)
	{
		if ($searchKey)
		{
			foreach ($array as $key => $value)
			{
				if (strcmp($key, $searchkey)==0)
				{
					return true;
				}
			}
		}
		return false;
	}

	/**
	* Sets the authors name
	*
	* Sets the authors name of the assQuestion object
	*
	* @param string $author A string containing the name of the questions author
	* @access public
	* @see $author
	*/
	function setAuthor($author = "")
	{
		if (!$author)
		{
			$author = $this->ilias->account->fullname;
		}
		$this->author = $author;
	}

	/**
	* Sets the creator/owner
	*
	* Sets the creator/owner ID of the assQuestion object
	*
	* @param integer $owner A numerical ID to identify the owner/creator
	* @access public
	* @see $owner
	*/
	function setOwner($owner = "")
	{
		$this->owner = $owner;
	}

	/**
	* Gets the title string
	*
	* Gets the title string of the assQuestion object
	*
	* @return string The title string to describe the question
	* @access public
	* @see $title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* Gets the id
	*
	* Gets the id of the assQuestion object
	*
	* @return integer The id of the assQuestion object
	* @access public
	* @see $id
	*/
	function getId()
	{
		return $this->id;
	}

	/**
	* Gets the shuffle flag
	*
	* Gets the shuffle flag
	*
	* @return boolean The shuffle flag
	* @access public
	* @see $shuffle
	*/
	function getShuffle()
	{
		return $this->shuffle;
	}

	/**
	* Gets the test id
	*
	* Gets the test id of the assQuestion object
	*
	* @return integer The test id of the assQuestion object
	* @access public
	* @see $test_id
	*/
	function getTestId()
	{
		return $this->test_id;
	}

	/**
	* Gets the comment
	*
	* Gets the comment string of the assQuestion object
	*
	* @return string The comment string to describe the question
	* @access public
	* @see $comment
	*/
	function getComment()
	{
		return $this->comment;
	}

	/**
	* Gets the output type
	*
	* Gets the output type
	*
	* @return integer The output type of the question
	* @access public
	* @see $outputType
	*/
	function getOutputType()
	{
		return $this->outputType;
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
		return FALSE;
	}

	/**
	* Gets the estimated working time of a question
	*
	* Gets the estimated working time of a question
	*
	* @return array Estimated Working Time of a question
	* @access public
	* @see $est_working_time
	*/
	function getEstimatedWorkingTime()
	{
		if (!$this->est_working_time)
		{
			$this->est_working_time = array("h" => 0, "m" => 0, "s" => 0);
		}
		return $this->est_working_time;
	}

	/**
	* Gets the authors name
	*
	* Gets the authors name of the assQuestion object
	*
	* @return string The string containing the name of the questions author
	* @access public
	* @see $author
	*/
	function getAuthor()
	{
		return $this->author;
	}

	/**
	* Gets the creator/owner
	*
	* Gets the creator/owner ID of the assQuestion object
	*
	* @return integer The numerical ID to identify the owner/creator
	* @access public
	* @see $owner
	*/
	function getOwner()
	{
		return $this->owner;
	}

	/**
	* Get the object id of the container object
	*
	* Get the object id of the container object
	*
	* @return integer The object id of the container object
	* @access public
	* @see $obj_id
	*/
	function getObjId()
	{
		return $this->obj_id;
	}

	/**
	* Set the object id of the container object
	*
	* Set the object id of the container object
	*
	* @param integer $obj_id The object id of the container object
	* @access public
	* @see $obj_id
	*/
	function setObjId($obj_id = 0)
	{
		$this->obj_id = $obj_id;
	}

	/**
	* create page object of question
	*/
	function createPageObject()
	{
		$qpl_id = $this->getObjId();

		include_once "./Services/COPage/classes/class.ilPageObject.php";
		$this->page = new ilPageObject("qpl", 0);
		$this->page->setId($this->getId());
		$this->page->setParentId($qpl_id);
		$this->page->setXMLContent("<PageObject><PageContent>".
			"<Question QRef=\"il__qst_".$this->getId()."\"/>".
			"</PageContent></PageObject>");
		$this->page->create();
	}

	/**
	* Insert the question into a test
	*
	* Insert the question into a test
	*
	* @param integer $test_id The database id of the test
	* @access private
	*/
	function insertIntoTest($test_id)
	{
		global $ilDB;
		
		// get maximum sequence index in test
		$query = sprintf("SELECT MAX(sequence) AS seq FROM dum_test_question WHERE test_fi=%s",
			$ilDB->quote($test_id)
			);
		$result = $ilDB->query($query);
		$sequence = 1;
		if ($result->numRows() == 1)
		{
			$data = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
			$sequence = $data->seq + 1;
		}
		$query = sprintf("INSERT INTO dum_test_question (test_question_id, test_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
			$ilDB->quote($test_id),
			$ilDB->quote($this->getId()),
			$ilDB->quote($sequence)
			);
		$result = $ilDB->query($query);
		if ($result != DB_OK)
		{
		// Fehlermeldung
		}
	}

/**
* Returns the maximum points, a learner can reach answering the question
*
* Returns the maximum points, a learner can reach answering the question
*
* @param integer $question_id The database Id of the question
* @access public
* @see $points
*/
  function _getMaximumPoints($question_id) 
	{
		global $ilDB;

		$points = 0;
		$query = sprintf("SELECT points FROM qpl_questions WHERE question_id = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			$points = $row["points"];
		}
		return $points;
  }

	/**
	* Returns question information from the database
	*
	* Returns question information from the database
	*
	* @param integer $question_id The database Id of the question
	* @return array The database row containing the question data
	* @access public static
	*/
	function &_getQuestionInfo($question_id)
	{
		global $ilDB;

		$query = sprintf("SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_question_type, qpl_questions WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_question_type.question_type_id",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			return $result->fetchRow(MDB2_FETCHMODE_ASSOC);
		}
		else return array();
	}
	
	/**
	* Returns the number of suggested solutions associated with a question
	*
	* Returns the number of suggested solutions associated with a question
	*
	* @param integer $question_id The database Id of the question
	* @return integer The number of suggested solutions
	* @access public static
	*/
	function _getSuggestedSolutionCount($question_id)
	{
		global $ilDB;

		$query = sprintf("SELECT suggested_solution_id FROM qpl_suggested_solutions WHERE question_fi = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		return $result->numRows();
	}

/**
* Returns a suggested solution for a given subquestion index
*
* Returns a suggested solution for a given subquestion index
*
* @param integer $question_id The database Id of the question
* @param integer $subquestion_index The index of a subquestion (i.e. a close test gap). Usually 0
* @return array A suggested solution array containing the internal link
* @access public
*/
	function &_getSuggestedSolution($question_id, $subquestion_index = 0)
	{
		global $ilDB;

		$query = sprintf("SELECT * FROM qpl_suggested_solutions WHERE question_fi = %s AND subquestion_index = %s",
			$ilDB->quote($question_id . ""),
			$ilDB->quote($subquestion_index . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return array(
				"internal_link" => $row["internal_link"],
				"import_id" => $row["import_id"]
			);
		}
		else
		{
			return array();
		}
	}
	
	/**
	* Returns the points, a learner has reached answering the question
	*
	* Returns the points, a learner has reached answering the question
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @param integer $question_id The database Id of the question
	* @access public static
	*/
	function _getReachedPoints($active_id, $question_id, $pass = NULL)
	{
		global $ilDB;

		$points = 0;
		if (is_null($pass))
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$pass = assQuestion::_getSolutionMaxPass($question_id, $active_id);
		}
		$query = sprintf("SELECT * FROM tst_test_result WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($question_id . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			$points = $row["points"];
		}
		return $points;
	}

	/**
	* Returns the points, a learner has reached answering the question
	*
	* Returns the points, a learner has reached answering the question
	* This is the fast way to get the points directly from the database.
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @access public
	*/
	function getReachedPoints($active_id, $pass = NULL)
	{
		return $this->_getReachedPoints($active_id, $this->getId(), $pass);
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
		return 0;
	}

	/**
	* Saves the learners input of the question to the database
	*
	* Saves the learners input of the question to the database
	*
*	* @param integer $test_id The database id of the test containing this question
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
		$reached_points = $this->calculateReachedPoints($active_id, $pass);
		$query = sprintf("REPLACE INTO tst_test_result (active_fi, question_fi, pass, points) VALUES (%s, %s, %s, %s)",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($pass . ""),
			$ilDB->quote($reached_points . "")
		);
		$result = $ilDB->query($query);
		include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$this->logAction(sprintf($this->lng->txtlng("assessment", "log_user_answered_question", ilObjAssessmentFolder::_getLogLanguage()), $reached_points), $active_id, $this->getId());
		}

		// update test pass results
		$this->_updateTestPassResults($active_id, $pass);

		// Update objective status
		include_once 'Modules/Course/classes/class.ilCourseObjectiveResult.php';
		ilCourseObjectiveResult::_updateObjectiveResult($ilUser->getId(),$active_id,$this->getId());
		
	}

	function _updateTestPassResults($active_id, $pass)
	{
		global $ilDB;
		// update test pass results
		$query = sprintf("SELECT SUM(points) AS reachedpoints FROM tst_test_result WHERE active_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() > 0)
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			$newresultquery = sprintf("REPLACE INTO tst_test_pass_result SET active_fi = %s, pass = %s, points = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($pass . ""),
				$ilDB->quote((($row["reachedpoints"]) ? $row["reachedpoints"] : 0) . "")
			);
			$ilDB->query($newresultquery);
		}
	}
/**
* Logs an action into the Test&Assessment log
* 
* Logs an action into the Test&Assessment log
*
* @param string $logtext The log text
* @param integer $question_id If given, saves the question id to the database
* @access public
*/
	function logAction($logtext = "", $active_id = "", $question_id = "")
	{
		global $ilUser;

		$original_id = "";
		if (strcmp($question_id, "") != 0)
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$original_id = assQuestion::_getOriginalId($question_id);
		}
		include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		ilObjAssessmentFolder::_addLog($ilUser->id, ilObjTest::_getObjectIDFromActiveID($active_id), $logtext, $question_id, $original_id);
	}
	
/**
* Logs an action into the Test&Assessment log
* 
* Logs an action into the Test&Assessment log
*
* @param string $logtext The log text
* @param integer $question_id If given, saves the question id to the database
* @access public
*/
	function _logAction($logtext = "", $active_id = "", $question_id = "")
	{
		global $ilUser;

		$original_id = "";
		if (strcmp($question_id, "") != 0)
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$original_id = assQuestion::_getOriginalId($question_id);
		}
		include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		ilObjAssessmentFolder::_addLog($ilUser->id, ilObjTest::_getObjectIDFromActiveID($active_id), $logtext, $question_id, $original_id);
	}
	
	/**
	* Returns the image path for web accessable images of a question
	*
	* Returns the image path for web accessable images of a question.
	* The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
	*
	* @access public
	*/
	function getJavaPath() {
		return CLIENT_WEB_DIR . "/assessment/$this->obj_id/$this->id/java/";
	}

	/**
	* Returns the image path for web accessable images of a question
	*
	* Returns the image path for web accessable images of a question.
	* The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
	*
	* @access public
	*/
	function getImagePath()
	{
		return CLIENT_WEB_DIR . "/assessment/$this->obj_id/$this->id/images/";
	}

	/**
	* Returns the image path for web accessable flash files of a question
	*
	* Returns the image path for web accessable flash files of a question.
	* The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/flash
	*
	* @access public
	*/
	function getFlashPath() 
	{
		return CLIENT_WEB_DIR . "/assessment/$this->obj_id/$this->id/flash/";
	}

	/**
	* Returns the web image path for web accessable java applets of a question
	*
	* Returns the web image path for web accessable java applets of a question.
	* The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/java
	*
	* @access public
	*/
	function getJavaPathWeb()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/$this->obj_id/$this->id/java/";
		return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
	}

	/**
	* Returns the web image path for web accessable images of a question
	*
	* Returns the web image path for web accessable images of a question.
	* The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
	*
	* @access public
	*/
	function getImagePathWeb()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/$this->obj_id/$this->id/images/";
		return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
	}

	/**
	* Returns the web image path for web accessable flash applications of a question
	*
	* Returns the web image path for web accessable flash applications of a question.
	* The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/flash
	*
	* @access public
	*/
	function getFlashPathWeb()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/$this->obj_id/$this->id/flash/";
		return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
	}
	
	/**
	* Loads solutions of a given user from the database an returns it
	*
	* Loads solutions of a given user from the database an returns it
	*
	* @param integer $test_id The database id of the test containing this question
	* @access public
	* @see $answers
	*/
	function &getSolutionValues($active_id, $pass = NULL)
	{
		global $ilDB;

		$values = array();
		
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
			/*
			$query = sprintf("SELECT MAX(pass) AS maxpass FROM tst_test_result WHERE active_fi = %s AND question_fi = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($this->getId() . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
				$pass = $row["maxpass"];
			}
			else
			{
				return $values;
			}
			*/
		}		

		$query = sprintf("SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		while	($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			array_push($values, $row);
		}

		return $values;
	}

	/**
	* Checks whether the question is in use or not
	*
	* Checks whether the question is in use or not
	*
	* @return boolean The number of datasets which are affected by the use of the query.
	* @access public
	*/
	function isInUse($question_id = "")
	{
		global $ilDB;
		
		if ($question_id < 1) $question_id = $this->id;
		$query = sprintf("SELECT COUNT(qpl_questions.question_id) AS question_count FROM qpl_questions, tst_test_question WHERE qpl_questions.original_id = %s AND qpl_questions.question_id = tst_test_question.question_fi",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
		$count = $row["question_count"];

		$query = sprintf("SELECT DISTINCT tst_active.test_fi, qpl_questions.question_id FROM qpl_questions, tst_test_random_question, tst_active WHERE qpl_questions.original_id = %s AND qpl_questions.question_id = tst_test_random_question.question_fi AND tst_test_random_question.active_fi = tst_active.active_id",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		$count += $result->numRows();

		return $count;
	}

	/**
	* Checks whether the question is a clone of another question or not
	*
	* Checks whether the question is a clone of another question or not
	*
	* @return boolean TRUE if the question is a clone, otherwise FALSE
	* @access public
	*/
	function isClone($question_id = "")
	{
		global $ilDB;
		
		if ($question_id < 1) $question_id = $this->id;
		$query = sprintf("SELECT original_id FROM qpl_questions WHERE question_id = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		$row = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
		if ($row->original_id > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* Shuffles the values of a given array
	*
	* Shuffles the values of a given array
	*
	* @param array $array An array which should be shuffled
	* @access public
	*/
	function pcArrayShuffle($array)
	{
		mt_srand((double)microtime()*1000000);
		$i = count($array);
		if ($i > 0)
		{
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
		}
		return $array;
	}

	/**
	* get question type for question id
	*
	* note: please don't use $this in this class to allow static calls
	*/
	function getQuestionTypeFromDb($question_id)
	{
		global $ilDB;

		$query = sprintf("SELECT qpl_question_type.type_tag FROM qpl_question_type, qpl_questions WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_question_type.question_type_id",
			$ilDB->quote($question_id));

		$result = $ilDB->query($query);
		$data = $result->fetchRow(MDB2_FETCHMODE_OBJECT);

		return $data->type_tag;
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
		return "";
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
	* Deletes datasets from answers tables
	*
	* @param integer $question_id The question id which should be deleted in the answers table
	* @access public
	*/
	function deleteAnswers($question_id)
	{
		global $ilDB;
		$answer_table_name = $this->getAnswerTableName();
		if (strlen($answer_table_name))
		{
			$query = sprintf("DELETE FROM $answer_table_name WHERE question_fi = %s",
				$ilDB->quote($question_id . "")
			);
			$result = $ilDB->query($query);
		}
	}

	/**
	* Deletes datasets from the additional question table in the database
	*
	* Deletes datasets from the additional question table in the database
	*
	* @param integer $question_id The question id which should be deleted in the additional question table
	* @access public
	*/
	function deleteAdditionalTableData($question_id)
	{
		global $ilDB;
		$additional_table_name = $this->getAdditionalTableName();
		$query = sprintf("DELETE FROM $additional_table_name WHERE question_fi = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
	}

	/**
	* Deletes a question from the database
	*
	* Deletes a question and all materials from the database
	*
	* @param integer $question_id The database id of the question
	* @access private
	*/
	function delete($question_id)
	{
		global $ilDB;
		
		if ($question_id < 1)
		return;

		$query = sprintf("SELECT obj_fi FROM qpl_questions WHERE question_id = %s",
			$ilDB->quote($question_id)
			);
    	$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			$obj_id = $row["obj_fi"];
		}
		else
		{
			return;
		}

		include_once "./Services/COPage/classes/class.ilPageObject.php";
		$page = new ilPageObject("qpl", $question_id);
		$page->delete();
		
		$query = sprintf("DELETE FROM qpl_questions WHERE question_id = %s",
			$ilDB->quote($question_id)
		);
		$result = $ilDB->query($query);
		
		$this->deleteAdditionalTableData($question_id);
		$this->deleteAnswers($question_id);

		// delete the question in the tst_test_question table (list of test questions)
		$querydelete = sprintf("DELETE FROM tst_test_question WHERE question_fi = %s", $ilDB->quote($question_id));
		$deleteresult = $ilDB->query($querydelete);

		// delete suggested solutions contained in the question
		$querydelete = sprintf("DELETE FROM qpl_suggested_solutions WHERE question_fi = %s", $ilDB->quote($question_id));
		$deleteresult = $ilDB->query($querydelete);
				
		$directory = CLIENT_WEB_DIR . "/assessment/" . $obj_id . "/$question_id";
		if (preg_match("/\d+/", $obj_id) and preg_match("/\d+/", $question_id) and is_dir($directory))
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::delDir($directory);
		}

		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $question_id);
		// remaining usages are not in text anymore -> delete them
		// and media objects (note: delete method of ilObjMediaObject
		// checks whether object is used in another context; if yes,
		// the object is not deleted!)
		foreach($mobs as $mob)
		{
			ilObjMediaObject::_removeUsage($mob, "qpl:html", $question_id);
			$mob_obj =& new ilObjMediaObject($mob);
			$mob_obj->delete();
		}

		// update question count of question pool
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		ilObjQuestionPool::_updateQuestionCount($this->obj_id);
	}

	/**
	* get total number of answers
	*/
	function getTotalAnswers()
	{
		return $this->_getTotalAnswers($this->id);
	}

	/**
	* get number of answers for question id (static)
	* note: do not use $this inside this method
	*
	* @param	int		$a_q_id		question id
	*/
	function _getTotalAnswers($a_q_id)
	{
		global $ilDB;

		// get all question references to the question id
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE original_id = %s OR question_id = %s",
			$ilDB->quote($a_q_id),
			$ilDB->quote($a_q_id)
		);

		$result = $ilDB->query($query);

		if ($result->numRows() == 0)
		{
			return 0;
		}
		$found_id = array();
		while ($row = $result->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			array_push($found_id, $row->question_id);
		}

		$query = sprintf("SELECT * FROM tst_test_result WHERE question_fi IN ('%s')",
			join($found_id, "','"));
		$result = $ilDB->query($query);

		return $result->numRows();
	}


	/**
	* get number of answers for question id (static)
	* note: do not use $this inside this method
	*
	* @param	int		$a_q_id		question id
	*/
	function _getTotalRightAnswers($a_q_id)
	{
		global $ilDB;
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE original_id = %s OR question_id = %s",
			$ilDB->quote($a_q_id),
			$ilDB->quote($a_q_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 0)
		{
			return 0;
		}
		$found_id = array();
		while ($row = $result->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			array_push($found_id, $row->question_id);
		}
		$query = sprintf("SELECT * FROM tst_test_result WHERE question_fi IN ('%s')",
			join($found_id, "','"));
		$result = $ilDB->query($query);
		$answers = array();
		while ($row = $result->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$reached = $row->points; 
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$max = assQuestion::_getMaximumPoints($row->question_fi);
			array_push($answers, array("reached" => $reached, "max" => $max));
		}
		$max = 0.0;
		$reached = 0.0;
		foreach ($answers as $key => $value)
		{
			$max += $value["max"];
			$reached += $value["reached"];
		}
		if ($max > 0)
		{
			return $reached / $max;
		}
		else
		{
			return 0;
		}
	}

	/**
	* Returns the title of a question
	*
	* @param	int		$a_q_id		question id
	*/
	function _getTitle($a_q_id)
	{
		global $ilDB;
		$query = sprintf("SELECT title FROM qpl_questions WHERE question_id = %s",
			$ilDB->quote($a_q_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row["title"];
		}
		else
		{
			return "";
		}
	}
	
	function copyXHTMLMediaObjectsOfQuestion($a_q_id)
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $a_q_id);
		foreach ($mobs as $mob)
		{
			ilObjMediaObject::_saveUsage($mob, "qpl:html", $this->getId());
		}
	}
	
	function syncXHTMLMediaObjectsOfQuestion()
	{
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
		foreach ($mobs as $mob)
		{
			ilObjMediaObject::_saveUsage($mob, "qpl:html", $this->original_id);
		}
	}
	
	function copyPageOfQuestion($a_q_id)
	{
		if ($a_q_id > 0)
		{
			include_once "./Services/COPage/classes/class.ilPageObject.php";
			$page = new ilPageObject("qpl", $a_q_id);

			$xml = str_replace("il__qst_".$a_q_id, "il__qst_".$this->id,
				$page->getXMLContent());
			$this->page->setXMLContent($xml);
			$this->page->saveMobUsage($xml);
			$this->page->updateFromXML();
		}
	}

	function getPageOfQuestion()
	{
		include_once "./Services/COPage/classes/class.ilPageObject.php";
		$page = new ilPageObject("qpl", $this->id);
		return $page->getXMLContent();
	}

/**
* Returns the question type of a question with a given id
* 
* Returns the question type of a question with a given id
*
* @param integer $question_id The database id of the question
* @result string The question type string
* @access private
*/
  function _getQuestionType($question_id) {
		global $ilDB;

    if ($question_id < 1)
      return "";

    $query = sprintf("SELECT type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_question_type.question_type_id",
      $ilDB->quote($question_id)
    );
    $result = $ilDB->query($query);
    if ($result->numRows() == 1) {
      $data = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
      return $data->type_tag;
    } else {
      return "";
    }
  }

/**
* Returns the question title of a question with a given id
* 
* Returns the question title of a question with a given id
*
* @param integer $question_id The database id of the question
* @result string The question title
* @access private
*/
  function _getQuestionTitle($question_id) {
		global $ilDB;

    if ($question_id < 1)
      return "";

    $query = sprintf("SELECT title FROM qpl_questions WHERE qpl_questions.question_id = %s",
      $ilDB->quote($question_id)
    );
    $result = $ilDB->query($query);
    if ($result->numRows() == 1) {
      $data = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
      return $data["title"];
    } else {
      return "";
    }
  }

/**
* Loads the question from the database
*
* Loads the question from the database
*
* @param integer $question_id A unique key which defines the question in the database
* @access public
*/
	function loadFromDb($question_id)
	{
		global $ilDB;
		
		$query = sprintf("SELECT * FROM qpl_suggested_solutions WHERE question_fi = %s",
			$ilDB->quote($this->getId() . "")
		);
		$result = $ilDB->query($query);
		$this->suggested_solutions = array();
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				$this->suggested_solutions[$row["subquestion_index"]] = array(
					"internal_link" => $row["internal_link"],
					"import_id" => $row["import_id"]
				);
			}
		}
	}

	/**
	* Saves the question to the database
	*
	* Saves the question to the database
	*
	* @param integer $original_id
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		global $ilDB;
		
		include_once "./Services/COPage/classes/class.ilInternalLink.php";
		$query = sprintf("DELETE FROM qpl_suggested_solutions WHERE question_fi = %s",
			$ilDB->quote($this->getId() . "")
		);
		$result = $ilDB->query($query);
		ilInternalLink::_deleteAllLinksOfSource("qst", $this->getId());
		foreach ($this->suggested_solutions as $index => $solution)
		{
			$query = sprintf("INSERT INTO qpl_suggested_solutions (suggested_solution_id, question_fi, internal_link, import_id, subquestion_index, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
				$ilDB->quote($this->getId() . ""),
				$ilDB->quote($solution["internal_link"] . ""),
				$ilDB->quote($solution["import_id"] . ""),
				$ilDB->quote($index . "")
			);
			$ilDB->query($query);
			if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches))
			{
				ilInternalLink::_saveLink("qst", $this->getId(), $matches[2], $matches[3], $matches[1]);
			}
		}
		// remove unused media objects from ILIAS
		$this->cleanupMediaObjectUsage();
		// update question count of question pool
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		ilObjQuestionPool::_updateQuestionCount($this->obj_id);
	}
	
/**
* Deletes all suggestes solutions in the database
*
* Deletes all suggestes solutions in the database
*
* @access public
*/
	function deleteSuggestedSolutions()
	{
		global $ilDB;
		// delete the links in the qpl_suggested_solutions table
		$query = sprintf("DELETE FROM qpl_suggested_solutions WHERE question_fi = %s",
			$ilDB->quote($this->getId() . "")
		);
		$result = $ilDB->query($query);
		// delete the links in the int_link table
		include_once "./Services/COPage/classes/class.ilInternalLink.php";
		ilInternalLink::_deleteAllLinksOfSource("qst", $this->getId());
	}
	
/**
* Returns a suggested solution for a given subquestion index
*
* Returns a suggested solution for a given subquestion index
*
* @param integer $subquestion_index The index of a subquestion (i.e. a close test gap). Usually 0
* @return array A suggested solution array containing the internal link
* @access public
*/
	function getSuggestedSolution($subquestion_index = 0)
	{
		if (array_key_exists($subquestion_index, $this->suggested_solutions))
		{
			return $this->suggested_solutions[$subquestion_index];
		}
		else
		{
			return array();
		}
	}

/**
* Returns the title of a suggested solution at a given subquestion_index
*
* Returns the title of a suggested solution at a given subquestion_index.
* This can be usable for displaying suggested solutions
*
* @param integer $subquestion_index The index of a subquestion (i.e. a close test gap). Usually 0
* @return string A string containing the type and title of the internal link
* @access public
*/
	function getSuggestedSolutionTitle($subquestion_index = 0)
	{
		if (array_key_exists($subquestion_index, $this->suggested_solutions))
		{
			$title = $this->suggested_solutions[$subquestion_index]["internal_link"];
			// TO DO: resolve internal link an get link type and title
		}
		else
		{
			$title = "";
		}
		return $title;
	}

/**
* Sets a suggested solution for the question
*
* Sets a suggested solution for the question.
* If there is more than one subquestion (i.e. close questions) may enter a subquestion index.
*
* @param string $solution_id An internal link pointing to the suggested solution
* @param integer $subquestion_index The index of a subquestion (i.e. a close test gap). Usually 0
* @param boolean $is_import A boolean indication that the internal link was imported from another ILIAS installation
* @access public
*/
	function setSuggestedSolution($solution_id = "", $subquestion_index = 0, $is_import = false)
	{
		if (strcmp($solution_id, "") != 0)
		{
			$import_id = "";
			if ($is_import)
			{
				$import_id = $solution_id;
				$solution_id = $this->_resolveInternalLink($import_id);
			}
			$this->suggested_solutions[$subquestion_index] = array(
				"internal_link" => $solution_id,
				"import_id" => $import_id
			);
		}
	}
	
	function _resolveInternalLink($internal_link)
	{
		if (preg_match("/il_(\d+)_(\w+)_(\d+)/", $internal_link, $matches))
		{
			include_once "./Services/COPage/classes/class.ilInternalLink.php";
			include_once "./Modules/LearningModule/classes/class.ilLMObject.php";
			include_once "./Modules/Glossary/classes/class.ilGlossaryTerm.php";
			switch ($matches[2])
			{
				case "lm":
					$resolved_link = ilLMObject::_getIdForImportId($internal_link);
					break;
				case "pg":
					$resolved_link = ilInternalLink::_getIdForImportId("PageObject", $internal_link);
					break;
				case "st":
					$resolved_link = ilInternalLink::_getIdForImportId("StructureObject", $internal_link);
					break;
				case "git":
					$resolved_link = ilInternalLink::_getIdForImportId("GlossaryItem", $internal_link);
					break;
				case "mob":
					$resolved_link = ilInternalLink::_getIdForImportId("MediaObject", $internal_link);
					break;
			}
			if (strcmp($resolved_link, "") == 0)
			{
				$resolved_link = $internal_link;
			}
		}
		else
		{
			$resolved_link = $internal_link;
		}
		return $resolved_link;
	}
	
	function _resolveIntLinks($question_id)
	{
		global $ilDB;
		$resolvedlinks = 0;
		$query = sprintf("SELECT * FROM qpl_suggested_solutions WHERE question_fi = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				$internal_link = $row["internal_link"];
				include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
				$resolved_link = assQuestion::_resolveInternalLink($internal_link);
				if (strcmp($internal_link, $resolved_link) != 0)
				{
					// internal link was resolved successfully
					$queryupdate = sprintf("UPDATE qpl_suggested_solutions SET internal_link = %s WHERE suggested_solution_id = %s",
						$ilDB->quote($resolved_link),
						$ilDB->quote($row["suggested_solution_id"] . "")
					);
					$updateresult = $ilDB->query($queryupdate);
					$resolvedlinks++;
				}
			}
		}
		if ($resolvedlinks)
		{
			// there are resolved links -> reenter theses links to the database

			// delete all internal links from the database
			include_once "./Services/COPage/classes/class.ilInternalLink.php";
			ilInternalLink::_deleteAllLinksOfSource("qst", $question_id);

			$query = sprintf("SELECT * FROM qpl_suggested_solutions WHERE question_fi = %s",
				$ilDB->quote($question_id . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
				{
					if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $row["internal_link"], $matches))
					{
						ilInternalLink::_saveLink("qst", $question_id, $matches[2], $matches[3], $matches[1]);
					}
				}
			}
		}
	}
	
	function _getInternalLinkHref($target = "")
	{
		global $ilDB;
		$linktypes = array(
			"lm" => "LearningModule",
			"pg" => "PageObject",
			"st" => "StructureObject",
			"git" => "GlossaryItem",
			"mob" => "MediaObject"
		);
		$href = "";
		if (preg_match("/il__(\w+)_(\d+)/", $target, $matches))
		{
			$type = $matches[1];
			$target_id = $matches[2];
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			switch($linktypes[$matches[1]])
			{
				case "LearningModule":
					$href = "./goto.php?target=" . $type . "_" . $target_id;
					break;
				case "PageObject":
				case "StructureObject":
					$href = "./goto.php?target=" . $type . "_" . $target_id;
					break;
				case "GlossaryItem":
					$href = "./goto.php?target=" . $type . "_" . $target_id;
					break;
				case "MediaObject":
					$href = "./ilias.php?baseClass=ilLMPresentationGUI&obj_type=" . $linktypes[$type] . "&cmd=media&ref_id=".$_GET["ref_id"]."&mob_id=".$target_id;
					break;
			}
		}
		return $href;
	}
	
/**
* Returns the original id of a question
*
* Returns the original id of a question
*
* @param integer $question_id The database id of the question
* @return integer The database id of the original question
* @access public
*/
	function _getOriginalId($question_id)
	{
		global $ilDB;
		$query = sprintf("SELECT * FROM qpl_questions WHERE question_id = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() > 0)
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			if ($row["original_id"] > 0)
			{
				return $row["original_id"];
			}
			else
			{
				return $row["question_id"];
			}
		}
		else
		{
			return "";
		}
	}

	function syncWithOriginal()
	{
		global $ilDB;
		
		include_once "./Services/COPage/classes/class.ilInternalLink.php";
		$query = sprintf("DELETE FROM qpl_suggested_solutions WHERE question_fi = %s",
			$ilDB->quote($this->original_id . "")
		);
		$result = $ilDB->query($query);
		ilInternalLink::_deleteAllLinksOfSource("qst", $this->original_id);
		foreach ($this->suggested_solutions as $index => $solution)
		{
			$query = sprintf("INSERT INTO qpl_suggested_solutions (suggested_solution_id, question_fi, internal_link, import_id, subquestion_index, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
				$ilDB->quote($this->original_id . ""),
				$ilDB->quote($solution["internal_link"] . ""),
				$ilDB->quote($solution["import_id"] . ""),
				$ilDB->quote($index . "")
			);
			$ilDB->query($query);
			if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches))
			{
				ilInternalLink::_saveLink("qst", $this->original_id, $matches[2], $matches[3], $matches[1]);
			}
		}
		$this->syncFeedbackGeneric();
		$this->syncXHTMLMediaObjectsOfQuestion();
	}

	function createRandomSolution($test_id, $user_id)
	{
	}

/**
* Returns true if the question already exists in the database
*
* Returns true if the question already exists in the database
*
* @param integer $question_id The database id of the question
* @result boolean True, if the question exists, otherwise False
* @access public
*/
	function _questionExists($question_id)
	{
		global $ilDB;

		if ($question_id < 1)
		{
			return false;
		}
		
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE question_id = %s",
			$ilDB->quote($question_id)
		);
    $result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

/**
* Creates an instance of a question with a given question id
*
* Creates an instance of a question with a given question id
*
* @param integer $question_id The question id
* @return object The question instance
* @access public
*/
  function &_instanciateQuestion($question_id) 
	{
		if (strcmp($question_id, "") != 0)
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$question_type = assQuestion::_getQuestionType($question_id);
			assQuestion::_includeClass($question_type);
			$question = new $question_type();
			$question->loadFromDb($question_id);
			return $question;
		}
  }
	
/**
* Returns the maximum available points for the question
*
* Returns the maximum available points for the question
*
* @return integer The points
* @access public
*/
	function getPoints()
	{
		if (strcmp($this->points, "") == 0)
		{
			return 0;
		}
		else
		{
			return $this->points;
		}
	}

	
/**
* Sets the maximum available points for the question
*
* Sets the maximum available points for the question
*
* @param integer $a_points The points
* @access public
*/
	function setPoints($a_points)
	{
		$this->points = $a_points;
	}
	
/**
* Returns the maximum pass a users question solution
*
* Returns the maximum pass a users question solution
*
* @param return integer The maximum pass of the users solution
* @access public
*/
	function getSolutionMaxPass($active_id)
	{
		return $this->_getSolutionMaxPass($this->getId(), $active_id);
	}

/**
* Returns the maximum pass a users question solution
*
* Returns the maximum pass a users question solution
*
* @param return integer The maximum pass of the users solution
* @access public
*/
	function _getSolutionMaxPass($question_id, $active_id)
	{
/*		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$pass = ilObjTest::_getPass($active_id);
		return $pass;*/

		// the following code was the old solution which added the non answered
		// questions of a pass from the answered questions of the previous pass
		// with the above solution, only the answered questions of the last pass are counted
		global $ilDB;

		$query = sprintf("SELECT MAX(pass) as maxpass FROM tst_test_result WHERE active_fi = %s AND question_fi = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($question_id . "")
		);
    $result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row["maxpass"];
		}
		else
		{
			return 0;
		}
	}

/**
* Returns true if the question is writeable by a certain user
*
* Returns true if the question is writeable by a certain user
*
* @param integer $question_id The database id of the question
* @param integer $user_id The database id of the user
* @result boolean True, if the question exists, otherwise False
* @access public
*/
	function _isWriteable($question_id, $user_id)
	{
		global $ilDB;

		if (($question_id < 1) || ($user_id < 1))
		{
			return false;
		}
		
		$query = sprintf("SELECT obj_fi FROM qpl_questions WHERE question_id = %s",
			$ilDB->quote($question_id . "")
		);
    $result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			$qpl_object_id = $row["obj_fi"];
			include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
			return ilObjQuestionPool::_isWriteable($qpl_object_id, $user_id);
		}
		else
		{
			return false;
		}
	}

	/**
	* Checks whether the question is used in a random test or not
	*
	* Checks whether the question is used in a random test or not
	*
	* @return boolean The number how often the question is used in a random test
	* @access public
	*/
	function _isUsedInRandomTest($question_id = "")
	{
		global $ilDB;
		
		if ($question_id < 1) return 0;
		$query = sprintf("SELECT test_random_question_id FROM tst_test_random_question WHERE question_fi = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		return $result->numRows();
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
	function calculateReachedPoints($active_id, $pass = NULL, $points = 0)
	{
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$count_system = ilObjTest::_getCountSystem($active_id);
		if ($count_system == 1)
		{
			if ($points != $this->getMaximumPoints())
			{
				$points = 0;
			}
		}
		$score_cutting = ilObjTest::_getScoreCutting($active_id);
		if ($score_cutting == 0)
		{
			if ($points < 0)
			{
				$points = 0;
			}
		}
		return $points;
	}

	/**
	* Returns true if the question was worked through in the given pass
	*
	* Returns true if the question was worked through in the given pass
	* Worked through means that the user entered at least one value
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @param integer $question_id The database Id of the question
	* @access public static
	*/
	function _isWorkedThrough($active_id, $question_id, $pass = NULL)
	{
		global $ilDB;

		$points = 0;
		if (is_null($pass))
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$pass = assQuestion::_getSolutionMaxPass($question_id, $active_id);
		}
		$query = sprintf("SELECT solution_id FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($question_id . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function getMultilineAnswerSetting()
	{
		global $ilUser;

		$multilineAnswerSetting = $ilUser->getPref("tst_multiline_answers");
		if ($multilineAnswerSetting != 1)
		{
			$multilineAnswerSetting = 0;
		}
		return $multilineAnswerSetting;
	}
	
	function setMultilineAnswerSetting($a_setting = 0)
	{
		global $ilUser;
		$ilUser->writePref("tst_multiline_answers", $a_setting);
	}
	/**
	* Checks if an array of question ids is answered by an user or not
	*
	* @param int user_id
	* @param array $question_ids user id array
	
	* @return boolean 
	* @access public static
	*/
	function _areAnswered($a_user_id,$a_question_ids)
	{
		global $ilDB;

		$query = "SELECT DISTINCT(question_fi) FROM tst_test_result JOIN tst_active ".
			"ON (active_id = active_fi) ".
			"WHERE question_fi IN ('".implode("','",$a_question_ids)."') ".
			"AND user_fi = '".$a_user_id."'";
		$res = $ilDB->query($query);
		return ($res->numRows() == count($a_question_ids)) ? true : false;
	}
	
	/**
	* Checks if a given string contains HTML or not
	*
	* @param string $a_text Text which should be checked
	* @return boolean 
	* @access public
	*/
	function isHTML($a_text)
	{
		if (preg_match("/<[^>]*?>/", $a_text))
		{
			return TRUE;
		}
		else
		{
			return FALSE; 
		}
	}
	
	/**
	* Prepares a string for a text area output in tests
	*
	* @param string $txt_output String which should be prepared for output
	* @access public
	*/
	function prepareTextareaOutput($txt_output, $prepare_for_latex_output = FALSE)
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		return ilUtil::prepareTextareaOutput($txt_output, $prepare_for_latex_output);
	}

	/**
	* Reads an QTI material tag an creates a text string
	*
	* @param string $a_material QTI material tag
	* @return string text or xhtml string
	* @access public
	*/
	function QTIMaterialToString($a_material)
	{
		$result = "";
		for ($i = 0; $i < $a_material->getMaterialCount(); $i++)
		{
			$material = $a_material->getMaterial($i);
			if (strcmp($material["type"], "mattext") == 0)
			{
				$result .= $material["material"]->getContent();
			}
			if (strcmp($material["type"], "matimage") == 0)
			{
				$matimage = $material["material"];
				if (preg_match("/(il_([0-9]+)_mob_([0-9]+))/", $matimage->getLabel(), $matches))
				{
					// import an mediaobject which was inserted using tiny mce
					if (!is_array($_SESSION["import_mob_xhtml"])) $_SESSION["import_mob_xhtml"] = array();
					array_push($_SESSION["import_mob_xhtml"], array("mob" => $matimage->getLabel(), "uri" => $matimage->getUri()));
				}
			}
		}
		return $result;
	}
	
	/**
	* Creates a QTI material tag from a plain text or xhtml text
	*
	* @param object $a_xml_writer Reference to the ILIAS XML writer
	* @param string $a_material plain text or html text containing the material
	* @return string QTI material tag
	* @access public
	*/
	function addQTIMaterial(&$a_xml_writer, $a_material, $close_material_tag = TRUE, $add_mobs = TRUE)
	{
		include_once "./Services/RTE/classes/class.ilRTE.php";
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

		$a_xml_writer->xmlStartTag("material");
		$attrs = array(
			"texttype" => "text/plain"
		);
		if ($this->isHTML($a_material))
		{
			$attrs["texttype"] = "text/xhtml";
		}
		$a_xml_writer->xmlElement("mattext", $attrs, ilRTE::_replaceMediaObjectImageSrc($a_material, 0));
		if ($add_mobs)
		{
			$mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
			foreach ($mobs as $mob)
			{
				$moblabel = "il_" . IL_INST_ID . "_mob_" . $mob;
				if (strpos($a_material, "mm_$mob") !== FALSE)
				{
					$mob_obj =& new ilObjMediaObject($mob);
					$imgattrs = array(
						"label" => $moblabel,
						"uri" => "objects/" . "il_" . IL_INST_ID . "_mob_" . $mob . "/" . $mob_obj->getTitle()
					);
					$a_xml_writer->xmlElement("matimage", $imgattrs, NULL);
				}
			}
		}		
		if ($close_material_tag) $a_xml_writer->xmlEndTag("material");
	}
	
	function createNewImageFileName($image_filename)
	{
		$extension = "";
		if (preg_match("/.*\.(png|jpg|gif|jpeg)$/i", $image_filename, $matches))
		{
			$extension = "." . $matches[1];
		}
		$image_filename = md5($image_filename) . $extension;
		return $image_filename;
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
	function _setReachedPoints($active_id, $question_id, $points, $maxpoints, $pass = NULL)
	{
		global $ilDB;
		
		if ($points <= $maxpoints)
		{
			if (is_null($pass))
			{
				$pass = assQuestion::_getSolutionMaxPass($question_id, $active_id);
			}

			// retrieve the already given points
			$old_points = 0;
			$query = sprintf("SELECT points FROM tst_test_result WHERE active_fi = %s AND question_fi = %s AND pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($question_id . ""),
				$ilDB->quote($pass . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
				$old_points = $row["points"];
			}
			$query = sprintf("UPDATE tst_test_result SET points = %s WHERE active_fi = %s AND question_fi = %s AND pass = %s",
				$ilDB->quote($points . ""),
				$ilDB->quote($active_id . ""),
				$ilDB->quote($question_id . ""),
				$ilDB->quote($pass . "")
			);
			$result = $ilDB->query($query);
			if ($result != DB_OK)
			{
				return FALSE;
			}
			assQuestion::_updateTestPassResults($active_id, $pass);
			// finally update objective result
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			include_once './Modules/Course/classes/class.ilCourseObjectiveResult.php';
			ilCourseObjectiveResult::_updateObjectiveResult(ilObjTest::_getUserIdFromActiveId($active_id),$question_id,$points);

			include_once ("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				global $lng;
				assQuestion::_logAction(sprintf($lng->txtlng("assessment", "log_answer_changed_points", ilObjAssessmentFolder::_getLogLanguage()), $old_points, $points), $active_id, $question_id);
			}
			
			return TRUE;
		}
			else
		{
			return FALSE;
		}
	}
	
/**
* Gets the question text
*
* Gets the question string of the question object
*
* @return string The question string of the question object
* @access public
* @see $question
*/
  function getQuestion() 
	{
    return $this->question;
  }

/**
* Sets the question text
*
* Sets the question string of the question object
*
* @param string $question A string containing the question text
* @access public
* @see $question
*/
  function setQuestion($question = "") 
	{
    $this->question = $question;
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
		// must be overwritten in every parent class
		return "";
	}
	
	/**
	* Returns the question type of the question
	*
	* Returns the question type of the question
	*
	* @return integer The question type of the question
	* @access public
	*/
	function getQuestionTypeID()
	{
		global $ilDB;
		
		$query = sprintf("SELECT question_type_id FROM qpl_question_type WHERE type_tag = %s",
			$ilDB->quote($this->getQuestionType() . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return $row["question_type_id"];
		}
		return 0;
	}
	
	/**
	* Saves generic feedback to the database
	*
	* Saves generic feedback to the database. Generic feedback is either
	* feedback for either the complete solution of the question or at least one
	* incorrect answer.
	*
	* @param integer $correctness 0 for at least one incorrect answer, 1 for the correct solution
	* @param string $feedback Feedback text
	* @access public
	*/
	function saveFeedbackGeneric($correctness, $feedback)
	{
		global $ilDB;
		
		switch ($correctness)
		{
			case 0:
				$correctness = 0;
				break;
			case 1:
			default:
				$correctness = 1;
				break;
		}
		$query = sprintf("DELETE FROM qpl_feedback_generic WHERE question_fi = %s AND correctness = %s",
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($correctness . "")
		);
		$result = $ilDB->query($query);
		if (strlen($feedback))
		{
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$query = sprintf("INSERT INTO qpl_feedback_generic VALUES (NULL, %s, %s, %s, NULL)",
				$ilDB->quote($this->getId() . ""),
				$ilDB->quote($correctness . ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($feedback, 0))
			);
			$result = $ilDB->query($query);
		}
	}
	
	/**
	* Returns the generic feedback for a given question state
	*
	* Returns the generic feedback for a given question state. The
	* state is either the complete solution of the question or at least one
	* incorrect answer
	*
	* @param integer $correctness 0 for at least one incorrect answer, 1 for the correct solution
	* @return string Feedback text
	* @access public
	*/
	function getFeedbackGeneric($correctness)
	{
		global $ilDB;
		
		$feedback = "";
		$query = sprintf("SELECT * FROM qpl_feedback_generic WHERE question_fi = %s AND correctness = %s",
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($correctness . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$feedback = ilRTE::_replaceMediaObjectImageSrc($row["feedback"], 1);
		}
		return $feedback;
	}

	/**
	* Duplicates the generic feedback of a question
	*
	* Duplicates the generic feedback of a question
	*
	* @param integer $original_id The database ID of the original question
	* @access public
	*/
	function duplicateFeedbackGeneric($original_id)
	{
		global $ilDB;
		
		$feedback = "";
		$query = sprintf("SELECT * FROM qpl_feedback_generic WHERE question_fi = %s",
			$ilDB->quote($original_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				$duplicatequery = sprintf("INSERT INTO qpl_feedback_generic VALUES (NULL, %s, %s, %s, NULL)",
					$ilDB->quote($this->getId() . ""),
					$ilDB->quote($row["correctness"] . ""),
					$ilDB->quote($row["feedback"] . "")
				);
				$duplicateresult = $ilDB->query($duplicatequery);
			}
		}
	}
	
	function syncFeedbackGeneric()
	{
		global $ilDB;

		$feedback = "";

		// delete generic feedback of the original
		$deletequery = sprintf("DELETE FROM qpl_feedback_generic WHERE question_fi = %s",
			$ilDB->quote($this->original_id . "")
		);
		$result = $ilDB->query($deletequery);
			
		// get generic feedback of the actual question
		$query = sprintf("SELECT * FROM qpl_feedback_generic WHERE question_fi = %s",
			$ilDB->quote($this->getId() . "")
		);
		$result = $ilDB->query($query);

		// save generic feedback to the original
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				$duplicatequery = sprintf("INSERT INTO qpl_feedback_generic VALUES (NULL, %s, %s, %s, NULL)",
					$ilDB->quote($this->original_id . ""),
					$ilDB->quote($row["correctness"] . ""),
					$ilDB->quote($row["feedback"] . "")
				);
				$duplicateresult = $ilDB->query($duplicatequery);
			}
		}
	}
	
	/**
	* Collects all text in the question which could contain media objects
	* which were created with the Rich Text Editor
	*/
	function getRTETextWithMediaObjects()
	{
		// must be called in parent classes. add additional RTE text in the parent
		// classes and call this method to add the standard RTE text
		$collected = $this->getQuestion();
		$collected .= $this->getFeedbackGeneric(0);
		$collected .= $this->getFeedbackGeneric(1);
		return $collected;
	}

	/**
	* synchronises appearances of media objects in the question with media
	* object usage table
	*/
	function cleanupMediaObjectUsage()
	{
		$combinedtext = $this->getRTETextWithMediaObjects();
		include_once("./Services/RTE/classes/class.ilRTE.php");
		ilRTE::_cleanupMediaObjectUsage($combinedtext, "qpl:html", $this->getId());
	}
	
	/**
	* Gets all instances of the question
	*
	* @result array All instances of question and its copies
	*/
	function &getInstances()
	{
		global $ilDB;
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE original_id = %s",
			$ilDB->quote($this->getId())
		);
		$result = $ilDB->query($query);
		$instances = array();
		$ids = array();
		while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			array_push($ids, $row["question_id"]);
		}
		foreach ($ids as $question_id)
		{
			$query = sprintf("SELECT DISTINCT object_data.obj_id, object_data.title FROM tst_test_question, object_data, tst_tests WHERE question_fi = %s AND tst_tests.test_id = tst_test_question.test_fi AND object_data.obj_id = tst_tests.obj_fi",
				$ilDB->quote($question_id . "")
			);
			$result = $ilDB->query($query);
			while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				$instances[$row["obj_id"]] = $row["title"];
			}
			$query = sprintf("SELECT DISTINCT object_data.obj_id, object_data.title FROM tst_test_random_question, tst_active, object_data, tst_tests WHERE tst_test_random_question.active_fi = tst_active.active_id AND tst_test_random_question.question_fi = %s AND tst_tests.test_id = tst_active.test_fi AND object_data.obj_id = tst_tests.obj_fi",
				$ilDB->quote($question_id . "")
			);
			$result = $ilDB->query($query);
			while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				$instances[$row["obj_id"]] = $row["title"];
			}
		}
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		foreach ($instances as $key => $value)
		{
			$query = sprintf("SELECT object_reference.ref_id FROM object_reference WHERE obj_id = %s",
				$ilDB->quote($key . "")
			);
			$result = $ilDB->query($query);
			$refs = array();
			while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				array_push($refs, $row["ref_id"]);
			}
			$instances[$key] = array("obj_id" => $key, "title" => $value, "author" => ilObjTest::_lookupAuthor($key), "refs" => $refs);
		}
		return $instances;
	}

	function _needsManualScoring($question_id)
	{
		include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
		$scoring = ilObjAssessmentFolder::_getManualScoringTypes();
		$questiontype = assQuestion::_getQuestionType($question_id);
		if (in_array($questiontype, $scoring))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* Returns the user id and the test id for a given active id
	*
	* @param integer $active_id Active id for a test/user
	* @return array Result array containing the user_id and test_id
	* @access public
	*/
	function getActiveUserData($active_id)
	{
		global $ilDB;
		$query = sprintf("SELECT * FROM tst_active WHERE active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			return array("user_id" => $row["user_fi"], "test_id" => $row["test_fi"]);
		}
		else
		{
			return array();
		}
	}

	/**
	* Include the php class file for a given question type
	*
	* @param string $question_type The type tag of the question type
	* @return integer 0 if the class should be included, 1 if the GUI class should be included
	* @access public
	*/
	static function _includeClass($question_type, $gui = 0)
	{
		$type = $question_type;
		if ($gui) $type .= "GUI";
		if (file_exists("./Modules/TestQuestionPool/classes/class.".$type.".php"))
		{
			include_once "./Modules/TestQuestionPool/classes/class.".$type.".php";
		}
		else
		{
			global $ilPluginAdmin;
			$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, "TestQuestionPool", "qst");
			foreach ($pl_names as $pl_name)
			{
				$pl = ilPlugin::getPluginObject(IL_COMP_MODULE, "TestQuestionPool", "qst", $pl_name);
				if (strcmp($pl->getQuestionType(), $question_type) == 0)
				{
					$pl->includeClass("class.".$type.".php");
				}
			}
		}
	}
}

?>
