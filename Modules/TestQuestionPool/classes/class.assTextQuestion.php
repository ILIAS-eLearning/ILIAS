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
* Class for text questions
*
* assTextQuestion is a class for text questions
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assTextQuestion extends assQuestion
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

	/**
	* The method which should be chosen for text comparisons
	*
	* The method which should be chosen for text comparisons
	*
	* @var string
	*/
	var $text_rating;

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
	* @access public
	* @see assQuestion:assQuestion()
	*/
	function assTextQuestion(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""
	  )
	{
		$this->assQuestion($title, $comment, $author, $owner, $question);
		$this->maxNumOfChars = 0;
		$this->points = 0;
		$this->keywords = "";
	}

	/**
	* Returns true, if a multiple choice question is complete for use
	*
	* Returns true, if a multiple choice question is complete for use
	*
	* @return boolean True, if the multiple choice question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
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
	* Saves a assTextQuestion object to a database
	*
	* Saves a assTextQuestion object to a database
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

		if ($original_id)
		{
			$original_id = $ilDB->quote($original_id);
		}
		else
		{
			$original_id = "NULL";
		}

		// cleanup RTE images which are not inserted into the question text
		include_once("./Services/RTE/classes/class.ilRTE.php");
		if ($this->id == -1)
		{
			// Neuen Datensatz schreiben
			$now = getdate();
			$question_type = $this->getQuestionTypeID();
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, points, question_text, working_time, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($question_type),
				$ilDB->quote($this->obj_id),
				$ilDB->quote($this->title),
				$ilDB->quote($this->comment),
				$ilDB->quote($this->author),
				$ilDB->quote($this->owner),
				$ilDB->quote($this->getPoints() . ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->question, 0)),
				$ilDB->quote($estw_time),
				$ilDB->quote("$complete"),
				$ilDB->quote($created),
				$original_id
			);
			$result = $ilDB->query($query);
			
			if (PEAR::isError($result)) 
			{
				global $ilias;
				$ilias->raiseError($result->getMessage());
			}
			else
			{
				$this->id = $ilDB->getLastInsertId();
				$query = sprintf("INSERT INTO qpl_question_essay (question_fi, maxNumOfChars, keywords, textgap_rating) VALUES (%s, %s, %s, %s)",
					$ilDB->quote($this->id . ""),
					$ilDB->quote($this->getMaxNumOfChars()),
					$ilDB->quote($this->getKeywords() . ""),
					$ilDB->quote($this->getTextRating() . "")
				);
				$ilDB->query($query);

				// create page object of question
				$this->createPageObject();

				if ($this->getTestId() > 0)
				{
					$this->insertIntoTest($this->getTestId());
				}
			}
		}
		else
		{
			// Vorhandenen Datensatz aktualisieren
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, points = %s, question_text = %s, working_time=%s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title),
				$ilDB->quote($this->comment),
				$ilDB->quote($this->author),
				$ilDB->quote($this->getPoints() . ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->question, 0)),
				$ilDB->quote($estw_time),
				$ilDB->quote("$complete"),
				$ilDB->quote($this->id)
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_essay SET maxNumOfChars = %s, keywords = %s, textgap_rating = %s WHERE question_fi = %s",
				$ilDB->quote($this->getMaxNumOfChars()),
				$ilDB->quote($this->getKeywords() . ""),
				$ilDB->quote($this->getTextRating() . ""),
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a assTextQuestion object from a database
	*
	* Loads a assTextQuestion object from a database
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the text question in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilDB;

    $query = sprintf("SELECT qpl_questions.*, qpl_question_essay.* FROM qpl_questions, qpl_question_essay WHERE question_id = %s AND qpl_questions.question_id = qpl_question_essay.question_fi",
			$ilDB->quote($question_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$data = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
			$this->id = $question_id;
			$this->title = $data->title;
			$this->comment = $data->comment;
			$this->solution_hint = $data->solution_hint;
			$this->original_id = $data->original_id;
			$this->obj_id = $data->obj_fi;
			$this->author = $data->author;
			$this->owner = $data->owner;
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->question = ilRTE::_replaceMediaObjectImageSrc($data->question_text, 1);
			$this->maxNumOfChars = $data->maxNumOfChars;
			$this->keywords = $data->keywords;
			$this->text_rating = $data->textgap_rating;
			$this->points = $data->points;
			$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
		}
		parent::loadFromDb($question_id);
	}

	/**
	* Duplicates an assTextQuestion
	*
	* Duplicates an assTextQuestion
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
		$clone->onDuplicate($this_id);

		return $clone->id;
	}

	/**
	* Copies an assTextQuestion object
	*
	* Copies an assTextQuestion object
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
		$clone->onCopy($this->getObjId(), $this->getId());

		return $clone->id;
	}

	/**
	* Gets the maximum number of characters for the text solution
	*
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
	* Sets the points, a learner has reached answering the question
	*
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
			$query = sprintf("UPDATE tst_test_result SET points = %s WHERE active_fi = %s AND question_fi = %s AND pass = %s",
				$ilDB->quote($points . ""),
				$ilDB->quote($active_id . ""),
				$ilDB->quote($this->getId() . ""),
				$ilDB->quote($pass . "")
			);
			$result = $ilDB->query($query);
			$this->_updateTestPassResults($active_id, $pass);
			return TRUE;
		}
			else
		{
			return TRUE;
		}
	}

	/**
	* Checks if one of the keywords matches the answertext
	*
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
				if (ilStr::strPos(utf8_decode($answertext), $a_keyword) !== false) return TRUE;
				break;
		}
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
					if (levenshtein(utf8_decode($a_original), utf8_decode($a_keyword)) <= 1) return TRUE;
					break;
				case TEXTGAP_RATING_LEVENSHTEIN2:
					if (levenshtein(utf8_decode($a_original), utf8_decode($a_keyword)) <= 2) return TRUE;
					break;
				case TEXTGAP_RATING_LEVENSHTEIN3:
					if (levenshtein(utf8_decode($a_original), utf8_decode($a_keyword)) <= 3) return TRUE;
					break;
				case TEXTGAP_RATING_LEVENSHTEIN4:
					if (levenshtein(utf8_decode($a_original), utf8_decode($a_keyword)) <= 4) return TRUE;
					break;
				case TEXTGAP_RATING_LEVENSHTEIN5:
					if (levenshtein(utf8_decode($a_original), utf8_decode($a_keyword)) <= 5) return TRUE;
					break;
			}
		}
		return $result;
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
	function calculateReachedPoints($active_id, $pass = NULL)
	{
		global $ilDB;

		$points = 0;
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($active_id);
		}
		$query = sprintf("SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			if ($row["points"])
			{
				$points = $row["points"];
			}
			else
			{
				$keywords =& $this->getKeywordList();
				if (count($keywords))
				{
					$foundkeyword = false;
					foreach ($keywords as $keyword)
					{
						if (!$foundkeyword)
						{
							if ($this->isKeywordMatching($row["value1"], $keyword)) 
							{
								$foundkeyword = true;
							}
						}
					}
					if ($foundkeyword) $points = $this->getMaximumPoints();
				}
			}
		}

		$points = parent::calculateReachedPoints($active_id, $pass = NULL, $points);
		return $points;
	}

	/**
	* Saves the learners input of the question to the database
	*
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
		$query = sprintf("DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		$text = ilUtil::stripSlashes($_POST["TEXT"], FALSE);
		if ($this->getMaxNumOfChars())
		{
			include_once "./Services/Utilities/classes/class.ilStr.php";
			$text_without_tags = preg_replace("/<[^>*?]>/is", "", $text);
			$len_with_tags = ilStr::strLen($text);
			$len_without_tags = ilStr::strLen($text_without_tags);
			if ($this->getMaxNumOfChars() < $len_without_tags)
			{
				if (!$this->isHTML($text))
				{
					$text = ilStr::subStr($text, 0, $this->getMaxNumOfChars()); 
				}
				else
				{
					// this often produces bad XHTML which leads to problems with PDF export
					// a cleanup must be made 
					// $text = ilStr::subStr($text, 0, $this->getMaxNumOfChars() + ($len_with_tags - $len_without_tags)); 
				}
			}
		}
		if ($this->isHTML($text))
		{
			$text = preg_replace("/<[^>]*$/ims", "", $text);
		}
		else
		{
			$text = htmlentities($text, ENT_QUOTES, "UTF-8");
		}
		$entered_values = 0;
		if (strlen($text))
		{
			$query = sprintf("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL, %s, NULL)",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($this->getId() . ""),
				$ilDB->quote(trim($text) . ""),
				$ilDB->quote($pass . "")
			);
			$result = $ilDB->query($query);
			$entered_values++;
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

	function createRandomSolution($test_id, $user_id)
	{
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
		return "assTextQuestion";
	}
	
	/**
	* Returns the keywords of the question
	*
	* Returns the keywords of the question
	*
	* @return string The keywords of the question
	* @access public
	*/
	function getKeywords()
	{
		return $this->keywords;
	}
	
	/**
	* Sets the keywords of the question
	*
	* Sets the keywords of the question
	*
	* @param string $a_keywords The keywords of the question
	* @access public
	*/
	function setKeywords($a_keywords)
	{
		$this->keywords = $a_keywords;
	}
	
	/**
	* Returns the keywords of the question in an array
	*
	* Returns the keywords of the question in an array
	*
	* @return array The keywords of the question
	* @access public
	*/
	function &getKeywordList()
	{
		$keywords = array();
		if (preg_match_all("/([^\s]+)/", $this->keywords, $matches))
		{
			foreach ($matches[1] as $keyword)
			{
				array_push($keywords, trim($keyword));
			}
		}
		return $keywords;
	}

	/**
	* Returns the rating option for text comparisons
	*
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
	* Returns the name of the additional question data table in the database
	*
	* @return string The additional table name
	* @access public
	*/
	function getAdditionalTableName()
	{
		return "qpl_question_essay";
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
		include_once ("./classes/class.ilExcelUtils.php");
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
}

?>
