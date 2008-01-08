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
* Class for Flash Application Questions
*
* assFlashApp is a class for Flash Application Questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author		Christian Müller <christian.mueller@uni-passau.de>
* @version	$Id: class.assFlashApp.php,v 1.81 2006/07/30 11:51:56 hschottm Exp $
* @module   class.assFlashAppApplet.php
* @modulegroup   Assessment
*/
class assFlashApp extends assQuestion
{
	/**
	* Question string
	*
	* The question string of the multiple choice question
	*
	* @var string
	*/
	var $question;

	/**
	* Flash file name
	*
	* The file name of the Flash Application
	*
	* @var string
	*/
	var $flash_filename;


	/**
	* Flash Application width parameter
	*
	* Flash Application width parameter
	*
	* @var integer
	*/
	var $flash_width;

	/**
	* Flash Application height parameter
	*
	* Flash Application height parameter
	*
	* @var integer
	*/
	var $flash_height;

	/**
	* Additional Flash Application parameters
	*
	* Additional Flash Application parameters
	*
	* @var array
	*/
	var $parameters;

	/**
	* assFlashApp constructor
	*
	* The constructor takes possible arguments an creates an instance of the assFlashApp object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the multiple choice question
	* @param integer $response Indicates the response type of the multiple choice question
	* @param integer $output_type The output order of the multiple choice answers
	* @access public
	* @see assQuestion:assQuestion()
	*/
	function assFlashApp(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$flash_filename = ""
	)
	{
		$this->assQuestion($title, $comment, $author, $owner);
		$this->question = $question;
		$this->flash_filename = $flash_filename;
		$this->parameters = array();
	}

	/**
	* Sets the applet parameters from a parameter string containing all parameters in a list
	*
	* Sets the applet parameters from a parameter string containing all parameters in a list
	*
	* @param string $params All applet parameters in a list
	* @access public
	*/
	function splitParams($params = "")
	{
		$params_array = split("<separator>", $params);
		foreach ($params_array as $pair)
		{
			if (preg_match("/(.*?)\=(.*)/", $pair, $matches))
			{
				switch ($matches[1])
				{
					case "flash_width" :
						$this->flash_width = $matches[2];
						break;
					case "flash_height" :
						$this->flash_height = $matches[2];
						break;
				}
				if (preg_match("/param_name_(\d+)/", $matches[1], $found_key))
				{
					$this->parameters[$found_key[1]]["name"] = $matches[2];
				}
				if (preg_match("/param_value_(\d+)/", $matches[1], $found_key))
				{
					$this->parameters[$found_key[1]]["value"] = $matches[2];
				}
			}
		}
	}

	/**
	* Returns a string containing the applet parameters
	*
	* Returns a string containing the applet parameters. This is used for saving the applet data to database
	*
	* @return string All applet parameters
	* @access public
	*/
	function buildParams()
	{
		$params_array = array();
		if ($this->flash_width)
		{
			array_push($params_array, "flash_width=$this->flash_width");
		}
		if ($this->flash_height)
		{
			array_push($params_array, "flash_height=$this->flash_height");
		}
		foreach ($this->parameters as $key => $value)
		{
			array_push($params_array, "param_name_$key=" . $value["name"]);
			array_push($params_array, "param_value_$key=" . $value["value"]);
		}
		return join($params_array, "<separator>");
	}

	/**
	* Returns a string containing the additional applet parameters
	*
	* Returns a string containing the additional applet parameters
	*
	* @return string All additional applet parameters
	* @access public
	*/
	function buildParamsOnly()
	{
		$params_array = array();
		
		foreach ($this->parameters as $key => $value)
		{
			array_push($params_array, "param_name_$key=" . $value["name"]);
			array_push($params_array, "param_value_$key=" . $value["value"]);
		}
		return join($params_array, "<separator>");
	}

