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

require_once './components/ILIAS/Test/classes/inc.AssessmentConstants.php';

/**
 * Class ilObjQuestionPool
 *
 * @author        Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @version       $Id$
 *
 * @extends ilObject
 * @defgroup      ModulesTestQuestionPool Modules/TestQuestionPool
 */
class ilObjQuestionPool extends ilObject
{
    private ilComponentRepository $component_repository;
    private ilBenchmark $benchmark;

    private array $mob_ids;
    private array $file_ids;
    private ?bool $show_taxonomies = null;
    private bool $skill_service_enabled;
    private \ILIAS\TestQuestionPool\QuestionInfoService $questioninfo;

    /**
     * Constructor
     * @access    public
     * @param integer    reference_id or object_id
     * @param boolean    treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;
        $this->component_repository = $DIC['component.repository'];
        $this->benchmark = $DIC['ilBench'];
        $this->questioninfo = $DIC->testQuestionPool()->questionInfo();
        $this->type = 'qpl';
        parent::__construct($a_id, $a_call_by_reference);

        $this->skill_service_enabled = false;
    }

    /**
     * create questionpool object
     */
    public function create($a_upload = false): int
    {
        $id = parent::create();

        // meta data will be created by
        // import parser
        if (!$a_upload) {
            $this->createMetaData();
        }
        return $id;
    }

    /**
     * Creates a database reference id for the object (saves the object
     * to the database and creates a reference id in the database)
     *
     * @access public
     */
    public function createReference(): int
    {
        $result = parent::createReference();
        $this->saveToDb();
        return $result;
    }

    /**
     * update object data
     *
     * @access    public
     * @return    boolean
     */
    public function update(): bool
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
     * @param boolean
     * @access    public
     */
    public function read($a_force_db = false): void
    {
        parent::read($a_force_db);
        $this->loadFromDb();
    }

    /**
     * delete object and all related data
     *
     * @access    public
     * @return    boolean    true if all object data were removed; false if only a references were removed
     */
    public function delete(): bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // delete meta data
        $this->deleteMetaData();

        //put here your module specific stuff
        $this->deleteQuestionpool();

        $qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($this->getId());
        $qsaImportFails->deleteRegisteredImportFails();

