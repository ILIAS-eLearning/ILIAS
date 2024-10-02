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

declare(strict_types=1);

use ILIAS\Test\InternalRequestService;
use ILIAS\Test\TestManScoringDoneHelper;
use ILIAS\Test\MainSettingsRepository;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Stream\Streams;

require_once 'Modules/Test/classes/inc.AssessmentConstants.php';

use ILIAS\Refinery\Factory as Refinery;

/**
 * Class ilObjTest
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @defgroup ModulesTest Modules/Test
 * @extends ilObject
 */
class ilObjTest extends ilObject implements ilMarkSchemaAware
{
    public const QUESTION_SET_TYPE_FIXED = 'FIXED_QUEST_SET';
    public const QUESTION_SET_TYPE_RANDOM = 'RANDOM_QUEST_SET';


    public const REDIRECT_NONE = 0;
    public const REDIRECT_ALWAYS = 1;
    public const REDIRECT_KIOSK = 2;

    private ?bool $activation_limited = null;
    private array $mob_ids;
    private array $file_ids = [];
    private bool $online;
    protected \ILIAS\TestQuestionPool\QuestionInfoService $questioninfo;
    private InternalRequestService $testrequest;
    private ASS_MarkSchema $mark_schema;
    public int $test_id = -1;
    public int $invitation = INVITATION_OFF;
    public string $author;

    /**
     * A reference to an IMS compatible matadata set
     */
    public $metadata;
    public array $questions = [];

    /**
     * Contains the evaluation data settings the tutor defines for the user
     */
    public $evaluation_data;

    /**
     * contains the test sequence data
     */
    public $test_sequence = false;

    private ?bool $has_obligations = null;
    private ?bool $current_user_all_obliations_answered = null;

    private int $template_id = 0;

    protected bool $print_best_solution_with_result = true;

    protected bool $activation_visibility = false;
    protected ?int $activation_starting_time = null;
    protected ?int $activation_ending_time = null;

    /**
     * holds the fact wether participant data exists or not
     * DO NOT USE TIS PROPERTY DRIRECTLY
     * ALWAYS USE ilObjTest::paricipantDataExist() since this method initialises this property
     */
    private $participantDataExist = null;

    protected bool $testFinalBroken = false;

    private ?int $tmpCopyWizardCopyId = null;

    private TestManScoringDoneHelper $testManScoringDoneHelper;
    protected ilCtrlInterface $ctrl;
    protected Refinery $refinery;
    protected ilSetting $settings;
    protected ilBenchmark $bench;
    protected ilTestParticipantAccessFilterFactory $participant_access_filter;
    protected ?ilObjTestMainSettings $main_settings = null;
    protected ?MainSettingsRepository $main_settings_repo = null;
    protected ?ilObjTestScoreSettings $score_settings = null;
    protected ?ScoreSettingsRepository $score_settings_repo = null;

    protected ilTestQuestionSetConfigFactory $question_set_config_factory;

    private ilComponentRepository $component_repository;
    private ilComponentFactory $component_factory;
    private Filesystem $filesystem_web;

    protected ?ilTestParticipantList $access_filtered_participant_list = null;

    /**
     * Constructor
     *
     * @param	$a_id 					int|string	Reference_id or object_id.
     * @param	$a_call_by_reference	bool		Treat the id as reference_id (true) or object_id (false).
     */
    public function __construct(int $id = 0, bool $a_call_by_reference = true)
    {
        $this->type = "tst";

        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->ctrl = $DIC['ilCtrl'];
        $this->refinery = $DIC['refinery'];
        $this->settings = $DIC['ilSetting'];
        $this->bench = $DIC['ilBench'];
        $this->testrequest = $DIC->test()->internal()->request();
        $this->component_repository = $DIC['component.repository'];
        $this->component_factory = $DIC['component.factory'];
        $this->filesystem_web = $DIC->filesystem()->web();

        $local_dic = $this->getLocalDIC();
        $this->participant_access_filter = $local_dic['participantAccessFilterFactory'];
        $this->testManScoringDoneHelper = $local_dic['manScoringDoneHelper'];

        $this->mark_schema = new ASS_MarkSchema($DIC['ilDB'], $DIC['lng'], $DIC['ilUser']->getId());
        $this->mark_schema->createSimpleSchema(
            $DIC->language()->txt("failed_short"),
            $DIC->language()->txt("failed_official"),
            0,
            0,
            $DIC->language()->txt("passed_short"),
            $DIC->language()->txt("passed_official"),
            50,
            1
        );

        parent::__construct($id, $a_call_by_reference);

        $this->lng->loadLanguageModule("assessment");
        $this->questioninfo = $DIC->testQuestionPool()->questionInfo();
        $this->score_settings = null;

        $this->question_set_config_factory = new ilTestQuestionSetConfigFactory(
            $this->tree,
            $this->db,
            $this->lng,
            $this->log,
            $this->component_repository,
            $this,
            $this->questioninfo
        );
    }

    public function getLocalDIC(): ILIAS\DI\Container
    {
        return ilTestDIC::dic();
    }

    public function getQuestionSetConfig(): ilTestQuestionSetConfig
    {
        return $this->question_set_config_factory->getQuestionSetConfig();
    }

    /**
     * returns the object title prepared to be used as a filename
     */
    public function getTitleFilenameCompliant(): string
    {
        return ilFileUtils::getASCIIFilename($this->getTitle());
    }

    public function getTmpCopyWizardCopyId(): ?int
    {
        return $this->tmpCopyWizardCopyId;
    }

    public function setTmpCopyWizardCopyId(int $tmpCopyWizardCopyId): void
    {
        $this->tmpCopyWizardCopyId = $tmpCopyWizardCopyId;
    }

    public function create(): int
    {
        $id = parent::create();
        $this->createMetaData();
        return $id;
    }

    public function update(): bool
    {
        if (!parent::update()) {
            return false;
        }

        // put here object specific stuff
        $this->updateMetaData();
        return true;
    }

    public function read(): void
    {
        parent::read();
        $this->main_settings = null;
        $this->score_settings = null;
        $this->loadFromDb();
    }

    public function delete(): bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // delet meta data
        $this->deleteMetaData();

        //put here your module specific stuff
        $this->deleteTest();

        $qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($this->getId());
        $qsaImportFails->deleteRegisteredImportFails();
        $sltImportFails = new ilTestSkillLevelThresholdImportFails($this->getId());
        $sltImportFails->deleteRegisteredImportFails();

