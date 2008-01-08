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
* Ordinal survey question
*
* The SurveyOrdinalQuestion class defines and encapsulates basic methods and attributes
* for ordinal survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestion
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyOrdinalQuestion extends SurveyQuestion 
{
/**
* Categories contained in this question
*
* Categories contained in this question
*
* @var array
*/
  var $categories;

/**
* SurveyOrdinalQuestion constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyOrdinalQuestion object.
*
* @param string $title A title string to describe the question
* @param string $description A description string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @access public
*/
  function SurveyOrdinalQuestion(
    $title = "",
    $description = "",
    $author = "",
		$questiontext = "",
    $owner = -1,
		$orientation = 1
  )

  {
		$this->SurveyQuestion($title, $description, $author, $questiontext, $owner);
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyCategories.php";
		$this->orientation = $orientation;
		$this->categories = new SurveyCategories();
	}
	
/**
* Gets the available categories for a given phrase
*
* Gets the available categories for a given phrase
*
* @param integer $phrase_id The database id of the given phrase
* @result array All available categories
* @access public
*/
	function &getCategoriesForPhrase($phrase_id)
	{
		global $ilDB;
		$categories = array();
    $query = sprintf("SELECT survey_category.* FROM survey_category, survey_phrase_category WHERE survey_phrase_category.category_fi = survey_category.category_id AND survey_phrase_category.phrase_fi = %s ORDER BY survey_phrase_category.sequence",
      $ilDB->quote($phrase_id)
    );
    $result = $ilDB->query($query);
		while ($row = $result->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			if (($row->defaultvalue == 1) and ($row->owner_fi == 0))
			{
				$categories[$row->category_id] = $this->lng->txt($row->title);
			}
			else
			{
				$categories[$row->category_id] = $row->title;
			}
		}
		return $categories;
	}
	
/**
* Adds a phrase to the question
*
* Adds a phrase to the question
*
* @param integer $phrase_id The database id of the given phrase
* @access public
*/
	function addPhrase($phrase_id)
	{
		global $ilUser;
		global $ilDB;
		
    $query = sprintf("SELECT survey_category.* FROM survey_category, survey_phrase_category WHERE survey_phrase_category.category_fi = survey_category.category_id AND survey_phrase_category.phrase_fi = %s AND (survey_category.owner_fi = 0 OR survey_category.owner_fi = %s) ORDER BY survey_phrase_category.sequence",
      $ilDB->quote($phrase_id),
			$ilDB->quote($ilUser->id)
    );
    $result = $ilDB->query($query);
		while ($row = $result->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			if (($row->defaultvalue == 1) and ($row->owner_fi == 0))
			{
				$this->categories->addCategory($this->lng->txt($row->title));
			}
			else
			{
				$this->categories->addCategory($row->title);
			}
		}
	}
	
	/**
	* Returns the question data fields from the database
	*
	* Returns the question data fields from the database
	*
	* @param integer $id The question ID from the database
	* @return array Array containing the question fields and data from the database
	* @access public
	*/
	function _getQuestionDataArray($id)
	{
		global $ilDB;
		
    $query = sprintf("SELECT survey_question.*, survey_question_ordinal.* FROM survey_question, survey_question_ordinal WHERE survey_question.question_id = %s AND survey_question.question_id = survey_question_ordinal.question_fi",
      $ilDB->quote($id)
    );
    $result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			return $result->fetchRow(MDB2_FETCHMODE_ASSOC);
		}
		else
		{
			return array();
		}
	}
	
