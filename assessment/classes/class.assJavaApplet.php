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
include_once "./assessment/classes/class.assQuestion.php";

define ("JAVAAPPLET_QUESTION_IDENTIFIER", "JAVA APPLET QUESTION");

/**
* Class for Java Applet Questions
*
* ASS_JavaApplet is a class for Java Applet Questions.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assJavaApplet.php
* @modulegroup   Assessment
*/
class ASS_JavaApplet extends ASS_Question
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
	* Java applet file name
	*
	* The file name of the java applet
	*
	* @var string
	*/
	var $javaapplet_filename;

	/**
	* Java Applet code parameter
	*
	* Java Applet code parameter
	*
	* @var string
	*/
	var $java_code;

	/**
	* Java Applet width parameter
	*
	* Java Applet width parameter
	*
	* @var integer
	*/
	var $java_width;

	/**
	* Java Applet height parameter
	*
	* Java Applet height parameter
	*
	* @var integer
	*/
	var $java_height;

	/**
	* Additional java applet parameters
	*
	* Additional java applet parameters
	*
	* @var array
	*/
	var $parameters;

	/**
	* ASS_JavaApplet constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_JavaApplet object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @param string $question The question string of the multiple choice question
	* @param integer $response Indicates the response type of the multiple choice question
	* @param integer $output_type The output order of the multiple choice answers
	* @access public
	* @see ASS_Question:ASS_Question()
	*/
	function ASS_JavaApplet(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1,
		$question = "",
		$javaapplet_filename = ""
	)
	{
		$this->ASS_Question($title, $comment, $author, $owner);
		$this->question = $question;
		$this->javaapplet_filename = $javaapplet_filename;
		$this->parameters = array();
	}


	/**
	* Returns a QTI xml representation of the question
	*
	* Returns a QTI xml representation of the question and sets the internal
	* domxml variable with the DOM XML representation of the QTI xml representation
	*
	* @return string The QTI xml representation of the question
	* @access public
	*/
	function to_xml($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false)
	{
		include_once("./classes/class.ilXmlWriter.php");
		$a_xml_writer = new ilXmlWriter;
		// set xml header
		$a_xml_writer->xmlHeader();
		$a_xml_writer->xmlStartTag("questestinterop");
		$attrs = array(
			"ident" => "il_".IL_INST_ID."_qst_".$this->getId(),
			"title" => $this->getTitle()
		);
		$a_xml_writer->xmlStartTag("item", $attrs);
		// add question description
		$a_xml_writer->xmlElement("qticomment", NULL, $this->getComment());
		// add estimated working time
		$workingtime = $this->getEstimatedWorkingTime();
		$duration = sprintf("P0Y0M0DT%dH%dM%dS", $workingtime["h"], $workingtime["m"], $workingtime["s"]);
		$a_xml_writer->xmlElement("duration", NULL, $duration);
		// add ILIAS specific metadata
		$a_xml_writer->xmlStartTag("itemmetadata");
		$a_xml_writer->xmlStartTag("qtimetadata");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "ILIAS_VERSION");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->ilias->getSetting("ilias_version"));
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "QUESTIONTYPE");
		$a_xml_writer->xmlElement("fieldentry", NULL, JAVAAPPLET_QUESTION_IDENTIFIER);
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "AUTHOR");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getAuthor());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlEndTag("qtimetadata");
		$a_xml_writer->xmlEndTag("itemmetadata");

		// PART I: qti presentation
		$attrs = array(
			"label" => $this->getTitle()
		);
		$a_xml_writer->xmlStartTag("presentation", $attrs);
		// add flow to presentation
		$a_xml_writer->xmlStartTag("flow");
		// add material with question text to presentation
		$a_xml_writer->xmlStartTag("material");
		$a_xml_writer->xmlElement("mattext", NULL, $this->getQuestion());
		$a_xml_writer->xmlEndTag("material");
		$solution = $this->getSuggestedSolution(0);
		if (count($solution))
		{
			if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches))
			{
				$a_xml_writer->xmlStartTag("material");
				$intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
				if (strcmp($matches[1], "") != 0)
				{
					$intlink = $solution["internal_link"];
				}
				$attrs = array(
					"label" => "suggested_solution"
				);
				$a_xml_writer->xmlElement("mattext", $attrs, $intlink);
				$a_xml_writer->xmlEndTag("material");
			}
		}

		$a_xml_writer->xmlStartTag("material");
		$attrs = array(
			"label" => "applet data",
			"uri" => $this->getJavaAppletFilename(),
			"height" => $this->getJavaHeight(),
			"width" => $this->getJavaWidth(),
			"embedded" => "base64"
		);
		$javapath = $this->getJavaPath() . $this->getJavaAppletFilename();
		$fh = @fopen($javapath, "rb");
		if ($fh == false)
		{
			return;
		}
		$javafile = fread($fh, filesize($javapath));
		fclose($fh);
		$base64 = base64_encode($javafile);
		$a_xml_writer->xmlElement("matapplet", $attrs, $base64);

		if ($this->buildParamsOnly())
		{
			if ($this->java_code)
			{
				$attrs = array(
					"label" => "java_code"
				);
				$a_xml_writer->xmlElement("mattext", $attrs, $this->java_code);
			}
			foreach ($this->parameters as $key => $value)
			{
				$attrs = array(
					"label" => $value["name"]
				);
				$a_xml_writer->xmlElement("mattext", $attrs, $value["value"]);
			}
			if ($test_output)
			{
				include_once "./assessment/classes/class.ilObjTest.php";
				$attrs = array(
					"label" => "test_type"
				);
				$a_xml_writer->xmlElement("mattext", $attrs, ilObjTest::_getTestType($test_output));
				$attrs = array(
					"label" => "test_id"
				);
				$a_xml_writer->xmlElement("mattext", $attrs, $test_output);
				$attrs = array(
					"label" => "question_id"
				);
				$a_xml_writer->xmlElement("mattext", $attrs, $this->getId());
				$attrs = array(
					"label" => "user_id"
				);
				global $ilUser;
				$a_xml_writer->xmlElement("mattext", $attrs, $ilUser->id);
				$attrs = array(
					"label" => "points_max"
				);
				$a_xml_writer->xmlElement("mattext", $attrs, $this->getPoints());
				$attrs = array(
					"label" => "pass"
				);
				include_once "./assessment/classes/class.ilObjTest.php";
				$pass = ilObjTest::_getPass($ilUser->id, $test_output);
				$a_xml_writer->xmlElement("mattext", $attrs, $pass);
				$attrs = array(
					"label" => "post_url"
				);
				$a_xml_writer->xmlElement("mattext", $attrs, ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/assessment/save_java_question_result.php");

				$info = array();
				if (ilObjTest::_getHidePreviousResults($test_output, true))
				{
					$info = $this->getReachedInformation($ilUser->id, $test_output, $pass);
				}
				else
				{
					$pass = $this->getSolutionMaxPass($user_id, $test_output);
					$info = $this->getReachedInformation($ilUser->id, $test_output, $pass);
				}
				foreach ($info as $kk => $infodata)
				{
					$attrs = array(
						"label" => "value_" . $infodata["order"] . "_1"
					);
					$a_xml_writer->xmlElement("mattext", $attrs, $infodata["value1"]);
					$attrs = array(
						"label" => "value_" . $infodata["order"] . "_2"
					);
					$a_xml_writer->xmlElement("mattext", $attrs, $infodata["value2"]);
				}
			}
		}
		$a_xml_writer->xmlEndTag("material");
		$a_xml_writer->xmlStartTag("material");
		$attrs = array(
			"label" => "points"
		);
		$a_xml_writer->xmlElement("mattext", $attrs, $this->getPoints());
		$a_xml_writer->xmlEndTag("material");

		$a_xml_writer->xmlEndTag("flow");
		$a_xml_writer->xmlEndTag("presentation");
		
		$a_xml_writer->xmlEndTag("item");
		$a_xml_writer->xmlEndTag("questestinterop");

		$xml = $a_xml_writer->xmlDumpMem(FALSE);
		if (!$a_include_header)
		{
			$pos = strpos($xml, "?>");
			$xml = substr($xml, $pos + 2);
		}
		return $xml;
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
					case "java_code" :
						$this->java_code = $matches[2];
						break;
					case "java_width" :
						$this->java_width = $matches[2];
						break;
					case "java_height" :
						$this->java_height = $matches[2];
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
		if ($this->java_code)
		{
			array_push($params_array, "java_code=$this->java_code");
		}
		if ($this->java_width)
		{
			array_push($params_array, "java_width=$this->java_width");
		}
		if ($this->java_height)
		{
			array_push($params_array, "java_height=$this->java_height");
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
		if ($this->java_code)
		{
			array_push($params_array, "java_code=$this->java_code");
		}
		foreach ($this->parameters as $key => $value)
		{
			array_push($params_array, "param_name_$key=" . $value["name"]);
			array_push($params_array, "param_value_$key=" . $value["value"]);
		}
		return join($params_array, "<separator>");
	}

	/**
	* Returns true, if a imagemap question is complete for use
	*
	* Returns true, if a imagemap question is complete for use
	*
	* @return boolean True, if the imagemap question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and ($this->javaapplet_filename) and ($this->java_width) and ($this->java_height) and ($this->points != ""))
		{
			return true;
		}
			else
		{
			return false;
		}
	}


	/**
	* Saves a ASS_JavaApplet object to a database
	*
	* Saves a ASS_JavaApplet object to a database (experimental)
	*
	* @param object $db A pear DB object
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		global $ilias;

		$complete = 0;
		if ($this->isComplete())
		{
			$complete = 1;
		}

		$db = & $ilias->db;

		$params = $this->buildParams();
		$estw_time = $this->getEstimatedWorkingTime();
		$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);

		if ($original_id)
		{
			$original_id = $db->quote($original_id);
		}
		else
		{
			$original_id = "NULL";
		}

		if ($this->id == -1)
		{
			// Neuen Datensatz schreiben
			$now = getdate();
			$question_type = $this->getQuestionType();
			$created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
			$query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, obj_fi, title, comment, author, owner, question_text, points, working_time, shuffle, complete, image_file, params, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($question_type . ""),
				$db->quote($this->obj_id . ""),
				$db->quote($this->title . ""),
				$db->quote($this->comment . ""),
				$db->quote($this->author . ""),
				$db->quote($this->owner . ""),
				$db->quote($this->question . ""),
				$db->quote($this->points . ""),
				$db->quote($estw_time . ""),
				$db->quote($this->shuffle . ""),
				$db->quote($complete . ""),
				$db->quote($this->javaapplet_filename . ""),
				$db->quote($params . ""),
				$db->quote($created . ""),
				$original_id
			);

			$result = $db->query($query);
			if ($result == DB_OK)
			{
				$this->id = $this->ilias->db->getLastInsertId();

				// create page object of question
				$this->createPageObject();

				// Falls die Frage in einen Test eingef�gt werden soll, auch diese Verbindung erstellen
				if ($this->getTestId() > 0)
				{
					$this->insertIntoTest($this->getTestId());
				}
			}
		}
		else
		{
			// Vorhandenen Datensatz aktualisieren
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, points = %s, working_time=%s, shuffle = %s, complete = %s, image_file = %s, params = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title . ""),
				$db->quote($this->comment . ""),
				$db->quote($this->author . ""),
				$db->quote($this->question . ""),
				$db->quote($this->points . ""),
				$db->quote($estw_time . ""),
				$db->quote($this->shuffle . ""),
				$db->quote($complete . ""),
				$db->quote($this->javaapplet_filename . ""),
				$db->quote($params . ""),
				$db->quote($this->id . "")
			);
			$result = $db->query($query);
		}
		parent::saveToDb($original_id);
	}

	/**
	* Loads a ASS_JavaApplet object from a database
	*
	* Loads a ASS_JavaApplet object from a database (experimental)
	*
	* @param object $db A pear DB object
	* @param integer $question_id A unique key which defines the multiple choice test in the database
	* @access public
	*/
	function loadFromDb($question_id)
	{
		global $ilias;

		$db = & $ilias->db;
		$query = sprintf("SELECT * FROM qpl_questions WHERE question_id = %s",
			$db->quote($question_id)
		);
		$result = $db->query($query);

		if (strcmp(strtolower(get_class($result)), db_result) == 0)
		{
			if ($result->numRows() == 1)
			{
				$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->id = $question_id;
				$this->title = $data->title;
				$this->comment = $data->comment;
				$this->obj_id = $data->obj_fi;
				$this->author = $data->author;
				$this->points = $data->points;
				$this->owner = $data->owner;
				$this->original_id = $data->original_id;
				$this->javaapplet_filename = $data->image_file;
				$this->question = $data->question_text;
				$this->solution_hint = $data->solution_hint;
				$this->splitParams($data->params);
				$this->setShuffle($data->shuffle);
				$this->setEstimatedWorkingTime(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
			}
		}
		parent::loadFromDb($question_id);
	}

	/**
	* Duplicates an ASS_JavaApplet
	*
	* Duplicates an ASS_JavaApplet
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
		include_once ("./assessment/classes/class.assQuestion.php");
		$original_id = ASS_Question::_getOriginalId($this->id);
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

		// duplicate the image
		$clone->duplicateApplet($original_id);
		return $clone->id;
	}

	/**
	* Copies an ASS_JavaApplet object
	*
	* Copies an ASS_JavaApplet object
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
		include_once ("./assessment/classes/class.assQuestion.php");
		$original_id = ASS_Question::_getOriginalId($this->id);
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

		// duplicate the image
		$clone->copyApplet($original_id, $source_questionpool);
		return $clone->id;
	}
	
	function duplicateApplet($question_id)
	{
		$javapath = $this->getJavaPath();
		$javapath_original = preg_replace("/([^\d])$this->id([^\d])/", "\${1}$question_id\${2}", $javapath);
		if (!file_exists($javapath))
		{
			ilUtil::makeDirParents($javapath);
		}
		$filename = $this->getJavaAppletFilename();
		if (!copy($javapath_original . $filename, $javapath . $filename)) {
			print "java applet could not be duplicated!!!! ";
		}
	}

	function copyApplet($question_id, $source_questionpool)
	{
		$javapath = $this->getJavaPath();
		$javapath_original = preg_replace("/([^\d])$this->id([^\d])/", "\${1}$question_id\${2}", $javapath);
		$javapath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $javapath_original);
		if (!file_exists($javapath))
		{
			ilUtil::makeDirParents($javapath);
		}
		$filename = $this->getJavaAppletFilename();
		if (!copy($javapath_original . $filename, $javapath . $filename)) {
			print "java applet could not be copied!!!! ";
		}
	}

	/**
	* Gets the multiple choice question
	*
	* Gets the question string of the ASS_JavaApplet object
	*
	* @return string The question string of the ASS_JavaApplet object
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
	* Sets the question string of the ASS_JavaApplet object
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
	* Returns the java applet code parameter
	*
	* Returns the java applet code parameter
	*
	* @return string java applet code parameter
	* @access public
	*/
	function getJavaCode()
	{
		return $this->java_code;
	}

	/**
	* Sets the java applet code parameter
	*
	* Sets the java applet code parameter
	*
	* @param string java applet code parameter
	* @access public
	*/
	function setJavaCode($java_code = "")
	{
		$this->java_code = $java_code;
	}

	/**
	* Returns the java applet width parameter
	*
	* Returns the java applet width parameter
	*
	* @return integer java applet width parameter
	* @access public
	*/
	function getJavaWidth()
	{
		return $this->java_width;
	}

	/**
	* Sets the java applet width parameter
	*
	* Sets the java applet width parameter
	*
	* @param integer java applet width parameter
	* @access public
	*/
	function setJavaWidth($java_width = "")
	{
		$this->java_width = $java_width;
	}

	/**
	* Returns the java applet height parameter
	*
	* Returns the java applet height parameter
	*
	* @return integer java applet height parameter
	* @access public
	*/
	function getJavaHeight()
	{
		return $this->java_height;
	}

	/**
	* Sets the java applet height parameter
	*
	* Sets the java applet height parameter
	*
	* @param integer java applet height parameter
	* @access public
	*/
	function setJavaHeight($java_height = "")
	{
		$this->java_height = $java_height;
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
	function calculateReachedPoints($user_id, $test_id, $pass = NULL)
	{
		global $ilDB;
		
		$found_values = array();
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($user_id, $test_id);
		}
		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s AND pass = %s",
			$ilDB->quote($user_id . ""),
			$ilDB->quote($test_id . ""),
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		$points = 0;
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$points += $data->points;
		}

		// check for special scoring options in test
		$query = sprintf("SELECT * FROM tst_tests WHERE test_id = %s",
			$ilDB->quote($test_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			if ($row["count_system"] == 1)
			{
				if ($points != $this->getMaximumPoints())
				{
					$points = 0;
				}
			}
		}
		else
		{
			$points = 0;
		}
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
	function getReachedInformation($user_id, $test_id, $pass = NULL)
	{
		$found_values = array();
		if (is_null($pass))
		{
			$pass = $this->getSolutionMaxPass($user_id, $test_id);
		}
		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s AND pass = %s",
			$this->ilias->db->quote($user_id . ""),
			$this->ilias->db->quote($test_id . ""),
			$this->ilias->db->quote($this->getId() . ""),
			$this->ilias->db->quote($pass . "")
		);
		$result = $this->ilias->db->query($query);
		$counter = 1;
		$user_result = array();
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT))
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
	function saveWorkingData($test_id, $limit_to = LIMIT_NO_LIMIT)
	{
    parent::saveWorkingData($test_id);
		return true;
  }

	/**
	* Gets the java applet file name
	*
	* Gets the java applet file name
	*
	* @return string The java applet file of the ASS_JavaApplet object
	* @access public
	* @see $javaapplet_filename
	*/
	function getJavaAppletFilename()
	{
		return $this->javaapplet_filename;
	}

	/**
	* Sets the java applet file name
	*
	* Sets the java applet file name
	*
	* @param string $javaapplet_file.
	* @access public
	* @see $javaapplet_filename
	*/
	function setJavaAppletFilename($javaapplet_filename, $javaapplet_tempfilename = "")
	{
		if (!empty($javaapplet_filename))
		{
			$this->javaapplet_filename = $javaapplet_filename;
		}
		if (!empty($javaapplet_tempfilename))
		{
			$javapath = $this->getJavaPath();
			if (!file_exists($javapath))
			{
				ilUtil::makeDirParents($javapath);
			}
			
			//if (!move_uploaded_file($javaapplet_tempfilename, $javapath . $javaapplet_filename))
			if (!ilUtil::moveUploadedFile($javaapplet_tempfilename, $javaapplet_filename, $javapath.$javaapplet_filename))
			{
				print "java applet not uploaded!!!! ";
			}
		}
	}

	function syncWithOriginal()
	{
		global $ilias;
		if ($this->original_id)
		{
			$complete = 0;
			if ($this->isComplete())
			{
				$complete = 1;
			}
			$db = & $ilias->db;
	
			$estw_time = $this->getEstimatedWorkingTime();
			$estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);
	
			$query = sprintf("UPDATE qpl_questions SET obj_fi = %s, title = %s, comment = %s, author = %s, question_text = %s, points = %s, working_time=%s, shuffle = %s, complete = %s, image_file = %s, params = %s WHERE question_id = %s",
				$db->quote($this->obj_id. ""),
				$db->quote($this->title . ""),
				$db->quote($this->comment . ""),
				$db->quote($this->author . ""),
				$db->quote($this->question . ""),
				$db->quote($this->points . ""),
				$db->quote($estw_time . ""),
				$db->quote($this->shuffle . ""),
				$db->quote($complete . ""),
				$db->quote($this->javaapplet_filename . ""),
				$db->quote($params . ""),
				$db->quote($this->original_id . "")
			);
			$result = $db->query($query);

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
		return 7;
	}
}

?>
