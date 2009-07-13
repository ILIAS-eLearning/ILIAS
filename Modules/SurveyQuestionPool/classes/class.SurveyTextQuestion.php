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

include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

/**
* Text survey question
*
* The SurveyTextQuestion class defines and encapsulates basic methods and attributes
* for text survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestion
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyTextQuestion extends SurveyQuestion 
{
	var $maxchars;
	var $textwidth;
	var $textheight;
	
/**
* The constructor takes possible arguments an creates an instance of the SurveyTextQuestion object.
*
* @param string $title A title string to describe the question
* @param string $description A description string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @access public
*/
	function SurveyTextQuestion(
		$title = "",
		$description = "",
		$author = "",
		$questiontext = "",
		$owner = -1
	)
	{
		$this->SurveyQuestion($title, $description, $author, $questiontext, $owner);
		$this->maxchars = 0;
		$this->textwidth = 50;
		$this->textheight = 5;
	}
	
	/**
	* Returns the question data fields from the database
	*
	* @param integer $id The question ID from the database
	* @return array Array containing the question fields and data from the database
	* @access public
	*/
	function _getQuestionDataArray($id)
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT svy_question.*, " . $this->getAdditionalTableName() . ".* FROM svy_question, " . $this->getAdditionalTableName() . " WHERE svy_question.question_id = %s AND svy_question.question_id = " . $this->getAdditionalTableName() . ".question_fi",
			array('integer'),
			array($id)
		);
		if ($result->numRows() == 1)
		{
			return $ilDB->fetchAssoc($row);
		}
		else
		{
			return array();
		}
	}
	
/**
* Loads a SurveyTextQuestion object from the database
*
* @param integer $id The database id of the text survey question
* @access public
*/
	function loadFromDb($id) 
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT svy_question.*, " . $this->getAdditionalTableName() . ".* FROM svy_question, " . $this->getAdditionalTableName() . " WHERE svy_question.question_id = %s AND svy_question.question_id = " . $this->getAdditionalTableName() . ".question_fi",
			array('integer'),
			array($id)
		);
		if ($result->numRows() == 1) 
		{
			$data = $ilDB->fetchAssoc($result);
			$this->setId($data["question_id"]);
			$this->setTitle($data["title"]);
			$this->setDescription($data["description"]);
			$this->setObjId($data["obj_fi"]);
			$this->setAuthor($data["author"]);
			$this->setOwner($data["owner_fi"]);
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->setQuestiontext(ilRTE::_replaceMediaObjectImageSrc($data["questiontext"], 1));
			$this->setObligatory($data["obligatory"]);
			$this->setComplete($data["complete"]);
			$this->setOriginalId($data["original_id"]);

			$this->setMaxChars($data["maxchars"]);
			$this->setTextWidth($data["width"]);
			$this->setTextHeight($data["height"]);

		}
		parent::loadFromDb($id);
	}

