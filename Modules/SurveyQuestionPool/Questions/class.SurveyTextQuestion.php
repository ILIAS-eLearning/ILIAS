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
 * Text survey question
 *
 * The SurveyTextQuestion class defines and encapsulates basic methods and attributes
 * for text survey question types.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class SurveyTextQuestion extends SurveyQuestion
{
    protected ?int $maxchars = null;
    protected ?int $textwidth = null;
    protected ?int $textheight = null;
    
    public function __construct(
        string $title = "",
        string $description = "",
        string $author = "",
        string $questiontext = "",
        int $owner = -1
    ) {
        global $DIC;

        $this->db = $DIC->database();
        parent::__construct($title, $description, $author, $questiontext, $owner);
        
        $this->maxchars = 0;
        $this->textwidth = 50;
        $this->textheight = 5;
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
            $this->label = (string) $data['label'];
            $this->setDescription((string) $data["description"]);
            $this->setObjId((int) $data["obj_fi"]);
            $this->setAuthor((string) $data["author"]);
            $this->setOwner((int) $data["owner_fi"]);
            $this->setQuestiontext(ilRTE::_replaceMediaObjectImageSrc((string) $data["questiontext"], 1));
            $this->setObligatory((bool) $data["obligatory"]);
            $this->setComplete((bool) $data["complete"]);
            $this->setOriginalId((int) $data["original_id"]);

            $this->setMaxChars((int) $data["maxchars"]);
            $this->setTextWidth($data["width"] ? (int) $data["width"] : null);
            $this->setTextHeight($data["height"] ? (int) $data["height"] : null);
        }
        parent::loadFromDb($question_id);
    }

    public function isComplete() : bool
    {
        if (
            strlen($this->getTitle()) &&
            strlen($this->getAuthor()) &&
            strlen($this->getQuestiontext())
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function setMaxChars(int $maxchars = 0) : void
    {
        $this->maxchars = $maxchars;
    }

    public function getMaxChars() : int
    {
        return $this->maxchars;
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
                "INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, maxchars, width, height) VALUES (%s, %s, %s, %s)",
                array('integer', 'integer', 'integer', 'integer'),
                array($this->getId(), $this->getMaxChars(), $this->getTextWidth(), $this->getTextHeight())
            );

            $this->saveMaterial();
        }
        return $affectedRows;
    }

    public function toXML(
        bool $a_include_header = true,
        bool $obligatory_state = false
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
        $a_include_header = true
    ) : void {
        $attrs = array(
            "id" => $this->getId(),
            "title" => $this->getTitle(),
            "type" => $this->getQuestionType(),
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

    public function getQuestionType() : string
    {
        return "SurveyTextQuestion";
    }

    public function getAdditionalTableName() : string
    {
        return "svy_qst_text";
    }
    
    public function getWorkingDataFromUserInput(
        array $post_data
    ) : array {
        $entered_value = $post_data[$this->getId() . "_text_question"] ?? "";
        $data = array();
        if (strlen($entered_value)) {
            $data[] = array("textanswer" => $entered_value);
        }
        return $data;
    }
    
    /**
     * Checks the input of the active user for obligatory status
     * and entered values
     */
    public function checkUserInput(
        array $post_data,
        int $survey_id
    ) : string {
        $entered_value = $post_data[$this->getId() . "_text_question"];
        
        if ((!$this->getObligatory()) && (strlen($entered_value) == 0)) {
            return "";
        }
        
        if (strlen($entered_value) == 0) {
            return $this->lng->txt("text_question_not_filled_out");
        }

        // see bug #22648
        if ($this->getMaxChars() > 0 && ilStr::strLen($entered_value) > $this->getMaxChars()) {
            return str_replace("%s", ilStr::strLen($entered_value), $this->lng->txt("svy_answer_too_long"));
        }

        return "";
    }
    
    public function saveUserInput(
        array $post_data,
        int $active_id,
        bool $a_return = false
    ) : ?array {
        $ilDB = $this->db;

        $entered_value = $this->stripSlashesAddSpaceFallback($post_data[$this->getId() . "_text_question"]);
        $maxchars = $this->getMaxChars();

        if ($maxchars > 0) {
            $entered_value = ilStr::subStr($entered_value, 0, $maxchars);
        }
        
        if ($a_return) {
            return array(array("value" => null, "textanswer" => $entered_value));
        }
        if (strlen($entered_value) == 0) {
            return null;
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

        $ilDB->insert("svy_answer", $fields);

        return null;
    }
    
    public function importResponses(array $a_data) : void
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

    public function usableForPrecondition() : bool
    {
        return false;
    }

    public function getTextWidth() : ?int
    {
        return $this->textwidth;
    }
    
    public function getTextHeight() : ?int
    {
        return $this->textheight;
    }
    
    public function setTextWidth(?int $a_textwidth = null) : void
    {
        if ($a_textwidth < 1) {
            $this->textwidth = 50;
        } else {
            $this->textwidth = $a_textwidth;
        }
    }

    public function setTextHeight(?int $a_textheight = null) : void
    {
        if ($a_textheight < 1) {
            $this->textheight = 5;
        } else {
            $this->textheight = $a_textheight;
        }
    }
}
