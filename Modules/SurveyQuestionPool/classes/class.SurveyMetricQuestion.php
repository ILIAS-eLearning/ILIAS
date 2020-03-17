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
* Metric survey question
*
* The SurveyMetricQuestion class defines and encapsulates basic methods and attributes
* for metric survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestion
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyMetricQuestion extends SurveyQuestion
{
    /**
     * @var ilDB
     */
    protected $db;

    const SUBTYPE_NON_RATIO = 3;
    const SUBTYPE_RATIO_NON_ABSOLUTE = 4;
    const SUBTYPE_RATIO_ABSOLUTE = 5;
    
    /**
    * Question subtype
    *
    * A question subtype (Multiple choice single response or multiple choice multiple response)
    *
    * @var integer
    */
    public $subtype;

    /**
    * The minimum value for the metric question
    *
    * @var double
    */
    public $minimum;

    /**
    * The maximum value for the metric question
    *
    * @var double
    */
    public $maximum;

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
    public function __construct($title = "", $description = "", $author = "", $questiontext = "", $owner = -1, $subtype = self::SUBTYPE_NON_RATIO)
    {
        global $DIC;

        $this->db = $DIC->database();
        parent::__construct($title, $description, $author, $questiontext, $owner);
        
        $this->subtype = $subtype;
        $this->minimum = "";
        $this->maximum = "";
    }
    
    /**
    * Sets the question subtype
    *
    * @param integer $subtype The question subtype
    * @access public
    * @see $subtype
    */
    public function setSubtype($subtype = self::SUBTYPE_NON_RATIO)
    {
        $this->subtype = $subtype;
    }

    /**
    * Sets the minimum value
    *
    * @param double $minimum The minimum value
    * @access public
    * @see $minimum
    */
    public function setMinimum($minimum = 0)
    {
        if ($minimum !== null) {
            $minimum = (float) $minimum;
        }
        if (!$minimum) {
            $minimum = null;
        }
        $this->minimum = $minimum;
    }

    /**
    * Sets the maximum value
    *
    * @param double $maximum The maximum value
    * @access public
    * @see $maximum
    */
    public function setMaximum($maximum = "")
    {
        if ($maximum !== null) {
            $maximum = (float) $maximum;
        }
        if (!$maximum) {
            $maximum = null;
        }
        $this->maximum = $maximum;
    }

    /**
    * Gets the question subtype
    *
    * @return integer The question subtype
    * @access public
    * @see $subtype
    */
    public function getSubtype()
    {
        return $this->subtype;
    }
    
    /**
    * Returns the minimum value of the question
    *
    * @return double The minimum value of the question
    * @access public
    * @see $minimum
    */
    public function getMinimum()
    {
        if ((strlen($this->minimum) == 0) && ($this->getSubtype() > 3)) {
            $this->minimum = 0;
        }
        return (strlen($this->minimum)) ? $this->minimum : null;
    }
    
    /**
    * Returns the maximum value of the question
    *
    * @return double The maximum value of the question
    * @access public
    * @see $maximum
    */
    public function getMaximum()
    {
        return (strlen($this->maximum)) ? $this->maximum : null;
    }
    
    /**
    * Returns the question data fields from the database
    *
    * @param integer $id The question ID from the database
    * @return array Array containing the question fields and data from the database
    * @access public
    */
    public function getQuestionDataArray($id)
    {
        $ilDB = $this->db;
        
        $result = $ilDB->queryF(
            "SELECT svy_question.*, " . $this->getAdditionalTableName() . ".* FROM svy_question, " . $this->getAdditionalTableName() . " WHERE svy_question.question_id = %s AND svy_question.question_id = " . $this->getAdditionalTableName() . ".question_fi",
            array('integer'),
            array($id)
        );
        if ($result->numRows() == 1) {
            return $ilDB->fetchAssoc($result);
        } else {
            return array();
        }
    }
    
    /**
    * Loads a SurveyMetricQuestion object from the database
    *
    * @param integer $id The database id of the metric survey question
    * @access public
    */
    public function loadFromDb($id)
    {
        $ilDB = $this->db;

        $result = $ilDB->queryF(
            "SELECT svy_question.*, " . $this->getAdditionalTableName() . ".* FROM svy_question LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = svy_question.question_id WHERE svy_question.question_id = %s",
            array('integer'),
            array($id)
        );
        if ($result->numRows() == 1) {
            $data = $ilDB->fetchAssoc($result);
            $this->setId($data["question_id"]);
            $this->setTitle($data["title"]);
            $this->setDescription($data["description"]);
            $this->setObjId($data["obj_fi"]);
            $this->setAuthor($data["author"]);
            $this->setOwner($data["owner_fi"]);
            $this->label = $data['label'];
            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->setQuestiontext(ilRTE::_replaceMediaObjectImageSrc($data["questiontext"], 1));
            $this->setObligatory($data["obligatory"]);
            $this->setComplete($data["complete"]);
            $this->setOriginalId($data["original_id"]);
            $this->setSubtype($data["subtype"]);

            $result = $ilDB->queryF(
                "SELECT svy_variable.* FROM svy_variable WHERE svy_variable.question_fi = %s",
                array('integer'),
                array($id)
            );
            if ($result->numRows() > 0) {
                if ($data = $ilDB->fetchAssoc($result)) {
                    $this->minimum = $data["value1"];
                    if (($data["value2"] < 0) or (strcmp($data["value2"], "") == 0)) {
                        $this->maximum = "";
                    } else {
                        $this->maximum = $data["value2"];
                    }
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
    public function isComplete()
    {
        if (
            strlen($this->getTitle()) &&
            strlen($this->getAuthor()) &&
            strlen($this->getQuestiontext())
        ) {
            return 1;
        } else {
            return 0;
        }
    }
    
    /**
    * Saves a SurveyMetricQuestion object to a database
    *
    * @access public
    */
    public function saveToDb($original_id = "")
    {
        $ilDB = $this->db;

        $affectedRows = parent::saveToDb($original_id);
        if ($affectedRows == 1) {
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
                array('integer'),
                array($this->getId())
            );
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, subtype) VALUES (%s, %s)",
                array('integer', 'text'),
                array($this->getId(), $this->getSubType())
            );

            // saving material uris in the database
            $this->saveMaterial();
            
            // save categories
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM svy_variable WHERE question_fi = %s",
                array('integer'),
                array($this->getId())
            );

            if (preg_match("/[\D]/", $this->maximum) or (strcmp($this->maximum, "&infin;") == 0)) {
                $max = -1;
            } else {
                $max = $this->getMaximum();
            }
            $next_id = $ilDB->nextId('svy_variable');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_variable (variable_id, category_fi, question_fi, value1, value2, sequence, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                array('integer','integer','integer','float','float','integer','integer'),
                array($next_id, 0, $this->getId(), $this->getMinimum(), $max, 0, time())
            );
        }
    }
    
    /**
    * Returns an xml representation of the question
    *
    * @return string The xml representation of the question
    * @access public
    */
    public function toXML($a_include_header = true, $obligatory_state = "")
    {
        include_once("./Services/Xml/classes/class.ilXmlWriter.php");
        $a_xml_writer = new ilXmlWriter;
        $a_xml_writer->xmlHeader();
        $this->insertXML($a_xml_writer, $a_include_header, $obligatory_state);
        $xml = $a_xml_writer->xmlDumpMem(false);
        if (!$a_include_header) {
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
    public function insertXML(&$a_xml_writer, $a_include_header = true)
    {
        $attrs = array(
            "id" => $this->getId(),
            "title" => $this->getTitle(),
            "type" => $this->getQuestiontype(),
            "subtype" => $this->getSubtype(),
            "obligatory" => $this->getObligatory()
        );
        $a_xml_writer->xmlStartTag("question", $attrs);
        
        $a_xml_writer->xmlElement("description", null, $this->getDescription());
        $a_xml_writer->xmlElement("author", null, $this->getAuthor());
        $a_xml_writer->xmlStartTag("questiontext");
        $this->addMaterialTag($a_xml_writer, $this->getQuestiontext());
        $a_xml_writer->xmlEndTag("questiontext");

        $a_xml_writer->xmlStartTag("responses");
        switch ($this->getSubtype()) {
            case 3:
                $attrs = array(
                    "id" => "0",
                    "format" => "double"
                );
                if (strlen($this->getMinimum())) {
                    $attrs["min"] = $this->getMinimum();
                }
                if (strlen($this->getMaximum())) {
                    $attrs["max"] = $this->getMaximum();
                }
                break;
            case 4:
                $attrs = array(
                    "id" => "0",
                    "format" => "double"
                );
                if (strlen($this->getMinimum())) {
                    $attrs["min"] = $this->getMinimum();
                }
                if (strlen($this->getMaximum())) {
                    $attrs["max"] = $this->getMaximum();
                }
                break;
            case 5:
                $attrs = array(
                    "id" => "0",
                    "format" => "integer"
                );
                if (strlen($this->getMinimum())) {
                    $attrs["min"] = $this->getMinimum();
                }
                if (strlen($this->getMaximum())) {
                    $attrs["max"] = $this->getMaximum();
                }
                break;
        }
        $a_xml_writer->xmlStartTag("response_num", $attrs);
        $a_xml_writer->xmlEndTag("response_num");

        $a_xml_writer->xmlEndTag("responses");

        if (count($this->material)) {
            if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $this->material["internal_link"], $matches)) {
                $attrs = array(
                    "label" => $this->material["title"]
                );
                $a_xml_writer->xmlStartTag("material", $attrs);
                $intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
                if (strcmp($matches[1], "") != 0) {
                    $intlink = $this->material["internal_link"];
                }
                $a_xml_writer->xmlElement("mattext", null, $intlink);
                $a_xml_writer->xmlEndTag("material");
            }
        }
        
        $a_xml_writer->xmlEndTag("question");
    }

    /**
    * Returns the question type ID of the question
    *
    * @return integer The question type of the question
    * @access public
    */
    public function getQuestionTypeID()
    {
        $ilDB = $this->db;
        $result = $ilDB->queryF(
            "SELECT questiontype_id FROM svy_qtype WHERE type_tag = %s",
            array('text'),
            array($this->getQuestionType())
        );
        $row = $ilDB->fetchAssoc($result);
        return $row["questiontype_id"];
    }

    /**
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    * @access public
    */
    public function getQuestionType()
    {
        return "SurveyMetricQuestion";
    }
    
    /**
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    * @access public
    */
    public function getAdditionalTableName()
    {
        return "svy_qst_metric";
    }
    
    /**
    * Creates the user data of the svy_answer table from the POST data
    *
    * @return array User data according to the svy_answer table
    * @access public
    */
    public function &getWorkingDataFromUserInput($post_data)
    {
        $entered_value = $post_data[$this->getId() . "_metric_question"];
        $data = array();
        if (strlen($entered_value)) {
            array_push($data, array("value" => $entered_value));
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
    public function checkUserInput($post_data, $survey_id)
    {
        $entered_value = $post_data[$this->getId() . "_metric_question"];
        // replace german notation with international notation
        $entered_value = str_replace(",", ".", $entered_value);
        
        if ((!$this->getObligatory($survey_id)) && (strlen($entered_value) == 0)) {
            return "";
        }
        
        if (strlen($entered_value) == 0) {
            return $this->lng->txt("survey_question_obligatory");
        }
        
        if (strlen($this->getMinimum())) {
            if ($entered_value < $this->getMinimum()) {
                return $this->lng->txt("metric_question_out_of_bounds");
            }
        }

        if (strlen($this->getMaximum())) {
            if (($this->getMaximum() == 1) && ($this->getMaximum() < $this->getMinimum())) {
                // old &infty; values as maximum
            } else {
                if ($entered_value > $this->getMaximum()) {
                    return $this->lng->txt("metric_question_out_of_bounds");
                }
            }
        }

        if (!is_numeric($entered_value)) {
            return $this->lng->txt("metric_question_not_a_value");
        }

        if (($this->getSubType() == self::SUBTYPE_RATIO_ABSOLUTE) && (intval($entered_value) != doubleval($entered_value))) {
            return $this->lng->txt("metric_question_floating_point");
        }
        return "";
    }
    
    public function saveUserInput($post_data, $active_id, $a_return = false)
    {
        $ilDB = $this->db;
        
        $entered_value = $post_data[$this->getId() . "_metric_question"];
        
        // replace german notation with international notation
        $entered_value = str_replace(",", ".", $entered_value);
        
        if ($a_return) {
            return array(array("value" => $entered_value, "textanswer" => null));
        }
        if (strlen($entered_value) == 0) {
            return;
        }
        
        $next_id = $ilDB->nextId('svy_answer');
        #20216
        $fields = array();
        $fields['answer_id'] = array("integer", $next_id);
        $fields['question_fi'] = array("integer", $this->getId());
        $fields['active_fi'] = array("integer", $active_id);
        $fields['value'] = array("float", (strlen($entered_value)) ? $entered_value : null);
        $fields['textanswer'] = array("clob", null);
        $fields['tstamp'] = array("integer", time());

        $affectedRows = $ilDB->insert("svy_answer", $fields);
    }

    /**
    * Import response data from the question import file
    *
    * @return array $a_data Array containing the response data
    * @access public
    */
    public function importResponses($a_data)
    {
        foreach ($a_data as $id => $data) {
            $this->setMinimum($data["min"]);
            $this->setMaximum($data["max"]);
        }
    }

    /**
    * Returns if the question is usable for preconditions
    *
    * @return boolean TRUE if the question is usable for a precondition, FALSE otherwise
    * @access public
    */
    public function usableForPrecondition()
    {
        return true;
    }

    /**
    * Returns the available relations for the question
    *
    * @return array An array containing the available relations
    * @access public
    */
    public function getAvailableRelations()
    {
        return array("<", "<=", "=", "<>", ">=", ">");
    }

    /**
    * Creates a value selection for preconditions
    *
    * @param object $template The template for the value selection (usually tpl.svy_svy_add_constraint.html)
    * @access public
    */
    public function outPreconditionSelectValue(&$template)
    {
        $template->setCurrentBlock("textfield");
        $template->setVariable("TEXTFIELD_VALUE", "");
        $template->parseCurrentBlock();
    }
    
    /**
    * Creates a form property for the precondition value
    *
    * @return The ILIAS form element
    * @access public
    */
    public function getPreconditionSelectValue($default = "", $title, $variable)
    {
        include_once "./Services/Form/classes/class.ilNumberInputGUI.php";
        $step3 = new ilNumberInputGUI($title, $variable);
        $step3->setValue($default);
        return $step3;
    }

    /**
    * Creates a text for the input range of the metric question
    *
    * @return string Range text
    * @access private
    */
    public function getMinMaxText()
    {
        $min = $this->getMinimum();
        $max = $this->getMaximum();
        if (strlen($min) && strlen($max)) {
            return "(" . $min . " " . strtolower($this->lng->txt("to")) . " " . $max . ")";
        } elseif (strlen($min)) {
            return "(&gt;= " . $min . ")";
        } elseif (strlen($max)) {
            return "(&lt;= " . $max . ")";
        } else {
            return "";
        }
    }
}