        return true;
    }

    public function deleteQuestionpool(): void
    {
        $questions = &$this->getAllQuestions();

        if (count($questions)) {
            foreach ($questions as $question_id) {
                $this->deleteQuestion($question_id);
            }
        }

        $qpl_data_dir = ilFileUtils::getDataDir() . '/qpl_data';
        $directory = $qpl_data_dir . '/qpl_' . $this->getId();
        if (is_dir($directory)) {
            ilFileUtils::delDir($directory);
        }
    }

    public function deleteQuestion(int $question_id): void
    {
        $question = assQuestion::instantiateQuestion($question_id);
        $question->delete($question_id);
    }

    public function loadFromDb(): void
    {
        $result = $this->db->queryF(
            'SELECT * FROM qpl_questionpool WHERE obj_fi = %s',
            ['integer'],
            [$this->getId()]
        );
        if ($result->numRows() == 1) {
            $row = $this->db->fetchAssoc($result);
            $this->setShowTaxonomies($row['show_taxonomies']);
            $this->setSkillServiceEnabled($row['skill_service']);
        }
    }

    public function saveToDb(): void
    {
        $result = $this->db->queryF(
            'SELECT id_questionpool FROM qpl_questionpool WHERE obj_fi = %s',
            ['integer'],
            [$this->getId()]
        );

        if ($result->numRows() == 1) {
            $result = $this->db->update(
                'qpl_questionpool',
                [
                    'show_taxonomies' => ['integer', (int) $this->getShowTaxonomies()],
                    'skill_service' => ['integer', (int) $this->isSkillServiceEnabled()],
                    'tstamp' => ['integer', time()]
                ],
                [
                    'obj_fi' => ['integer', $this->getId()]
                ]
            );
        } else {
            $next_id = $this->db->nextId('qpl_questionpool');

            $result = $this->db->insert('qpl_questionpool', [
                'id_questionpool' => ['integer', $next_id],
                'show_taxonomies' => ['integer', (int) $this->getShowTaxonomies()],
                'skill_service' => ['integer', (int) $this->isSkillServiceEnabled()],
                'tstamp' => ['integer', time()],
                'obj_fi' => ['integer', $this->getId()]
            ]);
        }
    }

    public function getQuestiontype($question_id)
    {
        if ($question_id < 1) {
            return null;
        }

        $result = $this->db->queryF(
            'SELECT qpl_qst_type.type_tag FROM qpl_questions, qpl_qst_type WHERE qpl_questions.question_type_fi = qpl_qst_type.question_type_id AND qpl_questions.question_id = %s',
            ['integer'],
            [$question_id]
        );

        if ($result->numRows() == 1) {
            $data = $this->db->fetchAssoc($result);
            return $data['type_tag'];
        }
        return null;
    }

    public function isInUse(int $question_id): bool
    {
        $result = $this->db->queryF(
            'SELECT COUNT(solution_id) solution_count FROM tst_solutions WHERE question_fi = %s',
            ['integer'],
            [$question_id]
        );
        $row = $this->db->fetchAssoc($result);
        return $row['solution_count'];
    }

    public function createQuestion(string $question_type, int $question_id = -1)
    {
        if ($question_id > 0) {
            return assQuestion::instantiateQuestionGUI($question_id);
        }
        $question_type_gui = $question_type . 'GUI';
        $question_gui = new $question_type_gui();
        return $question_gui;
    }

    public function duplicateQuestion(int $question_id): int
    {
        $question = $this->createQuestion('', $question_id);
        $newtitle = $question->object->getTitle();
        if ($this->questioninfo->questionTitleExistsInPool($this->getId(), $question->object->getTitle())) {
            $counter = 2;
            while ($this->questioninfo->questionTitleExistsInPool(
                $this->getId(),
                $question->object->getTitle() . ' (' . $counter . ')'
            )) {
                $counter++;
            }
            $newtitle = $question->object->getTitle() . ' (' . $counter . ')';
        }
        $new_id = $question->object->duplicate(false, $newtitle);
        ilObjQuestionPool::_updateQuestionCount($new_id);
        return $new_id;
    }

    public function copyQuestion(int $question_id, int $questionpool_to): int
    {
        $question_gui = $this->createQuestion('', $question_id);
        if ($question_gui->object->getObjId() == $questionpool_to) {
            // the question is copied into the same question pool
            return $this->duplicateQuestion($question_id);
        } else {
            // the question is copied into another question pool
            $newtitle = $question_gui->object->getTitle();
            if ($this->questioninfo->questionTitleExistsInPool($this->getId(), $question_gui->object->getTitle())) {
                $counter = 2;
                while ($this->questioninfo->questionTitleExistsInPool(
                    $this->getId(),
                    $question_gui->object->getTitle() . ' (' . $counter . ')'
                )) {
                    $counter++;
                }
                $newtitle = $question_gui->object->getTitle() . ' (' . $counter . ')';
            }
            return $question_gui->object->copyObject($this->getId(), $newtitle);
        }
    }

    public function getPrintviewQuestions(): array
    {
        $query_result = $this->db->queryF(
            'SELECT qpl_questions.*, qpl_qst_type.type_tag, qpl_qst_type.plugin, qpl_questions.tstamp updated FROM qpl_questions, qpl_qst_type WHERE qpl_questions.original_id IS NULL AND qpl_questions.tstamp > 0 AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id AND qpl_questions.obj_fi = %s',
            ['integer'],
            [$this->getId()]
        );
        $rows = [];
        $types = $this->getQuestionTypeTranslations();
        if ($query_result->numRows()) {
            while ($row = $this->db->fetchAssoc($query_result)) {
                $row['ttype'] = $types[$row['type_tag']];
                if ($row['plugin']) {
                    if ($this->isPluginActive($row['type_tag'])) {
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
    private function exportXMLSettings($xmlWriter): void
    {
        $xmlWriter->xmlStartTag('Settings');

        $xmlWriter->xmlElement('ShowTaxonomies', null, (int) $this->getShowTaxonomies());
        $xmlWriter->xmlElement('SkillService', null, (int) $this->isSkillServiceEnabled());

        $xmlWriter->xmlEndTag('Settings');
    }

    /**
     * export pages of test to xml (see ilias_co.dtd)
     *
     * @param object $a_xml_writer            ilXmlWriter object that receives the
     *                                        xml data
     */
    public function objectToXmlWriter(ilXmlWriter &$a_xml_writer, $a_inst, $a_target_dir, &$expLog, $questions): void
    {
        $ilBench = $this->benchmark;

        $this->mob_ids = [];
        $this->file_ids = [];

        $attrs = [];
        $attrs['Type'] = 'Questionpool_Test';
        $a_xml_writer->xmlStartTag('ContentObject', $attrs);

        // MetaData
        $this->exportXMLMetaData($a_xml_writer);

        // Settings
        $this->exportXMLSettings($a_xml_writer);

        // PageObjects
        $expLog->write(date('[y-m-d H:i:s] ') . 'Start Export Page Objects');
        $ilBench->start('ContentObjectExport', 'exportPageObjects');
        $this->exportXMLPageObjects($a_xml_writer, $a_inst, $expLog, $questions);
        $ilBench->stop('ContentObjectExport', 'exportPageObjects');
        $expLog->write(date('[y-m-d H:i:s] ') . 'Finished Export Page Objects');

        // MediaObjects
        $expLog->write(date('[y-m-d H:i:s] ') . 'Start Export Media Objects');
        $ilBench->start('ContentObjectExport', 'exportMediaObjects');
        $this->exportXMLMediaObjects($a_xml_writer, $a_inst, $a_target_dir, $expLog);
        $ilBench->stop('ContentObjectExport', 'exportMediaObjects');
        $expLog->write(date('[y-m-d H:i:s] ') . 'Finished Export Media Objects');

        // FileItems
        $expLog->write(date('[y-m-d H:i:s] ') . 'Start Export File Items');
        $ilBench->start('ContentObjectExport', 'exportFileItems');
        $this->exportFileItems($a_target_dir, $expLog);
        $ilBench->stop('ContentObjectExport', 'exportFileItems');
        $expLog->write(date('[y-m-d H:i:s] ') . 'Finished Export File Items');

        // skill assignments
        $this->populateQuestionSkillAssignmentsXml($a_xml_writer, $questions);

        $a_xml_writer->xmlEndTag('ContentObject');
    }

    /**
     * @param ilXmlWriter $a_xml_writer
     * @param             $questions
     */
    protected function populateQuestionSkillAssignmentsXml(ilXmlWriter &$a_xml_writer, $questions): void
    {
        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($this->getId());
        $assignmentList->loadFromDb();
        $assignmentList->loadAdditionalSkillData();

        $skillQuestionAssignmentExporter = new ilAssQuestionSkillAssignmentExporter();
        $skillQuestionAssignmentExporter->setXmlWriter($a_xml_writer);
        $skillQuestionAssignmentExporter->setQuestionIds($questions);
        $skillQuestionAssignmentExporter->setAssignmentList($assignmentList);
        $skillQuestionAssignmentExporter->export();
    }

    /**
     * export content objects meta data to xml (see ilias_co.dtd)
     *
     * @param object $a_xml_writer            ilXmlWriter object that receives the
     *                                        xml data
     */
    public function exportXMLMetaData(&$a_xml_writer): void
    {
        $md2xml = new ilMD2XML($this->getId(), 0, $this->getType());
        $md2xml->setExportMode(true);
        $md2xml->startExport();
        $a_xml_writer->appendXML($md2xml->getXML());
    }

    public function modifyExportIdentifier($a_tag, $a_param, $a_value)
    {
        if ($a_tag == 'Identifier' && $a_param == 'Entry') {
            $a_value = ilUtil::insertInstIntoID($a_value);
        }

        return $a_value;
    }

    /**
     * export page objects to xml (see ilias_co.dtd)
     *
     * @param object $a_xml_writer            ilXmlWriter object that receives the
     *                                        xml data
     */
    public function exportXMLPageObjects(&$a_xml_writer, $a_inst, &$expLog, $questions): void
    {
        $ilBench = $this->benchmark;

        foreach ($questions as $question_id) {
            $ilBench->start('ContentObjectExport', 'exportPageObject');
            $expLog->write(date('[y-m-d H:i:s] ') . 'Page Object ' . $question_id);

            $attrs = [];
            $a_xml_writer->xmlStartTag('PageObject', $attrs);

            // export xml to writer object
            $ilBench->start('ContentObjectExport', 'exportPageObject_XML');
            $page_object = new ilAssQuestionPage($question_id);
            $page_object->buildDom();
            $page_object->insertInstIntoIDs($a_inst);
            $mob_ids = $page_object->collectMediaObjects(false);
            $file_ids = ilPCFileList::collectFileItems($page_object, $page_object->getDomDoc());
            $xml = $page_object->getXMLFromDom(false, false, false, '', true);
            $xml = str_replace('&', '&amp;', $xml);
            $a_xml_writer->appendXML($xml);
            $page_object->freeDom();
            unset($page_object);
            $ilBench->stop('ContentObjectExport', 'exportPageObject_XML');

            $ilBench->start("ContentObjectExport", "exportPageObject_CollectMedia");
            foreach ($mob_ids as $mob_id) {
                $this->mob_ids[$mob_id] = $mob_id;
            }
            $ilBench->stop('ContentObjectExport', 'exportPageObject_CollectMedia');

            // collect all file items
            $ilBench->start('ContentObjectExport', 'exportPageObject_CollectFileItems');
            //$file_ids = $page_obj->getFileItemIds();
            foreach ($file_ids as $file_id) {
                $this->file_ids[$file_id] = $file_id;
            }
            $ilBench->stop('ContentObjectExport', 'exportPageObject_CollectFileItems');

            $a_xml_writer->xmlEndTag("PageObject");

            $ilBench->stop('ContentObjectExport', 'exportPageObject');
        }
    }

    public function exportXMLMediaObjects(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog): void
    {
        foreach ($this->mob_ids as $mob_id) {
            $expLog->write(date('[y-m-d H:i:s] ') . 'Media Object ' . $mob_id);
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
    public function exportFileItems($target_dir, &$expLog): void
    {
        foreach ($this->file_ids as $file_id) {
            $expLog->write(date("[y-m-d H:i:s] ") . "File Item " . $file_id);
            $file_dir = $target_dir . '/objects/il_' . IL_INST_ID . '_file_' . $file_id;
            ilFileUtils::makeDir($file_dir);
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
    public function createExportDirectory(): void
    {
        $qpl_data_dir = ilFileUtils::getDataDir() . '/qpl_data';
        ilFileUtils::makeDir($qpl_data_dir);
        if (!is_writable($qpl_data_dir)) {
            $this->error->raiseError(
                'Questionpool Data Directory (' . $qpl_data_dir
                . ') not writeable.',
                $this->error->FATAL
            );
        }

        // create learning module directory (data_dir/lm_data/lm_<id>)
        $qpl_dir = $qpl_data_dir . '/qpl_' . $this->getId();
        ilFileUtils::makeDir($qpl_dir);
        if (!@is_dir($qpl_dir)) {
            $this->error->raiseError('Creation of Questionpool Directory failed.', $this->error->FATAL);
        }
        // create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
        ilFileUtils::makeDir($this->getExportDirectory('xlsx'));
        if (!@is_dir($this->getExportDirectory('xlsx'))) {
            $this->error->raiseError('Creation of Export Directory failed.', $this->error->FATAL);
        }
        ilFileUtils::makeDir($this->getExportDirectory('zip'));
        if (!@is_dir($this->getExportDirectory('zip'))) {
            $this->error->raiseError('Creation of Export Directory failed.', $this->error->FATAL);
        }
    }

    /**
     * get export directory of questionpool
     */
    public function getExportDirectory($type = ''): string
    {
        switch ($type) {
            case 'xml':
                $export_dir = ilExport::_getExportDirectory($this->getId(), $type, $this->getType());
                break;
            case 'xlsx':
            case 'zip':
                $export_dir = ilFileUtils::getDataDir() . "/qpl_data/qpl_{$this->getId()}/export_{$type}";
                break;
            default:
                $export_dir = ilFileUtils::getDataDir() . '/qpl_data' . '/qpl_' . $this->getId() . '/export';
                break;
        }
        return $export_dir;
    }

    public static function _setImportDirectory($a_import_dir = null): void
    {
        if ($a_import_dir !== null) {
            ilSession::set('qpl_import_dir', $a_import_dir);
            return;
        }

        ilSession::clear('qpl_import_dir');
    }

    /**
     * get import directory of lm
     */
    public static function _getImportDirectory(): string
    {
        return ilSession::get('qpl_import_dir') ?? '';
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
    public function &getAllQuestions(): array
    {
        $result = $this->db->queryF(
            'SELECT question_id FROM qpl_questions WHERE obj_fi = %s AND qpl_questions.tstamp > 0 AND original_id IS NULL',
            ['integer'],
            [$this->getId()]
        );
        $questions = [];
        while ($row = $this->db->fetchAssoc($result)) {
            array_push($questions, $row['question_id']);
        }
        return $questions;
    }

    public function &getAllQuestionIds(): array
    {
        $query_result = $this->db->queryF(
            'SELECT question_id, qpl_qst_type.type_tag, qpl_qst_type.plugin FROM qpl_questions, qpl_qst_type WHERE original_id IS NULL AND qpl_questions.tstamp > 0 AND obj_fi = %s AND complete = %s AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id',
            ['integer', 'text'],
            [$this->getId(), 1]
        );
        $questions = [];
        if ($query_result->numRows()) {
            while ($row = $this->db->fetchAssoc($query_result)) {
                if ($row['plugin']) {
                    if ($this->isPluginActive($row['type_tag'])) {
                        array_push($questions, $row['question_id']);
                    }
                } else {
                    array_push($questions, $row['question_id']);
                }
            }
        }
        return $questions;
    }

    public function checkQuestionParent(int $question_id): bool
    {
        $row = $this->db->fetchAssoc(
            $this->db->queryF(
                'SELECT COUNT(question_id) cnt FROM qpl_questions WHERE question_id = %s AND obj_fi = %s',
                ['integer', 'integer'],
                [$question_id, $this->getId()]
            )
        );

        return (bool) $row['cnt'];
    }

    /**
     * get array of (two) new created questions for
     * import id
     */
    public function getImportMapping(): array
    {
        return [];
    }

    /**
     * Returns a QTI xml representation of a list of questions
     *
     * @param array $questions An array containing the question ids of the questions
     * @return string The QTI xml representation of the questions
     * @access public
     */
    public function questionsToXML($questions): string
    {
        $xml = '';
        // export button was pressed
        if (count($questions) > 0) {
            foreach ($questions as $key => $value) {
                $question = $this->createQuestion('', $value);
                $xml .= $question->object->toXML();
            }
            if (count($questions) > 1) {
                $xml = preg_replace('/<\/questestinterop>\s*<.xml.*?>\s*<questestinterop>/', '', $xml);
            }
        }
        $xml = preg_replace(
            '/(<\?xml[^>]*?>)/',
            '\\1' . '<!DOCTYPE questestinterop SYSTEM "ims_qtiasiv1p2p1.dtd">',
            $xml
        );
        return $xml;
    }

    protected static function _getQuestionCount(int $pool_id): int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            'SELECT COUNT(question_id) question_count FROM qpl_questions WHERE obj_fi = %s AND qpl_questions.tstamp > 0 AND original_id IS NULL AND complete = %s',
            ['integer', 'text'],
            [$pool_id, 1]
        );
        $row = $ilDB->fetchAssoc($result);
        return $row['question_count'];
    }

    public function setShowTaxonomies($show_taxonomies): void
    {
        $this->show_taxonomies = $show_taxonomies;
    }

    public function getShowTaxonomies(): ?bool
    {
        return $this->show_taxonomies;
    }

    /**
     * Checks a question pool for questions with the same maximum points
     *
     * @param integer $a_obj_id Object id of the question pool
     * @access private
     */
    public static function _hasEqualPoints($a_obj_id, $is_reference = false): int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($is_reference) {
            $result = $ilDB->queryF(
                'SELECT count(DISTINCT qpl_questions.points) equal_points FROM qpl_questions, object_reference WHERE object_reference.ref_id = %s AND qpl_questions.tstamp > 0 AND object_reference.obj_id = qpl_questions.obj_fi AND qpl_questions.original_id IS NULL',
                ['integer'],
                [$a_obj_id]
            );
        } else {
            $result = $ilDB->queryF(
                'SELECT count(DISTINCT points) equal_points FROM qpl_questions WHERE obj_fi = %s AND qpl_questions.tstamp > 0 AND qpl_questions.original_id IS NULL',
                ['integer'],
                [$a_obj_id]
            );
        }
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            if ($row['equal_points'] == 1) {
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
    public function pasteFromClipboard(): bool
    {
        $success = false;
        if (ilSession::get('qpl_clipboard') != null) {
            $success = true;
            foreach (ilSession::get('qpl_clipboard') as $question_object) {
                if (strcmp($question_object['action'], 'move') == 0) {
                    $result = $this->db->queryF(
                        'SELECT obj_fi FROM qpl_questions WHERE question_id = %s',
                        ['integer'],
                        [$question_object['question_id']]
                    );
                    if ($result->numRows() == 1) {
                        $row = $this->db->fetchAssoc($result);
                        $source_questionpool = $row['obj_fi'];
                        $affectedRows = $this->db->manipulateF(
                            'UPDATE qpl_questions SET obj_fi = %s WHERE question_id = %s',
                            ['integer', 'integer'],
                            [$this->getId(), $question_object['question_id']]
                        );
                        if (!$affectedRows) {
                            $success = false;
                        }

                        $source_path = CLIENT_WEB_DIR . '/assessment/' . $source_questionpool . '/' . $question_object['question_id'] . '/';
                        if (@is_dir($source_path)) {
                            $target_path = CLIENT_WEB_DIR . '/assessment/' . $this->getId() . '/';
                            if (!@is_dir($target_path)) {
                                ilFileUtils::makeDirParents($target_path);
                            }
                            rename($source_path, $target_path . $question_object['question_id']);
                        }

                        ilObjQuestionPool::_updateQuestionCount($source_questionpool);
                    }
                } else {
                    $new_question_id = $this->copyQuestion($question_object['question_id'], $this->getId());
                    if (!$new_question_id) {
                        $success = false;
                    }
                }
            }
        }
        // update question count of question pool
        ilObjQuestionPool::_updateQuestionCount($this->getId());
        ilSession::clear('qpl_clipboard');

        return $success;
    }

    /**
     * Copies a question to the clipboard
     *
     * @param integer $question_id Object id of the question
     * @access private
     */
    public function copyToClipboard($question_id): void
    {
        if (ilSession::get('qpl_clipboard') == null) {
            ilSession::set('qpl_clipboard', []);
        }
        $clip = ilSession::get('qpl_clipboard');
        $clip[$question_id] = ['question_id' => $question_id, 'action' => 'copy'];
        ilSession::set('qpl_clipboard', $clip);
    }

    /**
     * Moves a question to the clipboard
     *
     * @param integer $question_id Object id of the question
     * @access private
     */
    public function moveToClipboard($question_id): void
    {
        if (ilSession::get('qpl_clipboard') == null) {
            ilSession::set('qpl_clipboard', []);
        }
        $clip = ilSession::get('qpl_clipboard');
        $clip[$question_id] = ['question_id' => $question_id, 'action' => 'move'];
        ilSession::set('qpl_clipboard', $clip);
    }

    public function cleanupClipboard($deletedQuestionId): void
    {
        if (ilSession::get('qpl_clipboard') == null) {
            return;
        }

        $clip = ilSession::get('qpl_clipboard');
        if (!isset($clip[$deletedQuestionId])) {
            return;
        }

        unset($clip[$deletedQuestionId]);

        if (!count($clip)) {
            ilSession::clear('qpl_clipboard');
        } else {
            ilSession::set('qpl_clipboard', $clip);
        }
    }

    /**
     * Returns true, if the question pool is writeable by a given user
     *
     * @param integer $object_id The object id of the question pool object
     * @param integer $user_id   The database id of the user
     * @access public
     */
    public static function _isWriteable($object_id, $user_id): bool
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];

        $refs = ilObject::_getAllReferences($object_id);
        if (count($refs)) {
            foreach ($refs as $ref_id) {
                if ($rbacsystem->checkAccess('write', $ref_id) && (ilObject::_hasUntrashedReference($object_id))) {
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
    public function getQuestionDetails($question_ids): array
    {
        $result = [];
        $query_result = $this->db->query(
            'SELECT qpl_questions.*, qpl_qst_type.type_tag FROM qpl_questions, qpl_qst_type WHERE qpl_questions.question_type_fi = qpl_qst_type.question_type_id AND ' . $ilDB->in(
                'qpl_questions.question_id',
                $question_ids,
                false,
                'integer'
            ) . ' ORDER BY qpl_questions.title'
        );
        if ($query_result->numRows()) {
            while ($row = $this->db->fetchAssoc($query_result)) {
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
    public function getDeleteableQuestionDetails($question_ids): array
    {
        $result = [];
        $query_result = $this->db->query(
            'SELECT qpl_questions.*, qpl_qst_type.type_tag FROM qpl_questions, qpl_qst_type WHERE qpl_questions.question_type_fi = qpl_qst_type.question_type_id AND '
            . $this->db->in('qpl_questions.question_id', $question_ids, false, 'integer')
            . ' ORDER BY qpl_questions.title'
        );
        if ($query_result->numRows()) {
            while ($row = $this->db->fetchAssoc($query_result)) {
                if (!$this->questioninfo->isUsedInRandomTest($row['question_id'])) {
                    array_push($result, $row);
                } else {
                    // the question was used in a random test prior to ILIAS 3.7 so it was inserted
                    // as a reference to the original question pool object and not as a copy. To allow
                    // the deletion of the question pool object, a copy must be created and all database references
                    // of the original question must changed with the reference of the copy

                    // 1. Create a copy of the original question
                    $question = $this->createQuestion('', $row['question_id']);
                    $duplicate_id = $question->object->duplicate(true);
                    if ($duplicate_id > 0) {
                        // 2. replace the question id in the solutions
                        $affectedRows = $this->db->manipulateF(
                            'UPDATE tst_solutions SET question_fi = %s WHERE question_fi = %s',
                            ['integer', 'integer'],
                            [$duplicate_id, $row['question_id']]
                        );

                        // 3. replace the question id in the question list of random tests
                        $affectedRows = $this->db->manipulateF(
                            'UPDATE tst_test_rnd_qst SET question_fi = %s WHERE question_fi = %s',
                            ['integer', 'integer'],
                            [$duplicate_id, $row['question_id']]
                        );

                        // 4. replace the question id in the test results
                        $affectedRows = $this->db->manipulateF(
                            'UPDATE tst_test_result SET question_fi = %s WHERE question_fi = %s',
                            ['integer', 'integer'],
                            [$duplicate_id, $row['question_id']]
                        );

                        // 5. replace the question id in the test&assessment log
                        $affectedRows = $this->db->manipulateF(
                            'UPDATE ass_log SET question_fi = %s WHERE question_fi = %s',
                            ['integer', 'integer'],
                            [$duplicate_id, $row['question_id']]
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
     * Returns the available question pools for the active user
     *
     * @return array The available question pools
     * @access public
     */
    public static function _getAvailableQuestionpools(
        $use_object_id = false,
        $equal_points = false,
        $could_be_offline = false,
        $showPath = false,
        $with_questioncount = false,
        $permission = 'read',
        $usr_id = ''
    ): array {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];

        $result_array = [];
        $permission = (strlen($permission) == 0) ? 'read' : $permission;
        $qpls = ilUtil::_getObjectsByOperations('qpl', $permission, (strlen($usr_id)) ? $usr_id : $ilUser->getId(), -1);
        $obj_ids = [];
        foreach ($qpls as $ref_id) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            $obj_ids[$ref_id] = $obj_id;
        }
        $titles = ilObject::_prepareCloneSelection($qpls, 'qpl');
        if (count($obj_ids)) {
            $in = $ilDB->in('object_data.obj_id', $obj_ids, false, 'integer');
            if ($could_be_offline) {
                $result = $ilDB->query(
                    'SELECT qpl_questionpool.*, object_data.title FROM qpl_questionpool, object_data WHERE ' .
                    'qpl_questionpool.obj_fi = object_data.obj_id AND ' . $in . ' ORDER BY object_data.title'
                );
            } else {
                $result = $ilDB->queryF(
                    'SELECT qpl_questionpool.*, object_data.title FROM qpl_questionpool, object_data WHERE ' .
                    'qpl_questionpool.obj_fi = object_data.obj_id AND ' . $in . ' AND object_data.offline = %s ' .
                    'ORDER BY object_data.title',
                    ['text'],
                    [0]
                );
            }
            while ($row = $ilDB->fetchAssoc($result)) {
                $add = true;
                if ($equal_points) {
                    if (!ilObjQuestionPool::_hasEqualPoints($row['obj_fi'])) {
                        $add = false;
                    }
                }
                if ($add) {
                    $ref_id = array_search($row['obj_fi'], $obj_ids);
                    $title = (($showPath) ? $titles[$ref_id] : $row['title']);
                    if ($with_questioncount) {
                        $title .= ' [' . $row['questioncount'] . ' ' . ($row['questioncount'] == 1 ? $lng->txt(
                            'ass_question'
                        ) : $lng->txt('assQuestions')) . ']';
                    }

                    if ($use_object_id) {
                        $result_array[$row['obj_fi']] = [
                            'qpl_id' => $row['obj_fi'],
                            'qpl_title' => $row['title'],
                            'title' => $title,
                            'count' => $row['questioncount']
                        ];
                    } else {
                        $result_array[$ref_id] = [
                            'qpl_id' => $row['obj_fi'],
                            'qpl_title' => $row['title'],
                            'title' => $title,
                            'count' => $row['questioncount']
                        ];
                    }
                }
            }
        }
        return $result_array;
    }

    public function getQplQuestions(): array
    {
        $questions = [];
        $result = $this->db->queryF(
            'SELECT qpl_questions.question_id FROM qpl_questions WHERE qpl_questions.original_id IS NULL AND qpl_questions.tstamp > 0 AND qpl_questions.obj_fi = %s',
            ['integer'],
            [$this->getId()]
        );
        while ($row = $this->db->fetchAssoc($result)) {
            array_push($questions, $row['question_id']);
        }
        return $questions;
    }

    /**
     * Creates a 1:1 copy of the object and places the copy in a given repository
     *
     * @access public
     */
    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false): ?ilObject
    {
        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($copy_id);
        if ($cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOfflineStatus(true);
        }

        $new_obj->update();

        $new_obj->setSkillServiceEnabled($this->isSkillServiceEnabled());
        $new_obj->setShowTaxonomies($this->getShowTaxonomies());
        $new_obj->saveToDb();

        // clone the questions in the question pool
        $questions = $this->getQplQuestions();
        $questionIdsMap = [];
        foreach ($questions as $question_id) {
            $newQuestionId = $new_obj->copyQuestion($question_id, $new_obj->getId());
            $questionIdsMap[$question_id] = $newQuestionId;
        }

        $md = new ilMD($this->getId(), 0, $this->getType());
        $md->cloneMD($new_obj->getId(), 0, $new_obj->getType());
        $new_obj->updateMetaData();

        $duplicator = new ilQuestionPoolTaxonomiesDuplicator();
        $duplicator->setSourceObjId($this->getId());
        $duplicator->setSourceObjType($this->getType());
        $duplicator->setTargetObjId($new_obj->getId());
        $duplicator->setTargetObjType($new_obj->getType());
        $duplicator->setQuestionIdMapping($questionIdsMap);
        $duplicator->duplicate($duplicator->getAllTaxonomiesForSourceObject());

        $new_obj->saveToDb();

        return $new_obj;
    }

    public function getQuestionTypes($all_tags = false, $fixOrder = false, $withDeprecatedTypes = true): array
    {
        return self::_getQuestionTypes($all_tags, $fixOrder, $withDeprecatedTypes);
    }

    public static function _getQuestionTypes($all_tags = false, $fixOrder = false, $withDeprecatedTypes = true): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $component_factory = $DIC['component.factory'];

        $forbidden_types = ilObjAssessmentFolder::_getForbiddenQuestionTypes();
        $lng->loadLanguageModule('assessment');
        $result = $ilDB->query('SELECT * FROM qpl_qst_type');
        $types = [];
        while ($row = $ilDB->fetchAssoc($result)) {
            if ($all_tags || (!in_array($row['question_type_id'], $forbidden_types))) {
                if ($row['plugin'] == 0) {
                    $types[$lng->txt($row['type_tag'])] = $row;
                } else {
                    foreach ($component_factory->getActivePluginsInSlot('qst') as $pl) {
                        if (strcmp($pl->getQuestionType(), $row['type_tag']) == 0) {
                            $types[$pl->getQuestionTypeTranslation()] = $row;
                        }
                    }
                }
            }
        }

        $orderMode = ($fixOrder ? ilAssQuestionTypeOrderer::ORDER_MODE_FIX : ilAssQuestionTypeOrderer::ORDER_MODE_ALPHA);
        $orderer = new ilAssQuestionTypeOrderer($types, $orderMode);
        $types = $orderer->getOrderedTypes($withDeprecatedTypes);

        return $types;
    }

    public static function getQuestionTypeByTypeId($type_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT type_tag FROM qpl_qst_type WHERE question_type_id = %s';
        $types = ['integer'];
        $values = [$type_id];
        $result = $ilDB->queryF($query, $types, $values);

        if ($row = $ilDB->fetchAssoc($result)) {
            return $row['type_tag'];
        }
        return null;
    }

    public static function getQuestionTypeTranslations(): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $ilLog = $DIC['ilLog'];
        $component_factory = $DIC['component.factory'];

        $lng->loadLanguageModule('assessment');
        $result = $ilDB->query('SELECT * FROM qpl_qst_type');
        $types = [];
        while ($row = $ilDB->fetchAssoc($result)) {
            if ($row['plugin'] == 0) {
                $types[$row['type_tag']] = $lng->txt($row['type_tag']);
            } else {
                foreach ($component_factory->getActivePluginsInSlot('qst') as $pl) {
                    if (strcmp($pl->getQuestionType(), $row['type_tag']) == 0) {
                        $types[$row['type_tag']] = $pl->getQuestionTypeTranslation();
                    }
                }
            }
        }
        ksort($types);
        return $types;
    }

    /**
     * @todo        Make it more flexible
     */
    public static function &_getSelfAssessmentQuestionTypes($all_tags = false): array
    {
        $allowed_types = [
            'assSingleChoice' => 1,
            'assMultipleChoice' => 2,
            'assKprimChoice' => 3,
            'assClozeTest' => 4,
            'assMatchingQuestion' => 5,
            'assOrderingQuestion' => 6,
            'assOrderingHorizontal' => 7,
            'assImagemapQuestion' => 8,
            'assTextSubset' => 9,
            'assErrorText' => 10,
            'assLongMenu' => 11
        ];
        $satypes = [];
        $qtypes = ilObjQuestionPool::_getQuestionTypes($all_tags);
        foreach ($qtypes as $k => $t) {
            if (isset($allowed_types[$t['type_tag']])) {
                $t['order'] = $allowed_types[$t['type_tag']];
                $satypes[$k] = $t;
            }
        }
        return $satypes;
    }

    public function getQuestionList(): array
    {
        $questions = [];
        $result = $this->db->queryF(
            'SELECT qpl_questions.*, qpl_qst_type.* FROM qpl_questions, qpl_qst_type WHERE qpl_questions.original_id IS NULL AND qpl_questions.obj_fi = %s AND qpl_questions.tstamp > 0 AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id',
            ['integer'],
            [$this->getId()]
        );
        while ($row = $this->db->fetchAssoc($result)) {
            array_push($questions, $row);
        }
        return $questions;
    }

    public static function _updateQuestionCount(int $object_id): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilDB->manipulateF(
            'UPDATE qpl_questionpool SET questioncount = %s, tstamp = %s WHERE obj_fi = %s',
            ['integer', 'integer', 'integer'],
            [ilObjQuestionPool::_getQuestionCount($object_id), time(), $object_id]
        );
    }

    /**
     * Checks wheather or not a question plugin with a given name is active
     *
     * @param string $a_pname The plugin name
     * @access public
     */
    public function isPluginActive($questionType): bool
    {
        if (!$this->component_repository->getComponentByTypeAndName(
            ilComponentInfo::TYPE_MODULES,
            'TestQuestionPool'
        )->getPluginSlotById('qst')->hasPluginName($questionType)) {
            return false;
        }

        return $this->component_repository
            ->getComponentByTypeAndName(
                ilComponentInfo::TYPE_MODULES,
                'TestQuestionPool'
            )
            ->getPluginSlotById(
                'qst'
            )
            ->getPluginByName(
                $questionType
            )->isActive();
    }

    public function purgeQuestions(): void
    {
        $incompleteQuestionPurger = new ilAssIncompleteQuestionPurger($this->db);
        $incompleteQuestionPurger->setOwnerId($this->user->getId());
        $incompleteQuestionPurger->purge();
    }

    /**
     * get ids of all taxonomies corresponding to current pool
     *
     * @return array
     */
    public function getTaxonomyIds(): array
    {
        return ilObjTaxonomy::getUsageOfObject($this->getId());
    }

    public function isSkillServiceEnabled(): bool
    {
        return $this->skill_service_enabled;
    }

    public function setSkillServiceEnabled(bool $skill_service_enabled): void
    {
        $this->skill_service_enabled = $skill_service_enabled;
    }

    private static $isSkillManagementGloballyActivated = null;

    public static function isSkillManagementGloballyActivated(): ?bool
    {
        if (self::$isSkillManagementGloballyActivated === null) {
            $skmgSet = new ilSkillManagementSettings();

            self::$isSkillManagementGloballyActivated = $skmgSet->isActivated();
        }

        return self::$isSkillManagementGloballyActivated;
    }

    public function fromXML($xmlFile): void
    {
        $parser = new ilObjQuestionPoolXMLParser($this, $xmlFile);
        $parser->startParsing();
    }
}