	/**
	* Returns true, if a FlashApp question is complete for use
	*
	* Returns true, if a FlashApp question is complete for use
	*
	* @return boolean True, if the imagemap question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and ($this->flash_filename) and ($this->flash_width) and ($this->flash_height) and ($this->getMaximumPoints() > 0))
		{
			return true;
		}
		/*
		else if (($this->title) and ($this->author) and ($this->question) and ($this->getJavaArchive()) and ($this->getJavaCodebase()) and ($this->flash_width) and ($this->flash_height) and ($this->getMaximumPoints() > 0))
		{
			return true;
		}*/
		else
		{
			return false;
		}
	}


	/**
	* Saves a assFlashApp object to a database
	*
	* Saves a assFlashApp object to a database (experimental)
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

		$params = $this->buildParams();
		$estw_time = $this->getEstimatedWorkingTime();
		$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);
		$original_id = $original_id ? $ilDB->quote($original_id) : "NULL";

		// cleanup RTE images which are not inserted into the question text
		include_once("./Services/RTE/classes/class.ilRTE.php");

		if ($this->id == -1)
		{
			// Neuen Datensatz schreiben
			$now = getdate();
			$question_type = $this->getQuestionType();
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, points, working_time, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($this->getQuestionTypeID() . ""),
				$ilDB->quote($this->obj_id . ""),
				$ilDB->quote($this->title . ""),
				$ilDB->quote($this->comment . ""),
				$ilDB->quote($this->author . ""),
				$ilDB->quote($this->owner . ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->question, 0)),
				$ilDB->quote($this->points . ""),
				$ilDB->quote($estw_time . ""),
				$ilDB->quote($complete . ""),
				$ilDB->quote($created . ""),
				$original_id
			);

			$result = $ilDB->query($query);
			if ($result == DB_OK)
			{
				$this->id = $ilDB->getLastInsertId();
				$query = sprintf("INSERT INTO qpl_question_flashapp (question_fi, flash_file, params) VALUES (%s, %s, %s)",
					$ilDB->quote($this->id . ""),
					$ilDB->quote($this->flash_filename . ""),
					$ilDB->quote($params . "")
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
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, points = %s, working_time=%s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title . ""),
				$ilDB->quote($this->comment . ""),
				$ilDB->quote($this->author . ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->question, 0)),
				$ilDB->quote($this->points . ""),
				$ilDB->quote($estw_time . ""),
				$ilDB->quote($complete . ""),
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_flashapp SET flash_file = %s, params = %s WHERE question_fi = %s",
				$ilDB->quote($this->flash_filename . ""),
				$ilDB->quote($params . ""),
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a assFlashApp object from a database
	*
	* Loads a assFlashApp object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilDB;

    $query = sprintf("SELECT qpl_questions.*, qpl_question_flashapp.* FROM qpl_questions, qpl_question_flashapp WHERE question_id = %s AND qpl_questions.question_id = qpl_question_flashapp.question_fi",
			$ilDB->quote($question_id)
		);
		$result = $ilDB->query($query);

		if (strcmp(strtolower(get_class($result)), db_result) == 0)
		{
			if ($result->numRows() == 1)
			{
				$data = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
				$this->id = $question_id;
				$this->title = $data->title;
				$this->comment = $data->comment;
				$this->obj_id = $data->obj_fi;
				$this->author = $data->author;
				$this->points = $data->points;
				$this->owner = $data->owner;
				$this->original_id = $data->original_id;
				$this->flash_filename = $data->flash_file;
				include_once("./Services/RTE/classes/class.ilRTE.php");
				$this->question = ilRTE::_replaceMediaObjectImageSrc($data->question_text, 1);
				$this->solution_hint = $data->solution_hint;
				$this->splitParams($data->params);
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}
		}
		parent::loadFromDb($question_id);
	}

	/**
	* Duplicates an assFlashApp
	*
	* Duplicates an assFlashApp
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

		// duplicate the image
		$clone->duplicateApplet($this_id);
		return $clone->id;
	}

	/**
	* Copies an assFlashApp object
	*
	* Copies an assFlashApp object
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
		include_once ("./Modules/TestQuestionPoll/classes/class.assQuestion.php");
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

		// duplicate the image
		$clone->copyApplet($original_id, $source_questionpool);
		return $clone->id;
	}
	
	function duplicateApplet($question_id)
	{
		$javapath = $this->getFlashPath();
		$javapath_original = preg_replace("/([^\d])$this->id([^\d])/", "\${1}$question_id\${2}", $javapath);
		if (!file_exists($javapath))
		{
			ilUtil::makeDirParents($javapath);
		}
		$filename = $this->getFlashFilename();
		if (!copy($javapath_original . $filename, $javapath . $filename)) {
			print "Flash Application could not be duplicated!!!! ";
		}
	}

	function copyApplet($question_id, $source_questionpool)
	{
		$javapath = $this->getFlashPath();
		$javapath_original = preg_replace("/([^\d])$this->id([^\d])/", "\${1}$question_id\${2}", $javapath);
		$javapath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $javapath_original);
		if (!file_exists($javapath))
		{
			ilUtil::makeDirParents($javapath);
		}
		$filename = $this->getFlashFilename();
		if (!copy($javapath_original . $filename, $javapath . $filename)) {
			print "Flash Application could not be copied!!!! ";
		}
	}

	/**
	* Gets the multiple choice question
	*
	* Gets the question string of the assFlashApp object
	*
	* @return string The question string of the assFlashApp object
	* @access public
	* @see $question
	*/
	function getQuestion()
	{
		return $this->question;
	}

	/**
	* Sets the question text
	*
	* Sets the question string of the assFlashApp object
	*
	* @param string $question A string containing the question text
	* @access public
	* @see $question
	*/
	function setQuestion($question = "")
	{
		$this->question = $question;
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
	* Returns the Flash Application width parameter
	*
	* Returns the Flash Application width parameter
	*
	* @return integer Flash Application width parameter
	* @access public
	*/
	function getFlashWidth()
	{
		return $this->flash_width;
	}

	/**
	* Sets the Flash Application width parameter
	*
	* Sets the Flash Application width parameter
	*
	* @param integer Flash Application width parameter
	* @access public
	*/
	function setFlashWidth($flash_width = "")
	{
		$this->flash_width = $flash_width;
	}

	/**
	* Returns the Flash Application height parameter
	*
	* Returns the Flash Application height parameter
	*
	* @return integer Flash Application height parameter
	* @access public
	*/
	function getFlashHeight()
	{
		return $this->flash_height;
	}

	/**
	* Sets the Flash Application height parameter
	*
	* Sets the Flash Application height parameter
	*
	* @param integer Flash Application height parameter
	* @access public
	*/
	function setFlashHeight($flash_height = "")
	{
		$this->flash_height = $flash_height;
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
		$points = 0;
		while ($data = $result->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$points += $data->points;
		}

		$points = parent::calculateReachedPoints($active_id, $pass = NULL, $points);
		return $points;
	}

	/**
	* Returns the evaluation data, a learner has entered to answer the question
	*
	* Returns the evaluation data, a learner has entered to answer the question
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @access public
	*/
	function getReachedInformation($active_id, $pass = NULL)
	{
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
		$counter = 0;
		$user_result = array();
		while ($data = $result->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$true = 0;
			if ($data->points > 0)
			{
				$true = 1;
			}
			$solution = array(
				"order" => "$counter",
				"points" => "$data->points",
				"true" => "$true",
				"value1" => "$data->value1",
				"value2" => "$data->value2",
			);
			$counter++;
			array_push($user_result, $solution);
		}
		return $user_result;
	}

	/**
	* Adds a new parameter value to the parameter list
	*
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

	/**
	* Adds a new parameter value to the parameter list at a given index
	*
	* Adds a new parameter value to the parameter list at a given index
	*
	* @param integer $index The index at which the parameter should be inserted
	* @param string $name The name of the parameter value
	* @param string $value The value of the parameter value
	* @access public
	* @see $parameters
	*/
	function addParameterAtIndex($index = 0, $name = "", $value = "")
	{
		$this->parameters[$index] = array("name" => $name, "value" => $value);
	}

	/**
	* Removes a parameter value from the parameter list
	*
	* Removes a parameter value from the parameter list
	*
	* @param string $name The name of the parameter value
	* @access public
	* @see $parameters
	*/
	function removeParameter($name)
	{
		foreach ($this->parameters as $key => $value)
		{
			if (strcmp($name, $value["name"]) == 0)
			{
				array_splice($this->parameters, $key, 1);
				return;
			}
		}
	}

	/**
	* Returns the paramter at a given index
	*
	* Returns the paramter at a given index
	*
	* @param intege $index The index value of the parameter
	* @return array The parameter at the given index
	* @access public
	* @see $parameters
	*/
	function getParameter($index)
	{
		if (($index < 0) or ($index >= count($this->parameters)))
		{
			return undef;
		}
		return $this->parameters[$index];
	}

	/**
	* Returns the index of an applet parameter
	*
	* Returns the index of an applet parameter
	*
	* @param string $name The name of the parameter value
	* @return integer The index of the applet parameter or -1 if the parameter wasn't found
	* @access private
	* @see $parameters
	*/
	function getParameterIndex($name)
	{
		foreach ($this->parameters as $key => $value)
		{
			if (array_key_exists($name, $value))
			{
				return $key;
			}
		}
		return -1;
	}

	/**
	* Returns the number of additional applet parameters
	*
	* Returns the number of additional applet parameters
	*
	* @return integer The number of additional applet parameters
	* @access public
	* @see $parameters
	*/
	function getParameterCount()
	{
		return count($this->parameters);
	}

	/**
	* Removes all applet parameters
	*
	* Removes all applet parameters
	*
	* @access public
	* @see $parameters
	*/
	function flushParams()
	{
		$this->parameters = array();
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
    parent::saveWorkingData($active_id, $pass);
		return true;
  }

	/**
	* Gets the Flash Application file name
	*
	* Gets the Flash Application file name
	*
	* @return string The Flash Application file of the assFlashApp object
	* @access public
	* @see $flash_filename
	*/
	function getFlashFilename()
	{
		return $this->flash_filename;
	}

	/**
	* Sets the Flash Application file name
	*
	* Sets the Flash Application file name
	*
	* @param string $javaapplet_file.
	* @access public
	* @see $flash_filename
	*/
	function setFlashFilename($flash_filename, $javaapplet_tempfilename = "")
	{
		if (!empty($flash_filename))
		{
			$this->flash_filename = $flash_filename;
		}
		if (!empty($javaapplet_tempfilename))
		{
			$javapath = $this->getFlashPath();
			if (!file_exists($javapath))
			{
				ilUtil::makeDirParents($javapath);
			}
			
			//if (!move_uploaded_file($javaapplet_tempfilename, $javapath . $flash_filename))
			if (!ilUtil::moveUploadedFile($javaapplet_tempfilename, $flash_filename, $javapath.$flash_filename))
			{
				print "Flash Application not uploaded!!!! ";
			}
			else
			{
				//$this->setJavaCodebase();
				//$this->setJavaArchive();
			}
		}
	}
	
	function deleteFlashFilename()
	{
		unlink($this->getFlashPath() . $this->getFlashFilename());
		$this->flash_filename = "";
	}

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
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, points = %s, working_time=%s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->obj_id. ""),
				$ilDB->quote($this->title . ""),
				$ilDB->quote($this->comment . ""),
				$ilDB->quote($this->author . ""),
				$ilDB->quote($this->question . ""),
				$ilDB->quote($this->points . ""),
				$ilDB->quote($estw_time . ""),
				$ilDB->quote($complete . ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE qpl_question_flashapp SET flash_file = %s, params = %s WHERE question_fi = %s",
				$ilDB->quote($this->flash_filename . ""),
				$ilDB->quote($params . ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);

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
		return "assFlashApp";
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
		return "qpl_question_flashapp";
	}
	
	/**
	* Writes the temporary file which includes all parameters for 
	* the flash question. The name of the file is the actual session
	* number. It will be saved in the data/[client_id]/assesment/flash/tmp 
	* directory 
	*
	* @return boolean Success of creating the tmp File
	* @access public
	*/
	function writeTmpFile($tmp_data)
	{
	// tmp directory erzeugen 
	$tmp_flash_dir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/$this->obj_id/$this->id/flash/tmp";
	//echo $tmp_flash_dir;
	if (	ilUtil::makeDirParents($tmp_flash_dir)) 
		

            $fp = @fopen ($tmp_flash_dir."/".$_COOKIE["PHPSESSID"], "w");

			
			if ($fp == false)
			{
				die("FlashApp tmp file: cannot create file.");
			}

			if (fwrite($fp,$tmp_data) == -1)
			{
				die("FlashApp tmp file: cannot write to file. Please check Writepermissions.");
			}

			fclose($fp);
	
		return true;
	}	

	/**
	* Collects all text in the question which could contain media objects
	* which were created with the Rich Text Editor
	*/
	function getRTETextWithMediaObjects()
	{
		return parent::getRTETextWithMediaObjects();
	}

}

?>
