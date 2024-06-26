<?php

/**
 * This trait is used inside of question classes to track if the answer type has changed during the request.
 */
trait ChoiceQuestionAnswerTypeAwareTrait
{
    /**
     * @var string The answer type of the question.
     */
    private string $answer_type = ChoiceQuestionAnswerType::SINGLE_LINE;

    /**
     * @var bool True, if the answer type has changed during the request.
     */
    private bool $answer_type_changed = false;

    /**
     * Returns the valid answer types for the question.
     */
    public function getValidAnswerTypes(): array
    {
        return [ChoiceQuestionAnswerType::SINGLE_LINE, ChoiceQuestionAnswerType::MULTI_LINE];
    }

    /**
     * Returns the select options for the answer type used inside form elements.
     */
    public function getAnswerTypeSelectOptions(ilLanguage $lng): array
    {
        return [
            ChoiceQuestionAnswerType::SINGLE_LINE => $lng->txt('answers_singleline'),
            ChoiceQuestionAnswerType::MULTI_LINE => $lng->txt('answers_multiline')
        ];
    }

    /**
     * Set the answer type of the question.
     */
    public function setAnswerType(string $answer_type): void
    {
        $this->setAnswerTypeChanged($this->answer_type !== $answer_type);
        $this->answer_type = $answer_type;
    }

    /**
     * Returns the answer type of the question.
     */
    public function getAnswerType(): string
    {
        return $this->answer_type;
    }

    /**
     * Returns true, if the answer type of the question is the given type.
     */
    public function isAnswerType(string $answerType): bool
    {
        return $this->getAnswerType() === $answerType;
    }

    public function isSingleLineAnswerType(): bool
    {
        return $this->isAnswerType(ChoiceQuestionAnswerType::SINGLE_LINE);
    }

    /**
     * Returns true, if the given answer type is valid for the question.
     */
    public function isValidAnswerType($answerType): bool
    {
        return in_array($answerType, $this->getValidAnswerTypes());
    }

    /**
     * Set true, if the answer type has changed.
     * This method should be called from within the setter of the answer type.
     */
    protected function setAnswerTypeChanged(bool $changed): void
    {
        $this->answer_type_changed = $changed;
    }

    /**
     * Returns true, if the answer type has changed.
     */
    public function hasAnswerTypeChanged(): bool
    {
        return $this->answer_type_changed;
    }
}
