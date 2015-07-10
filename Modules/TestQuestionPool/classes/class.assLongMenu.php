<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php';
require_once './Modules/TestQuestionPool/classes/class.ilUserQuestionResult.php';


class assLongMenu extends assQuestion implements ilObjQuestionScoringAdjustable, iQuestionCondition
{
	private $shuffleAnswersEnabled;

	private $answerType, $long_menu_text;

	const ANSWER_TYPE_SELECT 	= 'select';
	const ANSWER_TYPE_TEXT_BOX	= 'text_box';
	/**
	 * @return mixed
	 */
	public function getAnswerType()
	{
		return $this->answerType;
	}

	private function buildFolderName()
	{
		return ilUtil::getDataDir() . '/assessment/longMenuQuestion/' . $this->getId() . '/' ;
	}
	private function buildFileName($gap_id)
	{
		try
		{
			$this->assertDirExists();
			return $this->buildFolderName($this->getId()) . $gap_id . '.txt';
		}
		catch (ilException $e) {
			
		}
	}

	/**
	 * @param mixed $answerType
	 */
	public function setAnswerType($answerType)
	{
		$this->answerType = $answerType;
	}

	function setLongMenuTextValue($long_menu_text = "")
	{
		$this->long_menu_text = $long_menu_text;
	}

	function getLongMenuTextValue()
	{
		return $this->long_menu_text;
	}
	
	/**
	 * @param ilLanguage $lng
	 * @return array
	 */
	public function getAnswerTypeSelectOptions(ilLanguage $lng)
	{
		return array(
			self::ANSWER_TYPE_SELECT => $lng->txt('answers_select'),
			self::ANSWER_TYPE_TEXT_BOX => $lng->txt('answers_text_box')
		);
	}

	function __construct(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""
	)
	{
		parent::__construct($title, $comment, $author, $owner, $question);
		$this->parameters = array();
	}
	
	public function isComplete()
	{
		if (strlen($this->title)
			&& $this->author
			&& $this->question
			&& $this->getPoints() > 0
		)
		{
			return true;
		}
		else if (strlen($this->title)
			&& $this->author
			&& $this->question
			&& $this->getPoints() > 0
		)
		{
			return true;
		}
		return false;
	}
	
	public function saveToDb($original_id = "")
	{
		$this->saveQuestionDataToDb($original_id);
		$this->saveAdditionalQuestionDataToDb();
		parent::saveToDb($original_id);
	}

	public function saveAdditionalQuestionDataToDb()
	{
		global $ilDB;
		// save additional data
		$ilDB->manipulateF( "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
			array( "integer" ),
			array( $this->getId() )
		);
		$ilDB->manipulateF( "INSERT INTO " . $this->getAdditionalTableName(
			) . " (question_fi, long_menu_text) VALUES (%s, %s)",
			array( "integer", "text"),
			array(
				$this->getId(),
				$this->getLongMenuTextValue()
			)
		);
		$this->createFileFromArray();
	}
	
	private function createFileFromArray()
	{
		$array = json_decode(ilUtil::stripSlashes($_POST['hidden_text_files']));
		$this->clearFolder();
		foreach($array as $gap => $values)
		{
			$file_content = '';
			if(is_array($values))
			{
				foreach($values as $key => $value)
				{
					$file_content .= $value . '\n';
				}
				$file_content = rtrim($file_content, '\n'); 
				$file = fopen($this->buildFileName($gap), "w");
				fwrite($file, $file_content);
				fclose($file);
			}
		}
	}

	private function createArrayFromFile()
	{
		$answers = array();
		$dir = $this->buildFolderName();
		$dir_h = opendir($dir);
		while($file = readdir($dir_h))
		{
			if ($file != "." && $file != "..")
			{
				$gap					= str_replace('.txt', '', $file);
				$answers[(int) $gap] 	= explode('\n', file_get_contents($dir . $file));
			}
		}
		return $answers;
	}
	
