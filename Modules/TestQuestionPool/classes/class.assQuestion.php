<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

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

    /**
    * Question id
    *
    * @var integer
    */
    protected $id;

    /**
    * Question title
    *
    * @var string
    */
    protected $title;

    /**
    * Question comment
    *
    * @var string
    */
    protected $comment;

    /**
    * Question owner/creator
    *
    * @var integer
    */
    protected $owner;

    /**
    * Contains the name of the author
    *
    * @var string
    */
    protected $author;

    /**
    * The question text
    *
    * @var string
    */
    protected $question;

    /**
    * The maximum available points for the question
    *
    * @var double
    */
    protected $points;

    /**
    * Contains estimates working time on a question (HH MM SS)
    *
    * @var array
    */
    protected $est_working_time;

    /**
    * Indicates whether the answers will be shuffled or not
    *
    * @var boolean
    */
    protected $shuffle;

    /**
    * The database id of a test in which the question is contained
    *
    * @var integer
    */
    protected $test_id;

    /**
    * Object id of the container object
    *
    * @var integer
    */
    protected $obj_id;

    /**
    * The reference to the ILIAS class
    *
    * @var object
    */
    protected $ilias;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
    * Contains the output type of a question
    *
    * @var integer
    */
    protected $outputType = OUTPUT_JAVASCRIPT;

    /**
    * Array of suggested solutions
    *
    * @var array
    */
    protected $suggested_solutions;

    /**
    * ID of the "original" question
    *
    * @var integer
    */
    protected $original_id;

    /**
    * Page object
    *
    * @var object
    */
    protected $page;

    /**
    * Number of tries
    */
    private $nr_of_tries;
    
    /**
    * Associative array to store properties
    */
    private $arrData;

    /**
     * (Web) Path to images
     */
    private $export_image_path;

    /**
     * An external id of a qustion
     * @var string
     */
    protected $external_id = '';
    
    /**
     * constant for additional content editing mode "default"
     */
    const ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT = 'default';

    /**
     * constant for additional content editing mode "pageobject"
     */
    const ADDITIONAL_CONTENT_EDITING_MODE_PAGE_OBJECT = 'pageobject';
    
    /**
     * additional content editing mode set for this question
     *
     * @var string
     */
    private $additinalContentEditingMode = null;
    
    /**
     * feedback object for question
     *
     * @var ilAssQuestionFeedback
     */
    public $feedbackOBJ = null;

    /**
     * do not use rte for editing
     *
     * @var boolean
     */
    public $prevent_rte_usage = false;

    /**
     * $selfassessmenteditingmode
     *
     * @var boolean
     */
    public $selfassessmenteditingmode = false;

    /**
     * $defaultnroftries
     *
     * @var int
     */
    public $defaultnroftries = 0;
    
    /**
     * @var array[ilQuestionChangeListener]
     */
    protected $questionChangeListeners = array();

    /**
     * @var ilAssQuestionProcessLocker
     */
    protected $processLocker;

    public $questionActionCmd = 'handleQuestionAction';

    /**
     * @var ilObjTestGateway
     */
    private static $resultGateway = null;

    /**
     * @var null|int
     */
    protected $step = null;
    
    protected $lastChange;

    /**
     * @var ilArrayElementShuffler
     */
    protected $shuffler;

    /**
     * @var bool
     */
    private $obligationsToBeConsidered = false;
    
    // fau: testNav - new variable $testQuestionConfig
    /**
     * @var ilTestQuestionConfig
     */
    protected $testQuestionConfig;
    // fau.
    
    /**
     * @var ilAssQuestionLifecycle
     */
    protected $lifecycle;
    
    protected static $allowedImageMaterialFileExtensionsByMimeType = array(
        'image/jpeg' => array('jpg', 'jpeg'), 'image/png' => array('png'), 'image/gif' => array('gif')
    );
    
    /**
    * assQuestion constructor
    *
    * @param string $title A title string to describe the question
    * @param string $comment A comment string to describe the question
    * @param string $author A string containing the name of the questions author
    * @param integer $owner A numerical ID to identify the owner/creator
    * @param string $question Question text
    * @access public
    */
    public function __construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = ""
    ) {
        global $DIC;
        $ilias = $DIC['ilias'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilDB = $DIC['ilDB'];

        $this->ilias = $ilias;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->db = $ilDB;

        $this->original_id = null;
        $this->title = $title;
        $this->comment = $comment;
        $this->page = null;
        $this->author = $author;
        $this->setQuestion($question);
        if (!$this->author) {
            $this->author = $this->ilias->account->fullname;
        }
        $this->owner = $owner;
        if ($this->owner <= 0) {
            $this->owner = $this->ilias->account->id;
        }
        $this->id = -1;
        $this->test_id = -1;
        $this->suggested_solutions = array();
        $this->shuffle = 1;
        $this->nr_of_tries = 0;
        $this->setEstimatedWorkingTime(0, 1, 0);
        $this->arrData = array();
        $this->setExternalId('');

        $this->questionActionCmd = 'handleQuestionAction';
        
        $this->lastChange = null;

        require_once 'Services/Randomization/classes/class.ilArrayElementOrderKeeper.php';
        $this->shuffler = new ilArrayElementOrderKeeper();
        
        $this->lifecycle = ilAssQuestionLifecycle::getDraftInstance();
    }
    
    protected static $forcePassResultsUpdateEnabled = false;
    
    public static function setForcePassResultUpdateEnabled($forcePassResultsUpdateEnabled)
    {
        self::$forcePassResultsUpdateEnabled = $forcePassResultsUpdateEnabled;
    }
    
    public static function isForcePassResultUpdateEnabled()
    {
        return self::$forcePassResultsUpdateEnabled;
    }

    public static function isAllowedImageMimeType($mimeType)
    {
        return (bool) count(self::getAllowedFileExtensionsForMimeType($mimeType));
    }

    public static function fetchMimeTypeIdentifier($contentTypeString)
    {
        return current(explode(';', $contentTypeString));
    }

    public static function getAllowedFileExtensionsForMimeType($mimeType)
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

    public static function isAllowedImageFileExtension($mimeType, $fileExtension)
    {
        return in_array(
            strtolower($fileExtension),
            self::getAllowedFileExtensionsForMimeType($mimeType)
        );
    }
    
    // hey: prevPassSolutions - question action actracted (heavy use in fileupload refactoring)
    
    /**
     * @return string
     */
    protected function getQuestionAction()
    {
        if (!isset($_POST['cmd']) || !isset($_POST['cmd'][$this->questionActionCmd])) {
            return '';
        }
        
        if (!is_array($_POST['cmd'][$this->questionActionCmd]) || !count($_POST['cmd'][$this->questionActionCmd])) {
            return '';
        }
        
        return key($_POST['cmd'][$this->questionActionCmd]);
    }
    
    /**
     * @param string $postSubmissionFieldname
     * @return bool
     */
    protected function isNonEmptyItemListPostSubmission($postSubmissionFieldname)
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
    
    /**
     * @param $active_id
     * @param $pass
     * @return int
     */
    protected function ensureCurrentTestPass($active_id, $pass)
    {
        if (is_integer($pass) && $pass >= 0) {
            return $pass;
        }
        
        return $this->lookupCurrentTestPass($active_id, $pass);
    }
    
    /**
     * @param $active_id
     * @param $pass
     * @return int
     */
    protected function lookupCurrentTestPass($active_id, $pass)
    {
        require_once 'Modules/Test/classes/class.ilObjTest.php';
        return ilObjTest::_getPass($active_id);
    }
    
    /**
     * @param $active_id
     * @return int
     */
    protected function lookupTestId($active_id)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $ilDB = $DIC['ilDB'];
        
        $result = $ilDB->queryF(
            "SELECT test_fi FROM tst_active WHERE active_id = %s",
            array('integer'),
            array($active_id)
        );
        
        while ($row = $ilDB->fetchAssoc($result)) {
            return $row["test_fi"];
        }
        
        return null;
    }
    // hey.
    
    /**
     * @param integer $active_id
     * @param string $langVar
     */
    protected function log($active_id, $langVar)
    {
        if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            $message = $this->lng->txtlng('assessment', $langVar, ilObjAssessmentFolder::_getLogLanguage());
            assQuestion::logAction($message, $active_id, $this->getId());
        }
    }

    /**
     * @return array	all allowed file extensions for image material
     */
    public static function getAllowedImageMaterialFileExtensions()
    {
        $extensions = array();

        foreach (self::$allowedImageMaterialFileExtensionsByMimeType as $mimeType => $mimeExtensions) {
            $extensions = array_merge($extensions, $mimeExtensions);
        }
        return array_unique($extensions);
    }

    /**
     * @return ilArrayElementShuffler
     */
    public function getShuffler()
    {
        return $this->shuffler;
    }

    /**
     * @param ilArrayElementShuffler $shuffler
     */
    public function setShuffler(ilArrayElementShuffler $shuffler)
    {
        $this->shuffler = $shuffler;
    }

    /**
     * @param \ilAssQuestionProcessLocker $processLocker
     */
    public function setProcessLocker($processLocker)
    {
        $this->processLocker = $processLocker;
    }

    /**
     * @return \ilAssQuestionProcessLocker
     */
    public function getProcessLocker()
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
    * @access public
    */
    public function fromXML(&$item, &$questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
    {
        include_once "./Modules/TestQuestionPool/classes/import/qti12/class." . $this->getQuestionType() . "Import.php";
        $classname = $this->getQuestionType() . "Import";
        $import = new $classname($this);
        $import->fromXML($item, $questionpool_id, $tst_id, $tst_object, $question_counter, $import_mapping);
    }
    
    /**
    * Returns a QTI xml representation of the question
    *
    * @return string The QTI xml representation of the question
    * @access public
    */
    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
    {
        include_once "./Modules/TestQuestionPool/classes/export/qti12/class." . $this->getQuestionType() . "Export.php";
        $classname = $this->getQuestionType() . "Export";
        $export = new $classname($this);
        return $export->toXML($a_include_header, $a_include_binary, $a_shuffle, $test_output, $force_image_references);
    }

    /**
    * Returns true, if a question is complete for use
    *
    * @return boolean True, if the question is complete for use, otherwise false
    * @access public
    */
    public function isComplete()
    {
        return false;
    }

    /**
    * Returns TRUE if the question title exists in the database
    *
    * @param string $title The title of the question
    * @return boolean The result of the title check
    * @access public
    */
    public function questionTitleExists($questionpool_id, $title)
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

    /**
    * Sets the title string of the assQuestion object
    *
    * @param string $title A title string to describe the question
    * @access public
    * @see $title
    */
    public function setTitle($title = "")
    {
        $this->title = $title;
    }

    /**
    * Sets the id of the assQuestion object
    *
    * @param integer $id A unique integer value
    * @access public
    * @see $id
    */
    public function setId($id = -1)
    {
        $this->id = $id;
    }

    /**
    * Sets the test id of the assQuestion object
    *
    * @param integer $id A unique integer value
    * @access public
    * @see $test_id
    */
    public function setTestId($id = -1)
    {
        $this->test_id = $id;
    }

    /**
    * Sets the comment string of the assQuestion object
    *
    * @param string $comment A comment string to describe the question
    * @access public
    * @see $comment
    */
    public function setComment($comment = "")
    {
        $this->comment = $comment;
    }

    /**
    * Sets the output type
    *
    * @param integer $outputType The output type of the question
    * @access public
    * @see $outputType
    */
    public function setOutputType($outputType = OUTPUT_HTML)
    {
        $this->outputType = $outputType;
    }


    /**
    * Sets the shuffle flag
    *
    * @param boolean $shuffle A flag indicating whether the answers are shuffled or not
    * @access public
    * @see $shuffle
    */
    public function setShuffle($shuffle = true)
    {
        if ($shuffle) {
            $this->shuffle = 1;
        } else {
            $this->shuffle = 0;
        }
    }

    /**
     * Sets the estimated working time of a question
     * from given hour, minute and second
     *
     * @param integer $hour Hour
     * @param integer $min Minutes
     * @param integer $sec Seconds
     * @access public
     * @see $comment
     */
    public function setEstimatedWorkingTime($hour = 0, $min = 0, $sec = 0)
    {
        $this->est_working_time = array("h" => (int) $hour, "m" => (int) $min, "s" => (int) $sec);
    }

    /**
     * Sets the estimated working time of a question
     * from a given datetime string
     *
     * @param string $datetime
     */
    public function setEstimatedWorkingTimeFromDurationString($durationString)
    {
        $this->est_working_time = array(
            'h' => (int) substr($durationString, 0, 2),
            'm' => (int) substr($durationString, 3, 2),
            's' => (int) substr($durationString, 6, 2)
        );
    }

    /**
    * returns TRUE if the key occurs in an array
    *
    * @param string $arraykey A key to an element in array
    * @param array $array An array to be searched
    * @access public
    */
    public function keyInArray($searchkey, $array)
    {
        if ($searchkey) {
            foreach ($array as $key => $value) {
                if (strcmp($key, $searchkey) == 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
    * Sets the authors name of the assQuestion object
    *
    * @param string $author A string containing the name of the questions author
    * @access public
    * @see $author
    */
    public function setAuthor($author = "")
    {
        if (!$author) {
            $author = $this->ilias->account->fullname;
        }
        $this->author = $author;
    }

    /**
    * Sets the creator/owner ID of the assQuestion object
    *
    * @param integer $owner A numerical ID to identify the owner/creator
    * @access public
    * @see $owner
    */
    public function setOwner($owner = "")
    {
        $this->owner = $owner;
    }

    /**
    * Gets the title string of the assQuestion object
    *
    * @return string The title string to describe the question
    * @access public
    * @see $title
    */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * returns the object title prepared to be used as a filename
     *
     * @return string
     */
    public function getTitleFilenameCompliant()
    {
        require_once 'Services/Utilities/classes/class.ilUtil.php';
        return ilUtil::getASCIIFilename($this->getTitle());
    }

    /**
    * Gets the id of the assQuestion object
    *
    * @return integer The id of the assQuestion object
    * @access public
    * @see $id
    */
    public function getId()
    {
        return $this->id;
    }

    /**
    * Gets the shuffle flag
    *
    * @return boolean The shuffle flag
    * @access public
    * @see $shuffle
    */
    public function getShuffle()
    {
        return $this->shuffle;
    }

    /**
    * Gets the test id of the assQuestion object
    *
    * @return integer The test id of the assQuestion object
    * @access public
    * @see $test_id
    */
    public function getTestId()
    {
        return $this->test_id;
    }

    /**
    * Gets the comment string of the assQuestion object
    *
    * @return string The comment string to describe the question
    * @access public
    * @see $comment
    */
    public function getComment()
    {
        return $this->comment;
    }

    /**
    * Gets the output type
    *
    * @return integer The output type of the question
    * @access public
    * @see $outputType
    */
    public function getOutputType()
    {
        return $this->outputType;
    }
    
    /**
    * Returns true if the question type supports JavaScript output
    *
    * @return boolean TRUE if the question type supports JavaScript output, FALSE otherwise
    * @access public
    */
    public function supportsJavascriptOutput()
    {
        return false;
    }

    public function supportsNonJsOutput()
    {
        return true;
    }
    
    public function requiresJsSwitch()
    {
        return $this->supportsJavascriptOutput() && $this->supportsNonJsOutput();
    }

    /**
    * Gets the estimated working time of a question
    *
    * @return array Estimated Working Time of a question
    * @access public
    * @see $est_working_time
    */
    public function getEstimatedWorkingTime()
    {
        if (!$this->est_working_time) {
            $this->est_working_time = array("h" => 0, "m" => 0, "s" => 0);
        }
        return $this->est_working_time;
    }

    /**
    * Gets the authors name of the assQuestion object
    *
    * @return string The string containing the name of the questions author
    * @access public
    * @see $author
    */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
    * Gets the creator/owner ID of the assQuestion object
    *
    * @return integer The numerical ID to identify the owner/creator
    * @access public
    * @see $owner
    */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
    * Get the object id of the container object
    *
    * @return integer The object id of the container object
    * @access public
    * @see $obj_id
    */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
    * Set the object id of the container object
    *
    * @param integer $obj_id The object id of the container object
    * @access public
    * @see $obj_id
    */
    public function setObjId($obj_id = 0)
    {
        $this->obj_id = $obj_id;
    }
    
    /**
     * @return ilAssQuestionLifecycle
     */
    public function getLifecycle()
    {
        return $this->lifecycle;
    }
    
    /**
     * @param ilAssQuestionLifecycle $lifecycle
     */
    public function setLifecycle(ilAssQuestionLifecycle $lifecycle)
    {
        $this->lifecycle = $lifecycle;
    }

    /**
     * @param string $external_id
     */
    public function setExternalId($external_id)
    {
        $this->external_id = $external_id;
    }

    /**
     * @return string
     */
    public function getExternalId()
    {
        if (!strlen($this->external_id)) {
            if ($this->getId() > 0) {
                return 'il_' . IL_INST_ID . '_qst_' . $this->getId();
            } else {
                return uniqid('', true);
            }
        } else {
            return $this->external_id;
        }
    }

    /**
    * Returns the maximum points, a learner can reach answering the question
    *
    * @param integer $question_id The database Id of the question
    * @see $points
    */
    public static function _getMaximumPoints($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $points = 0;
        $result = $ilDB->queryF(
            "SELECT points FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            $points = $row["points"];
        }
        return $points;
    }

    /**
    * Returns question information from the database
    *
    * @param integer $question_id The database Id of the question
    * @return array The database row containing the question data
    */
    public static function _getQuestionInfo($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT qpl_questions.*, qpl_qst_type.type_tag FROM qpl_qst_type, qpl_questions WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows()) {
            return $ilDB->fetchAssoc($result);
        } else {
            return array();
        }
    }
    
    /**
    * Returns the number of suggested solutions associated with a question
    *
    * @param integer $question_id The database Id of the question
    * @return integer The number of suggested solutions
    */
    public static function _getSuggestedSolutionCount($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT suggested_solution_id FROM qpl_sol_sug WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        return $result->numRows();
    }

    /**
    * Returns the output of the suggested solution
    *
    * @param integer $question_id The database Id of the question
    * @return string Suggested solution
    */
    public static function _getSuggestedSolutionOutput($question_id)
    {
        $question = &assQuestion::_instanciateQuestion($question_id);
        if (!is_object($question)) {
            return "";
        }
        return $question->getSuggestedSolutionOutput();
    }

    public function getSuggestedSolutionOutput()
    {
        $output = array();
        foreach ($this->suggested_solutions as $solution) {
            switch ($solution["type"]) {
                case "lm":
                case "st":
                case "pg":
                case "git":
                    array_push($output, '<a href="' . assQuestion::_getInternalLinkHref($solution["internal_link"]) . '">' . $this->lng->txt("solution_hint") . '</a>');
                    break;
                case "file":
                    $possible_texts = array_values(array_filter(array(
                        ilUtil::prepareFormOutput($solution['value']['filename']),
                        ilUtil::prepareFormOutput($solution['value']['name']),
                        $this->lng->txt('tst_show_solution_suggested')
                    )));

                    require_once 'Services/WebAccessChecker/classes/class.ilWACSignedPath.php';
                    ilWACSignedPath::setTokenMaxLifetimeInSeconds(60);
                    array_push($output, '<a href="' . ilWACSignedPath::signFile($this->getSuggestedSolutionPathWeb() . $solution["value"]["name"]) . '">' . $possible_texts[0] . '</a>');
                    break;
                case "text":
                    $solutionValue = $solution["value"];
                    $solutionValue = $this->fixSvgToPng($solutionValue);
                    $solutionValue = $this->fixUnavailableSkinImageSources($solutionValue);
                    $output[] = $this->prepareTextareaOutput($solutionValue, true);
                    break;
            }
        }
        return join("<br />", $output);
    }

    /**
    * Returns a suggested solution for a given subquestion index
    *
    * @param integer $question_id The database Id of the question
    * @param integer $subquestion_index The index of a subquestion (i.e. a close test gap). Usually 0
    * @return array A suggested solution array containing the internal link
    * @access public
    */
    public function &_getSuggestedSolution($question_id, $subquestion_index = 0)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT * FROM qpl_sol_sug WHERE question_fi = %s AND subquestion_index = %s",
            array('integer','integer'),
            array($question_id, $subquestion_index)
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            return array(
                "internal_link" => $row["internal_link"],
                "import_id" => $row["import_id"]
            );
        } else {
            return array();
        }
    }
    
    /**
    * Return the suggested solutions
    *
    * @return array Suggested solutions
    */
    public function getSuggestedSolutions()
    {
        return $this->suggested_solutions;
    }
    
    /**
    * Returns the points, a learner has reached answering the question
    *
    * @param integer $user_id The database ID of the learner
    * @param integer $test_id The database Id of the test containing the question
    * @param integer $question_id The database Id of the question
    */
    public static function _getReachedPoints($active_id, $question_id, $pass = null)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $points = 0;
        if (is_null($pass)) {
            include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
            $pass = assQuestion::_getSolutionMaxPass($question_id, $active_id);
        }
        $result = $ilDB->queryF(
            "SELECT * FROM tst_test_result WHERE active_fi = %s AND question_fi = %s AND pass = %s",
            array('integer','integer','integer'),
            array($active_id, $question_id, $pass)
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            $points = $row["points"];
        }
        return $points;
    }

    /**
    * Returns the points, a learner has reached answering the question
    * This is the fast way to get the points directly from the database.
    *
    * @param integer $user_id The database ID of the learner
    * @param integer $test_id The database Id of the test containing the question
    * @access public
    */
    public function getReachedPoints($active_id, $pass = null)
    {
        return round(self::_getReachedPoints($active_id, $this->getId(), $pass), 2);
    }
    
    /**
    * Returns the maximum points, a learner can reach answering the question
    *
    * @access public
    * @see $points
    */
    public function getMaximumPoints()
    {
        return $this->points;
    }
        
    /**
     *  returns the reached points ...
     * - calculated by concrete question type class
     * - adjusted by hint point deduction
     * - adjusted by scoring options
     * ... for given testactive and testpass
     *
     * @param integer $active_id
     * @param integer $pass
     * @return integer $reached_points
     */
    final public function getAdjustedReachedPoints($active_id, $pass = null, $authorizedSolution = true)
    {
        if (is_null($pass)) {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $pass = ilObjTest::_getPass($active_id);
        }
            
        // determine reached points for submitted solution
        $reached_points = $this->calculateReachedPoints($active_id, $pass, $authorizedSolution);
            
            

        // deduct points for requested hints from reached points
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintTracking.php';
        $hintTracking = new ilAssQuestionHintTracking($this->getId(), $active_id, $pass);
        $requestsStatisticData = $hintTracking->getRequestStatisticDataByQuestionAndTestpass();
        $reached_points = $reached_points - $requestsStatisticData->getRequestsPoints();

        // adjust reached points regarding to tests scoring options
        $reached_points = $this->adjustReachedPointsByScoringOptions($reached_points, $active_id, $pass);
            
        return $reached_points;
    }
    
    /**
     * Calculates the question results from a previously saved question solution
     *
     * @final
     * @global ilDBInterface $ilDB
     * @global ilObjUser $ilUser
     * @param integer $active_id Active id of the user
     * @param integer $pass Test pass
     */
    final public function calculateResultsFromSolution($active_id, $pass = null, $obligationsEnabled = false)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        
        if (is_null($pass)) {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $pass = ilObjTest::_getPass($active_id);
        }
        
        // determine reached points for submitted solution
        $reached_points = $this->calculateReachedPoints($active_id, $pass);

        // deduct points for requested hints from reached points
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintTracking.php';
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
            $reached_points = 0;
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
        // fau.

        include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
        
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

        // Update objective status
        include_once 'Modules/Course/classes/class.ilCourseObjectiveResult.php';
        ilCourseObjectiveResult::_updateObjectiveResult($ilUser->getId(), $active_id, $this->getId());
    }

    /**
     * persists the working state for current testactive and testpass
     *
     * @final
     * @access public
     * @param integer $active_id Active id of the user
     * @param integer $pass Test pass
     */
    final public function persistWorkingState($active_id, $pass = null, $obligationsEnabled = false, $authorized = true)
    {
        if (!$this->validateSolutionSubmit() && !$this->savePartial()) {
            return false;
        }

        $saveStatus = false;

        $this->getProcessLocker()->executePersistWorkingStateLockOperation(function () use ($active_id, $pass, $authorized, $obligationsEnabled, &$saveStatus) {
            if ($pass === null) {
                require_once 'Modules/Test/classes/class.ilObjTest.php';
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
    final public function persistPreviewState(ilAssQuestionPreviewSession $previewSession)
    {
        $this->savePreviewData($previewSession);
        return $this->validateSolutionSubmit();
    }
    
    public function validateSolutionSubmit()
    {
        return true;
    }
    
    /**
     * Saves the learners input of the question to the database.
     *
     * @abstract
     * @access public
     * @param integer $active_id Active id of the user
     * @param integer $pass Test pass
     * @return boolean $status
     */
    abstract public function saveWorkingData($active_id, $pass = null, $authorized = true);

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession)
    {
        $previewSession->setParticipantsSolution($this->getSolutionSubmit());
    }
    
    /** @TODO Move this to a proper place. */
    public static function _updateTestResultCache($active_id, ilAssQuestionProcessLocker $processLocker = null)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        include_once "./Modules/Test/classes/class.ilObjTest.php";
        include_once "./Modules/Test/classes/class.assMarkSchema.php";
        
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
        
        $max = $row['maxpoints'];
        $reached = $row['points'];
        
        $obligationsAnswered = (int) $row['obligations_answered'];
        
        include_once "./Modules/Test/classes/class.assMarkSchema.php";
        
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
    public static function _updateTestPassResults($active_id, $pass, $obligationsEnabled = false, ilAssQuestionProcessLocker $processLocker = null, $test_obj_id = null)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        include_once "./Modules/Test/classes/class.ilObjTest.php";

        if (self::getResultGateway() !== null) {
            $data = self::getResultGateway()->getQuestionCountAndPointsForPassOfParticipant($active_id, $pass);
            $time = self::getResultGateway()->getWorkingTimeOfParticipantForPass($active_id, $pass);
        } else {
            $data = ilObjTest::_getQuestionCountAndPointsForPassOfParticipant($active_id, $pass);
            $time = ilObjTest::_getWorkingTimeOfParticipantForPass($active_id, $pass);
        }

        
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
                $row['reachedpoints'] = 0;
            }
            if ($row['hint_count'] === null) {
                $row['hint_count'] = 0;
            }
            if ($row['hint_points'] === null) {
                $row['hint_points'] = 0;
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
                        'points' => array('float', $row['reachedpoints'] ? $row['reachedpoints'] : 0),
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

            if (is_object($processLocker)) {
                $processLocker->executeUserPassResultUpdateLockOperation($updatePassResultCallback);
            } else {
                $updatePassResultCallback();
            }
        }
        
        assQuestion::_updateTestResultCache($active_id, $processLocker);
        
        return array(
            'active_fi' => $active_id,
            'pass' => $pass,
            'points' => ($row["reachedpoints"]) ? $row["reachedpoints"] : 0,
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

    /**
     * Logs an action into the Test&Assessment log
     *
     * @param string $logtext The log text
     * @param int|string $active_id
     * @param int|string $question_id If given, saves the question id to the database
     */
    public static function logAction($logtext = "", $active_id = "", $question_id = "")
    {
        $original_id = "";
        if (strlen($question_id)) {
            $original_id = self::_getOriginalId($question_id);
        }
        
        require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
        require_once 'Modules/Test/classes/class.ilObjTest.php';
        
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
    * @access public
    */
    public function moveUploadedMediaFile($file, $name)
    {
        $mediatempdir = CLIENT_WEB_DIR . "/assessment/temp";
        if (!@is_dir($mediatempdir)) {
            ilUtil::createDirectory($mediatempdir);
        }
        $temp_name = tempnam($mediatempdir, $name . "_____");
        $temp_name = str_replace("\\", "/", $temp_name);
        @unlink($temp_name);
        if (!ilUtil::moveUploadedFile($file, $name, $temp_name)) {
            return false;
        } else {
            return $temp_name;
        }
    }
    
    /**
    * Returns the path for a suggested solution
    *
    * @access public
    */
    public function getSuggestedSolutionPath()
    {
        return CLIENT_WEB_DIR . "/assessment/$this->obj_id/$this->id/solution/";
    }

    /**
    * Returns the image path for web accessable images of a question.
    * The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
    *
    * @access public
    */
    public function getJavaPath()
    {
        return CLIENT_WEB_DIR . "/assessment/$this->obj_id/$this->id/java/";
    }
    
    /**
    * Returns the image path for web accessable images of a question.
    * The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
    *
    * @access public
    */
    public function getImagePath($question_id = null, $object_id = null)
    {
        if ($question_id === null) {
            $question_id = $this->id;
        }
        
        if ($object_id === null) {
            $object_id = $this->obj_id;
        }
        
        return $this->buildImagePath($question_id, $object_id);
    }
    
    public function buildImagePath($questionId, $parentObjectId)
    {
        return CLIENT_WEB_DIR . "/assessment/{$parentObjectId}/{$questionId}/images/";
    }

    /**
    * Returns the image path for web accessable flash files of a question.
    * The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/flash
    *
    * @access public
    */
    public function getFlashPath()
    {
        return CLIENT_WEB_DIR . "/assessment/$this->obj_id/$this->id/flash/";
    }

    /**
    * Returns the web image path for web accessable java applets of a question.
    * The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/java
    *
    * @access public
    */
    public function getJavaPathWeb()
    {
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        $webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/$this->obj_id/$this->id/java/";
        return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
    }

    /**
    * Returns the web path for a suggested solution
    *
    * @access public
    */
    public function getSuggestedSolutionPathWeb()
    {
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        $webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/$this->obj_id/$this->id/solution/";
        return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
    }

    /**
     * Returns the web image path for web accessable images of a question.
     * The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
     *
     * @access public
     *
     * TODO: in use? refactor and ask for a supported path in all cases, not for THE dynamic highlander path ^^
     */
    public function getImagePathWeb()
    {
        if (!$this->export_image_path) {
            include_once "./Services/Utilities/classes/class.ilUtil.php";
            $webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/$this->obj_id/$this->id/images/";
            return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
        } else {
            return $this->export_image_path;
        }
    }

    /**
    * Returns the web image path for web accessable flash applications of a question.
    * The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/flash
    *
    * @access public
    */
    public function getFlashPathWeb()
    {
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        $webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/$this->obj_id/$this->id/flash/";
        return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
    }

    // hey: prevPassSolutions - accept and prefer intermediate only from current pass
    public function getTestOutputSolutions($activeId, $pass)
    {
        // hey: refactored identifiers
        if ($this->getTestPresentationConfig()->isSolutionInitiallyPrefilled()) {
            // hey.
            return $this->getSolutionValues($activeId, $pass, true);
        }
        
        return $this->getUserSolutionPreferingIntermediate($activeId, $pass);
    }
    // hey.
    
    public function getUserSolutionPreferingIntermediate($active_id, $pass = null)
    {
        $solution = $this->getSolutionValues($active_id, $pass, false);
        
        if (!count($solution)) {
            $solution = $this->getSolutionValues($active_id, $pass, true);
        }
        
        return $solution;
    }
    
    /**
    * Loads solutions of a given user from the database an returns it
    */
    public function getSolutionValues($active_id, $pass = null, $authorized = true)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
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
            
            $result = $ilDB->queryF(
                $query,
                array('integer', 'integer', 'integer', 'integer', 'integer'),
                array($active_id, $this->getId(), $pass, $this->getStep(), (int) $authorized)
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
            
            $result = $ilDB->queryF(
                $query,
                array('integer', 'integer', 'integer', 'integer'),
                array($active_id, $this->getId(), $pass, (int) $authorized)
            );
        }

        $values = array();

        while ($row = $ilDB->fetchAssoc($result)) {
            $values[] = $row;
        }

        return $values;
    }

    /**
    * Checks whether the question is in use or not
    *
    * @return boolean The number of datasets which are affected by the use of the query.
    * @access public
    */
    public function isInUse($question_id = "")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        if ($question_id < 1) {
            $question_id = $this->getId();
        }
        $result = $ilDB->queryF(
            "SELECT COUNT(qpl_questions.question_id) question_count FROM qpl_questions, tst_test_question WHERE qpl_questions.original_id = %s AND qpl_questions.question_id = tst_test_question.question_fi",
            array('integer'),
            array($question_id)
        );
        $row = $ilDB->fetchAssoc($result);
        $count = $row["question_count"];

        $result = $ilDB->queryF(
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
        $count += $result->numRows();

        return $count;
    }

    /**
    * Checks whether the question is a clone of another question or not
    *
    * @return boolean TRUE if the question is a clone, otherwise FALSE
    * @access public
    */
    public function isClone($question_id = "")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        if ($question_id < 1) {
            $question_id = $this->id;
        }
        $result = $ilDB->queryF(
            "SELECT original_id FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        $row = $ilDB->fetchAssoc($result);
        return ($row["original_id"] > 0) ? true : false;
    }

    /**
    * Shuffles the values of a given array
    *
    * @param array $array An array which should be shuffled
    * @access public
    */
    public function pcArrayShuffle($array)
    {
        $keys = array_keys($array);
        shuffle($keys);
        $result = array();
        foreach ($keys as $key) {
            $result[$key] = $array[$key];
        }
        return $result;
    }

    /**
    * get question type for question id
    *
    * note: please don't use $this in this class to allow static calls
    */
    public static function getQuestionTypeFromDb($question_id)
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
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    * @access public
    */
    public function getAdditionalTableName()
    {
        return "";
    }
    
    /**
    * Returns the name of the answer table in the database
    *
    * @return string The answer table name
    * @access public
    */
    public function getAnswerTableName()
    {
        return "";
    }

    /**
    * Deletes datasets from answers tables
    *
    * @param integer $question_id The question id which should be deleted in the answers table
    * @access public
    */
    public function deleteAnswers($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $answer_table_name = $this->getAnswerTableName();
        
        if (!is_array($answer_table_name)) {
            $answer_table_name = array($answer_table_name);
        }
        
        foreach ($answer_table_name as $table) {
            if (strlen($table)) {
                $affectedRows = $ilDB->manipulateF(
                    "DELETE FROM $table WHERE question_fi = %s",
                    array('integer'),
                    array($question_id)
                );
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
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $additional_table_name = $this->getAdditionalTableName();
        
        if (!is_array($additional_table_name)) {
            $additional_table_name = array($additional_table_name);
        }
        
        foreach ($additional_table_name as $table) {
            if (strlen($table)) {
                $affectedRows = $ilDB->manipulateF(
                    "DELETE FROM $table WHERE question_fi = %s",
                    array('integer'),
                    array($question_id)
                );
            }
        }
    }

    /**
    * Deletes the page object of a question with a given ID
    *
    * @param integer $question_id The database id of the question
    * @access protected
    */
    protected function deletePageOfQuestion($question_id)
    {
        include_once "./Modules/TestQuestionPool/classes/class.ilAssQuestionPage.php";
        $page = new ilAssQuestionPage($question_id);
        $page->delete();
        return true;
    }

    /**
    * Deletes a question and all materials from the database
    *
    * @param integer $question_id The database id of the question
    * @access private
    */
    public function delete($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];
        
        if ($question_id < 1) {
            return true;
        } // nothing to do

        $result = $ilDB->queryF(
            "SELECT obj_fi FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            $obj_id = $row["obj_fi"];
        } else {
            return true; // nothing to do
        }
        try {
            $this->deletePageOfQuestion($question_id);
        } catch (Exception $e) {
            $ilLog->write("EXCEPTION: Could not delete page of question $question_id: $e");
            return false;
        }
        
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($affectedRows == 0) {
            return false;
        }

        try {
            $this->deleteAdditionalTableData($question_id);
            $this->deleteAnswers($question_id);
            $this->feedbackOBJ->deleteGenericFeedbacks($question_id, $this->isAdditionalContentEditingModePageObject());
            $this->feedbackOBJ->deleteSpecificAnswerFeedbacks($question_id, $this->isAdditionalContentEditingModePageObject());
        } catch (Exception $e) {
            $ilLog->write("EXCEPTION: Could not delete additional table data of question $question_id: $e");
            return false;
        }

        try {
            // delete the question in the tst_test_question table (list of test questions)
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM tst_test_question WHERE question_fi = %s",
                array('integer'),
                array($question_id)
            );
        } catch (Exception $e) {
            $ilLog->write("EXCEPTION: Could not delete delete question $question_id from a test: $e");
            return false;
        }

        try {
            // delete suggested solutions contained in the question
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM qpl_sol_sug WHERE question_fi = %s",
                array('integer'),
                array($question_id)
            );
        } catch (Exception $e) {
            $ilLog->write("EXCEPTION: Could not delete suggested solutions of question $question_id: $e");
            return false;
        }
                
        try {
            $directory = CLIENT_WEB_DIR . "/assessment/" . $obj_id . "/$question_id";
            if (preg_match("/\d+/", $obj_id) and preg_match("/\d+/", $question_id) and is_dir($directory)) {
                include_once "./Services/Utilities/classes/class.ilUtil.php";
                ilUtil::delDir($directory);
            }
        } catch (Exception $e) {
            $ilLog->write("EXCEPTION: Could not delete question file directory $directory of question $question_id: $e");
            return false;
        }

        try {
            include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
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
            $ilLog->write("EXCEPTION: Error deleting the media objects of question $question_id: $e");
            return false;
        }
        
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintTracking.php';
        ilAssQuestionHintTracking::deleteRequestsByQuestionIds(array($question_id));
        
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintList.php';
        ilAssQuestionHintList::deleteHintsByQuestionIds(array($question_id));

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
        $assignmentList = new ilAssQuestionSkillAssignmentList($ilDB);
        $assignmentList->setParentObjId($obj_id);
        $assignmentList->setQuestionIdFilter($question_id);
        $assignmentList->loadFromDb();
        foreach ($assignmentList->getAssignmentsByQuestionId($question_id) as $assignment) {
            /* @var ilAssQuestionSkillAssignment $assignment */
            $assignment->deleteFromDb();
        }

        $this->deleteTaxonomyAssignments();
        
        try {
            // update question count of question pool
            include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
            ilObjQuestionPool::_updateQuestionCount($this->getObjId());
        } catch (Exception $e) {
            $ilLog->write("EXCEPTION: Error updating the question pool question count of question pool " . $this->getObjId() . " when deleting question $question_id: $e");
            return false;
        }
        
        $this->notifyQuestionDeleted($this);
        
        return true;
    }
    
    private function deleteTaxonomyAssignments()
    {
        require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';
        require_once 'Services/Taxonomy/classes/class.ilTaxNodeAssignment.php';
        $taxIds = ilObjTaxonomy::getUsageOfObject($this->getObjId());
        
        foreach ($taxIds as $taxId) {
            $taxNodeAssignment = new ilTaxNodeAssignment('qpl', $this->getObjId(), 'quest', $taxId);
            $taxNodeAssignment->deleteAssignmentsOfItem($this->getId());
        }
    }

    /**
    * get total number of answers
    */
    public function getTotalAnswers()
    {
        return $this->_getTotalAnswers($this->id);
    }

    /**
    * get number of answers for question id (static)
    * note: do not use $this inside this method
    *
    * @param	int		$a_q_id		question id
    */
    public function _getTotalAnswers($a_q_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // get all question references to the question id
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
            array_push($found_id, $row["question_id"]);
        }

        $result = $ilDB->query("SELECT * FROM tst_test_result WHERE " . $ilDB->in('question_fi', $found_id, false, 'integer'));

        return $result->numRows();
    }


    /**
    * get number of answers for question id (static)
    * note: do not use $this inside this method
    *
    * @param	int		$a_q_id		question id
    */
    public static function _getTotalRightAnswers($a_q_id)
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
            array_push($found_id, $row["question_id"]);
        }
        $result = $ilDB->query("SELECT * FROM tst_test_result WHERE " . $ilDB->in('question_fi', $found_id, false, 'integer'));
        $answers = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $reached = $row["points"];
            include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
            $max = assQuestion::_getMaximumPoints($row["question_fi"]);
            array_push($answers, array("reached" => $reached, "max" => $max));
        }
        $max = 0.0;
        $reached = 0.0;
        foreach ($answers as $key => $value) {
            $max += $value["max"];
            $reached += $value["reached"];
        }
        if ($max > 0) {
            return $reached / $max;
        } else {
            return 0;
        }
    }

    /**
    * Returns the title of a question
    *
    * @param	int		$a_q_id		question id
    */
    public static function _getTitle($a_q_id)
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
        } else {
            return "";
        }
    }
    
    /**
    * Returns question text
    *
    * @param	int		$a_q_id		question id
    */
    public static function _getQuestionText($a_q_id)
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
        } else {
            return "";
        }
    }
    
    public static function isFileAvailable($file)
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
    
    public function copyXHTMLMediaObjectsOfQuestion($a_q_id)
    {
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $a_q_id);
        foreach ($mobs as $mob) {
            ilObjMediaObject::_saveUsage($mob, "qpl:html", $this->getId());
        }
    }
    
    public function syncXHTMLMediaObjectsOfQuestion()
    {
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
        foreach ($mobs as $mob) {
            ilObjMediaObject::_saveUsage($mob, "qpl:html", $this->original_id);
        }
    }
    
    /**
    * create page object of question
    */
    public function createPageObject()
    {
        $qpl_id = $this->getObjId();

        include_once "./Modules/TestQuestionPool/classes/class.ilAssQuestionPage.php";
        $this->page = new ilAssQuestionPage(0);
        $this->page->setId($this->getId());
        $this->page->setParentId($qpl_id);
        $this->page->setXMLContent("<PageObject><PageContent>" .
            "<Question QRef=\"il__qst_" . $this->getId() . "\"/>" .
            "</PageContent></PageObject>");
        $this->page->create();
    }

    public function copyPageOfQuestion($a_q_id)
    {
        if ($a_q_id > 0) {
            include_once "./Modules/TestQuestionPool/classes/class.ilAssQuestionPage.php";
            $page = new ilAssQuestionPage($a_q_id);

            $xml = str_replace("il__qst_" . $a_q_id, "il__qst_" . $this->id, $page->getXMLContent());
            $this->page->setXMLContent($xml);
            $this->page->updateFromXML();
        }
    }

    public function getPageOfQuestion()
    {
        include_once "./Modules/TestQuestionPool/classes/class.ilAssQuestionPage.php";
        $page = new ilAssQuestionPage($this->id);
        return $page->getXMLContent();
    }

    /**
     * Returns the question type of a question with a given id
     * @param integer $question_id The database id of the question
     * @return string The question type string
     */
    public static function _getQuestionType($question_id)
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
        } else {
            return "";
        }
    }

    /**
    * Returns the question title of a question with a given id
    *
    * @param integer $question_id The database id of the question
    * @result string The question title
    * @access private
    */
    public static function _getQuestionTitle($question_id)
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
        } else {
            return "";
        }
    }

    public function setOriginalId($original_id)
    {
        $this->original_id = $original_id;
    }
    
    public function getOriginalId()
    {
        return $this->original_id;
    }
    
    protected static $imageSourceFixReplaceMap = array(
        'ok.svg' => 'ok.png', 'not_ok.svg' => 'not_ok.png',
        'checkbox_checked.svg' => 'checkbox_checked.png',
        'checkbox_unchecked.svg' => 'checkbox_unchecked.png',
        'radiobutton_checked.svg' => 'radiobutton_checked.png',
        'radiobutton_unchecked.svg' => 'radiobutton_unchecked.png'
    );
    
    public function fixSvgToPng($imageFilenameContainingString)
    {
        $needles = array_keys(self::$imageSourceFixReplaceMap);
        $replacements = array_values(self::$imageSourceFixReplaceMap);
        return str_replace($needles, $replacements, $imageFilenameContainingString);
    }
    
    
    public function fixUnavailableSkinImageSources($html)
    {
        $matches = null;
        if (preg_match_all('/src="(.*?)"/m', $html, $matches)) {
            $sources = $matches[1];
            
            $needleReplacementMap = array();
            
            foreach ($sources as $src) {
                $file = ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH) . DIRECTORY_SEPARATOR . $src;
                
                if (file_exists($file)) {
                    continue;
                }
                
                $levels = explode(DIRECTORY_SEPARATOR, $src);
                if (count($levels) < 5 || $levels[0] != 'Customizing' || $levels[2] != 'skin') {
                    continue;
                }
                
                $component = '';
                
                if ($levels[4] == 'Modules' || $levels[4] == 'Services') {
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

    /**
    * Loads the question from the database
    *
    * @param integer $question_id A unique key which defines the question in the database
    * @access public
    */
    public function loadFromDb($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT external_id FROM qpl_questions WHERE question_id = %s",
            array("integer"),
            array($question_id)
        );
        if ($result->numRows() == 1) {
            $data = $ilDB->fetchAssoc($result);
            $this->external_id = $data['external_id'];
        }
        
        $result = $ilDB->queryF(
            "SELECT * FROM qpl_sol_sug WHERE question_fi = %s",
            array('integer'),
            array($this->getId())
        );
        $this->suggested_solutions = array();
        if ($result->numRows()) {
            include_once("./Services/RTE/classes/class.ilRTE.php");
            while ($row = $ilDB->fetchAssoc($result)) {
                $value = (is_array(unserialize($row["value"]))) ? unserialize($row["value"]) : ilRTE::_replaceMediaObjectImageSrc($row["value"], 1);
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
    public function createNewQuestion($a_create_page = true)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        
        $complete = "0";
        $estw_time = $this->getEstimatedWorkingTime();
        $estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);
        $obj_id = ($this->getObjId() <= 0) ? (ilObject::_lookupObjId((strlen($_GET["ref_id"])) ? $_GET["ref_id"] : $_POST["sel_qpl"])) : $this->getObjId();
        if ($obj_id > 0) {
            if ($a_create_page) {
                $tstamp = 0;
            } else {
                // question pool must not try to purge
                $tstamp = time();
            }
            
            $next_id = $ilDB->nextId('qpl_questions');
            $affectedRows = $ilDB->insert("qpl_questions", array(
                "question_id" => array("integer", $next_id),
                "question_type_fi" => array("integer", $this->getQuestionTypeID()),
                "obj_fi" => array("integer", $obj_id),
                "title" => array("text", null),
                "description" => array("text", null),
                "author" => array("text", $this->getAuthor()),
                "owner" => array("integer", $ilUser->getId()),
                "question_text" => array("clob", null),
                "points" => array("float", 0),
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
        
        $this->notifyQuestionCreated();
        
        return $this->getId();
    }

    public function saveQuestionDataToDb($original_id = "")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $estw_time = $this->getEstimatedWorkingTime();
        $estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);

        // cleanup RTE images which are not inserted into the question text
        include_once("./Services/RTE/classes/class.ilRTE.php");
        if ($this->getId() == -1) {
            // Neuen Datensatz schreiben
            $next_id = $ilDB->nextId('qpl_questions');
            $affectedRows = $ilDB->insert("qpl_questions", array(
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
                "original_id" => array("integer", ($original_id) ? $original_id : null),
                "tstamp" => array("integer", time()),
                "external_id" => array("text", $this->getExternalId()),
                'add_cont_edit_mode' => array('text', $this->getAdditionalContentEditingMode())
            ));
            $this->setId($next_id);
            // create page object of question
            $this->createPageObject();
        } else {
            // Vorhandenen Datensatz aktualisieren
            $affectedRows = $ilDB->update("qpl_questions", array(
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

    /**
    * Saves the question to the database
    *
    * @param integer $original_id
    * @access public
    */
    public function saveToDb($original_id = "")
    {
        global $DIC;

        $this->updateSuggestedSolutions();
        
        // remove unused media objects from ILIAS
        $this->cleanupMediaObjectUsage();

        $complete = "0";
        if ($this->isComplete()) {
            $complete = "1";
        }

        $DIC->database()->update('qpl_questions', array(
            'tstamp' => array('integer', time()),
            'owner' => array('integer', ($this->getOwner() <= 0 ? $this->ilias->account->id : $this->getOwner())),
            'complete' => array('integer', $complete),
            'lifecycle' => array('text', $this->getLifecycle()->getIdentifier()),
        ), array(
            'question_id' => array('integer', $this->getId())
        ));

        // update question count of question pool
        include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
        ilObjQuestionPool::_updateQuestionCount($this->obj_id);
        
        $this->notifyQuestionEdited($this);
    }
    
    /**
     * @deprecated
     */
    public function setNewOriginalId($newId)
    {
        self::saveOriginalId($this->getId(), $newId);
    }
    
    public static function saveOriginalId($questionId, $originalId)
    {
        $query = "UPDATE qpl_questions SET tstamp = %s, original_id = %s WHERE question_id = %s";
        
        $GLOBALS['DIC']['ilDB']->manipulateF(
            $query,
            array('integer','integer', 'text'),
            array(time(), $originalId, $questionId)
        );
    }
    
    public static function resetOriginalId($questionId)
    {
        $query = "UPDATE qpl_questions SET tstamp = %s, original_id = NULL WHERE question_id = %s";
        
        $GLOBALS['DIC']['ilDB']->manipulateF(
            $query,
            array('integer', 'text'),
            array(time(), $questionId)
        );
    }
    
    /**
    * Will be called when a question is duplicated (inside a question pool or for insertion in a test)
    */
    protected function onDuplicate($originalParentId, $originalQuestionId, $duplicateParentId, $duplicateQuestionId)
    {
        $this->duplicateSuggestedSolutionFiles($originalParentId, $originalQuestionId);
        
        // duplicate question feeback
        $this->feedbackOBJ->duplicateFeedback($originalQuestionId, $duplicateQuestionId);
        
        // duplicate question hints
        $this->duplicateQuestionHints($originalQuestionId, $duplicateQuestionId);
        
        // duplicate skill assignments
        $this->duplicateSkillAssignments($originalParentId, $originalQuestionId, $duplicateParentId, $duplicateQuestionId);
    }

    protected function beforeSyncWithOriginal($origQuestionId, $dupQuestionId, $origParentObjId, $dupParentObjId)
    {
    }

    protected function afterSyncWithOriginal($origQuestionId, $dupQuestionId, $origParentObjId, $dupParentObjId)
    {
        // sync question feeback
        $this->feedbackOBJ->syncFeedback($origQuestionId, $dupQuestionId);
    }
    
    /**
    * Will be called when a question is copied (into another question pool)
    */
    protected function onCopy($sourceParentId, $sourceQuestionId, $targetParentId, $targetQuestionId)
    {
        $this->copySuggestedSolutionFiles($sourceParentId, $sourceQuestionId);
        
        // duplicate question feeback
        $this->feedbackOBJ->duplicateFeedback($sourceQuestionId, $targetQuestionId);
        
        // duplicate question hints
        $this->duplicateQuestionHints($sourceQuestionId, $targetQuestionId);

        // duplicate skill assignments
        $this->duplicateSkillAssignments($sourceParentId, $sourceQuestionId, $targetParentId, $targetQuestionId);
    }
    
    /**
    * Deletes all suggestes solutions in the database
    */
    public function deleteSuggestedSolutions()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        // delete the links in the qpl_sol_sug table
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM qpl_sol_sug WHERE question_fi = %s",
            array('integer'),
            array($this->getId())
        );
        // delete the links in the int_link table
        include_once "./Services/Link/classes/class.ilInternalLink.php";
        ilInternalLink::_deleteAllLinksOfSource("qst", $this->getId());
        $this->suggested_solutions = array();
        ilUtil::delDir($this->getSuggestedSolutionPath());
    }
    
    /**
    * Returns a suggested solution for a given subquestion index
    *
    * @param integer $subquestion_index The index of a subquestion (i.e. a close test gap). Usually 0
    * @return array A suggested solution array containing the internal link
    * @access public
    */
    public function getSuggestedSolution($subquestion_index = 0)
    {
        if (array_key_exists($subquestion_index, $this->suggested_solutions)) {
            return $this->suggested_solutions[$subquestion_index];
        } else {
            return array();
        }
    }

    /**
    * Returns the title of a suggested solution at a given subquestion_index.
    * This can be usable for displaying suggested solutions
    *
    * @param integer $subquestion_index The index of a subquestion (i.e. a close test gap). Usually 0
    * @return string A string containing the type and title of the internal link
    * @access public
    */
    public function getSuggestedSolutionTitle($subquestion_index = 0)
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
    * @param integer $subquestion_index The index of a subquestion (i.e. a close test gap). Usually 0
    * @param boolean $is_import A boolean indication that the internal link was imported from another ILIAS installation
    * @access public
    */
    public function setSuggestedSolution($solution_id = "", $subquestion_index = 0, $is_import = false)
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
    protected function duplicateSuggestedSolutionFiles($parent_id, $question_id)
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];

        foreach ($this->suggested_solutions as $index => $solution) {
            if (strcmp($solution["type"], "file") == 0) {
                $filepath = $this->getSuggestedSolutionPath();
                $filepath_original = str_replace(
                    "/{$this->obj_id}/{$this->id}/solution",
                    "/$parent_id/$question_id/solution",
                    $filepath
                );
                if (!file_exists($filepath)) {
                    ilUtil::makeDirParents($filepath);
                }
                $filename = $solution["value"]["name"];
                if (strlen($filename)) {
                    if (!copy($filepath_original . $filename, $filepath . $filename)) {
                        $ilLog->write("File could not be duplicated!!!!", $ilLog->ERROR);
                        $ilLog->write("object: " . print_r($this, true), $ilLog->ERROR);
                    }
                }
            }
        }
    }

    /**
    * Syncs the files of a suggested solution if the question is synced
    */
    protected function syncSuggestedSolutionFiles($original_id)
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];

        $filepath = $this->getSuggestedSolutionPath();
        $filepath_original = str_replace("/$this->id/solution", "/$original_id/solution", $filepath);
        ilUtil::delDir($filepath_original);
        foreach ($this->suggested_solutions as $index => $solution) {
            if (strcmp($solution["type"], "file") == 0) {
                if (!file_exists($filepath_original)) {
                    ilUtil::makeDirParents($filepath_original);
                }
                $filename = $solution["value"]["name"];
                if (strlen($filename)) {
                    if (!@copy($filepath . $filename, $filepath_original . $filename)) {
                        $ilLog->write("File could not be duplicated!!!!", $ilLog->ERROR);
                        $ilLog->write("object: " . print_r($this, true), $ilLog->ERROR);
                    }
                }
            }
        }
    }

    protected function copySuggestedSolutionFiles($source_questionpool_id, $source_question_id)
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];

        foreach ($this->suggested_solutions as $index => $solution) {
            if (strcmp($solution["type"], "file") == 0) {
                $filepath = $this->getSuggestedSolutionPath();
                $filepath_original = str_replace("/$this->obj_id/$this->id/solution", "/$source_questionpool_id/$source_question_id/solution", $filepath);
                if (!file_exists($filepath)) {
                    ilUtil::makeDirParents($filepath);
                }
                $filename = $solution["value"]["name"];
                if (strlen($filename)) {
                    if (!copy($filepath_original . $filename, $filepath . $filename)) {
                        $ilLog->write("File could not be copied!!!!", $ilLog->ERROR);
                        $ilLog->write("object: " . print_r($this, true), $ilLog->ERROR);
                    }
                }
            }
        }
    }

    /**
    * Update the suggested solutions of a question based on the suggested solution array attribute
    */
    public function updateSuggestedSolutions($original_id = "")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $id = (strlen($original_id) && is_numeric($original_id)) ? $original_id : $this->getId();
        include_once "./Services/Link/classes/class.ilInternalLink.php";
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM qpl_sol_sug WHERE question_fi = %s",
            array('integer'),
            array($id)
        );
        ilInternalLink::_deleteAllLinksOfSource("qst", $id);
        include_once("./Services/RTE/classes/class.ilRTE.php");
        foreach ($this->suggested_solutions as $index => $solution) {
            $next_id = $ilDB->nextId('qpl_sol_sug');
            /** @var ilDBInterface $ilDB */
            $ilDB->insert(
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
        if (strlen($original_id) && is_numeric($original_id)) {
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
    public function saveSuggestedSolution($type, $solution_id = "", $subquestion_index = 0, $value = "")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM qpl_sol_sug WHERE question_fi = %s AND subquestion_index = %s",
            array("integer", "integer"),
            array(
                $this->getId(),
                $subquestion_index
            )
        );
        
        $next_id = $ilDB->nextId('qpl_sol_sug');
        include_once("./Services/RTE/classes/class.ilRTE.php");
        /** @var ilDBInterface $ilDB */
        $affectedRows = $ilDB->insert(
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

    public function _resolveInternalLink($internal_link)
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
    
    public function _resolveIntLinks($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $resolvedlinks = 0;
        $result = $ilDB->queryF(
            "SELECT * FROM qpl_sol_sug WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $internal_link = $row["internal_link"];
                include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
                $resolved_link = assQuestion::_resolveInternalLink($internal_link);
                if (strcmp($internal_link, $resolved_link) != 0) {
                    // internal link was resolved successfully
                    $affectedRows = $ilDB->manipulateF(
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
            include_once "./Services/Link/classes/class.ilInternalLink.php";
            ilInternalLink::_deleteAllLinksOfSource("qst", $question_id);

            $result = $ilDB->queryF(
                "SELECT * FROM qpl_sol_sug WHERE question_fi = %s",
                array('integer'),
                array($question_id)
            );
            if ($result->numRows()) {
                while ($row = $ilDB->fetchAssoc($result)) {
                    if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $row["internal_link"], $matches)) {
                        ilInternalLink::_saveLink("qst", $question_id, $matches[2], $matches[3], $matches[1]);
                    }
                }
            }
        }
    }
    
    public static function _getInternalLinkHref($target = "")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
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
            include_once "./Services/Utilities/classes/class.ilUtil.php";
            switch ($linktypes[$matches[1]]) {
                case "LearningModule":
                    $href = "./goto.php?target=" . $type . "_" . $target_id;
                    break;
                case "PageObject":
                case "StructureObject":
                    $href = "./goto.php?target=" . $type . "_" . $target_id;
                    break;
                case "GlossaryItem":
                    $href = "./goto.php?target=" . $type . "_" . $target_id;
                    break;
                case "MediaObject":
                    $href = "./ilias.php?baseClass=ilLMPresentationGUI&obj_type=" . $linktypes[$type] . "&cmd=media&ref_id=" . $_GET["ref_id"] . "&mob_id=" . $target_id;
                    break;
            }
        }
        return $href;
    }
    
    /**
    * Returns the original id of a question
    *
    * @param integer $question_id The database id of the question
    * @return integer The database id of the original question
    * @access public
    */
    public static function _getOriginalId($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT * FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() > 0) {
            $row = $ilDB->fetchAssoc($result);
            if ($row["original_id"] > 0) {
                return $row["original_id"];
            } else {
                return $row["question_id"];
            }
        } else {
            return "";
        }
    }

    public static function originalQuestionExists($questionId)
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

    public function syncWithOriginal()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (!$this->getOriginalId()) {
            return;
        }
        
        $originalObjId = self::lookupOriginalParentObjId($this->getOriginalId());
        
        if (!$originalObjId) {
            return;
        }
        
        $id = $this->getId();
        $objId = $this->getObjId();
        $original = $this->getOriginalId();

        $this->beforeSyncWithOriginal($original, $id, $originalObjId, $objId);

        $this->setId($original);
        $this->setOriginalId(null);
        $this->setObjId($originalObjId);
        
        $this->saveToDb();
        
        $this->deletePageOfQuestion($original);
        $this->createPageObject();
        $this->copyPageOfQuestion($id);

        $this->setId($id);
        $this->setOriginalId($original);
        $this->setObjId($objId);
        
        $this->updateSuggestedSolutions($original);
        $this->syncXHTMLMediaObjectsOfQuestion();

        $this->afterSyncWithOriginal($original, $id, $originalObjId, $objId);
        $this->syncHints();
    }

    public function createRandomSolution($test_id, $user_id)
    {
    }

    /**
    * Returns true if the question already exists in the database
    *
    * @param integer $question_id The database id of the question
    * @result boolean True, if the question exists, otherwise False
    * @access public
    */
    public function _questionExists($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($question_id < 1) {
            return false;
        }
        
        $result = $ilDB->queryF(
            "SELECT question_id FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Returns true if the question already exists in the database and is assigned to a question pool
    *
    * @param integer $question_id The database id of the question
    * @result boolean True, if the question exists, otherwise False
    * @access public
    */
    public function _questionExistsInPool($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($question_id < 1) {
            return false;
        }

        $result = $ilDB->queryF(
            "SELECT question_id FROM qpl_questions INNER JOIN object_data ON obj_fi = obj_id WHERE question_id = %s AND type = 'qpl'",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Creates an instance of a question with a given question id
     *
     * @param integer $question_id The question id
     * @return assQuestion The question instance
     * @deprecated use assQuestion::_instantiateQuestion() instead.
     */
    public static function _instanciateQuestion($question_id)
    {
        return self::_instantiateQuestion($question_id);
    }

    /**
     * @param $question_id
     * @return assQuestion
     */
    public static function _instantiateQuestion($question_id)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        
        if (strcmp($question_id, "") != 0) {
            $question_type = assQuestion::_getQuestionType($question_id);
            if (!strlen($question_type)) {
                return null;
            }
            assQuestion::_includeClass($question_type);
            $objectClassname = self::getObjectClassNameByQuestionType($question_type);
            $question = new $objectClassname();
            $question->loadFromDb($question_id);
            
            $feedbackObjectClassname = self::getFeedbackClassNameByQuestionType($question_type);
            $question->feedbackOBJ = new $feedbackObjectClassname($question, $ilCtrl, $ilDB, $lng);
            
            return $question;
        }
    }
    
    /**
    * Returns the maximum available points for the question
    *
    * @return integer The points
    * @access public
    */
    public function getPoints()
    {
        if (strcmp($this->points, "") == 0) {
            return 0;
        } else {
            return $this->points;
        }
    }

    
    /**
    * Sets the maximum available points for the question
    *
    * @param integer $a_points The points
    * @access public
    */
    public function setPoints($a_points)
    {
        $this->points = $a_points;
    }
    
    /**
    * Returns the maximum pass a users question solution
    *
    * @param return integer The maximum pass of the users solution
    * @access public
    */
    public function getSolutionMaxPass($active_id)
    {
        return self::_getSolutionMaxPass($this->getId(), $active_id);
    }

    /**
    * Returns the maximum pass a users question solution
    *
    * @param return integer The maximum pass of the users solution
    * @access public
    */
    public static function _getSolutionMaxPass($question_id, $active_id)
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
            return $row["maxpass"];
        } else {
            return 0;
        }
    }

    /**
    * Returns true if the question is writeable by a certain user
    *
    * @param integer $question_id The database id of the question
    * @param integer $user_id The database id of the user
    * @result boolean True, if the question exists, otherwise False
    * @access public
    */
    public static function _isWriteable($question_id, $user_id)
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
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            $qpl_object_id = $row["obj_fi"];
            include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
            return ilObjQuestionPool::_isWriteable($qpl_object_id, $user_id);
        } else {
            return false;
        }
    }

    /**
    * Checks whether the question is used in a random test or not
    *
    * @return boolean The number how often the question is used in a random test
    * @access public
    */
    public static function _isUsedInRandomTest($question_id = "")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        if ($question_id < 1) {
            return 0;
        }
        $result = $ilDB->queryF(
            "SELECT test_random_question_id FROM tst_test_rnd_qst WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        return $result->numRows();
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

    public function deductHintPointsFromReachedPoints(ilAssQuestionPreviewSession $previewSession, $reachedPoints)
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
    
    public function isPreviewSolutionCorrect(ilAssQuestionPreviewSession $previewSession)
    {
        $reachedPoints = $this->calculateReachedPointsFromPreviewSession($previewSession);

        if ($reachedPoints < $this->getMaximumPoints()) {
            return false;
        }
        
        return true;
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
    final public function adjustReachedPointsByScoringOptions($points, $active_id, $pass = null)
    {
        include_once "./Modules/Test/classes/class.ilObjTest.php";
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
    *
    * @param integer $user_id The database ID of the learner
    * @param integer $test_id The database Id of the test containing the question
    * @param integer $question_id The database Id of the question
    */
    public static function _isWorkedThrough($active_id, $question_id, $pass = null)
    {
        return self::lookupResultRecordExist($active_id, $question_id, $pass);
        
        // oldschool "workedthru"

        global $DIC;
        $ilDB = $DIC['ilDB'];

        $points = 0;
        if (is_null($pass)) {
            include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
            $pass = assQuestion::_getSolutionMaxPass($question_id, $active_id);
        }
        $result = $ilDB->queryF(
            "SELECT solution_id FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
            array('integer','integer','integer'),
            array($active_id, $question_id, $pass)
        );
        if ($result->numRows()) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Checks if an array of question ids is answered by an user or not
    *
    * @param int user_id
    * @param array $question_ids user id array
    * @return boolean
    */
    public static function _areAnswered($a_user_id, $a_question_ids)
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
    * @param string $a_text Text which should be checked
    * @return boolean
    * @access public
    * @deprecated use ilUtil::isHTML() instead
    */
    public function isHTML($a_text)
    {
        return ilUtil::isHTML($a_text);
    }
    
    /**
    * Prepares a string for a text area output in tests
    *
    * @param string $txt_output String which should be prepared for output
    * @access public
    */
    public function prepareTextareaOutput($txt_output, $prepare_for_latex_output = false, $omitNl2BrWhenTextArea = false)
    {
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        return ilUtil::prepareTextareaOutput($txt_output, $prepare_for_latex_output, $omitNl2BrWhenTextArea);
    }

    /**
    * Reads an QTI material tag an creates a text string
    *
    * @param string $a_material QTI material tag
    * @return string text or xhtml string
    * @access public
    */
    public function QTIMaterialToString($a_material)
    {
        $result = "";
        for ($i = 0; $i < $a_material->getMaterialCount(); $i++) {
            $material = $a_material->getMaterial($i);
            if (strcmp($material["type"], "mattext") == 0) {
                $result .= $material["material"]->getContent();
            }
            if (strcmp($material["type"], "matimage") == 0) {
                $matimage = $material["material"];
                if (preg_match("/(il_([0-9]+)_mob_([0-9]+))/", $matimage->getLabel(), $matches)) {
                    // import an mediaobject which was inserted using tiny mce
                    if (!is_array($_SESSION["import_mob_xhtml"])) {
                        $_SESSION["import_mob_xhtml"] = array();
                    }
                    array_push($_SESSION["import_mob_xhtml"], array("mob" => $matimage->getLabel(), "uri" => $matimage->getUri()));
                }
            }
        }
        return $result;
    }
    
    /**
    * Creates a QTI material tag from a plain text or xhtml text
    *
    * @param object $a_xml_writer Reference to the ILIAS XML writer
    * @param string $a_material plain text or html text containing the material
    * @return string QTI material tag
    * @access public
    */
    public function addQTIMaterial(&$a_xml_writer, $a_material, $close_material_tag = true, $add_mobs = true)
    {
        include_once "./Services/RTE/classes/class.ilRTE.php";
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

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
    
    public function buildHashedImageFilename($plain_image_filename, $unique = false)
    {
        $extension = "";
        
        if (preg_match("/.*\.(png|jpg|gif|jpeg)$/i", $plain_image_filename, $matches)) {
            $extension = "." . $matches[1];
        }
        
        if ($unique) {
            $plain_image_filename = uniqid($plain_image_filename . microtime(true));
        }
        
        $hashed_filename = md5($plain_image_filename) . $extension;
        
        return $hashed_filename;
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
    public static function _setReachedPoints($active_id, $question_id, $points, $maxpoints, $pass, $manualscoring, $obligationsEnabled)
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
            if ($rowsnum) {
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

            if (self::isForcePassResultUpdateEnabled() || $old_points != $points || !$rowsnum) {
                assQuestion::_updateTestPassResults($active_id, $pass, $obligationsEnabled);
                // finally update objective result
                include_once "./Modules/Test/classes/class.ilObjTest.php";
                include_once './Modules/Course/classes/class.ilCourseObjectiveResult.php';
                ilCourseObjectiveResult::_updateObjectiveResult(ilObjTest::_getUserIdFromActiveId($active_id), $question_id, $points);
    
                include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
                if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                    global $DIC;
                    $lng = $DIC['lng'];
                    $ilUser = $DIC['ilUser'];
                    include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
                    $username = ilObjTestAccess::_getParticipantData($active_id);
                    assQuestion::logAction(sprintf($lng->txtlng("assessment", "log_answer_changed_points", ilObjAssessmentFolder::_getLogLanguage()), $username, $old_points, $points, $ilUser->getFullname() . " (" . $ilUser->getLogin() . ")"), $active_id, $question_id);
                }
            }

            return true;
        } else {
            return false;
        }
    }
    
    /**
    * Gets the question string of the question object
    *
    * @return string The question string of the question object
    * @access public
    * @see $question
    */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
    * Sets the question string of the question object
    *
    * @param string $question A string containing the question text
    * @access public
    * @see $question
    */
    public function setQuestion($question = "")
    {
        $this->question = $question;
    }

    /**
    * Returns the question type of the question
    *
    * @return string The question type of the question
    */
    abstract public function getQuestionType();
    
    /**
    * Returns the question type of the question
    *
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    * @access public
    */
    public function getQuestionTypeID()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $result = $ilDB->queryF(
            "SELECT question_type_id FROM qpl_qst_type WHERE type_tag = %s",
            array('text'),
            array($this->getQuestionType())
        );
        if ($result->numRows() == 1) {
            $row = $ilDB->fetchAssoc($result);
            return $row["question_type_id"];
        }
        return 0;
    }

    public function syncHints()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // delete hints of the original
        $ilDB->manipulateF(
            "DELETE FROM qpl_hints WHERE qht_question_fi = %s",
            array('integer'),
            array($this->original_id)
        );

        // get hints of the actual question
        $result = $ilDB->queryF(
            "SELECT * FROM qpl_hints WHERE qht_question_fi = %s",
            array('integer'),
            array($this->getId())
        );

        // save hints to the original
        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $next_id = $ilDB->nextId('qpl_hints');
                /** @var ilDBInterface $ilDB */
                $ilDB->insert(
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
    
    /**
    * Collects all text in the question which could contain media objects
    * which were created with the Rich Text Editor
    */
    protected function getRTETextWithMediaObjects()
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

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintList.php';
        $questionHintList = ilAssQuestionHintList::getListByQuestionId($this->getId());
        foreach ($questionHintList as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */
            $collected .= $questionHint->getText();
        }

        return $collected;
    }

    /**
    * synchronises appearances of media objects in the question with media
    * object usage table
    */
    public function cleanupMediaObjectUsage()
    {
        $combinedtext = $this->getRTETextWithMediaObjects();
        include_once("./Services/RTE/classes/class.ilRTE.php");
        ilRTE::_cleanupMediaObjectUsage($combinedtext, "qpl:html", $this->getId());
    }
    
    /**
    * Gets all instances of the question
    *
    * @result array All instances of question and its copies
    */
    public function &getInstances()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT question_id FROM qpl_questions WHERE original_id = %s",
            array("integer"),
            array($this->getId())
        );
        $instances = array();
        $ids = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            array_push($ids, $row["question_id"]);
        }
        foreach ($ids as $question_id) {
            // check non random tests
            $result = $ilDB->queryF(
                "SELECT tst_tests.obj_fi FROM tst_tests, tst_test_question WHERE tst_test_question.question_fi = %s AND tst_test_question.test_fi = tst_tests.test_id",
                array("integer"),
                array($question_id)
            );
            while ($row = $ilDB->fetchAssoc($result)) {
                $instances[$row['obj_fi']] = ilObject::_lookupTitle($row['obj_fi']);
            }
            // check random tests
            $result = $ilDB->queryF(
                "SELECT tst_tests.obj_fi FROM tst_tests, tst_test_rnd_qst, tst_active WHERE tst_test_rnd_qst.active_fi = tst_active.active_id AND tst_test_rnd_qst.question_fi = %s AND tst_tests.test_id = tst_active.test_fi",
                array("integer"),
                array($question_id)
            );
            while ($row = $ilDB->fetchAssoc($result)) {
                $instances[$row['obj_fi']] = ilObject::_lookupTitle($row['obj_fi']);
            }
        }
        include_once "./Modules/Test/classes/class.ilObjTest.php";
        foreach ($instances as $key => $value) {
            $instances[$key] = array("obj_id" => $key, "title" => $value, "author" => ilObjTest::_lookupAuthor($key), "refs" => ilObject::_getAllReferences($key));
        }
        return $instances;
    }

    public static function _needsManualScoring($question_id)
    {
        include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
        $scoring = ilObjAssessmentFolder::_getManualScoringTypes();
        $questiontype = assQuestion::_getQuestionType($question_id);
        if (in_array($questiontype, $scoring)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Returns the user id and the test id for a given active id
    *
    * @param integer $active_id Active id for a test/user
    * @return array Result array containing the user_id and test_id
    * @access public
    */
    public function getActiveUserData($active_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT * FROM tst_active WHERE active_id = %s",
            array('integer'),
            array($active_id)
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            return array("user_id" => $row["user_fi"], "test_id" => $row["test_fi"]);
        } else {
            return array();
        }
    }

    /**
    * Include the php class file for a given question type
    *
    * @param string $question_type The type tag of the question type
    * @return integer 0 if the class should be included, 1 if the GUI class should be included
    * @access public
    */
    public static function _includeClass($question_type, $gui = 0)
    {
        if (self::isCoreQuestionType($question_type)) {
            self::includeCoreClass($question_type, $gui);
        } else {
            self::includePluginClass($question_type, $gui);
        }
    }

    public static function getGuiClassNameByQuestionType($questionType)
    {
        return $questionType . 'GUI';
    }

    public static function getObjectClassNameByQuestionType($questionType)
    {
        return $questionType;
    }

    public static function getFeedbackClassNameByQuestionType($questionType)
    {
        return str_replace('ass', 'ilAss', $questionType) . 'Feedback';
    }

    public static function isCoreQuestionType($questionType)
    {
        $guiClassName = self::getGuiClassNameByQuestionType($questionType);
        return file_exists("Modules/TestQuestionPool/classes/class.{$guiClassName}.php");
    }

    public static function includeCoreClass($questionType, $withGuiClass)
    {
        if ($withGuiClass) {
            $guiClassName = self::getGuiClassNameByQuestionType($questionType);
            require_once "Modules/TestQuestionPool/classes/class.{$guiClassName}.php";

        // object class is included by gui classes constructor
        } else {
            $objectClassName = self::getObjectClassNameByQuestionType($questionType);
            require_once "Modules/TestQuestionPool/classes/class.{$objectClassName}.php";
        }

        $feedbackClassName = self::getFeedbackClassNameByQuestionType($questionType);
        require_once "Modules/TestQuestionPool/classes/feedback/class.{$feedbackClassName}.php";
    }

    public static function includePluginClass($questionType, $withGuiClass)
    {
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        $classes = array(
            self::getObjectClassNameByQuestionType($questionType),
            self::getFeedbackClassNameByQuestionType($questionType)
        );

        if ($withGuiClass) {
            $classes[] = self::getGuiClassNameByQuestionType($questionType);
        }

        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, "TestQuestionPool", "qst");
        foreach ($pl_names as $pl_name) {
            $pl = ilPlugin::getPluginObject(IL_COMP_MODULE, "TestQuestionPool", "qst", $pl_name);
            if (strcmp($pl->getQuestionType(), $questionType) == 0) {
                foreach ($classes as $class) {
                    $pl->includeClass("class.{$class}.php");
                }

                break;
            }
        }
    }

    /**
     * Return the translation for a given question type tag
     *
     * @param string $type_tag The type tag of the question type
     * @access public
     */
    public static function _getQuestionTypeName($type_tag)
    {
        if (file_exists("./Modules/TestQuestionPool/classes/class." . $type_tag . ".php")) {
            global $DIC;
            $lng = $DIC['lng'];
            return $lng->txt($type_tag);
        } else {
            global $DIC;
            $ilPluginAdmin = $DIC['ilPluginAdmin'];
            $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, "TestQuestionPool", "qst");
            foreach ($pl_names as $pl_name) {
                $pl = ilPlugin::getPluginObject(IL_COMP_MODULE, "TestQuestionPool", "qst", $pl_name);
                if (strcmp($pl->getQuestionType(), $type_tag) == 0) {
                    return $pl->getQuestionTypeTranslation();
                }
            }
        }
        return "";
    }

    /**
     * Creates an instance of a question gui with a given question id
     *
     * @param integer $question_id The question id
     * @return \assQuestionGUI The question gui instance
     * @static
     * @deprecated Use instantiateQuestionGUI (without legacy underscore & typos) instead.
     * @access public
     */
    public static function &_instanciateQuestionGUI($question_id)
    {
        return self::instantiateQuestionGUI($question_id);
    }

    /**
     * Creates an instance of a question gui with a given question id
     *
     * @param 	integer	$a_question_id
     *
     * @return 	\assQuestionGUI	The question gui instance
     */
    public static function instantiateQuestionGUI($a_question_id)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];

        if (strcmp($a_question_id, "") != 0) {
            $question_type = assQuestion::_getQuestionType($a_question_id);

            assQuestion::_includeClass($question_type, 1);

            $question_type_gui = self::getGuiClassNameByQuestionType($question_type);
            $question_gui = new $question_type_gui();
            $question_gui->object->loadFromDb($a_question_id);

            $feedbackObjectClassname = self::getFeedbackClassNameByQuestionType($question_type);
            $question_gui->object->feedbackOBJ = new $feedbackObjectClassname($question_gui->object, $ilCtrl, $ilDB, $lng);

            $assSettings = new ilSetting('assessment');
            require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLockerFactory.php';
            $processLockerFactory = new ilAssQuestionProcessLockerFactory($assSettings, $ilDB);
            $processLockerFactory->setQuestionId($question_gui->object->getId());
            $processLockerFactory->setUserId($ilUser->getId());
            include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
            $processLockerFactory->setAssessmentLogEnabled(ilObjAssessmentFolder::_enabledAssessmentLogging());
            $question_gui->object->setProcessLocker($processLockerFactory->getLocker());
        } else {
            global $DIC;
            $ilLog = $DIC['ilLog'];
            $ilLog->write('Instantiate question called without question id. (instantiateQuestionGUI@assQuestion)', $ilLog->WARNING);
            return null;
        }
        return $question_gui;
    }

    /**
     * Creates an Excel worksheet for the detailed cumulated results of this question
     *
     * @param object $worksheet    Reference to the parent excel worksheet
     * @param object $startrow     Startrow of the output in the excel worksheet
     * @param object $active_id    Active id of the participant
     * @param object $pass         Test pass
     *
     * @return object
     */
    public function setExportDetailsXLS($worksheet, $startrow, $active_id, $pass)
    {
        $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord(0) . $startrow, $this->lng->txt($this->getQuestionType()));
        $worksheet->setFormattedExcelTitle($worksheet->getColumnCoord(1) . $startrow, $this->getTitle());

        return $startrow;
    }

    /**
    * Object getter
    */
    public function __get($value)
    {
        switch ($value) {
            case "id":
                return $this->getId();
                break;
            case "title":
                return $this->getTitle();
                break;
            case "comment":
                return $this->getComment();
                break;
            case "owner":
                return $this->getOwner();
                break;
            case "author":
                return $this->getAuthor();
                break;
            case "question":
                return $this->getQuestion();
                break;
            case "points":
                return $this->getPoints();
                break;
            case "est_working_time":
                return $this->getEstimatedWorkingTime();
                break;
            case "shuffle":
                return $this->getShuffle();
                break;
            case "test_id":
                return $this->getTestId();
                break;
            case "obj_id":
                return $this->getObjId();
                break;
            case "ilias":
                return $this->ilias;
                break;
            case "tpl":
                return $this->tpl;
                break;
            case "page":
                return $this->page;
                break;
            case "outputType":
                return $this->getOutputType();
                break;
            case "suggested_solutions":
                return $this->getSuggestedSolutions();
                break;
            case "original_id":
                return $this->getOriginalId();
                break;
            default:
                if (array_key_exists($value, $this->arrData)) {
                    return $this->arrData[$value];
                } else {
                    return null;
                }
                break;
        }
    }

    /**
    * Object setter
    */
    public function __set($key, $value)
    {
        switch ($key) {
            case "id":
                $this->setId($value);
                break;
            case "title":
                $this->setTitle($value);
                break;
            case "comment":
                $this->setComment($value);
                break;
            case "owner":
                $this->setOwner($value);
                break;
            case "author":
                $this->setAuthor($value);
                break;
            case "question":
                $this->setQuestion($value);
                break;
            case "points":
                $this->setPoints($value);
                break;
            case "est_working_time":
                if (is_array($value)) {
                    $this->setEstimatedWorkingTime($value["h"], $value["m"], $value["s"]);
                }
                break;
            case "shuffle":
                $this->setShuffle($value);
                break;
            case "test_id":
                $this->setTestId($value);
                break;
            case "obj_id":
                $this->setObjId($value);
                break;
            case "outputType":
                $this->setOutputType($value);
                break;
            case "original_id":
                $this->setOriginalId($value);
                break;
            case "page":
                $this->page = &$value;
                break;
            default:
                $this->arrData[$key] = $value;
                break;
        }
    }
    
    public function getNrOfTries()
    {
        return (int) $this->nr_of_tries;
    }
    
    public function setNrOfTries($a_nr_of_tries)
    {
        $this->nr_of_tries = $a_nr_of_tries;
    }

    public function setExportImagePath($a_path)
    {
        $this->export_image_path = (string) $a_path;
    }

    public static function _questionExistsInTest($question_id, $test_id)
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
        if ($result->numRows() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Format self assessment question
     *
     * @param
     * @return
     */
    public function formatSAQuestion($a_q)
    {
        return $this->getSelfAssessmentFormatter()->format($a_q);
    }

    /**
     * @return \ilAssSelfAssessmentQuestionFormatter
     */
    protected function getSelfAssessmentFormatter()
    {
        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssSelfAssessmentQuestionFormatter.php';
        return new \ilAssSelfAssessmentQuestionFormatter();
    }

    // scorm2004-start ???
    
    /**
     * Set prevent rte usage
     *
     * @param	boolean	prevent rte usage
     */
    public function setPreventRteUsage($a_val)
    {
        $this->prevent_rte_usage = $a_val;
    }

    /**
     * Get prevent rte usage
     *
     * @return	boolean	prevent rte usage
     */
    public function getPreventRteUsage()
    {
        return $this->prevent_rte_usage;
    }
    
    /**
     * @param ilAssSelfAssessmentMigrator $migrator
     */
    public function migrateContentForLearningModule(ilAssSelfAssessmentMigrator $migrator)
    {
        $this->lmMigrateQuestionTypeGenericContent($migrator);
        $this->lmMigrateQuestionTypeSpecificContent($migrator);
        $this->saveToDb();
        
        $this->feedbackOBJ->migrateContentForLearningModule($migrator, $this->getId());
    }
    
    /**
     * @param ilAssSelfAssessmentMigrator $migrator
     */
    protected function lmMigrateQuestionTypeGenericContent(ilAssSelfAssessmentMigrator $migrator)
    {
        $this->setQuestion($migrator->migrateToLmContent($this->getQuestion()));
    }
    
    /**
     * @param ilAssSelfAssessmentMigrator $migrator
     */
    protected function lmMigrateQuestionTypeSpecificContent(ilAssSelfAssessmentMigrator $migrator)
    {
        // overwrite if any question type specific content except feedback needs to be migrated
    }
    
    /**
     * Set Self-Assessment Editing Mode.
     *
     * @param	boolean	$a_selfassessmenteditingmode	Self-Assessment Editing Mode
     */
    public function setSelfAssessmentEditingMode($a_selfassessmenteditingmode)
    {
        $this->selfassessmenteditingmode = $a_selfassessmenteditingmode;
    }

    /**
     * Get Self-Assessment Editing Mode.
     *
     * @return	boolean	Self-Assessment Editing Mode
     */
    public function getSelfAssessmentEditingMode()
    {
        return $this->selfassessmenteditingmode;
    }

    /**
     * Set  Default Nr of Tries
     *
     * @param	int	$a_defaultnroftries		Default Nr. of Tries
     */
    public function setDefaultNrOfTries($a_defaultnroftries)
    {
        $this->defaultnroftries = $a_defaultnroftries;
    }
    
    /**
     * Get Default Nr of Tries
     *
     * @return	int	Default Nr of Tries
     */
    public function getDefaultNrOfTries()
    {
        return (int) $this->defaultnroftries;
    }
    
    // scorm2004-end ???

    /**
     * @global ilDBInterface $ilDB
     * @param integer $questionId
     * @return integer $parentObjectId
     */
    public static function lookupParentObjId($questionId)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "SELECT obj_fi FROM qpl_questions WHERE question_id = %s";

        $res = $ilDB->queryF($query, array('integer'), array((int) $questionId));
        $row = $ilDB->fetchAssoc($res);

        return $row['obj_fi'];
    }

    /**
     * returns the parent object id for given original question id
     * (should be a qpl id, but theoretically it can be a tst id, too)
     *
     * @global ilDBInterface $ilDB
     * @param integer $originalQuestionId
     * @return integer $originalQuestionParentObjectId
     *
     * @deprecated: use assQuestion::lookupParentObjId() instead
     */
    public static function lookupOriginalParentObjId($originalQuestionId)
    {
        return self::lookupParentObjId($originalQuestionId);
    }

    protected function duplicateQuestionHints($originalQuestionId, $duplicateQuestionId)
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintList.php';
        $hintIds = ilAssQuestionHintList::duplicateListForQuestion($originalQuestionId, $duplicateQuestionId);
        
        if ($this->isAdditionalContentEditingModePageObject()) {
            require_once 'Modules/TestQuestionPool/classes/class.ilAssHintPage.php';
            
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

    protected function duplicateSkillAssignments($srcParentId, $srcQuestionId, $trgParentId, $trgQuestionId)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
        $assignmentList = new ilAssQuestionSkillAssignmentList($ilDB);
        $assignmentList->setParentObjId($srcParentId);
        $assignmentList->setQuestionIdFilter($srcQuestionId);
        $assignmentList->loadFromDb();
        
        foreach ($assignmentList->getAssignmentsByQuestionId($srcQuestionId) as $assignment) {
            /* @var ilAssQuestionSkillAssignment $assignment */
            
            $assignment->setParentObjId($trgParentId);
            $assignment->setQuestionId($trgQuestionId);
            $assignment->saveToDb();
        }
    }

    public function syncSkillAssignments($srcParentId, $srcQuestionId, $trgParentId, $trgQuestionId)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentList.php';
        $assignmentList = new ilAssQuestionSkillAssignmentList($ilDB);
        $assignmentList->setParentObjId($trgParentId);
        $assignmentList->setQuestionIdFilter($trgQuestionId);
        $assignmentList->loadFromDb();
        
        foreach ($assignmentList->getAssignmentsByQuestionId($trgQuestionId) as $assignment) {
            /* @var ilAssQuestionSkillAssignment $assignment */

            $assignment->deleteFromDb();
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
     * @param integer $active_id
     * @param integer $pass
     * @return boolean $answered
     */
    public function isAnswered($active_id, $pass = null)
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
     *
     * @param integer $questionId
     * @return boolean $obligationPossible
     */
    public static function isObligationPossible($questionId)
    {
        return false;
    }
    
    public function isAutosaveable()
    {
        return true;
    }
    
    /**
     * returns the number of existing solution records
     * for the given test active / pass and given question id
     *
     * @access protected
     * @static
     * @global ilDBInterface $ilDB
     * @param integer $activeId
     * @param integer $pass
     * @param integer $questionId
     * @return integer $numberOfExistingSolutionRecords
     */
    protected static function getNumExistingSolutionRecords($activeId, $pass, $questionId)
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
    
    /**
     * getter for additional content editing mode for this question
     *
     * @access public
     * @return string
     */
    public function getAdditionalContentEditingMode()
    {
        return $this->additinalContentEditingMode;
    }
    
    /**
     * setter for additional content editing mode for this question
     *
     * @access public
     * @return string
     */
    public function setAdditionalContentEditingMode($additinalContentEditingMode)
    {
        if (!in_array($additinalContentEditingMode, $this->getValidAdditionalContentEditingModes())) {
            require_once 'Modules/TestQuestionPool/exceptions/class.ilTestQuestionPoolException.php';
            throw new ilTestQuestionPoolException('invalid additional content editing mode given: ' . $additinalContentEditingMode);
        }
        
        $this->additinalContentEditingMode = $additinalContentEditingMode;
    }
    
    /**
     * isser for additional "pageobject" content editing mode
     *
     * @access public
     * @return boolean
     */
    public function isAdditionalContentEditingModePageObject()
    {
        return $this->getAdditionalContentEditingMode() == assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_PAGE_OBJECT;
    }
    
    /**
     * returns the fact wether the passed additional content mode is valid or not
     *
     * @access public
     * @param string $additionalContentEditingMode
     * @return boolean $isValidAdditionalContentEditingMode
     */
    public function isValidAdditionalContentEditingMode($additionalContentEditingMode)
    {
        if (in_array($additionalContentEditingMode, $this->getValidAdditionalContentEditingModes())) {
            return true;
        }
        
        return false;
    }
    
    /**
     * getter for valid additional content editing modes
     *
     * @access public
     * @return array
     */
    public function getValidAdditionalContentEditingModes()
    {
        return array(
            self::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT,
            self::ADDITIONAL_CONTENT_EDITING_MODE_PAGE_OBJECT
        );
    }
    
    /**
     * @param ilQuestionChangeListener $listener
     */
    public function addQuestionChangeListener(ilQuestionChangeListener $listener)
    {
        $this->questionChangeListeners[] = $listener;
    }
    
    /**
     * @return array[ilQuestionChangeListener]
     */
    public function getQuestionChangeListeners()
    {
        return $this->questionChangeListeners;
    }
    
    private function notifyQuestionCreated()
    {
        foreach ($this->getQuestionChangeListeners() as $listener) {
            $listener->notifyQuestionCreated($this);
        }
    }
    
    private function notifyQuestionEdited()
    {
        foreach ($this->getQuestionChangeListeners() as $listener) {
            $listener->notifyQuestionEdited($this);
        }
    }
    
    private function notifyQuestionDeleted()
    {
        foreach ($this->getQuestionChangeListeners() as $listener) {
            $listener->notifyQuestionDeleted($this);
        }
    }

    /**
     * @return ilAssHtmlUserSolutionPurifier
     */
    public function getHtmlUserSolutionPurifier()
    {
        require_once 'Services/Html/classes/class.ilHtmlPurifierFactory.php';
        return ilHtmlPurifierFactory::_getInstanceByType('qpl_usersolution');
    }

    /**
     * @return ilAssHtmlUserSolutionPurifier
     */
    public function getHtmlQuestionContentPurifier()
    {
        require_once 'Services/Html/classes/class.ilHtmlPurifierFactory.php';
        return ilHtmlPurifierFactory::_getInstanceByType('qpl_usersolution');
    }
    
    protected function buildQuestionDataQuery()
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

    public function setLastChange($lastChange)
    {
        $this->lastChange = $lastChange;
    }

    public function getLastChange()
    {
        return $this->lastChange;
    }

    /**
     * Get a restulset for the current user solution for a this question by active_id and pass
     *
     * @param int $active_id
     * @param int $pass
     * @param bool|true $authorized
     * @global ilDBInterface $ilDB
     *
     * @return object
     */
    protected function getCurrentSolutionResultSet($active_id, $pass, $authorized = true)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

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

            return $ilDB->queryF(
                $query,
                array('integer', 'integer', 'integer', 'integer', 'integer'),
                array($active_id, $this->getId(), $pass, $this->getStep(), (int) $authorized)
            );
        } else {
            $query = "
				SELECT *
				FROM tst_solutions
				WHERE active_fi = %s
				AND question_fi = %s
				AND pass = %s
				AND authorized = %s
			";

            return $ilDB->queryF(
                $query,
                array('integer', 'integer', 'integer', 'integer'),
                array($active_id, $this->getId(), $pass, (int) $authorized)
            );
        }
    }

    /**
     * @param $solutionId
     * @global ilDBInterface $ilDB
     *
     * @return int
     */
    protected function removeSolutionRecordById($solutionId)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        return $ilDB->manipulateF(
            "DELETE FROM tst_solutions WHERE solution_id = %s",
            array('integer'),
            array($solutionId)
        );
    }
    
    // hey: prevPassSolutions - selected file reuse, copy solution records
    /**
     * @param $solutionId
     * @global ilDBInterface $ilDB
     *
     * @return int
     */
    protected function getSolutionRecordById($solutionId)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            "SELECT * FROM tst_solutions WHERE solution_id = %s",
            array('integer'),
            array($solutionId)
        );
        
        while ($row = $ilDB->fetchAssoc($res)) {
            return $row;
        }
    }
    // hey.
    
    /**
     * @param int $active_id
     * @param int $pass
     * @param bool|true $authorized
     * @global ilDBInterface $ilDB
     *
     * @return int
     */
    public function removeIntermediateSolution($active_id, $pass)
    {
        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use ($active_id, $pass) {
            $this->removeCurrentSolution($active_id, $pass, false);
        });
    }

    /**
     * @param int $active_id
     * @param int $pass
     * @param bool|true $authorized
     * @global ilDBInterface $ilDB
     *
     * @return int
     */
    public function removeCurrentSolution($active_id, $pass, $authorized = true)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($this->getStep() !== null) {
            $query = "
				DELETE FROM tst_solutions
				WHERE active_fi = %s
				AND question_fi = %s
				AND pass = %s
				AND step = %s
				AND authorized = %s
			";

            return $ilDB->manipulateF(
                $query,
                array('integer', 'integer', 'integer', 'integer', 'integer'),
                array($active_id, $this->getId(), $pass, $this->getStep(), (int) $authorized)
            );
        } else {
            $query = "
				DELETE FROM tst_solutions
				WHERE active_fi = %s
				AND question_fi = %s
				AND pass = %s
				AND authorized = %s
			";

            return $ilDB->manipulateF(
                $query,
                array('integer', 'integer', 'integer', 'integer'),
                array($active_id, $this->getId(), $pass, (int) $authorized)
            );
        }
    }

    // fau: testNav - add timestamp as parameter to saveCurrentSolution
    /**
     * @param int $active_id
     * @param int $pass
     * @param mixed $value1
     * @param mixed $value2
     * @param bool|true $authorized
     * @param int|null	$tstamp
     * @global ilDBInterface $ilDB
     *
     * @return int
     */
    public function saveCurrentSolution($active_id, $pass, $value1, $value2, $authorized = true, $tstamp = null)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $next_id = $ilDB->nextId("tst_solutions");
        
        $fieldData = array(
            "solution_id" => array("integer", $next_id),
            "active_fi" => array("integer", $active_id),
            "question_fi" => array("integer", $this->getId()),
            "value1" => array("clob", $value1),
            "value2" => array("clob", $value2),
            "pass" => array("integer", $pass),
            "tstamp" => array("integer", isset($tstamp) ? $tstamp : time()),
            'authorized' => array('integer', (int) $authorized)
        );

        if ($this->getStep() !== null) {
            $fieldData['step'] = array("integer", $this->getStep());
        }

        return $ilDB->insert("tst_solutions", $fieldData);
    }
    // fau.

    /**
     * @param int $active_id
     * @param int $pass
     * @param mixed $value1
     * @param mixed $value2
     * @param bool|true $authorized
     * @global ilDBInterface $ilDB
     *
     * @return int
     */
    public function updateCurrentSolution($solutionId, $value1, $value2, $authorized = true)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $fieldData = array(
            "value1" => array("clob", $value1),
            "value2" => array("clob", $value2),
            "tstamp" => array("integer", time()),
            'authorized' => array('integer', (int) $authorized)
        );

        if ($this->getStep() !== null) {
            $fieldData['step'] = array("integer", $this->getStep());
        }

        return $ilDB->update("tst_solutions", $fieldData, array(
            'solution_id' => array('integer', $solutionId)
        ));
    }

    // fau: testNav - added parameter to keep the timestamp (default: false)
    public function updateCurrentSolutionsAuthorization($activeId, $pass, $authorized, $keepTime = false)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

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

        return $ilDB->update('tst_solutions', $fieldData, $whereData);
    }
    // fau.
    
    // hey: prevPassSolutions - motivation slowly decreases on imagemap
    const KEY_VALUES_IMPLOSION_SEPARATOR = ':';
    protected static function getKeyValuesImplosionSeparator()
    {
        return self::KEY_VALUES_IMPLOSION_SEPARATOR;
    }
    public static function implodeKeyValues($keyValues)
    {
        return implode(self::getKeyValuesImplosionSeparator(), $keyValues);
    }
    public static function explodeKeyValues($keyValues)
    {
        return explode(self::getKeyValuesImplosionSeparator(), $keyValues);
    }
    
    protected function deleteDummySolutionRecord($activeId, $passIndex)
    {
        foreach ($this->getSolutionValues($activeId, $passIndex, false) as $solutionRec) {
            if (0 == strlen($solutionRec['value1']) && 0 == strlen($solutionRec['value2'])) {
                $this->removeSolutionRecordById($solutionRec['solution_id']);
            }
        }
    }
    
    protected function isDummySolutionRecord($solutionRecord)
    {
        return !strlen($solutionRecord['value1']) && !strlen($solutionRecord['value2']);
    }
    
    protected function deleteSolutionRecordByValues($activeId, $passIndex, $authorized, $matchValues)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $ilDB = $DIC['ilDB'];
        
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
                    require_once 'Modules/TestQuestionPool/exceptions/class.ilTestQuestionPoolException.php';
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
        
        $ilDB->manipulateF($query, $types, $values);
    }
    
    protected function duplicateIntermediateSolutionAuthorized($activeId, $passIndex)
    {
        foreach ($this->getSolutionValues($activeId, $passIndex, false) as $rec) {
            $this->saveCurrentSolution($activeId, $passIndex, $rec['value1'], $rec['value2'], true, $rec['tstamp']);
        }
    }
    
    protected function forceExistingIntermediateSolution($activeId, $passIndex, $considerDummyRecordCreation)
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
     * @param \ilObjTestGateway $resultGateway
     */
    public static function setResultGateway($resultGateway)
    {
        self::$resultGateway = $resultGateway;
    }

    /**
     * @return \ilObjTestGateway
     */
    public static function getResultGateway()
    {
        return self::$resultGateway;
    }

    /**
     * @param int|null $step
     */
    public function setStep($step)
    {
        $this->step = $step;
    }

    /**
     * @return int|null
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param $time1
     * @param $time2
     * @return string
     */
    public static function sumTimesInISO8601FormatH_i_s_Extended($time1, $time2)
    {
        $time = assQuestion::convertISO8601FormatH_i_s_ExtendedToSeconds($time1) +
                assQuestion::convertISO8601FormatH_i_s_ExtendedToSeconds($time2);
        return gmdate('H:i:s', $time);
    }

    /**
     * @param $time
     * @return int
     */
    public static function convertISO8601FormatH_i_s_ExtendedToSeconds($time)
    {
        $sec = 0;
        $time_array = explode(':', $time);
        if (sizeof($time_array) == 3) {
            $sec += $time_array[0] * 3600;
            $sec += $time_array[1] * 60;
            $sec += $time_array[2];
        }
        return $sec;
    }

    public function toJSON()
    {
        return json_encode(array());
    }
    
    abstract public function duplicate($for_test = true, $title = "", $author = "", $owner = "", $testObjId = null);

    // hey: prevPassSolutions - check for authorized solution
    public function intermediateSolutionExists($active_id, $pass)
    {
        $solutionAvailability = $this->lookupForExistingSolutions($active_id, $pass);
        return (bool) $solutionAvailability['intermediate'];
    }
    public function authorizedSolutionExists($active_id, $pass)
    {
        $solutionAvailability = $this->lookupForExistingSolutions($active_id, $pass);
        return (bool) $solutionAvailability['authorized'];
    }
    public function authorizedOrIntermediateSolutionExists($active_id, $pass)
    {
        $solutionAvailability = $this->lookupForExistingSolutions($active_id, $pass);
        return (bool) $solutionAvailability['authorized'] || (bool) $solutionAvailability['intermediate'];
    }
    // hey.
    
    /**
     * @param $active_id
     * @param $pass
     * @return integer
     */
    protected function lookupMaxStep($active_id, $pass)
    {
        /** @var ilDBInterface $ilDB */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryF(
            "SELECT MAX(step) max_step FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s",
            array("integer", "integer", "integer"),
            array($active_id, $pass, $this->getId())
        );

        $row = $ilDB->fetchAssoc($res);

        $maxStep = $row['max_step'];

        return $maxStep;
    }

    // fau: testNav - new function lookupForExistingSolutions
    /**
     * Lookup if an authorized or intermediate solution exists
     * @param 	int 		$activeId
     * @param 	int 		$pass
     * @return 	array		['authorized' => bool, 'intermediate' => bool]
     */
    public function lookupForExistingSolutions($activeId, $pass)
    {
        /** @var $ilDB \ilDBInterface  */
        global $DIC;
        $ilDB = $DIC['ilDB'];

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
            $query .= " AND step = " . $ilDB->quote((int) $this->getStep(), 'integer') . " ";
        }

        $query .= "
			GROUP BY authorized
		";

        $result = $ilDB->queryF($query, array('integer', 'integer', 'integer'), array($activeId, $this->getId(), $pass));

        while ($row = $ilDB->fetchAssoc($result)) {
            if ($row['authorized']) {
                $return['authorized'] = $row['cnt'] > 0;
            } else {
                $return['intermediate'] = $row['cnt'] > 0;
            }
        }
        return $return;
    }
    // fau.

    public function isAddableAnswerOptionValue($qIndex, $answerOptionValue)
    {
        return false;
    }
    
    public function addAnswerOptionValue($qIndex, $answerOptionValue, $points)
    {
    }

    public function removeAllExistingSolutions()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $query = "DELETE FROM tst_solutions WHERE question_fi = %s";
        
        $DIC->database()->manipulateF($query, array('integer'), array($this->getId()));
    }
    
    public function removeExistingSolutions($activeId, $pass)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "
			DELETE FROM tst_solutions
			WHERE active_fi = %s
			AND question_fi = %s
			AND pass = %s
		";

        if ($this->getStep() !== null) {
            $query .= " AND step = " . $ilDB->quote((int) $this->getStep(), 'integer') . " ";
        }

        return $ilDB->manipulateF(
            $query,
            array('integer', 'integer', 'integer'),
            array($activeId, $this->getId(), $pass)
        );
    }

    public function resetUsersAnswer($activeId, $pass)
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

    public function removeResultRecord($activeId, $pass)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $query = "
			DELETE FROM tst_test_result
			WHERE active_fi = %s
			AND question_fi = %s
			AND pass = %s
		";

        if ($this->getStep() !== null) {
            $query .= " AND step = " . $ilDB->quote((int) $this->getStep(), 'integer') . " ";
        }

        return $ilDB->manipulateF(
            $query,
            array('integer', 'integer', 'integer'),
            array($activeId, $this->getId(), $pass)
        );
    }
    
    public static function missingResultRecordExists($activeId, $pass, $questionIds)
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
    
    public static function getQuestionsMissingResultRecord($activeId, $pass, $questionIds)
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

    public static function lookupResultRecordExist($activeId, $questionId, $pass)
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
    
    /**
     * @param array $indexedValues
     * @return array $valuePairs
     */
    public function fetchValuePairsFromIndexedValues(array $indexedValues)
    {
        $valuePairs = array();
        
        foreach ($indexedValues as $value1 => $value2) {
            $valuePairs[] = array('value1' => $value1, 'value2' => $value2);
        }
        
        return $valuePairs;
    }
    
    /**
     * @param array $valuePairs
     * @return array $indexedValues
     */
    public function fetchIndexedValuesFromValuePairs(array $valuePairs)
    {
        $indexedValues = array();
        
        foreach ($valuePairs as $valuePair) {
            $indexedValues[ $valuePair['value1'] ] = $valuePair['value2'];
        }
        
        return $indexedValues;
    }

    /**
     * @return boolean
     */
    public function areObligationsToBeConsidered()
    {
        return $this->obligationsToBeConsidered;
    }

    /**
     * @param boolean $obligationsToBeConsidered
     */
    public function setObligationsToBeConsidered($obligationsToBeConsidered)
    {
        $this->obligationsToBeConsidered = $obligationsToBeConsidered;
    }

    public function updateTimestamp()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF(
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
    /**
     * @var ilTestQuestionConfig
     */
    private $testQuestionConfigInstance = null;
    
    /**
     * Get the test question configuration (initialised once)
     * @return ilTestQuestionConfig
     */
    public function getTestPresentationConfig()
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
     *
     * @return ilTestQuestionConfig
     */
    protected function buildTestPresentationConfig()
    {
        include_once('Modules/TestQuestionPool/classes/class.ilTestQuestionConfig.php');
        return new ilTestQuestionConfig();
    }
    // hey.
    // fau.

    public function savePartial()
    {
        return false;
    }
}
