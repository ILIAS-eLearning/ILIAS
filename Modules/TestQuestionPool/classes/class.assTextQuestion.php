<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjAnswerScoringAdjustable.php';

/**
 * Class for text questions
 *
 * assTextQuestion is a class for text questions
 * 
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com> 
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 *          
 * @version		$Id$
 * 
 * @ingroup		ModulesTestQuestionPool
 */
class assTextQuestion extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable
{
	/**
	* Maximum number of characters of the answertext
	*
	* Maximum number of characters of the answertext
	*
	* @var integer
	*/
	var $maxNumOfChars;

	/**
	* Keywords of the question
	*
	* If every keyword in $keywords is found in the question answer,
	* the question will be scored automatically with the maximum points
	*
	* @var string
	*/
	var $keywords;

	var $answers;

	/**
	* The method which should be chosen for text comparisons
	*
	* @var string
	*/
	var $text_rating;

	/* method for automatic string matching */
	private $matchcondition;

	public $keyword_relation = 'any';

	/**
	 * assTextQuestion constructor
	 *
	 * The constructor takes possible arguments an creates an instance of the assTextQuestion object.
	 *
	 * @param string $title A title string to describe the question
	 * @param string $comment A comment string to describe the question
	 * @param string $author A string containing the name of the questions author
	 * @param integer $owner A numerical ID to identify the owner/creator
	 * @param string $question The question string of the text question
	 *                         
	 */
	public function __construct(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""
	)
	{
		parent::__construct($title, $comment, $author, $owner, $question);
		$this->maxNumOfChars = 0;
		$this->points = 1;
		$this->answers = array();
		$this->matchcondition = 0;
	}

	/**
	* Returns true, if a multiple choice question is complete for use
	*
	* @return boolean True, if the multiple choice question is complete for use, otherwise false
	*/
	public function isComplete()
	{
		if (strlen($this->title) 
			&& $this->author 
			&& $this->question 
			&& $this->getMaximumPoints() > 0
		)
		{
			return true;
		}
		return false;
	}

	/**
	 * Saves a assTextQuestion object to a database
	 *
	 * @param string $original_id
	 */
	public function saveToDb($original_id = "")
	{
		$this->saveQuestionDataToDb($original_id);
		$this->saveAdditionalQuestionDataToDb();
		$this->saveAnswerSpecificDataToDb();
		parent::saveToDb($original_id);
	}

	/**
	* Loads a assTextQuestion object from a database
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the text question in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT qpl_questions.*, " . $this->getAdditionalTableName() . ".* FROM qpl_questions LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = qpl_questions.question_id WHERE qpl_questions.question_id = %s",
			array("integer"),
			array($question_id)
		);
		if ($ilDB->numRows($result) == 1)
		{
			$data = $ilDB->fetchAssoc($result);
			$this->setId($question_id);
			$this->setObjId($data["obj_fi"]);
			$this->setTitle($data["title"]);
			$this->setComment($data["description"]);
			$this->setOriginalId($data["original_id"]);
			$this->setNrOfTries($data['nr_of_tries']);
			$this->setAuthor($data["author"]);
			$this->setPoints((float) $data["points"]);
			$this->setOwner($data["owner"]);
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
			$this->setShuffle($data["shuffle"]);
			$this->setMaxNumOfChars($data["maxnumofchars"]);
			$this->setTextRating($this->isValidTextRating($data["textgap_rating"]) ? $data["textgap_rating"] : TEXTGAP_RATING_CASEINSENSITIVE);
			$this->matchcondition = (strlen($data['matchcondition'])) ? $data['matchcondition'] : 0;
			$this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
			$this->setKeywordRelation(($data['keyword_relation']));
			
			try
			{
				$this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
			}
			catch(ilTestQuestionPoolException $e)
			{
			}
		}

		$result = $ilDB->queryF("SELECT * FROM qpl_a_essay WHERE question_fi = %s",
								array("integer"),
								array($this->getId())
		);
		
		$this->flushAnswers();
		while ($row = $ilDB->fetchAssoc($result))
		{
			$this->addAnswer($row['answertext'], $row['points']);
		}
		
		parent::loadFromDb($question_id);
	}

	/**
	* Duplicates an assTextQuestion
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
		#$clone->duplicateAnswers($this_id);
		
		$clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());

		return $clone->id;
	}

	/**
	* Copies an assTextQuestion object
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
		// duplicate answers
		#$clone->duplicateAnswers($original_id);
		
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
		// duplicate answers
		#$clone->duplicateAnswers($sourceQuestionId);

		$clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

		return $clone->id;
	}

	/**
	* Gets the maximum number of characters for the text solution
	*
	* @return integer The maximum number of characters for the text solution
	* @access public
	* @see $maxNumOfChars
	*/
	function getMaxNumOfChars()
	{
		if (strcmp($this->maxNumOfChars, "") == 0)
		{
			return 0;
		}
		else
		{
			return $this->maxNumOfChars;
		}
	}

