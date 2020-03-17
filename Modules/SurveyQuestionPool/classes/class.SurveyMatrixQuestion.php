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
* The SurveyMatrixQuestion class defines and encapsulates basic methods and attributes
* for matrix question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestion
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyMatrixQuestion extends SurveyQuestion
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilDB
     */
    protected $db;

    /**
    * Columns contained in this question
    *
    * @var array
    */
    public $columns;
    
    /**
    * Rows contained in this question
    *
    * @var array
    */
    public $rows;

    /**
    * First bipolar adjective for ordinal matrix questions
    *
    * @var string
    */
    public $bipolar_adjective1;
    
    /**
    * Second bipolar adjective for ordinal matrix questions
    *
    * @var string
    */
    public $bipolar_adjective2;
    
    /**
    * Enable state of separators for matrix columns
    * 1 if separators are enabled for matrix columns, 0 otherwise
    *
    * @var integer
    */
    public $columnSeparators;
    
    /**
    * Enable state of separators for matrix rows
    * 1 if separators are enabled for matrix rows, 0 otherwise
    *
    * @var integer
    */
    public $rowSeparators;

    /**
    * Enable state of a separator for the neutral column
    * 1 if a separator is enabled for the neutral column, 0 otherwise
    *
    * @var integer
    */
    public $neutralColumnSeparator;
    
    /*
     * Layout of the matrix question
     *
     * @var array
     */
    public $layout;
    
    /*
     * Use placeholders for the column titles
     *
     * @var boolean
     */
    public $columnPlaceholders;
    
    /*
     * Show a legend
     *
     * @var boolean
     */
    public $legend;
    
    public $singleLineRowCaption;
    
    public $repeatColumnHeader;
    
    public $columnHeaderPosition;
    
    /*
     * Use random order for rows
     *
     * @var boolean
     */
    public $randomRows;
    
    public $columnOrder;
    
    public $columnImages;
    
    public $rowImages;
    
    public $openRows;
    
    
    /**
    * Matrix question subtype
    *
    * Matrix question subtype:
    * 0 = Single choice
    * 1 = Multiple choice
    * 2 = Text
    * 3 = Integer
    * 4 = Double
    * 5 = Date
    * 6 = Time
    *
    * @var integer
    */
    public $subtype;

    /**
    * SurveyMatrixQuestion constructor
    * The constructor takes possible arguments an creates an instance of the SurveyMatrixQuestion object.
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

        $this->user = $DIC->user();
        $this->db = $DIC->database();
        parent::__construct($title, $description, $author, $questiontext, $owner);
        
        $this->subtype = 0;
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyCategories.php";
        $this->columns = new SurveyCategories();
        $this->rows = new SurveyCategories();
        $this->bipolar_adjective1 = "";
        $this->bipolar_adjective2 = "";
        $this->rowSeparators = 0;
        $this->columnSeparators = 0;
        $this->neutralColumnSeparator = 1;
    }
    
    /**
    * Returns the number of columns
    *
    * @return integer The number of contained columns
    * @access public
    * @see $columns
    */
    public function getColumnCount()
    {
        return $this->columns->getCategoryCount();
    }
    
    /**
    * Removes a column from the list of columns
    *
    * @param integer $index The index of the column to be removed
    * @access public
    * @see $columns
    */
    public function removeColumn($index)
    {
        $this->columns->removeCategory($index);
    }

    /**
    * Removes many columns from the list of columns
    *
    * @param array $array An array containing the index positions of the columns to be removed
    * @access public
    * @see $columns
    */
    public function removeColumns($array)
    {
        $this->columns->removeCategories($array);
    }

    /**
    * Removes a column from the list of columns
    *
    * @param string $name The name of the column to be removed
    * @access public
    * @see $columns
    */
    public function removeColumnWithName($name)
    {
        $this->columns->removeCategoryWithName($name);
    }
    
    /**
    * Return the columns
    */
    public function getColumns()
    {
        return $this->columns;
    }
    
    /**
    * Returns the name of a column for a given index
    *
    * @param integer $index The index of the column
    * @result array column
    * @access public
    * @see $columns
    */
    public function getColumn($index)
    {
        return $this->columns->getCategory($index);
    }
    
    public function getColumnForScale($scale)
    {
        return $this->columns->getCategoryForScale($scale);
    }

    /**
    * Returns the index of a column with a given name.
    *
    * @param string $name The name of the column
    * @access public
    * @see $columns
    */
    public function getColumnIndex($name)
    {
        return $this->columns->getCategoryIndex($name);
    }
    
    
    /**
    * Empties the columns list
    *
    * @access public
    * @see $columns
    */
    public function flushColumns()
    {
        $this->columns->flushCategories();
    }
    
    /**
    * Returns the number of rows in the question
    *
    * @result integer The number of rows
    * @access public
    */
    public function getRowCount()
    {
        return $this->rows->getCategoryCount();
    }

    /**
    * Adds a row to the question
    *
    * @param string $a_text The text of the row
    */
    public function addRow($a_text, $a_other, $a_label)
    {
        $this->rows->addCategory($a_text, $a_other, 0, $a_label);
    }
    
    /**
    * Adds a row at a given position
    *
    * @param string $a_text The text of the row
    * @param integer $a_position The row position
    */
    public function addRowAtPosition($a_text, $a_other, $a_position)
    {
        $this->rows->addCategoryAtPosition($a_text, $a_position, $a_other);
    }

    /**
    * Empties the row list
    *
    * @access public
    * @see $rows
    */
    public function flushRows()
    {
        $this->rows = new SurveyCategories();
    }
    
    /**
    * Returns a specific row
    *
    * @param integer $a_index The index position of the row
    * @access public
    */
    public function getRow($a_index)
    {
        return $this->rows->getCategory($a_index);
    }

    public function moveRowUp($index)
    {
        $this->rows->moveCategoryUp($index);
    }
    
    public function moveRowDown($index)
    {
        $this->rows->moveCategoryDown($index);
    }
    
    /**
    * Removes rows from the question
    *
    * @param array $array An array containing the index positions of the rows to be removed
    * @access public
    * @see $rows
    */
    public function removeRows($array)
    {
        $this->rows->removeCategories($array);
    }

    /**
    * Removes a row
    *
    * @param integer $index The index of the row to be removed
    */
    public function removeRow($index)
    {
        $this->rows->removeCategory($index);
    }

    /**
    * Returns one of the bipolar adjectives
    *
    * @param integer $a_index The number of the bipolar adjective (0 for the first and 1 for the second adjective)
    * @result string The text of the bipolar adjective
    * @access public
    */
    public function getBipolarAdjective($a_index)
    {
        switch ($a_index) {
            case 1:
                return (strlen($this->bipolar_adjective2)) ? $this->bipolar_adjective2 : null;
                break;
            case 0:
            default:
                return (strlen($this->bipolar_adjective1)) ? $this->bipolar_adjective1 : null;
                break;
        }
        return null;
    }

    /**
    * Sets one of the bipolar adjectives
    *
    * @param integer $a_index The number of the bipolar adjective (0 for the first and 1 for the second adjective)
    * @param string $a_value The text of the bipolar adjective
    * @access public
    */
    public function setBipolarAdjective($a_index, $a_value)
    {
        switch ($a_index) {
            case 1:
                $this->bipolar_adjective2 = $a_value;
                break;
            case 0:
            default:
                $this->bipolar_adjective1 = $a_value;
                break;
        }
    }
    
    /**
    * Adds a phrase to the question
    *
    * @param integer $phrase_id The database id of the given phrase
    * @access public
    */
    public function addPhrase($phrase_id)
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
            if (($row["defaultvalue"] == 1) && ($row["owner_fi"] == 0)) {
                $this->columns->addCategory($this->lng->txt($row["title"]), 0, $neutral);
            } else {
                $this->columns->addCategory($row["title"], 0, $neutral);
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
    * Loads a SurveyMatrixQuestion object from the database
    *
    * @param integer $id The database id of the matrix question
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
            $this->setSubtype($data["subtype"]);
            $this->setRowSeparators($data["row_separators"]);
            $this->setNeutralColumnSeparator($data["neutral_column_separator"]);
            $this->setColumnSeparators($data["column_separators"]);
            $this->setColumnPlaceholders($data["column_placeholders"]);
            $this->setLegend($data["legend"]);
            $this->setSingleLineRowCaption($data["singleline_row_caption"]);
            $this->setRepeatColumnHeader($data["repeat_column_header"]);
            $this->setColumnHeaderPosition($data["column_header_position"]);
            $this->setRandomRows($data["random_rows"]);
            $this->setColumnOrder($data["column_order"]);
            $this->setColumnImages($data["column_images"]);
            $this->setRowImages($data["row_images"]);
            $this->setBipolarAdjective(0, $data["bipolar_adjective1"]);
            $this->setBipolarAdjective(1, $data["bipolar_adjective2"]);
            $this->setLayout($data["layout"]);
            $this->flushColumns();

            $result = $ilDB->queryF(
                "SELECT svy_variable.*, svy_category.title, svy_category.neutral FROM svy_variable, svy_category WHERE svy_variable.question_fi = %s AND svy_variable.category_fi = svy_category.category_id ORDER BY sequence ASC",
                array('integer'),
                array($id)
            );
            if ($result->numRows() > 0) {
                while ($data = $ilDB->fetchAssoc($result)) {
                    $this->columns->addCategory($data["title"], $data["other"], $data["neutral"], null, ($data['scale']) ? $data['scale'] : ($data['sequence'] + 1));
                }
            }
            
            $result = $ilDB->queryF(
                "SELECT * FROM svy_qst_matrixrows WHERE question_fi = %s ORDER BY sequence",
                array('integer'),
                array($id)
            );
            while ($row = $ilDB->fetchAssoc($result)) {
                $this->addRow($row["title"], $row['other'], $row['label']);
            }
        }
        parent::loadFromDb($id);
    }

    /**
    * Returns 1 if the question is complete for use
    *
    * @result integer 1 if the question is complete for use, otherwise 0
    * @access public
    */
    public function isComplete()
    {
        if (
            strlen($this->getTitle()) &&
            strlen($this->getAuthor()) &&
            strlen($this->getQuestiontext()) &&
            $this->getColumnCount() &&
            $this->getRowCount()
        ) {
            return 1;
        } else {
            return 0;
        }
    }
    
    /**
    * Saves a SurveyMatrixQuestion object to a database
    *
    * @access public
    */
    public function saveToDb($original_id = null, $withanswers = true)
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
                "INSERT INTO " . $this->getAdditionalTableName() . " (
				question_fi, subtype, column_separators, row_separators, neutral_column_separator,column_placeholders,
				legend, singleline_row_caption, repeat_column_header, column_header_position, random_rows,
				column_order, column_images, row_images, bipolar_adjective1, bipolar_adjective2, layout, tstamp)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                array(
                    'integer', 'integer', 'text', 'text', 'text', 'integer', 'text', 'text', 'text',
                    'text', 'text', 'text', 'text', 'text', 'text', 'text', 'text', 'integer'
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
                    $this->getColumnHeaderPosition(),
                    $this->getRandomRows(),
                    $this->getColumnOrder(),
                    $this->getColumnImages(),
                    $this->getRowImages(),
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
    }
        
    public function saveBipolarAdjectives($adjective1, $adjective2)
    {
        $ilDB = $this->db;
        
        $affectedRows = $ilDB->manipulateF(
            "UPDATE " . $this->getAdditionalTableName() . " SET bipolar_adjective1 = %s, bipolar_adjective2 = %s WHERE question_fi = %s",
            array('text', 'text', 'integer'),
            array((strlen($adjective1)) ? $adjective1 : null, (strlen($adjective2)) ? $adjective2 : null, $this->getId())
        );
    }

    /**
    * Saves a column to the database
    *
    * @param string $columntext The text of the column
    * @result integer The database ID of the column
    * @access public
    * @see $columns
    */
    public function saveColumnToDb($columntext, $neutral = 0)
    {
        $ilUser = $this->user;
        $ilDB = $this->db;
        
        $result = $ilDB->queryF(
            "SELECT title, category_id FROM svy_category WHERE title = %s AND neutral = %s AND owner_fi = %s",
            array('text', 'text', 'integer'),
            array($columntext, $neutral, $ilUser->getId())
        );
        $insert = false;
        $returnvalue = "";
        if ($result->numRows()) {
            $insert = true;
            while ($row = $ilDB->fetchAssoc($result)) {
                if (strcmp($row["title"], $columntext) == 0) {
                    $returnvalue = $row["category_id"];
                    $insert = false;
                }
            }
        } else {
            $insert = true;
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

    public function saveColumnsToDb($original_id = "")
    {
        $ilDB = $this->db;
        
        // save columns
        $question_id = $this->getId();
        if (strlen($original_id)) {
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

    public function saveRowsToDb($original_id = "")
    {
        $ilDB = $this->db;
        
        // save rows
        $question_id = $this->getId();
        if (strlen($original_id)) {
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
        if (strlen($this->getBipolarAdjective(0)) && (strlen($this->getBipolarAdjective(1)))) {
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
                if (strcmp($matches[1], "") != 0) {
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

    public function syncWithOriginal()
    {
        if ($this->getOriginalId()) {
            parent::syncWithOriginal();
            $this->saveColumnsToDb($this->getOriginalId());
            $this->saveRowsToDb($this->getOriginalId());
        }
    }


    /**
    * Adds standard numbers as columns
    *
    * @param integer $lower_limit The lower limit
    * @param integer $upper_limit The upper limit
    * @access public
    */
    public function addStandardNumbers($lower_limit, $upper_limit)
    {
        for ($i = $lower_limit; $i <= $upper_limit; $i++) {
            $this->columns->addCategory($i);
        }
    }

    /**
    * Saves a set of columns to a default phrase
    *
    * @param array $phrases The database ids of the seleted phrases
    * @param string $title The title of the default phrase
    * @access public
    */
    public function savePhrase($title)
    {
        $ilUser = $this->user;
        $ilDB = $this->db;

        $next_id = $ilDB->nextId('svy_phrase');
        $affectedRows = $ilDB->manipulateF(
            "INSERT INTO svy_phrase (phrase_id, title, defaultvalue, owner_fi, tstamp) VALUES (%s, %s, %s, %s, %s)",
            array('integer','text','text','integer','integer'),
            array($next_id, $title, 1, $ilUser->getId(), time())
        );
        $phrase_id = $next_id;
            
        $counter = 1;
        foreach ($_SESSION['save_phrase_data'] as $data) {
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
    
    /**
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    * @access public
    */
    public function getQuestionType()
    {
        return "SurveyMatrixQuestion";
    }

    /**
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    * @access public
    */
    public function getAdditionalTableName()
    {
        return "svy_qst_matrix";
    }
    
    /**
    * Creates the user data of the svy_answer table from the POST data
    *
    * @return array User data according to the svy_answer table
    * @access public
    */
    public function &getWorkingDataFromUserInput($post_data)
    {
        $data = array();
        foreach ($post_data as $key => $value) {
            switch ($this->getSubtype()) {
                case 0:
                    if (preg_match("/matrix_" . $this->getId() . "_(\d+)/", $key, $matches)) {
                        if (is_array($value)) {
                            foreach ($value as $val) {
                                array_push($data, array("value" => $val, "rowvalue" => $matches[1], "textanswer" => $post_data['matrix_other_' . $this->getId() . '_' . $matches[1]]));
                            }
                        } else {
                            array_push($data, array("value" => $value, "rowvalue" => $matches[1], "textanswer" => $post_data['matrix_other_' . $this->getId() . '_' . $matches[1]]));
                        }
                    }
                    break;
                case 1:
                    if (preg_match("/matrix_" . $this->getId() . "_(\d+)/", $key, $matches)) {
                        if (is_array($value)) {
                            foreach ($value as $val) {
                                array_push($data, array("value" => $val, "rowvalue" => $matches[1], "textanswer" => $post_data['matrix_other_' . $this->getId() . '_' . $matches[1]]));
                            }
                        } else {
                            array_push($data, array("value" => $value, "rowvalue" => $matches[1], "textanswer" => $post_data['matrix_other_' . $this->getId() . '_' . $matches[1]]));
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
    *
    * @param array $post_data The contents of the $_POST array
    * @param integer $survey_id The database ID of the active survey
    * @return string Empty string if the input is ok, an error message otherwise
    * @access public
    */
    public function checkUserInput($post_data, $survey_id)
    {
        if (!$this->getObligatory($survey_id)) {
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
                if ($counter != $this->getRowCount()) {
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
                if ($counter != $this->getRowCount()) {
                    return $this->lng->txt("matrix_question_checkbox_not_checked");
                }
                break;
        }
        return "";
    }

    public function saveUserInput($post_data, $active_id, $a_return = false)
    {
        $ilDB = $this->db;
        
        $answer_data = array();

        // gather data
        switch ($this->getSubtype()) {
            case 0:
                foreach ($post_data as $key => $value) {
                    if (preg_match("/matrix_" . $this->getId() . "_(\d+)/", $key, $matches)) {
                        if (strlen($value)) {
                            $other_value = (array_key_exists('matrix_other_' . $this->getId() . '_' . $matches[1], $post_data))
                                ? ($post_data['matrix_other_' . $this->getId() . '_' . $matches[1]])
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
                            ? ($post_data['matrix_other_' . $this->getId() . '_' . $matches[1]])
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
        if (sizeof($answer_data)) {
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
    }

    /**
    * Deletes datasets from the additional question table in the database
    *
    * @param integer $question_id The question id which should be deleted in the additional question table
    * @access public
    */
    public function deleteAdditionalTableData($question_id)
    {
        parent::deleteAdditionalTableData($question_id);
        
        $ilDB = $this->db;
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_qst_matrixrows WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
    }

    /**
    * Returns the subtype of the matrix question
    *
    * @return integer The subtype of the matrix question
    * @access public
    */
    public function getSubtype()
    {
        return $this->subtype;
    }

    /**
    * Sets the subtype of the matrix question
    *
    * @return integer $a_subtype The subtype of the matrix question
    * @access public
    */
    public function setSubtype($a_subtype = 0)
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
    *
    * @param integer $enable 1 if the separators should be enabled, 0 otherwise
    * @access public
    */
    public function setColumnSeparators($enable = 0)
    {
        switch ($enable) {
            case 1:
                $this->columnSeparators = 1;
                break;
            case 0:
            default:
                $this->columnSeparators = 0;
                break;
        }
    }
    
    /**
    * Gets the separators enable state for the matrix columns
    *
    * @return integer 1 if the separators are enabled, 0 otherwise
    * @access public
    */
    public function getColumnSeparators()
    {
        return ($this->columnSeparators) ? 1 : 0;
    }
    
    /**
    * Enables/Disables separators for the matrix rows
    *
    * @param integer $enable 1 if the separators should be enabled, 0 otherwise
    * @access public
    */
    public function setRowSeparators($enable = 0)
    {
        switch ($enable) {
            case 1:
                $this->rowSeparators = 1;
                break;
            case 0:
            default:
                $this->rowSeparators = 0;
                break;
        }
    }
    
    /**
    * Gets the separators enable state for the matrix rows
    *
    * @return integer 1 if the separators are enabled, 0 otherwise
    * @access public
    */
    public function getRowSeparators()
    {
        return ($this->rowSeparators) ? 1 : 0;
    }

    /**
    * Enables/Disables a separator for the neutral column
    *
    * @param integer $enable 1 if the separator should be enabled, 0 otherwise
    * @access public
    */
    public function setNeutralColumnSeparator($enable = 0)
    {
        switch ($enable) {
            case 1:
                $this->neutralColumnSeparator = 1;
                break;
            case 0:
            default:
                $this->neutralColumnSeparator = 0;
                break;
        }
    }
    
    /**
    * Gets the separator enable state for the neutral column
    *
    * @return integer 1 if the separator is enabled, 0 otherwise
    * @access public
    */
    public function getNeutralColumnSeparator()
    {
        return ($this->neutralColumnSeparator) ? 1 : 0;
    }

    /**
    * Import additional meta data from the question import file. Usually
    * the meta data section is used to store question elements which are not
    * part of the standard XML schema.
    *
    * @return array $a_meta Array containing the additional meta data
    * @access public
    */
    public function importAdditionalMetadata($a_meta)
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
    *
    * @return array $a_data Array containing the adjectives
    * @access public
    */
    public function importAdjectives($a_data)
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
    *
    * @return array $a_data Array containing the matrix rows
    * @access public
    */
    public function importMatrix($a_data)
    {
        foreach ($a_data as $row) {
            $this->addRow($row['title'], $row['other'], $row['label']);
        }
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
            $column = "";
            foreach ($data["material"] as $material) {
                $column .= $material["text"];
            }
            $this->columns->addCategory($column, null, (strcmp($data["label"], "neutral") == 0) ? true : false);
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
    * Returns the output for a precondition value
    *
    * @param string $value The precondition value
    * @return string The output of the precondition value
    * @access public
    */
    public function getPreconditionValueOutput($value)
    {
        return $value;
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
     * Saves the layout of a matrix question
     *
     * @param double $percent_row The width in percent for the matrix rows
     * @param double $percent_columns The width in percent for the matrix columns
     * @param double $percent_bipolar_adjective1 The width in percent for the first bipolar adjective
     * @param double $percent_bipolar_adjective2 The width in percent for the second bipolar adjective
     * @return void
     **/
    public function saveLayout($percent_row, $percent_columns, $percent_bipolar_adjective1 = "", $percent_bipolar_adjective2 = "", $percent_neutral)
    {
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

    public function getLayout()
    {
        if (!is_array($this->layout) || count($this->layout) == 0) {
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
    
    public function setLayout($layout)
    {
        if (is_array($layout)) {
            $this->layout = $layout;
        } else {
            $this->layout = unserialize($layout);
        }
    }
    
    /**
     * Returns TRUE if bipolar adjectives exist
     *
     * @return boolean TRUE if bipolar adjectives exist, FALSE otherwise
     **/
    public function hasBipolarAdjectives()
    {
        if ((strlen($this->getBipolarAdjective(0))) && (strlen($this->getBipolarAdjective(1)))) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Returns TRUE if a neutral column exists
     *
     * @return boolean TRUE if a neutral column exists, FALSE otherwise
     **/
    public function hasNeutralColumn()
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
    *
    * @param integer $a_value 1 for placeholders, 0 otherwise
    */
    public function setColumnPlaceholders($a_value = 0)
    {
        $this->columnPlaceholders = ($a_value) ? 1 : 0;
    }
    
    /**
    * Get whether placeholders should be used for the column titles or not
    *
    * @return integer 1 for placeholders, 0 otherwise
    */
    public function getColumnPlaceholders()
    {
        return ($this->columnPlaceholders) ? 1 : 0;
    }

    /**
    * Set whether the legend should be shown or not
    *
    * @param integer $a_value Show legend
    */
    public function setLegend($a_value = 0)
    {
        $this->legend = ($a_value) ? 1 : 0;
    }
    
    /**
    * Get whether the legend should be shown or not
    *
    * @return integer Show legend
    */
    public function getLegend()
    {
        return ($this->legend) ? 1 : 0;
    }
    
    public function setSingleLineRowCaption($a_value = 0)
    {
        $this->singleLineRowCaption = ($a_value) ? 1 : 0;
    }
    
    public function getSingleLineRowCaption()
    {
        return ($this->singleLineRowCaption) ? 1 : 0;
    }
    
    public function setRepeatColumnHeader($a_value = 0)
    {
        $this->repeatColumnHeader = ($a_value) ? 1 : 0;
    }
    
    public function getRepeatColumnHeader()
    {
        return ($this->repeatColumnHeader) ? 1 : 0;
    }
    
    public function setColumnHeaderPosition($a_value)
    {
        $this->columnHeaderPosition = $a_value;
    }
    
    public function getColumnHeaderPosition()
    {
        return ($this->columnHeaderPosition) ? $this->columnHeaderPosition : 0;
    }
    
    public function setRandomRows($a_value = 0)
    {
        $this->randomRows = ($a_value) ? 1 : 0;
    }
    
    public function getRandomRows()
    {
        return ($this->randomRows) ? 1 : 0;
    }
    
    public function setColumnOrder($a_value)
    {
        $this->columnOrder = $a_value;
    }
    
    public function getColumnOrder()
    {
        return ($this->columnOrder) ? $this->columnOrder : 0;
    }
    
    public function setColumnImages($a_value = 0)
    {
        $this->columnImages = ($a_value) ? 1 : 0;
    }
    
    public function getColumnImages()
    {
        return ($this->columnImages) ? 1 : 0;
    }
    
    public function setRowImages($a_value = 0)
    {
        $this->rowImages = ($a_value) ? 1 : 0;
    }
    
    public function getRowImages()
    {
        return ($this->rowImages) ? 1 : 0;
    }

    public function getRows()
    {
        return $this->rows;
    }
}
