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

require_once "./survey/classes/class.SurveyQuestion.php";

define("SUBTYPE_NON_RATIO", 3);
define("SUBTYPE_RATIO_NON_ABSOLUTE", 4);
define("SUBTYPE_RATIO_ABSOLUTE", 5);

define("METRIC_QUESTION_IDENTIFIER", "Metric Question");

/**
* Metric survey question
*
* The SurveyMetricQuestion class defines and encapsulates basic methods and attributes
* for metric survey question types.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.SurveyMetricQuestion.php
* @modulegroup   Survey
*/
class SurveyMetricQuestion extends SurveyQuestion {
/**
* Question subtype
*
* A question subtype (Multiple choice single response or multiple choice multiple response)
*
* @var integer
*/
  var $subtype;

/**
* The minimum value for the metric question
*
* The minimum value for the metric question
*
* @var double
*/
  var $minimum;

/**
* The maximum value for the metric question
*
* The maximum value for the metric question
*
* @var double
*/
  var $maximum;

/**
* SurveyMetricQuestion constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyMetricQuestion object.
*
* @param string $title A title string to describe the question
* @param string $description A description string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @access public
*/
  function SurveyMetricQuestion(
    $title = "",
    $description = "",
    $author = "",
		$questiontext = "",
    $owner = -1,
		$subtype = 0
  )