	private function clearFolder()
	{
		ilUtil::delDir($this->buildFolderName(), true);
	}
	private function assertDirExists()
	{
		$folder_name = $this->buildFolderName();
		if(!ilUtil::makeDirParents($folder_name))
		{
			throw new ilException('Cannot create export directory');
		}

		if(
			!is_dir($folder_name) ||
			!is_readable($folder_name) ||
			!is_writable($folder_name)
		)
		{
			throw new ilException('Cannot create export directory');
		}
	}
	
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
			$this->setNrOfTries($data['nr_of_tries']);
			$this->setTitle($data["title"]);
			$this->setComment($data["description"]);
			$this->setOriginalId($data["original_id"]);
			$this->setAuthor($data["author"]);
			$this->setPoints($data["points"]);
			$this->setOwner($data["owner"]);
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data['question_text'], 1));
			$this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
			$this->setLongMenuTextValue(ilRTE::_replaceMediaObjectImageSrc($data['long_menu_text'], 1));
			try
			{
				$this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
			}
			catch(ilTestQuestionPoolException $e)
			{
			}
		}
		parent::loadFromDb($question_id);
	}
	
	function getAnswersObject()
	{
		return json_encode($this->createArrayFromFile());
		$a = array(1,2,3,4,5,6,7,8,9,0);
		$b = array(1,2,3,4,5,6,7,8,9,0);
		return json_encode(array($a, $b));
	}
	
	function getCorrectAnswers()
	{
		//Todo: remove workaround and return the real correct answers
		$answers = $this->createArrayFromFile();
		$return_array = array();
		foreach( $answers as $key => $value )
		{
			$val1 = $value[rand(0, sizeof($value) / 2)];
			$val2 = $value[rand(0, sizeof($value) / 2)];
			$return_array[(int) $key] = array(0 => array($val1, $val2));
		}
		return json_encode($return_array);
		$a = array('correct_answers' => array(1,2,3));
		$b = array('correct_answers' => array(9,0));
		return json_encode(array($a, $b));
	}

	function duplicate($for_test = true, $title = "", $author = "", $owner = "", $testObjId = null)
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
		// duplicate the image

		$clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());

		return $clone->id;
	}

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

		$clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

		return $clone->id;
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
	 * @return integer/array $points/$details (array $details is deprecated !!)
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
		$result = $ilDB->queryF("SELECT points FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			array('integer','integer','integer'),
			array($active_id, $this->getId(), $pass)
		);
		$points = 0;
		while ($data = $ilDB->fetchAssoc($result))
		{
			$points += $data["points"];
		}

		return $points;
	}

	public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $previewSession)
	{
		$points = 0;
		foreach($previewSession->getParticipantsSolution() as $solution)
		{
			if( isset($solution['points']) )
			{
				$points += $solution['points'];
			}
		}
		return $points;
	}

	/**
	 * Returns the evaluation data, a learner has entered to answer the question
	 *
	 * @param      $active_id
	 * @param null $pass
	 *
	 * @return array
	 */
	public function getReachedInformation($active_id, $pass = NULL)
	{
		global $ilDB;

		$found_values = array();
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}
		$result = $ilDB->queryF("SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			array('integer','integer','integer'),
			array($active_id, $this->getId(), $pass)
		);
		$counter = 1;
		$user_result = array();
		while ($data = $ilDB->fetchAssoc($result))
		{
			$true = 0;
			if ($data["points"] > 0)
			{
				$true = 1;
			}
			$solution = array(
				"order" => $counter,
				"points" => $data["points"],
				"true" => $true,
				"value1" => $data["value1"],
				"value2" => $data["value2"],
			);
			$counter++;
			array_push($user_result, $solution);
		}
		return $user_result;
	}

	/**
	 * Adds a new parameter value to the parameter list
	 *
	 * @param string $name The name of the parameter value
	 * @param string $value The value of the parameter value
	 * @access public
	 * @see $parameters
	 */
	function addParameter($name = "", $value = "")
	{
		$index = $this->getParameterIndex($name);
		if ($index > -1)
		{
			$this->parameters[$index] = array("name" => $name, "value" => $value);
		}
		else
		{
			array_push($this->parameters, array("name" => $name, "value" => $value));
		}
	}

	public function addParameterAtIndex($index = 0, $name = "", $value = "")
	{
		if (array_key_exists($index, $this->parameters))
		{
			// insert parameter
			$newparams = array();
			for ($i = 0; $i < $index; $i++)
			{
				array_push($newparams, $this->parameters[$i]);
			}
			array_push($newparams, array($name, $value));
			for ($i = $index; $i < count($this->parameters); $i++)
			{
				array_push($newparams, $this->parameters[$i]);
			}
			$this->parameters = $newparams;
		}
		else
		{
			array_push($this->parameters, array($name, $value));
		}
	}
	
	/**
	 * Saves the learners input of the question to the database.
	 *
	 * @access public
	 * @param integer $active_id Active id of the user
	 * @param integer $pass Test pass
	 * @return boolean $status
	 */
	public function saveWorkingData($active_id, $pass = NULL)
	{
		// nothing to save!

		//$this->getProcessLocker()->requestUserSolutionUpdateLock();
		// store in tst_solutions
		//$this->getProcessLocker()->releaseUserSolutionUpdateLock();

		return true;
	}

	protected function savePreviewData(ilAssQuestionPreviewSession $previewSession)
	{
		// nothing to save!

		return true;
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
	
	/**
	 * Returns the question type of the question
	 *
	 * @return integer The question type of the question
	 */
	public function getQuestionType()
	{
		return "assLongMenu";
	}

	/**
	 * Returns the name of the additional question data table in the database
	 *
	 * @return string The additional table name
	 */
	public function getAdditionalTableName()
	{
		return 'qpl_qst_lomo';
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
		/*include_once ("./Services/Excel/classes/class.ilExcelUtils.php");
		$solutions = $this->getSolutionValues($active_id, $pass);
		$worksheet->writeString($startrow, 0, ilExcelUtils::_convert_text($this->lng->txt($this->getQuestionType())), $format_title);
		$worksheet->writeString($startrow, 1, ilExcelUtils::_convert_text($this->getTitle()), $format_title);
		$i = 1;
		foreach ($solutions as $solution)
		{
			$worksheet->write($startrow + $i, 1, ilExcelUtils::_convert_text($this->lng->txt("result") . " $i"));
			if (strlen($solution["value1"])) $worksheet->write($startrow + $i, 1, ilExcelUtils::_convert_text($solution["value1"]));
			if (strlen($solution["value2"])) $worksheet->write($startrow + $i, 2, ilExcelUtils::_convert_text($solution["value2"]));
			$i++;
		}
		return $startrow + $i + 1;*/
	}

	public function isAutosaveable()
	{
		return FALSE;
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
		$result = new ilUserQuestionResult($this, $active_id, $pass);

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
		return array();
	}

	/**
	 * Get all available operations for a specific question
	 * @param $expression
	 * @internal param string $expression_type
	 * @return array
	 */
	public function getOperators($expression)
	{
		// TODO: Implement getOperators() method.
	}

	/**
	 * Get all available expression types for a specific question
	 * @return array
	 */
	public function getExpressionTypes()
	{
		// TODO: Implement getExpressionTypes() method.
	}
	
	public function isShuffleAnswersEnabled()
	{
		return false;
	}
}