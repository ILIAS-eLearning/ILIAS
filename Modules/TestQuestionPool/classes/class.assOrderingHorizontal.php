<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php';
require_once './Modules/TestQuestionPool/classes/class.ilUserQuestionResult.php';

/**
 * Class for horizontal ordering questions
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version	 $Id$
 *
 * @ingroup	ModulesTestQuestionPool
 */
class assOrderingHorizontal extends assQuestion implements ilObjQuestionScoringAdjustable, iQuestionCondition
{
    protected $ordertext;
    protected $textsize;
    protected $separator = "::";
    protected $answer_separator = '{::}';
    
    /**
    * assOrderingHorizontal constructor
    *
    * The constructor takes possible arguments an creates an instance of the assOrderingHorizontal object.
    *
    * @param string $title A title string to describe the question
    * @param string $comment A comment string to describe the question
    * @param string $author A string containing the name of the questions author
    * @param integer $owner A numerical ID to identify the owner/creator
    * @param string $question The question string of the single choice question
    * @see assQuestion:__construct()
    */
    public function __construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = ""
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->ordertext = "";
    }
    
    /**
    * Returns true, if a single choice question is complete for use
    *
    * @return boolean True, if the single choice question is complete for use, otherwise false
    */
    public function isComplete()
    {
        if (strlen($this->title) and ($this->author) and ($this->question) and ($this->getMaximumPoints() > 0)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Saves a assOrderingHorizontal object to a database
    *
    */
    public function saveToDb($original_id = "")
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        parent::saveToDb();
    }

    /**
     * @return string
     */
    public function getAnswerSeparator()
    {
        return $this->answer_separator;
    }

    
    /**
    * Loads a assOrderingHorizontal object from a database
    *
    * @param object $db A pear DB object
    * @param integer $question_id A unique key which defines the multiple choice test in the database
    */
    public function loadFromDb($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->queryF(
            "SELECT qpl_questions.*, " . $this->getAdditionalTableName() . ".* FROM qpl_questions LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = qpl_questions.question_id WHERE qpl_questions.question_id = %s",
            array("integer"),
            array($question_id)
        );
        if ($result->numRows() == 1) {
            $data = $ilDB->fetchAssoc($result);
            $this->setId($question_id);
            $this->setObjId($data["obj_fi"]);
            $this->setTitle($data["title"]);
            $this->setComment($data["description"]);
            $this->setOriginalId($data["original_id"]);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
            $this->setOrderText($data["ordertext"]);
            $this->setTextSize($data["textsize"]);
            $this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
            
            try {
                $this->setLifecycle(ilAssQuestionLifecycle::getInstance($data['lifecycle']));
            } catch (ilTestQuestionPoolInvalidArgumentException $e) {
                $this->setLifecycle(ilAssQuestionLifecycle::getDraftInstance());
            }
            
            try {
                $this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
            } catch (ilTestQuestionPoolException $e) {
            }
        }

        parent::loadFromDb($question_id);
    }

    /**
    * Duplicates an assOrderingHorizontal
    */
    public function duplicate($for_test = true, $title = "", $author = "", $owner = "", $testObjId = null)
    {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return;
        }
        // duplicate the question in database
        $this_id = $this->getId();
        $thisObjId = $this->getObjId();
        
        $clone = $this;
        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
        $original_id = assQuestion::_getOriginalId($this->id);
        $clone->id = -1;
        
        if ((int) $testObjId > 0) {
            $clone->setObjId($testObjId);
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
            $clone->saveToDb($original_id);
        } else {
            $clone->saveToDb();
        }

        // copy question page content
        $clone->copyPageOfQuestion($this_id);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($this_id);

        $clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    /**
    * Copies an assOrderingHorizontal object
    */
    public function copyObject($target_questionpool_id, $title = "")
    {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return;
        }
        // duplicate the question in database
        $clone = $this;
        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
        $original_id = assQuestion::_getOriginalId($this->id);
        $clone->id = -1;
        $source_questionpool_id = $this->getObjId();
        $clone->setObjId($target_questionpool_id);
        if ($title) {
            $clone->setTitle($title);
        }
        $clone->saveToDb();
        // copy question page content
        $clone->copyPageOfQuestion($original_id);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($original_id);

        $clone->onCopy($source_questionpool_id, $original_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    public function createNewOriginalFromThisDuplicate($targetParentId, $targetQuestionTitle = "")
    {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return;
        }

        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");

        $sourceQuestionId = $this->id;
        $sourceParentId = $this->getObjId();

        // duplicate the question in database
        $clone = $this;
        $clone->id = -1;

        $clone->setObjId($targetParentId);

        if ($targetQuestionTitle) {
            $clone->setTitle($targetQuestionTitle);
        }

        $clone->saveToDb();
        // copy question page content
        $clone->copyPageOfQuestion($sourceQuestionId);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($sourceQuestionId);

        $clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    /**
    * Returns the maximum points, a learner can reach answering the question
    *
    * @see $points
    */
    public function getMaximumPoints()
    {
        return $this->getPoints();
    }

    /**
     * Returns the points, a learner has reached answering the question.
     * The points are calculated from the given answers.
     *
     * @access public
     * @param integer $active_id
     * @param integer $pass
     * @param boolean $returndetails (deprecated !!)
     * @return integer/array $points/$details (array $details is deprecated !!)
     */
    public function calculateReachedPoints($active_id, $pass = null, $authorizedSolution = true, $returndetails = false)
    {
        if ($returndetails) {
            throw new ilTestException('return details not implemented for ' . __METHOD__);
        }
        
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $found_values = array();
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorizedSolution);
        $points = 0;
        $data = $ilDB->fetchAssoc($result);

        $points = $this->calculateReachedPointsForSolution($data['value1']);
        
        return $points;
    }
    
    /**
     * Splits the answer string either by space(s) or the separator (eg. ::) and
     * trims the resulting array elements.
     *
     * @param string $in_string OrderElements
     * @param string $separator to be used for splitting.
     *
     * @return array
     */
    public function splitAndTrimOrderElementText($in_string, $separator)
    {
        $result = array();
        include_once "./Services/Utilities/classes/class.ilStr.php";
        
        if (ilStr::strPos($in_string, $separator) === false) {
            $result = preg_split("/\\s+/", $in_string);
        } else {
            $result = explode($separator, $in_string);
        }
        
        foreach ($result as $key => $value) {
            $result[$key] = trim($value);
        }
        
        return $result;
    }
    
    public function getSolutionSubmit()
    {
        return $_POST["orderresult"];
    }
    
    /**
     * Saves the learners input of the question to the database.
     *
     * @access public
     * @param integer $active_id Active id of the user
     * @param integer $pass Test pass
     * @return boolean $status
     */
    public function saveWorkingData($active_id, $pass = null, $authorized = true)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        if (is_null($pass)) {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $pass = ilObjTest::_getPass($active_id);
        }

        $entered_values = false;

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use (&$entered_values, $active_id, $pass, $authorized) {
            $this->removeCurrentSolution($active_id, $pass, $authorized);

            $solutionSubmit = $this->getSolutionSubmit();

            $entered_values = false;
            if (strlen($solutionSubmit)) {
                $this->saveCurrentSolution($active_id, $pass, $_POST['orderresult'], null, $authorized);
                $entered_values = true;
            }
        });

        if ($entered_values) {
            include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction($this->lng->txtlng("assessment", "log_user_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
            }
        } else {
            include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction($this->lng->txtlng("assessment", "log_user_not_entered_values", ilObjAssessmentFolder::_getLogLanguage()), $active_id, $this->getId());
            }
        }

        return true;
    }

    public function saveAdditionalQuestionDataToDb()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // save additional data
        $ilDB->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName()
                            . " WHERE question_fi = %s",
            array( "integer" ),
            array( $this->getId() )
        );

        $ilDB->manipulateF(
            "INSERT INTO " . $this->getAdditionalTableName()
                            . " (question_fi, ordertext, textsize) VALUES (%s, %s, %s)",
            array( "integer", "text", "float" ),
            array(
                                $this->getId(),
                                $this->getOrderText(),
                                ($this->getTextSize() < 10) ? null : $this->getTextSize()
                            )
        );
    }

    /**
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    */
    public function getQuestionType()
    {
        return "assOrderingHorizontal";
    }
    
    /**
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    */
    public function getAdditionalTableName()
    {
        return "qpl_qst_horder";
    }
    
    /**
    * Returns the name of the answer table in the database
    *
    * @return string The answer table name
    */
    public function getAnswerTableName()
    {
        return "";
    }
    
    /**
    * Deletes datasets from answers tables
    *
    * @param integer $question_id The question id which should be deleted in the answers table
    */
    public function deleteAnswers($question_id)
    {
    }

    /**
    * Collects all text in the question which could contain media objects
    * which were created with the Rich Text Editor
    */
    public function getRTETextWithMediaObjects()
    {
        $text = parent::getRTETextWithMediaObjects();
        return $text;
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLS($worksheet, $startrow, $active_id, $pass)
    {
        parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);

        $solutionvalue = "";
        $solutions = &$this->getSolutionValues($active_id, $pass);
        $solutionvalue = str_replace("{::}", " ", $solutions[0]["value1"]);
        $i = 1;
        $worksheet->setCell($startrow + $i, 0, $solutionvalue);
        $i++;

        return $startrow + $i + 1;
    }
    
    /**
    * Creates a question from a QTI file
    *
    * Receives parameters from a QTI parser and creates a valid ILIAS question object
    *
    * @param object $item The QTI item object
    * @param integer $questionpool_id The id of the parent questionpool
    * @param integer $tst_id The id of the parent test if the question is part of a test
    * @param object $tst_object A reference to the parent test object
    * @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
    * @param array $import_mapping An array containing references to included ILIAS objects
    */
    public function fromXML(&$item, &$questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping, array $solutionhints = [])
    {
        include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assOrderingHorizontalImport.php";
        $import = new assOrderingHorizontalImport($this);
        $import->fromXML($item, $questionpool_id, $tst_id, $tst_object, $question_counter, $import_mapping);
    }
    
    /**
    * Returns a QTI xml representation of the question and sets the internal
    * domxml variable with the DOM XML representation of the QTI xml representation
    *
    * @return string The QTI xml representation of the question
    */
    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
    {
        include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assOrderingHorizontalExport.php";
        $export = new assOrderingHorizontalExport($this);
        return $export->toXML($a_include_header, $a_include_binary, $a_shuffle, $test_output, $force_image_references);
    }

    /**
    * Returns the best solution for a given pass of a participant
    *
    * @return array An associated array containing the best solution
    */
    public function getBestSolution($active_id, $pass)
    {
        $user_solution = array();
        return $user_solution;
    }
    
    /**
    * Get ordering elements from order text
    *
    * @return array Ordering elements
    */
    public function getOrderingElements()
    {
        return $this->splitAndTrimOrderElementText($this->getOrderText(), $this->separator);
    }
    
    /**
    * Get ordering elements from order text in random sequence
    *
    * @return array Ordering elements
    */
    public function getRandomOrderingElements()
    {
        $elements = $this->getOrderingElements();
        $elements = $this->getShuffler()->shuffle($elements);
        return $elements;
    }
    
    /**
    * Get order text
    *
    * @return string Order text
    */
    public function getOrderText()
    {
        return $this->ordertext;
    }
    
    /**
    * Set order text
    *
    * @param string $a_value Order text
    */
    public function setOrderText($a_value)
    {
        $this->ordertext = $a_value;
    }
    
    /**
    * Get text size
    *
    * @return double Text size in percent
    */
    public function getTextSize()
    {
        return $this->textsize;
    }
    
    /**
    * Set text size
    *
    * @param double $a_value Text size in percent
    */
    public function setTextSize($a_value)
    {
        if ($a_value >= 10) {
            $this->textsize = $a_value;
        }
    }
    
    /**
    * Get order text separator
    *
    * @return string Separator
    */
    public function getSeparator()
    {
        return $this->separator;
    }
    
    /**
    * Set order text separator
    *
    * @param string $a_value Separator
    */
    public function setSeparator($a_value)
    {
        $this->separator = $a_value;
    }
    
    /**
    * Object getter
    */
    public function __get($value)
    {
        switch ($value) {
            case "ordertext":
                return $this->getOrderText();
                break;
            case "textsize":
                return $this->getTextSize();
                break;
            case "separator":
                return $this->getSeparator();
                break;
            default:
                return parent::__get($value);
                break;
        }
    }

    /**
    * Object setter
    */
    public function __set($key, $value)
    {
        switch ($key) {
            case "ordertext":
                $this->setOrderText($value);
                break;
            case "textsize":
                $this->setTextSize($value);
                break;
            case "separator":
                $this->setSeparator($value);
                break;
            default:
                parent::__set($key, $value);
                break;
        }
    }
    
    public function supportsJavascriptOutput()
    {
        return true;
    }

    public function supportsNonJsOutput()
    {
        return false;
    }

    /**
     * Returns a JSON representation of the question
     */
    public function toJSON()
    {
        include_once("./Services/RTE/classes/class.ilRTE.php");
        $result = array();
        $result['id'] = (int) $this->getId();
        $result['type'] = (string) $this->getQuestionType();
        $result['title'] = (string) $this->getTitle();
        $result['question'] = $this->formatSAQuestion($this->getQuestion());
        $result['nr_of_tries'] = (int) $this->getNrOfTries();
        $result['shuffle'] = (bool) true;
        $result['points'] = (bool) $this->getPoints();
        $result['textsize'] = ((int) $this->getTextSize()) // #10923
            ? (int) $this->getTextSize()
            : 100;
        $result['feedback'] = array(
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        );
        
        $arr = array();
        foreach ($this->getOrderingElements() as $order => $answer) {
            array_push($arr, array(
                "answertext" => (string) $answer,
                "order" => (int) $order + 1
            ));
        }
        $result['answers'] = $arr;

        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
        $result['mobs'] = $mobs;
    
        return json_encode($result);
    }

    /**
     * Get all available operations for a specific question
     *
     * @param $expression
     *
     * @internal param string $expression_type
     * @return array
     */
    public function getOperators($expression)
    {
        require_once "./Modules/TestQuestionPool/classes/class.ilOperatorsExpressionMapping.php";
        return ilOperatorsExpressionMapping::getOperatorsByExpression($expression);
    }

    /**
     * Get all available expression types for a specific question
     * @return array
     */
    public function getExpressionTypes()
    {
        return array(
            iQuestionCondition::PercentageResultExpression,
            iQuestionCondition::NumericResultExpression,
            iQuestionCondition::OrderingResultExpression,
            iQuestionCondition::StringResultExpression,
        );
    }

    /**
    * Get the user solution for a question by active_id and the test pass
    *
    * @param int $active_id
    * @param int $pass
    *
    * @return ilUserQuestionResult
    */
    public function getUserQuestionResult($active_id, $pass)
    {
        /** @var ilDBInterface $ilDB */
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = new ilUserQuestionResult($this, $active_id, $pass);

        $maxStep = $this->lookupMaxStep($active_id, $pass);

        if ($maxStep !== null) {
            $data = $ilDB->queryF(
                "SELECT value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s",
                array("integer", "integer", "integer","integer"),
                array($active_id, $pass, $this->getId(), $maxStep)
            );
        } else {
            $data = $ilDB->queryF(
                "SELECT value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s",
                array("integer", "integer", "integer"),
                array($active_id, $pass, $this->getId())
            );
        }
        $row = $ilDB->fetchAssoc($data);

        $answer_elements = $this->splitAndTrimOrderElementText($row["value1"], $this->answer_separator);
        $elements = $this->getOrderingElements();
        $solutions = array();

        foreach ($answer_elements as $answer) {
            foreach ($elements as $key => $element) {
                if ($element == $answer) {
                    $result->addKeyValue($key + 1, $answer);
                }
            }
        }

        $glue = " ";
        if ($this->answer_separator = '{::}') {
            $glue = "";
        }
        $result->addKeyValue(null, join($glue, $answer_elements));

        $points = $this->calculateReachedPoints($active_id, $pass);
        $max_points = $this->getMaximumPoints();

        $result->setReachedPercentage(($points / $max_points) * 100);

        return $result;
    }

    /**
     * If index is null, the function returns an array with all anwser options
     * Else it returns the specific answer option
     *
     * @param null|int $index
     *
     * @return array|ASS_AnswerSimple
     */
    public function getAvailableAnswerOptions($index = null)
    {
        $elements = $this->getOrderingElements();
        if ($index !== null) {
            if (array_key_exists($index, $elements)) {
                return $elements[$index];
            }
            return null;
        } else {
            return $elements;
        }
    }

    /**
     * @param $value
     * @return int
     */
    protected function calculateReachedPointsForSolution($value)
    {
        $value = $this->splitAndTrimOrderElementText($value, $this->answer_separator);
        $value = join($this->answer_separator, $value);
        if (strcmp($value, join($this->answer_separator, $this->getOrderingElements())) == 0) {
            $points = $this->getPoints();
            return $points;
        }
        return 0;
    }

    // fau: testNav - new function getTestQuestionConfig()
    /**
     * Get the test question configuration
     * @return ilTestQuestionConfig
     */
    // hey: refactored identifiers
    public function buildTestPresentationConfig()
    // hey.
    {
        // hey: refactored identifiers
        return parent::buildTestPresentationConfig()
        // hey.
            ->setIsUnchangedAnswerPossible(true)
            ->setUseUnchangedAnswerLabel($this->lng->txt('tst_unchanged_order_is_correct'));
    }
    // fau.
}