/**
* Loads a SurveyOrdinalQuestion object from the database
*
* Loads a SurveyOrdinalQuestion object from the database
*
* @param integer $id The database id of the ordinal survey question
* @access public
*/
	function loadFromDb($id) 
	{
		global $ilDB;
		$query = sprintf("SELECT survey_question.*, survey_question_ordinal.* FROM survey_question, survey_question_ordinal WHERE survey_question.question_id = %s AND survey_question.question_id = survey_question_ordinal.question_fi",
			$ilDB->quote($id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1) 
		{
			$data = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
			$this->id = $data->question_id;
			$this->title = $data->title;
			$this->description = $data->description;
			$this->obj_id = $data->obj_fi;
			$this->orientation = $data->orientation;
			$this->author = $data->author;
			$this->owner = $data->owner_fi;
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->questiontext = ilRTE::_replaceMediaObjectImageSrc($data->questiontext, 1);
			$this->obligatory = $data->obligatory;
			$this->complete = $data->complete;
			$this->original_id = $data->original_id;
			// loads materials uris from database
			$this->loadMaterialFromDb($id);

			$this->categories->flushCategories();
			$query = sprintf("SELECT survey_variable.*, survey_category.title FROM survey_variable, survey_category WHERE survey_variable.question_fi = %s AND survey_variable.category_fi = survey_category.category_id ORDER BY sequence ASC",
				$ilDB->quote($id)
			);
			$result = $ilDB->query($query);
			if ($result->numRows() > 0) 
			{
				while ($data = $result->fetchRow(MDB2_FETCHMODE_OBJECT)) 
				{
					$this->categories->addCategory($data->title);
				}
			}
		}
		parent::loadFromDb($id);
	}

/**
* Returns true if the question is complete for use
*
* Returns true if the question is complete for use
*
* @result boolean True if the question is complete for use, otherwise false
* @access public
*/
	function isComplete()
	{
		if ($this->title and $this->author and $this->questiontext and $this->categories->getCategoryCount())
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
/**
* Saves a SurveyOrdinalQuestion object to a database
*
* Saves a SurveyOrdinalQuestion object to a database
*
* @access public
*/
  function saveToDb($original_id = "", $withanswers = true)
  {
		global $ilDB;
		$complete = 0;
		if ($this->isComplete()) {
			$complete = 1;
		}
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
		ilRTE::_cleanupMediaObjectUsage($this->questiontext, "spl:html",
			$this->getId());

    if ($this->id == -1) 
		{
      // Write new dataset
      $now = getdate();
      $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
      $query = sprintf("INSERT INTO survey_question (question_id, questiontype_fi, obj_fi, owner_fi, title, description, author, questiontext, obligatory, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($this->getQuestionTypeID()),
				$ilDB->quote($this->obj_id),
				$ilDB->quote($this->owner),
				$ilDB->quote($this->title),
				$ilDB->quote($this->description),
				$ilDB->quote($this->author),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->questiontext, 0)),
				$ilDB->quote(sprintf("%d", $this->obligatory)),
				$ilDB->quote("$complete"),
				$ilDB->quote($created),
				$original_id
      );
      $result = $ilDB->query($query);
      if ($result == DB_OK) 
			{
        $this->id = $ilDB->getLastInsertId();
				$query = sprintf("INSERT INTO survey_question_ordinal (question_fi, orientation) VALUES (%s, %s)",
					$ilDB->quote($this->id . ""),
					$ilDB->quote(sprintf("%d", $this->orientation))
				);
				$ilDB->query($query);
      }
    } 
		else 
		{
      // update existing dataset
      $query = sprintf("UPDATE survey_question SET title = %s, description = %s, author = %s, questiontext = %s, obligatory = %s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->title),
				$ilDB->quote($this->description),
				$ilDB->quote($this->author),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->questiontext, 0)),
				$ilDB->quote(sprintf("%d", $this->obligatory)),
				$ilDB->quote("$complete"),
				$ilDB->quote($this->id)
      );
      $result = $ilDB->query($query);
			$query = sprintf("UPDATE survey_question_ordinal SET orientation = %s WHERE question_fi = %s",
				$ilDB->quote(sprintf("%d", $this->orientation)),
				$ilDB->quote($this->id . "")
			);
			$result = $ilDB->query($query);
    }
    if ($result == DB_OK) 
		{
      // saving material uris in the database
      $this->saveMaterialsToDb();
			if ($withanswers)
			{
				$this->saveCategoriesToDb();
			}
    }
		parent::saveToDb($original_id);
  }

	function saveCategoriesToDb()
	{
		// save categories
		
		// delete existing category relations
		$query = sprintf("DELETE FROM survey_variable WHERE question_fi = %s",
			$this->ilias->db->quote($this->id)
		);
		$result = $this->ilias->db->query($query);
		// create new category relations
		for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
		{
			$category_id = $this->saveCategoryToDb($this->categories->getCategory($i));
			$query = sprintf("INSERT INTO survey_variable (variable_id, category_fi, question_fi, value1, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
				$this->ilias->db->quote($category_id . ""),
				$this->ilias->db->quote($this->id . ""),
				$this->ilias->db->quote(($i + 1) . ""),
				$this->ilias->db->quote($i . "")
			);
			$answer_result = $this->ilias->db->query($query);
		}
		$this->saveCompletionStatus();
	}

	/**
	* Returns an xml representation of the question
	*
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

		for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
		{
			$attrs = array(
				"id" => $i
			);
			$a_xml_writer->xmlStartTag("response_single", $attrs);
			$this->addMaterialTag($a_xml_writer, $this->categories->getCategory($i));
			$a_xml_writer->xmlEndTag("response_single");
		}

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

		$a_xml_writer->xmlStartTag("metadata");
		$a_xml_writer->xmlStartTag("metadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "orientation");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getOrientation());
		$a_xml_writer->xmlEndTag("metadatafield");
		$a_xml_writer->xmlEndTag("metadata");

		$a_xml_writer->xmlEndTag("question");
	}

	function syncWithOriginal()
	{
		global $ilDB;
		if ($this->original_id)
		{
			$complete = 0;
			if ($this->isComplete()) {
				$complete = 1;
			}
			$query = sprintf("UPDATE survey_question SET title = %s, description = %s, author = %s, questiontext = %s, obligatory = %s, complete = %s WHERE question_id = %s",
				$ilDB->quote($this->title . ""),
				$ilDB->quote($this->description . ""),
				$ilDB->quote($this->author . ""),
				$ilDB->quote($this->questiontext . ""),
				$ilDB->quote(sprintf("%d", $this->obligatory) . ""),
				$ilDB->quote($complete . ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("UPDATE survey_question_ordinal SET orientation = %s WHERE question_fi = %s",
				$ilDB->quote($this->getOrientation() . ""),
				$ilDB->quote($this->original_id . "")
			);
			$result = $ilDB->query($query);
			if ($result == DB_OK) {
				// save categories
				
				// delete existing category relations
				$query = sprintf("DELETE FROM survey_variable WHERE question_fi = %s",
					$ilDB->quote($this->original_id . "")
				);
				$result = $ilDB->query($query);
				// create new category relations
				for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
				{
					$category_id = $this->saveCategoryToDb($this->categories->getCategory($i));
					$query = sprintf("INSERT INTO survey_variable (variable_id, category_fi, question_fi, value1, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
						$ilDB->quote($category_id . ""),
						$ilDB->quote($this->original_id . ""),
						$ilDB->quote(($i + 1) . ""),
						$ilDB->quote($i . "")
					);
					$answer_result = $ilDB->query($query);
				}
			}
		}
		parent::syncWithOriginal();
	}

/**
* Adds standard numbers as categories
*
* Adds standard numbers as categories
*
* @param integer $lower_limit The lower limit
* @param integer $upper_limit The upper limit
* @access public
*/
	function addStandardNumbers($lower_limit, $upper_limit)
	{
		for ($i = $lower_limit; $i <= $upper_limit; $i++)
		{
			$this->categories->addCategory($i);
		}
	}

