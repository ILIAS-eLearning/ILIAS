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
	* Maximum thumbnail geometry
	*
	* @var integer
	*/
	var $thumb_geometry = 100;

	/**
	* Minimum element height
	*
	* @var integer
	*/
	var $element_height;

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
	function __construct(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$ordering_type = OQ_TERMS
	)
	{
		parent::__construct($title, $comment, $author, $owner, $question);
		$this->answers = array();
		$this->ordering_type = $ordering_type;
	}

	/**
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

		// cleanup RTE images which are not inserted into the question text
		include_once("./Services/RTE/classes/class.ilRTE.php");
		if ($this->id == -1)
		{
			// Neuen Datensatz schreiben
			$next_id = $ilDB->nextId('qpl_questions');
			$affectedRows = $ilDB->manipulateF("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, description, author, owner, question_text, points, working_time, complete, created, original_id, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", 
				array("integer","integer", "integer", "text", "text", "text", "integer", "text", "float", "time", "text", "integer","integer","integer"),
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
					$complete,
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
			// Vorhandenen Datensatz aktualisieren
			$affectedRows = $ilDB->manipulateF("UPDATE qpl_questions SET obj_fi = %s, title = %s, description = %s, author = %s, question_text = %s, points = %s, working_time=%s, complete = %s, tstamp = %s WHERE question_id = %s", 
				array("integer", "text", "text", "text", "text", "float", "time", "text", "integer", "integer"),
				array(
					$this->getObjId(), 
					$this->getTitle(), 
					$this->getComment(), 
					$this->getAuthor(), 
					ilRTE::_replaceMediaObjectImageSrc($this->getQuestion(), 0), 
					$this->getMaximumPoints(),
					$estw_time,
					$complete,
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

		$affectedRows = $ilDB->manipulateF("INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, ordering_type, thumb_geometry, element_height) VALUES (%s, %s, %s, %s)", 
			array("integer", "text","integer","integer"),
			array(
				$this->getId(),
				$this->ordering_type,
				$this->getThumbGeometry(),
				($this->getElementHeight() > 20) ? $this->getElementHeight() : NULL
			)
		);

		$affectedRows = $ilDB->manipulateF("DELETE FROM qpl_a_ordering WHERE question_fi = %s",
			array('integer'),
			array($this->getId())
		);

		// Anworten wegschreiben
		foreach ($this->answers as $key => $value)
		{
			$answer_obj = $this->answers[$key];
			$next_id = $ilDB->nextId('qpl_a_ordering');
			$affectedRows = $ilDB->manipulateF("INSERT INTO qpl_a_ordering (answer_id, question_fi, answertext, solution_order, ".
				"random_id, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
				array('integer','integer','text','integer','integer','integer'),
				array(
					$next_id,
					$this->getId(),
					ilRTE::_replaceMediaObjectImageSrc($answer_obj->getAnswertext(), 0),
					$key,
					$answer_obj->getRandomID(),
					time()
				)
			);
		}

		if ($this->getOrderingType() == OQ_PICTURES)
		{
			$this->rebuildThumbnails();
		}

		$this->cleanImagefiles();
		parent::saveToDb($original_id);
	}

	/**
	* Loads a assOrderingQuestion object from a database
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
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
			$this->ordering_type = $data["ordering_type"];
			$this->thumb_geometry = $data["thumb_geometry"];
			$this->element_height = $data["element_height"];
			$this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
		}

		$result = $ilDB->queryF("SELECT * FROM qpl_a_ordering WHERE question_fi = %s ORDER BY solution_order ASC",
			array('integer'),
			array($question_id)
		);

		include_once "./Modules/TestQuestionPool/classes/class.assAnswerOrdering.php";
		if ($result->numRows() > 0)
		{
			while ($data = $ilDB->fetchAssoc($result))
			{
				include_once("./Services/RTE/classes/class.ilRTE.php");
				$data["answertext"] = ilRTE::_replaceMediaObjectImageSrc($data["answertext"], 1);
				array_push($this->answers, new ASS_AnswerOrdering($data["answertext"], $data["random_id"]));
			}
		}
		parent::loadFromDb($question_id);
	}
	
	/**
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
		$clone->onDuplicate($this_id);
		return $clone->id;
	}

	/**
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
		$clone->onCopy($this->getObjId(), $this->getId());
		return $clone->id;
	}
	
	function duplicateImages($question_id)
	{
		global $ilLog;
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
				if (!@copy($imagepath_original . $filename, $imagepath . $filename)) {
					$ilLog->write("image could not be duplicated!!!!");
				}
				if (!@copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) {
					$ilLog->write("image thumbnail could not be duplicated!!!!");
				}
			}
		}
	}

	function copyImages($question_id, $source_questionpool)
	{
		global $ilLog;
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
					$ilLog->write("Ordering Question image could not be copied: $imagepath_original$filename");
				}
				if (!copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) {
					$ilLog->write("Ordering Question image thumbnail could not be copied: $imagepath_original" . $this->getThumbPrefix() . $filename);
				}
			}
		}
	}

	/**
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
	* @return integer The ordering question type
	* @access public
	* @see $ordering_type
	*/
	function getOrderingType()
	{
		return $this->ordering_type;
	}

	/**
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
		$solution_order = -1
	)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerOrdering.php";
		$answer = new ASS_AnswerOrdering($answertext, $this->getRandomID());
		if (($solution_order >= 0) && ($solution_order < count($this->answers)))
		{
			$part1 = array_slice($this->answers, 0, $solution_order);
			$part2 = array_slice($this->answers, $solution_order);
			$this->answers = array_merge($part1, array($answer), $part2);
		}
		else
		{
			array_push($this->answers, $answer);
		}
	}
	
	public function moveAnswerUp($position)
	{
		if ($position > 0)
		{
			$temp = $this->answers[$position-1];
			$this->answers[$position-1] = $this->answers[$position];
			$this->answers[$position] = $temp;
		}
	}
	
	public function moveAnswerDown($position)
	{
		if ($position < count($this->answers)-1)
		{
			$temp = $this->answers[$position+1];
			$this->answers[$position+1] = $this->answers[$position];
			$this->answers[$position] = $temp;
		}
	}

	protected function getRandomID()
	{
		$random_number = mt_rand(1, 100000);
		$found = true;
		while ($found)
		{
			$found = false;
			foreach ($this->getAnswers() as $answer)
			{
				if ($answer->getRandomID() == $random_number)
				{
					$found = true;
					$random_number++;
				}
			}
		}
		return $random_number;
	}

	/**
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
	* @return integer The number of answers of the ordering question
	* @access public
	* @see $answers
	*/
	function getAnswerCount()
	{
		return count($this->answers);
	}

	/**
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
		$result = $ilDB->queryF("SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			array('integer','integer','integer'),
			array($active_id, $this->getId(), $pass)
		);
		$user_order = array();
		while ($data = $ilDB->fetchAssoc($result))
		{
			if ((strcmp($data["value1"], "") != 0) && (strcmp($data["value2"], "") != 0))
			{
				$user_order[$data["value2"]] = $data["value1"];
			}
		}
		ksort($user_order);
		$user_order = array_values($user_order);
		$points = 0;
		$correctcount = 0;
		foreach ($this->answers as $index => $answer)
		{
			if ($index == $user_order[$index])
			{
				$correctcount++;
			}
		}
		if ($correctcount == count($this->answers))
		{
			$points = $this->getPoints();
		}

		$points = parent::calculateReachedPoints($active_id, $pass = NULL, $points);
		return $points;
	}

	/**
	* Returns the maximum points, a learner can reach answering the question
	*
	* @return double Points
	* @see $points
	*/
	public function getMaximumPoints()
	{
		return $this->getPoints();
	}

	/*
	* Returns the encrypted save filename of a matching picture
	* Images are saved with an encrypted filename to prevent users from
	* cheating by guessing the solution from the image filename
	* 
	* @param string $filename Original filename
	* @return string Encrypted filename
	*/
	public function getEncryptedFilename($filename)
	{
		$extension = "";
		if (preg_match("/.*\\.(\\w+)$/", $filename, $matches))
		{
			$extension = $matches[1];
		}
		return md5($filename) . "." . $extension;
	}
	
	protected function cleanImagefiles()
	{
		if ($this->getOrderingType() == OQ_PICTURES)
		{
			if (@file_exists($this->getImagePath()))
			{
				$contents = ilUtil::getDir($this->getImagePath());
				foreach ($contents as $f)
				{
					if (strcmp($f['type'], 'file') == 0)
					{
						$found = false;
						foreach ($this->getAnswers() as $answer)
						{
							if (strcmp($f['entry'], $answer->getAnswertext()) == 0) $found = true;
							if (strcmp($f['entry'], $this->getThumbPrefix() . $answer->getAnswertext()) == 0) $found = true;
						}
						if (!$found)
						{
							if (@file_exists($this->getImagePath() . $f['entry'])) @unlink($this->getImagePath() . $f['entry']);
						}
					}
				}
			}
		}
		else
		{
			if (@file_exists($this->getImagePath()))
			{
				ilUtil::delDir($this->getImagePath());
			}
		}
	}

	/*
	* Deletes an imagefile from the system if the file is deleted manually
	* 
	* @param string $filename Image file filename
	* @return boolean Success
	*/
	public function deleteImagefile($filename)
	{
		$deletename = $$filename;
		$result = @unlink($this->getImagePath().$deletename);
		$result = $result & @unlink($this->getImagePath().$this->getThumbPrefix() . $deletename);
		return $result;
	}

	/**
	* Sets the image file and uploads the image to the object's image directory.
	*
	* @param string $image_filename Name of the original image file
	* @param string $image_tempfilename Name of the temporary uploaded image file
	* @return integer An errorcode if the image upload fails, 0 otherwise
	* @access public
	*/
	function setImageFile($image_tempfilename, $image_filename, $previous_filename)
	{
		$result = TRUE;
		if (strlen($image_tempfilename))
		{
			$image_filename = str_replace(" ", "_", $image_filename);
			$imagepath = $this->getImagePath();
			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			$savename = $image_filename;
			if (!ilUtil::moveUploadedFile($image_tempfilename, $savename, $imagepath.$savename))
			{
				$result = FALSE;
			}
			else
			{
				// create thumbnail file
				$thumbpath = $imagepath . $this->getThumbPrefix() . $savename;
				ilUtil::convertImage($imagepath.$savename, $thumbpath, "JPEG", $this->getThumbGeometry());
			}
			if ($result && (strcmp($image_filename, $previous_filename) != 0) && (strlen($previous_filename)))
			{
				$this->deleteImagefile($previous_filename);
			}
		}
		return $result;
	}

	/**
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

			$affectedRows = $ilDB->manipulateF("DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
				array('integer','integer','integer'),
				array($active_id, $this->getId(), $pass)
			);
			if (array_key_exists("orderresult", $_POST))
			{
				$orderresult = $_POST["orderresult"];
				if (strlen($orderresult))
				{
					$orderarray = explode(":", $orderresult);
					$ordervalue = 1;
					foreach ($orderarray as $index)
					{
						if (preg_match("/id_(\\d+)/", $index, $idmatch))
						{
							$randomid = $idmatch[1];
							foreach ($this->getAnswers() as $answeridx => $answer)
							{
								if ($answer->getRandomID() == $randomid)
								{
									$next_id = $ilDB->nextId('tst_solutions');
									$query = $ilDB->manipulateF("INSERT INTO tst_solutions ".
										"(solution_id, active_fi, question_fi, value1, value2, pass, tstamp) VALUES ".
										"(%s, %s, %s, %s, %s, %s, %s)",
										array('integer','integer','integer','text','text','integer','integer'),
										array(
											$next_id,
											$active_id,
											$this->getId(),
											$answeridx,
											trim($ordervalue),
											$pass,
											time()
										)
									);
									$ordervalue++;
									$entered_values++;
								}
							}
						}
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
								foreach ($this->getAnswers() as $answeridx => $answer)
								{
									if ($answer->getRandomID() == $matches[1])
									{
										$next_id = $ilDB->nextId('tst_solutions');
										$query = $ilDB->manipulateF("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s)",
											array('integer','integer','integer','text','text','integer','integer'),
											array(
												$next_id,
												$active_id,
												$this->getId(),
												$answeridx,
												$value,
												$pass,
												time()
											)
										);
										$entered_values++;
									}
								}
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

	/**
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
	* @return string The additional table name
	* @access public
	*/
	function getAdditionalTableName()
	{
		return "qpl_qst_ordering";
	}

	/**
	* Returns the name of the answer table in the database
	*
	* @return string The answer table name
	* @access public
	*/
	function getAnswerTableName()
	{
		return "qpl_a_ordering";
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
	* @return boolean TRUE if the question type supports JavaScript output, FALSE otherwise
	* @access public
	*/
	function supportsJavascriptOutput()
	{
		return TRUE;
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
		include_once ("./classes/class.ilExcelUtils.php");
		$solutions = $this->getSolutionValues($active_id, $pass);
		$sol = array();
		foreach ($solutions as $solution)
		{
			$sol[$solution["value1"]] = $solution["value2"];
		}
		asort($sol);
		$sol = array_keys($sol);
		$worksheet->writeString($startrow, 0, ilExcelUtils::_convert_text($this->lng->txt($this->getQuestionType())), $format_title);
		$worksheet->writeString($startrow, 1, ilExcelUtils::_convert_text($this->getTitle()), $format_title);
		$i = 1;
		$answers = $this->getAnswers();
		foreach ($sol as $idx)
		{
			foreach ($solutions as $solution)
			{
				if ($solution["value1"] == $idx) $worksheet->writeString($startrow + $i, 0, ilExcelUtils::_convert_text($solution["value2"]));
			}
			$worksheet->writeString($startrow + $i, 1, ilExcelUtils::_convert_text($answers[$idx]->getAnswertext()));
			$i++;
		}
		return $startrow + $i + 1;
	}

	/*
	* Get the thumbnail geometry
	*
	* @return integer Geometry
	*/
	public function getThumbGeometry()
	{
		return $this->thumb_geometry;
	}
	
	/*
	* Set the thumbnail geometry
	*
	* @param integer $a_geometry Geometry
	*/
	public function setThumbGeometry($a_geometry)
	{
		$this->thumb_geometry = ($a_geometry < 1) ? 100 : $a_geometry;
	}

	/*
	* Get the minimum element height
	*
	* @return integer Height
	*/
	public function getElementHeight()
	{
		return $this->element_height;
	}
	
	/*
	* Set the minimum element height
	*
	* @param integer $a_height Height
	*/
	public function setElementHeight($a_height)
	{
		$this->element_height = ($a_height < 20) ? "" : $a_height;
	}

	/*
	* Rebuild the thumbnail images with a new thumbnail size
	*/
	public function rebuildThumbnails()
	{
		if ($this->getOrderingType() == OQ_PICTURES)
		{
			foreach ($this->getAnswers() as $answer)
			{
				$this->generateThumbForFile($this->getImagePath(), $answer->getAnswertext());
			}
		}
	}
	
	public function getThumbPrefix()
	{
		return "thumb.";
	}
	
	protected function generateThumbForFile($path, $file)
	{
		$filename = $path . $file;
		if (@file_exists($filename))
		{
			$thumbpath = $path . $this->getThumbPrefix() . $file;
			$path_info = @pathinfo($filename);
			$ext = "";
			switch (strtoupper($path_info['extension']))
			{
				case 'PNG':
					$ext = 'PNG';
					break;
				case 'GIF':
					$ext = 'GIF';
					break;
				default:
					$ext = 'JPEG';
					break;
			}
			ilUtil::convertImage($filename, $thumbpath, $ext, $this->getThumbGeometry());
		}
	}
}

?>
