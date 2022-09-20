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
 * The SurveyMatrixQuestion class defines and encapsulates basic methods and attributes
 * for matrix question types.
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class SurveyMatrixQuestion extends SurveyQuestion
{
    public SurveyCategories $columns;
    public SurveyCategories $rows;
    // First bipolar adjective for ordinal matrix questions
    public string $bipolar_adjective1 = "";
    // Second bipolar adjective for ordinal matrix questions
    public string $bipolar_adjective2 = "";
    // Enable state of separators for matrix columns
    public bool $columnSeparators = false;
    // Enable state of separators for matrix rows
    public bool $rowSeparators = false;
    // Enable state of a separator for the neutral column
    public bool $neutralColumnSeparator = false;
    public array $layout;
    // Use placeholders for the column titles
    public bool $columnPlaceholders = false;
    public bool $legend = false;
    public bool $singleLineRowCaption = false;
    public bool $repeatColumnHeader = false;

    /**
     * Matrix question subtype
     * 0 = Single choice
     * 1 = Multiple choice
     * 2 = Text
     * 3 = Integer
     * 4 = Double
     * 5 = Date
     * 6 = Time
     */
    public int $subtype;

    public function __construct(
        string $title = "",
        string $description = "",
        string $author = "",
        string $questiontext = "",
        int $owner = -1
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->db = $DIC->database();
        parent::__construct($title, $description, $author, $questiontext, $owner);

        $this->subtype = 0;
        $this->columns = new SurveyCategories();
        $this->rows = new SurveyCategories();
        $this->bipolar_adjective1 = "";
        $this->bipolar_adjective2 = "";
        $this->rowSeparators = 0;
        $this->columnSeparators = 0;
        $this->neutralColumnSeparator = 1;
    }

    public function getColumnCount(): int
    {
        return $this->columns->getCategoryCount();
    }

    public function removeColumn(int $index): void
    {
        $this->columns->removeCategory($index);
    }

    /**
     * @param int[] $array index positions
     */
    public function removeColumns(array $array): void
    {
        $this->columns->removeCategories($array);
    }

    public function removeColumnWithName(string $name): void
    {
        $this->columns->removeCategoryWithName($name);
    }

    public function getColumns(): SurveyCategories
    {
        return $this->columns;
    }

    public function getColumn(int $index): ?ilSurveyCategory
    {
        return $this->columns->getCategory($index);
    }

    public function getColumnForScale(int $scale): ?ilSurveyCategory
    {
        return $this->columns->getCategoryForScale($scale);
    }

    public function getColumnIndex(string $name): int
    {
        return $this->columns->getCategoryIndex($name);
    }

    public function flushColumns(): void
    {
        $this->columns->flushCategories();
    }

    public function getRowCount(): int
    {
        return $this->rows->getCategoryCount();
    }

    public function addRow(
        string $a_text,
        string $a_other,
        string $a_label
    ): void {
        $this->rows->addCategory($a_text, (int) $a_other, 0, $a_label);
    }

    public function addRowAtPosition(
        string $a_text,
        string $a_other,
        int $a_position
    ): void {
        $this->rows->addCategoryAtPosition($a_text, $a_position, $a_other);
    }

    public function flushRows(): void
    {
        $this->rows = new SurveyCategories();
    }

    public function getRow(int $a_index): ?ilSurveyCategory
    {
        return $this->rows->getCategory($a_index);
    }

    public function moveRowUp(int $index): void
    {
        $this->rows->moveCategoryUp($index);
    }

    public function moveRowDown(int $index): void
    {
        $this->rows->moveCategoryDown($index);
    }

    /**
     * @param int[] $array index positions
     */
    public function removeRows(array $array): void
    {
        $this->rows->removeCategories($array);
    }

    public function removeRow(int $index): void
    {
        $this->rows->removeCategory($index);
    }

    /**
     * Returns one of the bipolar adjectives
     * @param int $a_index bipolar adjective (0 first,  and 1 for the second)
     */
    public function getBipolarAdjective(int $a_index): string
    {
        if ($a_index === 1) {
            return $this->bipolar_adjective2;
        }
        return $this->bipolar_adjective1;
    }

    public function setBipolarAdjective(
        int $a_index,
        string $a_value
    ): void {
        if ($a_index === 1) {
            $this->bipolar_adjective2 = $a_value;
        } else {
            $this->bipolar_adjective1 = $a_value;
        }
    }

    /**
     * Adds a phrase to the question
     */
    public function addPhrase(int $phrase_id): void
    {
        $ilUser = $this->user;
        $ilDB = $this->db;

        $result = $ilDB->queryF(
            "SELECT svy_category.* FROM svy_category, svy_phrase_cat WHERE svy_phrase_cat.category_fi = svy_category.category_id AND svy_phrase_cat.phrase_fi = %s AND (svy_category.owner_fi = %s OR svy_category.owner_fi = %s) ORDER BY svy_phrase_cat.sequence",
            array('integer', 'integer', 'integer'),
            array($phrase_id, 0, $ilUser->getId())
        );
        while ($row = $ilDB->fetchAssoc($result)) {
            $neutral = $row["neutral"];
            if ((int) $row["defaultvalue"] === 1 && (int) $row["owner_fi"] === 0) {
                $this->columns->addCategory($this->lng->txt($row["title"]), 0, $neutral);
            } else {
                $this->columns->addCategory($row["title"], 0, $neutral);
            }
        }
    }

    /**
     * Returns the question data fields from the database
     */
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
            $this->setSubtype((int) $data["subtype"]);
            $this->setRowSeparators((bool) $data["row_separators"]);
            $this->setNeutralColumnSeparator((bool) $data["neutral_column_separator"]);
            $this->setColumnSeparators((bool) $data["column_separators"]);
            $this->setColumnPlaceholders((bool) $data["column_placeholders"]);
            $this->setLegend((bool) $data["legend"]);
            $this->setSingleLineRowCaption((string) $data["singleline_row_caption"]);
            $this->setRepeatColumnHeader((bool) $data["repeat_column_header"]);
            $this->setBipolarAdjective(0, (string) $data["bipolar_adjective1"]);
            $this->setBipolarAdjective(1, (string) $data["bipolar_adjective2"]);
            $this->setLayout($data["layout"]);
            $this->flushColumns();

            $result = $ilDB->queryF(
                "SELECT svy_variable.*, svy_category.title, svy_category.neutral FROM svy_variable, svy_category WHERE svy_variable.question_fi = %s AND svy_variable.category_fi = svy_category.category_id ORDER BY sequence ASC",
                array('integer'),
                array($question_id)
            );
            if ($result->numRows() > 0) {
                while ($data = $ilDB->fetchAssoc($result)) {
                    $this->columns->addCategory($data["title"], (int) $data["other"], (int) $data["neutral"], null, ($data['scale']) ?: ($data['sequence'] + 1));
                }
            }

            $result = $ilDB->queryF(
                "SELECT * FROM svy_qst_matrixrows WHERE question_fi = %s ORDER BY sequence",
                array('integer'),
                array($question_id)
            );
            while ($row = $ilDB->fetchAssoc($result)) {
                $this->addRow($row["title"], $row['other'], $row['label']);
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
            $this->getColumnCount() &&
            $this->getRowCount()
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
                "INSERT INTO " . $this->getAdditionalTableName() . " (
				question_fi, subtype, column_separators, row_separators, neutral_column_separator,column_placeholders,
				legend, singleline_row_caption, repeat_column_header,
				bipolar_adjective1, bipolar_adjective2, layout, tstamp)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                array(
                    'integer', 'integer', 'text', 'text', 'text', 'integer', 'text', 'text', 'text',
                    'text', 'text', 'text', 'integer'
                ),
                array(
                    $this->getId(),
                    $this->getSubtype(),
                    $this->getColumnSeparators(),
                    $this->getRowSeparators(),
                    $this->getNeutralColumnSeparator(),
                    $this->getColumnPlaceholders(),
                    $this->getLegend(),
                    $this->getSingleLineRowCaption(),
                    $this->getRepeatColumnHeader(),
                    $this->getBipolarAdjective(0),
                    $this->getBipolarAdjective(1),
                    serialize($this->getLayout()),
                    time()
                )
            );

            // saving material uris in the database
            $this->saveMaterial();

            $this->saveColumnsToDb();
            $this->saveRowsToDb();
        }
        return $affectedRows;
    }

    public function saveBipolarAdjectives(
        string $adjective1,
        string $adjective2
    ): void {
        $ilDB = $this->db;

        $ilDB->manipulateF(
            "UPDATE " . $this->getAdditionalTableName() . " SET bipolar_adjective1 = %s, bipolar_adjective2 = %s WHERE question_fi = %s",
            array('text', 'text', 'integer'),
            array(($adjective1 !== '') ? $adjective1 : null, ($adjective2 !== '') ? $adjective2 : null, $this->getId())
        );
    }

    public function saveColumnToDb(
        string $columntext,
        int $neutral = 0
    ): int {
        $ilUser = $this->user;
        $ilDB = $this->db;

        $result = $ilDB->queryF(
            "SELECT title, category_id FROM svy_category WHERE title = %s AND neutral = %s AND owner_fi = %s",
            array('text', 'text', 'integer'),
            array($columntext, $neutral, $ilUser->getId())
        );
        $insert = false;
        $returnvalue = "";
        $insert = true;
        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                if (strcmp($row["title"] ?? '', $columntext) === 0) {
                    $returnvalue = $row["category_id"];
                    $insert = false;
                }
            }
        }
        if ($insert) {
            $next_id = $ilDB->nextId('svy_category');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_category (category_id, title, defaultvalue, owner_fi, neutral, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
                array('integer', 'text', 'text', 'integer', 'text', 'integer'),
                array($next_id, $columntext, 0, $ilUser->getId(), $neutral, time())
            );
            $returnvalue = $next_id;
        }
        return $returnvalue;
    }

    public function saveColumnsToDb(
        int $original_id = 0
    ): void {
        $ilDB = $this->db;

        // save columns
        $question_id = $this->getId();
        if ($original_id > 0) {
            $question_id = $original_id;
        }

        // delete existing column relations
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_variable WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        // create new column relations
        for ($i = 0; $i < $this->getColumnCount(); $i++) {
            $cat = $this->getColumn($i);
            $column_id = $this->saveColumnToDb($cat->title, $cat->neutral);
            $next_id = $ilDB->nextId('svy_variable');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_variable (variable_id, category_fi, question_fi, value1, other, sequence, scale, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
                array('integer','integer','integer','float','integer','integer', 'integer','integer'),
                array($next_id, $column_id, $question_id, ($i + 1), $cat->other, $i, ($cat->scale > 0) ? $cat->scale : null, time())
            );
        }
        $this->saveCompletionStatus($original_id);
    }

    public function saveRowsToDb(
        int $original_id = 0
    ): void {
        $ilDB = $this->db;

        // save rows
        $question_id = $this->getId();
        if ($original_id > 0) {
            $question_id = $original_id;
        }

        // delete existing rows
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_qst_matrixrows WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        // create new rows
        for ($i = 0; $i < $this->getRowCount(); $i++) {
            $row = $this->getRow($i);
            $next_id = $ilDB->nextId('svy_qst_matrixrows');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_qst_matrixrows (id_svy_qst_matrixrows, title, label, other, sequence, question_fi) VALUES (%s, %s, %s, %s, %s, %s)",
                array('integer','text','text','integer','integer','integer'),
                array($next_id, $row->title, $row->label, ($row->other) ? 1 : 0, $i, $question_id)
            );
        }
        $this->saveCompletionStatus($original_id);
    }

    /**
    * Returns an xml representation of the question
    *
    * @return string The xml representation of the question
    * @access public
    */
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
            "subtype" => $this->getSubtype(),
            "obligatory" => $this->getObligatory()
        );
        $a_xml_writer->xmlStartTag("question", $attrs);

        $a_xml_writer->xmlElement("description", null, $this->getDescription());
        $a_xml_writer->xmlElement("author", null, $this->getAuthor());
        $a_xml_writer->xmlStartTag("questiontext");
        $this->addMaterialTag($a_xml_writer, $this->getQuestiontext());
        $a_xml_writer->xmlEndTag("questiontext");

        $a_xml_writer->xmlStartTag("matrix");
        $a_xml_writer->xmlStartTag("matrixrows");
        for ($i = 0; $i < $this->getRowCount(); $i++) {
            $attrs = array(
                "id" => $i
            );
            if (strlen($this->getRow($i)->label)) {
                $attrs['label'] = $this->getRow($i)->label;
            }
            if ($this->getRow($i)->other) {
                $attrs['other'] = 1;
            }
            $a_xml_writer->xmlStartTag("matrixrow", $attrs);
            $this->addMaterialTag($a_xml_writer, $this->getRow($i)->title);
            $a_xml_writer->xmlEndTag("matrixrow");
        }
        $a_xml_writer->xmlEndTag("matrixrows");

        $a_xml_writer->xmlStartTag("responses");
        if ($this->getBipolarAdjective(0) !== '' && ($this->getBipolarAdjective(1) !== '')) {
            $a_xml_writer->xmlStartTag("bipolar_adjectives");
            $attribs = array(
                "label" => "0"
            );
            $a_xml_writer->xmlElement("adjective", $attribs, $this->getBipolarAdjective(0));
            $attribs = array(
                "label" => "1"
            );
            $a_xml_writer->xmlElement("adjective", $attribs, $this->getBipolarAdjective(1));
            $a_xml_writer->xmlEndTag("bipolar_adjectives");
        }
        for ($i = 0; $i < $this->getColumnCount(); $i++) {
            $attrs = array(
                "id" => $i
            );
            if ($this->getColumn($i)->neutral) {
                $attrs['label'] = 'neutral';
            }
            switch ($this->getSubtype()) {
                case 0:
                    $a_xml_writer->xmlStartTag("response_single", $attrs);
                    break;
                case 1:
                    $a_xml_writer->xmlStartTag("response_multiple", $attrs);
                    break;
            }
            $this->addMaterialTag($a_xml_writer, $this->getColumn($i)->title);
            switch ($this->getSubtype()) {
                case 0:
                    $a_xml_writer->xmlEndTag("response_single");
                    break;
                case 1:
                    $a_xml_writer->xmlEndTag("response_multiple");
                    break;
            }
        }

        $a_xml_writer->xmlEndTag("responses");
        $a_xml_writer->xmlEndTag("matrix");

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
        $a_xml_writer->xmlElement("fieldlabel", null, "column_separators");
        $a_xml_writer->xmlElement("fieldentry", null, $this->getColumnSeparators());
        $a_xml_writer->xmlEndTag("metadatafield");

        $a_xml_writer->xmlStartTag("metadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "row_separators");
        $a_xml_writer->xmlElement("fieldentry", null, $this->getRowSeparators());
        $a_xml_writer->xmlEndTag("metadatafield");

        $a_xml_writer->xmlStartTag("metadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "neutral_column_separator");
        $a_xml_writer->xmlElement("fieldentry", null, $this->getNeutralColumnSeparator());
        $a_xml_writer->xmlEndTag("metadatafield");

        $a_xml_writer->xmlStartTag("metadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "layout");
        $a_xml_writer->xmlElement("fieldentry", null, serialize($this->getLayout()));
        $a_xml_writer->xmlEndTag("metadatafield");

        $a_xml_writer->xmlEndTag("metadata");

        $a_xml_writer->xmlEndTag("question");
    }

    public function syncWithOriginal(): void
    {
        if ($this->getOriginalId()) {
            parent::syncWithOriginal();
            $this->saveColumnsToDb($this->getOriginalId());
            $this->saveRowsToDb($this->getOriginalId());
        }
    }

    /**
     * Adds standard numbers as columns
     */
    public function addStandardNumbers(
        int $lower_limit,
        int $upper_limit
    ): void {
        for ($i = $lower_limit; $i <= $upper_limit; $i++) {
            $this->columns->addCategory($i);
        }
    }

    /**
     * Saves a set of columns to a default phrase
     * (data currently comes from session)
     */
    public function savePhrase(
        string $title
    ): void {
        $ilUser = $this->user;
        $ilDB = $this->db;

        $next_id = $ilDB->nextId('svy_phrase');
        $ilDB->manipulateF(
            "INSERT INTO svy_phrase (phrase_id, title, defaultvalue, owner_fi, tstamp) VALUES (%s, %s, %s, %s, %s)",
            array('integer','text','text','integer','integer'),
            array($next_id, $title, 1, $ilUser->getId(), time())
        );
        $phrase_id = $next_id;

        $counter = 1;
        $phrase_data = $this->edit_manager->getPhraseData();
        foreach ($phrase_data as $data) {
            $next_id = $ilDB->nextId('svy_category');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_category (category_id, title, defaultvalue, owner_fi, tstamp, neutral) VALUES (%s, %s, %s, %s, %s, %s)",
                array('integer','text','text','integer','integer','text'),
                array($next_id, $data['answer'], 1, $ilUser->getId(), time(), $data['neutral'])
            );
            $category_id = $next_id;
            $next_id = $ilDB->nextId('svy_phrase_cat');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_phrase_cat (phrase_category_id, phrase_fi, category_fi, sequence, other, scale) VALUES (%s, %s, %s, %s, %s, %s)",
                array('integer', 'integer', 'integer','integer', 'integer', 'integer'),
                array($next_id, $phrase_id, $category_id, $counter, ($data['other']) ? 1 : 0, $data['scale'])
            );
            $counter++;
        }
    }

    public function getQuestionType(): string
    {
        return "SurveyMatrixQuestion";
    }

    /**
     * Returns the name of the additional question data table in the database
     */
    public function getAdditionalTableName(): string
    {
        return "svy_qst_matrix";
    }

    public function getWorkingDataFromUserInput(array $post_data): array
    {
        $data = array();
        foreach ($post_data as $key => $value) {
            switch ($this->getSubtype()) {
                case 1:
                case 0:
                    if (preg_match("/matrix_" . $this->getId() . "_(\d+)/", $key, $matches)) {
                        if (is_array($value)) {
                            foreach ($value as $val) {
                                $data[] = array("value" => $val,
                                                "rowvalue" => $matches[1],
                                                "textanswer" => $post_data['matrix_other_' . $this->getId(
                                                ) . '_' . $matches[1]] ?? ""
                                );
                            }
                        } else {
                            $data[] = array("value" => $value,
                                            "rowvalue" => $matches[1],
                                            "textanswer" => $post_data['matrix_other_' . $this->getId(
                                            ) . '_' . $matches[1]] ?? ""
                            );
                        }
                    }
                    break;
            }
        }
        return $data;
    }

    /**
     * Checks the input of the active user for obligatory status
     * and entered values
     * @return string Empty string if the input is ok, an error message otherwise
     */
    public function checkUserInput(
        array $post_data,
        int $survey_id
    ): string {
        if (!$this->getObligatory()) {
            return "";
        }
        switch ($this->getSubtype()) {
            case 0:
                $counter = 0;
                foreach ($post_data as $key => $value) {
                    if (preg_match("/matrix_" . $this->getId() . "_(\d+)/", $key, $matches)) {
                        if (array_key_exists('matrix_other_' . $this->getId() . "_" . $matches[1], $post_data) && strlen($post_data['matrix_other_' . $this->getId() . "_" . $matches[1]]) == 0) {
                            return $this->lng->txt("question_mr_no_other_answer");
                        }
                        $counter++;
                    }
                }
                if ($counter !== $this->getRowCount()) {
                    return $this->lng->txt("matrix_question_radio_button_not_checked");
                }
                break;
            case 1:
                $counter = 0;
                foreach ($post_data as $key => $value) {
                    if (preg_match("/matrix_" . $this->getId() . "_(\d+)/", $key, $matches)) {
                        if (array_key_exists('matrix_other_' . $this->getId() . "_" . $matches[1], $post_data) && strlen($post_data['matrix_other_' . $this->getId() . "_" . $matches[1]]) == 0) {
                            return $this->lng->txt("question_mr_no_other_answer");
                        }
                        $counter++;
                        if ((!is_array($value)) || (count($value) < 1)) {
                            return $this->lng->txt("matrix_question_checkbox_not_checked");
                        }
                    }
                }
                if ($counter !== $this->getRowCount()) {
                    return $this->lng->txt("matrix_question_checkbox_not_checked");
                }
                break;
        }
        return "";
    }

    public function saveUserInput(
        array $post_data,
        int $active_id,
        bool $a_return = false
    ): ?array {
        $ilDB = $this->db;

        $answer_data = array();

        // gather data
        switch ($this->getSubtype()) {
            case 0:
                foreach ($post_data as $key => $value) {
                    if (preg_match("/matrix_" . $this->getId() . "_(\d+)/", $key, $matches)) {
                        if (strlen($value)) {
                            $other_value = (array_key_exists('matrix_other_' . $this->getId() . '_' . $matches[1], $post_data))
                                ? $this->stripSlashesAddSpaceFallback($post_data['matrix_other_' . $this->getId() . '_' . $matches[1]])
                                : null;
                            $answer_data[] = array("value" => $value,
                                "textanswer" => $other_value,
                                "rowvalue" => $matches[1]);
                        }
                    }
                }
                break;

            case 1:
                foreach ($post_data as $key => $value) {
                    if (preg_match("/matrix_" . $this->getId() . "_(\d+)/", $key, $matches)) {
                        $other_value = (array_key_exists('matrix_other_' . $this->getId() . '_' . $matches[1], $post_data))
                            ? $this->stripSlashesAddSpaceFallback($post_data['matrix_other_' . $this->getId() . '_' . $matches[1]])
                            : null;
                        foreach ($value as $checked) {
                            $answer_data[] = array("value" => $checked,
                                "textanswer" => $other_value,
                                "rowvalue" => $matches[1]);
                        }
                    }
                }
                break;
        }

        if ($a_return) {
            return $answer_data;
        }

        // #16387 - only if any input
        if (count($answer_data)) {
            // save data
            foreach ($answer_data as $item) {
                $next_id = $ilDB->nextId('svy_answer');
                #20216
                $fields = array();
                $fields['answer_id'] = array("integer", $next_id);
                $fields['question_fi'] = array("integer", $this->getId());
                $fields['active_fi'] = array("integer", $active_id);
                $fields['value'] = array("float", $item['value']);
                $fields['textanswer'] = array("clob", $item['textanswer']);
                $fields['rowvalue'] = array("integer", $item['rowvalue']);
                $fields['tstamp'] = array("integer", time());

                $affectedRows = $ilDB->insert("svy_answer", $fields);
            }
        }
        return null;
    }

    /**
     * Delete question data from additional table
     */
    public function deleteAdditionalTableData(
        int $question_id
    ): void {
        parent::deleteAdditionalTableData($question_id);

        $ilDB = $this->db;
        $ilDB->manipulateF(
            "DELETE FROM svy_qst_matrixrows WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
    }

    /**
    * Returns the subtype of the matrix question
    */
    public function getSubtype(): ?int
    {
        return $this->subtype;
    }

    /**
     * Sets the subtype of the matrix question
     */
    public function setSubtype(int $a_subtype = 0): void
    {
        switch ($a_subtype) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
                $this->subtype = $a_subtype;
                break;
            case 0:
            default:
                $this->subtype = 0;
                break;
        }
    }

    /**
     * Enables/Disables separators for the matrix columns
     */
    public function setColumnSeparators(bool $enable = false): void
    {
        $this->columnSeparators = $enable;
    }

    public function getColumnSeparators(): bool
    {
        return $this->columnSeparators;
    }

    /**
     * Enables/Disables separators for the matrix rows
     */
    public function setRowSeparators(bool $enable = false): void
    {
        $this->rowSeparators = $enable;
    }

    public function getRowSeparators(): bool
    {
        return $this->rowSeparators;
    }

    public function setNeutralColumnSeparator(bool $enable = true): void
    {
        $this->neutralColumnSeparator = $enable;
    }

    public function getNeutralColumnSeparator(): bool
    {
        return $this->neutralColumnSeparator;
    }

    /**
     * Import additional meta data from the question import file.
     */
    public function importAdditionalMetadata(array $a_meta): void
    {
        foreach ($a_meta as $key => $value) {
            switch ($value["label"]) {
                case "column_separators":
                    $this->setColumnSeparators($value["entry"]);
                    break;
                case "row_separators":
                    $this->setRowSeparators($value["entry"]);
                    break;
                case "layout":
                    $this->setLayout($value["entry"]);
                    break;
                case "neutral_column_separator":
                    $this->setNeutralColumnSeparator($value["entry"]);
                    break;
            }
        }
    }

    /**
     * Import bipolar adjectives from the question import file
     */
    public function importAdjectives(array $a_data): void
    {
        $i = 0;
        foreach ($a_data as $adjective) {
            if (is_numeric($adjective["label"])) {
                $this->setBipolarAdjective($adjective["label"], $adjective["text"]);
            } else {
                $this->setBipolarAdjective($i, $adjective["text"]);
            }
            $i++;
        }
    }

    /**
     * Import matrix rows from the question import file
     */
    public function importMatrix(
        array $a_data
    ): void {
        foreach ($a_data as $row) {
            $this->addRow($row['title'], $row['other'], $row['label']);
        }
    }

    /**
     * Import response data from the question import file
     */
    public function importResponses(array $a_data): void
    {
        foreach ($a_data as $id => $data) {
            $column = "";
            foreach ($data["material"] as $material) {
                $column .= $material["text"];
            }
            $this->columns->addCategory($column, 0, strcmp($data["label"], "neutral") == 0);
        }
    }

    /**
     * Returns if the question is usable for preconditions
     */
    public function usableForPrecondition(): bool
    {
        return false;
    }

    /**
     * Returns the output for a precondition value
     */
    public function getPreconditionValueOutput(string $value): string
    {
        return $value;
    }

    /**
     * Creates a form property for the precondition value
     */
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

    /**
     * Saves the layout of a matrix question
     * @deprecated ?
     * @param float     $percent_row width in percent for the matrix rows
     * @param float     $percent_columns width in percent for the matrix columns
     * @param float|int $percent_bipolar_adjective1 width in percent for the first bipolar adjective
     * @param float|int $percent_bipolar_adjective2 width in percent for the second bipolar adjective
     * @param float|int $percent_neutral
     */
    public function saveLayout(
        float $percent_row,
        float $percent_columns,
        float $percent_bipolar_adjective1 = 0,
        float $percent_bipolar_adjective2 = 0,
        float $percent_neutral = 0
    ): void {
        $ilDB = $this->db;

        $layout = array(
            "percent_row" => $percent_row,
            "percent_columns" => $percent_columns,
            "percent_bipolar_adjective1" => $percent_bipolar_adjective1,
            "percent_bipolar_adjective2" => $percent_bipolar_adjective2,
            "percent_neutral" => $percent_neutral
        );
        $affectedRows = $ilDB->manipulateF(
            "UPDATE " . $this->getAdditionalTableName() . " SET layout = %s WHERE question_fi = %s",
            array('text', 'integer'),
            array(serialize($layout), $this->getId())
        );
    }

    public function getLayout(): array
    {
        if (count($this->layout) === 0) {
            if ($this->hasBipolarAdjectives() && $this->hasNeutralColumn()) {
                $this->layout = array(
                    "percent_row" => 30,
                    "percent_columns" => 40,
                    "percent_bipolar_adjective1" => 10,
                    "percent_bipolar_adjective2" => 10,
                    "percent_neutral" => 10
                );
            } elseif ($this->hasBipolarAdjectives()) {
                $this->layout = array(
                    "percent_row" => 30,
                    "percent_columns" => 50,
                    "percent_bipolar_adjective1" => 10,
                    "percent_bipolar_adjective2" => 10,
                    "percent_neutral" => 0
                );
            } elseif ($this->hasNeutralColumn()) {
                $this->layout = array(
                    "percent_row" => 30,
                    "percent_columns" => 50,
                    "percent_bipolar_adjective1" => 0,
                    "percent_bipolar_adjective2" => 0,
                    "percent_neutral" => 20
                );
            } else {
                $this->layout = array(
                    "percent_row" => 30,
                    "percent_columns" => 70,
                    "percent_bipolar_adjective1" => 0,
                    "percent_bipolar_adjective2" => 0,
                    "percent_neutral" => 0
                );
            }
        }
        return $this->layout;
    }

    /**
     * @param array|string $layout
     */
    public function setLayout($layout): void
    {
        if (is_array($layout)) {
            $this->layout = $layout;
        } else {
            $this->layout = unserialize($layout, ['allowed_classes' => false]) ?: [];
        }
    }

    /**
     * Returns TRUE if bipolar adjectives exist
     */
    public function hasBipolarAdjectives(): bool
    {
        return $this->getBipolarAdjective(0) !== '' && $this->getBipolarAdjective(1) !== '';
    }

    /**
     * Returns TRUE if a neutral column exists
     */
    public function hasNeutralColumn(): bool
    {
        for ($i = 0; $i < $this->getColumnCount(); $i++) {
            $column = $this->getColumn($i);
            if ($column->neutral && strlen($column->title)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set whether placeholders should be used for the column titles or not
     */
    public function setColumnPlaceholders(bool $a_value = false): void
    {
        $this->columnPlaceholders = $a_value;
    }

    public function getColumnPlaceholders(): bool
    {
        return $this->columnPlaceholders;
    }

    /**
     * Set whether the legend should be shown or not
     */
    public function setLegend(bool $a_value = false): void
    {
        $this->legend = $a_value;
    }

    public function getLegend(): bool
    {
        return $this->legend;
    }

    public function setSingleLineRowCaption(bool $a_value = false): void
    {
        $this->singleLineRowCaption = $a_value;
    }

    public function getSingleLineRowCaption(): bool
    {
        return $this->singleLineRowCaption;
    }

    public function setRepeatColumnHeader(bool $a_value = false): void
    {
        $this->repeatColumnHeader = $a_value;
    }

    public function getRepeatColumnHeader(): bool
    {
        return $this->repeatColumnHeader;
    }


    public function getRows(): SurveyCategories
    {
        return $this->rows;
    }

    public static function getMaxSumScore(int $survey_id): int
    {
        global $DIC;

        // we need max scale values of matrix rows * number of rows (type 5)
        $db = $DIC->database();

        $set = $db->queryF(
            "SELECT MAX(scale) max_sum_score, q.question_id FROM svy_svy_qst sq " .
            "JOIN svy_question q ON (sq.question_fi = q.question_id) " .
            "JOIN svy_variable v ON (v.question_fi = q.question_id) " .
            "WHERE sq.survey_fi  = %s AND q.questiontype_fi = %s " .
            "GROUP BY (q.question_id)",
            ["integer", "integer"],
            [$survey_id, 5]
        );
        $max_score = [];
        while ($rec = $db->fetchAssoc($set)) {
            $max_score[$rec["question_id"]] = $rec["max_sum_score"];
        }

        $set = $db->queryF(
            "SELECT COUNT(mr.id_svy_qst_matrixrows) cnt_rows, q.question_id FROM svy_svy_qst sq " .
            "JOIN svy_question q ON (sq.question_fi = q.question_id) " .
            "JOIN svy_qst_matrixrows mr ON (mr.question_fi = q.question_id) " .
            "WHERE sq.survey_fi  = %s AND q.questiontype_fi = %s " .
            "GROUP BY (q.question_id)",
            ["integer", "integer"],
            [$survey_id, 5]
        );
        $cnt_rows = [];
        while ($rec = $db->fetchAssoc($set)) {
            $cnt_rows[$rec["question_id"]] = $rec["cnt_rows"];
        }

        $sum_sum_score = 0;
        foreach ($max_score as $qid => $s) {
            $sum_sum_score += $s * $cnt_rows[$qid];
        }

        return $sum_sum_score;
    }
}
