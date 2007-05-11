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
* Class for cloze tests
*
* ASS_ClozeText is a class for cloze tests using text or select gaps.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com> 
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assClozeTest extends assQuestion
{
	/**
	* The gaps of the cloze question
	*
	* $gaps is an array of the predefined gaps of the cloze question
	*
	* @var array
	*/
	var $gaps;

	/**
	* The start tag beginning a cloze gap
	*
	* The start tag is set to "*[" by default.
	*
	* @var string
	*/
	var $start_tag;

	/**
	* The end tag beginning a cloze gap
	*
	* The end tag is set to "]" by default.
	*
	* @var string
	*/
	var $end_tag;
	
	/**
	* The rating option for text gaps
	*
	* This could contain one of the following options:
	* - case insensitive text gaps
	* - case sensitive text gaps
	* - various levenshtein distances
	*
	* @var string
	*/
	var $textgap_rating;

	/**
	* The fixed text length for all text fields in the cloze question
	*
	* @var integer
	*/
	var $fixedTextLength;
	
	/**
	* assClozeTest constructor
	*
	* The constructor takes possible arguments an creates an instance of the assClozeTest object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $cloze_text The question string of the cloze test
	* @access public
	*/
	function assClozeTest(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$cloze_text = ""
	)
	{
		$this->start_tag = "[gap]";
		$this->end_tag = "[/gap]";
		$this->assQuestion($title, $comment, $author, $owner);
		$this->gaps = array();
		$this->setClozeText($cloze_text);
		$this->fixedTextLength = "";
	}

	/**
	* Returns true, if a cloze test is complete for use
	*
	* Returns true, if a cloze test is complete for use
	*
	* @return boolean True, if the cloze test is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->getClozeText()) and (count($this->gaps)) and ($this->getMaximumPoints() > 0))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Cleans cloze question text to remove attributes or tags from older ILIAS versions
	*
	* Cleans cloze question text to remove attributes or tags from older ILIAS versions
	*
	* @param string $text The cloze question text
	* @return string The cleaned cloze question text
	* @access public
	*/
	function cleanQuestiontext($text)
	{
		$text = preg_replace("/\[gap[^\]]*?\]/", "[gap]", $text);
		$text = preg_replace("/\<gap([^>]*?)\>/", "[gap]", $text);
		$text = str_replace("</gap>", "[/gap]", $text);
		return $text;
	}
	
	/**
	* Loads a assClozeTest object from a database
	*
	* Loads a assClozeTest object from a database
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the cloze test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilDB;
		$query = sprintf("SELECT qpl_questions.*, %s.* FROM qpl_questions, %s WHERE question_id = %s AND qpl_questions.question_id = %s.question_fi",
			$this->getAdditionalTableName(),
			$this->getAdditionalTableName(),
			$ilDB->quote($question_id),
			$this->getAdditionalTableName()
		);
		$result = $ilDB->query($query);
		if (strcmp(strtolower(get_class($result)), db_result) == 0) 
		{
			if ($result->numRows() == 1) 
			{
				$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->id = $question_id;
				$this->obj_id = $data->obj_fi;
				$this->title = $data->title;
				$this->comment = $data->comment;
				$this->solution_hint = $data->solution_hint;
				$this->original_id = $data->original_id;
				$this->author = $data->author;
				$this->points = $data->points;
				$this->owner = $data->owner;
				$this->question = $this->cleanQuestiontext($data->question_text);
				$this->setFixedTextLength($data->fixed_textlen);
				// replacement of old syntax with new syntax
				include_once("./Services/RTE/classes/class.ilRTE.php");
				$this->question = ilRTE::_replaceMediaObjectImageSrc($this->question, 1);
				$this->setTextgapRating($data->textgap_rating);
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}

			// open the cloze gaps with all answers
			include_once "./Modules/TestQuestionPool/classes/class.assAnswerCloze.php";
			include_once "./Modules/TestQuestionPool/classes/class.assClozeGap.php";
			$query = sprintf("SELECT * FROM qpl_answer_cloze WHERE question_fi = %s ORDER BY gap_id, aorder ASC",
				$ilDB->quote($question_id)
			);
			$result = $ilDB->query($query);
			if (strcmp(strtolower(get_class($result)), db_result) == 0) 
			{
				$this->gaps = array();
				while ($data = $result->fetchRow(DB_FETCHMODE_ASSOC)) 
				{
					switch ($data["cloze_type"])
					{
						case CLOZE_TEXT:
							if (!array_key_exists($data["gap_id"], $this->gaps))
							{
								$this->gaps[$data["gap_id"]] = new assClozeGap(CLOZE_TEXT);
							}
							$answer = new assAnswerCloze(
								$data["answertext"],
								$data["points"],
								$data["aorder"]
							);
							$this->gaps[$data["gap_id"]]->addItem($answer);
							break;
						case CLOZE_SELECT:
							if (!array_key_exists($data["gap_id"], $this->gaps))
							{
								$this->gaps[$data["gap_id"]] = new assClozeGap(CLOZE_SELECT);
								$this->gaps[$data["gap_id"]]->setShuffle($data["shuffle"]);
							}
							$answer = new assAnswerCloze(
								$data["answertext"],
								$data["points"],
								$data["aorder"]
								);
							$this->gaps[$data["gap_id"]]->addItem($answer);
							break;
						case CLOZE_NUMERIC:
							if (!array_key_exists($data["gap_id"], $this->gaps))
							{
								$this->gaps[$data["gap_id"]] = new assClozeGap(CLOZE_NUMERIC);
							}
							$answer = new assAnswerCloze(
								$data["answertext"],
								$data["points"],
								$data["aorder"]
							);
							$answer->setLowerBound($data["lowerlimit"]);
							$answer->setUpperBound($data["upperlimit"]);
							$this->gaps[$data["gap_id"]]->addItem($answer);
							break;
					}
				}
			}
		}
		parent::loadFromDb($question_id);
	}

	/**
	* Saves a assClozeTest object to a database
	*
	* Saves a assClozeTest object to a database (experimental)
	*
	* @param object $db A pear DB object
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		global $ilDB;

		include_once "./Services/Math/classes/class.EvalMath.php";
		$eval = new EvalMath();
		$eval->suppress_errors = TRUE;
		$complete = 0;
		if ($this->isComplete())
		{
			$complete = 1;
		}

		$estw_time = $this->getEstimatedWorkingTime();
		$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);
		$original_id = $original_id ? $ilDB->quote($original_id) : "NULL";

		// cleanup RTE images which are not inserted into the question text
		include_once("./Services/RTE/classes/class.ilRTE.php");
		if ($this->id == -1)
		{
			$now = getdate();
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, points, author, " .
				"owner, question_text, working_time, complete, created, original_id, TIMESTAMP) " .
				"VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($this->getQuestionTypeID() . ""),
				$ilDB->quote($this->obj_id . ""),
				$ilDB->quote($this->title . ""),
				$ilDB->quote($this->comment . ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($this->author . ""),
				$ilDB->quote($this->owner . ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->getClozeText(), 0)),
				$ilDB->quote($estw_time . ""),
				$ilDB->quote($complete . ""),
				$ilDB->quote($created . ""),
				$original_id
			);
			$result = $ilDB->query($query);
			if ($result == DB_OK)
			{
				$this->id = $ilDB->getLastInsertId();
				$query = sprintf("INSERT INTO %s (question_fi, textgap_rating, fixed_textlen) VALUES (%s, %s, %s)",
					$this->getAdditionalTableName(),
					$ilDB->quote($this->id . ""),
					$ilDB->quote($this->textgap_rating . ""),
					$this->getFixedTextLength() ? $ilDB->quote($this->getFixedTextLength()) : "NULL"
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
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, points = %s, author = %s, " .
				"question_text = %s, working_time = %s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title . ""),
				$ilDB->quote($this->comment . ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($this->author . ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->getClozeText(), 0)),
				$ilDB->quote($estw_time . ""),
				$ilDB->quote($complete . ""),
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE %s SET textgap_rating = %s, fixed_textlen = %s WHERE question_fi = %s",
				$this->getAdditionalTableName(),
				$ilDB->quote($this->textgap_rating . ""),
				$this->getFixedTextLength() ? $ilDB->quote($this->getFixedTextLength()) : "NULL",
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
		}

		if ($result == DB_OK)
		{
			// delete old answers
			$query = sprintf("DELETE FROM qpl_answer_cloze WHERE question_fi = %s",
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
			foreach ($this->gaps as $key => $gap)
			{
				foreach ($gap->getItems() as $item)
				{
					$query = "";
					switch ($gap->getType())
					{
						case CLOZE_TEXT:
							$query = sprintf("INSERT INTO qpl_answer_cloze (answer_id, question_fi, gap_id, answertext, points, aorder, cloze_type) VALUES (NULL, %s, %s, %s, %s, %s, %s)",
								$ilDB->quote($this->getId()),
								$ilDB->quote($key . ""),
								$ilDB->quote($item->getAnswertext() . ""),
								$ilDB->quote($item->getPoints() . ""),
								$ilDB->quote($item->getOrder() . ""),
								$ilDB->quote($gap->getType() . "")
							);
							break;
						case CLOZE_SELECT:
							$query = sprintf("INSERT INTO qpl_answer_cloze (answer_id, question_fi, gap_id, answertext, points, aorder, cloze_type, shuffle) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s)",
								$ilDB->quote($this->getId()),
								$ilDB->quote($key . ""),
								$ilDB->quote($item->getAnswertext() . ""),
								$ilDB->quote($item->getPoints() . ""),
								$ilDB->quote($item->getOrder() . ""),
								$ilDB->quote($gap->getType() . ""),
								$ilDB->quote($gap->getShuffle() ? "1" : "0")
							);
							break;
						case CLOZE_NUMERIC:
							$query = sprintf("INSERT INTO qpl_answer_cloze (answer_id, question_fi, gap_id, answertext, points, aorder, cloze_type, lowerlimit, upperlimit) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s)",
								$ilDB->quote($this->getId()),
								$ilDB->quote($key . ""),
								$ilDB->quote($item->getAnswertext() . ""),
								$ilDB->quote($item->getPoints() . ""),
								$ilDB->quote($item->getOrder() . ""),
								$ilDB->quote($gap->getType() . ""),
								$eval->e($item->getLowerBound() !== FALSE) ? $ilDB->quote($item->getLowerBound()) : "NULL",
								$eval->e($item->getUpperBound() !== FALSE) ? $ilDB->quote($item->getUpperBound()) : "NULL"
							);
							break;
					}
					if (strlen($query)) $answer_result = $ilDB->query($query);
				}
			}
		}
		parent::saveToDb($original_id);
	}

	/**
	* Returns the array of gaps
	*
	* Returns the array of gaps
	*
	* @return array Array containing the gap objects of the cloze question gaps
	* @access public
	*/
	function getGaps()
	{
		return $this->gaps;
	}


	/**
	* Deletes all gaps without changing the cloze text
	*
	* Deletes all gaps without changing the cloze text
	*
	* @access public
	* @see $gaps
	*/
	function flushGaps() 
	{
		$this->gaps = array();
	}

	/**
	* Sets the cloze text field, evaluates the gaps and creates the gap array from the data
	*
	* Evaluates the text gap solutions from the cloze text. A single or multiple text gap solutions
	* could be entered using the following syntax in the cloze text:
	* solution1 [, solution2, ..., solutionN] enclosed in the text gap selector gap[]
	*
	* @param string $cloze_text The cloze text with all gaps and gap gaps
	* @access public
	* @see $cloze_text
	*/
	function setClozeText($cloze_text = "")
	{
		$this->gaps = array();
		$cloze_text = $this->cleanQuestiontext($cloze_text);
		$this->question = $cloze_text;
		$this->createGapsFromQuestiontext();
	}
	
	/**
	* Returns the cloze text
	*
	* Returns the cloze text
	*
	* @return string The cloze text string
	* @access public
	* @see $cloze_text
	*/
	function getClozeText() 
	{
		return $this->question;
	}

	/**
	* Returns the start tag of a cloze gap
	*
	* Returns the start tag of a cloze gap
	*
	* @return string The start tag of a cloze gap
	* @access public
	* @see $start_tag
	*/
	function getStartTag() 
	{
		return $this->start_tag;
	}

	/**
	* Sets the start tag of a cloze gap
	*
	* Sets the start tag of a cloze gap
	*
	* @param string $start_tag The start tag for a cloze gap
	* @access public
	* @see $start_tag
	*/
	function setStartTag($start_tag = "[gap]") 
	{
		$this->start_tag = $start_tag;
	}
	
	/**
	* Returns the end tag of a cloze gap
	*
	* Returns the end tag of a cloze gap
	*
	* @return string The end tag of a cloze gap
	* @access public
	* @see $end_tag
	*/
	function getEndTag() 
	{
		return $this->end_tag;
	}

	/**
	* Sets the end tag of a cloze gap
	*
	* Sets the end tag of a cloze gap
	*
	* @param string $end_tag The end tag for a cloze gap
	* @access public
	* @see $end_tag
	*/
	function setEndTag($end_tag = "[/gap]") 
	{
		$this->end_tag = $end_tag;
	}

	function createGapsFromQuestiontext()
	{
		include_once "./Modules/TestQuestionPool/classes/class.assClozeGap.php";
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerCloze.php";
		$search_pattern = "|\[gap\](.*?)\[/gap\]|i";
		preg_match_all($search_pattern, $this->getClozeText(), $found);
		$this->gaps = array();
		if (count($found[0]))
		{
			foreach ($found[1] as $gap_index => $answers)
			{
				// create text gaps by default
				$gap = new assClozeGap(CLOZE_TEXT);
				$textparams = preg_split("/(?<!\\\\),/", $answers);
				foreach ($textparams as $key => $value)
				{
					$answer = new assAnswerCloze($value, 0, $key);
					$gap->addItem($answer);
				}
				$this->gaps[$gap_index] = $gap;
			}
		}
	}
	
	/**
	* Set the type of a gap with a given index
	*
	* Set the type of a gap with a given index
	*
	* @access private
	*/
	function setGapType($gap_index, $gap_type)
	{
		if (array_key_exists($gap_index, $this->gaps))
		{
			$this->gaps[$gap_index]->setType($gap_type);
		}
	}

	/**
	* Sets the shuffle state of a gap
	*
	* Sets the shuffle state of a gap with a given index. The index of the first
	* gap is 0, the index of the second gap is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th gap
	* @param integer $shuffle Turn shuffle on (=1) or off (=0)
	* @access public
	* @see $gaps
	*/
	function setGapShuffle($gap_index = 0, $shuffle = 1) 
	{
		if (array_key_exists($gap_index, $this->gaps))
		{
			$this->gaps[$gap_index]->setShuffle($shuffle);
		}
	}

	/**
	* Removes all answers from the gaps
	*
	* Removes all answers from the gaps
	*
	* @access public
	* @see $gaps
	*/
	function clearGapAnswers() 
	{
		foreach ($this->gaps as $gap_index => $gap)
		{
			$this->gaps[$gap_index]->clearItems();
		}
	}

	/**
	* Returns the number of gaps
	*
	* Returns the number of gaps
	*
	* @return integer The number of gaps
	* @access public
	* @see $gaps
	*/
	function getGapCount() 
	{
		if (is_array($this->gaps))
		{
			return count($this->gaps);
		}
		else
		{
			return 0;
		}
	}

	/**
	* Sets the answer text of a gap
	*
	* Sets the answer text of a gap with a given index. The index of the first
	* gap is 0, the index of the second gap is 1 and so on.
	*
	* @param integer $gap_index A nonnegative index of the n-th gap
	* @param integer $order The order of the answer text
	* @param string $answer The answer text
	* @access public
	* @see $gaps
	*/
	function addGapAnswer($gap_index, $order, $answer) 
	{
		if (array_key_exists($gap_index, $this->gaps))
		{
			if ($this->gaps[$gap_index]->getType() == CLOZE_NUMERIC)
			{
				// only allow notation with "." for real numbers
				$answer = str_replace(",", ".", $answer);
			}
			$this->gaps[$gap_index]->addItem(new assAnswerCloze($answer, 0, $order));
		}
	}
	
	/**
	* Returns the gap at a given index
	*
	* Returns the gap at a given index
	*
	* @param integer $gap_index A nonnegative index of the n-th gap
	* @return object The gap of the given index
	* @access public
	* @see $gaps
	*/
	function getGap($gap_index = 0) 
	{
		if (array_key_exists($gap_index, $this->gaps))
		{
			return $this->gaps[$gap_index];
		}
		else
		{
			return NULL;
		}
	}

	/**
	* Sets the points of a gap answer
	*
	* Sets the points of a gap with a given index and an answer with a given order. The index of the first
	* gap is 0, the index of the second gap is 1 and so on.
	*
	* @param integer $gap_index A nonnegative index of the n-th gap
	* @param integer $order The order of the answer text
	* @param string $answer The points of the answer
	* @access public
	* @see $gaps
	*/
	function setGapAnswerPoints($gap_index, $order, $points) 
	{
		if (array_key_exists($gap_index, $this->gaps))
		{
			$this->gaps[$gap_index]->setItemPoints($order, $points);
		}
	}

	/**
	* Adds a new answer text value to a text gap
	*
	* Adds a new answer text value to a text gap with a given index. The index of the first
	* gap is 0, the index of the second gap is 1 and so on.
	*
	* @param integer $gap_index A nonnegative index of the n-th gap
	* @access public
	* @see $gaps
	*/
	function addGapText($gap_index) 
	{
		if (array_key_exists($gap_index, $this->gaps))
		{
			include_once "./Modules/TestQuestionPool/classes/class.assAnswerCloze.php";
			$answer = new assAnswerCloze(
				"",
				0,
				$this->gaps[$gap_index]->getItemCount()
			);
			$this->gaps[$gap_index]->addItem($answer);
		}
	}
	
	/**
	* Adds a ClozeGap object at a given index
	*
	* Adds a ClozeGap object at a given index
	*
	* @param object $gap The gap object
	* @param integer $index A nonnegative index of the n-th gap
	* @access public
	* @see $gaps
	*/
	function addGapAtIndex($gap, $index)
	{
		$this->gaps[$index] = $gap;
	}

	/**
	* Sets the lower bound of a gap answer
	*
	* Sets the lower bound of a gap with a given index and an answer with a given order. The index of the first
	* gap is 0, the index of the second gap is 1 and so on.
	*
	* @param integer $gap_index A nonnegative index of the n-th gap
	* @param integer $order The order of the answer text
	* @param string $answer The lower bound of the answer
	* @access public
	* @see $gaps
	*/
	function setGapAnswerLowerBound($gap_index, $order, $bound) 
	{
		if (array_key_exists($gap_index, $this->gaps))
		{
			$this->gaps[$gap_index]->setItemLowerBound($order, $bound);
		}
	}

	/**
	* Sets the upper bound of a gap answer
	*
	* Sets the upper bound of a gap with a given index and an answer with a given order. The index of the first
	* gap is 0, the index of the second gap is 1 and so on.
	*
	* @param integer $gap_index A nonnegative index of the n-th gap
	* @param integer $order The order of the answer text
	* @param string $answer The upper bound of the answer
	* @access public
	* @see $gaps
	*/
	function setGapAnswerUpperBound($gap_index, $order, $bound) 
	{
		if (array_key_exists($gap_index, $this->gaps))
		{
			$this->gaps[$gap_index]->setItemUpperBound($order, $bound);
		}
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
		$points = 0;
		foreach ($this->gaps as $gap_index => $gap) 
		{
			if ($gap->getType() == CLOZE_TEXT) 
			{
				$gap_max_points = 0;
				foreach ($gap->getItems() as $item) 
				{
					if ($item->getPoints() > $gap_max_points)
					{
						$gap_max_points = $item->getPoints();
					}
				}
				$points += $gap_max_points;
			} 
			else if ($gap->getType() == CLOZE_SELECT)
			{
				$srpoints = 0;
				foreach ($gap->getItems() as $item) 
				{
					if ($item->getPoints() > $srpoints)
					{
						$srpoints = $item->getPoints();
					}
				}
				$points += $srpoints;
			}
			else if ($gap->getType() == CLOZE_NUMERIC)
			{
				$numpoints = 0;
				foreach ($gap->getItems() as $item)
				{
					if ($item->getPoints() > $numpoints)
					{
						$numpoints = $item->getPoints();
					}
				}
				$points += $numpoints;
			}
		}
		return $points;
	}

	/**
	* Duplicates an assClozeTest
	*
	* Duplicates an assClozeTest
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
		$clone->copyPageOfQuestion($original_id);
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($original_id);
		// duplicate the generic feedback
		$clone->duplicateFeedbackGeneric($original_id);

		return $clone->id;
	}

	/**
	* Copies an assClozeTest object
	*
	* Copies an assClozeTest object
	*
	* @access public
	*/
	function copyObject($target_questionpool, $title = "")
	{
		if ($this->getId() <= 0)
		{
			// The question has not been saved. It cannot be duplicated
			return;
		}
		$clone = $this;
		include_once ("./Modules/TestQuestionPool/classes/class.assQuestion.php");
		$original_id = assQuestion::_getOriginalId($this->getId());
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

		return $clone->getId();
	}

	/**
	* Updates the gap parameters in the cloze text from the form input
	*
	* Updates the gap parameters in the cloze text from the form input
	*
	* @access private
	*/
	function updateClozeTextFromGaps()
	{
		$output = $this->getClozeText();
		foreach ($this->getGaps() as $gap_index => $gap)
		{
			$answers = array();
			foreach ($gap->getItemsRaw() as $item)
			{
				array_push($answers, str_replace(",", "\\,", $item->getAnswerText()));
			}
			$output = preg_replace("/\[gap\].*?\[\/gap\]/", "[_gap]" . join(",", $answers) . "[/_gap]", $output, 1);
		}
		$output = str_replace("_gap]", "gap]", $output);
		$this->question = $output;
	}
	
	/**
	* Deletes the answer text of a given gap
	*
	* Deletes the answer text of a gap with a given index and an answer with a given order. The index of the first
	* gap is 0, the index of the second gap is 1 and so on.
	*
	* @param integer $gap_index A nonnegative index of the n-th gap
	* @param integer $answer_index The order of the answer text
	* @access public
	* @see $gaps
	*/
	function deleteAnswerText($gap_index, $answer_index) 
	{
		if (array_key_exists($gap_index, $this->gaps))
		{
			if ($this->gaps[$gap_index]->getItemCount() == 1)
			{
				// this is the last answer text => remove the gap
				$this->deleteGap($gap_index);
			}
			else
			{
				// remove the answer text
				$this->gaps[$gap_index]->deleteItem($answer_index);
				$this->updateClozeTextFromGaps();
			}
		}
	}

	/**
	* Deletes a gap
	*
	* Deletes a gap with a given index. The index of the first
	* gap is 0, the index of the second gap is 1 and so on.
	*
	* @param integer $gap_index A nonnegative index of the n-th gap
	* @access public
	* @see $gaps
	*/
	function deleteGap($gap_index) 
	{
		if (array_key_exists($gap_index, $this->gaps))
		{
			$output = $this->getClozeText();
			foreach ($this->getGaps() as $replace_gap_index => $gap)
			{
				$answers = array();
				foreach ($gap->getItemsRaw() as $item)
				{
					array_push($answers, str_replace(",", "\\,", $item->getAnswerText()));
				}
				if ($replace_gap_index == $gap_index)
				{
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", "", $output, 1);
				}
				else
				{
					$output = preg_replace("/\[gap\].*?\[\/gap\]/", "[_gap]" . join(",", $answers) . "[/_gap]", $output, 1);
				}
			}
			$output = str_replace("_gap]", "gap]", $output);
			$this->question = $output;
			unset($this->gaps[$gap_index]);
			$this->gaps = array_values($this->gaps);
		}
	}

	/**
	* Returns the points for a text gap
	*
	* Returns the points for a text gap and compares the given solution with
	* the entered solution using the text gap rating options.
	*
	* @param string $a_original The original (correct) text
	* @param string $a_entered The text entered by the user
	* @param integer $max_points The maximum number of points for the solution
	* @access public
	*/
	function getTextgapPoints($a_original, $a_entered, $max_points)
	{
		$result = 0;
		$gaprating = $this->getTextgapRating();
		switch ($gaprating)
		{
			case TEXTGAP_RATING_CASEINSENSITIVE:
				if (strcmp(strtolower(utf8_decode($a_original)), strtolower(utf8_decode($a_entered))) == 0) $result = $max_points;
				break;
			case TEXTGAP_RATING_CASESENSITIVE:
				if (strcmp(utf8_decode($a_original), utf8_decode($a_entered)) == 0) $result = $max_points;
				break;
			case TEXTGAP_RATING_LEVENSHTEIN1:
				if (levenshtein(utf8_decode($a_original), utf8_decode($a_entered)) <= 1) $result = $max_points;
				break;
			case TEXTGAP_RATING_LEVENSHTEIN2:
				if (levenshtein(utf8_decode($a_original), utf8_decode($a_entered)) <= 2) $result = $max_points;
				break;
			case TEXTGAP_RATING_LEVENSHTEIN3:
				if (levenshtein(utf8_decode($a_original), utf8_decode($a_entered)) <= 3) $result = $max_points;
				break;
			case TEXTGAP_RATING_LEVENSHTEIN4:
				if (levenshtein(utf8_decode($a_original), utf8_decode($a_entered)) <= 4) $result = $max_points;
				break;
			case TEXTGAP_RATING_LEVENSHTEIN5:
				if (levenshtein(utf8_decode($a_original), utf8_decode($a_entered)) <= 5) $result = $max_points;
				break;
		}
		return $result;
	}
	
	/**
	* Returns the points for a text gap
	*
	* Returns the points for a text gap and compares the given solution with
	* the entered solution using the text gap rating options.
	*
	* @param string $a_original The original (correct) text
	* @param string $a_entered The text entered by the user
	* @param integer $max_points The maximum number of points for the solution
	* @access public
	*/
	function getNumericgapPoints($a_original, $a_entered, $max_points, $lowerBound, $upperBound)
	{
		include_once "./Services/Math/classes/class.EvalMath.php";
		$eval = new EvalMath();
		$eval->suppress_errors = TRUE;
		$result = 0;
		if (($eval->e($lowerBound) !== FALSE) && ($eval->e($upperBound) !== FALSE))
		{
			if (($eval->e($a_entered) >= $eval->e($lowerBound)) && ($eval->e($a_entered) <= $eval->e($upperBound))) $result = $max_points;
		}
		else if ($eval->e($lowerBound) !== FALSE)
		{
			if (($eval->e($a_entered) >= $eval->e($lowerBound)) && ($eval->e($a_entered) <= $eval->e($a_original))) $result = $max_points;
		}
		else if ($eval->e($upperBound) !== FALSE)
		{
			if (($eval->e($a_entered) >= $eval->e($a_original)) && ($eval->e($a_entered) <= $eval->e($upperBound))) $result = $max_points;
		}
		else
		{
			if ($eval->e($a_entered) == $eval->e($a_original)) $result = $max_points;
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

		$found_value1 = array();
		$found_value2 = array();
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
		$user_result = array();
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) 
		{
			if (strcmp($data->value2, "") != 0)
			{
				$user_result[$data->value1] = array(
					"gap_id" => $data->value1,
					"value" => $data->value2
				);
			}
		}
		$points = 0;
		$counter = 0;
		foreach ($user_result as $gap_id => $value) 
		{
			if (array_key_exists($gap_id, $this->gaps))
			{
				switch ($this->gaps[$gap_id]->getType())
				{
					case CLOZE_TEXT:
						$gappoints = 0;
						for ($order = 0; $order < $this->gaps[$gap_id]->getItemCount(); $order++) 
						{
							$answer = $this->gaps[$gap_id]->getItem($order);
							$gotpoints = $this->getTextgapPoints($answer->getAnswertext(), $value["value"], $answer->getPoints());
							if ($gotpoints > $gappoints) $gappoints = $gotpoints;
						}
						$points += $gappoints;
						break;
					case CLOZE_NUMERIC:
						$gappoints = 0;
						for ($order = 0; $order < $this->gaps[$gap_id]->getItemCount(); $order++) 
						{
							$answer = $this->gaps[$gap_id]->getItem($order);
							$gotpoints = $this->getNumericgapPoints($answer->getAnswertext(), $value["value"], $answer->getPoints(), $answer->getLowerBound(), $answer->getUpperBound());
							if ($gotpoints > $gappoints) $gappoints = $gotpoints;
						}
						$points += $gappoints;
						break;
					case CLOZE_SELECT:
						if ($value["value"] >= 0)
						{
							for ($order = 0; $order < $this->gaps[$gap_id]->getItemCount(); $order++) 
							{
								$answer = $this->gaps[$gap_id]->getItem($order);
								if ($value["value"] == $answer->getOrder())
								{
									$points += $answer->getPoints();
								}
							}
						}
						break;
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
			$ilDB->quote($active_id),
			$ilDB->quote($this->getId()),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);

		$entered_values = 0;
		foreach ($_POST as $key => $value) 
		{
			if (preg_match("/^gap_(\d+)/", $key, $matches)) 
			{ 
				$value = ilUtil::stripSlashes($value);
				if (strlen($value))
				{
					$gap = $this->getGap($matches[1]);
					if (is_object($gap))
					{
						if (!(($gap->getType() == CLOZE_SELECT) && ($value == -1)))
						{
							if ($gap->getType() == CLOZE_NUMERIC)
							{
								$value = str_replace(",", ".", $value);
							}
							$query = sprintf("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
								$ilDB->quote($active_id),
								$ilDB->quote($this->getId()),
								$ilDB->quote(trim($matches[1])),
								$ilDB->quote(trim($value)),
								$ilDB->quote($pass . "")
							);
							$result = $ilDB->query($query);
							$entered_values++;
						}
					}
				}
			}
		}
		if ($entered_values)
		{
			include_once ("./classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
			}
		}
		else
		{
			include_once ("./classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_user_not_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
			}
		}
		parent::saveWorkingData($active_id, $pass);
		return true;
	}

	/**
	* Synchronizes the "original" of the question with the question data
	*
	* Synchronizes the "original" of the question with the question data
	*
	* @access public
	*/
	function syncWithOriginal()
	{
		global $ilDB;
		if ($this->original_id)
		{
			$complete = 0;
			if ($this->isComplete())
			{
				$complete = 1;
			}

			$estw_time = $this->getEstimatedWorkingTime();
			$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);

			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, points = %s, author = %s, question_text = %s, working_time = %s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title . ""),
				$ilDB->quote($this->comment . ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($this->author . ""),
				$ilDB->quote($this->getQuestion() . ""),
				$ilDB->quote($estw_time . ""),
				$ilDB->quote($complete . ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_cloze SET textgap_rating = %s, fixed_textlen = %s WHERE question_fi = %s",
				$ilDB->quote($this->textgap_rating . ""),
				$this->getFixedTextLength() ? $ilDB->quote($this->getFixedTextLength()) : "NULL",
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);

			if ($result == DB_OK)
			{
				// write answers
				// delete old answers
				$query = sprintf("DELETE FROM qpl_answer_cloze WHERE question_fi = %s",
					$ilDB->quote($this->original_id)
				);
				$result = $ilDB->query($query);
				foreach ($this->gaps as $key => $value)
				{
					foreach ($value as $answer_id => $answer_obj)
					{
						$query = sprintf("INSERT INTO qpl_answer_cloze (answer_id, question_fi, gap_id, answertext, points, aorder, cloze_type, name, shuffle, correctness) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
							$ilDB->quote($this->original_id . ""),
							$ilDB->quote($key . ""),
							$ilDB->quote($answer_obj->getAnswertext() . ""),
							$ilDB->quote($answer_obj->getPoints() . ""),
							$ilDB->quote($answer_obj->getOrder() . ""),
							$ilDB->quote($answer_obj->getClozeType() . ""),
							$ilDB->quote($answer_obj->getName() . ""),
							$ilDB->quote($answer_obj->getShuffle() . ""),
							$ilDB->quote($answer_obj->getState() . "")
						);
						$answer_result = $ilDB->query($query);
					}
				}
			}
			parent::syncWithOriginal();
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
		return "assClozeTest";
	}

	/**
	* Returns the rating option for text gaps
	*
	* Returns the rating option for text gaps
	*
	* @return string The rating option for text gaps
	* @see $textgap_rating
	* @access public
	*/
	function getTextgapRating()
	{
		return $this->textgap_rating;
	}

	/**
	* Sets the rating option for text gaps
	*
	* Sets the rating option for text gaps
	*
	* @param string $a_textgap_rating The rating option for text gaps
	* @see $textgap_rating
	* @access public
	*/
	function setTextgapRating($a_textgap_rating)
	{
		switch ($a_textgap_rating)
		{
			case TEXTGAP_RATING_CASEINSENSITIVE:
			case TEXTGAP_RATING_CASESENSITIVE:
			case TEXTGAP_RATING_LEVENSHTEIN1:
			case TEXTGAP_RATING_LEVENSHTEIN2:
			case TEXTGAP_RATING_LEVENSHTEIN3:
			case TEXTGAP_RATING_LEVENSHTEIN4:
			case TEXTGAP_RATING_LEVENSHTEIN5:
				$this->textgap_rating = $a_textgap_rating;
				break;
			default:
				$this->textgap_rating = TEXTGAP_RATING_CASEINSENSITIVE;
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
		return "qpl_question_cloze";
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
		return "qpl_answer_cloze";
	}
	
	/**
	* Sets a fixed text length for all text fields in the cloze question
	*
	* Sets a fixed text length for all text fields in the cloze question
	*
	* @param integer $a_text_len The text field length
	* @access public
	*/
	function setFixedTextLength($a_text_len)
	{
		$this->fixedTextLength = $a_text_len;
	}
	
	/**
	* Gets the fixed text length for all text fields in the cloze question
	*
	* Gets the fixed text length for all text fields in the cloze question
	*
	* @return integer The text field length
	* @access public
	*/
	function getFixedTextLength()
	{
		return $this->fixedTextLength;
	}

	/**
	* Returns TRUE if a given value is the best solution for a gap, FALSE otherwise
	*
	* Returns TRUE if a given value is the best solution for a gap, FALSE otherwise
	*
	* @param string $value The value which should be checked
	* @param integer $gap_index The index of the gap which should be tested
	* @return array "best" => TRUE if the given value is the best solution for a gap, "positive" => TRUE if the resulting points are greater 0, FALSE otherwise
	* @access public
	*/
	function testGapSolution($value, $gap_index)
	{
		if (strlen($value) == 0) return FALSE;
		if (!array_key_exists($gap_index, $this->gaps)) return FALSE;
		$max_points = 0;
		foreach ($this->gaps[$gap_index]->getItems() as $answer)
		{
			if ($answer->getPoints() > $max_points) $max_points = $answer->getPoints();
		}
		switch ($this->gaps[$gap_index]->getType())
		{
			case CLOZE_SELECT:
				$positive = FALSE;
				if ($this->gaps[$gap_index]->getItem($value)->getPoints() > 0)
				{
					$positive = TRUE;
				}
				if ($max_points == $this->gaps[$gap_index]->getItem($value)->getPoints())
				{
					return array("best" => TRUE, "positive" => $positive);
				}
				else
				{
					return array("best" => FALSE, "positive" => $positive);
				}
				break;
			case CLOZE_NUMERIC:
				$gappoints = 0;
				$max_points = 0;
				foreach ($this->gaps[$gap_index]->getItems() as $answer) 
				{
					$gotpoints = $this->getNumericgapPoints($answer->getAnswertext(), $value, $answer->getPoints(), $answer->getLowerBound(), $answer->getUpperBound());
					if ($gotpoints > $gappoints) $gappoints = $gotpoints;
					if ($answer->getPoints() > $max_points) $max_points = $answer->getPoints();
				}
				$positive = FALSE;
				if ($gappoints > 0)
				{
					$positive = TRUE;
				}
				if ($gappoints == $max_points)
				{
					return array("best" => TRUE, "positive" => $positive);
				}
				else
				{
					return array("best" => FALSE, "positive" => $positive);
				}
				break;
			case CLOZE_TEXT:
				$gappoints = 0;
				$max_points = 0;
				foreach ($this->gaps[$gap_index]->getItems() as $answer) 
				{
					$gotpoints = $this->getTextgapPoints($answer->getAnswertext(), $value, $answer->getPoints());
					if ($gotpoints > $gappoints) $gappoints = $gotpoints;
					if ($answer->getPoints() > $max_points) $max_points = $answer->getPoints();
				}
				$positive = FALSE;
				if ($gappoints > 0)
				{
					$positive = TRUE;
				}
				if ($gappoints == $max_points)
				{
					return array("best" => TRUE, "positive" => $positive);
				}
				else
				{
					return array("best" => FALSE, "positive" => $positive);
				}
				break;
		}
	}

	/**
	* Returns the maximum points for a gap
	*
	* Returns the maximum points for a gap
	*
	* @param integer $gap_index The index of the gap
	* @return double The maximum points for the gap
	* @access public
	* @see $points
	*/
	function getMaximumGapPoints($gap_index) 
	{
		$points = 0;
		if (array_key_exists($gap_index, $this->gaps))
		{
			$gap =& $this->gaps[$gap_index];
			foreach ($gap->getItems() as $answer) 
			{
				if ($answer->getPoints() > $gap_max_points)
				{
					$gap_max_points = $answer->getPoints();
				}
			}
			$points += $gap_max_points;
		}
		return $points;
	}

	/**
	* Collects all text in the question which could contain media objects
	* which were created with the Rich Text Editor
	*/
	function getRTETextWithMediaObjects()
	{
		return parent::getRTETextWithMediaObjects();
	}

//TODO point of changes
}
?>
