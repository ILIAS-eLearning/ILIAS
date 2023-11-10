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

use ILIAS\Refinery\Transformation;
use ILIAS\TA\Questions\assQuestionSuggestedSolution;
use ILIAS\TA\Questions\assQuestionSuggestedSolutionsDatabaseRepository;
use ILIAS\DI\Container;
use ILIAS\Skill\Service\SkillUsageService;

require_once './components/ILIAS/Test/classes/inc.AssessmentConstants.php';

/**
 * Abstract basic class which is to be extended by the concrete assessment question type classes
 *
 * The assQuestion class defines and encapsulates basic/common methods and attributes as well
 * as it provides abstract methods that are to be implemented by concrete question type classes.
 *
 * @abstract
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @ingroup		ModulesTestQuestionPool
 */
abstract class assQuestion
{
    protected const HAS_SPECIFIC_FEEDBACK = true;

    protected const DEFAULT_THUMB_SIZE = 150;
    protected const MINIMUM_THUMB_SIZE = 20;
    protected \ILIAS\TestQuestionPool\QuestionInfoService $questioninfo;
    protected \ILIAS\Test\TestParticipantInfoService $testParticipantInfo;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    protected \ILIAS\TestQuestionPool\QuestionFilesService $questionFilesService;
    protected ILIAS\DI\LoggingServices $ilLog;

    protected int $id;
    protected string $title;
    protected string $comment;
    protected string $owner;
    protected string $author;
    protected int $thumb_size;

    /**
     * The question text
     */
    protected string $question;

    /**
     * The maximum available points for the question
     */
    protected float $points;

    /**
     * Indicates whether the answers will be shuffled or not
     */
    protected bool $shuffle;

    /**
     * The database id of a test in which the question is contained
     */
    protected int $test_id;

    /**
     * Object id of the container object
     */
    protected int $obj_id = 0;

    /**
     * The reference to the ILIAS class
     *
     * @var object
     */
    protected $ilias;

    protected ilGlobalPageTemplate $tpl;

    protected ilLanguage $lng;

    protected ilDBInterface $db;

    protected Container $dic;

    /**
     * Array of suggested solutions
     *
     * @var array
     */
    protected array $suggested_solutions;

    protected ?int $original_id = null;

    /**
     * Page object
     *
     * @var object
     */
    protected $page;

    private int $nr_of_tries;

    /**
     * (Web) Path to images
     */
    private string $export_image_path;

    protected ?string $external_id = null;

    public const ADDITIONAL_CONTENT_EDITING_MODE_RTE = 'default';
    public const ADDITIONAL_CONTENT_EDITING_MODE_IPE = 'pageobject';

    private string $additionalContentEditingMode = '';

    public \ilAssQuestionFeedback $feedbackOBJ;

    public bool $prevent_rte_usage = false;

    public bool $selfassessmenteditingmode = false;

    public int $defaultnroftries = 0;

    protected \ilAssQuestionProcessLocker $processLocker;

    public string $questionActionCmd = 'handleQuestionAction';

    /**
     * @var null|int
     */
    protected $step;

    protected $lastChange;

    protected Transformation $shuffler;

    private bool $obligationsToBeConsidered = false;

    protected ilTestQuestionConfig $testQuestionConfig;

    protected ilAssQuestionLifecycle $lifecycle;

    protected ilObjUser $current_user;

    protected SkillUsageService $skillUsageService;

    /**
     * assQuestion constructor
     */
    public function __construct(
        string $title = "",
        string $comment = "",
        string $author = "",
        int $owner = -1,
        string $question = ""
    ) {
        global $DIC;
        $this->dic = $DIC;
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC->logger();
        $this->questioninfo = $DIC->testQuestionPool()->questionInfo();
        $this->questionFilesService = $DIC->testQuestionPool()->questionFiles();
        $this->testParticipantInfo = $DIC->test()->testParticipantInfo();
        $this->current_user = $DIC['ilUser'];
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->db = $ilDB;
        $this->ilLog = $ilLog;
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->thumb_size = self::DEFAULT_THUMB_SIZE;

        $this->title = $title;
        $this->comment = $comment;
        $this->setAuthor($author);
        $this->setOwner($owner);

        $this->setQuestion($question);

        $this->id = -1;
        $this->test_id = -1;
        $this->suggested_solutions = [];
        $this->shuffle = 1;
        $this->nr_of_tries = 0;
        $this->setExternalId(null);

        $this->questionActionCmd = 'handleQuestionAction';
        $this->export_image_path = '';
        $this->shuffler = $DIC->refinery()->random()->dontShuffle();
        $this->lifecycle = ilAssQuestionLifecycle::getDraftInstance();
        $this->skillUsageService = $DIC->skills()->usage();
    }

    protected static $forcePassResultsUpdateEnabled = false;

    public static function setForcePassResultUpdateEnabled(bool $forcePassResultsUpdateEnabled): void
    {
        self::$forcePassResultsUpdateEnabled = $forcePassResultsUpdateEnabled;
    }

    public static function isForcePassResultUpdateEnabled(): bool
    {
        return self::$forcePassResultsUpdateEnabled;
    }

    protected function getQuestionAction(): string
    {
        if (!isset($_POST['cmd']) || !isset($_POST['cmd'][$this->questionActionCmd])) {
            return '';
        }

        if (!is_array($_POST['cmd'][$this->questionActionCmd]) || !count($_POST['cmd'][$this->questionActionCmd])) {
            return '';
        }

        return key($_POST['cmd'][$this->questionActionCmd]);
    }

    protected function isNonEmptyItemListPostSubmission(string $postSubmissionFieldname): bool
    {
        if (!isset($_POST[$postSubmissionFieldname])) {
            return false;
        }

        if (!is_array($_POST[$postSubmissionFieldname])) {
            return false;
        }

        if (!count($_POST[$postSubmissionFieldname])) {
            return false;
        }

        return true;
    }

