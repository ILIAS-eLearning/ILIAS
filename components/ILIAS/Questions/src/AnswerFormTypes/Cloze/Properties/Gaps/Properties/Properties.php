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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Properties;

use ILIAS\Questions\Question\Definitions\TextMatchingOptions;
use ILIAS\Questions\Presentation\Layout\Definitions\CarryWrapper;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Implementation\Component\Input\Field\Group;

class Properties
{
    private const string FORM_KEY_MAX_CHARS = 'max_chars';
    private const string FORM_KEY_STEP_SIZE = 'step_size';
    private const string FORM_KEY_TEXT_MATCHING_METHOD = 'matching_method';
    private const string FORM_KEY_MIN_AUTOCOMPLETE = 'min_autocomplete';
    private const string FORM_KEY_SHUFFLE_ANSWER_OPTIONS = 'shuffle';
    private const string FORM_KEY_ANSWER_OPTIONS = 'answer_options';

    /**
     * @param array<ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Properties    \AnswerOption> $answer_options
     */
    public function __construct(
        private readonly Uuid $answer_input_id,
        private AnswerOptions $answer_options,
        private ?int $max_chars = null,
        private ?float $step_size = null,
        private ?TextMatchingOptions $text_matching_method = null,
        private ?int $min_autocomplete = null,
        private ?bool $shuffle_answer_options = null
    ) {
    }

    public function getAnswerInputId(): Uuid
    {
        return $this->answer_input_id;
    }

    public function getMaxChars(): ?int
    {
        return $this->max_chars;
    }

    public function withMaxChars(?int $max_chars): self
    {
        $clone = clone $this;
        $clone->max_chars = $max_chars;
        return $clone;
    }

    public function getStepSize(): ?float
    {
        return $this->step_size;
    }

    public function withStepSize(float $step_size): self
    {
        $clone = clone $this;
        $clone->step_size = $step_size;
        return $clone;
    }

    public function getTextMatchingMethod(): ?TextMatchingOptions
    {
        return $this->text_matching_method;
    }

    public function withTextMatchingMethod(TextMatchingOptions $matching_method): self
    {
        $clone = clone $this;
        $clone->text_matching_method = $matching_method;
        return $clone;
    }

    public function getMinAutocomplete(): ?int
    {
        return $this->min_autocomplete;
    }

    public function withMinAutocomplete(int $min_autocomplete): self
    {
        $clone = clone $this;
        $clone->min_autocomplete = $min_autocomplete;
        return $clone;
    }

    public function getShuffleAnswerOptions(): ?bool
    {
        return $this->shuffle_answer_options;
    }

    public function withShuffleAnswerOptions(bool $shuffle_answer_options): self
    {
        $clone = clone $this;
        $clone->shuffle_answer_options = $shuffle_answer_options;
        return $clone;
    }

    public function getAnswerOptions(): AnswerOptions
    {
        return $this->answer_options;
    }

    public function withAnswerOptions(AnswerOptions $answer_options): self
    {
        $clone = clone $this;
        $clone->answer_options = $answer_options;
        return $clone;
    }

    public function getCarryInputs(FieldFactory $ff): Group
    {
        $inputs = [];
        if ($this->max_chars !== null) {
            $inputs[self::FORM_KEY_MAX_CHARS] = $ff->hidden()->withValue($this->getMaxChars())
                ->withDedicatedName(self::FORM_KEY_MAX_CHARS . $this->getShortenedAnswerInputId());
        }

        if ($this->step_size !== null) {
            $inputs[self::FORM_KEY_STEP_SIZE] = $ff->hidden()->withValue($this->getStepSize())
                ->withDedicatedName(self::FORM_KEY_STEP_SIZE . $this->getShortenedAnswerInputId());
        }

        if ($this->text_matching_method !== null) {
            $inputs[self::FORM_KEY_TEXT_MATCHING_METHOD] = $ff->hidden()->withValue($this->getTextMatchingMethod()->value)
                ->withDedicatedName(self::FORM_KEY_TEXT_MATCHING_METHOD . $this->getShortenedAnswerInputId());
        }

        if ($this->min_autocomplete !== null) {
            $inputs[self::FORM_KEY_MIN_AUTOCOMPLETE] = $ff->hidden()->withValue($this->getMinAutocomplete())
                ->withDedicatedName(self::FORM_KEY_MIN_AUTOCOMPLETE . $this->getShortenedAnswerInputId());
        }

        if ($this->shuffle_answer_options !== null) {
            $inputs[self::FORM_KEY_SHUFFLE_ANSWER_OPTIONS] = $ff->hidden()->withValue($this->getShuffleAnswerOptions() ? '1' : '0')
                ->withDedicatedName(self::FORM_KEY_SHUFFLE_ANSWER_OPTIONS . $this->getShortenedAnswerInputId());
        }

        $inputs[self::FORM_KEY_ANSWER_OPTIONS] = $ff->hidden()->withValue($this->answer_options->buildHiddenInputValue())
            ->withDedicatedName(self::FORM_KEY_ANSWER_OPTIONS . $this->getShortenedAnswerInputId());

        return $ff->group($inputs);
    }

