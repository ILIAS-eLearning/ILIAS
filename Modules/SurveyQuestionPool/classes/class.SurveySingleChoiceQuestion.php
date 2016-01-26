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

/**
* SingleChoice survey question
*
* The SurveySingleChoiceQuestion class defines and encapsulates basic methods and attributes
* for single choice survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestion
* @ingroup ModulesSurveyQuestionPool
*/
class SurveySingleChoiceQuestion extends SurveyQuestion 
{
/**
* Categories contained in this question
*
* @var array
*/
  var $categories;

/**
* SurveySingleChoiceQuestion constructor
*
* The constructor takes possible arguments an creates an instance of the SurveySingleChoiceQuestion object.
*
* @param string $title A title string to describe the question
* @param string $description A description string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @access public
*/
	function SurveySingleChoiceQuestion(
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
* @param integer $phrase_id The database id of the given phrase
* @result array All available categories
* @access public
*/
	function &getCategoriesForPhrase($phrase_id)
	{
		global $ilDB;
		$categories = array();
		$result = $ilDB->queryF("SELECT svy_category.* FROM svy_category, svy_phrase_cat WHERE svy_phrase_cat.category_fi = svy_category.category_id AND svy_phrase_cat.phrase_fi = %s ORDER BY svy_phrase_cat.sequence",
			array('integer'),
			array($phrase_id)
		);
		while ($row = $ilDB->fetchAssoc($result))
		{
			if (($row["defaultvalue"] == 1) and ($row["owner_fi"] == 0))
			{
				$categories[$row["category_id"]] = $this->lng->txt($row["title"]);
			}
			else
			{
				$categories[$row["category_id"]] = $row["title"];
			}
		}
		return $categories;
	}
	
/**
* Adds a phrase to the question
*
* @param integer $phrase_id The database id of the given phrase
* @access public
*/
	function addPhrase($phrase_id)
	{
		global $ilUser;
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT svy_category.* FROM svy_category, svy_phrase_cat WHERE svy_phrase_cat.category_fi = svy_category.category_id AND svy_phrase_cat.phrase_fi = %s AND (svy_category.owner_fi = 0 OR svy_category.owner_fi = %s) ORDER BY svy_phrase_cat.sequence",
			array('integer', 'integer'),
			array($phrase_id, $ilUser->getId())
		);
		while ($row = $ilDB->fetchAssoc($result))
		{
			$neutral = $row["neutral"];			
			if (($row["defaultvalue"] == 1) and ($row["owner_fi"] == 0))
			{
				$this->categories->addCategory($this->lng->txt($row["title"]), 0, $neutral);
			}
			else
			{
				$this->categories->addCategory($row["title"], 0, $neutral);
			}
		}
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
			return $ilDB->fetchAssoc($result);
		}
		else
		{
			return array();
		}
	}
	
