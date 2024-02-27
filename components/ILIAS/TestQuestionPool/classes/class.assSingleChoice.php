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
use ILIAS\TestQuestionPool\ManipulateImagesInChoiceQuestionsTrait;

/**
 * Class for single choice questions
 *
 * assSingleChoice is a class for single choice questions.
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup		ModulesTestQuestionPool
 */
class assSingleChoice extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, iQuestionCondition, ilAssSpecificFeedbackOptionLabelProvider, QuestionLMExportable, QuestionAutosaveable
{
    use ManipulateImagesInChoiceQuestionsTrait;

    protected const OUTPUT_ORDER = 0;
    protected const OUTPUT_RANDOM = 1;

    protected const FEEDBACK_MODE_ALL_ANSWERS = 1;
    protected const FEEDBACK_MODE_SELECTED_ANSWERS = 2;
    protected const FEEDBACK_MODE_CORRECT_ANSWERS = 3;

    private bool $isSingleline = true;

    /**
    * @var array<ASS_AnswerBinaryStateImage>
    */
    public array $answers = [];
    public int $output_type;
    protected int $feedback_setting = self::FEEDBACK_MODE_SELECTED_ANSWERS;

    public function __construct(
        string $title = "",
        string $comment = "",
        string $author = "",
        int $owner = -1,
        string $question = "",
        int $output_type = self::OUTPUT_ORDER
    ) {
        parent::__construct($title, $comment, $author, $owner, $question);
        $this->output_type = $output_type;
    }

