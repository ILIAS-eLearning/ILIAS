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
 * MultipleChoice survey question
 * The SurveyMultipleChoiceQuestion class defines and encapsulates basic methods and attributes
 * for multiple choice survey question types.
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class SurveyMultipleChoiceQuestion extends SurveyQuestion
{
    public SurveyCategories $categories;

    public function __construct(
        string $title = "",
        string $description = "",
        string $author = "",
        string $questiontext = "",
        int $owner = -1,
        int $orientation = 0
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        parent::__construct($title, $description, $author, $questiontext, $owner);

        $this->orientation = $orientation;
        $this->categories = new SurveyCategories();
    }

    public function getQuestionDataArray(int $id): array
    {
        $ilDB = $this->db;

        $result = $ilDB->queryF(
            "SELECT svy_question.*, " . $this->getAdditionalTableName() . ".* FROM svy_question, " . $this->getAdditionalTableName() . " WHERE svy_question.question_id = %s AND svy_question.question_id = " . $this->getAdditionalTableName() . ".question_fi",
            array('integer'),
            array($id)
        );
        if ($result->numRows() === 1) {
            return $ilDB->fetchAssoc($result);
        }

        return array();
    }

    public function loadFromDb(int $question_id): void
    {
        $ilDB = $this->db;

        $result = $ilDB->queryF(
            "SELECT svy_question.*, " . $this->getAdditionalTableName() . ".* FROM svy_question LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = svy_question.question_id WHERE svy_question.question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() === 1) {
            $data = $ilDB->fetchAssoc($result);
            $this->setId($data["question_id"]);
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
            $this->setOrientation((int) $data["orientation"]);
            $this->use_min_answers = (bool) $data['use_min_answers'];
            $this->nr_min_answers = (string) $data['nr_min_answers'];
            $this->nr_max_answers = (string) $data['nr_max_answers'];

            $this->categories->flushCategories();
            $result = $ilDB->queryF(
                "SELECT svy_variable.*, svy_category.title, svy_category.neutral FROM svy_variable, svy_category WHERE svy_variable.question_fi = %s AND svy_variable.category_fi = svy_category.category_id ORDER BY sequence ASC",
                array('integer'),
                array($question_id)
            );
            if ($result->numRows() > 0) {
                while ($data = $ilDB->fetchAssoc($result)) {
                    $this->categories->addCategory($data["title"], $data["other"], $data["neutral"], null, ($data['scale']) ?: ($data['sequence'] + 1));
                }
            }
        }
        parent::loadFromDb($question_id);
    }

    public function isComplete(): bool
    {
        return (
            $this->getTitle() !== '' &&
            $this->getAuthor() !== '' &&
            $this->getQuestiontext() !== '' &&
            $this->categories->getCategoryCount()
        );
    }

    public function saveToDb(int $original_id = 0): int
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
                "INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, orientation, use_min_answers, nr_min_answers, nr_max_answers) VALUES (%s, %s, %s, %s, %s)",
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
        return $affectedRows;
    }

    public function saveCategoriesToDb(): void
    {
        $ilDB = $this->db;

        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_variable WHERE question_fi = %s",
            array('integer'),
            array($this->getId())
        );

        for ($i = 0; $i < $this->categories->getCategoryCount(); $i++) {
            $cat = $this->categories->getCategory($i);
            $category_id = $this->saveCategoryToDb($cat->title, $cat->neutral);
            $next_id = $ilDB->nextId('svy_variable');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_variable (variable_id, category_fi, question_fi, value1, other, sequence, scale, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
                array('integer','integer','integer','float','integer','integer', 'integer','integer'),
                array($next_id, $category_id, $this->getId(), ($i + 1), $cat->other, $i, ($cat->scale > 0) ? $cat->scale : null, time())
            );
        }
        $this->saveCompletionStatus();
    }

    public function toXML(
        bool $a_include_header = true,
        bool $obligatory_state = false
    ): string {
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

    /**
     * Adds the question XML to a given XMLWriter object
     */
    public function insertXML(
        ilXmlWriter $a_xml_writer,
        bool $a_include_header = true
    ): void {
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

        for ($i = 0; $i < $this->categories->getCategoryCount(); $i++) {
            $attrs = array(
                "id" => $i
            );
            if (strlen($this->categories->getCategory($i)->other)) {
                $attrs['other'] = $this->categories->getCategory($i)->other;
            }
            if (strlen($this->categories->getCategory($i)->neutral)) {
                $attrs['neutral'] = $this->categories->getCategory($i)->neutral;
            }
            if (strlen($this->categories->getCategory($i)->label)) {
                $attrs['label'] = $this->categories->getCategory($i)->label;
            }
            if (strlen($this->categories->getCategory($i)->scale)) {
                $attrs['scale'] = $this->categories->getCategory($i)->scale;
            }
            $a_xml_writer->xmlStartTag("response_multiple", $attrs);
            $this->addMaterialTag($a_xml_writer, $this->categories->getCategory($i)->title);
            $a_xml_writer->xmlEndTag("response_multiple");
        }

        $a_xml_writer->xmlEndTag("responses");

        if (count($this->material)) {
            if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $this->material["internal_link"], $matches)) {
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
        }

        $a_xml_writer->xmlStartTag("metadata");
        $a_xml_writer->xmlStartTag("metadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "orientation");
        $a_xml_writer->xmlElement("fieldentry", null, $this->getOrientation());
        $a_xml_writer->xmlEndTag("metadatafield");
        $a_xml_writer->xmlStartTag("metadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "use_min_answers");
        $a_xml_writer->xmlElement("fieldentry", null, $this->use_min_answers);
        $a_xml_writer->xmlEndTag("metadatafield");
        $a_xml_writer->xmlStartTag("metadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "nr_min_answers");
        $a_xml_writer->xmlElement("fieldentry", null, $this->nr_min_answers);
        $a_xml_writer->xmlEndTag("metadatafield");
        $a_xml_writer->xmlStartTag("metadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "nr_max_answers");
        $a_xml_writer->xmlElement("fieldentry", null, $this->nr_max_answers);
        $a_xml_writer->xmlEndTag("metadatafield");
        $a_xml_writer->xmlEndTag("metadata");

        $a_xml_writer->xmlEndTag("question");
    }

    public function getQuestionType(): string
    {
        return "SurveyMultipleChoiceQuestion";
    }

    public function getAdditionalTableName(): string
    {
        return "svy_qst_mc";
    }

    public function getWorkingDataFromUserInput(array $post_data): array
    {
        $entered_value = $post_data[$this->getId() . "_value"] ?? "";
        $data = array();
        if (is_array($entered_value)) {
            foreach ($entered_value as $idx => $value) {
                $data[] = array("value" => $value,
                                "textanswer" => $post_data[$this->getId() . '_' . $value . '_other'] ?? ""
                );
            }
        }
        for ($i = 0; $i < $this->categories->getCategoryCount(); $i++) {
            $cat = $this->categories->getCategory($i);
            if ($cat->other) {
                // #18212
                if (!is_array($entered_value) || !in_array($i, $entered_value)) {
                    if (strlen($post_data[$this->getId() . "_" . $i . "_other"])) {
                        $data[] = array("value" => $i,
                                        "textanswer" => $post_data[$this->getId() . '_' . $i . '_other'] ?? "",
                                        "uncheck" => true
                        );
                    }
                }
            }
        }
        return $data;
    }

    /**
     * @return string Empty string if the input is ok, an error message otherwise
     */
    public function checkUserInput(
        array $post_data,
        int $survey_id
    ): string {
        $entered_value = (array) ($post_data[$this->getId() . "_value"] ?? []);
        if (!$this->getObligatory() && (count($entered_value) === 0)) {
            return "";
        }

        if ($this->use_min_answers && $this->nr_min_answers > 0 && $this->nr_max_answers > 0 && $this->nr_min_answers == $this->nr_max_answers && count($entered_value) !== $this->nr_max_answers) {
            return sprintf($this->lng->txt("err_no_exact_answers"), $this->nr_min_answers);
        }
        if ($this->use_min_answers && $this->nr_min_answers > 0 && count($entered_value) < $this->nr_min_answers) {
            return sprintf($this->lng->txt("err_no_min_answers"), $this->nr_min_answers);
        }
        if ($this->use_min_answers && $this->nr_max_answers > 0 && count($entered_value) > $this->nr_max_answers) {
            return sprintf($this->lng->txt("err_no_max_answers"), $this->nr_max_answers);
        }
        if (count($entered_value) == 0) {
            return $this->lng->txt("question_mr_not_checked");
        }
        for ($i = 0; $i < $this->categories->getCategoryCount(); $i++) {
            $cat = $this->categories->getCategory($i);
            if ($cat->other) {
                if (in_array($i, $entered_value)) {
                    if (array_key_exists($this->getId() . "_" . $i . "_other", $post_data) && !strlen($post_data[$this->getId() . "_" . $i . "_other"])) {
                        return $this->lng->txt("question_mr_no_other_answer");
                    }
                } elseif (strlen($post_data[$this->getId() . "_" . $i . "_other"] ?? "")) {
                    return $this->lng->txt("question_mr_no_other_answer_checked");
                }
            }
        }
        return "";
    }

    public function saveUserInput(
        array $post_data,
        int $active_id,
        bool $a_return = false
    ): ?array {
        $ilDB = $this->db;

        if ($a_return) {
            $return_data = array();
        }
        if (is_array($post_data[$this->getId() . "_value"])) {
            foreach ($post_data[$this->getId() . "_value"] as $entered_value) {
                if (strlen($entered_value) > 0) {
                    if (!$a_return) {
                        $next_id = $ilDB->nextId('svy_answer');

                        #20216
                        $fields = array();
                        $fields['answer_id'] = array("integer", $next_id);
                        $fields['question_fi'] = array("integer", $this->getId());
                        $fields['active_fi'] = array("integer", $active_id);
                        $fields['value'] = array("float", (strlen($entered_value)) ? $entered_value : null);
                        $fields['textanswer'] = array("clob", isset($post_data[$this->getId() . "_" . $entered_value . "_other"]) ? $this->stripSlashesAddSpaceFallback($post_data[$this->getId() . "_" . $entered_value . "_other"]) : null);
                        $fields['tstamp'] = array("integer", time());

                        $affectedRows = $ilDB->insert("svy_answer", $fields);
                    } else {
                        $return_data[] = array("value" => $entered_value,
                                "textanswer" => $post_data[$this->getId() . "_" . $entered_value . "_other"] ?? "");
                    }
                }
            }
        }
        if ($a_return) {
            return $return_data;
        }
        return null;
    }

    public function importAdditionalMetadata(array $a_meta): void
    {
        foreach ($a_meta as $key => $value) {
            switch ($value["label"]) {
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

    public function importResponses(array $a_data): void
    {
        foreach ($a_data as $id => $data) {
            $categorytext = "";
            foreach ($data["material"] as $material) {
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

    public function usableForPrecondition(): bool
    {
        return true;
    }

    public function getAvailableRelations(): array
    {
        return array("=", "<>");
    }

    public function getPreconditionOptions(): array
    {
        $options = array();
        for ($i = 0; $i < $this->categories->getCategoryCount(); $i++) {
            $category = $this->categories->getCategory($i);
            $options[$category->scale - 1] = $category->scale . " - " . $category->title;
        }
        return $options;
    }

    public function getPreconditionSelectValue(
        string $default,
        string $title,
        string $variable
    ): ?ilFormPropertyGUI {
        $step3 = new ilSelectInputGUI($title, $variable);
        $options = $this->getPreconditionOptions();
        $step3->setOptions($options);
        $step3->setValue($default);
        return $step3;
    }

    public function getPreconditionValueOutput(
        string $value
    ): string {
        // #18136
        $category = $this->categories->getCategoryForScale((int) $value + 1);

        // #17895 - see getPreconditionOptions()
        return $category->scale .
            " - " .
            ((strlen($category->title)) ? $category->title : $this->lng->txt('other_answer'));
    }

    public function getCategories(): SurveyCategories
    {
        return $this->categories;
    }

    public static function getMaxSumScore(int $survey_id): int
    {
        global $DIC;

        // we need sum of scale values of multiple choice questions (type 1)
        $db = $DIC->database();
        $set = $db->queryF(
            "SELECT SUM(scale) sum_sum_score FROM svy_svy_qst sq " .
            "JOIN svy_question q ON (sq.question_fi = q.question_id) " .
            "JOIN svy_variable v ON (v.question_fi = q.question_id) " .
            "WHERE sq.survey_fi  = %s AND q.questiontype_fi = %s ",
            ["integer", "integer"],
            [$survey_id, 1]
        );
        $rec = $db->fetchAssoc($set);
        return (int) $rec["sum_sum_score"];
    }
}
