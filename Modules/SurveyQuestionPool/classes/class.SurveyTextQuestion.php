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
    public $maxchars;
    public $textwidth;
    public $textheight;
    
    /**
    * The constructor takes possible arguments an creates an instance of the SurveyTextQuestion object.
    *
    * @param string $title A title string to describe the question
    * @param string $description A description string to describe the question
    * @param string $author A string containing the name of the questions author
    * @param integer $owner A numerical ID to identify the owner/creator
    * @access public
    */
    public function __construct($title = "", $description = "", $author = "", $questiontext = "", $owner = -1)
    {
        global $DIC;

        $this->db = $DIC->database();
        parent::__construct($title, $description, $author, $questiontext, $owner);
        
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
    * Loads a SurveyTextQuestion object from the database
    *
    * @param integer $id The database id of the text survey question
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
    * Sets the maximum number of allowed characters for the text answer
    *
    * @access public
    */
    public function setMaxChars($maxchars = 0)
    {
        $this->maxchars = $maxchars;
    }
    
    /**
    * Returns the maximum number of allowed characters for the text answer
    *
    * @access public
    */
    public function getMaxChars()
    {
        return ($this->maxchars) ? $this->maxchars : null;
    }
    
    /**
    * Saves a SurveyTextQuestion object to a database
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
                "INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, maxchars, width, height) VALUES (%s, %s, %s, %s)",
                array('integer', 'integer', 'integer', 'integer'),
                array($this->getId(), $this->getMaxChars(), $this->getTextWidth(), $this->getTextHeight())
            );

            $this->saveMaterial();
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
            "obligatory" => $this->getObligatory()
        );
        $a_xml_writer->xmlStartTag("question", $attrs);
        
        $a_xml_writer->xmlElement("description", null, $this->getDescription());
        $a_xml_writer->xmlElement("author", null, $this->getAuthor());
        if (strlen($this->label)) {
            $attrs = array(
                "label" => $this->label,
            );
        } else {
            $attrs = array();
        }
        $a_xml_writer->xmlStartTag("questiontext", $attrs);
        $this->addMaterialTag($a_xml_writer, $this->getQuestiontext());
        $a_xml_writer->xmlEndTag("questiontext");

        $a_xml_writer->xmlStartTag("responses");
        $attrs = array(
            "id" => "0",
            "rows" => $this->getTextHeight(),
            "columns" => $this->getTextWidth()
        );
        if ($this->getMaxChars() > 0) {
            $attrs["maxlength"] = $this->getMaxChars();
        }
        $a_xml_writer->xmlElement("response_text", $attrs);
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
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    * @access public
    */
    public function getQuestionType()
    {
        return "SurveyTextQuestion";
    }

    /**
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    * @access public
    */
    public function getAdditionalTableName()
    {
        return "svy_qst_text";
    }
    
    /**
    * Creates the user data of the svy_answer table from the POST data
    *
    * @return array User data according to the svy_answer table
    * @access public
    */
    public function &getWorkingDataFromUserInput($post_data)
    {
        $entered_value = $post_data[$this->getId() . "_text_question"];
        $data = array();
        if (strlen($entered_value)) {
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
    public function checkUserInput($post_data, $survey_id)
    {
        $entered_value = $post_data[$this->getId() . "_text_question"];
        
        if ((!$this->getObligatory($survey_id)) && (strlen($entered_value) == 0)) {
            return "";
        }
        
        if (strlen($entered_value) == 0) {
            return $this->lng->txt("text_question_not_filled_out");
        }

        // see bug #22648
        include_once("./Services/Utilities/classes/class.ilStr.php");
        if ($this->getMaxChars() > 0 && ilStr::strLen($entered_value) > $this->getMaxChars()) {
            return str_replace("%s", ilStr::strLen($entered_value), $this->lng->txt("svy_answer_too_long"));
        }

        return "";
    }
    
    public function saveUserInput($post_data, $active_id, $a_return = false)
    {
        $ilDB = $this->db;

        $entered_value = $this->stripSlashesAddSpaceFallback($post_data[$this->getId() . "_text_question"]);
        $maxchars = $this->getMaxChars();

        include_once("./Services/Utilities/classes/class.ilStr.php");
        if ($maxchars > 0) {
            $entered_value = ilStr::subStr($entered_value, 0, $maxchars);
        }
        
        if ($a_return) {
            return array(array("value" => null, "textanswer" => $entered_value));
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
        $fields['value'] = array("float", null);
        $fields['textanswer'] = array("clob", (strlen($entered_value)) ? $entered_value : null);
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
            if ($data["maxlength"] > 0) {
                $this->setMaxChars($data["maxlength"]);
            }
            if ($data["rows"] > 0) {
                $this->setTextHeight($data["rows"]);
            }
            if ($data["columns"] > 0) {
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
    public function usableForPrecondition()
    {
        return false;
    }

    /**
    * Returns the width of the answer field
    *
    * @return integer The width of the answer field in characters
    * @access public
    */
    public function getTextWidth()
    {
        return ($this->textwidth) ? $this->textwidth : null;
    }
    
    /**
    * Returns the height of the answer field
    *
    * @return integer The height of the answer field in characters
    * @access public
    */
    public function getTextHeight()
    {
        return ($this->textheight) ? $this->textheight : null;
    }
    
    /**
    * Sets the width of the answer field
    *
    * @param integer $a_textwidth The width of the answer field in characters
    * @access public
    */
    public function setTextWidth($a_textwidth)
    {
        if ($a_textwidth < 1) {
            $this->textwidth = 50;
        } else {
            $this->textwidth = $a_textwidth;
        }
    }
    
    /**
    * Sets the height of the answer field
    *
    * @param integer $a_textheight The height of the answer field in characters
    * @access public
    */
    public function setTextHeight($a_textheight)
    {
        if ($a_textheight < 1) {
            $this->textheight = 5;
        } else {
            $this->textheight = $a_textheight;
        }
    }
}
