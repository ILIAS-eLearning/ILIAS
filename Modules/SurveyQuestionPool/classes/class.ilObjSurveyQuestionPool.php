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
 * Class ilObjSurveyQuestionPool
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilObjSurveyQuestionPool extends ilObject
{
    protected \ILIAS\SurveyQuestionPool\Editing\EditManager $edit_manager;
    protected ilObjUser $user;
    public bool $online = false;
    protected ilComponentRepository $component_repository;
    private \ilGlobalTemplateInterface $main_tpl;
    
    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->log = $DIC["ilLog"];
        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->component_repository = $DIC["component.repository"];
        $this->type = "spl";
        parent::__construct($a_id, $a_call_by_reference);
        $this->edit_manager = $DIC->surveyQuestionPool()
            ->internal()
            ->domain()
            ->editing();
    }

    public function create($a_upload = false) : int
    {
        $id = parent::create();
        if (!$a_upload) {
            $this->createMetaData();
        }
        return $id;
    }

    public function update() : bool
    {
        $this->updateMetaData();
        if (!parent::update()) {
            return false;
        }
        return true;
    }

    public function read() : void
    {
        parent::read();
        $this->loadFromDb();
    }

    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false) : ?ilObject
    {
        $newObj = parent::cloneObject($target_id, $copy_id, $omit_tree);

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $newObj->setOnline($this->getOnline());
        }

        $newObj->saveToDb();
        // clone the questions in the question pool
        $questions = $this->getQuestions();
        foreach ($questions as $question_id) {
            $newObj->copyQuestion($question_id, $newObj->getId());
        }

        // clone meta data
        $md = new ilMD($this->getId(), 0, $this->getType());
        $new_md = $md->cloneMD($newObj->getId(), 0, $newObj->getType());

        // update the metadata with the new title of the question pool
        $newObj->updateMetaData();
        return $newObj;
    }

    /**
     * @todo check this method, it does not seem to create anything
     */
    public function createQuestion(
        string $question_type,
        int $question_id = -1
    ) : SurveyQuestionGUI {
        if ((!$question_type) and ($question_id > 0)) {
            $question_type = $this->getQuestiontype($question_id);
        }

        $question_type_gui = $question_type . "GUI";
        $question = new $question_type_gui();

        if ($question_id > 0) {
            $question->object->loadFromDb($question_id);
        }

        return $question;
    }

    /**
     * @param int $question_id
     * @param int $questionpool_to question pool id
     */
    public function copyQuestion(
        int $question_id,
        int $questionpool_to
    ) : void {
        $question_gui = $this->createQuestion("", $question_id);
        if ($question_gui->object->getObjId() === $questionpool_to) {
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

    public function loadFromDb() : void
    {
        $ilDB = $this->db;
        
        $result = $ilDB->queryF(
            "SELECT isonline FROM svy_qpl WHERE obj_fi = %s",
            array('integer'),
            array($this->getId())
        );
        if ($result->numRows() === 1) {
            $row = $ilDB->fetchAssoc($result);
            $this->setOnline((bool) $row["isonline"]);
        }
    }
    
    public function saveToDb() : void
    {
        $ilDB = $this->db;
        
        parent::update();
        
        $result = $ilDB->queryF(
            "SELECT * FROM svy_qpl WHERE obj_fi = %s",
            array('integer'),
            array($this->getId())
        );
        if ($result->numRows() === 1) {
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
    
    public function delete() : bool
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

    public function deleteAllData() : void
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
        $spl_data_dir = ilFileUtils::getDataDir() . "/spl_data";
        $directory = $spl_data_dir . "/spl_" . $this->getId();
        if (is_dir($directory)) {
            ilFileUtils::delDir($directory);
        }
    }

    /**
     * Removes a question from the question pool
     */
    public function removeQuestion(int $question_id) : void
    {
        if ($question_id < 1) {
            return;
        }
        $question = SurveyQuestion::_instanciateQuestion($question_id);
        $question->delete($question_id);
    }

    /**
     * @return string|null question type string
     */
    public function getQuestiontype(
        int $question_id
    ) : ?string {
        $ilDB = $this->db;
        if ($question_id < 1) {
            return null;
        }
        $result = $ilDB->queryF(
            "SELECT svy_qtype.type_tag FROM svy_question, svy_qtype WHERE svy_question.questiontype_fi = svy_qtype.questiontype_id AND svy_question.question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() === 1) {
            $data = $ilDB->fetchAssoc($result);
            return $data["type_tag"];
        } else {
            return null;
        }
    }
    
    /**
     * Checks if a question is in use by a survey
     * @return mixed array of the surveys which use the question,
     * when the question is in use by at least one survey, otherwise false
     */
    public function isInUse(int $question_id) : ?array
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
        if (($inserted + $answered) === 0) {
            return null;
        }
        $result_array = array();
        while ($row = $ilDB->fetchObject($result)) {
            $result_array[] = $row;
        }
        return $result_array;
    }
    
    /**
     * Pastes a duplicate of a question in the question pool
     */
    public function paste(int $question_id) : void
    {
        $this->duplicateQuestion($question_id, $this->getId());
    }
    
    /**
     * @param int[] $question_array question ids
     * @todo move to question manager/repo, use dto
     */
    public function getQuestionsInfo(
        array $question_array
    ) : array {
        $ilDB = $this->db;
        $result_array = array();
        $result = $ilDB->query("SELECT svy_question.*, svy_qtype.type_tag, svy_qtype.plugin FROM svy_question, svy_qtype WHERE svy_question.questiontype_fi = svy_qtype.questiontype_id AND svy_question.tstamp > 0 AND " . $ilDB->in('svy_question.question_id', $question_array, false, 'integer'));
        while ($row = $ilDB->fetchAssoc($result)) {
            if ($row["plugin"]) {
                if ($this->isPluginActive($row["type_tag"])) {
                    $result_array[] = $row;
                }
            } else {
                $result_array[] = $row;
            }
        }
        return $result_array;
    }
    
    /**
     * Duplicates a question for a question pool
     */
    public function duplicateQuestion(
        int $question_id,
        int $obj_id = 0
    ) : void {
        $ilUser = $this->user;
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
     * Retrieve the data for the output of the question pool
     * @todo move to question/pool manager
     */
    public function getQuestionsData(
        array $arrFilter
    ) : array {
        $ilDB = $this->db;
        $where = "";
        if (count($arrFilter) > 0) {
            foreach ($arrFilter as $key => $value) {
                $arrFilter[$key] = str_replace('%', '', $value);
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
                        $rows[] = $row;
                    }
                } else {
                    $rows[] = $row;
                }
            }
        }
        return $rows;
    }

    /**
     * creates data directory for export files
     * data_dir/spl_data/spl_<id>/export
     * @throws ilSurveyException
     */
    public function createExportDirectory() : void
    {
        $spl_data_dir = ilFileUtils::getDataDir() . "/spl_data";
        ilFileUtils::makeDir($spl_data_dir);
        if (!is_writable($spl_data_dir)) {
            throw new ilSurveyException("Survey Questionpool Data Directory (" . $spl_data_dir . ") not writeable.");
        }
        
        // create learning module directory (data_dir/lm_data/lm_<id>)
        $spl_dir = $spl_data_dir . "/spl_" . $this->getId();
        ilFileUtils::makeDir($spl_dir);
        if (!is_dir($spl_dir)) {
            throw new ilSurveyException("Creation of Survey Questionpool Directory failed.");
        }
        // create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
        $export_dir = $spl_dir . "/export";
        ilFileUtils::makeDir($export_dir);
        if (!is_dir($export_dir)) {
            throw new ilSurveyException("Creation of Survey Questionpool Export Directory failed.");
        }
    }

    /**
     * get export directory of survey
     */
    public function getExportDirectory() : string
    {
        $export_dir = ilFileUtils::getDataDir() . "/spl_data" . "/spl_" . $this->getId() . "/export";
        return $export_dir;
    }
    
    /**
     * get export files
     */
    public function getExportFiles(string $dir) : array
    {
        // quit if import dir not available
        if (!is_dir($dir) or
            !is_writable($dir)) {
            return array();
        }

        // open directory
        $dir = dir($dir);

        // initialize array
        $file = array();

        // get files and save the in the array
        while ($entry = $dir->read()) {
            if ($entry !== "." &&
                $entry !== ".." &&
                preg_match("/^[0-9]{10}__[0-9]+__(spl_)*[0-9]+\.[A-Za-z]{3}$/", $entry)) {
                $file[] = $entry;
            }
        }

        // close import directory
        $dir->close();
        // sort files
        sort($file);

        return $file;
    }

    /**
     * creates data directory for import files
     * (data_dir/spl_data/spl_<id>/import
     * @throws ilSurveyException
     */
    public function createImportDirectory() : void
    {
        $spl_data_dir = ilFileUtils::getDataDir() . "/spl_data";
        ilFileUtils::makeDir($spl_data_dir);
        
        if (!is_writable($spl_data_dir)) {
            throw new ilSurveyException("Survey Questionpool Data Directory (" . $spl_data_dir . ") not writeable.");
        }

        // create test directory (data_dir/spl_data/spl_<id>)
        $spl_dir = $spl_data_dir . "/spl_" . $this->getId();
        ilFileUtils::makeDir($spl_dir);
        if (!is_dir($spl_dir)) {
            throw new ilSurveyException("Creation of Survey Questionpool Directory failed.");
        }

        // create import subdirectory (data_dir/spl_data/spl_<id>/import)
        $import_dir = $spl_dir . "/import";
        ilFileUtils::makeDir($import_dir);
        if (!is_dir($import_dir)) {
            throw new ilSurveyException("Creation of Survey Questionpool Import Directory failed.");
        }
    }

    public function getImportDirectory() : string
    {
        return ilFileUtils::getDataDir() . "/spl_data" .
            "/spl_" . $this->getId() . "/import";
    }

    /**
     * export questions to xml
     * @todo move to export sub-component
     */
    public function toXML(?array $questions) : string
    {
        if (is_null($questions) || count($questions) === 0) {
            $questions = $this->getQuestions();
        }
        $a_xml_writer = new ilXmlWriter();
        // set xml header
        $a_xml_writer->xmlHeader();
        $attrs = array(
            "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
            "xsi:noNamespaceSchemaLocation" => "https://www.ilias.de/download/xsd/ilias_survey_4_2.xsd"
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
        $md = new ilMD($this->getId(), 0, $this->getType());
        $writer = new ilXmlWriter();
        $md->toXML($writer);
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
            SurveyQuestion::_includeClass($questiontype);
            $question = new $questiontype();
            $question->loadFromDb($value);
            $questionxml .= $question->toXML(false);
        }
        
        $xml = str_replace("<dummy>dummy</dummy>", $questionxml, $xml);
        return $xml;
    }

    public function getQuestions() : array
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
                $questions[] = $row["question_id"];
            }
        }
        return $questions;
    }

    /**
     * Imports survey questions into ILIAS
     * @param string $source The filename of an XML import file
     * @throws ilInvalidSurveyImportFileException
     */
    public function importObject(
        string $source,
        bool $spl_exists = false
    ) : void {
        if (is_file($source)) {
            $isZip = (strcmp(strtolower(substr($source, -3)), 'zip') === 0);
            if ($isZip) {
                // unzip file
                ilFileUtils::unzip($source);

                // determine filenames of xml files
                $subdir = basename($source, ".zip");
                $source = dirname($source) . "/" . $subdir . "/" . $subdir . ".xml";
            }

            $fh = fopen($source, 'rb') or die("");
            $xml = fread($fh, filesize($source));
            fclose($fh) or die("");
            if ($isZip) {
                $subdir = basename($source, ".zip");
                if (is_dir(dirname($source) . "/" . $subdir)) {
                    ilFileUtils::delDir(dirname($source) . "/" . $subdir);
                }
            }
            if (strpos($xml, "questestinterop") > 0) {
                throw new ilInvalidSurveyImportFileException("Unsupported survey version (< 3.8) found.");
            }

            // survey questions for ILIAS >= 3.8
            $import = new SurveyImportParser($this->getId(), "", $spl_exists);
            $import->setXMLContent($xml);
            $import->startParsing();
        }
    }

    public static function _setOnline(
        int $a_obj_id,
        bool $a_online_status
    ) : void {
        global $DIC;

        $status = (string) (int) $a_online_status;
        $db = $DIC->database();

        $db->manipulateF(
            "UPDATE svy_qpl SET isonline = %s  WHERE obj_fi = %s",
            array('text','integer'),
            array($status, $a_obj_id)
        );
    }
    
    public function setOnline(bool $a_online_status) : void
    {
        $this->online = $a_online_status;
    }
    
    public function getOnline() : bool
    {
        return $this->online;
    }
    
    public static function _lookupOnline(int $a_obj_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $result = $ilDB->queryF(
            "SELECT isonline FROM svy_qpl WHERE obj_fi = %s",
            array('integer'),
            array($a_obj_id)
        );
        if ($row = $ilDB->fetchAssoc($result)) {
            return (bool) $row["isonline"];
        }
        return false;
    }

    /**
     * Returns true, if the question pool is writeable for
     * the current user
     */
    public static function _isWriteable(
        int $object_id
    ) : bool {
        global $DIC;

        $rbacsystem = $DIC->rbac()->system();
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
     * Get all available question types
     * @todo move to question manager, use dto
     */
    public static function _getQuestiontypes() : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();
        
        $lng->loadLanguageModule("survey");
        $types = array();
        $query_result = $ilDB->query("SELECT * FROM svy_qtype ORDER BY type_tag");
        while ($row = $ilDB->fetchAssoc($query_result)) {
            //array_push($questiontypes, $row["type_tag"]);
            if ((int) $row["plugin"] === 0) {
                $types[$lng->txt($row["type_tag"])] = $row;
            } else {
                global $DIC;

                $component_factory = $DIC["component.factory"];
                foreach ($component_factory->getActivePluginsInSlot("svyq") as $pl) {
                    if (strcmp($pl->getQuestionType(), $row["type_tag"]) === 0) {
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
        $idx = count($default_sorting);
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

    public static function _getQuestionClasses() : array
    {
        $classes = array_map(
            static function (array $c) : string {
                return $c["type_tag"];
            },
            self::_getQuestiontypes()
        );
        return $classes;
    }

    /**
     * @todo move to question manager, use dto
     */
    public static function _getQuestionTypeTranslations() : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();

        $component_factory = $DIC["component.factory"];
        
        $lng->loadLanguageModule("survey");
        $result = $ilDB->query("SELECT * FROM svy_qtype");
        $types = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            if ((int) $row["plugin"] === 0) {
                $types[$row['type_tag']] = $lng->txt($row["type_tag"]);
            } else {
                foreach ($component_factory->getActivePluginsInSlot("svyq") as $pl) {
                    if (strcmp($pl->getQuestionType(), $row["type_tag"]) === 0) {
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
     * @return array<int, string> keys are ref or obj IDs, values are titles
     */
    public static function _getAvailableQuestionpools(
        bool $use_object_id = false,
        bool $could_be_offline = false,
        bool $showPath = false,
        string $permission = "read"
    ) : array {
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
            if ($could_be_offline || ($allqpls[$obj_id] ?? 0) == 1) {
                if ($use_object_id) {
                    $result_array[$obj_id] = $titles[$ref_id];
                } else {
                    $result_array[(int) $ref_id] = $titles[$ref_id];
                }
            }
        }
        return $result_array;
    }

    /**
     * Checks whether or not a question plugin with a given name is active
     */
    public function isPluginActive(string $a_pname) : bool
    {
        return $this->component_repository->getPluginByName($a_pname)->isActive();
    }
    
    /**
     * Returns title, description and type for an array of question id's
     * @param int[] $question_ids An array of question id's
     * @return array Array of associated arrays with title, description, type_tag
     * @todo move to question manager, use dto
     */
    public function getQuestionInfos(array $question_ids) : array
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
                    $found[] = array('id' => $data["question_id"],
                                     'title' => $data["title"],
                                     'description' => $data["description"],
                                     'type_tag' => $data["type_tag"]
                    );
                }
            }
        }
        return $found;
    }

    /**
     * Remove all questions with tstamp = 0
     */
    public function purgeQuestions() : void
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
     * @param int $question_id Object id of the question
     */
    public function copyToClipboard(
        int $question_id
    ) : void {
        $this->edit_manager->addQuestionToClipboard($question_id, "copy");
    }
    
    /**
     * Moves a question to the clipboard
     */
    public function moveToClipboard(
        int $question_id
    ) : void {
        $this->edit_manager->addQuestionToClipboard($question_id, "move");
    }

    /**
     * Copies/Moves a question from the clipboard
     */
    public function pasteFromClipboard() : void
    {
        $ilDB = $this->db;

        $qentries = $this->edit_manager->getQuestionsFromClipboard();
        if (count($qentries) > 0) {
            foreach ($qentries as $question_object) {
                if (strcmp($question_object["action"], "move") === 0) {
                    $result = $ilDB->queryF(
                        "SELECT obj_fi FROM svy_question WHERE question_id = %s",
                        array('integer'),
                        array($question_object["question_id"])
                    );
                    if ($result->numRows() === 1) {
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
                            if (is_dir($source_path)) {
                                $target_path = CLIENT_WEB_DIR . "/survey/" . $this->getId() . "/";
                                if (!is_dir($target_path)) {
                                    ilFileUtils::makeDirParents($target_path);
                                }
                                rename($source_path, $target_path . $question_object["question_id"]);
                            }
                        } else {
                            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("spl_move_same_pool"), true);
                            return;
                        }
                    }
                } else {
                    $this->copyQuestion($question_object["question_id"], $this->getId());
                }
            }
        }
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("spl_paste_success"), true);
        $this->edit_manager->clearClipboardQuestions();
    }
    
    /**
     * @param int[] $obligatory_questions obligatory question ids
     */
    public function setObligatoryStates(
        array $obligatory_questions
    ) : void {
        $ilDB = $this->db;
        foreach ($this->getQuestions() as $question_id) {
            $status = (int) (isset($obligatory_questions["$question_id"]));
            
            $ilDB->manipulate("UPDATE svy_question" .
                " SET obligatory = " . $ilDB->quote($status, "integer") .
                " WHERE question_id = " . $ilDB->quote($question_id, "integer"));
        }
    }
}
