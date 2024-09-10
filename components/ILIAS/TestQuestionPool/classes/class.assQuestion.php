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

use ILIAS\TestQuestionPool\Questions\QuestionPartiallySaveable;
use ILIAS\TestQuestionPool\Questions\Question;
use ILIAS\TestQuestionPool\Questions\SuggestedSolution\SuggestedSolution;
use ILIAS\TestQuestionPool\Questions\SuggestedSolution\SuggestedSolutionsDatabaseRepository;
use ILIAS\TestQuestionPool\QuestionPoolDIC;
use ILIAS\TestQuestionPool\Questions\Files\QuestionFiles;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\TestQuestionPool\RequestDataCollector;

use ILIAS\Test\Logging\TestParticipantInteraction;
use ILIAS\Test\Logging\TestQuestionAdministrationInteraction;
use ILIAS\Test\Logging\TestParticipantInteractionTypes;
use ILIAS\Test\Logging\TestQuestionAdministrationInteractionTypes;
use ILIAS\Test\Logging\AdditionalInformationGenerator;

use ILIAS\Refinery\Transformation;
use ILIAS\DI\Container;
use ILIAS\Skill\Service\SkillUsageService;
use ILIAS\Notes\Service as NotesService;
use ILIAS\Notes\InternalDataService as NotesInternalDataService;
use ILIAS\Notes\NoteDBRepository as NotesRepo;
use ILIAS\Notes\NotesManager;
use ILIAS\Notes\Note;
use ILIAS\DI\LoggingServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTPServices;

/**
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 */
abstract class assQuestion implements Question
{
    protected const HAS_SPECIFIC_FEEDBACK = true;

    public const ADDITIONAL_CONTENT_EDITING_MODE_RTE = 'default';
    public const ADDITIONAL_CONTENT_EDITING_MODE_IPE = 'pageobject';

    protected const DEFAULT_THUMB_SIZE = 150;
    protected const MINIMUM_THUMB_SIZE = 20;
    public const TRIM_PATTERN = '/^[\p{C}\p{Z}]+|[\p{C}\p{Z}]+$/u';

    protected static $force_pass_results_update_enabled = false;

    protected GeneralQuestionPropertiesRepository $questionrepository;
    protected RequestDataCollector $questionpool_request;
    protected QuestionFiles $question_files;
    protected \ilAssQuestionProcessLocker $processLocker;
    protected ilTestQuestionConfig $testQuestionConfig;
    protected SuggestedSolutionsDatabaseRepository $suggestedsolution_repo;

    protected ILIAS $ilias;
    protected ilGlobalPageTemplate $tpl;
    protected ilLanguage $lng;
    protected ilDBInterface $db;
    protected ilObjUser $current_user;
    protected SkillUsageService $skillUsageService;
    protected HTTPServices $http;
    protected Refinery $refinery;
    protected Transformation $shuffler;
    protected LoggingServices $log;
    protected Container $dic;

    private ?ilTestQuestionConfig $test_question_config = null;
    protected \ilAssQuestionLifecycle $lifecycle;
    public \ilAssQuestionFeedback $feedbackOBJ;
    protected \ilAssQuestionPage $page;

    protected int $id;
    protected string $title;
    protected string $comment;
    protected int $owner;
    protected string $author;
    protected int $thumb_size;
    protected string $question;
    protected float $points = 0.0;
    protected bool $shuffle = true;
    protected int $test_id;
    protected int $obj_id = 0;
    protected ?int $original_id = null;
    private int $nr_of_tries;
    protected ?int $lastChange = null;
    private string $export_image_path;
    protected ?string $external_id = null;
    private string $additionalContentEditingMode = '';
    public bool $prevent_rte_usage = false;
    public bool $selfassessmenteditingmode = false;
    public int $defaultnroftries = 0;
    public string $questionActionCmd = 'handleQuestionAction';
    protected ?int $step = null;
    private bool $obligationsToBeConsidered = false;

    /**
     * @var array<ILIAS\TestQuestionPool\Questions\SuggestedSolution\SuggestedSolution>
     */
    protected array $suggested_solutions;

    public function __construct(
        string $title = "",
        string $comment = "",
        string $author = "",
        int $owner = -1,
        string $question = ""
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->dic = $DIC;
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC->logger();
        $local_dic = QuestionPoolDIC::dic();
        $this->questionrepository = $local_dic['question.general_properties.repository'];
        $this->questionpool_request = $local_dic['request_data_collector'];
        $this->question_files = $local_dic['question_files'];
        $this->suggestedsolution_repo = $local_dic['question.repo.suggestedsolutions'];
        $this->current_user = $DIC['ilUser'];
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->db = $ilDB;
        $this->log = $ilLog;
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
        $this->shuffle = true;
        $this->nr_of_tries = 0;
        $this->setExternalId(null);

        $this->questionActionCmd = 'handleQuestionAction';
        $this->export_image_path = '';
        $this->shuffler = $DIC->refinery()->random()->dontShuffle();
        $this->lifecycle = ilAssQuestionLifecycle::getDraftInstance();
        $this->skillUsageService = $DIC->skills()->usage();
    }

    abstract public function getQuestionType(): string;

    abstract public function isComplete(): bool;

    abstract public function saveWorkingData(int $active_id, ?int $pass = null, bool $authorized = true): bool;

    abstract public function calculateReachedPoints(
        int $active_id,
        ?int $pass = null,
        bool $authorized_solution = true
    ): float;

    abstract public function getAdditionalTableName(): string;
    abstract public function getAnswerTableName(): string|array;

    /**
     * MUST return an array of the question settings that can
     * be stored in the log. Language variables must be generated through the
     * corresponding functions in the AdditionalInformationGenerator. If an array
     * is returned it will be rendered into a line per array entry in the format
     * "key: value". If the key exists as a language variable, it will be
     * translated.
     */
    abstract public function toLog(AdditionalInformationGenerator $additional_info): array;

    /**
     * MUST convert the given solution values into an array or a string that can
     * be stored in the log. Language variables must be generated through the
     * corresponding functions in the AdditionalInformationGenerator. If an array
     * is returned it will be rendered into a line per array entry in the format
     * "key: value". If the key exists as a language variable, it will be
     * translated.
     */
    abstract protected function solutionValuesToLog(
        AdditionalInformationGenerator $additional_info,
        array $solution_values
    ): array|string;

    /**
     * MUST convert the given solution values into text. If the text has
     * multiple lines each line MUST be placed as an entry in an array.
     */
    abstract protected function solutionValuesToText(
        array $solution_values
    ): array|string;

    public static function setForcePassResultUpdateEnabled(bool $force_pass_results_update_enabled): void
    {
        self::$force_pass_results_update_enabled = $force_pass_results_update_enabled;
    }

    public static function isForcePassResultUpdateEnabled(): bool
    {
        return self::$force_pass_results_update_enabled;
    }

