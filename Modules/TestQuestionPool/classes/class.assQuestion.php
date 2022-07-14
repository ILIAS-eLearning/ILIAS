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

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

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
    const IMG_MIME_TYPE_JPG = 'image/jpeg';
    const IMG_MIME_TYPE_PNG = 'image/png';
    const IMG_MIME_TYPE_GIF = 'image/gif';

    protected static $allowedFileExtensionsByMimeType = array(
        self::IMG_MIME_TYPE_JPG => array('jpg', 'jpeg'),
        self::IMG_MIME_TYPE_PNG => array('png'),
        self::IMG_MIME_TYPE_GIF => array('gif')
    );

    protected static $allowedCharsetsByMimeType = array(
        self::IMG_MIME_TYPE_JPG => array('binary'),
        self::IMG_MIME_TYPE_PNG => array('binary'),
        self::IMG_MIME_TYPE_GIF => array('binary')
    );

    protected ILIAS\DI\LoggingServices $ilLog;

    protected int $id;

    protected string $title;

    protected string $comment;

    protected string $owner;

    protected string $author;

    /**
     * The question text
     */
    protected string $question;

    /**
     * The maximum available points for the question
     */
    protected float $points;

    /**
     * @var array estimated working time on a question (HH MM SS)
     */
    protected array $est_working_time;

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
    protected int $obj_id;

    /**
     * The reference to the ILIAS class
     *
     * @var object
     */
    protected $ilias;

    protected ilGlobalPageTemplate $tpl;

    protected ilLanguage $lng;

    protected ilDBInterface $db;

    /**
     * Contains the output type of a question
     */
    protected int $outputType = OUTPUT_JAVASCRIPT;

    /**
     * Array of suggested solutions
     *
     * @var array
     */
    protected array $suggested_solutions;

    protected ?int $original_id;

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

    /**
     * An external id of a question
     */
    protected string $external_id = '';

    const ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT = 'default';
    const ADDITIONAL_CONTENT_EDITING_MODE_PAGE_OBJECT = 'pageobject';

    private string $additionalContentEditingMode;

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

    protected static $allowedImageMaterialFileExtensionsByMimeType = array(
        'image/jpeg' => array('jpg', 'jpeg'),
        'image/png' => array('png'),
        'image/gif' => array('gif')
    );

    protected ilObjUser $current_user;

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
        $ilias = $DIC['ilias'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC->logger();

        $this->current_user = $DIC['ilUser'];
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->db = $ilDB;
        $this->ilLog = $ilLog;

        $this->title = $title;
        $this->comment = $comment;
        $this->setAuthor($author);
        $this->setOwner($owner);

        $this->setQuestion($question);

        $this->id = -1;
        $this->test_id = -1;
        $this->suggested_solutions = array();
        $this->shuffle = 1;
        $this->nr_of_tries = 0;
        $this->setEstimatedWorkingTime(0, 1, 0);
        $this->setExternalId('');

        $this->questionActionCmd = 'handleQuestionAction';
        $this->export_image_path = '';
        $this->shuffler = $DIC->refinery()->random()->dontShuffle();
        $this->lifecycle = ilAssQuestionLifecycle::getDraftInstance();
    }

    protected static $forcePassResultsUpdateEnabled = false;

    public static function setForcePassResultUpdateEnabled(bool $forcePassResultsUpdateEnabled) : void
    {
        self::$forcePassResultsUpdateEnabled = $forcePassResultsUpdateEnabled;
    }

    public static function isForcePassResultUpdateEnabled() : bool
    {
        return self::$forcePassResultsUpdateEnabled;
    }

    public static function isAllowedImageMimeType($mimeType) : bool
    {
        return (bool) count(self::getAllowedFileExtensionsForMimeType($mimeType));
    }

    public static function fetchMimeTypeIdentifier(string $contentType) : string
    {
        return current(explode(';', $contentType));
    }

    public static function getAllowedFileExtensionsForMimeType(string $mimeType) : array
    {
        foreach (self::$allowedFileExtensionsByMimeType as $allowedMimeType => $extensions) {
            $rexCharsets = implode('|', self::$allowedCharsetsByMimeType[$allowedMimeType]);
            $rexMimeType = preg_quote($allowedMimeType, '/');

            $rex = '/^' . $rexMimeType . '(;(\s)*charset=(' . $rexCharsets . '))*$/';

            if (!preg_match($rex, $mimeType)) {
                continue;
            }

            return $extensions;
        }

        return array();
    }

    public static function isAllowedImageFileExtension(string $mimeType, string $fileExtension) : bool
    {
        return in_array(strtolower($fileExtension), self::getAllowedFileExtensionsForMimeType($mimeType), true);
    }

    // hey: prevPassSolutions - question action actracted (heavy use in fileupload refactoring)

    private function generateExternalId(int $question_id) : string
    {
        if ($question_id > 0) {
            return 'il_' . IL_INST_ID . '_qst_' . $question_id;
        }
        return uniqid('', true);
    }

    protected function getQuestionAction() : string
    {
        if (!isset($_POST['cmd']) || !isset($_POST['cmd'][$this->questionActionCmd])) {
            return '';
        }

        if (!is_array($_POST['cmd'][$this->questionActionCmd]) || !count($_POST['cmd'][$this->questionActionCmd])) {
            return '';
        }

        return key($_POST['cmd'][$this->questionActionCmd]);
    }

    protected function isNonEmptyItemListPostSubmission(string $postSubmissionFieldname) : bool
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

    protected function ensureCurrentTestPass(int $active_id, int $pass) : int
    {
        if (is_int($pass) && $pass >= 0) {
            return $pass;
        }

        return $this->lookupCurrentTestPass($active_id, $pass);
    }

    /**
     * @deprecated Use ilObjTest::_getPass($active_id) instead
     * @removal ILIAS 9
     */
    protected function lookupCurrentTestPass(int $active_id, int $pass) : int
    {
        return \ilObjTest::_getPass($active_id);
    }

    /**
     * @refactor Move to ilObjTest or similar
     */
    protected function lookupTestId(int $active_id) : int
    {
        $result = $this->db->queryF(
            "SELECT test_fi FROM tst_active WHERE active_id = %s",
            array('integer'),
            array($active_id)
        );
        $test_id = -1;
        if ($this->db->numRows($result) > 0) {
            $row = $this->db->fetchAssoc($result);
            $test_id = (int) $row["test_fi"];
        }

        return $test_id;
    }

    protected function log(int $active_id, string $langVar) : void
    {
        if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            $message = $this->lng->txtlng('assessment', $langVar, ilObjAssessmentFolder::_getLogLanguage());
            assQuestion::logAction($message, $active_id, $this->getId());
        }
    }

    /**
     * @return array	all allowed file extensions for image material
     */
    public static function getAllowedImageMaterialFileExtensions() : array
    {
        $extensions = array();

        foreach (self::$allowedImageMaterialFileExtensionsByMimeType as $mimeType => $mimeExtensions) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $extensions = array_merge($extensions, $mimeExtensions);
        }
        return array_unique($extensions);
    }

    public function getShuffler() : Transformation
    {
        return $this->shuffler;
    }

    public function setShuffler(Transformation $shuffler) : void
    {
        $this->shuffler = $shuffler;
    }

    public function setProcessLocker(ilAssQuestionProcessLocker $processLocker) : void
    {
        $this->processLocker = $processLocker;
    }

    public function getProcessLocker() : ilAssQuestionProcessLocker
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
    public function fromXML($item, int $questionpool_id, ?int $tst_id, $tst_object, int $question_counter, array $import_mapping) : void
    {
        $classname = $this->getQuestionType() . "Import";
        $import = new $classname($this);
        $import->fromXML($item, $questionpool_id, $tst_id, $tst_object, $question_counter, $import_mapping);
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
    ) : string {
        $classname = $this->getQuestionType() . "Export";
        $export = new $classname($this);
        return $export->toXML($a_include_header, $a_include_binary, $a_shuffle, $test_output, $force_image_references);
    }

    /**
    * Returns true, if a question is complete for use
    *
    * @return boolean True, if the question is complete for use, otherwise false
    */
    public function isComplete() : bool
    {
        return false;
    }

    /**
    * Returns TRUE if the question title exists in a question pool in the database
    */
    public function questionTitleExists(int $questionpool_id, string $title) : bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT * FROM qpl_questions WHERE obj_fi = %s AND title = %s",
            array('integer','text'),
            array($questionpool_id, $title)
        );
        return ($result->numRows() > 0) ? true : false;
    }

    public function setTitle(string $title = "") : void
    {
        $this->title = $title;
    }

    public function setId(int $id = -1) : void
    {
        $this->id = $id;
    }

    public function setTestId(int $id = -1) : void
    {
        $this->test_id = $id;
    }

    public function setComment(string $comment = "") : void
    {
        $this->comment = $comment;
    }

    public function setOutputType(int $outputType = OUTPUT_HTML) : void
    {
        $this->outputType = $outputType;
    }

    public function setShuffle(?bool $shuffle = true) : void
    {
        $this->shuffle = $shuffle ?? false;
    }

    public function setEstimatedWorkingTime(int $hour = 0, int $min = 0, int $sec = 0) : void
    {
        $this->est_working_time = array("h" => $hour, "m" => $min, "s" => $sec);
    }

    /**
     * @param string $datetime "hh:mm:ss"
     */
    public function setEstimatedWorkingTimeFromDurationString(string $durationString) : void
    {
        $this->est_working_time = array(
            'h' => (int) substr($durationString, 0, 2),
            'm' => (int) substr($durationString, 3, 2),
            's' => (int) substr($durationString, 6, 2)
        );
    }

    public function setAuthor(string $author = "") : void
    {
        if (!$author) {
            $author = $this->current_user->getFullname();
        }
        $this->author = $author;
    }

    public function setOwner(int $owner = -1) : void
    {
        $this->owner = $owner;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getTitleFilenameCompliant() : string
    {
        return ilFileUtils::getASCIIFilename($this->getTitle());
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getShuffle() : bool
    {
        return $this->shuffle;
    }

    public function getTestId() : int
    {
        return $this->test_id;
    }

    public function getComment() : string
    {
        return $this->comment;
    }

    public function getOutputType() : int
    {
        return $this->outputType;
    }

    public function supportsJavascriptOutput() : bool
    {
        return false;
    }

    public function supportsNonJsOutput() : bool
    {
        return true;
    }

    public function requiresJsSwitch() : bool
    {
        return $this->supportsJavascriptOutput() && $this->supportsNonJsOutput();
    }

    /**
    * @return array Estimated Working Time of a question as array("h" => 0, "m" => 0, "s" => 0)
    */
    public function getEstimatedWorkingTime() : array
    {
        if (!$this->est_working_time) {
            $this->est_working_time = array("h" => 0, "m" => 0, "s" => 0);
        }
        return $this->est_working_time;
    }

    public function getAuthor() : string
    {
        return $this->author;
    }

    public function getOwner() : int
    {
        return $this->owner;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id = 0) : void
    {
        $this->obj_id = $obj_id;
    }

    public function getLifecycle() : ilAssQuestionLifecycle
    {
        return $this->lifecycle;
    }

    public function setLifecycle(ilAssQuestionLifecycle $lifecycle) : void
    {
        $this->lifecycle = $lifecycle;
    }

    public function setExternalId(string $external_id) : void
    {
        $this->external_id = $external_id;
    }

    public function getExternalId() : string
    {
        if (!strlen($this->external_id)) {
            return $this->generateExternalId($this->getId());
        }
        return $this->external_id;
    }

    /**
    * Returns the maximum points, a learner can reach answering the question
    */
    public static function _getMaximumPoints(int $question_id) : float
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $points = 0.0;
        $result = $ilDB->queryF(
            "SELECT points FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($ilDB->numRows($result) == 1) {
            $row = $ilDB->fetchAssoc($result);
            $points = (float) $row["points"];
        }
        return $points;
    }

    /**
     * @return array Database row as associative array having qpl_questions.*, qpl_qst_type.type_tag
     */
    public static function _getQuestionInfo(int $question_id) : array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT qpl_questions.*, qpl_qst_type.type_tag FROM qpl_qst_type, qpl_questions WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id",
            array('integer'),
            array($question_id)
        );

        if ($ilDB->numRows($result)) {
            return $ilDB->fetchAssoc($result);
        }
        return array();
    }

    public static function _getSuggestedSolutionCount(int $question_id) : int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT suggested_solution_id FROM qpl_sol_sug WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        return $ilDB->numRows($result);
    }

    /**
     * @return string HTML
     * @throws ilWACException
     */
    public static function _getSuggestedSolutionOutput(int $question_id) : string
    {
        $question = self::_instantiateQuestion($question_id);
        if (!is_object($question)) {
            return "";
        }
        return $question->getSuggestedSolutionOutput();
    }

    /**
     * @return string HTML
     * @throws ilWACException
     */
    public function getSuggestedSolutionOutput() : string
    {
        $output = array();
        foreach ($this->suggested_solutions as $solution) {
            switch ($solution["type"]) {
                case "lm":
                case "st":
                case "pg":
                case "git":
                    $output[] = '<a href="' . assQuestion::_getInternalLinkHref($solution["internal_link"]) . '">' . $this->lng->txt("solution_hint") . '</a>';
                    break;
                case "file":
                    $possible_texts = array_values(array_filter(array(
                        ilLegacyFormElementsUtil::prepareFormOutput($solution['value']['filename']),
                        ilLegacyFormElementsUtil::prepareFormOutput($solution['value']['name']),
                        $this->lng->txt('tst_show_solution_suggested')
                    )));
                    ilWACSignedPath::setTokenMaxLifetimeInSeconds(60);
                    $output[] = '<a href="' . ilWACSignedPath::signFile($this->getSuggestedSolutionPathWeb() . $solution["value"]["name"]) . '">' . $possible_texts[0] . '</a>';
                    break;
                case "text":
                    $solutionValue = $solution["value"];
                    $solutionValue = $this->fixSvgToPng($solutionValue);
                    $solutionValue = $this->fixUnavailableSkinImageSources($solutionValue);
                    $output[] = $this->prepareTextareaOutput($solutionValue, true);
                    break;
            }
        }
        return implode("<br />", $output);
    }

    /**
     * @deprecated Use loadSuggestedSolution instead
     * @removal ILIAS 9
     */
    public function _getSuggestedSolution(int $question_id, int $subquestion_index = 0) : array
    {
        return $this->loadSuggestedSolution($question_id, $subquestion_index);
    }

    /**
     * Returns a suggested solution for a given subquestion index
     *
     * @return array array("internal_link" => $row["internal_link"],"import_id" => $row["import_id"]);
     */
    public function loadSuggestedSolution(int $question_id, int $subquestion_index = 0) : array
    {
        $result = $this->db->queryF(
            "SELECT * FROM qpl_sol_sug WHERE question_fi = %s AND subquestion_index = %s",
            array('integer','integer'),
            array($question_id, $subquestion_index)
        );
        if ($this->db->numRows($result) == 1) {
            $row = $this->db->fetchAssoc($result);
            return array(
                "internal_link" => $row["internal_link"],
                "import_id" => $row["import_id"]
            );
        }
        return array();
    }

    /**
     * @return string[] HTML
     */
    public function getSuggestedSolutions() : array
    {
        return $this->suggested_solutions;
    }

    public static function _getReachedPoints(int $active_id, int $question_id, int $pass) : float
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

    public function getReachedPoints(int $active_id, int $pass) : float
    {
        return round(self::_getReachedPoints($active_id, $this->getId(), $pass), 2);
    }

    public function getMaximumPoints() : float
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
    final public function getAdjustedReachedPoints(int $active_id, int $pass, bool $authorizedSolution = true) : float
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
    final public function calculateResultsFromSolution(int $active_id, int $pass, bool $obligationsEnabled = false) : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

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

        $this->getProcessLocker()->executeUserQuestionResultUpdateOperation(function () use ($ilDB, $active_id, $pass, $reached_points, $requestsStatisticData, $isAnswered, $existingSolutions) {
            $query = "
			DELETE FROM		tst_test_result
			
			WHERE			active_fi = %s
			AND				question_fi = %s
			AND				pass = %s
		";

            $types = array('integer', 'integer', 'integer');
            $values = array($active_id, $this->getId(), $pass);

            if ($this->getStep() !== null) {
                $query .= "
				AND				step = %s
			";

                $types[] = 'integer';
                $values[] = $this->getStep();
            }
            $ilDB->manipulateF($query, $types, $values);

            if ($existingSolutions['authorized']) {
                $next_id = $ilDB->nextId("tst_test_result");
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

                $ilDB->insert('tst_test_result', $fieldData);
            }
        });

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
        self::_updateTestPassResults($active_id, $pass, $obligationsEnabled, $this->getProcessLocker());
        ilCourseObjectiveResult::_updateObjectiveResult($ilUser->getId(), $active_id, $this->getId());
    }

    /**
     * persists the working state for current testactive and testpass
     * @return bool if saving happened
     */
    final public function persistWorkingState(int $active_id, $pass, bool $obligationsEnabled = false, bool $authorized = true) : bool
    {
        if (!$this->validateSolutionSubmit() && !$this->savePartial()) {
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
    final public function persistPreviewState(ilAssQuestionPreviewSession $previewSession) : bool
    {
        $this->savePreviewData($previewSession);
        return $this->validateSolutionSubmit();
    }

    public function validateSolutionSubmit() : bool
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
    abstract public function saveWorkingData(int $active_id, int $pass, bool $authorized = true) : bool;

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession) : void
    {
        $previewSession->setParticipantsSolution($this->getSolutionSubmit());
    }

    /** @TODO Move this to a proper place. */
    public static function _updateTestResultCache(int $active_id, ilAssQuestionProcessLocker $processLocker = null) : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $pass = ilObjTest::_getResultPass($active_id);

        $query = "
			SELECT		tst_pass_result.*
			FROM		tst_pass_result
			WHERE		active_fi = %s
			AND			pass = %s
		";

        $result = $ilDB->queryF(
            $query,
            array('integer','integer'),
            array($active_id, $pass)
        );

        $row = $ilDB->fetchAssoc($result);

        $max = (float) $row['maxpoints'];
        $reached = (float) $row['points'];

        $obligationsAnswered = (int) $row['obligations_answered'];

        $percentage = (!$max) ? 0 : ($reached / $max) * 100.0;

        $mark = ASS_MarkSchema::_getMatchingMarkFromActiveId($active_id, $percentage);

        $isPassed = ($mark["passed"] ? 1 : 0);
        $isFailed = (!$mark["passed"] ? 1 : 0);

        $userTestResultUpdateCallback = function () use ($ilDB, $active_id, $pass, $max, $reached, $isFailed, $isPassed, $obligationsAnswered, $row, $mark) {
            $passedOnceBefore = 0;
            $query = "SELECT passed_once FROM tst_result_cache WHERE active_fi = %s";
            $res = $ilDB->queryF($query, array('integer'), array($active_id));
            while ($row = $ilDB->fetchAssoc($res)) {
                $passedOnceBefore = (int) $row['passed_once'];
            }
            if ($row == null) {
                $row['hint_count'] = 0;
                $row['hint_points'] = 0.0;
            }
            $passedOnce = (int) ($isPassed || $passedOnceBefore);

            $ilDB->manipulateF(
                "DELETE FROM tst_result_cache WHERE active_fi = %s",
                array('integer'),
                array($active_id)
            );

            $ilDB->insert('tst_result_cache', array(
                'active_fi' => array('integer', $active_id),
                'pass' => array('integer', strlen($pass) ? $pass : 0),
                'max_points' => array('float', strlen($max) ? $max : 0),
                'reached_points' => array('float', strlen($reached) ? $reached : 0),
                'mark_short' => array('text', strlen($mark["short_name"]) ? $mark["short_name"] : " "),
                'mark_official' => array('text', strlen($mark["official_name"]) ? $mark["official_name"] : " "),
                'passed_once' => array('integer', $passedOnce),
                'passed' => array('integer', $isPassed),
                'failed' => array('integer', $isFailed),
                'tstamp' => array('integer', time()),
                'hint_count' => array('integer', $row['hint_count']),
                'hint_points' => array('float', $row['hint_points']),
                'obligations_answered' => array('integer', $obligationsAnswered)
            ));
        };

        if (is_object($processLocker)) {
            $processLocker->executeUserTestResultUpdateLockOperation($userTestResultUpdateCallback);
        } else {
            $userTestResultUpdateCallback();
        }
    }

    /** @TODO Move this to a proper place. */
    public static function _updateTestPassResults(
        int $active_id,
        int $pass,
        bool $obligationsEnabled = false,
        ilAssQuestionProcessLocker $processLocker = null,
        int $test_obj_id = null
    ) : array {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $data = ilObjTest::_getQuestionCountAndPointsForPassOfParticipant($active_id, $pass);
        $time = ilObjTest::_getWorkingTimeOfParticipantForPass($active_id, $pass);



        // update test pass results

        $result = $ilDB->queryF(
            "
			SELECT		SUM(points) reachedpoints,
						SUM(hint_count) hint_count,
						SUM(hint_points) hint_points,
						COUNT(DISTINCT(question_fi)) answeredquestions
			FROM		tst_test_result
			WHERE		active_fi = %s
			AND			pass = %s
			",
            array('integer','integer'),
            array($active_id, $pass)
        );

        if ($result->numRows() > 0) {
            if ($obligationsEnabled) {
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

                $result_obligatory = $ilDB->queryF(
                    $query,
                    array('integer','integer','integer'),
                    array($active_id, $active_id, $pass)
                );

                $obligations_answered = 1;

                while ($row_obligatory = $ilDB->fetchAssoc($result_obligatory)) {
                    if (!(int) $row_obligatory['answ']) {
                        $obligations_answered = 0;
                        break;
                    }
                }
            } else {
                $obligations_answered = 1;
            }

            $row = $ilDB->fetchAssoc($result);

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

            $updatePassResultCallback = function () use ($ilDB, $data, $active_id, $pass, $row, $time, $obligations_answered, $exam_identifier) {

                /** @var $ilDB ilDBInterface */
                $ilDB->replace(
                    'tst_pass_result',
                    array(
                        'active_fi' => array('integer', $active_id),
                        'pass' => array('integer', strlen($pass) ? $pass : 0)),
                    array(
                        'points' => array('float', $row['reachedpoints'] ?: 0),
                        'maxpoints' => array('float', $data['points']),
                        'questioncount' => array('integer', $data['count']),
                        'answeredquestions' => array('integer', $row['answeredquestions']),
                        'workingtime' => array('integer', $time),
                        'tstamp' => array('integer', time()),
                        'hint_count' => array('integer', $row['hint_count']),
                        'hint_points' => array('float', $row['hint_points']),
                        'obligations_answered' => array('integer', $obligations_answered),
                        'exam_id' => array('text', $exam_identifier)
                    )
                );
            };

            if (is_object($processLocker) && $processLocker instanceof ilAssQuestionProcessLocker) {
                $processLocker->executeUserPassResultUpdateLockOperation($updatePassResultCallback);
            } else {
                $updatePassResultCallback();
            }
        }

        assQuestion::_updateTestResultCache($active_id, $processLocker);

        return array(
            'active_fi' => $active_id,
            'pass' => $pass,
            'points' => ($row["reachedpoints"]) ?: 0.0,
            'maxpoints' => $data["points"],
            'questioncount' => $data["count"],
            'answeredquestions' => $row["answeredquestions"],
            'workingtime' => $time,
            'tstamp' => time(),
            'hint_count' => $row['hint_count'],
            'hint_points' => $row['hint_points'],
            'obligations_answered' => $obligations_answered,
            'exam_id' => $exam_identifier
        );
    }

    public static function logAction(string $logtext, int $active_id, int $question_id) : void
    {
        $original_id = self::_getOriginalId($question_id);

        ilObjAssessmentFolder::_addLog(
            $GLOBALS['DIC']['ilUser']->getId(),
            ilObjTest::_getObjectIDFromActiveID($active_id),
            $logtext,
            $question_id,
            $original_id
        );
    }

    /**
     * Move an uploaded media file to an public accessible temp dir to present it
     *
     * @param string $file File path
     * @param string $name Name of the file
     * @return string|bool Tempname or false
     */
    public function moveUploadedMediaFile(string $file, string $name)
    {
        $mediatempdir = CLIENT_WEB_DIR . "/assessment/temp";
        if (!@is_dir($mediatempdir)) {
            ilFileUtils::createDirectory($mediatempdir);
        }
        $temp_name = tempnam($mediatempdir, $name . "_____");
        $temp_name = str_replace("\\", "/", $temp_name);
        @unlink($temp_name);
        if (!ilFileUtils::moveUploadedFile($file, $name, $temp_name)) {
            return false;
        }
        return $temp_name;
    }

    public function getSuggestedSolutionPath() : string
    {
        return CLIENT_WEB_DIR . "/assessment/$this->obj_id/$this->id/solution/";
    }

    /**
    * Returns the image path for web accessable images of a question.
    * The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
    *
    */
    public function getImagePath($question_id = null, $object_id = null) : string
    {
        if ($question_id === null) {
            $question_id = $this->id;
        }

        if ($object_id === null) {
            $object_id = $this->obj_id;
        }

        return $this->buildImagePath($question_id, $object_id);
    }

    public function buildImagePath($questionId, $parentObjectId) : string
    {
        return CLIENT_WEB_DIR . "/assessment/{$parentObjectId}/{$questionId}/images/";
    }

    /**
    * Returns the image path for web accessable flash files of a question.
    * The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/flash
    *
    * @deprecated Flash is obsolete
    */
    public function getFlashPath() : string
    {
        return CLIENT_WEB_DIR . "/assessment/$this->obj_id/$this->id/flash/";
    }

    /**
    * Returns the web image path for web accessable java applets of a question.
    * The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/java
    *
     * @deprecated Java is obsolete
    */
    public function getJavaPathWeb() : string
    {
        $webdir = ilFileUtils::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/$this->obj_id/$this->id/java/";
        return str_replace(
            ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
            ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            $webdir
        );
    }

    public function getSuggestedSolutionPathWeb() : string
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
    public function getImagePathWeb() : string
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
    public function getTestOutputSolutions(int $activeId, int $pass) : array
    {
        if ($this->getTestPresentationConfig()->isSolutionInitiallyPrefilled()) {
            return $this->getSolutionValues($activeId, $pass, true);
        }
        return $this->getUserSolutionPreferingIntermediate($activeId, $pass);
    }
    // hey.

    public function getUserSolutionPreferingIntermediate(int $active_id, $pass = null) : array
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
    public function getSolutionValues($active_id, $pass = null, bool $authorized = true) : array
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

        $values = array();

        while ($row = $this->db->fetchAssoc($result)) {
            $values[] = $row;
        }

        return $values;
    }

    /**
     * Checks whether the question is in use or not in pools or tests
     */
    public function isInUse(int $question_id = 0) : bool
    {
        return $this->usageNumber($question_id) > 0;
    }

    /**
     * Returns the number of place the question is in use in pools or tests
     */
    public function usageNumber(int $question_id = 0) : int
    {
        if ($question_id < 1) {
            $question_id = $this->getId();
        }

        $result = $this->db->queryF(
            "SELECT COUNT(qpl_questions.question_id) question_count FROM qpl_questions, tst_test_question WHERE qpl_questions.original_id = %s AND qpl_questions.question_id = tst_test_question.question_fi",
            array('integer'),
            array($question_id)
        );
        $row = $this->db->fetchAssoc($result);
        $count = (int) $row["question_count"];

        $result = $this->db->queryF(
            "
			SELECT tst_active.test_fi
			FROM qpl_questions
			INNER JOIN tst_test_rnd_qst ON tst_test_rnd_qst.question_fi = qpl_questions.question_id
			INNER JOIN tst_active ON tst_active.active_id = tst_test_rnd_qst.active_fi
			WHERE qpl_questions.original_id = %s
			GROUP BY tst_active.test_fi",
            array('integer'),
            array($question_id)
        );
        $count += (int) $this->db->numRows($result);

        return $count;
    }

    /**
    * Checks whether the question is a clone of another question or not
    */
    public function isClone(int $question_id = 0) : bool
    {
        if ($question_id < 1) {
            $question_id = $this->id;
        }
        $result = $this->db->queryF(
            "SELECT COUNT(original_id) cnt FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        $row = $this->db->fetchAssoc($result);
        return ((int) $row["cnt"]) > 0;
    }

    public static function getQuestionTypeFromDb(int $question_id) : string
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT qpl_qst_type.type_tag FROM qpl_qst_type, qpl_questions WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id",
            array('integer'),
            array($question_id)
        );
        $data = $ilDB->fetchAssoc($result);
        return $data["type_tag"];
    }

    /**
     * @return string|array Or Array? @see Deletion methods here
     */
    public function getAdditionalTableName()
    {
        return "";
    }

    /**
     * @return string|array Or Array? @see Deletion methods here
     */
    public function getAnswerTableName()
    {
        return "";
    }

    public function deleteAnswers(int $question_id) : void
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

    public function deleteAdditionalTableData(int $question_id) : void
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

    protected function deletePageOfQuestion(int $question_id) : void
    {
        $page = new ilAssQuestionPage($question_id);
        $page->delete();
    }

    /**
    * Deletes a question and all materials from the database
    */
    public function delete(int $question_id) : void
    {
        if ($question_id < 1) {
            return;
        } // nothing to do

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
            // delete suggested solutions contained in the question
            $affectedRows = $this->db->manipulateF(
                "DELETE FROM qpl_sol_sug WHERE question_fi = %s",
                array('integer'),
                array($question_id)
            );
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
                ilSkillUsage::setUsage(
                    $assignment->getParentObjId(),
                    $assignment->getSkillBaseId(),
                    $assignment->getSkillTrefId(),
                    false
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

    private function deleteTaxonomyAssignments() : void
    {
        $taxIds = ilObjTaxonomy::getUsageOfObject($this->getObjId());

        foreach ($taxIds as $taxId) {
            $taxNodeAssignment = new ilTaxNodeAssignment('qpl', $this->getObjId(), 'quest', $taxId);
            $taxNodeAssignment->deleteAssignmentsOfItem($this->getId());
        }
    }

    public function getTotalAnswers() : int
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
        $found_id = array();
        while ($row = $this->db->fetchAssoc($result)) {
            $found_id[] = $row["question_id"];
        }

        $result = $this->db->query("SELECT * FROM tst_test_result WHERE " . $this->db->in('question_fi', $found_id, false, 'integer'));

        return $this->db->numRows($result);
    }

    public static function _getTotalRightAnswers(int $a_q_id) : int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT question_id FROM qpl_questions WHERE original_id = %s OR question_id = %s",
            array('integer','integer'),
            array($a_q_id, $a_q_id)
        );
        if ($result->numRows() == 0) {
            return 0;
        }

        $found_id = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $found_id[] = $row["question_id"];
        }

        $result = $ilDB->query("SELECT * FROM tst_test_result WHERE " . $ilDB->in('question_fi', $found_id, false, 'integer'));
        $answers = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $reached = $row["points"];
            $max = self::_getMaximumPoints($row["question_fi"]);
            $answers[] = array("reached" => $reached, "max" => $max);
        }
        $max = 0.0;
        $reached = 0.0;
        foreach ($answers as $key => $value) {
            $max += $value["max"];
            $reached += $value["reached"];
        }
        if ($max > 0) {
            return $reached / $max;
        }
        return 0;
    }

    public static function _getTitle(int $a_q_id) : string
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT title FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($a_q_id)
        );

        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            return $row["title"];
        }
        return "";
    }

    public static function _getQuestionText(int $a_q_id) : string
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT question_text FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($a_q_id)
        );

        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            return $row["question_text"];
        }

        return "";
    }

    public static function isFileAvailable(string $file) : bool
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

    public function copyXHTMLMediaObjectsOfQuestion(int $a_q_id) : void
    {
        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $a_q_id);
        foreach ($mobs as $mob) {
            ilObjMediaObject::_saveUsage($mob, "qpl:html", $this->getId());
        }
    }

    public function syncXHTMLMediaObjectsOfQuestion() : void
    {
        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
        foreach ($mobs as $mob) {
            ilObjMediaObject::_saveUsage($mob, "qpl:html", $this->original_id);
        }
    }

    public function createPageObject() : void
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

    public function copyPageOfQuestion(int $a_q_id) : void
    {
        if ($a_q_id > 0) {
            $page = new ilAssQuestionPage($a_q_id);

            $xml = str_replace("il__qst_" . $a_q_id, "il__qst_" . $this->id, $page->getXMLContent());
            $this->page->setXMLContent($xml);
            $this->page->updateFromXML();
        }
    }

    public function getPageOfQuestion() : string
    {
        $page = new ilAssQuestionPage($this->id);
        return $page->getXMLContent();
    }

    public static function _getQuestionType(int $question_id) : string
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($question_id < 1) {
            return "";
        }
        $result = $ilDB->queryF(
            "SELECT type_tag FROM qpl_questions, qpl_qst_type WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() == 1) {
            $data = $ilDB->fetchAssoc($result);
            return $data["type_tag"];
        }

        return "";
    }

    public static function _getQuestionTitle(int $question_id) : string
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($question_id < 1) {
            return "";
        }

        $result = $ilDB->queryF(
            "SELECT title FROM qpl_questions WHERE qpl_questions.question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() == 1) {
            $data = $ilDB->fetchAssoc($result);
            return $data["title"];
        }

        return "";
    }

    public function setOriginalId(?int $original_id) : void
    {
        $this->original_id = $original_id;
    }

    public function getOriginalId() : ?int
    {
        return $this->original_id;
    }

    protected static $imageSourceFixReplaceMap = array(
        'ok.svg' => 'ok.png',
        'not_ok.svg' => 'not_ok.png',
        'checkbox_checked.svg' => 'checkbox_checked.png',
        'checkbox_unchecked.svg' => 'checkbox_unchecked.png',
        'radiobutton_checked.svg' => 'radiobutton_checked.png',
        'radiobutton_unchecked.svg' => 'radiobutton_unchecked.png'
    );

    public function fixSvgToPng(string $imageFilenameContainingString) : string
    {
        $needles = array_keys(self::$imageSourceFixReplaceMap);
        $replacements = array_values(self::$imageSourceFixReplaceMap);
        return str_replace($needles, $replacements, $imageFilenameContainingString);
    }

    public function fixUnavailableSkinImageSources(string $html) : string
    {
        $matches = null;
        if (preg_match_all('/src="(.*?)"/m', $html, $matches)) {
            $sources = $matches[1];

            $needleReplacementMap = array();

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

                if ($levels[4] === 'Modules' || $levels[4] === 'Services') {
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

    public function loadFromDb(int $question_id) : void
    {
        $result = $this->db->queryF(
            "SELECT external_id FROM qpl_questions WHERE question_id = %s",
            array("integer"),
            array($question_id)
        );
        if ($this->db->numRows($result) == 1) {
            $data = $this->db->fetchAssoc($result);
            $this->external_id = $data['external_id'];
        }

        $result = $this->db->queryF(
            "SELECT * FROM qpl_sol_sug WHERE question_fi = %s",
            array('integer'),
            array($this->getId())
        );
        $this->suggested_solutions = array();
        if ($this->db->numRows($result) > 0) {
            while ($row = $this->db->fetchAssoc($result)) {
                $value = (is_array(unserialize($row["value"], ['allowed_classes' => false]))) ? unserialize($row["value"], ['allowed_classes' => false]) : ilRTE::_replaceMediaObjectImageSrc($row["value"], 1);
                $this->suggested_solutions[$row["subquestion_index"]] = array(
                    "type" => $row["type"],
                    "value" => $value,
                    "internal_link" => $row["internal_link"],
                    "import_id" => $row["import_id"]
                );
            }
        }
    }

    /**
    * Creates a new question without an owner when a new question is created
    * This assures that an ID is given to the question if a file upload or something else occurs
    *
    * @return integer ID of the new question
    */
    public function createNewQuestion(bool $a_create_page = true) : int
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        $complete = "0";
        $estw_time = $this->getEstimatedWorkingTime();
        $estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);
        $obj_id = ($this->getObjId() <= 0) ? (ilObject::_lookupObjId((strlen($DIC->testQuestionPool()->internal()->request()->getRefId())) ? $DIC->testQuestionPool()->internal()->request()->getRefId() : $_POST["sel_qpl"])) : $this->getObjId();
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
                "working_time" => array("text", $estw_time),
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

    public function saveQuestionDataToDb(int $original_id = -1) : void
    {
        $estw_time = $this->getEstimatedWorkingTime();
        $estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);
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
                "working_time" => array("text", $estw_time),
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
                "working_time" => array("text", $estw_time),
                "tstamp" => array("integer", time()),
                'complete' => array('integer', $this->isComplete()),
                "external_id" => array("text", $this->getExternalId())
            ), array(
            "question_id" => array("integer", $this->getId())
            ));
        }
    }

    public function saveToDb() : void
    {
        $this->updateSuggestedSolutions();

        // remove unused media objects from ILIAS
        $this->cleanupMediaObjectUsage();

        $complete = "0";
        if ($this->isComplete()) {
            $complete = "1";
        }

        $this->db->update('qpl_questions', array(
            'tstamp' => array('integer', time()),
            'owner' => array('integer', $this->getOwner()),
            'complete' => array('integer', $complete),
            'lifecycle' => array('text', $this->getLifecycle()->getIdentifier()),
        ), array(
            'question_id' => array('integer', $this->getId())
        ));
        ilObjQuestionPool::_updateQuestionCount($this->obj_id);
    }

    /**
     * @deprecated
     */
    public function setNewOriginalId(int $newId) : void
    {
        self::saveOriginalId($this->getId(), $newId);
    }

    public static function saveOriginalId(int $questionId, int $originalId) : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $query = "UPDATE qpl_questions SET tstamp = %s, original_id = %s WHERE question_id = %s";

        $ilDB->manipulateF(
            $query,
            array('integer','integer', 'text'),
            array(time(), $originalId, $questionId)
        );
    }

    public static function resetOriginalId(int $questionId) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = "UPDATE qpl_questions SET tstamp = %s, original_id = NULL WHERE question_id = %s";

        $ilDB->manipulateF(
            $query,
            array('integer', 'text'),
            array(time(), $questionId)
        );
    }

    protected function onDuplicate(int $originalParentId, int $originalQuestionId, int $duplicateParentId, int $duplicateQuestionId) : void
    {
        $this->duplicateSuggestedSolutionFiles($originalParentId, $originalQuestionId);
        $this->feedbackOBJ->duplicateFeedback($originalQuestionId, $duplicateQuestionId);
        $this->duplicateQuestionHints($originalQuestionId, $duplicateQuestionId);
        $this->duplicateSkillAssignments($originalParentId, $originalQuestionId, $duplicateParentId, $duplicateQuestionId);
    }

    protected function beforeSyncWithOriginal(int $origQuestionId, int $dupQuestionId, int $origParentObjId, int $dupParentObjId) : void
    {
    }

    protected function afterSyncWithOriginal(int $origQuestionId, int $dupQuestionId, int $origParentObjId, int $dupParentObjId) : void
    {
        $this->feedbackOBJ->syncFeedback($origQuestionId, $dupQuestionId);
    }

    protected function onCopy(int $sourceParentId, int $sourceQuestionId, int $targetParentId, int $targetQuestionId) : void
    {
        $this->copySuggestedSolutionFiles($sourceParentId, $sourceQuestionId);

        // duplicate question feeback
        $this->feedbackOBJ->duplicateFeedback($sourceQuestionId, $targetQuestionId);

        // duplicate question hints
        $this->duplicateQuestionHints($sourceQuestionId, $targetQuestionId);

        // duplicate skill assignments
        $this->duplicateSkillAssignments($sourceParentId, $sourceQuestionId, $targetParentId, $targetQuestionId);
    }

    public function deleteSuggestedSolutions() : void
    {
        // delete the links in the qpl_sol_sug table
        $this->db->manipulateF(
            "DELETE FROM qpl_sol_sug WHERE question_fi = %s",
            array('integer'),
            array($this->getId())
        );
        ilInternalLink::_deleteAllLinksOfSource("qst", $this->getId());
        $this->suggested_solutions = array();
        ilFileUtils::delDir($this->getSuggestedSolutionPath());
    }

    /**
     * Returns a suggested solution for a given subquestion index
     */
    public function getSuggestedSolution(int $subquestion_index = 0) : array
    {
        if (array_key_exists($subquestion_index, $this->suggested_solutions)) {
            return $this->suggested_solutions[$subquestion_index];
        }
        return array();
    }

    /**
    * Returns the title of a suggested solution at a given subquestion_index.
    * This can be usable for displaying suggested solutions
    *
    * @param integer $subquestion_index The index of a subquestion (i.e. a close test gap). Usually 0
    * @return string A string containing the type and title of the internal link
    */
    public function getSuggestedSolutionTitle(int $subquestion_index = 0) : string
    {
        if (array_key_exists($subquestion_index, $this->suggested_solutions)) {
            $title = $this->suggested_solutions[$subquestion_index]["internal_link"];
        // TO DO: resolve internal link an get link type and title
        } else {
            $title = "";
        }
        return $title;
    }

    /**
    * Sets a suggested solution for the question.
    * If there is more than one subquestion (i.e. close questions) may enter a subquestion index.
    *
    * @param string $solution_id An internal link pointing to the suggested solution
    * @param int $subquestion_index The index of a subquestion (i.e. a close test gap). Usually 0
    * @param bool $is_import A boolean indication that the internal link was imported from another ILIAS installation
    * @access public
    */
    public function setSuggestedSolution(string $solution_id = "", int $subquestion_index = 0, bool $is_import = false) : void
    {
        if (strcmp($solution_id, "") != 0) {
            $import_id = "";
            if ($is_import) {
                $import_id = $solution_id;
                $solution_id = $this->_resolveInternalLink($import_id);
            }
            $this->suggested_solutions[$subquestion_index] = array(
                "internal_link" => $solution_id,
                "import_id" => $import_id
            );
        }
    }

    /**
    * Duplicates the files of a suggested solution if the question is duplicated
    */
    protected function duplicateSuggestedSolutionFiles(int $parent_id, int $question_id) : void
    {
        foreach ($this->suggested_solutions as $index => $solution) {
            if (strcmp($solution["type"], "file") == 0) {
                $filepath = $this->getSuggestedSolutionPath();
                $filepath_original = str_replace(
                    "/{$this->obj_id}/{$this->id}/solution",
                    "/$parent_id/$question_id/solution",
                    $filepath
                );
                if (!file_exists($filepath)) {
                    ilFileUtils::makeDirParents($filepath);
                }
                $filename = $solution["value"]["name"];
                if (strlen($filename)) {
                    if (!copy($filepath_original . $filename, $filepath . $filename)) {
                        $this->ilLog->root()->error("File could not be duplicated!!!!");
                        $this->ilLog->root()->error("object: " . print_r($this, true));
                    }
                }
            }
        }
    }

    protected function syncSuggestedSolutionFiles(int $original_id) : void
    {
        $filepath = $this->getSuggestedSolutionPath();
        $filepath_original = str_replace("/$this->id/solution", "/$original_id/solution", $filepath);
        ilFileUtils::delDir($filepath_original);
        foreach ($this->suggested_solutions as $index => $solution) {
            if (strcmp($solution["type"], "file") == 0) {
                if (!file_exists($filepath_original)) {
                    ilFileUtils::makeDirParents($filepath_original);
                }
                $filename = $solution["value"]["name"];
                if (strlen($filename)) {
                    if (!@copy($filepath . $filename, $filepath_original . $filename)) {
                        $this->ilLog->root()->error("File could not be duplicated!!!!");
                        $this->ilLog->root()->error("object: " . print_r($this, true));
                    }
                }
            }
        }
    }

    protected function copySuggestedSolutionFiles(int $source_questionpool_id, int $source_question_id) : void
    {
        foreach ($this->suggested_solutions as $index => $solution) {
            if (strcmp($solution["type"], "file") == 0) {
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

    public function updateSuggestedSolutions(int $original_id = -1) : void
    {
        $id = (strlen($original_id) && is_numeric($original_id)) ? $original_id : $this->getId();
        $this->db->manipulateF(
            "DELETE FROM qpl_sol_sug WHERE question_fi = %s",
            array('integer'),
            array($id)
        );
        ilInternalLink::_deleteAllLinksOfSource("qst", $id);
        foreach ($this->suggested_solutions as $index => $solution) {
            $next_id = $this->db->nextId('qpl_sol_sug');
            /** @var ilDBInterface $ilDB */
            $this->db->insert(
                'qpl_sol_sug',
                array(
                   'suggested_solution_id' => array( 'integer', 	$next_id ),
                   'question_fi' => array( 'integer', 	$id ),
                   'type' => array( 'text', 		$solution['type'] ),
                   'value' => array( 'clob', 		ilRTE::_replaceMediaObjectImageSrc((is_array($solution['value'])) ? serialize($solution[ 'value' ]) : $solution['value'], 0) ),
                   'internal_link' => array( 'text', 		$solution['internal_link'] ),
                   'import_id' => array( 'text',		null ),
                   'subquestion_index' => array( 'integer', 	$index ),
                   'tstamp' => array( 'integer',	time() ),
                )
            );
            if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches)) {
                ilInternalLink::_saveLink("qst", $id, $matches[2], $matches[3], $matches[1]);
            }
        }
        if ($original_id !== -1) {
            $this->syncSuggestedSolutionFiles($id);
        }
        $this->cleanupMediaObjectUsage();
    }

    /**
    * Saves a suggested solution for the question.
    * If there is more than one subquestion (i.e. close questions) may enter a subquestion index.
    *
    * @param string $solution_id An internal link pointing to the suggested solution
    * @param integer $subquestion_index The index of a subquestion (i.e. a close test gap). Usually 0
    * @param boolean $is_import A boolean indication that the internal link was imported from another ILIAS installation
    * @access public
    */
    public function saveSuggestedSolution(string $type, $solution_id = "", int $subquestion_index = 0, $value = "") : void
    {
        $this->db->manipulateF(
            "DELETE FROM qpl_sol_sug WHERE question_fi = %s AND subquestion_index = %s",
            array("integer", "integer"),
            array(
                $this->getId(),
                $subquestion_index
            )
        );

        $next_id = $this->db->nextId('qpl_sol_sug');
        /** @var ilDBInterface $ilDB */
        $affectedRows = $this->db->insert(
            'qpl_sol_sug',
            array(
                                                       'suggested_solution_id' => array( 'integer', 	$next_id ),
                                                       'question_fi' => array( 'integer', 	$this->getId() ),
                                                       'type' => array( 'text', 		$type ),
                                                       'value' => array( 'clob', 		ilRTE::_replaceMediaObjectImageSrc((is_array($value)) ? serialize($value) : $value, 0) ),
                                                       'internal_link' => array( 'text', 		$solution_id ),
                                                       'import_id' => array( 'text',		null ),
                                                       'subquestion_index' => array( 'integer', 	$subquestion_index ),
                                                       'tstamp' => array( 'integer',	time() ),
                                                   )
        );
        if ($affectedRows == 1) {
            $this->suggested_solutions[$subquestion_index] = array(
                "type" => $type,
                "value" => $value,
                "internal_link" => $solution_id,
                "import_id" => ""
            );
        }
        $this->cleanupMediaObjectUsage();
    }

    public function _resolveInternalLink(string $internal_link) : string
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
            if (strcmp($resolved_link, "") == 0) {
                $resolved_link = $internal_link;
            }
        } else {
            $resolved_link = $internal_link;
        }
        return $resolved_link;
    }

    public function _resolveIntLinks(int $question_id) : void
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
                $resolved_link = $this->_resolveInternalLink($internal_link);
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

    public static function _getInternalLinkHref(string $target = "") : string
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

    public static function _getOriginalId(int $question_id) : int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT * FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($ilDB->numRows($result) > 0) {
            $row = $ilDB->fetchAssoc($result);
            if ($row["original_id"] > 0) {
                return $row["original_id"];
            }

            return (int) $row["question_id"];
        }

        return -1;
    }

    public static function originalQuestionExists(int $questionId) : bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "
			SELECT COUNT(dupl.question_id) cnt
			FROM qpl_questions dupl
			INNER JOIN qpl_questions orig
			ON orig.question_id = dupl.original_id
			WHERE dupl.question_id = %s
		";

        $res = $ilDB->queryF($query, array('integer'), array($questionId));
        $row = $ilDB->fetchAssoc($res);

        return $row['cnt'] > 0;
    }

    public function syncWithOriginal() : void
    {
        if (!$this->getOriginalId()) {
            return;
        }

        $originalObjId = self::lookupParentObjId($this->getOriginalId());

        if (!$originalObjId) {
            return;
        }

        $this->beforeSyncWithOriginal($this->getOriginalId(), $this->getId(), $originalObjId, $this->getObjId());

        $this->setId($this->getOriginalId());
        $this->setOriginalId(null);
        $this->setObjId($originalObjId);

        $this->saveToDb();

        $this->deletePageOfQuestion($this->getOriginalId());
        $this->createPageObject();
        $this->copyPageOfQuestion($this->getId());

        $this->setId($this->getId());
        $this->setOriginalId($this->getOriginalId());
        $this->setObjId($this->getObjId());

        $this->updateSuggestedSolutions($this->getOriginalId());
        $this->syncXHTMLMediaObjectsOfQuestion();

        $this->afterSyncWithOriginal($this->getOriginalId(), $this->getId(), $originalObjId, $this->getObjId());
        $this->syncHints();
    }

    public function createRandomSolution(int $test_id, int $user_id) : void
    {
    }

    public function _questionExists(int $question_id) : bool
    {
        if ($question_id < 1) {
            return false;
        }

        $result = $this->db->queryF(
            "SELECT question_id FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        return $result->numRows() == 1;
    }

    public function _questionExistsInPool(int $question_id) : bool
    {
        if ($question_id < 1) {
            return false;
        }

        $result = $this->db->queryF(
            "SELECT question_id FROM qpl_questions INNER JOIN object_data ON obj_fi = obj_id WHERE question_id = %s AND type = 'qpl'",
            array('integer'),
            array($question_id)
        );
        return $this->db->numRows($result) == 1;
    }

    /**
     * @deprecated use assQuestion::instantiateQuestion() instead.
     * @removal ILIAS 9
     */
    public static function _instanciateQuestion(int $question_id) : assQuestion
    {
        return self::_instantiateQuestion($question_id);
    }

    /**
     * @deprecated use assQuestion::instantiateQuestion() instead.
     */
    public static function _instantiateQuestion(int $question_id) : assQuestion
    {
        return self::instantiateQuestion($question_id);
    }

    public static function instantiateQuestion(int $question_id) : assQuestion
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];

        $question_type = assQuestion::_getQuestionType($question_id);
        if (!strlen($question_type)) {
            throw new InvalidArgumentException('No question with ID ' . $question_id . ' exists');
        }
        assQuestion::_includeClass($question_type);
        $question = new $question_type();
        $question->loadFromDb($question_id);

        $feedbackObjectClassname = self::getFeedbackClassNameByQuestionType($question_type);
        $question->feedbackOBJ = new $feedbackObjectClassname($question, $ilCtrl, $ilDB, $lng);

        return $question;
    }

    public function getPoints() : float
    {
        if (strcmp($this->points, "") == 0) {
            return 0.0;
        }

        return $this->points;
    }

    public function setPoints(float $points) : void
    {
        $this->points = $points;
    }

    public function getSolutionMaxPass(int $active_id) : int
    {
        return self::_getSolutionMaxPass($this->getId(), $active_id);
    }

    /**
    * Returns the maximum pass a users question solution
    */
    public static function _getSolutionMaxPass(int $question_id, int $active_id) : int
    {
        /*		include_once "./Modules/Test/classes/class.ilObjTest.php";
                $pass = ilObjTest::_getPass($active_id);
                return $pass;*/

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

    public static function _isWriteable(int $question_id, int $user_id) : bool
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

    public static function _isUsedInRandomTest(int $question_id) : bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT test_random_question_id FROM tst_test_rnd_qst WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        return $ilDB->numRows($result) > 0;
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

    public function deductHintPointsFromReachedPoints(ilAssQuestionPreviewSession $previewSession, $reachedPoints) : ?int
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

    public function isPreviewSolutionCorrect(ilAssQuestionPreviewSession $previewSession) : bool
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
    final public function adjustReachedPointsByScoringOptions($points, $active_id, $pass = null) : int
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

    /**
     * Returns true if the question was worked through in the given pass
     * Worked through means that the user entered at least one value
     */
    public static function _isWorkedThrough(int $active_id, int $question_id, int $pass) : bool
    {
        return self::lookupResultRecordExist($active_id, $question_id, $pass);
    }

    /**
     * Checks if an array of question ids is answered by a user or not
     *
     * @param int user_id
     * @param array $question_ids user id array
     */
    public static function _areAnswered(int $a_user_id, array $a_question_ids) : bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            "SELECT DISTINCT(question_fi) FROM tst_test_result JOIN tst_active " .
            "ON (active_id = active_fi) " .
            "WHERE " . $ilDB->in('question_fi', $a_question_ids, false, 'integer') .
            " AND user_fi = %s",
            array('integer'),
            array($a_user_id)
        );
        return ($res->numRows() == count($a_question_ids)) ? true : false;
    }

    /**
     * Checks if a given string contains HTML or not
     *
     * @deprecated use ilUtil::isHTML() (or successor) instead
     */
    public function isHTML($a_text) : bool
    {
        return ilUtil::isHTML($a_text);
    }

    /**
     * @deprecated use ilUtil::prepareTextareaOutput() (or successor) instead
     */
    public function prepareTextareaOutput(string $txt_output, bool $prepare_for_latex_output = false, bool $omitNl2BrWhenTextArea = false)
    {
        return ilLegacyFormElementsUtil::prepareTextareaOutput(
            $txt_output,
            $prepare_for_latex_output,
            $omitNl2BrWhenTextArea
        );
    }

    /**
    * Reads an QTI material tag and creates a text or XHTML string
    * @return string text or xhtml string
    */
    public function QTIMaterialToString(ilQTIMaterial $a_material) : string
    {
        $result = "";
        $mobs = array();
        for ($i = 0; $i < $a_material->getMaterialCount(); $i++) {
            $material = $a_material->getMaterial($i);
            if (strcmp($material["type"], "mattext") == 0) {
                $result .= $material["material"]->getContent();
            }
            if (strcmp($material["type"], "matimage") == 0) {
                $matimage = $material["material"];
                if (preg_match("/(il_([0-9]+)_mob_([0-9]+))/", $matimage->getLabel(), $matches)) {
                    // import an mediaobject which was inserted using tiny mce
                    //if (!is_array(ilSession::get("import_mob_xhtml"))) {
                    //    ilSession::set("import_mob_xhtml", array());
                    //}
                    $mobs[] = array("mob" => $matimage->getLabel(),
                                                            "uri" => $matimage->getUri()
                    );
                }
            }
        }
        ilSession::set('import_mob_xhtml', $mobs);
        return $result;
    }

    public function addQTIMaterial(ilXmlWriter $a_xml_writer, string $a_material, bool $close_material_tag = true, bool $add_mobs = true) : void
    {
        $a_xml_writer->xmlStartTag("material");
        $attrs = array(
            "texttype" => "text/plain"
        );
        if ($this->isHTML($a_material)) {
            $attrs["texttype"] = "text/xhtml";
        }
        $a_xml_writer->xmlElement("mattext", $attrs, ilRTE::_replaceMediaObjectImageSrc($a_material, 0));
        if ($add_mobs) {
            $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
            foreach ($mobs as $mob) {
                $moblabel = "il_" . IL_INST_ID . "_mob_" . $mob;
                if (strpos($a_material, "mm_$mob") !== false) {
                    if (ilObjMediaObject::_exists($mob)) {
                        $mob_obj = new ilObjMediaObject($mob);
                        $imgattrs = array(
                            "label" => $moblabel,
                            "uri" => "objects/" . "il_" . IL_INST_ID . "_mob_" . $mob . "/" . $mob_obj->getTitle()
                        );
                    }
                    $a_xml_writer->xmlElement("matimage", $imgattrs, null);
                }
            }
        }
        if ($close_material_tag) {
            $a_xml_writer->xmlEndTag("material");
        }
    }

    public function buildHashedImageFilename(string $plain_image_filename, bool $unique = false) : string
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
    public static function _setReachedPoints(int $active_id, int $question_id, float $points, float $maxpoints, int $pass, bool $manualscoring, bool $obligationsEnabled) : bool
    {
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
                assQuestion::_updateTestPassResults($active_id, $pass, $obligationsEnabled);
                ilCourseObjectiveResult::_updateObjectiveResult(ilObjTest::_getUserIdFromActiveId($active_id), $question_id, $points);
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

    public function getQuestion() : string
    {
        return $this->question;
    }

    public function setQuestion(string $question = "") : void
    {
        $this->question = $question;
    }

    /**
    * Returns the question type of the question
    *
    * @return string The question type of the question
    */
    abstract public function getQuestionType() : string;

    public function getQuestionTypeID() : int
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

    public function syncHints() : void
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
                        'qht_hint_points' => array('integer', $row["qht_hint_points"]),
                        'qht_hint_text' => array('text', $row["qht_hint_text"]),
                    )
                );
            }
        }
    }

    protected function getRTETextWithMediaObjects() : string
    {
        // must be called in parent classes. add additional RTE text in the parent
        // classes and call this method to add the standard RTE text
        $collected = $this->getQuestion();
        $collected .= $this->feedbackOBJ->getGenericFeedbackContent($this->getId(), false);
        $collected .= $this->feedbackOBJ->getGenericFeedbackContent($this->getId(), true);
        $collected .= $this->feedbackOBJ->getAllSpecificAnswerFeedbackContents($this->getId());

        foreach ($this->suggested_solutions as $solution_array) {
            $collected .= $solution_array["value"];
        }
        $questionHintList = ilAssQuestionHintList::getListByQuestionId($this->getId());
        foreach ($questionHintList as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */
            $collected .= $questionHint->getText();
        }

        return $collected;
    }

    public function cleanupMediaObjectUsage() : void
    {
        $combinedtext = $this->getRTETextWithMediaObjects();
        ilRTE::_cleanupMediaObjectUsage($combinedtext, "qpl:html", $this->getId());
    }

    public function getInstances() : array
    {
        $result = $this->db->queryF(
            "SELECT question_id FROM qpl_questions WHERE original_id = %s",
            array("integer"),
            array($this->getId())
        );
        $instances = array();
        $ids = array();
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

    public static function _needsManualScoring(int $question_id) : bool
    {
        $scoring = ilObjAssessmentFolder::_getManualScoringTypes();
        $questiontype = assQuestion::_getQuestionType($question_id);
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
    public function getActiveUserData(int $active_id) : array
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

        return array();
    }

    public static function _includeClass(string $question_type, int $gui = 0) : void
    {
        if (self::isCoreQuestionType($question_type)) {
            self::includeCoreClass($question_type, $gui);
        }
    }

    public static function getFeedbackClassNameByQuestionType(string $questionType) : string
    {
        return str_replace('ass', 'ilAss', $questionType) . 'Feedback';
    }

    public static function isCoreQuestionType(string $questionType) : bool
    {
        return file_exists("Modules/TestQuestionPool/classes/class.{$questionType}GUI.php");
    }

    public static function includeCoreClass($questionType, $withGuiClass) : void
    {
        if ($withGuiClass) {
            // object class is included by gui classes constructor
        } else {
        }

        $feedbackClassName = self::getFeedbackClassNameByQuestionType($questionType);
    }

    public static function _getQuestionTypeName($type_tag) : string
    {
        global $DIC;
        if (file_exists("./Modules/TestQuestionPool/classes/class." . $type_tag . ".php")) {
            $lng = $DIC['lng'];
            return $lng->txt($type_tag);
        }
        $component_factory = $DIC['component.factory'];

        foreach ($component_factory->getActivePluginsInSlot("qst") as $pl) {
            if ($pl->getQuestionType() === $type_tag) {
                return $pl->getQuestionTypeTranslation();
            }
        }
        return "";
    }

    /**
     * @deprecated Use instantiateQuestionGUI (without legacy underscore & typos) instead.
     * @removal ILIAS 9
     */
    public static function _instanciateQuestionGUI(int $question_id) : assQuestionGUI
    {
        return self::instantiateQuestionGUI($question_id);
    }

    public static function instantiateQuestionGUI(int $a_question_id) : assQuestionGUI
    {
        //Shouldn't you live in assQuestionGUI, Mister?

        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];

        if (strcmp($a_question_id, "") != 0) {
            $question_type = assQuestion::_getQuestionType($a_question_id);

            assQuestion::_includeClass($question_type, 1);

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

    public function setExportDetailsXLS(ilAssExcelFormatHelper $worksheet, int $startrow, int $active_id, int $pass) : int
    {
        $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord(0) . $startrow, $this->lng->txt($this->getQuestionType()));
        $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord(1) . $startrow, $this->getTitle());

        return $startrow;
    }

    /**
     * Object getter
     * @deprecated Simply do not use this.
     * @removal ILIAS 9
     */
    public function __get($value)
    {
        throw new BadMethodCallException('assQuestion::__get is discouraged, used with: ' . $value);
    }

    /**
     * Object setter
     * @deprecated Simply do not use this.
     * @removal ILIAS 9
     */
    public function __set($key, $value)
    {
        throw new BadMethodCallException('assQuestion::__set is discouraged, used with: ' . $key);
    }

    /**
     * Object issetter
     * @deprecated Simply do not use this.
     * @removal ILIAS 9
     */
    public function __isset($key)
    {
        throw new BadMethodCallException('assQuestion::__isset is discouraged, used with: ' . $key);
    }

    public function getNrOfTries() : int
    {
        return $this->nr_of_tries;
    }

    public function setNrOfTries(int $a_nr_of_tries) : void
    {
        $this->nr_of_tries = $a_nr_of_tries;
    }

    public function setExportImagePath(string $path) : void
    {
        $this->export_image_path = $path;
    }

    public static function _questionExistsInTest(int $question_id, int $test_id) : bool
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

    public function formatSAQuestion($a_q) : string
    {
        return $this->getSelfAssessmentFormatter()->format($a_q);
    }

    protected function getSelfAssessmentFormatter() : \ilAssSelfAssessmentQuestionFormatter
    {
        return new \ilAssSelfAssessmentQuestionFormatter();
    }

    // scorm2004-start ???

    public function setPreventRteUsage(bool $prevent_rte_usage) : void
    {
        $this->prevent_rte_usage = $prevent_rte_usage;
    }

    public function getPreventRteUsage() : bool
    {
        return $this->prevent_rte_usage;
    }

    public function migrateContentForLearningModule(ilAssSelfAssessmentMigrator $migrator) : void
    {
        $this->lmMigrateQuestionTypeGenericContent($migrator);
        $this->lmMigrateQuestionTypeSpecificContent($migrator);
        $this->saveToDb();

        $this->feedbackOBJ->migrateContentForLearningModule($migrator, $this->getId());
    }

    protected function lmMigrateQuestionTypeGenericContent(ilAssSelfAssessmentMigrator $migrator) : void
    {
        $this->setQuestion($migrator->migrateToLmContent($this->getQuestion()));
    }

    protected function lmMigrateQuestionTypeSpecificContent(ilAssSelfAssessmentMigrator $migrator) : void
    {
        // overwrite if any question type specific content except feedback needs to be migrated
    }

    public function setSelfAssessmentEditingMode(bool $selfassessmenteditingmode) : void
    {
        $this->selfassessmenteditingmode = $selfassessmenteditingmode;
    }

    public function getSelfAssessmentEditingMode() : bool
    {
        return $this->selfassessmenteditingmode;
    }

    public function setDefaultNrOfTries(int $defaultnroftries) : void
    {
        $this->defaultnroftries = $defaultnroftries;
    }

    public function getDefaultNrOfTries() : int
    {
        return $this->defaultnroftries;
    }

    // scorm2004-end ???

    public static function lookupParentObjId(int $questionId) : int
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT obj_fi FROM qpl_questions WHERE question_id = %s";

        $res = $ilDB->queryF($query, array('integer'), array($questionId));
        $row = $ilDB->fetchAssoc($res);

        return $row['obj_fi'];
    }

    /**
     * returns the parent object id for given original question id
     * (should be a qpl id, but theoretically it can be a tst id, too)
     *
     * @deprecated: use assQuestion::lookupParentObjId() instead
     */
    public static function lookupOriginalParentObjId(int $originalQuestionId) : int
    {
        return self::lookupParentObjId($originalQuestionId);
    }

    protected function duplicateQuestionHints(int $originalQuestionId, int $duplicateQuestionId) : void
    {
        $hintIds = ilAssQuestionHintList::duplicateListForQuestion($originalQuestionId, $duplicateQuestionId);

        if ($this->isAdditionalContentEditingModePageObject()) {
            foreach ($hintIds as $originalHintId => $duplicateHintId) {
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

    protected function duplicateSkillAssignments(int $srcParentId, int $srcQuestionId, int $trgParentId, int $trgQuestionId) : void
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
            ilSkillUsage::setUsage(
                $trgParentId,
                $assignment->getSkillBaseId(),
                $assignment->getSkillTrefId()
            );
        }
    }

    public function syncSkillAssignments(int $srcParentId, int $srcQuestionId, int $trgParentId, int $trgQuestionId) : void
    {
        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($trgParentId);
        $assignmentList->setQuestionIdFilter($trgQuestionId);
        $assignmentList->loadFromDb();

        foreach ($assignmentList->getAssignmentsByQuestionId($trgQuestionId) as $assignment) {
            $assignment->deleteFromDb();

            // remove skill usage
            if (!$assignment->isSkillUsed()) {
                ilSkillUsage::setUsage(
                    $assignment->getParentObjId(),
                    $assignment->getSkillBaseId(),
                    $assignment->getSkillTrefId(),
                    false
                );
            }
        }

        $this->duplicateSkillAssignments($srcParentId, $srcQuestionId, $trgParentId, $trgQuestionId);
    }

    /**
     * returns boolean wether the question
     * is answered during test pass or not
     *
     * method can be overwritten in derived classes,
     * but be aware of also overwrite the method
     * assQuestion::isObligationPossible()
     *
     */
    public function isAnswered(int $active_id, int $pass) : bool
    {
        return true;
    }

    /**
     * returns boolean wether it is possible to set
     * this question type as obligatory or not
     * considering the current question configuration
     *
     * method can be overwritten in derived classes,
     * but be aware of also overwrite the method
     * assQuestion::isAnswered()
     */
    public static function isObligationPossible(int $questionId) : bool
    {
        return false;
    }

    public function isAutosaveable() : bool
    {
        return true;
    }

    protected static function getNumExistingSolutionRecords(int $activeId, int $pass, int $questionId) : int
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

    public function getAdditionalContentEditingMode() : string
    {
        return $this->additionalContentEditingMode;
    }

    public function setAdditionalContentEditingMode(?string $additionalContentEditingMode) : void
    {
        if (!in_array((string) $additionalContentEditingMode, $this->getValidAdditionalContentEditingModes())) {
            throw new ilTestQuestionPoolException('invalid additional content editing mode given: ' . $additionalContentEditingMode);
        }

        $this->additionalContentEditingMode = $additionalContentEditingMode;
    }

    public function isAdditionalContentEditingModePageObject() : bool
    {
        return $this->getAdditionalContentEditingMode() == assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_PAGE_OBJECT;
    }

    public function isValidAdditionalContentEditingMode(string $additionalContentEditingMode) : bool
    {
        if (in_array($additionalContentEditingMode, $this->getValidAdditionalContentEditingModes())) {
            return true;
        }

        return false;
    }

    public function getValidAdditionalContentEditingModes() : array
    {
        return array(
            self::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT,
            self::ADDITIONAL_CONTENT_EDITING_MODE_PAGE_OBJECT
        );
    }

    /**
     * @return ilHtmlPurifierInterface|ilAssHtmlUserSolutionPurifier
     */
    public function getHtmlUserSolutionPurifier() : ilHtmlPurifierInterface
    {
        return ilHtmlPurifierFactory::getInstanceByType('qpl_usersolution');
    }

    /**
     * @return ilHtmlPurifierInterface|ilAssHtmlUserSolutionPurifier
     */
    public function getHtmlQuestionContentPurifier() : ilHtmlPurifierInterface
    {
        return ilHtmlPurifierFactory::getInstanceByType('qpl_usersolution');
    }

    protected function buildQuestionDataQuery() : string
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

    public function setLastChange($lastChange) : void
    {
        $this->lastChange = $lastChange;
    }

    public function getLastChange()
    {
        return $this->lastChange;
    }

    protected function getCurrentSolutionResultSet(int $active_id, int $pass, bool $authorized = true) : \ilDBStatement
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

    protected function removeSolutionRecordById(int $solutionId) : int
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
    protected function getSolutionRecordById(int $solutionId) : array
    {
        $result = $this->db->queryF(
            "SELECT * FROM tst_solutions WHERE solution_id = %s",
            array('integer'),
            array($solutionId)
        );

        if ($this->db->numRows($result) > 0) {
            return $this->db->fetchAssoc($result);
        }
        return array();
    }
    // hey.

    public function removeIntermediateSolution(int $active_id, int $pass) : void
    {
        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use ($active_id, $pass) {
            $this->removeCurrentSolution($active_id, $pass, false);
        });
    }

    /**
     * @return int Affected rows
     */
    public function removeCurrentSolution(int $active_id, int $pass, bool $authorized = true) : int
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
    public function saveCurrentSolution(int $active_id, int $pass, $value1, $value2, bool $authorized = true, int $tstamp = 0) : int
    {
        $next_id = $this->db->nextId("tst_solutions");

        $fieldData = array(
            "solution_id" => array("integer", $next_id),
            "active_fi" => array("integer", $active_id),
            "question_fi" => array("integer", $this->getId()),
            "value1" => array("clob", $value1),
            "value2" => array("clob", $value2),
            "pass" => array("integer", $pass),
            "tstamp" => array("integer", ($tstamp > 0) ? $tstamp : time()),
            'authorized' => array('integer', (int) $authorized)
        );

        if ($this->getStep() !== null) {
            $fieldData['step'] = array("integer", $this->getStep());
        }

        return $this->db->insert("tst_solutions", $fieldData);
    }
    // fau.

    public function updateCurrentSolution(int $solutionId, $value1, $value2, bool $authorized = true) : int
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
    public function updateCurrentSolutionsAuthorization(int $activeId, int $pass, bool $authorized, bool $keepTime = false) : int
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
    const KEY_VALUES_IMPLOSION_SEPARATOR = ':';

    public static function implodeKeyValues(array $keyValues) : string
    {
        return implode(assQuestion::KEY_VALUES_IMPLOSION_SEPARATOR, $keyValues);
    }

    public static function explodeKeyValues(string $keyValues) : array
    {
        return explode(assQuestion::KEY_VALUES_IMPLOSION_SEPARATOR, $keyValues);
    }

    protected function deleteDummySolutionRecord(int $activeId, int $passIndex) : void
    {
        foreach ($this->getSolutionValues($activeId, $passIndex, false) as $solutionRec) {
            if ($solutionRec['value1'] == '' && $solutionRec['value2'] == '') {
                $this->removeSolutionRecordById($solutionRec['solution_id']);
            }
        }
    }

    protected function isDummySolutionRecord(array $solutionRecord) : bool
    {
        return !strlen($solutionRecord['value1']) && !strlen($solutionRecord['value2']);
    }

    protected function deleteSolutionRecordByValues(int $activeId, int $passIndex, bool $authorized, array $matchValues) : void
    {
        $types = array("integer", "integer", "integer", "integer");
        $values = array($activeId, $this->getId(), $passIndex, (int) $authorized);
        $valuesCondition = array();

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

    protected function duplicateIntermediateSolutionAuthorized(int $activeId, int $passIndex) : void
    {
        foreach ($this->getSolutionValues($activeId, $passIndex, false) as $rec) {
            $this->saveCurrentSolution($activeId, $passIndex, $rec['value1'], $rec['value2'], true, $rec['tstamp']);
        }
    }

    protected function forceExistingIntermediateSolution(int $activeId, int $passIndex, bool $considerDummyRecordCreation) : void
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
                $this->saveCurrentSolution($activeId, $passIndex, null, null, false, null);
            }
        }
    }
    // hey.



    /**
     * @param int|null $step
     */
    public function setStep($step) : void
    {
        $this->step = $step;
    }

    /**
     * @return int|null
     */
    public function getStep() : ?int
    {
        return $this->step;
    }

    public static function sumTimesInISO8601FormatH_i_s_Extended(string $time1, string $time2) : string
    {
        $time = assQuestion::convertISO8601FormatH_i_s_ExtendedToSeconds($time1) +
                assQuestion::convertISO8601FormatH_i_s_ExtendedToSeconds($time2);
        return gmdate('H:i:s', $time);
    }

    public static function convertISO8601FormatH_i_s_ExtendedToSeconds(string $time) : int
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

    public function toJSON() : string
    {
        return json_encode(array());
    }

    abstract public function duplicate(bool $for_test = true, string $title = "", string $author = "", string $owner = "", $testObjId = null) : int;

    // hey: prevPassSolutions - check for authorized solution
    public function intermediateSolutionExists(int $active_id, int $pass) : bool
    {
        $solutionAvailability = $this->lookupForExistingSolutions($active_id, $pass);
        return (bool) $solutionAvailability['intermediate'];
    }

    public function authorizedSolutionExists(int $active_id, int $pass) : bool
    {
        $solutionAvailability = $this->lookupForExistingSolutions($active_id, $pass);
        return (bool) $solutionAvailability['authorized'];
    }

    public function authorizedOrIntermediateSolutionExists(int $active_id, int $pass) : bool
    {
        $solutionAvailability = $this->lookupForExistingSolutions($active_id, $pass);
        return $solutionAvailability['authorized'] || $solutionAvailability['intermediate'];
    }
    // hey.

    protected function lookupMaxStep(int $active_id, int $pass) : int
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
    public function lookupForExistingSolutions(int $activeId, int $pass) : array
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

    public function isAddableAnswerOptionValue(int $qIndex, string $answerOptionValue) : bool
    {
        return false;
    }

    public function addAnswerOptionValue(int $qIndex, string $answerOptionValue, float $points) : void
    {
    }

    public function removeAllExistingSolutions() : void
    {
        $query = "DELETE FROM tst_solutions WHERE question_fi = %s";
        $this->db->manipulateF($query, array('integer'), array($this->getId()));
    }

    public function removeExistingSolutions(int $activeId, int $pass) : int
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

    public function resetUsersAnswer(int $activeId, int $pass) : void
    {
        $this->removeExistingSolutions($activeId, $pass);
        $this->removeResultRecord($activeId, $pass);

        $this->log($activeId, "log_user_solution_willingly_deleted");

        self::_updateTestPassResults(
            $activeId,
            $pass,
            $this->areObligationsToBeConsidered(),
            $this->getProcessLocker(),
            $this->getTestId()
        );
    }

    public function removeResultRecord(int $activeId, int $pass) : int
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

    public static function missingResultRecordExists(int $activeId, int $pass, array $questionIds) : bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $IN_questionIds = $ilDB->in('question_fi', $questionIds, false, 'integer');

        $query = "
			SELECT COUNT(*) cnt
			FROM tst_test_result
			WHERE active_fi = %s
			AND pass = %s
			AND $IN_questionIds
		";

        $row = $ilDB->fetchAssoc($ilDB->queryF(
            $query,
            array('integer', 'integer'),
            array($activeId, $pass)
        ));

        return $row['cnt'] < count($questionIds);
    }

    public static function getQuestionsMissingResultRecord(int $activeId, int $pass, array $questionIds) : array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $IN_questionIds = $ilDB->in('question_fi', $questionIds, false, 'integer');

        $query = "
			SELECT question_fi
			FROM tst_test_result
			WHERE active_fi = %s
			AND pass = %s
			AND $IN_questionIds
		";

        $res = $ilDB->queryF(
            $query,
            array('integer', 'integer'),
            array($activeId, $pass)
        );

        $questionsHavingResultRecord = array();

        while ($row = $ilDB->fetchAssoc($res)) {
            $questionsHavingResultRecord[] = $row['question_fi'];
        }

        $questionsMissingResultRecordt = array_diff(
            $questionIds,
            $questionsHavingResultRecord
        );

        return $questionsMissingResultRecordt;
    }

    public static function lookupResultRecordExist(int $activeId, int $questionId, int $pass) : bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "
			SELECT COUNT(*) cnt
			FROM tst_test_result
			WHERE active_fi = %s
			AND question_fi = %s
			AND pass = %s
		";

        $row = $ilDB->fetchAssoc($ilDB->queryF($query, array('integer', 'integer', 'integer'), array($activeId, $questionId, $pass)));

        return $row['cnt'] > 0;
    }

    public function fetchValuePairsFromIndexedValues(array $indexedValues) : array
    {
        $valuePairs = array();

        foreach ($indexedValues as $value1 => $value2) {
            $valuePairs[] = array('value1' => $value1, 'value2' => $value2);
        }

        return $valuePairs;
    }

    public function fetchIndexedValuesFromValuePairs(array $valuePairs) : array
    {
        $indexedValues = array();

        foreach ($valuePairs as $valuePair) {
            $indexedValues[ $valuePair['value1'] ] = $valuePair['value2'];
        }

        return $indexedValues;
    }

    public function areObligationsToBeConsidered() : bool
    {
        return $this->obligationsToBeConsidered;
    }

    public function setObligationsToBeConsidered(bool $obligationsToBeConsidered) : void
    {
        $this->obligationsToBeConsidered = $obligationsToBeConsidered;
    }

    public function updateTimestamp() : void
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

    public function getTestPresentationConfig() : ilTestQuestionConfig
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
    protected function buildTestPresentationConfig() : ilTestQuestionConfig
    {
        return new ilTestQuestionConfig();
    }
    // hey.
    // fau.

    public function savePartial() : bool
    {
        return false;
    }


    /* doubles isInUse? */
    public function isInActiveTest() : bool
    {
        $query = 'SELECT user_fi FROM tst_active ' . PHP_EOL
            . 'JOIN tst_test_question ON tst_test_question.test_fi = tst_active.test_fi ' . PHP_EOL
            . 'JOIN qpl_questions ON qpl_questions.question_id = tst_test_question.question_fi ' . PHP_EOL
            . 'WHERE qpl_questions.obj_fi = ' . $this->db->quote($this->getObjId(), 'integer');

        $res = $this->db->query($query);
        return $res->numRows() > 0;
    }
}
