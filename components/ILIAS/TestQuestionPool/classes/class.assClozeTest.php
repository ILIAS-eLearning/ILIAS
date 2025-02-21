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
use ILIAS\TestQuestionPool\Questions\QuestionPartiallySaveable;
use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\Refinery\Random\Group as RandomGroup;

/**
 * Class for cloze tests
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup 	ModulesTestQuestionPool
 */
class assClozeTest extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition, QuestionPartiallySaveable, QuestionLMExportable, QuestionAutosaveable
{
    /**
    * The gaps of the cloze question
    * @var array<int, assClozeGap>
    */
    public array $gaps = [];

    /**
     * The optional gap combinations of the cloze question
     *
     * $gap_combinations is an array of the combination of predefined gaps of the cloze question
     *
     * @var array
     */
    protected $gap_combinations = [];
    protected bool $gap_combinations_exist = false;
    private string $start_tag = '[gap]';
    private string $end_tag = '[/gap]';

    /**
    * The rating option for text gaps
    *
    * This could contain one of the following options:
    * - case insensitive text gaps
    * - case sensitive text gaps
    * - various levenshtein distances
    */
    public string $textgap_rating = assClozeGap::TEXTGAP_RATING_CASEINSENSITIVE;

    /**
    * Defines the scoring for "identical solutions"
    *
    * If the learner selects the same solution twice
    * or more in different gaps, only the first choice
    * will be scored if identical_scoring is 0.
    */
    protected bool $identical_scoring = true;
    protected ?int $fixed_text_length = null;
    protected string $cloze_text = '';
    public ilAssQuestionFeedback $feedbackOBJ;
    protected $feedbackMode = ilAssClozeTestFeedback::FB_MODE_GAP_QUESTION;
    private RandomGroup $randomGroup;

    public function __construct(
        string $title = "",
        string $comment = "",
        string $author = "",
        int $owner = -1,
        string $question = ""
    ) {
        global $DIC;
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->setQuestion($question); // @TODO: Should this be $question?? See setter for why this is not trivial.
        $this->randomGroup = $DIC->refinery()->random();
    }

    /**
    * Returns TRUE, if a cloze test is complete for use
    *
    * @return boolean TRUE, if the cloze test is complete for use, otherwise FALSE
    */
    public function isComplete(): bool
    {
        if ($this->getTitle() !== ''
            && $this->getAuthor()
            && $this->getClozeText()
            && count($this->getGaps())
            && $this->getMaximumPoints() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Cleans cloze question text to remove attributes or tags from older ILIAS versions
     *
     * @param string $text The cloze question text
     *
     * @return string The cleaned cloze question text
     */
    public function cleanQuestiontext($text): string
    {
        if ($text === null) {
            return '';
        }
        // fau: fixGapReplace - mask dollars for replacement
        $text = str_replace('$', 'GAPMASKEDDOLLAR', $text);
        $text = preg_replace("/\[gap[^\]]*?\]/", "[gap]", $text);
        $text = preg_replace("/\<gap([^>]*?)\>/", "[gap]", $text);
        $text = str_replace("</gap>", "[/gap]", $text);
        $text = str_replace('GAPMASKEDDOLLAR', '$', $text);
        // fau.
        return $text;
    }

    public function replaceFirstGap(
        string $gaptext,
        string $content
    ): string {
        $output = preg_replace(
            '/\[gap\].*?\[\/gap\]/',
            str_replace('$', 'GAPMASKEDDOLLAR', $content),
            $gaptext,
            1
        );
        return str_replace('GAPMASKEDDOLLAR', '$', $output);
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
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setObjId($data["obj_fi"]);
            $this->setTitle((string) $data["title"]);
            $this->setComment((string) $data["description"]);
            $this->setOriginalId($data["original_id"]);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            $this->setQuestion($this->cleanQuestiontext($data["question_text"]));
            $this->setClozeText($data['cloze_text'] ?? '');
            $this->setFixedTextLength($data["fixed_textlen"]);
            $this->setIdenticalScoring(($data['tstamp'] === 0) ? true : (bool) $data['identical_scoring']);
            $this->setFeedbackMode($data['feedback_mode'] === null ? ilAssClozeTestFeedback::FB_MODE_GAP_QUESTION : $data['feedback_mode']);

            try {
                $this->setLifecycle(ilAssQuestionLifecycle::getInstance($data['lifecycle']));
            } catch (ilTestQuestionPoolInvalidArgumentException $e) {
                $this->setLifecycle(ilAssQuestionLifecycle::getDraftInstance());
            }

            $this->question = ilRTE::_replaceMediaObjectImageSrc($this->question, 1);
            $this->cloze_text = ilRTE::_replaceMediaObjectImageSrc($this->cloze_text, 1);
            $this->setTextgapRating($data["textgap_rating"]);

            try {
                $this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
            } catch (ilTestQuestionPoolException $e) {
            }

            $result = $this->db->queryF(
                "SELECT * FROM qpl_a_cloze WHERE question_fi = %s ORDER BY gap_id, aorder ASC",
                ["integer"],
                [$question_id]
            );
            if ($result->numRows() > 0) {
                $this->gaps = [];
                while ($data = $this->db->fetchAssoc($result)) {
                    switch ($data["cloze_type"]) {
                        case assClozeGap::TYPE_TEXT:
                            if (!array_key_exists($data["gap_id"], $this->gaps)) {
                                $this->gaps[$data["gap_id"]] = new assClozeGap(assClozeGap::TYPE_TEXT);
                            }
                            $answer = new assAnswerCloze(
                                $data["answertext"],
                                $data["points"],
                                $data["aorder"]
                            );
                            $this->gaps[$data["gap_id"]]->setGapSize((int) $data['gap_size']);

                            $this->gaps[$data["gap_id"]]->addItem($answer);
                            break;
                        case assClozeGap::TYPE_SELECT:
                            if (!array_key_exists($data["gap_id"], $this->gaps)) {
                                $this->gaps[$data["gap_id"]] = new assClozeGap(assClozeGap::TYPE_SELECT);
                                $this->gaps[$data["gap_id"]]->setShuffle($data["shuffle"]);
                            }
                            $answer = new assAnswerCloze(
                                $data["answertext"],
                                $data["points"],
                                $data["aorder"]
                            );
                            $this->gaps[$data["gap_id"]]->addItem($answer);
                            break;
                        case assClozeGap::TYPE_NUMERIC:
                            if (!array_key_exists($data["gap_id"], $this->gaps)) {
                                $this->gaps[$data["gap_id"]] = new assClozeGap(assClozeGap::TYPE_NUMERIC);
                            }
                            $answer = new assAnswerCloze(
                                $data["answertext"],
                                $data["points"],
                                $data["aorder"]
                            );
                            $this->gaps[$data["gap_id"]]->setGapSize((int) $data['gap_size']);
                            $answer->setLowerBound($data["lowerlimit"]);
                            $answer->setUpperBound($data["upperlimit"]);
                            $this->gaps[$data["gap_id"]]->addItem($answer);
                            break;
                    }
                }
            }
        }
        $check_for_gap_combinations = (new assClozeGapCombination())->loadFromDb($question_id);
        if (count($check_for_gap_combinations) != 0) {
            $this->setGapCombinationsExists(true);
            $this->setGapCombinations($check_for_gap_combinations);
        }
        parent::loadFromDb($question_id);
    }

    public function saveToDb(?int $original_id = null): void
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();

        parent::saveToDb();
    }

