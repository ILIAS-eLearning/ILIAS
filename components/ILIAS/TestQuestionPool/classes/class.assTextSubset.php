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

use ILIAS\TestQuestionPool\Questions\QuestionLMExportable;
use ILIAS\TestQuestionPool\Questions\QuestionAutosaveable;

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
class assTextSubset extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition, QuestionLMExportable, QuestionAutosaveable
{
    public array $answers = [];
    public int $correctanswers = 0;
    public string $text_rating = assClozeGap::TEXTGAP_RATING_CASEINSENSITIVE;

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

    public function saveToDb(?int $original_id = null): void
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();

        parent::saveToDb();
    }

    public function loadFromDb(int $question_id): void
    {
        $result = $this->db->queryF(
            "SELECT qpl_questions.*, " . $this->getAdditionalTableName() . ".* FROM qpl_questions LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = qpl_questions.question_id WHERE qpl_questions.question_id = %s",
            ["integer"],
            [$question_id]
        );
        if ($result->numRows() == 1) {
            $data = $this->db->fetchAssoc($result);
            $this->setId($question_id);
            $this->setObjId($data["obj_fi"]);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setTitle((string) $data["title"]);
            $this->setComment((string) $data["description"]);
            $this->setOriginalId($data["original_id"]);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data["question_text"], 1));
            $this->setCorrectAnswers((int) $data["correctanswers"]);
            $this->setTextRating($data["textgap_rating"]);

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


        $result = $this->db->queryF(
            "SELECT * FROM qpl_a_textsubset WHERE question_fi = %s ORDER BY aorder ASC",
            ['integer'],
            [$question_id]
        );
        if ($result->numRows() > 0) {
            while ($data = $this->db->fetchAssoc($result)) {
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
        if (array_key_exists($order, $this->answers)) {
            // insert answer
            $answer = new ASS_AnswerBinaryStateImage($answertext, $points, $order);
            $newchoices = [];
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
        $this->answers = [];
    }

    /**
    * Returns the maximum points, a learner can reach answering the question
    *
    * @access public
    * @see $points
    */
    public function getMaximumPoints(): float
    {
        $points = [];
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
        $available_answers = [];
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
        global $DIC;
        $refinery = $DIC->refinery();
        $textrating = $this->getTextRating();

        foreach ($answers as $key => $value) {
            if ($this->answers[$key]->getPoints() <= 0) {
                continue;
            }
            $value = html_entity_decode($value); #SB
            switch ($textrating) {
                case assClozeGap::TEXTGAP_RATING_CASEINSENSITIVE:
                    if (strcmp(ilStr::strToLower($value), ilStr::strToLower($answer)) == 0) { #SB
                        return $key;
                    }
                    break;
                case assClozeGap::TEXTGAP_RATING_CASESENSITIVE:
                    if (strcmp($value, $answer) == 0) {
                        return $key;
                    }
                    break;
                case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN1:
                    $transformation = $refinery->string()->levenshtein()->standard($answer, 1);
                    break;
                case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN2:
                    $transformation = $refinery->string()->levenshtein()->standard($answer, 2);
                    break;
                case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN3:
                    $transformation = $refinery->string()->levenshtein()->standard($answer, 3);
                    break;
                case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN4:
                    $transformation = $refinery->string()->levenshtein()->standard($answer, 4);
                    break;
                case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN5:
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

    public function getTextRating(): string
    {
        return $this->text_rating;
    }

    public function setTextRating(string $text_rating): void
    {
        switch ($text_rating) {
            case assClozeGap::TEXTGAP_RATING_CASEINSENSITIVE:
            case assClozeGap::TEXTGAP_RATING_CASESENSITIVE:
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN1:
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN2:
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN3:
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN4:
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN5:
                $this->text_rating = $text_rating;
                break;
            default:
                $this->text_rating = assClozeGap::TEXTGAP_RATING_CASEINSENSITIVE;
                break;
        }
    }

    public function calculateReachedPoints(
        int $active_id,
        ?int $pass = null,
        bool $authorized_solution = true
    ): float {
        if ($pass === null) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorized_solution);

        $enteredTexts = [];
        while ($data = $this->db->fetchAssoc($result)) {
            $enteredTexts[] = $data['value1'];
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

    public function saveWorkingData(
        int $active_id,
        ?int $pass = null,
        bool $authorized = true
    ): bool {
        if ($pass === null) {
            $pass = ilObjTest::_getPass($active_id);
        }

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(
            function () use ($active_id, $pass, $authorized) {
                $solution_submit = $this->getSolutionSubmit();
                $this->removeCurrentSolution($active_id, $pass, $authorized);

                foreach ($solution_submit as $value) {
                    if ($value !== '') {
                        $this->saveCurrentSolution($active_id, $pass, $value, null, $authorized);
                    }
                }
            }
        );

        return true;
    }

    public function saveAdditionalQuestionDataToDb()
    {
        // save additional data
        $this->db->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
            [ "integer" ],
            [ $this->getId() ]
        );

        $this->db->manipulateF(
            "INSERT INTO " . $this->getAdditionalTableName(
            ) . " (question_fi, textgap_rating, correctanswers) VALUES (%s, %s, %s)",
            [ "integer", "text", "integer" ],
            [
                                $this->getId(),
                                $this->getTextRating(),
                                $this->getCorrectAnswers()
                            ]
        );
    }

    public function saveAnswerSpecificDataToDb()
    {
        $this->db->manipulateF(
            "DELETE FROM qpl_a_textsubset WHERE question_fi = %s",
            [ 'integer' ],
            [ $this->getId() ]
        );

        foreach ($this->answers as $key => $value) {
            $answer_obj = $this->answers[$key];
            $next_id = $this->db->nextId('qpl_a_textsubset');
            $this->db->manipulateF(
                "INSERT INTO qpl_a_textsubset (answer_id, question_fi, answertext, points, aorder, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
                [ 'integer', 'integer', 'text', 'float', 'integer', 'integer' ],
                [
                                        $next_id,
                                        $this->getId(),
                                        $answer_obj->getAnswertext(),
                                        $answer_obj->getPoints(),
                                        $answer_obj->getOrder(),
                                        time()
                                ]
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
    public function setExportDetailsXLSX(ilAssExcelFormatHelper $worksheet, int $startrow, int $col, int $active_id, int $pass): int
    {
        parent::setExportDetailsXLSX($worksheet, $startrow, $col, $active_id, $pass);

        $solutions = $this->getSolutionValues($active_id, $pass);

        $i = 1;
        foreach ($solutions as $solution) {
            $worksheet->setCell($startrow + $i, $col + 2, $solution["value1"]);
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
        $result = [];
        $result['id'] = $this->getId();
        $result['type'] = (string) $this->getQuestionType();
        $result['title'] = $this->getTitle();
        $result['question'] = $this->formatSAQuestion($this->getQuestion());
        $result['nr_of_tries'] = $this->getNrOfTries();
        $result['matching_method'] = $this->getTextRating();
        $result['feedback'] = [
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        ];

        $answers = [];
        foreach ($this->getAnswers() as $key => $answer_obj) {
            $answers[] = [
                "answertext" => (string) $answer_obj->getAnswertext(),
                "points" => (float) $answer_obj->getPoints(),
                "order" => (int) $answer_obj->getOrder()
            ];
        }
        $result['correct_answers'] = $answers;

        $answers = [];
        for ($loop = 1; $loop <= $this->getCorrectAnswers(); $loop++) {
            $answers[] = [
                "answernr" => $loop
            ];
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
                    $value = $this->extendedTrim($value);
                    $value = $purifier->purify($value);
                    $solutionSubmit[] = $value;
                }
            }
        }

        return $solutionSubmit;
    }

    protected function calculateReachedPointsForSolution(?array $enteredTexts): float
    {
        $enteredTexts ??= [];
        $available_answers = $this->getAvailableAnswers();
        $points = 0.0;
        foreach ($enteredTexts as $enteredtext) {
            $index = $this->isAnswerCorrect($available_answers, html_entity_decode($enteredtext));
            if ($index !== false) {
                unset($available_answers[$index]);
                $points += $this->answers[$index]->getPoints();
            }
        }
        return $points;
    }

    public function getOperators(string $expression): array
    {
        return ilOperatorsExpressionMapping::getOperatorsByExpression($expression);
    }

    public function getExpressionTypes(): array
    {
        return [
            iQuestionCondition::PercentageResultExpression,
            iQuestionCondition::NumericResultExpression,
            iQuestionCondition::StringResultExpression,
            iQuestionCondition::EmptyAnswerExpression,
        ];
    }

    public function getUserQuestionResult(
        int $active_id,
        int $pass
    ): ilUserQuestionResult {
        $result = new ilUserQuestionResult($this, $active_id, $pass);

        $maxStep = $this->lookupMaxStep($active_id, $pass);
        if ($maxStep > 0) {
            $data = $this->db->queryF(
                "SELECT value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s ORDER BY solution_id",
                ["integer", "integer", "integer","integer"],
                [$active_id, $pass, $this->getId(), $maxStep]
            );
        } else {
            $data = $this->db->queryF(
                "SELECT value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s ORDER BY solution_id",
                ["integer", "integer", "integer"],
                [$active_id, $pass, $this->getId()]
            );
        }

        for ($index = 1; $index <= $this->db->numRows($data); ++$index) {
            $row = $this->db->fetchAssoc($data);
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

    public function toLog(): array
    {
        $result = [
            'question_id' => $this->getId(),
            'question_type' => (string) $this->getQuestionType(),
            'question_title' => $this->getTitle(),
            'tst_question' => $this->formatSAQuestion($this->getQuestion()),
            'matching_method' => '{{ ' . $this->getMatchingMethodLangVar($this->getTextRating()) . ' }}',
            'keywords' => array_map(
                fn(ASS_AnswerBinaryStateImage $answer) => [
                    'answer' => $answer->getAnswertext(),
                    'points' => $answer->getPoints()
                ],
                $this->getAnswers()
            ),
            'tst_feedback' => [
                'feedback_incomplete_solution' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
                'feedback_complete_solution' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
            ]
        ];
    }

    private function getMatchingMethodLangVar(string $matching_method): string
    {
        switch($matching_method) {
            case assClozeGap::TEXTGAP_RATING_CASEINSENSITIVE:
                return 'cloze_textgap_case_insensitive';
            case assClozeGap::TEXTGAP_RATING_CASESENSITIVE:
                return 'cloze_textgap_case_sensitive';
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN1:
                return 'cloze_textgap_levenshtein_of:1';
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN2:
                return 'cloze_textgap_levenshtein_of:2';
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN3:
                return 'cloze_textgap_levenshtein_of:3';
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN4:
                return 'cloze_textgap_levenshtein_of:4';
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN5:
                return 'cloze_textgap_levenshtein_of:5';
            default:
                return '';
        }
    }
}
