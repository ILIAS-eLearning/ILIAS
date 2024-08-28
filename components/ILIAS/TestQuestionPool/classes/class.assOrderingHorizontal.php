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

use ILIAS\Test\Logging\AdditionalInformationGenerator;

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
class assOrderingHorizontal extends assQuestion implements ilObjQuestionScoringAdjustable, iQuestionCondition, QuestionLMExportable, QuestionAutosaveable
{
    protected const HAS_SPECIFIC_FEEDBACK = false;
    protected const DEFAULT_TEXT_SIZE = 100;

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
    public function isComplete(): bool
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
    public function saveToDb(?int $original_id = null): void
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        parent::saveToDb();
    }

    /**
     * @return string
     */
    public function getAnswerSeparator(): string
    {
        return $this->answer_separator;
    }


    /**
    * Loads a assOrderingHorizontal object from a database
    *
    * @param object $db A pear DB object
    * @param integer $question_id A unique key which defines the multiple choice test in the database
    */
    public function loadFromDb($question_id): void
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
            $this->setTitle((string) $data["title"]);
            $this->setComment((string) $data["description"]);
            $this->setOriginalId($data["original_id"]);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data["question_text"], 1));
            $this->setOrderText($data["ordertext"]);
            $this->setTextSize($data["textsize"]);

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

    public function getMaximumPoints(): float
    {
        return $this->getPoints();
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
        $points = 0.0;
        if ($this->db->numRows($result) > 0) {
            $data = $this->db->fetchAssoc($result);
            $points = $this->calculateReachedPointsForSolution($data['value1']);
        }

        return $points;
    }

    public function splitAndTrimOrderElementText(string $in_string, string $separator): array
    {
        $result = [];

        if (ilStr::strPos($in_string, $separator, 0) === false) {
            $result = preg_split("/\\s+/", $in_string);
        } else {
            $result = explode($separator, $in_string);
        }

        foreach ($result as $key => $value) {
            $result[$key] = trim($value);
        }

        return $result;
    }

    protected function getSolutionSubmit(): string
    {
        return $this->questionpool_request->retrieveStringValueFromPost('orderresult');
    }

    public function saveWorkingData(
        int $active_id,
        ?int $pass = null,
        bool $authorized = true
    ): bool {
        if($this->questionpool_request->raw('test_answer_changed') === null) {
            return true;
        }

        if (is_null($pass)) {
            $pass = ilObjTest::_getPass($active_id);
        }

        $answer = $this->getSolutionSubmit();
        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(
            function () use ($answer, $active_id, $pass, $authorized) {
                $this->removeCurrentSolution($active_id, $pass, $authorized);

                if ($answer === '') {
                    return;
                }

                $this->saveCurrentSolution(
                    $active_id,
                    $pass,
                    $answer,
                    null,
                    $authorized
                );
            }
        );

        return true;
    }

    public function saveAdditionalQuestionDataToDb()
    {
        // save additional data
        $this->db->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName()
                            . " WHERE question_fi = %s",
            [ "integer" ],
            [ $this->getId() ]
        );

        $this->db->manipulateF(
            "INSERT INTO " . $this->getAdditionalTableName()
                            . " (question_fi, ordertext, textsize) VALUES (%s, %s, %s)",
            [ "integer", "text", "float" ],
            [
                                $this->getId(),
                                $this->getOrderText(),
                                ($this->getTextSize() < 10) ? null : (float) $this->getTextSize()
                            ]
        );
    }

    /**
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    */
    public function getQuestionType(): string
    {
        return "assOrderingHorizontal";
    }

    /**
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    */
    public function getAdditionalTableName(): string
    {
        return "qpl_qst_horder";
    }

    /**
    * Returns the name of the answer table in the database
    *
    * @return string The answer table name
    */
    public function getAnswerTableName(): string
    {
        return "";
    }

    /**
    * Deletes datasets from answers tables
    *
    * @param integer $question_id The question id which should be deleted in the answers table
    */
    public function deleteAnswers($question_id): void
    {
    }

    /**
    * Collects all text in the question which could contain media objects
    * which were created with the Rich Text Editor
    */
    public function getRTETextWithMediaObjects(): string
    {
        $text = parent::getRTETextWithMediaObjects();
        return $text;
    }

    /**
    * Returns the best solution for a given pass of a participant
    *
    * @return array An associated array containing the best solution
    */
    public function getBestSolution($active_id, $pass): array
    {
        $user_solution = [];
        return $user_solution;
    }

    /**
    * Get ordering elements from order text
    *
    * @return array Ordering elements
    */
    public function getOrderingElements(): array
    {
        return $this->splitAndTrimOrderElementText($this->getOrderText() ?? "", $this->separator);
    }

    /**
    * Get ordering elements from order text in random sequence
    *
    * @return array Ordering elements
    */
    public function getRandomOrderingElements(): array
    {
        $elements = $this->getOrderingElements();
        $elements = $this->getShuffler()->transform($elements);
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
    public function setOrderText($a_value): void
    {
        $this->ordertext = $a_value;
    }

    /**
    * Get text size
    *
    * @return double Text size in percent maybe
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
    public function setTextSize(?float $textsize): void
    {
        if ($textsize === null || $textsize === 0.0) {
            $textsize = self::DEFAULT_TEXT_SIZE;
        }
        $this->textsize = $textsize;
    }

    /**
    * Get order text separator
    *
    * @return string Separator
    */
    public function getSeparator(): string
    {
        return $this->separator;
    }

    /**
    * Set order text separator
    *
    * @param string $a_value Separator
    */
    public function setSeparator($a_value): void
    {
        $this->separator = $a_value;
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
        $result['shuffle'] = true;
        $result['points'] = (bool) $this->getPoints();
        $result['textsize'] = ((int) $this->getTextSize()) // #10923
            ? (int) $this->getTextSize()
            : self::DEFAULT_TEXT_SIZE;
        $result['feedback'] = [
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        ];

        $arr = [];
        foreach ($this->getOrderingElements() as $order => $answer) {
            array_push($arr, [
                "answertext" => (string) $answer,
                "order" => (int) $order + 1
            ]);
        }
        $result['answers'] = $arr;

        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
        $result['mobs'] = $mobs;

        return json_encode($result);
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
            iQuestionCondition::OrderingResultExpression,
            iQuestionCondition::StringResultExpression,
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
                "SELECT value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s",
                ["integer", "integer", "integer","integer"],
                [$active_id, $pass, $this->getId(), $maxStep]
            );
        } else {
            $data = $this->db->queryF(
                "SELECT value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s",
                ["integer", "integer", "integer"],
                [$active_id, $pass, $this->getId()]
            );
        }
        $row = $this->db->fetchAssoc($data);

        $answer_elements = $this->splitAndTrimOrderElementText($row["value1"] ?? "", $this->answer_separator);
        $elements = $this->getOrderingElements();

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
     * @return array<ASS_AnswerSimple>|ASS_AnswerSimple|null
     */
    public function getAvailableAnswerOptions(?int $index = null): array|ASS_AnswerSimple|null
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
     * @return float
     */
    protected function calculateReachedPointsForSolution(?array $value): float
    {
        $value = $this->splitAndTrimOrderElementText($value ?? "", $this->answer_separator);
        $value = join($this->answer_separator, $value);
        if (strcmp($value, join($this->answer_separator, $this->getOrderingElements())) == 0) {
            $points = $this->getPoints();
            return $points;
        }
        return 0;
    }

    public function buildTestPresentationConfig(): ilTestQuestionConfig
    // hey.
    {
        // hey: refactored identifiers
        return parent::buildTestPresentationConfig()
        // hey.
            ->setIsUnchangedAnswerPossible(true)
            ->setUseUnchangedAnswerLabel($this->lng->txt('tst_unchanged_order_is_correct'));
    }

    public function toLog(AdditionalInformationGenerator $additional_info): array
    {
        return [
            AdditionalInformationGenerator::KEY_QUESTION_TYPE => (string) $this->getQuestionType(),
            AdditionalInformationGenerator::KEY_QUESTION_TITLE => $this->getTitle(),
            AdditionalInformationGenerator::KEY_QUESTION_TEXT => $this->formatSAQuestion($this->getQuestion()),
            AdditionalInformationGenerator::KEY_QUESTION_TEXTSIZE => ((int) $this->getTextSize()) ? (int) $this->getTextSize() : 100,
            AdditionalInformationGenerator::KEY_QUESTION_CORRECT_ANSWER_OPTIONS => $this->getOrderText(),
            AdditionalInformationGenerator::KEY_QUESTION_REACHABLE_POINTS => $this->getPoints(),
            AdditionalInformationGenerator::KEY_FEEDBACK => [
                AdditionalInformationGenerator::KEY_QUESTION_FEEDBACK_ON_INCOMPLETE => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
                AdditionalInformationGenerator::KEY_QUESTION_FEEDBACK_ON_COMPLETE => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
            ]
        ];
    }

    protected function solutionValuesToLog(
        AdditionalInformationGenerator $additional_info,
        array $solution_values
    ): string {
        if (!array_key_exists(0, $solution_values) ||
            !array_key_exists('value1', $solution_values[0])) {
            return '';
        }

        return str_replace("{::}", " ", $solution_values[0]['value1']);
    }

    public function solutionValuesToText(array $solution_values): string
    {
        if (!array_key_exists(0, $solution_values) ||
            !array_key_exists('value1', $solution_values[0])) {
            return '';
        }

        return str_replace("{::}", " ", $solution_values[0]['value1']);
    }

    public function getCorrectSolutionForTextOutput(int $active_id, int $pass): string
    {
        return $this->getOrderText();
    }
}
