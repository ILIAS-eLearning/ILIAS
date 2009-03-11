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
* Class for matching questions
*
* assMatchingQuestion is a class for matching questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assMatchingQuestion extends assQuestion
{
	/**
	* The possible matching pairs of the matching question
	*
	* $matchingpairs is an array of the predefined matching pairs of the matching question
	*
	* @var array
	*/
	var $matchingpairs;

	/**
	* Type of matching question
	*
	* There are two possible types of matching questions: Matching terms and definitions (=1)
	* and Matching terms and pictures (=0).
	*
	* @var integer
	*/
	var $matching_type;
	
	/**
	* The terms of the matching question
	*
	* @var array
	*/
	var $terms;
	
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
	* assMatchingQuestion constructor
	*
	* The constructor takes possible arguments an creates an instance of the assMatchingQuestion object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the matching question
	* @access public
	*/
	function __construct(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$matching_type = MT_TERMS_DEFINITIONS
	)
	{
		parent::__construct($title, $comment, $author, $owner, $question);
		$this->matchingpairs = array();
		$this->matching_type = $matching_type;
		$this->terms = array();
	}

	/**
	* Returns true, if a matching question is complete for use
	*
	* @return boolean True, if the matching question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and (count($this->matchingpairs)) and ($this->getMaximumPoints() > 0))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Saves a assMatchingQuestion object to a database
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
					$next_id
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
		$affectedRows = $ilDB->manipulateF("INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, shuffle, matching_type, thumb_geometry, element_height) VALUES (%s, %s, %s, %s, %s)", 
			array("integer", "text", "text","integer","integer"),
			array(
				$this->getId(),
				$this->shuffle,
				$this->matching_type,
				$this->getThumbGeometry(),
				($this->getElementHeight() > 20) ? $this->getElementHeight() : NULL
			)
		);

		// delete old terms
		$affectedRows = $ilDB->manipulateF("DELETE FROM qpl_answer_matching_term WHERE question_fi = %s",
			array('integer'),
			array($this->getId())
		);
		
		// write terms
		$newterms = array();
		$matchingpairs = $this->getMatchingPairs();
		foreach ($this->terms as $key => $value)
		{
			$next_id = $ilDB->nextId('qpl_answer_matching_term');
			$affectedRows = $ilDB->manipulateF("INSERT INTO qpl_answer_matching_term (term_id, question_fi, term) VALUES (%s, %s, %s)",
				array('integer','integer','text'),
				array($next_id, $this->getId(), $value)
			);
			$newTermID = $next_id;
			$newterms[$newTermID] = $value;
		}

		// alte Antworten löschen
		$affectedRows = $ilDB->manipulateF("DELETE FROM qpl_answer_matching WHERE question_fi = %s",
			array('integer'),
			array($this->getId())
		);

		// Anworten wegschreiben
		foreach ($matchingpairs as $key => $value)
		{
			$matching_obj = $matchingpairs[$key];
			$term = $this->terms[$matching_obj->getTermId()];
			$termindex = array_search($term, $newterms);
			$next_id = $ilDB->nextId('qpl_answer_matching');
			$affectedRows = $ilDB->manipulateF("INSERT INTO qpl_answer_matching (answer_id, question_fi, points, term_fi, matchingtext, matching_order, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s)",
				array('integer','integer','float','integer','text','integer','integer'),
				array(
					$next_id,
					$this->getId(),
					$matching_obj->getPoints(),
					$termindex,
					$matching_obj->getDefinition(),
					$matching_obj->getDefinitionId(),
					time()
				)
			);
		}
		
		if ($this->getMatchingType() == MT_TERMS_PICTURES)
		{
			if ($this->getMatchingPairCount())
			{
				if (@file_exists($this->getImagePath() . $this->getMatchingPair(0)->getPicture()  . ".thumb.jpg"))
				{
					$size = getimagesize($this->getImagePath() . $this->getMatchingPair(0)->getPicture()  . ".thumb.jpg");
					$max = ($size[0] > $size[1]) ? $size[0] : $size[1];
					if ($this->getThumbGeometry() != $max)
					{
						$this->rebuildThumbnails();
					}
				}
			}
		}
		
		
		parent::saveToDb($original_id);
	}

	/**
	* Loads a assMatchingQuestion object from a database
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT qpl_questions.*, " . $this->getAdditionalTableName() . ".* FROM qpl_questions, " . $this->getAdditionalTableName() . " WHERE question_id = %s AND qpl_questions.question_id = " . $this->getAdditionalTableName() . ".question_fi",
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
			$this->setMatchingType($data["matching_type"]);
			$this->setThumbGeometry($data["thumb_geometry"]);
			$this->setElementHeight($data["element_height"]);
			$this->setShuffle($data["shuffle"]);
			$this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
		}

		$result = $ilDB->queryF("SELECT * FROM qpl_answer_matching_term WHERE question_fi = %s ORDER BY term ASC",
			array('integer'),
			array($question_id)
		);
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatching.php";
		$this->terms = array();
		if ($result->numRows() > 0)
		{
			while ($data = $ilDB->fetchAssoc($result))
			{
				array_push($this->terms, $data["term"]);
			}
		}

		$result = $ilDB->queryF("SELECT qpl_answer_matching.*, qpl_answer_matching_term.term FROM qpl_answer_matching, qpl_answer_matching_term WHERE qpl_answer_matching.question_fi = %s AND qpl_answer_matching_term.term_id = qpl_answer_matching.term_fi ORDER BY answer_id ASC",
			array('integer'),
			array($question_id)
		);
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatching.php";
		if ($result->numRows() > 0)
		{
			while ($data = $ilDB->fetchAssoc($result))
			{
				$index = array_search($data->term, $this->getTerms());
				array_push($this->matchingpairs, new ASS_AnswerMatching($data["points"], $index, $data["matchingtext"], $data["matching_order"]));
			}
		}

		parent::loadFromDb($question_id);
	}

	
	/**
	* Duplicates an assMatchingQuestion
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
	* Copies an assMatchingQuestion
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
		if ($title)
		{
			$clone->setTitle($title);
		}
		$source_questionpool = $this->getObjId();
		$clone->setObjId($target_questionpool);
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
		if ($this->get_matching_type() == MT_TERMS_PICTURES)
		{
			$imagepath = $this->getImagePath();
			$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			foreach ($this->matchingpairs as $answer)
			{
				$filename = $answer->getPicture();
				$sourcefilename = $imagepath_original . $filename;
				if (!copy($sourcefilename, $imagepath . $filename))
				{
					print "image could not be duplicated!!!! ";
				}
				if (!copy($sourcefilename . ".thumb.jpg", $imagepath . $filename . ".thumb.jpg"))
				{
					print "image thumbnail could not be duplicated!!!! ";
				}
			}
		}
	}

	function copyImages($question_id, $source_questionpool)
	{
		if ($this->get_matching_type() == MT_TERMS_PICTURES)
		{
			$imagepath = $this->getImagePath();
			$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
			$imagepath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $imagepath_original);
			if (!file_exists($imagepath))
			{
				ilUtil::makeDirParents($imagepath);
			}
			foreach ($this->matchingpairs as $answer)
			{
				$filename = $answer->getPicture();
				$sourcefilename = $imagepath_original . $filename;
				if (!@copy($sourcefilename, $imagepath . $filename))
				{
					print "image could not be duplicated!!!! ";
				}
				if (!@copy($sourcefilename . ".thumb.jpg", $imagepath . $filename . ".thumb.jpg"))
				{
					print "image thumbnail could not be duplicated!!!! ";
				}
			}
		}
	}

	/**
	* Sets the matching question type
	*
	* @param integer $matching_type The question matching type
	* @access public
	* @see $matching_type
	*/
	function setMatchingType($matching_type = MT_TERMS_DEFINITIONS)
	{
		$this->matching_type = $matching_type;
	}

	/**
	* Returns the matching question type
	*
	* @return integer The matching question type
	* @access public
	* @see $matching_type
	*/
	function get_matching_type()
	{
		return $this->matching_type;
	}

	function getMatchingType()
	{
		return $this->matching_type;
	}

	/**
	* Inserts a matching pair for an matching choice question. The students have to fill in an order for the matching pair.
	* The matching pair is an ASS_AnswerMatching object that will be created and assigned to the array $this->matchingpairs.
	*
	* @param string $answertext The answer text
	* @param string $matchingtext The matching text of the answer text
	* @param double $points The points for selecting the matching pair (even negative points can be used)
	* @param integer $order A possible display order of the matching pair
	* @access public
	* @see $matchingpairs
	* @see ASS_AnswerMatching
	*/
	function insertMatchingPair(
		$position,
		$picture_or_definition = "",
		$points = 0.0,
		$term_id = 0,
		$picture_or_definition_id = 0
	)
	{
		if ($picture_or_definition_id == 0)
		{
			$picture_or_definition_id = $this->get_random_id();
		}
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatching.php";
		$matchingpair = new ASS_AnswerMatching($points, $term_id, $picture_or_definition, $picture_or_definition_id);
		if ($position < count($this->matchingpairs))
		{
			$part1 = array_slice($this->matchingpairs, 0, $position);
			$part2 = array_slice($this->matchingpairs, $position);
			$this->matchingpairs = array_merge($part1, array($matchingpair), $part2);
		}
		else
		{
			array_push($this->matchingpairs, $matchingpair);
		}
	}
	/**
	* Adds an matching pair for an matching choice question. The students have to fill in an order for the matching pair.
	* The matching pair is an ASS_AnswerMatching object that will be created and assigned to the array $this->matchingpairs.
	*
	* @param string $answertext The answer text
	* @param string $matchingtext The matching text of the answer text
	* @param double $points The points for selecting the matching pair (even negative points can be used)
	* @param integer $order A possible display order of the matching pair
	* @access public
	* @see $matchingpairs
	* @see ASS_AnswerMatching
	*/
	function addMatchingPair(
		$picture_or_definition = "",
		$points = 0.0,
		$term_id = 0,
		$picture_or_definition_id = 0
	)
	{
		if ($picture_or_definition_id == 0)
		{
			$picture_or_definition_id = $this->get_random_id();
		}
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatching.php";
		$matchingpair = new ASS_AnswerMatching($points, $term_id, $picture_or_definition, $picture_or_definition_id);
		array_push($this->matchingpairs, $matchingpair);
	}

	/**
	* Returns a matching pair with a given index. The index of the first
	* matching pair is 0, the index of the second matching pair is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th matching pair
	* @return object ASS_AnswerMatching-Object
	* @access public
	* @see $matchingpairs
	*/
	function get_matchingpair($index = 0)
	{
		return $this->getMatchingPair($index);
	}
	
	/**
	* Returns a matching pair with a given index. The index of the first
	* matching pair is 0, the index of the second matching pair is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th matching pair
	* @return object ASS_AnswerMatching-Object
	* @access public
	* @see $matchingpairs
	*/
	function getMatchingPair($index = 0)
	{
		if ($index < 0)
		{
			return NULL;
		}
		if (count($this->matchingpairs) < 1)
		{
			return NULL;
		}
		if ($index >= count($this->matchingpairs))
		{
			return NULL;
		}
		return $this->matchingpairs[$index];
	}

	/**
	* Deletes a matching pair with a given index. The index of the first
	* matching pair is 0, the index of the second matching pair is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th matching pair
	* @access public
	* @see $matchingpairs
	*/
	function deleteMatchingPair($index = 0)
	{
		if ($index < 0)
		{
			return;
		}
		if (count($this->matchingpairs) < 1)
		{
			return;
		}
		if ($index >= count($this->matchingpairs))
		{
			return;
		}
		unset($this->matchingpairs[$index]);
		$this->matchingpairs = array_values($this->matchingpairs);
	}

	/**
	* Deletes all matching pairs
	*
	* @access public
	* @see $matchingpairs
	*/
	function flush_matchingpairs()
	{
		$this->matchingpairs = array();
	}

	/**
	* Returns the number of matching pairs
	*
	* @return integer The number of matching pairs of the matching question
	* @access public
	* @see $matchingpairs
	*/
	function getMatchingPairCount()
	{
		return count($this->matchingpairs);
	}

	/**
	* Returns the terms of the matching question
	*
	* @return array An array containing the terms (sorted alphabetically)
	* @access public
	* @see $terms
	*/
	function getTerms()
	{
		return $this->terms;
	}
	
	/**
	* Returns a term with a given ID
	*
	* @param string $id The id of the term
	* @return string The term
	* @access public
	* @see $terms
	*/
	function getTermWithID($id)
	{
		return $this->terms[$id];
	}
	
	/**
	* Returns the number of terms
	*
	* @return integer The number of terms
	* @access public
	* @see $terms
	*/
	function getTermCount()
	{
		return count($this->terms);
	}
	
	/**
	* Adds a term
	*
	* @param string $term The text of the term
	* @access public
	* @see $terms
	*/
	function addTerm($term)
	{
		array_push($this->terms, $term);
	}
	
	/**
	* Inserts a term
	*
	* @param string $term The text of the term
	* @access public
	* @see $terms
	*/
	function insertTerm($position, $term)
	{
		if ($position < count($this->terms))
		{
			$part1 = array_slice($this->terms, 0, $position);
			$part2 = array_slice($this->terms, $position);
			$this->terms = array_merge($part1, array($term), $part2);
			foreach ($this->matchingpairs as $index => $pair)
			{
				if ($pair->getTermId() >= $position) $this->matchingpairs[$index]->setTermId($pair->getTermId()+1);
			}
		}
		else
		{
			array_push($this->terms, $term);
		}
	}
	
	/**
	* Deletes all terms
	*
	* @access public
	* @see $terms
	*/
	function flushTerms()
	{
		$this->terms = array();
	}

	/**
	* Deletes a term
	*
	* @param string $term_id The id of the term to delete
	* @access public
	* @see $terms
	*/
	function deleteTerm($position)
	{
		unset($this->terms[$position]);
		$this->terms = array_values($this->terms);
		foreach ($this->matchingpairs as $index => $pair)
		{
			if ($pair->getTermId() >= $position) $this->matchingpairs[$index]->setTermId($pair->getTermId()-1);
		}
	}

	/**
	* Sets a specific term
	*
	* @param string $term The text of the term
	* @param string $index The index of the term
	* @access public
	* @see $terms
	*/
	function setTerm($term, $index)
	{
		$this->terms[$index] = $term;
		ksort($this->terms);
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
		while ($data = $ilDB->fetchAssoc($result))
		{
			if (strcmp($data["value1"], "") != 0)
			{
				array_push($found_value1, $data["value1"]);
				array_push($found_value2, $data["value2"]);
			}
		}
		$points = 0;
		foreach ($found_value2 as $key => $value)
		{
			foreach ($this->matchingpairs as $answer_key => $answer_value)
			{
				if (($answer_value->getDefinitionId() == $value) and ($answer_value->getTermId() == $found_value1[$key]))
				{
					$points += $answer_value->getPoints();
				}
			}
		}

		$points = parent::calculateReachedPoints($active_id, $pass = NULL, $points);
		return $points;
	}

	/**
	* Returns the maximum points, a learner can reach answering the question
	*
	* @access public
	* @see $points
	*/
	function getMaximumPoints()
	{
		$points = 0;
		foreach ($this->matchingpairs as $key => $value)
		{
			if ($value->getPoints() > 0)
			{
				$points += $value->getPoints();
			}
		}
		return $points;
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
		$result = $result & @unlink($this->getImagePath().$deletename.".thumb.jpg");
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
				$thumbpath = $imagepath . $savename . "." . "thumb.jpg";
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
		$matching_values = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^sel_matching_(\d+)/", $key, $matches))
			{
				if ((strcmp($value, "") != 0) && ($value != -1))
				{
					array_push($matching_values, $value);
				}
			}
		}
		$check_matching = array_flip($matching_values);
		if (count($check_matching) != count($matching_values))
		{
			// duplicate matching values!!!
			$result = false;
			ilUtil::sendInfo($this->lng->txt("duplicate_matching_values_selected"), TRUE);
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
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/^sel_matching_(\d+)/", $key, $matches))
				{
					if ($value > -1) // -1 is the unselected value in the non javascript version
					{
						$entered_values++;
						$next_id = $ilDB->nextId('tst_solutions');
						$query = sprintf("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s)",
							array('integer','integer','integer','text','text','integer','integer'),
							array(
								$next_id,
								$active_id,
								$this->getId(),
								trim($value),
								trim($matches[1]),
								$pass,
								time()
							)
						);
					}
				}
			}
			$saveWorkingDataResult = true;
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

	function get_random_id()
	{
		mt_srand((double)microtime()*1000000);
		$random_number = mt_rand(1, 100000);
		$found = FALSE;
		while ($found)
		{
			$found = FALSE;
			foreach ($this->matchingpairs as $key => $value)
			{
				if (($value->getTermId() == $random_number) || ($value->getDefinitionId() == $random_number))
				{
					$found = TRUE;
					$random_number++;
				}
			}
		}
		return $random_number;
	}

	function pc_array_shuffle($array) {
		$i = count($array);
		mt_srand((double)microtime()*1000000);
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
		return $array;
	}
	
	/**
	* Sets the shuffle flag
	*
	* @param boolean $shuffle A flag indicating whether the answers are shuffled or not
	* @access public
	* @see $shuffle
	*/
	function setShuffle($shuffle)
	{
		switch ($shuffle)
		{
			case 0:
			case 1:
			case 2:
			case 3:
				$this->shuffle = $shuffle;
				break;
			default:
				$this->shuffle = 1;
				break;
		}
	}

	/**
	* Returns the question type of the question
	*
	* @return integer The question type of the question
	* @access public
	*/
	function getQuestionType()
	{
		return "assMatchingQuestion";
	}
	
	/**
	* Returns the name of the additional question data table in the database
	*
	* @return string The additional table name
	* @access public
	*/
	function getAdditionalTableName()
	{
		return "qpl_question_matching";
	}

	/**
	* Returns the name of the answer table in the database
	*
	* @return string The answer table name
	* @access public
	*/
	function getAnswerTableName()
	{
		return array("qpl_answer_matching", "qpl_answer_matching_term");
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
	* Returns the matchingpairs array
	*/
	function &getMatchingPairs()
	{
		return $this->matchingpairs;
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
		$worksheet->writeString($startrow, 0, ilExcelUtils::_convert_text($this->lng->txt($this->getQuestionType())), $format_title);
		$worksheet->writeString($startrow, 1, ilExcelUtils::_convert_text($this->getTitle()), $format_title);
		$imagepath = $this->getImagePath();
		$i = 1;
		$terms = $this->getTerms();
		foreach ($solutions as $solution)
		{
			$matches_written = FALSE;
			foreach ($this->getMatchingPairs() as $idx => $answer)
			{
				if (!$matches_written) $worksheet->writeString($startrow + $i, 1, ilExcelUtils::_convert_text($this->lng->txt("matches")));
				$matches_written = TRUE;
				if ($answer->getDefinitionId() == $solution["value2"])
				{
					if (strlen($answer->getDefinition())) $worksheet->writeString($startrow + $i, 0, ilExcelUtils::_convert_text($answer->getDefinition()));
				}
				if ($answer->getTermId() == $solution["value1"])
				{
					if (strlen($answer->getTermId())) $worksheet->writeString($startrow + $i, 2, ilExcelUtils::_convert_text($terms[$answer->getTermId()]));
				}
			}
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
	protected function rebuildThumbnails()
	{
		if ($this->getMatchingType() == MT_TERMS_PICTURES)
		{
			if ($this->getMatchingPairCount())
			{
				foreach ($this->getMatchingPairs() as $pair)
				{
					if (@file_exists($this->getImagePath() . $pair->getPicture()))
					{
						$thumbpath = $this->getImagePath() . $pair->getPicture() . "." . "thumb.jpg";
						ilUtil::convertImage($this->getImagePath() . $pair->getPicture(), $thumbpath, "JPEG", $this->getThumbGeometry());
					}
				}
			}
		}
	}

}

?>
