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
	protected $terms;
	protected $definitions;
	
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
		$this->definitions = array();
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
		$affectedRows = $ilDB->manipulateF("INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, shuffle, matching_type, thumb_geometry, element_height) VALUES (%s, %s, %s, %s, %s)", 
			array("integer", "text", "text","integer","integer"),
			array(
				$this->getId(),
				$this->shuffle,
				$this->matching_type,
				$this->getThumbGeometry(),
				($this->getElementHeight() >= 20) ? $this->getElementHeight() : NULL
			)
		);

		// delete old terms
		$affectedRows = $ilDB->manipulateF("DELETE FROM qpl_a_mterm WHERE question_fi = %s",
			array('integer'),
			array($this->getId())
		);
		
		// delete old definitions
		$affectedRows = $ilDB->manipulateF("DELETE FROM qpl_a_mdef WHERE question_fi = %s",
			array('integer'),
			array($this->getId())
		);
	
		$termids = array();
		// write terms
		foreach ($this->terms as $key => $term)
		{
			$next_id = $ilDB->nextId('qpl_a_mterm');
			$affectedRows = $ilDB->manipulateF("INSERT INTO qpl_a_mterm (term_id, question_fi, picture, term) VALUES (%s, %s, %s, %s)",
				array('integer','integer','text', 'text'),
				array($next_id, $this->getId(), $term->picture, $term->text)
			);
			$termids[$term->identifier] = $next_id;
		}

		$definitionids = array();
		// write definitions
		foreach ($this->definitions as $key => $definition)
		{
			$next_id = $ilDB->nextId('qpl_a_mdef');
			$affectedRows = $ilDB->manipulateF("INSERT INTO qpl_a_mdef (def_id, question_fi, picture, definition, morder) VALUES (%s, %s, %s, %s, %s)",
				array('integer','integer','text', 'text', 'integer'),
				array($next_id, $this->getId(), $definition->picture, $definition->text, $definition->identifier)
			);
			$definitionids[$definition->identifier] = $next_id;
		}

		$affectedRows = $ilDB->manipulateF("DELETE FROM qpl_a_matching WHERE question_fi = %s",
			array('integer'),
			array($this->getId())
		);
		$matchingpairs = $this->getMatchingPairs();
		foreach ($matchingpairs as $key => $pair)
		{
			$next_id = $ilDB->nextId('qpl_a_matching');
			$affectedRows = $ilDB->manipulateF("INSERT INTO qpl_a_matching (answer_id, question_fi, points, term_fi, definition_fi) VALUES (%s, %s, %s, %s, %s)",
				array('integer','integer','float','integer','integer'),
				array(
					$next_id,
					$this->getId(),
					$pair->points,
					$termids[$pair->term->identifier],
					$definitionids[$pair->definition->identifier]
				)
			);
		}
		
		$this->rebuildThumbnails();
		
		parent::saveToDb($original_id);
	}

	/**
	* Loads a assMatchingQuestion object from a database
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
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
			$this->setObjId($data["obj_fi"]);
			$this->setTitle($data["title"]);
			$this->setComment($data["description"]);
			$this->setOriginalId($data["original_id"]);
			$this->setNrOfTries($data['nr_of_tries']);
			$this->setAuthor($data["author"]);
			$this->setPoints($data["points"]);
			$this->setOwner($data["owner"]);
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
			$this->setThumbGeometry($data["thumb_geometry"]);
			$this->setElementHeight($data["element_height"]);
			$this->setShuffle($data["shuffle"]);
			$this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
		}

		$termids = array();
		$result = $ilDB->queryF("SELECT * FROM qpl_a_mterm WHERE question_fi = %s ORDER BY term_id ASC",
			array('integer'),
			array($question_id)
		);
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php";
		$this->terms = array();
		if ($result->numRows() > 0)
		{
			while ($data = $ilDB->fetchAssoc($result))
			{
				$term = new assAnswerMatchingTerm($data['term'], $data['picture'], $data['term_id']);
				array_push($this->terms, $term);
				$termids[$data['term_id']] = $term;
			}
		}

		$definitionids = array();
		$result = $ilDB->queryF("SELECT * FROM qpl_a_mdef WHERE question_fi = %s ORDER BY def_id ASC",
			array('integer'),
			array($question_id)
		);
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php";
		$this->definitions = array();
		if ($result->numRows() > 0)
		{
			while ($data = $ilDB->fetchAssoc($result))
			{
				$definition = new assAnswerMatchingDefinition($data['definition'], $data['picture'], $data['morder']);
				array_push($this->definitions, $definition);
				$definitionids[$data['def_id']] = $definition;
			}
		}

		$this->matchingpairs = array();
		$result = $ilDB->queryF("SELECT * FROM qpl_a_matching WHERE question_fi = %s ORDER BY answer_id",
			array('integer'),
			array($question_id)
		);
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php";
		if ($result->numRows() > 0)
		{
			while ($data = $ilDB->fetchAssoc($result))
			{
				array_push($this->matchingpairs, new assAnswerMatchingPair($termids[$data['term_fi']], $definitionids[$data['definition_fi']], $data['points']));
			}
		}
		parent::loadFromDb($question_id);
	}

	
	/**
	* Duplicates an assMatchingQuestion
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

		// duplicate the image
		$clone->duplicateImages($this_id);
		$clone->onDuplicate($this_id);
		return $clone->id;
	}

	/**
	* Copies an assMatchingQuestion
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

	public function duplicateImages($question_id)
	{
		$imagepath = $this->getImagePath();
		$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
		if (!file_exists($imagepath))
		{
			ilUtil::makeDirParents($imagepath);
		}
		foreach ($this->terms as $term)
		{
			if (strlen($term->picture))
			{
				$filename = $term->picture;
				if (!@copy($imagepath_original . $filename, $imagepath . $filename)) 
				{
					$ilLog->write("matching question image could not be duplicated: $imagepath_original$filename");
				}
				if (!@copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) 
				{
					$ilLog->write("matching question image thumbnail could not be duplicated: $imagepath_original" . $this->getThumbPrefix() . $filename);
				}
			}
		}
		foreach ($this->definitions as $definition)
		{
			if (strlen($definition->picture))
			{
				$filename = $definition->picture;
				if (!@copy($imagepath_original . $filename, $imagepath . $filename)) 
				{
					$ilLog->write("matching question image could not be duplicated: $imagepath_original$filename");
				}
				if (!@copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) 
				{
					$ilLog->write("matching question image thumbnail could not be duplicated: $imagepath_original" . $this->getThumbPrefix() . $filename);
				}
			}
		}
	}

	public function copyImages($question_id, $source_questionpool)
	{
		$imagepath = $this->getImagePath();
		$imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
		$imagepath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $imagepath_original);
		if (!file_exists($imagepath))
		{
			ilUtil::makeDirParents($imagepath);
		}
		foreach ($this->term as $term)
		{
			if (strlen($term->picture))
			{
				$filename = $term->picture;
				if (!copy($imagepath_original . $filename, $imagepath . $filename)) 
				{
					$ilLog->write("matching question image could not be copied: $imagepath_original$filename");
				}
				if (!copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) 
				{
					$ilLog->write("matching question image thumbnail could not be copied: $imagepath_original" . $this->getThumbPrefix() . $filename);
				}
			}
		}
		foreach ($this->definitions as $definition)
		{
			if (strlen($definition->picture))
			{
				$filename = $definition->picture;
				if (!copy($imagepath_original . $filename, $imagepath . $filename)) 
				{
					$ilLog->write("matching question image could not be copied: $imagepath_original$filename");
				}
				if (!copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) 
				{
					$ilLog->write("matching question image thumbnail could not be copied: $imagepath_original" . $this->getThumbPrefix() . $filename);
				}
			}
		}
	}

	/**
	* Inserts a matching pair for an matching choice question. The students have to fill in an order for the matching pair.
	* The matching pair is an ASS_AnswerMatching object that will be created and assigned to the array $this->matchingpairs.
	*
	* @param integer $position The insert position in the matching pairs array
	* @param object $term A matching term
	* @param object $definition A matching definition
	* @param double $points The points for selecting the matching pair (even negative points can be used)
	* @see $matchingpairs
	*/
	public function insertMatchingPair($position, $term = null, $definition = null, $points = 0.0)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php";
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php";
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php";
		if (is_null($term)) $term = new assAnswerMatchingTerm();
		if (is_null($definition)) $definition = new assAnswerMatchingDefinition();
		$pair = new assAnswerMatchingPair($term, $definition, $points);
		if ($position < count($this->matchingpairs))
		{
			$part1 = array_slice($this->matchingpairs, 0, $position);
			$part2 = array_slice($this->matchingpairs, $position);
			$this->matchingpairs = array_merge($part1, array($pair), $part2);
		}
		else
		{
			array_push($this->matchingpairs, $pair);
		}
	}
	/**
	* Adds an matching pair for an matching choice question. The students have to fill in an order for the matching pair.
	* The matching pair is an ASS_AnswerMatching object that will be created and assigned to the array $this->matchingpairs.
	*
	* @param object $term A matching term
	* @param object $definition A matching definition
	* @param double $points The points for selecting the matching pair (even negative points can be used)
	* @see $matchingpairs
	*/
	public function addMatchingPair($term = null, $definition = null, $points = 0.0)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php";
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php";
		include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php";
		if (is_null($term)) $term = new assAnswerMatchingTerm();
		if (is_null($definition)) $definition = new assAnswerMatchingDefinition();
		$pair = new assAnswerMatchingPair($term, $definition, $points);
		array_push($this->matchingpairs, $pair);
	}
	
	/**
	* Returns a term with a given identifier
	*/
	public function getTermWithIdentifier($a_identifier)
	{
		foreach ($this->terms as $term)
		{
			if ($term->identifier == $a_identifier) return $term;
		}
		return null;
	}

	/**
	* Returns a definition with a given identifier
	*/
	public function getDefinitionWithIdentifier($a_identifier)
	{
		foreach ($this->definitions as $definition)
		{
			if ($definition->identifier == $a_identifier) return $definition;
		}
		return null;
	}

	/**
	* Returns a matching pair with a given index. The index of the first
	* matching pair is 0, the index of the second matching pair is 1 and so on.
	*
	* @param integer $index A nonnegative index of the n-th matching pair
	* @return object ASS_AnswerMatching-Object
	* @see $matchingpairs
	*/
	public function getMatchingPair($index = 0)
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
	* @see $matchingpairs
	*/
	public function deleteMatchingPair($index = 0)
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
	* @see $matchingpairs
	*/
	public function flushMatchingPairs()
	{
		$this->matchingpairs = array();
	}

	/**
	* Returns the number of matching pairs
	*
	* @return integer The number of matching pairs of the matching question
	* @see $matchingpairs
	*/
	public function getMatchingPairCount()
	{
		return count($this->matchingpairs);
	}

	/**
	* Returns the terms of the matching question
	*
	* @return array An array containing the terms
	* @see $terms
	*/
	public function getTerms()
	{
		return $this->terms;
	}
	
	/**
	* Returns the definitions of the matching question
	*
	* @return array An array containing the definitions
	* @see $terms
	*/
	public function getDefinitions()
	{
		return $this->definitions;
	}
	
	/**
	* Returns the number of terms
	*
	* @return integer The number of terms
	* @see $terms
	*/
	public function getTermCount()
	{
		return count($this->terms);
	}
	
	/**
	* Returns the number of definitions
	*
	* @return integer The number of definitions
	* @see $definitions
	*/
	public function getDefinitionCount()
	{
		return count($this->definitions);
	}
	
	/**
	* Adds a term
	*
	* @param string $term The text of the term
	* @see $terms
	*/
	public function addTerm($term)
	{
		array_push($this->terms, $term);
	}
	
	/**
	* Adds a definition
	*
	* @param object $definition The definition
	* @see $definitions
	*/
	public function addDefinition($definition)
	{
		array_push($this->definitions, $definition);
	}
	
	/**
	* Inserts a term
	*
	* @param string $term The text of the term
	* @see $terms
	*/
	public function insertTerm($position, $term = null)
	{
		if (is_null($term))
		{
			include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingTerm.php";
			$term = new assAnswerMatchingTerm();
		}
		if ($position < count($this->terms))
		{
			$part1 = array_slice($this->terms, 0, $position);
			$part2 = array_slice($this->terms, $position);
			$this->terms = array_merge($part1, array($term), $part2);
		}
		else
		{
			array_push($this->terms, $term);
		}
	}
	
	/**
	* Inserts a definition
	*
	* @param object $definition The definition
	* @see $definitions
	*/
	public function insertDefinition($position, $definition = null)
	{
		if (is_null($definition))
		{
			include_once "./Modules/TestQuestionPool/classes/class.assAnswerMatchingDefinition.php";
			$definition = new assAnswerMatchingDefinition();
		}
		if ($position < count($this->definitions))
		{
			$part1 = array_slice($this->definitions, 0, $position);
			$part2 = array_slice($this->definitions, $position);
			$this->definitions = array_merge($part1, array($definition), $part2);
		}
		else
		{
			array_push($this->definitions, $definition);
		}
	}
	
	/**
	* Deletes all terms
	* @see $terms
	*/
	public function flushTerms()
	{
		$this->terms = array();
	}

	/**
	* Deletes all definitions
	* @see $definitions
	*/
	public function flushDefinitions()
	{
		$this->definitions = array();
	}

	/**
	* Deletes a term
	*
	* @param string $term_id The id of the term to delete
	* @see $terms
	*/
	public function deleteTerm($position)
	{
		unset($this->terms[$position]);
		$this->terms = array_values($this->terms);
	}

	/**
	* Deletes a definition
	*
	* @param integer $position The position of the definition in the definition array
	* @see $definitions
	*/
	public function deleteDefinition($position)
	{
		unset($this->definitions[$position]);
		$this->definitions = array_values($this->definitions);
	}

	/**
	* Sets a specific term
	*
	* @param string $term The text of the term
	* @param string $index The index of the term
	* @see $terms
	*/
	public function setTerm($term, $index)
	{
		$this->terms[$index] = $term;
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
			foreach ($this->matchingpairs as $pair)
			{
				if (($pair->definition->identifier == $value) && ($pair->term->identifier == $found_value1[$key]))
				{
					$points += $pair->points;
				}
			}
		}

		$points = parent::calculateReachedPoints($active_id, $pass = NULL, $points);
		return $points;
	}

	/**
	* Returns the maximum points, a learner can reach answering the question
	*/
	function getMaximumPoints()
	{
		$points = 0;
		foreach ($this->matchingpairs as $key => $pair)
		{
			if ($pair->points > 0)
			{
				$points += $pair->points;
			}
		}
		return $points;
	}
	
	/**
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

	public function removeTermImage($index)
	{
		$term = $this->terms[$index];
		if (is_object($term))
		{
			$this->deleteImagefile($term->picture);
			$term->picture = null;
		}
	}
	
	public function removeDefinitionImage($index)
	{
		$definition = $this->definitions[$index];
		if (is_object($definition))
		{
			$this->deleteImagefile($definition->picture);
			$definition->picture = null;
		}
	}
	

	/**
	* Deletes an imagefile from the system if the file is deleted manually
	* 
	* @param string $filename Image file filename
	* @return boolean Success
	*/
	public function deleteImagefile($filename)
	{
		$deletename = $filename;
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
	function setImageFile($image_tempfilename, $image_filename, $previous_filename = '')
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
	* @see $answers
	*/
	function checkSaveData()
	{
		$result = true;
		$matching_values = array();
		foreach ($_POST['matching'][$this->getId()] as $definition => $term)
		{
			if ($term > 0)
			{
				array_push($matching_values, $term);
			}
		}

		$check_matching = array_flip($matching_values);
		if (count($check_matching) != count($matching_values))
		{
			$result = false;
			ilUtil::sendFailure($this->lng->txt("duplicate_matching_values_selected"), TRUE);
		}
		return $result;
	}

	/**
	* Saves the learners input of the question to the database
	* 
	* @param integer $test_id The database id of the test containing this question
  * @return boolean Indicates the save status (true if saved successful, false otherwise)
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

			foreach ($_POST['matching'][$this->getId()] as $definition => $term)
			{
				$entered_values++;
				$next_id = $ilDB->nextId('tst_solutions');
				$query = $ilDB->manipulateF("INSERT INTO tst_solutions (solution_id, active_fi, question_fi, value1, value2, pass, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s)",
					array('integer','integer','integer','text','text','integer','integer'),
					array(
						$next_id,
						$active_id,
						$this->getId(),
						$term,
						$definition,
						$pass,
						time()
					)
				);
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

	public function getRandomId()
	{
		mt_srand((double)microtime()*1000000);
		$random_number = mt_rand(1, 100000);
		$found = FALSE;
		while ($found)
		{
			$found = FALSE;
			foreach ($this->matchingpairs as $key => $pair)
			{
				if (($pair->term->identifier == $random_number) || ($pair->definition->identifier == $random_number))
				{
					$found = TRUE;
					$random_number++;
				}
			}
		}
		return $random_number;
	}

	/**
	* Sets the shuffle flag
	*
	* @param boolean $shuffle A flag indicating whether the answers are shuffled or not
	* @see $shuffle
	*/
	public function setShuffle($shuffle)
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
	*/
	public function getQuestionType()
	{
		return "assMatchingQuestion";
	}
	
	/**
	* Returns the name of the additional question data table in the database
	*
	* @return string The additional table name
	*/
	public function getAdditionalTableName()
	{
		return "qpl_qst_matching";
	}

	/**
	* Returns the name of the answer table in the database
	*
	* @return string The answer table name
	*/
	public function getAnswerTableName()
	{
		return array("qpl_a_matching", "qpl_a_mterm");
	}

	/**
	* Collects all text in the question which could contain media objects
	* which were created with the Rich Text Editor
	*/
	public function getRTETextWithMediaObjects()
	{
		return parent::getRTETextWithMediaObjects();
	}
	
	/**
	* Returns the matchingpairs array
	*/
	public function &getMatchingPairs()
	{
		return $this->matchingpairs;
	}

	/**
	* Returns true if the question type supports JavaScript output
	*
	* @return boolean TRUE if the question type supports JavaScript output, FALSE otherwise
	*/
	public function supportsJavascriptOutput()
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
		include_once ("./Services/Excel/classes/class.ilExcelUtils.php");
		$solutions = $this->getSolutionValues($active_id, $pass);
		$worksheet->writeString($startrow, 0, ilExcelUtils::_convert_text($this->lng->txt($this->getQuestionType())), $format_title);
		$worksheet->writeString($startrow, 1, ilExcelUtils::_convert_text($this->getTitle()), $format_title);
		$imagepath = $this->getImagePath();
		$i = 1;
		foreach ($solutions as $solution)
		{
			$matches_written = FALSE;
			foreach ($this->getMatchingPairs() as $idx => $pair)
			{
				if (!$matches_written) $worksheet->writeString($startrow + $i, 1, ilExcelUtils::_convert_text($this->lng->txt("matches")));
				$matches_written = TRUE;
				if ($pair->definition->identifier == $solution["value2"])
				{
					if (strlen($pair->definition->text))
					{
						$worksheet->writeString($startrow + $i, 0, ilExcelUtils::_convert_text($pair->definition->text));
					}
					else
					{
						$worksheet->writeString($startrow + $i, 0, ilExcelUtils::_convert_text($pair->definition->picture));
					}
				}
				if ($pair->term->identifier == $solution["value1"])
				{
					if (strlen($pair->term->text))
					{
						$worksheet->writeString($startrow + $i, 2, ilExcelUtils::_convert_text($pair->term->text));
					}
					else
					{
						$worksheet->writeString($startrow + $i, 2, ilExcelUtils::_convert_text($pair->term->picture));
					}
				}
			}
			$i++;
		}
		return $startrow + $i + 1;
	}
	
	/**
	* Get the thumbnail geometry
	*
	* @return integer Geometry
	*/
	public function getThumbGeometry()
	{
		return $this->thumb_geometry;
	}
	
	/**
	* Get the thumbnail geometry
	*
	* @return integer Geometry
	*/
	public function getThumbSize()
	{
		return $this->getThumbGeometry();
	}
	
	/**
	* Set the thumbnail geometry
	*
	* @param integer $a_geometry Geometry
	*/
	public function setThumbGeometry($a_geometry)
	{
		$this->thumb_geometry = ($a_geometry < 1) ? 100 : $a_geometry;
	}

	/**
	* Get the minimum element height
	*
	* @return integer Height
	*/
	public function getElementHeight()
	{
		return $this->element_height;
	}
	
	/**
	* Set the minimum element height
	*
	* @param integer $a_height Height
	*/
	public function setElementHeight($a_height)
	{
		$this->element_height = ($a_height < 20) ? "" : $a_height;
	}

	/**
	* Rebuild the thumbnail images with a new thumbnail size
	*/
	public function rebuildThumbnails()
	{
		foreach ($this->terms as $term)
		{
			if (strlen($term->picture)) $this->generateThumbForFile($this->getImagePath(), $term->picture);
		}
		foreach ($this->definitions as $definition)
		{
			if (strlen($definition->picture)) $this->generateThumbForFile($this->getImagePath(), $definition->picture);
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
	
	public function getEstimatedElementHeight()
	{
		$hasImages = false;
		foreach ($this->terms as $term)
		{
			if (strlen($term->picture))
			{
				$hasImages = true;
			}
		}
		foreach ($this->definitions as $definition)
		{
			if (strlen($definition->picture))
			{
				$hasImages = true;
			}
		}
		if ($hasImages)
		{ // 40 is approx. the height of the preview image
			return max($this->getElementHeight(), $this->getThumbSize() + 40);
		}
		else
		{
			return ($this->getElementHeight()) ? $this->getElementHeight() : 0;
		}
	}
	
	/**
	* Returns a JSON representation of the question
	* TODO
	*/
	public function toJSON()
	{
		include_once("./Services/RTE/classes/class.ilRTE.php");
		$result = array();
		$result['id'] = (int) $this->getId();
		$result['type'] = (string) $this->getQuestionType();
		$result['title'] = (string) $this->getTitle();
		$result['question'] =  (string) ilRTE::_replaceMediaObjectImageSrc($this->getQuestion(), 0);
		$result['nr_of_tries'] = (int) $this->getNrOfTries();
		$result['shuffle'] = true;
		$result['feedback'] = array(
			"onenotcorrect" => nl2br(ilRTE::_replaceMediaObjectImageSrc($this->getFeedbackGeneric(0), 0)),
			"allcorrect" => nl2br(ilRTE::_replaceMediaObjectImageSrc($this->getFeedbackGeneric(1), 0))
			);
		$terms = array();
		foreach ($this->getMatchingPairs() as $pair)
		{
			array_push($terms, array(
				"term" => $pair->term->text,
				"id" =>(int)$pair->term->identifier
			));
		}
		$terms = $this->pcArrayShuffle($terms);
		$pairs = array();
		foreach ($this->getMatchingPairs() as $pair)
		{
			array_push($pairs, array(
				"term_id" => (int) $pair->term->identifier,
				"points" => (float) $pair->points,
				"definition" => (string) $pair->definition->text,
				"def_id" => (int) $pair->definition->identifier,
				"terms" => $terms
			));
		}
		$result['pairs'] = $pairs;
		$mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
		$result['mobs'] = $mobs;
		return json_encode($result);
	}

}

?>
