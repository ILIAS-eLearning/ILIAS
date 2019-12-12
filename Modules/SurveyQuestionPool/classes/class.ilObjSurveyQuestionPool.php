<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Class ilObjSurveyQuestionPool
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @extends ilObject
* @defgroup ModulesSurveyQuestionPool Modules/SurveyQuestionPool
*/

include_once "./Services/Object/classes/class.ilObject.php";

class ilObjSurveyQuestionPool extends ilObject
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;

    /**
    * Online status of questionpool
    *
    * @var string
    */
    public $online;
    
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->log = $DIC["ilLog"];
        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->type = "spl";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
    * create question pool object
    */
    public function create($a_upload = false)
    {
        parent::create();
        if (!$a_upload) {
            $this->createMetaData();
        }
    }

    /**
    * update object data
    *
    * @access	public
    * @return	boolean
    */
    public function update()
    {
        $this->updateMetaData();
        if (!parent::update()) {
            return false;
        }

        // put here object specific stuff

        return true;
    }

    /**
        * read object data from db into object
        * @access	public
        */
    public function read()
    {
        parent::read();
        $this->loadFromDb();
    }

    /**
    * Creates a 1:1 copy of the object and places the copy in a given repository
    *
    * @access public
    */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $ilLog = $this->log;
        $newObj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $newObj->setOnline($this->getOnline());
        }

        $newObj->saveToDb();
        // clone the questions in the question pool
        $questions =&$this->getQuestions();
        foreach ($questions as $question_id) {
            $newObj->copyQuestion($question_id, $newObj->getId());
        }

        // clone meta data
        include_once "./Services/MetaData/classes/class.ilMD.php";
        $md = new ilMD($this->getId(), 0, $this->getType());
        $new_md =&$md->cloneMD($newObj->getId(), 0, $newObj->getType());

        // update the metadata with the new title of the question pool
        $newObj->updateMetaData();
        return $newObj;
    }

    public function &createQuestion($question_type, $question_id = -1)
    {
        if ((!$question_type) and ($question_id > 0)) {
            $question_type = $this->getQuestiontype($question_id);
        }

        include_once "./Modules/SurveyQuestionPool/classes/class." . $question_type . "GUI.php";
        $question_type_gui = $question_type . "GUI";
        $question = new $question_type_gui();

        if ($question_id > 0) {
            $question->object->loadFromDb($question_id);
        }

        return $question;
    }

    /**
    * Copies a question into another question pool
    *
    * @param integer $question_id Database id of the question
    * @param integer $questionpool_to Database id of the target questionpool
    * @access public
    */
    public function copyQuestion($question_id, $questionpool_to)
    {
        $question_gui =&$this->createQuestion("", $question_id);
        if ($question_gui->object->getObjId() == $questionpool_to) {
            // the question is copied into the same question pool
            $this->duplicateQuestion($question_id);
        } else {
            // the question is copied into another question pool
            $newtitle = $question_gui->object->getTitle();
            if ($question_gui->object->questionTitleExists($question_gui->object->getTitle(), $questionpool_to)) {
                $counter = 2;
                while ($question_gui->object->questionTitleExists($question_gui->object->getTitle() . " ($counter)", $questionpool_to)) {
                    $counter++;
                }
                $newtitle = $question_gui->object->getTitle() . " ($counter)";
            }
            $question_gui->object->copyObject($this->getId(), $newtitle);
        }
    }

    /**
    * Loads a ilObjQuestionpool object from a database
    *
    * @access public
    */
    public function loadFromDb()
    {
        $ilDB = $this->db;
        
        $result = $ilDB->queryF(
            "SELECT * FROM svy_qpl WHERE obj_fi = %s",
            array('integer'),
            array($this->getId())
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            $this->setOnline($row["isonline"]);
        }
    }
    
    /**
    * Saves a ilObjSurveyQuestionPool object to a database
    *
    * @access public
    */
    public function saveToDb()
    {
        $ilDB = $this->db;
        
        parent::update();
        
        $result = $ilDB->queryF(
            "SELECT * FROM svy_qpl WHERE obj_fi = %s",
            array('integer'),
            array($this->getId())
        );
        if ($result->numRows() == 1) {
            $affectedRows = $ilDB->manipulateF(
                "UPDATE svy_qpl SET isonline = %s, tstamp = %s WHERE obj_fi = %s",
                array('text','integer','integer'),
                array($this->getOnline(), time(), $this->getId())
            );
        } else {
            $next_id = $ilDB->nextId('svy_qpl');
            $query = $ilDB->manipulateF(
                "INSERT INTO svy_qpl (id_questionpool, isonline, obj_fi, tstamp) VALUES (%s, %s, %s, %s)",
                array('integer', 'text', 'integer', 'integer'),
                array($next_id, $this->getOnline(), $this->getId(), time())
            );
        }
    }
    
    /**
    * delete object and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        $remove = parent::delete();
        // always call parent delete function first!!
        if (!$remove) {
            return false;
        }

        // delete all related questions
        $this->deleteAllData();

        // delete meta data
        $this->deleteMetaData();
        
        return true;
    }

    public function deleteAllData()
    {
        $ilDB = $this->db;
        $result = $ilDB->queryF(
            "SELECT question_id FROM svy_question WHERE obj_fi = %s AND original_id IS NULL",
            array('integer'),
            array($this->getId())
        );
        $found_questions = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $this->removeQuestion($row["question_id"]);
        }

        // delete export files
        $spl_data_dir = ilUtil::getDataDir() . "/spl_data";
        $directory = $spl_data_dir . "/spl_" . $this->getId();
        if (is_dir($directory)) {
            ilUtil::delDir($directory);
        }
    }

    /**
    * Removes a question from the question pool
    *
    * @param integer $question_id The database id of the question
    * @access private
    */
    public function removeQuestion($question_id)
    {
        if ($question_id < 1) {
            return;
        }
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
        $question =&SurveyQuestion::_instanciateQuestion($question_id);
        $question->delete($question_id);
    }

    /**
    * Returns the question type of a question with a given id
    *
    * @param integer $question_id The database id of the question
    * @result string The question type string
    * @access private
*/
    public function getQuestiontype($question_id)
    {
        $ilDB = $this->db;
        if ($question_id < 1) {
            return;
        }
        $result = $ilDB->queryF(
            "SELECT svy_qtype.type_tag FROM svy_question, svy_qtype WHERE svy_question.questiontype_fi = svy_qtype.questiontype_id AND svy_question.question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() == 1) {
            $data = $ilDB->fetchAssoc($result);
            return $data["type_tag"];
        } else {
            return;
        }
    }
    
    /**
    * Checks if a question is in use by a survey
    *
    * @param integer $question_id The database id of the question
    * @result mixed An array of the surveys which use the question, when the question is in use by at least one survey, otherwise false
    * @access public
    */
    public function isInUse($question_id)
    {
        $ilDB = $this->db;
        // check out the already answered questions
        $result = $ilDB->queryF(
            "SELECT answer_id FROM svy_answer WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        $answered = $result->numRows();
        
        // check out the questions inserted in surveys
        $result = $ilDB->queryF(
            "SELECT svy_svy.* FROM svy_svy, svy_svy_qst WHERE svy_svy_qst.survey_fi = svy_svy.survey_id AND svy_svy_qst.question_fi = %s",
            array('integer'),
            array($question_id)
        );
        $inserted = $result->numRows();
        if (($inserted + $answered) == 0) {
            return false;
        }
        $result_array = array();
        while ($row = $ilDB->fetchObject($result)) {
            array_push($result_array, $row);
        }
        return $result_array;
    }
    
    /**
    * Pastes a question in the question pool
    *
    * @param integer $question_id The database id of the question
    * @access public
    */
    public function paste($question_id)
    {
        $this->duplicateQuestion($question_id, $this->getId());
    }
    
    /**
    * Retrieves the datase entries for questions from a given array
    *
    * @param array $question_array An array containing the id's of the questions
    * @result array An array containing the database rows of the given question id's
    * @access public
    */
    public function &getQuestionsInfo($question_array)
    {
        $ilDB = $this->db;
        $result_array = array();
        $result = $ilDB->query("SELECT svy_question.*, svy_qtype.type_tag, svy_qtype.plugin FROM svy_question, svy_qtype WHERE svy_question.questiontype_fi = svy_qtype.questiontype_id AND svy_question.tstamp > 0 AND " . $ilDB->in('svy_question.question_id', $question_array, false, 'integer'));
        while ($row = $ilDB->fetchAssoc($result)) {
            if ($row["plugin"]) {
                if ($this->isPluginActive($row["type_tag"])) {
                    array_push($result_array, $row);
                }
            } else {
                array_push($result_array, $row);
            }
        }
        return $result_array;
    }
    
    /**
    * Duplicates a question for a questionpool
    *
    * @param integer $question_id The database id of the question
    * @access public
    */
    public function duplicateQuestion($question_id, $obj_id = "")
    {
        $ilUser = $this->user;
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
        $question = SurveyQuestion::_instanciateQuestion($question_id);
        $suffix = "";
        $counter = 1;
        while ($question->questionTitleExists($question->getTitle() . $suffix, $obj_id)) {
            $counter++;
            if ($counter > 1) {
                $suffix = " ($counter)";
            }
        }
        if ($obj_id) {
            $question->setObjId($obj_id);
        }
        $question->duplicate(false, $question->getTitle() . $suffix, $ilUser->fullname, $ilUser->id);
    }
    
    /**
    * Calculates the data for the output of the questionpool
    *
    * @access public
    */
    public function getQuestionsData($arrFilter)
    {
        $ilUser = $this->user;
        $ilDB = $this->db;
        $where = "";
        if (is_array($arrFilter)) {
            foreach ($arrFilter as $key => $value) {
                $arrFilter[$key] = str_replace('%', '', $arrFilter[$key]);
            }
            if (array_key_exists('title', $arrFilter) && strlen($arrFilter['title'])) {
                $where .= " AND " . $ilDB->like('svy_question.title', 'text', "%%" . $arrFilter['title'] . "%%");
            }
            if (array_key_exists('description', $arrFilter) && strlen($arrFilter['description'])) {
                $where .= " AND " . $ilDB->like('svy_question.description', 'text', "%%" . $arrFilter['description'] . "%%");
            }
            if (array_key_exists('author', $arrFilter) && strlen($arrFilter['author'])) {
                $where .= " AND " . $ilDB->like('svy_question.author', 'text', "%%" . $arrFilter['author'] . "%%");
            }
            if (array_key_exists('type', $arrFilter) && strlen($arrFilter['type'])) {
                $where .= " AND svy_qtype.type_tag = " . $ilDB->quote($arrFilter['type'], 'text');
            }
        }
        $query_result = $ilDB->queryF(
            "SELECT svy_question.*, svy_qtype.type_tag, svy_qtype.plugin FROM svy_question, svy_qtype WHERE svy_question.original_id IS NULL AND svy_question.tstamp > 0 AND svy_question.questiontype_fi = svy_qtype.questiontype_id AND svy_question.obj_fi = %s" . $where,
            array('integer'),
            array($this->getId())
        );
        $rows = array();
        if ($query_result->numRows()) {
            while ($row = $ilDB->fetchAssoc($query_result)) {
                if ($row["plugin"]) {
                    if ($this->isPluginActive($row["type_tag"])) {
                        array_push($rows, $row);
                    }
                } else {
                    array_push($rows, $row);
                }
            }
        }
        return $rows;
    }

    /**
    * creates data directory for export files
    * (data_dir/spl_data/spl_<id>/export, depending on data
    * directory that is set in ILIAS setup/ini)
    *
    * @throws ilSurveyException
    */
    public function createExportDirectory()
    {
        $spl_data_dir = ilUtil::getDataDir() . "/spl_data";
        ilUtil::makeDir($spl_data_dir);
        if (!is_writable($spl_data_dir)) {
            include_once "Modules/Survey/exceptions/class.ilSurveyException.php";
            throw new ilSurveyException("Survey Questionpool Data Directory (" . $spl_data_dir . ") not writeable.");
        }
        
        // create learning module directory (data_dir/lm_data/lm_<id>)
        $spl_dir = $spl_data_dir . "/spl_" . $this->getId();
        ilUtil::makeDir($spl_dir);
        if (!@is_dir($spl_dir)) {
            include_once "Modules/Survey/exceptions/class.ilSurveyException.php";
            throw new ilSurveyException("Creation of Survey Questionpool Directory failed.");
        }
        // create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
        $export_dir = $spl_dir . "/export";
        ilUtil::makeDir($export_dir);
        if (!@is_dir($export_dir)) {
            include_once "Modules/Survey/exceptions/class.ilSurveyException.php";
            throw new ilSurveyException("Creation of Survey Questionpool Export Directory failed.");
        }
    }

    /**
    * get export directory of survey
    */
    public function getExportDirectory()
    {
        $export_dir = ilUtil::getDataDir() . "/spl_data" . "/spl_" . $this->getId() . "/export";
        return $export_dir;
    }
    
    /**
    * get export files
    */
    public function getExportFiles($dir)
    {
        // quit if import dir not available
        if (!@is_dir($dir) or
            !is_writeable($dir)) {
            return array();
        }

        // open directory
        $dir = dir($dir);

        // initialize array
        $file = array();

        // get files and save the in the array
        while ($entry = $dir->read()) {
            if ($entry != "." &&
                $entry != ".." &&
                preg_match("/^[0-9]{10}__[0-9]+__(spl_)*[0-9]+\.[A-Za-z]{3}$/", $entry)) {
                $file[] = $entry;
            }
        }

        // close import directory
        $dir->close();
        // sort files
        sort($file);
        reset($file);

        return $file;
    }

    /**
    * creates data directory for import files
    * (data_dir/spl_data/spl_<id>/import, depending on data
    * directory that is set in ILIAS setup/ini)
    *
    * @throws ilSurveyException
    */
    public function createImportDirectory()
    {
        $spl_data_dir = ilUtil::getDataDir() . "/spl_data";
        ilUtil::makeDir($spl_data_dir);
        
        if (!is_writable($spl_data_dir)) {
            include_once "Modules/Survey/exceptions/class.ilSurveyException.php";
            throw new ilSurveyException("Survey Questionpool Data Directory (" . $spl_data_dir . ") not writeable.");
        }

        // create test directory (data_dir/spl_data/spl_<id>)
        $spl_dir = $spl_data_dir . "/spl_" . $this->getId();
        ilUtil::makeDir($spl_dir);
        if (!@is_dir($spl_dir)) {
            include_once "Modules/Survey/exceptions/class.ilSurveyException.php";
            throw new ilSurveyException("Creation of Survey Questionpool Directory failed.");
        }

        // create import subdirectory (data_dir/spl_data/spl_<id>/import)
        $import_dir = $spl_dir . "/import";
        ilUtil::makeDir($import_dir);
        if (!@is_dir($import_dir)) {
            include_once "Modules/Survey/exceptions/class.ilSurveyException.php";
            throw new ilSurveyException("Creation of Survey Questionpool Import Directory failed.");
        }
    }

    /**
    * get import directory of survey
    */
    public function getImportDirectory()
    {
        $import_dir = ilUtil::getDataDir() . "/spl_data" .
            "/spl_" . $this->getId() . "/import";
        if (@is_dir($import_dir)) {
            return $import_dir;
        } else {
            return false;
        }
    }

    /**
    * export questions to xml
    */
    public function toXML($questions)
    {
        if (!is_array($questions)) {
            $questions =&$this->getQuestions();
        }
        if (count($questions) == 0) {
            $questions =&$this->getQuestions();
        }
        $xml = "";

        include_once("./Services/Xml/classes/class.ilXmlWriter.php");
        $a_xml_writer = new ilXmlWriter;
        // set xml header
        $a_xml_writer->xmlHeader();
        $attrs = array(
            "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
            "xsi:noNamespaceSchemaLocation" => "http://www.ilias.de/download/xsd/ilias_survey_4_2.xsd"
        );
        $a_xml_writer->xmlStartTag("surveyobject", $attrs);
        $attrs = array(
            "id" => "qpl_" . $this->getId(),
            "label" => $this->getTitle(),
            "online" => $this->getOnline()
        );
        $a_xml_writer->xmlStartTag("surveyquestions", $attrs);
        $a_xml_writer->xmlElement("dummy", null, "dummy");
        // add ILIAS specific metadata
        $a_xml_writer->xmlStartTag("metadata");
        $a_xml_writer->xmlStartTag("metadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "SCORM");
        include_once "./Services/MetaData/classes/class.ilMD.php";
        $md = new ilMD($this->getId(), 0, $this->getType());
        $writer = new ilXmlWriter();
        $md->toXml($writer);
        $metadata = $writer->xmlDumpMem();
        $a_xml_writer->xmlElement("fieldentry", null, $metadata);
        $a_xml_writer->xmlEndTag("metadatafield");
        $a_xml_writer->xmlEndTag("metadata");

        $a_xml_writer->xmlEndTag("surveyquestions");
        $a_xml_writer->xmlEndTag("surveyobject");

        $xml = $a_xml_writer->xmlDumpMem(false);

        $questionxml = "";
        foreach ($questions as $key => $value) {
            $questiontype = $this->getQuestiontype($value);
            include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
            SurveyQuestion::_includeClass($questiontype);
            $question = new $questiontype();
            $question->loadFromDb($value);
            $questionxml .= $question->toXML(false);
        }
        
        $xml = str_replace("<dummy>dummy</dummy>", $questionxml, $xml);
        return $xml;
    }

    public function &getQuestions()
    {
        $ilDB = $this->db;
        $questions = array();
        $result = $ilDB->queryF(
            "SELECT question_id FROM svy_question WHERE obj_fi = %s AND svy_question.tstamp > 0 AND original_id IS NULL",
            array('integer'),
            array($this->getId())
        );
        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                array_push($questions, $row["question_id"]);
            }
        }
        return $questions;
    }

    /**
    * Imports survey questions into ILIAS
    *
    * @param string $source The filename of an XML import file
    * @access public
    */
    public function importObject($source, $spl_exists = false)
    {
        if (is_file($source)) {
            $isZip = (strcmp(strtolower(substr($source, -3)), 'zip') == 0);
            if ($isZip) {
                // unzip file
                ilUtil::unzip($source);

                // determine filenames of xml files
                $subdir = basename($source, ".zip");
                $source = dirname($source) . "/" . $subdir . "/" . $subdir . ".xml";
            }

            $fh = fopen($source, "r") or die("");
            $xml = fread($fh, filesize($source));
            fclose($fh) or die("");
            if ($isZip) {
                $subdir = basename($source, ".zip");
                if (@is_dir(dirname($source) . "/" . $subdir)) {
                    ilUtil::delDir(dirname($source) . "/" . $subdir);
                }
            }
            if (strpos($xml, "questestinterop") > 0) {
                include_once("./Modules/Survey/exceptions/class.ilInvalidSurveyImportFileException.php");
                throw new ilInvalidSurveyImportFileException("Unsupported survey version (< 3.8) found.");
            } else {
                // survey questions for ILIAS >= 3.8
                include_once "./Services/Survey/classes/class.SurveyImportParser.php";
                $import = new SurveyImportParser($this->getId(), "", $spl_exists);
                $import->setXMLContent($xml);
                $import->startParsing();
            }
        }
    }

    public static function _setOnline($a_obj_id, $a_online_status)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $status = "0";
        switch ($a_online_status) {
            case 0:
            case 1:
                $status = "$a_online_status";
                break;
        }
        $affectedRows = $ilDB->manipulateF(
            "UPDATE svy_qpl SET isonline = %s  WHERE obj_fi = %s",
            array('text','integer'),
            array($status, $a_obj_id)
        );
    }
    
    /**
    * Sets the questionpool online status
    *
    * @param integer $a_online_status Online status of the questionpool
    * @see online
    * @access public
    */
    public function setOnline($a_online_status)
    {
        switch ($a_online_status) {
            case 0:
            case 1:
                $this->online = $a_online_status;
                break;
            default:
                $this->online = 0;
                break;
        }
    }
    
    public function getOnline()
    {
        if (strcmp($this->online, "") == 0) {
            $this->online = "0";
        }
        return $this->online;
    }
    
    public static function _lookupOnline($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $result = $ilDB->queryF(
            "SELECT isonline FROM svy_qpl WHERE obj_fi = %s",
            array('integer'),
            array($a_obj_id)
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            return $row["isonline"];
        }
        return 0;
    }

    /**
    * Returns true, if the question pool is writeable by a given user
    *
    * @param integer $object_id The object id of the question pool
    * @param integer $user_id The database id of the user
    * @access public
    */
    public static function _isWriteable($object_id, $user_id)
    {
        global $DIC;

        $rbacsystem = $DIC->rbac()->system();
        global $DIC;

        $ilDB = $DIC->database();
        
        $refs = ilObject::_getAllReferences($object_id);
        $result = false;
        foreach ($refs as $ref) {
            if ($rbacsystem->checkAccess("write", $ref) && (ilObject::_hasUntrashedReference($object_id))) {
                $result = true;
            }
        }
        return $result;
    }

    /**
    * Creates a list of all available question types
    *
    * @return array An array containing the available questiontypes
    * @access public
    */
    public static function _getQuestiontypes()
    {
        global $DIC;

        $ilDB = $DIC->database();
        global $DIC;

        $lng = $DIC->language();
        
        $lng->loadLanguageModule("survey");
        $types = array();
        $query_result = $ilDB->query("SELECT * FROM svy_qtype ORDER BY type_tag");
        while ($row = $ilDB->fetchAssoc($query_result)) {
            //array_push($questiontypes, $row["type_tag"]);
            if ($row["plugin"] == 0) {
                $types[$lng->txt($row["type_tag"])] = $row;
            } else {
                global $DIC;

                $ilPluginAdmin = $DIC["ilPluginAdmin"];
                $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, "SurveyQuestionPool", "svyq");
                foreach ($pl_names as $pl_name) {
                    $pl = ilPlugin::getPluginObject(IL_COMP_MODULE, "SurveyQuestionPool", "svyq", $pl_name);
                    if (strcmp($pl->getQuestionType(), $row["type_tag"]) == 0) {
                        $types[$pl->getQuestionTypeTranslation()] = $row;
                    }
                }
            }
        }
        ksort($types);
        
        
        // #14263 - default sorting
        
        $default_sorting = array_flip(array(
            "SurveySingleChoiceQuestion",
            "SurveyMultipleChoiceQuestion",
            "SurveyMatrixQuestion",
            "SurveyMetricQuestion",
            "SurveyTextQuestion"
        ));
   
        $sorted = array();
        $idx = sizeof($default_sorting);
        foreach ($types as $caption => $item) {
            $type = $item["type_tag"];
            $item["caption"] = $caption;
            
            // default
            if (array_key_exists($type, $default_sorting)) {
                $sorted[$default_sorting[$type]] = $item;
            }
            // plugin (append alphabetically sorted)
            else {
                $sorted[$idx] = $item;
                $idx++;
            }
        }
        ksort($sorted);
        
        // redo captions as index
        $types = array();
        foreach ($sorted as $item) {
            $types[$item["caption"]] = $item;
        }
        
        return $types;
    }
    
    public static function _getQuestionTypeTranslations()
    {
        global $DIC;

        $ilDB = $DIC->database();
        global $DIC;

        $lng = $DIC->language();
        global $DIC;

        $ilLog = $DIC["ilLog"];
        global $DIC;

        $ilPluginAdmin = $DIC["ilPluginAdmin"];
        
        $lng->loadLanguageModule("survey");
        $result = $ilDB->query("SELECT * FROM svy_qtype");
        $types = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            if ($row["plugin"] == 0) {
                $types[$row['type_tag']] = $lng->txt($row["type_tag"]);
            } else {
                $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, "SurveyQuestionPool", "svyq");
                foreach ($pl_names as $pl_name) {
                    $pl = ilPlugin::getPluginObject(IL_COMP_MODULE, "SurveyQuestionPool", "svyq", $pl_name);
                    if (strcmp($pl->getQuestionType(), $row["type_tag"]) == 0) {
                        $types[$row['type_tag']] = $pl->getQuestionTypeTranslation();
                    }
                }
            }
        }
        ksort($types);
        return $types;
    }

    /**
    * Returns the available question pools for the active user
    *
    * @return array The available question pools
    * @access public
    */
    public static function _getAvailableQuestionpools($use_object_id = false, $could_be_offline = false, $showPath = false, $permission = "read")
    {
        global $DIC;

        $ilUser = $DIC->user();
        global $DIC;

        $ilDB = $DIC->database();

        $result_array = array();
        $qpls = ilUtil::_getObjectsByOperations("spl", $permission, $ilUser->getId(), -1);
        $titles = ilObject::_prepareCloneSelection($qpls, "spl", $showPath);
        $allqpls = array();
        $result = $ilDB->query("SELECT obj_fi, isonline FROM svy_qpl");
        while ($row = $ilDB->fetchAssoc($result)) {
            $allqpls[$row['obj_fi']] = $row['isonline'];
        }
        foreach ($qpls as $ref_id) {
            $obj_id = ilObject::_lookupObjectId($ref_id);
            if ($could_be_offline || $allqpls[$obj_id] == 1) {
                if ($use_object_id) {
                    $result_array[$obj_id] = $titles[$ref_id];
                } else {
                    $result_array[$ref_id] = $titles[$ref_id];
                }
            }
        }
        return $result_array;
    }

    /**
    * Checks whether or not a question plugin with a given name is active
    *
    * @param string $a_pname The plugin name
    * @access public
    */
    public function isPluginActive($a_pname)
    {
        $ilPluginAdmin = $this->plugin_admin;
        if ($ilPluginAdmin->isActive(IL_COMP_MODULE, "SurveyQuestionPool", "svyq", $a_pname)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
    * Returns title, description and type for an array of question id's
    *
    * @param array $question_ids An array of question id's
    * @return array Array of associated arrays with title, description, type_tag
    */
    public function getQuestionInfos($question_ids)
    {
        $ilDB = $this->db;
        
        $found = array();
        $query_result = $ilDB->query("SELECT svy_question.*, svy_qtype.type_tag FROM svy_question, svy_qtype " .
            "WHERE svy_question.questiontype_fi = svy_qtype.questiontype_id " .
            "AND svy_question.tstamp > 0 AND " . $ilDB->in('svy_question.question_id', $question_ids, false, 'integer') . " " .
            "ORDER BY svy_question.title");
        if ($query_result->numRows() > 0) {
            while ($data = $ilDB->fetchAssoc($query_result)) {
                if (in_array($data["question_id"], $question_ids)) {
                    array_push($found, array('id' => $data["question_id"],
                        'title' => $data["title"],
                        'description' => $data["description"],
                        'type_tag' => $data["type_tag"]));
                }
            }
        }
        return $found;
    }

    /*
    * Remove all questions with tstamp = 0
    */
    public function purgeQuestions()
    {
        $ilDB = $this->db;
        $ilUser = $this->user;
        
        $result = $ilDB->queryF(
            "SELECT question_id FROM svy_question WHERE owner_fi = %s AND tstamp = %s",
            array("integer", "integer"),
            array($ilUser->getId(), 0)
        );
        while ($data = $ilDB->fetchAssoc($result)) {
            $this->removeQuestion($data["question_id"]);
        }
    }

    /**
    * Copies a question to the clipboard
    *
    * @param integer $question_id Object id of the question
    */
    public function copyToClipboard($question_id)
    {
        if (!array_key_exists("spl_clipboard", $_SESSION)) {
            $_SESSION["spl_clipboard"] = array();
        }
        $_SESSION["spl_clipboard"][$question_id] = array("question_id" => $question_id, "action" => "copy");
    }
    
    /**
    * Moves a question to the clipboard
    *
    * @param integer $question_id Object id of the question
    */
    public function moveToClipboard($question_id)
    {
        if (!array_key_exists("spl_clipboard", $_SESSION)) {
            $_SESSION["spl_clipboard"] = array();
        }
        $_SESSION["spl_clipboard"][$question_id] = array("question_id" => $question_id, "action" => "move");
    }

    /**
    * Copies/Moves a question from the clipboard
    */
    public function pasteFromClipboard()
    {
        $ilDB = $this->db;

        if (array_key_exists("spl_clipboard", $_SESSION)) {
            foreach ($_SESSION["spl_clipboard"] as $question_object) {
                if (strcmp($question_object["action"], "move") == 0) {
                    $result = $ilDB->queryF(
                        "SELECT obj_fi FROM svy_question WHERE question_id = %s",
                        array('integer'),
                        array($question_object["question_id"])
                    );
                    if ($result->numRows() == 1) {
                        $row = $ilDB->fetchAssoc($result);
                        $source_questionpool = $row["obj_fi"];
                        if ($this->getId() != $source_questionpool) {
                            // change the questionpool id in the qpl_questions table
                            $affectedRows = $ilDB->manipulateF(
                                "UPDATE svy_question SET obj_fi = %s WHERE question_id = %s",
                                array('integer','integer'),
                                array($this->getId(), $question_object["question_id"])
                            );

                            // move question data to the new target directory
                            $source_path = CLIENT_WEB_DIR . "/survey/" . $source_questionpool . "/" . $question_object["question_id"] . "/";
                            if (@is_dir($source_path)) {
                                $target_path = CLIENT_WEB_DIR . "/survey/" . $this->getId() . "/";
                                if (!@is_dir($target_path)) {
                                    ilUtil::makeDirParents($target_path);
                                }
                                @rename($source_path, $target_path . $question_object["question_id"]);
                            }
                        } else {
                            ilUtil::sendFailure($this->lng->txt("spl_move_same_pool"), true);
                            return;
                        }
                    }
                } else {
                    $this->copyQuestion($question_object["question_id"], $this->getId());
                }
            }
        }
        ilUtil::sendSuccess($this->lng->txt("spl_paste_success"), true);
        unset($_SESSION["spl_clipboard"]);
    }
    
    /**
    * Sets the obligatory states for questions in a survey from the questions form
    *
    * @param array $obligatory_questions The questions which should be set obligatory from the questions form, the remaining questions should be setted not obligatory
    * @access public
    */
    public function setObligatoryStates($obligatory_questions)
    {
        $ilDB = $this->db;
        
        foreach ($this->getQuestions() as $question_id) {
            $status = (int) (in_array($question_id, $obligatory_questions));
            
            $ilDB->manipulate("UPDATE svy_question" .
                " SET obligatory = " . $ilDB->quote($status, "integer") .
                " WHERE question_id = " . $ilDB->quote($question_id, "integer"));
        }
    }
} // END class.ilSurveyObjQuestionPool
