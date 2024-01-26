<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class ilObjQuestionPool
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @extends ilObject
* @defgroup ModulesTestQuestionPool Modules/TestQuestionPool
*/

class ilObjQuestionPool extends ilObject
{
    /**
    * Online status of questionpool
    *
    * @var string
    */
    public $online;

    /**
     * the fact wether taxonomies are shown or not
     *
     * @var boolean
     */
    private $showTaxonomies = null;

    /**
     * the id of taxonomy used for navigation in questin list
     *
     * @var integer
     */
    private $navTaxonomyId = null;

    /**
     * @var bool
     */
    private $skillServiceEnabled;

    /**
     * Import for container (courses containing tests) import
     * @var string
     */
    private $import_dir;

    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "qpl";
        parent::__construct($a_id, $a_call_by_reference);
        $this->setOnline(0);

        $this->skillServiceEnabled = false;
    }

    /**
    * create questionpool object
    */
    public function create($a_upload = false)
    {
        parent::create();

        // meta data will be created by
        // import parser
        if (!$a_upload) {
            $this->createMetaData();
        }
    }

    /**
    * Creates a database reference id for the object (saves the object
    * to the database and creates a reference id in the database)
    *
    * @access public
    */
    public function createReference()
    {
        $result = parent::createReference();
        $this->saveToDb();
        return $result;
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

    public function updateMetaData()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        include_once "./Services/MetaData/classes/class.ilMD.php";
        $md = new ilMD($this->getId(), 0, $this->getType());
        $md_gen = &$md->getGeneral();
        if ($md_gen == false) {
            include_once "./Services/MetaData/classes/class.ilMDCreator.php";
            $md_creator = new ilMDCreator($this->getId(), 0, $this->getType());
            $md_creator->setTitle($this->getTitle());
            $md_creator->setTitleLanguage($ilUser->getPref('language'));
            $md_creator->create();
        }
        parent::updateMetaData();
    }

    /**
    * read object data from db into object
    * @param	boolean
    * @access	public
    */
    public function read($a_force_db = false)
    {
        parent::read($a_force_db);
        $this->loadFromDb();
    }


    /**
    * delete object and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // delete meta data
        $this->deleteMetaData();

        //put here your module specific stuff
        $this->deleteQuestionpool();

        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImportFails.php';
        $qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($this->getId());
        $qsaImportFails->deleteRegisteredImportFails();

        return true;
    }

    public function deleteQuestionpool()
    {
        $questions = &$this->getAllQuestions();

        if (count($questions)) {
            foreach ($questions as $question_id) {
                $this->deleteQuestion($question_id);
            }
        }

        // delete export files
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        $qpl_data_dir = ilUtil::getDataDir() . "/qpl_data";
        $directory = $qpl_data_dir . "/qpl_" . $this->getId();
        if (is_dir($directory)) {
            include_once "./Services/Utilities/classes/class.ilUtil.php";
            ilUtil::delDir($directory);
        }
    }

    /**
    * Deletes a question from the question pool
    *
    * @param integer $question_id The database id of the question
    * @access private
    */
    public function deleteQuestion($question_id)
    {
        include_once "./Modules/Test/classes/class.ilObjTest.php";
        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";

        $question = assQuestion::_instanciateQuestion($question_id);
        $this->addQuestionChangeListeners($question);
        $question->delete($question_id);
    }

    /**
     * @param assQuestion $question
     */
    public function addQuestionChangeListeners(assQuestion $question)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        foreach (ilObjTest::getPoolQuestionChangeListeners($ilDB, $this->getId()) as $listener) {
            $question->addQuestionChangeListener($listener);
        }
    }

    /**
    * Loads a ilObjQuestionpool object from a database
    *
    * @access public
    */
    public function loadFromDb()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT * FROM qpl_questionpool WHERE obj_fi = %s",
            array('integer'),
            array($this->getId())
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            $this->setOnline($row['isonline']);
            $this->setShowTaxonomies($row['show_taxonomies']);
            $this->setNavTaxonomyId($row['nav_taxonomy']);
            $this->setSkillServiceEnabled($row['skill_service']);
        }
    }

    /**
    * Saves a ilObjQuestionpool object to a database
    *
    * @access public
    */
    public function saveToDb()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT id_questionpool FROM qpl_questionpool WHERE obj_fi = %s",
            array('integer'),
            array($this->getId())
        );

        if ($result->numRows() == 1) {
            $result = $ilDB->update(
                'qpl_questionpool',
                array(
                    'isonline' => array('text', $this->getOnline()),
                    'show_taxonomies' => array('integer', (int) $this->getShowTaxonomies()),
                    'nav_taxonomy' => array('integer', (int) $this->getNavTaxonomyId()),
                    'skill_service' => array('integer', (int) $this->isSkillServiceEnabled()),
                    'tstamp' => array('integer', time())
                ),
                array(
                    'obj_fi' => array('integer', $this->getId())
                )
            );
        } else {
            $next_id = $ilDB->nextId('qpl_questionpool');

            $result = $ilDB->insert('qpl_questionpool', array(
                'id_questionpool' => array('integer', $next_id),
                'isonline' => array('text', $this->getOnline()),
                'show_taxonomies' => array('integer', (int) $this->getShowTaxonomies()),
                'nav_taxonomy' => array('integer', (int) $this->getNavTaxonomyId()),
                'skill_service' => array('integer', (int) $this->isSkillServiceEnabled()),
                'tstamp' => array('integer', time()),
                'obj_fi' => array('integer', $this->getId())
            ));
        }
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
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($question_id < 1) {
            return;
        }

        $result = $ilDB->queryF(
            "SELECT qpl_qst_type.type_tag FROM qpl_questions, qpl_qst_type WHERE qpl_questions.question_type_fi = qpl_qst_type.question_type_id AND qpl_questions.question_id = %s",
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
    * get description of content object
    *
    * @return	string		description
    */
    public function getDescription()
    {
        return parent::getDescription();
    }

    /**
    * set description of content object
    */
    public function setDescription($a_description)
    {
        parent::setDescription($a_description);
    }

    /**
    * get title of glossary object
    *
    * @return	string		title
    */
    public function getTitle()
    {
        return parent::getTitle();
    }

    /**
    * set title of glossary object
    */
    public function setTitle($a_title)
    {
        parent::setTitle($a_title);
    }

    /**
    * Checks whether the question is in use or not
    *
    * @param integer $question_id The question id of the question to be checked
    * @return boolean The number of datasets which are affected by the use of the query.
    * @access public
    */
    public function isInUse($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT COUNT(solution_id) solution_count FROM tst_solutions WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        $row = $ilDB->fetchAssoc($result);
        return $row["solution_count"];
    }

    public function &createQuestion($question_type, $question_id = -1)
    {
        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        if ($question_id > 0) {
            return assQuestion::_instanciateQuestionGUI($question_id);
        }
        assQuestion::_includeClass($question_type, 1);
        $question_type_gui = $question_type . "GUI";
        $question_gui = new $question_type_gui();
        return $question_gui;
    }

    /**
    * Duplicates a question for a questionpool
    *
    * @param integer $question_id The database id of the question
    * @access public
    */
    public function duplicateQuestion($question_id)
    {
        $question = &$this->createQuestion("", $question_id);
        $newtitle = $question->object->getTitle();
        if ($question->object->questionTitleExists($this->getId(), $question->object->getTitle())) {
            $counter = 2;
            while ($question->object->questionTitleExists($this->getId(), $question->object->getTitle() . " ($counter)")) {
                $counter++;
            }
            $newtitle = $question->object->getTitle() . " ($counter)";
        }
        $new_id = $question->object->duplicate(false, $newtitle);
        // update question count of question pool
        ilObjQuestionPool::_updateQuestionCount($this->getId());
        return $new_id;
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
        $question_gui = &$this->createQuestion("", $question_id);
        if ($question_gui->object->getObjId() == $questionpool_to) {
            // the question is copied into the same question pool
            return $this->duplicateQuestion($question_id);
        } else {
            // the question is copied into another question pool
            $newtitle = $question_gui->object->getTitle();
            if ($question_gui->object->questionTitleExists($this->getId(), $question_gui->object->getTitle())) {
                $counter = 2;
                while ($question_gui->object->questionTitleExists($this->getId(), $question_gui->object->getTitle() . " ($counter)")) {
                    $counter++;
                }
                $newtitle = $question_gui->object->getTitle() . " ($counter)";
            }
            return $question_gui->object->copyObject($this->getId(), $newtitle);
        }
    }

    /**
    * Calculates the data for the print view of the questionpool
    *
    * @access public
    */
    public function getPrintviewQuestions()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query_result = $ilDB->queryF(
            "SELECT qpl_questions.*, qpl_qst_type.type_tag, qpl_qst_type.plugin, qpl_questions.tstamp updated FROM qpl_questions, qpl_qst_type WHERE qpl_questions.original_id IS NULL AND qpl_questions.tstamp > 0 AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id AND qpl_questions.obj_fi = %s",
            array('integer'),
            array($this->getId())
        );
        $rows = array();
        $types = $this->getQuestionTypeTranslations();
        if ($query_result->numRows()) {
            while ($row = $ilDB->fetchAssoc($query_result)) {
                $row['ttype'] = $types[$row['type_tag']];
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
     * @param ilXmlWriter $xmlWriter
     */
    private function exportXMLSettings($xmlWriter)
    {
        $xmlWriter->xmlStartTag('Settings');

        $xmlWriter->xmlElement('ShowTaxonomies', null, (int) $this->getShowTaxonomies());
        $xmlWriter->xmlElement('NavTaxonomy', null, (int) $this->getNavTaxonomyId());
        $xmlWriter->xmlElement('SkillService', null, (int) $this->isSkillServiceEnabled());

        $xmlWriter->xmlEndTag('Settings');
    }

    /**
    * export pages of test to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function objectToXmlWriter(ilXmlWriter &$a_xml_writer, $a_inst, $a_target_dir, &$expLog, $questions)
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];

        $this->mob_ids = array();
        $this->file_ids = array();

        $attrs = array();
        $attrs["Type"] = "Questionpool_Test";
        $a_xml_writer->xmlStartTag("ContentObject", $attrs);

        // MetaData
        $this->exportXMLMetaData($a_xml_writer);

        // Settings
        $this->exportXMLSettings($a_xml_writer);

        // PageObjects
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export Page Objects");
        $ilBench->start("ContentObjectExport", "exportPageObjects");
        $this->exportXMLPageObjects($a_xml_writer, $a_inst, $expLog, $questions);
        $ilBench->stop("ContentObjectExport", "exportPageObjects");
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export Page Objects");

        // MediaObjects
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export Media Objects");
        $ilBench->start("ContentObjectExport", "exportMediaObjects");
        $this->exportXMLMediaObjects($a_xml_writer, $a_inst, $a_target_dir, $expLog);
        $ilBench->stop("ContentObjectExport", "exportMediaObjects");
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export Media Objects");

        // FileItems
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export File Items");
        $ilBench->start("ContentObjectExport", "exportFileItems");
        $this->exportFileItems($a_target_dir, $expLog);
        $ilBench->stop("ContentObjectExport", "exportFileItems");
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export File Items");

        // skill assignments
        $this->populateQuestionSkillAssignmentsXml($a_xml_writer, $questions);

        $a_xml_writer->xmlEndTag("ContentObject");
    }

    /**
     * @param ilXmlWriter $a_xml_writer
     * @param $questions
     */
    protected function populateQuestionSkillAssignmentsXml(ilXmlWriter &$a_xml_writer, $questions)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
        $assignmentList = new ilAssQuestionSkillAssignmentList($ilDB);
        $assignmentList->setParentObjId($this->getId());
        $assignmentList->loadFromDb();
        $assignmentList->loadAdditionalSkillData();

        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentExporter.php';
        $skillQuestionAssignmentExporter = new ilAssQuestionSkillAssignmentExporter();
        $skillQuestionAssignmentExporter->setXmlWriter($a_xml_writer);
        $skillQuestionAssignmentExporter->setQuestionIds($questions);
        $skillQuestionAssignmentExporter->setAssignmentList($assignmentList);
        $skillQuestionAssignmentExporter->export();
    }

    /**
    * export content objects meta data to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXMLMetaData(&$a_xml_writer)
    {
        include_once("Services/MetaData/classes/class.ilMD2XML.php");
        $md2xml = new ilMD2XML($this->getId(), 0, $this->getType());
        $md2xml->setExportMode(true);
        $md2xml->startExport();
        $a_xml_writer->appendXML($md2xml->getXML());
    }

    public function modifyExportIdentifier($a_tag, $a_param, $a_value)
    {
        if ($a_tag == "Identifier" && $a_param == "Entry") {
            include_once "./Services/Utilities/classes/class.ilUtil.php";
            $a_value = ilUtil::insertInstIntoID($a_value);
        }

        return $a_value;
    }


    /**
    * export page objects to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXMLPageObjects(&$a_xml_writer, $a_inst, &$expLog, $questions)
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];

        include_once "./Modules/LearningModule/classes/class.ilLMPageObject.php";

        foreach ($questions as $question_id) {
            $ilBench->start("ContentObjectExport", "exportPageObject");
            $expLog->write(date("[y-m-d H:i:s] ") . "Page Object " . $question_id);

            $attrs = array();
            $a_xml_writer->xmlStartTag("PageObject", $attrs);


            // export xml to writer object
            $ilBench->start("ContentObjectExport", "exportPageObject_XML");
            include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPage.php");
            $page_object = new ilAssQuestionPage($question_id);
            $page_object->buildDom();
            $page_object->insertInstIntoIDs($a_inst);
            $mob_ids = $page_object->collectMediaObjects(false);
            require_once 'Services/COPage/classes/class.ilPCFileList.php';
            $file_ids = ilPCFileList::collectFileItems($page_object, $page_object->getDomDoc());
            $xml = $page_object->getXMLFromDom(false, false, false, "", true);
            $xml = str_replace("&", "&amp;", $xml);
            $a_xml_writer->appendXML($xml);
            $page_object->freeDom();
            unset($page_object);

            $ilBench->stop("ContentObjectExport", "exportPageObject_XML");

            // collect media objects
            $ilBench->start("ContentObjectExport", "exportPageObject_CollectMedia");
            foreach ($mob_ids as $mob_id) {
                $this->mob_ids[$mob_id] = $mob_id;
            }
            $ilBench->stop("ContentObjectExport", "exportPageObject_CollectMedia");

            // collect all file items
            $ilBench->start("ContentObjectExport", "exportPageObject_CollectFileItems");
            //$file_ids = $page_obj->getFileItemIds();
            foreach ($file_ids as $file_id) {
                $this->file_ids[$file_id] = $file_id;
            }
            $ilBench->stop("ContentObjectExport", "exportPageObject_CollectFileItems");

            $a_xml_writer->xmlEndTag("PageObject");

            $ilBench->stop("ContentObjectExport", "exportPageObject");
        }
    }

    /**
    * export media objects to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXMLMediaObjects(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
    {
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

        foreach ($this->mob_ids as $mob_id) {
            $expLog->write(date("[y-m-d H:i:s] ") . "Media Object " . $mob_id);
            if (ilObjMediaObject::_exists($mob_id)) {
                $media_obj = new ilObjMediaObject($mob_id);
                $media_obj->exportXML($a_xml_writer, $a_inst);
                $media_obj->exportFiles($a_target_dir);
                unset($media_obj);
            }
        }
    }

    /**
    * export files of file itmes
    *
    */
    public function exportFileItems($target_dir, &$expLog) : void
    {
        include_once("./Modules/File/classes/class.ilObjFile.php");

        foreach ($this->file_ids as $file_id) {
            $expLog->write(date("[y-m-d H:i:s] ") . "File Item " . $file_id);
            $file_dir = $target_dir . '/objects/il_' . IL_INST_ID . '_file_' . $file_id;
            ilUtil::makeDir($file_dir);
            $file_obj = new ilObjFile($file_id, false);
            $source_file = $file_obj->getFile($file_obj->getVersion());
            if (!is_file($source_file)) {
                $source_file = $file_obj->getFile();
            }
            if (is_file($source_file)) {
                copy($source_file, $file_dir . '/' . $file_obj->getFileName());
            }
            unset($file_obj);
        }
    }

    /**
    * creates data directory for export files
    * (data_dir/qpl_data/qpl_<id>/export, depending on data
    * directory that is set in ILIAS setup/ini)
    */
    public function createExportDirectory()
    {
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        $qpl_data_dir = ilUtil::getDataDir() . "/qpl_data";
        ilUtil::makeDir($qpl_data_dir);
        if (!is_writable($qpl_data_dir)) {
            $this->ilias->raiseError("Questionpool Data Directory (" . $qpl_data_dir
                . ") not writeable.", $this->ilias->error_obj->FATAL);
        }

        // create learning module directory (data_dir/lm_data/lm_<id>)
        $qpl_dir = $qpl_data_dir . "/qpl_" . $this->getId();
        ilUtil::makeDir($qpl_dir);
        if (!@is_dir($qpl_dir)) {
            $this->ilias->raiseError("Creation of Questionpool Directory failed.", $this->ilias->error_obj->FATAL);
        }
        // create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
        ilUtil::makeDir($this->getExportDirectory('xls'));
        if (!@is_dir($this->getExportDirectory('xls'))) {
            $this->ilias->raiseError("Creation of Export Directory failed.", $this->ilias->error_obj->FATAL);
        }
        ilUtil::makeDir($this->getExportDirectory('zip'));
        if (!@is_dir($this->getExportDirectory('zip'))) {
            $this->ilias->raiseError("Creation of Export Directory failed.", $this->ilias->error_obj->FATAL);
        }
    }

    /**
    * get export directory of questionpool
    */
    public function getExportDirectory($type = "")
    {
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        switch ($type) {
            case 'xml':
                include_once("./Services/Export/classes/class.ilExport.php");
                $export_dir = ilExport::_getExportDirectory($this->getId(), $type, $this->getType());
                break;
            case 'xls':
            case 'zip':
                $export_dir = ilUtil::getDataDir() . "/qpl_data" . "/qpl_" . $this->getId() . "/export_$type";
                break;
            default:
                $export_dir = ilUtil::getDataDir() . "/qpl_data" . "/qpl_" . $this->getId() . "/export";
                break;
        }
        return $export_dir;
    }

    /**
    * creates data directory for import files
    * (data_dir/qpl_data/qpl_<id>/import, depending on data
    * directory that is set in ILIAS setup/ini)
    */
    public static function _createImportDirectory()
    {
        global $DIC;
        $ilias = $DIC['ilias'];

        include_once "./Services/Utilities/classes/class.ilUtil.php";
        $qpl_data_dir = ilUtil::getDataDir() . "/qpl_data";
        ilUtil::makeDir($qpl_data_dir);

        if (!is_writable($qpl_data_dir)) {
            $ilias->raiseError("Questionpool Data Directory (" . $qpl_data_dir
                . ") not writeable.", $ilias->error_obj->FATAL);
        }

        // create questionpool directory (data_dir/qpl_data/qpl_import)
        $qpl_dir = $qpl_data_dir . "/qpl_import";
        ilUtil::makeDir($qpl_dir);
        if (!@is_dir($qpl_dir)) {
            $ilias->raiseError("Creation of Questionpool Directory failed.", $ilias->error_obj->FATAL);
        }
        return $qpl_dir;
    }

    /**
    * set import directory
    */
    public static function _setImportDirectory($a_import_dir = null)
    {
        if (strlen($a_import_dir)) {
            $_SESSION["qpl_import_dir"] = $a_import_dir;
        } else {
            unset($_SESSION["qpl_import_dir"]);
        }
    }

    /**
    * get import directory of lm
    */
    public static function _getImportDirectory()
    {
        if (strlen($_SESSION["qpl_import_dir"])) {
            return $_SESSION["qpl_import_dir"];
        }
        return null;
    }

    public function getImportDirectory()
    {
        return ilObjQuestionPool::_getImportDirectory();
    }

    /**
    * Retrieve an array containing all question ids of the questionpool
    *
    * @return array An array containing all question ids of the questionpool
    */
    public function &getAllQuestions()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT question_id FROM qpl_questions WHERE obj_fi = %s AND qpl_questions.tstamp > 0 AND original_id IS NULL",
            array('integer'),
            array($this->getId())
        );
        $questions = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            array_push($questions, $row["question_id"]);
        }
        return $questions;
    }

    public function &getAllQuestionIds()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query_result = $ilDB->queryF(
            "SELECT question_id, qpl_qst_type.type_tag, qpl_qst_type.plugin FROM qpl_questions, qpl_qst_type WHERE original_id IS NULL AND qpl_questions.tstamp > 0 AND obj_fi = %s AND complete = %s AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id",
            array('integer','text'),
            array($this->getId(), 1)
        );
        $questions = array();
        if ($query_result->numRows()) {
            while ($row = $ilDB->fetchAssoc($query_result)) {
                if ($row["plugin"]) {
                    if ($this->isPluginActive($row["type_tag"])) {
                        array_push($questions, $row["question_id"]);
                    }
                } else {
                    array_push($questions, $row["question_id"]);
                }
            }
        }
        return $questions;
    }

    public function checkQuestionParent($questionId)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $row = $DIC->database()->fetchAssoc($DIC->database()->queryF(
            "SELECT COUNT(question_id) cnt FROM qpl_questions WHERE question_id = %s AND obj_fi = %s",
            array('integer', 'integer'),
            array($questionId, $this->getId())
        ));

        return (bool) $row['cnt'];
    }

    /**
    * get array of (two) new created questions for
    * import id
    */
    public function getImportMapping()
    {
        if (!is_array($this->import_mapping)) {
            return array();
        } else {
            return $this->import_mapping;
        }
    }

    /**
    * Returns a QTI xml representation of a list of questions
    *
    * @param array $questions An array containing the question ids of the questions
    * @return string The QTI xml representation of the questions
    * @access public
    */
    public function questionsToXML($questions)
    {
        $xml = "";
        // export button was pressed
        if (count($questions) > 0) {
            foreach ($questions as $key => $value) {
                $question = &$this->createQuestion("", $value);
                $xml .= $question->object->toXML();
            }
            if (count($questions) > 1) {
                $xml = preg_replace("/<\/questestinterop>\s*<.xml.*?>\s*<questestinterop>/", "", $xml);
            }
        }
        $xml = preg_replace("/(<\?xml[^>]*?>)/", "\\1" . "<!DOCTYPE questestinterop SYSTEM \"ims_qtiasiv1p2p1.dtd\">", $xml);
        return $xml;
    }

    /**
    * Returns the number of questions in a question pool
    *
    * @param integer $questonpool_id Object id of the questionpool to examine
    * @param boolean $complete_questions_only If set to TRUE, returns only the number of complete questions in the questionpool. Default is FALSE
    * @return integer The number of questions in the questionpool object
    * @access public
    */
    public static function _getQuestionCount($questionpool_id, $complete_questions_only = false)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        if ($complete_questions_only) {
            $result = $ilDB->queryF(
                "SELECT COUNT(question_id) question_count FROM qpl_questions WHERE obj_fi = %s AND qpl_questions.tstamp > 0 AND original_id IS NULL AND complete = %s",
                array('integer', 'text'),
                array($questionpool_id, 1)
            );
        } else {
            $result = $ilDB->queryF(
                "SELECT COUNT(question_id) question_count FROM qpl_questions WHERE obj_fi = %s AND qpl_questions.tstamp > 0 AND original_id IS NULL",
                array('integer'),
                array($questionpool_id)
            );
        }
        $row = $ilDB->fetchAssoc($result);
        return $row["question_count"];
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

    public function setShowTaxonomies($showTaxonomies)
    {
        $this->showTaxonomies = $showTaxonomies;
    }

    public function getShowTaxonomies()
    {
        return $this->showTaxonomies;
    }

    public function setNavTaxonomyId($navTaxonomyId)
    {
        $this->navTaxonomyId = $navTaxonomyId;
    }

    public function getNavTaxonomyId()
    {
        return $this->navTaxonomyId;
    }

    public function isNavTaxonomyActive()
    {
        return $this->getShowTaxonomies() && (int) $this->getNavTaxonomyId();
    }

    public static function _lookupOnline($a_obj_id, $is_reference = false)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($is_reference) {
            $result = $ilDB->queryF(
                "SELECT qpl_questionpool.isonline FROM qpl_questionpool,object_reference WHERE object_reference.ref_id = %s AND object_reference.obj_id = qpl_questionpool.obj_fi",
                array('integer'),
                array($a_obj_id)
            );
        } else {
            $result = $ilDB->queryF(
                "SELECT isonline FROM qpl_questionpool WHERE obj_fi = %s",
                array('integer'),
                array($a_obj_id)
            );
        }
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            return $row["isonline"];
        }
        return 0;
    }

    /**
    * Checks a question pool for questions with the same maximum points
    *
    * @param integer $a_obj_id Object id of the question pool
    * @access private
    */
    public static function _hasEqualPoints($a_obj_id, $is_reference = false)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($is_reference) {
            $result = $ilDB->queryF(
                "SELECT count(DISTINCT qpl_questions.points) equal_points FROM qpl_questions, object_reference WHERE object_reference.ref_id = %s AND qpl_questions.tstamp > 0 AND object_reference.obj_id = qpl_questions.obj_fi AND qpl_questions.original_id IS NULL",
                array('integer'),
                array($a_obj_id)
            );
        } else {
            $result = $ilDB->queryF(
                "SELECT count(DISTINCT points) equal_points FROM qpl_questions WHERE obj_fi = %s AND qpl_questions.tstamp > 0 AND qpl_questions.original_id IS NULL",
                array('integer'),
                array($a_obj_id)
            );
        }
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            if ($row["equal_points"] == 1) {
                return 1;
            } else {
                return 0;
            }
        }
        return 0;
    }

    /**
    * Copies/Moves a question from the clipboard
    *
    * @access private
    */
    public function pasteFromClipboard()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $success = false;
        if (array_key_exists("qpl_clipboard", $_SESSION)) {
            $success = true;
            foreach ($_SESSION["qpl_clipboard"] as $question_object) {
                if (strcmp($question_object["action"], "move") == 0) {
                    $result = $ilDB->queryF(
                        "SELECT obj_fi FROM qpl_questions WHERE question_id = %s",
                        array('integer'),
                        array($question_object["question_id"])
                    );
                    if ($result->numRows() == 1) {
                        $row = $ilDB->fetchAssoc($result);
                        $source_questionpool = $row["obj_fi"];
                        // change the questionpool id in the qpl_questions table
                        $affectedRows = $ilDB->manipulateF(
                            "UPDATE qpl_questions SET obj_fi = %s WHERE question_id = %s",
                            array('integer','integer'),
                            array($this->getId(), $question_object["question_id"])
                        );
                        if (!$affectedRows) {
                            $success = false;
                        }

                        // move question data to the new target directory
                        $source_path = CLIENT_WEB_DIR . "/assessment/" . $source_questionpool . "/" . $question_object["question_id"] . "/";
                        if (@is_dir($source_path)) {
                            $target_path = CLIENT_WEB_DIR . "/assessment/" . $this->getId() . "/";
                            if (!@is_dir($target_path)) {
                                include_once "./Services/Utilities/classes/class.ilUtil.php";
                                ilUtil::makeDirParents($target_path);
                            }
                            rename($source_path, $target_path . $question_object["question_id"]);
                        }
                        // update question count of source question pool
                        ilObjQuestionPool::_updateQuestionCount($source_questionpool);
                    }
                } else {
                    $new_question_id = $this->copyQuestion($question_object["question_id"], $this->getId());
                    if (!$new_question_id) {
                        $success = false;
                    }
                }
            }
        }
        // update question count of question pool
        ilObjQuestionPool::_updateQuestionCount($this->getId());
        unset($_SESSION["qpl_clipboard"]);

        return (bool) $success;
    }

    /**
    * Copies a question to the clipboard
    *
    * @param integer $question_id Object id of the question
    * @access private
    */
    public function copyToClipboard($question_id)
    {
        if (!array_key_exists("qpl_clipboard", $_SESSION)) {
            $_SESSION["qpl_clipboard"] = array();
        }
        $_SESSION["qpl_clipboard"][$question_id] = array("question_id" => $question_id, "action" => "copy");
    }

    /**
    * Moves a question to the clipboard
    *
    * @param integer $question_id Object id of the question
    * @access private
    */
    public function moveToClipboard($question_id)
    {
        if (!array_key_exists("qpl_clipboard", $_SESSION)) {
            $_SESSION["qpl_clipboard"] = array();
        }
        $_SESSION["qpl_clipboard"][$question_id] = array("question_id" => $question_id, "action" => "move");
    }

    public function cleanupClipboard($deletedQuestionId)
    {
        if (!isset($_SESSION['qpl_clipboard'])) {
            return;
        }

        if (!isset($_SESSION['qpl_clipboard'][$deletedQuestionId])) {
            return;
        }

        unset($_SESSION['qpl_clipboard'][$deletedQuestionId]);

        if (!count($_SESSION['qpl_clipboard'])) {
            unset($_SESSION['qpl_clipboard']);
        }
    }

    /**
    * Returns true, if the question pool is writeable by a given user
    *
    * @param integer $object_id The object id of the question pool object
    * @param integer $user_id The database id of the user
    * @access public
    */
    public static function _isWriteable($object_id, $user_id)
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        include_once "./Services/Object/classes/class.ilObject.php";
        $refs = ilObject::_getAllReferences($object_id);
        if (count($refs)) {
            foreach ($refs as $ref_id) {
                if ($rbacsystem->checkAccess("write", $ref_id) && (ilObject::_hasUntrashedReference($object_id))) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
    * Returns an array containing the qpl_question and qpl_qst_type fields for an array of question ids
    *
    * @param array $question_ids An array containing the question ids
    * @return array An array containing the details of the requested questions
    * @access public
    */
    public function &getQuestionDetails($question_ids)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = array();
        $query_result = $ilDB->query("SELECT qpl_questions.*, qpl_qst_type.type_tag FROM qpl_questions, qpl_qst_type WHERE qpl_questions.question_type_fi = qpl_qst_type.question_type_id AND " . $ilDB->in('qpl_questions.question_id', $question_ids, false, 'integer') . " ORDER BY qpl_questions.title");
        if ($query_result->numRows()) {
            while ($row = $ilDB->fetchAssoc($query_result)) {
                array_push($result, $row);
            }
        }
        return $result;
    }

    /**
    * Returns an array containing the qpl_question and qpl_qst_type fields
    * of deleteable questions for an array of question ids
    *
    * @param array $question_ids An array containing the question ids
    * @return array An array containing the details of the requested questions
    * @access public
    */
    public function &getDeleteableQuestionDetails($question_ids)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];

        $result = array();
        $query_result = $ilDB->query("SELECT qpl_questions.*, qpl_qst_type.type_tag FROM qpl_questions, qpl_qst_type WHERE qpl_questions.question_type_fi = qpl_qst_type.question_type_id AND " . $ilDB->in('qpl_questions.question_id', $question_ids, false, 'integer') . " ORDER BY qpl_questions.title");
        if ($query_result->numRows()) {
            include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
            while ($row = $ilDB->fetchAssoc($query_result)) {
                if (!assQuestion::_isUsedInRandomTest($row["question_id"])) {
                    array_push($result, $row);
                } else {
                    // the question was used in a random test prior to ILIAS 3.7 so it was inserted
                    // as a reference to the original question pool object and not as a copy. To allow
                    // the deletion of the question pool object, a copy must be created and all database references
                    // of the original question must changed with the reference of the copy

                    // 1. Create a copy of the original question
                    $question = &$this->createQuestion("", $row["question_id"]);
                    $duplicate_id = $question->object->duplicate(true);
                    if ($duplicate_id > 0) {
                        // 2. replace the question id in the solutions
                        $affectedRows = $ilDB->manipulateF(
                            "UPDATE tst_solutions SET question_fi = %s WHERE question_fi = %s",
                            array('integer','integer'),
                            array($duplicate_id, $row["question_id"])
                        );

                        // 3. replace the question id in the question list of random tests
                        $affectedRows = $ilDB->manipulateF(
                            "UPDATE tst_test_rnd_qst SET question_fi = %s WHERE question_fi = %s",
                            array('integer','integer'),
                            array($duplicate_id, $row["question_id"])
                        );

                        // 4. replace the question id in the test results
                        $affectedRows = $ilDB->manipulateF(
                            "UPDATE tst_test_result SET question_fi = %s WHERE question_fi = %s",
                            array('integer','integer'),
                            array($duplicate_id, $row["question_id"])
                        );

                        // 5. replace the question id in the test&assessment log
                        $affectedRows = $ilDB->manipulateF(
                            "UPDATE ass_log SET question_fi = %s WHERE question_fi = %s",
                            array('integer','integer'),
                            array($duplicate_id, $row["question_id"])
                        );

                        // 6. The original question can be deleted, so add it to the list of questions
                        array_push($result, $row);
                    }
                }
            }
        }
        return $result;
    }

    /**
    * Retrieves the full path to a question pool with a given reference id
    *
    * @return string The full path to the question pool including the locator
    * @access public
    */
    public function _getFullPathToQpl($ref_id)
    {
        global $DIC;
        $tree = $DIC['tree'];
        $path = $tree->getPathFull($ref_id);
        $items = array();
        $counter = 0;
        foreach ($path as $item) {
            if (($counter > 0) && ($counter < count($path) - 1)) {
                array_push($items, $item["title"]);
            }
            $counter++;
        }
        $fullpath = join(" > ", $items);
        include_once "./Services/Utilities/classes/class.ilStr.php";
        if (strlen($fullpath) > 60) {
            $fullpath = ilStr::subStr($fullpath, 0, 30) . "..." . ilStr::subStr($fullpath, ilStr::strLen($fullpath) - 30, 30);
        }
        return $fullpath;
    }

    /**
    * Returns the available question pools for the active user
    *
    * @return array The available question pools
    * @access public
    */
    public static function _getAvailableQuestionpools($use_object_id = false, $equal_points = false, $could_be_offline = false, $showPath = false, $with_questioncount = false, $permission = "read", $usr_id = "")
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];

        $result_array = array();
        $permission = (strlen($permission) == 0) ? "read" : $permission;
        $qpls = ilUtil::_getObjectsByOperations("qpl", $permission, (strlen($usr_id)) ? $usr_id : $ilUser->getId(), -1);
        $obj_ids = array();
        foreach ($qpls as $ref_id) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            $obj_ids[$ref_id] = $obj_id;
        }
        $titles = ilObject::_prepareCloneSelection($qpls, "qpl");
        if (count($obj_ids)) {
            $in = $ilDB->in('object_data.obj_id', $obj_ids, false, 'integer');
            if ($could_be_offline) {
                $result = $ilDB->query("SELECT qpl_questionpool.*, object_data.title FROM qpl_questionpool, object_data WHERE " .
                    "qpl_questionpool.obj_fi = object_data.obj_id AND $in ORDER BY object_data.title");
            } else {
                $result = $ilDB->queryF(
                    "SELECT qpl_questionpool.*, object_data.title FROM qpl_questionpool, object_data WHERE " .
                    "qpl_questionpool.obj_fi = object_data.obj_id AND $in AND qpl_questionpool.isonline = %s " .
                    "ORDER BY object_data.title",
                    array('text'),
                    array(1)
                );
            }
            while ($row = $ilDB->fetchAssoc($result)) {
                $add = true;
                if ($equal_points) {
                    if (!ilObjQuestionPool::_hasEqualPoints($row["obj_fi"])) {
                        $add = false;
                    }
                }
                if ($add) {
                    $ref_id = array_search($row["obj_fi"], $obj_ids);
                    $title = (($showPath) ? $titles[$ref_id] : $row["title"]);
                    if ($with_questioncount) {
                        $title .= " [" . $row["questioncount"] . " " . ($row["questioncount"] == 1 ? $lng->txt("ass_question") : $lng->txt("assQuestions")) . "]";
                    }

                    if ($use_object_id) {
                        $result_array[$row["obj_fi"]] = array(
                            'qpl_id' => $row['obj_fi'],
                            'qpl_title' => $row['title'],
                            "title" => $title,
                            "count" => $row["questioncount"]
                        );
                    } else {
                        $result_array[$ref_id] = array(
                            'qpl_id' => $row['obj_fi'],
                            'qpl_title' => $row['title'],
                            "title" => $title,
                            "count" => $row["questioncount"]
                        );
                    }
                }
            }
        }
        return $result_array;
    }

    public function &getQplQuestions()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $questions = array();
        $result = $ilDB->queryF(
            "SELECT qpl_questions.question_id FROM qpl_questions WHERE qpl_questions.original_id IS NULL AND qpl_questions.tstamp > 0 AND qpl_questions.obj_fi = %s",
            array('integer'),
            array($this->getId())
        );
        while ($row = $ilDB->fetchAssoc($result)) {
            array_push($questions, $row["question_id"]);
        }
        return $questions;
    }

    /**
    * Creates a 1:1 copy of the object and places the copy in a given repository
    *
    * @access public
    */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $newObj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);

        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);
        $newObj->setOnline($this->getOnline());
        if ($cp_options->isRootNode($this->getRefId())) {
            $newObj->setOnline(0);
        }

        $newObj->setSkillServiceEnabled($this->isSkillServiceEnabled());
        $newObj->setShowTaxonomies($this->getShowTaxonomies());
        $newObj->saveToDb();

        // clone the questions in the question pool
        $questions = &$this->getQplQuestions();
        $questionIdsMap = array();
        foreach ($questions as $question_id) {
            $newQuestionId = $newObj->copyQuestion($question_id, $newObj->getId());
            $questionIdsMap[$question_id] = $newQuestionId;
        }

        // clone meta data
        include_once "./Services/MetaData/classes/class.ilMD.php";
        $md = new ilMD($this->getId(), 0, $this->getType());
        $md->cloneMD($newObj->getId(), 0, $newObj->getType());

        // update the metadata with the new title of the question pool
        $newObj->updateMetaData();

        require_once 'Modules/TestQuestionPool/classes/class.ilQuestionPoolTaxonomiesDuplicator.php';
        $duplicator = new ilQuestionPoolTaxonomiesDuplicator();
        $duplicator->setSourceObjId($this->getId());
        $duplicator->setSourceObjType($this->getType());
        $duplicator->setTargetObjId($newObj->getId());
        $duplicator->setTargetObjType($newObj->getType());
        $duplicator->setQuestionIdMapping($questionIdsMap);
        $duplicator->duplicate($duplicator->getAllTaxonomiesForSourceObject());

        $duplicatedTaxKeyMap = $duplicator->getDuplicatedTaxonomiesKeysMap();
        $newObj->setNavTaxonomyId($duplicatedTaxKeyMap->getMappedTaxonomyId($this->getNavTaxonomyId()));
        $newObj->saveToDb();

        return $newObj;
    }

    public function getQuestionTypes($all_tags = false, $fixOrder = false, $withDeprecatedTypes = true)
    {
        return self::_getQuestionTypes($all_tags, $fixOrder, $withDeprecatedTypes);
    }

    public static function _getQuestionTypes($all_tags = false, $fixOrder = false, $withDeprecatedTypes = true)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];

        include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
        $forbidden_types = ilObjAssessmentFolder::_getForbiddenQuestionTypes();
        $lng->loadLanguageModule("assessment");
        $result = $ilDB->query("SELECT * FROM qpl_qst_type");
        $types = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            if ($all_tags || (!in_array($row["question_type_id"], $forbidden_types))) {
                global $DIC;
                $ilLog = $DIC['ilLog'];

                if ($row["plugin"] == 0) {
                    $types[$lng->txt($row["type_tag"])] = $row;
                } else {
                    global $DIC;
                    $ilPluginAdmin = $DIC['ilPluginAdmin'];
                    $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, "TestQuestionPool", "qst");
                    foreach ($pl_names as $pl_name) {
                        $pl = ilPlugin::getPluginObject(IL_COMP_MODULE, "TestQuestionPool", "qst", $pl_name);
                        if (strcmp($pl->getQuestionType(), $row["type_tag"]) == 0) {
                            $types[$pl->getQuestionTypeTranslation()] = $row;
                        }
                    }
                }
            }
        }

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionTypeOrderer.php';
        $orderMode = ($fixOrder ? ilAssQuestionTypeOrderer::ORDER_MODE_FIX : ilAssQuestionTypeOrderer::ORDER_MODE_ALPHA);
        $orderer = new ilAssQuestionTypeOrderer($types, $orderMode);
        $types = $orderer->getOrderedTypes($withDeprecatedTypes);

        return $types;
    }

    public static function getQuestionTypeByTypeId($type_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT type_tag FROM qpl_qst_type WHERE question_type_id = %s";
        $types = array('integer');
        $values = array($type_id);
        $result = $ilDB->queryF($query, $types, $values);

        if ($row = $ilDB->fetchAssoc($result)) {
            return $row['type_tag'];
        }
    }

    public static function getQuestionTypeTranslations()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $ilLog = $DIC['ilLog'];
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        $lng->loadLanguageModule("assessment");
        $result = $ilDB->query("SELECT * FROM qpl_qst_type");
        $types = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            if ($row["plugin"] == 0) {
                $types[$row['type_tag']] = $lng->txt($row["type_tag"]);
            } else {
                $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, "TestQuestionPool", "qst");
                foreach ($pl_names as $pl_name) {
                    $pl = ilPlugin::getPluginObject(IL_COMP_MODULE, "TestQuestionPool", "qst", $pl_name);
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
    * Get all self assessment question types.
    *
    * @todo		Make it more flexible
    */
    public static function &_getSelfAssessmentQuestionTypes($all_tags = false)
    {
        /*		$allowed_types = array(
                    "assSingleChoice" => 1,
                    "assMultipleChoice" => 2,
                    "assClozeTest" => 3,
                    "assMatchingQuestion" => 4,
                    "assOrderingQuestion" => 5,
                    "assOrderingHorizontal" => 6,
                    "assImagemapQuestion" => 7,
                    "assTextQuestion" => 8,
                    "assTextSubset" => 9,
                    "assErrorText" => 10
                    );*/
        $allowed_types = array(
            "assSingleChoice" => 1,
            "assMultipleChoice" => 2,
            "assKprimChoice" => 3,
            "assClozeTest" => 4,
            "assMatchingQuestion" => 5,
            "assOrderingQuestion" => 6,
            "assOrderingHorizontal" => 7,
            "assImagemapQuestion" => 8,
            "assTextSubset" => 9,
            "assErrorText" => 10,
            "assLongMenu" => 11
            );
        $satypes = array();
        $qtypes = ilObjQuestionPool::_getQuestionTypes($all_tags);
        foreach ($qtypes as $k => $t) {
            //if (in_array($t["type_tag"], $allowed_types))
            if (isset($allowed_types[$t["type_tag"]])) {
                $t["order"] = $allowed_types[$t["type_tag"]];
                $satypes[$k] = $t;
            }
        }
        return $satypes;
    }


    public function &getQuestionList()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $questions = array();
        $result = $ilDB->queryF(
            "SELECT qpl_questions.*, qpl_qst_type.* FROM qpl_questions, qpl_qst_type WHERE qpl_questions.original_id IS NULL AND qpl_questions.obj_fi = %s AND qpl_questions.tstamp > 0 AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id",
            array('integer'),
            array($this->getId())
        );
        while ($row = $ilDB->fetchAssoc($result)) {
            array_push($questions, $row);
        }
        return $questions;
    }

    /**
    * Updates the number of available questions for a question pool in the database
    *
    * @param integer $object_id Object id of the questionpool to examine
    * @access public
    */
    public static function _updateQuestionCount($object_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->manipulateF(
            "UPDATE qpl_questionpool SET questioncount = %s, tstamp = %s WHERE obj_fi = %s",
            array('integer','integer','integer'),
            array(ilObjQuestionPool::_getQuestionCount($object_id, true), time(), $object_id)
        );
    }

    /**
    * Checks wheather or not a question plugin with a given name is active
    *
    * @param string $a_pname The plugin name
    * @access public
    */
    public function isPluginActive($questionType)
    {
        /* @var ilPluginAdmin $ilPluginAdmin */
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        $plugins = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, "TestQuestionPool", "qst");
        foreach ($plugins as $pluginName) {
            if ($pluginName == $questionType) { // plugins having pname == qtype
                return true;
            }

            /* @var ilQuestionsPlugin $plugin */
            $plugin = ilPlugin::getPluginObject(IL_COMP_MODULE, "TestQuestionPool", "qst", $pluginName);

            if ($plugin->getQuestionType() == $questionType) { // plugins havin an independent name
                return true;
            }
        }

        return false;
    }

    /*
    * Remove all questions with owner = 0
    */
    public function purgeQuestions()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        require_once 'Modules/TestQuestionPool/classes/class.ilAssIncompleteQuestionPurger.php';
        $incompleteQuestionPurger = new ilAssIncompleteQuestionPurger($ilDB);
        $incompleteQuestionPurger->setOwnerId($ilUser->getId());
        $incompleteQuestionPurger->purge();
    }

    /**
     * get ids of all taxonomies corresponding to current pool
     *
     * @return array
     */
    public function getTaxonomyIds()
    {
        require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';
        return ilObjTaxonomy::getUsageOfObject($this->getId());
    }

    /**
     * @return boolean
     */
    public function isSkillServiceEnabled()
    {
        return $this->skillServiceEnabled;
    }

    /**
     * @param boolean $skillServiceEnabled
     */
    public function setSkillServiceEnabled($skillServiceEnabled)
    {
        $this->skillServiceEnabled = $skillServiceEnabled;
    }

    private static $isSkillManagementGloballyActivated = null;

    public static function isSkillManagementGloballyActivated()
    {
        if (self::$isSkillManagementGloballyActivated === null) {
            $skmgSet = new ilSkillManagementSettings();

            self::$isSkillManagementGloballyActivated = $skmgSet->isActivated();
        }

        return self::$isSkillManagementGloballyActivated;
    }

    public function fromXML($xmlFile)
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilObjQuestionPoolXMLParser.php';
        $parser = new ilObjQuestionPoolXMLParser($this, $xmlFile);
        $parser->startParsing();
    }
} // END class.ilObjQuestionPool
