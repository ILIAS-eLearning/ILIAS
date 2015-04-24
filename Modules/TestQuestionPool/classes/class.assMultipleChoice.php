<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjAnswerScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php';
require_once './Modules/TestQuestionPool/classes/class.ilUserQuestionResult.php';

/**
 * Class for multiple choice tests.
 *
 * assMultipleChoice is a class for multiple choice questions.
 *
 * @extends assQuestion
 * 
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com> 
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <bheyser@databay.de>
 *
 * @version		$Id$
 * 
 * @ingroup		ModulesTestQuestionPool
 */
class assMultipleChoice extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition
{
	/**
	 * The given answers of the multiple choice question
	 *
	 * $answers is an array of the given answers of the multiple choice question
	 *
	 * @var array
	 */
	var $answers;

	/**
	 * Output type
	 *
	 * This is the output type for the answers of the multiple choice question. You can select
	 * OUTPUT_ORDER(=0) or OUTPUT_RANDOM (=1). The default output type is OUTPUT_ORDER
	 *
	 * @var integer
	 */
	var $output_type;

	public $isSingleline;
	public $lastChange;
	public $feedback_setting;

	/** @var integer Thumbnail size */
	protected $thumb_size;

	/**
	 * @param mixed $isSingleline
	 */
	public function setIsSingleline($isSingleline)
	{
		$this->isSingleline = $isSingleline;
	}

	/**
	 * @return mixed
	 */
	public function getIsSingleline()
	{
		return $this->isSingleline;
	}

	/**
	 * @param mixed $lastChange
	 */
	public function setLastChange($lastChange)
	{
		$this->lastChange = $lastChange;
	}

	/**
	 * @return mixed
	 */
	public function getLastChange()
	{
		return $this->lastChange;
	}

	/**
	 * assMultipleChoice constructor
	 *
	 * The constructor takes possible arguments an creates an instance of the assMultipleChoice object.
	 *
	 * @param string     $title       A title string to describe the question
	 * @param string     $comment     A comment string to describe the question
	 * @param string     $author      A string containing the name of the questions author
	 * @param integer    $owner       A numerical ID to identify the owner/creator
	 * @param string     $question    The question string of the multiple choice question
	 * @param int|string $output_type The output order of the multiple choice answers
	 *
	 * @see assQuestion:assQuestion()
	 */
	public function __construct(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$output_type = OUTPUT_ORDER
	)
	{
		parent::__construct($title, $comment, $author, $owner, $question);
		$this->output_type = $output_type;
		$this->thumb_size = 150;
		$this->answers = array();
		$this->shuffle = 1;
	}

	/**
	* Returns true, if a multiple choice question is complete for use
	*
	* @return boolean True, if the multiple choice question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (strlen($this->title) and ($this->author) and ($this->question) and (count($this->answers)) and ($this->getMaximumPoints() > 0))
		{
			return true;
		}
			else
		{
			return false;
		}
	}

	/**
	 * Saves a assMultipleChoice object to a database
	 *
	 * @param string $original_id
	 */
	public function saveToDb($original_id = "")
	{
		$this->saveQuestionDataToDb($original_id);
		$this->saveAdditionalQuestionDataToDb();
		$this->saveAnswerSpecificDataToDb();

		$this->ensureNoInvalidObligation($this->getId());
		parent::saveToDb($original_id);
	}