	/**
	* Sets the maximum number of characters for the text solution
	*
	* @param integer $maxchars The maximum number of characters for the text solution
	* @access public
	* @see $maxNumOfChars
	*/
	function setMaxNumOfChars($maxchars = 0)
	{
		$this->maxNumOfChars = $maxchars;
	}

	/**
	* Returns the maximum points, a learner can reach answering the question
	*
	* @access public
	* @see $points
	*/
	function getMaximumPoints()
	{
		if( in_array($this->getKeywordRelation(), self::getScoringModesWithPointsByQuestion()) )
		{
			return parent::getPoints();
		}
		
		$points = 0;
		
		foreach ($this->answers as $answer)
		{
			if ($answer->getPoints() > 0)
			{
				$points = $points + $answer->getPoints();
			}
		}
		
		return $points;
	}
	
	function getMinimumPoints()
	{
		if( in_array($this->getKeywordRelation(), self::getScoringModesWithPointsByQuestion()) )
		{
			return 0;
		}
		
		$points = 0;

		foreach ($this->answers as $answer)
		{
			if ($answer->getPoints() < 0)
			{
				$points = $points + $answer->getPoints();
			}
		}

		return $points;
	}
	/**
	* Sets the points, a learner has reached answering the question
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @param integer $points The points the user has reached answering the question
	* @return boolean true on success, otherwise false
	* @access public
	*/
	function setReachedPoints($active_id, $points, $pass = NULL)
	{
		global $ilDB;
		
		if (($points > 0) && ($points <= $this->getPoints()))
		{
			if (is_null($pass))
			{
				$pass = $this->getSolutionMaxPass($active_id);
			}
			$affectedRows = $ilDB->manipulateF("UPDATE tst_test_result SET points = %s WHERE active_fi = %s AND question_fi = %s AND pass = %s",
				array('float','integer','integer','integer'),
				array($points, $active_id, $this->getId(), $pass)
			);
			$this->_updateTestPassResults($active_id, $pass);
			return TRUE;
		}
			else
		{
			return TRUE;
		}
	}
	
	private function isValidTextRating($textRating)
	{
		switch($textRating)
		{
			case TEXTGAP_RATING_CASEINSENSITIVE:
			case TEXTGAP_RATING_CASESENSITIVE:
			case TEXTGAP_RATING_LEVENSHTEIN1:
			case TEXTGAP_RATING_LEVENSHTEIN2:
			case TEXTGAP_RATING_LEVENSHTEIN3:
			case TEXTGAP_RATING_LEVENSHTEIN4:
			case TEXTGAP_RATING_LEVENSHTEIN5:
				return true;
		}
		
		return false;
	}

	/**
	* Checks if one of the keywords matches the answertext
	*
	* @param string $answertext The answertext of the user
	* @param string $a_keyword The keyword which should be checked
	* @return boolean TRUE if the keyword matches, FALSE otherwise
	* @access private
	*/
	function isKeywordMatching($answertext, $a_keyword)
	{
		$result = FALSE;
		$textrating = $this->getTextRating();
		include_once "./Services/Utilities/classes/class.ilStr.php";
		switch ($textrating)
		{
			case TEXTGAP_RATING_CASEINSENSITIVE:
				if (ilStr::strPos(ilStr::strToLower($answertext), ilStr::strToLower($a_keyword)) !== false) return TRUE;
				break;
			case TEXTGAP_RATING_CASESENSITIVE:
				if (ilStr::strPos($answertext, $a_keyword) !== false) return TRUE;
				break;
		}
		
		// "<p>red</p>" would not match "red" even with distance of 5
		$answertext = strip_tags($answertext);
		
		$answerwords = array();
		if (preg_match_all("/([^\s.]+)/", $answertext, $matches))
		{
			foreach ($matches[1] as $answerword)
			{
				array_push($answerwords, trim($answerword));
			}
		}
		foreach ($answerwords as $a_original)
		{
			switch ($textrating)
			{
				case TEXTGAP_RATING_LEVENSHTEIN1:
					if (levenshtein($a_original, $a_keyword) <= 1) return TRUE;
					break;
				case TEXTGAP_RATING_LEVENSHTEIN2:
					if (levenshtein($a_original, $a_keyword) <= 2) return TRUE;
					break;
				case TEXTGAP_RATING_LEVENSHTEIN3:
					if (levenshtein($a_original, $a_keyword) <= 3) return TRUE;
					break;
				case TEXTGAP_RATING_LEVENSHTEIN4:
					if (levenshtein($a_original, $a_keyword) <= 4) return TRUE;
					break;
				case TEXTGAP_RATING_LEVENSHTEIN5:
					if (levenshtein($a_original, $a_keyword) <= 5) return TRUE;
					break;
			}
		}
		return $result;
	}
	
