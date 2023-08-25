<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilObjAnswerScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.iQuestionCondition.php';
require_once './Modules/TestQuestionPool/classes/class.ilUserQuestionResult.php';
require_once 'Modules/TestQuestionPool/interfaces/interface.ilAssSpecificFeedbackOptionLabelProvider.php';

/**
 * Class for multiple choice tests.
 *
 * assMultipleChoice is a class for multiple choice questions.
 *
 * @extends assQuestion
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <bheyser@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup		ModulesTestQuestionPool
 */
class assMultipleChoice extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition, ilAssSpecificFeedbackOptionLabelProvider
{
    /**
     * The given answers of the multiple choice question
     *
     * $answers is an array of the given answers of the multiple choice question
     *
     * @var array
     */
    public $answers;

    /**
     * Output type
     *
     * This is the output type for the answers of the multiple choice question. You can select
     * OUTPUT_ORDER(=0) or OUTPUT_RANDOM (=1). The default output type is OUTPUT_ORDER
     *
     * @var integer
     */
    public $output_type;

    public $isSingleline;
    public $lastChange;
    public $feedback_setting;

    /** @var integer Thumbnail size */
    protected $thumb_size;

    /**
     * @var integer
     */
    protected $selectionLimit;

    /**
     * @param mixed $isSingleline
     */
    public function setIsSingleline($isSingleline)
    {
        $this->isSingleline = $isSingleline;
    }

    /**
     * @return mixed
     */
    public function getIsSingleline()
    {
        return $this->isSingleline;
    }

    /**
     * @param mixed $lastChange
     */
    public function setLastChange($lastChange)
    {
        $this->lastChange = $lastChange;
    }

    /**
     * @return mixed
     */
    public function getLastChange()
    {
        return $this->lastChange;
    }

