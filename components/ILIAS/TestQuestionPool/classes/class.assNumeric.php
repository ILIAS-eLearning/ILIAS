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

use ILIAS\TestQuestionPool\Questions\QuestionAutosaveable;

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
class assNumeric extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition, QuestionAutosaveable
{
    protected $lower_limit;
    protected $upper_limit;
    public $maxchars = 6;

    public function isComplete(): bool
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

    public function saveToDb(?int $original_id = null): void
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();
        parent::saveToDb($original_id);
    }

    public function loadFromDb(int $question_id): void
    {
        $result = $this->db->queryF(
            "SELECT qpl_questions.*, " . $this->getAdditionalTableName() . ".* FROM qpl_questions LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = qpl_questions.question_id WHERE qpl_questions.question_id = %s",
            array("integer"),
            array($question_id)
        );
        if ($result->numRows() == 1) {
            $data = $this->db->fetchAssoc($result);
            $this->setId($question_id);
            $this->setObjId($data["obj_fi"]);
            $this->setTitle((string) $data["title"]);
            $this->setComment((string) $data["description"]);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setOriginalId($data["original_id"]);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data["question_text"], 1));
            $this->setMaxChars($data["maxnumofchars"]);

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
            "SELECT * FROM qpl_num_range WHERE question_fi = %s ORDER BY aorder ASC",
            array('integer'),
            array($question_id)
        );

        if ($result->numRows() > 0) {
            /** @noinspection PhpAssignmentInConditionInspection */
            while ($data = $this->db->fetchAssoc($result)) {
                $this->setPoints($data['points']);
                $this->setLowerLimit($data['lowerlimit']);
                $this->setUpperLimit($data['upperlimit']);
            }
        }

        parent::loadFromDb($question_id);
    }

    public function getLowerLimit()
    {
        return $this->lower_limit;
    }

    public function getUpperLimit()
    {
        return $this->upper_limit;
    }

    public function setLowerLimit(string $limit): void
    {
        $this->lower_limit = str_replace(',', '.', $limit);
    }

    public function setUpperLimit(string $limit): void
    {
        $this->upper_limit = str_replace(',', '.', $limit);
    }

    public function getMaximumPoints(): float
    {
        return $this->getPoints();
    }

    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $previewSession): float
    {
        $points = 0;
        if ($this->contains($previewSession->getParticipantsSolution())) {
            $points = $this->getPoints();
        }

        $reachedPoints = $this->deductHintPointsFromReachedPoints($previewSession, $points);

        return $this->ensureNonNegativePoints($reachedPoints);
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
        $data = $this->db->fetchAssoc($result);
        $enteredvalue = '';
        if (is_array($data) && array_key_exists('value1', $data)) {
            $enteredvalue = $data["value1"];
        }

        $points = 0.0;
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
    public function contains($value): bool
    {
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

    public function validateSolutionSubmit(): bool
    {
        if ($this->getSolutionSubmit() === null) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("err_no_numeric_value"), true);
            return false;
        }

        return true;
    }

    public function getSolutionSubmit(): ?float
    {
        return $this->questionpool_request->getNumericAnswer('numeric_result');
    }

    public function isValidSolutionSubmit($numeric_solution): bool
    {
        $math = new EvalMath();
        $math->suppress_errors = true;
        $result = $math->evaluate($numeric_solution);

        return !(
            ($result === false || $result === true) && strlen($numeric_solution) > 0
        );
    }

    public function saveWorkingData(
        int $active_id,
        ?int $pass = null,
        bool $authorized = true
    ): bool {
        if (is_null($pass)) {
            $pass = ilObjTest::_getPass($active_id);
        }

        $answer = $this->getSolutionSubmit();
        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(
            function () use ($answer, $active_id, $pass, $authorized) {
                $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorized);
                $update = -1;
                if ($this->db->numRows($result) !== 0) {
                    $row = $this->db->fetchAssoc($result);
                    $update = $row['solution_id'];
                }

                if ($update !== -1
                    && $answer === '') {
                    $this->removeSolutionRecordById($update);
                    return;
                }
                if ($update !== -1) {
                    $this->updateCurrentSolution($update, $answer, null, $authorized);
                    return;
                }

                if ($answer !== '') {
                    $this->saveCurrentSolution($active_id, $pass, $answer, null, $authorized);
                }
            }
        );

        return true;
    }

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession): void
    {
        $numericSolution = $this->getSolutionSubmit();
        $previewSession->setParticipantsSolution($numericSolution);
    }

    public function saveAdditionalQuestionDataToDb()
    {
        // save additional data
        $this->db->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
            array( "integer" ),
            array( $this->getId() )
        );

        $this->db->manipulateF(
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
        // Write range to the database
        $this->db->manipulateF(
            "DELETE FROM qpl_num_range WHERE question_fi = %s",
            array( 'integer' ),
            array( $this->getId() )
        );

        $next_id = $this->db->nextId('qpl_num_range');
        $this->db->manipulateF(
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
    public function getQuestionType(): string
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
    public function setMaxChars($maxchars): void
    {
        $this->maxchars = $maxchars;
    }

    /**
     * Returns the name of the additional question data table in the database
     *
     * @return string The additional table name
     */
    public function getAdditionalTableName(): string
    {
        return "qpl_qst_numeric";
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
        $worksheet->setCell($startrow + $i, $col, $this->lng->txt("result"));
        $worksheet->setBold($worksheet->getColumnCoord($col) . ($startrow + $i));

        $worksheet->setBold($worksheet->getColumnCoord($col) . ($startrow + $i));
        if (array_key_exists(0, $solutions) &&
            array_key_exists('value1', $solutions[0]) &&
            strlen($solutions[0]["value1"])) {
            $worksheet->setCell($startrow + $i, $col + 2, $solutions[0]["value1"]);
        }
        $i++;

        return $startrow + $i + 1;
    }

    public function getOperators(string $expression): array
    {
        return ilOperatorsExpressionMapping::getOperatorsByExpression($expression);
    }

    public function getExpressionTypes(): array
    {
        return array(
            iQuestionCondition::PercentageResultExpression,
            iQuestionCondition::NumericResultExpression,
            iQuestionCondition::EmptyAnswerExpression,
        );
    }

    public function getUserQuestionResult(
        int $active_id,
        int $pass
    ): ilUserQuestionResult {
        $result = new ilUserQuestionResult($this, $active_id, $pass);

        $maxStep = $this->lookupMaxStep($active_id, $pass);
        if ($maxStep > 0) {
            $data = $this->db->queryF(
                "SELECT value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s",
                array("integer", "integer", "integer","integer"),
                array($active_id, $pass, $this->getId(), $maxStep)
            );
        } else {
            $data = $this->db->queryF(
                "SELECT value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s",
                array("integer", "integer", "integer"),
                array($active_id, $pass, $this->getId())
            );
        }

        while ($row = $this->db->fetchAssoc($data)) {
            $result->addKeyValue(1, $row["value1"]);
        }

        $points = $this->calculateReachedPoints($active_id, $pass);
        $max_points = $this->getMaximumPoints();

        $result->setReachedPercentage(($points / $max_points) * 100);

        return $result;
    }

    public function getAvailableAnswerOptions(int $index = null): array
    {
        return array(
            "lower" => $this->getLowerLimit(),
            "upper" => $this->getUpperLimit()
        );
    }

    public function getAnswerTableName(): string
    {
        return '';
    }

    public function toLog(): array
    {
        return [
            'question_id' => $this->getId(),
            'question_type' => (string) $this->getQuestionType(),
            'question_title' => $this->getTitle(),
            'tst_question' => $this->formatSAQuestion($this->getQuestion()),
            'shuffle_answers' => $this->getShuffle() ? '{{ enabled }}' : '{{ disabled }}',
            'maxchars' => $this->getMaxChars(),
            'points' => $this->getPoints(),
            'lower_limit' => $this->getLowerLimit(),
            'upper_limit' => $this->getUpperLimit(),
            'tst_feedback' => [
                'feedback_incomplete_solution' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
                'feedback_complete_solution' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
            ]
        ];
    }
}
