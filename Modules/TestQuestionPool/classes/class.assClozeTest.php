<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/classes/class.assClozeGapCombination.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjAnswerScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php';
require_once './Modules/TestQuestionPool/classes/class.ilUserQuestionResult.php';
require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssClozeTestFeedback.php';

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
class assClozeTest extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition
{
    /**
    * The gaps of the cloze question
    *
    * $gaps is an array of the predefined gaps of the cloze question
    *
    * @var array
    */
    public $gaps;

    /**
     * The optional gap combinations of the cloze question
     *
     * $gap_combinations is an array of the combination of predefined gaps of the cloze question
     *
     * @var array
     */
    public $gap_combinations;


    public $gap_combinations_exists;

    /**
    * The start tag beginning a cloze gap
    *
    * The start tag is set to "*[" by default.
    *
    * @var string
    */
    public $start_tag;

    /**
    * The end tag beginning a cloze gap
    *
    * The end tag is set to "]" by default.
    *
    * @var string
    */
    public $end_tag;

    /**
    * The rating option for text gaps
    *
    * This could contain one of the following options:
    * - case insensitive text gaps
    * - case sensitive text gaps
    * - various levenshtein distances
    *
    * @var string
    */
    public $textgap_rating;

    /**
    * Defines the scoring for "identical solutions"
    *
    * If the learner selects the same solution twice
    * or more in different gaps, only the first choice
    * will be scored if identical_scoring is 0.
    *
    * @var boolean
    */
    public $identical_scoring;

    /**
    * The fixed text length for all text fields in the cloze question
    *
    * @var integer
    */
    public $fixedTextLength;

    public $cloze_text;

    /**
     * @var ilAssClozeTestFeedback
     */
    public $feedbackOBJ;

    protected $feedbackMode = ilAssClozeTestFeedback::FB_MODE_GAP_QUESTION;