    /**
     * assMultipleChoice constructor
     *
     * The constructor takes possible arguments an creates an instance of the assMultipleChoice object.
     *
     * @param string     $title       A title string to describe the question
     * @param string     $comment     A comment string to describe the question
     * @param string     $author      A string containing the name of the questions author
     * @param integer    $owner       A numerical ID to identify the owner/creator
     * @param string     $question    The question string of the multiple choice question
     * @param int|string $output_type The output order of the multiple choice answers
     *
     * @see assQuestion:assQuestion()
     */
    public function __construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = "",
        $output_type = OUTPUT_ORDER
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->output_type = $output_type;
        $this->thumb_size = 150;
        $this->answers = array();
        $this->shuffle = 1;
        $this->selectionLimit = null;
        $this->feedback_setting = 0;
    }

    /**
     * @return int
     */
    public function getSelectionLimit()
    {
        return $this->selectionLimit;
    }

    /**
     * @param int $selectionLimit
     */
    public function setSelectionLimit($selectionLimit)
    {
        $this->selectionLimit = $selectionLimit;
    }

    /**
    * Returns true, if a multiple choice question is complete for use
    *
    * @return boolean True, if the multiple choice question is complete for use, otherwise false
    * @access public
    */
    public function isComplete()
    {
        if (strlen($this->title) and ($this->author) and ($this->question) and (count($this->answers)) and ($this->getMaximumPoints() > 0)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Saves a assMultipleChoice object to a database
     *
     * @param string $original_id
     */
    public function saveToDb($original_id = "")
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();

        $this->ensureNoInvalidObligation($this->getId());
        parent::saveToDb($original_id);
    }

    /**
     * Rebuild the thumbnail images with a new thumbnail size
     */
    protected function rebuildThumbnails()
    {
        if ($this->isSingleline && ($this->getThumbSize())) {
            foreach ($this->getAnswers() as $answer) {
                if (strlen($answer->getImage())) {
                    $this->generateThumbForFile($this->getImagePath(), $answer->getImage());
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getThumbPrefix()
    {
        return "thumb.";
    }

    /**
     * @param $path string
     * @param $file string
     */
    protected function generateThumbForFile($path, $file)
    {
        $filename = $path . $file;
        if (@file_exists($filename)) {
            $thumbpath = $path . $this->getThumbPrefix() . $file;
            $path_info = @pathinfo($filename);
            $ext = "";
            switch (strtoupper($path_info['extension'])) {
                case 'PNG':
                    $ext = 'PNG';
                    break;
                case 'GIF':
                    $ext = 'GIF';
                    break;
                default:
                    $ext = 'JPEG';
                    break;
            }
            ilUtil::convertImage($filename, $thumbpath, $ext, $this->getThumbSize());
        }
    }

    /**
    * Loads a assMultipleChoice object from a database
    *
    * @param integer $question_id A unique key which defines the multiple choice test in the database
    */
    public function loadFromDb($question_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $hasimages = 0;

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
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setComment($data["description"]);
            $this->setOriginalId($data["original_id"]);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            include_once("./Services/RTE/classes/class.ilRTE.php");
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"], 1));
            $shuffle = (is_null($data['shuffle'])) ? true : $data['shuffle'];
            $this->setShuffle($shuffle);
            $this->setEstimatedWorkingTime(substr($data["working_time"], 0, 2), substr($data["working_time"], 3, 2), substr($data["working_time"], 6, 2));
            $this->setThumbSize($data['thumb_size']);
            $this->isSingleline = ($data['allow_images']) ? false : true;
            $this->lastChange = $data['tstamp'];
            $this->setSelectionLimit((int) $data['selection_limit'] > 0 ? (int) $data['selection_limit'] : null);
            $this->feedback_setting = $data['feedback_setting'];

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
            "SELECT * FROM qpl_a_mc WHERE question_fi = %s ORDER BY aorder ASC",
            array('integer'),
            array($question_id)
        );
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerMultipleResponseImage.php";
        if ($result->numRows() > 0) {
            while ($data = $ilDB->fetchAssoc($result)) {
                $imagefilename = $this->getImagePath() . $data["imagefile"];
                if (!@file_exists($imagefilename)) {
                    $data["imagefile"] = "";
                }
                include_once("./Services/RTE/classes/class.ilRTE.php");
                $data["answertext"] = ilRTE::_replaceMediaObjectImageSrc($data["answertext"], 1);
                array_push($this->answers, new ASS_AnswerMultipleResponseImage($data["answertext"], $data["points"], $data["aorder"], $data["points_unchecked"], $data["imagefile"], $data["answer_id"]));
            }
        }

        parent::loadFromDb($question_id);
    }

    /**
     * Duplicates an assMultipleChoiceQuestion
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
        // duplicate the images
        $clone->duplicateImages($this_id, $thisObjId);

        $clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    /**
     * Copies an assMultipleChoice object
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
        // duplicate the image
        $clone->copyImages($original_id, $source_questionpool_id);

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
        // duplicate the image
        $clone->copyImages($sourceQuestionId, $sourceParentId);

        $clone->onCopy($sourceParentId, $sourceQuestionId, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    /**
    * Gets the multiple choice output type which is either OUTPUT_ORDER (=0) or OUTPUT_RANDOM (=1).
    *
    * @return integer The output type of the assMultipleChoice object
    * @see $output_type
    */
    public function getOutputType()
    {
        return $this->output_type;
    }

    /**
     * Sets the output type of the assMultipleChoice object
     *
     * @param int|string $output_type A nonnegative integer value specifying the output type. It is OUTPUT_ORDER (=0) or OUTPUT_RANDOM (=1).
     *
     * @see    $response
     */
    public function setOutputType($output_type = OUTPUT_ORDER)
    {
        $this->output_type = $output_type;
    }

    /**
     * Adds a possible answer for a multiple choice question. A ASS_AnswerBinaryStateImage object will be
     * created and assigned to the array $this->answers.
     *
     * @param string  $answertext 		The answer text
     * @param double  $points     		The points for selecting the answer (even negative points can be used)
     * @param float   $points_unchecked The points for not selecting the answer (even positive points can be used)
     * @param integer $order      		A possible display order of the answer
     * @param string  $answerimage
     * @param int     $answer_id        The Answer id used in the database
     *
     * @see      $answers
     * @see      ASS_AnswerBinaryStateImage
     */
    public function addAnswer(
        $answertext = "",
        $points = 0.0,
        $points_unchecked = 0.0,
        $order = 0,
        $answerimage = "",
        $answer_id = -1
    ) {
        include_once "./Modules/TestQuestionPool/classes/class.assAnswerMultipleResponseImage.php";
        $answertext = $this->getHtmlQuestionContentPurifier()->purify($answertext);
        if (array_key_exists($order, $this->answers)) {
            // insert answer
            $answer = new ASS_AnswerMultipleResponseImage($answertext, $points, $order, $points_unchecked, $answerimage, $answer_id);
            $newchoices = array();
            for ($i = 0; $i < $order; $i++) {
                array_push($newchoices, $this->answers[$i]);
            }
            array_push($newchoices, $answer);
            for ($i = $order; $i < count($this->answers); $i++) {
                $changed = $this->answers[$i];
                $changed->setOrder($i + 1);
                array_push($newchoices, $changed);
            }
            $this->answers = $newchoices;
        } else {
            // add answer
            $answer = new ASS_AnswerMultipleResponseImage($answertext, $points, count($this->answers), $points_unchecked, $answerimage, $answer_id);
            array_push($this->answers, $answer);
        }
    }

    /**
     * Returns the number of answers
     *
     * @return integer The number of answers of the multiple choice question
     * @see $answers
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
     * @return object ASS_AnswerBinaryStateImage-Object containing the answer
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
        $answer = $this->answers[$index];
        if (strlen($answer->getImage())) {
            $this->deleteImage($answer->getImage());
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
     * @see $answers
     */
    public function flushAnswers()
    {
        $this->answers = array();
    }

    /**
     * Returns the maximum points, a learner can reach answering the question
     *
     * @see $points
     */
    public function getMaximumPoints()
    {
        $points = 0;
        $allpoints = 0;
        foreach ($this->answers as $key => $value) {
            if ($value->getPoints() > $value->getPointsUnchecked()) {
                $allpoints += $value->getPoints();
            } else {
                $allpoints += $value->getPointsUnchecked();
            }
        }
        return $allpoints;
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
     * @return integer|array $points/$details (array $details is deprecated !!)
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
        while ($data = $ilDB->fetchAssoc($result)) {
            if (strcmp($data["value1"], "") != 0) {
                array_push($found_values, $data["value1"]);
            }
        }

        $points = $this->calculateReachedPointsForSolution($found_values, $active_id);

        return $points;
    }

    public function validateSolutionSubmit()
    {
        $submit = $this->getSolutionSubmit();

        if ($this->getSelectionLimit()) {
            if (count($submit) > $this->getSelectionLimit()) {
                $failureMsg = sprintf(
                    $this->lng->txt('ass_mc_sel_lim_exhausted_hint'),
                    $this->getSelectionLimit(),
                    $this->getAnswerCount()
                );

                ilUtil::sendFailure($failureMsg, true);
                return false;
            }
        }

        return true;
    }

    protected function isForcedEmptySolution($solutionSubmit)
    {
        if (!count($solutionSubmit) && !empty($_POST['tst_force_form_diff_input'])) {
            return true;
        }

        return false;
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
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $pass = ilObjTest::_getPass($active_id);
        }

        $entered_values = 0;

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use (&$entered_values, $active_id, $pass, $authorized) {
            $this->removeCurrentSolution($active_id, $pass, $authorized);

            $solutionSubmit = $this->getSolutionSubmit();

            foreach ($solutionSubmit as $value) {
                if (strlen($value)) {
                    $this->saveCurrentSolution($active_id, $pass, $value, null, $authorized);
                    $entered_values++;
                }
            }

            // fau: testNav - write a dummy entry for the evil mc questions with "None of the above" checked
            if ($this->isForcedEmptySolution($solutionSubmit)) {
                $this->saveCurrentSolution($active_id, $pass, 'mc_none_above', null, $authorized);
                $entered_values++;
            }
            // fau.
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
        /** @var $ilDB ilDBInterface */
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $oldthumbsize = 0;
        if ($this->isSingleline && ($this->getThumbSize())) {
            // get old thumbnail size
            $result = $ilDB->queryF(
                "SELECT thumb_size FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
                ['integer'],
                [$this->getId()]
            );
            if ($result->numRows() == 1) {
                $data = $ilDB->fetchAssoc($result);
                $oldthumbsize = $data['thumb_size'];
            }
        }

        if (!$this->isSingleline) {
            ilUtil::delDir($this->getImagePath());
        }

        // save additional data
        $ilDB->replace(
            $this->getAdditionalTableName(),
            [
                'shuffle' => array('text', $this->getShuffle()),
                'allow_images' => array('text', $this->isSingleline ? 0 : 1),
                'thumb_size' => array('integer', strlen($this->getThumbSize()) ? $this->getThumbSize() : null),
                'selection_limit' => array('integer', $this->getSelectionLimit()),
                'feedback_setting' => array('integer', $this->getSpecificFeedbackSetting())
            ],
            ['question_fi' => array('integer', $this->getId())]
        );
    }

    /**
     * Deletes all existing Answer data from a question and reintroduces old data and changes.
     * Additionally, it updates the corresponding feedback.
     * @return void
     */
    public function saveAnswerSpecificDataToDb()
    {
        /** @var $ilDB ilDBInterface */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // Get all feedback entries
        $result = $ilDB->queryF(
            "SELECT * FROM qpl_fb_specific WHERE question_fi = %s",
            ['integer'],
            [$this->getId()]
        );
        $db_feedback = $ilDB->fetchAll($result);

        // Check if feedback exists and the regular editor is used and not the page editor
        if (sizeof($db_feedback) >= 1 && $this->getAdditionalContentEditingMode() == 'default'){
            // Get all existing answer data for question
            $result = $ilDB->queryF(
                "SELECT answer_id, aorder  FROM qpl_a_mc WHERE question_fi = %s",
                ['integer'],
                [$this->getId()]
            );
            $db_answers = $ilDB->fetchAll($result);

            // Collect old and new order entries by ids and order to calculate a diff/intersection and remove/update feedback
            $post_answer_order_for_id = [];
            foreach ($this->answers as $answer){
                // Only the first appearance of an id is used
                if ($answer->getId() !== null && !in_array($answer->getId(), array_keys($post_answer_order_for_id))) {
                    // -1 is happening while import and also if a new multi line answer is generated
                    if ($answer->getId() == -1) {
                        continue;
                    }
                    $post_answer_order_for_id[$answer->getId()] = $answer->getOrder();
                }
            }

            // If there is no usable ids from post, it's better to not touch the feedback
            // This is useful since the import is also using this function or the first creation of a new question in general
            if (sizeof($post_answer_order_for_id) >= 1) {
                $db_answer_order_for_id = [];
                $db_answer_id_for_order = [];
                foreach ($db_answers as $db_answer){
                    $db_answer_order_for_id[intval($db_answer['answer_id'])] = intval($db_answer['aorder']);
                    $db_answer_id_for_order[intval($db_answer['aorder'])] = intval($db_answer['answer_id']);
                }

                // Handle feedback
                // the diff between the already existing answer ids from the Database and the answer ids from post
                // feedback related to the answer ids should be deleted or in our case not recreated.
                $db_answer_ids = array_keys($db_answer_order_for_id);
                $post_answer_ids = array_keys($post_answer_order_for_id);
                $diff_db_post_answer_ids = array_diff($db_answer_ids, $post_answer_ids);
                $unused_answer_ids = array_keys($diff_db_post_answer_ids);

                // Delete all feedback in the database
                $this->feedbackOBJ->deleteSpecificAnswerFeedbacks($this->getId(), false);
                // Recreate feedback
                foreach ($db_feedback as $feedback_option) {
                    // skip feedback which answer is deleted
                    if (in_array(intval($feedback_option['answer']), $unused_answer_ids)) {
                        continue;
                    }

                    // Reorder feedback
                    $feedback_order_db = intval($feedback_option['answer']);
                    $db_answer_id = $db_answer_id_for_order[$feedback_order_db];
                    // This cuts feedback that currently would have no corresponding answer
                    // This case can happen while copying "broken" questions
                    // Or when saving a question with less answers than feedback
                    if (is_null($db_answer_id) || $db_answer_id < 0) {
                        continue;
                    }
                    $feedback_order_post = $post_answer_order_for_id[$db_answer_id];
                    $feedback_option['answer'] = $feedback_order_post;

                    // Recreate remaining feedback in database
                    $next_id = $ilDB->nextId('qpl_fb_specific');
                    $ilDB->manipulateF(
                        "INSERT INTO qpl_fb_specific (feedback_id, question_fi, answer, tstamp, feedback, question) 
                            VALUES (%s, %s, %s, %s, %s, %s)",
                        ['integer', 'integer', 'integer', 'integer', 'text', 'integer'],
                        [
                            $next_id,
                            $feedback_option['question_fi'],
                            $feedback_option['answer'],
                            time(),
                            $feedback_option['feedback'],
                            $feedback_option['question']
                        ]
                    );
                }
            }
        }

        // Delete all entries in qpl_a_mc for question
        $ilDB->manipulateF(
            "DELETE FROM qpl_a_mc WHERE question_fi = %s",
            ['integer'],
            [$this->getId()]
        );

        // Recreate answers one by one
        foreach ($this->answers as $key => $value) {
            $answer_obj = $this->answers[$key];
            $next_id = $ilDB->nextId('qpl_a_mc');
            $ilDB->manipulateF(
                "INSERT INTO qpl_a_mc (answer_id, question_fi, answertext, points, points_unchecked, aorder, imagefile, tstamp) 
                        VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
                ['integer', 'integer', 'text', 'float', 'float', 'integer', 'text', 'integer'],
                [
                    $next_id,
                    $this->getId(),
                    ilRTE::_replaceMediaObjectImageSrc($answer_obj->getAnswertext(), 0),
                    $answer_obj->getPoints(),
                    $answer_obj->getPointsUnchecked(),
                    $answer_obj->getOrder(),
                    $answer_obj->getImage(),
                    time()
                ]
            );
        }
        $this->rebuildThumbnails();
    }

    public function syncWithOriginal()
    {
        if ($this->getOriginalId()) {
            $this->syncImages();
            parent::syncWithOriginal();
        }
    }

    /**
     * Returns the question type of the question
     *
     * @return integer The question type of the question
     */
    public function getQuestionType()
    {
        return "assMultipleChoice";
    }

    /**
     * Returns the name of the additional question data table in the database
     *
     * @return string The additional table name
     */
    public function getAdditionalTableName()
    {
        return "qpl_qst_mc";
    }

    /**
     * Returns the name of the answer table in the database
     *
     * @return string The answer table name
     */
    public function getAnswerTableName()
    {
        return "qpl_a_mc";
    }

    /**
     * Sets the image file and uploads the image to the object's image directory.
     *
     * @param string $image_filename Name of the original image file
     * @param string $image_tempfilename Name of the temporary uploaded image file
     * @return integer An errorcode if the image upload fails, 0 otherwise
     */
    public function setImageFile($image_filename, $image_tempfilename = "")
    {
        $result = 0;
        if (!empty($image_tempfilename)) {
            $image_filename = str_replace(" ", "_", $image_filename);
            $imagepath = $this->getImagePath();
            if (!file_exists($imagepath)) {
                ilUtil::makeDirParents($imagepath);
            }
            if (!ilUtil::moveUploadedFile($image_tempfilename, $image_filename, $imagepath . $image_filename)) {
                $result = 2;
            } else {
                include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
                $mimetype = ilObjMediaObject::getMimeType($imagepath . $image_filename);
                if (!preg_match("/^image/", $mimetype)) {
                    unlink($imagepath . $image_filename);
                    $result = 1;
                } else {
                    // create thumbnail file
                    if ($this->isSingleline && ($this->getThumbSize())) {
                        $this->generateThumbForFile($imagepath, $image_filename);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Deletes an image file
     *
     * @param string $image_filename Name of the image file to delete
     */
    protected function deleteImage($image_filename)
    {
        $imagepath = $this->getImagePath();
        @unlink($imagepath . $image_filename);
        $thumbpath = $imagepath . $this->getThumbPrefix() . $image_filename;
        @unlink($thumbpath);
    }

    public function duplicateImages($question_id, $objectId = null)
    {
        /** @var $ilLog ilLogger */
        global $DIC;
        $ilLog = $DIC['ilLog'];

        $imagepath = $this->getImagePath();
        $imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);

        if ((int) $objectId > 0) {
            $imagepath_original = str_replace("/$this->obj_id/", "/$objectId/", $imagepath_original);
        }

        foreach ($this->answers as $answer) {
            $filename = $answer->getImage();
            if (strlen($filename)) {
                if (!file_exists($imagepath)) {
                    ilUtil::makeDirParents($imagepath);
                }

                if (file_exists($imagepath_original . $filename)) {
                    if (!copy($imagepath_original . $filename, $imagepath . $filename)) {
                        $ilLog->warning(sprintf(
                            "Could not clone source image '%s' to '%s' (srcQuestionId: %s|tgtQuestionId: %s|srcParentObjId: %s|tgtParentObjId: %s)",
                            $imagepath_original . $filename,
                            $imagepath . $filename,
                            $question_id,
                            $this->id,
                            $objectId,
                            $this->obj_id
                        ));
                    }
                }

                if (file_exists($imagepath_original . $this->getThumbPrefix() . $filename)) {
                    if (!copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) {
                        $ilLog->warning(sprintf(
                            "Could not clone thumbnail source image '%s' to '%s' (srcQuestionId: %s|tgtQuestionId: %s|srcParentObjId: %s|tgtParentObjId: %s)",
                            $imagepath_original . $this->getThumbPrefix() . $filename,
                            $imagepath . $this->getThumbPrefix() . $filename,
                            $question_id,
                            $this->id,
                            $objectId,
                            $this->obj_id
                        ));
                    }
                }
            }
        }
    }

    public function copyImages($question_id, $source_questionpool)
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        $imagepath = $this->getImagePath();
        $imagepath_original = str_replace("/$this->id/images", "/$question_id/images", $imagepath);
        $imagepath_original = str_replace("/$this->obj_id/", "/$source_questionpool/", $imagepath_original);
        foreach ($this->answers as $answer) {
            $filename = $answer->getImage();
            if (strlen($filename)) {
                if (!file_exists($imagepath)) {
                    ilUtil::makeDirParents($imagepath);
                }
                if (!@copy($imagepath_original . $filename, $imagepath . $filename)) {
                    $ilLog->write("image could not be duplicated!!!!", $ilLog->ERROR);
                    $ilLog->write("object: " . print_r($this, true), $ilLog->ERROR);
                }
                if (@file_exists($imagepath_original . $this->getThumbPrefix() . $filename)) {
                    if (!@copy($imagepath_original . $this->getThumbPrefix() . $filename, $imagepath . $this->getThumbPrefix() . $filename)) {
                        $ilLog->write("image thumbnail could not be duplicated!!!!", $ilLog->ERROR);
                        $ilLog->write("object: " . print_r($this, true), $ilLog->ERROR);
                    }
                }
            }
        }
    }

    /**
     * Sync images of a MC question on synchronisation with the original question
     */
    protected function syncImages()
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        $imagepath = $this->getImagePath();
        $question_id = $this->getOriginalId();
        $originalObjId = parent::lookupParentObjId($this->getOriginalId());
        $imagepath_original = $this->getImagePath($question_id, $originalObjId);

        ilUtil::delDir($imagepath_original);
        foreach ($this->answers as $answer) {
            $filename = $answer->getImage();
            if (strlen($filename)) {
                if (@file_exists($imagepath . $filename)) {
                    if (!file_exists($imagepath)) {
                        ilUtil::makeDirParents($imagepath);
                    }
                    if (!file_exists($imagepath_original)) {
                        ilUtil::makeDirParents($imagepath_original);
                    }
                    if (!@copy($imagepath . $filename, $imagepath_original . $filename)) {
                        $ilLog->write("image could not be duplicated!!!!", $ilLog->ERROR);
                        $ilLog->write("object: " . print_r($this, true), $ilLog->ERROR);
                    }
                }
                if (@file_exists($imagepath . $this->getThumbPrefix() . $filename)) {
                    if (!@copy($imagepath . $this->getThumbPrefix() . $filename, $imagepath_original . $this->getThumbPrefix() . $filename)) {
                        $ilLog->write("image thumbnail could not be duplicated!!!!", $ilLog->ERROR);
                        $ilLog->write("object: " . print_r($this, true), $ilLog->ERROR);
                    }
                }
            }
        }
    }

    /**
     * Collects all text in the question which could contain media objects which were created with the Rich Text Editor.
     */
    public function getRTETextWithMediaObjects()
    {
        $text = parent::getRTETextWithMediaObjects();
        foreach ($this->answers as $index => $answer) {
            $text .= $this->feedbackOBJ->getSpecificAnswerFeedbackContent($this->getId(), 0, $index);
            $answer_obj = $this->answers[$index];
            $text .= $answer_obj->getAnswertext();
        }
        return $text;
    }

    /**
    * Returns a reference to the answers array
    */
    public function &getAnswers()
    {
        return $this->answers;
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLS($worksheet, $startrow, $active_id, $pass)
    {
        parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);

        $solution = $this->getSolutionValues($active_id, $pass);

        $i = 1;
        foreach ($this->getAnswers() as $id => $answer) {
            $worksheet->setCell($startrow + $i, 0, $answer->getAnswertext());
            $worksheet->setBold($worksheet->getColumnCoord(0) . ($startrow + $i));
            $checked = false;
            foreach ($solution as $solutionvalue) {
                if ($id == $solutionvalue["value1"]) {
                    $checked = true;
                }
            }
            if ($checked) {
                $worksheet->setCell($startrow + $i, 2, 1);
            } else {
                $worksheet->setCell($startrow + $i, 2, 0);
            }
            $i++;
        }

        return $startrow + $i + 1;
    }

    public function getThumbSize()
    {
        return $this->thumb_size;
    }

    public function setThumbSize($a_size)
    {
        $this->thumb_size = $a_size;
    }

    /**
     * @param ilAssSelfAssessmentMigrator $migrator
     */
    protected function lmMigrateQuestionTypeSpecificContent(ilAssSelfAssessmentMigrator $migrator)
    {
        foreach ($this->getAnswers() as $answer) {
            /* @var ASS_AnswerBinaryStateImage $answer */
            $answer->setAnswertext($migrator->migrateToLmContent($answer->getAnswertext()));
        }
    }

    /**
     * Returns a JSON representation of the question
     */
    public function toJSON()
    {
        require_once './Services/RTE/classes/class.ilRTE.php';
        $result = array();
        $result['id'] = (int) $this->getId();
        $result['type'] = (string) $this->getQuestionType();
        $result['title'] = (string) $this->getTitle();
        $result['question'] = $this->formatSAQuestion($this->getQuestion());
        $result['nr_of_tries'] = (int) $this->getNrOfTries();
        $result['shuffle'] = (bool) $this->getShuffle();
        $result['selection_limit'] = (int) $this->getSelectionLimit();
        $result['feedback'] = array(
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        );

        $answers = array();
        $has_image = false;
        foreach ($this->getAnswers() as $key => $answer_obj) {
            if ((string) $answer_obj->getImage()) {
                $has_image = true;
            }
            array_push($answers, array(
                "answertext" => (string) $this->formatSAQuestion($answer_obj->getAnswertext()),
                "points_checked" => (float) $answer_obj->getPointsChecked(),
                "points_unchecked" => (float) $answer_obj->getPointsUnchecked(),
                "order" => (int) $answer_obj->getOrder(),
                "image" => (string) $answer_obj->getImage(),
                "feedback" => $this->formatSAQuestion(
                    $this->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation($this->getId(), 0, $key)
                )
            ));
        }
        $result['answers'] = $answers;

        if ($has_image) {
            $result['path'] = $this->getImagePathWeb();
            $result['thumb'] = $this->getThumbSize();
        }

        $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->getId());
        $result['mobs'] = $mobs;

        return json_encode($result);
    }

    public function removeAnswerImage($index)
    {
        $answer = $this->answers[$index];
        if (is_object($answer)) {
            $this->deleteImage($answer->getImage());
            $answer->setImage('');
        }
    }

    public function getMultilineAnswerSetting()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        $multilineAnswerSetting = $ilUser->getPref("tst_multiline_answers");
        if ($multilineAnswerSetting != 1) {
            $multilineAnswerSetting = 0;
        }
        return $multilineAnswerSetting;
    }

    public function setMultilineAnswerSetting($a_setting = 0)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilUser->writePref("tst_multiline_answers", $a_setting);
    }

    /**
     * Sets the feedback settings in effect for the question.
     * Options are:
     * 1 - Feedback is shown for all answer options.
     * 2 - Feedback is shown for all checked/selected options.
     * 3 - Feedback is shown for all correct options.
     *
     * @param integer $a_feedback_setting
     */
    public function setSpecificFeedbackSetting($a_feedback_setting)
    {
        $this->feedback_setting = $a_feedback_setting;
    }

    /**
     * Gets the current feedback settings in effect for the question.
     * Values are:
     * 1 - Feedback is shown for all answer options.
     * 2 - Feedback is shown for all checked/selected options.
     * 3 - Feedback is shown for all correct options.
     *
     * @return integer
     */
    public function getSpecificFeedbackSetting()
    {
        if ($this->feedback_setting) {
            return $this->feedback_setting;
        } else {
            return 1;
        }
    }

    public function getSpecificFeedbackAllCorrectOptionLabel()
    {
        return 'feedback_correct_sc_mc';
    }

    /**
     * returns boolean wether the question
     * is answered during test pass or not
     *
     * (overwrites method in class assQuestion)
     *
     * @param integer $active_id
     * @param integer $pass
     *
     * @return boolean $answered
     */
    public function isAnswered($active_id, $pass = null)
    {
        $numExistingSolutionRecords = assQuestion::getNumExistingSolutionRecords($active_id, $pass, $this->getId());

        return $numExistingSolutionRecords > 0;
    }

    /**
     * returns boolean wether it is possible to set
     * this question type as obligatory or not
     * considering the current question configuration
     *
     * (overwrites method in class assQuestion)
     *
     * @param integer $questionId
     *
     * @return boolean $obligationPossible
     */
    public static function isObligationPossible($questionId)
    {
        /** @var $ilDB ilDBInterface */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "
			SELECT SUM(points) points_for_checked_answers
			FROM qpl_a_mc
			WHERE question_fi = %s AND points > 0
		";

        $res = $ilDB->queryF($query, array('integer'), array($questionId));

        $row = $ilDB->fetchAssoc($res);

        return $row['points_for_checked_answers'] > 0;
    }

    /**
     * ensures that no invalid obligation is saved for the question used in test
     *
     * when points can be reached ONLY by NOT check any answer
     * a possibly still configured obligation will be removed
     *
     * @param integer $questionId
     */
    public function ensureNoInvalidObligation($questionId)
    {
        /** @var $ilDB ilDBInterface */
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "
			SELECT		SUM(qpl_a_mc.points) points_for_checked_answers,
						test_question_id

			FROM		tst_test_question

			INNER JOIN	qpl_a_mc
			ON			qpl_a_mc.question_fi = tst_test_question.question_fi

			WHERE		tst_test_question.question_fi = %s
			AND			tst_test_question.obligatory = 1

			GROUP BY	test_question_id
		";

        $res = $ilDB->queryF($query, array('integer'), array($questionId));

        $updateTestQuestionIds = array();

        while ($row = $ilDB->fetchAssoc($res)) {
            if ($row['points_for_checked_answers'] <= 0) {
                $updateTestQuestionIds[] = $row['test_question_id'];
            }
        }

        if (count($updateTestQuestionIds)) {
            $test_question_id__IN__updateTestQuestionIds = $ilDB->in(
                'test_question_id',
                $updateTestQuestionIds,
                false,
                'integer'
            );

            $query = "
				UPDATE tst_test_question
				SET obligatory = 0
				WHERE $test_question_id__IN__updateTestQuestionIds
			";

            $ilDB->manipulate($query);
        }
    }

    /**
     * @return array
     */
    protected function getSolutionSubmit()
    {
        $solutionSubmit = array();
        foreach ($_POST as $key => $value) {
            if (preg_match("/^multiple_choice_result_(\d+)/", $key)) {
                if (strlen($value)) {
                    $solutionSubmit[] = $value;
                }
            }
        }
        return $solutionSubmit;
    }

    /**
     * @param $found_values
     * @param $active_id
     * @return int
     */
    protected function calculateReachedPointsForSolution($found_values, $active_id = 0)
    {
        $points = 0;
        foreach ($this->answers as $key => $answer) {
            if (in_array($key, $found_values)) {
                $points += $answer->getPoints();
            } else {
                $points += $answer->getPointsUnchecked();
            }
        }
        if ($active_id) {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $mc_scoring = ilObjTest::_getMCScoring($active_id);
            if (($mc_scoring == 0) && (count($found_values) == 0)) {
                $points = 0;
            }
        }
        return $points;
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
            iQuestionCondition::NumberOfResultExpression,
            iQuestionCondition::ExclusiveResultExpression,
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
                "SELECT value1+1 as value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s",
                array("integer", "integer", "integer","integer"),
                array($active_id, $pass, $this->getId(), $maxStep)
            );
        } else {
            $data = $ilDB->queryF(
                "SELECT value1+1 as value1 FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s",
                array("integer", "integer", "integer"),
                array($active_id, $pass, $this->getId())
            );
        }

        while ($row = $ilDB->fetchAssoc($data)) {
            $result->addKeyValue($row["value1"], $row["value1"]);
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
            return $this->getAnswer($index);
        } else {
            return $this->getAnswers();
        }
    }

    protected function buildTestPresentationConfig()
    {
        $config = parent::buildTestPresentationConfig();
        $config->setUseUnchangedAnswerLabel($this->lng->txt('tst_mc_label_none_above'));
        return $config;
    }
}
