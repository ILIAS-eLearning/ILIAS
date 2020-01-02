<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjAnswerScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php';
require_once './Modules/TestQuestionPool/classes/class.ilUserQuestionResult.php';

/**
 * Class for TextSubset questions
 *
 * assTextSubset is a class for TextSubset questions. To solve a TextSubset
 * question, a learner has to enter a TextSubsetal value in a defined range
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Nina Gharib <nina@wgserve.de>
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup		ModulesTestQuestionPool
 */
class assTextSubset extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition
{
    /**
    * The text which defines the correct set of answers
    *
    * The text which defines the correct set of answers
    *
    * @var array
    */
    public $answers;
    
    /**
    * The number of correct answers to solve the question
    *
    * The number of correct answers to solve the question
    *
    * @var integer
    */
    public $correctanswers;

    /**
    * The method which should be chosen for text comparisons
    *
    * The method which should be chosen for text comparisons
    *
    * @var string
    */
    public $text_rating;

    /**
     * assTextSubset constructor
     *
     * The constructor takes possible arguments an creates an instance of the assTextSubset object.
     *
     * @param string $title A title string to describe the question
     * @param string $comment A comment string to describe the question
     * @param string $author A string containing the name of the questions author
     * @param integer $owner A TextSubsetal ID to identify the owner/creator
     * @param string $question The question string of the TextSubset question
     */
    public function __construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = ""
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->answers = array();
        $this->correctanswers = 0;
    }

    /**
    * Returns true, if a TextSubset question is complete for use
    *
    * @return boolean True, if the TextSubset question is complete for use, otherwise false
    * @access public
    */
    public function isComplete()
    {
        if (
            strlen($this->title)
            && $this->author
            && $this->question  &&
            count($this->answers) >= $this->correctanswers
            && $this->getMaximumPoints() > 0
        ) {
            return true;
        }
        return false;
    }

    /**
     * Saves a assTextSubset object to a database
     *
     * @param string $original_id
     *
     */
    public function saveToDb($original_id = "")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();

        parent::saveToDb($original_id);
    }

    /**
    * Loads a assTextSubset object from a database
    *
    * @param object $db A pear DB object
    * @param integer $question_id A unique key which defines the multiple choice test in the database
    * @access public
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
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setTitle($data["title"]);
            $this->setComment($data["description"]);
            $this->setOriginalId($data["original_id"]);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
            $this->setCorrectAnswers($data["correctanswers"]);
            $this->setTextRating($data["textgap_rating"]);
            $this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
            
            try {
                $this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
            } catch (ilTestQuestionPoolException $e) {
            }
        }


        $result = $ilDB->queryF(
            "SELECT * FROM qpl_a_textsubset WHERE question_fi = %s ORDER BY aorder ASC",
            array('integer'),
            array($question_id)
        );
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerBinaryStateImage.php";
        if ($result->numRows() > 0) {
            while ($data = $ilDB->fetchAssoc($result)) {
                array_push($this->answers, new ASS_AnswerBinaryStateImage($data["answertext"], $data["points"], $data["aorder"]));
            }
        }

        parent::loadFromDb($question_id);
    }

    /**
    * Adds an answer to the question
    *
    * @access public
    */
    public function addAnswer($answertext, $points, $order)
    {
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerBinaryStateImage.php";
        if (array_key_exists($order, $this->answers)) {
            // insert answer
            $answer = new ASS_AnswerBinaryStateImage($answertext, $points, $order);
            $newchoices = array();
            for ($i = 0; $i < $order; $i++) {
                array_push($newchoices, $this->answers[$i]);
            }
            array_push($newchoices, $answer);
            for ($i = $order; $i < count($this->answers); $i++) {
                $changed = $this->answers[$i];
                $changed->setOrder($i+1);
                array_push($newchoices, $changed);
            }
            $this->answers = $newchoices;
        } else {
            // add answer
            array_push($this->answers, new ASS_AnswerBinaryStateImage($answertext, $points, count($this->answers)));
        }
    }
    
    /**
    * Duplicates an assTextSubsetQuestion
    *
    * @access public
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
    * Copies an assTextSubset object
    *
    * @access public
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
    * Returns the number of answers
    *
    * @return integer The number of answers of the TextSubset question
    * @access public
    * @see $ranges
    */
    public function getAnswerCount()
    {
        return count($this->answers);
    }

    /**
    * Returns an answer with a given index. The index of the first
    * answer is 0, the index of the second answer is 1 and so on.
    *
    * @param integer $index A nonnegative index of the n-th answer
    * @return object ASS_assAnswerBinaryStateImage-Object containing the answer
    * @access public
    * @see $answers
    */
    public function getAnswer($index = 0)
    {
        if ($index < 0) {
            return null;
        }
        if (count($this->answers) < 1) {
            return null;
        }
        if ($index >= count($this->answers)) {
            return null;
        }

        return $this->answers[$index];
    }

    /**
    * Deletes an answer with a given index. The index of the first
    * answer is 0, the index of the second answer is 1 and so on.
    *
    * @param integer $index A nonnegative index of the n-th answer
    * @access public
    * @see $answers
    */
    public function deleteAnswer($index = 0)
    {
        if ($index < 0) {
            return;
        }
        if (count($this->answers) < 1) {
            return;
        }
        if ($index >= count($this->answers)) {
            return;
        }
        unset($this->answers[$index]);
        $this->answers = array_values($this->answers);
        for ($i = 0; $i < count($this->answers); $i++) {
            if ($this->answers[$i]->getOrder() > $index) {
                $this->answers[$i]->setOrder($i);
            }
        }
    }

    /**
    * Deletes all answers
    *
    * @access public
    * @see $answers
    */
    public function flushAnswers()
    {
        $this->answers = array();
    }

    /**
    * Returns the maximum points, a learner can reach answering the question
    *
    * @access public
    * @see $points
    */
    public function getMaximumPoints()
    {
        $points = array();
        foreach ($this->answers as $answer) {
            if ($answer->getPoints() > 0) {
                array_push($points, $answer->getPoints());
            }
        }
        rsort($points, SORT_NUMERIC);
        $maxpoints = 0;
        for ($counter = 0; $counter < $this->getCorrectAnswers(); $counter++) {
            $maxpoints += $points[$counter];
        }
        return $maxpoints;
    }
    
    /**
    * Returns the available answers for the question
    *
    * @access private
    * @see $answers
    */
    public function &getAvailableAnswers()
    {
        $available_answers = array();
        foreach ($this->answers as $answer) {
            array_push($available_answers, $answer->getAnswertext());
        }
        return $available_answers;
    }

    /**
    * Returns the index of the found answer, if the given answer is in the
    * set of correct answers and matchess
    * the matching options, otherwise FALSE is returned
    *
    * @param array $answers An array containing the correct answers
    * @param string $answer The text of the given answer
    * @return mixed The index of the correct answer, FALSE otherwise
    * @access public
    */
    public function isAnswerCorrect($answers, $answer)
    {
        include_once "./Services/Utilities/classes/class.ilStr.php";
        $result = 0;
        $textrating = $this->getTextRating();
        foreach ($answers as $key => $value) {
            switch ($textrating) {
                case TEXTGAP_RATING_CASEINSENSITIVE:
                    if (strcmp(ilStr::strToLower($value), ilStr::strToLower($answer)) == 0 && $this->answers[$key]->getPoints() > 0) {
                        return $key;
                    }
                    break;
                case TEXTGAP_RATING_CASESENSITIVE:
                    if (strcmp($value, $answer) == 0 && $this->answers[$key]->getPoints() > 0) {
                        return $key;
                    }
                    break;
                case TEXTGAP_RATING_LEVENSHTEIN1:
                    if (levenshtein($value, $answer) <= 1 && $this->answers[$key]->getPoints() > 0) {
                        return $key;
                    }
                    break;
                case TEXTGAP_RATING_LEVENSHTEIN2:
                    if (levenshtein($value, $answer) <= 2 && $this->answers[$key]->getPoints() > 0) {
                        return $key;
                    }
                    break;
                case TEXTGAP_RATING_LEVENSHTEIN3:
                    if (levenshtein($value, $answer) <= 3 && $this->answers[$key]->getPoints() > 0) {
                        return $key;
                    }
                    break;
                case TEXTGAP_RATING_LEVENSHTEIN4:
                    if (levenshtein($value, $answer) <= 4 && $this->answers[$key]->getPoints() > 0) {
                        return $key;
                    }
                    break;
                case TEXTGAP_RATING_LEVENSHTEIN5:
                    if (levenshtein($value, $answer) <= 5 && $this->answers[$key]->getPoints() > 0) {
                        return $key;
                    }
                    break;
            }
        }
        return false;
    }

    /**
    * Returns the rating option for text comparisons
    *
    * @return string The rating option for text comparisons
    * @see $text_rating
    * @access public
    */
    public function getTextRating()
    {
        return $this->text_rating;
    }
    
    /**
    * Sets the rating option for text comparisons
    *
    * @param string $a_textgap_rating The rating option for text comparisons
    * @see $textgap_rating
    * @access public
    */
    public function setTextRating($a_text_rating)
    {
        switch ($a_text_rating) {
            case TEXTGAP_RATING_CASEINSENSITIVE:
            case TEXTGAP_RATING_CASESENSITIVE:
            case TEXTGAP_RATING_LEVENSHTEIN1:
            case TEXTGAP_RATING_LEVENSHTEIN2:
            case TEXTGAP_RATING_LEVENSHTEIN3:
            case TEXTGAP_RATING_LEVENSHTEIN4:
            case TEXTGAP_RATING_LEVENSHTEIN5:
                $this->text_rating = $a_text_rating;
                break;
            default:
                $this->text_rating = TEXTGAP_RATING_CASEINSENSITIVE;
                break;
        }
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
        
        
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorizedSolution);
        
        $enteredTexts = array();
        while ($data = $ilDB->fetchAssoc($result)) {
            $enteredTexts[] = $data["value1"];
        }

        $points = $this->calculateReachedPointsForSolution($enteredTexts);

        return $points;
    }
    
    /**
    * Sets the number of correct answers needed to solve the question
    *
    * @param integer $a_correct_anwers The number of correct answers
    * @access public
    */
    public function setCorrectAnswers($a_correct_answers)
    {
        $this->correctanswers = $a_correct_answers;
    }
    
    /**
    * Returns the number of correct answers needed to solve the question
    *
    * @return integer The number of correct answers
    * @access public
    */
    public function getCorrectAnswers()
    {
        return $this->correctanswers;
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

        $entered_values = 0;
        $solutionSubmit = $this->getSolutionSubmit();

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use (&$entered_values, $solutionSubmit, $active_id, $pass, $authorized) {
            $this->removeCurrentSolution($active_id, $pass, $authorized);

            foreach ($solutionSubmit as $value) {
                if (strlen($value)) {
                    $this->saveCurrentSolution($active_id, $pass, $value, null, $authorized);
                    $entered_values++;
                }
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
        /** @var ilDBInterface $ilDB */
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
                                                                        ) . " (question_fi, textgap_rating, correctanswers) VALUES (%s, %s, %s)",
            array( "integer", "text", "integer" ),
            array(
                                $this->getId(),
                                $this->getTextRating(),
                                $this->getCorrectAnswers()
                            )
        );
    }

    public function saveAnswerSpecificDataToDb()
    {
        /** @var ilDBInterface $ilDB */
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilDB->manipulateF(
            "DELETE FROM qpl_a_textsubset WHERE question_fi = %s",
            array( 'integer' ),
            array( $this->getId() )
        );

        foreach ($this->answers as $key => $value) {
            $answer_obj = $this->answers[$key];
            $next_id    = $ilDB->nextId('qpl_a_textsubset');
            $ilDB->manipulateF(
                "INSERT INTO qpl_a_textsubset (answer_id, question_fi, answertext, points, aorder, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
                array( 'integer', 'integer', 'text', 'float', 'integer', 'integer' ),
                array(
                                        $next_id,
                                        $this->getId(),
                                        $answer_obj->getAnswertext(),
                                        $answer_obj->getPoints(),
                                        $answer_obj->getOrder(),
                                        time()
                                )
            );
        }
    }

    /**
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    * @access public
    */
    public function getQuestionType()
    {
        return "assTextSubset";
    }
    
    /**
    * Returns the answers of the question as a comma separated string
    *
    * @return string The answer string
    * @access public
    */
    public function &joinAnswers()
    {
        $join = array();
        foreach ($this->answers as $answer) {
            if (!is_array($join[$answer->getPoints() . ""])) {
                $join[$answer->getPoints() . ""] = array();
            }
            array_push($join[$answer->getPoints() . ""], $answer->getAnswertext());
        }
        return $join;
    }
    
    /**
    * Returns the maximum width needed for the answer textboxes
    *
    * @return integer Maximum textbox width
    * @access public
    */
    public function getMaxTextboxWidth()
    {
        $maxwidth = 0;
        foreach ($this->answers as $answer) {
            $len = strlen($answer->getAnswertext());
            if ($len > $maxwidth) {
                $maxwidth = $len;
            }
        }
        return $maxwidth + 3;
    }

    /**
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    * @access public
    */
    public function getAdditionalTableName()
    {
        return "qpl_qst_textsubset";
    }

    /**
    * Returns the name of the answer table in the database
    *
    * @return string The answer table name
    * @access public
    */
    public function getAnswerTableName()
    {
        return "qpl_a_textsubset";
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
        foreach ($solutions as $solution) {
            $worksheet->setCell($startrow + $i, 0, $solution["value1"]);
            $i++;
        }

        return $startrow + $i + 1;
    }
    
    public function getAnswers()
    {
        return $this->answers;
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
        $result['question'] =  $this->formatSAQuestion($this->getQuestion());
        $result['nr_of_tries'] = (int) $this->getNrOfTries();
        $result['matching_method'] = (string) $this->getTextRating();
        $result['feedback'] = array(
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        );

        $answers = array();
        foreach ($this->getAnswers() as $key => $answer_obj) {
            array_push($answers, array(
                "answertext" => (string) $answer_obj->getAnswertext(),
                "points" => (float) $answer_obj->getPoints(),
                "order" => (int) $answer_obj->getOrder()
            ));
        }
        $result['correct_answers'] = $answers;

        $answers = array();
        for ($loop = 1; $loop <= (int) $this->getCorrectAnswers(); $loop++) {
            array_push($answers, array(
                "answernr" => $loop
            ));
        }
        $result['answers'] = $answers;

        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
        $result['mobs'] = $mobs;
        
        return json_encode($result);
    }

    /**
     * @return array
     */
    protected function getSolutionSubmit()
    {
        $solutionSubmit = array();
        $purifier = $this->getHtmlUserSolutionPurifier();
        foreach ($_POST as $key => $val) {
            if (preg_match("/^TEXTSUBSET_(\d+)/", $key, $matches)) {
                $val = trim($val);
                if (strlen($val)) {
                    $val = $purifier->purify($val);
                    $solutionSubmit[] = $val;
                }
            }
        }
        return $solutionSubmit;
    }

    /**
     * @param $enteredTexts
     * @return int
     */
    protected function calculateReachedPointsForSolution($enteredTexts)
    {
        $available_answers = $this->getAvailableAnswers();
        $points = 0;
        foreach ($enteredTexts as $enteredtext) {
            $index = $this->isAnswerCorrect($available_answers, $enteredtext);
            if ($index !== false) {
                unset($available_answers[$index]);
                $points += $this->answers[$index]->getPoints();
            }
        }
        return $points;
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
            iQuestionCondition::StringResultExpression,
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
                "SELECT value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s ORDER BY solution_id",
                array("integer", "integer", "integer","integer"),
                array($active_id, $pass, $this->getId(), $maxStep)
            );
        } else {
            $data = $ilDB->queryF(
                "SELECT value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s ORDER BY solution_id",
                array("integer", "integer", "integer"),
                array($active_id, $pass, $this->getId())
            );
        }

        for ($index = 1; $index <= $ilDB->numRows($data); ++$index) {
            $row = $ilDB->fetchAssoc($data);
            $result->addKeyValue($index, $row["value1"]);
        }

        $points = $this->calculateReachedPoints($active_id, $pass);
        $max_points = $this->getMaximumPoints();

        $result->setReachedPercentage(($points/$max_points) * 100);

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
        if ($index !== null) {
            return $this->getAnswer($index);
        } else {
            return $this->getAnswers();
        }
    }
}
