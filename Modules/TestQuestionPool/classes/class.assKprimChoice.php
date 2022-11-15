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

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class assKprimChoice extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, ilAssSpecificFeedbackOptionLabelProvider
{
    public const NUM_REQUIRED_ANSWERS = 4;

    public const PARTIAL_SCORING_NUM_CORRECT_ANSWERS = 3;

    public const ANSWER_TYPE_SINGLE_LINE = 'singleLine';
    public const ANSWER_TYPE_MULTI_LINE = 'multiLine';

    public const OPTION_LABEL_RIGHT_WRONG = 'right_wrong';
    public const OPTION_LABEL_PLUS_MINUS = 'plus_minus';
    public const OPTION_LABEL_APPLICABLE_OR_NOT = 'applicable_or_not';
    public const OPTION_LABEL_ADEQUATE_OR_NOT = 'adequate_or_not';
    public const OPTION_LABEL_CUSTOM = 'customlabel';

    public const DEFAULT_THUMB_SIZE = 150;
    public const THUMB_PREFIX = 'thumb.';

    private $shuffleAnswersEnabled;

    private $answerType;

    private $thumbSize;

    private $scorePartialSolutionEnabled;

    private $optionLabel;

    private $customTrueOptionLabel;

    private $customFalseOptionLabel;

    private $specificFeedbackSetting;

    private $answers;

    public function __construct($title = '', $comment = '', $author = '', $owner = -1, $question = '')
    {
        parent::__construct($title, $comment, $author, $owner, $question);

        $this->shuffleAnswersEnabled = true;
        $this->answerType = self::ANSWER_TYPE_SINGLE_LINE;
        $this->thumbSize = self::DEFAULT_THUMB_SIZE;
        $this->scorePartialSolutionEnabled = true;
        $this->optionLabel = self::OPTION_LABEL_RIGHT_WRONG;
        $this->customTrueOptionLabel = '';
        $this->customFalseOptionLabel = '';

        $this->specificFeedbackSetting = ilAssConfigurableMultiOptionQuestionFeedback::FEEDBACK_SETTING_ALL;

        $this->answers = array();
    }

    public function getQuestionType(): string
    {
        return 'assKprimChoice';
    }

    public function getAdditionalTableName(): string
    {
        return "qpl_qst_kprim";
    }

    public function getAnswerTableName(): string
    {
        return "qpl_a_kprim";
    }

    public function setShuffleAnswersEnabled($shuffleAnswersEnabled): void
    {
        $this->shuffleAnswersEnabled = $shuffleAnswersEnabled;
    }

    public function isShuffleAnswersEnabled(): bool
    {
        return $this->shuffleAnswersEnabled;
    }

    public function setAnswerType($answerType): void
    {
        $this->answerType = $answerType;
    }

    public function getAnswerType(): string
    {
        return $this->answerType;
    }

    public function setThumbSize($thumbSize): void
    {
        $this->thumbSize = $thumbSize;
    }

    public function getThumbSize(): int
    {
        return $this->thumbSize;
    }

    public function setScorePartialSolutionEnabled($scorePartialSolutionEnabled): void
    {
        $this->scorePartialSolutionEnabled = $scorePartialSolutionEnabled;
    }

    public function isScorePartialSolutionEnabled(): bool
    {
        return $this->scorePartialSolutionEnabled;
    }

    public function setOptionLabel($optionLabel): void
    {
        $this->optionLabel = $optionLabel;
    }

    public function getOptionLabel(): string
    {
        return $this->optionLabel;
    }

    public function setCustomTrueOptionLabel($customTrueOptionLabel): void
    {
        $this->customTrueOptionLabel = $customTrueOptionLabel;
    }

    public function getCustomTrueOptionLabel()
    {
        return $this->customTrueOptionLabel;
    }

    public function setCustomFalseOptionLabel($customFalseOptionLabel): void
    {
        $this->customFalseOptionLabel = $customFalseOptionLabel;
    }

    public function getCustomFalseOptionLabel()
    {
        return $this->customFalseOptionLabel;
    }

    public function setSpecificFeedbackSetting($specificFeedbackSetting): void
    {
        $this->specificFeedbackSetting = $specificFeedbackSetting;
    }

    public function getSpecificFeedbackSetting(): int
    {
        return $this->specificFeedbackSetting;
    }

    public function setAnswers($answers): void
    {
        if (is_null($answers)) {
            return;
        }
        $clean_answer_text = function (ilAssKprimChoiceAnswer $answer) {
            $answer->setAnswertext(
                $this->getHtmlQuestionContentPurifier()->purify($answer->getAnswertext())
            );
            return $answer;
        };
        $this->answers = array_map($clean_answer_text, $answers);
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function getAnswer($position)
    {
        foreach ($this->getAnswers() as $answer) {
            if ($answer->getPosition() == $position) {
                return $answer;
            }
        }

        return null;
    }

    public function addAnswer(ilAssKprimChoiceAnswer $answer): void
    {
        $answer->setAnswertext(
            $this->getHtmlQuestionContentPurifier()->purify($answer->getAnswertext())
        );
        $this->answers[] = $answer;
    }

    public function loadFromDb($questionId): void
    {
        $res = $this->db->queryF($this->buildQuestionDataQuery(), array('integer'), array($questionId));

        while ($data = $this->db->fetchAssoc($res)) {
            $this->setId($questionId);

            $this->setOriginalId($data['original_id']);

            $this->setObjId($data['obj_fi']);

            $this->setTitle((string) $data['title']);
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setComment((string) $data['description']);
            $this->setAuthor($data['author']);
            $this->setPoints($data['points']);
            $this->setOwner($data['owner']);
            $this->setEstimatedWorkingTimeFromDurationString($data['working_time']);
            $this->setLastChange($data['tstamp']);
            require_once 'Services/RTE/classes/class.ilRTE.php';
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc((string) $data['question_text'], 1));

            $this->setShuffleAnswersEnabled((bool) $data['shuffle_answers']);

            if ($this->isValidAnswerType($data['answer_type'])) {
                $this->setAnswerType($data['answer_type']);
            }

            if (is_numeric($data['thumb_size'])) {
                $this->setThumbSize((int) $data['thumb_size']);
            }

            if ($this->isValidOptionLabel($data['opt_label'])) {
                $this->setOptionLabel($data['opt_label']);
            }

            $this->setCustomTrueOptionLabel($data['custom_true']);
            $this->setCustomFalseOptionLabel($data['custom_false']);

            if ($data['score_partsol'] !== null) {
                $this->setScorePartialSolutionEnabled((bool) $data['score_partsol']);
            }

            if (isset($data['feedback_setting'])) {
                $this->setSpecificFeedbackSetting((int) $data['feedback_setting']);
            }

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

        $this->loadAnswerData($questionId);

        parent::loadFromDb($questionId);
    }

    private function loadAnswerData($questionId): void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $this->db->queryF(
            "SELECT * FROM {$this->getAnswerTableName()} WHERE question_fi = %s ORDER BY position ASC",
            array('integer'),
            array($questionId)
        );

        require_once 'Modules/TestQuestionPool/classes/class.ilAssKprimChoiceAnswer.php';
        require_once 'Services/RTE/classes/class.ilRTE.php';

        while ($data = $ilDB->fetchAssoc($res)) {
            $answer = new ilAssKprimChoiceAnswer();

            $answer->setPosition($data['position']);

            $answer->setAnswertext(ilRTE::_replaceMediaObjectImageSrc($data['answertext'], 1));

            $answer->setImageFile($data['imagefile']);
            $answer->setThumbPrefix($this->getThumbPrefix());
            $answer->setImageFsDir($this->getImagePath());
            $answer->setImageWebDir($this->getImagePathWeb());

            $answer->setCorrectness($data['correctness']);

            $this->answers[$answer->getPosition()] = $answer;
        }

        for ($i = count($this->answers); $i < self::NUM_REQUIRED_ANSWERS; $i++) {
            $answer = new ilAssKprimChoiceAnswer();

            $answer->setPosition($i);

            $this->answers[$answer->getPosition()] = $answer;
        }
    }

    public function saveToDb($originalId = ''): void
    {
        if ($originalId == '') {
            $this->saveQuestionDataToDb();
        } else {
            $this->saveQuestionDataToDb($originalId);
        }

        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();

        parent::saveToDb($originalId);
    }

    public function saveAdditionalQuestionDataToDb()
    {
        $this->db->replace(
            $this->getAdditionalTableName(),
            array(
                'question_fi' => array('integer', $this->getId())
            ),
            array(
                'shuffle_answers' => array('integer', (int) $this->isShuffleAnswersEnabled()),
                'answer_type' => array('text', $this->getAnswerType()),
                'thumb_size' => array('integer', $this->getThumbSize()),
                'opt_label' => array('text', $this->getOptionLabel()),
                'custom_true' => array('text', $this->getCustomTrueOptionLabel()),
                'custom_false' => array('text', $this->getCustomFalseOptionLabel()),
                'score_partsol' => array('integer', (int) $this->isScorePartialSolutionEnabled()),
                'feedback_setting' => array('integer', $this->getSpecificFeedbackSetting())
            )
        );
    }

    public function saveAnswerSpecificDataToDb()
    {
        foreach ($this->getAnswers() as $answer) {
            $this->db->replace(
                $this->getAnswerTableName(),
                array(
                    'question_fi' => array('integer', $this->getId()),
                    'position' => array('integer', (int) $answer->getPosition())
                ),
                array(
                    'answertext' => array('text', $answer->getAnswertext()),
                    'imagefile' => array('text', $answer->getImageFile()),
                    'correctness' => array('integer', (int) $answer->getCorrectness())
                )
            );
        }

        $this->rebuildThumbnails();
    }

    public function isComplete(): bool
    {
        foreach (array($this->title, $this->author, $this->question) as $text) {
            if (!strlen($text)) {
                return false;
            }
        }

        if (!isset($this->points)) {
            return false;
        }

        foreach ($this->getAnswers() as $answer) {
            /* @var ilAssKprimChoiceAnswer $answer */

            if (is_null($answer->getCorrectness())) {
                return false;
            }

            if (
                (!is_string($answer->getAnswertext()) || $answer->getAnswertext() === '') &&
                (!is_string($answer->getImageFile()) || $answer->getImageFile() === '')
            ) {
                return false;
            }
        }

        return true;
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
        /** @var ilDBInterface $ilDB */
        $ilDB = $GLOBALS['DIC']['ilDB'];

        if (is_null($pass)) {
            include_once "./Modules/Test/classes/class.ilObjTest.php";
            $pass = ilObjTest::_getPass($active_id);
        }

        $entered_values = 0;

        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(function () use (&$entered_values, $active_id, $pass, $authorized) {
            $this->removeCurrentSolution($active_id, $pass, $authorized);

            $solutionSubmit = $this->getSolutionSubmit();

            foreach ($solutionSubmit as $answerIndex => $answerValue) {
                $this->saveCurrentSolution($active_id, $pass, (int) $answerIndex, (int) $answerValue, $authorized);
                $entered_values++;
            }
        });

        if ($entered_values) {
            include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction($this->lng->txtlng(
                    "assessment",
                    "log_user_entered_values",
                    ilObjAssessmentFolder::_getLogLanguage()
                ), $active_id, $this->getId());
            }
        } else {
            include_once("./Modules/Test/classes/class.ilObjAssessmentFolder.php");
            if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
                assQuestion::logAction($this->lng->txtlng(
                    "assessment",
                    "log_user_not_entered_values",
                    ilObjAssessmentFolder::_getLogLanguage()
                ), $active_id, $this->getId());
            }
        }

        return true;
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

        $found_values = array();
        if (is_null($pass)) {
            $pass = $this->getSolutionMaxPass($active_id);
        }

        $result = $this->getCurrentSolutionResultSet($active_id, $pass, $authorizedSolution);

        while ($data = $ilDB->fetchAssoc($result)) {
            $found_values[(int) $data['value1']] = (int) $data['value2'];
        }

        $points = $this->calculateReachedPointsForSolution($found_values, $active_id);

        return $points;
    }

    public function getValidAnswerTypes(): array
    {
        return array(self::ANSWER_TYPE_SINGLE_LINE, self::ANSWER_TYPE_MULTI_LINE);
    }

    public function isValidAnswerType($answerType): bool
    {
        $validTypes = $this->getValidAnswerTypes();
        return in_array($answerType, $validTypes);
    }

    public function isSingleLineAnswerType($answerType): bool
    {
        return $answerType == assKprimChoice::ANSWER_TYPE_SINGLE_LINE;
    }

    /**
     * @param ilLanguage $lng
     * @return array
     */
    public function getAnswerTypeSelectOptions(ilLanguage $lng): array
    {
        return array(
            self::ANSWER_TYPE_SINGLE_LINE => $lng->txt('answers_singleline'),
            self::ANSWER_TYPE_MULTI_LINE => $lng->txt('answers_multiline')
        );
    }

    public function getValidOptionLabels(): array
    {
        return array(
            self::OPTION_LABEL_RIGHT_WRONG,
            self::OPTION_LABEL_PLUS_MINUS,
            self::OPTION_LABEL_APPLICABLE_OR_NOT,
            self::OPTION_LABEL_ADEQUATE_OR_NOT,
            self::OPTION_LABEL_CUSTOM
        );
    }

    public function getValidOptionLabelsTranslated(ilLanguage $lng): array
    {
        return array(
            self::OPTION_LABEL_RIGHT_WRONG => $lng->txt('option_label_right_wrong'),
            self::OPTION_LABEL_PLUS_MINUS => $lng->txt('option_label_plus_minus'),
            self::OPTION_LABEL_APPLICABLE_OR_NOT => $lng->txt('option_label_applicable_or_not'),
            self::OPTION_LABEL_ADEQUATE_OR_NOT => $lng->txt('option_label_adequate_or_not'),
            self::OPTION_LABEL_CUSTOM => $lng->txt('option_label_custom')
        );
    }

    public function isValidOptionLabel($optionLabel): bool
    {
        $validLabels = $this->getValidOptionLabels();
        return in_array($optionLabel, $validLabels);
    }

    public function getTrueOptionLabelTranslation(ilLanguage $lng, $optionLabel)
    {
        switch ($optionLabel) {
            case self::OPTION_LABEL_RIGHT_WRONG:
                return $lng->txt('option_label_right');

            case self::OPTION_LABEL_PLUS_MINUS:
                return $lng->txt('option_label_plus');

            case self::OPTION_LABEL_APPLICABLE_OR_NOT:
                return $lng->txt('option_label_applicable');

            case self::OPTION_LABEL_ADEQUATE_OR_NOT:
                return $lng->txt('option_label_adequate');

            case self::OPTION_LABEL_CUSTOM:
            default:
                return $this->getCustomTrueOptionLabel();
        }
    }

    public function getFalseOptionLabelTranslation(ilLanguage $lng, $optionLabel)
    {
        switch ($optionLabel) {
            case self::OPTION_LABEL_RIGHT_WRONG:
                return $lng->txt('option_label_wrong');

            case self::OPTION_LABEL_PLUS_MINUS:
                return $lng->txt('option_label_minus');

            case self::OPTION_LABEL_APPLICABLE_OR_NOT:
                return $lng->txt('option_label_not_applicable');

            case self::OPTION_LABEL_ADEQUATE_OR_NOT:
                return $lng->txt('option_label_not_adequate');

            case self::OPTION_LABEL_CUSTOM:
            default:
                return $this->getCustomFalseOptionLabel();
        }
    }

    public function getInstructionTextTranslation(ilLanguage $lng, $optionLabel): string
    {
        return sprintf(
            $lng->txt('kprim_instruction_text'),
            $this->getTrueOptionLabelTranslation($lng, $optionLabel),
            $this->getFalseOptionLabelTranslation($lng, $optionLabel)
        );
    }

    public function isCustomOptionLabel($labelValue): bool
    {
        return $labelValue == self::OPTION_LABEL_CUSTOM;
    }

    public function getThumbPrefix(): string
    {
        return self::THUMB_PREFIX;
    }

    public function rebuildThumbnails(): void
    {
        if ($this->isSingleLineAnswerType($this->getAnswerType()) && $this->getThumbSize()) {
            foreach ($this->getAnswers() as $answer) {
                if (strlen($answer->getImageFile())) {
                    $this->generateThumbForFile($answer->getImageFsDir(), $answer->getImageFile());
                }
            }
        }
    }

    protected function generateThumbForFile($path, $file): void
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
            ilShellUtil::convertImage($filename, $thumbpath, $ext, $this->getThumbSize());
        }
    }

    public function handleFileUploads($answers, $files): void
    {
        foreach ($answers as $answer) {
            /* @var ilAssKprimChoiceAnswer $answer */

            if (!isset($files[$answer->getPosition()])) {
                continue;
            }

            $this->handleFileUpload($answer, $files[$answer->getPosition()]);
        }
    }

    private function handleFileUpload(ilAssKprimChoiceAnswer $answer, $fileData): int
    {
        $imagePath = $this->getImagePath();

        if (!file_exists($imagePath)) {
            ilFileUtils::makeDirParents($imagePath);
        }

        $filename = $this->buildHashedImageFilename($fileData['name'], true);

        $answer->setImageFsDir($imagePath);
        $answer->setImageFile($filename);

        if (!ilFileUtils::moveUploadedFile($fileData['tmp_name'], $fileData['name'], $answer->getImageFsPath())) {
            return 2;
        }

        return 0;
    }

    public function removeAnswerImage($position): void
    {
        $answer = $this->getAnswer($position);

        if (file_exists($answer->getImageFsPath())) {
            ilFileUtils::delDir($answer->getImageFsPath());
        }

        if (file_exists($answer->getThumbFsPath())) {
            ilFileUtils::delDir($answer->getThumbFsPath());
        }

        $answer->setImageFile(null);
    }

    protected function getSolutionSubmit(): array
    {
        $solutionSubmit = [];
        $post = $this->dic->http()->wrapper()->post();

        foreach ($this->getAnswers() as $index => $a) {
            if ($post->has("kprim_choice_result_$index")) {
                $value = $post->retrieve(
                    "kprim_choice_result_$index",
                    $this->dic->refinery()->kindlyTo()->string()
                );
                if (is_numeric($value)) {
                    $solutionSubmit[] = $value;
                }
            }
        }
        return $solutionSubmit;
    }

    protected function calculateReachedPointsForSolution($found_values, $active_id = 0)
    {
        $numCorrect = 0;

        foreach ($this->getAnswers() as $key => $answer) {
            if (!isset($found_values[$answer->getPosition()])) {
                continue;
            }

            if ($found_values[$answer->getPosition()] == $answer->getCorrectness()) {
                $numCorrect++;
            }
        }

        if ($numCorrect >= self::NUM_REQUIRED_ANSWERS) {
            $points = $this->getPoints();
        } elseif ($this->isScorePartialSolutionEnabled() && $numCorrect >= self::PARTIAL_SCORING_NUM_CORRECT_ANSWERS) {
            $points = $this->getPoints() / 2;
        } else {
            $points = 0;
        }

        if ($active_id) {
            if (count($found_values) == 0) {
                $points = 0;
            }
        }
        return $points;
    }

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
        // duplicate the images
        $clone->cloneAnswerImages($this_id, $thisObjId, $clone->getId(), $clone->getObjId());

        $clone->onDuplicate($thisObjId, $this_id, $clone->getObjId(), $clone->getId());

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
        // duplicate the image
        $clone->cloneAnswerImages($sourceQuestionId, $sourceParentId, $clone->getId(), $clone->getObjId());

        $clone->onCopy($sourceParentId, $sourceQuestionId, $targetParentId, $clone->getId());

        return $clone->id;
    }

    /**
     * Copies an assMultipleChoice object
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
        // duplicate the image
        $clone->cloneAnswerImages($original_id, $source_questionpool_id, $clone->getId(), $clone->getObjId());

        $clone->onCopy($source_questionpool_id, $original_id, $clone->getObjId(), $clone->getId());

        return $clone->id;
    }

    protected function beforeSyncWithOriginal($origQuestionId, $dupQuestionId, $origParentObjId, $dupParentObjId): void
    {
        parent::beforeSyncWithOriginal($origQuestionId, $dupQuestionId, $origParentObjId, $dupParentObjId);

        $question = self::instantiateQuestion($origQuestionId);

        foreach ($question->getAnswers() as $answer) {
            $question->removeAnswerImage($answer->getPosition());
        }
    }

    protected function afterSyncWithOriginal($origQuestionId, $dupQuestionId, $origParentObjId, $dupParentObjId): void
    {
        parent::afterSyncWithOriginal($origQuestionId, $dupQuestionId, $origParentObjId, $dupParentObjId);

        $this->cloneAnswerImages($dupQuestionId, $dupParentObjId, $origQuestionId, $origParentObjId);
    }

    protected function cloneAnswerImages($sourceQuestionId, $sourceParentId, $targetQuestionId, $targetParentId): void
    {
        /** @var $ilLog ilLogger */
        global $DIC;
        $ilLog = $DIC['ilLog'];

        $sourcePath = $this->buildImagePath($sourceQuestionId, $sourceParentId);
        $targetPath = $this->buildImagePath($targetQuestionId, $targetParentId);

        foreach ($this->getAnswers() as $answer) {
            $filename = $answer->getImageFile();

            if (strlen($filename)) {
                if (!file_exists($targetPath)) {
                    ilFileUtils::makeDirParents($targetPath);
                }

                if (file_exists($sourcePath . $filename)) {
                    if (!copy($sourcePath . $filename, $targetPath . $filename)) {
                        $ilLog->warning(sprintf(
                            "Could not clone source image '%s' to '%s' (srcQuestionId: %s|tgtQuestionId: %s|srcParentObjId: %s|tgtParentObjId: %s)",
                            $sourcePath . $filename,
                            $targetPath . $filename,
                            $sourceQuestionId,
                            $targetQuestionId,
                            $sourceParentId,
                            $targetParentId
                        ));
                    }
                }

                if (file_exists($sourcePath . $this->getThumbPrefix() . $filename)) {
                    if (!copy($sourcePath . $this->getThumbPrefix() . $filename, $targetPath . $this->getThumbPrefix() . $filename)) {
                        $ilLog->warning(sprintf(
                            "Could not clone thumbnail source image '%s' to '%s' (srcQuestionId: %s|tgtQuestionId: %s|srcParentObjId: %s|tgtParentObjId: %s)",
                            $sourcePath . $this->getThumbPrefix() . $filename,
                            $targetPath . $this->getThumbPrefix() . $filename,
                            $sourceQuestionId,
                            $targetQuestionId,
                            $sourceParentId,
                            $targetParentId
                        ));
                    }
                }
            }
        }
    }

    protected function getRTETextWithMediaObjects(): string
    {
        $combinedText = parent::getRTETextWithMediaObjects();

        foreach ($this->getAnswers() as $answer) {
            $combinedText .= $answer->getAnswertext();
        }

        return $combinedText;
    }

    /**
     * @param ilAssSelfAssessmentMigrator $migrator
     */
    protected function lmMigrateQuestionTypeSpecificContent(ilAssSelfAssessmentMigrator $migrator): void
    {
        foreach ($this->getAnswers() as $answer) {
            /* @var ilAssKprimChoiceAnswer $answer */
            $answer->setAnswertext($migrator->migrateToLmContent($answer->getAnswertext()));
        }
    }

    /**
     * Returns a JSON representation of the question
     */
    public function toJSON(): string
    {
        $this->lng->loadLanguageModule('assessment');

        require_once './Services/RTE/classes/class.ilRTE.php';
        $result = array();
        $result['id'] = $this->getId();
        $result['type'] = $this->getQuestionType();
        $result['title'] = $this->getTitle();
        $result['question'] = $this->formatSAQuestion($this->getQuestion());
        $result['instruction'] = $this->getInstructionTextTranslation(
            $this->lng,
            $this->getOptionLabel()
        );
        $result['nr_of_tries'] = $this->getNrOfTries();
        $result['shuffle'] = $this->isShuffleAnswersEnabled();
        $result['feedback'] = array(
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        );

        $result['trueOptionLabel'] = $this->getTrueOptionLabelTranslation($this->lng, $this->getOptionLabel());
        $result['falseOptionLabel'] = $this->getFalseOptionLabelTranslation($this->lng, $this->getOptionLabel());

        $result['num_allowed_failures'] = $this->getNumAllowedFailures();

        $answers = array();
        $has_image = false;

        foreach ($this->getAnswers() as $key => $answer) {
            if (strlen((string) $answer->getImageFile())) {
                $has_image = true;
            }

            $answers[] = array(
                'answertext' => $this->formatSAQuestion($answer->getAnswertext()),
                'correctness' => (bool) $answer->getCorrectness(),
                'order' => (int) $answer->getPosition(),
                'image' => (string) $answer->getImageFile(),
                'feedback' => $this->formatSAQuestion(
                    $this->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation($this->getId(), 0, $key)
                )
            );
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

    private function getNumAllowedFailures(): int
    {
        if ($this->isScorePartialSolutionEnabled()) {
            return self::NUM_REQUIRED_ANSWERS - self::PARTIAL_SCORING_NUM_CORRECT_ANSWERS;
        }

        return 0;
    }

    public function getSpecificFeedbackAllCorrectOptionLabel(): string
    {
        return 'feedback_correct_kprim';
    }

    public static function isObligationPossible(int $questionId): bool
    {
        return true;
    }

    public function isAnswered(int $active_id, int $pass): bool
    {
        $numExistingSolutionRecords = assQuestion::getNumExistingSolutionRecords($active_id, $pass, $this->getId());

        return $numExistingSolutionRecords >= 4;
    }

    /**
     * {@inheritdoc}
     */
    public function setExportDetailsXLS(ilAssExcelFormatHelper $worksheet, int $startrow, int $active_id, int $pass): int
    {
        parent::setExportDetailsXLS($worksheet, $startrow, $active_id, $pass);

        $solution = $this->getSolutionValues($active_id, $pass);

        $i = 1;
        foreach ($this->getAnswers() as $id => $answer) {
            $worksheet->setCell($startrow + $i, 0, $answer->getAnswertext());
            $worksheet->setBold($worksheet->getColumnCoord(0) . ($startrow + $i));
            $correctness = false;
            foreach ($solution as $solutionvalue) {
                if ($id == $solutionvalue['value1']) {
                    $correctness = $solutionvalue['value2'];
                    break;
                }
            }
            $worksheet->setCell($startrow + $i, 1, $correctness);
            $i++;
        }

        return $startrow + $i + 1;
    }

    public function moveAnswerDown($position): bool
    {
        if ($position < 0 || $position >= (self::NUM_REQUIRED_ANSWERS - 1)) {
            return false;
        }

        for ($i = 0, $max = count($this->answers); $i < $max; $i++) {
            if ($i == $position) {
                $movingAnswer = $this->answers[$i];
                $targetAnswer = $this->answers[ $i + 1 ];

                $movingAnswer->setPosition($position + 1);
                $targetAnswer->setPosition($position);

                $this->answers[ $i + 1 ] = $movingAnswer;
                $this->answers[$i] = $targetAnswer;
            }
        }
        return true;
    }

    public function moveAnswerUp($position): bool
    {
        if ($position <= 0 || $position > (self::NUM_REQUIRED_ANSWERS - 1)) {
            return false;
        }

        for ($i = 0, $max = count($this->answers); $i < $max; $i++) {
            if ($i == $position) {
                $movingAnswer = $this->answers[$i];
                $targetAnswer = $this->answers[ $i - 1 ];

                $movingAnswer->setPosition($position - 1);
                $targetAnswer->setPosition($position);

                $this->answers[ $i - 1 ] = $movingAnswer;
                $this->answers[$i] = $targetAnswer;
            }
        }

        return true;
    }
}
