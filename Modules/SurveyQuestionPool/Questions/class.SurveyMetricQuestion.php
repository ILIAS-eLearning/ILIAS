<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Metric survey question
 * The SurveyMetricQuestion class defines and encapsulates basic methods and attributes
 * for metric survey question types.
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class SurveyMetricQuestion extends SurveyQuestion
{
    public const SUBTYPE_NON_RATIO = 3;
    public const SUBTYPE_RATIO_NON_ABSOLUTE = 4;
    public const SUBTYPE_RATIO_ABSOLUTE = 5;
    
    public int $subtype;

    public ?float $minimum;
    public ?float $maximum;

    public function __construct(
        string $title = "",
        string $description = "",
        string $author = "",
        string $questiontext = "",
        int $owner = -1,
        int $subtype = self::SUBTYPE_NON_RATIO
    ) {
        global $DIC;

        $this->db = $DIC->database();
        parent::__construct($title, $description, $author, $questiontext, $owner);
        
        $this->subtype = $subtype;
        $this->minimum = null;
        $this->maximum = null;
    }
    
    public function setSubtype(int $a_subtype = self::SUBTYPE_NON_RATIO) : void
    {
        $this->subtype = $a_subtype;
    }

    public function setMinimum(?float $minimum = null) : void
    {
        $this->minimum = $minimum;
    }

    public function setMaximum(?float $maximum = null) : void
    {
        $this->maximum = $maximum;
    }

    public function getSubtype() : ?int
    {
        return $this->subtype;
    }
    
    public function getMinimum() : ?float
    {
        if (is_null($this->minimum) && $this->getSubtype() > 3) {
            $this->minimum = 0;
        }
        return $this->minimum;
    }
    
    public function getMaximum() : ?float
    {
        return $this->maximum;
    }
    
    public function getQuestionDataArray(int $id) : array
    {
        $ilDB = $this->db;
        
        $result = $ilDB->queryF(
            "SELECT svy_question.*, " . $this->getAdditionalTableName() . ".* FROM svy_question, " . $this->getAdditionalTableName() . " WHERE svy_question.question_id = %s AND svy_question.question_id = " . $this->getAdditionalTableName() . ".question_fi",
            array('integer'),
            array($id)
        );
        if ($result->numRows() === 1) {
            return $ilDB->fetchAssoc($result);
        } else {
            return array();
        }
    }
    
    public function loadFromDb(int $question_id) : void
    {
        $ilDB = $this->db;

        $result = $ilDB->queryF(
            "SELECT svy_question.*, " . $this->getAdditionalTableName() . ".* FROM svy_question LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = svy_question.question_id WHERE svy_question.question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() === 1) {
            $data = $ilDB->fetchAssoc($result);
            $this->setId((int) $data["question_id"]);
            $this->setTitle((string) $data["title"]);
            $this->setDescription((string) $data["description"]);
            $this->setObjId((int) $data["obj_fi"]);
            $this->setAuthor((string) $data["author"]);
            $this->setOwner((int) $data["owner_fi"]);
            $this->label = (string) $data['label'];
            $this->setQuestiontext(ilRTE::_replaceMediaObjectImageSrc((string) $data["questiontext"], 1));
            $this->setObligatory((bool) $data["obligatory"]);
            $this->setComplete((bool) $data["complete"]);
            $this->setOriginalId((int) $data["original_id"]);
            $this->setSubtype((int) $data["subtype"]);

            $result = $ilDB->queryF(
                "SELECT svy_variable.* FROM svy_variable WHERE svy_variable.question_fi = %s",
                array('integer'),
                array($question_id)
            );
            if ($result->numRows() > 0) {
                if ($data = $ilDB->fetchAssoc($result)) {
                    $this->minimum = is_null($data["value1"]) ? null : (float) $data["value1"];
                    if (($data["value2"] < 0) or (strcmp($data["value2"], "") == 0)) {
                        $this->maximum = null;
                    } else {
                        $this->maximum = is_null($data["value2"]) ? null : (float) $data["value2"];
                    }
                }
            }
        }
        parent::loadFromDb($question_id);
    }

    public function isComplete() : bool
    {
        return (
            $this->getTitle() !== '' &&
            $this->getAuthor() !== '' &&
            $this->getQuestiontext() !== ''
        );
    }
    
    public function saveToDb(int $original_id = 0) : int
    {
        $ilDB = $this->db;

        $affectedRows = parent::saveToDb($original_id);
        if ($affectedRows === 1) {
            $ilDB->manipulateF(
                "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
                array('integer'),
                array($this->getId())
            );
            $ilDB->manipulateF(
                "INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, subtype) VALUES (%s, %s)",
                array('integer', 'text'),
                array($this->getId(), $this->getSubtype())
            );

            // saving material uris in the database
            $this->saveMaterial();
            
            // save categories
            $ilDB->manipulateF(
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
            $ilDB->manipulateF(
                "INSERT INTO svy_variable (variable_id, category_fi, question_fi, value1, value2, sequence, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                array('integer','integer','integer','float','float','integer','integer'),
                array($next_id, 0, $this->getId(), $this->getMinimum(), $max, 0, time())
            );
        }
        return $affectedRows;
    }
    
    public function toXML(
        bool $a_include_header = true
    ) : string {
        $a_xml_writer = new ilXmlWriter();
        $a_xml_writer->xmlHeader();
        $this->insertXML($a_xml_writer, $a_include_header);
        $xml = $a_xml_writer->xmlDumpMem(false);
        if (!$a_include_header) {
            $pos = strpos($xml, "?>");
            $xml = substr($xml, $pos + 2);
        }
        return $xml;
    }
    
    public function insertXML(
        ilXmlWriter $a_xml_writer,
        bool $a_include_header = true
    ) : void {
        $attrs = array(
            "id" => $this->getId(),
            "title" => $this->getTitle(),
            "type" => $this->getQuestionType(),
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
            case 4:
            case 3:
                $attrs = array(
                    "id" => "0",
                    "format" => "double"
                );
                if ((string) $this->getMinimum() !== '') {
                    $attrs["min"] = $this->getMinimum();
                }
                if ((string) $this->getMaximum() !== '') {
                    $attrs["max"] = $this->getMaximum();
                }
                break;
            case 5:
                $attrs = array(
                    "id" => "0",
                    "format" => "integer"
                );
                if ((string) $this->getMinimum() !== '') {
                    $attrs["min"] = $this->getMinimum();
                }
                if ((string) $this->getMaximum() !== '') {
                    $attrs["max"] = $this->getMaximum();
                }
                break;
        }
        $a_xml_writer->xmlStartTag("response_num", $attrs);
        $a_xml_writer->xmlEndTag("response_num");

        $a_xml_writer->xmlEndTag("responses");

        if (count($this->material) && preg_match(
            "/il_(\d*?)_(\w+)_(\d+)/",
            $this->material["internal_link"],
            $matches
        )) {
            $attrs = array(
                "label" => $this->material["title"]
            );
            $a_xml_writer->xmlStartTag("material", $attrs);
            $intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
            if (strcmp($matches[1], "") !== 0) {
                $intlink = $this->material["internal_link"];
            }
            $a_xml_writer->xmlElement("mattext", null, $intlink);
            $a_xml_writer->xmlEndTag("material");
        }
        
        $a_xml_writer->xmlEndTag("question");
    }

    public function getQuestionTypeID() : int
    {
        $ilDB = $this->db;
        $result = $ilDB->queryF(
            "SELECT questiontype_id FROM svy_qtype WHERE type_tag = %s",
            array('text'),
            array($this->getQuestionType())
        );
        $row = $ilDB->fetchAssoc($result);
        return (int) $row["questiontype_id"];
    }

    public function getQuestionType() : string
    {
        return "SurveyMetricQuestion";
    }
    
    public function getAdditionalTableName() : string
    {
        return "svy_qst_metric";
    }
    
    public function getWorkingDataFromUserInput(array $post_data) : array
    {
        $entered_value = $post_data[$this->getId() . "_metric_question"] ?? "";
        $data = array();
        if (strlen($entered_value)) {
            $data[] = array("value" => $entered_value);
        }
        return $data;
    }
    
    /**
     * @return string Empty string if the input is ok, an error message otherwise
     */
    public function checkUserInput(
        array $post_data,
        int $survey_id
    ) : string {
        $entered_value = $post_data[$this->getId() . "_metric_question"];
        // replace german notation with international notation
        $entered_value = str_replace(",", ".", $entered_value);
        
        if ((!$this->getObligatory()) && (strlen($entered_value) == 0)) {
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
            } elseif ($entered_value > $this->getMaximum()) {
                return $this->lng->txt("metric_question_out_of_bounds");
            }
        }

        if (!is_numeric($entered_value)) {
            return $this->lng->txt("metric_question_not_a_value");
        }

        if ($this->getSubtype() === self::SUBTYPE_RATIO_ABSOLUTE && ((int) $entered_value != (float) $entered_value)) {
            return $this->lng->txt("metric_question_floating_point");
        }
        return "";
    }
    
    public function saveUserInput(
        array $post_data,
        int $active_id,
        bool $a_return = false
    ) : ?array {
        $ilDB = $this->db;
        
        $entered_value = $post_data[$this->getId() . "_metric_question"];
        
        // replace german notation with international notation
        $entered_value = str_replace(",", ".", $entered_value);
        
        if ($a_return) {
            return array(array("value" => $entered_value, "textanswer" => null));
        }

        if ($entered_value === '') {
            return null;
        }
        
        $next_id = $ilDB->nextId('svy_answer');
        #20216
        $fields = array();
        $fields['answer_id'] = array("integer", $next_id);
        $fields['question_fi'] = array("integer", $this->getId());
        $fields['active_fi'] = array("integer", $active_id);
        $fields['value'] = array("float", $entered_value);
        $fields['textanswer'] = array("clob", null);
        $fields['tstamp'] = array("integer", time());

        $ilDB->insert("svy_answer", $fields);

        return null;
    }

    public function importResponses(array $a_data) : void
    {
        foreach ($a_data as $id => $data) {
            $this->setMinimum($data["min"]);
            $this->setMaximum($data["max"]);
        }
    }

    public function usableForPrecondition() : bool
    {
        return true;
    }

    public function getAvailableRelations() : array
    {
        return array("<", "<=", "=", "<>", ">=", ">");
    }

    public function outPreconditionSelectValue(
        ilTemplate $template
    ) : void {
        $template->setCurrentBlock("textfield");
        $template->setVariable("TEXTFIELD_VALUE", "");
        $template->parseCurrentBlock();
    }
    
    /**
    * Creates a form property for the precondition value
    * @return ilFormPropertyGUI|null ILIAS form element
    * @access public
    */
    public function getPreconditionSelectValue(
        string $default,
        string $title,
        string $variable
    ) : ?ilFormPropertyGUI {
        $step3 = new ilNumberInputGUI($title, $variable);
        $step3->setValue($default);
        return $step3;
    }

    /**
     * Creates a text for the input range of the metric question
     */
    public function getMinMaxText() : string
    {
        $min = (string) $this->getMinimum();
        $max = (string) $this->getMaximum();
        if ($min !== '' && $max !== '') {
            return "(" . $min . " " . strtolower($this->lng->txt("to")) . " " . $max . ")";
        } elseif ($min !== '') {
            return "(&gt;= " . $min . ")";
        } elseif ($max !== '') {
            return "(&lt;= " . $max . ")";
        } else {
            return "";
        }
    }
}
