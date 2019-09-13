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
* MultipleChoice survey question
*
* The SurveyMultipleChoiceQuestion class defines and encapsulates basic methods and attributes
* for multiple choice survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestion
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyMultipleChoiceQuestion extends SurveyQuestion 
{
	/**
	 * @var ilDB
	 */
	protected $db;

/**
* Categories contained in this question
*
* @var array
*/
	var $categories;

/**
* The constructor takes possible arguments an creates an instance of the SurveyMultipleChoiceQuestion object.
*
* @param string $title A title string to describe the question
* @param string $description A description string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @access public
*/
	function __construct($title = "", $description = "", $author = "", $questiontext = "", $owner = -1, $orientation = 0)
	{
		global $DIC;

		$this->db = $DIC->database();
		$this->lng = $DIC->language();
		parent::__construct($title, $description, $author, $questiontext, $owner);
		
		$this->orientation = $orientation;
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyCategories.php";
		$this->categories = new SurveyCategories();
	}
	
	/**
	* Returns the question data fields from the database
	*
	* @param integer $id The question ID from the database
	* @return array Array containing the question fields and data from the database
	* @access public
	*/
	function getQuestionDataArray($id)
	{
		$ilDB = $this->db;
		
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
* Loads a SurveyMultipleChoiceQuestion object from the database
*
* @param integer $id The database id of the multiple choice survey question
* @access public
*/
	function loadFromDb($id) 
	{
		$ilDB = $this->db;

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
			$this->use_min_answers = ($data['use_min_answers']) ? true : false;
			$this->nr_min_answers = $data['nr_min_answers'];
			$this->nr_max_answers = $data['nr_max_answers'];

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
* Saves a SurveyMultipleChoiceQuestion object to a database
*
* @access public
*/
  function saveToDb($original_id = "")
  {
		$ilDB = $this->db;

		$affectedRows = parent::saveToDb($original_id);
		if ($affectedRows == 1) 
		{
			$affectedRows = $ilDB->manipulateF("DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
				array('integer'),
				array($this->getId())
			);
			$affectedRows = $ilDB->manipulateF("INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, orientation, use_min_answers, nr_min_answers, nr_max_answers) VALUES (%s, %s, %s, %s, %s)",
				array('integer', 'text', 'integer', 'integer', 'integer'),
				array(
					$this->getId(), 
					$this->getOrientation(),
					($this->use_min_answers) ? 1 : 0,
					($this->nr_min_answers > 0) ? $this->nr_min_answers : null,
					($this->nr_max_answers > 0) ? $this->nr_max_answers : null
				)
			);

			// saving material uris in the database
			$this->saveMaterial();
			$this->saveCategoriesToDb();
		}
	}

	function saveCategoriesToDb()
	{
		$ilDB = $this->db;
		
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
	* @access public
	*/
	function insertXML(&$a_xml_writer, $a_include_header = TRUE)
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
			$a_xml_writer->xmlStartTag("response_multiple", $attrs);
			$this->addMaterialTag($a_xml_writer, $this->categories->getCategory($i)->title);
			$a_xml_writer->xmlEndTag("response_multiple");
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
		$a_xml_writer->xmlStartTag("metadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "use_min_answers");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->use_min_answers);
		$a_xml_writer->xmlEndTag("metadatafield");
		$a_xml_writer->xmlStartTag("metadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "nr_min_answers");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->nr_min_answers);
		$a_xml_writer->xmlEndTag("metadatafield");
		$a_xml_writer->xmlStartTag("metadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "nr_max_answers");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->nr_max_answers);
		$a_xml_writer->xmlEndTag("metadatafield");
		$a_xml_writer->xmlEndTag("metadata");

		$a_xml_writer->xmlEndTag("question");
	}

	/**
	* Returns the question type of the question
	*
	* @return integer The question type of the question
	* @access public
	*/
	function getQuestionType()
	{
		return "SurveyMultipleChoiceQuestion";
	}

	/**
	* Returns the name of the additional question data table in the database
	*
	* @return string The additional table name
	* @access public
	*/
	function getAdditionalTableName()
	{
		return "svy_qst_mc";
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
		if (is_array($entered_value))
		{
			foreach ($entered_value as $idx => $value)
			{
				array_push($data, array("value" => $value, "textanswer" => $post_data[$this->getId() . '_' . $value . '_other']));
			}
		}
		for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
		{
			$cat = $this->categories->getCategory($i);
			if ($cat->other)
			{
				// #18212
				if (!is_array($entered_value) || !in_array($i, $entered_value))
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
		if (!$this->getObligatory($survey_id) && (!is_array($entered_value) || count($entered_value) == 0)) return "";

		if ($this->use_min_answers && $this->nr_min_answers > 0 && $this->nr_max_answers > 0 && $this->nr_min_answers == $this->nr_max_answers && count($entered_value) != $this->nr_max_answers)
		{
			return sprintf($this->lng->txt("err_no_exact_answers"), $this->nr_min_answers);
		}
		if ($this->use_min_answers && $this->nr_min_answers > 0 && count($entered_value) < $this->nr_min_answers)
		{
			return sprintf($this->lng->txt("err_no_min_answers"), $this->nr_min_answers);
		}
		if ($this->use_min_answers && $this->nr_max_answers > 0 && count($entered_value) > $this->nr_max_answers)
		{
			return sprintf($this->lng->txt("err_no_max_answers"), $this->nr_max_answers);
		}
		if (!is_array($entered_value))
		{
			return $this->lng->txt("question_mr_not_checked");
		}
		for ($i = 0; $i < $this->categories->getCategoryCount(); $i++)
		{
			$cat = $this->categories->getCategory($i);
			if ($cat->other)
			{
				if (in_array($i, $entered_value))
				{
					if (array_key_exists($this->getId() . "_" . $i . "_other", $post_data) && !strlen($post_data[$this->getId() . "_" . $i . "_other"]))
					{
						return $this->lng->txt("question_mr_no_other_answer");
					}
				}
				else
				{
					if (strlen($post_data[$this->getId() . "_" . $i . "_other"]))
					{
						return $this->lng->txt("question_mr_no_other_answer_checked");
					}
				}
			}
		}
		return "";
	}
	
	function saveUserInput($post_data, $active_id, $a_return = false)
	{
		$ilDB = $this->db;

		if($a_return)
		{
			$return_data = array();
		}
		if (is_array($post_data[$this->getId() . "_value"]))
		{
			foreach ($post_data[$this->getId() . "_value"] as $entered_value)
			{
				if (strlen($entered_value) > 0)
				{
					if(!$a_return)
					{
						$next_id = $ilDB->nextId('svy_answer');
						
						#20216
						$fields = array();
						$fields['answer_id'] = array("integer", $next_id);
						$fields['question_fi'] = array("integer", $this->getId());
						$fields['active_fi'] = array("integer", $active_id);
						$fields['value'] = array("float", (strlen($entered_value)) ? $entered_value : NULL);
						$fields['textanswer'] = array("clob", ($post_data[$this->getId() . "_" . $entered_value . "_other"]) ? $post_data[$this->getId() . "_" . $entered_value . "_other"] : null);
						$fields['tstamp'] = array("integer", time());

						$affectedRows = $ilDB->insert("svy_answer", $fields);	
					}
					else
					{
						$return_data[] = array("value"=>$entered_value, 
								"textanswer"=>$post_data[$this->getId() . "_" . $entered_value . "_other"]);
					}
				}
			}
		}
		if($a_return)
		{
			return $return_data;
		}
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
				case "use_min_answers":
					$this->use_min_answers = $value["entry"];
					break;
				case "nr_min_answers":
					$this->nr_min_answers = $value["entry"];
					break;
				case "nr_max_answers":
					$this->nr_max_answers = $value["entry"];
					break;
			}
		}
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
		return array("=", "<>");
	}

	/**
	* Returns the options for preconditions
	*
	* @return array
	*/
	public function getPreconditionOptions()
	{
		$lng = $this->lng;
		
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
		// #18136
		$category = $this->categories->getCategoryForScale($value+1);
		
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