<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjAnswerScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php';
require_once './Modules/TestQuestionPool/classes/class.ilUserQuestionResult.php';

require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElementList.php';

/**
 * Class for ordering questions
 *
 * assOrderingQuestion is a class for ordering questions.
 * 
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com> 
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 *         
 * @version		$Id$
 * 
 * @ingroup		ModulesTestQuestionPool
 */
class assOrderingQuestion extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition
{
	const ORDERING_ELEMENT_FORM_FIELD_POSTVAR = 'order_elems';
	
	const ORDERING_ELEMENT_FORM_CMD_UPLOAD_IMG = 'uploadElementImage';
	const ORDERING_ELEMENT_FORM_CMD_REMOVE_IMG = 'removeElementImage';
	
	/**
	 * @var ilAssOrderingElementList
	 */
	protected $orderingElementList;
	
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

	public $old_ordering_depth = array();
	public $leveled_ordering = array();

	/**
	 * assOrderingQuestion constructor
	 *
	 * The constructor takes possible arguments an creates an instance of the assOrderingQuestion object.
	 *
	 * @param string  $title    A title string to describe the question
	 * @param string  $comment  A comment string to describe the question
	 * @param string  $author   A string containing the name of the questions author
	 * @param integer $owner    A numerical ID to identify the owner/creator
	 * @param string  $question The question string of the ordering test
	 * @param int     $ordering_type
	 */
	public function __construct(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$ordering_type = OQ_TERMS
	)
	{
		parent::__construct($title, $comment, $author, $owner, $question);
		$this->orderingElementList = new ilAssOrderingElementList();
		$this->ordering_type = $ordering_type;
	}

	/**
	* Returns true, if a ordering question is complete for use
	*
	* @return boolean True, if the ordering question is complete for use, otherwise false
	*/
	public function isComplete()
	{
		if( !$this->getAuthor() )
		{
			return false;
		}
		
		if( !$this->getTitle() )
		{
			return false;
		}
		
		if( !$this->getQuestion() )
		{
			return false;
		}
		
		if( !$this->getMaximumPoints() )
		{
			return false;
		}
		
		if( !$this->getOrderingElementList()->countElements() )
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Saves a assOrderingQuestion object to a database
	 *
	 * @param string $original_id
	 *
	 * @internal param object $db A pear DB object
	 */
	public function saveToDb($original_id = "")
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];

		$this->saveQuestionDataToDb($original_id);
		$this->saveAdditionalQuestionDataToDb();
		$this->saveAnswerSpecificDataToDb();
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
		global $DIC;
		$ilDB = $DIC['ilDB'];

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
			$this->setNrOfTries($data['nr_of_tries']);
			$this->setPoints($data["points"]);
			$this->setOwner($data["owner"]);
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
			$this->ordering_type = strlen($data["ordering_type"]) ? $data["ordering_type"] : OQ_TERMS;
			$this->thumb_geometry = $data["thumb_geometry"];
			$this->element_height = $data["element_height"];
			$this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
			