	/**
	 * Rebuild the thumbnail images with a new thumbnail size
	 */
	protected function rebuildThumbnails()
	{
		if ($this->isSingleline && ($this->getThumbSize()))
		{
			foreach ($this->getAnswers() as $answer)
			{
				if (strlen($answer->getImage()))
				{
					$this->generateThumbForFile($this->getImagePath(), $answer->getImage());
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function getThumbPrefix()
	{
		return "thumb.";
	}

	/**
	 * @param $path string
	 * @param $file string
	 */
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
			ilUtil::convertImage($filename, $thumbpath, $ext, $this->getThumbSize());
		}
	}

	/**
	* Loads a assMultipleChoice object from a database
	*
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	*/
	public function loadFromDb($question_id)
	{
		global $ilDB;
		$hasimages = 0;

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
			$this->setNrOfTries($data['nr_of_tries']);
			$this->setComment($data["description"]);
			$this->setOriginalId($data["original_id"]);
			$this->setAuthor($data["author"]);
			$this->setPoints($data["points"]);
			$this->setOwner($data["owner"]);
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
			$shuffle = (is_null($data['shuffle'])) ? true : $data['shuffle'];
			$this->setShuffle($shuffle);
			$this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
			$this->setThumbSize($data['thumb_size']);
			$this->isSingleline = ($data['allow_images']) ? false : true;
			$this->lastChange = $data['tstamp'];
			$this->feedback_setting = $data['feedback_setting'];
			
			try
			{
				$this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
			}
			catch(ilTestQuestionPoolException $e)
			{
			}
		}

		$result = $ilDB->queryF("SELECT * FROM qpl_a_mc WHERE question_fi = %s ORDER BY aorder ASC",
			array('integer'),
			array($question_id)
		);
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMultipleResponseImage.php";
		if ($result->numRows() > 0)
		{
			while ($data = $ilDB->fetchAssoc($result))
			{
				$imagefilename = $this->getImagePath() . $data["imagefile"];
				if (!@file_exists($imagefilename))
				{
					$data["imagefile"] = "";
				}
				include_once("./Services/RTE/classes/class.ilRTE.php");
				$data["answertext"] = ilRTE::_replaceMediaObjectImageSrc($data["answertext"], 1);
				array_push($this->answers, new ASS_AnswerMultipleResponseImage($data["answertext"], $data["points"], $data["aorder"], $data["points_unchecked"], $data["imagefile"]));
			}
		}

		parent::loadFromDb($question_id);
	}

	/**
	 * Duplicates an assMultipleChoiceQuestion
	 */
	public function duplicate($for_test = true, $title = "", $author = "", $owner = "", $testObjId = null)
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		// duplicate the question in database
		$this_id = $this->getId();
		
		if( (int)$testObjId > 0 )
		{
			$thisObjId = $this->getObjId();
		}
		
		$clone = $this;
		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		$original_id = assQuestion::_getOriginalId($this->id);
		$clone->id = -1;
		
		if( (int)$testObjId > 0 )
		{
			$clone->setObjId($testObjId);
		}
		
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
		// duplicate the images
		$clone->duplicateImages($this_id, $thisObjId);

		$clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());