  {
		$this->SurveyQuestion($title, $description, $author, $questiontext, $owner);
		$this->subtype = $subtype;
		$this->minimum = 0;
		$this->maximum = "&infin;";
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
  function setSubtype($subtype = SUBTYPE_NON_RATIO) 
	{
    $this->subtype = $subtype;
  }

/**
* Sets the minimum value
*
* Sets the minimum value
*
* @param double $minimum The minimum value
* @access public
* @see $minimum
*/
  function setMinimum($minimum = 0) 
	{
    $this->minimum = $minimum;
  }

/**
* Sets the maximum value
*
* Sets the maximum value
*
* @param double $maximum The maximum value
* @access public
* @see $maximum
*/
  function setMaximum($maximum = "&infin;") 
	{
    $this->maximum = $maximum;
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
* Returns the minimum value of the question
*
* Returns the minimum value of the question
*
* @return double The minimum value of the question
* @access public
* @see $minimum
*/
	function getMinimum() 
	{
		return $this->minimum;
	}
	
/**
* Returns the maximum value of the question
*
* Returns the maximum value of the question
*
* @return double The maximum value of the question
* @access public
* @see $maximum
*/
	function getMaximum() 
	{
		return $this->maximum;
	}
	
/**
* Loads a SurveyMetricQuestion object from the database
*
* Loads a SurveyMetricQuestion object from the database
*
* @param integer $id The database id of the metric survey question
* @access public
*/
  function loadFromDb($id) {
    $query = sprintf("SELECT * FROM survey_question WHERE question_id = %s",
      $this->ilias->db->quote($id)
    );
    $result = $this->ilias->db->query($query);
    if (strcmp(strtolower(get_class($result)), db_result) == 0) {
      if ($result->numRows() == 1) {
        $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
        $this->id = $data->question_id;
        $this->title = $data->title;
        $this->description = $data->description;
        $this->obj_id = $data->obj_fi;
				$this->obligatory = $data->obligatory;
        $this->author = $data->author;
				$this->subtype = $data->subtype;
				$this->original_id = $data->original_id;
        $this->owner = $data->owner_fi;
        $this->questiontext = $data->questiontext;
        $this->complete = $data->complete;
      }
      // loads materials uris from database
      $this->loadMaterialFromDb($id);

      $query = sprintf("SELECT survey_variable.* FROM survey_variable WHERE survey_variable.question_fi = %s",
        $this->ilias->db->quote($id)
      );
      $result = $this->ilias->db->query($query);
      if (strcmp(strtolower(get_class($result)), db_result) == 0) {
        if ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
          $this->minimum = $data->value1;
					if (($data->value2 < 0) or (strcmp($data->value2, "") == 0))
					{
						$this->maximum = "&infin;";
					}
					else
					{
						$this->maximum = $data->value2;
					}
        }
      }
    }
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
* Saves a SurveyMetricQuestion object to a database
*
* Saves a SurveyMetricQuestion object to a database
*
* @access public
*/
  function saveToDb($original_id = "")
  {
		$complete = 0;
		if ($this->isComplete()) {
			$complete = 1;
		}
		if ($original_id)
		{
			$original_id = $this->ilias->db->quote($original_id);
		}
		else
		{
			$original_id = "NULL";
		}
    if ($this->id == -1) {
      // Write new dataset
      $now = getdate();
      $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
      $query = sprintf("INSERT INTO survey_question (question_id, subtype, questiontype_fi, obj_fi, owner_fi, title, description, author, questiontext, obligatory, complete, created, original_id, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$this->ilias->db->quote("$this->subtype"),
        $this->ilias->db->quote("3"),
        $this->ilias->db->quote($this->obj_id),
        $this->ilias->db->quote($this->owner),
        $this->ilias->db->quote($this->title),
        $this->ilias->db->quote($this->description),
        $this->ilias->db->quote($this->author),
        $this->ilias->db->quote($this->questiontext),
				$this->ilias->db->quote(sprintf("%d", $this->obligatory)),
				$this->ilias->db->quote("$complete"),
        $this->ilias->db->quote($created),
				$original_id
      );
      $result = $this->ilias->db->query($query);
      if ($result == DB_OK) {
        $this->id = $this->ilias->db->getLastInsertId();
      }
    } else {
      // update existing dataset
      $query = sprintf("UPDATE survey_question SET title = %s, subtype = %s, description = %s, author = %s, questiontext = %s, obligatory = %s, complete = %s WHERE question_id = %s",
        $this->ilias->db->quote($this->title),
				$this->ilias->db->quote("$this->subtype"),
        $this->ilias->db->quote($this->description),
        $this->ilias->db->quote($this->author),
        $this->ilias->db->quote($this->questiontext),
				$this->ilias->db->quote(sprintf("%d", $this->obligatory)),
				$this->ilias->db->quote("$complete"),
        $this->ilias->db->quote($this->id)
      );
      $result = $this->ilias->db->query($query);
    }
    if ($result == DB_OK) {
      // saving material uris in the database
      $this->saveMaterialsToDb();

      // save categories
			
			// delete existing category relations
      $query = sprintf("DELETE FROM survey_variable WHERE question_fi = %s",
        $this->ilias->db->quote($this->id)
      );
      $result = $this->ilias->db->query($query);
      // create new category relations
			if (strcmp($this->minimum, "") == 0)
			{
				$min = "NULL";
			}
			else
			{
				$min = $this->ilias->db->quote($this->minimum);
			}
			if (preg_match("/[\D]/", $this->maximum) or (strcmp($this->maximum, "&infin;") == 0))
			{
				$max = -1;
			}
			else
			{
				if (strcmp($this->maximum, "") == 0)
				{
					$max = "NULL";
				}
				else
				{
					$max = $this->ilias->db->quote($this->maximum);
				}
			}
			$query = sprintf("INSERT INTO survey_variable (variable_id, category_fi, question_fi, value1, value2, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
				$this->ilias->db->quote(0),
				$this->ilias->db->quote($this->id),
				$min,
				$max,
				$this->ilias->db->quote(0)
			);
			$answer_result = $this->ilias->db->query($query);
    }
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
								case "subtype":
									$this->setSubtype($fieldentry->get_content());
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
							elseif (strcmp($flownode->node_name(), "response_num") == 0)
							{
								$ident = $flownode->get_attribute("ident");
								$shuffle = "";
								$render_choice = $flownode->first_child();
								if (strcmp($render_choice->node_name(), "render_fib") == 0)
								{
									$minnumber = $render_choice->get_attribute("minnumber");
									$this->setMinimum($minnumber);
									$maxnumber = $render_choice->get_attribute("maxnumber");
									$this->setMaximum($maxnumber);
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
		if (!empty($this->domxml))
		{
			$this->domxml->free();
		}
		$xml_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xml_header .= "<questestinterop></questestinterop>\n";
		$this->domxml = domxml_open_mem($xml_header);
		$root = $this->domxml->document_element();
		// qti ident
		$qtiIdent = $this->domxml->create_element("item");
		$qtiIdent->set_attribute("ident", $this->getId());
		$qtiIdent->set_attribute("title", $this->getTitle());
		$root->append_child($qtiIdent);
		// add qti comment
		$qtiComment = $this->domxml->create_element("qticomment");
		$qtiCommentText = $this->domxml->create_text_node($this->getDescription());
		$qtiComment->append_child($qtiCommentText);
		$qtiIdent->append_child($qtiComment);
		$qtiComment = $this->domxml->create_element("qticomment");
		$qtiCommentText = $this->domxml->create_text_node("ILIAS Version=".$this->ilias->getSetting("ilias_version"));
		$qtiComment->append_child($qtiCommentText);
		$qtiIdent->append_child($qtiComment);
		$qtiComment = $this->domxml->create_element("qticomment");
		$qtiCommentText = $this->domxml->create_text_node("Questiontype=".METRIC_QUESTION_IDENTIFIER);
		$qtiComment->append_child($qtiCommentText);
		$qtiIdent->append_child($qtiComment);
		$qtiComment = $this->domxml->create_element("qticomment");
		$qtiCommentText = $this->domxml->create_text_node("Author=".$this->getAuthor());
		$qtiComment->append_child($qtiCommentText);
		$qtiIdent->append_child($qtiComment);

		$qtiItemMetadata = $this->domxml->create_element("itemmetadata");
		$qtiMetadata = $this->domxml->create_element("qtimetadata");
		// obligatory state
		$qtiMetadatafield = $this->domxml->create_element("qtimetadatafield");
		$qtiFieldLabel = $this->domxml->create_element("fieldlabel");
		$qtiFieldLabelText = $this->domxml->create_text_node("obligatory");
		$qtiFieldLabel->append_child($qtiFieldLabelText);
		$qtiFieldEntry = $this->domxml->create_element("fieldentry");
		if (strcmp($obligatory_state, "") != 0)
		{
			$this->setObligatory($obligatory_state);
		}
		$qtiFieldEntryText = $this->domxml->create_text_node(sprintf("%d", $this->getObligatory()));
		$qtiFieldEntry->append_child($qtiFieldEntryText);
		$qtiMetadatafield->append_child($qtiFieldLabel);
		$qtiMetadatafield->append_child($qtiFieldEntry);
		$qtiMetadata->append_child($qtiMetadatafield);
		$qtiMetadatafield = $this->domxml->create_element("qtimetadatafield");
		$qtiFieldLabel = $this->domxml->create_element("fieldlabel");
		$qtiFieldLabelText = $this->domxml->create_text_node("subtype");
		$qtiFieldLabel->append_child($qtiFieldLabelText);
		$qtiFieldEntry = $this->domxml->create_element("fieldentry");
		$qtiFieldEntryText = $this->domxml->create_text_node(sprintf("%d", $this->getSubtype()));
		$qtiFieldEntry->append_child($qtiFieldEntryText);
		$qtiMetadatafield->append_child($qtiFieldLabel);
		$qtiMetadatafield->append_child($qtiFieldEntry);
		$qtiMetadata->append_child($qtiMetadatafield);
		$qtiItemMetadata->append_child($qtiMetadata);
		$qtiIdent->append_child($qtiItemMetadata);

		// PART I: qti presentation
		$qtiPresentation = $this->domxml->create_element("presentation");
		$qtiPresentation->set_attribute("label", $this->getTitle());
		// add flow to presentation
		$qtiFlow = $this->domxml->create_element("flow");
		// add material with question text to presentation
		$qtiMaterial = $this->domxml->create_element("material");
		$qtiMatText = $this->domxml->create_element("mattext");
		$qtiMatTextText = $this->domxml->create_text_node($this->getQuestiontext());
		$qtiMatText->append_child($qtiMatTextText);
		$qtiMaterial->append_child($qtiMatText);
		$qtiFlow->append_child($qtiMaterial);
		// add answers to presentation
		$qtiResponseNum = $this->domxml->create_element("response_num");
		$qtiResponseNum->set_attribute("ident", "METRIC");
		$qtiResponseNum->set_attribute("rcardinality", "Single");
		$qtiRenderChoice = $this->domxml->create_element("render_fib");
		$qtiRenderChoice->set_attribute("minnumber", $this->getMinimum());
		$qtiRenderChoice->set_attribute("maxnumber", $this->getMaximum());

		$qtiResponseNum->append_child($qtiRenderChoice);
		$qtiFlow->append_child($qtiResponseNum);
		$qtiPresentation->append_child($qtiFlow);
		$qtiIdent->append_child($qtiPresentation);
		$xml = $this->domxml->dump_mem(true);
		if (!$a_include_header)
		{
			$pos = strpos($xml, "?>");
			$xml = substr($xml, $pos + 2);
		}
//echo htmlentities($xml);
		return $xml;
	}

	function syncWithOriginal()
	{
		if ($this->original_id)
		{
			$complete = 0;
			if ($this->isComplete()) {
				$complete = 1;
			}
			$query = sprintf("UPDATE survey_question SET title = %s, subtype = %s, description = %s, author = %s, questiontext = %s, obligatory = %s, complete = %s WHERE question_id = %s",
				$this->ilias->db->quote($this->title . ""),
				$this->ilias->db->quote($this->subtype . ""),
				$this->ilias->db->quote($this->description . ""),
				$this->ilias->db->quote($this->author . ""),
				$this->ilias->db->quote($this->questiontext . ""),
				$this->ilias->db->quote(sprintf("%d", $this->obligatory) . ""),
				$this->ilias->db->quote($complete . ""),
				$this->ilias->db->quote($this->original_id . "")
			);
			$result = $this->ilias->db->query($query);
			if ($result == DB_OK) 
			{
				// save categories
				
				// delete existing category relations
				$query = sprintf("DELETE FROM survey_variable WHERE question_fi = %s",
					$this->ilias->db->quote($this->original_id)
				);
				$result = $this->ilias->db->query($query);
				// create new category relations
				if (strcmp($this->minimum, "") == 0)
				{
					$min = "NULL";
				}
				else
				{
					$min = $this->ilias->db->quote($this->minimum . "");
				}
				if (preg_match("/[\D]/", $this->maximum) or (strcmp($this->maximum, "&infin;") == 0))
				{
					$max = -1;
				}
				else
				{
					if (strcmp($this->maximum, "") == 0)
					{
						$max = "NULL";
					}
					else
					{
						$max = $this->ilias->db->quote($this->maximum . "");
					}
				}
				$query = sprintf("INSERT INTO survey_variable (variable_id, category_fi, question_fi, value1, value2, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
					$this->ilias->db->quote("0"),
					$this->ilias->db->quote($this->original_id . ""),
					$min,
					$max,
					$this->ilias->db->quote("0")
				);
				$answer_result = $this->ilias->db->query($query);
			}
		}
	}
	
}
?>