	protected function calculateReachedPointsForSolution($solution)
	{
		// Return min points when keyword relation is NON KEYWORDS
		if( $this->getKeywordRelation() == 'non' )
		{
			return $this->getMinimumPoints();
		}

		// Return min points if there are no answers present.
		$answers = $this->getAnswers();

		if (count($answers) == 0)
		{
			return $this->getMinimumPoints();
		}
		
		switch( $this->getKeywordRelation() )
		{
			case 'any':

				$points = 0;

				foreach ($answers as $answer)
				{
					$qst_answer  = $answer->getAnswertext();
					$user_answer = '  '.$solution;

					if( $this->isKeywordMatching( $user_answer, $qst_answer ) )
					{
						$points += $answer->getPoints();
					}
				}

				break;

			case 'all':

				$points = $this->getMaximumPoints();

				foreach ($answers as $answer)
				{
					$qst_answer  = $answer->getAnswertext();
					$user_answer = '  '.$solution;

					if( !$this->isKeywordMatching( $user_answer, $qst_answer ) )
					{
						$points = 0;
						break;
					}
				}

				break;

			case 'one':

				$points = 0;

				foreach ($answers as $answer)
				{
					$qst_answer  = $answer->getAnswertext();
					$user_answer = '  '.$solution;

					if( $this->isKeywordMatching( $user_answer, $qst_answer ) )
					{
						$points = $this->getMaximumPoints();
						break;
					}
				}

				break;
		}

		return $points;
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
	public function calculateReachedPoints($active_id, $pass = NULL, $returndetails = FALSE)
	{
		if( $returndetails )
		{
			throw new ilTestException('return details not implemented for '.__METHOD__);
		}
		
		global $ilDB;

		$points = 0;
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}
		
		$result = $ilDB->queryF("SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			array('integer','integer','integer'),
			array($active_id, $this->getId(), $pass)
		);
		
		// Return min points when no answer was given.
		if ($ilDB->numRows($result) == 0)
		{
			return $this->getMinimumPoints();
		}
		
		// Return points of points are already on the row.
		$row = $ilDB->fetchAssoc($result);
		if ($row["points"] != NULL)
		{
			return $row["points"];
		}

		return $this->calculateReachedPointsForSolution($row['value1']);
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
		global $ilDB;
		global $ilUser;

		include_once "./Services/Utilities/classes/class.ilStr.php";
		if (is_null($pass))
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			$pass = ilObjTest::_getPass($active_id);
		}

		$this->getProcessLocker()->requestUserSolutionUpdateLock();

		$affectedRows = $ilDB->manipulateF("DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			array('integer','integer','integer'),
			array($active_id, $this->getId(), $pass)
		);
		
		$text = $this->getSolutionSubmit();
		
