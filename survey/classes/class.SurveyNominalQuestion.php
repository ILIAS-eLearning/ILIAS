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

include_once "./survey/classes/class.SurveyQuestion.php";
include_once "./survey/classes/inc.SurveyConstants.php";

define("SUBTYPE_MCSR", 1);
define("SUBTYPE_MCMR", 2);

/**
* Nominal survey question
*
* The SurveyNominalQuestion class defines and encapsulates basic methods and attributes
* for nominal survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.SurveyNominalQuestion.php
* @modulegroup   Survey
*/
class SurveyNominalQuestion extends SurveyQuestion 
{
/**
* Question subtype
*
* A question subtype (Multiple choice single response or multiple choice multiple response)
*
* @var integer
*/
  var $subtype;

/**
* Categories contained in this question
*
* Categories contained in this question
*
* @var array
*/
  var $categories;

/**
* SurveyNominalQuestion constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyNominalQuestion object.
*
* @param string $title A title string to describe the question
* @param string $description A description string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @access public
*/
  function SurveyNominalQuestion(
    $title = "",
    $description = "",
    $author = "",
		$questiontext = "",
    $owner = -1,
		$subtype = SUBTYPE_MCSR,
		$orientation = 0 
  )

  {
		$this->SurveyQuestion($title, $description, $author, $questiontext, $owner);
		$this->subtype = $subtype;
		$this->orientation = $orientation;
		include_once "./survey/classes/class.SurveyCategories.php";
		$this->categories = new SurveyCategories();
	}
	
/**
* Sets the question subtype
*
* Sets the question subtype
*
* @param integer $subtype The question subtype
* @access public
* @see $subtype
*/
  function setSubtype($subtype = SUBTYPE_MCSR) 
	{
    $this->subtype = $subtype;
  }

/**
* Gets the question subtype
*
* Gets the question subtype
*
* @return integer The question subtype
* @access public
* @see $subtype
*/
  function getSubtype() 
	{
    return $this->subtype;
  }
	
/**
* Loads a SurveyNominalQuestion object from the database
*
* Loads a SurveyNominalQuestion object from the database
*
* @param integer $id The database id of the nominal survey question
* @access public
*/
  function loadFromDb($id) 
	{
		global $ilDB;
    $query = sprintf("SELECT survey_question.*, survey_question_nominal.* FROM survey_question, survey_question_nominal WHERE survey_question.question_id = %s AND survey_question.question_id = survey_question_nominal.question_fi",
      $ilDB->quote($id)
    );
    $result = $ilDB->query($query);
    if (strcmp(strtolower(get_class($result)), db_result) == 0) 
		{
      if ($result->numRows() == 1) 
			{
				$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->id = $data->question_id;
				$this->title = $data->title;
				$this->description = $data->description;
				$this->obj_id = $data->obj_fi;
				$this->author = $data->author;
				$this->subtype = $data->subtype;
				$this->orientation = $data->orientation;
				$this->obligatory = $data->obligatory;
				$this->owner = $data->owner_fi;
				include_once("./Services/RTE/classes/class.ilRTE.php");
				$this->questiontext = ilRTE::_replaceMediaObjectImageSrc($data->questiontext, 1);
				$this->complete = $data->complete;
				$this->original_id = $data->original_id;
      }
      // loads materials uris from database
      $this->loadMaterialFromDb($id);

			$this->categories->flushCategories();
      $query = sprintf("SELECT survey_variable.*, survey_category.title FROM survey_variable, survey_category WHERE survey_variable.question_fi = %s AND survey_variable.category_fi = survey_category.category_id ORDER BY sequence ASC",
        $ilDB->quote($id)
      );
      $result = $ilDB->query($query);
      if (strcmp(strtolower(get_class($result)), db_result) == 0) 
			{
        while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) 
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
		if (strlen($this->title) && strlen($this->author) && strlen($this->questiontext) && $this->categories->getCategoryCount())
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

/**
* Saves a SurveyNominalQuestion object to a database
*
* Saves a SurveyNominalQuestion object to a database
*
* @access public
*/
  function saveToDb($original_id = "", $withanswers = true)
  {
		global $ilDB;
		$complete = 0;
		if ($this->isComplete()) 
		{
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
				$ilDB->quote($this->getQuestionType()),
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
				$query = sprintf("INSERT INTO survey_question_nominal (question_fi, subtype, orientation) VALUES (%s, %s, %s)",
					$ilDB->quote($this->id . ""),
					$ilDB->quote($this->getSubType() . ""),
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
			$query = sprintf("UPDATE survey_question_nominal SET subtype = %s, orientation = %s WHERE question_fi = %s",
				$ilDB->quote($this->getSubType() . ""),
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

	/**
	* Imports a question from XML
	*
	* Sets the attributes of the question from the XML text passed
	* as argument
	*
	* @return boolean True, if the import succeeds, false otherwise
	* @access public
	*/
	function from_xml($xml_text)
	{
		$result = false;
		if (!empty($this->domxml))
		{
			$this->domxml->free();
		}
		$xml_text = preg_replace("/>\s*?</", "><", $xml_text);
		$this->domxml = domxml_open_mem($xml_text);
		if (!empty($this->domxml))
		{
			$root = $this->domxml->document_element();
			$item = $root->first_child();
			$this->setTitle($item->get_attribute("title"));
			$this->gaps = array();
			$itemnodes = $item->child_nodes();
			foreach ($itemnodes as $index => $node)
			{
				switch ($node->node_name())
				{
					case "qticomment":
						$comment = $node->get_content();
						if (strpos($comment, "ILIAS Version=") !== false)
						{
						}
						elseif (strpos($comment, "Questiontype=") !== false)
						{
						}
						elseif (strpos($comment, "Author=") !== false)
						{
							$comment = str_replace("Author=", "", $comment);
							$this->setAuthor($comment);
						}
						else
						{
							$this->setDescription($comment);
						}
						break;
					case "itemmetadata":
						$qtimetadata = $node->first_child();
						$metadata_fields = $qtimetadata->child_nodes();
						foreach ($metadata_fields as $index => $metadata_field)
						{
							$fieldlabel = $metadata_field->first_child();
							$fieldentry = $fieldlabel->next_sibling();
							switch ($fieldlabel->get_content())
							{
								case "obligatory":
									$this->setObligatory($fieldentry->get_content());
									break;
								case "orientation":
									$this->setOrientation($fieldentry->get_content());
									break;
							}
						}
						break;
					case "presentation":
						$flow = $node->first_child();
						$flownodes = $flow->child_nodes();
						foreach ($flownodes as $idx => $flownode)
						{
							if (strcmp($flownode->node_name(), "material") == 0)
							{
								$mattext = $flownode->first_child();
								$this->setQuestiontext($mattext->get_content());
							}
							elseif (strcmp($flownode->node_name(), "response_lid") == 0)
							{
								$ident = $flownode->get_attribute("ident");
								if (strcmp($ident, "MCSR") == 0)
								{
									$this->setSubtype(SUBTYPE_MCSR);
								}
								else
								{
									$this->setSubtype(SUBTYPE_MCMR);
								}
								$shuffle = "";
								$response_lid_nodes = $flownode->child_nodes();
								foreach ($response_lid_nodes as $resp_lid_id => $resp_lid_node)
								{
									switch ($resp_lid_node->node_name())
									{
										case "render_choice":
											$render_choice = $resp_lid_node;
											$labels = $render_choice->child_nodes();
											foreach ($labels as $lidx => $response_label)
											{
												$material = $response_label->first_child();
												$mattext = $material->first_child();
												$shuf = 0;
												$this->categories->addCategoryAtPosition($mattext->get_content(), $response_label->get_attribute("ident"));
											}
											break;
										case "material":
											$matlabel = $resp_lid_node->get_attribute("label");
											$mattype = $resp_lid_node->first_child();
											if (strcmp($mattype->node_name(), "mattext") == 0)
											{
												$material = $mattype->get_content();
												if ($material)
												{
													if ($this->getId() < 1)
													{
														$this->saveToDb();
													}
													$this->setMaterial($material, true, $matlabel);
												}
											}
											break;
									}
								}
							}
						}
						break;
				}
			}
			$result = true;
		}
		return $result;
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
	function to_xml($a_include_header = true, $obligatory_state = "")
	{
		include_once("./classes/class.ilXmlWriter.php");
		$a_xml_writer = new ilXmlWriter;
		// set xml header
		$a_xml_writer->xmlHeader();
		$a_xml_writer->xmlStartTag("questestinterop");
		$attrs = array(
			"ident" => $this->getId(),
			"title" => $this->getTitle()
		);
		$a_xml_writer->xmlStartTag("item", $attrs);
		// add question description
		$a_xml_writer->xmlElement("qticomment", NULL, $this->getDescription());
		$a_xml_writer->xmlElement("qticomment", NULL, "ILIAS Version=".$this->ilias->getSetting("ilias_version"));
		$a_xml_writer->xmlElement("qticomment", NULL, "Questiontype=".NOMINAL_QUESTION_IDENTIFIER);
		$a_xml_writer->xmlElement("qticomment", NULL, "Author=".$this->getAuthor());
		// add ILIAS specific metadata
		$a_xml_writer->xmlStartTag("itemmetadata");
		$a_xml_writer->xmlStartTag("qtimetadata");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "obligatory");
		if (strcmp($obligatory_state, "") != 0)
		{
			$this->setObligatory($obligatory_state);
		}
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getObligatory()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "orientation");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getOrientation()));
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
		$a_xml_writer->xmlElement("mattext", NULL, $this->getQuestiontext());
		$a_xml_writer->xmlEndTag("material");
		// add answers to presentation
		$ident = "MCMR";
		$rcardinality = "Multiple";
		if ($this->getSubtype() == SUBTYPE_MCSR)
		{
			$ident = "MCSR";
			$rcardinality = "Single";
		}
		$attrs = array(
			"ident" => $ident,
			"rcardinality" => $rcardinality
		);
		$a_xml_writer->xmlStartTag("response_lid", $attrs);
		
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

		$attrs = array(
			"shuffle" => "no"
		);
		$a_xml_writer->xmlStartTag("render_choice", $attrs);

		// add categories
		for ($index = 0; $index < $this->categories->getCategoryCount(); $index++)
		{
			$category = $this->categories->getCategory($index);
			$attrs = array(
				"ident" => "$index"
			);
			$a_xml_writer->xmlStartTag("response_label", $attrs);
			$a_xml_writer->xmlStartTag("material");
			$a_xml_writer->xmlElement("mattext", NULL, $category);
			$a_xml_writer->xmlEndTag("material");
			$a_xml_writer->xmlEndTag("response_label");
		}
		$a_xml_writer->xmlEndTag("render_choice");
		$a_xml_writer->xmlEndTag("response_lid");
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
			$query = sprintf("UPDATE survey_question_nominal SET subtype = %s, orientation = %s WHERE question_fi = %s",
				$ilDB->quote($this->getSubType() . ""),
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
	* Returns the question type of the question
	*
	* Returns the question type of the question
	*
	* @return integer The question type of the question
	* @access public
	*/
	function getQuestionType()
	{
		return 1;
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
		return "survey_question_nominal";
	}

	function checkUserInput($post_data)
	{
		// multiple response questions are always non-obligatory
		if ($this->getSubType() == SUBTYPE_MCMR) return "";
		
		$entered_value = $post_data[$this->getId() . "_value"];
		
		if ((!$this->getObligatory()) && (strlen($entered_value) == 0)) return "";

		if (strlen($entered_value) == 0) return $this->lng->txt("nominal_question_not_checked");
		
		return "";
	}
	
	function saveUserInput($post_data, $survey_id, $user_id, $anonymous_id)
	{
		global $ilDB;

		if (is_array($post_data[$this->getId() . "_value"]))
		{
			foreach ($post_data[$this->getId() . "_value"] as $value)
			{
				$entered_value = $value;
				if (strlen($entered_value) > 0)
				{
					$entered_value = $ilDB->quote($entered_value . "");
					$query = sprintf("INSERT INTO survey_answer (answer_id, survey_fi, question_fi, user_fi, anonymous_id, value, textanswer, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
						$ilDB->quote($survey_id . ""),
						$ilDB->quote($this->getId() . ""),
						$ilDB->quote($user_id . ""),
						$ilDB->quote($anonymous_id . ""),
						$entered_value,
						"NULL"
					);
					$result = $ilDB->query($query);
				}
			}
		}
		else
		{
			$entered_value = $post_data[$this->getId() . "_value"];
			if (strlen($entered_value) == 0) return;
			$entered_value = $ilDB->quote($entered_value . "");
			$query = sprintf("INSERT INTO survey_answer (answer_id, survey_fi, question_fi, user_fi, anonymous_id, value, textanswer, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($survey_id . ""),
				$ilDB->quote($this->getId() . ""),
				$ilDB->quote($user_id . ""),
				$ilDB->quote($anonymous_id . ""),
				$entered_value,
				"NULL"
			);
			$result = $ilDB->query($query);
		}
	}

	function &getCumulatedResults($survey_id, $nr_of_users)
	{
		global $ilDB;
		
		$question_id = $this->getId();
		
		$result_array = array();
		$cumulated = array();

		$query = sprintf("SELECT * FROM survey_answer WHERE question_fi = %s AND survey_fi = %s",
			$ilDB->quote($question_id),
			$ilDB->quote($survey_id)
		);
		$result = $ilDB->query($query);
		$numrows = $result->numRows();
		if ($numrows == 0) return $result_array;
		
		// count the answers for every answer value
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$cumulated["$row->value"]++;
		}
		asort($cumulated, SORT_NUMERIC);
		end($cumulated);
		
		if ($this->getSubType() == SUBTYPE_MCMR)
		{
			/* even a mcmr questions without answers could be an answered question
			   so for this question type there is no possibility to cound skipped questions */
			/*$query = sprintf("SELECT answer_id, concat( question_fi,  \"_\", anonymous_id, \"_\", user_fi)  AS groupval FROM `survey_answer` WHERE question_fi = %s AND survey_fi = %s GROUP BY groupval",
				$ilDB->quote($question_id),
				$ilDB->quote($survey_id)
			);
			$mcmr_result = $ilDB->query($query);
			$result_array["USERS_ANSWERED"] = $mcmr_result->numRows();
			$result_array["USERS_SKIPPED"] = $nr_of_users - $mcmr_result->numRows();
			$numrows = $mcmr_result->numRows();*/
			$result_array["USERS_ANSWERED"] = $nr_of_users;
			$result_array["USERS_SKIPPED"] = 0;
		}
		else
		{
			$result_array["USERS_ANSWERED"] = $result->numRows();
			$result_array["USERS_SKIPPED"] = $nr_of_users - $result->numRows();
		}
		$result_array["MEDIAN"] = "";
		$result_array["ARITHMETIC_MEAN"] = "";
		$prefix = "";
		if (strcmp(key($cumulated), "") != 0)
		{
			$prefix = (key($cumulated)+1) . " - ";
		}
		$result_array["MODE"] =  $prefix . $this->categories->getCategory(key($cumulated));
		$result_array["MODE_VALUE"] =  key($cumulated)+1;
		$result_array["MODE_NR_OF_SELECTIONS"] = $cumulated[key($cumulated)];
		$result_array["QUESTION_TYPE"] = "SurveyNominalQuestion";
		$maxvalues = 0;
		for ($key = 0; $key < $this->categories->getCategoryCount(); $key++)
		{
			$maxvalues += $cumulated[$key];
		}
		for ($key = 0; $key < $this->categories->getCategoryCount(); $key++)
		{
			$percentage = 0;
			if ($numrows > 0)
			{
				if ($this->getSubType() == SUBTYPE_MCMR)
				{
					if ($maxvalues > 0)
					{
						$percentage = (float)((int)$cumulated[$key]/$result_array["USERS_ANSWERED"]);
					}
				}
				else
				{
					$percentage = (float)((int)$cumulated[$key]/$numrows);
				}
			}
			$result_array["variables"][$key] = array("title" => $this->categories->getCategory($key), "selected" => (int)$cumulated[$key], "percentage" => $percentage);
		}
		return $result_array;
	}
}
?>
