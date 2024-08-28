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

use ILIAS\Test\Logging\AdditionalInformationGenerator;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/TestQuestionPool
 */
class assKprimChoice extends assQuestion implements ilObjQuestionScoringAdjustable, ilObjAnswerScoringAdjustable, ilAssSpecificFeedbackOptionLabelProvider, QuestionLMExportable, QuestionAutosaveable
{
    use ManipulateImagesInChoiceQuestionsTrait;

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

    private bool $shuffle_answers_enabled = true;
    private string $answerType = self::ANSWER_TYPE_SINGLE_LINE;
    private int $thumbSize = self::DEFAULT_THUMB_SIZE;
    private bool $scorePartialSolutionEnabled = true;
    private string $option_label = self::OPTION_LABEL_RIGHT_WRONG;
    private string $customTrueOptionLabel = '';
    private string $customFalseOptionLabel = '';
    private int $specific_feedback_setting = ilAssConfigurableMultiOptionQuestionFeedback::FEEDBACK_SETTING_ALL;

    private $answers = [];

    public function __construct($title = '', $comment = '', $author = '', $owner = -1, $question = '')
    {
        parent::__construct($title, $comment, $author, $owner, $question);

        for ($i = count($this->answers); $i < self::NUM_REQUIRED_ANSWERS; $i++) {
            $answer = new ilAssKprimChoiceAnswer();
            $answer->setPosition($i);
            $this->answers[$answer->getPosition()] = $answer;
        }
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

    public function setShuffleAnswersEnabled(bool $shuffle_answers_enabled): void
    {
        $this->shuffle_answers_enabled = $shuffle_answers_enabled;
    }

    public function isShuffleAnswersEnabled(): bool
    {
        return $this->shuffle_answers_enabled;
    }

    public function setAnswerType($answerType): void
    {
        $this->answerType = $answerType;
    }

    public function getAnswerType(): string
    {
        return $this->answerType;
    }

    public function setThumbSize(int $thumbSize): void
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

    public function setOptionLabel(string $option_label): void
    {
        $this->option_label = $option_label;
    }

    public function getOptionLabel(): string
    {
        return $this->option_label;
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

    public function setSpecificFeedbackSetting(int $specific_feedback_setting): void
    {
        $this->specific_feedback_setting = $specific_feedback_setting;
    }

    public function getSpecificFeedbackSetting(): int
    {
        return $this->specific_feedback_setting;
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

    /**
     * @return array<ilAssKprimChoiceAnswer>
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function getAnswer($position): ?ilAssKprimChoiceAnswer
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
        $res = $this->db->queryF($this->buildQuestionDataQuery(), ['integer'], [$questionId]);

        while ($data = $this->db->fetchAssoc($res)) {
            $this->setId($questionId);

            $this->setOriginalId($data['original_id']);

            $this->setObjId($data['obj_fi']);

            $this->setTitle($data['title'] ?? '');
            $this->setNrOfTries($data['nr_of_tries']);
            $this->setComment($data['description'] ?? '');
            $this->setAuthor($data['author']);
            $this->setPoints($data['points']);
            $this->setOwner($data['owner']);
            $this->setLastChange($data['tstamp']);
            $this->setQuestion(ilRTE::_replaceMediaObjectImageSrc($data['question_text'] ?? '', 1));

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

    private function loadAnswerData(int $question_id): void
    {
        $res = $this->db->queryF(
            "SELECT * FROM {$this->getAnswerTableName()} WHERE question_fi = %s ORDER BY position ASC",
            ['integer'],
            [$question_id]
        );

        while ($data = $this->db->fetchAssoc($res)) {
            $answer = new ilAssKprimChoiceAnswer();

            $answer->setPosition($data['position']);

            $answer->setAnswertext(ilRTE::_replaceMediaObjectImageSrc($data['answertext'] ?? '', 1));

            $answer->setImageFile($data['imagefile']);
            $answer->setThumbPrefix($this->getThumbPrefix());
            $answer->setImageFsDir($this->getImagePath());
            $answer->setImageWebDir($this->getImagePathWeb());

            $answer->setCorrectness($data['correctness']);

            $this->answers[$answer->getPosition()] = $answer;
        }
    }

    public function saveToDb(?int $original_id = null): void
    {
        $this->saveQuestionDataToDb($original_id);
        $this->saveAdditionalQuestionDataToDb();
        $this->saveAnswerSpecificDataToDb();

        parent::saveToDb();
    }

    public function saveAdditionalQuestionDataToDb()
    {
        $this->db->replace(
            $this->getAdditionalTableName(),
            [
                'question_fi' => ['integer', $this->getId()]
            ],
            [
                'shuffle_answers' => ['integer', (int) $this->isShuffleAnswersEnabled()],
                'answer_type' => ['text', $this->getAnswerType()],
                'thumb_size' => ['integer', $this->getThumbSize()],
                'opt_label' => ['text', $this->getOptionLabel()],
                'custom_true' => ['text', $this->getCustomTrueOptionLabel()],
                'custom_false' => ['text', $this->getCustomFalseOptionLabel()],
                'score_partsol' => ['integer', (int) $this->isScorePartialSolutionEnabled()],
                'feedback_setting' => ['integer', $this->getSpecificFeedbackSetting()]
            ]
        );
    }

    public function saveAnswerSpecificDataToDb()
    {
        foreach ($this->getAnswers() as $answer) {
            $this->db->replace(
                $this->getAnswerTableName(),
                [
                    'question_fi' => ['integer', $this->getId()],
                    'position' => ['integer', (int) $answer->getPosition()]
                ],
                [
                    'answertext' => ['text', $answer->getAnswertext()],
                    'imagefile' => ['text', $answer->getImageFile()],
                    'correctness' => ['integer', (int) $answer->getCorrectness()]
                ]
            );
        }
    }

    public function isComplete(): bool
    {
        foreach ([$this->title, $this->author, $this->question] as $text) {
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

    public function saveWorkingData(
        int $active_id,
        ?int $pass = null,
        bool $authorized = true
    ): bool {
        if ($pass === null) {
            $pass = ilObjTest::_getPass($active_id);
        }

        $answer = $this->getSolutionSubmit();
        $this->getProcessLocker()->executeUserSolutionUpdateLockOperation(
            function () use ($answer, $active_id, $pass, $authorized) {
                $this->removeCurrentSolution($active_id, $pass, $authorized);
                foreach ($answer as $index => $value) {
                    if ($value !== null) {
                        $this->saveCurrentSolution($active_id, $pass, (int) $index, (int) $value, $authorized);
                    }
                }
            }
        );

        return true;
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
            $found_values[(int) $data['value1']] = (int) $data['value2'];
        }

        $points = $this->calculateReachedPointsForSolution($found_values, $active_id);

        return $points;
    }

    public function getValidAnswerTypes(): array
    {
        return [self::ANSWER_TYPE_SINGLE_LINE, self::ANSWER_TYPE_MULTI_LINE];
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
        return [
            self::ANSWER_TYPE_SINGLE_LINE => $lng->txt('answers_singleline'),
            self::ANSWER_TYPE_MULTI_LINE => $lng->txt('answers_multiline')
        ];
    }

    public function getValidOptionLabels(): array
    {
        return [
            self::OPTION_LABEL_RIGHT_WRONG,
            self::OPTION_LABEL_PLUS_MINUS,
            self::OPTION_LABEL_APPLICABLE_OR_NOT,
            self::OPTION_LABEL_ADEQUATE_OR_NOT,
            self::OPTION_LABEL_CUSTOM
        ];
    }

    public function getValidOptionLabelsTranslated(ilLanguage $lng): array
    {
        return array_reduce(
            $this->getValidOptionLabels(),
            function (array $c, string $option_label) use ($lng): array {
                $c[$option_label] = $lng->txt($this->getLangVarForOptionLabel($option_label));
                return $c;
            },
            []
        );
    }

    public function getLangVarForOptionLabel(string $option_label): string
    {
        return match ($option_label) {
            self::OPTION_LABEL_RIGHT_WRONG => 'option_label_right_wrong',
            self::OPTION_LABEL_PLUS_MINUS => 'option_label_plus_minus',
            self::OPTION_LABEL_APPLICABLE_OR_NOT => 'option_label_applicable_or_not',
            self::OPTION_LABEL_ADEQUATE_OR_NOT => 'option_label_adequate_or_not',
            self::OPTION_LABEL_CUSTOM => 'option_label_custom'
        };
    }

    public function isValidOptionLabel(string $option_label): bool
    {
        $valid_labels = $this->getValidOptionLabels();
        return in_array($option_label, $valid_labels);
    }

    public function getTrueOptionLabelTranslation(ilLanguage $lng, string $option_label): string
    {
        if ($option_label === self::OPTION_LABEL_CUSTOM) {
            return $this->getCustomTrueOptionLabel();
        }

        return $lng->txt(
            $this->getTrueOptionLabel($option_label)
        );
    }

    public function getTrueOptionLabel(string $option_label): string
    {
        switch ($option_label) {
            case self::OPTION_LABEL_RIGHT_WRONG:
                return 'option_label_right';

            case self::OPTION_LABEL_PLUS_MINUS:
                return 'option_label_plus';

            case self::OPTION_LABEL_APPLICABLE_OR_NOT:
                return 'option_label_applicable';

            case self::OPTION_LABEL_ADEQUATE_OR_NOT:
                return 'option_label_adequate';

            default:
                throw new \ErrorException('Invalide  Option Label');
        }
    }

    public function getFalseOptionLabelTranslation(ilLanguage $lng, string $option_label): string
    {
        if ($option_label === self::OPTION_LABEL_CUSTOM) {
            return $this->getCustomFalseOptionLabel();
        }

        return $lng->txt(
            $this->getFalseOptionLabel($option_label)
        );
    }

    private function getFalseOptionLabel(string $option_label): string
    {
        switch ($option_label) {
            case self::OPTION_LABEL_RIGHT_WRONG:
                return 'option_label_wrong';

            case self::OPTION_LABEL_PLUS_MINUS:
                return 'option_label_minus';

            case self::OPTION_LABEL_APPLICABLE_OR_NOT:
                return 'option_label_not_applicable';

            case self::OPTION_LABEL_ADEQUATE_OR_NOT:
                return 'option_label_not_adequate';

            default:
                throw new \ErrorException('Invalide  Option Label');
        }
    }

    public function getInstructionTextTranslation(ilLanguage $lng, $option_label): string
    {
        return sprintf(
            $lng->txt('kprim_instruction_text'),
            $this->getTrueOptionLabelTranslation($lng, $option_label),
            $this->getFalseOptionLabelTranslation($lng, $option_label)
        );
    }

    public function isCustomOptionLabel($labelValue): bool
    {
        return $labelValue == self::OPTION_LABEL_CUSTOM;
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

        if (!ilFileUtils::moveUploadedFile($fileData['tmp_name'], $filename, $answer->getImageFsPath())) {
            return 2;
        }

        $this->generateThumbForFile($filename, $this->getImagePath(), $this->getThumbSize());

        return 0;
    }

    private function removeAnswerImage($position): void
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
            } else {
                $solutionSubmit[] = null;
            }
        }
        return $solutionSubmit;
    }

    protected function calculateReachedPointsForSolution(?array $found_values, int $active_id = 0): float
    {
        $numCorrect = 0;
        if ($found_values === null) {
            $found_values = [];
        }
        foreach ($this->getAnswers() as $answer) {
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
        return (float) $points;
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

        $result = [];
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
        $result['feedback'] = [
            'onenotcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
            'allcorrect' => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
        ];

        $result['trueOptionLabel'] = $this->getTrueOptionLabelTranslation($this->lng, $this->getOptionLabel());
        $result['falseOptionLabel'] = $this->getFalseOptionLabelTranslation($this->lng, $this->getOptionLabel());

        $result['num_allowed_failures'] = $this->getNumAllowedFailures();

        $answers = [];
        $has_image = false;

        foreach ($this->getAnswers() as $key => $answer) {
            if (strlen((string) $answer->getImageFile())) {
                $has_image = true;
            }

            $answers[] = [
                'answertext' => $this->formatSAQuestion($answer->getAnswertext() ?? ''),
                'correctness' => (bool) $answer->getCorrectness(),
                'order' => (int) $answer->getPosition(),
                'image' => (string) $answer->getImageFile(),
                'feedback' => $this->formatSAQuestion(
                    $this->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation($this->getId(), 0, $key)
                )
            ];
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

    public function toLog(AdditionalInformationGenerator $additional_info): array
    {
        $result = [
            AdditionalInformationGenerator::KEY_QUESTION_TYPE => (string) $this->getQuestionType(),
            AdditionalInformationGenerator::KEY_QUESTION_TITLE => $this->getTitle(),
            AdditionalInformationGenerator::KEY_QUESTION_TEXT => $this->formatSAQuestion($this->getQuestion()),
            AdditionalInformationGenerator::KEY_QUESTION_KPRIM_OPTION_LABEL => $additional_info
                ->getTagForLangVar($this->getLangVarForOptionLabel($this->getOptionLabel())),
            AdditionalInformationGenerator::KEY_QUESTION_SHUFFLE_ANSWER_OPTIONS => $additional_info
                ->getTrueFalseTagForBool($this->getShuffle()),
            AdditionalInformationGenerator::KEY_FEEDBACK => [
                AdditionalInformationGenerator::KEY_QUESTION_FEEDBACK_ON_INCOMPLETE => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), false)),
                AdditionalInformationGenerator::KEY_QUESTION_FEEDBACK_ON_COMPLETE => $this->formatSAQuestion($this->feedbackOBJ->getGenericFeedbackTestPresentation($this->getId(), true))
            ]
        ];

        $result[AdditionalInformationGenerator::KEY_QUESTION_KPRIM_SCORE_PARTIAL_SOLUTION_ENABLED] = $additional_info
            ->getEnabledDisabledTagForBool($this->getNumAllowedFailures() > 0);

        $answers = [];
        foreach ($this->getAnswers() as $key => $answer) {
            $answers[$key + 1] = [
                AdditionalInformationGenerator::KEY_QUESTION_ANSWER_OPTION => $this->formatSAQuestion($answer->getAnswertext()),
                AdditionalInformationGenerator::KEY_QUESTION_ANSWER_OPTION_CORRECTNESS => $additional_info->getTrueFalseTagForBool((bool) $answer->getCorrectness()),
                AdditionalInformationGenerator::KEY_QUESTION_ANSWER_OPTION_ORDER => (int) $answer->getPosition(),
                AdditionalInformationGenerator::KEY_QUESTION_ANSWER_OPTION_IMAGE => (string) $answer->getImageFile(),
                AdditionalInformationGenerator::KEY_FEEDBACK => $this->formatSAQuestion(
                    $this->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation($this->getId(), 0, $key)
                )
            ];
        }

        $result[AdditionalInformationGenerator::KEY_QUESTION_ANSWER_OPTIONS] = $answers;

        return $result;
    }

    protected function solutionValuesToLog(
        AdditionalInformationGenerator $additional_info,
        array $solution_values
    ): array {
        $parsed_solution = [];
        $true_option_label = $this->getTrueOptionLabel($this->getOptionLabel());
        $false_option_label = $this->getFalseOptionLabel($this->getOptionLabel());
        foreach ($this->getAnswers() as $id => $answer) {
            $value = $additional_info->getNoneTag();
            foreach ($solution_values as $solution) {
                if ($solution['value1'] !== (string) $id) {
                    continue;
                }

                $value = $false_option_label;
                if ($solution['value2'] === '1') {
                    $value = $true_option_label;
                }
                break;
            }
            $parsed_solution[$answer->getAnswertext()] = $value;
        }
        return $parsed_solution;
    }

    public function solutionValuesToText(array $solution_values): array
    {
        $parsed_solution = [];
        $true_option_label = $this->getTrueOptionLabelTranslation($this->lng, $this->getOptionLabel());
        $false_option_label = $this->getFalseOptionLabelTranslation($this->lng, $this->getOptionLabel());
        foreach ($this->getAnswers() as $id => $answer) {
            $value = $this->lng->txt('none');
            foreach ($solution_values as $solution) {
                if ($solution['value1'] !== (string) $id) {
                    continue;
                }

                $value = $false_option_label;
                if ($solution['value2'] === '1') {
                    $value = $true_option_label;
                }
                break;
            }
            $parsed_solution[] = "{$answer->getAnswertext()} ({$value})";
        }
        return $parsed_solution;
    }

    public function getCorrectSolutionForTextOutput(int $active_id, int $pass): array
    {
        $true_option_label = $this->getTrueOptionLabelTranslation($this->lng, $this->getOptionLabel());
        $false_option_label = $this->getFalseOptionLabelTranslation($this->lng, $this->getOptionLabel());
        return array_map(
            fn(ilAssKprimChoiceAnswer $v): string => $v->getAnswertext()
                . ' (' . $v->getCorrectness() ? $true_option_label : $false_option_label . ')',
            $this->getAnswers()
        );
    }
}
