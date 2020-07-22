<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjAnswerScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php';
require_once './Modules/TestQuestionPool/classes/class.ilUserQuestionResult.php';

/**
 * Class for numeric questions
 *
 * assNumeric is a class for numeric questions. To solve a numeric
 * question, a learner has to enter a numerical value in a defined range.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Nina Gharib <nina@wgserve.de>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup		ModulesTestQuestionPool
 */
class assNumeric extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition
{
    protected $lower_limit;
    protected $upper_limit;

    /** @var $maxchars integer The maximum number of characters for the numeric input field. */
    public $maxchars;

    /**
     * assNumeric constructor
     *
     * The constructor takes possible arguments an creates an instance of the assNumeric object.
     *
     * @param string $title A title string to describe the question
     * @param string $comment A comment string to describe the question
     * @param string $author A string containing the name of the questions author
     * @param integer $owner A numerical ID to identify the owner/creator
     * @param string $question The question string of the numeric question
     */
    public function __construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = ""
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->maxchars = 6;
    }

    /**
     * Returns true, if a numeric question is complete for use
     *
     * @return boolean True, if the numeric question is complete for use, otherwise false
     */
    public function isComplete()
    {
        if (
            strlen($this->title)
            && $this->author
            && $this->question
            && $this->getMaximumPoints() > 0
        ) {
            return true;
        }
        return false;
    }

    /**
     * Saves a assNumeric object to a database
     *
     * @param string $original_id
     */
    public function saveToDb($original_id = "")
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();
        parent::saveToDb($original_id);
    }

    /**
     * Loads a assNumeric object from a database
     *
     * @param integer $question_id A unique key which defines the multiple choice test in the database
     */
    public function loadFromDb($question_id)
    {
        /** @var $ilDB ilDBInterface */
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
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setOriginalId($data["original_id"]);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            require_once './Services/RTE/classes/class.ilRTE.php';
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
            $this->setMaxChars($data["maxnumofchars"]);
            $this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
            
            try {
                $this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
            } catch (ilTestQuestionPoolException $e) {
            }
        }

        $result = $ilDB->queryF(
            "SELECT * FROM qpl_num_range WHERE question_fi = %s ORDER BY aorder ASC",
            array('integer'),
            array($question_id)
        );

        require_once './Modules/TestQuestionPool/classes/class.assNumericRange.php';
        if ($result->numRows() > 0) {
            /** @noinspection PhpAssignmentInConditionInspection */
            while ($data = $ilDB->fetchAssoc($result)) {
                $this->setPoints($data['points']);
                $this->setLowerLimit($data['lowerlimit']);
                $this->setUpperLimit($data['upperlimit']);
            }
        }

        parent::loadFromDb($question_id);
    }

    /**
     * Duplicates an assNumericQuestion
     *
     * @param bool   		$for_test
     * @param string 		$title
     * @param string 		$author
     * @param string 		$owner
     * @param integer|null	$testObjId
     *
     * @return void|integer Id of the clone or nothing.
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
        require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
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
     * Copies an assNumeric object
     *
     * @param integer	$target_questionpool_id
     * @param string	$title
     *
     * @return void|integer Id of the clone or nothing.
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

    public function getLowerLimit()
    {
        return $this->lower_limit;
    }

    public function getUpperLimit()
    {
        return $this->upper_limit;
    }

    public function setLowerLimit($a_limit)
    {
        $a_limit = str_replace(',', '.', $a_limit);
        $this->lower_limit = $a_limit;
    }

    public function setUpperLimit($a_limit)
    {
        $a_limit = str_replace(',', '.', $a_limit);
        $this->upper_limit = $a_limit;
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

    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $previewSession)
    {
        $points = 0;
        if ($this->contains($previewSession->getParticipantsSolution())) {
            $points = $this->getPoints();
        }

        $reachedPoints = $this->deductHintPointsFromReachedPoints($previewSession, $points);
        
        return $this->ensureNonNegativePoints($reachedPoints);
    }

    /**
     * Returns the points, a learner has reached answering the question.
     * The points are calculated from the given answers.
     *
     * @param integer $active_id
     * @param integer $pass
     * @param boolean $returndetails (deprecated !!)
     *
     * @throws ilTestException
     *
     * @return integer|array $points/$details (array $details is deprecated !!)
     */
    public function calculateReachedPoints($active_id, $pass = null, $authorizedSolution = true, $returndetails = false)
    {
        if ($returndetails) {
            throw new ilTestException('return details not implemented for ' . __METHOD__);
        }

        /** @var $ilDB ilDBInterface */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $found_values = array();
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorizedSolution);
        $data = $ilDB->fetchAssoc($result);

        $enteredvalue = $data["value1"];

        $points = 0;
        if ($this->contains($enteredvalue)) {
            $points = $this->getPoints();
        }

        return $points;
    }

    /**
     * Checks for a given value within the range
     *
     * @see $upperlimit
     * @see $lowerlimit
     *
     * @param double $value The value to check
     *
     * @return boolean TRUE if the value is in the range, FALSE otherwise
     */
    public function contains($value)
    {
        require_once './Services/Math/classes/class.EvalMath.php';
        $eval = new EvalMath();
        $eval->suppress_errors = true;
        $result = $eval->e($value);
        if (($result === false) || ($result === true)) {
            return false;
        }

        if (($result >= $eval->e($this->getLowerLimit())) && ($result <= $eval->e($this->getUpperLimit()))) {
            return true;
        }
        return false;
    }
    
    protected function isValidNumericSubmitValue($submittedValue)
    {
        if (is_numeric($submittedValue)) {
            return true;
        }
        
        if (preg_match('/^[-+]{0,1}\d+\/\d+$/', $submittedValue)) {
            return true;
        }
        
        return false;
    }
    
    public function validateSolutionSubmit()
    {
        if (strlen($this->getSolutionSubmit()) && !$this->isValidNumericSubmitValue($this->getSolutionSubmit())) {
            ilUtil::sendFailure($this->lng->txt("err_no_numeric_value"), true);
            return false;
        }
        
        return true;
    }
    
    public function getSolutionSubmit()
    {
        return trim(str_replace(",", ".", $_POST["numeric_result"]));
    }
    
    public function isValidSolutionSubmit($numeric_solution)
    {
        require_once './Services/Math/classes/class.EvalMath.php';
        $math = new EvalMath();
        $math->suppress_errors = true;
        $result = $math->evaluate($numeric_solution);
        
        return !(
            ($result === false || $result === true) && strlen($numeric_solution) > 0
        );
    }

    /**
     * Saves the learners input of the question to the database.
     *
     * @param integer $active_id Active id of the user
     * @param integer $pass Test pass
     *
     * @return boolean $status
     */
    public function saveWorkingData($active_id, $pass = null, $authorized = true)
    {
        /** @var $ilDB ilDBInterface */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (is_null($pass)) {
            require_once './Modules/Test/classes/class.ilObjTest.php';
            $pass = ilObjTest::_getPass($active_id);
        }

        $entered_values = 0;

        $returnvalue = true;

        $numeric_result = $this->getSolutionSubmit();

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use (&$entered_values, $numeric_result, $ilDB, $active_id, $pass, $authorized) {
            $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorized);

            $row = $ilDB->fetchAssoc($result);
            $update = $row["solution_id"];
            if ($update) {
                if (strlen($numeric_result)) {
                    $this->updateCurrentSolution($update, trim($numeric_result), null, $authorized);
                    $entered_values++;
                } else {
                    $this->removeSolutionRecordById($update);
                }
            } else {
                if (strlen($numeric_result)) {
                    $this->saveCurrentSolution($active_id, $pass, trim($numeric_result), null, $authorized);
                    $entered_values++;
                }
            }
        });

        if ($entered_values) {
            require_once './Modules/Test/classes/class.ilObjAssessmentFolder.php';
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction(
                    $this->lng->txtlng(
                        "assessment",
                        "log_user_entered_values",
                        ilObjAssessmentFolder::_getLogLanguage()
                    ),
                    $active_id,
                    $this->getId()
                );
            }
        } else {
            include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction(
                    $this->lng->txtlng(
                        "assessment",
                        "log_user_not_entered_values",
                        ilObjAssessmentFolder::_getLogLanguage()
                    ),
                    $active_id,
                    $this->getId()
                );
            }
        }

        return $returnvalue;
    }

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession)
    {
        $numericSolution = $this->getSolutionSubmit();
        $previewSession->setParticipantsSolution($numericSolution);
    }

    public function saveAdditionalQuestionDataToDb()
    {
        /** @var $ilDB ilDBInterface */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // save additional data
        $ilDB->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
            array( "integer" ),
            array( $this->getId() )
        );

        $ilDB->manipulateF(
            "INSERT INTO " . $this->getAdditionalTableName(
                                                   ) . " (question_fi, maxnumofchars) VALUES (%s, %s)",
            array( "integer", "integer" ),
            array(
                                $this->getId(),
                                ($this->getMaxChars()) ? $this->getMaxChars() : 0
                            )
        );
    }

    public function saveAnswerSpecificDataToDb()
    {
        /** @var $ilDB ilDBInterface */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // Write range to the database
        $ilDB->manipulateF(
            "DELETE FROM qpl_num_range WHERE question_fi = %s",
            array( 'integer' ),
            array( $this->getId() )
        );

        $next_id = $ilDB->nextId('qpl_num_range');
        $ilDB->manipulateF(
            "INSERT INTO qpl_num_range (range_id, question_fi, lowerlimit, upperlimit, points, aorder, tstamp) 
							 VALUES (%s, %s, %s, %s, %s, %s, %s)",
            array( 'integer', 'integer', 'text', 'text', 'float', 'integer', 'integer' ),
            array( $next_id, $this->id, $this->getLowerLimit(), $this->getUpperLimit(
                            ), $this->getPoints(), 0, time() )
        );
    }

    /**
     * Returns the question type of the question
     *
     * @return integer The question type of the question
     */
    public function getQuestionType()
    {
        return "assNumeric";
    }

    /**
     * Returns the maximum number of characters for the numeric input field
     *
     * @return integer The maximum number of characters
     */
    public function getMaxChars()
    {
        return $this->maxchars;
    }

    /**
     * Sets the maximum number of characters for the numeric input field
     *
     * @param integer $maxchars The maximum number of characters
     */
    public function setMaxChars($maxchars)
    {
        $this->maxchars = $maxchars;
    }

    /**
     * Returns the name of the additional question data table in the database
     *
     * @return string The additional table name
     */
    public function getAdditionalTableName()
    {
        return "qpl_qst_numeric";
    }

    /**
     * Collects all text in the question which could contain media objects
     * which were created with the Rich Text Editor
     */
    public function getRTETextWithMediaObjects()
    {
        return parent::getRTETextWithMediaObjects();
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLS($worksheet, $startrow, $active_id, $pass)
    {
        parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);

        $solutions = $this->getSolutionValues($active_id, $pass);

        $i = 1;
        $worksheet->setCell($startrow + $i, 0, $this->lng->txt("result"));
        $worksheet->setBold($worksheet->getColumnCoord(0) . ($startrow + $i));
        
        $worksheet->setBold($worksheet->getColumnCoord(0) . ($startrow + $i));
        if (strlen($solutions[0]["value1"])) {
            $worksheet->setCell($startrow + $i, 1, $solutions[0]["value1"]);
        }
        $i++;

        return $startrow + $i + 1;
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
            iQuestionCondition::EmptyAnswerExpression,
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

        while ($row = $ilDB->fetchAssoc($data)) {
            $result->addKeyValue(1, $row["value1"]);
        }

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
        return array(
            "lower" => $this->getLowerLimit(),
            "upper" => $this->getUpperLimit()
        );
    }
}