/**
* Returns true if the question is complete for use
*
* @result boolean True if the question is complete for use, otherwise false
* @access public
*/
	function isComplete()
	{
		if ($this->title and $this->author and $this->questiontext)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
/**
* Sets the maximum number of allowed characters for the text answer
*
* @access public
*/
	function setMaxChars($maxchars = 0)
	{
		$this->maxchars = $maxchars;
	}
	
/**
* Returns the maximum number of allowed characters for the text answer
*
* @access public
*/
	function getMaxChars()
	{
		return ($this->maxchars) ? $this->maxchars : NULL;
	}
	
/**
* Saves a SurveyTextQuestion object to a database
*
* @access public
*/
  function saveToDb($original_id = "")
  {
		global $ilDB;
		
		$complete = $this->isComplete();
		$original_id = ($original_id) ? $original_id : NULL;
		// cleanup RTE images which are not inserted into the question text
		include_once("./Services/RTE/classes/class.ilRTE.php");
		ilRTE::_cleanupMediaObjectUsage($this->questiontext, "spl:html", $this->getId());

		if ($this->getId() == -1) 
		{
			// Write new dataset
			$next_id = $ilDB->nextId('svy_question');
			$affectedRows = $ilDB->manipulateF("INSERT INTO svy_question (question_id, questiontype_fi, obj_fi, owner_fi, title, description, author, questiontext, obligatory, complete, created, original_id, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
				array('integer', 'integer', 'integer', 'integer', 'text', 'text', 'text', 'text', 'text', 'text', 'integer', 'integer', 'integer'),
				array(
					$next_id,
					$this->getQuestionTypeID(),
					$this->getObjId(),
					$this->getOwner(),
					$this->getTitle(),
					$this->getDescription(),
					$this->getAuthor(),
					ilRTE::_replaceMediaObjectImageSrc($this->getQuestiontext(), 0),
					$this->getObligatory(),
					$this->isComplete(),
					time(),
					$original_id,
					time()
				)
			);
			$this->setId($next_id);
		} 
		else 
		{
			// update existing dataset
			$affectedRows = $ilDB->manipulateF("UPDATE svy_question SET title = %s, description = %s, author = %s, questiontext = %s, obligatory = %s, complete = %s, tstamp = %s WHERE question_id = %s",
				array('text', 'text', 'text', 'text', 'text', 'text', 'integer', 'integer'),
				array(
					$this->getTitle(),
					$this->getDescription(),
					$this->getAuthor(),
					ilRTE::_replaceMediaObjectImageSrc($this->getQuestiontext(), 0),
					$this->getObligatory(),
					$this->isComplete(),
					time(),
					$this->getId()
				)
			);
		}
		if ($affectedRows == 1) 
		{
			$affectedRows = $ilDB->manipulateF("DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
				array('integer'),
				array($this->getId())
			);
			$affectedRows = $ilDB->manipulateF("INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, maxchars, width, height) VALUES (%s, %s, %s, %s)",
				array('integer', 'integer', 'integer', 'integer'),
				array($this->getId(), $this->getMaxChars(), $this->getTextWidth(), $this->getTextHeight())
			);

			$this->saveMaterialsToDb();
		}
		parent::saveToDb($original_id);
	}

	/**
	* Returns an xml representation of the question
	*
	* @return string The xml representation of the question
	* @access public
	*/
	function toXML($a_include_header = TRUE, $obligatory_state = "")
	{
		include_once("./classes/class.ilXmlWriter.php");
		$a_xml_writer = new ilXmlWriter;
		$a_xml_writer->xmlHeader();
		$this->insertXML($a_xml_writer, $a_include_header, $obligatory_state);
		$xml = $a_xml_writer->xmlDumpMem(FALSE);
		if (!$a_include_header)
		{
			$pos = strpos($xml, "?>");
			$xml = substr($xml, $pos + 2);
		}
		return $xml;
	}
	
	/**
	* Adds the question XML to a given XMLWriter object
	*
	* @param object $a_xml_writer The XMLWriter object
	* @param boolean $a_include_header Determines wheather or not the XML should be used
	* @param string $obligatory_state The value of the obligatory state
	* @access public
	*/
	function insertXML(&$a_xml_writer, $a_include_header = TRUE, $obligatory_state = "")
	{
		$attrs = array(
			"id" => $this->getId(),
			"title" => $this->getTitle(),
			"type" => $this->getQuestiontype(),
			"obligatory" => $this->getObligatory()
		);
		$a_xml_writer->xmlStartTag("question", $attrs);
		
		$a_xml_writer->xmlElement("description", NULL, $this->getDescription());
		$a_xml_writer->xmlElement("author", NULL, $this->getAuthor());
		$a_xml_writer->xmlStartTag("questiontext");
		$this->addMaterialTag($a_xml_writer, $this->getQuestiontext());
		$a_xml_writer->xmlEndTag("questiontext");

		$a_xml_writer->xmlStartTag("responses");
		$attrs = array(
			"id" => "0",
			"rows" => $this->getTextHeight(),
			"columns" => $this->getTextWidth()
		);
		if ($this->getMaxChars() > 0)
		{
			$attrs["maxlength"] = $this->getMaxChars();
		}
		$a_xml_writer->xmlElement("response_text", $attrs);
		$a_xml_writer->xmlEndTag("responses");

		if (count($this->material))
		{
			if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $this->material["internal_link"], $matches))
			{
				$attrs = array(
					"label" => $this->material["title"]
				);
				$a_xml_writer->xmlStartTag("material", $attrs);
				$intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
				if (strcmp($matches[1], "") != 0)
				{
					$intlink = $this->material["internal_link"];
				}
				$a_xml_writer->xmlElement("mattext", NULL, $intlink);
				$a_xml_writer->xmlEndTag("material");
			}
		}
		
		$a_xml_writer->xmlEndTag("question");
	}

	/**
	* Returns the maxium number of allowed characters for the text answer
	*
	* @return integer The maximum number of characters
	* @access public
	*/
	function _getMaxChars($question_id)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT maxchars FROM svy_question WHERE question_id = %s",
			array('integer'),
			array($question_id)
		);
		if ($result->numRows())
		{
			$row = $ilDB->fetchAssoc($result);
			return $row["maxchars"];
		}
		return 0;
	}

	/**
	* Returns the question type of the question
	*
	* @return integer The question type of the question
	* @access public
	*/
	function getQuestionType()
	{
		return "SurveyTextQuestion";
	}

	/**
	* Returns the name of the additional question data table in the database
	*
	* @return string The additional table name
	* @access public
	*/
	function getAdditionalTableName()
	{
		return "svy_qst_text";
	}
	
	/**
	* Creates the user data of the svy_answer table from the POST data
	*
	* @return array User data according to the svy_answer table
	* @access public
	*/
	function &getWorkingDataFromUserInput($post_data)
	{
		$entered_value = $post_data[$this->getId() . "_text_question"];
		$data = array();
		if (strlen($entered_value))
		{
			array_push($data, array("textanswer" => $entered_value));
		}
		return $data;
	}
	
	/**
	* Checks the input of the active user for obligatory status
	* and entered values
	*
	* @param array $post_data The contents of the $_POST array
	* @param integer $survey_id The database ID of the active survey
	* @return string Empty string if the input is ok, an error message otherwise
	* @access public
	*/
	function checkUserInput($post_data, $survey_id)
	{
		$entered_value = $post_data[$this->getId() . "_text_question"];
		
		if ((!$this->getObligatory($survey_id)) && (strlen($entered_value) == 0)) return "";
		
		if (strlen($entered_value) == 0) return $this->lng->txt("text_question_not_filled_out");

		return "";
	}
	
	function randomText($length)
	{
		$random= "";
		$char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$char_list .= "abcdefghijklmnopqrstuvwxyz";
		$char_list .= "1234567890";
		for($i = 0; $i < $length; $i++)
		{ 
			$random .= substr($char_list,(rand()%(strlen($char_list))), 1);
			if (!rand(0,5)) $random .= ' ';
		}
		return $random;
	}
	
	/**
	* Saves random answers for a given active user in the database
	*
	* @param integer $active_id The database ID of the active user
	*/
	public function saveRandomData($active_id)
	{
		global $ilDB;
		// single response
		$randomtext = $this->randomText(rand(25,100));
		$next_id = $ilDB->nextId('svy_answer');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_answer (answer_id, question_fi, active_fi, value, textanswer, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
			array('integer', 'integer', 'integer', 'float', 'text', 'integer'),
			array($next_id, $this->getId(), $active_id, NULL, $randomtext, time())
		);
	}

	function saveUserInput($post_data, $active_id)
	{
		global $ilDB;

		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$entered_value = ilUtil::stripSlashes($post_data[$this->getId() . "_text_question"]);
		$maxchars = $this->getMaxChars();
		if ($maxchars > 0)
		{
			$entered_value = substr($entered_value, 0, $maxchars);
		}
		if (strlen($entered_value) == 0) return;
		$next_id = $ilDB->nextId('svy_answer');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_answer (answer_id, question_fi, active_fi, value, textanswer, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
			array('integer', 'integer', 'integer', 'float', 'text', 'integer'),
			array($next_id, $this->getId(), $active_id, NULL, (strlen($entered_value)) ? $entered_value : NULL, time())
		);
	}
	
	function &getCumulatedResults($survey_id, $nr_of_users)
	{
		global $ilDB;
		
		$question_id = $this->getId();
		
		$result_array = array();
		$cumulated = array();
		$textvalues = array();

		$result = $ilDB->queryF("SELECT svy_answer.* FROM svy_answer, svy_finished WHERE svy_answer.question_fi = %s AND svy_finished.survey_fi = %s AND svy_finished.finished_id = svy_answer.active_fi",
			array('integer','integer'),
			array($question_id, $survey_id)
		);
		
		while ($row = $ilDB->fetchAssoc($result))
		{
			$cumulated[$row["value"]]++;
			array_push($textvalues, $row["textanswer"]);
		}
		asort($cumulated, SORT_NUMERIC);
		end($cumulated);
		$numrows = $result->numRows();
		$result_array["USERS_ANSWERED"] = $result->numRows();
		$result_array["USERS_SKIPPED"] = $nr_of_users - $result->numRows();
		$result_array["QUESTION_TYPE"] = "SurveyTextQuestion";
		$result_array["textvalues"] = $textvalues;
		return $result_array;
	}
	
	/**
	* Creates an Excel worksheet for the detailed cumulated results of this question
	*
	* @param object $workbook Reference to the parent excel workbook
	* @param object $format_title Excel title format
	* @param object $format_bold Excel bold format
	* @param array $eval_data Cumulated evaluation data
	* @access public
	*/
	function setExportDetailsXLS(&$workbook, &$format_title, &$format_bold, &$eval_data)
	{
		include_once ("./classes/class.ilExcelUtils.php");
		$worksheet =& $workbook->addWorksheet();
		$worksheet->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("title")), $format_bold);
		$worksheet->writeString(0, 1, ilExcelUtils::_convert_text($this->getTitle()));
		$worksheet->writeString(1, 0, ilExcelUtils::_convert_text($this->lng->txt("question")), $format_bold);
		$worksheet->writeString(1, 1, ilExcelUtils::_convert_text($this->getQuestiontext()));
		$worksheet->writeString(2, 0, ilExcelUtils::_convert_text($this->lng->txt("question_type")), $format_bold);
		$worksheet->writeString(2, 1, ilExcelUtils::_convert_text($this->lng->txt($this->getQuestionType())));
		$worksheet->writeString(3, 0, ilExcelUtils::_convert_text($this->lng->txt("users_answered")), $format_bold);
		$worksheet->write(3, 1, $eval_data["USERS_ANSWERED"]);
		$worksheet->writeString(4, 0, ilExcelUtils::_convert_text($this->lng->txt("users_skipped")), $format_bold);
		$worksheet->write(4, 1, $eval_data["USERS_SKIPPED"]);
		$rowcounter = 5;

		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("given_answers")), $format_bold);
		$textvalues = "";
		if (is_array($eval_data["textvalues"]))
		{
			foreach ($eval_data["textvalues"] as $textvalue)
			{
				$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($textvalue));
			}
		}
	}

	/**
	* Adds the values for the user specific results export for a given user
	*
	* @param array $a_array An array which is used to append the values
	* @param array $resultset The evaluation data for a given user
	* @access public
	*/
	function addUserSpecificResultsData(&$a_array, &$resultset)
	{
		if (count($resultset["answers"][$this->getId()]))
		{
			foreach ($resultset["answers"][$this->getId()] as $key => $answer)
			{
				array_push($a_array, $answer["textanswer"]);
			}
		}
		else
		{
			array_push($a_array, $this->lng->txt("skipped"));
		}
	}

	/**
	* Returns an array containing all answers to this question in a given survey
	*
	* @param integer $survey_id The database ID of the survey
	* @return array An array containing the answers to the question. The keys are either the user id or the anonymous id
	* @access public
	*/
	function &getUserAnswers($survey_id)
	{
		global $ilDB;
		
		$answers = array();

		$result = $ilDB->queryF("SELECT svy_answer.* FROM svy_answer, svy_finished WHERE svy_finished.survey_fi = %s AND svy_answer.question_fi = %s AND svy_finished.finished_id = svy_answer.active_fi",
			array('integer','integer'),
			array($survey_id, $this->getId())
		);
		while ($row = $ilDB->fetchAssoc($result))
		{
			$answers[$row["active_fi"]] = $row["textanswer"];
		}
		return $answers;
	}

	/**
	* Import response data from the question import file
	*
	* @return array $a_data Array containing the response data
	* @access public
	*/
	function importResponses($a_data)
	{
		foreach ($a_data as $id => $data)
		{
			if ($data["maxlength"] > 0)
			{
				$this->setMaxChars($data["maxlength"]);
			}
			if ($data["rows"] > 0)
			{
				$this->setTextHeight($data["rows"]);
			}
			if ($data["columns"] > 0)
			{
				$this->setTextWidth($data["columns"]);
			}
		}
	}

	/**
	* Returns if the question is usable for preconditions
	*
	* @return boolean TRUE if the question is usable for a precondition, FALSE otherwise
	* @access public
	*/
	function usableForPrecondition()
	{
		return FALSE;
	}

	/**
	* Returns the width of the answer field
	*
	* @return integer The width of the answer field in characters
	* @access public
	*/
	function getTextWidth()
	{
		return ($this->textwidth) ? $this->textwidth : NULL;
	}
	
	/**
	* Returns the height of the answer field
	*
	* @return integer The height of the answer field in characters
	* @access public
	*/
	function getTextHeight()
	{
		return ($this->textheight) ? $this->textheight : NULL;
	}
	
	/**
	* Sets the width of the answer field
	*
	* @param integer $a_textwidth The width of the answer field in characters
	* @access public
	*/
	function setTextWidth($a_textwidth)
	{
		if ($a_textwidth < 1)
		{
			$this->textwidth = 50;
		}
		else
		{
			$this->textwidth = $a_textwidth;
		}
	}
	
	/**
	* Sets the height of the answer field
	*
	* @param integer $a_textheight The height of the answer field in characters
	* @access public
	*/
	function setTextHeight($a_textheight)
	{
		if ($a_textheight < 1)
		{
			$this->textheight = 5;
		}
		else
		{
			$this->textheight = $a_textheight;
		}
	}
}
?>