    protected function log(int $active_id, string $langVar): void
    {
        if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            $message = $this->lng->txtlng('assessment', $langVar, ilObjAssessmentFolder::_getLogLanguage());
            assQuestion::logAction($message, $active_id, $this->getId());
        }
    }

    public function getShuffler(): Transformation
    {
        return $this->shuffler;
    }

    public function setShuffler(Transformation $shuffler): void
    {
        $this->shuffler = $shuffler;
    }

    public function setProcessLocker(ilAssQuestionProcessLocker $processLocker): void
    {
        $this->processLocker = $processLocker;
    }

    public function getProcessLocker(): ilAssQuestionProcessLocker
    {
        return $this->processLocker;
    }

    /**
    * Receives parameters from a QTI parser and creates a valid ILIAS question object
    *
    * @param object $item The QTI item object
    * @param integer $questionpool_id The id of the parent questionpool
    * @param integer $tst_id The id of the parent test if the question is part of a test
    * @param object $tst_object A reference to the parent test object
    * @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
    * @param array $import_mapping An array containing references to included ILIAS objects
    */
    public function fromXML($item, int $questionpool_id, ?int $tst_id, &$tst_object, int &$question_counter, array $import_mapping, array &$solutionhints = []): array
    {
        $classname = $this->getQuestionType() . "Import";
        $import = new $classname($this);
        $import_mapping = $import->fromXML($item, $questionpool_id, $tst_id, $tst_object, $question_counter, $import_mapping);

        foreach ($solutionhints as $hint) {
            $h = new ilAssQuestionHint();
            $h->setQuestionId($import->getQuestionId());
            $h->setIndex($hint['index'] ?? "");
            $h->setPoints($hint['points'] ?? "");
            $h->setText($hint['txt'] ?? "");
            $h->save();
        }
        return $import_mapping;
    }

    /**
    * Returns a QTI xml representation of the question
    *
    * @return string The QTI xml representation of the question
    */
    public function toXML(
        bool $a_include_header = true,
        bool $a_include_binary = true,
        bool $a_shuffle = false,
        bool $test_output = false,
        bool $force_image_references = false
    ): string {
        $classname = $this->getQuestionType() . "Export";
        $export = new $classname($this);
        return $export->toXML($a_include_header, $a_include_binary, $a_shuffle, $test_output, $force_image_references);
    }

    /**
    * Returns true, if a question is complete for use
    *
    * @return boolean True, if the question is complete for use, otherwise false
    */
    abstract public function isComplete(): bool;

    public function setTitle(string $title = ""): void
    {
        $this->title = $title;
    }

    public function setId(int $id = -1): void
    {
        $this->id = $id;
    }

    public function setTestId(int $id = -1): void
    {
        $this->test_id = $id;
    }

    public function setComment(string $comment = ""): void
    {
        $this->comment = $comment;
    }

    public function setShuffle(?bool $shuffle = true): void
    {
        $this->shuffle = $shuffle ?? false;
    }

    public function setAuthor(string $author = ""): void
    {
        if (!$author) {
            $author = $this->current_user->getFullname();
        }
        $this->author = $author;
    }

    public function setOwner(int $owner = -1): void
    {
        $this->owner = $owner;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTitleFilenameCompliant(): string
    {
        return ilFileUtils::getASCIIFilename($this->getTitle());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getShuffle(): bool
    {
        return $this->shuffle;
    }

    public function getTestId(): int
    {
        return $this->test_id;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getThumbSize(): int
    {
        return $this->thumb_size;
    }

    public function setThumbSize(int $a_size): void
    {
        if ($a_size >= self::MINIMUM_THUMB_SIZE) {
            $this->thumb_size = $a_size;
        } else {
            throw new ilException("Thumb size must be at least " . self::MINIMUM_THUMB_SIZE . "px");
        }
    }

    public function getMinimumThumbSize(): int
    {
        return self::MINIMUM_THUMB_SIZE;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getOwner(): int
    {
        return $this->owner;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id = 0): void
    {
        $this->obj_id = $obj_id;
    }

    public function getLifecycle(): ilAssQuestionLifecycle
    {
        return $this->lifecycle;
    }

    public function setLifecycle(ilAssQuestionLifecycle $lifecycle): void
    {
        $this->lifecycle = $lifecycle;
    }

    public function setExternalId(?string $external_id): void
    {
        $this->external_id = $external_id;
    }

    public function getExternalId(): string
    {
        if ($this->external_id === null || $this->external_id === '') {
            if ($this->getId() > 0) {
                return 'il_' . IL_INST_ID . '_qst_' . $this->getId();
            }
            return uniqid('', true);
        }
        return $this->external_id;
    }

    /**
     * @return string HTML
     * @throws ilWACException
     */
    public static function _getSuggestedSolutionOutput(int $question_id): string
    {
        $question = self::instantiateQuestion($question_id);
        if (!is_object($question)) {
            return "";
        }
        return $question->getSuggestedSolutionOutput();
    }

    /**
     * @return string HTML
     * @throws ilWACException
     */
    public function getSuggestedSolutionOutput(): string
    {
        $output = [];
        foreach ($this->suggested_solutions as $solution) {
            switch ($solution->getType()) {
                case assQuestionSuggestedSolution::TYPE_LM:
                case assQuestionSuggestedSolution::TYPE_LM_CHAPTER:
                case assQuestionSuggestedSolution::TYPE_LM_PAGE:
                case assQuestionSuggestedSolution::TYPE_GLOSARY_TERM:
                    $output[] = '<a href="'
                        . assQuestion::_getInternalLinkHref($solution->getInternalLink())
                        . '">'
                        . $this->lng->txt("solution_hint")
                        . '</a>';
                    break;

                case assQuestionSuggestedSolution::TYPE_FILE:
                    $possible_texts = array_values(
                        array_filter(
                            [
                                ilLegacyFormElementsUtil::prepareFormOutput($solution->getTitle()),
                                ilLegacyFormElementsUtil::prepareFormOutput($solution->getFilename()),
                                $this->lng->txt('tst_show_solution_suggested')
                            ]
                        )
                    );

                    ilWACSignedPath::setTokenMaxLifetimeInSeconds(60);
                    $output[] = '<a href="'
                        . ilWACSignedPath::signFile(
                            $this->getSuggestedSolutionPathWeb() . $solution->getFilename()
                        )
                        . '">'
                        . $possible_texts[0]
                        . '</a>';
                    break;
            }
        }
        return implode("<br />", $output);
    }

    public function getSuggestedSolutions(): array
    {
        return $this->suggested_solutions;
    }

    public static function _getReachedPoints(int $active_id, int $question_id, int $pass): float
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $points = 0.0;

        $result = $ilDB->queryF(
            "SELECT * FROM tst_test_result WHERE active_fi = %s AND question_fi = %s AND pass = %s",
            array('integer','integer','integer'),
            array($active_id, $question_id, $pass)
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            $points = (float) $row["points"];
        }
        return $points;
    }

    public function getReachedPoints(int $active_id, int $pass): float
    {
        return round(self::_getReachedPoints($active_id, $this->getId(), $pass), 2);
    }

    public function getMaximumPoints(): float
    {
        return $this->points;
    }

    /**
     *  returns the reached points ...
     * - calculated by concrete question type class
     * - adjusted by hint point deduction
     * - adjusted by scoring options
     * ... for given testactive and testpass
     */
    final public function getAdjustedReachedPoints(int $active_id, int $pass, bool $authorizedSolution = true): float
    {
        // determine reached points for submitted solution
        $reached_points = $this->calculateReachedPoints($active_id, $pass, $authorizedSolution);
        $hintTracking = new ilAssQuestionHintTracking($this->getId(), $active_id, $pass);
        $requestsStatisticData = $hintTracking->getRequestStatisticDataByQuestionAndTestpass();
        $reached_points = $reached_points - $requestsStatisticData->getRequestsPoints();

        // adjust reached points regarding to tests scoring options
        $reached_points = $this->adjustReachedPointsByScoringOptions($reached_points, $active_id, $pass);

        return $reached_points;
    }

    /**
     * Calculates the question results from a previously saved question solution
     */
    final public function calculateResultsFromSolution(int $active_id, int $pass, bool $obligationsEnabled = false): void
    {
        // determine reached points for submitted solution
        $reached_points = $this->calculateReachedPoints($active_id, $pass);
        $questionHintTracking = new ilAssQuestionHintTracking($this->getId(), $active_id, $pass);
        $requestsStatisticData = $questionHintTracking->getRequestStatisticDataByQuestionAndTestpass();
        $reached_points = $reached_points - $requestsStatisticData->getRequestsPoints();

        // adjust reached points regarding to tests scoring options
        $reached_points = $this->adjustReachedPointsByScoringOptions($reached_points, $active_id, $pass);

        if ($obligationsEnabled && ilObjTest::isQuestionObligatory($this->getId())) {
            $isAnswered = $this->isAnswered($active_id, $pass);
        } else {
            $isAnswered = true;
        }

        if (is_null($reached_points)) {
            $reached_points = 0.0;
        }

        // fau: testNav - check for existing authorized solution to know if a result record should be written
        $existingSolutions = $this->lookupForExistingSolutions($active_id, $pass);

        $this->getProcessLocker()->executeUserQuestionResultUpdateOperation(
            function () use ($active_id, $pass, $reached_points, $requestsStatisticData, $isAnswered, $existingSolutions) {
                $query = "
                    DELETE FROM		tst_test_result

                    WHERE			active_fi = %s
                    AND				question_fi = %s
                    AND				pass = %s
                ";

                $types = ['integer', 'integer', 'integer'];
                $values = [$active_id, $this->getId(), $pass];

                if ($this->getStep() !== null) {
                    $query .= "
                    AND				step = %s
                ";

                    $types[] = 'integer';
                    $values[] = $this->getStep();
                }
                $this->db->manipulateF($query, $types, $values);

                if ($existingSolutions['authorized']) {
                    $next_id = $this->db->nextId("tst_test_result");
                    $fieldData = array(
                        'test_result_id' => array('integer', $next_id),
                        'active_fi' => array('integer', $active_id),
                        'question_fi' => array('integer', $this->getId()),
                        'pass' => array('integer', $pass),
                        'points' => array('float', $reached_points),
                        'tstamp' => array('integer', time()),
                        'hint_count' => array('integer', $requestsStatisticData->getRequestsCount()),
                        'hint_points' => array('float', $requestsStatisticData->getRequestsPoints()),
                        'answered' => array('integer', $isAnswered)
                    );

                    if ($this->getStep() !== null) {
                        $fieldData['step'] = array('integer', $this->getStep());
                    }

                    $this->db->insert('tst_test_result', $fieldData);
                }
            }
        );

        if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            assQuestion::logAction(
                sprintf(
                    $this->lng->txtlng(
                        "assessment",
                        "log_user_answered_question",
                        ilObjAssessmentFolder::_getLogLanguage()
                    ),
                    $reached_points
                ),
                $active_id,
                $this->getId()
            );
        }

        // update test pass results
        $test = new ilObjTest(
            $this->getObjId(),
            false
        );
        $test->updateTestPassResults($active_id, $pass, $obligationsEnabled, $this->getProcessLocker());
        ilCourseObjectiveResult::_updateObjectiveResult($this->current_user->getId(), $active_id, $this->getId());
    }

    /**
     * persists the working state for current testactive and testpass
     * @return bool if saving happened
     */
    final public function persistWorkingState(int $active_id, $pass, bool $obligationsEnabled = false, bool $authorized = true): bool
    {
        if (!$this instanceof ilAssQuestionPartiallySaveable && !$this->validateSolutionSubmit()) {
            return false;
        }

        $saveStatus = false;

        $this->getProcessLocker()->executePersistWorkingStateLockOperation(function () use ($active_id, $pass, $authorized, $obligationsEnabled, &$saveStatus) {
            if ($pass === null) {
                $pass = ilObjTest::_getPass($active_id);
            }

            $saveStatus = $this->saveWorkingData($active_id, $pass, $authorized);

            if ($authorized) {
                // fau: testNav - remove an intermediate solution if the authorized solution is saved
                //		the intermediate solution would set the displayed question status as "editing ..."
                $this->removeIntermediateSolution($active_id, $pass);
                // fau.
                $this->calculateResultsFromSolution($active_id, $pass, $obligationsEnabled);
            }
        });

        return $saveStatus;
    }

    /**
     * persists the preview state for current user and question
     */
    final public function persistPreviewState(ilAssQuestionPreviewSession $previewSession): bool
    {
        $this->savePreviewData($previewSession);
        return $this->validateSolutionSubmit();
    }

    public function validateSolutionSubmit(): bool
    {
        return true;
    }

    /**
     * Saves the learners input of the question to the database.
     *
     * @param int $active_id Active id of the user
     * @param int $pass Test pass
     * @param bool $authorized
     * @return bool $status
     */
    abstract public function saveWorkingData(int $active_id, int $pass, bool $authorized = true): bool;

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession): void
    {
        $previewSession->setParticipantsSolution($this->getSolutionSubmit());
    }

    public static function logAction(string $logtext, int $active_id, int $question_id): void
    {
        global $DIC;
        $original_id = $DIC->testQuestionPool()->questionInfo()->getOriginalId($question_id);

        ilObjAssessmentFolder::_addLog(
            $DIC->user()->getId(),
            ilObjTest::_getObjectIDFromActiveID($active_id),
            $logtext,
            $question_id,
            $original_id
        );
    }

    public function getSuggestedSolutionPath(): string
    {
        return CLIENT_WEB_DIR . "/assessment/$this->obj_id/$this->id/solution/";
    }

    /**
    * Returns the image path for web accessable images of a question.
    * The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
    */
    public function getImagePath($question_id = null, $object_id = null): string
    {
        if ($question_id === null) {
            $question_id = $this->id;
        }

        if ($object_id === null) {
            $object_id = $this->obj_id;
        }

        return $this->questionFilesService->buildImagePath($question_id, $object_id);
    }

    public function getSuggestedSolutionPathWeb(): string
    {
        $webdir = ilFileUtils::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/$this->obj_id/$this->id/solution/";
        return str_replace(
            ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
            ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            $webdir
        );
    }

    /**
     * Returns the web image path for web accessable images of a question.
     * The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
     * TODO: in use? refactor and ask for a supported path in all cases, not for THE dynamic highlander path ^^
     */
    public function getImagePathWeb(): string
    {
        if (!$this->export_image_path) {
            $webdir = ilFileUtils::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/$this->obj_id/$this->id/images/";
            return str_replace(
                ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
                ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
                $webdir
            );
        }
        return $this->export_image_path;
    }

    // hey: prevPassSolutions - accept and prefer intermediate only from current pass
    public function getTestOutputSolutions(int $activeId, int $pass): array
    {
        if ($this->getTestPresentationConfig()->isSolutionInitiallyPrefilled()) {
            return $this->getSolutionValues($activeId, $pass, true);
        }
        return $this->getUserSolutionPreferingIntermediate($activeId, $pass);
    }
    // hey.

    public function getUserSolutionPreferingIntermediate(int $active_id, $pass = null): array
    {
        $solution = $this->getSolutionValues($active_id, $pass, false);

        if (!count($solution)) {
            $solution = $this->getSolutionValues($active_id, $pass, true);
        }

        return $solution;
    }

    /**
     * Loads solutions of a given user from the database an returns it
     * @param int|string $active_id
     * @return array Assoc result from tst_solutions.*
     */
    public function getSolutionValues($active_id, $pass = null, bool $authorized = true): array
    {
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass((int) $active_id);
        }

        if ($this->getStep() !== null) {
            $query = "
				SELECT *
				FROM tst_solutions
				WHERE active_fi = %s
				AND question_fi = %s
				AND pass = %s
				AND step = %s
				AND authorized = %s
				ORDER BY solution_id";

            $result = $this->db->queryF(
                $query,
                array('integer', 'integer', 'integer', 'integer', 'integer'),
                array((int) $active_id, $this->getId(), $pass, $this->getStep(), (int) $authorized)
            );
        } else {
            $query = "
				SELECT *
				FROM tst_solutions
				WHERE active_fi = %s
				AND question_fi = %s
		  		AND pass = %s
				AND authorized = %s
				ORDER BY solution_id
			";

            $result = $this->db->queryF(
                $query,
                array('integer', 'integer', 'integer', 'integer'),
                array((int) $active_id, $this->getId(), $pass, (int) $authorized)
            );
        }

        $values = [];

        while ($row = $this->db->fetchAssoc($result)) {
            $values[] = $row;
        }

        return $values;
    }

    /**
     * @return string|array Or Array? @see Deletion methods here
     */
    abstract public function getAdditionalTableName();

    /**
     * @return string|array Or Array? @see Deletion methods here
     */
    abstract public function getAnswerTableName();

    public function deleteAnswers(int $question_id): void
    {
        $answer_table_name = $this->getAnswerTableName();

        if (!is_array($answer_table_name)) {
            $answer_table_name = array($answer_table_name);
        }

        foreach ($answer_table_name as $table) {
            if (strlen($table)) {
                $this->db->manipulateF(
                    "DELETE FROM $table WHERE question_fi = %s",
                    array('integer'),
                    array($question_id)
                );
            }
        }
    }

    public function deleteAdditionalTableData(int $question_id): void
    {
        $additional_table_name = $this->getAdditionalTableName();

        if (!is_array($additional_table_name)) {
            $additional_table_name = array($additional_table_name);
        }

        foreach ($additional_table_name as $table) {
            if (strlen($table)) {
                $this->db->manipulateF(
                    "DELETE FROM $table WHERE question_fi = %s",
                    array('integer'),
                    array($question_id)
                );
            }
        }
    }

    protected function deletePageOfQuestion(int $question_id): void
    {
        if (ilAssQuestionPage::_exists('qpl', $question_id, "", true)) {
            $page = new ilAssQuestionPage($question_id);
            $page->delete();
        }
    }

    public function delete(int $question_id): void
    {
        if ($question_id < 1) {
            return;
        }

        $result = $this->db->queryF(
            "SELECT obj_fi FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($this->db->numRows($result) == 1) {
            $row = $this->db->fetchAssoc($result);
            $obj_id = $row["obj_fi"];
        } else {
            return; // nothing to do
        }
        try {
            $this->deletePageOfQuestion($question_id);
        } catch (Exception $e) {
            $this->ilLog->root()->error("EXCEPTION: Could not delete page of question $question_id: $e");
            return;
        }

        $affectedRows = $this->db->manipulateF(
            "DELETE FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($affectedRows == 0) {
            return;
        }

        try {
            $this->deleteAdditionalTableData($question_id);
            $this->deleteAnswers($question_id);
            $this->feedbackOBJ->deleteGenericFeedbacks($question_id, $this->isAdditionalContentEditingModePageObject());
            $this->feedbackOBJ->deleteSpecificAnswerFeedbacks($question_id, $this->isAdditionalContentEditingModePageObject());
        } catch (Exception $e) {
            $this->ilLog->root()->error("EXCEPTION: Could not delete additional table data of question $question_id: $e");
            return;
        }

        try {
            // delete the question in the tst_test_question table (list of test questions)
            $affectedRows = $this->db->manipulateF(
                "DELETE FROM tst_test_question WHERE question_fi = %s",
                array('integer'),
                array($question_id)
            );
        } catch (Exception $e) {
            $this->ilLog->root()->error("EXCEPTION: Could not delete delete question $question_id from a test: $e");
            return;
        }

        try {
            $this->getSuggestedSolutionsRepo()->deleteForQuestion($question_id);
        } catch (Exception $e) {
            $this->ilLog->root()->error("EXCEPTION: Could not delete suggested solutions of question $question_id: $e");
            return;
        }

        try {
            $directory = CLIENT_WEB_DIR . "/assessment/" . $obj_id . "/$question_id";
            if (preg_match("/\d+/", $obj_id) and preg_match("/\d+/", $question_id) and is_dir($directory)) {
                ilFileUtils::delDir($directory);
            }
        } catch (Exception $e) {
            $this->ilLog->root()->error("EXCEPTION: Could not delete question file directory $directory of question $question_id: $e");
            return;
        }

        try {
            $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $question_id);
            // remaining usages are not in text anymore -> delete them
            // and media objects (note: delete method of ilObjMediaObject
            // checks whether object is used in another context; if yes,
            // the object is not deleted!)
            foreach ($mobs as $mob) {
                ilObjMediaObject::_removeUsage($mob, "qpl:html", $question_id);
                if (ilObjMediaObject::_exists($mob)) {
                    $mob_obj = new ilObjMediaObject($mob);
                    $mob_obj->delete();
                }
            }
        } catch (Exception $e) {
            $this->ilLog->root()->error("EXCEPTION: Error deleting the media objects of question $question_id: $e");
            return;
        }
        ilAssQuestionHintTracking::deleteRequestsByQuestionIds(array($question_id));
        ilAssQuestionHintList::deleteHintsByQuestionIds(array($question_id));
        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($obj_id);
        $assignmentList->setQuestionIdFilter($question_id);
        $assignmentList->loadFromDb();
        foreach ($assignmentList->getAssignmentsByQuestionId($question_id) as $assignment) {
            /* @var ilAssQuestionSkillAssignment $assignment */
            $assignment->deleteFromDb();

            // remove skill usage
            if (!$assignment->isSkillUsed()) {
                $this->skillUsageService->removeUsage(
                    $assignment->getParentObjId(),
                    $assignment->getSkillBaseId(),
                    $assignment->getSkillTrefId()
                );
            }
        }

        $this->deleteTaxonomyAssignments();

        try {
            ilObjQuestionPool::_updateQuestionCount($this->getObjId());
        } catch (Exception $e) {
            $this->ilLog->root()->error("EXCEPTION: Error updating the question pool question count of question pool " . $this->getObjId() . " when deleting question $question_id: $e");
            return;
        }
    }

    private function deleteTaxonomyAssignments(): void
    {
        $taxIds = ilObjTaxonomy::getUsageOfObject($this->getObjId());

        foreach ($taxIds as $taxId) {
            $taxNodeAssignment = new ilTaxNodeAssignment('qpl', $this->getObjId(), 'quest', $taxId);
            $taxNodeAssignment->deleteAssignmentsOfItem($this->getId());
        }
    }

    public function getTotalAnswers(): int
    {
        // get all question references to the question id
        $result = $this->db->queryF(
            "SELECT question_id FROM qpl_questions WHERE original_id = %s OR question_id = %s",
            array('integer','integer'),
            array($this->id, $this->id)
        );
        if ($this->db->numRows($result) == 0) {
            return 0;
        }
        $found_id = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $found_id[] = $row["question_id"];
        }

        $result = $this->db->query("SELECT * FROM tst_test_result WHERE " . $this->db->in('question_fi', $found_id, false, 'integer'));

        return $this->db->numRows($result);
    }

    public static function isFileAvailable(string $file): bool
    {
        if (!file_exists($file)) {
            return false;
        }

        if (!is_file($file)) {
            return false;
        }

        if (!is_readable($file)) {
            return false;
        }

        return true;
    }

    public function copyXHTMLMediaObjectsOfQuestion(int $a_q_id): void
    {
        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $a_q_id);
        foreach ($mobs as $mob) {
            ilObjMediaObject::_saveUsage($mob, "qpl:html", $this->getId());
        }
    }

    public function syncXHTMLMediaObjectsOfQuestion(): void
    {
        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
        foreach ($mobs as $mob) {
            ilObjMediaObject::_saveUsage($mob, "qpl:html", $this->original_id);
        }
    }

    public function createPageObject(): void
    {
        $qpl_id = $this->getObjId();
        $this->page = new ilAssQuestionPage(0);
        $this->page->setId($this->getId());
        $this->page->setParentId($qpl_id);
        $this->page->setXMLContent("<PageObject><PageContent>" .
            "<Question QRef=\"il__qst_" . $this->getId() . "\"/>" .
            "</PageContent></PageObject>");
        $this->page->create(false);
    }

    public function copyPageOfQuestion(int $a_q_id): void
    {
        if ($a_q_id > 0) {
            $page = new ilAssQuestionPage($a_q_id);

            $xml = str_replace("il__qst_" . $a_q_id, "il__qst_" . $this->id, $page->getXMLContent());
            $this->page->setXMLContent($xml);
            $this->page->updateFromXML();
        }
    }

    public function getPageOfQuestion(): string
    {
        $page = new ilAssQuestionPage($this->id);
        return $page->getXMLContent();
    }

    public function setOriginalId(?int $original_id): void
    {
        $this->original_id = $original_id;
    }

    public function getOriginalId(): ?int
    {
        return $this->original_id;
    }

    protected static $imageSourceFixReplaceMap = array(
        'ok.svg' => 'ok.png',
        'not_ok.svg' => 'not_ok.png',
        'object/checkbox_checked.svg' => 'checkbox_checked.png',
        'object/checkbox_unchecked.svg' => 'checkbox_unchecked.png',
        'object/radiobutton_checked.svg' => 'radiobutton_checked.png',
        'object/radiobutton_unchecked.svg' => 'radiobutton_unchecked.png'
    );

    public function fixSvgToPng(string $imageFilenameContainingString): string
    {
        $needles = array_keys(self::$imageSourceFixReplaceMap);
        $replacements = array_values(self::$imageSourceFixReplaceMap);
        return str_replace($needles, $replacements, $imageFilenameContainingString);
    }

    public function fixUnavailableSkinImageSources(string $html): string
    {
        $matches = null;
        if (preg_match_all('/src="(.*?)"/m', $html, $matches)) {
            $sources = $matches[1];

            $needleReplacementMap = [];

            foreach ($sources as $src) {
                $file = ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH) . DIRECTORY_SEPARATOR . $src;

                if (file_exists($file)) {
                    continue;
                }

                $levels = explode(DIRECTORY_SEPARATOR, $src);
                if (count($levels) < 5 || $levels[0] !== 'Customizing' || $levels[2] !== 'skin') {
                    continue;
                }

                $component = '';

                if ($levels[4] === 'components/ILIAS' || $levels[4] === 'components/ILIAS') {
                    $component = $levels[4] . DIRECTORY_SEPARATOR . $levels[5];
                }

                $needleReplacementMap[$src] = ilUtil::getImagePath(basename($src), $component);
            }

            if (count($needleReplacementMap)) {
                $html = str_replace(array_keys($needleReplacementMap), array_values($needleReplacementMap), $html);
            }
        }

        return $html;
    }

    public function loadFromDb(int $question_id): void
    {
        $result = $this->db->queryF(
            'SELECT external_id FROM qpl_questions WHERE question_id = %s',
            ['integer'],
            [$question_id]
        );
        if ($this->db->numRows($result) === 1) {
            $data = $this->db->fetchAssoc($result);
            $this->external_id = $data['external_id'];
        }

        $suggested_solutions = $this->loadSuggestedSolutions();
        $this->suggested_solutions = array();
        if ($suggested_solutions) {
            foreach ($suggested_solutions as $solution) {
                $this->suggested_solutions[$solution->getSubquestionIndex()] = $solution;
            }
        }
    }

    /**
    * Creates a new question without an owner when a new question is created
    * This assures that an ID is given to the question if a file upload or something else occurs
    *
    * @return integer ID of the new question
    */
    public function createNewQuestion(bool $a_create_page = true): int
    {
        $ilUser = $this->current_user;

        $complete = "0";
        $obj_id = ($this->getObjId() <= 0) ? (ilObject::_lookupObjId((strlen($this->dic->testQuestionPool()->internal()->request()->getRefId())) ? $this->dic->testQuestionPool()->internal()->request()->getRefId() : $_POST["sel_qpl"])) : $this->getObjId();
        if ($obj_id > 0) {
            if ($a_create_page) {
                $tstamp = 0;
            } else {
                // question pool must not try to purge
                $tstamp = time();
            }

            $next_id = $this->db->nextId('qpl_questions');
            $this->db->insert("qpl_questions", array(
                "question_id" => array("integer", $next_id),
                "question_type_fi" => array("integer", $this->getQuestionTypeID()),
                "obj_fi" => array("integer", $obj_id),
                "title" => array("text", null),
                "description" => array("text", null),
                "author" => array("text", $this->getAuthor()),
                "owner" => array("integer", $ilUser->getId()),
                "question_text" => array("clob", null),
                "points" => array("float", "0.0"),
                "nr_of_tries" => array("integer", $this->getDefaultNrOfTries()), // #10771
                "complete" => array("text", $complete),
                "created" => array("integer", time()),
                "original_id" => array("integer", null),
                "tstamp" => array("integer", $tstamp),
                "external_id" => array("text", $this->getExternalId()),
                'add_cont_edit_mode' => array('text', $this->getAdditionalContentEditingMode())
            ));
            $this->setId($next_id);

            if ($a_create_page) {
                // create page object of question
                $this->createPageObject();
            }
        }

        return $this->getId();
    }

    public function saveQuestionDataToDb(int $original_id = -1): void
    {
        if ($this->getId() == -1) {
            $next_id = $this->db->nextId('qpl_questions');
            $this->db->insert("qpl_questions", array(
                "question_id" => array("integer", $next_id),
                "question_type_fi" => array("integer", $this->getQuestionTypeID()),
                "obj_fi" => array("integer", $this->getObjId()),
                "title" => array("text", $this->getTitle()),
                "description" => array("text", $this->getComment()),
                "author" => array("text", $this->getAuthor()),
                "owner" => array("integer", $this->getOwner()),
                "question_text" => array("clob", ilRTE::_replaceMediaObjectImageSrc($this->getQuestion(), 0)),
                "points" => array("float", $this->getMaximumPoints()),
                "nr_of_tries" => array("integer", $this->getNrOfTries()),
                "created" => array("integer", time()),
                "original_id" => array("integer", ($original_id != -1) ? $original_id : null),
                "tstamp" => array("integer", time()),
                "external_id" => array("text", $this->getExternalId()),
                'add_cont_edit_mode' => array('text', $this->getAdditionalContentEditingMode())
            ));
            $this->setId($next_id);
            // create page object of question
            $this->createPageObject();
        } else {
            // Vorhandenen Datensatz aktualisieren
            $this->db->update("qpl_questions", array(
                "obj_fi" => array("integer", $this->getObjId()),
                "title" => array("text", $this->getTitle()),
                "description" => array("text", $this->getComment()),
                "author" => array("text", $this->getAuthor()),
                "question_text" => array("clob", ilRTE::_replaceMediaObjectImageSrc($this->getQuestion(), 0)),
                "points" => array("float", $this->getMaximumPoints()),
                "nr_of_tries" => array("integer", $this->getNrOfTries()),
                "tstamp" => array("integer", time()),
                'complete' => array('integer', $this->isComplete()),
                "external_id" => array("text", $this->getExternalId())
            ), array(
            "question_id" => array("integer", $this->getId())
            ));
        }
    }

    public function saveToDb(): void
    {
        // remove unused media objects from ILIAS
        $this->cleanupMediaObjectUsage();

        $complete = "0";
        if ($this->isComplete()) {
            $complete = "1";
        }

        $this->db->update(
            'qpl_questions',
            [
                'tstamp' => ['integer', time()],
                'owner' => ['integer', $this->getOwner()],
                'complete' => ['integer', $complete],
                'lifecycle' => ['text', $this->getLifecycle()->getIdentifier()],
            ],
            [
                'question_id' => array('integer', $this->getId())
            ]
        );

        ilObjQuestionPool::_updateQuestionCount($this->getObjId());
    }

    public static function saveOriginalId(int $questionId, int $originalId): void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $query = "UPDATE qpl_questions SET tstamp = %s, original_id = %s WHERE question_id = %s";

        $ilDB->manipulateF(
            $query,
            ['integer','integer', 'text'],
            [time(), $originalId, $questionId]
        );
    }

    public static function resetOriginalId(int $questionId): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = "UPDATE qpl_questions SET tstamp = %s, original_id = NULL WHERE question_id = %s";

        $ilDB->manipulateF(
            $query,
            ['integer', 'text'],
            [time(), $questionId]
        );
    }

    protected function onDuplicate(int $originalParentId, int $originalQuestionId, int $duplicateParentId, int $duplicateQuestionId): void
    {
        $this->duplicateSuggestedSolutionFiles($originalParentId, $originalQuestionId);
        $this->feedbackOBJ->duplicateFeedback($originalQuestionId, $duplicateQuestionId);
        $this->duplicateQuestionHints($originalQuestionId, $duplicateQuestionId);
        $this->duplicateSkillAssignments($originalParentId, $originalQuestionId, $duplicateParentId, $duplicateQuestionId);
    }

    protected function beforeSyncWithOriginal(int $origQuestionId, int $dupQuestionId, int $origParentObjId, int $dupParentObjId): void
    {
    }

    protected function afterSyncWithOriginal(int $origQuestionId, int $dupQuestionId, int $origParentObjId, int $dupParentObjId): void
    {
        $this->feedbackOBJ->syncFeedback($origQuestionId, $dupQuestionId);
    }

    protected function onCopy(int $sourceParentId, int $sourceQuestionId, int $targetParentId, int $targetQuestionId): void
    {
        $this->copySuggestedSolutionFiles($sourceParentId, $sourceQuestionId);

        // duplicate question feeback
        $this->feedbackOBJ->duplicateFeedback($sourceQuestionId, $targetQuestionId);

        // duplicate question hints
        $this->duplicateQuestionHints($sourceQuestionId, $targetQuestionId);

        // duplicate skill assignments
        $this->duplicateSkillAssignments($sourceParentId, $sourceQuestionId, $targetParentId, $targetQuestionId);
    }

    public function deleteSuggestedSolutions(): void
    {
        $this->getSuggestedSolutionsRepo()->deleteForQuestion($this->getId());
        ilInternalLink::_deleteAllLinksOfSource("qst", $this->getId());
        ilFileUtils::delDir($this->getSuggestedSolutionPath());
        $this->suggested_solutions = [];
    }


    public function getSuggestedSolution(int $subquestion_index = 0): ?assQuestionSuggestedSolution
    {
        if (array_key_exists($subquestion_index, $this->suggested_solutions)) {
            return $this->suggested_solutions[$subquestion_index];
        }
        return null;
    }

    protected function syncSuggestedSolutions(int $source_question_id, int $target_question_id): void
    {
        $this->getSuggestedSolutionsRepo()->syncForQuestion($source_question_id, $target_question_id);
        $this->syncSuggestedSolutionFiles($source_question_id);
    }

    /**
    * Duplicates the files of a suggested solution if the question is duplicated
    */
    protected function duplicateSuggestedSolutionFiles(int $parent_id, int $question_id): void
    {
        foreach ($this->suggested_solutions as $index => $solution) {
            if (!is_array($solution) ||
                !array_key_exists("type", $solution) ||
                strcmp($solution["type"], "file") !== 0) {
                continue;
            }

            $filepath = $this->getSuggestedSolutionPath();
            $filepath_original = str_replace(
                "/{$this->obj_id}/{$this->id}/solution",
                "/$parent_id/$question_id/solution",
                $filepath
            );
            if (!file_exists($filepath)) {
                ilFileUtils::makeDirParents($filepath);
            }
            $filename = $solution->getFilename();
            if (strlen($filename) &&
                !copy($filepath_original . $filename, $filepath . $filename)) {
                $this->ilLog->root()->error("File could not be duplicated!!!!");
                $this->ilLog->root()->error("object: " . print_r($this, true));
            }
        }
    }

    protected function syncSuggestedSolutionFiles(int $original_id): void
    {
        $filepath = $this->getSuggestedSolutionPath();
        $filepath_original = str_replace("/$this->id/solution", "/$original_id/solution", $filepath);
        ilFileUtils::delDir($filepath_original);
        foreach ($this->suggested_solutions as $index => $solution) {
            if ($solution->isOfTypeFile()) {
                if (!file_exists($filepath_original)) {
                    ilFileUtils::makeDirParents($filepath_original);
                }
                $filename = $solution->getFilename();
                if (strlen($filename)) {
                    if (!@copy($filepath . $filename, $filepath_original . $filename)) {
                        $this->ilLog->root()->error("File could not be duplicated!!!!");
                        $this->ilLog->root()->error("object: " . print_r($this, true));
                    }
                }
            }
        }
    }

    protected function copySuggestedSolutionFiles(int $source_questionpool_id, int $source_question_id): void
    {
        foreach ($this->suggested_solutions as $index => $solution) {
            if ($solution->isOfTypeFile()) {
                $filepath = $this->getSuggestedSolutionPath();
                $filepath_original = str_replace("/$this->obj_id/$this->id/solution", "/$source_questionpool_id/$source_question_id/solution", $filepath);
                if (!file_exists($filepath)) {
                    ilFileUtils::makeDirParents($filepath);
                }
                $filename = $solution["value"]["name"];
                if (strlen($filename)) {
                    if (!copy($filepath_original . $filename, $filepath . $filename)) {
                        $this->ilLog->root()->error("File could not be copied!!!!");
                        $this->ilLog->root()->error("object: " . print_r($this, true));
                    }
                }
            }
        }
    }

    public function resolveInternalLink(string $internal_link): string
    {
        if (preg_match("/il_(\d+)_(\w+)_(\d+)/", $internal_link, $matches)) {
            switch ($matches[2]) {
                case "lm":
                    $resolved_link = ilLMObject::_getIdForImportId($internal_link);
                    break;
                case "pg":
                    $resolved_link = ilInternalLink::_getIdForImportId("PageObject", $internal_link);
                    break;
                case "st":
                    $resolved_link = ilInternalLink::_getIdForImportId("StructureObject", $internal_link);
                    break;
                case "git":
                    $resolved_link = ilInternalLink::_getIdForImportId("GlossaryItem", $internal_link);
                    break;
                case "mob":
                    $resolved_link = ilInternalLink::_getIdForImportId("MediaObject", $internal_link);
                    break;
            }
            if ($resolved_link !== null) {
                $resolved_link = $internal_link;
            }
        } else {
            $resolved_link = $internal_link;
        }
        return $resolved_link ?? '';
    }


    //TODO: move this to import or suggested solutions repo.
    //use in LearningModule and Survey as well ;(
    public function _resolveIntLinks(int $question_id): void
    {
        $resolvedlinks = 0;
        $result = $this->db->queryF(
            "SELECT * FROM qpl_sol_sug WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        if ($this->db->numRows($result) > 0) {
            while ($row = $this->db->fetchAssoc($result)) {
                $internal_link = $row["internal_link"];
                $resolved_link = $this->resolveInternalLink($internal_link);
                if (strcmp($internal_link, $resolved_link) != 0) {
                    // internal link was resolved successfully
                    $affectedRows = $this->db->manipulateF(
                        "UPDATE qpl_sol_sug SET internal_link = %s WHERE suggested_solution_id = %s",
                        array('text','integer'),
                        array($resolved_link, $row["suggested_solution_id"])
                    );
                    $resolvedlinks++;
                }
            }
        }
        if ($resolvedlinks) {
            // there are resolved links -> reenter theses links to the database
            // delete all internal links from the database
            ilInternalLink::_deleteAllLinksOfSource("qst", $question_id);

            $result = $this->db->queryF(
                "SELECT * FROM qpl_sol_sug WHERE question_fi = %s",
                array('integer'),
                array($question_id)
            );
            if ($this->db->numRows($result) > 0) {
                while ($row = $this->db->fetchAssoc($result)) {
                    if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $row["internal_link"], $matches)) {
                        ilInternalLink::_saveLink("qst", $question_id, $matches[2], $matches[3], $matches[1]);
                    }
                }
            }
        }
    }

    public static function _getInternalLinkHref(string $target = ""): string
    {
        global $DIC;
        $linktypes = array(
            "lm" => "LearningModule",
            "pg" => "PageObject",
            "st" => "StructureObject",
            "git" => "GlossaryItem",
            "mob" => "MediaObject"
        );
        $href = "";
        if (preg_match("/il__(\w+)_(\d+)/", $target, $matches)) {
            $type = $matches[1];
            $target_id = $matches[2];
            switch ($linktypes[$matches[1]]) {
                case "MediaObject":
                    $href = "./ilias.php?baseClass=ilLMPresentationGUI&obj_type=" . $linktypes[$type]
                        . "&cmd=media&ref_id=" . $DIC->testQuestionPool()->internal()->request()->getRefId()
                        . "&mob_id=" . $target_id;
                    break;
                case "StructureObject":
                case "GlossaryItem":
                case "PageObject":
                case "LearningModule":
                default:
                    $href = "./goto.php?target=" . $type . "_" . $target_id;
                    break;
            }
        }
        return $href;
    }

    public function syncWithOriginal(): void
    {
        if (!$this->getOriginalId()) {
            return; // No original -> no sync
        }

        $originalObjId = self::lookupParentObjId($this->getOriginalId());

        if (!$originalObjId) {
            return; // Original does not exist -> no sync
        }

        $this->beforeSyncWithOriginal($this->getOriginalId(), $this->getId(), $originalObjId, $this->getObjId());
        $this->syncSuggestedSolutions($this->getId(), $this->getOriginalId());
        $this->syncXHTMLMediaObjectsOfQuestion();
        $this->afterSyncWithOriginal($this->getId(), $this->getOriginalId(), $this->getObjId(), $originalObjId);
        $this->syncHints();
    }

    /**
     * @param int $question_id
     * @return assQuestion
     * @throws InvalidArgumentException
     */
    public static function instantiateQuestion(int $question_id): assQuestion
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $questioninfo = $DIC->testQuestionPool()->questionInfo();
        $question_type = $questioninfo->getQuestionType($question_id);
        if ($question_type === '') {
            throw new InvalidArgumentException('No question with ID ' . $question_id . ' exists');
        }

        $question = new $question_type();
        $question->loadFromDb($question_id);

        $feedbackObjectClassname = self::getFeedbackClassNameByQuestionType($question_type);
        $question->feedbackOBJ = new $feedbackObjectClassname($question, $ilCtrl, $ilDB, $lng);

        return $question;
    }

    public function getPoints(): float
    {
        if (strcmp($this->points, "") == 0) {
            return 0.0;
        }

        return $this->points;
    }

    public function setPoints(float $points): void
    {
        $this->points = $points;
    }

    public function getSolutionMaxPass(int $active_id): int
    {
        return self::_getSolutionMaxPass($this->getId(), $active_id);
    }

    /**
    * Returns the maximum pass a users question solution
    */
    public static function _getSolutionMaxPass(int $question_id, int $active_id): int
    {
        // the following code was the old solution which added the non answered
        // questions of a pass from the answered questions of the previous pass
        // with the above solution, only the answered questions of the last pass are counted
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT MAX(pass) maxpass FROM tst_test_result WHERE active_fi = %s AND question_fi = %s",
            array('integer','integer'),
            array($active_id, $question_id)
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            return (int) $row["maxpass"];
        }

        return 0;
    }

    public static function _isWriteable(int $question_id, int $user_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (($question_id < 1) || ($user_id < 1)) {
            return false;
        }

        $result = $ilDB->queryF(
            "SELECT obj_fi FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($ilDB->numRows($result) == 1) {
            $row = $ilDB->fetchAssoc($result);
            $qpl_object_id = (int) $row["obj_fi"];
            return ilObjQuestionPool::_isWriteable($qpl_object_id, $user_id);
        }

        return false;
    }

    /**
     * Returns the points, a learner has reached answering the question.
     * The points are calculated from the given answers.
     *
     * @abstract
     * @access public
     * @param integer $active_id
     * @param integer $pass
     * @param boolean $returndetails (deprecated !!)
     * @return integer/array $points/$details (array $details is deprecated !!)
     */
    abstract public function calculateReachedPoints($active_id, $pass = null, $authorizedSolution = true, $returndetails = false);

    public function deductHintPointsFromReachedPoints(ilAssQuestionPreviewSession $previewSession, $reachedPoints): ?float
    {
        global $DIC;

        $hintTracking = new ilAssQuestionPreviewHintTracking($DIC->database(), $previewSession);
        $requestsStatisticData = $hintTracking->getRequestStatisticData();
        $reachedPoints = $reachedPoints - $requestsStatisticData->getRequestsPoints();

        return $reachedPoints;
    }

    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $previewSession)
    {
        $reachedPoints = $this->calculateReachedPointsForSolution($previewSession->getParticipantsSolution());
        $reachedPoints = $this->deductHintPointsFromReachedPoints($previewSession, $reachedPoints);

        return $this->ensureNonNegativePoints($reachedPoints);
    }

    protected function ensureNonNegativePoints($points)
    {
        return $points > 0 ? $points : 0;
    }

    public function isPreviewSolutionCorrect(ilAssQuestionPreviewSession $previewSession): bool
    {
        $reachedPoints = $this->calculateReachedPointsFromPreviewSession($previewSession);

        return !($reachedPoints < $this->getMaximumPoints());
    }


    /**
     * Adjust the given reached points by checks for all
     * special scoring options in the test container.
     *
     * @final
     * @access public
     * @param integer $points
     * @param integer $active_id
     * @param integer $pass
     */
    final public function adjustReachedPointsByScoringOptions($points, $active_id, $pass = null): float
    {
        $count_system = ilObjTest::_getCountSystem($active_id);
        if ($count_system == 1) {
            if (abs($this->getMaximumPoints() - $points) > 0.0000000001) {
                $points = 0;
            }
        }
        $score_cutting = ilObjTest::_getScoreCutting($active_id);
        if ($score_cutting == 0) {
            if ($points < 0) {
                $points = 0;
            }
        }
        return $points;
    }
    public function buildHashedImageFilename(string $plain_image_filename, bool $unique = false): string
    {
        $extension = "";

        if (preg_match("/.*\.(png|jpg|gif|jpeg)$/i", $plain_image_filename, $matches)) {
            $extension = "." . $matches[1];
        }

        if ($unique) {
            $plain_image_filename = uniqid($plain_image_filename . microtime(true), true);
        }

        return md5($plain_image_filename) . $extension;
    }

    /**
    * Sets the points, a learner has reached answering the question
    * Additionally objective results are updated
    *
    * @param integer $user_id The database ID of the learner
    * @param integer $test_id The database Id of the test containing the question
    * @param integer $points The points the user has reached answering the question
    * @return boolean true on success, otherwise false
    * @access public
    */
    public static function _setReachedPoints(
        int $active_id,
        int $question_id,
        float $points,
        float $maxpoints,
        int $pass,
        bool $manualscoring,
        bool $obligationsEnabled
    ): bool {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $refinery = $DIC['refinery'];

        $float_trafo = $refinery->kindlyTo()->float();
        try {
            $points = $float_trafo->transform($points);
        } catch (ILIAS\Refinery\ConstraintViolationException $e) {
            return false;
        }

        if ($points <= $maxpoints) {
            if (is_null($pass)) {
                $pass = assQuestion::_getSolutionMaxPass($question_id, $active_id);
            }

            // retrieve the already given points
            $old_points = 0;
            $result = $ilDB->queryF(
                "SELECT points FROM tst_test_result WHERE active_fi = %s AND question_fi = %s AND pass = %s",
                array('integer','integer','integer'),
                array($active_id, $question_id, $pass)
            );
            $manual = ($manualscoring) ? 1 : 0;
            $rowsnum = $result->numRows();
            if ($rowsnum > 0) {
                $row = $ilDB->fetchAssoc($result);
                $old_points = $row["points"];
                if ($old_points != $points) {
                    $affectedRows = $ilDB->manipulateF(
                        "UPDATE tst_test_result SET points = %s, manual = %s, tstamp = %s WHERE active_fi = %s AND question_fi = %s AND pass = %s",
                        array('float', 'integer', 'integer', 'integer', 'integer', 'integer'),
                        array($points, $manual, time(), $active_id, $question_id, $pass)
                    );
                }
            } else {
                $next_id = $ilDB->nextId('tst_test_result');
                $affectedRows = $ilDB->manipulateF(
                    "INSERT INTO tst_test_result (test_result_id, active_fi, question_fi, points, pass, manual, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                    array('integer', 'integer','integer', 'float', 'integer', 'integer','integer'),
                    array($next_id, $active_id, $question_id, $points, $pass, $manual, time())
                );
            }

            if (self::isForcePassResultUpdateEnabled() || $old_points != $points || $rowsnum == 0) {
                $test_id = ilObjTest::_lookupTestObjIdForQuestionId($question_id);
                if ($test_id === null) {
                    return false;
                }
                $test = new ilObjTest(
                    $test_id,
                    false
                );
                $test->updateTestPassResults($active_id, $pass, $obligationsEnabled);
                ilCourseObjectiveResult::_updateObjectiveResult(ilObjTest::_getUserIdFromActiveId($active_id), $active_id, $question_id);
                if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                    global $DIC;
                    $lng = $DIC['lng'];
                    $ilUser = $DIC['ilUser'];
                    $username = ilObjTestAccess::_getParticipantData($active_id);
                    assQuestion::logAction(sprintf(
                        $lng->txtlng(
                            "assessment",
                            "log_answer_changed_points",
                            ilObjAssessmentFolder::_getLogLanguage()
                        ),
                        $username,
                        $old_points,
                        $points,
                        $ilUser->getFullname() . " (" . $ilUser->getLogin() . ")"
                    ), $active_id, $question_id);
                }
            }

            return true;
        }

        return false;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function getQuestionForHTMLOutput(): string
    {
        $question_text = $this->getHtmlQuestionContentPurifier()->purify($this->question);
        if ($this->isAdditionalContentEditingModePageObject()
            || !(new ilSetting('advanced_editing'))->get('advanced_editing_javascript_editor') === 'tinymce') {
            $question_text = nl2br($question_text);
        }
        return ilLegacyFormElementsUtil::prepareTextareaOutput(
            $question_text,
            true,
            true
        );
    }

    public function setQuestion(string $question = ""): void
    {
        $this->question = $question;
    }

    /**
    * Returns the question type of the question
    *
    * @return string The question type of the question
    */
    abstract public function getQuestionType(): string;

    public function getQuestionTypeID(): int
    {
        $result = $this->db->queryF(
            "SELECT question_type_id FROM qpl_qst_type WHERE type_tag = %s",
            array('text'),
            array($this->getQuestionType())
        );
        if ($this->db->numRows($result) == 1) {
            $row = $this->db->fetchAssoc($result);
            return (int) $row["question_type_id"];
        }
        return 0;
    }

    public function syncHints(): void
    {
        // delete hints of the original
        $this->db->manipulateF(
            "DELETE FROM qpl_hints WHERE qht_question_fi = %s",
            array('integer'),
            array($this->original_id)
        );

        // get hints of the actual question
        $result = $this->db->queryF(
            "SELECT * FROM qpl_hints WHERE qht_question_fi = %s",
            array('integer'),
            array($this->getId())
        );

        // save hints to the original
        if ($this->db->numRows($result) > 0) {
            while ($row = $this->db->fetchAssoc($result)) {
                $next_id = $this->db->nextId('qpl_hints');
                $this->db->insert(
                    'qpl_hints',
                    array(
                        'qht_hint_id' => array('integer', $next_id),
                        'qht_question_fi' => array('integer', $this->original_id),
                        'qht_hint_index' => array('integer', $row["qht_hint_index"]),
                        'qht_hint_points' => array('float', $row["qht_hint_points"]),
                        'qht_hint_text' => array('text', $row["qht_hint_text"]),
                    )
                );
            }
        }
    }

    protected function getRTETextWithMediaObjects(): string
    {
        // must be called in parent classes. add additional RTE text in the parent
        // classes and call this method to add the standard RTE text
        $collected = $this->getQuestion();
        $collected .= $this->feedbackOBJ->getGenericFeedbackContent($this->getId(), false);
        $collected .= $this->feedbackOBJ->getGenericFeedbackContent($this->getId(), true);
        $collected .= $this->feedbackOBJ->getAllSpecificAnswerFeedbackContents($this->getId());

        $questionHintList = ilAssQuestionHintList::getListByQuestionId($this->getId());
        foreach ($questionHintList as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */
            $collected .= $questionHint->getText();
        }

        return $collected;
    }

    public function cleanupMediaObjectUsage(): void
    {
        $combinedtext = $this->getRTETextWithMediaObjects();
        ilRTE::_cleanupMediaObjectUsage($combinedtext, "qpl:html", $this->getId());
    }

    public function getInstances(): array
    {
        $result = $this->db->queryF(
            "SELECT question_id FROM qpl_questions WHERE original_id = %s",
            array("integer"),
            array($this->getId())
        );
        $instances = [];
        $ids = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $ids[] = $row["question_id"];
        }
        foreach ($ids as $question_id) {
            // check non random tests
            $result = $this->db->queryF(
                "SELECT tst_tests.obj_fi FROM tst_tests, tst_test_question WHERE tst_test_question.question_fi = %s AND tst_test_question.test_fi = tst_tests.test_id",
                array("integer"),
                array($question_id)
            );
            while ($row = $this->db->fetchAssoc($result)) {
                $instances[$row['obj_fi']] = ilObject::_lookupTitle($row['obj_fi']);
            }
            // check random tests
            $result = $this->db->queryF(
                "SELECT tst_tests.obj_fi FROM tst_tests, tst_test_rnd_qst, tst_active WHERE tst_test_rnd_qst.active_fi = tst_active.active_id AND tst_test_rnd_qst.question_fi = %s AND tst_tests.test_id = tst_active.test_fi",
                array("integer"),
                array($question_id)
            );
            while ($row = $this->db->fetchAssoc($result)) {
                $instances[$row['obj_fi']] = ilObject::_lookupTitle($row['obj_fi']);
            }
        }
        foreach ($instances as $key => $value) {
            $instances[$key] = array("obj_id" => $key, "title" => $value, "author" => ilObjTest::_lookupAuthor($key), "refs" => ilObject::_getAllReferences($key));
        }
        return $instances;
    }

    public static function _needsManualScoring(int $question_id): bool
    {
        global $DIC;
        $questioninfo = $DIC->testQuestionPool()->questionInfo();
        $scoring = ilObjAssessmentFolder::_getManualScoringTypes();
        $questiontype = $questioninfo->getQuestionType($question_id);
        if (in_array($questiontype, $scoring)) {
            return true;
        }

        return false;
    }

    /**
    * Returns the user id and the test id for a given active id
    *
    * @param integer $active_id Active id for a test/user
    * @return array Result array containing the user_id and test_id
    */
    public function getActiveUserData(int $active_id): array
    {
        $result = $this->db->queryF(
            "SELECT * FROM tst_active WHERE active_id = %s",
            array('integer'),
            array($active_id)
        );
        if ($this->db->numRows($result)) {
            $row = $this->db->fetchAssoc($result);
            return array("user_id" => $row["user_fi"], "test_id" => $row["test_fi"]);
        }

        return [];
    }

    public function hasSpecificFeedback(): bool
    {
        return static::HAS_SPECIFIC_FEEDBACK;
    }

    public static function getFeedbackClassNameByQuestionType(string $questionType): string
    {
        return str_replace('ass', 'ilAss', $questionType) . 'Feedback';
    }



    public static function instantiateQuestionGUI(int $a_question_id): assQuestionGUI
    {
        //Shouldn't you live in assQuestionGUI, Mister?

        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $questioninfo = $DIC->testQuestionPool()->questionInfo();
        if (strcmp($a_question_id, "") != 0) {
            $question_type = $questioninfo->getQuestionType($a_question_id);

            $question_type_gui = $question_type . 'GUI';
            $question_gui = new $question_type_gui();
            $question_gui->object->loadFromDb($a_question_id);

            $feedbackObjectClassname = self::getFeedbackClassNameByQuestionType($question_type);
            $question_gui->object->feedbackOBJ = new $feedbackObjectClassname($question_gui->object, $ilCtrl, $ilDB, $lng);

            $assSettings = new ilSetting('assessment');
            $processLockerFactory = new ilAssQuestionProcessLockerFactory($assSettings, $ilDB);
            $processLockerFactory->setQuestionId($question_gui->object->getId());
            $processLockerFactory->setUserId($ilUser->getId());
            $processLockerFactory->setAssessmentLogEnabled(ilObjAssessmentFolder::_enabledAssessmentLogging());
            $question_gui->object->setProcessLocker($processLockerFactory->getLocker());
        } else {
            global $DIC;
            $ilLog = $DIC['ilLog'];
            $ilLog->write('Instantiate question called without question id. (instantiateQuestionGUI@assQuestion)', $ilLog->WARNING);
            throw new InvalidArgumentException('Instantiate question called without question id. (instantiateQuestionGUI@assQuestion)');
        }
        return $question_gui;
    }

    public function setExportDetailsXLSX(ilAssExcelFormatHelper $worksheet, int $startrow, int $col, int $active_id, int $pass): int
    {
        $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col) . $startrow, $this->lng->txt($this->getQuestionType()));
        $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord($col + 1) . $startrow, $this->getTitle());

        return $startrow;
    }

    public function getNrOfTries(): int
    {
        return $this->nr_of_tries;
    }

    public function setNrOfTries(int $a_nr_of_tries): void
    {
        $this->nr_of_tries = $a_nr_of_tries;
    }

    public function setExportImagePath(string $path): void
    {
        $this->export_image_path = $path;
    }

    public static function _questionExistsInTest(int $question_id, int $test_id): bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($question_id < 1) {
            return false;
        }

        $result = $ilDB->queryF(
            "SELECT question_fi FROM tst_test_question WHERE question_fi = %s AND test_fi = %s",
            array('integer', 'integer'),
            array($question_id, $test_id)
        );
        return $ilDB->numRows($result) == 1;
    }

    public function formatSAQuestion($a_q): string
    {
        return $this->getSelfAssessmentFormatter()->format($a_q);
    }

    protected function getSelfAssessmentFormatter(): \ilAssSelfAssessmentQuestionFormatter
    {
        return new \ilAssSelfAssessmentQuestionFormatter();
    }

    // scorm2004-start ???

    public function setPreventRteUsage(bool $prevent_rte_usage): void
    {
        $this->prevent_rte_usage = $prevent_rte_usage;
    }

    public function getPreventRteUsage(): bool
    {
        return $this->prevent_rte_usage;
    }

    public function migrateContentForLearningModule(ilAssSelfAssessmentMigrator $migrator): void
    {
        $this->lmMigrateQuestionTypeGenericContent($migrator);
        $this->lmMigrateQuestionTypeSpecificContent($migrator);
        $this->saveToDb();

        $this->feedbackOBJ->migrateContentForLearningModule($migrator, $this->getId());
    }

    protected function lmMigrateQuestionTypeGenericContent(ilAssSelfAssessmentMigrator $migrator): void
    {
        $this->setQuestion($migrator->migrateToLmContent($this->getQuestion()));
    }

    protected function lmMigrateQuestionTypeSpecificContent(ilAssSelfAssessmentMigrator $migrator): void
    {
        // overwrite if any question type specific content except feedback needs to be migrated
    }

    public function setSelfAssessmentEditingMode(bool $selfassessmenteditingmode): void
    {
        $this->selfassessmenteditingmode = $selfassessmenteditingmode;
    }

    public function getSelfAssessmentEditingMode(): bool
    {
        return $this->selfassessmenteditingmode;
    }

    public function setDefaultNrOfTries(int $defaultnroftries): void
    {
        $this->defaultnroftries = $defaultnroftries;
    }

    public function getDefaultNrOfTries(): int
    {
        return $this->defaultnroftries;
    }

    // scorm2004-end ???

    public static function lookupParentObjId(int $questionId): int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT obj_fi FROM qpl_questions WHERE question_id = %s";

        $res = $ilDB->queryF($query, array('integer'), array($questionId));
        $row = $ilDB->fetchAssoc($res);

        return $row['obj_fi'];
    }

    protected function duplicateQuestionHints(int $originalQuestionId, int $duplicateQuestionId): void
    {
        $hintIds = ilAssQuestionHintList::duplicateListForQuestion($originalQuestionId, $duplicateQuestionId);

        if ($this->isAdditionalContentEditingModePageObject()) {
            foreach ($hintIds as $originalHintId => $duplicateHintId) {
                $this->ensureHintPageObjectExists($originalHintId);
                $originalPageObject = new ilAssHintPage($originalHintId);
                $originalXML = $originalPageObject->getXMLContent();

                $duplicatePageObject = new ilAssHintPage();
                $duplicatePageObject->setId($duplicateHintId);
                $duplicatePageObject->setParentId($this->getId());
                $duplicatePageObject->setXMLContent($originalXML);
                $duplicatePageObject->createFromXML();
            }
        }
    }

    protected function duplicateSkillAssignments(int $srcParentId, int $srcQuestionId, int $trgParentId, int $trgQuestionId): void
    {
        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($srcParentId);
        $assignmentList->setQuestionIdFilter($srcQuestionId);
        $assignmentList->loadFromDb();

        foreach ($assignmentList->getAssignmentsByQuestionId($srcQuestionId) as $assignment) {
            $assignment->setParentObjId($trgParentId);
            $assignment->setQuestionId($trgQuestionId);
            $assignment->saveToDb();

            // add skill usage
            $this->skillUsageService->addUsage(
                $trgParentId,
                $assignment->getSkillBaseId(),
                $assignment->getSkillTrefId()
            );
        }
    }

    public function syncSkillAssignments(int $srcParentId, int $srcQuestionId, int $trgParentId, int $trgQuestionId): void
    {
        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($trgParentId);
        $assignmentList->setQuestionIdFilter($trgQuestionId);
        $assignmentList->loadFromDb();

        foreach ($assignmentList->getAssignmentsByQuestionId($trgQuestionId) as $assignment) {
            $assignment->deleteFromDb();

            // remove skill usage
            if (!$assignment->isSkillUsed()) {
                $this->skillUsageService->removeUsage(
                    $assignment->getParentObjId(),
                    $assignment->getSkillBaseId(),
                    $assignment->getSkillTrefId()
                );
            }
        }

        $this->duplicateSkillAssignments($srcParentId, $srcQuestionId, $trgParentId, $trgQuestionId);
    }

    public function ensureHintPageObjectExists($pageObjectId): void
    {
        if (!ilAssHintPage::_exists('qht', $pageObjectId)) {
            $pageObject = new ilAssHintPage();
            $pageObject->setParentId($this->getId());
            $pageObject->setId($pageObjectId);
            $pageObject->createFromXML();
        }
    }

    public function isAnswered(int $active_id, int $pass): bool
    {
        return true;
    }

    public static function isObligationPossible(int $questionId): bool
    {
        return false;
    }

    protected static function getNumExistingSolutionRecords(int $activeId, int $pass, int $questionId): int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "
			SELECT		count(active_fi) cnt

			FROM		tst_solutions

			WHERE		active_fi = %s
			AND			question_fi = %s
			AND			pass = %s
		";

        $res = $ilDB->queryF(
            $query,
            array('integer','integer','integer'),
            array($activeId, $questionId, $pass)
        );

        $row = $ilDB->fetchAssoc($res);

        return (int) $row['cnt'];
    }

    public function getAdditionalContentEditingMode(): string
    {
        return $this->additionalContentEditingMode;
    }

    public function setAdditionalContentEditingMode(?string $additionalContentEditingMode): void
    {
        if (!in_array((string) $additionalContentEditingMode, $this->getValidAdditionalContentEditingModes())) {
            throw new ilTestQuestionPoolException('invalid additional content editing mode given: ' . $additionalContentEditingMode);
        }

        $this->additionalContentEditingMode = $additionalContentEditingMode;
    }

    public function isAdditionalContentEditingModePageObject(): bool
    {
        return $this->getAdditionalContentEditingMode() == assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE;
    }

    public function isValidAdditionalContentEditingMode(string $additionalContentEditingMode): bool
    {
        if (in_array($additionalContentEditingMode, $this->getValidAdditionalContentEditingModes())) {
            return true;
        }

        return false;
    }

    public function getValidAdditionalContentEditingModes(): array
    {
        return array(
            self::ADDITIONAL_CONTENT_EDITING_MODE_RTE,
            self::ADDITIONAL_CONTENT_EDITING_MODE_IPE
        );
    }

    /**
     * @return ilHtmlPurifierInterface|ilAssHtmlUserSolutionPurifier
     */
    public function getHtmlUserSolutionPurifier(): ilHtmlPurifierInterface
    {
        return ilHtmlPurifierFactory::getInstanceByType('qpl_usersolution');
    }

    /**
     * @return ilHtmlPurifierInterface|ilAssHtmlUserSolutionPurifier
     */
    public function getHtmlQuestionContentPurifier(): ilHtmlPurifierInterface
    {
        return ilHtmlPurifierFactory::getInstanceByType('qpl_usersolution');
    }

    protected function buildQuestionDataQuery(): string
    {
        return "
			SELECT 		qpl_questions.*,
						{$this->getAdditionalTableName()}.*
			FROM		qpl_questions
			LEFT JOIN	{$this->getAdditionalTableName()}
			ON			{$this->getAdditionalTableName()}.question_fi = qpl_questions.question_id
			WHERE			qpl_questions.question_id = %s
		";
    }

    public function setLastChange($lastChange): void
    {
        $this->lastChange = $lastChange;
    }

    public function getLastChange()
    {
        return $this->lastChange;
    }

    protected function getCurrentSolutionResultSet(int $active_id, int $pass, bool $authorized = true): \ilDBStatement
    {
        if ($this->getStep() !== null) {
            $query = "
				SELECT *
				FROM tst_solutions
				WHERE active_fi = %s
				AND question_fi = %s
				AND pass = %s
				AND step = %s
				AND authorized = %s
			";

            return $this->db->queryF(
                $query,
                array('integer', 'integer', 'integer', 'integer', 'integer'),
                array($active_id, $this->getId(), $pass, $this->getStep(), (int) $authorized)
            );
        }

        $query = "
            SELECT *
            FROM tst_solutions
            WHERE active_fi = %s
            AND question_fi = %s
            AND pass = %s
            AND authorized = %s
        ";

        return $this->db->queryF(
            $query,
            array('integer', 'integer', 'integer', 'integer'),
            array($active_id, $this->getId(), $pass, (int) $authorized)
        );
    }

    protected function removeSolutionRecordById(int $solutionId): int
    {
        return $this->db->manipulateF(
            "DELETE FROM tst_solutions WHERE solution_id = %s",
            array('integer'),
            array($solutionId)
        );
    }

    // hey: prevPassSolutions - selected file reuse, copy solution records
    /**
     * @return array tst_solutions.*
     */
    protected function getSolutionRecordById(int $solutionId): array
    {
        $result = $this->db->queryF(
            "SELECT * FROM tst_solutions WHERE solution_id = %s",
            array('integer'),
            array($solutionId)
        );

        if ($this->db->numRows($result) > 0) {
            return $this->db->fetchAssoc($result);
        }
        return [];
    }
    // hey.

    public function removeIntermediateSolution(int $active_id, int $pass): void
    {
        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use ($active_id, $pass) {
            $this->removeCurrentSolution($active_id, $pass, false);
        });
    }

    /**
     * @return int Affected rows
     */
    public function removeCurrentSolution(int $active_id, int $pass, bool $authorized = true): int
    {
        if ($this->getStep() !== null) {
            $query = '
				DELETE FROM tst_solutions
				WHERE active_fi = %s
				AND question_fi = %s
				AND pass = %s
				AND step = %s
				AND authorized = %s
			';

            return $this->db->manipulateF(
                $query,
                array('integer', 'integer', 'integer', 'integer', 'integer'),
                array($active_id, $this->getId(), $pass, $this->getStep(), (int) $authorized)
            );
        }

        $query = "
            DELETE FROM tst_solutions
            WHERE active_fi = %s
            AND question_fi = %s
            AND pass = %s
            AND authorized = %s
        ";

        return $this->db->manipulateF(
            $query,
            array('integer', 'integer', 'integer', 'integer'),
            array($active_id, $this->getId(), $pass, (int) $authorized)
        );
    }

    // fau: testNav - add timestamp as parameter to saveCurrentSolution
    public function saveCurrentSolution(int $active_id, int $pass, $value1, $value2, bool $authorized = true, $tstamp = 0): int
    {
        $next_id = $this->db->nextId("tst_solutions");

        $fieldData = array(
            "solution_id" => array("integer", $next_id),
            "active_fi" => array("integer", $active_id),
            "question_fi" => array("integer", $this->getId()),
            "value1" => array("clob", $value1),
            "value2" => array("clob", $value2),
            "pass" => array("integer", $pass),
            "tstamp" => array("integer", ((int)$tstamp > 0) ? (int)$tstamp : time()),
            'authorized' => array('integer', (int) $authorized)
        );

        if ($this->getStep() !== null) {
            $fieldData['step'] = array("integer", $this->getStep());
        }

        return $this->db->insert("tst_solutions", $fieldData);
    }
    // fau.

    public function updateCurrentSolution(int $solutionId, $value1, $value2, bool $authorized = true): int
    {
        $fieldData = array(
            "value1" => array("clob", $value1),
            "value2" => array("clob", $value2),
            "tstamp" => array("integer", time()),
            'authorized' => array('integer', (int) $authorized)
        );

        if ($this->getStep() !== null) {
            $fieldData['step'] = array("integer", $this->getStep());
        }

        return $this->db->update("tst_solutions", $fieldData, array(
            'solution_id' => array('integer', $solutionId)
        ));
    }

    // fau: testNav - added parameter to keep the timestamp (default: false)
    public function updateCurrentSolutionsAuthorization(int $activeId, int $pass, bool $authorized, bool $keepTime = false): int
    {
        $fieldData = array(
            'authorized' => array('integer', (int) $authorized)
        );

        if (!$keepTime) {
            $fieldData['tstamp'] = array('integer', time());
        }

        $whereData = array(
            'question_fi' => array('integer', $this->getId()),
            'active_fi' => array('integer', $activeId),
            'pass' => array('integer', $pass)
        );

        if ($this->getStep() !== null) {
            $whereData['step'] = array("integer", $this->getStep());
        }

        return $this->db->update('tst_solutions', $fieldData, $whereData);
    }
    // fau.

    // hey: prevPassSolutions - motivation slowly decreases on imagemap
    public const KEY_VALUES_IMPLOSION_SEPARATOR = ':';

    public static function implodeKeyValues(array $keyValues): string
    {
        return implode(assQuestion::KEY_VALUES_IMPLOSION_SEPARATOR, $keyValues);
    }

    public static function explodeKeyValues(string $keyValues): array
    {
        return explode(assQuestion::KEY_VALUES_IMPLOSION_SEPARATOR, $keyValues);
    }

    protected function deleteDummySolutionRecord(int $activeId, int $passIndex): void
    {
        foreach ($this->getSolutionValues($activeId, $passIndex, false) as $solutionRec) {
            if ($solutionRec['value1'] == '' && $solutionRec['value2'] == '') {
                $this->removeSolutionRecordById($solutionRec['solution_id']);
            }
        }
    }

    protected function isDummySolutionRecord(array $solutionRecord): bool
    {
        return !strlen($solutionRecord['value1']) && !strlen($solutionRecord['value2']);
    }

    protected function deleteSolutionRecordByValues(int $activeId, int $passIndex, bool $authorized, array $matchValues): void
    {
        $types = array("integer", "integer", "integer", "integer");
        $values = array($activeId, $this->getId(), $passIndex, (int) $authorized);
        $valuesCondition = [];

        foreach ($matchValues as $valueField => $value) {
            switch ($valueField) {
                case 'value1':
                case 'value2':
                    $valuesCondition[] = "{$valueField} = %s";
                    $types[] = 'text';
                    $values[] = $value;
                    break;

                default:
                    throw new ilTestQuestionPoolException('invalid value field given: ' . $valueField);
            }
        }

        $valuesCondition = implode(' AND ', $valuesCondition);

        $query = "
			DELETE FROM tst_solutions
			WHERE active_fi = %s
			AND question_fi = %s
			AND pass = %s
			AND authorized = %s
			AND $valuesCondition
		";

        if ($this->getStep() !== null) {
            $query .= " AND step = %s ";
            $types[] = 'integer';
            $values[] = $this->getStep();
        }

        $this->db->manipulateF($query, $types, $values);
    }

    protected function duplicateIntermediateSolutionAuthorized(int $activeId, int $passIndex): void
    {
        foreach ($this->getSolutionValues($activeId, $passIndex, false) as $rec) {
            $this->saveCurrentSolution($activeId, $passIndex, $rec['value1'], $rec['value2'], true, $rec['tstamp']);
        }
    }

    protected function forceExistingIntermediateSolution(int $activeId, int $passIndex, bool $considerDummyRecordCreation): void
    {
        $intermediateSolution = $this->getSolutionValues($activeId, $passIndex, false);

        if (!count($intermediateSolution)) {
            // make the authorized solution intermediate (keeping timestamps)
            // this keeps the solution_ids in synch with eventually selected in $_POST['deletefiles']
            $this->updateCurrentSolutionsAuthorization($activeId, $passIndex, false, true);

            // create a backup as authorized solution again (keeping timestamps)
            $this->duplicateIntermediateSolutionAuthorized($activeId, $passIndex);

            if ($considerDummyRecordCreation) {
                // create an additional dummy record to indicate the existence of an intermediate solution
                // even if all entries are deleted from the intermediate solution later
                $this->saveCurrentSolution($activeId, $passIndex, null, null, false);
            }
        }
    }
    // hey.

    /**
     * @param int|null $step
     */
    public function setStep($step): void
    {
        $this->step = $step;
    }

    /**
     * @return int|null
     */
    public function getStep(): ?int
    {
        return $this->step;
    }

    public static function convertISO8601FormatH_i_s_ExtendedToSeconds(string $time): int
    {
        $sec = 0;
        $time_array = explode(':', $time);
        if (count($time_array) == 3) {
            $sec += (int) $time_array[0] * 3600;
            $sec += (int) $time_array[1] * 60;
            $sec += (int) $time_array[2];
        }
        return $sec;
    }

    public function toJSON(): string
    {
        return json_encode([]);
    }

    abstract public function duplicate(bool $for_test = true, string $title = "", string $author = "", string $owner = "", $testObjId = null): int;

    // hey: prevPassSolutions - check for authorized solution
    public function intermediateSolutionExists(int $active_id, int $pass): bool
    {
        $solutionAvailability = $this->lookupForExistingSolutions($active_id, $pass);
        return (bool) $solutionAvailability['intermediate'];
    }

    public function authorizedSolutionExists(int $active_id, int $pass): bool
    {
        $solutionAvailability = $this->lookupForExistingSolutions($active_id, $pass);
        return (bool) $solutionAvailability['authorized'];
    }

    public function authorizedOrIntermediateSolutionExists(int $active_id, int $pass): bool
    {
        $solutionAvailability = $this->lookupForExistingSolutions($active_id, $pass);
        return $solutionAvailability['authorized'] || $solutionAvailability['intermediate'];
    }
    // hey.

    protected function lookupMaxStep(int $active_id, int $pass): int
    {
        $result = $this->db->queryF(
            "SELECT MAX(step) max_step FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s",
            array("integer", "integer", "integer"),
            array($active_id, $pass, $this->getId())
        );

        $row = $this->db->fetchAssoc($result);

        return (int) $row['max_step'];
    }

    // fau: testNav - new function lookupForExistingSolutions
    /**
     * Lookup if an authorized or intermediate solution exists
     * @return 	array		['authorized' => bool, 'intermediate' => bool]
     */
    public function lookupForExistingSolutions(int $activeId, int $pass): array
    {
        $return = array(
            'authorized' => false,
            'intermediate' => false
        );

        $query = "
			SELECT authorized, COUNT(*) cnt
			FROM tst_solutions
			WHERE active_fi = %s
			AND question_fi = %s
			AND pass = %s
		";

        if ($this->getStep() !== null) {
            $query .= " AND step = " . $this->db->quote((int) $this->getStep(), 'integer') . " ";
        }

        $query .= "
			GROUP BY authorized
		";

        $result = $this->db->queryF($query, array('integer', 'integer', 'integer'), array($activeId, $this->getId(), $pass));

        while ($row = $this->db->fetchAssoc($result)) {
            if ($row['authorized']) {
                $return['authorized'] = $row['cnt'] > 0;
            } else {
                $return['intermediate'] = $row['cnt'] > 0;
            }
        }
        return $return;
    }
    // fau.

    public function isAddableAnswerOptionValue(int $qIndex, string $answerOptionValue): bool
    {
        return false;
    }

    public function addAnswerOptionValue(int $qIndex, string $answerOptionValue, float $points): void
    {
    }

    public function removeAllExistingSolutions(): void
    {
        $query = "DELETE FROM tst_solutions WHERE question_fi = %s";
        $this->db->manipulateF($query, array('integer'), array($this->getId()));
    }

    public function removeExistingSolutions(int $activeId, int $pass): int
    {
        $query = "
			DELETE FROM tst_solutions
			WHERE active_fi = %s
			AND question_fi = %s
			AND pass = %s
		";

        if ($this->getStep() !== null) {
            $query .= " AND step = " . $this->db->quote((int) $this->getStep(), 'integer') . " ";
        }

        return $this->db->manipulateF(
            $query,
            array('integer', 'integer', 'integer'),
            array($activeId, $this->getId(), $pass)
        );
    }

    public function resetUsersAnswer(int $activeId, int $pass): void
    {
        $this->removeExistingSolutions($activeId, $pass);
        $this->removeResultRecord($activeId, $pass);

        $this->log($activeId, "log_user_solution_willingly_deleted");

        $test = new ilObjTest(
            ilObjTest::_lookupTestObjIdForQuestionId($this->getId()),
            false
        );
        $test->updateTestPassResults(
            $activeId,
            $pass,
            $this->areObligationsToBeConsidered(),
            $this->getProcessLocker(),
            $this->getTestId()
        );
    }

    public function removeResultRecord(int $activeId, int $pass): int
    {
        $query = "
			DELETE FROM tst_test_result
			WHERE active_fi = %s
			AND question_fi = %s
			AND pass = %s
		";

        if ($this->getStep() !== null) {
            $query .= " AND step = " . $this->db->quote((int) $this->getStep(), 'integer') . " ";
        }

        return $this->db->manipulateF(
            $query,
            array('integer', 'integer', 'integer'),
            array($activeId, $this->getId(), $pass)
        );
    }

    public function fetchValuePairsFromIndexedValues(array $indexedValues): array
    {
        $valuePairs = [];

        foreach ($indexedValues as $value1 => $value2) {
            $valuePairs[] = array('value1' => $value1, 'value2' => $value2);
        }

        return $valuePairs;
    }

    public function fetchIndexedValuesFromValuePairs(array $valuePairs): array
    {
        $indexedValues = [];

        foreach ($valuePairs as $valuePair) {
            $indexedValues[ $valuePair['value1'] ] = $valuePair['value2'];
        }

        return $indexedValues;
    }

    public function areObligationsToBeConsidered(): bool
    {
        return $this->obligationsToBeConsidered;
    }

    public function setObligationsToBeConsidered(bool $obligationsToBeConsidered): void
    {
        $this->obligationsToBeConsidered = $obligationsToBeConsidered;
    }

    public function updateTimestamp(): void
    {
        $this->db->manipulateF(
            "UPDATE qpl_questions SET tstamp = %s  WHERE question_id = %s",
            array('integer', 'integer'),
            array(time(), $this->getId())
        );
    }

    // fau: testNav - new function getTestQuestionConfig()
    // hey: prevPassSolutions - get caching independent from configuration (config once)
    //					renamed: getTestPresentationConfig() -> does the caching
    //					completed: extracted instance building
    //					avoids configuring cached instances on every access
    //					allows a stable reconfigure of the instance from outside
    private ?ilTestQuestionConfig $testQuestionConfigInstance = null;

    public function getTestPresentationConfig(): ilTestQuestionConfig
    {
        if ($this->testQuestionConfigInstance === null) {
            $this->testQuestionConfigInstance = $this->buildTestPresentationConfig();
        }

        return $this->testQuestionConfigInstance;
    }

    /**
     * build basic test question configuration instance
     *
     * method can be overwritten to configure an instance
     * use parent call for building when possible
     */
    protected function buildTestPresentationConfig(): ilTestQuestionConfig
    {
        return new ilTestQuestionConfig();
    }

    protected ?assQuestionSuggestedSolutionsDatabaseRepository $suggestedsolution_repo = null;
    protected function getSuggestedSolutionsRepo(): assQuestionSuggestedSolutionsDatabaseRepository
    {
        if (is_null($this->suggestedsolution_repo)) {
            $dic = ilQuestionPoolDIC::dic();
            $this->suggestedsolution_repo = $dic['question.repo.suggestedsolutions'];
        }
        return $this->suggestedsolution_repo;
    }

    protected function loadSuggestedSolutions(): array
    {
        $question_id = $this->getId();
        return $this->getSuggestedSolutionsRepo()->selectFor($question_id);
    }
}
