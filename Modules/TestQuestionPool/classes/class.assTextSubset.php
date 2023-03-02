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

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

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
    * @var array
    */
    public $answers;

    /**
    * The number of correct answers to solve the question
    *
    * @var integer
    */
    public $correctanswers;

    /**
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
    public function isComplete(): bool
    {
        if (
            strlen($this->title)
            && $this->author
            && $this->question &&
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
    public function saveToDb($original_id = ""): void
    {
        if ($original_id == "") {
            $this->saveQuestionDataToDb();
        } else {
            $this->saveQuestionDataToDb($original_id);
        }

        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();

        parent::saveToDb();
    }

    /**
    * Loads a assTextSubset object from a database
    *
    * @param object $db A pear DB object
    * @param integer $question_id A unique key which defines the multiple choice test in the database
    * @access public
    */
    public function loadFromDb($question_id): void
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
            $this->setTitle((string) $data["title"]);
            $this->setComment((string) $data["description"]);
            $this->setOriginalId($data["original_id"]);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data["question_text"], 1));
            $this->setCorrectAnswers((int) $data["correctanswers"]);
            $this->setTextRating($data["textgap_rating"]);
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


        $result = $ilDB->queryF(
            "SELECT * FROM qpl_a_textsubset WHERE question_fi = %s ORDER BY aorder ASC",
            array('integer'),
            array($question_id)
        );
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerBinaryStateImage.php";
        if ($result->numRows() > 0) {
            while ($data = $ilDB->fetchAssoc($result)) {
                $this->answers[] = new ASS_AnswerBinaryStateImage($data["answertext"], $data["points"], $data["aorder"]);
            }
        }

        parent::loadFromDb($question_id);
    }

    /**
    * Adds an answer to the question
    *
    * @access public
    */
    public function addAnswer($answertext, $points, $order): void
    {
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerBinaryStateImage.php";
        if (array_key_exists($order, $this->answers)) {
            // insert answer
            $answer = new ASS_AnswerBinaryStateImage($answertext, $points, $order);
            $newchoices = array();
            for ($i = 0; $i < $order; $i++) {
                $newchoices[] = $this->answers[$i];
            }
            $newchoices[] = $answer;
            for ($i = $order, $iMax = count($this->answers); $i < $iMax; $i++) {
                $changed = $this->answers[$i];
                $changed->setOrder($i + 1);
                $newchoices[] = $changed;
            }
            $this->answers = $newchoices;
        } else {
            // add answer
            $this->answers[] = new ASS_AnswerBinaryStateImage($answertext, $points, count($this->answers));
        }
    }

    /**
    * Duplicates an assTextSubsetQuestion
    *
    * @access public
    */
    public function duplicate(bool $for_test = true, string $title = "", string $author = "", string $owner = "", $testObjId = null): int
    {
        if ($this->id <= 0) {
            // The question has not been saved. It cannot be duplicated
            return -1;
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
    public function copyObject($target_questionpool_id, $title = ""): int
    {
        if ($this->getId() <= 0) {
            throw new RuntimeException('The question has not been saved. It cannot be duplicated');
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

    public function createNewOriginalFromThisDuplicate($targetParentId, $targetQuestionTitle = ""): int
    {
        if ($this->getId() <= 0) {
            throw new RuntimeException('The question has not been saved. It cannot be duplicated');
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
    public function getAnswerCount(): int
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
    public function getAnswer($index = 0): ?object
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
    public function deleteAnswer($index = 0): void
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
        for ($i = 0, $iMax = count($this->answers); $i < $iMax; $i++) {
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
    public function flushAnswers(): void
    {
        $this->answers = array();
    }

    /**
    * Returns the maximum points, a learner can reach answering the question
    *
    * @access public
    * @see $points
    */
    public function getMaximumPoints(): float
    {
        $points = array();
        foreach ($this->answers as $answer) {
            if ($answer->getPoints() > 0) {
                $points[] = $answer->getPoints();
            }
        }
        rsort($points, SORT_NUMERIC);
        $maxpoints = 0;
        for ($counter = 0; $counter < $this->getCorrectAnswers(); $counter++) {
            if (isset($points[$counter])) {
                $maxpoints += $points[$counter];
            }
        }
        return $maxpoints;
    }

    /**
    * Returns the available answers for the question
    *
    * @access private
    * @see $answers
    */
    public function &getAvailableAnswers(): array
    {
        $available_answers = array();
        foreach ($this->answers as $answer) {
            $available_answers[] = $answer->getAnswertext();
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
        global $DIC;
        $refinery = $DIC->refinery();
        $textrating = $this->getTextRating();

        foreach ($answers as $key => $value) {
            if ($this->answers[$key]->getPoints() <= 0) {
                continue;
            }
            $value = html_entity_decode($value); #SB
            switch ($textrating) {
                case TEXTGAP_RATING_CASEINSENSITIVE:
                    if (strcmp(ilStr::strToLower($value), ilStr::strToLower($answer)) == 0) { #SB
                        return $key;
                    }
                    break;
                case TEXTGAP_RATING_CASESENSITIVE:
                    if (strcmp($value, $answer) == 0) {
                        return $key;
                    }
                    break;
                case TEXTGAP_RATING_LEVENSHTEIN1:
                    $transformation = $refinery->string()->levenshtein()->standard($answer, 1);
                    break;
                case TEXTGAP_RATING_LEVENSHTEIN2:
                    $transformation = $refinery->string()->levenshtein()->standard($answer, 2);
                    break;
                case TEXTGAP_RATING_LEVENSHTEIN3:
                    $transformation = $refinery->string()->levenshtein()->standard($answer, 3);
                    break;
                case TEXTGAP_RATING_LEVENSHTEIN4:
                    $transformation = $refinery->string()->levenshtein()->standard($answer, 4);
                    break;
                case TEXTGAP_RATING_LEVENSHTEIN5:
                    $transformation = $refinery->string()->levenshtein()->standard($answer, 5);
                    break;
            }

            // run answers against Levenshtein2 methods
            if (isset($transformation) && $transformation->transform($value) >= 0) {
                return $key;
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
    public function getTextRating(): string
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
    public function setTextRating($a_text_rating): void
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
    public function calculateReachedPoints($active_id, $pass = null, $authorizedSolution = true, $returndetails = false): int
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

        return $this->calculateReachedPointsForSolution($enteredTexts);
    }

    /**
    * Sets the number of correct answers needed to solve the question
    *
    * @param integer $a_correct_anwers The number of correct answers
    * @access public
    */
    public function setCorrectAnswers(int $a_correct_answers): void
    {
        $this->correctanswers = $a_correct_answers;
    }

    /**
    * Returns the number of correct answers needed to solve the question
    *
    * @return integer The number of correct answers
    * @access public
    */
    public function getCorrectAnswers(): int
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
    public function saveWorkingData($active_id, $pass = null, $authorized = true): bool
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
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction($this->lng->txtlng(
                    "assessment",
                    "log_user_entered_values",
                    ilObjAssessmentFolder::_getLogLanguage()
                ), $active_id, $this->getId());
            }
        } elseif (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            assQuestion::logAction($this->lng->txtlng(
                "assessment",
                "log_user_not_entered_values",
                ilObjAssessmentFolder::_getLogLanguage()
            ), $active_id, $this->getId());
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
            $next_id = $ilDB->nextId('qpl_a_textsubset');
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
    public function getQuestionType(): string
    {
        return "assTextSubset";
    }

    /**
    * Returns the answers of the question as a comma separated string
    *
    */
    public function &joinAnswers(): array
    {
        $join = [];
        foreach ($this->answers as $answer) {
            $key = $answer->getPoints() . '';

            if (!isset($join[$key]) || !is_array($join[$key])) {
                $join[$key] = [];
            }

            $join[$key][] = $answer->getAnswertext();
        }

        return $join;
    }

    /**
    * Returns the maximum width needed for the answer textboxes
    *
    * @return integer Maximum textbox width
    * @access public
    */
    public function getMaxTextboxWidth(): int
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
    public function getAdditionalTableName(): string
    {
        return "qpl_qst_textsubset";
    }

    /**
    * Returns the name of the answer table in the database
    *
    * @return string The answer table name
    * @access public
    */
    public function getAnswerTableName(): string
    {
        return "qpl_a_textsubset";
    }

    /**
    * Collects all text in the question which could contain media objects
    * which were created with the Rich Text Editor
    */
    public function getRTETextWithMediaObjects(): string
    {
        return parent::getRTETextWithMediaObjects();
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLS(ilAssExcelFormatHelper $worksheet, int $startrow, int $col, int $active_id, int $pass): int
    {
        parent::setExportDetailsXLS($worksheet, $startrow, $col, $active_id, $pass);

        $solutions = $this->getSolutionValues($active_id, $pass);

        $i = 1;
        foreach ($solutions as $solution) {
            $worksheet->setCell($startrow + $i, $col, $solution["value1"]);
            $i++;
        }

        return $startrow + $i + 1;
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    /**
     * Returns a JSON representation of the question
     */
    public function toJSON(): string
    {
        include_once("./Services/RTE/classes/class.ilRTE.php");
        $result = array();
        $result['id'] = $this->getId();
        $result['type'] = (string) $this->getQuestionType();
        $result['title'] = $this->getTitle();
        $result['question'] = $this->formatSAQuestion($this->getQuestion());
        $result['nr_of_tries'] = $this->getNrOfTries();
        $result['matching_method'] = $this->getTextRating();
        $result['feedback'] = array(
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        );

        $answers = array();
        foreach ($this->getAnswers() as $key => $answer_obj) {
            $answers[] = array(
                "answertext" => (string) $answer_obj->getAnswertext(),
                "points" => (float) $answer_obj->getPoints(),
                "order" => (int) $answer_obj->getOrder()
            );
        }
        $result['correct_answers'] = $answers;

        $answers = array();
        for ($loop = 1; $loop <= $this->getCorrectAnswers(); $loop++) {
            $answers[] = array(
                "answernr" => $loop
            );
        }
        $result['answers'] = $answers;

        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
        $result['mobs'] = $mobs;

        return json_encode($result);
    }

    /**
     * @return array
     */
    protected function getSolutionSubmit(): array
    {
        $purifier = $this->getHtmlUserSolutionPurifier();
        $post = $this->dic->http()->wrapper()->post();

        $solutionSubmit = [];
        foreach ($this->getAnswers() as $index => $a) {
            if ($post->has("TEXTSUBSET_$index")) {
                $value = $post->retrieve(
                    "TEXTSUBSET_$index",
                    $this->dic->refinery()->kindlyTo()->string()
                );
                if ($value) {
                    $value = trim($value);
                    $value = $purifier->purify($value);
                    $solutionSubmit[] = $value;
                }
            }
        }

        return $solutionSubmit;
    }

    /**
     * @param $enteredTexts
     * @return int
     */
    protected function calculateReachedPointsForSolution($enteredTexts): float
    {
        $enteredTexts ??= [];
        $available_answers = $this->getAvailableAnswers();
        $points = 0;
        foreach ($enteredTexts as $enteredtext) {
            $index = $this->isAnswerCorrect($available_answers, html_entity_decode($enteredtext));
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
    public function getOperators($expression): array
    {
        return ilOperatorsExpressionMapping::getOperatorsByExpression($expression);
    }

    /**
     * Get all available expression types for a specific question
     * @return array
     */
    public function getExpressionTypes(): array
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
    public function getUserQuestionResult($active_id, $pass): ilUserQuestionResult
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

        $result->setReachedPercentage(($points / $max_points) * 100);

        return $result;
    }

    /**
     * If index is null, the function returns an array with all anwser options
     * Else it returns the specific answer option
     *
     * @param null|int $index
     */
    public function getAvailableAnswerOptions($index = null)
    {
        if ($index !== null) {
            return $this->getAnswer($index);
        } else {
            return $this->getAnswers();
        }
    }

    public function isAddableAnswerOptionValue(int $qIndex, string $answerOptionValue): bool
    {
        $found = false;

        foreach ($this->getAnswers() as $item) {
            if ($answerOptionValue !== $item->getAnswerText()) {
                continue;
            }

            $found = true;
            break;
        }

        return !$found;
    }

    public function addAnswerOptionValue(int $qIndex, string $answerOptionValue, float $points): void
    {
        $this->addAnswer($answerOptionValue, $points, $qIndex);
    }
}