			try
			{
				$this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
			}
			catch(ilTestQuestionPoolException $e)
			{
			}
		}
		
		$this->orderingElementList->setQuestionId($this->getId());
		$this->orderingElementList->loadFromDb();
		
		parent::loadFromDb($question_id);
	}
	
	/**
	* Duplicates an assOrderingQuestion
	*
	* @access public
	*/
	function duplicate($for_test = true, $title = "", $author = "", $owner = "", $testObjId = null)
	{
		if ($this->id <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		// duplicate the question in database
		$this_id = $this->getId();
		$thisObjId = $this->getObjId();
		
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
		
		$clone->duplicateOrderlingElementList();

		// copy question page content
		$clone->copyPageOfQuestion($this_id);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($this_id);
		// duplicate the image
		$clone->duplicateImages($this_id, $thisObjId, $clone->getId(), $testObjId);
		
		$clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());
		
		return $clone->id;
	}
	
	protected function duplicateOrderlingElementList()
	{
		$this->getOrderingElementList()->setQuestionId($this->getId());
		$this->getOrderingElementList()->distributeNewRandomIdentifiers();
		$this->getOrderingElementList()->saveToDb();
	}

	/**
	* Copies an assOrderingQuestion object
	*
	* @access public
	*/
	function copyObject($target_questionpool_id, $title = "")
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
		$clone->duplicateImages($original_id, $source_questionpool_id, $clone->getId(), $target_questionpool_id);
		
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
		$clone->duplicateImages($sourceQuestionId, $sourceParentId, $clone->getId(), $clone->getObjId());

		$clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

		return $clone->id;
	}

	function duplicateImages($src_question_id, $src_object_id, $dest_question_id, $dest_object_id)
	{
		global $DIC;
		$ilLog = $DIC['ilLog'];
		if ($this->getOrderingType() == OQ_PICTURES || $this->getOrderingType() == OQ_NESTED_PICTURES)
		{
			$imagepath_original = $this->getImagePath($src_question_id, $src_object_id);
			$imagepath = $this->getImagePath($dest_question_id, $dest_object_id);

			if (!file_exists($imagepath)) {
				ilUtil::makeDirParents($imagepath);
			}
			foreach($this->getOrderingElementList() as $element)
			{
				$filename = $element->getContent();
				if (!@copy($imagepath_original . $filename, $imagepath . $filename)) 
				{
					$ilLog->write("image could not be duplicated!!!!");
				}
				if (@file_exists($imagepath_original. $this->getThumbPrefix(). $filename))
				{
					if (!@copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) 
					{
						$ilLog->write("image thumbnail could not be duplicated!!!!");
					}
				}
			}
		}
	}
	
	/**
	 * @deprecated (!)
	 * simply use the working method duplicateImages(), we do not search the difference here
	 * and we will delete this soon (!) currently no usage found, remove for il5.3
	 */
	function copyImages($question_id, $source_questionpool)
	{
		global $DIC;
		$ilLog = $DIC['ilLog'];
		if ($this->getOrderingType() == OQ_PICTURES)
		{
			$imagepath = $this->getImagePath();
			$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
			$imagepath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $imagepath_original);
			if (!file_exists($imagepath)) {
				ilUtil::makeDirParents($imagepath);
			}
			foreach($this->getOrderingElementList() as $element)
			{
				$filename = $element->getContent();
				if (!@copy($imagepath_original . $filename, $imagepath . $filename)) 
				{
					$ilLog->write("Ordering Question image could not be copied: ${imagepath_original}${filename}");
				}
				if (@file_exists($imagepath_original. $this->getThumbPrefix(). $filename))
				{
					if (!@copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) 
					{
						$ilLog->write("Ordering Question image thumbnail could not be copied: $imagepath_original" . $this->getThumbPrefix() . $filename);
					}
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
	
	public function isOrderingTypeNested()
	{
		return in_array($this->getOrderingType(), array(OQ_NESTED_TERMS, OQ_NESTED_PICTURES));
	}
	
	public function isImageOrderingType()
	{
		return in_array($this->getOrderingType(), array(OQ_PICTURES, OQ_NESTED_PICTURES));
	}
	
	public function hasOrderingTypeUploadSupport()
	{
		return $this->getOrderingType() == OQ_PICTURES;
	}
	
	/**
	 * @param $forceCorrectSolution
	 * @param $activeId
	 * @param $passIndex
	 * @return ilAssOrderingElementList
	 */
	public function getOrderingElementListForSolutionOutput($forceCorrectSolution, $activeId, $passIndex)
	{
		if( $forceCorrectSolution || !$activeId || $passIndex === null )
		{
			return $this->getOrderingElementList();
		}
		
		$solutionValues = $this->getSolutionValues($activeId, $passIndex);
		
		if( !count($solutionValues) )
		{
			return $this->getShuffledOrderingElementList();
		}
		
		return $this->getSolutionOrderingElementList( $this->fetchIndexedValuesFromValuePairs($solutionValues) );
	}
	
	/**
	 * @param ilAssNestedOrderingElementsInputGUI $inputGUI
	 * @param array $lastPost
	 * @param integer $activeId
	 * @param integer $pass
	 * @return ilAssOrderingElementList
	 * @throws ilTestException
	 * @throws ilTestQuestionPoolException
	 */
	public function getSolutionOrderingElementListForTestOutput(ilAssNestedOrderingElementsInputGUI $inputGUI, $lastPost, $activeId, $pass)
	{
		if( $inputGUI->isPostSubmit($lastPost) )
		{
			return $this->fetchSolutionListFromFormSubmissionData($lastPost);
		}
		
		// hey: prevPassSolutions - pass will be always available from now on
		#if( $pass === null && !ilObjTest::_getUsePreviousAnswers($activeId, true) )
		#// condition looks strange? yes - keep it null when previous solutions not enabled (!)
		#{
		#	$pass = ilObjTest::_getPass($activeId);
		#}
		// hey.
		
		$indexedSolutionValues = $this->fetchIndexedValuesFromValuePairs(
			// hey: prevPassSolutions - obsolete due to central check
			$this->getTestOutputSolutions($activeId, $pass)
			// hey.
		);
		
		if( count($indexedSolutionValues) )
		{
			return $this->getSolutionOrderingElementList($indexedSolutionValues);
		}
		
		return $this->getShuffledOrderingElementList();
	}
	
	/**
	 * @param string $value1
	 * @param string $value2
	 * @return ilAssOrderingElement
	 */
	protected function getSolutionValuePairBrandedOrderingElementByRandomIdentifier($value1, $value2)
	{
		$value2 = explode(':', $value2);
		
		$randomIdentifier = $value2[0];
		$selectedPosition = $value1;
		$selectedIndentation = $value2[1];
		
		$element = $this->getOrderingElementList()->getElementByRandomIdentifier($randomIdentifier)->getClone();
		
		$element->setPosition($selectedPosition);
		$element->setIndentation($selectedIndentation);
		
		return $element;
	}
	
	/**
	 * @param string $value1
	 * @param string $value2
	 * @return ilAssOrderingElement
	 */
	protected function getSolutionValuePairBrandedOrderingElementBySolutionIdentifier($value1, $value2)
	{
		$solutionIdentifier = $value1;
		$selectedPosition = ($value2 - 1);
		$selectedIndentation = 0;
		
		$element = $this->getOrderingElementList()->getElementBySolutionIdentifier($solutionIdentifier)->getClone();
		
		$element->setPosition($selectedPosition);
		$element->setIndentation($selectedIndentation);
		
		return $element;
	}
	
	/**
	 * @param array $valuePairs
	 * @return ilAssOrderingElementList
	 * @throws ilTestQuestionPoolException
	 */
	public function getSolutionOrderingElementList($indexedSolutionValues)
	{		
		$solutionOrderingList = new ilAssOrderingElementList();
		$solutionOrderingList->setQuestionId($this->getId());
		
		foreach($indexedSolutionValues as $value1 => $value2)
		{
			if( $this->isOrderingTypeNested() )
			{
				$element = $this->getSolutionValuePairBrandedOrderingElementByRandomIdentifier($value1, $value2);
			}
			else
			{
				$element = $this->getSolutionValuePairBrandedOrderingElementBySolutionIdentifier($value1, $value2);
			}
			
			$solutionOrderingList->addElement($element);
		}
		
		if( !$this->getOrderingElementList()->hasSameElementSetByRandomIdentifiers($solutionOrderingList) )
		{
			throw new ilTestQuestionPoolException('inconsistent solution values given');
		}
		
		return $solutionOrderingList;
	}
	
	/**
	 * @param $active_id
	 * @param $pass
	 * @return ilAssOrderingElementList
	 */
	public function getShuffledOrderingElementList()
	{
		$shuffledRandomIdentifierIndex = $this->getShuffler()->shuffle(
			$this->getOrderingElementList()->getRandomIdentifierIndex()
		);
		
		$shuffledElementList = $this->getOrderingElementList()->getClone();
		$shuffledElementList->reorderByRandomIdentifiers($shuffledRandomIdentifierIndex);
		$shuffledElementList->resetElementsIndentations();
		
		return $shuffledElementList;
	}
	
	/**
	 * @return ilAssOrderingElementList
	 */
	public function getOrderingElementList()
	{
		return $this->orderingElementList;
	}
	
	/**
	 * @param ilAssOrderingElementList $orderingElementList
	 */
	public function setOrderingElementList($orderingElementList)
	{
		$this->orderingElementList = $orderingElementList;
	}
	
	/**
	 * @param $position
	 *
	 * TODO: still in use? should not since element moving is js supported!?
	 */
	public function moveAnswerUp($position)
	{
		if( !$this->getOrderingElementList()->elementExistByPosition($position) )
		{
			return false;
		}
		
		if( $this->getOrderingElementList()->isFirstElementPosition($position) )
		{
			return false;
		}
		
		$this->getOrderingElementList()->moveElementByPositions($position, $position - 1);
	}
	
	/**
	 * @param $position
	 *
	 * TODO: still in use? should not since element moving is js supported!?
	 */
	public function moveAnswerDown($position)
	{
		if( !$this->getOrderingElementList()->elementExistByPosition($position) )
		{
			return false;
		}
		
		if( $this->getOrderingElementList()->isLastElementPosition($position) )
		{
			return false;
		}
		
		$this->getOrderingElementList()->moveElementByPositions($position, $position + 1);
		
		return true;
	}
	
	/**
	 * Returns the ordering element from the given position.
	 * 
	 * @param int $position
	 * @return ilAssOrderingElement|null
	 */
	public function getAnswer($index = 0)
	{
		if( !$this->getOrderingElementList()->elementExistByPosition($index) )
		{
			return null;
		}
		
		return $this->getOrderingElementList()->getElementByPosition($index);
	}

	/**
	* Deletes an answer with a given index. The index of the first
	* answer is 0, the index of the second answer is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th answer
	* @access public
	* @see $answers
	*/
	function deleteAnswer($randomIdentifier)
	{
		$this->getOrderingElementList()->removeElement(
			$this->getOrderingElementList()->getElementByRandomIdentifier($randomIdentifier)
		);
		$this->getOrderingElementList()->saveToDb();
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
		return $this->getOrderingElementList()->countElements();
	}

	/**
	 * Returns the points, a learner has reached answering the question.
	 * The points are calculated from the given answers.
	 * 
	 * @access public
	 * @param integer $active_id
	 * @param integer $pass
	 * @param boolean $returndetails (deprecated !!)
	 * @return integer/array $points/$details (array $details is deprecated !!)
	 */
	public function calculateReachedPoints($active_id, $pass = NULL, $authorizedSolution = true, $returndetails = FALSE)
	{
		if( $returndetails )
		{
			throw new ilTestException('return details not implemented for '.__METHOD__);
		}
		
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}
		
		$solutionValuePairs = $this->getSolutionValues($active_id, $pass, $authorizedSolution);
		
		if( !count($solutionValuePairs) )
		{
			return 0;
		}
		
		$indexedSolutionValues = $this->fetchIndexedValuesFromValuePairs($solutionValuePairs);
		$solutionOrderingElementList = $this->getSolutionOrderingElementList($indexedSolutionValues);
		
		return $this->calculateReachedPointsForSolution($solutionOrderingElementList);
	}
	
	public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $previewSession)
	{
		if( !$previewSession->hasParticipantSolution() )
		{
			return 0;
		}
		
		$solutionOrderingElementList = unserialize(
			$previewSession->getParticipantsSolution()
		);
		
		return $this->calculateReachedPointsForSolution($solutionOrderingElementList);
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
						foreach($this->getOrderingElementList() as $orderElement)
						{
							if (strcmp($f['entry'], $orderElement->getContent()) == 0) $found = true;
							if (strcmp($f['entry'], $this->getThumbPrefix() .  $orderElement->getContent()) == 0) $found = true;
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
	public function dropImageFile($imageFilename)
	{
		if( !strlen($imageFilename) )
		{
			return false;
		}
		
		$result = @unlink($this->getImagePath().$imageFilename);
		$result = $result & @unlink($this->getImagePath().$this->getThumbPrefix() . $imageFilename);
		
		return $result;
	}
	
	public function isImageFileStored($imageFilename)
	{
		if( !strlen($imageFilename) )
		{
			return false;
		}
		
		if( !file_exists($this->getImagePath().$imageFilename) )
		{
			return false;
		}
		
		return is_file($this->getImagePath().$imageFilename);
	}
	
	public function isImageReplaced(ilAssOrderingElement $newElement, ilAssOrderingElement $oldElement)
	{
		if( !$this->hasOrderingTypeUploadSupport() )
		{
			return false;
		}
		
		if( !$newElement->getContent() )
		{
			return false;
		}
		
		return $newElement->getContent() != $oldElement->getContent();
	}

	/**
	* Sets the image file and uploads the image to the object's image directory.
	*
	* @param string $image_filename Name of the original image file
	* @param string $image_tempfilename Name of the temporary uploaded image file
	* @return integer An errorcode if the image upload fails, 0 otherwise
	* @access public
	*/
	function storeImageFile($uploadFile, $targetFile)
	{
		if( !strlen($uploadFile) )
		{
			return false;
		}
		
		$this->ensureImagePathExists();
		
		// store file with hashed name
		
		if( !ilUtil::moveUploadedFile($uploadFile, $targetFile, $this->getImagePath().$targetFile) )
		{
			return false;
		}

		return true;
	}
	
	public function handleThumbnailCreation(ilAssOrderingElementList $elementList)
	{
		foreach($elementList as $element)
		{
			$this->createImageThumbnail($element);
		}
	}
	
	public function createImageThumbnail(ilAssOrderingElement $element)
	{
		if( $this->getThumbGeometry() )
		{
			$imageFile = $this->getImagePath() . $element->getContent();
			$thumbFile = $this->getImagePath() . $this->getThumbPrefix().$element->getContent();
			
			ilUtil::convertImage( $imageFile, $thumbFile, "JPEG", $this->getThumbGeometry() );
		}
	}

	/**
	* Checks the data to be saved for consistency
	*
  * @return boolean True, if the check was ok, False otherwise
	* @access public
	* @see $answers
	*/
	public function validateSolutionSubmit()
	{
		$submittedSolutionList = $this->getSolutionListFromPostSubmit();
		return $this->getOrderingElementList()->hasSameElementSetByRandomIdentifiers($submittedSolutionList);
	}

	/**
	 * Saves the learners input of the question to the database.
	 * 
	 * @access public
	 * @param integer $active_id Active id of the user
	 * @param integer $pass Test pass
	 * @return boolean $status
	 */
	public function saveWorkingData($active_id, $pass = NULL, $authorized = true)
	{
		$entered_values = 0;
		
		if (is_null($pass))
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$pass = ilObjTest::_getPass($active_id);
		}

		$this->getProcessLocker()->executeUserSolutionUpdateLockOperation(
			function() use (&$entered_values, $active_id, $pass, $authorized)
			{
				$this->removeCurrentSolution($active_id, $pass, $authorized);
		
				foreach( $this->getSolutionListFromPostSubmit() as $orderingElement)
				{
					$value1 = $orderingElement->getStorageValue1($this->getOrderingType());
					$value2 = $orderingElement->getStorageValue2($this->getOrderingType());
					
					$this->saveCurrentSolution($active_id, $pass, $value1, trim($value2), $authorized);
					
					$entered_values++;
				}
			}
		);
		
		if ($entered_values)
		{
			$this->log($active_id, 'log_user_entered_values');
		}
		else
		{
			$this->log($active_id, 'log_user_not_entered_values');
		}

		return true;
	}
	
	protected function savePreviewData(ilAssQuestionPreviewSession $previewSession)
	{
		if( $this->validateSolutionSubmit() )
		{
			$previewSession->setParticipantsSolution( serialize($this->getSolutionListFromPostSubmit()) );
		}
	}

	public function saveAdditionalQuestionDataToDb()
	{
		/** @var ilDBInterface $ilDB */
		global $DIC;
		$ilDB = $DIC['ilDB'];

		// save additional data
		$ilDB->manipulateF( "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
							array( "integer" ),
							array( $this->getId() )
		);

		$ilDB->manipulateF( "INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, ordering_type, thumb_geometry, element_height) 
							VALUES (%s, %s, %s, %s)",
							array( "integer", "text", "integer", "integer" ),
							array(
								$this->getId(),
								$this->ordering_type,
								$this->getThumbGeometry(),
								($this->getElementHeight() > 20) ? $this->getElementHeight() : NULL
							)
		);
	}

	public function saveAnswerSpecificDataToDb()
	{
		$this->getOrderingElementList()->setQuestionId($this->getId());
		$this->getOrderingElementList()->saveToDb();
		
		if( $this->hasOrderingTypeUploadSupport() )
		{
			$this->rebuildThumbnails();
			$this->cleanImagefiles();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function reworkWorkingData($active_id, $pass, $obligationsAnswered, $authorized)
	{
		// nothing to rework!
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
		
		foreach ( $this->getOrderingElementList() as $orderingElement)
		{
			$text .= $orderingElement->getContent();
		}
		
		return $text;
	}
	
	/**
	 * Returns the answers array
	 * @deprecated seriously, stop looking for this kind data at this point (!) look where it comes from and learn (!)
	 */
	function getOrderElements()
	{
		return $this->getOrderingElementList()->getRandomIdentifierIndexedElements();
	}

	/**
	* Returns true if the question type supports JavaScript output
	*
	* @return boolean TRUE if the question type supports JavaScript output, FALSE otherwise
	* @access public
	*/
	public function supportsJavascriptOutput()
	{
		return TRUE;
	}
	
	public function supportsNonJsOutput()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setExportDetailsXLS($worksheet, $startrow, $active_id, $pass)
	{
		parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);

		$solutions = $this->getSolutionValues($active_id, $pass);
		$sol = array();
		foreach ($solutions as $solution)
		{
			$sol[$solution["value1"]] = $solution["value2"];
		}
		asort($sol);
		$sol = array_keys($sol);

		$i = 1;
		foreach ($sol as $idx)
		{
			foreach ($solutions as $solution)
			{
				if ($solution["value1"] == $idx) $worksheet->setCell($startrow + $i, 0, $solution["value2"]);
			}
			$element = $this->getOrderingElementList()->getElementByPosition($idx);
			$worksheet->setCell($startrow + $i, 1, $element->getContent());
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
	
	public function getThumbSize()
	{
		return $this->getThumbGeometry();
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
		if ($this->getOrderingType() == OQ_PICTURES || $this->getOrderingType() == OQ_NESTED_PICTURES)
		{
			foreach ( $this->getOrderElements() as $orderingElement)
			{
				$this->generateThumbForFile($this->getImagePath(), $orderingElement->getContent());
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
	
	/**
	* Returns a JSON representation of the question
	*/
	public function toJSON()
	{
		include_once("./Services/RTE/classes/class.ilRTE.php");
		$result = array();
		$result['id'] = (int) $this->getId();
		$result['type'] = (string) $this->getQuestionType();
		$result['title'] = (string) $this->getTitle();
		$result['question'] =  $this->formatSAQuestion($this->getQuestion());
		$result['nr_of_tries'] = (int) $this->getNrOfTries();
		$result['shuffle'] = (bool) true;
		$result['points'] =  $this->getPoints();
		$result['feedback'] = array(
			'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
			'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
		);
		if ($this->getOrderingType() == OQ_PICTURES)
		{
			$result['path'] = $this->getImagePathWeb();
		}
		
		$counter = 1;
		$answers = array();
		foreach ( $this->getOrderingElementList() as $orderingElement)
		{
			$answers[$counter] = $orderingElement->getContent();
			$counter++;
		}
		$answers = $this->getShuffler()->shuffle($answers);
		$arr = array();
		foreach ($answers as $order => $answer)
		{
			array_push($arr, array(
				"answertext" => (string) $answer,
				"order" => (int) $order
			));
		}
		$result['answers'] = $arr;

		$mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
		$result['mobs'] = $mobs;

		return json_encode($result);
	}
	
	/**
	 * @return ilAssNestedOrderingElementsInputGUI|ilAssOrderingImagesInputGUI|ilAssOrderingTextsInputGUI
	 * @throws ilTestQuestionPoolException
	 */
	public function buildOrderingElementInputGui()
	{
		switch( $this->getOrderingType() )
		{
			case OQ_TERMS:
				
				return $this->buildOrderingTextsInputGui();
				
			case OQ_PICTURES:
				
				return $this->buildOrderingImagesInputGui();
			
			case OQ_NESTED_TERMS:
			case OQ_NESTED_PICTURES:
				
				return $this->buildNestedOrderingElementInputGui();
				
			default:
				throw new ilTestQuestionPoolException('unknown ordering mode');
		}
	}

	/**
	 * @param ilAssOrderingTextsInputGUI|ilAssOrderingImagesInputGUI|ilAssNestedOrderingElementsInputGUI $formField
	 */
	public function initOrderingElementAuthoringProperties(ilFormPropertyGUI $formField)
	{
		switch( true )
		{
			case $formField instanceof ilAssNestedOrderingElementsInputGUI:
				
				$formField->setInteractionEnabled(true);
				$formField->setNestingEnabled($this->isOrderingTypeNested());
				break;
			
			case $formField instanceof ilAssOrderingTextsInputGUI:
			case $formField instanceof ilAssOrderingImagesInputGUI:
			default:
			
				$formField->setEditElementOccuranceEnabled(true);
				$formField->setEditElementOrderEnabled(true);
		}
		
		$formField->setRequired(true);
	}
	
	/**
	 * @param ilFormPropertyGUI $formField
	 */
	public function initOrderingElementFormFieldLabels(ilFormPropertyGUI $formField)
	{
		$formField->setInfo($this->lng->txt('ordering_answer_sequence_info'));
		$formField->setTitle($this->lng->txt('answers'));
	}
	
	/**
	 * @return ilAssOrderingTextsInputGUI
	 */
	public function buildOrderingTextsInputGui()
	{
		$formDataConverter = $this->buildOrderingTextsFormDataConverter();
		
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingTextsInputGUI.php';
		
		$orderingElementInput = new ilAssOrderingTextsInputGUI(
			$formDataConverter, self::ORDERING_ELEMENT_FORM_FIELD_POSTVAR
		);
		
		$this->initOrderingElementFormFieldLabels($orderingElementInput);
		
		return $orderingElementInput;
	}
	
	/**
	 * @return ilAssOrderingImagesInputGUI
	 */
	public function buildOrderingImagesInputGui()
	{
		$formDataConverter = $this->buildOrderingImagesFormDataConverter();
		
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingImagesInputGUI.php';
		
		$orderingElementInput = new ilAssOrderingImagesInputGUI(
			$formDataConverter, self::ORDERING_ELEMENT_FORM_FIELD_POSTVAR
		);
		
		$orderingElementInput->setImageRemovalCommand(self::ORDERING_ELEMENT_FORM_CMD_REMOVE_IMG);
		$orderingElementInput->setImageUploadCommand(self::ORDERING_ELEMENT_FORM_CMD_UPLOAD_IMG);
		
		$this->initOrderingElementFormFieldLabels($orderingElementInput);
		
		return $orderingElementInput;
	}
	
	/**
	 * @return ilAssNestedOrderingElementsInputGUI
	 */
	public function buildNestedOrderingElementInputGui()
	{
		$formDataConverter = $this->buildNestedOrderingFormDataConverter();
		
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssNestedOrderingElementsInputGUI.php';
		
		$orderingElementInput = new ilAssNestedOrderingElementsInputGUI(
			$formDataConverter, self::ORDERING_ELEMENT_FORM_FIELD_POSTVAR
		);
		
		$orderingElementInput->setUniquePrefix($this->getId());
		$orderingElementInput->setOrderingType($this->getOrderingType());
		$orderingElementInput->setElementImagePath($this->getImagePathWeb());
		$orderingElementInput->setThumbPrefix($this->getThumbPrefix());
		
		$this->initOrderingElementFormFieldLabels($orderingElementInput);
		
		return $orderingElementInput;
	}
	
	/**
	 * @param ilPropertyFormGUI $form
	 * @return ilAssOrderingElementList $submittedElementList
	 */
	public function fetchSolutionListFromSubmittedForm(ilPropertyFormGUI $form)
	{
		return $form->getItemByPostVar(self::ORDERING_ELEMENT_FORM_FIELD_POSTVAR)->getElementList($this->getId());
	}
	
	/**
	 * @param array $userSolutionPost
	 * @return ilAssOrderingElementList
	 * @throws ilTestException
	 */
	public function fetchSolutionListFromFormSubmissionData($userSolutionPost)
	{
		$orderingGUI = $this->buildNestedOrderingElementInputGui();
		$orderingGUI->setContext(ilAssNestedOrderingElementsInputGUI::CONTEXT_USER_SOLUTION_SUBMISSION);
		$orderingGUI->setValueByArray($userSolutionPost);
		
		if( !$orderingGUI->checkInput() )
		{
			require_once 'Modules/Test/exceptions/class.ilTestException.php';
			throw new ilTestException('error on validating user solution post');
		}
		
		require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElementList.php';
		$solutionOrderingElementList = ilAssOrderingElementList::buildInstance($this->getId());
		
		$storedElementList = $this->getOrderingElementList();
		
		foreach($orderingGUI->getElementList($this->getId()) as $submittedElement)
		{
			$solutionElement = $storedElementList->getElementByRandomIdentifier(
				$submittedElement->getRandomIdentifier()
			)->getClone();
			
			$solutionElement->setPosition($submittedElement->getPosition());

			if( $this->isOrderingTypeNested() )
			{
				$solutionElement->setIndentation($submittedElement->getIndentation());
			}
			
			$solutionOrderingElementList->addElement($solutionElement);
		}
		
		return $solutionOrderingElementList;
	}
	
	/**
	 * @var ilAssOrderingElementList
	 */
	private $postSolutionOrderingElementList = null;
	
	/**
	 * @return ilAssOrderingElementList
	 */
	public function getSolutionListFromPostSubmit()
	{
		if( $this->postSolutionOrderingElementList === null )
		{
			$list = $this->fetchSolutionListFromFormSubmissionData($_POST);
			$this->postSolutionOrderingElementList = $list;
		}
		
		return $this->postSolutionOrderingElementList;
	}
	
	/**
	 * @return array
	 */
	public function getSolutionPostSubmit()
	{
		return $this->fetchSolutionSubmit($_POST);
	}

	/**
	 * @param $user_order
	 * @param $nested_solution
	 * @return int
	 */
	protected function calculateReachedPointsForSolution(ilAssOrderingElementList $solutionOrderingElementList)
	{
		$reachedPoints = $this->getPoints();

		foreach($this->getOrderingElementList() as $correctElement)
		{
			$userElement = $solutionOrderingElementList->getElementByPosition( $correctElement->getPosition() );
			
			if( !$correctElement->isSameElement($userElement) )
			{
				$reachedPoints = 0;
				break;
			}
		}
		
		return $reachedPoints;
	}

	/***
	 * @param object 	$child
	 * @param integer 	$ordering_depth
	 * @param bool 		$with_random_id
	 */
	public function getLeveledOrdering()
	{
		return $this->leveled_ordering;
	}

	public function getOldLeveledOrdering()
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];

		$res = $ilDB->queryF('SELECT depth FROM qpl_a_ordering WHERE question_fi = %s ORDER BY position ASC',
			array('integer'), array($this->getId()));
		while($row = $ilDB->fetchAssoc($res))
		{
			$this->old_ordering_depth[] = $row['depth'];
		}
		return $this->old_ordering_depth;
	}
	
	/***
	 * @param integer $a_random_id
	 * @return integer
	 */
	public function lookupSolutionOrderByRandomid($a_random_id)
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];
		
		$res = $ilDB->queryF('SELECT solution_key FROM qpl_a_ordering WHERE random_id = %s',
		array('integer'), array($a_random_id));
		$row = $ilDB->fetchAssoc($res);
		
		return $row['solution_key'];
	}

	public function updateLeveledOrdering($a_index, $a_answer_text, $a_depth)
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];
		
		$ilDB->update('qpl_a_ordering',
		array('solution_key'=> array('integer', $a_index),
			  'depth'		=> array('integer', $a_depth)),
		array('answertext' 	=> array('text', $a_answer_text)));
		
		
		return true;
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
			iQuestionCondition::NumericResultExpression,
			iQuestionCondition::OrderingResultExpression,
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
		/** @var ilDBInterface $ilDB */
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$result = new ilUserQuestionResult($this, $active_id, $pass);

		$maxStep = $this->lookupMaxStep($active_id, $pass);

		if( $maxStep !== null )
		{
			$data = $ilDB->queryF(
				"SELECT value1, value2 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s ORDER BY value1 ASC ",
				array("integer", "integer", "integer","integer"),
				array($active_id, $pass, $this->getId(), $maxStep)
			);
		}
		else
		{
			$data = $ilDB->queryF(
				"SELECT value1, value2 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s ORDER BY value1 ASC ",
				array("integer", "integer", "integer"),
				array($active_id, $pass, $this->getId())
			);
		}

		$elements = array();
		while($row = $ilDB->fetchAssoc($data))
		{

			$newKey = explode(":", $row["value2"]);

			foreach( $this->getOrderingElementList() as $answer)
			{
				// Images nut supported
				if( !$this->isOrderingTypeNested() )
				{
					if($answer->getSolutionIdentifier() == $row["value1"])
					{
						$elements[$row["value2"]] = $answer->getSolutionIdentifier() + 1;
						break;
					}
				}
				else
				{
					if($answer->getRandomIdentifier() == $newKey[0])
					{
						$elements[$row["value1"]] = $answer->getSolutionIdentifier() + 1;
						break;
					}
				}
			}
		}

		ksort($elements);

		foreach(array_values($elements) as $element)
		{
			$result->addKeyValue($element, $element);
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
			return $this->getOrderingElementList()->getElementByPosition($index);
		}

		return $this->getOrderingElementList()->getElements();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function afterSyncWithOriginal($origQuestionId, $dupQuestionId, $origParentObjId, $dupParentObjId)
	{
		parent::afterSyncWithOriginal($origQuestionId, $dupQuestionId, $origParentObjId, $dupParentObjId);
		$this->duplicateImages($dupQuestionId, $dupParentObjId, $origQuestionId, $origParentObjId);
	}

// fau: testNav - new function getTestQuestionConfig()
	/**
	 * Get the test question configuration
	 * @return ilTestQuestionConfig
	 */
	// hey: refactored identifiers
	public function buildTestPresentationConfig()
	// hey.
	{
		// hey: refactored identifiers
		return parent::buildTestPresentationConfig()
		// hey.
			->setIsUnchangedAnswerPossible(true)
			->setUseUnchangedAnswerLabel($this->lng->txt('tst_unchanged_order_is_correct'));
	}
	// fau.
	
	protected function ensureImagePathExists()
	{
		if( !file_exists($this->getImagePath()) )
		{
			ilUtil::makeDirParents($this->getImagePath());
		}
	}
	
	/**
	 * @return array
	 */
	public function fetchSolutionSubmit($formSubmissionDataStructure)
	{
		$solutionSubmit = array();
		
		if( isset($formSubmissionDataStructure['orderresult']) )
		{
			$orderresult = $formSubmissionDataStructure['orderresult'];
			
			if( strlen($orderresult) )
			{
				$orderarray = explode(":", $orderresult);
				$ordervalue = 1;
				foreach( $orderarray as $index )
				{
					$idmatch = null;
					if( preg_match("/id_(\\d+)/", $index, $idmatch) )
					{
						$randomid = $idmatch[1];
						foreach( $this->getOrderingElementList() as $answeridx => $answer )
						{
							if( $answer->getRandomIdentifier() == $randomid )
							{
								$solutionSubmit[$answeridx] = $ordervalue;
								$ordervalue++;
							}
						}
					}
				}
			}
		}
		else if( $this->getOrderingType() == OQ_NESTED_TERMS || $this->getOrderingType() == OQ_NESTED_PICTURES )
		{
			$index = 0;
			foreach( $formSubmissionDataStructure['content'] as $randomId => $content )
			{
				$indentation = $formSubmissionDataStructure['indentation'];
				
				$value1 = $index++;
				$value2 = implode(':', array($randomId, $indentation));
				
				$solutionSubmit[$value1] = $value2;
			}
		}
		else
		{
			foreach( $formSubmissionDataStructure as $key => $value )
			{
				$matches = null;
				if( preg_match("/^order_(\d+)/", $key, $matches) )
				{
					if( !( preg_match("/initial_value_\d+/", $value) ) )
					{
						if( strlen($value) )
						{
							foreach( $this->getOrderingElementList() as $answeridx => $answer )
							{
								if( $answer->getRandomIdentifier() == $matches[1] )
								{
									$solutionSubmit[$answeridx] = $value;
								}
							}
						}
					}
				}
			}
		}
		
		return $solutionSubmit;
	}
	
	/**
	 * @return ilAssOrderingFormValuesObjectsConverter
	 */
	protected function buildOrderingElementFormDataConverter()
	{
		require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingFormValuesObjectsConverter.php';
		$converter = new ilAssOrderingFormValuesObjectsConverter();
		$converter->setPostVar(self::ORDERING_ELEMENT_FORM_FIELD_POSTVAR);
		
		return $converter;
	}
	
	/**
	 * @return ilAssOrderingFormValuesObjectsConverter
	 */
	protected function buildOrderingImagesFormDataConverter()
	{
		$formDataConverter = $this->buildOrderingElementFormDataConverter();
		$formDataConverter->setContext(ilAssOrderingFormValuesObjectsConverter::CONTEXT_MAINTAIN_ELEMENT_IMAGE);
		
		$formDataConverter->setImageRemovalCommand(self::ORDERING_ELEMENT_FORM_CMD_REMOVE_IMG);
		$formDataConverter->setImageUrlPath($this->getImagePathWeb());
		$formDataConverter->setImageFsPath($this->getImagePath());
		
		if( $this->getThumbSize() && $this->getThumbPrefix() )
		{
			$formDataConverter->setThumbnailPrefix($this->getThumbPrefix());
		}
		return $formDataConverter;
	}
	
	/**
	 * @return ilAssOrderingFormValuesObjectsConverter
	 */
	protected function buildOrderingTextsFormDataConverter()
	{
		$formDataConverter = $this->buildOrderingElementFormDataConverter();
		$formDataConverter->setContext(ilAssOrderingFormValuesObjectsConverter::CONTEXT_MAINTAIN_ELEMENT_TEXT);
		return $formDataConverter;
	}
	
	/**
	 * @return ilAssOrderingFormValuesObjectsConverter
	 */
	protected function buildNestedOrderingFormDataConverter()
	{
		$formDataConverter = $this->buildOrderingElementFormDataConverter();
		$formDataConverter->setContext(ilAssOrderingFormValuesObjectsConverter::CONTEXT_MAINTAIN_HIERARCHY);
		
		if( $this->getOrderingType() == OQ_NESTED_PICTURES )
		{
			$formDataConverter->setImageRemovalCommand(self::ORDERING_ELEMENT_FORM_CMD_REMOVE_IMG);
			$formDataConverter->setImageUrlPath($this->getImagePathWeb());
			
			if( $this->getThumbSize() && $this->getThumbPrefix() )
			{
				$formDataConverter->setThumbnailPrefix($this->getThumbPrefix());
			}
		}
		
		return $formDataConverter;
	}
}