        return true;
    }

    public function deleteTest(): void
    {
        $participantData = new ilTestParticipantData($this->db, $this->lng);
        $participantData->load($this->getTestId());
        $this->removeTestResults($participantData);

        $this->db->manipulateF(
            "DELETE FROM tst_mark WHERE test_fi = %s",
            ['integer'],
            [$this->getTestId()]
        );

        $this->db->manipulateF(
            "DELETE FROM tst_tests WHERE test_id = %s",
            ['integer'],
            [$this->getTestId()]
        );

        /**
         * 2023-08-08, sk: We check this here to allow an easy deletion of
         * Dynamic-Tests in migration. The check can go with ILIAS10
         * @todo: Remove check with ILIAS10
         */
        if ($this->isFixedTest() || $this->isRandomTest()) {
            $this->question_set_config_factory->getQuestionSetConfig()->removeQuestionSetRelatedData();
        }

        $tst_data_dir = ilFileUtils::getDataDir() . "/tst_data";
        $directory = $tst_data_dir . "/tst_" . $this->getId();
        if (is_dir($directory)) {
            ilFileUtils::delDir($directory);
        }
        $mobs = ilObjMediaObject::_getMobsOfObject("tst:html", $this->getId());
        // remaining usages are not in text anymore -> delete them
        // and media objects (note: delete method of ilObjMediaObject
        // checks whether object is used in another context; if yes,
        // the object is not deleted!)
        foreach ($mobs as $mob) {
            ilObjMediaObject::_removeUsage($mob, "tst:html", $this->getId());
            if (ilObjMediaObject::_exists($mob)) {
                $mob_obj = new ilObjMediaObject($mob);
                $mob_obj->delete();
            }
        }
    }

    /**
    * creates data directory for export files
    * (data_dir/tst_data/tst_<id>/export, depending on data
    * directory that is set in ILIAS setup/ini)
    */
    public function createExportDirectory(): void
    {
        $tst_data_dir = ilFileUtils::getDataDir() . "/tst_data";
        ilFileUtils::makeDir($tst_data_dir);
        if (!is_writable($tst_data_dir)) {
            $this->ilias->raiseError("Test Data Directory (" . $tst_data_dir
                . ") not writeable.", $this->ilias->error_obj->MESSAGE);
        }

        // create learning module directory (data_dir/lm_data/lm_<id>)
        $tst_dir = $tst_data_dir . "/tst_" . $this->getId();
        ilFileUtils::makeDir($tst_dir);
        if (!@is_dir($tst_dir)) {
            $this->ilias->raiseError("Creation of Test Directory failed.", $this->ilias->error_obj->MESSAGE);
        }
        // create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
        $export_dir = $tst_dir . "/export";
        ilFileUtils::makeDir($export_dir);
        if (!@is_dir($export_dir)) {
            $this->ilias->raiseError("Creation of Export Directory failed.", $this->ilias->error_obj->MESSAGE);
        }
    }

    public function getExportDirectory(): string
    {
        $export_dir = ilFileUtils::getDataDir() . "/tst_data" . "/tst_" . $this->getId() . "/export";
        return $export_dir;
    }

    public function getExportFiles(string $dir = ''): array
    {
        // quit if import dir not available
        if (!@is_dir($dir) || !is_writable($dir)) {
            return [];
        }

        $files = [];
        foreach (new DirectoryIterator($dir) as $file) {
            /**
             * @var $file SplFileInfo
             */
            if ($file->isDir()) {
                continue;
            }

            $files[] = $file->getBasename();
        }

        sort($files);

        return $files;
    }

    public static function _setImportDirectory($a_import_dir = null): void
    {
        if ($a_import_dir !== null) {
            ilSession::set('tst_import_dir', $a_import_dir);
            return;
        }

        ilSession::clear('tst_import_dir');
    }

    /**
    * Get the import directory location of the test
    *
    * @return mixed|null The location of the import directory or false if the directory doesn't exist
    */
    public static function _getImportDirectory()
    {
        if (strlen(ilSession::get('tst_import_dir'))) {
            return ilSession::get('tst_import_dir');
        }
        return null;
    }

    /** @return mixed|null */
    public function getImportDirectory()
    {
        return ilObjTest::_getImportDirectory();
    }

    /**
    * creates data directory for import files
    * (data_dir/tst_data/tst_<id>/import, depending on data
    * directory that is set in ILIAS setup/ini)
    */
    public static function _createImportDirectory(): string
    {
        global $DIC;
        $ilias = $DIC['ilias'];
        $tst_data_dir = ilFileUtils::getDataDir() . "/tst_data";
        ilFileUtils::makeDir($tst_data_dir);

        if (!is_writable($tst_data_dir)) {
            $ilias->raiseError("Test Data Directory (" . $tst_data_dir
                . ") not writeable.", $ilias->error_obj->FATAL);
        }

        // create test directory (data_dir/tst_data/tst_import)
        $tst_dir = $tst_data_dir . "/tst_import";
        ilFileUtils::makeDir($tst_dir);
        if (!@is_dir($tst_dir)) {
            $ilias->raiseError("Creation of test import directory failed.", $ilias->error_obj->FATAL);
        }

        // assert that this is empty and does not contain old data
        ilFileUtils::delDir($tst_dir, true);

        return $tst_dir;
    }

    final public function isComplete(ilTestQuestionSetConfig $testQuestionSetConfig): bool
    {
        if (!count($this->mark_schema->mark_steps)) {
            return false;
        }

        if (!$testQuestionSetConfig->isQuestionSetConfigured()) {
            return false;
        }

        return true;
    }

    public function saveCompleteStatus(ilTestQuestionSetConfig $testQuestionSetConfig): void
    {
        $complete = 0;
        if ($this->isComplete($testQuestionSetConfig)) {
            $complete = 1;
        }
        if ($this->getTestId() > 0) {
            $this->db->manipulateF(
                "UPDATE tst_tests SET complete = %s WHERE test_id = %s",
                ['text', 'integer'],
                [$complete, $this->test_id]
            );
        }
    }

    public function saveToDb(bool $properties_only = false): void
    {
        if ($this->test_id === -1) {
            // Create new dataset
            $next_id = $this->db->nextId('tst_tests');

            $this->db->insert(
                'tst_tests',
                [
                    'test_id' => ['integer', $next_id],
                    'obj_fi' => ['integer', $this->getId()],
                    'author' => ['text', $this->getAuthor()],
                    'created' => ['integer', time()],
                    'tstamp' => ['integer', time()],
                    'template_id' => ['integer', $this->getTemplate()],
                    'broken' => ['integer', (int) $this->isTestFinalBroken()]
                ]
            );

            $this->test_id = $next_id;

            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                $this->logAction($this->lng->txtlng("assessment", "log_create_new_test", ilObjAssessmentFolder::_getLogLanguage()));
            }
        } else {
            // Modify existing dataset
            $oldrow = [];
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                $result = $this->db->queryF(
                    "SELECT * FROM tst_tests WHERE test_id = %s",
                    ['integer'],
                    [$this->test_id]
                );
                if ($result->numRows() == 1) {
                    $oldrow = $this->db->fetchAssoc($result);
                }
            }

            $this->db->update(
                'tst_tests',
                [
                    'author' => ['text', $this->getAuthor()],
                    'broken' => ['integer', (int) $this->isTestFinalBroken()]
                ],
                [
                    'test_id' => ['integer', $this->getTestId()]
                ]
            );

            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                $logresult = $this->db->queryF(
                    "SELECT * FROM tst_tests WHERE test_id = %s",
                    ['integer'],
                    [$this->getTestId()]
                );
                $newrow = [];
                if ($logresult->numRows() == 1) {
                    $newrow = $this->db->fetchAssoc($logresult);
                }
                $changed_fields = [];
                foreach ($oldrow as $key => $value) {
                    if ($oldrow[$key] !== $newrow[$key]) {
                        array_push($changed_fields, "$key: " . $oldrow[$key] . " => " . $newrow[$key]);
                    }
                }
                $changes = join(", ", $changed_fields);
                if (count($changed_fields) > 0) {
                    $this->logAction($this->lng->txtlng("assessment", "log_modified_test", ilObjAssessmentFolder::_getLogLanguage()) . " [" . $changes . "]");
                }
            }
            if ($this->evalTotalPersons() > 0) {
                // reset the finished status of participants if the nr of test passes did change
                if ($this->getNrOfTries() > 0) {
                    // set all unfinished tests with nr of passes >= allowed passes finished
                    $aresult = $this->db->queryF(
                        "SELECT active_id FROM tst_active WHERE test_fi = %s AND tries >= %s AND submitted = %s",
                        ['integer', 'integer', 'integer'],
                        [$this->getTestId(), $this->getNrOfTries(), 0]
                    );
                    while ($row = $this->db->fetchAssoc($aresult)) {
                        $this->db->manipulateF(
                            "UPDATE tst_active SET submitted = %s, submittimestamp = %s WHERE active_id = %s",
                            ['integer', 'timestamp', 'integer'],
                            [1, date('Y-m-d H:i:s'), $row["active_id"]]
                        );
                    }

                    // set all finished tests with nr of passes < allowed passes not finished
                    $aresult = $this->db->queryF(
                        "SELECT active_id FROM tst_active WHERE test_fi = %s AND tries < %s AND submitted = %s",
                        ['integer', 'integer', 'integer'],
                        [$this->getTestId(), $this->getNrOfTries() - 1, 1]
                    );
                    while ($row = $this->db->fetchAssoc($aresult)) {
                        $this->db->manipulateF(
                            "UPDATE tst_active SET submitted = %s, submittimestamp = %s WHERE active_id = %s",
                            ['integer', 'timestamp', 'integer'],
                            [0, null, $row["active_id"]]
                        );
                    }
                } else {
                    // set all finished tests with nr of passes >= allowed passes not finished
                    $aresult = $this->db->queryF(
                        "SELECT active_id FROM tst_active WHERE test_fi = %s AND submitted = %s",
                        ['integer', 'integer'],
                        [$this->getTestId(), 1]
                    );
                    while ($row = $this->db->fetchAssoc($aresult)) {
                        $this->db->manipulateF(
                            "UPDATE tst_active SET submitted = %s, submittimestamp = %s WHERE active_id = %s",
                            ['integer', 'timestamp', 'integer'],
                            [0, null, $row["active_id"]]
                        );
                    }
                }
            }
        }

        $this->storeActivationSettings([
            'is_activation_limited' => $this->isActivationLimited(),
            'activation_starting_time' => $this->getActivationStartingTime(),
            'activation_ending_time' => $this->getActivationEndingTime(),
            'activation_visibility' => $this->getActivationVisibility()
        ]);

        if (!$properties_only) {
            if ($this->getQuestionSetType() == self::QUESTION_SET_TYPE_FIXED) {
                $this->saveQuestionsToDb();
            }

            $this->mark_schema->saveToDb($this->test_id);
        }
    }

    public function saveQuestionsToDb(): void
    {
        $oldquestions = [];
        if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            $result = $this->db->queryF(
                "SELECT question_fi FROM tst_test_question WHERE test_fi = %s ORDER BY sequence",
                ['integer'],
                [$this->getTestId()]
            );
            if ($result->numRows() > 0) {
                while ($row = $this->db->fetchAssoc($result)) {
                    array_push($oldquestions, $row["question_fi"]);
                }
            }
        }
        // workaround for lost obligations
        // this method is called if a question is removed
        $currentQuestionsObligationsQuery = 'SELECT question_fi, obligatory FROM tst_test_question WHERE test_fi = %s';
        $rset = $this->db->queryF($currentQuestionsObligationsQuery, ['integer'], [$this->getTestId()]);
        while ($row = $this->db->fetchAssoc($rset)) {
            $obligatoryQuestionState[$row['question_fi']] = $row['obligatory'];
        }
        // delete existing category relations
        $this->db->manipulateF(
            "DELETE FROM tst_test_question WHERE test_fi = %s",
            ['integer'],
            [$this->getTestId()]
        );
        // create new category relations
        foreach ($this->questions as $key => $value) {
            // workaround for import witout obligations information
            if (!isset($obligatoryQuestionState[$value]) || is_null($obligatoryQuestionState[$value])) {
                $obligatoryQuestionState[$value] = 0;
            }

            // insert question
            $next_id = $this->db->nextId('tst_test_question');
            $this->db->insert('tst_test_question', [
                'test_question_id' => ['integer', $next_id],
                'test_fi' => ['integer', $this->getTestId()],
                'question_fi' => ['integer', $value],
                'sequence' => ['integer', $key],
                'obligatory' => ['integer', $obligatoryQuestionState[$value]],
                'tstamp' => ['integer', time()]
            ]);
        }
        if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            $result = $this->db->queryF(
                "SELECT question_fi FROM tst_test_question WHERE test_fi = %s ORDER BY sequence",
                ['integer'],
                [$this->getTestId()]
            );
            $newquestions = [];
            if ($result->numRows() > 0) {
                while ($row = $this->db->fetchAssoc($result)) {
                    array_push($newquestions, $row["question_fi"]);
                }
            }
            foreach ($oldquestions as $index => $question_id) {
                if (!isset($newquestions[$index]) || $newquestions[$index] !== $question_id) {
                    $pos = array_search($question_id, $newquestions);
                    if ($pos === false) {
                        $this->logAction($this->lng->txtlng("assessment", "log_question_removed", ilObjAssessmentFolder::_getLogLanguage()), $question_id);
                    } else {
                        $this->logAction($this->lng->txtlng("assessment", "log_question_position_changed", ilObjAssessmentFolder::_getLogLanguage()) . ": " . ($index + 1) . " => " . ($pos + 1), $question_id);
                    }
                }
            }
            foreach ($newquestions as $index => $question_id) {
                if (array_search($question_id, $oldquestions) === false) {
                    $this->logAction($this->lng->txtlng("assessment", "log_question_added", ilObjAssessmentFolder::_getLogLanguage()) . ": " . ($index + 1), $question_id);
                }
            }
        }
    }

    /**
     * Calculates the number of user results for a specific test pass
     */
    public function getNrOfResultsForPass($active_id, $pass): int
    {
        $result = $this->db->queryF(
            "SELECT test_result_id FROM tst_test_result WHERE active_fi = %s AND pass = %s",
            ['integer','integer'],
            [$active_id, $pass]
        );
        return $result->numRows();
    }

    public function loadFromDb(): void
    {
        $result = $this->db->queryF(
            "SELECT test_id FROM tst_tests WHERE obj_fi = %s",
            ['integer'],
            [$this->getId()]
        );
        if ($result->numRows() === 1) {
            $data = $this->db->fetchObject($result);
            $this->setTestId($data->test_id);

            $this->mark_schema->flush();
            $this->mark_schema->loadFromDb($this->getTestId());

            $this->loadQuestions();
        }

        // moved activation to ilObjectActivation
        if (isset($this->ref_id)) {
            $activation = ilObjectActivation::getItem($this->ref_id);
            switch ($activation["timing_type"]) {
                case ilObjectActivation::TIMINGS_ACTIVATION:
                    $this->setActivationLimited(true);
                    $this->setActivationStartingTime($activation["timing_start"]);
                    $this->setActivationEndingTime($activation["timing_end"]);
                    $this->setActivationVisibility($activation["visible"]);
                    break;

                default:
                    $this->setActivationLimited(false);
                    break;
            }
        }
    }

    /**
    * Load the test question id's from the database
    *
    */
    public function loadQuestions(int $active_id = 0, ?int $pass = null): void
    {
        $this->questions = [];
        if ($this->isRandomTest()) {
            if ($active_id === 0) {
                $active_id = $this->getActiveIdOfUser($this->user->getId());
            }
            if (is_null($pass)) {
                $pass = self::_getPass($active_id);
            }
            $result = $this->db->queryF(
                "SELECT tst_test_rnd_qst.* FROM tst_test_rnd_qst, qpl_questions WHERE tst_test_rnd_qst.active_fi = %s AND qpl_questions.question_id = tst_test_rnd_qst.question_fi AND tst_test_rnd_qst.pass = %s ORDER BY sequence",
                ['integer', 'integer'],
                [$active_id, $pass]
            );
            // The following is a fix for random tests prior to ILIAS 3.8. If someone started a random test in ILIAS < 3.8, there
            // is only one test pass (pass = 0) in tst_test_rnd_qst while with ILIAS 3.8 there are questions for every test pass.
            // To prevent problems with tests started in an older version and continued in ILIAS 3.8, the first pass should be taken if
            // no questions are present for a newer pass.
            if ($result->numRows() == 0) {
                $result = $this->db->queryF(
                    "SELECT tst_test_rnd_qst.* FROM tst_test_rnd_qst, qpl_questions WHERE tst_test_rnd_qst.active_fi = %s AND qpl_questions.question_id = tst_test_rnd_qst.question_fi AND tst_test_rnd_qst.pass = 0 ORDER BY sequence",
                    ['integer'],
                    [$active_id]
                );
            }
        } else {
            $result = $this->db->queryF(
                "SELECT tst_test_question.* FROM tst_test_question, qpl_questions WHERE tst_test_question.test_fi = %s AND qpl_questions.question_id = tst_test_question.question_fi ORDER BY sequence",
                ['integer'],
                [$this->test_id]
            );
        }
        $index = 1;
        if ($this->test_id !== -1) {
            //Omit loading of questions for non-id'ed test
            while ($data = $this->db->fetchAssoc($result)) {
                $this->questions[$index++] = $data["question_fi"];
            }
        }
    }

    public function getIntroduction(): string
    {
        $page_id = $this->getMainSettings()->getIntroductionSettings()->getIntroductionPageId();
        if ($page_id !== null) {
            return (new ilTestPageGUI('tst', $page_id))->showPage();
        }

        return $this->getMainSettings()->getIntroductionSettings()->getIntroductionText();
    }

    public function getFinalStatement(): string
    {
        $page_id = $this->getMainSettings()->getFinishingSettings()->getConcludingRemarksPageId();
        if ($page_id !== null) {
            return (new ilTestPageGUI('tst', $page_id))->showPage();
        }

        return $this->getMainSettings()->getFinishingSettings()->getConcludingRemarksText();
    }

    /**
    * Gets the database id of the additional test data
    *
    * @return integer The database id of the additional test data
    * @access public
    * @see $test_id
    */
    public function getTestId(): int
    {
        return $this->test_id;
    }

    public function isPostponingEnabled(): bool
    {
        return $this->getMainSettings()->getParticipantFunctionalitySettings()->getPostponedQuestionsMoveToEnd();
    }

    /**
    * Gets the score reporting of the ilObjTest object
    *
    * @return integer The score reporting of the test
    * @access public
    * @see $score_reporting
    */
    public function getScoreReporting(): int
    {
        return $this->getScoreSettings()->getResultSummarySettings()->getScoreReporting();
    }

    public function isScoreReportingEnabled(): bool
    {
        switch ($this->getScoreSettings()->getResultSummarySettings()->getScoreReporting()) {
            case ilObjTestSettingsResultSummary::SCORE_REPORTING_FINISHED:
            case ilObjTestSettingsResultSummary::SCORE_REPORTING_IMMIDIATLY:
            case ilObjTestSettingsResultSummary::SCORE_REPORTING_DATE:
            case ilObjTestSettingsResultSummary::SCORE_REPORTING_AFTER_PASSED:

                return true;

            case ilObjTestSettingsResultSummary::SCORE_REPORTING_DISABLED:
            default:

                return false;
        }
    }

    public function getAnswerFeedbackPoints(): bool
    {
        return $this->getMainSettings()->getQuestionBehaviourSettings()->getInstantFeedbackPointsEnabled();
    }

    public function getGenericAnswerFeedback(): bool
    {
        return $this->getMainSettings()->getQuestionBehaviourSettings()->getInstantFeedbackGenericEnabled();
    }

    public function getInstantFeedbackSolution(): bool
    {
        return $this->getMainSettings()->getQuestionBehaviourSettings()->getInstantFeedbackSolutionEnabled();
    }

    public function getCountSystem(): int
    {
        return $this->getScoreSettings()->getScoringSettings()->getCountSystem();
    }

    public static function _getCountSystem($active_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT tst_tests.count_system FROM tst_tests, tst_active WHERE tst_active.active_id = %s AND tst_active.test_fi = tst_tests.test_id",
            ['integer'],
            [$active_id]
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            return $row["count_system"];
        }
        return false;
    }

    /**
    * Determines if the score of a question should be cut at 0 points or the score of the whole test
    *
    * @return integer The score cutting type. 0 for question cutting, 1 for test cutting
    * @access public
    * @see $score_cutting
    */
    public function getScoreCutting(): int
    {
        return $this->getScoreSettings()->getScoringSettings()->getScoreCutting();
    }

    /**
    * Gets the pass scoring type
    *
    * @return integer The pass scoring type
    * @access public
    * @see $pass_scoring
    */
    public function getPassScoring(): int
    {
        return $this->getScoreSettings()->getScoringSettings()->getPassScoring();
    }

    /**
    * Gets the pass scoring type
    *
    * @return integer The pass scoring type
    * @access public
    * @see $pass_scoring
    */
    public static function _getPassScoring($active_id): int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT tst_tests.pass_scoring FROM tst_tests, tst_active WHERE tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s",
            ['integer'],
            [$active_id]
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            return (int) $row["pass_scoring"];
        }
        return 0;
    }

    /**
    * Determines if the score of a question should be cut at 0 points or the score of the whole test
    *
    * @return boolean The score cutting type. 0 for question cutting, 1 for test cutting
    * @access public
    * @see $score_cutting
    */
    public static function _getScoreCutting($active_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT tst_tests.score_cutting FROM tst_tests, tst_active WHERE tst_active.active_id = %s AND tst_tests.test_id = tst_active.test_fi",
            ['integer'],
            [$active_id]
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            return (bool) $row["score_cutting"];
        }
        return false;
    }

    /**
    * Gets the reporting date of the ilObjTest object
    *
    * @return string The reporting date of the test of an empty string (=FALSE) if no reporting date is set
    * @access public
    * @see $reporting_date
    */
    public function getReportingDate(): ?string
    {
        return $this->getScoreSettings()->getResultSummarySettings()->getReportingDate()?->format('YmdHis');
    }

    public function getNrOfTries(): int
    {
        return $this->getMainSettings()->getTestBehaviourSettings()->getNumberOfTries();
    }

    public function isBlockPassesAfterPassedEnabled(): bool
    {
        return $this->getMainSettings()->getTestBehaviourSettings()->getBlockAfterPassedEnabled();
    }

    public function getKioskMode(): bool
    {
        return $this->getMainSettings()->getTestBehaviourSettings()->getKioskModeEnabled();
    }

    public function getShowKioskModeTitle(): bool
    {
        return $this->getMainSettings()->getTestBehaviourSettings()->getShowTitleInKioskMode();
    }
    public function getShowKioskModeParticipant(): bool
    {
        return $this->getMainSettings()->getTestBehaviourSettings()->getShowParticipantNameInKioskMode();
    }

    public function getUsePreviousAnswers(): bool
    {
        return $this->getMainSettings()->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed();
    }

    public function getTitleOutput(): int
    {
        return $this->getMainSettings()->getQuestionBehaviourSettings()->getQuestionTitleOutputMode();
    }

    public function isPreviousSolutionReuseEnabled($active_id): bool
    {
        $result = $this->db->queryF(
            "SELECT tst_tests.use_previous_answers FROM tst_tests, tst_active WHERE tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s",
            ["integer"],
            [$active_id]
        );
        if ($result->numRows()) {
            $row = $this->db->fetchAssoc($result);
            $test_allows_reuse = $row["use_previous_answers"];
        }

        if ($test_allows_reuse === '1') {
            $res = $this->user->getPref("tst_use_previous_answers");
            if ($res === '1') {
                return true;
            }
        }
        return false;
    }

    /**

    * @return string The processing time for the test in some weired format (needs checking)
    */
    public function getProcessingTime(): ?string
    {
        return $this->getMainSettings()->getTestBehaviourSettings()->getProcessingTime();
    }

    /**
    * @see $processing_time
    */
    public function getProcessingTimeAsArray(): array
    {
        $processing_time = $this->getMainSettings()->getTestBehaviourSettings()->getProcessingTime();
        if ($processing_time && $processing_time !== '') {
            if (preg_match("/(\d{2}):(\d{2}):(\d{2})/is", (string) $processing_time, $matches)) {
                return [
                    'hh' => $matches[1],
                    'mm' => $matches[2],
                    'ss' => $matches[3],
                ];
            }
        }
    }

    public function getProcessingTimeAsMinutes()
    {
        if ($this->processing_time !== null) {
            if (preg_match("/(\d{2}):(\d{2}):(\d{2})/is", (string) $this->processing_time, $matches)) {
                return ($matches[1] * 60) + $matches[2];
            }
        }

        return self::DEFAULT_PROCESSING_TIME_MINUTES;
    }

    /**
    * Returns the processing time for the test in seconds
    *
    * @return integer The processing time for the test in seconds
    * @access public
    * @see $processing_time
    */
    public function getProcessingTimeInSeconds($active_id = ""): int
    {
        $processing_time = $this->getMainSettings()->getTestBehaviourSettings()->getProcessingTime() ?? '';
        if (preg_match("/(\d{2}):(\d{2}):(\d{2})/", (string) $processing_time, $matches)) {
            $extratime = $this->getExtraTime($active_id) * 60;
            return ($matches[1] * 3600) + ($matches[2] * 60) + $matches[3] + $extratime;
        } else {
            return 0;
        }
    }

    public function getEnableProcessingTime(): bool
    {
        return $this->getMainSettings()->getTestBehaviourSettings()->getProcessingTimeEnabled();
    }

    public function getResetProcessingTime(): bool
    {
        return $this->getMainSettings()->getTestBehaviourSettings()->getResetProcessingTime();
    }

    public function isStartingTimeEnabled(): bool
    {
        return $this->getMainSettings()->getAccessSettings()->getStartTimeEnabled();
    }

    public function getStartingTime(): int
    {
        $start_time = $this->getMainSettings()->getAccessSettings()->getStartTime();
        return $start_time !== null ? $start_time->getTimestamp() : 0;
    }

    public function isEndingTimeEnabled(): bool
    {
        return $this->getMainSettings()->getAccessSettings()->getEndTimeEnabled();
    }

    public function getEndingTime(): int
    {
        $end_time = $this->getMainSettings()->getAccessSettings()->getEndTime();
        return $end_time !== null ? $end_time->getTimestamp() : 0;
    }

    public function getRedirectionMode(): int
    {
        return $this->getMainSettings()->getFinishingSettings()->getRedirectionMode();
    }

    public function isRedirectModeKiosk(): bool
    {
        return $this->getMainSettings()->getFinishingSettings()->getRedirectionMode() === self::REDIRECT_KIOSK;
    }

    public function isRedirectModeNone(): bool
    {
        return $this->getMainSettings()->getFinishingSettings()->getRedirectionMode() === self::REDIRECT_NONE;
    }

    public function getRedirectionUrl(): string
    {
        return $this->getMainSettings()->getFinishingSettings()->getRedirectionUrl() ?? '';
    }

    public function isPasswordEnabled(): bool
    {
        return $this->getMainSettings()->getAccessSettings()->getPasswordEnabled();
    }

    public function getPassword(): ?string
    {
        return $this->getMainSettings()->getAccessSettings()->getPassword();
    }

    /**
     * @param int $questionId
     * @param array $activeIds
     * @param ilTestReindexedSequencePositionMap $reindexedSequencePositionMap
     */
    public function removeQuestionFromSequences($questionId, $activeIds, ilTestReindexedSequencePositionMap $reindexedSequencePositionMap): void
    {
        $test_sequence_factory = new ilTestSequenceFactory(
            $this,
            $this->db,
            $this->questioninfo
        );

        foreach ($activeIds as $activeId) {
            $passSelector = new ilTestPassesSelector($this->db, $this);
            $passSelector->setActiveId($activeId);

            foreach ($passSelector->getExistingPasses() as $pass) {
                $test_sequence = $test_sequence_factory->getSequenceByActiveIdAndPass($activeId, $pass);
                $test_sequence->loadFromDb();

                $test_sequence->removeQuestion($questionId, $reindexedSequencePositionMap);
                $test_sequence->saveToDb();
            }
        }
    }

    /**
     * @param int[] $removeQuestionIds
     */
    public function removeQuestions(array $removeQuestionIds): void
    {
        foreach ($removeQuestionIds as $value) {
            $this->removeQuestion((int) $value);
        }

        $this->reindexFixedQuestionOrdering();
    }

    public function removeQuestion(int $question_id): void
    {
        try {
            $question = self::_instanciateQuestion($question_id);
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                $this->logAction(
                    $this->lng->txtlng("assessment", "log_question_removed", ilObjAssessmentFolder::_getLogLanguage()),
                    $question_id
                );
            }
            $question->delete($question_id);
        } catch (InvalidArgumentException $e) {
            $this->log->error($e->getMessage());
            $this->log->error($e->getTraceAsString());
        }
    }

    /**
     * - at the time beeing ilObjTest::removeTestResults needs to call the LP service for deletion
     * - ilTestLP calls ilObjTest::removeTestResultsByUserIds
     *
     * this method should only be used from non refactored soap context i think
     *
     * @param $userIds
     */
    public function removeTestResultsFromSoapLpAdministration($userIds)
    {
        $this->removeTestResultsByUserIds($userIds);

        $participantData = new ilTestParticipantData($this->db, $this->lng);
        $participantData->setUserIdsFilter($userIds);
        $participantData->load($this->getTestId());

        $this->removeTestActives($participantData->getActiveIds());
    }

    public function removeTestResults(ilTestParticipantData $participantData)
    {
        if (count($participantData->getAnonymousActiveIds())) {
            $this->removeTestResultsByActiveIds($participantData->getAnonymousActiveIds());
        }

        if (count($participantData->getUserIds())) {
            /* @var ilTestLP $testLP */
            $testLP = ilObjectLP::getInstance($this->getId());
            $testLP->setTestObject($this);
            $testLP->resetLPDataForUserIds($participantData->getUserIds(), false);
        }

        if (count($participantData->getActiveIds())) {
            $this->removeTestActives($participantData->getActiveIds());
        }
    }

    public function removeTestResultsByUserIds($userIds)
    {
        $participantData = new ilTestParticipantData($this->db, $this->lng);
        $participantData->setUserIdsFilter($userIds);
        $participantData->load($this->getTestId());

        $IN_userIds = $this->db->in('usr_id', $participantData->getUserIds(), false, 'integer');
        $this->db->manipulateF(
            "DELETE FROM usr_pref WHERE $IN_userIds AND keyword = %s",
            ['text'],
            ["tst_password_" . $this->getTestId()]
        );

        if (count($participantData->getActiveIds())) {
            $this->removeTestResultsByActiveIds($participantData->getActiveIds());
        }
    }

    public function removeTestResultsByActiveIds($activeIds)
    {
        $IN_activeIds = $this->db->in('active_fi', $activeIds, false, 'integer');

        $this->db->manipulate("DELETE FROM tst_solutions WHERE $IN_activeIds");
        $this->db->manipulate("DELETE FROM tst_qst_solved WHERE $IN_activeIds");
        $this->db->manipulate("DELETE FROM tst_test_result WHERE $IN_activeIds");
        $this->db->manipulate("DELETE FROM tst_pass_result WHERE $IN_activeIds");
        $this->db->manipulate("DELETE FROM tst_result_cache WHERE $IN_activeIds");
        $this->db->manipulate("DELETE FROM tst_sequence WHERE $IN_activeIds");
        $this->db->manipulate("DELETE FROM tst_times WHERE $IN_activeIds");
        $this->db->manipulate('DELETE FROM ' . PassPresentedVariablesRepo::TABLE_NAME . ' WHERE ' . $this->db->in('active_id', $activeIds, false, 'integer'));

        if ($this->isRandomTest()) {
            $this->db->manipulate("DELETE FROM tst_test_rnd_qst WHERE $IN_activeIds");
        }

        foreach ($activeIds as $active_id) {
            // remove file uploads
            if (@is_dir(CLIENT_WEB_DIR . "/assessment/tst_" . $this->getTestId() . "/$active_id")) {
                ilFileUtils::delDir(CLIENT_WEB_DIR . "/assessment/tst_" . $this->getTestId() . "/$active_id");
            }

            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                $this->logAction(sprintf($this->lng->txtlng("assessment", "log_selected_user_data_removed", ilObjAssessmentFolder::_getLogLanguage()), $this->userLookupFullName($this->_getUserIdFromActiveId($active_id))));
            }
        }

        ilAssQuestionHintTracking::deleteRequestsByActiveIds($activeIds);
    }

    public function removeTestActives($activeIds)
    {
        $IN_activeIds = $this->db->in('active_id', $activeIds, false, 'integer');
        $this->db->manipulate("DELETE FROM tst_active WHERE $IN_activeIds");
    }

    /**
    * Moves a question up in order
    *
    * @param integer $question_id The database id of the question to be moved up
    * @access public
    * @see $test_id
    */
    public function questionMoveUp($question_id)
    {
        // Move a question up in sequence
        $result = $this->db->queryF(
            "SELECT * FROM tst_test_question WHERE test_fi=%s AND question_fi=%s",
            ['integer', 'integer'],
            [$this->getTestId(), $question_id]
        );
        $data = $this->db->fetchObject($result);
        if ($data->sequence > 1) {
            // OK, it's not the top question, so move it up
            $result = $this->db->queryF(
                "SELECT * FROM tst_test_question WHERE test_fi=%s AND sequence=%s",
                ['integer','integer'],
                [$this->getTestId(), $data->sequence - 1]
            );
            $data_previous = $this->db->fetchObject($result);
            // change previous dataset
            $this->db->manipulateF(
                "UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
                ['integer','integer'],
                [$data->sequence, $data_previous->test_question_id]
            );
            // move actual dataset up
            $this->db->manipulateF(
                "UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
                ['integer','integer'],
                [$data->sequence - 1, $data->test_question_id]
            );
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                $this->logAction($this->lng->txtlng("assessment", "log_question_position_changed", ilObjAssessmentFolder::_getLogLanguage()) . ": " . ($data->sequence) . " => " . ($data->sequence - 1), $question_id);
            }
        }
        $this->loadQuestions();
    }

    /**
    * Moves a question down in order
    *
    * @param integer $question_id The database id of the question to be moved down
    * @access public
    * @see $test_id
    */
    public function questionMoveDown($question_id)
    {
        // Move a question down in sequence
        $result = $this->db->queryF(
            "SELECT * FROM tst_test_question WHERE test_fi=%s AND question_fi=%s",
            ['integer','integer'],
            [$this->getTestId(), $question_id]
        );
        $data = $this->db->fetchObject($result);
        $result = $this->db->queryF(
            "SELECT * FROM tst_test_question WHERE test_fi=%s AND sequence=%s",
            ['integer','integer'],
            [$this->getTestId(), $data->sequence + 1]
        );
        if ($result->numRows() == 1) {
            // OK, it's not the last question, so move it down
            $data_next = $this->db->fetchObject($result);
            // change next dataset
            $this->db->manipulateF(
                "UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
                ['integer','integer'],
                [$data->sequence, $data_next->test_question_id]
            );
            // move actual dataset down
            $this->db->manipulateF(
                "UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
                ['integer','integer'],
                [$data->sequence + 1, $data->test_question_id]
            );
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                $this->logAction($this->lng->txtlng("assessment", "log_question_position_changed", ilObjAssessmentFolder::_getLogLanguage()) . ": " . ($data->sequence) . " => " . ($data->sequence + 1), $question_id);
            }
        }
        $this->loadQuestions();
    }

    /**
    * Takes a question and creates a copy of the question for use in the test
    *
    * @param integer $question_id The database id of the question
    * @result integer The database id of the copied question
    */
    public function duplicateQuestionForTest($question_id): int
    {
        $question = ilObjTest::_instanciateQuestion($question_id);
        $duplicate_id = $question->duplicate(true, '', '', -1, $this->getId());
        return $duplicate_id;
    }

    /**
     * Insert a question in the list of questions
     *
     * @param ilTestQuestionSetConfig $testQuestionSetConfig
     * @param integer $question_id The database id of the inserted question
     * @param boolean $linkOnly
     * @return integer $duplicate_id
     */
    public function insertQuestion(ilTestQuestionSetConfig $testQuestionSetConfig, $question_id, $linkOnly = false): int
    {
        if ($linkOnly) {
            $duplicate_id = $question_id;
        } else {
            $duplicate_id = $this->duplicateQuestionForTest($question_id);
        }

        // get maximum sequence index in test
        $result = $this->db->queryF(
            "SELECT MAX(sequence) seq FROM tst_test_question WHERE test_fi=%s",
            ['integer'],
            [$this->getTestId()]
        );
        $sequence = 1;

        if ($result->numRows() == 1) {
            $data = $this->db->fetchObject($result);
            $sequence = $data->seq + 1;
        }

        $next_id = $this->db->nextId('tst_test_question');
        $affectedRows = $this->db->manipulateF(
            "INSERT INTO tst_test_question (test_question_id, test_fi, question_fi, sequence, tstamp) VALUES (%s, %s, %s, %s, %s)",
            ['integer', 'integer','integer','integer','integer'],
            [$next_id, $this->getTestId(), $duplicate_id, $sequence, time()]
        );
        if ($affectedRows == 1) {
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                $this->logAction($this->lng->txtlng("assessment", "log_question_added", ilObjAssessmentFolder::_getLogLanguage()) . ": " . $sequence, $duplicate_id);
            }
        }
        // remove test_active entries, because test has changed
        $affectedRows = $this->db->manipulateF(
            "DELETE FROM tst_active WHERE test_fi = %s",
            ['integer'],
            [$this->getTestId()]
        );
        $this->loadQuestions();
        $this->saveCompleteStatus($testQuestionSetConfig);
        return $duplicate_id;
    }

    /**
    * Returns the titles of the test questions in question sequence
    *
    * @return array The question titles
    * @access public
    * @see $questions
    */
    public function &getQuestionTitles(): array
    {
        $titles = [];
        if ($this->getQuestionSetType() == self::QUESTION_SET_TYPE_FIXED) {
            $result = $this->db->queryF(
                "SELECT qpl_questions.title FROM tst_test_question, qpl_questions WHERE tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id ORDER BY tst_test_question.sequence",
                ['integer'],
                [$this->getTestId()]
            );
            while ($row = $this->db->fetchAssoc($result)) {
                array_push($titles, $row["title"]);
            }
        }
        return $titles;
    }

    /**
    * Returns the titles of the test questions in question sequence
    *
    * @return array The question titles
    * @access public
    * @see $questions
    */
    public function &getQuestionTitlesAndIndexes(): array
    {
        $titles = [];
        if ($this->getQuestionSetType() == self::QUESTION_SET_TYPE_FIXED) {
            $result = $this->db->queryF(
                "SELECT qpl_questions.title, qpl_questions.question_id FROM tst_test_question, qpl_questions WHERE tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id ORDER BY tst_test_question.sequence",
                ['integer'],
                [$this->getTestId()]
            );
            while ($row = $this->db->fetchAssoc($result)) {
                $titles[$row['question_id']] = $row["title"];
            }
        }
        return $titles;
    }

    // fau: testNav - add number parameter (to show if title should not be shown)
    /**
     * Returns the title of a test question and checks if the title output is allowed.
     * If not, the localized text "question" will be returned.
     *
     * @param string $title The original title of the question
     * @param integer $nr The number of the question in the sequence
     * @return string The title for the question title output
     * @access public
     */
    public function getQuestionTitle($title, $nr = null, $points = null): string
    {
        switch ($this->getTitleOutput()) {
            case '0':
            case '1':
                return $title;
                break;
            case '2':
                if (isset($nr)) {
                    return $this->lng->txt("ass_question") . ' ' . $nr;
                }
                return $this->lng->txt("ass_question");
                break;
            case 3:
                if (isset($nr)) {
                    $txt = $this->lng->txt("ass_question") . ' ' . $nr;
                } else {
                    $txt = $this->lng->txt("ass_question");
                }
                if ($points != '') {
                    $lngv = $this->lng->txt('points');
                    if ($points == 1) {
                        $lngv = $this->lng->txt('point');
                    }
                    $txt .= ' - ' . $points . ' ' . $lngv;
                }
                return $txt;
                break;

        }
        return $this->lng->txt("ass_question");
    }
    // fau.

    /**
    * Returns the dataset for a given question id
    *
    * @param integer $question_id The database id of the question
    * @return object Question dataset
    * @access public
    * @see $questions
    */
    public function getQuestionDataset($question_id): object
    {
        $result = $this->db->queryF(
            "SELECT qpl_questions.*, qpl_qst_type.type_tag FROM qpl_questions, qpl_qst_type WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id",
            ['integer'],
            [$question_id]
        );
        $row = $this->db->fetchObject($result);
        return $row;
    }

    /**
    * Get the id's of the questions which are already part of the test
    *
    * @return array An array containing the already existing questions
    * @access	public
    */
    public function &getExistingQuestions($pass = null): array
    {
        $existing_questions = [];
        $active_id = $this->getActiveIdOfUser($this->user->getId());
        if ($this->isRandomTest()) {
            if (is_null($pass)) {
                $pass = 0;
            }
            $result = $this->db->queryF(
                "SELECT qpl_questions.original_id FROM qpl_questions, tst_test_rnd_qst WHERE tst_test_rnd_qst.active_fi = %s AND tst_test_rnd_qst.question_fi = qpl_questions.question_id AND tst_test_rnd_qst.pass = %s",
                ['integer','integer'],
                [$active_id, $pass]
            );
        } else {
            $result = $this->db->queryF(
                "SELECT qpl_questions.original_id FROM qpl_questions, tst_test_question WHERE tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id",
                ['integer'],
                [$this->getTestId()]
            );
        }
        while ($data = $this->db->fetchObject($result)) {
            if ($data->original_id === null) {
                continue;
            }

            array_push($existing_questions, $data->original_id);
        }
        return $existing_questions;
    }

    /**
    * Returns the question type of a question with a given id
    *
    * @param integer $question_id The database id of the question
    * @result string The question type string
    * @access private
    */
    public function getQuestionType($question_id)
    {
        if ($question_id < 1) {
            return -1;
        }
        $result = $this->db->queryF(
            "SELECT type_tag FROM qpl_questions, qpl_qst_type WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id",
            ['integer'],
            [$question_id]
        );
        if ($result->numRows() == 1) {
            $data = $this->db->fetchObject($result);
            return $data->type_tag;
        } else {
            return "";
        }
    }

    /**
    * Write the initial entry for the tests working time to the database
    *
    * @param integer $user_id The database id of the user working with the test
    * @access	public
    */
    public function startWorkingTime($active_id, $pass)
    {
        $next_id = $this->db->nextId('tst_times');
        $affectedRows = $this->db->manipulateF(
            "INSERT INTO tst_times (times_id, active_fi, started, finished, pass, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
            ['integer', 'integer', 'timestamp', 'timestamp', 'integer', 'integer'],
            [$next_id, $active_id, date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), $pass, time()]
        );
        return $next_id;
    }

    /**
    * Update the working time of a test when a question is answered
    *
    * @param integer $times_id The database id of a working time entry
    * @access	public
    */
    public function updateWorkingTime($times_id)
    {
        $affectedRows = $this->db->manipulateF(
            "UPDATE tst_times SET finished = %s, tstamp = %s WHERE times_id = %s",
            ['timestamp', 'integer', 'integer'],
            [date('Y-m-d H:i:s'), time(), $times_id]
        );
    }

    /**
    * Gets the id's of all questions a user already worked through
    *
    * @return array The question id's of the questions already worked through
    * @access	public
    */
    public function &getWorkedQuestions($active_id, $pass = null): array
    {
        if (is_null($pass)) {
            $result = $this->db->queryF(
                "SELECT question_fi FROM tst_solutions WHERE active_fi = %s AND pass = %s GROUP BY question_fi",
                ['integer','integer'],
                [$active_id, 0]
            );
        } else {
            $result = $this->db->queryF(
                "SELECT question_fi FROM tst_solutions WHERE active_fi = %s AND pass = %s GROUP BY question_fi",
                ['integer','integer'],
                [$active_id, $pass]
            );
        }
        $result_array = [];
        while ($row = $this->db->fetchAssoc($result)) {
            array_push($result_array, $row["question_fi"]);
        }
        return $result_array;
    }

    /**
    * Returns true if an active user completed a test pass and did not start a new pass
    *
    * @param integer $active_id The active id of the user
    * @param integer $currentpass The current test pass of the user
    * @return boolean true if an active user completed a test pass and did not start a new pass, false otherwise
    * @access public
    */
    public function isTestFinishedToViewResults($active_id, $currentpass): bool
    {
        $num = ilObjTest::lookupPassResultsUpdateTimestamp($active_id, $currentpass);
        return ((($currentpass > 0) && ($num == 0)) || $this->isTestFinished($active_id)) ? true : false;
    }

    /**
    * Returns all questions of a test in test order
    *
    * @return array An array containing the id's as keys and the database row objects as values
    * @access public
    */
    public function &getAllQuestions($pass = null): array
    {
        $result_array = [];
        if ($this->isRandomTest()) {
            $active_id = $this->getActiveIdOfUser($this->user->getId());
            $this->loadQuestions($active_id, $pass);
            if (count($this->questions) == 0) {
                return $result_array;
            }
            if (is_null($pass)) {
                $pass = self::_getPass($active_id);
            }
            $result = $this->db->queryF(
                "SELECT qpl_questions.* FROM qpl_questions, tst_test_rnd_qst WHERE tst_test_rnd_qst.question_fi = qpl_questions.question_id AND tst_test_rnd_qst.active_fi = %s AND tst_test_rnd_qst.pass = %s AND " . $this->db->in('qpl_questions.question_id', $this->questions, false, 'integer'),
                ['integer','integer'],
                [$active_id, $pass]
            );
        } else {
            if (count($this->questions) == 0) {
                return $result_array;
            }
            $result = $this->db->query("SELECT qpl_questions.* FROM qpl_questions, tst_test_question WHERE tst_test_question.question_fi = qpl_questions.question_id AND " . $this->db->in('qpl_questions.question_id', $this->questions, false, 'integer'));
        }
        while ($row = $this->db->fetchAssoc($result)) {
            $result_array[$row["question_id"]] = $row;
        }
        return $result_array;
    }

    /**
    * Gets the active id of a given user
    *
    * @param integer $user_id The database id of the user
    * @param string $anonymous_id The anonymous id if the test is an anonymized test
    * @return integer The active ID
    * @access	public
    */
    public function getActiveIdOfUser($user_id = "", $anonymous_id = ""): ?int
    {
        if (!$user_id) {
            $user_id = $this->user->getId();
        }

        $tst_access_code = ilSession::get('tst_access_code');
        if (is_array($tst_access_code) &&
            $this->user->getId() === ANONYMOUS_USER_ID &&
            isset($tst_access_code[$this->getTestId()]) &&
            $tst_access_code[$this->getTestId()] !== '') {
            $result = $this->db->queryF(
                'SELECT active_id FROM tst_active WHERE user_fi = %s AND test_fi = %s AND anonymous_id = %s',
                ['integer', 'integer', 'text'],
                [$user_id, $this->test_id, $tst_access_code[$this->getTestId()]]
            );
        } elseif ((string) $anonymous_id !== '') {
            $result = $this->db->queryF(
                'SELECT active_id FROM tst_active WHERE user_fi = %s AND test_fi = %s AND anonymous_id = %s',
                ['integer', 'integer', 'text'],
                [$user_id, $this->test_id, $anonymous_id]
            );
        } else {
            if ($this->user->getId() === ANONYMOUS_USER_ID) {
                return null;
            }
            $result = $this->db->queryF(
                'SELECT active_id FROM tst_active WHERE user_fi = %s AND test_fi = %s',
                ['integer', 'integer'],
                [$user_id, $this->test_id]
            );
        }

        if ($result->numRows()) {
            $row = $this->db->fetchAssoc($result);
            return (int) $row['active_id'];
        }

        return 0;
    }

    public static function _getActiveIdOfUser($user_id = "", $test_id = "")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        if (!$user_id) {
            $user_id = $ilUser->id;
        }
        if (!$test_id) {
            return "";
        }
        $result = $ilDB->queryF(
            "SELECT tst_active.active_id FROM tst_active WHERE user_fi = %s AND test_fi = %s",
            ['integer', 'integer'],
            [$user_id, $test_id]
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            return $row["active_id"];
        } else {
            return "";
        }
    }

    /**
    * Shuffles the values of a given array
    *
    * @param array $array An array which should be shuffled
    * @access public
    */
    public function pcArrayShuffle($array): array
    {
        $keys = array_keys($array);
        shuffle($keys);
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $array[$key];
        }
        return $result;
    }

    /**
     * Calculates the results of a test for a given user
     * and returns an array with all test results
     *
     * @return array An array containing the test results for the given user
     */
    public function &getTestResult(
        int $active_id,
        ?int $pass = null,
        bool $ordered_sequence = false,
        bool $considerHiddenQuestions = true,
        bool $considerOptionalQuestions = true
    ): array {
        $results = $this->getResultsForActiveId($active_id);

        if ($pass === null) {
            $pass = (int) $results['pass'];
        }

        $test_sequence_factory = new ilTestSequenceFactory($this, $this->db, $this->questioninfo);
        $test_sequence = $test_sequence_factory->getSequenceByActiveIdAndPass($active_id, $pass);

        $test_sequence->setConsiderHiddenQuestionsEnabled($considerHiddenQuestions);
        $test_sequence->setConsiderOptionalQuestionsEnabled($considerOptionalQuestions);

        $test_sequence->loadFromDb();
        $test_sequence->loadQuestions();

        if ($ordered_sequence) {
            $sequence = $test_sequence->getOrderedSequenceQuestions();
        } else {
            $sequence = $test_sequence->getUserSequenceQuestions();
        }

        $arrResults = [];

        $query = "
            SELECT
                tst_test_result.question_fi,
                tst_test_result.points reached,
                tst_test_result.hint_count requested_hints,
                tst_test_result.hint_points hint_points,
                tst_test_result.answered answered,
                tst_manual_fb.finalized_evaluation finalized_evaluation

            FROM tst_test_result

            LEFT JOIN tst_solutions
            ON tst_solutions.active_fi = tst_test_result.active_fi
            AND tst_solutions.question_fi = tst_test_result.question_fi

            LEFT JOIN tst_manual_fb
            ON tst_test_result.active_fi = tst_manual_fb.active_fi
            AND tst_test_result.question_fi = tst_manual_fb.question_fi

            WHERE tst_test_result.active_fi = %s
            AND tst_test_result.pass = %s
        ";

        $solutionresult = $this->db->queryF(
            $query,
            ['integer', 'integer'],
            [$active_id, $pass]
        );

        while ($row = $this->db->fetchAssoc($solutionresult)) {
            $arrResults[ $row['question_fi'] ] = $row;
        }

        $numWorkedThrough = count($arrResults);

        $IN_question_ids = $this->db->in('qpl_questions.question_id', $sequence, false, 'integer');

        $query = "
			SELECT		qpl_questions.*,
						qpl_qst_type.type_tag,
						qpl_sol_sug.question_fi has_sug_sol

			FROM		qpl_qst_type,
						qpl_questions

			LEFT JOIN	qpl_sol_sug
			ON			qpl_sol_sug.question_fi = qpl_questions.question_id

			WHERE		qpl_qst_type.question_type_id = qpl_questions.question_type_fi
			AND			$IN_question_ids
		";

        $result = $this->db->query($query);

        $unordered = [];

        $key = 1;

        $obligationsAnswered = true;

        while ($row = $this->db->fetchAssoc($result)) {
            if (!isset($arrResults[ $row['question_id'] ])) {
                $percentvalue = 0.0;
            } else {
                $percentvalue = (
                    $row['points'] ? $arrResults[$row['question_id']]['reached'] / $row['points'] : 0
                );
            }
            if ($percentvalue < 0) {
                $percentvalue = 0.0;
            }

            $data = [
                "nr" => "$key",
                "title" => ilLegacyFormElementsUtil::prepareFormOutput($row['title']),
                "max" => round($row['points'], 2),
                "reached" => round($arrResults[$row['question_id']]['reached'] ?? 0, 2),
                'requested_hints' => $arrResults[$row['question_id']]['requested_hints'] ?? 0,
                'hint_points' => $arrResults[$row['question_id']]['hint_points'] ?? 0,
                "percent" => sprintf("%2.2f ", ($percentvalue) * 100) . "%",
                "solution" => ($row['has_sug_sol']) ? assQuestion::_getSuggestedSolutionOutput($row['question_id']) : '',
                "type" => $row["type_tag"],
                "qid" => $row['question_id'],
                "original_id" => $row["original_id"],
                "workedthrough" => isset($arrResults[$row['question_id']]) ? 1 : 0,
                'answered' => $arrResults[$row['question_id']]['answered'] ?? 0,
                'finalized_evaluation' => $arrResults[$row['question_id']]['finalized_evaluation'] ?? 0,
            ];

            if (!isset($arrResults[ $row['question_id'] ]['answered']) || !$arrResults[ $row['question_id'] ]['answered']) {
                $obligationsAnswered = false;
            }

            $unordered[ $row['question_id'] ] = $data;

            $key++;
        }

        $numQuestionsTotal = count($unordered);

        $pass_max = 0;
        $pass_reached = 0;
        $pass_requested_hints = 0;
        $pass_hint_points = 0;
        $key = 1;

        $found = [];

        foreach ($sequence as $qid) {
            // building pass point sums based on prepared data
            // for question that exists in users qst sequence
            $pass_max += round($unordered[$qid]['max'], 2);
            $pass_reached += round($unordered[$qid]['reached'], 2);
            $pass_requested_hints += $unordered[$qid]['requested_hints'];
            $pass_hint_points += $unordered[$qid]['hint_points'];

            // pickup prepared data for question
            // that exists in users qst sequence
            $unordered[$qid]['nr'] = $key;
            array_push($found, $unordered[$qid]);

            // increment key counter
            $key++;
        }

        $unordered = null;

        if ($this->getScoreCutting() == 1) {
            if ($results['reached_points'] < 0) {
                $results['reached_points'] = 0;
            }

            if ($pass_reached < 0) {
                $pass_reached = 0;
            }
        }

        $found['pass']['total_max_points'] = $pass_max;
        $found['pass']['total_reached_points'] = $pass_reached;
        $found['pass']['total_requested_hints'] = $pass_requested_hints;
        $found['pass']['total_hint_points'] = $pass_hint_points;
        $found['pass']['percent'] = ($pass_max > 0) ? $pass_reached / $pass_max : 0;
        $found['pass']['obligationsAnswered'] = $obligationsAnswered;
        $found['pass']['num_workedthrough'] = $numWorkedThrough;
        $found['pass']['num_questions_total'] = $numQuestionsTotal;

        $found["test"]["total_max_points"] = $results['max_points'];
        $found["test"]["total_reached_points"] = $results['reached_points'];
        $found["test"]["total_requested_hints"] = $results['hint_count'];
        $found["test"]["total_hint_points"] = $results['hint_points'];
        $found["test"]["result_pass"] = $results['pass'];
        $found['test']['result_tstamp'] = $results['tstamp'];
        $found['test']['obligations_answered'] = $results['obligations_answered'];

        if ((!$found['pass']['total_reached_points']) or (!$found['pass']['total_max_points'])) {
            $percentage = 0.0;
        } else {
            $percentage = ($found['pass']['total_reached_points'] / $found['pass']['total_max_points']) * 100.0;

            if ($percentage < 0) {
                $percentage = 0.0;
            }
        }

        $found["test"]["passed"] = $results['passed'];

        return $found;
    }

    /**
    * Returns the number of persons who started the test
    *
    * @return integer The number of persons who started the test
    * @access public
    */
    public function evalTotalPersons(): int
    {
        $result = $this->db->queryF(
            "SELECT COUNT(active_id) total FROM tst_active WHERE test_fi = %s",
            ['integer'],
            [$this->getTestId()]
        );
        $row = $this->db->fetchAssoc($result);
        return $row["total"];
    }

    /**
    * Returns the complete working time in seconds a user worked on the test
    *
    * @return integer The working time in seconds
    * @access public
    */
    public function getCompleteWorkingTime($user_id): int
    {
        $result = $this->db->queryF(
            "SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi AND tst_active.user_fi = %s",
            ['integer','integer'],
            [$this->getTestId(), $user_id]
        );
        $time = 0;
        while ($row = $this->db->fetchAssoc($result)) {
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches);
            $epoch_1 = mktime(
                (int) $matches[4],
                (int) $matches[5],
                (int) $matches[6],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[1]
            );
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["finished"], $matches);
            $epoch_2 = mktime(
                (int) $matches[4],
                (int) $matches[5],
                (int) $matches[6],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[1]
            );
            $time += ($epoch_2 - $epoch_1);
        }
        return $time;
    }

    /**
    * Returns the complete working time in seconds for all test participants
    *
    * @return array An array containing the working time in seconds for all test participants
    * @access public
    */
    public function &getCompleteWorkingTimeOfParticipants(): array
    {
        return $this->_getCompleteWorkingTimeOfParticipants($this->getTestId());
    }

    /**
    * Returns the complete working time in seconds for all test participants
    *
    * @param integer $test_id The database ID of the test
    * @return array An array containing the working time in seconds for all test participants
    * @access public
    */
    public function &_getCompleteWorkingTimeOfParticipants($test_id): array
    {
        $result = $this->db->queryF(
            "SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi ORDER BY tst_times.active_fi, tst_times.started",
            ['integer'],
            [$test_id]
        );
        $time = 0;
        $times = [];
        while ($row = $this->db->fetchAssoc($result)) {
            if (!array_key_exists($row["active_fi"], $times)) {
                $times[$row["active_fi"]] = 0;
            }
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches);
            $epoch_1 = mktime(
                (int) $matches[4],
                (int) $matches[5],
                (int) $matches[6],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[1]
            );
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["finished"], $matches);
            $epoch_2 = mktime(
                (int) $matches[4],
                (int) $matches[5],
                (int) $matches[6],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[1]
            );
            $times[$row["active_fi"]] += ($epoch_2 - $epoch_1);
        }
        return $times;
    }

    /**
    * Returns the complete working time in seconds for a test participant
    *
    * @return integer The working time in seconds for the test participant
    * @access public
    */
    public function getCompleteWorkingTimeOfParticipant($active_id): int
    {
        $result = $this->db->queryF(
            "SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi AND tst_active.active_id = %s ORDER BY tst_times.active_fi, tst_times.started",
            ['integer','integer'],
            [$this->getTestId(), $active_id]
        );
        $time = 0;
        while ($row = $this->db->fetchAssoc($result)) {
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches);
            $epoch_1 = mktime(
                (int) $matches[4],
                (int) $matches[5],
                (int) $matches[6],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[1]
            );
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["finished"], $matches);
            $epoch_2 = mktime(
                (int) $matches[4],
                (int) $matches[5],
                (int) $matches[6],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[1]
            );
            $time += ($epoch_2 - $epoch_1);
        }
        return $time;
    }

    /**
    * Returns the complete working time in seconds for a test participant
    *
    * @return integer The working time in seconds for the test participant
    * @access public
    */
    public static function _getWorkingTimeOfParticipantForPass($active_id, $pass): int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT * FROM tst_times WHERE active_fi = %s AND pass = %s ORDER BY started",
            ['integer','integer'],
            [$active_id, $pass]
        );
        $time = 0;
        while ($row = $ilDB->fetchAssoc($result)) {
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches);
            $epoch_1 = mktime(
                (int) $matches[4],
                (int) $matches[5],
                (int) $matches[6],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[1]
            );
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["finished"], $matches);
            $epoch_2 = mktime(
                (int) $matches[4],
                (int) $matches[5],
                (int) $matches[6],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[1]
            );
            $time += ($epoch_2 - $epoch_1);
        }
        return $time;
    }

    /**
    * Returns the first and last visit of a participant
    *
    * @param integer $active_id The active ID of the participant
    * @return array The first and last visit of a participant
    * @access public
    */
    public function getVisitTimeOfParticipant($active_id): array
    {
        return ilObjTest::_getVisitTimeOfParticipant($this->getTestId(), $active_id);
    }

    /**
    * Returns the first and last visit of a participant
    *
    * @param integer $test_id The database ID of the test
    * @param integer $active_id The active ID of the participant
    * @return array The first and last visit of a participant
    * @access public
    */
    public function _getVisitTimeOfParticipant($test_id, $active_id): array
    {
        $result = $this->db->queryF(
            "SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi AND tst_active.active_id = %s ORDER BY tst_times.started",
            ['integer','integer'],
            [$test_id, $active_id]
        );
        $firstvisit = 0;
        $lastvisit = 0;
        while ($row = $this->db->fetchAssoc($result)) {
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches);
            $epoch_1 = mktime(
                (int) $matches[4],
                (int) $matches[5],
                (int) $matches[6],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[1]
            );
            if ($firstvisit == 0 || $epoch_1 < $firstvisit) {
                $firstvisit = $epoch_1;
            }
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["finished"], $matches);
            $epoch_2 = mktime(
                (int) $matches[4],
                (int) $matches[5],
                (int) $matches[6],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[1]
            );
            if ($epoch_2 > $lastvisit) {
                $lastvisit = $epoch_2;
            }
        }
        return ["firstvisit" => $firstvisit, "lastvisit" => $lastvisit];
    }

    /**
    * Returns the statistical evaluation of the test for a specified user
    */
    public function evalStatistical($active_id): array
    {
        $pass = ilObjTest::_getResultPass($active_id);
        $test_result = &$this->getTestResult($active_id, $pass);
        $result = $this->db->queryF(
            "SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.active_id = %s AND tst_active.active_id = tst_times.active_fi",
            ['integer'],
            [$active_id]
        );
        $times = [];
        $first_visit = 0;
        $last_visit = 0;
        while ($row = $this->db->fetchObject($result)) {
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->started, $matches);
            $epoch_1 = mktime(
                (int) $matches[4],
                (int) $matches[5],
                (int) $matches[6],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[1]
            );
            if (!$first_visit) {
                $first_visit = $epoch_1;
            }
            if ($epoch_1 < $first_visit) {
                $first_visit = $epoch_1;
            }
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->finished, $matches);
            $epoch_2 = mktime(
                (int) $matches[4],
                (int) $matches[5],
                (int) $matches[6],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[1]
            );
            if (!$last_visit) {
                $last_visit = $epoch_2;
            }
            if ($epoch_2 > $last_visit) {
                $last_visit = $epoch_2;
            }
            $times[$row->active_fi] += ($epoch_2 - $epoch_1);
        }
        $max_time = 0;
        foreach ($times as $key => $value) {
            $max_time += $value;
        }
        if ((!$test_result["test"]["total_reached_points"]) or (!$test_result["test"]["total_max_points"])) {
            $percentage = 0.0;
        } else {
            $percentage = ($test_result["test"]["total_reached_points"] / $test_result["test"]["total_max_points"]) * 100.0;
            if ($percentage < 0) {
                $percentage = 0.0;
            }
        }
        $mark_obj = $this->mark_schema->getMatchingMark($percentage);
        $first_date = getdate($first_visit);
        $last_date = getdate($last_visit);
        $qworkedthrough = 0;
        foreach ($test_result as $key => $value) {
            if (preg_match("/\d+/", $key)) {
                $qworkedthrough += $value["workedthrough"];
            }
        }
        if (!$qworkedthrough) {
            $atimeofwork = 0;
        } else {
            $atimeofwork = $max_time / $qworkedthrough;
        }

        $obligationsAnswered = $test_result["test"]["obligations_answered"];

        $result_mark = "";
        $passed = "";

        if ($mark_obj) {
            $result_mark = $mark_obj->getShortName();

            if ($mark_obj->getPassed() && $obligationsAnswered) {
                $passed = 1;
            } else {
                $passed = 0;
            }
        }
        $percent_worked_through = 0;
        if (count($this->questions)) {
            $percent_worked_through = $qworkedthrough / count($this->questions);
        }
        $result_array = [
            "qworkedthrough" => $qworkedthrough,
            "qmax" => count($this->questions),
            "pworkedthrough" => $percent_worked_through,
            "timeofwork" => $max_time,
            "atimeofwork" => $atimeofwork,
            "firstvisit" => $first_date,
            "lastvisit" => $last_date,
            "resultspoints" => $test_result["test"]["total_reached_points"],
            "maxpoints" => $test_result["test"]["total_max_points"],
            "resultsmarks" => $result_mark,
            "passed" => $passed,
            "distancemedian" => "0"
        ];
        foreach ($test_result as $key => $value) {
            if (preg_match("/\d+/", $key)) {
                $result_array[$key] = $value;
            }
        }
        return $result_array;
    }

    /**
    * Returns an array with the total points of all users who passed the test
    * This array could be used for statistics
    *
    * @return array The total point values
    * @access public
    */
    public function &getTotalPointsPassedArray(): array
    {
        $totalpoints_array = [];
        $all_users = $this->evalTotalParticipantsArray();
        foreach ($all_users as $active_id => $user_name) {
            $test_result = &$this->getTestResult($active_id);
            $reached = $test_result["test"]["total_reached_points"];
            $total = $test_result["test"]["total_max_points"];
            $percentage = $total != 0 ? $reached / $total : 0;
            $mark = $this->mark_schema->getMatchingMark($percentage * 100.0);

            $obligationsAnswered = $test_result["test"]["obligations_answered"];

            if ($mark) {
                if ($mark->getPassed() && $obligationsAnswered) {
                    array_push($totalpoints_array, $test_result["test"]["total_reached_points"]);
                }
            }
        }
        return $totalpoints_array;
    }

    /**
     * Returns all persons who started the test
     *
     * @return array The active ids, names and logins of the persons who started the test
    */
    public function &getParticipants(): array
    {
        $result = $this->db->queryF(
            "SELECT tst_active.active_id, usr_data.usr_id, usr_data.firstname, usr_data.lastname, usr_data.title, usr_data.login FROM tst_active LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id WHERE tst_active.test_fi = %s ORDER BY usr_data.lastname ASC",
            ['integer'],
            [$this->getTestId()]
        );
        $persons_array = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $name = $this->lng->txt("anonymous");
            $fullname = $this->lng->txt("anonymous");
            $login = "";
            if (!$this->getAnonymity()) {
                if (strlen($row["firstname"] . $row["lastname"] . $row["title"]) == 0) {
                    $name = $this->lng->txt("deleted_user");
                    $fullname = $this->lng->txt("deleted_user");
                    $login = $this->lng->txt("unknown");
                } else {
                    $login = $row["login"];
                    if ($row["usr_id"] == ANONYMOUS_USER_ID) {
                        $name = $this->lng->txt("anonymous");
                        $fullname = $this->lng->txt("anonymous");
                    } else {
                        $name = trim($row["lastname"] . ", " . $row["firstname"] . " " . $row["title"]);
                        $fullname = trim($row["title"] . " " . $row["firstname"] . " " . $row["lastname"]);
                    }
                }
            }
            $persons_array[$row["active_id"]] = [
                "name" => $name,
                "fullname" => $fullname,
                "login" => $login
            ];
        }
        return $persons_array;
    }

    /**
    * Returns all persons who started the test
    *
    * @return array The user id's and names of the persons who started the test
    * @access public
    */
    public function evalTotalPersonsArray($name_sort_order = "asc"): array
    {
        $result = $this->db->queryF(
            "SELECT tst_active.user_fi, tst_active.active_id, usr_data.firstname, usr_data.lastname, usr_data.title FROM tst_active LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id WHERE tst_active.test_fi = %s ORDER BY usr_data.lastname " . strtoupper($name_sort_order),
            ['integer'],
            [$this->getTestId()]
        );
        $persons_array = [];
        while ($row = $this->db->fetchAssoc($result)) {
            if ($this->getAccessFilteredParticipantList() && !$this->getAccessFilteredParticipantList()->isActiveIdInList($row["active_id"])) {
                continue;
            }

            if ($this->getAnonymity()) {
                $persons_array[$row["active_id"]] = $this->lng->txt("anonymous");
            } else {
                if (strlen($row["firstname"] . $row["lastname"] . $row["title"]) == 0) {
                    $persons_array[$row["active_id"]] = $this->lng->txt("deleted_user");
                } else {
                    if ($row["user_fi"] == ANONYMOUS_USER_ID) {
                        $persons_array[$row["active_id"]] = $row["lastname"];
                    } else {
                        $persons_array[$row["active_id"]] = trim($row["lastname"] . ", " . $row["firstname"] . " " . $row["title"]);
                    }
                }
            }
        }
        return $persons_array;
    }

    /**
    * Returns all participants who started the test
    *
    * @return array The active user id's and names of the persons who started the test
    */
    public function evalTotalParticipantsArray($name_sort_order = "asc"): array
    {
        $result = $this->db->queryF(
            "SELECT tst_active.user_fi, tst_active.active_id, usr_data.login, usr_data.firstname, usr_data.lastname, usr_data.title FROM tst_active LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id WHERE tst_active.test_fi = %s ORDER BY usr_data.lastname " . strtoupper($name_sort_order),
            ['integer'],
            [$this->getTestId()]
        );
        $persons_array = [];
        while ($row = $this->db->fetchAssoc($result)) {
            if ($this->getAnonymity()) {
                $persons_array[$row["active_id"]] = ["name" => $this->lng->txt("anonymous")];
            } else {
                if (strlen($row["firstname"] . $row["lastname"] . $row["title"]) == 0) {
                    $persons_array[$row["active_id"]] = ["name" => $this->lng->txt("deleted_user")];
                } else {
                    if ($row["user_fi"] == ANONYMOUS_USER_ID) {
                        $persons_array[$row["active_id"]] = ["name" => $row["lastname"]];
                    } else {
                        $persons_array[$row["active_id"]] = ["name" => trim($row["lastname"] . ", " . $row["firstname"] . " " . $row["title"]), "login" => $row["login"]];
                    }
                }
            }
        }
        return $persons_array;
    }

    /**
    * Retrieves all the assigned questions for all test passes of a test participant
    *
    * @return array An associated array containing the questions
    * @access public
    */
    public function &getQuestionsOfTest($active_id): array
    {
        if ($this->isRandomTest()) {
            $this->db->setLimit($this->getQuestionCount(), 0);
            $result = $this->db->queryF(
                "SELECT tst_test_rnd_qst.sequence, tst_test_rnd_qst.question_fi, " .
                "tst_test_rnd_qst.pass, qpl_questions.points " .
                "FROM tst_test_rnd_qst, qpl_questions " .
                "WHERE tst_test_rnd_qst.question_fi = qpl_questions.question_id " .
                "AND tst_test_rnd_qst.active_fi = %s ORDER BY tst_test_rnd_qst.sequence",
                ['integer'],
                [$active_id]
            );
        } else {
            $result = $this->db->queryF(
                "SELECT tst_test_question.sequence, tst_test_question.question_fi, " .
                "qpl_questions.points " .
                "FROM tst_test_question, tst_active, qpl_questions " .
                "WHERE tst_test_question.question_fi = qpl_questions.question_id " .
                "AND tst_active.active_id = %s AND tst_active.test_fi = tst_test_question.test_fi",
                ['integer'],
                [$active_id]
            );
        }
        $qtest = [];
        if ($result->numRows()) {
            while ($row = $this->db->fetchAssoc($result)) {
                array_push($qtest, $row);
            }
        }
        return $qtest;
    }

    /**
    * Retrieves all the assigned questions for a test participant in a given test pass
    *
    * @return array An associated array containing the questions
    * @access public
    */
    public function &getQuestionsOfPass($active_id, $pass): array
    {
        if ($this->isRandomTest()) {
            $this->db->setLimit($this->getQuestionCount(), 0);
            $result = $this->db->queryF(
                "SELECT tst_test_rnd_qst.sequence, tst_test_rnd_qst.question_fi, " .
                "qpl_questions.points " .
                "FROM tst_test_rnd_qst, qpl_questions " .
                "WHERE tst_test_rnd_qst.question_fi = qpl_questions.question_id " .
                "AND tst_test_rnd_qst.active_fi = %s AND tst_test_rnd_qst.pass = %s " .
                "ORDER BY tst_test_rnd_qst.sequence",
                ['integer', 'integer'],
                [$active_id, $pass]
            );
        } else {
            $result = $this->db->queryF(
                "SELECT tst_test_question.sequence, tst_test_question.question_fi, " .
                "qpl_questions.points " .
                "FROM tst_test_question, tst_active, qpl_questions " .
                "WHERE tst_test_question.question_fi = qpl_questions.question_id " .
                "AND tst_active.active_id = %s AND tst_active.test_fi = tst_test_question.test_fi",
                ['integer'],
                [$active_id]
            );
        }
        $qpass = [];
        if ($result->numRows()) {
            while ($row = $this->db->fetchAssoc($result)) {
                array_push($qpass, $row);
            }
        }
        return $qpass;
    }

    public function getAccessFilteredParticipantList(): ?ilTestParticipantList
    {
        return $this->access_filtered_participant_list;
    }

    public function setAccessFilteredParticipantList(ilTestParticipantList $access_filtered_participant_list): void
    {
        $this->access_filtered_participant_list = $access_filtered_participant_list;
    }

    public function buildStatisticsAccessFilteredParticipantList(): ilTestParticipantList
    {
        $list = new ilTestParticipantList($this, $this->user, $this->lng, $this->db);
        $list->initializeFromDbRows($this->getTestParticipants());

        return $list->getAccessFilteredList(
            $this->participant_access_filter->getAccessStatisticsUserFilter($this->getRefId())
        );
    }

    public function getUnfilteredEvaluationData(): ilTestEvaluationData
    {
        $data = new ilTestEvaluationData($this->db, $this);

        $query = "
			SELECT		tst_test_result.*,
						qpl_questions.original_id,
						qpl_questions.title questiontitle,
						qpl_questions.points maxpoints

			FROM		tst_test_result, qpl_questions, tst_active

			WHERE		tst_active.active_id = tst_test_result.active_fi
			AND			qpl_questions.question_id = tst_test_result.question_fi
			AND			tst_active.test_fi = %s

			ORDER BY	tst_active.active_id ASC, tst_test_result.pass ASC, tst_test_result.tstamp DESC
		";

        $result = $this->db->queryF(
            $query,
            ['integer'],
            [$this->getTestId()]
        );

        $pass = null;
        $checked = [];
        $datasets = 0;
        $questionData = [];

        while ($row = $this->db->fetchAssoc($result)) {
            if (!$data->participantExists($row["active_fi"])) {
                continue;
            }

            $participantObject = $data->getParticipant($row["active_fi"]);
            $passObject = $participantObject->getPass($row["pass"]);

            if (!($passObject instanceof ilTestEvaluationPassData)) {
                continue;
            }

            $passObject->addAnsweredQuestion(
                $row["question_fi"],
                $row["maxpoints"],
                $row["points"],
                (bool) $row['answered'],
                null,
                $row['manual']
            );
        }

        foreach (array_keys($data->getParticipants()) as $active_id) {
            if ($this->isRandomTest()) {
                for ($testpass = 0; $testpass <= $data->getParticipant($active_id)->getLastPass(); $testpass++) {
                    $this->db->setLimit($this->getQuestionCount(), 0);

                    $query = "
						SELECT tst_test_rnd_qst.sequence, tst_test_rnd_qst.question_fi, qpl_questions.original_id,
						tst_test_rnd_qst.pass, qpl_questions.points, qpl_questions.title
						FROM tst_test_rnd_qst, qpl_questions
						WHERE tst_test_rnd_qst.question_fi = qpl_questions.question_id
						AND tst_test_rnd_qst.pass = %s
						AND tst_test_rnd_qst.active_fi = %s ORDER BY tst_test_rnd_qst.sequence
					";

                    $result = $this->db->queryF(
                        $query,
                        ['integer','integer'],
                        [$testpass, $active_id]
                    );

                    if ($result->numRows()) {
                        while ($row = $this->db->fetchAssoc($result)) {
                            $tpass = array_key_exists("pass", $row) ? $row["pass"] : 0;

                            if (
                                !isset($row["question_fi"], $row["points"], $row["sequence"]) ||
                                !is_numeric($row["question_fi"]) || !is_numeric($row["points"]) || !is_numeric($row["sequence"])
                            ) {
                                continue;
                            }

                            $data->getParticipant($active_id)->addQuestion(
                                (int) $row["original_id"],
                                (int) $row["question_fi"],
                                (float) $row["points"],
                                (int) $row["sequence"],
                                $tpass
                            );

                            $data->addQuestionTitle($row["question_fi"], $row["title"]);
                        }
                    }
                }
            } else {
                $query = "
					SELECT tst_test_question.sequence, tst_test_question.question_fi,
					qpl_questions.points, qpl_questions.title, qpl_questions.original_id
					FROM tst_test_question, tst_active, qpl_questions
					WHERE tst_test_question.question_fi = qpl_questions.question_id
					AND tst_active.active_id = %s
					AND tst_active.test_fi = tst_test_question.test_fi
					ORDER BY tst_test_question.sequence
				";

                $result = $this->db->queryF(
                    $query,
                    ['integer'],
                    [$active_id]
                );

                if ($result->numRows()) {
                    $questionsbysequence = [];

                    while ($row = $this->db->fetchAssoc($result)) {
                        $questionsbysequence[$row["sequence"]] = $row;
                    }

                    $seqresult = $this->db->queryF(
                        "SELECT * FROM tst_sequence WHERE active_fi = %s",
                        ['integer'],
                        [$active_id]
                    );

                    while ($seqrow = $this->db->fetchAssoc($seqresult)) {
                        $questionsequence = unserialize($seqrow["sequence"]);

                        foreach ($questionsequence as $sidx => $seq) {
                            $data->getParticipant($active_id)->addQuestion(
                                $questionsbysequence[$seq]['original_id'] ?? 0,
                                $questionsbysequence[$seq]['question_fi'],
                                $questionsbysequence[$seq]['points'],
                                $sidx + 1,
                                $seqrow['pass']
                            );

                            $data->addQuestionTitle(
                                $questionsbysequence[$seq]["question_fi"],
                                $questionsbysequence[$seq]["title"]
                            );
                        }
                    }
                }
            }
        }

        foreach (array_keys($data->getParticipants()) as $active_id) {
            $tstUserData = $data->getParticipant($active_id);

            $percentage = $tstUserData->getReachedPointsInPercent();

            $obligationsAnswered = $tstUserData->areObligationsAnswered();

            $mark = $this->mark_schema->getMatchingMark($percentage);

            if (is_object($mark)) {
                $tstUserData->setMark($mark->getShortName());
                $tstUserData->setMarkOfficial($mark->getOfficialName());

                $tstUserData->setPassed(
                    $mark->getPassed() && $tstUserData->areObligationsAnswered()
                );
            }

            $visitingTime = $this->getVisitTimeOfParticipant($active_id);

            $tstUserData->setFirstVisit($visitingTime["firstvisit"]);
            $tstUserData->setLastVisit($visitingTime["lastvisit"]);
        }

        return $data;
    }

    public static function _getQuestionCountAndPointsForPassOfParticipant($active_id, $pass): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $questionSetType = ilObjTest::lookupQuestionSetTypeByActiveId($active_id);

        switch ($questionSetType) {
            case ilObjTest::QUESTION_SET_TYPE_RANDOM:

                $res = $ilDB->queryF(
                    "
						SELECT		tst_test_rnd_qst.pass,
									COUNT(tst_test_rnd_qst.question_fi) qcount,
									SUM(qpl_questions.points) qsum

						FROM		tst_test_rnd_qst,
									qpl_questions

						WHERE		tst_test_rnd_qst.question_fi = qpl_questions.question_id
						AND			tst_test_rnd_qst.active_fi = %s
						AND			pass = %s

						GROUP BY	tst_test_rnd_qst.active_fi,
									tst_test_rnd_qst.pass
					",
                    ['integer', 'integer'],
                    [$active_id, $pass]
                );

                break;

            case ilObjTest::QUESTION_SET_TYPE_FIXED:

                $res = $ilDB->queryF(
                    "
						SELECT		COUNT(tst_test_question.question_fi) qcount,
									SUM(qpl_questions.points) qsum

						FROM		tst_test_question,
									qpl_questions,
									tst_active

						WHERE		tst_test_question.question_fi = qpl_questions.question_id
						AND			tst_test_question.test_fi = tst_active.test_fi
						AND			tst_active.active_id = %s

						GROUP BY	tst_test_question.test_fi
					",
                    ['integer'],
                    [$active_id]
                );

                break;

            default:

                throw new ilTestException("not supported question set type: $questionSetType");
        }

        $row = $ilDB->fetchAssoc($res);

        if (is_array($row)) {
            return ["count" => $row["qcount"], "points" => $row["qsum"]];
        }

        return ["count" => 0, "points" => 0];
    }

    public function &getCompleteEvaluationData($withStatistics = true, $filterby = "", $filtertext = ""): ilTestEvaluationData
    {
        $data = $this->getUnfilteredEvaluationData();
        if ($withStatistics) {
            $data->calculateStatistics();
        }
        $data->setFilter($filterby, $filtertext);
        return $data;
    }

    /**
    * Creates an associated array with the results of all participants of a test
    *
    * @return array An associated array containing the results
    * @access public
    */
    public function &evalResultsOverview(): array
    {
        return $this->_evalResultsOverview($this->getTestId());
    }

    /**
    * Creates an associated array with the results of all participants of a test
    *
    * @return array An associated array containing the results
    * @access public
    */
    public function &_evalResultsOverview($test_id): array
    {
        $result = $this->db->queryF(
            "SELECT usr_data.usr_id, usr_data.firstname, usr_data.lastname, usr_data.title, usr_data.login, " .
            "tst_test_result.*, qpl_questions.original_id, qpl_questions.title questiontitle, " .
            "qpl_questions.points maxpoints " .
            "FROM tst_test_result, qpl_questions, tst_active " .
            "LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id " .
            "WHERE tst_active.active_id = tst_test_result.active_fi " .
            "AND qpl_questions.question_id = tst_test_result.question_fi " .
            "AND tst_active.test_fi = %s " .
            "ORDER BY tst_active.active_id, tst_test_result.pass, tst_test_result.tstamp",
            ['integer'],
            [$test_id]
        );
        $overview = [];
        while ($row = $this->db->fetchAssoc($result)) {
            if (!array_key_exists($row["active_fi"], $overview)) {
                $overview[$row["active_fi"]] = [];
                $overview[$row["active_fi"]]["firstname"] = $row["firstname"];
                $overview[$row["active_fi"]]["lastname"] = $row["lastname"];
                $overview[$row["active_fi"]]["title"] = $row["title"];
                $overview[$row["active_fi"]]["login"] = $row["login"];
                $overview[$row["active_fi"]]["usr_id"] = $row["usr_id"];
                $overview[$row["active_fi"]]["started"] = $row["started"];
                $overview[$row["active_fi"]]["finished"] = $row["finished"];
            }
            if (!array_key_exists($row["pass"], $overview[$row["active_fi"]])) {
                $overview[$row["active_fi"]][$row["pass"]] = [];
                $overview[$row["active_fi"]][$row["pass"]]["reached"] = 0;
                $overview[$row["active_fi"]][$row["pass"]]["maxpoints"] = $row["maxpoints"];
            }
            array_push($overview[$row["active_fi"]][$row["pass"]], $row);
            $overview[$row["active_fi"]][$row["pass"]]["reached"] += $row["points"];
        }
        return $overview;
    }

    /**
    * Creates an associated array with the results for a given participant of a test
    *
    * @param integer $active_id The active id of the participant
    * @return array An associated array containing the results
    * @access public
    */
    public function &evalResultsOverviewOfParticipant($active_id): array
    {
        $result = $this->db->queryF(
            "SELECT usr_data.usr_id, usr_data.firstname, usr_data.lastname, usr_data.title, usr_data.login, " .
            "tst_test_result.*, qpl_questions.original_id, qpl_questions.title questiontitle, " .
            "qpl_questions.points maxpoints " .
            "FROM tst_test_result, qpl_questions, tst_active " .
            "LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id " .
            "WHERE tst_active.active_id = tst_test_result.active_fi " .
            "AND qpl_questions.question_id = tst_test_result.question_fi " .
            "AND tst_active.test_fi = %s AND tst_active.active_id = %s" .
            "ORDER BY tst_active.active_id, tst_test_result.pass, tst_test_result.tstamp",
            ['integer', 'integer'],
            [$this->getTestId(), $active_id]
        );
        $overview = [];
        while ($row = $this->db->fetchAssoc($result)) {
            if (!array_key_exists($row["active_fi"], $overview)) {
                $overview[$row["active_fi"]] = [];
                $overview[$row["active_fi"]]["firstname"] = $row["firstname"];
                $overview[$row["active_fi"]]["lastname"] = $row["lastname"];
                $overview[$row["active_fi"]]["title"] = $row["title"];
                $overview[$row["active_fi"]]["login"] = $row["login"];
                $overview[$row["active_fi"]]["usr_id"] = $row["usr_id"];
                $overview[$row["active_fi"]]["started"] = $row["started"];
                $overview[$row["active_fi"]]["finished"] = $row["finished"];
            }
            if (!array_key_exists($row["pass"], $overview[$row["active_fi"]])) {
                $overview[$row["active_fi"]][$row["pass"]] = [];
                $overview[$row["active_fi"]][$row["pass"]]["reached"] = 0;
                $overview[$row["active_fi"]][$row["pass"]]["maxpoints"] = $row["maxpoints"];
            }
            array_push($overview[$row["active_fi"]][$row["pass"]], $row);
            $overview[$row["active_fi"]][$row["pass"]]["reached"] += $row["points"];
        }
        return $overview;
    }

    /**
    * Builds a user name for the output depending on test type and existence of
    * the user
    *
    * @param int $user_id The database ID of the user
    * @param string $firstname The first name of the user
    * @param string $lastname The last name of the user
    * @param string $title The title of the user
    * @return string The output name of the user
    * @access public
    */
    public function buildName($user_id, $firstname, $lastname, $title): string
    {
        $name = "";
        if (strlen($firstname . $lastname . $title) == 0) {
            $name = $this->lng->txt('deleted_user');
        } else {
            if ($user_id == ANONYMOUS_USER_ID) {
                $name = $lastname;
            } else {
                $name = trim($lastname . ", " . $firstname . " " . $title);
            }
            if ($this->getAnonymity()) {
                $name = $this->lng->txt("anonymous");
            }
        }
        return $name;
    }

    public function evalTotalStartedAverageTime(?array $active_ids_to_filter = null): float
    {
        $query = "SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi";

        if ($active_ids_to_filter !== null && $active_ids_to_filter !== []) {
            $query .= " AND " . $this->db->in('active_id', $active_ids_to_filter, false, 'integer');
        }

        $result = $this->db->queryF($query, ['integer'], [$this->getTestId()]);
        $times = [];
        while ($row = $this->db->fetchObject($result)) {
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->started, $matches);
            $epoch_1 = mktime(
                (int) $matches[4],
                (int) $matches[5],
                (int) $matches[6],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[1]
            );
            preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->finished, $matches);
            $epoch_2 = mktime(
                (int) $matches[4],
                (int) $matches[5],
                (int) $matches[6],
                (int) $matches[2],
                (int) $matches[3],
                (int) $matches[1]
            );
            if (isset($times[$row->active_fi])) {
                $times[$row->active_fi] += ($epoch_2 - $epoch_1);
            } else {
                $times[$row->active_fi] = ($epoch_2 - $epoch_1);
            }
        }
        $max_time = 0;
        $counter = 0;
        foreach ($times as $key => $value) {
            $max_time += $value;
            $counter++;
        }
        if ($counter) {
            $average_time = round($max_time / $counter);
        } else {
            $average_time = 0;
        }
        return $average_time;
    }

    /**
    * Returns the available question pools for the active user
    *
    * @return array The available question pools
    * @access public
    */
    public function getAvailableQuestionpools($use_object_id = false, $equal_points = false, $could_be_offline = false, $show_path = false, $with_questioncount = false, $permission = "read"): array
    {
        return ilObjQuestionPool::_getAvailableQuestionpools($use_object_id, $equal_points, $could_be_offline, $show_path, $with_questioncount, $permission);
    }

    /**
    * Returns the image path for web accessable images of a test
    * The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_TEST/images
    *
    * @access public
    */
    public function getImagePath(): string
    {
        return CLIENT_WEB_DIR . "/assessment/" . $this->getId() . "/images/";
    }

    /**
    * Returns the web image path for web accessable images of a test
    * The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_TEST/images
    *
    * @access public
    */
    public function getImagePathWeb()
    {
        $webdir = ilFileUtils::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/" . $this->getId() . "/images/";
        return str_replace(
            ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
            ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            $webdir
        );
    }

    /**
    * Creates a question GUI instance of a given question type
    *
    * @param integer $question_type The question type of the question
    * @param integer $question_id The question id of the question, if available
    * @return assQuestionGUI $questionGUI The question GUI instance
    * @access	public
    */
    public function createQuestionGUI($question_type, $question_id = -1): ?assQuestionGUI
    {
        if ((!$question_type) and ($question_id > 0)) {
            $question_type = $this->getQuestionType($question_id);
        }

        if (!strlen($question_type)) {
            return null;
        }

        $question_type_gui = $question_type . 'GUI';
        $question = new $question_type_gui();

        if ($question_id > 0) {
            $question->object->loadFromDb($question_id);

            $feedbackObjectClassname = assQuestion::getFeedbackClassNameByQuestionType($question_type);
            $question->object->feedbackOBJ = new $feedbackObjectClassname($question->object, $this->ctrl, $this->db, $this->lng);

            $assSettings = new ilSetting('assessment');
            $processLockerFactory = new ilAssQuestionProcessLockerFactory($assSettings, $this->db);
            $processLockerFactory->setQuestionId($question->object->getId());
            $processLockerFactory->setUserId($this->user->getId());
            $processLockerFactory->setAssessmentLogEnabled(ilObjAssessmentFolder::_enabledAssessmentLogging());
            $question->object->setProcessLocker($processLockerFactory->getLocker());
        }

        return $question;
    }

    /**
     * Creates an instance of a question with a given question id
     * @param int $question_id The question id
     * @throws InvalidArgumentException
     * @deprecated use assQuestion::_instanciateQuestion($question_id) instead
     */
    public static function _instanciateQuestion($question_id): ?assQuestion
    {
        if (strcmp((string) $question_id, "") !== 0) {
            return assQuestion::instantiateQuestion((int) $question_id);
        }

        return null;
    }

    /**
    * Move questions to another position
    *
    * @param array $move_questions An array with the question id's of the questions to move
    * @param integer $target_index The question id of the target position
    * @param integer $insert_mode 0, if insert before the target position, 1 if insert after the target position
    * @access public
    */
    public function moveQuestions($move_questions, $target_index, $insert_mode)
    {
        $this->questions = array_values($this->questions);
        $array_pos = array_search($target_index, $this->questions);
        if ($insert_mode == 0) {
            $part1 = array_slice($this->questions, 0, $array_pos);
            $part2 = array_slice($this->questions, $array_pos);
        } elseif ($insert_mode == 1) {
            $part1 = array_slice($this->questions, 0, $array_pos + 1);
            $part2 = array_slice($this->questions, $array_pos + 1);
        }
        foreach ($move_questions as $question_id) {
            if (!(array_search($question_id, $part1) === false)) {
                unset($part1[array_search($question_id, $part1)]);
            }
            if (!(array_search($question_id, $part2) === false)) {
                unset($part2[array_search($question_id, $part2)]);
            }
        }
        $part1 = array_values($part1);
        $part2 = array_values($part2);
        $new_array = array_values(array_merge($part1, $move_questions, $part2));
        $this->questions = [];
        $counter = 1;
        foreach ($new_array as $question_id) {
            $this->questions[$counter] = $question_id;
            $counter++;
        }
        $this->saveQuestionsToDb();
    }


    /**
    * Returns true if the starting time of a test is reached
    * A starting time is not available for self assessment tests
    *
    * @return boolean true if the starting time is reached, otherwise false
    * @access public
    */
    public function startingTimeReached(): bool
    {
        if ($this->isStartingTimeEnabled() && $this->getStartingTime() != 0) {
            $now = time();
            if ($now < $this->getStartingTime()) {
                return false;
            }
        }
        return true;
    }

    /**
    * Returns true if the ending time of a test is reached
    * An ending time is not available for self assessment tests
    *
    * @return boolean true if the ending time is reached, otherwise false
    * @access public
    */
    public function endingTimeReached(): bool
    {
        if ($this->isEndingTimeEnabled() && $this->getEndingTime() != 0) {
            $now = time();
            if ($now > $this->getEndingTime()) {
                return true;
            }
        }
        return false;
    }

    /**
    * Calculates the available questions for a test
    *
    * @access public
    */
    public function getAvailableQuestions($arr_filter, $completeonly = 0): array
    {
        $available_pools = array_keys(ilObjQuestionPool::_getAvailableQuestionpools(true, false, false, false, false));
        $available = "";
        if (count($available_pools)) {
            $available = " AND " . $this->db->in('qpl_questions.obj_fi', $available_pools, false, 'integer');
        } else {
            return [];
        }
        if ($completeonly) {
            $available .= " AND qpl_questions.complete = " . $this->db->quote("1", 'text');
        }

        $where = "";
        if (is_array($arr_filter)) {
            if (array_key_exists('title', $arr_filter) && strlen($arr_filter['title'])) {
                $where .= " AND " . $this->db->like('qpl_questions.title', 'text', "%%" . $arr_filter['title'] . "%%");
            }
            if (array_key_exists('description', $arr_filter) && strlen($arr_filter['description'])) {
                $where .= " AND " . $this->db->like('qpl_questions.description', 'text', "%%" . $arr_filter['description'] . "%%");
            }
            if (array_key_exists('author', $arr_filter) && strlen($arr_filter['author'])) {
                $where .= " AND " . $this->db->like('qpl_questions.author', 'text', "%%" . $arr_filter['author'] . "%%");
            }
            if (array_key_exists('type', $arr_filter) && strlen($arr_filter['type'])) {
                $where .= " AND qpl_qst_type.type_tag = " . $this->db->quote($arr_filter['type'], 'text');
            }
            if (array_key_exists('qpl', $arr_filter) && strlen($arr_filter['qpl'])) {
                $where .= " AND " . $this->db->like('object_data.title', 'text', "%%" . $arr_filter['qpl'] . "%%");
            }
        }

        $original_ids = &$this->getExistingQuestions();
        $original_clause = " qpl_questions.original_id IS NULL";
        if (count($original_ids)) {
            $original_clause = " qpl_questions.original_id IS NULL AND " . $this->db->in('qpl_questions.question_id', $original_ids, true, 'integer');
        }

        $query_result = $this->db->query("
			SELECT		qpl_questions.*, qpl_questions.tstamp,
						qpl_qst_type.type_tag, qpl_qst_type.plugin, qpl_qst_type.plugin_name,
						object_data.title parent_title
			FROM		qpl_questions, qpl_qst_type, object_data
			WHERE $original_clause $available
			AND object_data.obj_id = qpl_questions.obj_fi
			AND qpl_questions.tstamp > 0
			AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id
			$where
		");
        $rows = [];

        if ($query_result->numRows()) {
            while ($row = $this->db->fetchAssoc($query_result)) {
                $row = ilAssQuestionType::completeMissingPluginName($row);

                if (!$row['plugin']) {
                    $row[ 'ttype' ] = $this->lng->txt($row[ "type_tag" ]);

                    $rows[] = $row;
                    continue;
                }

                $plugin = $this->component_repository->getPluginByName($row['plugin_name']);
                if (!$plugin->isActive()) {
                    continue;
                }

                $pl = $this->component_factory->getPlugin($plugin->getId());
                $row[ 'ttype' ] = $pl->getQuestionTypeTranslation();

                $rows[] = $row;
            }
        }
        return $rows;
    }

    /**
     * Receives parameters from a QTI parser and creates a valid ILIAS test object
     * @param ilQTIAssessment $assessment
     */
    public function fromXML(ilQTIAssessment $assessment)
    {
        ilSession::clear('import_mob_xhtml');

        $this->saveToDb(true);

        $main_settings = $this->getMainSettings();
        $general_settings = $main_settings->getGeneralSettings();
        $introduction_settings = $main_settings->getIntroductionSettings();
        $access_settings = $main_settings->getAccessSettings();
        $test_behaviour_settings = $main_settings->getTestBehaviourSettings();
        $question_behaviour_settings = $main_settings->getQuestionBehaviourSettings();
        $participant_functionality_settings = $main_settings->getParticipantFunctionalitySettings();
        $finishing_settings = $main_settings->getFinishingSettings();
        $additional_settings = $main_settings->getAdditionalSettings();

        $introduction_settings = $introduction_settings->withIntroductionEnabled(false);
        foreach ($assessment->objectives as $objectives) {
            foreach ($objectives->materials as $material) {
                $introduction_settings = $this->addIntroductionToSettingsFromImport(
                    $introduction_settings,
                    $this->qtiMaterialToArray($material)
                );
            }
        }

        if ($assessment->getPresentationMaterial()
            && $assessment->getPresentationMaterial()->getFlowMat(0)
            && $assessment->getPresentationMaterial()->getFlowMat(0)->getMaterial(0)) {
            $finishing_settings = $this->addConcludingRemarksToSettingsFromImport(
                $finishing_settings,
                $this->qtiMaterialToArray(
                    $assessment->getPresentationMaterial()->getFlowMat(0)->getMaterial(0)
                )
            );
        }

        $score_settings = $this->getScoreSettings();
        $scoring_settings = $score_settings->getScoringSettings();
        $gamification_settings = $score_settings->getGamificationSettings();
        $result_summary_settings = $score_settings->getResultSummarySettings();
        $result_details_settings = $score_settings->getResultDetailsSettings();
        foreach ($assessment->qtimetadata as $metadata) {
            switch ($metadata["label"]) {
                case "solution_details":
                    $result_details_settings = $result_details_settings->withShowPassDetails((bool) $metadata["entry"]);
                    break;
                case "show_solution_list_comparison":
                    $result_details_settings = $result_details_settings->withShowSolutionListComparison((bool) $metadata["entry"]);
                    break;
                case "print_bs_with_res":
                    $result_details_settings = $result_details_settings->withShowSolutionListComparison((bool) $metadata["entry"]);
                    break;
                case "author":
                    $this->saveAuthorToMetadata($metadata["entry"]);
                    break;
                case "nr_of_tries":
                    $test_behaviour_settings = $test_behaviour_settings->withNumberOfTries((int) $metadata["entry"]);
                    break;
                case 'block_after_passed':
                    $test_behaviour_settings = $test_behaviour_settings->withBlockAfterPassedEnabled((bool) $metadata['entry']);
                    break;
                case "pass_waiting":
                    $test_behaviour_settings = $test_behaviour_settings->withPassWaiting($metadata["entry"]);
                    break;
                case "kiosk":
                    $test_behaviour_settings = $test_behaviour_settings->withKioskMode((int) $metadata["entry"]);
                    break;
                case 'show_introduction':
                    $introduction_settings = $introduction_settings->withIntroductionEnabled((bool) $metadata['entry']);
                    // no break
                case "showfinalstatement":
                case 'show_concluding_remarks':
                    $finishing_settings = $finishing_settings->withConcludingRemarksEnabled((bool) $metadata["entry"]);
                    break;
                case "highscore_enabled":
                    $gamification_settings = $gamification_settings->withHighscoreEnabled((bool) $metadata["entry"]);
                    break;

                case "highscore_anon":
                    $gamification_settings = $gamification_settings->withHighscoreAnon((bool) $metadata["entry"]);
                    break;

                case "highscore_achieved_ts":
                    $gamification_settings = $gamification_settings->withHighscoreAchievedTS((bool) $metadata["entry"]);
                    break;

                case "highscore_score":
                    $gamification_settings = $gamification_settings->withHighscoreScore((bool) $metadata["entry"]);
                    break;

                case "highscore_percentage":
                    $gamification_settings = $gamification_settings->withHighscorePercentage((bool) $metadata["entry"]);
                    break;

                case "highscore_hints":
                    $gamification_settings = $gamification_settings->withHighscoreHints((bool) $metadata["entry"]);
                    break;

                case "highscore_wtime":
                    $gamification_settings = $gamification_settings->withHighscoreWTime((bool) $metadata["entry"]);
                    break;

                case "highscore_own_table":
                    $gamification_settings = $gamification_settings->withHighscoreOwnTable((bool) $metadata["entry"]);
                    break;

                case "highscore_top_table":
                    $gamification_settings = $gamification_settings->withHighscoreTopTable((bool) $metadata["entry"]);
                    break;

                case "highscore_top_num":
                    $gamification_settings = $gamification_settings->withHighscoreTopNum((int) $metadata["entry"]);
                    break;
                case "use_previous_answers":
                    $participant_functionality_settings = $participant_functionality_settings->withUsePreviousAnswerAllowed((bool) $metadata["entry"]);
                    break;
                case "title_output":
                    $question_behaviour_settings = $question_behaviour_settings->withQuestionTitleOutputMode((int) $metadata["entry"]);
                    break;
                case "question_set_type":
                    $general_settings = $general_settings->withQuestionSetType($metadata["entry"]);
                    break;
                case "anonymity":
                    $general_settings = $general_settings->withAnonymity((bool) $metadata["entry"]);
                    break;
                case "results_presentation":
                    $result_details_settings = $result_details_settings->withResultsPresentation((int) $metadata["entry"]);
                    break;
                case "reset_processing_time":
                    $test_behaviour_settings = $test_behaviour_settings->withResetProcessingTime((bool) $metadata["entry"]);
                    break;
                case "answer_feedback_points":
                    $question_behaviour_settings = $question_behaviour_settings->withInstantFeedbackPointsEnabled((bool) $metadata["entry"]);
                    break;
                case "answer_feedback":
                    $question_behaviour_settings = $question_behaviour_settings->withInstantFeedbackGenericEnabled((bool) $metadata["entry"]);
                    break;
                case 'instant_feedback_specific':
                    $question_behaviour_settings = $question_behaviour_settings->withInstantFeedbackSpecificEnabled((bool) $metadata['entry']);
                    break;
                case "instant_verification":
                    $question_behaviour_settings = $question_behaviour_settings->withInstantFeedbackSolutionEnabled((bool) $metadata["entry"]);
                    break;
                case "force_instant_feedback":
                    $question_behaviour_settings = $question_behaviour_settings->withForceInstantFeedbackOnNextQuestion((bool) $metadata["entry"]);
                    break;
                case "follow_qst_answer_fixation":
                    $question_behaviour_settings = $question_behaviour_settings->withLockAnswerOnNextQuestionEnabled((bool) $metadata["entry"]);
                    break;
                case "instant_feedback_answer_fixation":
                    $question_behaviour_settings = $question_behaviour_settings->withLockAnswerOnInstantFeedbackEnabled((bool) $metadata["entry"]);
                    break;
                case "show_cancel":
                case "suspend_test_allowed":
                    $participant_functionality_settings = $participant_functionality_settings->withSuspendTestAllowed((bool) $metadata["entry"]);
                    break;
                case "sequence_settings":
                    $participant_functionality_settings = $participant_functionality_settings->withPostponedQuestionsMoveToEnd((bool) $metadata["entry"]);
                    break;
                case "show_marker":
                    $participant_functionality_settings = $participant_functionality_settings->withQuestionMarkingEnabled((bool) $metadata["entry"]);
                    break;
                case "fixed_participants":
                    $access_settings = $access_settings->withFixedParticipants((bool) $metadata["entry"]);
                    break;
                case "score_reporting":
                    $result_summary_settings = $result_summary_settings->withScoreReporting((int) $metadata["entry"]);
                    break;
                case "shuffle_questions":
                    $question_behaviour_settings = $question_behaviour_settings->withShuffleQuestions((bool) $metadata["entry"]);
                    break;
                case "count_system":
                    $scoring_settings = $scoring_settings->withCountSystem((int) $metadata["entry"]);
                    break;
                case "mailnotification":
                    $finishing_settings = $finishing_settings->withMailNotificationContentType((int) $metadata["entry"]);
                    break;
                case "mailnottype":
                    $finishing_settings = $finishing_settings->withAlwaysSendMailNotification((bool) $metadata["entry"]);
                    break;
                case "exportsettings":
                    $result_details_settings = $result_details_settings->withExportSettings((int) $metadata["entry"]);
                    break;
                case "score_cutting":
                    $scoring_settings = $scoring_settings->withScoreCutting((int) $metadata["entry"]);
                    break;
                case "password":
                    $access_settings = $access_settings->withPasswordEnabled(
                        $metadata["entry"] !== null && $metadata["entry"] !== ''
                    )->withPassword($metadata["entry"]);
                    break;
                case "pass_scoring":
                    $scoring_settings = $scoring_settings->withPassScoring((int) $metadata["entry"]);
                    break;
                case 'pass_deletion_allowed':
                    $result_summary_settings = $result_summary_settings->withPassDeletionAllowed((bool) $metadata["entry"]);
                    break;
                case "usr_pass_overview_mode":
                    $participant_functionality_settings = $participant_functionality_settings->withUsrPassOverviewMode((int) $metadata["entry"]);
                    break;
                case "question_list":
                    $participant_functionality_settings = $participant_functionality_settings->withQuestionListEnabled((bool) $metadata["entry"]);
                    break;

                case "reporting_date":
                    $reporting_date = $this->buildDateTimeImmutableFromPeriod($metadata['entry']);
                    if ($reporting_date !== null) {
                        $result_summary_settings = $result_summary_settings->withReportingDate($reporting_date);
                    }
                    break;
                case 'enable_processing_time':
                    $test_behaviour_settings = $test_behaviour_settings->withProcessingTimeEnabled((bool) $metadata['entry']);
                    break;
                case "processing_time":
                    $test_behaviour_settings = $test_behaviour_settings->withProcessingTime($metadata['entry']);
                    break;
                case "starting_time":
                    $starting_time = $this->buildDateTimeImmutableFromPeriod($metadata['entry']);
                    if ($starting_time !== null) {
                        $access_settings = $access_settings->withStartTime($starting_time)
                            ->withStartTimeEnabled(true);
                    }
                    break;
                case "ending_time":
                    $ending_time = $this->buildDateTimeImmutableFromPeriod($metadata['entry']);
                    if ($ending_time !== null) {
                        $access_settings = $access_settings->withEndTime($ending_time)
                            ->withStartTimeEnabled(true);
                    }
                    break;
                case "enable_examview":
                    $finishing_settings = $finishing_settings->withShowAnswerOverview((bool) $metadata["entry"]);
                    break;
                case 'redirection_mode':
                    $finishing_settings = $finishing_settings->withRedirectionMode((int) $metadata['entry']);
                    break;
                case 'redirection_url':
                    $finishing_settings = $finishing_settings->withRedirectionUrl($metadata['entry']);
                    break;
                case 'examid_in_test_pass':
                    $test_behaviour_settings = $test_behaviour_settings->withExamIdInTestPassEnabled((bool) $metadata['entry']);
                    break;
                case 'examid_in_test_res':
                    $result_details_settings = $result_details_settings->withShowExamIdInTestResults((bool) $metadata["entry"]);
                    break;
                case 'skill_service':
                    $additional_settings = $additional_settings->withSkillsServiceEnabled((bool) $metadata['entry']);
                    break;
                case 'show_grading_status':
                    $result_summary_settings = $result_summary_settings->withShowGradingStatusEnabled((bool) $metadata["entry"]);
                    break;
                case 'show_grading_mark':
                    $result_summary_settings = $result_summary_settings->withShowGradingMarkEnabled((bool) $metadata["entry"]);
                    break;
                case 'activation_limited':
                    $this->setActivationLimited((bool) $metadata['entry']);
                    break;
                case 'activation_start_time':
                    $this->setActivationStartingTime($metadata['entry'] !== 'null' ? (int) $metadata['entry'] : null);
                    break;
                case 'activation_end_time':
                    $this->setActivationEndingTime($metadata['entry'] !== 'null' ? (int) $metadata['entry'] : null);
                    break;
                case 'activation_visibility':
                    $this->setActivationVisibility($metadata['entry']);
                    break;
                case 'autosave':
                    $question_behaviour_settings = $question_behaviour_settings->withAutosaveEnabled((bool) $metadata['entry']);
                    break;
                case 'autosave_ival':
                    $question_behaviour_settings = $question_behaviour_settings->withAutosaveInterval((int) $metadata['entry']);
                    break;
                case 'offer_question_hints':
                    $question_behaviour_settings = $question_behaviour_settings->withQuestionHintsEnabled((bool) $metadata['entry']);
                    break;
                case 'obligations_enabled':
                    $question_behaviour_settings = $question_behaviour_settings->withCompulsoryQuestionsEnabled((bool) $metadata['entry']);
                    break;
                case 'show_summary':
                    $participant_functionality_settings = $participant_functionality_settings->withQuestionListEnabled(($metadata['entry'] & 1) > 0)
                        ->withUsrPassOverviewMode((int) $metadata['entry']);
            }
            if (preg_match("/mark_step_\d+/", $metadata["label"])) {
                $xmlmark = $metadata["entry"];
                preg_match("/<short>(.*?)<\/short>/", $xmlmark, $matches);
                $mark_short = $matches[1];
                preg_match("/<official>(.*?)<\/official>/", $xmlmark, $matches);
                $mark_official = $matches[1];
                preg_match("/<percentage>(.*?)<\/percentage>/", $xmlmark, $matches);
                $mark_percentage = (float) $matches[1];
                preg_match("/<passed>(.*?)<\/passed>/", $xmlmark, $matches);
                $mark_passed = (int) $matches[1];
                $this->mark_schema->addMarkStep($mark_short, $mark_official, $mark_percentage, $mark_passed);
            }
        }

        $this->saveToDb();
        $this->getObjectProperties()->storePropertyTitleAndDescription(
            $this->getObjectProperties()->getPropertyTitleAndDescription()
                ->withTitle($assessment->getTitle())
                ->withDescription($assessment->getComment())
        );
        $this->addToNewsOnOnline(false, $this->getObjectProperties()->getPropertyIsOnline()->getIsOnline());
        $main_settings = $main_settings
            ->withGeneralSettings($general_settings)
            ->withIntroductionSettings($introduction_settings)
            ->withAccessSettings($access_settings)
            ->withParticipantFunctionalitySettings($participant_functionality_settings)
            ->withTestBehaviourSettings($test_behaviour_settings)
            ->withQuestionBehaviourSettings($question_behaviour_settings)
            ->withFinishingSettings($finishing_settings)
            ->withAdditionalSettings($additional_settings);
        $this->getMainSettingsRepository()->store($main_settings);
        $this->main_settings = $main_settings;

        $score_settings = $score_settings
                ->withGamificationSettings($gamification_settings)
                ->withScoringSettings($scoring_settings)
                ->withResultDetailsSettings($result_details_settings)
                ->withResultSummarySettings($result_summary_settings);
        $this->getScoreSettingsRepository()->store($score_settings);
        $this->score_settings = $score_settings;
        $this->loadFromDb();
    }

    private function addIntroductionToSettingsFromImport(ilObjTestSettingsIntroduction $settings, array $material)
    {
        $text = $material['text'];
        $mobs = $material['mobs'];
        if (str_starts_with($text, '<PageObject>')) {
            $text = $this->retrieveMobsFromPageImports($text, $mobs);
            $text = $this->retrieveFilesFromPageImports($text);
            $page_object = new ilTestPage();
            $page_object->setParentId($this->getId());
            $page_object->setXMLContent($text);
            $new_page_id = $page_object->createPageWithNextId();
            return $settings->withIntroductionPageId($new_page_id);
        }

        $text = $this->retrieveMobsFromLegacyImports($text, $mobs);

        return new ilObjTestSettingsIntroduction(
            $settings->getTestId(),
            strlen($text) > 0,
            $text
        );
    }

    private function addConcludingRemarksToSettingsFromImport(ilObjTestSettingsFinishing $settings, array $material)
    {
        $text = $material['text'];
        $mobs = $material['mobs'];
        if (str_starts_with($text, '<PageObject>')) {
            $text = $this->retrieveMobsFromPageImports($text, $mobs);
            $text = $this->retrieveFilesFromPageImports($text);
            $page_object = new ilTestPage();
            $page_object->setParentId($this->getId());
            $page_object->setXMLContent($text);
            $new_page_id = $page_object->createPageWithNextId();
            return $settings->withConcludingRemarksPageId($new_page_id);
        }

        $text = $this->retrieveMobsFromLegacyImports($text, $mobs);

        return new ilObjTestSettingsFinishing(
            $settings->getTestId(),
            $settings->getShowAnswerOverview(),
            strlen($text) > 0,
            $text,
            null,
            $settings->getRedirectionMode(),
            $settings->getRedirectionUrl(),
            $settings->getMailNotificationContentType(),
            $settings->getAlwaysSendMailNotification()
        );
    }

    private function retrieveMobsFromPageImports(string $text, array $mobs): string
    {
        foreach ($mobs as $mob) {
            $importfile = ilObjTest::_getImportDirectory() . '/' . ilSession::get('tst_import_subdir') . '/' . $mob['uri'];
            if (file_exists($importfile)) {
                $media_object = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
                ilObjMediaObject::_saveUsage($media_object->getId(), 'tst:gp', $this->getId());
                $text = str_replace($mob['mob'], 'il__mob_' . (string) $media_object->getId(), $text);
            }
        }
        return $text;
    }

    private function retrieveFilesFromPageImports(string $text): string
    {
        preg_match_all('/il_(\d+)_file_(\d+)/', $text, $matches);
        foreach ($matches[0] as $match) {
            $source_dir = ilObjTest::_getImportDirectory() . '/' . ilSession::get('tst_import_subdir') . '/objects/' . $match;
            $files = scandir($source_dir, SCANDIR_SORT_DESCENDING);
            if ($files !== false && $files !== [] && is_file($source_dir . '/' . $files[0])) {
                $file = fopen($source_dir . '/' . $files[0], 'rb');
                $file_stream = Streams::ofResource($file);
                $file_obj = new ilObjFile();
                $file_id = $file_obj->create();
                $file_obj->appendStream($file_stream, $files[0]);
                $text = str_replace($match, "il__file_{$file_id}", $text);
            }
        }
        return $text;
    }

    private function retrieveMobsFromLegacyImports(string $text, array $mobs): string
    {
        foreach ($mobs as $mob) {
            $importfile = ilObjTest::_getImportDirectory() . '/' . ilSession::get('tst_import_subdir') . '/' . $mob['uri'];
            if (file_exists($importfile)) {
                $media_object = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
                ilObjMediaObject::_saveUsage($media_object->getId(), 'tst:html', $this->getId());
                $text = ilRTE::_replaceMediaObjectImageSrc(
                    str_replace(
                        'src="' . $mob['mob'] . '"',
                        'src="' . 'il_' . IL_INST_ID . '_mob_' . $media_object->getId() . '"',
                        $text
                    ),
                    1
                );
            }
        }
        return $text;
    }

    /**
     * Returns a QTI xml representation of the test
     *
     * @return string The QTI xml representation of the test
     */
    public function toXML(): string
    {
        $main_settings = $this->getMainSettings();
        $a_xml_writer = new ilXmlWriter();
        // set xml header
        $a_xml_writer->xmlHeader();
        $a_xml_writer->xmlSetDtdDef("<!DOCTYPE questestinterop SYSTEM \"ims_qtiasiv1p2p1.dtd\">");
        $a_xml_writer->xmlStartTag("questestinterop");

        $attrs = [
            "ident" => "il_" . IL_INST_ID . "_tst_" . $this->getTestId(),
            "title" => $this->getTitle()
        ];
        $a_xml_writer->xmlStartTag("assessment", $attrs);
        // add qti comment
        $a_xml_writer->xmlElement("qticomment", null, $this->getDescription());

        // add qti duration
        if ($main_settings->getTestBehaviourSettings()->getProcessingTimeEnabled()) {
            $processing_time_array = $this->getProcessingTimeAsArray();
            $a_xml_writer->xmlElement(
                "duration",
                null,
                sprintf(
                    "P0Y0M0DT%dH%dM%dS",
                    $processing_time_array['hh'],
                    $processing_time_array['mm'],
                    $processing_time_array['ss']
                )
            );
        }

        // add the rest of the preferences in qtimetadata tags, because there is no correspondent definition in QTI
        $a_xml_writer->xmlStartTag("qtimetadata");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "ILIAS_VERSION");
        $a_xml_writer->xmlElement("fieldentry", null, ILIAS_VERSION);
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // anonymity
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "anonymity");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", $main_settings->getGeneralSettings()->getAnonymity()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // question set type (fixed, random, ...)
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "question_set_type");
        $a_xml_writer->xmlElement("fieldentry", null, $main_settings->getGeneralSettings()->getQuestionSetType());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // sequence settings
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "sequence_settings");
        $a_xml_writer->xmlElement("fieldentry", null, $main_settings->getParticipantFunctionalitySettings()->getPostponedQuestionsMoveToEnd());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // author
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "author");
        $a_xml_writer->xmlElement("fieldentry", null, $this->getAuthor());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // reset processing time
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "reset_processing_time");
        $a_xml_writer->xmlElement("fieldentry", null, $main_settings->getTestBehaviourSettings()->getResetProcessingTime());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // count system
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "count_system");
        $a_xml_writer->xmlElement("fieldentry", null, $this->getCountSystem());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // multiple choice scoring
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "score_cutting");
        $a_xml_writer->xmlElement("fieldentry", null, $this->getScoreCutting());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // multiple choice scoring
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "password");
        $a_xml_writer->xmlElement("fieldentry", null, $main_settings->getAccessSettings()->getPassword() ?? '');
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // pass scoring
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "pass_scoring");
        $a_xml_writer->xmlElement("fieldentry", null, $this->getPassScoring());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        $a_xml_writer->xmlStartTag('qtimetadatafield');
        $a_xml_writer->xmlElement('fieldlabel', null, 'pass_deletion_allowed');
        $a_xml_writer->xmlElement('fieldentry', null, (int) $this->isPassDeletionAllowed());
        $a_xml_writer->xmlEndTag('qtimetadatafield');

        // score reporting date
        if ($this->getScoreSettings()->getResultSummarySettings()->getReportingDate() !== null) {
            $a_xml_writer->xmlStartTag("qtimetadatafield");
            $a_xml_writer->xmlElement("fieldlabel", null, "reporting_date");
            $reporting_date = $this->buildPeriodFromFormatedDateString(
                $this->getScoreSettings()->getResultSummarySettings()->getReportingDate()->format('Y-m-d H:i:s')
            );
            $a_xml_writer->xmlElement("fieldentry", null, $reporting_date);
            $a_xml_writer->xmlEndTag("qtimetadatafield");
        }
        // number of tries
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "nr_of_tries");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", $main_settings->getTestBehaviourSettings()->getNumberOfTries()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // number of tries
        $a_xml_writer->xmlStartTag('qtimetadatafield');
        $a_xml_writer->xmlElement('fieldlabel', null, 'block_after_passed');
        $a_xml_writer->xmlElement('fieldentry', null, (int) $main_settings->getTestBehaviourSettings()->getBlockAfterPassedEnabled());
        $a_xml_writer->xmlEndTag('qtimetadatafield');

        // pass_waiting
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "pass_waiting");
        $a_xml_writer->xmlElement("fieldentry", null, $main_settings->getTestBehaviourSettings()->getPassWaiting());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // kiosk
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "kiosk");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", $main_settings->getTestBehaviourSettings()->getKioskMode()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");


        //redirection_mode
        $a_xml_writer->xmlStartTag('qtimetadatafield');
        $a_xml_writer->xmlElement("fieldlabel", null, "redirection_mode");
        $a_xml_writer->xmlElement("fieldentry", null, $main_settings->getFinishingSettings()->getRedirectionMode());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        //redirection_url
        $a_xml_writer->xmlStartTag('qtimetadatafield');
        $a_xml_writer->xmlElement("fieldlabel", null, "redirection_url");
        $a_xml_writer->xmlElement("fieldentry", null, $main_settings->getFinishingSettings()->getRedirectionUrl());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // use previous answers
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "use_previous_answers");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $main_settings->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // hide title points
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "title_output");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", $main_settings->getQuestionBehaviourSettings()->getQuestionTitleOutputMode()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // results presentation
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "results_presentation");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", $this->getScoreSettings()->getResultDetailsSettings()->getResultsPresentation()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // examid in test pass
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "examid_in_test_pass");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", $main_settings->getTestBehaviourSettings()->getExamIdInTestPassEnabled()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // examid in kiosk
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "examid_in_test_res");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", $this->getScoreSettings()->getResultDetailsSettings()->getShowExamIdInTestResults()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // solution details
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "usr_pass_overview_mode");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", $main_settings->getParticipantFunctionalitySettings()->getUsrPassOverviewMode()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // solution details
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "score_reporting");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", $this->getScoreSettings()->getResultSummarySettings()->getScoreReporting()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "show_solution_list_comparison");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $this->score_settings->getResultDetailsSettings()->getShowSolutionListComparison());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // solution details
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "instant_verification");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", (int) $main_settings->getQuestionBehaviourSettings()->getInstantFeedbackSolutionEnabled()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // generic feedback
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "answer_feedback");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", (int) $main_settings->getQuestionBehaviourSettings()->getInstantFeedbackGenericEnabled()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // answer specific feedback
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "instant_feedback_specific");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", (int) $main_settings->getQuestionBehaviourSettings()->getInstantFeedbackSpecificEnabled()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // answer specific feedback of reached points
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "answer_feedback_points");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", (int) $main_settings->getQuestionBehaviourSettings()->getInstantFeedbackPointsEnabled()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // followup question previous answer freezing
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "follow_qst_answer_fixation");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $main_settings->getQuestionBehaviourSettings()->getLockAnswerOnNextQuestionEnabled());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // instant response answer freezing
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "instant_feedback_answer_fixation");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $main_settings->getQuestionBehaviourSettings()->getLockAnswerOnInstantFeedbackEnabled());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // instant response forced
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "force_instant_feedback");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $main_settings->getQuestionBehaviourSettings()->getForceInstantFeedbackOnNextQuestion());
        $a_xml_writer->xmlEndTag("qtimetadatafield");


        // highscore
        $highscore_metadata = [
            'highscore_enabled' => ['value' => $this->getHighscoreEnabled()],
            'highscore_anon' => ['value' => $this->getHighscoreAnon()],
            'highscore_achieved_ts' => ['value' => $this->getHighscoreAchievedTS()],
            'highscore_score' => ['value' => $this->getHighscoreScore()],
            'highscore_percentage' => ['value' => $this->getHighscorePercentage()],
            'highscore_hints' => ['value' => $this->getHighscoreHints()],
            'highscore_wtime' => ['value' => $this->getHighscoreWTime()],
            'highscore_own_table' => ['value' => $this->getHighscoreOwnTable()],
            'highscore_top_table' => ['value' => $this->getHighscoreTopTable()],
            'highscore_top_num' => ['value' => $this->getHighscoreTopNum()],
        ];
        foreach ($highscore_metadata as $label => $data) {
            $a_xml_writer->xmlStartTag("qtimetadatafield");
            $a_xml_writer->xmlElement("fieldlabel", null, $label);
            $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", $data['value']));
            $a_xml_writer->xmlEndTag("qtimetadatafield");
        }

        // show cancel
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "suspend_test_allowed");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", (int) $main_settings->getParticipantFunctionalitySettings()->getSuspendTestAllowed()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // show marker
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "show_marker");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", (int) $main_settings->getParticipantFunctionalitySettings()->getQuestionMarkingEnabled()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // fixed participants
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "fixed_participants");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", (int) $main_settings->getAccessSettings()->getFixedParticipants()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // show final statement
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "show_introduction");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", (int) $main_settings->getIntroductionSettings()->getIntroductionEnabled()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // show final statement
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "show_concluding_remarks");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", (int) $main_settings->getFinishingSettings()->getConcludingRemarksEnabled()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // mail notification
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "mailnotification");
        $a_xml_writer->xmlElement("fieldentry", null, $main_settings->getFinishingSettings()->getMailNotificationContentType());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // mail notification type
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "mailnottype");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $main_settings->getFinishingSettings()->getAlwaysSendMailNotification());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // export settings
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "exportsettings");
        $a_xml_writer->xmlElement("fieldentry", null, $this->getExportSettings());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // shuffle questions
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "shuffle_questions");
        $a_xml_writer->xmlElement("fieldentry", null, sprintf("%d", (int) $main_settings->getQuestionBehaviourSettings()->getShuffleQuestions()));
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // processing time
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "processing_time");
        $a_xml_writer->xmlElement("fieldentry", null, $main_settings->getTestBehaviourSettings()->getProcessingTime());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // enable_examview
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "enable_examview");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $main_settings->getFinishingSettings()->getShowAnswerOverview());
        $a_xml_writer->xmlEndTag("qtimetadatafield");


        // skill_service
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "skill_service");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $main_settings->getAdditionalSettings()->getSkillsServiceEnabled());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // add qti assessmentcontrol
        if ($this->getInstantFeedbackSolution() == 1) {
            $attrs = [
                "solutionswitch" => "Yes"
            ];
        } else {
            $attrs = null;
        }
        $a_xml_writer->xmlElement("assessmentcontrol", $attrs, null);

        // show_grading_status
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "show_grading_status");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $this->isShowGradingStatusEnabled());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // show_grading_mark
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "show_grading_mark");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $this->isShowGradingMarkEnabled());
        $a_xml_writer->xmlEndTag("qtimetadatafield");


        // starting time
        if ($this->getStartingTime()) {
            $a_xml_writer->xmlStartTag("qtimetadatafield");
            $a_xml_writer->xmlElement("fieldlabel", null, "starting_time");
            $backward_compatibility_format = $this->buildIso8601PeriodFromUnixtimeForExportCompatibility($this->getStartingTime());
            $a_xml_writer->xmlElement("fieldentry", null, $backward_compatibility_format);
            $a_xml_writer->xmlEndTag("qtimetadatafield");
        }
        // ending time
        if ($this->getEndingTime()) {
            $a_xml_writer->xmlStartTag("qtimetadatafield");
            $a_xml_writer->xmlElement("fieldlabel", null, "ending_time");
            $backward_compatibility_format = $this->buildIso8601PeriodFromUnixtimeForExportCompatibility($this->getEndingTime());
            $a_xml_writer->xmlElement("fieldentry", null, $backward_compatibility_format);
            $a_xml_writer->xmlEndTag("qtimetadatafield");
        }


        //activation_limited
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "activation_limited");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $this->isActivationLimited());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        //activation_start_time
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "activation_start_time");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $this->getActivationStartingTime());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        //activation_end_time
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "activation_end_time");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $this->getActivationEndingTime());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        //activation_visibility
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "activation_visibility");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $this->getActivationVisibility());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // autosave
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "autosave");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $main_settings->getQuestionBehaviourSettings()->getAutosaveEnabled());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // autosave_ival
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "autosave_ival");
        $a_xml_writer->xmlElement("fieldentry", null, $main_settings->getQuestionBehaviourSettings()->getAutosaveInterval());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        //offer_question_hints
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "offer_question_hints");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $main_settings->getQuestionBehaviourSettings()->getQuestionHintsEnabled());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        //instant_feedback_specific
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "instant_feedback_specific");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $main_settings->getQuestionBehaviourSettings()->getInstantFeedbackSpecificEnabled());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        //instant_feedback_answer_fixation
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "instant_feedback_answer_fixation");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $main_settings->getQuestionBehaviourSettings()->getLockAnswerOnInstantFeedbackEnabled());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        //obligations_enabled
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "obligations_enabled");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $main_settings->getQuestionBehaviourSettings()->getCompulsoryQuestionsEnabled());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        //enable_processing_time
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "enable_processing_time");
        $a_xml_writer->xmlElement("fieldentry", null, (int) $main_settings->getTestBehaviourSettings()->getProcessingTimeEnabled());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        foreach ($this->mark_schema->mark_steps as $index => $mark) {
            // mark steps
            $a_xml_writer->xmlStartTag("qtimetadatafield");
            $a_xml_writer->xmlElement("fieldlabel", null, "mark_step_$index");
            $a_xml_writer->xmlElement("fieldentry", null, sprintf(
                "<short>%s</short><official>%s</official><percentage>%.2f</percentage><passed>%d</passed>",
                $mark->getShortName(),
                $mark->getOfficialName(),
                $mark->getMinimumLevel(),
                $mark->getPassed()
            ));
            $a_xml_writer->xmlEndTag("qtimetadatafield");
        }
        $a_xml_writer->xmlEndTag("qtimetadata");

        $page_id = $main_settings->getIntroductionSettings()->getIntroductionPageId();
        $introduction = $page_id !== null
            ? (new ilTestPage($page_id))->getXMLContent()
            : ilRTE::_replaceMediaObjectImageSrc($this->getIntroduction(), 0);

        // add qti objectives
        $a_xml_writer->xmlStartTag("objectives");
        $this->addQTIMaterial($a_xml_writer, $page_id, $introduction);
        $a_xml_writer->xmlEndTag("objectives");

        // add qti assessmentcontrol
        if ($this->getInstantFeedbackSolution() == 1) {
            $attrs = [
                "solutionswitch" => "Yes"
            ];
        } else {
            $attrs = null;
        }
        $a_xml_writer->xmlElement("assessmentcontrol", $attrs, null);

        if (strlen($this->getFinalStatement())) {
            $page_id = $main_settings->getFinishingSettings()->getConcludingRemarksPageId();
            $concluding_remarks = $page_id !== null
                ? (new ilTestPage($page_id))->getXMLContent()
                : ilRTE::_replaceMediaObjectImageSrc($this->getFinalStatement());
            // add qti presentation_material
            $a_xml_writer->xmlStartTag("presentation_material");
            $a_xml_writer->xmlStartTag("flow_mat");
            $this->addQTIMaterial($a_xml_writer, $page_id, $concluding_remarks);
            $a_xml_writer->xmlEndTag("flow_mat");
            $a_xml_writer->xmlEndTag("presentation_material");
        }

        $attrs = [
            "ident" => "1"
        ];
        $a_xml_writer->xmlElement("section", $attrs, null);
        $a_xml_writer->xmlEndTag("assessment");
        $a_xml_writer->xmlEndTag("questestinterop");

        $xml = $a_xml_writer->xmlDumpMem(false);
        return $xml;
    }

    /**
     * @param $unix_timestamp
     * @return string
     */
    protected function buildIso8601PeriodFromUnixtimeForExportCompatibility($unix_timestamp): string
    {
        $date_time_unix = new ilDateTime($unix_timestamp, IL_CAL_UNIX);
        $date_time = $date_time_unix->get(IL_CAL_DATETIME);
        return $this->buildPeriodFromFormatedDateString($date_time);
    }

    protected function buildPeriodFromFormatedDateString(string $date_time): string
    {
        preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $date_time, $matches);
        return sprintf("P%dY%dM%dDT%dH%dM%dS", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]);
    }

    protected function buildDateTimeImmutableFromPeriod(?string $period): ?DateTimeImmutable
    {
        if ($period === null) {
            return null;
        }
        if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $period, $matches)) {
            return new DateTimeImmutable(
                sprintf(
                    "%02d-%02d-%02d %02d:%02d:%02d",
                    $matches[1],
                    $matches[2],
                    $matches[3],
                    $matches[4],
                    $matches[5],
                    $matches[6]
                ),
                new \DateTimeZone('UTC')
            );
        }
        return null;
    }

    /**
    * export pages of test to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportPagesXML(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog): void
    {
        $this->mob_ids = [];

        // MetaData
        $this->exportXMLMetaData($a_xml_writer);

        // PageObjects
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export Page Objects");
        $this->bench->start("ContentObjectExport", "exportPageObjects");
        $this->exportXMLPageObjects($a_xml_writer, $a_inst, $expLog);
        $this->bench->stop("ContentObjectExport", "exportPageObjects");
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export Page Objects");

        // MediaObjects
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export Media Objects");
        $this->bench->start("ContentObjectExport", "exportMediaObjects");
        $this->exportXMLMediaObjects($a_xml_writer, $a_inst, $a_target_dir, $expLog);
        $this->bench->stop("ContentObjectExport", "exportMediaObjects");
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export Media Objects");

        // FileItems
        $expLog->write(date("[y-m-d H:i:s] ") . "Start Export File Items");
        $this->bench->start("ContentObjectExport", "exportFileItems");
        $this->exportFileItems($a_target_dir, $expLog);
        $this->bench->stop("ContentObjectExport", "exportFileItems");
        $expLog->write(date("[y-m-d H:i:s] ") . "Finished Export File Items");
    }

    /**
    * export content objects meta data to xml (see ilias_co.dtd)
    *
    * @param	object		$a_xml_writer	ilXmlWriter object that receives the
    *										xml data
    */
    public function exportXMLMetaData(&$a_xml_writer)
    {
        $md2xml = new ilMD2XML($this->getId(), 0, $this->getType());
        $md2xml->setExportMode(true);
        $md2xml->startExport();
        $a_xml_writer->appendXML($md2xml->getXML());
    }

    /**
    * Returns the installation id for a given identifier
    *
    * @access	private
    */
    public function modifyExportIdentifier($a_tag, $a_param, $a_value)
    {
        if ($a_tag == "Identifier" && $a_param == "Entry") {
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
    public function exportXMLPageObjects(&$a_xml_writer, $inst, &$expLog)
    {
        foreach ($this->questions as $question_id) {
            $this->bench->start("ContentObjectExport", "exportPageObject");
            $expLog->write(date("[y-m-d H:i:s] ") . "Page Object " . $question_id);

            $attrs = [];
            $a_xml_writer->xmlStartTag("PageObject", $attrs);


            // export xml to writer object
            $this->bench->start("ContentObjectExport", "exportPageObject_XML");
            $page_object = new ilAssQuestionPage($question_id);
            $page_object->buildDom();
            $page_object->insertInstIntoIDs((string) $inst);
            $mob_ids = $page_object->collectMediaObjects(false);
            $file_ids = ilPCFileList::collectFileItems($page_object, $page_object->getDomDoc());
            $xml = $page_object->getXMLFromDom(false, false, false, "", true);
            $xml = str_replace("&", "&amp;", $xml);
            $a_xml_writer->appendXML($xml);
            $page_object->freeDom();
            unset($page_object);

            $this->bench->stop("ContentObjectExport", "exportPageObject_XML");

            // collect media objects
            $this->bench->start("ContentObjectExport", "exportPageObject_CollectMedia");
            //$mob_ids = $page_obj->getMediaObjectIDs();
            foreach ($mob_ids as $mob_id) {
                $this->mob_ids[$mob_id] = $mob_id;
            }
            $this->bench->stop("ContentObjectExport", "exportPageObject_CollectMedia");

            // collect all file items
            $this->bench->start("ContentObjectExport", "exportPageObject_CollectFileItems");
            //$file_ids = $page_obj->getFileItemIds();
            foreach ($file_ids as $file_id) {
                $this->file_ids[$file_id] = $file_id;
            }
            $this->bench->stop("ContentObjectExport", "exportPageObject_CollectFileItems");

            $a_xml_writer->xmlEndTag("PageObject");
            //unset($page_obj);

            $this->bench->stop("ContentObjectExport", "exportPageObject");
        }
    }

    /**
    * export media objects to xml (see ilias_co.dtd)
    */
    public function exportXMLMediaObjects(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
    {
        foreach ($this->mob_ids as $mob_id) {
            $expLog->write(date("[y-m-d H:i:s] ") . "Media Object " . $mob_id);
            if (ilObjMediaObject::_exists((int) $mob_id)) {
                $media_obj = new ilObjMediaObject((int) $mob_id);
                $media_obj->exportXML($a_xml_writer, (int) $a_inst);
                $media_obj->exportFiles($a_target_dir);
                unset($media_obj);
            }
        }
    }

    /**
    * export files of file itmes
    *
    */
    public function exportFileItems($target_dir, &$expLog)
    {
        foreach ($this->file_ids as $file_id) {
            $expLog->write(date("[y-m-d H:i:s] ") . "File Item " . $file_id);
            $file_dir = $target_dir . '/objects/il_' . IL_INST_ID . '_file_' . $file_id;
            ilFileUtils::makeDir($file_dir);
            $file_obj = new ilObjFile((int) $file_id, false);
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
    * get array of (two) new created questions for
    * import id
    */
    public function getImportMapping(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function checkMarks()
    {
        return $this->mark_schema->checkMarks();
    }

    /**
     * {@inheritdoc}
     */
    public function getMarkSchema(): ASS_MarkSchema
    {
        return $this->mark_schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getMarkSchemaForeignId(): int
    {
        return $this->getTestId();
    }

    /**
     */
    public function onMarkSchemaSaved()
    {
        $this->saveCompleteStatus($this->question_set_config_factory->getQuestionSetConfig());

        if ($this->participantDataExist()) {
            $this->recalculateScores(true);
        }
    }

    /**
     * @return {@inheritdoc}
     */
    public function canEditMarks(): bool
    {
        $total = $this->evalTotalPersons();
        if ($total > 0) {
            if ($this->getReportingDate()) {
                if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getReportingDate(), $matches)) {
                    $epoch_time = mktime(
                        (int) $matches[4],
                        (int) $matches[5],
                        (int) $matches[6],
                        (int) $matches[2],
                        (int) $matches[3],
                        (int) $matches[1]
                    );
                    $now = time();
                    if ($now < $epoch_time) {
                        return true;
                    }
                }
            }
            return false;
        } else {
            return true;
        }
    }

    /**
    * Saves an authors name into the lifecycle metadata if no lifecycle metadata exists
    * This will only be called for conversion of "old" tests where the author hasn't been
    * stored in the lifecycle metadata
    *
    * @param string $a_author A string containing the name of the test author
    * @access private
    * @see $author
    */
    public function saveAuthorToMetadata($author = "")
    {
        $md = new ilMD($this->getId(), 0, $this->getType());
        $md_life = $md->getLifecycle();
        if (!$md_life) {
            if (strlen($author) == 0) {
                $author = $this->user->getFullname();
            }

            $md_life = $md->addLifecycle();
            $md_life->save();
            $con = $md_life->addContribute();
            $con->setRole("Author");
            $con->save();
            $ent = $con->addEntity();
            $ent->setEntity($author);
            $ent->save();
        }
    }

    /**
    * @inheritDoc
    */
    protected function doCreateMetaData(): void
    {
        $this->saveAuthorToMetadata();
    }

    /**
    * Gets the authors name of the ilObjTest object
    *
    * @return string The string containing the name of the test author
    * @access public
    * @see $author
    */
    public function getAuthor(): string
    {
        $author = [];
        $md = new ilMD($this->getId(), 0, $this->getType());
        $md_life = $md->getLifecycle();
        if ($md_life) {
            $ids = $md_life->getContributeIds();
            foreach ($ids as $id) {
                $md_cont = $md_life->getContribute($id);
                if (strcmp($md_cont->getRole(), "Author") == 0) {
                    $entids = $md_cont->getEntityIds();
                    foreach ($entids as $entid) {
                        $md_ent = $md_cont->getEntity($entid);
                        array_push($author, $md_ent->getEntity());
                    }
                }
            }
        }
        return join(",", $author);
    }

    /**
    * Gets the authors name of the ilObjTest object
    *
    * @return string The string containing the name of the test author
    * @access public
    * @see $author
    */
    public static function _lookupAuthor($obj_id): string
    {
        $author = [];
        $md = new ilMD($obj_id, 0, "tst");
        $md_life = $md->getLifecycle();
        if ($md_life) {
            $ids = $md_life->getContributeIds();
            foreach ($ids as $id) {
                $md_cont = $md_life->getContribute($id);
                if (strcmp($md_cont->getRole(), "Author") == 0) {
                    $entids = $md_cont->getEntityIds();
                    foreach ($entids as $entid) {
                        $md_ent = $md_cont->getEntity($entid);
                        array_push($author, $md_ent->getEntity());
                    }
                }
            }
        }
        return join(",", $author);
    }

    /**
    * Returns the available tests for the active user
    *
    * @return array The available tests
    * @access public
    */
    public static function _getAvailableTests($use_object_id = false): array
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        $result_array = [];
        $tests = array_slice(
            array_reverse(
                ilUtil::_getObjectsByOperations("tst", "write", $ilUser->getId(), PHP_INT_MAX)
            ),
            0,
            10000
        );

        if (count($tests)) {
            $titles = ilObject::_prepareCloneSelection($tests, "tst");
            foreach ($tests as $ref_id) {
                if ($use_object_id) {
                    $obj_id = ilObject::_lookupObjId($ref_id);
                    $result_array[$obj_id] = $titles[$ref_id];
                } else {
                    $result_array[$ref_id] = $titles[$ref_id];
                }
            }
        }
        return $result_array;
    }

    /**
    * Clone object
    *
    * @access public
    * @param int ref id of parent container
    * @param int copy id
    * @return object new test object
    */
    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false): ?ilObject
    {
        $this->loadFromDb();

        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);
        $new_obj->setTmpCopyWizardCopyId($copy_id);
        $this->cloneMetaData($new_obj);

        $new_obj->saveToDb();
        $new_obj->addToNewsOnOnline(false, $new_obj->getObjectProperties()->getPropertyIsOnline()->getIsOnline());
        $this->getMainSettingsRepository()->store(
            $this->getMainSettings()->withTestId($new_obj->getTestId())
        );
        $this->getScoreSettingsRepository()->store(
            $this->getScoreSettings()->withTestId($new_obj->getTestId())
        );

        $new_obj->mark_schema = clone $this->mark_schema;
        $new_obj->setTemplate($this->getTemplate());

        // clone certificate
        $pathFactory = new ilCertificatePathFactory();
        $templateRepository = new ilCertificateTemplateDatabaseRepository($this->db);

        $cloneAction = new ilCertificateCloneAction(
            $this->db,
            $pathFactory,
            $templateRepository,
            CLIENT_WEB_DIR,
            $this->filesystem_web,
            new ilCertificateObjectHelper()
        );

        $cloneAction->cloneCertificate($this, $new_obj);

        $this->question_set_config_factory->getQuestionSetConfig()->cloneQuestionSetRelatedData($new_obj);
        $new_obj->saveQuestionsToDb();

        $skillLevelThresholdList = new ilTestSkillLevelThresholdList($this->db);
        $skillLevelThresholdList->setTestId($this->getTestId());
        $skillLevelThresholdList->loadFromDb();
        $skillLevelThresholdList->cloneListForTest($new_obj->getTestId());

        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($new_obj->getId());

        return $new_obj;
    }

    public function getQuestionCount(): int
    {
        $num = 0;

        if ($this->isRandomTest()) {
            $questionSetConfig = new ilTestRandomQuestionSetConfig(
                $this->tree,
                $this->db,
                $this->lng,
                $this->log,
                $this->component_repository,
                $this,
                $this->questioninfo
            );

            $questionSetConfig->loadFromDb();

            if ($questionSetConfig->isQuestionAmountConfigurationModePerPool()) {
                $sourcePoolDefinitionList = new ilTestRandomQuestionSetSourcePoolDefinitionList(
                    $this->db,
                    $this,
                    new ilTestRandomQuestionSetSourcePoolDefinitionFactory($this->db, $this)
                );

                $sourcePoolDefinitionList->loadDefinitions();

                if (is_int($sourcePoolDefinitionList->getQuestionAmount())) {
                    $num = $sourcePoolDefinitionList->getQuestionAmount();
                }
            } elseif (is_int($questionSetConfig->getQuestionAmountPerTest())) {
                $num = $questionSetConfig->getQuestionAmountPerTest();
            }
        } else {
            $this->loadQuestions();
            $num = count($this->questions);
        }

        return $num;
    }

    public function getQuestionCountWithoutReloading(): int
    {
        if ($this->isRandomTest()) {
            return $this->getQuestionCount();
        }
        return count($this->questions);
    }

    /**
    * Logs an action into the Test&Assessment log
    *
    * @param string $logtext The log text
    * @param integer $question_id If given, saves the question id to the database
    * @access public
    */
    public function logAction($logtext = "", $question_id = 0)
    {
        $original_id = 0;
        if ($question_id !== 0) {
            $original_id = $this->questioninfo->getOriginalId($question_id);
        }
        ilObjAssessmentFolder::_addLog($this->user->getId(), $this->getId(), $logtext, $question_id, $original_id, true, $this->getRefId());
    }

    /**
    * Returns the ILIAS test object id for a given test id
    *
    * @param integer $test_id The test id
    * @return mixed The ILIAS test object id or FALSE if the query was not successful
    * @access public
    */
    public static function _getObjectIDFromTestID($test_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $object_id = false;
        $result = $ilDB->queryF(
            "SELECT obj_fi FROM tst_tests WHERE test_id = %s",
            ['integer'],
            [$test_id]
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            $object_id = $row["obj_fi"];
        }
        return $object_id;
    }

    /**
    * Returns the ILIAS test object id for a given active id
    *
    * @param integer $active_id The active id
    * @return mixed The ILIAS test object id or FALSE if the query was not successful
    * @access public
    */
    public static function _getObjectIDFromActiveID($active_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $object_id = false;
        $result = $ilDB->queryF(
            "SELECT tst_tests.obj_fi FROM tst_tests, tst_active WHERE tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s",
            ['integer'],
            [$active_id]
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            $object_id = $row["obj_fi"];
        }
        return $object_id;
    }

    /**
    * Returns the ILIAS test id for a given object id
    *
    * @param integer $object_id The object id
    * @return mixed The ILIAS test id or FALSE if the query was not successful
    * @access public
    */
    public static function _getTestIDFromObjectID($object_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $test_id = false;
        $result = $ilDB->queryF(
            "SELECT test_id FROM tst_tests WHERE obj_fi = %s",
            ['integer'],
            [$object_id]
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            $test_id = $row["test_id"];
        }
        return $test_id;
    }

    /**
    * Returns the text answer of a given user for a given question
    *
    * @param integer $active_id
    * @param integer $question_id
    * @return string The answer text
    * @access public
    */
    public function getTextAnswer($active_id, $question_id, $pass = null): string
    {
        if (($active_id) && ($question_id)) {
            if ($pass === null) {
                $pass = assQuestion::_getSolutionMaxPass($question_id, $active_id);
            }
            if ($pass === null) {
                return '';
            }
            $query = $this->db->queryF(
                "SELECT value1 FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
                ['integer', 'integer', 'integer'],
                [$active_id, $question_id, $pass]
            );
            $result = $this->db->fetchAll($query);
            if (count($result) == 1) {
                return $result[0]["value1"];
            }
        }
        return '';
    }

    /**
    * Returns the question text for a given question
    *
    * @param integer $question_id The question id
    * @return string The question text
    * @access public
    */
    public function getQuestiontext($question_id): string
    {
        $res = "";
        if ($question_id) {
            $result = $this->db->queryF(
                "SELECT question_text FROM qpl_questions WHERE question_id = %s",
                ['integer'],
                [$question_id]
            );
            if ($result->numRows() == 1) {
                $row = $this->db->fetchAssoc($result);
                $res = $row["question_text"];
            }
        }
        return $res;
    }

    public function getInvitedParticipantList(): ilTestParticipantList
    {
        $participant_list = new ilTestParticipantList($this, $this->user, $this->lng, $this->db);
        $participant_list->initializeFromDbRows($this->getInvitedUsers());
        return $participant_list;
    }

    public function getActiveParticipantList(): ilTestParticipantList
    {
        $participant_list = new ilTestParticipantList($this, $this->user, $this->lng, $this->db);
        $participant_list->initializeFromDbRows($this->getTestParticipants());

        return $participant_list;
    }

    /**
    * Returns a list of all invited users in a test
    *
    * @return array array of invited users
    * @access public
    */
    public function &getInvitedUsers(int $user_id = 0, $order = "login, lastname, firstname"): array
    {
        $result_array = [];

        if ($this->getAnonymity()) {
            if ($user_id !== 0) {
                $result = $this->db->queryF(
                    "SELECT tst_active.active_id, tst_active.tries, usr_id, %s login, %s lastname, %s firstname, tst_invited_user.clientip, " .
                    "tst_active.submitted test_finished, matriculation, COALESCE(tst_active.last_finished_pass, -1) <> tst_active.last_started_pass unfinished_passes  FROM usr_data, tst_invited_user " .
                    "LEFT JOIN tst_active ON tst_active.user_fi = tst_invited_user.user_fi AND tst_active.test_fi = tst_invited_user.test_fi " .
                    "WHERE tst_invited_user.test_fi = %s and tst_invited_user.user_fi=usr_data.usr_id AND usr_data.usr_id=%s " .
                    "ORDER BY $order",
                    ['text', 'text', 'text', 'integer', 'integer'],
                    ['', $this->lng->txt('anonymous'), '', $this->getTestId(), $user_id]
                );
            } else {
                $result = $this->db->queryF(
                    "SELECT tst_active.active_id, tst_active.tries, usr_id, %s login, %s lastname, %s firstname, tst_invited_user.clientip, " .
                    "tst_active.submitted test_finished, matriculation, COALESCE(tst_active.last_finished_pass, -1) <> tst_active.last_started_pass unfinished_passes  FROM usr_data, tst_invited_user " .
                    "LEFT JOIN tst_active ON tst_active.user_fi = tst_invited_user.user_fi AND tst_active.test_fi = tst_invited_user.test_fi " .
                    "WHERE tst_invited_user.test_fi = %s and tst_invited_user.user_fi=usr_data.usr_id " .
                    "ORDER BY $order",
                    ['text', 'text', 'text', 'integer'],
                    ['', $this->lng->txt('anonymous'), '', $this->getTestId()]
                );
            }
        } else {
            if ($user_id !== 0) {
                $result = $this->db->queryF(
                    "SELECT tst_active.active_id, tst_active.tries, usr_id, login, lastname, firstname, tst_invited_user.clientip, " .
                    "tst_active.submitted test_finished, matriculation, COALESCE(tst_active.last_finished_pass, -1) <> tst_active.last_started_pass unfinished_passes  FROM usr_data, tst_invited_user " .
                    "LEFT JOIN tst_active ON tst_active.user_fi = tst_invited_user.user_fi AND tst_active.test_fi = tst_invited_user.test_fi " .
                    "WHERE tst_invited_user.test_fi = %s and tst_invited_user.user_fi=usr_data.usr_id AND usr_data.usr_id=%s " .
                    "ORDER BY $order",
                    ['integer', 'integer'],
                    [$this->getTestId(), $user_id]
                );
            } else {
                $result = $this->db->queryF(
                    "SELECT tst_active.active_id, tst_active.tries, usr_id, login, lastname, firstname, tst_invited_user.clientip, " .
                    "tst_active.submitted test_finished, matriculation, COALESCE(tst_active.last_finished_pass, -1) <> tst_active.last_started_pass unfinished_passes  FROM usr_data, tst_invited_user " .
                    "LEFT JOIN tst_active ON tst_active.user_fi = tst_invited_user.user_fi AND tst_active.test_fi = tst_invited_user.test_fi " .
                    "WHERE tst_invited_user.test_fi = %s and tst_invited_user.user_fi=usr_data.usr_id " .
                    "ORDER BY $order",
                    ['integer'],
                    [$this->getTestId()]
                );
            }
        }
        $result_array = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $result_array[$row['usr_id']] = $row;
        }
        return $result_array;
    }

    public function &getTestParticipants(): array
    {
        if ($this->getMainSettings()->getGeneralSettings()->getAnonymity()) {
            $query = "
				SELECT	tst_active.active_id,
						tst_active.tries,
						tst_active.user_fi usr_id,
						%s login,
						%s lastname,
						%s firstname,
						tst_active.submitted test_finished,
						usr_data.matriculation,
						usr_data.active,
						tst_active.lastindex,
						COALESCE(tst_active.last_finished_pass, -1) <> tst_active.last_started_pass unfinished_passes
				FROM tst_active
				LEFT JOIN usr_data
				ON tst_active.user_fi = usr_data.usr_id
				WHERE tst_active.test_fi = %s
				ORDER BY usr_data.lastname
			";
            $result = $this->db->queryF(
                $query,
                ['text', 'text', 'text', 'integer'],
                ['', $this->lng->txt("anonymous"), "", $this->getTestId()]
            );
        } else {
            $query = "
				SELECT	tst_active.active_id,
						tst_active.tries,
						tst_active.user_fi usr_id,
						usr_data.login,
						usr_data.lastname,
						usr_data.firstname,
						tst_active.submitted test_finished,
						usr_data.matriculation,
						usr_data.active,
						tst_active.lastindex,
						COALESCE(tst_active.last_finished_pass, -1) <> tst_active.last_started_pass unfinished_passes
				FROM tst_active
				LEFT JOIN usr_data
				ON tst_active.user_fi = usr_data.usr_id
				WHERE tst_active.test_fi = %s
				ORDER BY usr_data.lastname
			";
            $result = $this->db->queryF(
                $query,
                ['integer'],
                [$this->getTestId()]
            );
        }
        $data = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $data[$row['active_id']] = $row;
        }
        foreach ($data as $index => $participant) {
            if (strlen(trim($participant["firstname"] . $participant["lastname"])) == 0) {
                $data[$index]["lastname"] = $this->lng->txt("deleted_user");
            }
        }
        return $data;
    }

    public function getTestParticipantsForManualScoring($filter = null): array
    {
        $scoring = ilObjAssessmentFolder::_getManualScoring();
        if (count($scoring) == 0) {
            return [];
        }

        $participants = &$this->getTestParticipants();
        $filtered_participants = [];
        foreach ($participants as $active_id => $participant) {
            $qstType_IN_manScoreableQstTypes = $this->db->in('qpl_questions.question_type_fi', $scoring, false, 'integer');

            $queryString = "
				SELECT		tst_test_result.manual

				FROM		tst_test_result

				INNER JOIN	qpl_questions
				ON			tst_test_result.question_fi = qpl_questions.question_id

				WHERE		tst_test_result.active_fi = %s
				AND			$qstType_IN_manScoreableQstTypes
			";

            $result = $this->db->queryF(
                $queryString,
                ["integer"],
                [$active_id]
            );

            $count = $result->numRows();

            if ($count > 0) {
                switch ($filter) {
                    case 3: // all users
                        $filtered_participants[$active_id] = $participant;
                        break;
                    case 4:
                        if ($this->testManScoringDoneHelper->isDone((int) $active_id)) {
                            $filtered_participants[$active_id] = $participant;
                        }
                        break;
                    case 5:
                        if (!$this->testManScoringDoneHelper->isDone((int) $active_id)) {
                            $filtered_participants[$active_id] = $participant;
                        }
                        break;
                    case 6:
                        // partially scored participants
                        $found = 0;
                        while ($row = $this->db->fetchAssoc($result)) {
                            if ($row["manual"]) {
                                $found++;
                            }
                        }
                        if (($found > 0) && ($found < $count)) {
                            $filtered_participants[$active_id] = $participant;
                        }
                        break;
                    default:
                        $filtered_participants[$active_id] = $participant;
                        break;
                }
            }
        }
        return $filtered_participants;
    }

    /**
    * Returns a data of all users specified by id list
    *
    * @return array The user data "usr_id, login, lastname, firstname, clientip" of the users with id as key
    * @access public
    */
    public function getUserData($ids): array
    {
        if (!is_array($ids) || count($ids) == 0) {
            return [];
        }

        if ($this->getAnonymity()) {
            $result = $this->db->queryF(
                "SELECT usr_id, %s login, %s lastname, %s firstname, client_ip clientip FROM usr_data WHERE " . $this->db->in('usr_id', $ids, false, 'integer') . " ORDER BY login",
                ['text', 'text', 'text'],
                ["", $this->lng->txt("anonymous"), ""]
            );
        } else {
            $result = $this->db->query("SELECT usr_id, login, lastname, firstname, client_ip clientip FROM usr_data WHERE " . $this->db->in('usr_id', $ids, false, 'integer') . " ORDER BY login");
        }

        $result_array = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $result_array[$row["usr_id"]] = $row;
        }
        return $result_array;
    }

    public function getGroupData($ids): array
    {
        if (!is_array($ids) || count($ids) == 0) {
            return [];
        }
        $result = [];
        foreach ($ids as $ref_id) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            $result[$ref_id] = ["ref_id" => $ref_id, "title" => ilObject::_lookupTitle($obj_id), "description" => ilObject::_lookupDescription($obj_id)];
        }
        return $result;
    }

    public function getRoleData($ids): array
    {
        if (!is_array($ids) || count($ids) == 0) {
            return [];
        }
        $result = [];
        foreach ($ids as $obj_id) {
            $result[$obj_id] = ["obj_id" => $obj_id, "title" => ilObject::_lookupTitle($obj_id), "description" => ilObject::_lookupDescription($obj_id)];
        }
        return $result;
    }


    /**
    * Invites all users of a group to a test
    *
    * @param integer $group_id The database id of the invited group
    * @access public
    */
    public function inviteGroup($group_id)
    {
        $group = new ilObjGroup($group_id);
        $members = $group->getGroupMemberIds();
        foreach ($members as $user_id) {
            $this->inviteUser($user_id, ilObjUser::_lookupClientIP($user_id));
        }
    }

    /**
    * Invites all users of a role to a test
    *
    * @param integer $group_id The database id of the invited group
    * @access public
    */
    public function inviteRole($role_id)
    {
        $members = $this->rbac_review->assignedUsers($role_id);
        foreach ($members as $user_id) {
            $this->inviteUser($user_id, ilObjUser::_lookupClientIP($user_id));
        }
    }



    /**
    * Disinvites a user from a test
    *
    * @param integer $user_id The database id of the disinvited user
    * @access public
    */
    public function disinviteUser($user_id)
    {
        $affectedRows = $this->db->manipulateF(
            "DELETE FROM tst_invited_user WHERE test_fi = %s AND user_fi = %s",
            ['integer', 'integer'],
            [$this->getTestId(), $user_id]
        );
    }

    /**
    * Invites a user to a test
    *
    * @param integer $user_id The database id of the invited user
    * @access public
    */
    public function inviteUser($user_id, $client_ip = "")
    {
        $this->db->manipulateF(
            "DELETE FROM tst_invited_user WHERE test_fi = %s AND user_fi = %s",
            ['integer', 'integer'],
            [$this->getTestId(), $user_id]
        );
        $this->db->manipulateF(
            "INSERT INTO tst_invited_user (test_fi, user_fi, clientip, tstamp) VALUES (%s, %s, %s, %s)",
            ['integer', 'integer', 'text', 'integer'],
            [$this->getTestId(), $user_id, (strlen($client_ip)) ? $client_ip : null, time()]
        );
    }


    public function setClientIP($user_id, $client_ip)
    {
        $this->db->manipulateF(
            "UPDATE tst_invited_user SET clientip = %s, tstamp = %s WHERE test_fi=%s and user_fi=%s",
            ['text', 'integer', 'integer', 'integer'],
            [(strlen($client_ip)) ? $client_ip : null, time(), $this->getTestId(), $user_id]
        );
    }

    /**
     * get solved questions
     *
     * @return array of int containing all question ids which have been set solved for the given user and test
     */
    public static function _getSolvedQuestions($active_id, $question_fi = null): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        if (is_numeric($question_fi)) {
            $result = $ilDB->queryF(
                "SELECT question_fi, solved FROM tst_qst_solved WHERE active_fi = %s AND question_fi=%s",
                ['integer', 'integer'],
                [$active_id, $question_fi]
            );
        } else {
            $result = $ilDB->queryF(
                "SELECT question_fi, solved FROM tst_qst_solved WHERE active_fi = %s",
                ['integer'],
                [$active_id]
            );
        }
        $result_array = [];
        while ($row = $ilDB->fetchAssoc($result)) {
            $result_array[$row["question_fi"]] = $row;
        }
        return $result_array;
    }


    /**
     * sets question solved state to value for given user_id
     */
    public function setQuestionSetSolved($value, $question_id, $user_id)
    {
        $active_id = $this->getActiveIdOfUser($user_id);
        $this->db->manipulateF(
            "DELETE FROM tst_qst_solved WHERE active_fi = %s AND question_fi = %s",
            ['integer', 'integer'],
            [$active_id, $question_id]
        );
        $this->db->manipulateF(
            "INSERT INTO tst_qst_solved (solved, question_fi, active_fi) VALUES (%s, %s, %s)",
            ['integer', 'integer', 'integer'],
            [$value, $question_id, $active_id]
        );
    }

    /**
     * returns if the active for user_id has been submitted
     */
    public function isTestFinished($active_id): bool
    {
        $result = $this->db->queryF(
            "SELECT submitted FROM tst_active WHERE active_id=%s AND submitted=%s",
            ['integer', 'integer'],
            [$active_id, 1]
        );
        return $result->numRows() == 1;
    }

    /**
     * returns if the active for user_id has been submitted
     */
    public function isActiveTestSubmitted($user_id = null): bool
    {
        if (!is_numeric($user_id)) {
            $user_id = $this->user->getId();
        }

        $result = $this->db->queryF(
            "SELECT submitted FROM tst_active WHERE test_fi=%s AND user_fi=%s AND submitted=%s",
            ['integer', 'integer', 'integer'],
            [$this->getTestId(), $user_id, 1]
        );
        return $result->numRows() == 1;
    }

    /**
     * returns if the numbers of tries have to be checked
     */
    public function hasNrOfTriesRestriction(): bool
    {
        return $this->getNrOfTries() != 0;
    }


    /**
     * returns if number of tries are reached
     * @deprecated: tries field differs per situation, outside a pass it's the number of tries, inside a pass it's the current pass number.
     */

    public function isNrOfTriesReached($tries): bool
    {
        return $tries >= $this->getNrOfTries();
    }


    /**
     * returns all test results for all participants
     *
     * @param array $partipants array of user ids
     * @param boolean if true, the result will be prepared for csv output (see processCSVRow)
     *
     * @return array of fields, see code for column titles
     */
    public function getAllTestResults($participants, $prepareForCSV = true): array
    {
        $results = [];
        $row = [
            "user_id" => $this->lng->txt("user_id"),
            "matriculation" => $this->lng->txt("matriculation"),
            "lastname" => $this->lng->txt("lastname"),
            "firstname" => $this->lng->txt("firstname"),
            "login" => $this->lng->txt("login"),
            "reached_points" => $this->lng->txt("tst_reached_points"),
            "max_points" => $this->lng->txt("tst_maximum_points"),
            "percent_value" => $this->lng->txt("tst_percent_solved"),
            "mark" => $this->lng->txt("tst_mark"),
            "passed" => $this->lng->txt("tst_mark_passed"),
        ];
        $results[] = $row;
        if (count($participants)) {
            foreach ($participants as $active_id => $user_rec) {
                $mark = '';
                $row = [];
                $reached_points = 0;
                $max_points = 0;
                $pass = ilObjTest::_getResultPass($active_id);
                // abort if no valid pass can be found
                if (!is_int($pass)) {
                    continue;
                }
                foreach ($this->questions as $value) {
                    $question = ilObjTest::_instanciateQuestion($value);
                    if (is_object($question)) {
                        $max_points += $question->getMaximumPoints();
                        $reached_points += $question->getReachedPoints($active_id, $pass);
                    }
                }
                if ($max_points > 0) {
                    $percentvalue = $reached_points / $max_points;
                    if ($percentvalue < 0) {
                        $percentvalue = 0.0;
                    }
                } else {
                    $percentvalue = 0;
                }
                $mark_obj = $this->mark_schema->getMatchingMark($percentvalue * 100);
                $passed = "";
                if ($mark_obj) {
                    $mark = $mark_obj->getOfficialName();
                }
                if ($this->getAnonymity()) {
                    $user_rec['firstname'] = "";
                    $user_rec['lastname'] = $this->lng->txt("anonymous");
                }
                $row = [
                    "user_id" => $user_rec['usr_id'],
                    "matriculation" => $user_rec['matriculation'],
                    "lastname" => $user_rec['lastname'],
                    "firstname" => $user_rec['firstname'],
                    "login" => $user_rec['login'],
                    "reached_points" => $reached_points,
                    "max_points" => $max_points,
                    "percent_value" => $percentvalue,
                    "mark" => $mark,
                    "passed" => $user_rec['passed'] ? '1' : '0',
                ];
                $results[] = $prepareForCSV ? $this->processCSVRow($row, true) : $row;
            }
        }
        return $results;
    }

    /**
    * Processes an array as a CSV row and converts the array values to correct CSV
    * values. The "converted" array is returned
    *
    * @param array $row The array containing the values for a CSV row
    * @param string $quoteAll Indicates to quote every value (=TRUE) or only values containing quotes and separators (=FALSE, default)
    * @param string $separator The value separator in the CSV row (used for quoting) (; = default)
    * @return array The converted array ready for CSV use
    * @access public
    */
    public function processCSVRow(
        mixed $row,
        bool $quote_all = false,
        string $separator = ";"
    ): array {
        $resultarray = [];
        foreach ($row as $rowindex => $entry) {
            $surround = false;
            if ($quote_all) {
                $surround = true;
            }
            if (is_string($entry) && strpos($entry, "\"") !== false) {
                $entry = str_replace("\"", "\"\"", $entry);
                $surround = true;
            }
            if (is_string($entry) && strpos($entry, $separator) !== false) {
                $surround = true;
            }

            if (is_string($entry)) {
                // replace all CR LF with LF (for Excel for Windows compatibility
                $entry = str_replace(chr(13) . chr(10), chr(10), $entry);
            }

            if ($surround) {
                $entry = "\"" . $entry . "\"";
            }

            $resultarray[$rowindex] = $entry;
        }
        return $resultarray;
    }

    /**
    * Retrieves the actual pass of a given user for a given test
    *
    * @param integer $user_id The user id
    * @param integer $test_id The test id
    * @return integer The pass of the user for the given test
    * @access public
    */
    public static function _getPass($active_id): int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT tries FROM tst_active WHERE active_id = %s",
            ['integer'],
            [$active_id]
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            return $row["tries"];
        } else {
            return 0;
        }
    }

    /**
    * Retrieves the maximum pass of a given user for a given test
    * in which the user answered at least one question
    *
    * @param integer $user_id The user id
    * @param integer $test_id The test id
    * @return integer The pass of the user for the given test
    * @access public
    */
    public static function _getMaxPass($active_id): ?int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT MAX(pass) maxpass FROM tst_pass_result WHERE active_fi = %s",
            ['integer'],
            [$active_id]
        );

        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            return $row["maxpass"];
        }

        return null;
    }

    /**
     * Retrieves the best pass of a given user for a given test
     * @param int $active_id
     * @return int|mixed
     */
    public static function _getBestPass($active_id): ?int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT * FROM tst_pass_result WHERE active_fi = %s",
            ['integer'],
            [$active_id]
        );

        if (!$result->numRows()) {
            return null;
        }

        $bestrow = null;
        $bestfactor = 0.0;
        while ($row = $ilDB->fetchAssoc($result)) {
            if ($row["maxpoints"] > 0.0) {
                $factor = (float) ($row["points"] / $row["maxpoints"]);
            } else {
                $factor = 0.0;
            }
            if ($factor === 0.0 && $bestfactor === 0.0
                || $factor > $bestfactor) {
                $bestrow = $row;
                $bestfactor = $factor;
            }
        }

        if (is_array($bestrow)) {
            return $bestrow["pass"];
        }

        return null;
    }

    /**
    * Retrieves the pass number that should be counted for a given user
    *
    * @param integer $user_id The user id
    * @param integer $test_id The test id
    * @return integer The result pass of the user for the given test
    * @access public
    */
    public static function _getResultPass($active_id): ?int
    {
        $counted_pass = null;
        if (ilObjTest::_getPassScoring($active_id) == SCORE_BEST_PASS) {
            $counted_pass = ilObjTest::_getBestPass($active_id);
        } else {
            $counted_pass = ilObjTest::_getMaxPass($active_id);
        }
        return $counted_pass;
    }

    /**
    * Retrieves the number of answered questions for a given user in a given test
    *
    * @param integer $user_id The user id
    * @param integer $test_id The test id
    * @param integer $pass The pass of the test (optional)
    * @return integer The number of answered questions
    * @access public
    */
    public function getAnsweredQuestionCount($active_id, $pass = null): int
    {
        if ($this->isRandomTest()) {
            $this->loadQuestions($active_id, $pass);
        }
        $workedthrough = 0;
        foreach ($this->questions as $value) {
            if ($this->questioninfo->lookupResultRecordExist($active_id, $value, $pass)) {
                $workedthrough += 1;
            }
        }
        return $workedthrough;
    }

    /**
     * @param int $active_id
     * @param int $pass
     *
     * @return int
     */
    public static function lookupPassResultsUpdateTimestamp($active_id, $pass): int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (is_null($pass)) {
            $pass = 0;
        }

        $query = "
			SELECT	tst_pass_result.tstamp pass_res_tstamp,
					tst_test_result.tstamp quest_res_tstamp

			FROM tst_pass_result

			LEFT JOIN tst_test_result
			ON tst_test_result.active_fi = tst_pass_result.active_fi
			AND tst_test_result.pass = tst_pass_result.pass

			WHERE tst_pass_result.active_fi = %s
			AND tst_pass_result.pass = %s

			ORDER BY tst_test_result.tstamp DESC
		";

        $result = $ilDB->queryF(
            $query,
            ['integer', 'integer'],
            [$active_id, $pass]
        );

        while ($row = $ilDB->fetchAssoc($result)) {
            if ($row['quest_res_tstamp']) {
                return $row['quest_res_tstamp'];
            }

            return $row['pass_res_tstamp'];
        }

        return 0;
    }

    /**
     * Checks if the test is executable by the given user
     * @param         $test_session
     * @param integer $user_id The user id
     * @param bool    $allowPassIncrease
     * @return array Result array
     * @throws ilDateTimeException
     */
    public function isExecutable($test_session, $user_id, $allow_pass_increase = false): array
    {
        $result = [
            "executable" => true,
            "errormessage" => ""
        ];

        if (!$this->startingTimeReached()) {
            $result["executable"] = false;
            $result["errormessage"] = sprintf($this->lng->txt("detail_starting_time_not_reached"), ilDatePresentation::formatDate(new ilDateTime($this->getStartingTime(), IL_CAL_UNIX)));
            return $result;
        }
        if ($this->endingTimeReached()) {
            $result["executable"] = false;
            $result["errormessage"] = sprintf($this->lng->txt("detail_ending_time_reached"), ilDatePresentation::formatDate(new ilDateTime($this->getEndingTime(), IL_CAL_UNIX)));
            return $result;
        }

        $active_id = $this->getActiveIdOfUser($user_id);

        if ($this->getEnableProcessingTime()
            && $active_id > 0
            && ($starting_time = $this->getStartingTimeOfUser($active_id)) !== false
            && $this->isMaxProcessingTimeReached($starting_time, $active_id)) {
            if ($allow_pass_increase
                    && $this->getResetProcessingTime()
                    && (($this->getNrOfTries() === 0)
                || ($this->getNrOfTries() > (self::_getPass($active_id) + 1)))) {
                // a test pass was quitted because the maximum processing time was reached, but the time
                // will be resetted for future passes, so if there are more passes allowed, the participant may
                // start the test again.
                // This code block is only called when $allowPassIncrease is TRUE which only happens when
                // the test info page is opened. Otherwise this will lead to unexpected results!
                $test_session->increasePass();
                $test_session->setLastSequence(0);
                $test_session->saveToDb();
            } else {
                $result["executable"] = false;
                $result["errormessage"] = $this->lng->txt("detail_max_processing_time_reached");
            }
            return $result;
        }

        $testPassesSelector = new ilTestPassesSelector($this->db, $this);
        $testPassesSelector->setActiveId($active_id);
        $testPassesSelector->setLastFinishedPass($test_session->getLastFinishedPass());

        if ($this->hasNrOfTriesRestriction() && ($active_id > 0)) {
            $closedPasses = $testPassesSelector->getClosedPasses();

            if (count($closedPasses) >= $this->getNrOfTries()) {
                $result["executable"] = false;
                $result["errormessage"] = $this->lng->txt("maximum_nr_of_tries_reached");
                return $result;
            }

            if ($this->isBlockPassesAfterPassedEnabled() && !$testPassesSelector->openPassExists()) {
                if (ilObjTestAccess::_isPassed($user_id, $this->getId())) {
                    $result['executable'] = false;
                    $result['errormessage'] = $this->lng->txt("tst_addit_passes_blocked_after_passed_msg");
                    return $result;
                }
            }
        }

        $next_pass_allowed_timestamp = 0;
        if (!$this->isNextPassAllowed($testPassesSelector, $next_pass_allowed_timestamp)) {
            $date = ilDatePresentation::formatDate(new ilDateTime($next_pass_allowed_timestamp, IL_CAL_UNIX));

            $result['executable'] = false;
            $result['errormessage'] = sprintf($this->lng->txt('wait_for_next_pass_hint_msg'), $date);
            return $result;
        }
        return $result;
    }

    public function isNextPassAllowed(ilTestPassesSelector $testPassesSelector, int &$next_pass_allowed_timestamp): bool
    {
        $waiting_between_passes = $this->getMainSettings()->getTestBehaviourSettings()->getPassWaiting();
        $last_finished_pass_timestamp = $testPassesSelector->getLastFinishedPassTimestamp();

        if (
            $this->getMainSettings()->getTestBehaviourSettings()->getPassWaitingEnabled()
            && ($waiting_between_passes !== '')
            && ($testPassesSelector->getLastFinishedPass() !== null)
            && ($last_finished_pass_timestamp !== null)
        ) {
            $time_values = explode(':', $waiting_between_passes);
            $next_pass_allowed_timestamp = strtotime('+ ' . $time_values[0] . ' Days + ' . $time_values[1] . ' Hours' . $time_values[2] . ' Minutes', $last_finished_pass_timestamp);
            return (time() > $next_pass_allowed_timestamp);
        }

        return true;
    }

    public function canShowTestResults(ilTestSession $test_session): bool
    {
        $passSelector = new ilTestPassesSelector($this->db, $this);

        $passSelector->setActiveId($test_session->getActiveId());
        $passSelector->setLastFinishedPass($test_session->getLastFinishedPass());

        return $passSelector->hasReportablePasses();
    }

    public function hasAnyTestResult(ilTestSession $test_session): bool
    {
        $passSelector = new ilTestPassesSelector($this->db, $this);

        $passSelector->setActiveId($test_session->getActiveId());
        $passSelector->setLastFinishedPass($test_session->getLastFinishedPass());

        return $passSelector->hasExistingPasses();
    }

    /**
    * Returns the unix timestamp of the time a user started a test
    *
    * @param integer $active_id The active id of the user
    * @return false|int The unix timestamp if the user started the test, FALSE otherwise
    * @access public
    */
    public function getStartingTimeOfUser($active_id, $pass = null)
    {
        if ($active_id < 1) {
            return false;
        }
        if ($pass === null) {
            $pass = ($this->getResetProcessingTime()) ? self::_getPass($active_id) : 0;
        }
        $result = $this->db->queryF(
            "SELECT tst_times.started FROM tst_times WHERE tst_times.active_fi = %s AND tst_times.pass = %s ORDER BY tst_times.started",
            ['integer', 'integer'],
            [$active_id, $pass]
        );
        if ($result->numRows()) {
            $row = $this->db->fetchAssoc($result);
            if (preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches)) {
                return mktime(
                    (int) $matches[4],
                    (int) $matches[5],
                    (int) $matches[6],
                    (int) $matches[2],
                    (int) $matches[3],
                    (int) $matches[1]
                );
            } else {
                return time();
            }
        } else {
            return time();
        }
    }

    /**
    * Returns whether the maximum processing time for a test is reached or not
    *
    * @return bool TRUE if the maxium processing time is reached, FALSE if the
    *					maximum processing time is not reached or no maximum processing time is given
    */
    public function isMaxProcessingTimeReached(int $starting_time, int $active_id): bool
    {
        if (!$this->getEnableProcessingTime()) {
            return false;
        }

        $processing_time = $this->getProcessingTimeInSeconds($active_id);
        $now = time();
        if ($now > ($starting_time + $processing_time)) {
            return true;
        }

        return false;
    }

    public function &getTestQuestions(): array
    {
        $tags_trafo = $this->refinery->string()->stripTags();

        $query = "
			SELECT		questions.*,
						questtypes.type_tag,
						tstquest.sequence,
						tstquest.obligatory,
						origquest.obj_fi orig_obj_fi

			FROM		qpl_questions questions

			INNER JOIN	qpl_qst_type questtypes
			ON			questtypes.question_type_id = questions.question_type_fi

			INNER JOIN	tst_test_question tstquest
			ON			tstquest.question_fi = questions.question_id

			LEFT JOIN	qpl_questions origquest
			ON			origquest.question_id = questions.original_id

			WHERE		tstquest.test_fi = %s

			ORDER BY	tstquest.sequence
		";

        $query_result = $this->db->queryF(
            $query,
            ['integer'],
            [$this->getTestId()]
        );

        $questions = [];

        while ($row = $this->db->fetchAssoc($query_result)) {
            $row['title'] = $tags_trafo->transform($row['title']);
            $row['description'] = $tags_trafo->transform($row['description'] !== '' && $row['description'] !== null ? $row['description'] : '&nbsp;');
            $row['author'] = $tags_trafo->transform($row['author']);
            $row['obligationPossible'] = self::isQuestionObligationPossible($row['question_id']);

            $questions[] = $row;
        }

        return $questions;
    }

    /**
     * @param int $questionId
     * @return bool
     */
    public function isTestQuestion($questionId): bool
    {
        foreach ($this->getTestQuestions() as $questionData) {
            if ($questionData['question_id'] != $questionId) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function checkQuestionParent($questionId): bool
    {
        $row = $this->db->fetchAssoc($this->db->queryF(
            "SELECT COUNT(question_id) cnt FROM qpl_questions WHERE question_id = %s AND obj_fi = %s",
            ['integer', 'integer'],
            [$questionId, $this->getId()]
        ));

        return (bool) $row['cnt'];
    }

    public function getFixedQuestionSetTotalPoints(): float
    {
        $points = 0;

        foreach ($this->getTestQuestions() as $question_data) {
            $points += $question_data['points'];
        }

        return $points;
    }

    /**
     * @return array
     */
    public function getPotentialRandomTestQuestions(): array
    {
        $query = "
			SELECT		questions.*,
						questtypes.type_tag,
						origquest.obj_fi orig_obj_fi

			FROM		qpl_questions questions

			INNER JOIN	qpl_qst_type questtypes
			ON			questtypes.question_type_id = questions.question_type_fi

			INNER JOIN	tst_rnd_cpy tstquest
			ON			tstquest.qst_fi = questions.question_id

			LEFT JOIN	qpl_questions origquest
			ON			origquest.question_id = questions.original_id

			WHERE		tstquest.tst_fi = %s
		";

        $query_result = $this->db->queryF(
            $query,
            ['integer'],
            [$this->getTestId()]
        );

        $questions = [];

        while ($row = $this->db->fetchAssoc($query_result)) {
            $question = $row;

            $question['obligationPossible'] = self::isQuestionObligationPossible($row['question_id']);

            $questions[] = $question;
        }

        return $questions;
    }

    public function getShuffleQuestions(): bool
    {
        return $this->getMainSettings()->getQuestionBehaviourSettings()->getShuffleQuestions();
    }

    /**
    * Returns the settings for the list of questions options in the test properties
    * This could contain one of the following values:
    *   0 = No list of questions offered
    *   1 = A list of questions is offered
    *   3 = A list of questions is offered and the list of questions is shown as first page of the test
    *   5 = A list of questions is offered and the list of questions is shown as last page of the test
    *   7 = A list of questions is offered and the list of questions is shown as first and last page of the test
    *
    * @return integer
    */
    public function getListOfQuestionsSettings()
    {
        return $this->getMainSettings()->getParticipantFunctionalitySettings()->getUsrPassOverviewMode();
    }

    public function getListOfQuestions(): bool
    {
        return $this->getMainSettings()->getParticipantFunctionalitySettings()->getQuestionListEnabled();
    }

    public function getUsrPassOverviewEnabled(): bool
    {
        return $this->getMainSettings()->getParticipantFunctionalitySettings()->getUsrPassOverviewEnabled();
    }

    public function getListOfQuestionsStart(): bool
    {
        return $this->getMainSettings()->getParticipantFunctionalitySettings()->getShownQuestionListAtBeginning();
    }

    public function getListOfQuestionsEnd(): bool
    {
        return $this->getMainSettings()->getParticipantFunctionalitySettings()->getShownQuestionListAtEnd();
    }

    public function getListOfQuestionsDescription(): bool
    {
        return $this->getMainSettings()->getParticipantFunctionalitySettings()->getShowDescriptionInQuestionList();
    }

    /**
    * Returns if the pass details should be shown when a test is not finished
    */
    public function getShowPassDetails(): bool
    {
        return $this->getScoreSettings()->getResultDetailsSettings()->getShowPassDetails();
    }

    /**
    * Returns if the solution printview should be presented to the user or not
    */
    public function getShowSolutionPrintview(): bool
    {
        return $this->getScoreSettings()->getResultDetailsSettings()->getShowSolutionPrintview();
    }
    /**
     * @deprecated
     */
    public function canShowSolutionPrintview($user_id = null): bool
    {
        return $this->getShowSolutionPrintview();
    }

    /**
    * Returns if the feedback should be presented to the solution or not
    */
    public function getShowSolutionFeedback(): bool
    {
        return $this->getScoreSettings()->getResultDetailsSettings()->getShowSolutionFeedback();
    }

    /**
    * Returns if the full solution (including ILIAS content) should be presented to the solution or not
    */
    public function getShowSolutionAnswersOnly(): bool
    {
        return $this->getScoreSettings()->getResultDetailsSettings()->getShowSolutionAnswersOnly();
    }

    /**
    * Returns if the signature field should be shown in the test results
    */
    public function getShowSolutionSignature(): bool
    {
        return $this->getScoreSettings()->getResultDetailsSettings()->getShowSolutionSignature();
    }

    /**
    * @return boolean TRUE if the suggested solutions should be shown, FALSE otherwise
    */
    public function getShowSolutionSuggested(): bool
    {
        return $this->getScoreSettings()->getResultDetailsSettings()->getShowSolutionSuggested();
    }

    /**
     * @return boolean TRUE if the results should be compared with the correct results in the list of answers, FALSE otherwise
     * @access public
     */
    public function getShowSolutionListComparison(): bool
    {
        return $this->getScoreSettings()->getResultDetailsSettings()->getShowSolutionListComparison();
    }

    public function getShowSolutionListOwnAnswers(): bool
    {
        return $this->getScoreSettings()->getResultDetailsSettings()->getShowSolutionListOwnAnswers();
    }

    /**
     * @deprecated: use ilTestParticipantData instead
     */
    public static function _getUserIdFromActiveId(int $active_id): int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT user_fi FROM tst_active WHERE active_id = %s",
            ['integer'],
            [$active_id]
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            return $row["user_fi"];
        } else {
            return -1;
        }
    }

    public function _getLastAccess(int $active_id): string
    {
        $result = $this->db->queryF(
            "SELECT finished FROM tst_times WHERE active_fi = %s ORDER BY finished DESC",
            ['integer'],
            [$active_id]
        );
        if ($result->numRows()) {
            $row = $this->db->fetchAssoc($result);
            return $row["finished"];
        }
        return "";
    }

    public static function lookupLastTestPassAccess(int $active_id, int $pass_index): ?int
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "
            SELECT MAX(tst_times.tstamp) as last_pass_access
            FROM tst_times
            WHERE active_fi = %s
            AND pass = %s
        ";

        $res = $ilDB->queryF(
            $query,
            ['integer', 'integer'],
            [$active_id, $pass_index]
        );

        while ($row = $ilDB->fetchAssoc($res)) {
            return $row['last_pass_access'];
        }

        return null;
    }

    /**
    * Checks if a given string contains HTML or not
    *
    * @param string $a_text Text which should be checked
    * @return boolean
    * @access public
    */
    public function isHTML($a_text): bool
    {
        if (preg_match("/<[^>]*?>/", $a_text)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Reads an QTI material tag and creates a text string
    *
    * @return string text or xhtml string
    * @access public
    */
    public function qtiMaterialToArray($a_material): array
    {
        $result = '';
        $mobs = [];
        for ($i = 0; $i < $a_material->getMaterialCount(); $i++) {
            $material = $a_material->getMaterial($i);
            if ($material['type'] === 'mattext') {
                $result .= $material['material']->getContent();
            }
            if ($material['type'] === 'matimage') {
                $matimage = $material['material'];
                if (preg_match('/(il_([0-9]+)_mob_([0-9]+))/', $matimage->getLabel(), $matches)) {
                    $mobs[] = [
                        'mob' => $matimage->getLabel(),
                        'uri' => $matimage->getUri()
                    ];
                }
            }
        }

        $decoded_result = base64_decode($result);
        if (str_starts_with($decoded_result, '<PageObject>')) {
            $result = $decoded_result;
        }

        $this->log->write(print_r(ilSession::get('import_mob_xhtml'), true));
        return [
            'text' => $result,
            'mobs' => $mobs
        ];
    }

    public function addQTIMaterial(ilXmlWriter &$xml_writer, ?int $page_id, string $material = ''): void
    {
        $xml_writer->xmlStartTag('material');
        $attrs = [
            'texttype' => 'text/plain'
        ];
        $file_ids = [];
        $mobs = [];
        if ($page_id !== null) {
            $attrs['texttype'] = 'text/xml';
            $mobs = ilObjMediaObject::_getMobsOfObject('tst:pg', $page_id);
            $page_object = new ilTestPage($page_id);
            $page_object->buildDom();
            $page_object->insertInstIntoIDs((string) IL_INST_ID);
            $material = base64_encode($page_object->getXMLFromDom());
            $file_ids = ilPCFileList::collectFileItems($page_object, $page_object->getDomDoc());
            foreach ($file_ids as $file_id) {
                $this->file_ids[] = (int) $file_id;
            };
            $mob_string = 'il_' . IL_INST_ID . '_mob_';
        } elseif ($this->isHTML($material)) {
            $attrs['texttype'] = 'text/xhtml';
            $mobs = ilObjMediaObject::_getMobsOfObject('tst:html', $this->getId());
            $mob_string = 'mm_';
        }

        $xml_writer->xmlElement('mattext', $attrs, $material);
        foreach ($mobs as $mob) {
            $mob_id_string = (string) $mob;
            $moblabel = 'il_' . IL_INST_ID . '_mob_' . $mob_id_string;
            if (strpos($material, $mob_string . $mob_id_string) !== false) {
                if (ilObjMediaObject::_exists($mob)) {
                    $mob_obj = new ilObjMediaObject($mob);
                    $imgattrs = [
                        'label' => $moblabel,
                        'uri' => 'objects/' . 'il_' . IL_INST_ID . '_mob_' . $mob_id_string . '/' . $mob_obj->getTitle()
                    ];
                }
                $xml_writer->xmlElement('matimage', $imgattrs, null);
            }
        }
        $xml_writer->xmlEndTag('material');
    }

    /**
    * Prepares a string for a text area output in tests
    *
    * @param string $txt_output String which should be prepared for output
    * @access public
    */
    public function prepareTextareaOutput($txt_output, $prepare_for_latex_output = false, $omitNl2BrWhenTextArea = false)
    {
        if ($txt_output == null) {
            $txt_output = '';
        }
        return ilLegacyFormElementsUtil::prepareTextareaOutput(
            $txt_output,
            $prepare_for_latex_output,
            $omitNl2BrWhenTextArea
        );
    }

    public function getAnonymity(): bool
    {
        return $this->getMainSettings()->getGeneralSettings()->getAnonymity();
    }


    public static function _lookupAnonymity($a_obj_id): int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT anonymity FROM tst_tests WHERE obj_fi = %s",
            ['integer'],
            [$a_obj_id]
        );
        while ($row = $ilDB->fetchAssoc($result)) {
            return (int) $row['anonymity'];
        }
        return 0;
    }

    public function getShowCancel(): bool
    {
        return $this->getMainSettings()->getParticipantFunctionalitySettings()->getSuspendTestAllowed();
    }

    public function getShowMarker(): bool
    {
        return $this->getMainSettings()->getParticipantFunctionalitySettings()->getQuestionMarkingEnabled();
    }

    public function getFixedParticipants(): bool
    {
        return $this->getMainSettings()->getAccessSettings()->getFixedParticipants();
    }

    /**
     * returns the question set type of test relating to passed active id
     *
     * @param integer $activeId
     * @return string $questionSetType
     */
    public static function lookupQuestionSetTypeByActiveId($active_id): ?string
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "
			SELECT		tst_tests.question_set_type
			FROM		tst_active
			INNER JOIN	tst_tests
			ON			tst_active.test_fi = tst_tests.test_id
			WHERE		tst_active.active_id = %s
		";

        $res = $ilDB->queryF($query, ['integer'], [$active_id]);

        while ($row = $ilDB->fetchAssoc($res)) {
            return $row['question_set_type'];
        }

        return null;
    }

    /**
     * Returns the full name of a test user according to the anonymity status
     *
     * @param int $user_id The database ID of the user
     * @param boolean $overwrite_anonymity Indicates if the anonymity status should be ignored
     * @return string The full name of the user or UNKNOWN if the anonymity status is affected
     * @access public
     *
     * @deprecated: use ilTestParticipantData instead
     */
    public function userLookupFullName($user_id, $overwrite_anonymity = false, $sorted_order = false, $suffix = ""): string
    {
        if ($this->getAnonymity() && !$overwrite_anonymity) {
            return $this->lng->txt("anonymous") . $suffix;
        } else {
            $uname = ilObjUser::_lookupName($user_id);
            if (strlen($uname["firstname"] . $uname["lastname"]) == 0) {
                $uname["firstname"] = $this->lng->txt("deleted_user");
            }
            if ($sorted_order) {
                return trim($uname["lastname"] . ", " . $uname["firstname"]) . $suffix;
            } else {
                return trim($uname["firstname"] . " " . $uname["lastname"]) . $suffix;
            }
        }
    }

    /**
     * Returns the available test defaults for the active user
     * @return array An array containing the defaults
     * @access public
     */
    public function getAvailableDefaults(): array
    {
        $result = $this->db->queryF(
            "SELECT * FROM tst_test_defaults WHERE user_fi = %s ORDER BY name ASC",
            ['integer'],
            [$this->user->getId()]
        );
        $defaults = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $defaults[$row["test_defaults_id"]] = $row;
        }
        return $defaults;
    }

    public function getTestDefaults($test_defaults_id): ?array
    {
        $result = $this->db->queryF(
            "SELECT * FROM tst_test_defaults WHERE test_defaults_id = %s",
            ['integer'],
            [$test_defaults_id]
        );
        if ($result->numRows() == 1) {
            $row = $this->db->fetchAssoc($result);
            return $row;
        } else {
            return null;
        }
    }

    public function deleteDefaults($test_default_id)
    {
        $this->db->manipulateF(
            "DELETE FROM tst_test_defaults WHERE test_defaults_id = %s",
            ['integer'],
            [$test_default_id]
        );
    }

    /**
    * Adds the defaults of this test to the test defaults
    *
    * @param string $a_name The name of the test defaults
    * @access public
    */
    public function addDefaults($a_name)
    {
        $main_settings = $this->getMainSettings();
        $score_settings = $this->getScoreSettings();
        $testsettings = [
            'questionSetType' => $main_settings->getGeneralSettings()->getQuestionSetType(),
            'Anonymity' => (int) $main_settings->getGeneralSettings()->getAnonymity(),

            'activation_limited' => $this->isActivationLimited(),
            'activation_start_time' => $this->getActivationStartingTime(),
            'activation_end_time' => $this->getActivationEndingTime(),
            'activation_visibility' => $this->getActivationVisibility(),

            'IntroEnabled' => (int) $main_settings->getIntroductionSettings()->getIntroductionEnabled(),
            'ExamConditionsCheckboxEnabled' => (int) $main_settings->getIntroductionSettings()->getExamConditionsCheckboxEnabled(),

            'StartingTimeEnabled' => (int) $main_settings->getAccessSettings()->getStartTimeEnabled(),
            'StartingTime' => $main_settings->getAccessSettings()->getStartTime(),
            'EndingTimeEnabled' => (int) $main_settings->getAccessSettings()->getEndTimeEnabled(),
            'EndingTime' => $main_settings->getAccessSettings()->getEndTime(),
            'password_enabled' => (int) $main_settings->getAccessSettings()->getPasswordEnabled(),
            'password' => $main_settings->getAccessSettings()->getPassword(),
            'fixed_participants' => (int) $main_settings->getAccessSettings()->getFixedParticipants(),

            'NrOfTries' => $main_settings->getTestBehaviourSettings()->getNumberOfTries(),
            'BlockAfterPassed' => (int) $main_settings->getTestBehaviourSettings()->getBlockAfterPassedEnabled(),
            'pass_waiting' => $main_settings->getTestBehaviourSettings()->getPassWaiting(),
            'EnableProcessingTime' => (int) $main_settings->getTestBehaviourSettings()->getProcessingTimeEnabled(),
            'ProcessingTime' => $main_settings->getTestBehaviourSettings()->getProcessingTime(),
            'ResetProcessingTime' => $main_settings->getTestBehaviourSettings()->getResetProcessingTime(),
            'Kiosk' => $main_settings->getTestBehaviourSettings()->getKioskMode(),
            'examid_in_test_pass' => (int) $main_settings->getTestBehaviourSettings()->getExamIdInTestPassEnabled(),

            'TitleOutput' => $main_settings->getQuestionBehaviourSettings()->getQuestionTitleOutputMode(),
            'autosave' => (int) $main_settings->getQuestionBehaviourSettings()->getAutosaveEnabled(),
            'autosave_ival' => $main_settings->getQuestionBehaviourSettings()->getAutosaveInterval(),
            'Shuffle' => (int) $main_settings->getQuestionBehaviourSettings()->getShuffleQuestions(),
            'offer_question_hints' => (int) $main_settings->getQuestionBehaviourSettings()->getQuestionHintsEnabled(),
            'AnswerFeedbackPoints' => (int) $main_settings->getQuestionBehaviourSettings()->getInstantFeedbackPointsEnabled(),
            'AnswerFeedback' => (int) $main_settings->getQuestionBehaviourSettings()->getInstantFeedbackGenericEnabled(),
            'SpecificAnswerFeedback' => (int) $main_settings->getQuestionBehaviourSettings()->getInstantFeedbackSpecificEnabled(),
            'InstantFeedbackSolution' => (int) $main_settings->getQuestionBehaviourSettings()->getInstantFeedbackSolutionEnabled(),
            'force_inst_fb' => (int) $main_settings->getQuestionBehaviourSettings()->getForceInstantFeedbackOnNextQuestion(),
            'follow_qst_answer_fixation' => (int) $main_settings->getQuestionBehaviourSettings()->getLockAnswerOnNextQuestionEnabled(),
            'inst_fb_answer_fixation' => (int) $main_settings->getQuestionBehaviourSettings()->getLockAnswerOnInstantFeedbackEnabled(),
            'obligations_enabled' => (int) $main_settings->getQuestionBehaviourSettings()->getCompulsoryQuestionsEnabled(),

            'use_previous_answers' => (int) $main_settings->getParticipantFunctionalitySettings()->getUsePreviousAnswerAllowed(),
            'ShowCancel' => (int) $main_settings->getParticipantFunctionalitySettings()->getSuspendTestAllowed(),
            'SequenceSettings' => (int) $main_settings->getParticipantFunctionalitySettings()->getPostponedQuestionsMoveToEnd(),
            'ListOfQuestionsSettings' => $main_settings->getParticipantFunctionalitySettings()->getUsrPassOverviewMode(),
            'ShowMarker' => (int) $main_settings->getParticipantFunctionalitySettings()->getQuestionMarkingEnabled(),

            'enable_examview' => $main_settings->getFinishingSettings()->getShowAnswerOverview(),
            'ShowFinalStatement' => (int) $main_settings->getFinishingSettings()->getConcludingRemarksEnabled(),
            'redirection_mode' => $main_settings->getFinishingSettings()->getRedirectionMode(),
            'redirection_url' => $main_settings->getFinishingSettings()->getRedirectionUrl(),
            'mailnotification' => $main_settings->getFinishingSettings()->getMailNotificationContentType(),
            'mailnottype' => (int) $main_settings->getFinishingSettings()->getAlwaysSendMailNotification(),

            'skill_service' => (int) $main_settings->getAdditionalSettings()->getSkillsServiceEnabled(),

            'PassScoring' => $score_settings->getScoringSettings()->getPassScoring(),
            'ScoreCutting' => $score_settings->getScoringSettings()->getScoreCutting(),
            'CountSystem' => $score_settings->getScoringSettings()->getCountSystem(),

            'ScoreReporting' => $score_settings->getResultSummarySettings()->getScoreReporting(),
            'ReportingDate' => $score_settings->getResultSummarySettings()->getReportingDate(),
            'pass_deletion_allowed' => (int) $score_settings->getResultSummarySettings()->getPassDeletionAllowed(),
            'show_grading_status' => (int) $score_settings->getResultSummarySettings()->getShowGradingStatusEnabled(),
            'show_grading_mark' => (int) $score_settings->getResultSummarySettings()->getShowGradingMarkEnabled(),

            'ResultsPresentation' => $score_settings->getResultDetailsSettings()->getResultsPresentation(),
            'show_solution_list_comparison' => (int) $score_settings->getResultDetailsSettings()->getShowSolutionListComparison(),
            'examid_in_test_res' => (int) $score_settings->getResultDetailsSettings()->getShowExamIdInTestResults(),

            'highscore_enabled' => (int) $score_settings->getGamificationSettings()->getHighscoreEnabled(),
            'highscore_anon' => (int) $score_settings->getGamificationSettings()->getHighscoreAnon(),
            'highscore_achieved_ts' => $score_settings->getGamificationSettings()->getHighscoreAchievedTS(),
            'highscore_score' => $score_settings->getGamificationSettings()->getHighscoreScore(),
            'highscore_percentage' => $score_settings->getGamificationSettings()->getHighscorePercentage(),
            'highscore_hints' => $score_settings->getGamificationSettings()->getHighscoreHints(),
            'highscore_wtime' => $score_settings->getGamificationSettings()->getHighscoreWTime(),
            'highscore_own_table' => $score_settings->getGamificationSettings()->getHighscoreOwnTable(),
            'highscore_top_table' => $score_settings->getGamificationSettings()->getHighscoreTopTable(),
            'highscore_top_num' => $score_settings->getGamificationSettings()->getHighscoreTopNum(),

            'HideInfoTab' => (int) $main_settings->getAdditionalSettings()->getHideInfoTab(),
        ];

        $next_id = $this->db->nextId('tst_test_defaults');
        $this->db->insert(
            'tst_test_defaults',
            [
                'test_defaults_id' => ['integer', $next_id],
                'name' => ['text', $a_name],
                'user_fi' => ['integer', $this->user->getId()],
                'defaults' => ['clob', serialize($testsettings)],
                'marks' => ['clob', serialize($this->mark_schema->getMarkSteps())],
                'tstamp' => ['integer', time()]
            ]
        );
    }

    /**
     * Applies given test defaults to this test
     *
     * @param array $test_default The test defaults database id.
     *
     * @return boolean TRUE if the application succeeds, FALSE otherwise
     */
    public function applyDefaults($test_defaults): bool
    {
        $testsettings = unserialize($test_defaults['defaults']);
        $unserialized_marks = unserialize($test_defaults['marks']);

        if ($unserialized_marks instanceof ASS_MarkSchema) {
            $unserialized_marks = $unserialized_marks->getMarkSteps();
        }

        $this->mark_schema->setMarkSteps($unserialized_marks);

        $this->storeActivationSettings([
            'is_activation_limited' => $testsettings['activation_limited'],
            'activation_starting_time' => $testsettings['activation_start_time'],
            'activation_ending_time' => $testsettings['activation_end_time'],
            'activation_visibility' => $testsettings['activation_visibility']
        ]);

        $main_settings = $this->getMainSettings();
        $main_settings = $main_settings
            ->withGeneralSettings(
                $main_settings->getGeneralSettings()
                ->withQuestionSetType($testsettings['questionSetType'])
                ->withAnonymity((bool) $testsettings['Anonymity'])
            )
            ->withIntroductionSettings(
                $main_settings->getIntroductionSettings()
                ->withIntroductionEnabled((bool) $testsettings['IntroEnabled'])
                ->withExamConditionsCheckboxEnabled((bool) ($testsettings['ExamConditionsCheckboxEnabled'] ?? false))
            )
            ->withAccessSettings(
                $main_settings->getAccessSettings()
                ->withStartTimeEnabled((bool) $testsettings['StartingTimeEnabled'])
                ->withStartTime($this->convertTimeToDateTimeImmutableIfNecessary($testsettings['StartingTime']))
                ->withEndTimeEnabled((bool) $testsettings['EndingTimeEnabled'])
                ->withEndTime($this->convertTimeToDateTimeImmutableIfNecessary($testsettings['EndingTime']))
                ->withPasswordEnabled((bool) $testsettings['password_enabled'])
                ->withPassword($testsettings['password'])
                ->withFixedParticipants((bool) $testsettings['fixed_participants'])
            )
            ->withTestBehaviourSettings(
                $main_settings->getTestBehaviourSettings()
                ->withNumberOfTries($testsettings['NrOfTries'])
                ->withBlockAfterPassedEnabled((bool) $testsettings['BlockAfterPassed'])
                ->withPassWaiting($testsettings['pass_waiting'])
                ->withKioskMode($testsettings['Kiosk'])
                ->withProcessingTimeEnabled((bool) $testsettings['EnableProcessingTime'])
                ->withProcessingTime($testsettings['ProcessingTime'])
                ->withResetProcessingTime((bool) $testsettings['ResetProcessingTime'])
                ->withExamIdInTestPassEnabled((bool) ($testsettings['examid_in_test_pass'] ?? 0))
            )
            ->withQuestionBehaviourSettings(
                $main_settings->getQuestionBehaviourSettings()
                ->withQuestionTitleOutputMode($testsettings['TitleOutput'])
                ->withAutosaveEnabled((bool) $testsettings['autosave'])
                ->withAutosaveInterval($testsettings['autosave_ival'])
                ->withShuffleQuestions((bool) $testsettings['Shuffle'])
                ->withQuestionHintsEnabled((bool) $testsettings['offer_question_hints'])
                ->withInstantFeedbackPointsEnabled((bool) $testsettings['AnswerFeedbackPoints'])
                ->withInstantFeedbackGenericEnabled((bool) $testsettings['AnswerFeedback'])
                ->withInstantFeedbackSpecificEnabled((bool) $testsettings['SpecificAnswerFeedback'])
                ->withInstantFeedbackSolutionEnabled((bool) $testsettings['InstantFeedbackSolution'])
                ->withForceInstantFeedbackOnNextQuestion((bool) $testsettings['force_inst_fb'])
                ->withLockAnswerOnInstantFeedbackEnabled((bool) $testsettings['inst_fb_answer_fixation'])
                ->withLockAnswerOnNextQuestionEnabled((bool) $testsettings['follow_qst_answer_fixation'])
                ->withCompulsoryQuestionsEnabled((bool) $testsettings['obligations_enabled'])
            )
            ->withParticipantFunctionalitySettings(
                $main_settings->getParticipantFunctionalitySettings()
                ->withUsePreviousAnswerAllowed((bool) $testsettings['use_previous_answers'])
                ->withSuspendTestAllowed((bool) $testsettings['ShowCancel'])
                ->withPostponedQuestionsMoveToEnd((bool) $testsettings['SequenceSettings'])
                ->withUsrPassOverviewMode($testsettings['ListOfQuestionsSettings'])
                ->withQuestionMarkingEnabled((bool) $testsettings['ShowMarker'])
            )
            ->withFinishingSettings(
                $main_settings->getFinishingSettings()
                ->withShowAnswerOverview((bool) $testsettings['enable_examview'])
                ->withConcludingRemarksEnabled((bool) $testsettings['ShowFinalStatement'])
                ->withRedirectionMode((int) $testsettings['redirection_mode'])
                ->withRedirectionUrl($testsettings['redirection_url'])
                ->withMailNotificationContentType((int) $testsettings['mailnotification'])
                ->withAlwaysSendMailNotification((bool) $testsettings['mailnottype'])
            )
            ->withAdditionalSettings(
                $main_settings->getAdditionalSettings()
                    ->withSkillsServiceEnabled((bool) $testsettings['skill_service'])
                    ->withHideInfoTab((bool) ($testsettings['HideInfoTab'] ?? false))
            );

        $this->getMainSettingsRepository()->store($main_settings);

        $reporting_date = $testsettings['ReportingDate'];
        if (is_string($reporting_date)) {
            $reporting_date = DateTimeImmutable($testsettings['ReportingDate']);
        }

        $score_settings = $this->getScoreSettings();
        $score_settings = $score_settings
            ->withScoringSettings(
                $score_settings->getScoringSettings()
                ->withPassScoring($testsettings['PassScoring'])
                ->withScoreCutting($testsettings['ScoreCutting'])
                ->withCountSystem($testsettings['CountSystem'])
            )
            ->withResultSummarySettings(
                $score_settings->getResultSummarySettings()
                ->withPassDeletionAllowed((bool) $testsettings['pass_deletion_allowed'])
                ->withShowGradingStatusEnabled((bool) $testsettings['show_grading_status'])
                ->withShowGradingMarkEnabled((bool) $testsettings['show_grading_mark'])
                ->withScoreReporting((int) $testsettings['ScoreReporting'])
                ->withReportingDate($reporting_date)
            )
            ->withResultDetailsSettings(
                $score_settings->getResultDetailsSettings()
                ->withResultsPresentation((int) $testsettings['ResultsPresentation'])
                ->withShowSolutionListComparison((bool) ($testsettings['show_solution_list_comparison'] ?? 0))
                ->withShowExamIdInTestResults((bool) $testsettings['examid_in_test_res'])
            )
            ->withGamificationSettings(
                $score_settings->getGamificationSettings()
                ->withHighscoreEnabled((bool) $testsettings['highscore_enabled'])
                ->withHighscoreAnon((bool) $testsettings['highscore_anon'])
                ->withHighscoreAchievedTS($testsettings['highscore_achieved_ts'])
                ->withHighscoreScore((bool) $testsettings['highscore_score'])
                ->withHighscorePercentage($testsettings['highscore_percentage'])
                ->withHighscoreHints((bool) $testsettings['highscore_hints'])
                ->withHighscoreWTime((bool) $testsettings['highscore_wtime'])
                ->withHighscoreOwnTable((bool) $testsettings['highscore_own_table'])
                ->withHighscoreTopTable((bool) $testsettings['highscore_top_table'])
                ->withHighscoreTopNum($testsettings['highscore_top_num'])
            )
        ;
        $this->getScoreSettingsRepository()->store($score_settings);
        $this->saveToDb();

        return true;
    }

    private function convertTimeToDateTimeImmutableIfNecessary(
        DateTimeImmutable|int|null $date_time
    ): ?DateTimeImmutable {
        if ($date_time === null || $date_time instanceof DateTimeImmutable) {
            return $date_time;
        }

        return DateTimeImmutable::createFromFormat('U', (string) $date_time);
    }

    /**
    * Convert a print output to XSL-FO
    *
    * @param string $print_output The print output
    * @return string XSL-FO code
    * @access public
    */
    public function processPrintoutput2FO($print_output): string
    {
        if (extension_loaded("tidy")) {
            $config = [
                "indent" => false,
                "output-xml" => true,
                "numeric-entities" => true
            ];
            $tidy = new tidy();
            $tidy->parseString($print_output, $config, 'utf8');
            $tidy->cleanRepair();
            $print_output = tidy_get_output($tidy);
            $print_output = preg_replace("/^.*?(<html)/", "\\1", $print_output);
        } else {
            $print_output = str_replace("&nbsp;", "&#160;", $print_output);
            $print_output = str_replace("&otimes;", "X", $print_output);
        }
        $xsl = file_get_contents("./Modules/Test/xml/question2fo.xsl");

        // additional font support

        $xsl = str_replace(
            'font-family="Helvetica, unifont"',
            'font-family="' . $this->settings->get('rpc_pdf_font', 'Helvetica, unifont') . '"',
            $xsl
        );

        $args = [ '/_xml' => $print_output, '/_xsl' => $xsl ];
        $xh = xslt_create();
        $params = [];
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
        xslt_error($xh);
        xslt_free($xh);
        return $output;
    }

    /**
    * Delivers a PDF file from XHTML
    *
    * @param string $html The XHTML string
    * @access public
    */
    public function deliverPDFfromHTML($content, $title = null)
    {
        $content = preg_replace("/href=\".*?\"/", "", $content);
        $printbody = new ilTemplate("tpl.il_as_tst_print_body.html", true, true, "Modules/Test");
        $printbody->setVariable("TITLE", ilLegacyFormElementsUtil::prepareFormOutput($this->getTitle()));
        $printbody->setVariable("ADM_CONTENT", $content);
        $printbody->setCurrentBlock("css_file");
        $printbody->setVariable("CSS_FILE", ilUtil::getStyleSheetLocation("filesystem", "delos.css"));
        $printbody->parseCurrentBlock();
        $printoutput = $printbody->get();
        $html = str_replace("href=\"./", "href=\"" . ILIAS_HTTP_PATH . "/", $printoutput);
        $html = preg_replace("/<div id=\"dontprint\">.*?<\\/div>/ims", "", $html);
        if (extension_loaded("tidy")) {
            $config = [
                "indent" => false,
                "output-xml" => true,
                "numeric-entities" => true
            ];
            $tidy = new tidy();
            $tidy->parseString($html, $config, 'utf8');
            $tidy->cleanRepair();
            $html = tidy_get_output($tidy);
            $html = preg_replace("/^.*?(<html)/", "\\1", $html);
        } else {
            $html = str_replace("&nbsp;", "&#160;", $html);
            $html = str_replace("&otimes;", "X", $html);
        }
        $html = preg_replace("/src=\".\\//ims", "src=\"" . ILIAS_HTTP_PATH . "/", $html);
        $this->deliverPDFfromFO($this->processPrintoutput2FO($html), $title);
    }

    /**
    * Delivers a PDF file from a XSL-FO string
    * @param string $fo The XSL-FO string
    * @access public
    */
    public function deliverPDFfromFO($fo, $title = null): bool
    {
        $fo_file = ilFileUtils::ilTempnam() . ".fo";
        $fp = fopen($fo_file, "w");
        fwrite($fp, $fo);
        fclose($fp);

        try {
            $pdf_base64 = ilRpcClientFactory::factory('RPCTransformationHandler')->ilFO2PDF($fo);
            $filename = (strlen($title)) ? $title : $this->getTitle();
            /** @noinspection PhpUndefinedFieldInspection */
            ilUtil::deliverData(
                $pdf_base64->scalar,
                ilFileUtils::getASCIIFilename($filename) . ".pdf",
                "application/pdf"
            );
            return true;
        } catch (Exception $e) {
            $this->log->write(__METHOD__ . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
    * Retrieves the feedback comment for a question in a test if it is finalized
    *
    * @param integer $active_id Active ID of the user
    * @param integer $question_id Question ID
    * @param integer $pass Pass number
    * @return string The feedback text
    * @access public
    */
    public static function getManualFeedback(int $active_id, int $question_id, ?int $pass): string
    {
        if ($pass === null) {
            return '';
        }
        $feedback = '';
        $row = self::getSingleManualFeedback((int) $active_id, (int) $question_id, (int) $pass);

        if ($row !== [] && ($row['finalized_evaluation'] || \ilTestService::isManScoringDone((int) $active_id))) {
            $feedback = $row['feedback'] ?? '';
        }

        return $feedback;
    }

    public static function getSingleManualFeedback(int $active_id, int $question_id, int $pass): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $row = [];
        $result = $ilDB->queryF(
            "SELECT * FROM tst_manual_fb WHERE active_fi = %s AND question_fi = %s AND pass = %s",
            ['integer', 'integer', 'integer'],
            [$active_id, $question_id, $pass]
        );

        if ($ilDB->numRows($result) === 1) {
            $row = $ilDB->fetchAssoc($result);
            $row['feedback'] = ilRTE::_replaceMediaObjectImageSrc($row['feedback'] ?? '', 1);
        } elseif ($ilDB->numRows($result) > 1) {
            $DIC->logger()->root()->warning(
                "WARNING: Multiple feedback entries on tst_manual_fb for " .
                "active_fi = $active_id , question_fi = $question_id and pass = $pass"
            );
        }

        return $row;
    }

    /**
     * Retrieves the manual feedback for a question in a test
     *
     * @param integer $question_id Question ID
     * @return array The feedback text
     * @access public
     */
    public static function getCompleteManualFeedback(int $question_id): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $feedback = [];
        $result = $ilDB->queryF(
            "SELECT * FROM tst_manual_fb WHERE question_fi = %s",
            ['integer'],
            [$question_id]
        );

        while ($row = $ilDB->fetchAssoc($result)) {
            $active = $row['active_fi'];
            $pass = $row['pass'];
            $question = $row['question_fi'];

            $row['feedback'] = ilRTE::_replaceMediaObjectImageSrc($row['feedback'] ?? '', 1);

            $feedback[$active][$pass][$question] = $row;
        }

        return $feedback;
    }

    public function saveManualFeedback(
        int $active_id,
        int $question_id,
        int $pass,
        ?string $feedback,
        bool $finalized = false,
        bool $is_single_feedback = false
    ): bool {
        $feedback_old = self::getSingleManualFeedback($active_id, $question_id, $pass);

        $finalized_record = (int) ($feedback_old['finalized_evaluation'] ?? 0);
        if ($finalized_record === 0 || ($is_single_feedback && $finalized_record === 1)) {
            $this->db->manipulateF(
                "DELETE FROM tst_manual_fb WHERE active_fi = %s AND question_fi = %s AND pass = %s",
                ['integer', 'integer', 'integer'],
                [$active_id, $question_id, $pass]
            );

            $this->insertManualFeedback($active_id, $question_id, $pass, $feedback, $finalized, $feedback_old);

            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                $this->logManualFeedback($active_id, $question_id, $feedback);
            }
        }

        return true;
    }

    private function insertManualFeedback(
        int $active_id,
        int $question_id,
        int $pass,
        ?string $feedback,
        bool $finalized,
        array $feedback_old
    ): void {
        $next_id = $this->db->nextId('tst_manual_fb');
        $user = $this->user->getId();
        $finalized_time = time();

        $update_default = [
            'manual_feedback_id' => [ 'integer', $next_id],
            'active_fi' => [ 'integer', $active_id],
            'question_fi' => [ 'integer', $question_id],
            'pass' => [ 'integer', $pass],
            'feedback' => [ 'clob', $feedback ? ilRTE::_replaceMediaObjectImageSrc($feedback, 0) : null],
            'tstamp' => [ 'integer', time()]
        ];

        if ($feedback_old !== [] && (int) $feedback_old['finalized_evaluation'] === 1) {
            $user = $feedback_old['finalized_by_usr_id'];
            $finalized_time = $feedback_old['finalized_tstamp'];
        }

        if ($finalized === false) {
            $update_default['finalized_evaluation'] = ['integer', 0];
            $update_default['finalized_by_usr_id'] = ['integer', 0];
            $update_default['finalized_tstamp'] = ['integer', 0];
        } elseif ($finalized === true) {
            $update_default['finalized_evaluation'] = ['integer', 1];
            $update_default['finalized_by_usr_id'] = ['integer', $user];
            $update_default['finalized_tstamp'] = ['integer', $finalized_time];
        }

        $this->db->insert('tst_manual_fb', $update_default);
    }

    /**
     * Creates a log for the manual feedback
     *
     * @param integer $active_id Active ID of the user
     * @param integer $question_id Question ID
     * @param string  $feedback The feedback text
     */
    private function logManualFeedback($active_id, $question_id, $feedback)
    {
        $username = ilObjTestAccess::_getParticipantData($active_id);

        $this->logAction(
            sprintf(
                $this->lng->txtlng('assessment', 'log_manual_feedback', ilObjAssessmentFolder::_getLogLanguage()),
                $this->user->getFullname() . ' (' . $this->user->getLogin() . ')',
                $username,
                $this->questioninfo->getQuestionTitle($question_id),
                $feedback
            )
        );
    }

    /**
    * Returns if Javascript should be chosen for drag & drop actions
    * for the active user
    *
    * @return boolean TRUE if Javascript should be chosen, FALSE otherwise
    * @access public
    */
    public function getJavaScriptOutput(): bool
    {
        return true;
    }

    public function &createTestSequence($active_id, $pass, $shuffle)
    {
        $this->test_sequence = new ilTestSequence($active_id, $pass, $this->isRandomTest(), $this->questioninfo);
    }

    /**
    * Sets the test ID
    *
    * @param integer $a_id Test ID
    */
    public function setTestId($a_id)
    {
        $this->test_id = $a_id;
    }

    /**
     * returns all test results for all participants
     *
     * @param array $partipants array of user ids
     * @param boolean if true, the result will be prepared for csv output (see processCSVRow)
     *
     * @return array of fields, see code for column titles
     */
    public function getDetailedTestResults($participants): array
    {
        $results = [];
        if (count($participants)) {
            foreach ($participants as $active_id => $user_rec) {
                $row = [];
                $reached_points = 0;
                $max_points = 0;
                $pass = ilObjTest::_getResultPass($active_id);
                foreach ($this->questions as $value) {
                    $question = ilObjTest::_instanciateQuestion($value);
                    if (is_object($question)) {
                        $max_points += $question->getMaximumPoints();
                        $reached_points += $question->getReachedPoints($active_id, $pass);
                        if ($max_points > 0) {
                            $percentvalue = $reached_points / $max_points;
                            if ($percentvalue < 0) {
                                $percentvalue = 0.0;
                            }
                        } else {
                            $percentvalue = 0;
                        }
                        if ($this->getAnonymity()) {
                            $user_rec['firstname'] = "";
                            $user_rec['lastname'] = $this->lng->txt("anonymous");
                        }
                        $row = [
                            "user_id" => $user_rec['usr_id'],
                            "matriculation" => $user_rec['matriculation'],
                            "lastname" => $user_rec['lastname'],
                            "firstname" => $user_rec['firstname'],
                            "login" => $user_rec['login'],
                            "question_id" => $question->getId(),
                            "question_title" => $question->getTitle(),
                            "reached_points" => $reached_points,
                            "max_points" => $max_points,
                            "passed" => $user_rec['passed'] ? '1' : '0',
                        ];
                        $results[] = $row;
                    }
                }
            }
        }
        return $results;
    }

    /**
    * Get test Object ID for question ID
    */
    public static function _lookupTestObjIdForQuestionId(int $q_id): ?int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            'SELECT t.obj_fi obj_id FROM tst_test_question q, tst_tests t WHERE q.test_fi = t.test_id AND q.question_fi = %s',
            ['integer'],
            [$q_id]
        );
        $rec = $ilDB->fetchAssoc($result);
        return $rec['obj_id'] ?? null;
    }

    /**
    * Checks wheather or not a question plugin with a given name is active
    *
    * @param string $a_pname The plugin name
    * @access public
    */
    public function isPluginActive($a_pname): bool
    {
        if (!$this->component_repository->getComponentByTypeAndName(
            ilComponentInfo::TYPE_MODULES,
            'TestQuestionPool'
        )->getPluginSlotById('qst')->hasPluginName($a_pname)) {
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
                $a_pname
            )->isActive();
    }

    public function getPassed($active_id)
    {
        $result = $this->db->queryF(
            "SELECT passed FROM tst_result_cache WHERE active_fi = %s",
            ['integer'],
            [$active_id]
        );
        if ($result->numRows()) {
            $row = $this->db->fetchAssoc($result);
            return $row['passed'];
        } else {
            $counted_pass = ilObjTest::_getResultPass($active_id);
            $result_array = &$this->getTestResult($active_id, $counted_pass);
            return $result_array["test"]["passed"];
        }
    }

    /**
     * Creates an associated array with all active id's for a given test and original question id
     */
    public function getParticipantsForTestAndQuestion($test_id, $question_id): array
    {
        $query = "
			SELECT tst_test_result.active_fi, tst_test_result.question_fi, tst_test_result.pass
			FROM tst_test_result
			INNER JOIN tst_active ON tst_active.active_id = tst_test_result.active_fi AND tst_active.test_fi = %s
			INNER JOIN qpl_questions ON qpl_questions.question_id = tst_test_result.question_fi
			LEFT JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
			WHERE tst_test_result.question_fi = %s
			ORDER BY usr_data.lastname ASC, usr_data.firstname ASC
		";

        $result = $this->db->queryF(
            $query,
            ['integer', 'integer'],
            [$test_id, $question_id]
        );
        $foundusers = [];
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($row = $this->db->fetchAssoc($result)) {
            if ($this->getAccessFilteredParticipantList() && !$this->getAccessFilteredParticipantList()->isActiveIdInList($row["active_fi"])) {
                continue;
            }

            if (!array_key_exists($row["active_fi"], $foundusers)) {
                $foundusers[$row["active_fi"]] = [];
            }
            array_push($foundusers[$row["active_fi"]], ["pass" => $row["pass"], "qid" => $row["question_fi"]]);
        }
        return $foundusers;
    }

    /**
    * Returns the aggregated test results
    *
    * @access public
    */
    public function getAggregatedResultsData(): array
    {
        $data = &$this->getCompleteEvaluationData();
        $foundParticipants = $data->getParticipants();
        $results = ["overview" => [], "questions" => []];
        if (count($foundParticipants)) {
            $results["overview"][$this->lng->txt("tst_eval_total_persons")] = count($foundParticipants);
            $total_finished = $data->getTotalFinishedParticipants();
            $results["overview"][$this->lng->txt("tst_eval_total_finished")] = $total_finished;
            $average_time = $this->evalTotalStartedAverageTime($data->getParticipantIds());
            $diff_seconds = $average_time;
            $diff_hours = floor($diff_seconds / 3600);
            $diff_seconds -= $diff_hours * 3600;
            $diff_minutes = floor($diff_seconds / 60);
            $diff_seconds -= $diff_minutes * 60;
            $results["overview"][$this->lng->txt("tst_eval_total_finished_average_time")] = sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds);
            $total_passed = 0;
            $total_passed_reached = 0;
            $total_passed_max = 0;
            $total_passed_time = 0;
            foreach ($foundParticipants as $userdata) {
                if ($userdata->getPassed()) {
                    $total_passed++;
                    $total_passed_reached += $userdata->getReached();
                    $total_passed_max += $userdata->getMaxpoints();
                    $total_passed_time += $userdata->getTimeOfWork();
                }
            }
            $average_passed_reached = $total_passed ? $total_passed_reached / $total_passed : 0;
            $average_passed_max = $total_passed ? $total_passed_max / $total_passed : 0;
            $average_passed_time = $total_passed ? $total_passed_time / $total_passed : 0;
            $results["overview"][$this->lng->txt("tst_eval_total_passed")] = $total_passed;
            $results["overview"][$this->lng->txt("tst_eval_total_passed_average_points")] = sprintf("%2.2f", $average_passed_reached) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%2.2f", $average_passed_max);
            $average_time = $average_passed_time;
            $diff_seconds = $average_time;
            $diff_hours = floor($diff_seconds / 3600);
            $diff_seconds -= $diff_hours * 3600;
            $diff_minutes = floor($diff_seconds / 60);
            $diff_seconds -= $diff_minutes * 60;
            $results["overview"][$this->lng->txt("tst_eval_total_passed_average_time")] = sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds);
        }

        foreach ($data->getQuestionTitles() as $question_id => $question_title) {
            $answered = 0;
            $reached = 0;
            $max = 0;
            foreach ($foundParticipants as $userdata) {
                for ($i = 0; $i <= $userdata->getLastPass(); $i++) {
                    if (is_object($userdata->getPass($i))) {
                        $question = $userdata->getPass($i)->getAnsweredQuestionByQuestionId($question_id);
                        if (is_array($question)) {
                            $answered++;
                            $reached += $question["reached"];
                            $max += $question["points"];
                        }
                    }
                }
            }
            $percent = $max ? $reached / $max * 100.0 : 0;
            $results["questions"][$question_id] = [
                $question_title,
                sprintf("%.2f", $answered ? $reached / $answered : 0) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%.2f", $answered ? $max / $answered : 0),
                sprintf("%.2f", $percent) . "%",
                $answered,
                sprintf("%.2f", $answered ? $reached / $answered : 0),
                sprintf("%.2f", $answered ? $max / $answered : 0),
                $percent / 100.0
            ];
        }
        return $results;
    }

    /**
    * Get zipped xml file for test
    */
    public function getXMLZip(): string
    {
        $expFactory = new ilTestExportFactory($this, $this->lng, $this->log, $this->tree, $this->component_repository, $this->questioninfo);
        $test_exp = $expFactory->getExporter('xml');
        return $test_exp->buildExportFile();
    }

    public function getMailNotification(): int
    {
        return $this->getMainSettings()->getFinishingSettings()->getMailNotificationContentType();
    }

    public function sendSimpleNotification($active_id)
    {
        $mail = new ilTestMailNotification();
        $owner_id = $this->getOwner();
        $usr_data = $this->userLookupFullName(ilObjTest::_getUserIdFromActiveId($active_id));
        $mail->sendSimpleNotification($owner_id, $this->getTitle(), $usr_data);
    }

    /**
     * Gets additional user fields that should be shown in the user evaluation
     *
     * @return array An array containing the database fields that should be shown in the evaluation
     */
    public function getEvaluationAdditionalFields(): array
    {
        $table_gui = new ilEvaluationAllTableGUI(
            new ilObjTestGUI($this->getRefId()),
            'outEvaluation',
            $this->settings,
            $this->getAnonymity()
        );
        return $table_gui->getSelectedColumns();
    }

    public function sendAdvancedNotification(int $active_id): void
    {
        $mail = new ilTestMailNotification();
        $owner_id = $this->getOwner();
        $usr_data = $this->userLookupFullName(ilObjTest::_getUserIdFromActiveId($active_id));

        $worksheet = (new ilExcelTestExport($this, ilTestEvaluationData::FILTER_BY_ACTIVE_ID, (string) $active_id, false, true))
            ->withResultsPage()
            ->withUserPages()
            ->getContent();
        $temp_file_path = ilFileUtils::ilTempnam();
        $delivered_file_name = 'result_' . $active_id . '.xlsx';
        $worksheet->writeToFile($temp_file_path);
        $fd = new ilFileDataMail(ANONYMOUS_USER_ID);
        $fd->copyAttachmentFile($temp_file_path . '.xlsx', $delivered_file_name);
        $file_names[] = $delivered_file_name;

        $mail->sendAdvancedNotification($owner_id, $this->getTitle(), $usr_data, $file_names);

        if (count($file_names)) {
            $fd->unlinkFiles($file_names);
            unset($fd);
            @unlink($file . 'xlsx');
        }
    }

    public function getResultsForActiveId(int $active_id): array
    {
        $query = "
			SELECT		*
			FROM		tst_result_cache
			WHERE		active_fi = %s
		";

        $result = $this->db->queryF(
            $query,
            ['integer'],
            [$active_id]
        );

        if (!$result->numRows()) {
            $this->updateTestResultCache($active_id);

            $query = "
				SELECT		*
				FROM		tst_result_cache
				WHERE		active_fi = %s
			";

            $result = $this->db->queryF(
                $query,
                ['integer'],
                [$active_id]
            );
        }

        $row = $this->db->fetchAssoc($result);

        return $row;
    }

    public function getMailNotificationType(): bool
    {
        return $this->getMainSettings()->getFinishingSettings()->getAlwaysSendMailNotification();
    }

    public function getExportSettings(): int
    {
        return $this->getScoreSettings()->getResultDetailsSettings()->getExportSettings();
    }

    public function setTemplate(int $template_id)
    {
        $this->template_id = $template_id;
    }

    public function getTemplate(): int
    {
        return $this->template_id;
    }

    public function moveQuestionAfterOLD($previous_question_id, $new_question_id)
    {
        $new_array = [];
        $position = 1;

        $query = 'SELECT question_fi  FROM tst_test_question WHERE test_fi = %s';
        $types = ['integer'];
        $values = [$this->getTestId()];

        $new_question_id += 1;

        $inserted = false;
        $res = $this->db->queryF($query, $types, $values);
        while ($row = $this->db->fetchAssoc($res)) {
            $qid = $row['question_fi'];

            if ($qid == $new_question_id) {
                continue;
            } elseif ($qid == $previous_question_id) {
                $new_array[$position++] = $qid;
                $new_array[$position++] = $new_question_id;
                $inserted = true;
            } else {
                $new_array[$position++] = $qid;
            }
        }

        $update_query = 'UPDATE tst_test_question SET sequence = %s WHERE test_fi = %s AND question_fi = %s';
        $update_types = ['integer', 'integer', 'integer'];

        foreach ($new_array as $position => $qid) {
            $this->db->manipulateF(
                $update_query,
                $update_types,
                $vals = [
                            $position,
                            $this->getTestId(),
                            $qid
                        ]
            );
        }
    }

    public function reindexFixedQuestionOrdering(): ilTestReindexedSequencePositionMap
    {
        $question_set_config = $this->question_set_config_factory->getQuestionSetConfig();
        $reindexed_sequence_position_map = $question_set_config->reindexQuestionOrdering();

        $this->loadQuestions();

        return $reindexed_sequence_position_map;
    }

    public function setQuestionOrderAndObligations($orders, $obligations)
    {
        asort($orders);

        $i = 0;

        foreach ($orders as $id => $position) {
            $i++;

            $obligatory = (
                isset($obligations[$id]) && $obligations[$id] ? 1 : 0
            );

            $query = "
				UPDATE		tst_test_question
				SET			sequence = %s,
							obligatory = %s
				WHERE		question_fi = %s
			";

            $this->db->manipulateF(
                $query,
                ['integer', 'integer', 'integer'],
                [$i, $obligatory, $id]
            );
        }

        $this->loadQuestions();
    }

    public function moveQuestionAfter($question_to_move, $question_before)
    {
        if ($question_before) {
            $query = 'SELECT sequence, test_fi FROM tst_test_question WHERE question_fi = %s';
            $types = ['integer'];
            $values = [$question_before];
            $rset = $this->db->queryF($query, $types, $values);
        }

        if (!$question_before || ($rset && !($row = $this->db->fetchAssoc($rset)))) {
            $row = [
            'sequence' => 0,
            'test_fi' => $this->getTestId(),
        ];
        }

        $update = 'UPDATE tst_test_question SET sequence = sequence + 1 WHERE sequence > %s AND test_fi = %s';
        $types = ['integer', 'integer'];
        $values = [$row['sequence'], $row['test_fi']];
        $this->db->manipulateF($update, $types, $values);

        $update = 'UPDATE tst_test_question SET sequence = %s WHERE question_fi = %s';
        $types = ['integer', 'integer'];
        $values = [$row['sequence'] + 1, $question_to_move];
        $this->db->manipulateF($update, $types, $values);

        $this->reindexFixedQuestionOrdering();
    }

    public function hasQuestionsWithoutQuestionpool(): bool
    {
        $questions = $this->getQuestionTitlesAndIndexes();

        $IN_questions = $this->db->in('q1.question_id', array_keys($questions), false, 'integer');

        $query = "
			SELECT		count(q1.question_id) cnt

			FROM		qpl_questions q1

			INNER JOIN	qpl_questions q2
			ON			q2.question_id = q1.original_id

			WHERE		$IN_questions
			AND		 	q1.obj_fi = q2.obj_fi
		";
        $rset = $this->db->query($query);
        $row = $this->db->fetchAssoc($rset);

        return $row['cnt'] > 0;
    }

    /**
     * Gather all finished tests for user
     *
     * @param int $a_user_id
     * @return array(test id => passed)
     */
    public static function _lookupFinishedUserTests($a_user_id): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT test_fi,MAX(pass) AS pass FROM tst_active" .
            " JOIN tst_pass_result ON (tst_pass_result.active_fi = tst_active.active_id)" .
            " WHERE user_fi=%s" .
            " GROUP BY test_fi",
            ['integer', 'integer'],
            [$a_user_id, 1]
        );
        $all = [];
        while ($row = $ilDB->fetchAssoc($result)) {
            $obj_id = self::_getObjectIDFromTestID($row["test_fi"]);
            $all[$obj_id] = (bool) $row["pass"];
        }
        return $all;
    }
    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function isOfferingQuestionHintsEnabled(): bool
    {
        return $this->getMainSettings()->getQuestionBehaviourSettings()->getQuestionHintsEnabled();
    }

    public function setActivationVisibility($a_value)
    {
        $this->activation_visibility = (bool) $a_value;
    }

    public function getActivationVisibility(): bool
    {
        return $this->activation_visibility;
    }

    public function isActivationLimited(): ?bool
    {
        return $this->activation_limited;
    }

    public function setActivationLimited($a_value)
    {
        $this->activation_limited = (bool) $a_value;
    }

    public function storeActivationSettings(array $settings): void
    {
        if (!$this->ref_id) {
            return;
        }

        $item = new ilObjectActivation();
        if (!$settings['is_activation_limited']) {
            $item->setTimingType(ilObjectActivation::TIMINGS_DEACTIVATED);
        } else {
            $item->setTimingType(ilObjectActivation::TIMINGS_ACTIVATION);
            $item->setTimingStart($settings['activation_starting_time']);
            $item->setTimingEnd($settings['activation_ending_time']);
            $item->toggleVisible($settings['activation_visibility']);
        }

        $item->update($this->ref_id);

        $this->setActivationLimited($settings['is_activation_limited']);
        $this->setActivationStartingTime($settings['activation_starting_time']);
        $this->setActivationStartingTime($settings['activation_ending_time']);
        $this->setActivationVisibility($settings['activation_visibility']);
    }

    public function getIntroductionPageId(): int
    {
        $page_id = $this->getMainSettings()->getIntroductionSettings()->getIntroductionPageId();
        if ($page_id !== null) {
            return $page_id;
        }

        $page_object = new ilTestPage();
        $page_object->setParentId($this->getId());
        $new_page_id = $page_object->createPageWithNextId();
        $settings = $this->getMainSettings()->getIntroductionSettings()
            ->withIntroductionPageId($new_page_id);
        $this->getMainSettingsRepository()->store(
            $this->getMainSettings()->withIntroductionSettings($settings)
        );
        return $new_page_id;
    }

    public function getConcludingRemarksPageId(): int
    {
        $page_id = $this->getMainSettings()->getFinishingSettings()->getConcludingRemarksPageId();
        if ($page_id !== null) {
            return $page_id;
        }

        $page_object = new ilTestPage();
        $page_object->setParentId($this->getId());
        $new_page_id = $page_object->createPageWithNextId();
        $settings = $this->getMainSettings()->getFinishingSettings()
            ->withConcludingRemarksPageId($new_page_id);
        $this->getMainSettingsRepository()->store(
            $this->getMainSettings()->withFinishingSettings($settings)
        );
        return $new_page_id;
    }

    public function getHighscoreEnabled(): bool
    {
        return $this->getScoreSettings()->getGamificationSettings()->getHighscoreEnabled();
    }

    /**
     * Gets if the highscores should be anonymized per setting.
     *
     * Note: This method will retrieve the setting as set by the user. If you want
     * to figure out, if the highscore is to be shown anonymized or not, with
     * consideration of the global anon switch you should @see isHighscoreAnon().
     *
     * @return bool True, if setting is to anonymize highscores.
     */
    public function getHighscoreAnon(): bool
    {
        return $this->getScoreSettings()->getGamificationSettings()->getHighscoreAnon();
    }

    /**
     * Gets if the highscores should be displayed anonymized.
     *
     * Note: This method considers the global anonymity switch. If you need
     * access to the users setting, @see getHighscoreAnon()
     *
     * @return boolean True, if output is anonymized.
     */
    public function isHighscoreAnon(): bool
    {
        return $this->getAnonymity() == 1 || $this->getHighscoreAnon();
    }

    /**
     * Returns if date and time of the scores achievement should be displayed.
     */
    public function getHighscoreAchievedTS(): bool
    {
        return $this->getScoreSettings()->getGamificationSettings()->getHighscoreAchievedTS();
    }

    /**
     * Gets if the score column should be shown.
     */
    public function getHighscoreScore(): bool
    {
        return $this->getScoreSettings()->getGamificationSettings()->getHighscoreScore();
    }

    /**
     * Gets if the percentage column should be shown.
     */
    public function getHighscorePercentage(): bool
    {
        return $this->getScoreSettings()->getGamificationSettings()->getHighscorePercentage();
    }

    /**
     * Gets, if the column with the number of requested hints should be shown.
     */
    public function getHighscoreHints(): bool
    {
        return $this->getScoreSettings()->getGamificationSettings()->getHighscoreHints();
    }

    /**
     * Gets if the column with the workingtime should be shown.
     */
    public function getHighscoreWTime(): bool
    {
        return $this->getScoreSettings()->getGamificationSettings()->getHighscoreWTime();
    }

    /**
     * Gets if the own rankings table should be shown.
     */
    public function getHighscoreOwnTable(): bool
    {
        return $this->getScoreSettings()->getGamificationSettings()->getHighscoreOwnTable();
    }

    /**
     * Gets, if the top-rankings table should be shown.
     */
    public function getHighscoreTopTable(): bool
    {
        return $this->getScoreSettings()->getGamificationSettings()->getHighscoreTopTable();
    }

    /**
     * Gets the number of entries which are to be shown in the top-rankings table.
     * @return integer Number of entries to be shown in the top-rankings table.
     */
    public function getHighscoreTopNum(int $a_retval = 10): int
    {
        return $this->getScoreSettings()->getGamificationSettings()->getHighscoreTopNum();
    }

    public function getHighscoreMode(): int
    {
        return $this->getScoreSettings()->getGamificationSettings()->getHighScoreMode();
    }

    public function getSpecificAnswerFeedback(): bool
    {
        return $this->getMainSettings()->getQuestionBehaviourSettings()->getInstantFeedbackSpecificEnabled();
    }

    public function areObligationsEnabled(): bool
    {
        return $this->getMainSettings()->getQuestionBehaviourSettings()->getCompulsoryQuestionsEnabled();
    }

    public static function isQuestionObligationPossible(int $question_id): bool
    {
        global $DIC;
        $question_info = $DIC->testQuestionPool()->questionInfo();
        $class = $question_info->getQuestionType($question_id);
        return call_user_func([$class, 'isObligationPossible'], $question_id);
    }

    /**
     * checks wether the question with given id is marked as obligatory or not
     *
     * @param integer $questionId
     * @return boolean $obligatory
     */
    public static function isQuestionObligatory($question_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $rset = $ilDB->queryF('SELECT obligatory FROM tst_test_question WHERE question_fi = %s', ['integer'], [$question_id]);

        if ($row = $ilDB->fetchAssoc($rset)) {
            return (bool) $row['obligatory'];
        }

        return false;
    }

    /**
     * checks wether all questions marked as obligatory were answered
     * within the test pass with given testId, activeId and pass index
     *
     * @static
     * @access public
     * @global ilDBInterface $ilDB
     * @param integer $test_id
     * @param integer $active_id
     * @param integer $pass
     * @return boolean $allObligationsAnswered
     */
    public function allObligationsAnswered(): bool
    {
        if (!$this->hasObligations()) {
            return true;
        }

        if ($this->current_user_all_obliations_answered === null) {
            $active_id = $this->getActiveIdOfUser();
            $rset = $this->db->queryF(
                'SELECT obligations_answered FROM tst_pass_result WHERE active_fi = %s AND pass = %s',
                ['integer', 'integer'],
                [$active_id, self::_getPass($active_id)]
            );

            if ($row = $this->db->fetchAssoc($rset)) {
                $this->current_user_all_obliations_answered = (bool) ($row['obligations_answered'] ?? 0);
            }
        }

        return $this->current_user_all_obliations_answered;
    }

    public function hasObligations(): bool
    {
        if ($this->has_obligations === null) {
            $rset = $this->db->queryF(
                'SELECT count(*) cnt FROM tst_test_question WHERE test_fi = %s AND obligatory = 1',
                ['integer'],
                [$this->getTestId()]
            );
            $row = $this->db->fetchAssoc($rset);
            $this->has_obligations = $row['cnt'] > 0;
        }

        return $this->has_obligations;
    }

    public function getAutosave(): bool
    {
        return $this->getMainSettings()->getQuestionBehaviourSettings()->getAutosaveEnabled();
    }

    public function isPassDeletionAllowed(): bool
    {
        return $this->getScoreSettings()->getResultSummarySettings()->getPassDeletionAllowed();
    }

    public function getEnableExamview(): bool
    {
        return $this->getMainSettings()->getFinishingSettings()->getShowAnswerOverview();
    }

    public function setActivationStartingTime(?int $starting_time = null)
    {
        $this->activation_starting_time = $starting_time;
    }

    public function setActivationEndingTime(?int $ending_time = null)
    {
        $this->activation_ending_time = $ending_time;
    }

    public function getActivationStartingTime(): ?int
    {
        return $this->activation_starting_time;
    }

    public function getActivationEndingTime(): ?int
    {
        return $this->activation_ending_time;
    }

    /**
     * Note, this function should only be used if absolutely necessary, since it perform joins on tables that
     * tend to grow huge and returns vast amount of data. If possible, use getStartingTimeOfUser($active_id) instead
     *
     * @return array
     */
    public function getStartingTimeOfParticipants(): array
    {
        $times = [];
        $result = $this->db->queryF(
            "SELECT tst_times.active_fi, tst_times.started FROM tst_times, tst_active WHERE tst_times.active_fi = tst_active.active_id AND tst_active.test_fi = %s ORDER BY tst_times.tstamp DESC",
            ['integer'],
            [$this->getTestId()]
        );
        while ($row = $this->db->fetchAssoc($result)) {
            $times[$row['active_fi']] = $row['started'];
        }
        return $times;
    }

    public function getTimeExtensionsOfParticipants(): array
    {
        $times = [];
        $result = $this->db->queryF(
            "SELECT tst_addtime.active_fi, tst_addtime.additionaltime FROM tst_addtime, tst_active WHERE tst_addtime.active_fi = tst_active.active_id AND tst_active.test_fi = %s",
            ['integer'],
            [$this->getTestId()]
        );
        while ($row = $this->db->fetchAssoc($result)) {
            $times[$row['active_fi']] = $row['additionaltime'];
        }
        return $times;
    }

    public function getExtraTime($active_id)
    {
        $result = $this->db->queryF(
            "SELECT additionaltime FROM tst_addtime WHERE active_fi = %s",
            ['integer'],
            [$active_id]
        );
        if ($result->numRows() > 0) {
            $row = $this->db->fetchAssoc($result);
            return $row['additionaltime'];
        }
        return 0;
    }

    public function addExtraTime($active_id, $minutes)
    {
        $participantData = new ilTestParticipantData($this->db, $this->lng);
        $participantData->setParticipantAccessFilter(
            $this->participant_access_filter->getManageParticipantsUserFilter($this->getRefId())
        );

        if ($active_id) {
            $participantData->setActiveIdsFilter([$active_id]);
        }

        $participantData->load($this->getTestId());

        foreach ($participantData->getActiveIds() as $active_fi) {
            $result = $this->db->queryF(
                "SELECT active_fi FROM tst_addtime WHERE active_fi = %s",
                ['integer'],
                [$active_fi]
            );

            if ($result->numRows() > 0) {
                $this->db->manipulateF(
                    "DELETE FROM tst_addtime WHERE active_fi = %s",
                    ['integer'],
                    [$active_fi]
                );
            }

            $this->db->manipulateF(
                "UPDATE tst_active SET tries = %s, submitted = %s, submittimestamp = %s WHERE active_id = %s",
                ['integer','integer','timestamp','integer'],
                [0, 0, null, $active_fi]
            );

            $this->db->manipulateF(
                "INSERT INTO tst_addtime (active_fi, additionaltime, tstamp) VALUES (%s, %s, %s)",
                ['integer','integer','integer'],
                [$active_fi, $minutes, time()]
            );

            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                $this->logAction(sprintf($this->lng->txtlng("assessment", "log_added_extratime", ilObjAssessmentFolder::_getLogLanguage()), $minutes, $active_id));
            }
        }
    }

    public function getMaxPassOfTest(): int
    {
        $query = '
			SELECT MAX(tst_pass_result.pass) + 1 max_res
			FROM tst_pass_result
			INNER JOIN tst_active ON tst_active.active_id = tst_pass_result.active_fi
			WHERE test_fi = ' . $this->db->quote($this->getTestId(), 'integer') . '
		';
        $res = $this->db->query($query);
        $data = $this->db->fetchAssoc($res);
        return (int) $data['max_res'];
    }

    public static function lookupExamId($active_id, $pass)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $exam_id_query = 'SELECT exam_id FROM tst_pass_result WHERE active_fi = %s AND pass = %s';
        $exam_id_result = $ilDB->queryF($exam_id_query, [ 'integer', 'integer' ], [ $active_id, $pass ]);
        if ($ilDB->numRows($exam_id_result) == 1) {
            $exam_id_row = $ilDB->fetchAssoc($exam_id_result);

            if ($exam_id_row['exam_id'] != null) {
                return $exam_id_row['exam_id'];
            }
        }

        return null;
    }

    public static function buildExamId($active_id, $pass, $test_obj_id = null): string
    {
        global $DIC;
        $ilSetting = $DIC['ilSetting'];

        $inst_id = $ilSetting->get('inst_id', null);

        if ($test_obj_id === null) {
            $obj_id = self::_getObjectIDFromActiveID($active_id);
        } else {
            $obj_id = $test_obj_id;
        }

        $examId = 'I' . $inst_id . '_T' . $obj_id . '_A' . $active_id . '_P' . $pass;

        return $examId;
    }

    public function isShowExamIdInTestPassEnabled(): bool
    {
        return $this->getMainSettings()->getTestBehaviourSettings()->getExamIdInTestPassEnabled();
    }

    public function isShowExamIdInTestResultsEnabled(): bool
    {
        return $this->getScoreSettings()->getResultDetailsSettings()->getShowExamIdInTestResults();
    }


    public function setQuestionSetType(string $question_set_type)
    {
        $this->main_settings = $this->getMainSettings()->withGeneralSettings(
            $this->getMainSettings()->getGeneralSettings()
                ->withQuestionSetType($question_set_type)
        );
    }

    public function getQuestionSetType(): string
    {
        return $this->getMainSettings()->getGeneralSettings()->getQuestionSetType();
    }

    /**
     * lookup-er for question set type
     *
     * @global ilDBInterface $ilDB
     * @param integer $objId
     * @return string $questionSetType
     */
    public static function lookupQuestionSetType($objId): ?string
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT question_set_type FROM tst_tests WHERE obj_fi = %s";

        $res = $ilDB->queryF($query, ['integer'], [$objId]);

        $questionSetType = null;

        while ($row = $ilDB->fetchAssoc($res)) {
            $questionSetType = $row['question_set_type'];
        }

        return $questionSetType;
    }

    /**
     * Returns the fact wether this test is a fixed question set test or not
     *
     * @return boolean $isFixedTest
     */
    public function isFixedTest(): bool
    {
        return $this->getQuestionSetType() == self::QUESTION_SET_TYPE_FIXED;
    }

    /**
     * Returns the fact wether this test is a random questions test or not
     *
     * @return boolean $isRandomTest
     */
    public function isRandomTest(): bool
    {
        return $this->getQuestionSetType() == self::QUESTION_SET_TYPE_RANDOM;
    }

    /**
     * Returns the fact wether the test with passed obj id is a random questions test or not
     *
     * @param integer $a_obj_id
     * @return boolean $isRandomTest
     * @deprecated
     */
    public static function _lookupRandomTest($a_obj_id): bool
    {
        return self::lookupQuestionSetType($a_obj_id) == self::QUESTION_SET_TYPE_RANDOM;
    }

    public function getQuestionSetTypeTranslation(ilLanguage $lng, $questionSetType): string
    {
        switch ($questionSetType) {
            case ilObjTest::QUESTION_SET_TYPE_FIXED:
                return $lng->txt('tst_question_set_type_fixed');

            case ilObjTest::QUESTION_SET_TYPE_RANDOM:
                return $lng->txt('tst_question_set_type_random');
        }

        throw new ilTestException('invalid question set type value given: ' . $questionSetType);
    }

    public function participantDataExist(): bool
    {
        if ($this->participantDataExist === null) {
            $this->participantDataExist = (bool) $this->evalTotalPersons();
        }

        return $this->participantDataExist;
    }

    public function recalculateScores($preserve_manscoring = false)
    {
        $scoring = new ilTestScoring($this, $this->db);
        $scoring->setPreserveManualScores($preserve_manscoring);
        $scoring->recalculateSolutions();
        ilLPStatusWrapper::_updateStatus($this->getId(), $this->user->getId());
    }

    public static function getTestObjIdsWithActiveForUserId($userId): array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "
			SELECT obj_fi
			FROM tst_active
			INNER JOIN tst_tests
			ON test_id = test_fi
			WHERE user_fi = %s
		";

        $res = $ilDB->queryF($query, ['integer'], [$userId]);

        $objIds = [];

        while ($row = $ilDB->fetchAssoc($res)) {
            $objIds[] = (int) $row['obj_fi'];
        }

        return $objIds;
    }

    public function isSkillServiceEnabled(): bool
    {
        return $this->getMainSettings()->getAdditionalSettings()->getSkillsServiceEnabled();
    }

    /**
     * Returns whether this test must consider skills, usually by providing
     * appropriate extensions in the user interface components. Skills must be
     * considered if skill management is globally enabled and this object has
     * the skill service enabled as well.
     *
     * @see #isSkillServiceEnabled()
     * @see #isSkillManagementGloballyActivated()
     *
     * @return boolean whether this test takes skills into account.
     */
    public function isSkillServiceToBeConsidered(): bool
    {
        if (!$this->getMainSettings()->getAdditionalSettings()->getSkillsServiceEnabled()) {
            return false;
        }

        if (!self::isSkillManagementGloballyActivated()) {
            return false;
        }

        return true;
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

    public function isShowGradingStatusEnabled(): bool
    {
        return $this->getScoreSettings()->getResultSummarySettings()->getShowGradingStatusEnabled();
    }

    public function isShowGradingMarkEnabled(): bool
    {
        return $this->getScoreSettings()->getResultSummarySettings()->getShowGradingMarkEnabled();
    }

    public function isFollowupQuestionAnswerFixationEnabled(): bool
    {
        return $this->getMainSettings()->getQuestionBehaviourSettings()->getLockAnswerOnNextQuestionEnabled();
    }

    public function isInstantFeedbackAnswerFixationEnabled(): bool
    {
        return $this->getMainSettings()->getQuestionBehaviourSettings()->getLockAnswerOnInstantFeedbackEnabled();
    }

    public function isForceInstantFeedbackEnabled(): ?bool
    {
        return $this->getMainSettings()->getQuestionBehaviourSettings()->getForceInstantFeedbackOnNextQuestion();
    }

    public static function isParticipantsLastPassActive(int $test_ref_id, int $user_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        $test_obj = ilObjectFactory::getInstanceByRefId($test_ref_id, false);

        $active_id = $test_obj->getActiveIdOfUser($user_id);

        $test_session_factory = new ilTestSessionFactory($test_obj, $ilDB, $ilUser);

        // Added temporarily bugfix smeyer
        $test_session_factory->reset();

        $test_sequence_factory = new ilTestSequenceFactory($test_obj, $ilDB, $DIC->testQuestionPool()->questionInfo());

        $test_session = $test_session_factory->getSession($active_id);
        $test_sequence = $test_sequence_factory->getSequenceByActiveIdAndPass($active_id, $test_session->getPass());
        $test_sequence->loadFromDb();

        return $test_sequence->hasSequence();
    }

    /**
     * @return boolean
     */
    public function isTestFinalBroken(): bool
    {
        return $this->testFinalBroken;
    }

    public function adjustTestSequence()
    {
        $query = "
			SELECT COUNT(test_question_id) cnt
			FROM tst_test_question
			WHERE test_fi = %s
			ORDER BY sequence
		";

        $questRes = $this->db->queryF($query, ['integer'], [$this->getTestId()]);

        $row = $this->db->fetchAssoc($questRes);
        $questCount = $row['cnt'];

        if ($this->getShuffleQuestions()) {
            $query = "
				SELECT tseq.*
				FROM tst_active tac
				INNER JOIN tst_sequence tseq
					ON tseq.active_fi = tac.active_id
				WHERE tac.test_fi = %s
			";

            $partRes = $this->db->queryF(
                $query,
                ['integer'],
                [$this->getTestId()]
            );

            while ($row = $this->db->fetchAssoc($partRes)) {
                $sequence = @unserialize($row['sequence']);

                if (!$sequence) {
                    $sequence = [];
                }

                $sequence = array_filter($sequence, function ($value) use ($questCount) {
                    return $value <= $questCount;
                });

                $num_seq = count($sequence);
                if ($questCount > $num_seq) {
                    $diff = $questCount - $num_seq;
                    for ($i = 1; $i <= $diff; $i++) {
                        $sequence[$num_seq + $i - 1] = $num_seq + $i;
                    }
                }

                $new_sequence = serialize($sequence);

                $this->db->update('tst_sequence', [
                    'sequence' => ['clob', $new_sequence]
                ], [
                    'active_fi' => ['integer', $row['active_fi']],
                    'pass' => ['integer', $row['pass']]
                ]);
            }
        } else {
            $new_sequence = serialize($questCount > 0 ? range(1, $questCount) : []);

            $query = "
				SELECT tseq.*
				FROM tst_active tac
				INNER JOIN tst_sequence tseq
					ON tseq.active_fi = tac.active_id
				WHERE tac.test_fi = %s
			";

            $part_rest = $this->db->queryF(
                $query,
                ['integer'],
                [$this->getTestId()]
            );

            while ($row = $this->db->fetchAssoc($part_rest)) {
                $this->db->update('tst_sequence', [
                    'sequence' => ['clob', $new_sequence]
                ], [
                    'active_fi' => ['integer', $row['active_fi']],
                    'pass' => ['integer', $row['pass']]
                ]);
            }
        }
    }

    /**
     * @return ilHtmlPurifierInterface|ilAssHtmlUserSolutionPurifier
     */
    protected function getHtmlQuestionContentPurifier(): ilHtmlPurifierInterface
    {
        return ilHtmlPurifierFactory::getInstanceByType('qpl_usersolution');
    }

    public function getScoreSettings(): ilObjTestScoreSettings
    {
        if (!$this->score_settings) {
            $this->score_settings = $this->getScoreSettingsRepository()
                ->getFor($this->getTestId());
        }
        return $this->score_settings;
    }

    public function getScoreSettingsRepository(): ScoreSettingsRepository
    {
        if (!$this->score_settings_repo) {
            $this->score_settings_repo = new ilObjTestScoreSettingsDatabaseRepository($this->db);
        }
        return $this->score_settings_repo;
    }

    public function getMainSettings(): ilObjTestMainSettings
    {
        if (!$this->main_settings) {
            $this->main_settings = $this->getMainSettingsRepository()
                ->getFor($this->getTestId());
        }
        return $this->main_settings;
    }

    public function getMainSettingsRepository(): MainSettingsRepository
    {
        if (!$this->main_settings_repo) {
            $this->main_settings_repo = new ilObjTestMainSettingsDatabaseRepository($this->db);
        }
        return $this->main_settings_repo;
    }

    public function updateTestResultCache(int $active_id, ilAssQuestionProcessLocker $process_locker = null): void
    {
        $pass = ilObjTest::_getResultPass($active_id);

        if ($pass !== null) {
            $query = '
                SELECT		tst_pass_result.*
                FROM		tst_pass_result
                WHERE		active_fi = %s
                AND			pass = %s
            ';

            $result = $this->db->queryF(
                $query,
                ['integer','integer'],
                [$active_id, $pass]
            );

            $test_pass_result_row = $this->db->fetchAssoc($result);

            if (!is_array($test_pass_result_row)) {
                $test_pass_result_row = [];
            }
            $max = (float) ($test_pass_result_row['maxpoints'] ?? 0);
            $reached = (float) ($test_pass_result_row['points'] ?? 0);
            $percentage = ($max <= 0.0 || $reached <= 0.0) ? 0 : ($reached / $max) * 100.0;

            $obligations_answered = (int) ($test_pass_result_row['obligations_answered'] ?? 1);

            $mark = $this->mark_schema->getMatchingMark($percentage);
            $is_passed = (bool) $mark->getPassed();

            $hint_count = $test_pass_result_row['hint_count'] ?? 0;
            $hint_points = $test_pass_result_row['hint_points'] ?? 0.0;

            $user_test_result_update_callback = function () use ($active_id, $pass, $max, $reached, $is_passed, $obligations_answered, $hint_count, $hint_points, $mark) {
                $passed_once_before = 0;
                $query = 'SELECT passed_once FROM tst_result_cache WHERE active_fi = %s';
                $res = $this->db->queryF($query, ['integer'], [$active_id]);
                while ($passed_once_result_row = $this->db->fetchAssoc($res)) {
                    $passed_once_before = (int) $passed_once_result_row['passed_once'];
                }

                $passed_once = (int) ($is_passed || $passed_once_before);

                $this->db->manipulateF(
                    'DELETE FROM tst_result_cache WHERE active_fi = %s',
                    ['integer'],
                    [$active_id]
                );

                $mark_short_name = $mark->getShortName();
                if ($mark_short_name === '') {
                    $mark_short_name = ' ';
                }

                $mark_official_name = $mark->getOfficialName();
                if ($mark_official_name === '') {
                    $mark_official_name = ' ';
                }

                $this->db->insert(
                    'tst_result_cache',
                    [
                        'active_fi' => ['integer', $active_id],
                        'pass' => ['integer', $pass ?? 0],
                        'max_points' => ['float', $max],
                        'reached_points' => ['float', $reached],
                        'mark_short' => ['text', $mark_short_name],
                        'mark_official' => ['text', $mark_official_name],
                        'passed_once' => ['integer', $passed_once],
                        'passed' => ['integer', (int) $is_passed],
                        'failed' => ['integer', (int) !$is_passed],
                        'tstamp' => ['integer', time()],
                        'hint_count' => ['integer', $hint_count],
                        'hint_points' => ['float', $hint_points],
                        'obligations_answered' => ['integer', $obligations_answered]
                    ]
                );
            };

            if (is_object($process_locker)) {
                $process_locker->executeUserTestResultUpdateLockOperation($user_test_result_update_callback);
            } else {
                $user_test_result_update_callback();
            }
        }
    }

    public function updateTestPassResults(
        int $active_id,
        int $pass,
        bool $obligations_enabled = false,
        ilAssQuestionProcessLocker $process_locker = null,
        int $test_obj_id = null
    ): array {
        $data = ilObjTest::_getQuestionCountAndPointsForPassOfParticipant($active_id, $pass);
        $time = ilObjTest::_getWorkingTimeOfParticipantForPass($active_id, $pass);

        $result = $this->db->queryF(
            '
			SELECT		SUM(points) reachedpoints,
						SUM(hint_count) hint_count,
						SUM(hint_points) hint_points,
						COUNT(DISTINCT(question_fi)) answeredquestions
			FROM		tst_test_result
			WHERE		active_fi = %s
			AND			pass = %s
			',
            ['integer','integer'],
            [$active_id, $pass]
        );

        if ($result->numRows() > 0) {
            if ($obligations_enabled) {
                $query = '
					SELECT		answered answ
					FROM		tst_test_question
					  INNER JOIN	tst_active
						ON			active_id = %s
						AND			tst_test_question.test_fi = tst_active.test_fi
					LEFT JOIN	tst_test_result
						ON			tst_test_result.active_fi = %s
						AND			tst_test_result.pass = %s
						AND			tst_test_question.question_fi = tst_test_result.question_fi
					WHERE		obligatory = 1';

                $result_obligatory = $this->db->queryF(
                    $query,
                    ['integer','integer','integer'],
                    [$active_id, $active_id, $pass]
                );

                $obligations_answered = 1;

                while ($row_obligatory = $this->db->fetchAssoc($result_obligatory)) {
                    if (!(int) $row_obligatory['answ']) {
                        $obligations_answered = 0;
                        break;
                    }
                }
            } else {
                $obligations_answered = 1;
            }

            $row = $this->db->fetchAssoc($result);

            if ($row['reachedpoints'] === null) {
                $row['reachedpoints'] = 0.0;
            }
            if ($row['hint_count'] === null) {
                $row['hint_count'] = 0;
            }
            if ($row['hint_points'] === null) {
                $row['hint_points'] = 0.0;
            }

            $exam_identifier = ilObjTest::buildExamId($active_id, $pass, $test_obj_id);

            $update_pass_result_callback = function () use ($data, $active_id, $pass, $row, $time, $obligations_answered, $exam_identifier) {
                $this->db->replace(
                    'tst_pass_result',
                    [
                        'active_fi' => ['integer', $active_id],
                        'pass' => ['integer', $pass]
                    ],
                    [
                        'points' => ['float', $row['reachedpoints'] ?: 0],
                        'maxpoints' => ['float', $data['points']],
                        'questioncount' => ['integer', $data['count']],
                        'answeredquestions' => ['integer', $row['answeredquestions']],
                        'workingtime' => ['integer', $time],
                        'tstamp' => ['integer', time()],
                        'hint_count' => ['integer', $row['hint_count']],
                        'hint_points' => ['float', $row['hint_points']],
                        'obligations_answered' => ['integer', $obligations_answered],
                        'exam_id' => ['text', $exam_identifier]
                    ]
                );
            };

            if (is_object($process_locker) && $process_locker instanceof ilAssQuestionProcessLocker) {
                $process_locker->executeUserPassResultUpdateLockOperation($update_pass_result_callback);
            } else {
                $update_pass_result_callback();
            }
        }

        $this->updateTestResultCache($active_id, $process_locker);

        return [
            'active_fi' => $active_id,
            'pass' => $pass,
            'points' => $row["reachedpoints"] ?? 0.0,
            'maxpoints' => $data["points"],
            'questioncount' => $data["count"],
            'answeredquestions' => $row["answeredquestions"],
            'workingtime' => $time,
            'tstamp' => time(),
            'hint_count' => $row['hint_count'],
            'hint_points' => $row['hint_points'],
            'obligations_answered' => $obligations_answered,
            'exam_id' => $exam_identifier
        ];
    }

    public function resetMarkSchema(): void
    {
        $this->mark_schema->flush();
    }

    public function addToNewsOnOnline(
        bool $old_online_status,
        bool $new_online_status
    ): void {
        if (!$old_online_status && $new_online_status) {
            $newsItem = new ilNewsItem();
            $newsItem->setContext($this->getId(), 'tst');
            $newsItem->setPriority(NEWS_NOTICE);
            $newsItem->setTitle('new_test_online');
            $newsItem->setContentIsLangVar(true);
            $newsItem->setContent('');
            $newsItem->setUserId($this->user->getId());
            $newsItem->setVisibility(NEWS_USERS);
            $newsItem->create();
            return;
        }

        if ($old_online_status && !$new_online_status) {
            ilNewsItem::deleteNewsOfContext($this->getId(), 'tst');
            return;
        }

        $newsId = ilNewsItem::getFirstNewsIdForContext($this->getId(), 'tst');
        if (!$new_online_status && $newsId > 0) {
            $newsItem = new ilNewsItem($newsId);
            $newsItem->setTitle('new_test_online');
            $newsItem->setContentIsLangVar(true);
            $newsItem->setContent('');
            $newsItem->update();
        }
    }
}