    public function saveAnswerSpecificDataToDb(): void
    {
        $this->db->manipulateF(
            "DELETE FROM qpl_a_cloze WHERE question_fi = %s",
            [ "integer" ],
            [ $this->getId() ]
        );

        foreach ($this->gaps as $key => $gap) {
            $this->saveClozeGapItemsToDb($gap, $key);
        }
    }

    public function saveAdditionalQuestionDataToDb(): void
    {
        $this->db->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
            [ "integer" ],
            [ $this->getId() ]
        );

        $this->db->insert($this->getAdditionalTableName(), [
            'question_fi' => ['integer', $this->getId()],
            'textgap_rating' => ['text', $this->getTextgapRating()],
            'identical_scoring' => ['text', $this->getIdenticalScoring()],
            'fixed_textlen' => ['integer', $this->getFixedTextLength() ? $this->getFixedTextLength() : null],
            'cloze_text' => ['text', ilRTE::_replaceMediaObjectImageSrc($this->getClozeText(), 0)],
            'feedback_mode' => ['text', $this->getFeedbackMode()]
        ]);
    }
    protected function saveClozeGapItemsToDb(
        assClozeGap $gap,
        int $key
    ): void {
        foreach ($gap->getItems($this->getShuffler()) as $item) {
            $next_id = $this->db->nextId('qpl_a_cloze');
            switch ($gap->getType()) {
                case assClozeGap::TYPE_TEXT:
                    $this->saveClozeTextGapRecordToDb($next_id, $key, $item, $gap);
                    break;
                case assClozeGap::TYPE_SELECT:
                    $this->saveClozeSelectGapRecordToDb($next_id, $key, $item, $gap);
                    break;
                case assClozeGap::TYPE_NUMERIC:
                    $this->saveClozeNumericGapRecordToDb($next_id, $key, $item, $gap);
                    break;
            }
        }
    }

    protected function saveClozeTextGapRecordToDb(
        int $next_id,
        int $key,
        assAnswerCloze $item,
        assClozeGap $gap
    ): void {
        $this->db->manipulateF(
            'INSERT INTO qpl_a_cloze (answer_id, question_fi, gap_id, answertext, points, aorder, cloze_type, gap_size) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)',
            [
                'integer',
                'integer',
                'integer',
                'text',
                'float',
                'integer',
                'text',
                'integer'
            ],
            [
                $next_id,
                $this->getId(),
                $key,
                strlen($item->getAnswertext()) ? $item->getAnswertext() : '',
                $item->getPoints(),
                $item->getOrder(),
                $gap->getType(),
                (int) $gap->getGapSize()
            ]
        );
    }

    protected function saveClozeSelectGapRecordToDb(
        int $next_id,
        int $key,
        assAnswerCloze $item,
        assClozeGap $gap
    ): void {
        $this->db->manipulateF(
            'INSERT INTO qpl_a_cloze (answer_id, question_fi, gap_id, answertext, points, aorder, cloze_type, shuffle) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)',
            [
                'integer',
                'integer',
                'integer',
                'text',
                'float',
                'integer',
                'text',
                'text'
            ],
            [
                $next_id,
                $this->getId(),
                $key,
                strlen($item->getAnswertext()) ? $item->getAnswertext() : '',
                $item->getPoints(),
                $item->getOrder(),
                $gap->getType(),
                ($gap->getShuffle()) ? '1' : '0'
            ]
        );
    }

    protected function saveClozeNumericGapRecordToDb(
        int $next_id,
        int $key,
        assAnswerCloze $item,
        assClozeGap $gap
    ): void {
        $eval = new EvalMath();
        $eval->suppress_errors = true;
        $this->db->manipulateF(
            'INSERT INTO qpl_a_cloze (answer_id, question_fi, gap_id, answertext, points, aorder, cloze_type, lowerlimit, upperlimit, gap_size) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)',
            [
                'integer',
                'integer',
                'integer',
                'text',
                'float',
                'integer',
                'text',
                'text',
                'text',
                'integer'
            ],
            [
                $next_id,
                $this->getId(),
                $key,
                strlen($item->getAnswertext()) ? $item->getAnswertext() : '',
                $item->getPoints(),
                $item->getOrder(),
                $gap->getType(),
                ($eval->e($item->getLowerBound()) !== false && strlen(
                    $item->getLowerBound()
                ) > 0) ? $item->getLowerBound() : $item->getAnswertext(),
                ($eval->e($item->getUpperBound()) !== false && strlen(
                    $item->getUpperBound()
                ) > 0) ? $item->getUpperBound() : $item->getAnswertext(),
                (int) $gap->getGapSize()
            ]
        );
    }

    public function getGaps(): array
    {
        return $this->gaps;
    }

    public function flushGaps(): void
    {
        $this->gaps = [];
    }

    public function setClozeText(string $cloze_text = ''): void
    {
        $this->gaps = [];
        $this->cloze_text = $this->cleanQuestiontext($cloze_text);
        $this->createGapsFromQuestiontext();
    }

    public function setClozeTextValue($cloze_text = ""): void
    {
        $this->cloze_text = $cloze_text;
    }

    /**
    * Returns the cloze text
    *
    * @return string The cloze text string
    * @access public
    * @see $cloze_text
    */
    public function getClozeText(): string
    {
        return $this->cloze_text;
    }

    /**
    * Returns the cloze text as HTML (with optional nl2br)
    * Fix for Mantis 29987: We assume Tiny embeds any text in tags, so if no tags are present, we derive it's
    * non-HTML content and apply nl2br.
    *
    * @return string The cloze text string as HTML
    * @see $cloze_text
    */
    public function getClozeTextForHTMLOutput(): string
    {
        $gaps = [];
        preg_match_all('/\[gap\].*?\[\/gap\]/', $this->getClozeText(), $gaps);
        $string_with_replaced_gaps = str_replace($gaps[0], '######GAP######', $this->getClozeText());
        $cleaned_text = $this->getHtmlQuestionContentPurifier()->purify(
            $string_with_replaced_gaps
        );
        $cleaned_text_with_gaps = preg_replace_callback('/######GAP######/', function ($match) use (&$gaps) {
            return array_shift($gaps[0]);
        }, $cleaned_text);

        if ($this->isAdditionalContentEditingModePageObject()
            || !(new ilSetting('advanced_editing'))->get('advanced_editing_javascript_editor') === 'tinymce') {
            $cleaned_text_with_gaps = nl2br($cleaned_text_with_gaps);
        }

        return ilLegacyFormElementsUtil::prepareTextareaOutput($cleaned_text_with_gaps, true);
    }

    /**
    * Returns the start tag of a cloze gap
    *
    * @return string The start tag of a cloze gap
    * @access public
    * @see $start_tag
    */
    public function getStartTag(): string
    {
        return $this->start_tag;
    }

    /**
    * Sets the start tag of a cloze gap
    *
    * @param string $start_tag The start tag for a cloze gap
    * @access public
    * @see $start_tag
    */
    public function setStartTag($start_tag = "[gap]"): void
    {
        $this->start_tag = $start_tag;
    }

    /**
    * Returns the end tag of a cloze gap
    *
    * @return string The end tag of a cloze gap
    * @access public
    * @see $end_tag
    */
    public function getEndTag(): string
    {
        return $this->end_tag;
    }

    /**
    * Sets the end tag of a cloze gap
    *
    * @param string $end_tag The end tag for a cloze gap
    * @access public
    * @see $end_tag
    */
    public function setEndTag($end_tag = "[/gap]"): void
    {
        $this->end_tag = $end_tag;
    }

    /**
     * @return string
     */
    public function getFeedbackMode(): string
    {
        return $this->feedbackMode;
    }

    /**
     * @param string $feedbackMode
     */
    public function setFeedbackMode($feedbackMode): void
    {
        $this->feedbackMode = $feedbackMode;
    }

    /**
    * Create gap entries by parsing the question text
    *
    * @access public
    * @see $gaps
    */
    public function createGapsFromQuestiontext(): void
    {
        $search_pattern = "|\[gap\](.*?)\[/gap\]|i";
        preg_match_all($search_pattern, $this->getClozeText(), $found);
        $this->gaps = [];
        if (count($found[0])) {
            foreach ($found[1] as $gap_index => $answers) {
                // create text gaps by default
                $gap = new assClozeGap(assClozeGap::TYPE_TEXT);
                $textparams = preg_split("/(?<!\\\\),/", $answers);
                foreach ($textparams as $key => $value) {
                    $answer = new assAnswerCloze($value, 0, $key);
                    $gap->addItem($answer);
                }
                $this->gaps[$gap_index] = $gap;
            }
        }
    }

    /**
    * Set the type of a gap with a given index
    *
    * @access private
    */
    public function setGapType($gap_index, $gap_type): void
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            $this->gaps[$gap_index]->setType($gap_type);
        }
    }

    /**
    * Sets the shuffle state of a gap with a given index. The index of the first
    * gap is 0, the index of the second gap is 1 and so on.
    *
    * @param integer $index A nonnegative index of the n-th gap
    * @param integer $shuffle Turn shuffle on (=1) or off (=0)
    * @access public
    * @see $gaps
    */
    public function setGapShuffle($gap_index = 0, $shuffle = 1): void
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            $this->gaps[$gap_index]->setShuffle($shuffle);
        }
    }

    /**
    * Removes all answers from the gaps
    *
    * @access public
    * @see $gaps
    */
    public function clearGapAnswers(): void
    {
        foreach ($this->gaps as $gap_index => $gap) {
            $this->gaps[$gap_index]->clearItems();
        }
    }

    /**
    * Returns the number of gaps
    *
    * @return integer The number of gaps
    * @access public
    * @see $gaps
    */
    public function getGapCount(): int
    {
        if (is_array($this->gaps)) {
            return count($this->gaps);
        } else {
            return 0;
        }
    }

    /**
    * Sets the answer text of a gap with a given index. The index of the first
    * gap is 0, the index of the second gap is 1 and so on.
    *
    * @param integer $gap_index A nonnegative index of the n-th gap
    * @param integer $order The order of the answer text
    * @param string $answer The answer text
    * @access public
    * @see $gaps
    */
    public function addGapAnswer($gap_index, $order, $answer): void
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            if ($this->gaps[$gap_index]->getType() == assClozeGap::TYPE_NUMERIC) {
                // only allow notation with "." for real numbers
                $answer = str_replace(",", ".", $answer);
            }
            $this->gaps[$gap_index]->addItem(new assAnswerCloze(trim($answer), 0, $order));
        }
    }

    public function getGap(int $gap_index = 0): ?assClozeGap
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            return $this->gaps[$gap_index];
        }
        return null;
    }

    public function setGapSize($gap_index, $size): void
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            $this->gaps[$gap_index]->setGapSize((int) $size);
        }
    }

    /**
    * Sets the points of a gap with a given index and an answer with a given order. The index of the first
    * gap is 0, the index of the second gap is 1 and so on.
    *
    * @param integer $gap_index A nonnegative index of the n-th gap
    * @param integer $order The order of the answer text
    * @param string $answer The points of the answer
    * @access public
    * @see $gaps
    */
    public function setGapAnswerPoints($gap_index, $order, $points): void
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            $this->gaps[$gap_index]->setItemPoints($order, $points);
        }
    }

    /**
    * Adds a new answer text value to a text gap with a given index. The index of the first
    * gap is 0, the index of the second gap is 1 and so on.
    *
    * @param integer $gap_index A nonnegative index of the n-th gap
    * @access public
    * @see $gaps
    */
    public function addGapText($gap_index): void
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            $answer = new assAnswerCloze(
                "",
                0,
                $this->gaps[$gap_index]->getItemCount()
            );
            $this->gaps[$gap_index]->addItem($answer);
        }
    }

    /**
    * Adds a ClozeGap object at a given index
    *
    * @param object $gap The gap object
    * @param integer $index A nonnegative index of the n-th gap
    * @access public
    * @see $gaps
    */
    public function addGapAtIndex($gap, $index): void
    {
        $this->gaps[$index] = $gap;
    }

    /**
    * Sets the lower bound of a gap with a given index and an answer with a given order. The index of the first
    * gap is 0, the index of the second gap is 1 and so on.
    *
    * @param integer $gap_index A nonnegative index of the n-th gap
    * @param integer $order The order of the answer text
    * @param string $answer The lower bound of the answer
    * @access public
    * @see $gaps
    */
    public function setGapAnswerLowerBound($gap_index, $order, $bound): void
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            $this->gaps[$gap_index]->setItemLowerBound($order, $bound);
        }
    }

    /**
    * Sets the upper bound of a gap with a given index and an answer with a given order. The index of the first
    * gap is 0, the index of the second gap is 1 and so on.
    *
    * @param integer $gap_index A nonnegative index of the n-th gap
    * @param integer $order The order of the answer text
    * @param string $answer The upper bound of the answer
    * @access public
    * @see $gaps
    */
    public function setGapAnswerUpperBound($gap_index, $order, $bound): void
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            $this->gaps[$gap_index]->setItemUpperBound($order, $bound);
        }
    }

    /**
    * Returns the maximum points, a learner can reach answering the question
    *
    * @access public
    * @see $points
    */
    public function getMaximumPoints(): float
    {
        $assClozeGapCombinationObj = new assClozeGapCombination();
        $points = 0;
        $gaps_used_in_combination = [];
        if ($assClozeGapCombinationObj->combinationExistsForQid($this->getId())) {
            $points = $assClozeGapCombinationObj->getMaxPointsForCombination($this->getId());
            $gaps_used_in_combination = $assClozeGapCombinationObj->getGapsWhichAreUsedInCombination($this->getId());
        }
        foreach ($this->gaps as $gap_index => $gap) {
            if (!array_key_exists($gap_index, $gaps_used_in_combination)) {
                if ($gap->getType() == assClozeGap::TYPE_TEXT) {
                    $gap_max_points = 0;
                    foreach ($gap->getItems($this->getShuffler()) as $item) {
                        if ($item->getPoints() > $gap_max_points) {
                            $gap_max_points = $item->getPoints();
                        }
                    }
                    $points += $gap_max_points;
                } elseif ($gap->getType() == assClozeGap::TYPE_SELECT) {
                    $srpoints = 0;
                    foreach ($gap->getItems($this->getShuffler()) as $item) {
                        if ($item->getPoints() > $srpoints) {
                            $srpoints = $item->getPoints();
                        }
                    }
                    $points += $srpoints;
                } elseif ($gap->getType() == assClozeGap::TYPE_NUMERIC) {
                    $numpoints = 0;
                    foreach ($gap->getItems($this->getShuffler()) as $item) {
                        if ($item->getPoints() > $numpoints) {
                            $numpoints = $item->getPoints();
                        }
                    }
                    $points += $numpoints;
                }
            }
        }

        return $points;
    }

    public function cloneQuestionTypeSpecificProperties(
        \assQuestion $target
    ): \assQuestion {
        if ($this->gap_combinations_exist) {
            (new assClozeGapCombination())->importGapCombinationToDb(
                $target->getId(),
                $this->gap_combinations
            );
        }
        return $target;
    }

    /**
    * Updates the gap parameters in the cloze text from the form input
    *
    * @access private
    */
    public function updateClozeTextFromGaps(): void
    {
        $output = $this->getClozeText();
        foreach ($this->getGaps() as $gap_index => $gap) {
            $answers = [];
            foreach ($gap->getItemsRaw() as $item) {
                array_push($answers, str_replace([',', '['], ["\\,", '[&hairsp;'], $item->getAnswerText()));
            }
            // fau: fixGapReplace - use replace function
            $output = $this->replaceFirstGap($output, "[_gap]" . ilLegacyFormElementsUtil::prepareTextareaOutput(join(",", $answers), true) . "[/_gap]");
            // fau.
        }
        $output = str_replace("_gap]", "gap]", $output);
        $this->cloze_text = $output;
    }

    /**
    * Deletes the answer text of a gap with a given index and an answer with a given order. The index of the first
    * gap is 0, the index of the second gap is 1 and so on.
    *
    * @param integer $gap_index A nonnegative index of the n-th gap
    * @param integer $answer_index The order of the answer text
    * @access public
    * @see $gaps
    */
    public function deleteAnswerText($gap_index, $answer_index): void
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            if ($this->gaps[$gap_index]->getItemCount() == 1) {
                // this is the last answer text => remove the gap
                $this->deleteGap($gap_index);
            } else {
                // remove the answer text
                $this->gaps[$gap_index]->deleteItem($answer_index);
                $this->updateClozeTextFromGaps();
            }
        }
    }

    /**
    * Deletes a gap with a given index. The index of the first
    * gap is 0, the index of the second gap is 1 and so on.
    *
    * @param integer $gap_index A nonnegative index of the n-th gap
    * @access public
    * @see $gaps
    */
    public function deleteGap($gap_index): void
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            $output = $this->getClozeText();
            foreach ($this->getGaps() as $replace_gap_index => $gap) {
                $answers = [];
                foreach ($gap->getItemsRaw() as $item) {
                    array_push($answers, str_replace(",", "\\,", $item->getAnswerText()));
                }
                if ($replace_gap_index == $gap_index) {
                    // fau: fixGapReplace - use replace function
                    $output = $this->replaceFirstGap($output, '');
                    // fau.
                } else {
                    // fau: fixGapReplace - use replace function
                    $output = $this->replaceFirstGap($output, "[_gap]" . join(",", $answers) . "[/_gap]");
                    // fau.
                }
            }
            $output = str_replace("_gap]", "gap]", $output);
            $this->cloze_text = $output;
            unset($this->gaps[$gap_index]);
            $this->gaps = array_values($this->gaps);
        }
    }

    /**
    * Returns the points for a text gap and compares the given solution with
    * the entered solution using the text gap rating options.
    *
    * @param string $a_original The original (correct) text
    * @param string $a_entered The text entered by the user
    * @param integer $max_points The maximum number of points for the solution
    * @access public
    */
    public function getTextgapPoints($a_original, $a_entered, $max_points): float
    {
        global $DIC;
        $refinery = $DIC->refinery();
        $result = 0;
        $gaprating = $this->getTextgapRating();

        switch ($gaprating) {
            case assClozeGap::TEXTGAP_RATING_CASEINSENSITIVE:
                if (strcmp(ilStr::strToLower($a_original), ilStr::strToLower($a_entered)) == 0) {
                    $result = $max_points;
                }
                break;
            case assClozeGap::TEXTGAP_RATING_CASESENSITIVE:
                if (strcmp($a_original, $a_entered) == 0) {
                    $result = $max_points;
                }
                break;
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN1:
                $transformation = $refinery->string()->levenshtein()->standard($a_original, 1);
                break;
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN2:
                $transformation = $refinery->string()->levenshtein()->standard($a_original, 2);
                break;
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN3:
                $transformation = $refinery->string()->levenshtein()->standard($a_original, 3);
                break;
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN4:
                $transformation = $refinery->string()->levenshtein()->standard($a_original, 4);
                break;
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN5:
                $transformation = $refinery->string()->levenshtein()->standard($a_original, 5);
                break;
        }

        // run answers against Levenshtein2 methods
        if (isset($transformation) && $transformation->transform($a_entered) >= 0) {
            $result = $max_points;
        }
        return $result;
    }


    /**
    * Returns the points for a text gap and compares the given solution with
    * the entered solution using the text gap rating options.
    *
    * @param string $a_original The original (correct) text
    * @param string $a_entered The text entered by the user
    * @param float $max_points The maximum number of points for the solution
    * @access public
    */
    public function getNumericgapPoints($a_original, $a_entered, $max_points, $lowerBound, $upperBound): float
    {
        $eval = new EvalMath();
        $eval->suppress_errors = true;
        $result = 0.0;

        if ($eval->e($a_entered) === false) {
            return 0.0;
        } elseif (($eval->e($lowerBound) !== false) && ($eval->e($upperBound) !== false)) {
            if (($eval->e($a_entered) >= $eval->e($lowerBound)) && ($eval->e($a_entered) <= $eval->e($upperBound))) {
                $result = $max_points;
            }
        } elseif ($eval->e($lowerBound) !== false) {
            if (($eval->e($a_entered) >= $eval->e($lowerBound)) && ($eval->e($a_entered) <= $eval->e($a_original))) {
                $result = $max_points;
            }
        } elseif ($eval->e($upperBound) !== false) {
            if (($eval->e($a_entered) >= $eval->e($a_original)) && ($eval->e($a_entered) <= $eval->e($upperBound))) {
                $result = $max_points;
            }
        } elseif ($eval->e($a_entered) == $eval->e($a_original)) {
            $result = $max_points;
        }
        return $result;
    }

    public function checkForValidFormula(string $value): int
    {
        return preg_match("/^-?(\\d*)(,|\\.|\\/){0,1}(\\d*)$/", $value, $matches);
    }

    public function calculateReachedPoints(
        int $active_id,
        ?int $pass = null,
        bool $authorized_solution = true
    ): float {
        $user_result = $this->fetchUserResult($active_id, $pass, $authorized_solution);
        return $this->calculateReachedPointsForSolution($user_result);
    }

    public function getUserResultDetails(
        int $active_id,
        ?int $pass = null,
        bool $authorized_solution = true
    ): array {
        $user_result = $this->fetchUserResult($active_id, $pass, $authorized_solution);
        $detailed = [];
        $this->calculateReachedPointsForSolution($user_result, $detailed);
        return $detailed;
    }

    private function fetchUserResult(
        int $active_id,
        ?int $pass
    ): array {
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }

        $result = $this->getCurrentSolutionResultSet($active_id, $pass, true);
        $user_result = [];
        while ($data = $this->db->fetchAssoc($result)) {
            if ($data['value2'] === '') {
                continue;
            }
            $user_result[$data['value1']] = [
                'gap_id' => $data['value1'],
                'value' => $data['value2']
            ];
        }

        ksort($user_result);
        return $user_result;
    }

    protected function isValidNumericSubmitValue($submittedValue): bool
    {
        if (is_numeric($submittedValue)) {
            return true;
        }

        if (preg_match('/^[-+]{0,1}\d+\/\d+$/', $submittedValue)) {
            return true;
        }

        return false;
    }

    public function fetchSolutionSubmit(): array
    {
        $solution_submit = [];
        $post_wrapper = $this->dic->http()->wrapper()->post();
        foreach ($this->getGaps() as $index => $gap) {
            if (!$post_wrapper->has("gap_$index")) {
                continue;
            }
            $value = trim($post_wrapper->retrieve(
                "gap_$index",
                $this->dic->refinery()->kindlyTo()->string()
            ));
            if ($value === '') {
                continue;
            }

            if ($gap->getType() === assClozeGap::TYPE_SELECT && $value === '-1') {
                continue;
            }

            if ($gap->getType() === assClozeGap::TYPE_NUMERIC) {
                $value = str_replace(',', '.', $value);
                if (!is_numeric($value)) {
                    $value = null;
                }
            }

            $solution_submit[$index] = $value;
        }

        return $solution_submit;
    }

    protected function getSolutionSubmit(): array
    {
        return $this->fetchSolutionSubmit();
    }

    public function saveWorkingData(
        int $active_id,
        ?int $pass = null,
        bool $authorized = true
    ): bool {
        if (is_null($pass)) {
            $pass = ilObjTest::_getPass($active_id);
        }

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(
            function () use ($active_id, $pass, $authorized) {
                $this->removeCurrentSolution($active_id, $pass, $authorized);

                foreach ($this->fetchSolutionSubmit() as $key => $value) {
                    if ($value === null || $value === '') {
                        continue;
                    }
                    $gap = $this->getGap($key);
                    if ($gap === null
                        || $gap->getType() === assClozeGap::TYPE_SELECT && $value === -1) {
                        continue;
                    }
                    $this->saveCurrentSolution($active_id, $pass, $key, $value, $authorized);
                }
            }
        );

        return true;
    }

    /**
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    * @access public
    */
    public function getQuestionType(): string
    {
        return "assClozeTest";
    }

    /**
    * Returns the rating option for text gaps
    *
    * @return string The rating option for text gaps
    * @see $textgap_rating
    * @access public
    */
    public function getTextgapRating(): string
    {
        return $this->textgap_rating;
    }

    /**
    * Sets the rating option for text gaps
    *
    * @param string $a_textgap_rating The rating option for text gaps
    * @see $textgap_rating
    * @access public
    */
    public function setTextgapRating($a_textgap_rating): void
    {
        switch ($a_textgap_rating) {
            case assClozeGap::TEXTGAP_RATING_CASEINSENSITIVE:
            case assClozeGap::TEXTGAP_RATING_CASESENSITIVE:
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN1:
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN2:
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN3:
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN4:
            case assClozeGap::TEXTGAP_RATING_LEVENSHTEIN5:
                $this->textgap_rating = $a_textgap_rating;
                break;
            default:
                $this->textgap_rating = assClozeGap::TEXTGAP_RATING_CASEINSENSITIVE;
                break;
        }
    }

    /**
    * Returns the identical scoring status of the question
    *
    * @return boolean The identical scoring status
    * @see $identical_scoring
    * @access public
    */
    public function getIdenticalScoring(): bool
    {
        return $this->identical_scoring;
    }

    /**
    * Sets the identical scoring option for cloze questions
    *
    * @param boolean $a_identical_scoring The identical scoring option for cloze questions
    * @see $identical_scoring
    * @access public
    */
    public function setIdenticalScoring(bool $identical_scoring): void
    {
        $this->identical_scoring = $identical_scoring;
    }

    /**
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    * @access public
    */
    public function getAdditionalTableName(): string
    {
        return "qpl_qst_cloze";
    }

    public function getAnswerTableName(): array
    {
        return ["qpl_a_cloze",'qpl_a_cloze_combi_res'];
    }

    /**
    * Sets a fixed text length for all text fields in the cloze question
    *
    * @param integer $a_text_len The text field length
    * @access public
    */
    public function setFixedTextLength(?int $fixed_text_length): void
    {
        $this->fixed_text_length = $fixed_text_length;
    }

    /**
    * Gets the fixed text length for all text fields in the cloze question
    *
    * @return integer The text field length
    * @access public
    */
    public function getFixedTextLength(): ?int
    {
        return $this->fixed_text_length;
    }

    /**
    * Returns the maximum points for a gap
    *
    * @param integer $gap_index The index of the gap
    * @return double The maximum points for the gap
    * @access public
    * @see $points
    */
    public function getMaximumGapPoints($gap_index)
    {
        $points = 0;
        $gap_max_points = 0;
        if (array_key_exists($gap_index, $this->gaps)) {
            $gap = &$this->gaps[$gap_index];
            foreach ($gap->getItems($this->getShuffler()) as $answer) {
                if ($answer->getPoints() > $gap_max_points) {
                    $gap_max_points = $answer->getPoints();
                }
            }
            $points += $gap_max_points;
        }
        return $points;
    }

    /**
    * Collects all text in the question which could contain media objects
    * which were created with the Rich Text Editor
    */
    public function getRTETextWithMediaObjects(): string
    {
        return parent::getRTETextWithMediaObjects() . $this->getClozeText();
    }
    public function getGapCombinationsExists(): bool
    {
        return $this->gap_combinations_exist;
    }

    public function getGapCombinations(): array
    {
        return $this->gap_combinations;
    }

    public function setGapCombinationsExists($value): void
    {
        $this->gap_combinations_exist = $value;
    }

    public function setGapCombinations($value): void
    {
        $this->gap_combinations = $value;
    }

    /**
     * @param ilAssSelfAssessmentMigrator $migrator
     */
    protected function lmMigrateQuestionTypeSpecificContent(ilAssSelfAssessmentMigrator $migrator): void
    {
        // DO NOT USE SETTER FOR CLOZE TEXT -> SETTER DOES RECREATE GAP OBJECTS without having gap type info ^^
        //$this->setClozeText( $migrator->migrateToLmContent($this->getClozeText()) );
        $this->cloze_text = $migrator->migrateToLmContent($this->getClozeText());
        // DO NOT USE SETTER FOR CLOZE TEXT -> SETTER DOES RECREATE GAP OBJECTS without having gap type info ^^
    }

    /**
    * Returns a JSON representation of the question
    */
    public function toJSON(): string
    {
        $result = [
            'id' => $this->getId(),
            'type' => (string) $this->getQuestionType(),
            'title' => $this->getTitle(),
            'question' => $this->formatSAQuestion($this->getQuestion()),
            'clozetext' => $this->formatSAQuestion($this->getClozeText()),
            'nr_of_tries' => $this->getNrOfTries(),
            'shuffle' => $this->getShuffle(),
            'feedback' => [
                'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
                'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
            ]
        ];

        $gaps = [];
        foreach ($this->getGaps() as $key => $gap) {
            $items = [];
            foreach ($gap->getItems($this->getShuffler()) as $item) {
                $jitem = [];
                $jitem['points'] = $item->getPoints();
                $jitem['value'] = $this->formatSAQuestion($item->getAnswertext());
                $jitem['order'] = $item->getOrder();
                if ($gap->getType() == assClozeGap::TYPE_NUMERIC) {
                    $jitem['lowerbound'] = $item->getLowerBound();
                    $jitem['upperbound'] = $item->getUpperBound();
                } else {
                    $jitem['value'] = trim($jitem['value']);
                }
                array_push($items, $jitem);
            }

            if ($gap->getGapSize() && ($gap->getType() == assClozeGap::TYPE_TEXT || $gap->getType() == assClozeGap::TYPE_NUMERIC)) {
                $jgap['size'] = $gap->getGapSize();
            }

            $jgap['shuffle'] = $gap->getShuffle();
            $jgap['type'] = $gap->getType();
            $jgap['item'] = $items;

            array_push($gaps, $jgap);
        }
        $result['gaps'] = $gaps;
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
            iQuestionCondition::NumberOfResultExpression,
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
                "
				SELECT sol.value1+1 as val, sol.value2, cloze.cloze_type
				FROM tst_solutions sol
				INNER JOIN qpl_a_cloze cloze ON cloze.gap_id = value1 AND cloze.question_fi = sol.question_fi
				WHERE sol.active_fi = %s AND sol.pass = %s AND sol.question_fi = %s AND sol.step = %s
				GROUP BY sol.solution_id, sol.value1+1, sol.value2, cloze.cloze_type
				",
                ["integer", "integer", "integer","integer"],
                [$active_id, $pass, $this->getId(), $maxStep]
            );
        } else {
            $data = $this->db->queryF(
                "
				SELECT sol.value1+1 as val, sol.value2, cloze.cloze_type
				FROM tst_solutions sol
				INNER JOIN qpl_a_cloze cloze ON cloze.gap_id = value1 AND cloze.question_fi = sol.question_fi
				WHERE sol.active_fi = %s AND sol.pass = %s AND sol.question_fi = %s
				GROUP BY sol.solution_id, sol.value1+1, sol.value2, cloze.cloze_type
				",
                ["integer", "integer", "integer"],
                [$active_id, $pass, $this->getId()]
            );
        }

        while ($row = $this->db->fetchAssoc($data)) {
            if ($row["cloze_type"] == 1) {
                $row["value2"]++;
            }
            $result->addKeyValue($row["val"], $row["value2"]);
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
        if ($index !== null) {
            return $this->getGap($index);
        } else {
            return $this->getGaps();
        }
    }

    public function calculateCombinationResult($user_result): array
    {
        $points = 0;

        $assClozeGapCombinationObj = new assClozeGapCombination();
        $gap_used_in_combination = [];
        if ($assClozeGapCombinationObj->combinationExistsForQid($this->getId())) {
            $combinations_for_question = $assClozeGapCombinationObj->getCleanCombinationArray($this->getId());
            $gap_answers = [];

            foreach ($user_result as $user_result_build_list) {
                if (is_array($user_result_build_list)) {
                    $gap_answers[$user_result_build_list['gap_id']] = $user_result_build_list['value'];
                }
            }

            foreach ($combinations_for_question as $combination) {
                foreach ($combination as $row_key => $row_answers) {
                    $combination_fulfilled = true;
                    $points_for_combination = $row_answers['points'];
                    foreach ($row_answers as $gap_key => $combination_gap_answer) {
                        if ($gap_key !== 'points') {
                            $gap_used_in_combination[$gap_key] = $gap_key;
                        }
                        if ($combination_fulfilled && array_key_exists($gap_key, $gap_answers)) {
                            switch ($combination_gap_answer['type']) {
                                case assClozeGap::TYPE_TEXT:
                                    $is_text_gap_correct = $this->getTextgapPoints($gap_answers[$gap_key], $combination_gap_answer['answer'], 1);
                                    if ($is_text_gap_correct != 1) {
                                        $combination_fulfilled = false;
                                    }
                                    break;
                                case assClozeGap::TYPE_SELECT:
                                    $answer = $this->gaps[$gap_key]->getItem($gap_answers[$gap_key]);
                                    $answertext = $answer?->getAnswertext();
                                    if ($answertext != $combination_gap_answer['answer']) {
                                        $combination_fulfilled = false;
                                    }
                                    break;
                                case assClozeGap::TYPE_NUMERIC:
                                    $answer = $this->gaps[$gap_key]->getItem(0);
                                    if ($combination_gap_answer['answer'] != 'out_of_bound') {
                                        $is_numeric_gap_correct = $this->getNumericgapPoints($answer->getAnswertext(), $gap_answers[$gap_key], 1, $answer->getLowerBound(), $answer->getUpperBound());
                                        if ($is_numeric_gap_correct != 1) {
                                            $combination_fulfilled = false;
                                        }
                                    } else {
                                        $wrong_is_the_new_right = $this->getNumericgapPoints($answer->getAnswertext(), $gap_answers[$gap_key], 1, $answer->getLowerBound(), $answer->getUpperBound());
                                        if ($wrong_is_the_new_right == 1) {
                                            $combination_fulfilled = false;
                                        }
                                    }
                                    break;
                            }
                        } else {
                            if ($gap_key !== 'points') {
                                $combination_fulfilled = false;
                            }
                        }
                    }
                    if ($combination_fulfilled) {
                        $points += $points_for_combination;
                    }
                }
            }
        }
        return [$points, $gap_used_in_combination];
    }
    /**
     * @param array $user_result
     * @param array $detailed
     */
    protected function calculateReachedPointsForSolution(?array $user_result, array &$detailed = []): float
    {
        $points = 0.0;

        $assClozeGapCombinationObj = new assClozeGapCombination();
        $combinations[1] = [];
        if ($assClozeGapCombinationObj->combinationExistsForQid($this->getId())) {
            $combinations = $this->calculateCombinationResult($user_result);
            $points = $combinations[0];
        }

        $solution_values_text = []; // for identical scoring checks
        $solution_values_select = []; // for identical scoring checks
        $solution_values_numeric = []; // for identical scoring checks
        foreach ($user_result as $gap_id => $value) {
            if (is_string($value)) {
                $value = ["value" => $value];
            }

            if (array_key_exists($gap_id, $this->gaps) && !array_key_exists($gap_id, $combinations[1])) {
                switch ($this->gaps[$gap_id]->getType()) {
                    case assClozeGap::TYPE_TEXT:
                        $gappoints = 0.0;
                        for ($order = 0; $order < $this->gaps[$gap_id]->getItemCount(); $order++) {
                            $answer = $this->gaps[$gap_id]->getItem($order);
                            $gotpoints = $this->getTextgapPoints($answer->getAnswertext(), $value["value"], $answer->getPoints());
                            if ($gotpoints > $gappoints) {
                                $gappoints = $gotpoints;
                            }
                        }
                        if (!$this->getIdenticalScoring()) {
                            // check if the same solution text was already entered
                            if ((in_array($value["value"], $solution_values_text)) && ($gappoints > 0.0)) {
                                $gappoints = 0.0;
                            }
                        }
                        $points += $gappoints;
                        $detailed[$gap_id] = ["points" => $gappoints, "best" => ($this->getMaximumGapPoints($gap_id) == $gappoints) ? true : false, "positive" => ($gappoints > 0.0) ? true : false];
                        array_push($solution_values_text, $value["value"]);
                        break;
                    case assClozeGap::TYPE_NUMERIC:
                        $gappoints = 0.0;
                        for ($order = 0; $order < $this->gaps[$gap_id]->getItemCount(); $order++) {
                            $answer = $this->gaps[$gap_id]->getItem($order);
                            $gotpoints = $this->getNumericgapPoints($answer->getAnswertext(), $value["value"], $answer->getPoints(), $answer->getLowerBound(), $answer->getUpperBound());
                            if ($gotpoints > $gappoints) {
                                $gappoints = $gotpoints;
                            }
                        }
                        if (!$this->getIdenticalScoring()) {
                            // check if the same solution value was already entered
                            $eval = new EvalMath();
                            $eval->suppress_errors = true;
                            $found_value = false;
                            foreach ($solution_values_numeric as $solval) {
                                if ($eval->e($solval) == $eval->e($value["value"])) {
                                    $found_value = true;
                                }
                            }
                            if ($found_value && ($gappoints > 0.0)) {
                                $gappoints = 0.0;
                            }
                        }
                        $points += $gappoints;
                        $detailed[$gap_id] = ["points" => $gappoints, "best" => ($this->getMaximumGapPoints($gap_id) == $gappoints) ? true : false, "positive" => ($gappoints > 0.0) ? true : false];
                        array_push($solution_values_numeric, $value["value"]);
                        break;
                    case assClozeGap::TYPE_SELECT:
                        if ($value["value"] >= 0.0) {
                            for ($order = 0; $order < $this->gaps[$gap_id]->getItemCount(); $order++) {
                                $answer = $this->gaps[$gap_id]->getItem($order);
                                if ($value["value"] == $answer->getOrder()) {
                                    $answerpoints = $answer->getPoints();
                                    if (!$this->getIdenticalScoring()) {
                                        // check if the same solution value was already entered
                                        if ((in_array($answer->getAnswertext(), $solution_values_select)) && ($answerpoints > 0.0)) {
                                            $answerpoints = 0.0;
                                        }
                                    }
                                    $points += $answerpoints;
                                    $detailed[$gap_id] = ["points" => $answerpoints, "best" => ($this->getMaximumGapPoints($gap_id) == $answerpoints) ? true : false, "positive" => ($answerpoints > 0.0) ? true : false];
                                    array_push($solution_values_select, $answer->getAnswertext());
                                }
                            }
                        }
                        break;
                }
            }
        }

        return $points;
    }

    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $preview_session): float
    {
        $participant_session = $preview_session->getParticipantsSolution();

        if (!is_array($participant_session)) {
            return 0.0;
        }

        $user_solution = [];

        foreach ($participant_session as $key => $val) {
            $user_solution[$key] = ['gap_id' => $key, 'value' => $val];
        }

        $reached_points = $this->calculateReachedPointsForSolution($user_solution);
        $reached_points = $this->deductHintPointsFromReachedPoints($preview_session, $reached_points);

        return $this->ensureNonNegativePoints($reached_points);
    }

    public function fetchAnswerValueForGap($userSolution, $gapIndex): string
    {
        $answerValue = '';

        foreach ($userSolution as $value1 => $value2) {
            if ($value1 == $gapIndex) {
                $answerValue = $value2;
                break;
            }
        }

        return $answerValue;
    }

    public function isAddableAnswerOptionValue(int $qIndex, string $answerOptionValue): bool
    {
        $gap = $this->getGap($qIndex);

        if ($gap->getType() != assClozeGap::TYPE_TEXT) {
            return false;
        }

        foreach ($gap->getItems($this->randomGroup->dontShuffle()) as $item) {
            if ($item->getAnswertext() === $answerOptionValue) {
                return false;
            }
        }

        return true;
    }

    public function addAnswerOptionValue(int $qIndex, string $answerOptionValue, float $points): void
    {
        $gap = $this->getGap($qIndex); /* @var assClozeGap $gap */

        $item = new assAnswerCloze($answerOptionValue, $points);
        $item->setOrder($gap->getItemCount());

        $gap->addItem($item);
    }

    public function toLog(AdditionalInformationGenerator $additional_info): array
    {
        $result = [
            AdditionalInformationGenerator::KEY_QUESTION_TYPE => (string) $this->getQuestionType(),
            AdditionalInformationGenerator::KEY_QUESTION_TITLE => $this->getTitle(),
            AdditionalInformationGenerator::KEY_QUESTION_TEXT => $this->formatSAQuestion($this->getQuestion()),
            AdditionalInformationGenerator::KEY_QUESTION_CLOZE_CLOZETEXT => $this->formatSAQuestion($this->getClozeText()),
            AdditionalInformationGenerator::KEY_QUESTION_SHUFFLE_ANSWER_OPTIONS => $additional_info
                ->getTrueFalseTagForBool($this->getShuffle()),
            AdditionalInformationGenerator::KEY_FEEDBACK => [
                AdditionalInformationGenerator::KEY_QUESTION_FEEDBACK_ON_INCOMPLETE => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
                AdditionalInformationGenerator::KEY_QUESTION_FEEDBACK_ON_COMPLETE => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
            ]
        ];

        $gaps = [];
        foreach ($this->getGaps() as $gap_index => $gap) {
            $items = [];
            foreach ($gap->getItems($this->getShuffler()) as $item) {
                $item_array = [
                    AdditionalInformationGenerator::KEY_QUESTION_REACHABLE_POINTS => $item->getPoints(),
                    AdditionalInformationGenerator::KEY_QUESTION_ANSWER_OPTION => $this->formatSAQuestion($item->getAnswertext()),
                    AdditionalInformationGenerator::KEY_QUESTION_ANSWER_OPTION_ORDER => $item->getOrder()
                ];
                if ($gap->getType() === assClozeGap::TYPE_NUMERIC) {
                    $item_array[AdditionalInformationGenerator::KEY_QUESTION_LOWER_LIMIT] = $item->getLowerBound();
                    $item_array[AdditionalInformationGenerator::KEY_QUESTION_UPPER_LIMIT] = $item->getUpperBound();
                }
                array_push($items, $item_array);
            }

            $gap_array[AdditionalInformationGenerator::KEY_QUESTION_TEXTSIZE] = $gap->getGapSize();
            $gap_array[AdditionalInformationGenerator::KEY_QUESTION_SHUFFLE_ANSWER_OPTIONS] = $additional_info->getTrueFalseTagForBool(
                $gap->getShuffle()
            );
            $gap_array[AdditionalInformationGenerator::KEY_QUESTION_CLOZE_GAP_TYPE] = $gap->getType();
            $gap_array[AdditionalInformationGenerator::KEY_QUESTION_ANSWER_OPTIONS] = $items;

            $gaps[$gap_index + 1] = $gap_array;
        }
        $result[AdditionalInformationGenerator::KEY_QUESTION_CLOZE_GAPS] = $gaps;
        return $result;
    }

    protected function solutionValuesToLog(
        AdditionalInformationGenerator $additional_info,
        array $solution_values
    ): array {
        $parsed_solution = [];
        foreach ($this->getGaps() as $gap_index => $gap) {
            foreach ($solution_values as $solutionvalue) {
                if ($gap_index !== (int) $solutionvalue['value1']) {
                    continue;
                }

                if ($gap->getType() === assClozeGap::TYPE_SELECT) {
                    $parsed_solution[$gap_index + 1] = $gap->getItem($solutionvalue['value2'])->getAnswertext();
                    continue;
                }

                $parsed_solution[$gap_index + 1] = $solutionvalue['value2'];
            }
        }
        return $parsed_solution;
    }

    public function solutionValuesToText(array $solution_values): array
    {
        $parsed_solution = [];
        foreach ($this->getGaps() as $gap_index => $gap) {
            foreach ($solution_values as $solutionvalue) {
                if ($gap_index !== (int) $solutionvalue['value1']) {
                    continue;
                }

                if ($gap->getType() === assClozeGap::TYPE_SELECT) {
                    $parsed_solution[] = $this->lng->txt('gap') . ' ' . $gap_index + 1 . ': '
                        . $gap->getItem($solutionvalue['value2'])->getAnswertext();
                    continue;
                }

                $parsed_solution[] = $this->lng->txt('gap') . ' ' . $gap_index + 1 . ': '
                    . $solutionvalue['value2'];
            }
        }
        return $parsed_solution;
    }

    public function getCorrectSolutionForTextOutput(int $active_id, int $pass): array
    {
        $answers = [];
        foreach ($this->getGaps() as $gap_index => $gap) {
            $correct_answers = array_map(
                fn(int $v): string => $gap->getItem($v)->getAnswertext(),
                $gap->getBestSolutionIndexes()
            );
            $answers[] = $this->lng->txt('gap') . ' ' . $gap_index + 1 . ': '
                . implode(',', $correct_answers);
        }
        return $answers;
    }
}