    protected function getQuestionAction(): string
    {
        if (!isset($_POST['cmd']) || !isset($_POST['cmd'][$this->questionActionCmd])) {
            return '';
        }

        if (!is_array($_POST['cmd'][$this->questionActionCmd]) || $_POST['cmd'][$this->questionActionCmd] === []) {
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

    public function getCurrentUser(): ilObjUser
    {
        return $this->current_user;
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

    final public function fromXML(
        string $importdirectory,
        int $user_id,
        ilQTIItem $item,
        int $questionpool_id,
        ?int $tst_id,
        ?ilObject &$tst_object,
        int &$question_counter,
        array $import_mapping
    ): array {
        $classname = $this->getQuestionType() . "Import";
        $import = new $classname($this);
        $new_import_mapping = $import->fromXML(
            $importdirectory,
            $user_id,
            $item,
            $questionpool_id,
            $tst_id,
            $tst_object,
            $question_counter,
            $import_mapping
        );

        return $new_import_mapping;
    }

    /**
    * Returns a QTI xml representation of the question
    *
    * @return string The QTI xml representation of the question
    */
    final public function toXML(
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
        if ($author === '') {
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

    public function getTitleForHTMLOutput(): string
    {
        return $this->refinery->string()->stripTags()->transform($this->title);
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

    public function getDescriptionForHTMLOutput(): string
    {
        return $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform($this->comment);
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

    public function getAuthorForHTMLOutput(): string
    {
        return $this->refinery->string()->stripTags()->transform($this->author);
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

    public static function _getSuggestedSolutionOutput(int $question_id): string
    {
        $question = self::instantiateQuestion($question_id);
        if (!is_object($question)) {
            return "";
        }
        return $question->getSuggestedSolutionOutput();
    }

    public function getSuggestedSolutionOutput(): string
    {
        $output = [];
        foreach ($this->suggested_solutions as $solution) {
            switch ($solution->getType()) {
                case SuggestedSolution::TYPE_LM:
                case SuggestedSolution::TYPE_LM_CHAPTER:
                case SuggestedSolution::TYPE_LM_PAGE:
                case SuggestedSolution::TYPE_GLOSARY_TERM:
                    $output[] = '<a href="'
                        . $this->getInternalLinkHref($solution->getInternalLink())
                        . '">'
                        . $this->lng->txt("solution_hint")
                        . '</a>';
                    break;

                case SuggestedSolution::TYPE_FILE:
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
            ['integer','integer','integer'],
            [$active_id, $question_id, $pass]
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

    final public function getAdjustedReachedPoints(int $active_id, int $pass, bool $authorized_solution = true): float
    {
        // determine reached points for submitted solution
        $reached_points = $this->calculateReachedPoints($active_id, $pass, $authorized_solution);
        $hint_tracking = new ilAssQuestionHintTracking($this->getId(), $active_id, $pass);
        $requests_statistic_data = $hint_tracking->getRequestStatisticDataByQuestionAndTestpass();
        $reached_points = $reached_points - $requests_statistic_data->getRequestsPoints();

        // adjust reached points regarding to tests scoring options
        $reached_points = $this->adjustReachedPointsByScoringOptions($reached_points, $active_id, $pass);

        return $reached_points;
    }

    /**
     * Calculates the question results from a previously saved question solution
     */
    final public function calculateResultsFromSolution(int $active_id, int $pass, bool $obligations_enabled = false): void
    {
        // determine reached points for submitted solution
        $reached_points = $this->calculateReachedPoints($active_id, $pass);
        $questionHintTracking = new ilAssQuestionHintTracking($this->getId(), $active_id, $pass);
        $requests_statistic_data = $questionHintTracking->getRequestStatisticDataByQuestionAndTestpass();
        $reached_points = $reached_points - $requests_statistic_data->getRequestsPoints();

        // adjust reached points regarding to tests scoring options
        $reached_points = $this->adjustReachedPointsByScoringOptions($reached_points, $active_id, $pass);

        if ($obligations_enabled && ilObjTest::isQuestionObligatory($this->getId())) {
            $isAnswered = $this->isAnswered($active_id, $pass);
        } else {
            $isAnswered = true;
        }

        if (is_null($reached_points)) {
            $reached_points = 0.0;
        }

        // fau: testNav - check for existing authorized solution to know if a result record should be written
        $existing_solutions = $this->lookupForExistingSolutions($active_id, $pass);

        $this->getProcessLocker()->executeUserQuestionResultUpdateOperation(
            function () use ($active_id, $pass, $reached_points, $requests_statistic_data, $isAnswered, $existing_solutions) {
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

                if ($existing_solutions['authorized']) {
                    $next_id = $this->db->nextId("tst_test_result");
                    $fieldData = [
                        'test_result_id' => ['integer', $next_id],
                        'active_fi' => ['integer', $active_id],
                        'question_fi' => ['integer', $this->getId()],
                        'pass' => ['integer', $pass],
                        'points' => ['float', $reached_points],
                        'tstamp' => ['integer', time()],
                        'hint_count' => ['integer', $requests_statistic_data->getRequestsCount()],
                        'hint_points' => ['float', $requests_statistic_data->getRequestsPoints()],
                        'answered' => ['integer', $isAnswered]
                    ];

                    if ($this->getStep() !== null) {
                        $fieldData['step'] = ['integer', $this->getStep()];
                    }

                    $this->db->insert('tst_test_result', $fieldData);
                }
            }
        );

        // update test pass results
        $test = new ilObjTest(
            $this->getObjId(),
            false
        );
        $test->updateTestPassResults($active_id, $pass, $obligations_enabled, $this->getProcessLocker());
        ilCourseObjectiveResult::_updateObjectiveResult($this->current_user->getId(), $active_id, $this->getId());
    }

    /**
     * persists the working state for current testactive and testpass
     * @return bool if saving happened
     */
    final public function persistWorkingState(int $active_id, $pass, bool $obligationsEnabled = false, bool $authorized = true): bool
    {
        if (!$this instanceof QuestionPartiallySaveable && !$this->validateSolutionSubmit()) {
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
    final public function persistPreviewState(ilAssQuestionPreviewSession $preview_session): bool
    {
        $this->savePreviewData($preview_session);
        return $this->validateSolutionSubmit();
    }

    public function validateSolutionSubmit(): bool
    {
        return true;
    }

    protected function savePreviewData(ilAssQuestionPreviewSession $preview_session): void
    {
        $preview_session->setParticipantsSolution($this->getSolutionSubmit());
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

        return $this->question_files->buildImagePath($question_id, $object_id);
    }

    public function getSuggestedSolutionPathWeb(): string
    {
        $webdir = ilFileUtils::removeTrailingPathSeparators(CLIENT_WEB_DIR)
            . "/data/assessment/{$this->obj_id}/{$this->id}/solution/";
        return str_replace(
            ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH . '/public'),
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
            $webdir = ilFileUtils::removeTrailingPathSeparators(CLIENT_WEB_DIR)
                . "/assessment/{$this->obj_id}/{$this->id}/images/";
            return str_replace(
                ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH . '/public'),
                ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
                $webdir
            );
        }
        return $this->export_image_path;
    }

    public function getTestOutputSolutions(int $activeId, int $pass): array
    {
        if ($this->getTestPresentationConfig()->isSolutionInitiallyPrefilled()) {
            return $this->getSolutionValues($activeId, $pass, true);
        }
        return $this->getUserSolutionPreferingIntermediate($activeId, $pass);
    }


    public function getUserSolutionPreferingIntermediate(
        int $active_id,
        ?int $pass = null
    ): array {
        $solution = $this->getSolutionValues($active_id, $pass, false);

        if (!count($solution)) {
            $solution = $this->getSolutionValues($active_id, $pass, true);
        }

        return $solution;
    }

    /**
     * Loads solutions of a given user from the database an returns it
     */
    public function getSolutionValues(
        int $active_id,
        ?int $pass = null,
        bool $authorized = true
    ): array {
        if ($pass === null && is_numeric($active_id)) {
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
                ['integer', 'integer', 'integer', 'integer', 'integer'],
                [(int) $active_id, $this->getId(), $pass, $this->getStep(), (int) $authorized]
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
                ['integer', 'integer', 'integer', 'integer'],
                [(int) $active_id, $this->getId(), $pass, (int) $authorized]
            );
        }

        $values = [];

        while ($row = $this->db->fetchAssoc($result)) {
            $values[] = $row;
        }

        return $values;
    }

    public function deleteAnswers(int $question_id): void
    {
        $answer_table_name = $this->getAnswerTableName();

        if (!is_array($answer_table_name)) {
            $answer_table_name = [$answer_table_name];
        }

        foreach ($answer_table_name as $table) {
            if (strlen($table)) {
                $this->db->manipulateF(
                    "DELETE FROM $table WHERE question_fi = %s",
                    ['integer'],
                    [$question_id]
                );
            }
        }
    }

    public function deleteAdditionalTableData(int $question_id): void
    {
        $additional_table_name = $this->getAdditionalTableName();

        if (!is_array($additional_table_name)) {
            $additional_table_name = [$additional_table_name];
        }

        foreach ($additional_table_name as $table) {
            if (strlen($table)) {
                $this->db->manipulateF(
                    "DELETE FROM $table WHERE question_fi = %s",
                    ['integer'],
                    [$question_id]
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
            ['integer'],
            [$question_id]
        );
        if ($this->db->numRows($result) !== 1) {
            return;
        }

        $row = $this->db->fetchAssoc($result);
        $obj_id = $row["obj_fi"];

        try {
            $this->deletePageOfQuestion($question_id);
        } catch (Exception $e) {
            $this->log->root()->error("EXCEPTION: Could not delete page of question $question_id: $e");
            return;
        }

        $affectedRows = $this->db->manipulateF(
            "DELETE FROM qpl_questions WHERE question_id = %s",
            ['integer'],
            [$question_id]
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
            $this->log->root()->error("EXCEPTION: Could not delete additional table data of question $question_id: $e");
            return;
        }

        try {
            // delete the question in the tst_test_question table (list of test questions)
            $affectedRows = $this->db->manipulateF(
                "DELETE FROM tst_test_question WHERE question_fi = %s",
                ['integer'],
                [$question_id]
            );
        } catch (Exception $e) {
            $this->log->root()->error("EXCEPTION: Could not delete delete question $question_id from a test: $e");
            return;
        }

        try {
            $this->getSuggestedSolutionsRepo()->deleteForQuestion($question_id);
        } catch (Exception $e) {
            $this->log->root()->error("EXCEPTION: Could not delete suggested solutions of question $question_id: $e");
            return;
        }

        $directory = CLIENT_WEB_DIR . "/assessment/" . $obj_id . "/$question_id";
        try {
            if (is_dir($directory)) {
                ilFileUtils::delDir($directory);
            }
        } catch (Exception $e) {
            $this->log->root()->error("EXCEPTION: Could not delete question file directory $directory of question $question_id: $e");
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
            $this->log->root()->error("EXCEPTION: Error deleting the media objects of question $question_id: $e");
            return;
        }
        ilAssQuestionHintTracking::deleteRequestsByQuestionIds([$question_id]);
        ilAssQuestionHintList::deleteHintsByQuestionIds([$question_id]);
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
        $this->deleteComments();

        try {
            ilObjQuestionPool::_updateQuestionCount($this->getObjId());
        } catch (Exception $e) {
            $this->log->root()->error("EXCEPTION: Error updating the question pool question count of question pool " . $this->getObjId() . " when deleting question $question_id: $e");
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
            ['integer','integer'],
            [$this->id, $this->id]
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

    public function cloneXHTMLMediaObjectsOfQuestion(int $source_question_id): void
    {
        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $source_question_id);
        foreach ($mobs as $mob) {
            ilObjMediaObject::_saveUsage($mob, "qpl:html", $this->getId());
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

    public function clonePageOfQuestion(int $a_q_id): void
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

    protected static $imageSourceFixReplaceMap = [
        'ok.svg' => 'ok.png',
        'not_ok.svg' => 'not_ok.png',
        'object/checkbox_checked.svg' => 'checkbox_checked.png',
        'object/checkbox_unchecked.svg' => 'checkbox_unchecked.png',
        'object/radiobutton_checked.svg' => 'radiobutton_checked.png',
        'object/radiobutton_unchecked.svg' => 'radiobutton_unchecked.png'
    ];

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
        $this->suggested_solutions = [];
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
        $complete = '0';
        $obj_id = $this->getObjId();
        if ($obj_id <= 0
            && $this->questionpool_request->hasRefId()) {
            $obj_id = $this->questionpool_request->getRefId();
        }

        if ($obj_id <= 0) {
            $obj_id = $this->questionpool_request->int('sel_qpl');
        }

        if ($obj_id <= 0) {
            return $this->getId();
        }

        $tstamp = time();
        if ($a_create_page) {
            $tstamp = 0;
        }

        $next_id = $this->db->nextId('qpl_questions');
        $this->db->insert("qpl_questions", [
            "question_id" => ["integer", $next_id],
            "question_type_fi" => ["integer", $this->getQuestionTypeID()],
            "obj_fi" => ["integer", $obj_id],
            "title" => ["text", ''],
            "description" => ["text", ''],
            "author" => ["text", $this->getAuthor()],
            "owner" => ["integer", $this->current_user->getId()],
            "question_text" => ["clob", ''],
            "points" => ["float", "0.0"],
            "nr_of_tries" => ["integer", $this->getDefaultNrOfTries()], // #10771
            "complete" => ["text", $complete],
            "created" => ["integer", time()],
            "original_id" => ["integer", null],
            "tstamp" => ["integer", $tstamp],
            "external_id" => ["text", $this->getExternalId()],
            'add_cont_edit_mode' => ['text', $this->getAdditionalContentEditingMode()]
        ]);
        $this->setId($next_id);

        if ($a_create_page) {
            // create page object of question
            $this->createPageObject();
        }

        return $this->getId();
    }

    public function saveQuestionDataToDb(?int $original_id = null): void
    {
        if ($this->getId() === -1) {
            $next_id = $this->db->nextId('qpl_questions');
            $this->db->insert("qpl_questions", [
                "question_id" => ["integer", $next_id],
                "question_type_fi" => ["integer", $this->getQuestionTypeID()],
                "obj_fi" => ["integer", $this->getObjId()],
                "title" => ["text", $this->getTitle()],
                "description" => ["text", $this->getComment()],
                "author" => ["text", $this->getAuthor()],
                "owner" => ["integer", $this->getOwner()],
                "question_text" => ["clob", ilRTE::_replaceMediaObjectImageSrc($this->getQuestion(), 0)],
                "points" => ["float", $this->getMaximumPoints()],
                "nr_of_tries" => ["integer", $this->getNrOfTries()],
                "created" => ["integer", time()],
                "original_id" => ["integer", $original_id],
                "tstamp" => ["integer", time()],
                "external_id" => ["text", $this->getExternalId()],
                'add_cont_edit_mode' => ['text', $this->getAdditionalContentEditingMode()]
            ]);
            $this->setId($next_id);
            // create page object of question
            $this->createPageObject();
            return;
        }

        // Vorhandenen Datensatz aktualisieren
        $this->db->update("qpl_questions", [
            "obj_fi" => ["integer", $this->getObjId()],
            "title" => ["text", $this->getTitle()],
            "description" => ["text", $this->getComment()],
            "author" => ["text", $this->getAuthor()],
            "question_text" => ["clob", ilRTE::_replaceMediaObjectImageSrc($this->getQuestion(), 0)],
            "points" => ["float", $this->getMaximumPoints()],
            "nr_of_tries" => ["integer", $this->getNrOfTries()],
            "tstamp" => ["integer", time()],
            'complete' => ['integer', $this->isComplete()],
            "external_id" => ["text", $this->getExternalId()]
        ], [
        "question_id" => ["integer", $this->getId()]
        ]);
    }

    public function duplicate(
        bool $for_test = true,
        string $title = '',
        string $author = '',
        int $owner = -1,
        $test_obj_id = null
    ): int {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return -1;
        }

        $clone = clone $this;
        $clone->id = -1;

        if ((int) $test_obj_id > 0) {
            $clone->setObjId($test_obj_id);
        }

        if ($title) {
            $clone->setTitle($title);
        }
        if ($author) {
            $clone->setAuthor($author);
        }
        if ($owner) {
            $clone->setOwner($owner);
        }
        if ($for_test) {
            $clone->saveToDb($this->id);
        } else {
            $clone->saveToDb();
        }

        $clone->clonePageOfQuestion($this->getId());
        $clone->cloneXHTMLMediaObjectsOfQuestion($this->getId());

        $clone = $this->cloneQuestionTypeSpecificProperties($clone);

        $clone->onDuplicate($this->getObjId(), $this->getId(), $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    final public function copyObject(
        int $target_parent_id,
        string $title = ''
    ): int {
        if ($this->getId() <= 0) {
            throw new RuntimeException('The question has not been saved. It cannot be duplicated');
        }
        // duplicate the question in database
        $clone = clone $this;
        $original_id = $this->questionrepository->getForQuestionId($this->id)->getOriginalId();
        $clone->id = -1;
        $source_parent_id = $this->getObjId();
        $clone->setObjId($target_parent_id);
        if ($title) {
            $clone->setTitle($title);
        }
        $clone->saveToDb();
        $clone->clonePageOfQuestion($original_id);
        $clone->cloneXHTMLMediaObjectsOfQuestion($original_id);
        $clone = $this->cloneQuestionTypeSpecificProperties($clone);

        $clone->onCopy($source_parent_id, $original_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    final public function createNewOriginalFromThisDuplicate(
        int $target_parent_id,
        string $target_question_title = ''
    ): int {
        if ($this->getId() <= 0) {
            throw new RuntimeException('The question has not been saved. It cannot be duplicated');
        }

        $source_question_id = $this->id;
        $source_parent_id = $this->getObjId();

        // duplicate the question in database
        $clone = clone $this;
        $clone->id = -1;

        $clone->setObjId($target_parent_id);

        if ($target_question_title) {
            $clone->setTitle($target_question_title);
        }

        $clone->saveToDb();
        $clone->clonePageOfQuestion($source_question_id);
        $clone->cloneXHTMLMediaObjectsOfQuestion($source_question_id);

        $clone = $this->cloneQuestionTypeSpecificProperties($clone);

        $clone->onCopy($source_parent_id, $source_question_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    protected function cloneQuestionTypeSpecificProperties(
        self $target
    ): self {
        return $target;
    }

    public function saveToDb(?int $original_id = null): void
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
                'question_id' => ['integer', $this->getId()]
            ]
        );

        ilObjQuestionPool::_updateQuestionCount($this->getObjId());
    }

    protected function removeAllImageFiles(string $image_target_path): void
    {
        $target = opendir($image_target_path);
        while($target_file = readdir($target)) {
            if ($target_file === '.' || $target_file === '..') {
                continue;
            }
            copy(
                $image_target_path . DIRECTORY_SEPARATOR . $target_file,
                $image_target_path . DIRECTORY_SEPARATOR . $target_file
            );
        }
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

    protected function onDuplicate(
        int $original_parent_id,
        int $original_question_id,
        int $duplicate_parent_id,
        int $duplicate_question_id
    ): void {
        $this->copySuggestedSolutions($duplicate_question_id);
        $this->cloneSuggestedSolutionFiles($original_parent_id, $original_question_id);
        $this->feedbackOBJ->duplicateFeedback($original_question_id, $duplicate_question_id);
        $this->duplicateQuestionHints($original_question_id, $duplicate_question_id);
        $this->duplicateSkillAssignments($original_parent_id, $original_question_id, $duplicate_parent_id, $duplicate_question_id);
        $this->duplicateComments($original_parent_id, $original_question_id, $duplicate_parent_id, $duplicate_question_id);
    }

    protected function afterSyncWithOriginal(
        int $original_question_id,
        int $clone_question_id,
        int $original_parent_id,
        int $clone_parent_id
    ): void {
        $this->feedbackOBJ->cloneFeedback($original_question_id, $clone_question_id);
    }

    protected function onCopy(int $sourceParentId, int $sourceQuestionId, int $targetParentId, int $targetQuestionId): void
    {
        $this->copySuggestedSolutions($targetQuestionId);
        $this->duplicateSuggestedSolutionFiles($sourceParentId, $sourceQuestionId);
        $this->feedbackOBJ->duplicateFeedback($sourceQuestionId, $targetQuestionId);
        $this->duplicateQuestionHints($sourceQuestionId, $targetQuestionId);
        $this->duplicateSkillAssignments($sourceParentId, $sourceQuestionId, $targetParentId, $targetQuestionId);
        $this->duplicateComments($sourceParentId, $sourceQuestionId, $targetParentId, $targetQuestionId);
    }

    protected function duplicateComments(
        int $parent_source_id,
        int $source_id,
        int $parent_target_id,
        int $target_id
    ): void {
        $manager = $this->getNotesManager();
        $data_service = $this->getNotesDataService();
        $notes = $manager->getNotesForRepositoryObjIds([$parent_source_id], Note::PUBLIC);
        $notes = array_filter(
            $notes,
            fn($n) => $n->getContext()->getSubObjId() === $source_id
        );

        foreach($notes as $note) {
            $new_context = $data_service->context(
                $parent_target_id,
                $target_id,
                $note->getContext()->getType()
            );
            $new_note = $data_service->note(
                -1,
                $new_context,
                $note->getText(),
                $note->getAuthor(),
                $note->getType(),
                $note->getCreationDate(),
                $note->getUpdateDate(),
                $note->getRecipient()
            );
            $manager->createNote($new_note, [], true);
        }
    }

    protected function deleteComments(): void
    {
        $repo = $this->getNotesRepo();
        $manager = $this->getNotesManager();
        $source_id = $this->getId();
        $notes = $manager->getNotesForRepositoryObjIds([$this->getObjId()], Note::PUBLIC);
        $notes = array_filter(
            $notes,
            fn($n) => $n->getContext()->getSubObjId() === $source_id
        );
        foreach($notes as $note) {
            $repo->deleteNote($note->getId());
        }
    }

    protected function getNotesManager(): NotesManager
    {
        $service = new NotesService($this->dic);
        return $service->internal()->domain()->notes();
    }

    protected function getNotesDataService(): NotesInternalDataService
    {
        $service = new NotesService($this->dic);
        return $service->internal()->data();
    }

    protected function getNotesRepo(): NotesRepo
    {
        $service = new NotesService($this->dic);
        return $service->internal()->repo()->note();
    }

    public function deleteSuggestedSolutions(): void
    {
        $this->getSuggestedSolutionsRepo()->deleteForQuestion($this->getId());
        ilInternalLink::_deleteAllLinksOfSource("qst", $this->getId());
        ilFileUtils::delDir($this->getSuggestedSolutionPath());
        $this->suggested_solutions = [];
    }


    public function getSuggestedSolution(int $subquestion_index = 0): ?SuggestedSolution
    {
        if (array_key_exists($subquestion_index, $this->suggested_solutions)) {
            return $this->suggested_solutions[$subquestion_index];
        }
        return null;
    }

    protected function cloneSuggestedSolutions(
        int $source_question_id,
        int $target_question_id
    ): void {
        $this->getSuggestedSolutionsRepo()->clone($source_question_id, $target_question_id);
        $this->cloneSuggestedSolutionFiles($source_question_id, $target_question_id);
    }

    /**
    * Duplicates the files of a suggested solution if the question is duplicated
    */
    protected function duplicateSuggestedSolutionFiles(int $parent_id, int $question_id): void
    {
        foreach ($this->suggested_solutions as $solution) {
            if (!$solution->isOfTypeFile()
                || $solution->getFilename() === '') {
                continue;
            }

            $filepath = $this->getSuggestedSolutionPath();
            $filepath_original = str_replace(
                "/{$this->obj_id}/{$this->id}/solution",
                "/{$parent_id}/{$question_id}/solution",
                $filepath
            );
            if (!file_exists($filepath)) {
                ilFileUtils::makeDirParents($filepath);
            }
            if (!is_file($filepath_original . $solution->getFilename())
                || !copy($filepath_original . $solution->getFilename(), $filepath . $solution->getFilename())) {
                $this->log->root()->error("File could not be duplicated!!!!");
                $this->log->root()->error("object: " . print_r($this, true));
            }
        }
    }

    protected function cloneSuggestedSolutionFiles(
        int $source_question_id,
        int $target_question_id
    ): void {
        $filepath_target = $this->getSuggestedSolutionPath();
        $filepath_original = str_replace("/$target_question_id/solution", "/$source_question_id/solution", $filepath_target);
        ilFileUtils::delDir($filepath_original);
        foreach ($this->suggested_solutions as $solution) {
            if (!$solution->isOfTypeFile()
                || $solution->getFilename() === '') {
                continue;
            }

            if (!file_exists($filepath_original)) {
                ilFileUtils::makeDirParents($filepath_original);
            }

            if (!is_file($filepath_original . $solution->getFilename())
                || copy($filepath_target . $solution->getFilename(), $filepath_target . $solution->getFilename())) {
                $this->log->root()->error("File could not be duplicated!!!!");
                $this->log->root()->error("object: " . print_r($this, true));
            }
        }
    }

    protected function copySuggestedSolutions(int $target_question_id): void
    {
        $update = [];
        foreach($this->getSuggestedSolutions() as $index => $solution) {
            $solution = $solution->withQuestionId($target_question_id);
            $update[] = $solution;
        }
        $this->getSuggestedSolutionsRepo()->update($update);
    }

    public function resolveInternalLink(string $internal_link): string
    {
        if (preg_match("/il_(\d+)_(\w+)_(\d+)/", $internal_link, $matches) === false) {
            return $internal_link;
        }
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
            return (string) $resolved_link;
        }
        return $internal_link;
    }


    //TODO: move this to import or suggested solutions repo.
    //use in LearningModule and Survey as well ;(
    public function resolveSuggestedSolutionLinks(): void
    {
        $resolvedlinks = 0;
        $result_pre = $this->db->queryF(
            "SELECT internal_link, suggested_solution_id FROM qpl_sol_sug WHERE question_fi = %s",
            ['integer'],
            [$this->getId()]
        );
        if ($this->db->numRows($result_pre) < 1) {
            return;
        }

        while ($row = $this->db->fetchAssoc($result_pre)) {
            $internal_link = $row["internal_link"];
            $resolved_link = $this->resolveInternalLink($internal_link);
            if ($internal_link === $resolved_link) {
                continue;
            }
            // internal link was resolved successfully
            $this->db->manipulateF(
                "UPDATE qpl_sol_sug SET internal_link = %s WHERE suggested_solution_id = %s",
                ['text','integer'],
                [$resolved_link, $row["suggested_solution_id"]]
            );
            $resolvedlinks++;
        }
        if ($resolvedlinks === 0) {
            return;
        }

        ilInternalLink::_deleteAllLinksOfSource("qst", $this->getId());

        $result_post = $this->db->queryF(
            "SELECT internal_link FROM qpl_sol_sug WHERE question_fi = %s",
            ['integer'],
            [$this->getId()]
        );
        if ($this->db->numRows($result_post) < 1) {
            return;
        }

        while ($row = $this->db->fetchAssoc($result_post)) {
            if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $row["internal_link"], $matches)) {
                ilInternalLink::_saveLink("qst", $this->getId(), $matches[2], $matches[3], $matches[1]);
            }
        }
    }

    public function getInternalLinkHref(string $target): string
    {
        $linktypes = [
            "lm" => "LearningModule",
            "pg" => "PageObject",
            "st" => "StructureObject",
            "git" => "GlossaryItem",
            "mob" => "MediaObject"
        ];
        $href = "";
        if (preg_match("/il__(\w+)_(\d+)/", $target, $matches)) {
            $type = $matches[1];
            $target_id = $matches[2];
            switch ($linktypes[$matches[1]]) {
                case "MediaObject":
                    $href = "./ilias.php?baseClass=ilLMPresentationGUI&obj_type=" . $linktypes[$type]
                        . "&cmd=media&ref_id=" . $this->questionpool_request->getRefId()
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
        if ($this->getOriginalId() === null) {
            return;
        }

        $original_parent_id = self::lookupParentObjId($this->getOriginalId());

        if ($original_parent_id === null) {
            return;
        }

        $this->cloneSuggestedSolutions($this->getId(), $this->getOriginalId());
        $original = clone $this;
        // Now we become the original
        $original->setId($this->getOriginalId());
        $original->setOriginalId(null);
        $original->setObjId($original_parent_id);

        $original->saveToDb();

        $original->deletePageOfQuestion($this->getOriginalId());
        $original->createPageObject();
        $original->clonePageOfQuestion($this->getId());
        $original = $this->cloneQuestionTypeSpecificProperties($original);
        $this->cloneXHTMLMediaObjectsOfQuestion($original->getId());
        $this->afterSyncWithOriginal($this->getOriginalId(), $this->getId(), $this->getObjId(), $original_parent_id);
        $this->cloneHints($this->id, $this->original_id);
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
        $questionrepository = QuestionPoolDIC::dic()['question.general_properties.repository'];
        $question_type = $questionrepository->getForQuestionId($question_id)->getClassName();
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
        return $this->points;
    }

    public function setPoints(float $points): void
    {
        $this->points = $points;
    }

    public function getSolutionMaxPass(int $active_id): ?int
    {
        return self::_getSolutionMaxPass($this->getId(), $active_id);
    }

    /**
    * Returns the maximum pass a users question solution
    */
    public static function _getSolutionMaxPass(int $question_id, int $active_id): ?int
    {
        // the following code was the old solution which added the non answered
        // questions of a pass from the answered questions of the previous pass
        // with the above solution, only the answered questions of the last pass are counted
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT MAX(pass) maxpass FROM tst_test_result WHERE active_fi = %s AND question_fi = %s",
            ['integer','integer'],
            [$active_id, $question_id]
        );
        if ($result->numRows() === 1) {
            $row = $ilDB->fetchAssoc($result);
            return $row["maxpass"];
        }

        return null;
    }

    public function isWriteable(): bool
    {
        return ilObjQuestionPool::_isWriteable($this->getObjId(), $this->getCurrentUser()->getId());
    }

    public function deductHintPointsFromReachedPoints(ilAssQuestionPreviewSession $preview_session, $reached_points): ?float
    {
        $hint_tracking = new ilAssQuestionPreviewHintTracking($this->db, $preview_session);
        $requests_statistic_data = $hint_tracking->getRequestStatisticData();
        return $reached_points - $requests_statistic_data->getRequestsPoints();
    }

    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $preview_session)
    {
        $reached_points = $this->deductHintPointsFromReachedPoints(
            $preview_session,
            $this->calculateReachedPointsForSolution($preview_session->getParticipantsSolution())
        );

        return $this->ensureNonNegativePoints($reached_points);
    }

    protected function ensureNonNegativePoints(float $points): float
    {
        return $points > 0.0 ? $points : 0.0;
    }

    public function isPreviewSolutionCorrect(ilAssQuestionPreviewSession $preview_session): bool
    {
        $reached_points = $this->calculateReachedPointsFromPreviewSession($preview_session);

        return !($reached_points < $this->getMaximumPoints());
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
            if ($pass === null) {
                $pass = assQuestion::_getSolutionMaxPass($question_id, $active_id);
            }

            $rowsnum = 0;
            $old_points = 0;
            $result = $ilDB->queryF(
                "SELECT points FROM tst_test_result WHERE active_fi = %s AND question_fi = %s AND pass = %s",
                ['integer','integer','integer'],
                [$active_id, $question_id, $pass]
            );
            $manual = ($manualscoring) ? 1 : 0;
            $rowsnum = $result->numRows();
            if ($rowsnum > 0) {
                $row = $ilDB->fetchAssoc($result);
                $old_points = $row["points"];
                if ($old_points !== $points) {
                    $affectedRows = $ilDB->manipulateF(
                        "UPDATE tst_test_result SET points = %s, manual = %s, tstamp = %s WHERE active_fi = %s AND question_fi = %s AND pass = %s",
                        ['float', 'integer', 'integer', 'integer', 'integer', 'integer'],
                        [$points, $manual, time(), $active_id, $question_id, $pass]
                    );
                }
            } else {
                $next_id = $ilDB->nextId('tst_test_result');
                $affectedRows = $ilDB->manipulateF(
                    "INSERT INTO tst_test_result (test_result_id, active_fi, question_fi, points, pass, manual, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                    ['integer', 'integer','integer', 'float', 'integer', 'integer','integer'],
                    [$next_id, $active_id, $question_id, $points, $pass, $manual, time()]
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
        return $this->purifyAndPrepareTextAreaOutput($this->question);
    }

    protected function purifyAndPrepareTextAreaOutput(string $content): string
    {
        $purified_content = $this->getHtmlQuestionContentPurifier()->purify($content);
        if ($this->isAdditionalContentEditingModePageObject()
            || !(new ilSetting('advanced_editing'))->get('advanced_editing_javascript_editor') === 'tinymce') {
            $purified_content = nl2br($purified_content);
        }
        return ilLegacyFormElementsUtil::prepareTextareaOutput(
            $purified_content,
            true,
            true
        );
    }

    public function setQuestion(string $question = ""): void
    {
        $this->question = $question;
    }

    public function getQuestionTypeID(): int
    {
        $result = $this->db->queryF(
            "SELECT question_type_id FROM qpl_qst_type WHERE type_tag = %s",
            ['text'],
            [$this->getQuestionType()]
        );
        if ($this->db->numRows($result) == 1) {
            $row = $this->db->fetchAssoc($result);
            return (int) $row["question_type_id"];
        }
        return 0;
    }

    public function cloneHints(
        int $source_question_id,
        int $target_question_id
    ): void {
        // delete hints of the original
        $this->db->manipulateF(
            "DELETE FROM qpl_hints WHERE qht_question_fi = %s",
            ['integer'],
            [$target_question_id]
        );

        // get hints of the actual question
        $result = $this->db->queryF(
            "SELECT * FROM qpl_hints WHERE qht_question_fi = %s",
            ['integer'],
            [$source_question_id]
        );

        // save hints to the original
        if ($this->db->numRows($result) < 1) {
            return;
        }

        while ($row = $this->db->fetchAssoc($result)) {
            $next_id = $this->db->nextId('qpl_hints');
            $this->db->insert(
                'qpl_hints',
                [
                    'qht_hint_id' => ['integer', $next_id],
                    'qht_question_fi' => ['integer', $target_question_id],
                    'qht_hint_index' => ['integer', $row["qht_hint_index"]],
                    'qht_hint_points' => ['float', $row["qht_hint_points"]],
                    'qht_hint_text' => ['text', $row["qht_hint_text"]],
                ]
            );
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
            ["integer"],
            [$this->getId()]
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
                ["integer"],
                [$question_id]
            );
            while ($row = $this->db->fetchAssoc($result)) {
                $instances[$row['obj_fi']] = ilObject::_lookupTitle($row['obj_fi']);
            }
            // check random tests
            $result = $this->db->queryF(
                "SELECT tst_tests.obj_fi FROM tst_tests, tst_test_rnd_qst, tst_active WHERE tst_test_rnd_qst.active_fi = tst_active.active_id AND tst_test_rnd_qst.question_fi = %s AND tst_tests.test_id = tst_active.test_fi",
                ["integer"],
                [$question_id]
            );
            while ($row = $this->db->fetchAssoc($result)) {
                $instances[$row['obj_fi']] = ilObject::_lookupTitle($row['obj_fi']);
            }
        }
        foreach ($instances as $key => $value) {
            $instances[$key] = ["obj_id" => $key, "title" => $value, "author" => ilObjTest::_lookupAuthor($key), "refs" => ilObject::_getAllReferences($key)];
        }
        return $instances;
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
            ['integer'],
            [$active_id]
        );
        if ($this->db->numRows($result)) {
            $row = $this->db->fetchAssoc($result);
            return ["user_id" => $row["user_fi"], "test_id" => $row["test_fi"]];
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



    public static function instantiateQuestionGUI(int $question_id): ?assQuestionGUI
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $ilLog = $DIC['ilLog'];

        if ($question_id <= 0) {
            $ilLog->warning('Instantiate question called without question id. (instantiateQuestionGUI@assQuestion)');
            throw new InvalidArgumentException('Instantiate question called without question id. (instantiateQuestionGUI@assQuestion)');
        }

        $questionrepository = QuestionPoolDIC::dic()['question.general_properties.repository'];
        $question_type = $questionrepository->getForQuestionId($question_id)?->getClassName();

        if ($question_type === null) {
            return null;
        }

        $question_type_gui = $question_type . 'GUI';
        $question_gui = new $question_type_gui($question_id);

        $feedback_object_classname = self::getFeedbackClassNameByQuestionType($question_type);
        $question = $question_gui->getObject();
        $question->feedbackOBJ = new $feedback_object_classname($question, $ilCtrl, $ilDB, $lng);

        $assSettings = new ilSetting('assessment');
        $processLockerFactory = new ilAssQuestionProcessLockerFactory($assSettings, $ilDB);
        $processLockerFactory->setQuestionId($question_gui->getObject()->getId());
        $processLockerFactory->setUserId($ilUser->getId());
        $question->setProcessLocker($processLockerFactory->getLocker());
        $question_gui->setObject($question);

        return $question_gui;
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
            ['integer', 'integer'],
            [$question_id, $test_id]
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

    public static function lookupParentObjId(int $question_id): ?int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT obj_fi FROM qpl_questions WHERE question_id = %s";

        $res = $ilDB->queryF($query, ['integer'], [$question_id]);
        $row = $ilDB->fetchAssoc($res);

        return $row['obj_fi'] ?? null;
    }

    protected function duplicateQuestionHints(int $original_question_id, int $duplicate_question_id): void
    {
        $hintIds = ilAssQuestionHintList::duplicateListForQuestion($original_question_id, $duplicate_question_id);

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
        $numExistingSolutionRecords = assQuestion::getNumExistingSolutionRecords($active_id, $pass, $this->getId());
        return $numExistingSolutionRecords > 0;
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
            ['integer','integer','integer'],
            [$activeId, $questionId, $pass]
        );

        $row = $ilDB->fetchAssoc($res);

        return (int) $row['cnt'];
    }

    public function getAdditionalContentEditingMode(): string
    {
        return $this->additionalContentEditingMode;
    }

    public function setAdditionalContentEditingMode(string $additionalContentEditingMode): void
    {
        if (!in_array($additionalContentEditingMode, $this->getValidAdditionalContentEditingModes())) {
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
        return [
            self::ADDITIONAL_CONTENT_EDITING_MODE_RTE,
            self::ADDITIONAL_CONTENT_EDITING_MODE_IPE
        ];
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

    public function setLastChange(int $lastChange): void
    {
        $this->lastChange = $lastChange;
    }

    public function getLastChange(): ?int
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
                ['integer', 'integer', 'integer', 'integer', 'integer'],
                [$active_id, $this->getId(), $pass, $this->getStep(), (int) $authorized]
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
            ['integer', 'integer', 'integer', 'integer'],
            [$active_id, $this->getId(), $pass, (int) $authorized]
        );
    }

    protected function removeSolutionRecordById(int $solutionId): int
    {
        return $this->db->manipulateF(
            "DELETE FROM tst_solutions WHERE solution_id = %s",
            ['integer'],
            [$solutionId]
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
            ['integer'],
            [$solutionId]
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
                ['integer', 'integer', 'integer', 'integer', 'integer'],
                [$active_id, $this->getId(), $pass, $this->getStep(), (int) $authorized]
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
            ['integer', 'integer', 'integer', 'integer'],
            [$active_id, $this->getId(), $pass, (int) $authorized]
        );
    }

    // fau: testNav - add timestamp as parameter to saveCurrentSolution
    public function saveCurrentSolution(int $active_id, int $pass, $value1, $value2, bool $authorized = true, $tstamp = 0): int
    {
        $next_id = $this->db->nextId("tst_solutions");

        $fieldData = [
            "solution_id" => ["integer", $next_id],
            "active_fi" => ["integer", $active_id],
            "question_fi" => ["integer", $this->getId()],
            "value1" => ["clob", $value1],
            "value2" => ["clob", $value2],
            "pass" => ["integer", $pass],
            "tstamp" => ["integer", ((int) $tstamp > 0) ? (int) $tstamp : time()],
            'authorized' => ['integer', (int) $authorized]
        ];

        if ($this->getStep() !== null) {
            $fieldData['step'] = ["integer", $this->getStep()];
        }

        return $this->db->insert("tst_solutions", $fieldData);
    }
    // fau.

    public function updateCurrentSolution(int $solutionId, $value1, $value2, bool $authorized = true): int
    {
        $fieldData = [
            "value1" => ["clob", $value1],
            "value2" => ["clob", $value2],
            "tstamp" => ["integer", time()],
            'authorized' => ['integer', (int) $authorized]
        ];

        if ($this->getStep() !== null) {
            $fieldData['step'] = ["integer", $this->getStep()];
        }

        return $this->db->update("tst_solutions", $fieldData, [
            'solution_id' => ['integer', $solutionId]
        ]);
    }

    // fau: testNav - added parameter to keep the timestamp (default: false)
    public function updateCurrentSolutionsAuthorization(int $activeId, int $pass, bool $authorized, bool $keepTime = false): int
    {
        $fieldData = [
            'authorized' => ['integer', (int) $authorized]
        ];

        if (!$keepTime) {
            $fieldData['tstamp'] = ['integer', time()];
        }

        $whereData = [
            'question_fi' => ['integer', $this->getId()],
            'active_fi' => ['integer', $activeId],
            'pass' => ['integer', $pass]
        ];

        if ($this->getStep() !== null) {
            $whereData['step'] = ["integer", $this->getStep()];
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
        $types = ["integer", "integer", "integer", "integer"];
        $values = [$activeId, $this->getId(), $passIndex, (int) $authorized];
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

    // hey: prevPassSolutions - check for authorized solution
    public function intermediateSolutionExists(int $active_id, int $pass): bool
    {
        $solutionAvailability = $this->lookupForExistingSolutions($active_id, $pass);
        return (bool) $solutionAvailability['intermediate'];
    }

    public function authorizedSolutionExists(int $active_id, ?int $pass): bool
    {
        if ($pass === null) {
            return false;
        }
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
            ["integer", "integer", "integer"],
            [$active_id, $pass, $this->getId()]
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
        $return = [
            'authorized' => false,
            'intermediate' => false
        ];

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

        $result = $this->db->queryF($query, ['integer', 'integer', 'integer'], [$activeId, $this->getId(), $pass]);

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
        $this->db->manipulateF($query, ['integer'], [$this->getId()]);
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
            ['integer', 'integer', 'integer'],
            [$activeId, $this->getId(), $pass]
        );
    }

    public function resetUsersAnswer(int $activeId, int $pass): void
    {
        $this->removeExistingSolutions($activeId, $pass);
        $this->removeResultRecord($activeId, $pass);

        $test = new ilObjTest(
            $this->test_id,
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
            ['integer', 'integer', 'integer'],
            [$activeId, $this->getId(), $pass]
        );
    }

    public function fetchValuePairsFromIndexedValues(array $indexedValues): array
    {
        $valuePairs = [];

        foreach ($indexedValues as $value1 => $value2) {
            $valuePairs[] = ['value1' => $value1, 'value2' => $value2];
        }

        return $valuePairs;
    }

    public function fetchIndexedValuesFromValuePairs(array $value_pairs): array
    {
        $indexed_values = [];

        foreach ($value_pairs as $valuePair) {
            $indexed_values[$valuePair['value1']] = $valuePair['value2'];
        }

        return $indexed_values;
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
            ['integer', 'integer'],
            [time(), $this->getId()]
        );
    }

    public function getTestPresentationConfig(): ilTestQuestionConfig
    {
        if ($this->test_question_config === null) {
            $this->test_question_config = $this->buildTestPresentationConfig();
        }

        return $this->test_question_config;
    }

    protected function buildTestPresentationConfig(): ilTestQuestionConfig
    {
        return new ilTestQuestionConfig();
    }

    protected function getSuggestedSolutionsRepo(): SuggestedSolutionsDatabaseRepository
    {
        return $this->suggestedsolution_repo;
    }

    protected function loadSuggestedSolutions(): array
    {
        $question_id = $this->getId();
        return $this->getSuggestedSolutionsRepo()->selectFor($question_id);
    }

    /**
     * Trim non-printable characters from the beginning and end of a string.
     *
     * Note: The PHP trim() function is not fully Unicode-compatible and may not handle
     * non-printable characters effectively. As a result, it may not trim certain Unicode
     * characters, such as control characters, zero width characters or ideographic space as expected.
     *
     * This method provides a workaround for trimming non-printable characters until PHP 8.4,
     * where the mb_trim() function is introduced. Users are encouraged to migrate to mb_trim()
     * for proper Unicode and non-printable character handling.
     *
     * @param string $value The string to trim.
     * @return string The trimmed string.
     */
    public static function extendedTrim(string $value): string
    {
        return preg_replace(self::TRIM_PATTERN, '', $value);
    }

    public function hasWritableOriginalInQuestionPool(): bool
    {
        return !is_null($this->original_id)
            && $this->questionrepository->questionExistsInPool($this->original_id)
            && assQuestion::instantiateQuestion($this->original_id)->isWriteable();
    }

    public function answerToParticipantInteraction(
        AdditionalInformationGenerator $additional_info,
        int $test_ref_id,
        int $active_id,
        int $pass,
        string $source_ip,
        TestParticipantInteractionTypes $interaction_type
    ): TestParticipantInteraction {
        return new TestParticipantInteraction(
            $test_ref_id,
            $this->id,
            $this->current_user->getId(),
            $source_ip,
            $interaction_type,
            time(),
            $this->answerToLog($additional_info, $active_id, $pass)
        );
    }

    public function toQuestionAdministrationInteraction(
        AdditionalInformationGenerator $additional_info,
        int $test_ref_id,
        TestQuestionAdministrationInteractionTypes $interaction_type
    ): TestQuestionAdministrationInteraction {
        return new TestQuestionAdministrationInteraction(
            $test_ref_id,
            $this->id,
            $this->current_user->getId(),
            $interaction_type,
            time(),
            $this->toLog($additional_info)
        );
    }

    protected function answerToLog(
        AdditionalInformationGenerator $additional_info,
        int $active_id,
        int $pass
    ): array {
        return [
            AdditionalInformationGenerator::KEY_PASS => $pass,
            AdditionalInformationGenerator::KEY_REACHED_POINTS => $this->getReachedPoints($active_id, $pass),
            AdditionalInformationGenerator::KEY_PAX_ANSWER => $this->solutionValuesToLog(
                $additional_info,
                $this->getSolutionValues($active_id, $pass)
            )
        ];
    }

    public function getSolutionForTextOutput(
        int $active_id,
        int $pass
    ): array|string {
        return $this->solutionValuesToText(
            $this->getSolutionValues($active_id, $pass)
        );
    }

    public function getCorrectSolutionForTextOutput(
        int $active_id,
        int $pass
    ): array|string {
        return $this->solutionValuesToText(
            $this->getSolutionValues($active_id, $pass)
        );
    }

    public function getVariablesAsTextArray(
        int $active_id,
        int $pass
    ): array {
        return [];
    }
}