/**
* Saves a set of categories to a default phrase
*
* Saves a set of categories to a default phrase
*
* @param array $phrases The database ids of the seleted phrases
* @param string $title The title of the default phrase
* @access public
*/
	function savePhrase($phrases, $title)
	{
		global $ilUser;
		global $ilDB;
		
		$query = sprintf("INSERT INTO survey_phrase (phrase_id, title, defaultvalue, owner_fi, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
			$ilDB->quote($title . ""),
			$ilDB->quote("1"),
			$ilDB->quote($ilUser->id . "")
		);
    $result = $ilDB->query($query);
		$phrase_id = $ilDB->getLastInsertId();
				
		$counter = 1;
	  foreach ($phrases as $category) 
		{
			$query = sprintf("INSERT INTO survey_category (category_id, title, defaultvalue, owner_fi, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
				$ilDB->quote($this->categories->getCategory($category) . ""),
				$ilDB->quote("1"),
				$ilDB->quote($ilUser->id . "")
			);
			$result = $ilDB->query($query);
			$category_id = $ilDB->getLastInsertId();
			$query = sprintf("INSERT INTO survey_phrase_category (phrase_category_id, phrase_fi, category_fi, sequence) VALUES (NULL, %s, %s, %s)",
				$ilDB->quote($phrase_id . ""),
				$ilDB->quote($category_id . ""),
				$ilDB->quote($counter . "")
			);
			$result = $ilDB->query($query);
			$counter++;
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
		return "SurveyOrdinalQuestion";
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
		return "survey_question_ordinal";
	}
	
	/**
	* Creates the user data of the survey_answer table from the POST data
	*
	* Creates the user data of the survey_answer table from the POST data
	*
	* @return array User data according to the survey_answer table
	* @access public
	*/
	function &getWorkingDataFromUserInput($post_data)
	{
		$entered_value = $post_data[$this->getId() . "_value"];
		$data = array();
		if (strlen($entered_value))
		{
			array_push($data, array("value" => $entered_value));
		}
		return $data;
	}

	/**
	* Checks the input of the active user for obligatory status
	* and entered values
	*
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
		$entered_value = $post_data[$this->getId() . "_value"];
		
		if ((!$this->getObligatory($survey_id)) && (strlen($entered_value) == 0)) return "";
		
		if (strlen($entered_value) == 0) return $this->lng->txt("ordinal_question_not_checked");

		return "";
	}

	function saveUserInput($post_data, $active_id)
	{
		global $ilDB;

		$entered_value = $post_data[$this->getId() . "_value"];
		if (strlen($entered_value) == 0) return;
		$entered_value = $ilDB->quote($entered_value . "");
		$query = sprintf("INSERT INTO survey_answer (answer_id, question_fi, active_fi, value, textanswer, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
			$ilDB->quote($this->getId() . ""),
			$ilDB->quote($active_id . ""),
			$entered_value,
			"NULL"
		);
		$result = $ilDB->query($query);
	}
	
	function &getCumulatedResults($survey_id, $nr_of_users)
	{
		global $ilDB;
		
		$question_id = $this->getId();
		
		$result_array = array();
		$cumulated = array();

		$query = sprintf("SELECT survey_answer.* FROM survey_answer, survey_finished WHERE survey_answer.question_fi = %s AND survey_finished.survey_fi = %s AND survey_finished.finished_id = survey_answer.active_fi",
			$ilDB->quote($question_id),
			$ilDB->quote($survey_id)
		);
		$result = $ilDB->query($query);
		
		while ($row = $result->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$cumulated["$row->value"]++;
		}
		asort($cumulated, SORT_NUMERIC);
		end($cumulated);
		$numrows = $result->numRows();
		$result_array["USERS_ANSWERED"] = $result->numRows();
		$result_array["USERS_SKIPPED"] = $nr_of_users - $result->numRows();

		$prefix = "";
		if (strcmp(key($cumulated), "") != 0)
		{
			$prefix = (key($cumulated)+1) . " - ";
		}
		$result_array["MODE"] =  $prefix . $this->categories->getCategory(key($cumulated));
		$result_array["MODE_VALUE"] =  key($cumulated)+1;
		$result_array["MODE_NR_OF_SELECTIONS"] = $cumulated[key($cumulated)];
		for ($key = 0; $key < $this->categories->getCategoryCount(); $key++)
		{
			$percentage = 0;
			if ($numrows > 0)
			{
				$percentage = (float)((int)$cumulated[$key]/$numrows);
			}
			$result_array["variables"][$key] = array("title" => $this->categories->getCategory($key), "selected" => (int)$cumulated[$key], "percentage" => $percentage);
		}
		ksort($cumulated, SORT_NUMERIC);
		$median = array();
		$total = 0;
		foreach ($cumulated as $value => $key)
		{
			$total += $key;
			for ($i = 0; $i < $key; $i++)
			{
				array_push($median, $value+1);
			}
		}
		if ($total > 0)
		{
			if (($total % 2) == 0)
			{
				$median_value = 0.5 * ($median[($total/2)-1] + $median[($total/2)]);
				if (round($median_value) != $median_value)
				{
					$median_value = $median_value . "<br />" . "(" . $this->lng->txt("median_between") . " " . (floor($median_value)) . "-" . $this->categories->getCategory((int)floor($median_value)-1) . " " . $this->lng->txt("and") . " " . (ceil($median_value)) . "-" . $this->categories->getCategory((int)ceil($median_value)-1) . ")";
				}
			}
			else
			{
				$median_value = $median[(($total+1)/2)-1];
			}
		}
		else
		{
			$median_value = "";
		}
		$result_array["ARITHMETIC_MEAN"] = "";
		$result_array["MEDIAN"] = $median_value;
		$result_array["QUESTION_TYPE"] = "SurveyOrdinalQuestion";
		return $result_array;
	}
	
	/**
	* Creates an Excel worksheet for the detailed cumulated results of this question
	*
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

		preg_match("/(.*?)\s+-\s+(.*)/", $eval_data["MODE"], $matches);
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode")), $format_bold);
		$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($matches[1]));
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_text")), $format_bold);
		$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($matches[2]));
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_nr_of_selections")), $format_bold);
		$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval_data["MODE_NR_OF_SELECTIONS"]));
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("median")), $format_bold);
		$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text(str_replace("<br />", " ", $eval_data["MEDIAN"])));
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("categories")), $format_bold);
		$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($this->lng->txt("title")), $format_title);
		$worksheet->write($rowcounter, 2, ilExcelUtils::_convert_text($this->lng->txt("value")), $format_title);
		$worksheet->write($rowcounter, 3, ilExcelUtils::_convert_text($this->lng->txt("category_nr_selected")), $format_title);
		$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($this->lng->txt("percentage_of_selections")), $format_title);

		foreach ($eval_data["variables"] as $key => $value)
		{
			$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($value["title"]));
			$worksheet->write($rowcounter, 2, $key+1);
			$worksheet->write($rowcounter, 3, ilExcelUtils::_convert_text($value["selected"]));
			$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($value["percentage"]), $format_percent);
		}
	}

	/**
	* Adds the values for the user specific results export for a given user
	*
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
				array_push($a_array, $answer["value"]+1);
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

		$query = sprintf("SELECT survey_answer.* FROM survey_answer, survey_finished WHERE survey_finished.survey_fi = %s AND survey_answer.question_fi = %s AND survey_finished.finished_id = survey_answer.active_fi",
			$ilDB->quote($survey_id),
			$ilDB->quote($this->getId())
		);
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$category = $this->categories->getCategory($row["value"]);
			$answers[$row["active_fi"]] = $row["value"] + 1 . " - " . $category;
		}
		return $answers;
	}

	/**
	* Import response data from the question import file
	*
	* Import response data from the question import file
	*
	* @return array $a_data Array containing the response data
	* @access public
	*/
	function importResponses($a_data)
	{
		foreach ($a_data as $id => $data)
		{
			$categorytext = "";
			foreach ($data["material"] as $material)
			{
				$categorytext .= $material["text"];
			}
			$this->categories->addCategory($categorytext);
		}
	}

	/**
	* Returns if the question is usable for preconditions
	*
	* Returns if the question is usable for preconditions
	*
	* @return boolean TRUE if the question is usable for a precondition, FALSE otherwise
	* @access public
	*/
	function usableForPrecondition()
	{
		return TRUE;
	}
	
	/**
	* Returns the available relations for the question
	*
	* Returns the available relations for the question
	*
	* @return array An array containing the available relations
	* @access public
	*/
	function getAvailableRelations()
	{
		return array("<", "<=", "=", "<>", ">=", ">");
	}

	/**
	* Creates a value selection for preconditions
	*
	* Creates a value selection for preconditions
	*
	* @return The HTML code for the precondition value selection
	* @access public
	*/
	function getPreconditionSelectValue($default = "")
	{
		global $lng;
		
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_svy_svy_precondition_select_value_combobox.html", TRUE, TRUE, "Modules/Survey");
		for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
		{
			$template->setCurrentBlock("option_v");
			$template->setVariable("OPTION_VALUE", $i);
			$template->setVariable("OPTION_TEXT", ($i+1) . " - " . $this->categories->getCategory($i));
			if ($i == $default)
			{
				$template->setVariable("OPTION_CHECKED", " selected=\"selected\"");
			}
			$template->parseCurrentBlock();
		}
		$template->setVariable("SELECT_VALUE", $lng->txt("step") . " 3: " . $lng->txt("select_value"));
		return $template->get();
	}

	/**
	* Returns the output for a precondition value
	*
	* Returns the output for a precondition value
	*
	* @param string $value The precondition value
	* @return string The output of the precondition value
	* @access public
	*/
	function getPreconditionValueOutput($value)
	{
		return ($value + 1) . " - " . $this->categories->getCategory($value);
	}

/**
* Creates an image visualising the results of the question
*
* Creates an image visualising the results of the question
*
* @param integer $survey_id The database ID of the survey
* @param string $type An additional parameter to allow to draw more than one chart per question. Must be interpreted by the question. Default is an empty string
* @return binary Image with the visualisation
* @access private
*/
	function outChart($survey_id, $type = "")
	{
		if (count($this->cumulated) == 0)
		{
			include_once "./Modules/Survey/classes/class.ilObjSurvey.php";
			$nr_of_users = ilObjSurvey::_getNrOfParticipants($survey_id);
			$this->cumulated =& $this->getCumulatedResults($survey_id, $nr_of_users);
		}
		
		foreach ($this->cumulated["variables"] as $key => $value)
		{
			foreach ($value as $key2 => $value2)
			{
				$this->cumulated["variables"][$key][$key2] = utf8_decode($value2);
			}
		}
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyChart.php";
		$b1 = new SurveyChart("bars",400,250,utf8_decode($this->getTitle()),utf8_decode($this->lng->txt("answers")),utf8_decode($this->lng->txt("users_answered")),$this->cumulated["variables"]);
	}
}
?>