    /**
     * assClozeTest constructor
     *
     * The constructor takes possible arguments an creates an instance of the assClozeTest object.
     *
     * @param string  $title   A title string to describe the question
     * @param string  $comment A comment string to describe the question
     * @param string  $author  A string containing the name of the questions author
     * @param integer $owner   A numerical ID to identify the owner/creator
     * @param string  $question
     *
     * @return \assClozeTest
     */
    public function __construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = ""
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->start_tag = "[gap]";
        $this->end_tag = "[/gap]";
        $this->gaps = array();
        $this->setQuestion($question); // @TODO: Should this be $question?? See setter for why this is not trivial.
        $this->fixedTextLength = "";
        $this->identical_scoring = 1;
        $this->gap_combinations_exists = false;
        $this->gap_combinations = array();
    }

    /**
    * Returns TRUE, if a cloze test is complete for use
    *
    * @return boolean TRUE, if the cloze test is complete for use, otherwise FALSE
    */
    public function isComplete()
    {
        if (strlen($this->getTitle())
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
    public function cleanQuestiontext($text)
    {
        // fau: fixGapReplace - mask dollars for replacement
        $text = str_replace('$', 'GAPMASKEDDOLLAR', $text);
        $text = preg_replace("/\[gap[^\]]*?\]/", "[gap]", $text);
        $text = preg_replace("/\<gap([^>]*?)\>/", "[gap]", $text);
        $text = str_replace("</gap>", "[/gap]", $text);
        $text = str_replace('GAPMASKEDDOLLAR', '$', $text);
        // fau.
        return $text;
    }

    // fau: fixGapReplace - add function replaceFirstGap()
    /**
     * Replace the first gap in a string without treating backreferences
     * @param string $gaptext	text with gap tags
     * @param string $content	content for the first gap
     * @return string
     */
    public function replaceFirstGap($gaptext, $content)
    {
        $content = str_replace('$', 'GAPMASKEDDOLLAR', $content);
        $output = preg_replace("/\[gap\].*?\[\/gap\]/", $content, $gaptext, 1);
        $output = str_replace('GAPMASKEDDOLLAR', '$', $output);

        return $output;
    }
    // fau.
    /**
     * Loads a assClozeTest object from a database
     *
     * @param integer $question_id A unique key which defines the cloze test in the database
     *
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
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setObjId($data["obj_fi"]);
            $this->setTitle($data["title"]);
            $this->setComment($data["description"]);
            $this->setOriginalId($data["original_id"]);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            $this->setQuestion($this->cleanQuestiontext($data["question_text"]));
            $this->setClozeText($data['cloze_text']);
            $this->setFixedTextLength($data["fixed_textlen"]);
            $this->setIdenticalScoring(($data['tstamp'] == 0) ? true : $data["identical_scoring"]);
            $this->setFeedbackMode($data['feedback_mode'] === null ? ilAssClozeTestFeedback::FB_MODE_GAP_QUESTION : $data['feedback_mode']);

            try {
                $this->setLifecycle(ilAssQuestionLifecycle::getInstance($data['lifecycle']));
            } catch (ilTestQuestionPoolInvalidArgumentException $e) {
                $this->setLifecycle(ilAssQuestionLifecycle::getDraftInstance());
            }

            // replacement of old syntax with new syntax
            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->question = ilRTE::_replaceMediaObjectImageSrc($this->question, 1);
            $this->cloze_text = ilRTE::_replaceMediaObjectImageSrc($this->cloze_text, 1);
            $this->setTextgapRating($data["textgap_rating"]);
            $this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));

            try {
                $this->setAdditionalContentEditingMode($data['add_cont_edit_mode']);
            } catch (ilTestQuestionPoolException $e) {
            }

            // open the cloze gaps with all answers
            include_once "./Modules/TestQuestionPool/classes/class.assAnswerCloze.php";
            include_once "./Modules/TestQuestionPool/classes/class.assClozeGap.php";
            $result = $ilDB->queryF(
                "SELECT * FROM qpl_a_cloze WHERE question_fi = %s ORDER BY gap_id, aorder ASC",
                array("integer"),
                array($question_id)
            );
            if ($result->numRows() > 0) {
                $this->gaps = array();
                while ($data = $ilDB->fetchAssoc($result)) {
                    switch ($data["cloze_type"]) {
                        case CLOZE_TEXT:
                            if (!array_key_exists($data["gap_id"], $this->gaps)) {
                                $this->gaps[$data["gap_id"]] = new assClozeGap(CLOZE_TEXT);
                            }
                            $answer = new assAnswerCloze(
                                $data["answertext"],
                                $data["points"],
                                $data["aorder"]
                            );
                            $this->gaps[$data["gap_id"]]->setGapSize($data['gap_size']);

                            $this->gaps[$data["gap_id"]]->addItem($answer);
                            break;
                        case CLOZE_SELECT:
                            if (!array_key_exists($data["gap_id"], $this->gaps)) {
                                $this->gaps[$data["gap_id"]] = new assClozeGap(CLOZE_SELECT);
                                $this->gaps[$data["gap_id"]]->setShuffle($data["shuffle"]);
                            }
                            $answer = new assAnswerCloze(
                                $data["answertext"],
                                $data["points"],
                                $data["aorder"]
                            );
                            $this->gaps[$data["gap_id"]]->addItem($answer);
                            break;
                        case CLOZE_NUMERIC:
                            if (!array_key_exists($data["gap_id"], $this->gaps)) {
                                $this->gaps[$data["gap_id"]] = new assClozeGap(CLOZE_NUMERIC);
                            }
                            $answer = new assAnswerCloze(
                                $data["answertext"],
                                $data["points"],
                                $data["aorder"]
                            );
                            $this->gaps[$data["gap_id"]]->setGapSize($data['gap_size']);
                            $answer->setLowerBound($data["lowerlimit"]);
                            $answer->setUpperBound($data["upperlimit"]);
                            $this->gaps[$data["gap_id"]]->addItem($answer);
                            break;
                    }
                }
            }
        }
        $assClozeGapCombinationObj = new assClozeGapCombination();
        $check_for_gap_combinations = $assClozeGapCombinationObj->loadFromDb($question_id);
        if (count($check_for_gap_combinations) != 0) {
            $this->setGapCombinationsExists(true);
            $this->setGapCombinations($check_for_gap_combinations);
        }
        parent::loadFromDb($question_id);
    }

    #region Save question to db

    /**
     * Saves a assClozeTest object to a database
     *
     * @param int|string $original_id ID of the original question
     *
     * @return mixed|void
     *
     * @access public
     */
    public function saveToDb($original_id = "")
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();

        parent::saveToDb($original_id);
    }

    /**
     * Save all gaps to the database.
     */
    public function saveAnswerSpecificDataToDb()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->manipulateF(
            "DELETE FROM qpl_a_cloze WHERE question_fi = %s",
            array( "integer" ),
            array( $this->getId() )
        );

        foreach ($this->gaps as $key => $gap) {
            $this->saveClozeGapItemsToDb($gap, $key);
        }
    }

    /**
     * Saves the data for the additional data table.
     *
     * This method uses the ugly DELETE-INSERT. Here, this does no harm.
     */
    public function saveAdditionalQuestionDataToDb()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */


        $DIC->database()->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
            array( "integer" ),
            array( $this->getId() )
        );

        $DIC->database()->insert($this->getAdditionalTableName(), array(
            'question_fi' => array('integer', $this->getId()),
            'textgap_rating' => array('text', $this->getTextgapRating()),
            'identical_scoring' => array('text', $this->getIdenticalScoring()),
            'fixed_textlen' => array('integer', $this->getFixedTextLength() ? $this->getFixedTextLength() : null),
            'cloze_text' => array('text', ilRTE::_replaceMediaObjectImageSrc($this->getClozeText(), 0)),
            'feedback_mode' => array('text', $this->getFeedbackMode())
        ));
    }

    /**
     * Save all items belonging to one cloze gap to the db.
     *
     * @param $gap
     * @param $key
     */
    protected function saveClozeGapItemsToDb($gap, $key)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        foreach ($gap->getItems($this->getShuffler()) as $item) {
            $query = "";
            $next_id = $ilDB->nextId('qpl_a_cloze');
            switch ($gap->getType()) {
                case CLOZE_TEXT:
                    $this->saveClozeTextGapRecordToDb($next_id, $key, $item, $gap);
                    break;
                case CLOZE_SELECT:
                    $this->saveClozeSelectGapRecordToDb($next_id, $key, $item, $gap);
                    break;
                case CLOZE_NUMERIC:
                    $this->saveClozeNumericGapRecordToDb($next_id, $key, $item, $gap);
                    break;
            }
        }
    }

    /**
     * Saves a gap-item record.
     *
     * @param $next_id			int	Next Id for the record.
     * @param $key				int Gap Id
     * @param $item				gap Gap item data object.
     * @param $gap				gap Gap data object.
     */
    protected function saveClozeTextGapRecordToDb($next_id, $key, $item, $gap)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilDB->manipulateF(
            "INSERT INTO qpl_a_cloze (answer_id, question_fi, gap_id, answertext, points, aorder, cloze_type, gap_size) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
            array(
                                "integer",
                                "integer",
                                "integer",
                                "text",
                                "float",
                                "integer",
                                "text",
                                "integer"
                            ),
            array(
                                $next_id,
                                $this->getId(),
                                $key,
                                strlen($item->getAnswertext()) ? $item->getAnswertext() : "",
                                $item->getPoints(),
                                $item->getOrder(),
                                $gap->getType(),
                                (int) $gap->getGapSize()
                            )
        );
    }

    /**
     * Saves a gap-item record.
     *
     * @param $next_id			int	Next Id for the record.
     * @param $key				int Gap Id
     * @param $item				gap Gap item data object.
     * @param $gap				gap Gap data object.
     */
    protected function saveClozeSelectGapRecordToDb($next_id, $key, $item, $gap)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilDB->manipulateF(
            "INSERT INTO qpl_a_cloze (answer_id, question_fi, gap_id, answertext, points, aorder, cloze_type, shuffle) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
            array(
                                "integer",
                                "integer",
                                "integer",
                                "text",
                                "float",
                                "integer",
                                "text",
                                "text"
                            ),
            array(
                                $next_id,
                                $this->getId(),
                                $key,
                                strlen($item->getAnswertext()) ? $item->getAnswertext() : "",
                                $item->getPoints(),
                                $item->getOrder(),
                                $gap->getType(),
                                ($gap->getShuffle()) ? "1" : "0"
                            )
        );
    }

    /**
     * Saves a gap-item record.
     *
     * @param $next_id			int	Next Id for the record.
     * @param $key				int Gap Id
     * @param $item				gap Gap item data object.
     * @param $gap				gap Gap data object.
     */
    protected function saveClozeNumericGapRecordToDb($next_id, $key, $item, $gap)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        include_once "./Services/Math/classes/class.EvalMath.php";
        $eval = new EvalMath();
        $eval->suppress_errors = true;
        $ilDB->manipulateF(
            "INSERT INTO qpl_a_cloze (answer_id, question_fi, gap_id, answertext, points, aorder, cloze_type, lowerlimit, upperlimit, gap_size) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
            array(
                                "integer",
                                "integer",
                                "integer",
                                "text",
                                "float",
                                "integer",
                                "text",
                                "text",
                                "text",
                                "integer"
                            ),
            array(
                                $next_id,
                                $this->getId(),
                                $key,
                                strlen($item->getAnswertext()) ? $item->getAnswertext() : "",
                                $item->getPoints(),
                                $item->getOrder(),
                                $gap->getType(),
                                ($eval->e($item->getLowerBound() !== false) && strlen(
                                    $item->getLowerBound()
                                ) > 0) ? $item->getLowerBound() : $item->getAnswertext(),
                                ($eval->e($item->getUpperBound() !== false) && strlen(
                                    $item->getUpperBound()
                                ) > 0) ? $item->getUpperBound() : $item->getAnswertext(),
                                (int) $gap->getGapSize()
                            )
        );
    }



    #endregion Save question to db

    /**
    * Returns the array of gaps
    *
    * @return array Array containing the gap objects of the cloze question gaps
    * @access public
    */
    public function getGaps()
    {
        return $this->gaps;
    }


    /**
    * Deletes all gaps without changing the cloze text
    *
    * @access public
    * @see $gaps
    */
    public function flushGaps()
    {
        $this->gaps = array();
    }

    /**
    * Evaluates the text gap solutions from the cloze text. A single or multiple text gap solutions
    * could be entered using the following syntax in the cloze text:
    * solution1 [, solution2, ..., solutionN] enclosed in the text gap selector gap[]
    *
    * @param string $cloze_text The cloze text with all gaps and gap gaps
    * @access public
    * @see $cloze_text
    */
    public function setClozeText($cloze_text = "")
    {
        $this->gaps = [];
        $this->cloze_text = $this->cleanQuestiontext($cloze_text);
        $this->createGapsFromQuestiontext();
    }

    public function setClozeTextValue($cloze_text = "")
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
    public function getClozeText()
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
    public function getClozeTextForHTMLOutput() : string
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

        return $this->prepareTextareaOutput($cleaned_text_with_gaps, true);
    }

    /**
    * Returns the start tag of a cloze gap
    *
    * @return string The start tag of a cloze gap
    * @access public
    * @see $start_tag
    */
    public function getStartTag()
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
    public function setStartTag($start_tag = "[gap]")
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
    public function getEndTag()
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
    public function setEndTag($end_tag = "[/gap]")
    {
        $this->end_tag = $end_tag;
    }

    /**
     * @return string
     */
    public function getFeedbackMode()
    {
        return $this->feedbackMode;
    }

    /**
     * @param string $feedbackMode
     */
    public function setFeedbackMode($feedbackMode)
    {
        $this->feedbackMode = $feedbackMode;
    }

    /**
    * Create gap entries by parsing the question text
    *
    * @access public
    * @see $gaps
    */
    public function createGapsFromQuestiontext()
    {
        include_once "./Modules/TestQuestionPool/classes/class.assClozeGap.php";
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerCloze.php";
        $search_pattern = "|\[gap\](.*?)\[/gap\]|i";
        preg_match_all($search_pattern, $this->getClozeText(), $found);
        $this->gaps = array();
        if (count($found[0])) {
            foreach ($found[1] as $gap_index => $answers) {
                // create text gaps by default
                $gap = new assClozeGap(CLOZE_TEXT);
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
    public function setGapType($gap_index, $gap_type)
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
    public function setGapShuffle($gap_index = 0, $shuffle = 1)
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
    public function clearGapAnswers()
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
    public function getGapCount()
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
    public function addGapAnswer($gap_index, $order, $answer)
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            if ($this->gaps[$gap_index]->getType() == CLOZE_NUMERIC) {
                // only allow notation with "." for real numbers
                $answer = str_replace(",", ".", $answer);
            }
            $this->gaps[$gap_index]->addItem(new assAnswerCloze(trim($answer), 0, $order));
        }
    }

    /**
    * Returns the gap at a given index
    *
    * @param integer $gap_index A nonnegative index of the n-th gap
    * @return object The gap of the given index
    * @access public
    * @see $gaps
    */
    public function getGap($gap_index = 0)
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            return $this->gaps[$gap_index];
        } else {
            return null;
        }
    }

    public function setGapSize($gap_index, $order, $size)
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            $this->gaps[$gap_index]->setGapSize($size);
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
    public function setGapAnswerPoints($gap_index, $order, $points)
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
    public function addGapText($gap_index)
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            include_once "./Modules/TestQuestionPool/classes/class.assAnswerCloze.php";
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
    public function addGapAtIndex($gap, $index)
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
    public function setGapAnswerLowerBound($gap_index, $order, $bound)
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
    public function setGapAnswerUpperBound($gap_index, $order, $bound)
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
    public function getMaximumPoints()
    {
        $assClozeGapCombinationObj = new assClozeGapCombination();
        $points = 0;
        $gaps_used_in_combination = array();
        if ($assClozeGapCombinationObj->combinationExistsForQid($this->getId())) {
            $points = $assClozeGapCombinationObj->getMaxPointsForCombination($this->getId());
            $gaps_used_in_combination = $assClozeGapCombinationObj->getGapsWhichAreUsedInCombination($this->getId());
        }
        foreach ($this->gaps as $gap_index => $gap) {
            if (!array_key_exists($gap_index, $gaps_used_in_combination)) {
                if ($gap->getType() == CLOZE_TEXT) {
                    $gap_max_points = 0;
                    foreach ($gap->getItems($this->getShuffler()) as $item) {
                        if ($item->getPoints() > $gap_max_points) {
                            $gap_max_points = $item->getPoints();
                        }
                    }
                    $points += $gap_max_points;
                } elseif ($gap->getType() == CLOZE_SELECT) {
                    $srpoints = 0;
                    foreach ($gap->getItems($this->getShuffler()) as $item) {
                        if ($item->getPoints() > $srpoints) {
                            $srpoints = $item->getPoints();
                        }
                    }
                    $points += $srpoints;
                } elseif ($gap->getType() == CLOZE_NUMERIC) {
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

    /**
    * Duplicates an assClozeTest
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
        if ($this->gap_combinations_exists) {
            $this->copyGapCombination($this_id, $clone->getId());
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

        return $clone->getId();
    }

    /**
    * Copies an assClozeTest object
    *
    * @access public
    */
    public function copyObject($target_questionpool_id, $title = "")
    {
        if ($this->getId() <= 0) {
            // The question has not been saved. It cannot be duplicated
            return;
        }

        $thisId = $this->getId();
        $thisObjId = $this->getObjId();

        $clone = $this;
        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
        $original_id = assQuestion::_getOriginalId($this->getId());
        $clone->id = -1;
        $clone->setObjId($target_questionpool_id);
        if ($title) {
            $clone->setTitle($title);
        }

        $clone->saveToDb();

        if ($this->gap_combinations_exists) {
            $this->copyGapCombination($original_id, $clone->getId());
            $clone->saveToDb();
        }

        // copy question page content
        $clone->copyPageOfQuestion($original_id);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($original_id);

        $clone->onCopy($thisObjId, $thisId, $clone->getObjId(), $clone->getId());

        return $clone->getId();
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

        if ($this->gap_combinations_exists) {
            $this->copyGapCombination($sourceQuestionId, $clone->getId());
            $clone->saveToDb();
        }
        // copy question page content
        $clone->copyPageOfQuestion($sourceQuestionId);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($sourceQuestionId);

        $clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    public function copyGapCombination($orgID, $newID)
    {
        $assClozeGapCombinationObj = new assClozeGapCombination();
        $array = $assClozeGapCombinationObj->loadFromDb($orgID);
        $assClozeGapCombinationObj->importGapCombinationToDb($newID, $array);
    }

    /**
    * Updates the gap parameters in the cloze text from the form input
    *
    * @access private
    */
    public function updateClozeTextFromGaps()
    {
        $output = $this->getClozeText();
        foreach ($this->getGaps() as $gap_index => $gap) {
            $answers = array();
            foreach ($gap->getItemsRaw() as $item) {
                array_push($answers, str_replace([',', '['], ["\\,", '[&hairsp;'], $item->getAnswerText()));
            }
            // fau: fixGapReplace - use replace function
            $output = $this->replaceFirstGap($output, "[_gap]" . $this->prepareTextareaOutput(join(",", $answers), true) . "[/_gap]");
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
    public function deleteAnswerText($gap_index, $answer_index)
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
    public function deleteGap($gap_index)
    {
        if (array_key_exists($gap_index, $this->gaps)) {
            $output = $this->getClozeText();
            foreach ($this->getGaps() as $replace_gap_index => $gap) {
                $answers = array();
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
    public function getTextgapPoints($a_original, $a_entered, $max_points)
    {
        include_once "./Services/Utilities/classes/class.ilStr.php";
        global $DIC;
        $refinery = $DIC->refinery();
        $result = 0;
        $gaprating = $this->getTextgapRating();

        switch ($gaprating) {
            case TEXTGAP_RATING_CASEINSENSITIVE:
                if (strcmp(ilStr::strToLower($a_original), ilStr::strToLower($a_entered)) == 0) {
                    $result = $max_points;
                }
                break;
            case TEXTGAP_RATING_CASESENSITIVE:
                if (strcmp($a_original, $a_entered) == 0) {
                    $result = $max_points;
                }
                break;
            case TEXTGAP_RATING_LEVENSHTEIN1:
                $transformation = $refinery->string()->levenshtein()->standard($a_original, 1);
                break;
            case TEXTGAP_RATING_LEVENSHTEIN2:
                $transformation = $refinery->string()->levenshtein()->standard($a_original, 2);
                break;
            case TEXTGAP_RATING_LEVENSHTEIN3:
                $transformation = $refinery->string()->levenshtein()->standard($a_original, 3);
                break;
            case TEXTGAP_RATING_LEVENSHTEIN4:
                $transformation = $refinery->string()->levenshtein()->standard($a_original, 4);
                break;
            case TEXTGAP_RATING_LEVENSHTEIN5:
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
    * @param integer $max_points The maximum number of points for the solution
    * @access public
    */
    public function getNumericgapPoints($a_original, $a_entered, $max_points, $lowerBound, $upperBound)
    {
        // fau: fixGapFormula - check entered value by evalMath
        //		if( ! $this->checkForValidFormula($a_entered) )
        //		{
        //			return 0;
        //		}

        include_once "./Services/Math/classes/class.EvalMath.php";
        $eval = new EvalMath();
        $eval->suppress_errors = true;
        $result = 0;

        if ($eval->e($a_entered) === false) {
            return 0;
        } elseif (($eval->e($lowerBound) !== false) && ($eval->e($upperBound) !== false)) {
            // fau.
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
        } else {
            if ($eval->e($a_entered) == $eval->e($a_original)) {
                $result = $max_points;
            }
        }
        return $result;
    }

    /**
     * @param $value
     * @return int
     */
    public function checkForValidFormula($value)
    {
        return preg_match("/^-?(\\d*)(,|\\.|\\/){0,1}(\\d*)$/", $value, $matches);
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
    public function calculateReachedPoints($active_id, $pass = null, $authorized = true, $returndetails = false)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }

        $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorized);
        $user_result = array();
        while ($data = $ilDB->fetchAssoc($result)) {
            if (strcmp($data["value2"], "") != 0) {
                $user_result[$data["value1"]] = array(
                    "gap_id" => $data["value1"],
                    "value" => $data["value2"]
                );
            }
        }

        ksort($user_result); // this is required when identical scoring for same solutions is disabled

        if ($returndetails) {
            $detailed = array();
            $this->calculateReachedPointsForSolution($user_result, $detailed);
            return $detailed;
        }

        return $this->calculateReachedPointsForSolution($user_result);
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
        foreach ($this->getSolutionSubmitValidation() as $gapIndex => $value) {
            $gap = $this->getGap($gapIndex);

            if ($gap->getType() != CLOZE_NUMERIC) {
                continue;
            }

            if (strlen($value) && !$this->isValidNumericSubmitValue($value)) {
                ilUtil::sendFailure($this->lng->txt("err_no_numeric_value"), true);
                return false;
            }
        }

        return true;
    }

    public function fetchSolutionSubmit($submit)
    {
        $solutionSubmit = array();
        foreach ($submit as $key => $value) {
            if ($value === null || is_array($value)) {
                continue;
            }

            $trimmed_value = trim($value);
            if ($trimmed_value === '') {
                continue;
            }

            if (preg_match("/^gap_(\d+)/", $key, $matches)) {
                $gap = $this->getGap($matches[1]);
                if (!is_object($gap)
                    || $gap->getType() == CLOZE_SELECT && $trimmed_value == -1) {
                    continue;
                }

                if ($gap->getType() == CLOZE_NUMERIC && !is_numeric(str_replace(",", ".", $trimmed_value))) {
                    $trimmed_value = null;
                } elseif ($gap->getType() == CLOZE_NUMERIC) {
                    $trimmed_value = str_replace(",", ".", $trimmed_value);
                }
                $solutionSubmit[trim($matches[1])] = $trimmed_value;
            }
        }

        return $solutionSubmit;
    }

    public function getSolutionSubmitValidation()
    {
        $submit = $_POST;
        $solutionSubmit = array();

        foreach ($submit as $key => $value) {
            if (preg_match("/^gap_(\d+)/", $key, $matches)) {
                if ($value !== null && $value !== '') {
                    $gap = $this->getGap($matches[1]);
                    if (is_object($gap)) {
                        if (!(($gap->getType() == CLOZE_SELECT) && ($value == -1))) {
                            if ($gap->getType() == CLOZE_NUMERIC) {
                                $value = str_replace(",", ".", $value);
                            }
                            $solutionSubmit[trim($matches[1])] = $value;
                        }
                    }
                }
            }
        }

        return $solutionSubmit;
    }

    public function getSolutionSubmit()
    {
        return $this->fetchSolutionSubmit($_POST);
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

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use (&$entered_values, $active_id, $pass, $authorized) {
            $this->removeCurrentSolution($active_id, $pass, $authorized);

            foreach ($this->getSolutionSubmit() as $key => $value) {
                if ($value !== null && $value !== '') {
                    $gap = $this->getGap($key);
                    if (is_object($gap)) {
                        if (!(($gap->getType() == CLOZE_SELECT) && ($value == -1))) {
                            $this->saveCurrentSolution($active_id, $pass, $key, $value, $authorized);
                            $entered_values++;
                        }
                    }
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

    /**
    * Returns the question type of the question
    *
    * @return integer The question type of the question
    * @access public
    */
    public function getQuestionType()
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
    public function getTextgapRating()
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
    public function setTextgapRating($a_textgap_rating)
    {
        switch ($a_textgap_rating) {
            case TEXTGAP_RATING_CASEINSENSITIVE:
            case TEXTGAP_RATING_CASESENSITIVE:
            case TEXTGAP_RATING_LEVENSHTEIN1:
            case TEXTGAP_RATING_LEVENSHTEIN2:
            case TEXTGAP_RATING_LEVENSHTEIN3:
            case TEXTGAP_RATING_LEVENSHTEIN4:
            case TEXTGAP_RATING_LEVENSHTEIN5:
                $this->textgap_rating = $a_textgap_rating;
                break;
            default:
                $this->textgap_rating = TEXTGAP_RATING_CASEINSENSITIVE;
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
    public function getIdenticalScoring()
    {
        return ($this->identical_scoring) ? 1 : 0;
    }

    /**
    * Sets the identical scoring option for cloze questions
    *
    * @param boolean $a_identical_scoring The identical scoring option for cloze questions
    * @see $identical_scoring
    * @access public
    */
    public function setIdenticalScoring($a_identical_scoring)
    {
        $this->identical_scoring = ($a_identical_scoring) ? 1 : 0;
    }

    /**
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    * @access public
    */
    public function getAdditionalTableName()
    {
        return "qpl_qst_cloze";
    }

    /**
    * Returns the name of the answer table in the database
    *
    * @return string The answer table name
    * @access public
    */
    public function getAnswerTableName()
    {
        return array("qpl_a_cloze",'qpl_a_cloze_combi_res');
    }

    /**
    * Sets a fixed text length for all text fields in the cloze question
    *
    * @param integer $a_text_len The text field length
    * @access public
    */
    public function setFixedTextLength($a_text_len)
    {
        $this->fixedTextLength = $a_text_len;
    }

    /**
    * Gets the fixed text length for all text fields in the cloze question
    *
    * @return integer The text field length
    * @access public
    */
    public function getFixedTextLength()
    {
        return $this->fixedTextLength;
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
    public function getRTETextWithMediaObjects()
    {
        return parent::getRTETextWithMediaObjects() . $this->getClozeText();
    }
    public function getGapCombinationsExists()
    {
        return $this->gap_combinations_exists;
    }

    public function getGapCombinations()
    {
        return $this->gap_combinations;
    }

    public function setGapCombinationsExists($value)
    {
        $this->gap_combinations_exists = $value;
    }

    public function setGapCombinations($value)
    {
        $this->gap_combinations = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLS($worksheet, $startrow, $active_id, $pass)
    {
        parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);

        $solution = $this->getSolutionValues($active_id, $pass);
        $i = 1;
        foreach ($this->getGaps() as $gap_index => $gap) {
            $worksheet->setCell($startrow + $i, 0, $this->lng->txt("gap") . " $i");
            $worksheet->setBold($worksheet->getColumnCoord(0) . ($startrow + $i));
            $checked = false;
            foreach ($solution as $solutionvalue) {
                if ($gap_index == $solutionvalue["value1"]) {
                    $string_escaping_org_value = $worksheet->getStringEscaping();
                    try {
                        $worksheet->setStringEscaping(false);

                        switch ($gap->getType()) {
                            case CLOZE_SELECT:
                                $worksheet->setCell($startrow + $i, 2, $gap->getItem($solutionvalue["value2"])->getAnswertext());
                                break;
                            case CLOZE_NUMERIC:
                            case CLOZE_TEXT:
                                $worksheet->setCell($startrow + $i, 2, $solutionvalue["value2"]);
                                break;
                        }
                    } finally {
                        $worksheet->setStringEscaping($string_escaping_org_value);
                    }
                }
            }
            $i++;
        }

        return $startrow + $i + 1;
    }

    /**
     * @param ilAssSelfAssessmentMigrator $migrator
     */
    protected function lmMigrateQuestionTypeSpecificContent(ilAssSelfAssessmentMigrator $migrator)
    {
        // DO NOT USE SETTER FOR CLOZE TEXT -> SETTER DOES RECREATE GAP OBJECTS without having gap type info ^^
        //$this->setClozeText( $migrator->migrateToLmContent($this->getClozeText()) );
        $this->cloze_text = $migrator->migrateToLmContent($this->getClozeText());
        // DO NOT USE SETTER FOR CLOZE TEXT -> SETTER DOES RECREATE GAP OBJECTS without having gap type info ^^
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
        $result['clozetext'] = $this->formatSAQuestion($this->getClozeText());
        $result['nr_of_tries'] = (int) $this->getNrOfTries();
        $result['shuffle'] = (bool) $this->getShuffle();
        $result['feedback'] = array(
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        );

        $gaps = array();
        foreach ($this->getGaps() as $key => $gap) {
            $items = array();
            foreach ($gap->getItems($this->getShuffler()) as $item) {
                $jitem = array();
                $jitem['points'] = $item->getPoints();
                $jitem['value'] = $this->formatSAQuestion($item->getAnswertext());
                $jitem['order'] = $item->getOrder();
                if ($gap->getType() == CLOZE_NUMERIC) {
                    $jitem['lowerbound'] = $item->getLowerBound();
                    $jitem['upperbound'] = $item->getUpperBound();
                } else {
                    $jitem['value'] = trim($jitem['value']);
                }
                array_push($items, $jitem);
            }

            if ($gap->getGapSize() && ($gap->getType() == CLOZE_TEXT || $gap->getType() == CLOZE_NUMERIC)) {
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

    /**
     * Get all available operations for a specific question
     *
     * @param string $expression
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
            iQuestionCondition::NumberOfResultExpression,
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
                "
				SELECT sol.value1+1 as val, sol.value2, cloze.cloze_type
				FROM tst_solutions sol
				INNER JOIN qpl_a_cloze cloze ON cloze.gap_id = value1 AND cloze.question_fi = sol.question_fi
				WHERE sol.active_fi = %s AND sol.pass = %s AND sol.question_fi = %s AND sol.step = %s
				GROUP BY sol.solution_id, sol.value1+1, sol.value2, cloze.cloze_type
				",
                array("integer", "integer", "integer","integer"),
                array($active_id, $pass, $this->getId(), $maxStep)
            );
        } else {
            $data = $ilDB->queryF(
                "
				SELECT sol.value1+1 as val, sol.value2, cloze.cloze_type
				FROM tst_solutions sol
				INNER JOIN qpl_a_cloze cloze ON cloze.gap_id = value1 AND cloze.question_fi = sol.question_fi
				WHERE sol.active_fi = %s AND sol.pass = %s AND sol.question_fi = %s
				GROUP BY sol.solution_id, sol.value1+1, sol.value2, cloze.cloze_type
				",
                array("integer", "integer", "integer"),
                array($active_id, $pass, $this->getId())
            );
        }

        while ($row = $ilDB->fetchAssoc($data)) {
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

    public function calculateCombinationResult($user_result)
    {
        $points = 0;

        $assClozeGapCombinationObj = new assClozeGapCombination();

        if ($assClozeGapCombinationObj->combinationExistsForQid($this->getId())) {
            $combinations_for_question = $assClozeGapCombinationObj->getCleanCombinationArray($this->getId());
            $gap_answers = array();
            $gap_used_in_combination = array();
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
                                case CLOZE_TEXT:
                                    $is_text_gap_correct = $this->getTextgapPoints($gap_answers[$gap_key], $combination_gap_answer['answer'], 1);
                                    if ($is_text_gap_correct != 1) {
                                        $combination_fulfilled = false;
                                    }
                                    break;
                                case CLOZE_SELECT:
                                    $answer = $this->gaps[$gap_key]->getItem($gap_answers[$gap_key]);
                                    $answertext = $answer->getAnswertext();
                                    if ($answertext != $combination_gap_answer['answer']) {
                                        $combination_fulfilled = false;
                                    }
                                    break;
                                case CLOZE_NUMERIC:
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
        return array($points, $gap_used_in_combination);
    }
    /**
     * @param $user_result
     * @param $detailed
     * @return array
     */
    protected function calculateReachedPointsForSolution($user_result, &$detailed = null)
    {
        if ($detailed === null) {
            $detailed = array();
        }

        $assClozeGapCombinationObj = new assClozeGapCombination();
        $combinations[1] = array();
        if ($assClozeGapCombinationObj->combinationExistsForQid($this->getId())) {
            $combinations = $this->calculateCombinationResult($user_result);
            $points = $combinations[0];
        }
        $counter = 0;
        $solution_values_text = array(); // for identical scoring checks
        $solution_values_select = array(); // for identical scoring checks
        $solution_values_numeric = array(); // for identical scoring checks
        foreach ($user_result as $gap_id => $value) {
            if (is_string($value)) {
                $value = array("value" => $value);
            }

            if (array_key_exists($gap_id, $this->gaps) && !array_key_exists($gap_id, $combinations[1])) {
                switch ($this->gaps[$gap_id]->getType()) {
                    case CLOZE_TEXT:
                        $gappoints = 0;
                        for ($order = 0; $order < $this->gaps[$gap_id]->getItemCount(); $order++) {
                            $answer = $this->gaps[$gap_id]->getItem($order);
                            $gotpoints = $this->getTextgapPoints($answer->getAnswertext(), $value["value"], $answer->getPoints());
                            if ($gotpoints > $gappoints) {
                                $gappoints = $gotpoints;
                            }
                        }
                        if (!$this->getIdenticalScoring()) {
                            // check if the same solution text was already entered
                            if ((in_array($value["value"], $solution_values_text)) && ($gappoints > 0)) {
                                $gappoints = 0;
                            }
                        }
                        $points += $gappoints;
                        $detailed[$gap_id] = array("points" => $gappoints, "best" => ($this->getMaximumGapPoints($gap_id) == $gappoints) ? true : false, "positive" => ($gappoints > 0) ? true : false);
                        array_push($solution_values_text, $value["value"]);
                        break;
                    case CLOZE_NUMERIC:
                        $gappoints = 0;
                        for ($order = 0; $order < $this->gaps[$gap_id]->getItemCount(); $order++) {
                            $answer = $this->gaps[$gap_id]->getItem($order);
                            $gotpoints = $this->getNumericgapPoints($answer->getAnswertext(), $value["value"], $answer->getPoints(), $answer->getLowerBound(), $answer->getUpperBound());
                            if ($gotpoints > $gappoints) {
                                $gappoints = $gotpoints;
                            }
                        }
                        if (!$this->getIdenticalScoring()) {
                            // check if the same solution value was already entered
                            include_once "./Services/Math/classes/class.EvalMath.php";
                            $eval = new EvalMath();
                            $eval->suppress_errors = true;
                            $found_value = false;
                            foreach ($solution_values_numeric as $solval) {
                                if ($eval->e($solval) == $eval->e($value["value"])) {
                                    $found_value = true;
                                }
                            }
                            if ($found_value && ($gappoints > 0)) {
                                $gappoints = 0;
                            }
                        }
                        $points += $gappoints;
                        $detailed[$gap_id] = array("points" => $gappoints, "best" => ($this->getMaximumGapPoints($gap_id) == $gappoints) ? true : false, "positive" => ($gappoints > 0) ? true : false);
                        array_push($solution_values_numeric, $value["value"]);
                        break;
                    case CLOZE_SELECT:
                        if ($value["value"] >= 0) {
                            for ($order = 0; $order < $this->gaps[$gap_id]->getItemCount(); $order++) {
                                $answer = $this->gaps[$gap_id]->getItem($order);
                                if ($value["value"] == $answer->getOrder()) {
                                    $answerpoints = $answer->getPoints();
                                    if (!$this->getIdenticalScoring()) {
                                        // check if the same solution value was already entered
                                        if ((in_array($answer->getAnswertext(), $solution_values_select)) && ($answerpoints > 0)) {
                                            $answerpoints = 0;
                                        }
                                    }
                                    $points += $answerpoints;
                                    $detailed[$gap_id] = array("points" => $answerpoints, "best" => ($this->getMaximumGapPoints($gap_id) == $answerpoints) ? true : false, "positive" => ($answerpoints > 0) ? true : false);
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

    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $previewSession)
    {
        $userSolution = array();

        foreach ($previewSession->getParticipantsSolution() as $key => $val) {
            $userSolution[$key] = array('gap_id' => $key, 'value' => $val);
        }

        $reachedPoints = $this->calculateReachedPointsForSolution($userSolution);
        $reachedPoints = $this->deductHintPointsFromReachedPoints($previewSession, $reachedPoints);

        return $this->ensureNonNegativePoints($reachedPoints);
    }

    public function fetchAnswerValueForGap($userSolution, $gapIndex)
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

    public function isAddableAnswerOptionValue($qIndex, $answerOptionValue)
    {
        $gap = $this->getGap($qIndex);

        if ($gap->getType() != CLOZE_TEXT) {
            return false;
        }

        foreach ($gap->getItems(new ilArrayElementOrderKeeper()) as $item) {
            if ($item->getAnswertext() === $answerOptionValue) {
                return false;
            }
        }

        return true;
    }

    public function addAnswerOptionValue($qIndex, $answerOptionValue, $points)
    {
        $gap = $this->getGap($qIndex); /* @var assClozeGap $gap */

        $item = new assAnswerCloze($answerOptionValue, $points);
        $item->setOrder($gap->getItemCount());

        $gap->addItem($item);
    }

    public function savePartial()
    {
        return true;
    }
}