/**
* Loads a SurveySingleChoiceQuestion object from the database
*
* @param integer $id The database id of the single choice survey question
* @access public
*/
	function loadFromDb($id) 
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT svy_question.*, " . $this->getAdditionalTableName() . ".* FROM svy_question LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = svy_question.question_id WHERE svy_question.question_id = %s",
			array('integer'),
			array($id)
		);
		if ($result->numRows() == 1) 
		{
			$data = $ilDB->fetchAssoc($result);
			$this->setId($data["question_id"]);
			$this->setTitle($data["title"]);
			$this->label = $data['label'];
			$this->setDescription($data["description"]);
			$this->setObjId($data["obj_fi"]);
			$this->setAuthor($data["author"]);
			$this->setOwner($data["owner_fi"]);
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->setQuestiontext(ilRTE::_replaceMediaObjectImageSrc($data["questiontext"], 1));
			$this->setObligatory($data["obligatory"]);
			$this->setComplete($data["complete"]);
			$this->setOriginalId($data["original_id"]);
			$this->setOrientation($data["orientation"]);

			$this->categories->flushCategories();
			$result = $ilDB->queryF("SELECT svy_variable.*, svy_category.title, svy_category.neutral FROM svy_variable, svy_category WHERE svy_variable.question_fi = %s AND svy_variable.category_fi = svy_category.category_id ORDER BY sequence ASC",
				array('integer'),
				array($id)
			);
			if ($result->numRows() > 0) 
			{
				while ($data = $ilDB->fetchAssoc($result)) 
				{
					$this->categories->addCategory($data["title"], $data["other"], $data["neutral"], null, ($data['scale']) ? $data['scale'] : ($data['sequence'] + 1));
				}
			}
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
		if (
			strlen($this->getTitle()) &&
			strlen($this->getAuthor()) &&
			strlen($this->getQuestiontext()) &&
			$this->categories->getCategoryCount()
		)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
/**
* Saves a SurveySingleChoiceQuestion object to a database
*
* @access public
*/
	function saveToDb($original_id = "")
	{
		global $ilDB;

		$affectedRows = parent::saveToDb($original_id);
		if ($affectedRows == 1) 
		{
			$affectedRows = $ilDB->manipulateF("DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
				array('integer'),
				array($this->getId())
			);
			$affectedRows = $ilDB->manipulateF("INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, orientation) VALUES (%s, %s)",
				array('integer', 'text'),
				array(
					$this->getId(), 
					$this->getOrientation()
				)
			);

			$this->saveMaterial();
			$this->saveCategoriesToDb();
		}
	}

	function saveCategoriesToDb()
	{
		global $ilDB;
		
		$affectedRows = $ilDB->manipulateF("DELETE FROM svy_variable WHERE question_fi = %s",
			array('integer'),
			array($this->getId())
		);

		for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
		{
			$cat = $this->categories->getCategory($i);
			$category_id = $this->saveCategoryToDb($cat->title, $cat->neutral);
			$next_id = $ilDB->nextId('svy_variable');
			$affectedRows = $ilDB->manipulateF("INSERT INTO svy_variable (variable_id, category_fi, question_fi, value1, other, sequence, scale, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
				array('integer','integer','integer','float','integer','integer', 'integer','integer'),
				array($next_id, $category_id, $this->getId(), ($i + 1), $cat->other, $i, ($cat->scale > 0) ? $cat->scale : null, time())
			);
		}
		$this->saveCompletionStatus();
	}

	/**
	* Returns an xml representation of the question
	*
	* @return string The xml representation of the question
	* @access public
	*/
	function toXML($a_include_header = TRUE, $obligatory_state = "")
	{
		include_once("./Services/Xml/classes/class.ilXmlWriter.php");
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
		if (strlen($this->label))
		{
			$attrs = array(
				"label" => $this->label,
			);
		}
		else
		{
			$attrs = array();
		}
		$a_xml_writer->xmlStartTag("questiontext", $attrs);
		$this->addMaterialTag($a_xml_writer, $this->getQuestiontext());
		$a_xml_writer->xmlEndTag("questiontext");

		$a_xml_writer->xmlStartTag("responses");

		for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
		{
			$attrs = array(
				"id" => $i
			);
			if (strlen($this->categories->getCategory($i)->other)) $attrs['other'] = $this->categories->getCategory($i)->other;
			if (strlen($this->categories->getCategory($i)->neutral)) $attrs['neutral'] = $this->categories->getCategory($i)->neutral;
			if (strlen($this->categories->getCategory($i)->label)) $attrs['label'] = $this->categories->getCategory($i)->label;
			if (strlen($this->categories->getCategory($i)->scale)) $attrs['scale'] = $this->categories->getCategory($i)->scale;
			$a_xml_writer->xmlStartTag("response_single", $attrs);
			$this->addMaterialTag($a_xml_writer, $this->categories->getCategory($i)->title);
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

	/**
	* Import additional meta data from the question import file. Usually
	* the meta data section is used to store question elements which are not
	* part of the standard XML schema.
	*
	* @return array $a_meta Array containing the additional meta data
	* @access public
	*/
	function importAdditionalMetadata($a_meta)
	{
		foreach ($a_meta as $key => $value)
		{
			switch ($value["label"])
			{
				case "orientation":
					$this->setOrientation($value["entry"]);
					break;
			}
		}
	}

/**
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
* @param array $phrases The database ids of the seleted phrases
* @param string $title The title of the default phrase
* @access public
*/
	function savePhrase($title)
	{
		global $ilUser;
		global $ilDB;

		$next_id = $ilDB->nextId('svy_phrase');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_phrase (phrase_id, title, defaultvalue, owner_fi, tstamp) VALUES (%s, %s, %s, %s, %s)",
			array('integer','text','text','integer','integer'),
			array($next_id, $title, 1, $ilUser->getId(), time())
		);
		$phrase_id = $next_id;
				
		$counter = 1;
	  foreach ($_SESSION['save_phrase_data'] as $data) 
		{
			$next_id = $ilDB->nextId('svy_category');
			$affectedRows = $ilDB->manipulateF("INSERT INTO svy_category (category_id, title, defaultvalue, owner_fi, tstamp, neutral) VALUES (%s, %s, %s, %s, %s, %s)",
				array('integer','text','text','integer','integer','text'),
				array($next_id, $data['answer'], 1, $ilUser->getId(), time(), $data['neutral'])
			);
			$category_id = $next_id;
			$next_id = $ilDB->nextId('svy_phrase_cat');
			$affectedRows = $ilDB->manipulateF("INSERT INTO svy_phrase_cat (phrase_category_id, phrase_fi, category_fi, sequence, other, scale) VALUES (%s, %s, %s, %s, %s, %s)",
				array('integer', 'integer', 'integer','integer', 'integer', 'integer'),
				array($next_id, $phrase_id, $category_id, $counter, ($data['other']) ? 1 : 0, $data['scale'])
			);
			$counter++;
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
		return "SurveySingleChoiceQuestion";
	}

	/**
	* Returns the name of the additional question data table in the database
	*
	* @return string The additional table name
	* @access public
	*/
	function getAdditionalTableName()
	{
		return "svy_qst_sc";
	}
	
	/**
	* Creates the user data of the svy_answer table from the POST data
	*
	* @return array User data according to the svy_answer table
	* @access public
	*/
	function &getWorkingDataFromUserInput($post_data)
	{
		$entered_value = $post_data[$this->getId() . "_value"];
		$data = array();
		if (strlen($entered_value))
		{
			array_push($data, array("value" => $entered_value, "textanswer" => $post_data[$this->getId() . '_' . $entered_value . '_other']));
		}
		for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
		{
			$cat = $this->categories->getCategory($i);
			if ($cat->other)
			{
				if ($i != $entered_value)
				{
					if (strlen($post_data[$this->getId() . "_" . $i . "_other"]))
					{
						array_push($data, array("value" => $i, "textanswer" => $post_data[$this->getId() . '_' . $i . '_other'], "uncheck" => true));
					}
				}
			}
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
		$entered_value = $post_data[$this->getId() . "_value"];
		
		if ((!$this->getObligatory($survey_id)) && (strlen($entered_value) == 0)) return "";
		
		if (strlen($entered_value) == 0) return $this->lng->txt("question_not_checked");

		for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
		{
			$cat = $this->categories->getCategory($i);
			if ($cat->other)
			{
				if ($i == $entered_value)
				{
					if (array_key_exists($this->getId() . "_" . $entered_value . "_other", $post_data) && !strlen($post_data[$this->getId() . "_" . $entered_value . "_other"]))
					{
						return $this->lng->txt("question_mr_no_other_answer");
					}
				}
				else
				{
					if (strlen($post_data[$this->getId() . "_" . $i . "_other"]))
					{
						return $this->lng->txt("question_sr_no_other_answer_checked");
					}
				}
			}
		}

		return "";
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
		$category = rand(0, $this->categories->getCategoryCount()-1);
		$cat = $this->categories->getCategory($category);
		$next_id = $ilDB->nextId('svy_answer');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_answer (answer_id, question_fi, active_fi, value, textanswer, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
			array('integer','integer','integer','float','text','integer'),
			array($next_id, $this->getId(), $active_id, $category, ($cat->other) ? "Random Data" : null, time())
		);
	}

	function saveUserInput($post_data, $active_id, $a_return = false)
	{
		global $ilDB;

		$entered_value = $post_data[$this->getId() . "_value"];
		
		if($a_return)
		{
			return array(array("value"=>$entered_value,
				"textanswer"=>$post_data[$this->getId() . "_" . $entered_value . "_other"]));
		}
		if (strlen($entered_value) == 0) return;
		
		$next_id = $ilDB->nextId('svy_answer');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_answer (answer_id, question_fi, active_fi, value, textanswer, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
			array('integer','integer','integer','float','text','integer'),
			array($next_id, $this->getId(), $active_id, (strlen($entered_value)) ? $entered_value : NULL, ($post_data[$this->getId() . "_" . $entered_value . "_other"]) ? $post_data[$this->getId() . "_" . $entered_value . "_other"] : null, time())
		);
	}
	
	function &getCumulatedResults($survey_id, $nr_of_users, $finished_ids)
	{
		global $ilDB;
		
		$question_id = $this->getId();
		
		$result_array = array();
		$cumulated = array();
		
		$sql = "SELECT svy_answer.* FROM svy_answer".
			" JOIN svy_finished ON (svy_finished.finished_id = svy_answer.active_fi)".
			" WHERE svy_answer.question_fi = ".$ilDB->quote($question_id, "integer").
			" AND svy_finished.survey_fi = ".$ilDB->quote($survey_id, "integer");		
		if($finished_ids)
		{
			$sql .= " AND ".$ilDB->in("svy_finished.finished_id", $finished_ids, "", "integer");
		}

		$result = $ilDB->query($sql);		
		while ($row = $ilDB->fetchAssoc($result))
		{
			$cumulated[$row["value"]]++;
			
			// add text value to result array
			if ($row["textanswer"])
			{
				$result_array["textanswers"][$row["value"]][] = $row["textanswer"];
			}
		}
		// sort textanswers by value
		if (is_array($result_array["textanswers"]))
		{
			ksort($result_array["textanswers"], SORT_NUMERIC);
		}
		asort($cumulated, SORT_NUMERIC);
		end($cumulated);
		$numrows = $result->numRows();
		$result_array["USERS_ANSWERED"] = $result->numRows();
		$result_array["USERS_SKIPPED"] = $nr_of_users - $result->numRows();

		if(sizeof($cumulated))
		{
			$prefix = "";
			if (strcmp(key($cumulated), "") != 0)
			{
				$prefix = (key($cumulated)+1) . " - ";
			}
			$category = $this->categories->getCategoryForScale(key($cumulated)+1);
			$result_array["MODE"] =  $prefix . $category->title;
			$result_array["MODE_VALUE"] =  key($cumulated)+1;
			$result_array["MODE_NR_OF_SELECTIONS"] = $cumulated[key($cumulated)];
		}
		for ($key = 0; $key < $this->categories->getCategoryCount(); $key++)
		{
			$cat = $this->categories->getCategory($key);
			$percentage = 0;
			if ($numrows > 0)
			{
				$percentage = (float)((int)$cumulated[$cat->scale-1]/$numrows);
			}
			if ($cat->other)
			{
				$result_array["variables"][$key] = array("title" => (strlen($cat->title)) ? $cat->title : $this->lng->txt('other_answer'), "selected" => (int)$cumulated[$cat->scale-1], "percentage" => $percentage);
			}
			else
			{
				$result_array["variables"][$key] = array("title" => $cat->title, "selected" => (int)$cumulated[$cat->scale-1], "percentage" => $percentage);
			}
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
					$median_value = $median_value . "<br />" . "(" . $this->lng->txt("median_between") . " " . (floor($median_value)) . "-" . $this->categories->getCategory((int)floor($median_value)-1)->title . " " . $this->lng->txt("and") . " " . (ceil($median_value)) . "-" . $this->categories->getCategory((int)ceil($median_value)-1)->title . ")";
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
		$result_array["QUESTION_TYPE"] = "SurveySingleChoiceQuestion";
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
	function setExportDetailsXLS(&$workbook, &$format_title, &$format_bold, &$eval_data, $export_label)
	{
		include_once ("./Services/Excel/classes/class.ilExcelUtils.php");
		$worksheet =& $workbook->addWorksheet();
		$rowcounter = 0;
		switch ($export_label)
		{
			case 'label_only':
				$worksheet->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("label")), $format_bold);
				$worksheet->writeString(0, 1, ilExcelUtils::_convert_text($this->label));
				break;
			case 'title_only':
				$worksheet->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("title")), $format_bold);
				$worksheet->writeString(0, 1, ilExcelUtils::_convert_text($this->getTitle()));
				break;
			default:
				$worksheet->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("title")), $format_bold);
				$worksheet->writeString(0, 1, ilExcelUtils::_convert_text($this->getTitle()));
				$rowcounter++;
				$worksheet->writeString($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("label")), $format_bold);
				$worksheet->writeString($rowcounter, 1, ilExcelUtils::_convert_text($this->label));
				break;
		}
		$rowcounter++;
		$worksheet->writeString($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("question")), $format_bold);
		$worksheet->writeString($rowcounter, 1, ilExcelUtils::_convert_text($this->getQuestiontext()));
		$rowcounter++;
		$worksheet->writeString($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("question_type")), $format_bold);
		$worksheet->writeString($rowcounter, 1, ilExcelUtils::_convert_text($this->lng->txt($this->getQuestionType())));
		$rowcounter++;
		$worksheet->writeString($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("users_answered")), $format_bold);
		$worksheet->write($rowcounter, 1, $eval_data["USERS_ANSWERED"]);
		$rowcounter++;
		$worksheet->writeString($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("users_skipped")), $format_bold);
		$worksheet->write($rowcounter, 1, $eval_data["USERS_SKIPPED"]);
		$rowcounter++;

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
		$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($this->lng->txt("svy_fraction_of_selections")), $format_title);

		foreach ($eval_data["variables"] as $key => $value)
		{
			$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($value["title"]));
			$category = $this->categories->getCategory($key);
			$worksheet->write($rowcounter, 2, $category->scale);
			$worksheet->write($rowcounter, 3, ilExcelUtils::_convert_text($value["selected"]));
			$worksheet->write($rowcounter++, 4, ilExcelUtils::_convert_text($value["percentage"]), $format_percent);
		}
		
		// add text answers to detailed results
		if (is_array($eval_data["textanswers"]))
		{
			$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("freetext_answers")), $format_bold);
			$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($this->lng->txt("title")), $format_title);
			$worksheet->write($rowcounter++, 2, ilExcelUtils::_convert_text($this->lng->txt("answer")), $format_title);
			
			foreach ($eval_data["textanswers"] as $key => $answers)
			{
				$title = $eval_data["variables"][$key]["title"];
				foreach ($answers as $answer)
				{
					$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($title));
					$worksheet->write($rowcounter++, 2, ilExcelUtils::_convert_text($answer));
				}
			}
		}			
	}
	
	/**
	* overwritten addUserSpecificResultsExportTitles
	* 
	* Adds the entries for the title row of the user specific results
	*
	* @param array $a_array An array which is used to append the title row entries
	* @access public
	*/
	function addUserSpecificResultsExportTitles(&$a_array, $a_use_label = false, $a_substitute = true)
	{
		$title = parent::addUserSpecificResultsExportTitles($a_array, $a_use_label, $a_substitute);

		// optionally add header for text answer
		for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
		{
			$cat = $this->categories->getCategory($i);
			if ($cat->other)
			{
				if(!$a_use_label || $a_substitute)
				{
					array_push($a_array, $title. ' - '. $this->lng->txt('other'));	
				}
				else
				{
					array_push($a_array, "");
				}
				break;	
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
		// check if text answer column is needed
		$other = false;
		for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
		{
			$cat = $this->categories->getCategory($i);
			if ($cat->other)
			{
				$other = true;	
				break;	
			}
		}
		
		if (count($resultset["answers"][$this->getId()]))
		{
			foreach ($resultset["answers"][$this->getId()] as $key => $answer)
			{
				array_push($a_array, $answer["value"]+1);
				
				// add the text answer from the selected option
				if ($other)
				{
					array_push($a_array, $answer["textanswer"]);
				}
			}
		}
		else
		{
			array_push($a_array, $this->getSkippedValue());
			
			if ($other)
			{
				array_push($a_array, "");
			}
		}
	}

	/**
	* Returns an array containing all answers to this question in a given survey
	*
	* @param integer $survey_id The database ID of the survey
	* @return array An array containing the answers to the question. The keys are either the user id or the anonymous id
	* @access public
	*/
	function &getUserAnswers($survey_id, $finished_ids)
	{
		global $ilDB;
		
		$answers = array();
		
		$sql = "SELECT svy_answer.* FROM svy_answer".
			" JOIN svy_finished ON (svy_finished.finished_id = svy_answer.active_fi)".
			" WHERE svy_answer.question_fi = ".$ilDB->quote($this->getId(), "integer").
			" AND svy_finished.survey_fi = ".$ilDB->quote($survey_id, "integer");		
		if($finished_ids)
		{
			$sql .= " AND ".$ilDB->in("svy_finished.finished_id", $finished_ids, "", "integer");
		}
		
		$result = $ilDB->query($sql);		
		while ($row = $ilDB->fetchAssoc($result))
		{
			$category = $this->categories->getCategoryForScale($row["value"]+1);
			$title = $row["value"] + 1 . " - " . $category->title;
			if ($category->other) $title .= ": " . $row["textanswer"];
			$answers[$row["active_fi"]] = $title;
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
			$categorytext = "";
			foreach ($data["material"] as $material)
			{
				$categorytext .= $material["text"];
			}
			$this->categories->addCategory(
				$categorytext, 
				strlen($data['other']) ? $data['other'] : 0, 
				strlen($data['neutral']) ? $data['neutral'] : 0, 
				strlen($data['label']) ? $data['label'] : null, 
				strlen($data['scale']) ? $data['scale'] : null
			);
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
		return TRUE;
	}
	
	/**
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
	* Returns the options for preconditions
	*
	* @return array
	*/
	public function getPreconditionOptions()
	{
		global $lng;
		
		$options = array();
		for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
		{
			$category = $this->categories->getCategory($i);			
			$options[$category->scale-1] = $category->scale . " - " . $category->title;
		}
		return $options;
	}

	/**
	* Creates a form property for the precondition value
	*
	* @return The ILIAS form element
	* @access public
	*/
	public function getPreconditionSelectValue($default = "", $title, $variable)
	{
		include_once "./Services/Form/classes/class.ilSelectInputGUI.php";
		$step3 = new ilSelectInputGUI($title, $variable);
		$options = $this->getPreconditionOptions();
		$step3->setOptions($options);
		$step3->setValue($default);
		return $step3;
	}

	/**
	* Returns the output for a precondition value
	*
	* @param string $value The precondition value
	* @return string The output of the precondition value
	* @access public
	*/
	function getPreconditionValueOutput($value)
	{
		$category = $this->categories->getCategory($value);
		
		// #17895 - see getPreconditionOptions()
		return $category->scale . 
			" - " . 
			((strlen($category->title)) ? $category->title : $this->lng->txt('other_answer'));
	}

	public function getCategories()
	{
		return $this->categories;
	}

}
?>