		$entered_values = 0;
		if (strlen($text))
		{
			$next_id = $ilDB->nextId('tst_solutions');
			$affectedRows = $ilDB->insert("tst_solutions", array(
				"solution_id" => array("integer", $next_id),
				"active_fi" => array("integer", $active_id),
				"question_fi" => array("integer", $this->getId()),
				"value1" => array("clob", trim($text)),
				"value2" => array("clob", null),
				"pass" => array("integer", $pass),
				"tstamp" => array("integer", time())
			));
			$entered_values++;
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

	/**
	 * @return mixed|string
	 */
	public function getSolutionSubmit()
	{
		$text = ilUtil::stripSlashes($_POST["TEXT"], FALSE);
		if($this->getMaxNumOfChars())
		{
			include_once "./Services/Utilities/classes/class.ilStr.php";
			$text_without_tags = preg_replace("/<[^>*?]>/is", "", $text);
			$len_with_tags = ilStr::strLen($text);
			$len_without_tags = ilStr::strLen($text_without_tags);
			if($this->getMaxNumOfChars() < $len_without_tags)
			{
				if(!$this->isHTML($text))
				{
					$text = ilStr::subStr($text, 0, $this->getMaxNumOfChars());
				}
			}
		}
		if($this->isHTML($text))
		{
			$text = preg_replace("/<[^>]*$/ims", "", $text);
			return $text;
		} else
		{
			//$text = htmlentities($text, ENT_QUOTES, "UTF-8");
		}
		return $text;
	}

	public function saveAdditionalQuestionDataToDb()
	{
		/** @var ilDB $ilDB */
		global $ilDB;
		$ilDB->manipulateF( "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
							array( "integer" ),
							array( $this->getId() 
							)
		);

		$ilDB->manipulateF( "INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, maxnumofchars, keywords, 
							 textgap_rating, matchcondition, keyword_relation) VALUES (%s, %s, %s, %s, %s, %s)",
							array( "integer", "integer", "text", "text", 'integer', 'text' ),
							array(
								$this->getId(),
								$this->getMaxNumOfChars(),
								NULL,
								$this->getTextRating(),
								$this->matchcondition,
								$this->getKeywordRelation()
							)
		);
	}

	public function saveAnswerSpecificDataToDb()
	{
		/** @var ilDB $ilDB */
		global $ilDB;

		$ilDB->manipulateF( "DELETE FROM qpl_a_essay WHERE question_fi = %s",
							array( "integer" ),
							array( $this->getId() )
		);

		foreach ($this->answers as $answer)
		{
			/** @var $answer ASS_AnswerMultipleResponseImage */
			$nextID = $ilDB->nextId( 'qpl_a_essay' );
			$ilDB->manipulateF( "INSERT INTO qpl_a_essay (answer_id, question_fi, answertext, points) VALUES (%s, %s, %s, %s)",
								array( "integer", "integer", "text", 'float' ),
								array(
									$nextID,
									$this->getId(),
									$answer->getAnswertext(),
									$answer->getPoints()
								)
			);
		}
	}

	/**
	 * Reworks the allready saved working data if neccessary
	 *
	 * @access protected
	 * @param integer $active_id
	 * @param integer $pass
	 * @param boolean $obligationsAnswered
	 */
	protected function reworkWorkingData($active_id, $pass, $obligationsAnswered)
	{
		// nothing to rework!
	}

	function createRandomSolution($test_id, $user_id)
	{
	}

	/**
	* Returns the question type of the question
	*
	* @return integer The question type of the question
	* @access public
	*/
	function getQuestionType()
	{
		return "assTextQuestion";
	}

	/**
	* Returns the rating option for text comparisons
	*
	* @return string The rating option for text comparisons
	* @see $text_rating
	* @access public
	*/
	function getTextRating()
	{
		return $this->text_rating;
	}
	
	/**
	* Sets the rating option for text comparisons
	*
	* @param string $a_textgap_rating The rating option for text comparisons
	* @see $textgap_rating
	* @access public
	*/
	function setTextRating($a_text_rating)
	{
		switch ($a_text_rating)
		{
			case TEXTGAP_RATING_CASEINSENSITIVE:
			case TEXTGAP_RATING_CASESENSITIVE:
			case TEXTGAP_RATING_LEVENSHTEIN1:
			case TEXTGAP_RATING_LEVENSHTEIN2:
			case TEXTGAP_RATING_LEVENSHTEIN3:
			case TEXTGAP_RATING_LEVENSHTEIN4:
			case TEXTGAP_RATING_LEVENSHTEIN5:
				$this->text_rating = $a_text_rating;
				break;
			default:
				$this->text_rating = TEXTGAP_RATING_CASEINSENSITIVE;
				break;
		}
	}
	