    public function getFromCarryTransformation(
        Refinery $refinery
    ): Transformation {
        return $refinery->custom()->transformation(
            function (CarryWrapper $v) use ($refinery): self {
                $clone = clone $this;
                $clone->max_chars = $this->retrieveMaxCharsFromCarry($refinery, $v);
                $clone->step_size = $this->retrieveStepSizeFromCarry($refinery, $v);
                $clone->text_matching_method = $this->retrieveTextMatchingMethodFromCarry($refinery, $v);
                $clone->min_autocomplete = $this->retrieveMinAutocompleteFromCarry($refinery, $v);
                $clone->shuffle_answer_options = $this->retrieveShuffleAnswerOptionsFromCarry($refinery, $v);
                $clone->answer_options = $this->retrieveAnswerOptionsFromCarry($refinery, $v);
                return $clone;
            }
        );
    }

    private function retrieveMaxCharsFromCarry(
        Refinery $refinery,
        CarryWrapper $carry
    ): ?int {
        return $carry->retrieve(
            self::FORM_KEY_MAX_CHARS . $this->getShortenedAnswerInputId(),
            $refinery->byTrying([
                $refinery->kindlyTo()->int(),
                $refinery->always($this->getMaxChars())
            ])
        );
    }

    private function retrieveStepSizeFromCarry(
        Refinery $refinery,
        CarryWrapper $carry
    ): ?float {
        return $carry->retrieve(
            self::FORM_KEY_STEP_SIZE . $this->getShortenedAnswerInputId(),
            $refinery->byTrying([
                $refinery->kindlyTo()->float(),
                $refinery->always($this->getStepSize())
            ])
        );
    }

    private function retrieveTextMatchingMethodFromCarry(
        Refinery $refinery,
        CarryWrapper $carry
    ): ?TextMatchingOptions {
        return $carry->retrieve(
            self::FORM_KEY_TEXT_MATCHING_METHOD . $this->getShortenedAnswerInputId(),
            $refinery->custom()->transformation(
                fn(?string $v): ?TextMatchingOptions => $v !== null
                    ? TextMatchingOptions::tryFrom($v)
                    : $this->getTextMatchingMethod()
            )
        );
    }

    private function retrieveMinAutocompleteFromCarry(
        Refinery $refinery,
        CarryWrapper $carry
    ): ?int {
        return $carry->retrieve(
            self::FORM_KEY_MIN_AUTOCOMPLETE . $this->getShortenedAnswerInputId(),
            $refinery->byTrying([
                $refinery->kindlyTo()->int(),
                $refinery->always($this->getMinAutocomplete())
            ])
        );
    }

    private function retrieveShuffleAnswerOptionsFromCarry(
        Refinery $refinery,
        CarryWrapper $carry
    ): ?bool {
        return $carry->retrieve(
            self::FORM_KEY_SHUFFLE_ANSWER_OPTIONS . $this->getShortenedAnswerInputId(),
            $refinery->byTrying([
                $refinery->kindlyTo()->bool(),
                $refinery->always($this->getShuffleAnswerOptions())
            ])
        );
    }

    private function retrieveAnswerOptionsFromCarry(
        Refinery $refinery,
        CarryWrapper $carry
    ): AnswerOptions {
        return $carry->retrieve(
            self::FORM_KEY_ANSWER_OPTIONS . $this->getShortenedAnswerInputId(),
            $refinery->custom()->transformation(
                fn(?string $v): AnswerOptions => $this->answer_options->withValuesFromHiddenInputValue($v)
            )
        );
    }

    private function getShortenedAnswerInputId(): string
    {
        return mb_substr($this->answer_input_id->toString(), 0, 4);
    }
}