		return $clone->id;
	}

	/**
	 * Copies an assMultipleChoice object
	 */
	public function copyObject($target_questionpool_id, $title = "")
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
		$source_questionpool_id = $this->getObjId();
		$clone->setObjId($target_questionpool_id);
		if ($title)
		{
			$clone->setTitle($title);
		}
		$clone->saveToDb();
		// copy question page content
		$clone->copyPageOfQuestion($original_id);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($original_id);
		// duplicate the image
		$clone->copyImages($original_id, $source_questionpool_id);

		$clone->onCopy($source_questionpool_id, $original_id, $clone->getObjId(), $clone->getId());
		
		return $clone->id;
	}

	public function createNewOriginalFromThisDuplicate($targetParentId, $targetQuestionTitle = "")
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}

		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");

		$sourceQuestionId = $this->id;
		$sourceParentId = $this->getObjId();

		// duplicate the question in database
		$clone = $this;
		$clone->id = -1;

		$clone->setObjId($targetParentId);

		if ($targetQuestionTitle)
		{
			$clone->setTitle($targetQuestionTitle);
		}

		$clone->saveToDb();
		// copy question page content
		$clone->copyPageOfQuestion($sourceQuestionId);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($sourceQuestionId);
		// duplicate the image
		$clone->copyImages($sourceQuestionId, $sourceParentId);

		$clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

		return $clone->id;
	}

	/**
	* Gets the multiple choice output type which is either OUTPUT_ORDER (=0) or OUTPUT_RANDOM (=1).
	*
	* @return integer The output type of the assMultipleChoice object
	* @see $output_type
	*/
	public function getOutputType()
	{
		return $this->output_type;
	}

	/**
	 * Sets the output type of the assMultipleChoice object
	 *
	 * @param int|string $output_type A nonnegative integer value specifying the output type. It is OUTPUT_ORDER (=0) or OUTPUT_RANDOM (=1).
	 *                                
	 * @see    $response
	 */
	public function setOutputType($output_type = OUTPUT_ORDER)
	{
		$this->output_type = $output_type;
	}

	/**
	 * Adds a possible answer for a multiple choice question. A ASS_AnswerBinaryStateImage object will be
	 * created and assigned to the array $this->answers.
	 *
	 * @param string  $answertext 		The answer text
	 * @param double  $points     		The points for selecting the answer (even negative points can be used)
	 * @param float   $points_unchecked The points for not selecting the answer (even positive points can be used)
	 * @param integer $order      		A possible display order of the answer
	 * @param string  $answerimage
	 * 
	 * @see      $answers
	 * @see      ASS_AnswerBinaryStateImage
	 */
	public function addAnswer(
		$answertext = "",
		$points = 0.0,
		$points_unchecked = 0.0,
		$order = 0,
		$answerimage = ""
	)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMultipleResponseImage.php";
		if (array_key_exists($order, $this->answers))
		{
			// insert answer
			$answer = new ASS_AnswerMultipleResponseImage($answertext, $points, $order, $points_unchecked, $answerimage);
			$newchoices = array();
			for ($i = 0; $i < $order; $i++)
			{
				array_push($newchoices, $this->answers[$i]);
			}
			array_push($newchoices, $answer);
			for ($i = $order; $i < count($this->answers); $i++)
			{
				$changed = $this->answers[$i];
				$changed->setOrder($i+1);
				array_push($newchoices, $changed);
			}
			$this->answers = $newchoices;
		}
		else
		{
			// add answer
			$answer = new ASS_AnswerMultipleResponseImage($answertext, $points, count($this->answers), $points_unchecked, $answerimage);
			array_push($this->answers, $answer);
		}
	}

	/**
	 * Returns the number of answers
	 *
	 * @return integer The number of answers of the multiple choice question
	 * @see $answers
	 */
	public function getAnswerCount()
	{
		return count($this->answers);
	}

	/**
	 * Returns an answer with a given index. The index of the first
	 * answer is 0, the index of the second answer is 1 and so on.
	 *
	 * @param integer $index A nonnegative index of the n-th answer
	 * @return object ASS_AnswerBinaryStateImage-Object containing the answer
	 * @see $answers
	*/
	public function getAnswer($index = 0)
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
	 * @see $answers
	 */
	public function deleteAnswer($index = 0)
	{
		if ($index < 0) return;
		if (count($this->answers) < 1) return;
		if ($index >= count($this->answers)) return;
		$answer = $this->answers[$index];
		if (strlen($answer->getImage())) $this->deleteImage($answer->getImage());
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
	 * @see $answers
	 */
	public function flushAnswers()
	{
		$this->answers = array();
	}

	/**
	 * Returns the maximum points, a learner can reach answering the question
	 * 
	 * @see $points
	 */
	public function getMaximumPoints()
	{
		$points = 0;
		$allpoints = 0;
		foreach ($this->answers as $key => $value) 
		{
			if ($value->getPoints() > $value->getPointsUnchecked())
			{
				$allpoints += $value->getPoints();
			}
			else
			{
				$allpoints += $value->getPointsUnchecked();
			}
		}
		return $allpoints;
	}

	/**
	 * Returns the points, a learner has reached answering the question.
	 * The points are calculated from the given answers.
	 *
	 * @param integer $active_id
	 * @param integer $pass
	 * @param boolean $returndetails (deprecated !!)
	 *
	 * @throws ilTestException
	 * @return integer|array $points/$details (array $details is deprecated !!)
	 */
	public function calculateReachedPoints($active_id, $pass = NULL, $returndetails = FALSE)
	{
		if( $returndetails )
		{
			throw new ilTestException('return details not implemented for '.__METHOD__);
		}
		
		global $ilDB;
		
		$found_values = array();
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}
		$result = $this->getCurrentSolutionResultSet($active_id, $pass);
		while ($data = $ilDB->fetchAssoc($result))
		{
			if (strcmp($data["value1"], "") != 0)
			{
				array_push($found_values, $data["value1"]);
			}
		}
		
		$points = $this->calculateReachedPointsForSolution($found_values, $active_id);
		
		return $points;
	}
	
	/**
	 * Saves the learners input of the question to the database.
	 * 
	 * @param integer $active_id Active id of the user
	 * @param integer $pass Test pass
	 *                      
	 * @return boolean $status
	 */
	public function saveWorkingData($active_id, $pass = NULL)
	{
		/** @var $ilDB ilDB */
		global $ilDB;

		if (is_null($pass))
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$pass = ilObjTest::_getPass($active_id);
		}

		$entered_values = 0;
		
		$this->getProcessLocker()->requestUserSolutionUpdateLock();

		$this->removeCurrentSolution($active_id, $pass);

		$solutionSubmit = $this->getSolutionSubmit();
		
		foreach($solutionSubmit as $value)
		{
			if (strlen($value))
			{
				$this->saveCurrentSolution($active_id, $pass, $value, null);
				$entered_values++;
			}
		}

		$this->getProcessLocker()->releaseUserSolutionUpdateLock();
		
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

		return true;
	}
	
	public function saveAdditionalQuestionDataToDb()
	{
		/** @var $ilDB ilDB */
		global $ilDB;
		$oldthumbsize = 0;
		if ($this->isSingleline && ($this->getThumbSize()))
		{
			// get old thumbnail size
			$result = $ilDB->queryF( "SELECT thumb_size FROM " . $this->getAdditionalTableName(
							 ) . " WHERE question_fi = %s",
									 array( "integer" ),
									 array( $this->getId() )
			);
			if ($result->numRows() == 1)
			{
				$data         = $ilDB->fetchAssoc( $result );
				$oldthumbsize = $data['thumb_size'];
			}
		}

		if (!$this->isSingleline)
		{
			ilUtil::delDir( $this->getImagePath() );
		}

		// save additional data
		$ilDB->manipulateF( "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
							array( "integer" ),
							array( $this->getId() )
		);

		$ilDB->manipulateF( "INSERT INTO " . $this->getAdditionalTableName() 
							. " (question_fi, shuffle, allow_images, thumb_size) VALUES (%s, %s, %s, %s)",
							array( "integer", "text", "text", "integer" ),
							array(
								$this->getId(),
								$this->getShuffle(),
								($this->isSingleline) ? "0" : "1",
								(strlen( $this->getThumbSize() ) == 0) ? null : $this->getThumbSize()
							)
		);
	}

	public function saveAnswerSpecificDataToDb()
	{
		/** @var $ilDB ilDB */
		global $ilDB;
		$ilDB->manipulateF( "DELETE FROM qpl_a_mc WHERE question_fi = %s",
							array( 'integer' ),
							array( $this->getId() )
		);

		foreach ($this->answers as $key => $value)
		{
			$answer_obj = $this->answers[$key];
			$next_id    = $ilDB->nextId( 'qpl_a_mc' );
			$ilDB->manipulateF( "INSERT INTO qpl_a_mc (answer_id, question_fi, answertext, points, points_unchecked, aorder, imagefile, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
								array( 'integer', 'integer', 'text', 'float', 'float', 'integer', 'text', 'integer' ),
								array(
									$next_id,
									$this->getId(),
									ilRTE::_replaceMediaObjectImageSrc( $answer_obj->getAnswertext(), 0 ),
									$answer_obj->getPoints(),
									$answer_obj->getPointsUnchecked(),
									$answer_obj->getOrder(),
									$answer_obj->getImage(),
									time()
								)
			);
		}
		$this->rebuildThumbnails();
	}

	/**
	 * Reworks the allready saved working data if neccessary
	 *
	 * @param integer $active_id
	 * @param integer $pass
	 * @param boolean $obligationsAnswered
	 */
	protected function reworkWorkingData($active_id, $pass, $obligationsAnswered)
	{
		// nothing to rework!
	}

	function syncWithOriginal()
	{
		if ($this->getOriginalId())
		{
			$this->syncImages();
			parent::syncWithOriginal();
		}
	}

	/**
	 * Returns the question type of the question
	 *
	 * @return integer The question type of the question
	 */
	public function getQuestionType()
	{
		return "assMultipleChoice";
	}
	
	/**
	 * Returns the name of the additional question data table in the database
	 *
	 * @return string The additional table name
	 */
	public function getAdditionalTableName()
	{
		return "qpl_qst_mc";
	}
	
	/**
	 * Returns the name of the answer table in the database
	 *
	 * @return string The answer table name
	 */
	public function getAnswerTableName()
	{
		return "qpl_a_mc";
	}
	
	/**
	 * Sets the image file and uploads the image to the object's image directory.
	 *
	 * @param string $image_filename Name of the original image file
	 * @param string $image_tempfilename Name of the temporary uploaded image file
	 * @return integer An errorcode if the image upload fails, 0 otherwise
	 */
	public function setImageFile($image_filename, $image_tempfilename = "")
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
					if ($this->isSingleline && ($this->getThumbSize()))
					{
						$this->generateThumbForFile($imagepath, $image_filename);
					}
				}
			}
		}
		return $result;
	}
	
	/**
	 * Deletes an image file
	 *
	 * @param string $image_filename Name of the image file to delete
	 */
	protected function deleteImage($image_filename)
	{
		$imagepath = $this->getImagePath();
		@unlink($imagepath . $image_filename);
		$thumbpath = $imagepath . $this->getThumbPrefix() . $image_filename;
		@unlink($thumbpath);
	}

	function duplicateImages($question_id, $objectId = null)
	{
		global $ilLog;
		$imagepath = $this->getImagePath();
		$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
		
		if( (int)$objectId > 0 )
		{
			$imagepath_original = str_replace("/$this->obj_id/", "/$objectId/", $imagepath_original);
		}
		
		foreach ($this->answers as $answer)
		{
			$filename = $answer->getImage();
			if (strlen($filename))
			{
				if (!file_exists($imagepath))
				{
					ilUtil::makeDirParents($imagepath);
				}
				if (!@copy($imagepath_original . $filename, $imagepath . $filename))
				{
					$ilLog->write("image could not be duplicated!!!!", $ilLog->ERROR);
					$ilLog->write("object: " . print_r($this, TRUE), $ilLog->ERROR);
				}
				if (@file_exists($imagepath_original. $this->getThumbPrefix(). $filename))
				{
					if (!@copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename))
					{
						$ilLog->write("image thumbnail could not be duplicated!!!!", $ilLog->ERROR);
						$ilLog->write("object: " . print_r($this, TRUE), $ilLog->ERROR);
					}
				}
			}
		}
	}

	function copyImages($question_id, $source_questionpool)
	{
		global $ilLog;
		$imagepath = $this->getImagePath();
		$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
		$imagepath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $imagepath_original);
		foreach ($this->answers as $answer)
		{
			$filename = $answer->getImage();
			if (strlen($filename))
			{
				if (!file_exists($imagepath))
				{
					ilUtil::makeDirParents($imagepath);
				}
				if (!@copy($imagepath_original . $filename, $imagepath . $filename))
				{
					$ilLog->write("image could not be duplicated!!!!", $ilLog->ERROR);
					$ilLog->write("object: " . print_r($this, TRUE), $ilLog->ERROR);
				}
				if (@file_exists($imagepath_original. $this->getThumbPrefix(). $filename))
				{
					if (!@copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename))
					{
						$ilLog->write("image thumbnail could not be duplicated!!!!", $ilLog->ERROR);
						$ilLog->write("object: " . print_r($this, TRUE), $ilLog->ERROR);
					}
				}
			}
		}
	}

	/**
	 * Sync images of a MC question on synchronisation with the original question
	 */
	protected function syncImages()
	{
		global $ilLog;
		$question_id = $this->getOriginalId();
		$imagepath = $this->getImagePath();
		$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
		ilUtil::delDir($imagepath_original);
		foreach ($this->answers as $answer)
		{
			$filename = $answer->getImage();
			if (strlen($filename))
			{
				if (@file_exists($imagepath . $filename))
				{
					if (!file_exists($imagepath))
					{
						ilUtil::makeDirParents($imagepath);
					}
					if (!file_exists($imagepath_original))
					{
						ilUtil::makeDirParents($imagepath_original);
					}
					if (!@copy($imagepath . $filename, $imagepath_original . $filename))
					{
						$ilLog->write("image could not be duplicated!!!!", $ilLog->ERROR);
						$ilLog->write("object: " . print_r($this, TRUE), $ilLog->ERROR);
					}
				}
				if (@file_exists($imagepath . $this->getThumbPrefix() . $filename))
				{
					if (!@copy($imagepath . $this->getThumbPrefix() . $filename, $imagepath_original . $this->getThumbPrefix() . $filename))
					{
						$ilLog->write("image thumbnail could not be duplicated!!!!", $ilLog->ERROR);
						$ilLog->write("object: " . print_r($this, TRUE), $ilLog->ERROR);
					}
				}
			}
		}
	}

	/**
	 * Collects all text in the question which could contain media objects which were created with the Rich Text Editor.
	 */
	function getRTETextWithMediaObjects()
	{
		$text = parent::getRTETextWithMediaObjects();
		foreach ($this->answers as $index => $answer)
		{
			$text .= $this->feedbackOBJ->getSpecificAnswerFeedbackContent($this->getId(), $index);
			$answer_obj = $this->answers[$index];
			$text .= $answer_obj->getAnswertext();
		}
		return $text;
	}
	
	/**
	* Returns a reference to the answers array
	*/
	function &getAnswers()
	{
		return $this->answers;
	}

	/**
	 * Creates an Excel worksheet for the detailed cumulated results of this question
	 *
	 * @param object $worksheet    Reference to the parent excel worksheet
	 * @param object $startrow     Startrow of the output in the excel worksheet
	 * @param object $active_id    Active id of the participant
	 * @param object $pass         Test pass
	 * @param object $format_title Excel title format
	 * @param object $format_bold  Excel bold format
	 *
	 * @return object
	 */
	public function setExportDetailsXLS(&$worksheet, $startrow, $active_id, $pass, &$format_title, &$format_bold)
	{
		include_once ("./Services/Excel/classes/class.ilExcelUtils.php");
		$solution = $this->getSolutionValues($active_id, $pass);
		$worksheet->writeString($startrow, 0, ilExcelUtils::_convert_text($this->lng->txt($this->getQuestionType())), $format_title);
		$worksheet->writeString($startrow, 1, ilExcelUtils::_convert_text($this->getTitle()), $format_title);
		$i = 1;
		foreach ($this->getAnswers() as $id => $answer)
		{
			$worksheet->writeString($startrow + $i, 0, ilExcelUtils::_convert_text($answer->getAnswertext()), $format_bold);
			$checked = FALSE;
			foreach ($solution as $solutionvalue)
			{
				if ($id == $solutionvalue["value1"])
				{
					$checked = TRUE;
				}
			}
			if ($checked)
			{
				$worksheet->write($startrow + $i, 1, 1);
			}
			else
			{
				$worksheet->write($startrow + $i, 1, 0);
			}
			$i++;
		}
		return $startrow + $i + 1;
	}

	public function getThumbSize()
	{
		return $this->thumb_size;
	}
	
	public function setThumbSize($a_size)
	{
		$this->thumb_size = $a_size;
	}

	/**
	 * Returns a JSON representation of the question
	 */
	public function toJSON()
	{
		require_once './Services/RTE/classes/class.ilRTE.php';
		$result = array();
		$result['id'] = (int) $this->getId();
		$result['type'] = (string) $this->getQuestionType();
		$result['title'] = (string) $this->getTitle();
		$result['question'] =  $this->formatSAQuestion($this->getQuestion());
		$result['nr_of_tries'] = (int) $this->getNrOfTries();
		$result['shuffle'] = (bool) $this->getShuffle();
		$result['feedback'] = array(
			"onenotcorrect" => $this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false),
			"allcorrect" => $this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true)
		);

		$answers = array();
		$has_image = false;
		foreach ($this->getAnswers() as $key => $answer_obj)
		{
			if((string) $answer_obj->getImage())
			{
				$has_image = true;
			}
			array_push($answers, array(
				"answertext" => (string) $this->formatSAQuestion($answer_obj->getAnswertext(), "\<span class\=\"latex\">", "\<\/span>"),
				"points_checked" => (float) $answer_obj->getPointsChecked(),
				"points_unchecked" => (float) $answer_obj->getPointsUnchecked(),
				"order" => (int) $answer_obj->getOrder(),
				"image" => (string) $answer_obj->getImage(),
				"feedback" => ilRTE::_replaceMediaObjectImageSrc(
						$this->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation($this->getId(), $key), 0
				)
			));
		}
		$result['answers'] = $answers;

		if($has_image)
		{
			$result['path'] = $this->getImagePathWeb();
			$result['thumb'] = $this->getThumbSize();
		}

		$mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
		$result['mobs'] = $mobs;
		
		return json_encode($result);
	}

	public function removeAnswerImage($index)
	{
		$answer = $this->answers[$index];
		if (is_object($answer))
		{
			$this->deleteImage($answer->getImage());
			$answer->setImage('');
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
	 * Sets the feedback settings in effect for the question.
	 * Options are:
	 * 1 - Feedback is shown for all answer options.
	 * 2 - Feedback is shown for all checked/selected options.
	 * 3 - Feedback is shown for all correct options.
	 * 
	 * @param integer $a_feedback_setting 
	 */
	function setSpecificFeedbackSetting($a_feedback_setting)
	{
		$this->feedback_setting = $a_feedback_setting;
	}
	
	/**
	 * Gets the current feedback settings in effect for the question.
	 * Values are:
	 * 1 - Feedback is shown for all answer options.
	 * 2 - Feedback is shown for all checked/selected options.
	 * 3 - Feedback is shown for all correct options.
	 * 
	 * @return integer 
	 */
	function getSpecificFeedbackSetting()
	{
		if ($this->feedback_setting)
		{
			return $this->feedback_setting;
		}
		else
		{
			return 1;
		}
	}
	
	/**
	 * returns boolean wether the question
	 * is answered during test pass or not
	 * 
	 * (overwrites method in class assQuestion)
	 * 
	 * @param integer $active_id
	 * @param integer $pass
	 * 
	 * @return boolean $answered
	 */
	public function isAnswered($active_id, $pass)
	{
		$numExistingSolutionRecords = assQuestion::getNumExistingSolutionRecords($active_id, $pass, $this->getId());

		return $numExistingSolutionRecords > 0;
	}
	
	/**
	 * returns boolean wether it is possible to set
	 * this question type as obligatory or not
	 * considering the current question configuration
	 * 
	 * (overwrites method in class assQuestion)
	 * 
	 * @param integer $questionId
	 * 
	 * @return boolean $obligationPossible
	 */
	public static function isObligationPossible($questionId)
	{
		/** @var $ilDB ilDB */
		global $ilDB;
		
		$query = "
			SELECT SUM(points) points_for_checked_answers
			FROM qpl_a_mc
			WHERE question_fi = %s AND points > 0
		";
		
		$res = $ilDB->queryF($query, array('integer'), array($questionId));
		
		$row = $ilDB->fetchAssoc($res);
		
		return $row['points_for_checked_answers'] > 0;
	}
	
	/**
	 * ensures that no invalid obligation is saved for the question used in test
	 * 
	 * when points can be reached ONLY by NOT check any answer
	 * a possibly still configured obligation will be removed
	 * 
	 * @param integer $questionId 
	 */
	public function ensureNoInvalidObligation($questionId)
	{
		/** @var $ilDB ilDB */
		global $ilDB;
		
		$query = "
			SELECT		SUM(qpl_a_mc.points) points_for_checked_answers,
						test_question_id
			
			FROM		tst_test_question
			
			INNER JOIN	qpl_a_mc
			ON			qpl_a_mc.question_fi = tst_test_question.question_fi
			
			WHERE		tst_test_question.question_fi = %s
			AND			tst_test_question.obligatory = 1
			
			GROUP BY	test_question_id
		";
		
		$res = $ilDB->queryF($query, array('integer'), array($questionId));
		
		$updateTestQuestionIds = array();
		
		while( $row = $ilDB->fetchAssoc($res) )
		{
			if( $row['points_for_checked_answers'] <= 0 )
			{
				$updateTestQuestionIds[] = $row['test_question_id'];
			}
		}
		
		if( count($updateTestQuestionIds) )
		{
			$test_question_id__IN__updateTestQuestionIds = $ilDB->in(
					'test_question_id', $updateTestQuestionIds, false, 'integer'
			);
			
			$query = "
				UPDATE tst_test_question
				SET obligatory = 0
				WHERE $test_question_id__IN__updateTestQuestionIds
			";
			
			$ilDB->manipulate($query);
		}
	}

	/**
	 * @return array
	 */
	protected function getSolutionSubmit()
	{
		$solutionSubmit = array();
		foreach($_POST as $key => $value)
		{
			if(preg_match("/^multiple_choice_result_(\d+)/", $key))
			{
				if(strlen($value))
				{
					$solutionSubmit[] = $value;
				}
			}
		}
		return $solutionSubmit;
	}

	/**
	 * @param $found_values
	 * @param $active_id
	 * @return int
	 */
	protected function calculateReachedPointsForSolution($found_values, $active_id = 0)
	{
		$points = 0;
		foreach($this->answers as $key => $answer)
		{
			if(in_array($key, $found_values))
			{
				$points += $answer->getPoints();
			} else
			{
				$points += $answer->getPointsUnchecked();
			}
		}
		if($active_id)
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$mc_scoring = ilObjTest::_getMCScoring($active_id);
			if(($mc_scoring == 0) && (count($found_values) == 0))
			{
				$points = 0;
			}
		}
		return $points;
	}

	/**
	 * Get all available operations for a specific question
	 *
	 * @param string $expression
	 *
	 * @internal param string $expression_type
	 * @return array
	 */
	public function getOperators($expression)
	{
		require_once "./Modules/TestQuestionPool/classes/class.ilOperatorsExpressionMapping.php";
		return ilOperatorsExpressionMapping::getOperatorsByExpression($expression);
	}

	/**
	 * Get all available expression types for a specific question
	 * @return array
	 */
	public function getExpressionTypes()
	{
		return array(
			iQuestionCondition::PercentageResultExpression,
			iQuestionCondition::NumberOfResultExpression,
			iQuestionCondition::ExclusiveResultExpression,
			iQuestionCondition::EmptyAnswerExpression,
		);
	}

	/**
	* Get the user solution for a question by active_id and the test pass
	*
	* @param int $active_id
	* @param int $pass
	*
	* @return ilUserQuestionResult
	*/
	public function getUserQuestionResult($active_id, $pass)
	{
		/** @var ilDB $ilDB */
		global $ilDB;
		$result = new ilUserQuestionResult($this, $active_id, $pass);

		$data = $ilDB->queryF(
			"SELECT value1+1 as value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = (
				SELECT MAX(step) FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s
			)",
			array("integer", "integer", "integer","integer", "integer", "integer"),
			array($active_id, $pass, $this->getId(), $active_id, $pass, $this->getId())
		);

		while($row = $ilDB->fetchAssoc($data))
		{
			$result->addKeyValue($row["value1"], $row["value1"]);
		}

		$points = $this->calculateReachedPoints($active_id, $pass);
		$max_points = $this->getMaximumPoints();

		$result->setReachedPercentage(($points/$max_points) * 100);

		return $result;
	}

	/**
	 * If index is null, the function returns an array with all anwser options
	 * Else it returns the specific answer option
	 *
	 * @param null|int $index
	 *
	 * @return array|ASS_AnswerSimple
	 */
	public function getAvailableAnswerOptions($index = null)
	{
		if($index !== null)
		{
			return $this->getAnswer($index);
		}
		else
		{
			return $this->getAnswers();
		}
	}
}