	/**
	* Returns the name of the additional question data table in the database
	*
	* @return string The additional table name
	* @access public
	*/
	function getAdditionalTableName()
	{
		return "qpl_qst_essay";
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
		include_once ("./Services/Excel/classes/class.ilExcelUtils.php");
		$solutions = $this->getSolutionValues($active_id, $pass);
		$worksheet->writeString($startrow, 0, ilExcelUtils::_convert_text($this->lng->txt($this->getQuestionType())), $format_title);
		$worksheet->writeString($startrow, 1, ilExcelUtils::_convert_text($this->getTitle()), $format_title);
		$i = 1;
		$worksheet->writeString($startrow + $i, 0, ilExcelUtils::_convert_text($this->lng->txt("result")), $format_bold);
		if (strlen($solutions[0]["value1"]))
		{
			$worksheet->write($startrow + $i, 1, ilExcelUtils::_convert_text($solutions[0]["value1"]));
		}
		$i++;
		return $startrow + $i + 1;
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
		$result['shuffle'] = (bool) $this->getShuffle();
		$result['maxlength'] = (int) $this->getMaxNumOfChars();
		return json_encode($result);
	}

	public function getAnswerCount()
	{
		return count($this->answers);
	}

	/**
	 * Adds a possible answer for a multiple choice question. A ASS_AnswerBinaryStateImage object will be
	 * created and assigned to the array $this->answers.
	 *
	 * @param string $answertext The answer text
	 * @param double $points The points for selecting the answer (even negative points can be used)
	 * @param boolean $state Defines the answer as correct (TRUE) or incorrect (FALSE)
	 * @param integer $order A possible display order of the answer
	 * @param double $points The points for not selecting the answer (even negative points can be used)
	 * @access public
	 * @see $answers
	 * @see ASS_AnswerBinaryStateImage
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

			// add answer
			$answer = new ASS_AnswerMultipleResponseImage($answertext, $points);
			$this->answers[] = $answer;
	}
	
	public function getAnswers()
	{
		return $this->answers; 
	}

	/**
	 * Returns an answer with a given index. The index of the first
	 * answer is 0, the index of the second answer is 1 and so on.
	 *
	 * @param integer $index A nonnegative index of the n-th answer
	 * @return object ASS_AnswerBinaryStateImage-Object containing the answer
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

	function getAnswerTableName()
	{
		return 'qpl_a_essay';
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

	public function setAnswers($answers)
	{
		if( isset($answers['answer']) )
		{
			$count = count($answers['answer']);
			$withPoints = true;
		}
		else
		{
			$count = count($answers);
			$withPoints = false;
		}
		
		$this->flushAnswers();
		
		for( $i = 0; $i < $count; $i++ )
		{
			if($withPoints)
			{
				$this->addAnswer($answers['answer'][$i], $answers['points'][$i]);
			}
			else
			{
				$this->addAnswer($answers[$i], 0);
			}
		}
	}

	public function duplicateAnswers($original_id)
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT * FROM qpl_a_essay WHERE question_fi = %s",
								array('integer'),
								array($original_id)
		);
		if ($result->numRows())
		{
			while ($row = $ilDB->fetchAssoc($result))
			{
				$next_id = $ilDB->nextId('qpl_a_essay');
				$affectedRows = $ilDB->manipulateF(
					"INSERT INTO qpl_a_essay (answer_id, question_fi, answertext, points) 
					 VALUES (%s, %s, %s, %s)",
					 array('integer','integer','text','float'),
					 array($next_id, $this->getId(), $row["answertext"], $row["points"])
				);
			}
		}		
	}

	public function getKeywordRelation()
	{
		return $this->keyword_relation;
	}

	/**
	 * This method implements a default behaviour. During the creation of a text question, the record which holds
	 * the keyword relation is not existing, so keyword_relation defaults to 'one'.
	 */
	public function setKeywordRelation($a_relation)
	{
		$this->keyword_relation = $a_relation;
	}
	
	public static function getValidScoringModes()
	{
		return array_merge(self::getScoringModesWithPointsByQuestion(), self::getScoringModesWithPointsByKeyword());
	}
	
	public static function getScoringModesWithPointsByQuestion()
	{
		return array('non', 'all', 'one');
	}
	
	public static function getScoringModesWithPointsByKeyword()
	{
		return array('any');
	}
	
	
	/**
	 * returns boolean wether the question
	 * is answered during test pass or not
	 * 
	 * (overwrites method in class assQuestion)
	 * 
	 * @param integer $active_id
	 * @param integer $pass
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
	 * @return boolean $obligationPossible
	 */
	public static function isObligationPossible($questionId)
	{
		return true;
	}
}