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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps;

use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOptions;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOption;
use ILIAS\Questions\Definitions\TextMatchingOptions;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Factory as UIFactory;

class Text extends Type
{
    private const TextMatchingOptions DEFAULT_TECT_MATCHING_METHOD = TextMatchingOptions::CaseInsensitive;

    public function __construct(
        Refinery $refinery,
        private readonly Language $lng,
        private readonly UIFactory $ui_factory
    ) {
        parent::__construct($refinery);
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'text';
    }

    #[\Override]
    public function getParticipantViewLegacyInput(
        Gap $gap
    ): string {
        $gaptemplate = new \ilTemplate(
            'tpl.il_as_qpl_cloze_question_gap_text.html',
            true,
            true,
            'components/ILIAS/TestQuestionPool'
        );

        $gap_size = $gap->getMaxChars();
        if ($gap_size > 0) {
            $gaptemplate->setCurrentBlock('size_and_maxlength');
            $gaptemplate->setVariable('TEXT_GAP_SIZE', $gap_size);
            $gaptemplate->parseCurrentBlock();
        }
        $gaptemplate->setVariable(
            'GAP_COUNTER',
            $gap->getAnswerInputId()->toString()
        );

        return $gaptemplate->get();
    }

    #[\Override]
    public function getEditAnswerOptionsInputs(
        Gap $gap
    ): array {
        $ff = $this->ui_factory->input()->field();
        return [
            'answer_options' => $ff->tag(
                $this->lng->txt('answer_options'),
                []
            )->withRequired(true)
            ->withValue($gap->getAnswerOptions()->getTagsArrayFromAnswerOptions()),
            'matching_method' => $ff->select(
                $this->lng->txt('matching_method'),
                TextMatchingOptions::buildOptionsList($this->lng)
            )->withRequired(true)
            ->withValue($gap->getTextMatchingMethod()?->value ?? self::DEFAULT_TECT_MATCHING_METHOD->value),
            'max_chars' => $ff->numeric(
                $this->lng->txt('max_chars'),
            )->withValue($gap->getMaxChars())
        ];
    }

    #[\Override]
    public function getEditAnswerOptionsSectionConstraint(): ?Constraint
    {
        return $this->refinery->custom()->constraint(
            fn(array $vs): bool => array_filter(
                $vs['answer_options'],
                fn(string $v): bool => in_array($v, $vs['answer_options'])
            ) !== [],
            $this->lng->txt('answer_options_must_be_unique')
        );
    }

    #[\Override]
    public function getEditPointsInputs(
        AnswerOptions $answer_options
    ): array {
        return $answer_options->getEditPointsInputs(
            $this->ui_factory->input()->field(),
            fn(AnswerOption $v): string => $v->getTextValue()
        );
    }

    #[\Override]
    public function getEditPointsSectionConstraint(): ?Constraint
    {
        return $this->refinery->custom()->constraint(
            function (array $vs): bool {
                foreach ($vs as $v) {
                    if ($v > 0.0) {
                        return true;
                    }
                }
                return false;
            },
            $this->lng->txt('at_least_one_gap_positiv_points')
        );
    }

    #[\Override]
    public function getBuildGapTransformation(
        Gap $gap
    ): Transformation {
        return $this->refinery->custom()->transformation(
            fn(array $vs): Gap => $gap->withMaxChars($vs['max_chars'])
                ->withTextMatchingMethod(
                    TextMatchingOptions::tryFrom($vs['matching_method'])
                        ?? self::DEFAULT_TECT_MATCHING_METHOD
                )->withAnswerOptions(
                    $gap->getAnswerOptions()->withAnswerOptionsFromTags(
                        $vs['answer_options']
                    )
                )
        );
    }

    #[\Override]
    public function getAnswerInput(): \ilFormPropertyGUI
    {
        ;
    }
}
