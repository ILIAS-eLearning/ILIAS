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

/**
* Class for a new question type
*
* assQuestionTypeTemplate is a class for a new question type
*
* @author		Unknown <unknowns@email>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assQuestionTypeTemplate extends assQuestion
{
	/**
	* assQuestionTypeTemplate constructor
	*
	* The constructor takes possible arguments an creates an instance of the assQuestionTypeTemplate object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the new question type
	* @access public
	* @see assQuestion:assQuestion()
	*/
	function assQuestionTypeTemplate(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = ""
	  )
	{
		$this->assQuestion($title, $comment, $author, $owner, $question);

		// do your own initialization stuff here
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
		// this method is inherited from assQuestion
		
		// overwrite it here and define your own code which checks the completion status of this question
		// the following code is example code and you have to exchange it with your own code:
		if ((strlen($this->getTitle())) and (strlen($this->getAuthor())) and (strlen($this->getQuestion())) and ($this->getMaximumPoints() > 0))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	* Saves a the question object in the ILIAS database
	*
	* Saves a the question object in the ILIAS database
	*
	* @param integer $original_id The question ID of the question from which this question is cloned
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		// this method is inherited from assQuestion
		
		// overwrite it here and define your own code to save the question into the ILIAS database
		// the following code is example code and you have to exchange it with your own code:
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

		if ($this->getId() == -1)
		{
			// create a new dataset
			$now = getdate();
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, points, working_time, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote("1"),
				$ilDB->quote($this->obj_id),
				$ilDB->quote($this->title),
				$ilDB->quote($this->comment),
				$ilDB->quote($this->author),
				$ilDB->quote($this->owner),
				$ilDB->quote($this->question),
				$ilDB->quote($this->getMaximumPoints() . ""),
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
				// maybe you have more fields than the qpl_question table offers.
				// so you have to write this content in your question type table which
				// could look like the following code
				
				$this->setId($ilDB->getLastInsertId());
				$query = sprintf("INSERT INTO qpl_question_mytype (question_fi, myattribute) VALUES (%s, %s)",
					$ilDB->quote($this->getId() . ""),
					$ilDB->quote($this->getMyAttribute())
				);
				$ilDB->query($query);

				// create a page object of question (always necessary!!!!!)
				$this->createPageObject();

				// this is used then the question should be inserted into a test
				if ($this->getTestId() > 0)
				{
					$this->insertIntoTest($this->getTestId());
				}
			}
		}
		else
		{
			// update an existing dataset
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, points = %s, working_time=%s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title),
				$ilDB->quote($this->comment),
				$ilDB->quote($this->author),
				$ilDB->quote($this->question),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($estw_time),
				$ilDB->quote("$complete"),
				$ilDB->quote($this->getId())
			);
			$result = $ilDB->query($query);
			
			// maybe you have more fields than the qpl_question table offers.
			// so you have to write this content in your question type table which
			// could look like the following code
			$query = sprintf("UPDATE qpl_question_mytype SET myattribute = %s WHERE question_fi = %s",
				$ilDB->quote($this->getMyAttribute()),
				$ilDB->quote($this->getId() . "")
			);
			$result = $ilDB->query($query);
		}

		// call the save method of the parent class (assQuestion)
		// to store the suggested solutions
		parent::saveToDb($original_id);
	}

	/**
	* Loads the question object from the ILIAS database
	*
	* Loads the question object from the ILIAS database
	*
	* @param integer $question_id A unique key containing the database key of the question
	* @access public
	*/
	function loadFromDb($question_id)
	{
		// this method is inherited from assQuestion
		
		// overwrite it here and define your own code to load the question from the ILIAS database
		// the following code is example code and you have to exchange it with your own code:
		global $ilDB;

		$hasimages = 0;
    $query = sprintf("SELECT qpl_questions.*, qpl_question_mytype.* FROM qpl_questions, qpl_question_mytype WHERE question_id = %s AND qpl_questions.question_id = qpl_question_mytype.question_fi",
			$ilDB->quote($question_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$data = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			$this->setId($question_id);
			$this->setTitle($data["title"]);
			$this->setComment($data["comment"]);
			// ... and so on
		}

		// call the parent class to load the suggested solutions
		parent::loadFromDb($question_id);
	}

	/**
	* Duplicates a question
	*
	* Duplicates a question
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
		
		// the following varies for different question types
		
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($original_id);
		// duplicate the images
		$clone->duplicateImages($original_id);

		return $clone->id;
	}

	/**
	* Copies the question into another question pool
	*
	* Copies the question into another question pool
	*
	* @param integer $target_questionpool The ID of the target question pool
	* @param string $title A new title of the question, if given
	* @access public
	*/
	function copyObject($target_questionpool, $title = "")
	{
		if ($this->getId() <= 0)
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

		// the following varies for different question types
		
		// copy XHTML media objects
		$clone->copyXHTMLMediaObjectsOfQuestion($original_id);
		// duplicate the image
		$clone->copyImages($original_id, $source_questionpool);

		return $clone->id;
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
		// this method is inherited from assQuestion
		
		// overwrite it here and define your own code to calculate the maximum points of the question
		// the following code is example code and you have to exchange it with your own code:
		$points = 3;
		return $points;
	}

	/**
	* Returns the points, a learner has reached answering the question
	*
	* Returns the points, a learner has reached answering the question
	* The points are calculated from the given answers including checks
	* for all special scoring options in the test container.
	*
	* @param integer $active_id The active ID of the tst_active database table
	* @param integer $pass The test pass of the given user
	* @access public
	*/
	function calculateReachedPoints($active_id, $pass = NULL)
	{
		// this method is inherited from assQuestion
		
		// overwrite it here and define your own code to calculate the reached points of a given user of the question
		
		// what you have to do is to check the user solutions in the database
		// table tst_solutions for the given active id, the question id and the given pass
		global $ilDB;
		
		$found_values = array();
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
		
		// no you have to do something with the values
		
		$points = .... some calculations

		// then call the calculateReachedPoints method of the parent class to check
		// for special test settings
		$points = parent::calculateReachedPoints($active_id, $pass = NULL, $points);
		return $points;
	}
	
	/**
	* Saves the learners input of the question to the database
	*
	* Saves the learners input of the question to the database
	*
	* @param integer $active_id The active ID of the tst_active database table
	* @param integer $pass The test pass of the given user
	* @access public
	*/
	function saveWorkingData($active_id, $pass = NULL)
	{
		// this method is inherited from assQuestion
		
		// overwrite it here and define your own code to save the results of a users input during a test
		
		// what you have to do is to check the POST values of a user
		// who works through this question in a test and save the values
		// in the tst_solutions database table
		
		// The rules say that it is not allowed to work with $_GET and $_POST variables
		// in non-GUI classes. I will change this in a future version but for now you
		// should do it the same way
		global $ilDB;
		global $ilUser;

		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$activepass = ilObjTest::_getPass($active_id);
		$entered_values = 0;

		$query = sprintf("SELECT * FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($activepass . "")
		);
		$result = $ilDB->query($query);
		$row = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
		$update = $row->solution_id;
		if ($update)
		{
			// update your dataset in the tst_solutions table
		}
		else
		{
			// create a new dataset in the tst_solutions table from the
			// POST values of the user
		}
		
		// call the saveWorkingData method of the parent class
		// This method calls the calculateReachedPoints method and saves
		// the reached points of the user in the tst_test_result table
		// for a better performance.
    parent::saveWorkingData($active_id, $pass);

		return TRUE;
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
		// this method is inherited from assQuestion
		
		// overwrite it here and define your own code to synchronize the original of the question
		// with the question data
		
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
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, points = %s, working_time=%s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title. ""),
				$ilDB->quote($this->comment. ""),
				$ilDB->quote($this->author. ""),
				$ilDB->quote($this->question. ""),
				$ilDB->quote($this->getMaximumPoints() . ""),
				$ilDB->quote($estw_time. ""),
				$ilDB->quote($complete. ""),
				$ilDB->quote($this->original_id. "")
			);
			$result = $ilDB->query($query);
			
			// do your own synchronization on your additional question database table if there is one
			$query = sprintf("UPDATE qpl_question_mytype SET myattribute = %s WHERE question_fi = %s",
				$ilDB->quote($this->getMyAttribute()),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);

			// call the synchronization method of the parent class
			parent::syncWithOriginal();
		}
	}

	/**
	* Returns the question type of the question
	*
	* Returns the question type of the question which is the database
	* ID of the qpl_question_type database table entry of this question type
	*
	* @return integer The question type of the question
	* @access public
	*/
	function getQuestionType()
	{
		return "assQuestionTypeTemplate";
	}
	
	/**
	* Returns the name of the additional question data table in the database (if one exists)
	*
	* Returns the name of the additional question data table in the database (if one exists)
	*
	* @return string The additional table name
	* @access public
	*/
	function getAdditionalTableName()
	{
		return "qpl_question_mytype";
	}
	
	/**
	* Returns the name of the additional answer table in the database (if one exists)
	*
	* Returns the name of the additional answer table in the database (if one exists)
	*
	* @return string The answer table name
	* @access public
	*/
	function getAnswerTableName()
	{
		return "qpl_answer_mytype";
	}
	
	/**
	* Collects all text in the question which could contain media objects
	* which were created with the Rich Text Editor
	*/
	function getRTETextWithMediaObjects()
	{
		// here additional RTE capable text of the question type
		// must be collected
		return parent::getRTETextWithMediaObjects();
	}
}

?>