    public function isComplete(): bool
    {
        if (
            $this->title !== ''
            && $this->author !== null && $this->author !== ''
            && $this->question !== null && $this->question !== ''
            && $this->answers !== []
            && $this->getMaximumPoints() > 0
        ) {
            foreach ($this->answers as $answer) {
                if ($answer->getAnswertext() === '' && $answer->getImage() === '') {
                    return false;
                }
            }
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
            ["integer"],
            [$question_id]
        );
        if ($result->numRows() == 1) {
            $data = $this->db->fetchAssoc($result);
            $this->setId($question_id);
            $this->setObjId($data["obj_fi"]);
            $this->setTitle((string) $data["title"]);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setComment($data["description"] ?? '');
            $this->setOriginalId($data["original_id"]);
            $this->setAuthor($data["author"]);
            $this->setPoints($data["points"]);
            $this->setOwner($data["owner"]);
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data["question_text"] ?? '', 1));
            $shuffle = (is_null($data['shuffle'])) ? true : $data['shuffle'];
            $this->setShuffle((bool) $shuffle);
            if ($data['thumb_size'] !== null && $data['thumb_size'] >= self::MINIMUM_THUMB_SIZE) {
                $this->setThumbSize($data['thumb_size']);
            }
            $this->isSingleline = $data['allow_images'] === false;
            $this->lastChange = $data['tstamp'];
            $this->feedback_setting = $data['feedback_setting'] ?? self::FEEDBACK_MODE_SELECTED_ANSWERS;

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
            "SELECT * FROM qpl_a_sc WHERE question_fi = %s ORDER BY aorder ASC",
            ['integer'],
            [$question_id]
        );

        if ($result->numRows() > 0) {
            while ($data = $this->db->fetchAssoc($result)) {
                $imagefilename = $this->getImagePath() . $data["imagefile"];
                if (!file_exists($imagefilename)) {
                    $data["imagefile"] = null;
                }

                $data["answertext"] = ilRTE::_replaceMediaObjectImageSrc($data["answertext"] ?? '', 1);
                $image = new ASS_AnswerBinaryStateImage(
                    $data["answertext"],
                    $data["points"],
                    $data["aorder"],
                    true,
                    $data["imagefile"] ? $data["imagefile"] : null,
                    $data["answer_id"]
                );
                $this->answers[] = $image;
            }
        }

        parent::loadFromDb($question_id);
    }

    protected function cloneQuestionTypeSpecificProperties(
        \assQuestion $target
    ): \assQuestion {
        $this->cloneImages(
            $this->getId(),
            $this->getObjId(),
            $target->getId(),
            $target->getObjId(),
            $this->getAnswers()
        );
        return $target;
    }

    public function addAnswer(
        string $answertext = '',
        float $points = 0.0,
        int $order = 0,
        string $answerimage = '',
        int $answer_id = -1
    ): void {
        $answertext = $this->getHtmlQuestionContentPurifier()->purify($answertext);
        if (array_key_exists($order, $this->answers)) {
            // insert answer
            $answer = new ASS_AnswerBinaryStateImage($answertext, $points, $order, true, $answerimage, $answer_id);
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
            $answer = new ASS_AnswerBinaryStateImage(
                $answertext,
                $points,
                count($this->answers),
                true,
                $answerimage,
                $answer_id
            );
            $this->answers[] = $answer;
        }
    }

    /**
    * Returns the number of answers
    *
    * @return integer The number of answers of the multiple choice question
    * @access public
    * @see $answers
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
    * @return object ASS_AnswerBinaryStateImage-Object containing the answer
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
        $answer = $this->answers[$index];
        if ($answer->hasImage()) {
            $this->deleteImage($answer->getImage());
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
        $points = 0;
        foreach ($this->answers as $key => $value) {
            if ($value->getPoints() > $points) {
                $points = $value->getPoints();
            }
        }
        return $points;
    }

    public function calculateReachedPoints(
        int $active_id,
        ?int $pass = null,
        bool $authorized_solution = true
    ): float {
        $found_values = [];
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }
        $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorized_solution);
        while ($data = $this->db->fetchAssoc($result)) {
            if (strcmp($data["value1"], "") != 0) {
                array_push($found_values, $data["value1"]);
            }
        }
        $points = 0.0;
        foreach ($this->answers as $key => $answer) {
            if ($found_values !== []
                && in_array($key, $found_values)) {
                $points += $answer->getPoints();
            }
        }

        return $points;
    }

    public function calculateReachedPointsFromPreviewSession(ilAssQuestionPreviewSession $preview_session): float
    {
        $participant_solution = $preview_session->getParticipantsSolution();

        $points = 0.0;
        foreach ($this->answers as $key => $answer) {
            if (is_numeric($participant_solution)
                && $key === $participant_solution) {
                $points = $answer->getPoints();
            }
        }
        $reached_points = $this->deductHintPointsFromReachedPoints($preview_session, $points);
        return $this->ensureNonNegativePoints($reached_points);
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
                $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorized);

                $update = -1;
                if ($this->db->numRows($result)) {
                    $row = $this->db->fetchAssoc($result);
                    $update = $row["solution_id"];
                }

                $multiple_choice_result = $this->http->wrapper()->post()->has('multiple_choice_result') ?
                    $this->http->wrapper()->post()->retrieve('multiple_choice_result', $this->refinery->kindlyTo()->string()) :
                    '';

                if ($update !== -1
                    && $multiple_choice_result === '') {
                    $this->removeSolutionRecordById($update);
                    return;
                }

                if ($update !== -1) {
                    $this->updateCurrentSolution($update, $multiple_choice_result, null, $authorized);
                    return;
                }

                if ($multiple_choice_result !== '') {
                    $this->saveCurrentSolution($active_id, $pass, $multiple_choice_result, null, $authorized);
                }
            }
        );

        return true;
    }

    protected function savePreviewData(ilAssQuestionPreviewSession $previewSession): void
    {
        $mc_result_key = 'multiple_choice_result' . $this->getId() . 'ID';
        if (
            $this->http->wrapper()->post()->has($mc_result_key) &&
            ($mc_result = $this->http->wrapper()->post()->retrieve($mc_result_key, $this->refinery->kindlyTo()->string())) !== ''
        ) {
            $previewSession->setParticipantsSolution($mc_result);
        } else {
            $previewSession->setParticipantsSolution(null);
        }
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
            ) . " (question_fi, shuffle, allow_images, thumb_size) VALUES (%s, %s, %s, %s)",
            [ "integer", "text", "text", "integer" ],
            [
                                $this->getId(),
                                $this->getShuffle(),
                                ($this->isSingleline) ? "0" : "1",
                                $this->getThumbSize()
                            ]
        );
    }

    /**
     * Deletes all existing Answer data from a question and reintroduces old data and changes.
     * Additionally, it updates the corresponding feedback.
     * @return void
     */
    public function saveAnswerSpecificDataToDb()
    {
        if (!$this->isSingleline) {
            ilFileUtils::delDir($this->getImagePath());
        }
        // Get all feedback entries
        $result = $this->db->queryF(
            "SELECT * FROM qpl_fb_specific WHERE question_fi = %s",
            ['integer'],
            [$this->getId()]
        );
        $db_feedback = $this->db->fetchAll($result);

        // Check if feedback exists and the regular editor is used and not the page editor
        if (sizeof($db_feedback) >= 1 && $this->getAdditionalContentEditingMode() == 'default') {
            // Get all existing answer data for question
            $result = $this->db->queryF(
                "SELECT answer_id, aorder  FROM qpl_a_sc WHERE question_fi = %s",
                ['integer'],
                [$this->getId()]
            );
            $db_answers = $this->db->fetchAll($result);

            // Collect old and new order entries by ids and order to calculate a diff/intersection and remove/update feedback
            $post_answer_order_for_id = [];
            foreach ($this->answers as $answer) {
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
                foreach ($db_answers as $db_answer) {
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
                    $next_id = $this->db->nextId('qpl_fb_specific');
                    $this->db->manipulateF(
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

        // Delete all entries in qpl_a_sc for question
        $this->db->manipulateF(
            "DELETE FROM qpl_a_sc WHERE question_fi = %s",
            ['integer'],
            [$this->getId()]
        );

        // Recreate answers one by one
        foreach ($this->answers as $key => $value) {
            /** @var ASS_AnswerMultipleResponseImage $answer_obj */
            $answer_obj = $this->answers[$key];
            $next_id = $this->db->nextId('qpl_a_sc');
            $this->db->manipulateF(
                "INSERT INTO qpl_a_sc (answer_id, question_fi, answertext, points, aorder, imagefile, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                ['integer', 'integer', 'text', 'float', 'integer', 'text', 'integer'],
                [
                    $next_id,
                    $this->getId(),
                    ilRTE::_replaceMediaObjectImageSrc($answer_obj->getAnswertext(), 0),
                    $answer_obj->getPoints(),
                    $answer_obj->getOrder(),
                    $answer_obj->getImage(),
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
        return "assSingleChoice";
    }

    /**
    * Returns the name of the additional question data table in the database
    *
    * @return string The additional table name
    * @access public
    */
    public function getAdditionalTableName(): string
    {
        return "qpl_qst_sc";
    }

    /**
    * Returns the name of the answer table in the database
    *
    * @return string The answer table name
    * @access public
    */
    public function getAnswerTableName(): string
    {
        return "qpl_a_sc";
    }

    /**
    * Sets the image file and uploads the image to the object's image directory.
    *
    * @param string $image_filename Name of the original image file
    * @param string $image_tempfilename Name of the temporary uploaded image file
    * @return integer An errorcode if the image upload fails, 0 otherwise
    * @access public
    */
    public function setImageFile($image_filename, $image_tempfilename = ""): int
    {
        if (empty($image_tempfilename)) {
            return 0;
        }

        $cleaned_image_filename = str_replace(" ", "_", $image_filename);
        $imagepath = $this->getImagePath();
        if (!file_exists($imagepath)) {
            ilFileUtils::makeDirParents($imagepath);
        }

        if (!ilFileUtils::moveUploadedFile($image_tempfilename, $cleaned_image_filename, $imagepath . $cleaned_image_filename)) {
            return 2;
        }

        $mimetype = ilObjMediaObject::getMimeType($imagepath . $cleaned_image_filename);
        if (!preg_match("/^image/", $mimetype)) {
            unlink($imagepath . $cleaned_image_filename);
            return 1;
        }

        if ($this->isSingleline && $this->getThumbSize()) {
            $this->generateThumbForFile(
                $cleaned_image_filename,
                $this->getImagePath(),
                $this->getThumbSize()
            );
        }

        return 0;
    }

    /*
     * Collects all text in the question which could contain media objects
     * which were created with the Rich Text Editor
     */
    public function getRTETextWithMediaObjects(): string
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
    public function &getAnswers(): array
    {
        return $this->answers;
    }

    public function setAnswers(array $answers): void
    {
        $this->answers = $answers;
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLSX(ilAssExcelFormatHelper $worksheet, int $startrow, int $col, int $active_id, int $pass): int
    {
        parent::setExportDetailsXLSX($worksheet, $startrow, $col, $active_id, $pass);

        $solution = $this->getSolutionValues($active_id, $pass);
        $i = 1;
        foreach ($this->getAnswers() as $id => $answer) {
            $worksheet->setCell($startrow + $i, $col, $answer->getAnswertext());
            $worksheet->setBold($worksheet->getColumnCoord($col) . ($startrow + $i));
            if (
                count($solution) > 0 &&
                isset($solution[0]) &&
                is_array($solution[0]) &&
                strlen($solution[0]['value1']) > 0 && $id == $solution[0]['value1']
            ) {
                $worksheet->setCell($startrow + $i, $col + 2, 1);
            } else {
                $worksheet->setCell($startrow + $i, $col + 2, 0);
            }
            $i++;
        }

        return $startrow + $i + 1;
    }

    /**
     * @param ilAssSelfAssessmentMigrator $migrator
     */
    protected function lmMigrateQuestionTypeSpecificContent(ilAssSelfAssessmentMigrator $migrator): void
    {
        foreach ($this->getAnswers() as $answer) {
            /* @var ASS_AnswerBinaryStateImage $answer */
            $answer->setAnswertext($migrator->migrateToLmContent($answer->getAnswertext()));
        }
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
        $result['shuffle'] = $this->getShuffle();

        $result['feedback'] = [
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        ];

        $answers = [];
        $has_image = false;
        foreach ($this->getAnswers() as $key => $answer_obj) {
            if ((string) $answer_obj->getImage()) {
                $has_image = true;
            }
            array_push($answers, [
                "answertext" => $this->formatSAQuestion($answer_obj->getAnswertext()),
                'html_id' => $this->getId() . '_' . $key,
                "points" => (float) $answer_obj->getPoints(),
                "order" => (int) $answer_obj->getOrder(),
                "image" => (string) $answer_obj->getImage(),
                "feedback" => $this->formatSAQuestion(
                    $this->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation($this->getId(), 0, $key)
                )
            ]);
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

    public function removeAnswerImage($index): void
    {
        $answer = $this->answers[$index];
        if (is_object($answer)) {
            $this->deleteImage($answer->getImage());
            $answer->setImage(null);
        }
    }

    public function getMultilineAnswerSetting()
    {
        $multilineAnswerSetting = $this->current_user->getPref("tst_multiline_answers");
        if ($multilineAnswerSetting != 1) {
            $multilineAnswerSetting = 0;
        }
        return $multilineAnswerSetting;
    }

    public function setMultilineAnswerSetting($a_setting = 0): void
    {
        $this->current_user->writePref("tst_multiline_answers", (string) $a_setting);
    }

    /**
     * Sets the feedback settings in effect for the question.
     * Options are:
     * 1 - Feedback is shown for all answer options.
     * 2 - Feedback is shown for all checked/selected options.
     * 3 - Feedback is shown for all correct options.
     */
    public function setSpecificFeedbackSetting($feedback_setting): void
    {
        $this->feedback_setting = $feedback_setting;
    }

    /**
     * Gets the current feedback settings in effect for the question.
     * Values are:
     * 1 - Feedback is shown for all answer options.
     * 2 - Feedback is shown for all checked/selected options.
     * 3 - Feedback is shown for all correct options.
     */
    public function getSpecificFeedbackSetting(): int
    {
        if ($this->feedback_setting) {
            return $this->feedback_setting;
        } else {
            return 1;
        }
    }

    public function getSpecificFeedbackAllCorrectOptionLabel(): string
    {
        return 'feedback_correct_sc_mc';
    }

    public static function isObligationPossible(int $question_id): bool
    {
        return true;
    }

    public function getOperators(string $expression): array
    {
        return ilOperatorsExpressionMapping::getOperatorsByExpression($expression);
    }

    public function getExpressionTypes(): array
    {
        return [
            iQuestionCondition::PercentageResultExpression,
            iQuestionCondition::NumberOfResultExpression,
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
                "SELECT * FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s AND step = %s",
                ["integer", "integer", "integer","integer"],
                [$active_id, $pass, $this->getId(), $maxStep]
            );
        } else {
            $data = $this->db->queryF(
                "SELECT * FROM tst_solutions WHERE active_fi = %s AND pass = %s AND question_fi = %s",
                ["integer", "integer", "integer"],
                [$active_id, $pass, $this->getId()]
            );
        }

        $row = $this->db->fetchAssoc($data);

        if ($row != null) {
            ++$row["value1"];
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
     */
    public function getAvailableAnswerOptions($index = null)
    {
        if ($index !== null) {
            return $this->getAnswer($index);
        } else {
            return $this->getAnswers();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function afterSyncWithOriginal(
        int $original_question_id,
        int $clone_question_id,
        int $original_parent_id,
        int $clone_parent_id
    ): void {
        parent::afterSyncWithOriginal($original_question_id, $clone_question_id, $original_parent_id, $clone_parent_id);

        $original_image_path = $this->questionFilesService->buildImagePath($original_question_id, $original_parent_id);
        $clone_image_path = $this->questionFilesService->buildImagePath($clone_question_id, $clone_parent_id);

        ilFileUtils::delDir($original_image_path);
        if (is_dir($clone_image_path)) {
            ilFileUtils::makeDirParents($original_image_path);
            ilFileUtils::rCopy($clone_image_path, $original_image_path);
        }
    }

    public function isSingleline(): bool
    {
        return $this->isSingleline;
    }

    public function setIsSingleline(bool $isSingleline): void
    {
        $this->isSingleline = $isSingleline;
    }

    public function getFeedbackSetting(): int
    {
        return $this->feedback_setting;
    }

    public function setFeedbackSetting(int $feedback_setting): void
    {
        $this->feedback_setting = $feedback_setting;
    }

    public function toLog(): array
    {
        $result = [
            'question_id' => $this->getId(),
            'question_type' => (string) $this->getQuestionType(),
            'question_title' => $this->getTitle(),
            'tst_question' => $this->formatSAQuestion($this->getQuestion()),
            'shuffle_answers' => $this->getShuffle() ? '{{ enabled }}' : '{{ disabled }}',
            'tst_feedback' => [
                'feedback_incomplete_solution' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
                'feedback_complete_solution' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
            ]
        ];

        foreach ($this->getAnswers() as $key => $answer_obj) {
            $result['answers'][] = [
                "answertext" => $this->formatSAQuestion($answer_obj->getAnswertext()),
                "points" => (float) $answer_obj->getPoints(),
                "order" => (int) $answer_obj->getOrder(),
                "image" => (string) $answer_obj->getImage(),
                "feedback" => $this->formatSAQuestion(
                    $this->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation($this->getId(), 0, $key)
                )
            ];
        }

        return $result;
    }
}
