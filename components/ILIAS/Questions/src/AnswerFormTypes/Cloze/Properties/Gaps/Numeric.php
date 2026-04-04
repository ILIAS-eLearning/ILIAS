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
use ILIAS\Questions\Definitions\Range;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Field\Numeric as NumericInput;

class Numeric extends Type
{
    private const float DEFAULT_SUB_ACTION_SIZE = 0.0001;

    public function __construct(
        Refinery $refinery,
        Language $lng,
        private readonly UIFactory $ui_factory
    ) {
        parent::__construct($refinery, $lng);
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'numeric';
    }

    #[\Override]
    public function getParticipantViewLegacyInput(
        Gap $gap
    ): string {
        $gaptemplate = new \ilTemplate(
            'tpl.il_as_qpl_cloze_question_gap_numeric.html',
            true,
            true,
            'components/ILIAS/TestQuestionPool'
        );

        $gaptemplate->setVariable(
            'GAP_COUNTER',
            $gap->getAnswerInputId()->toString()
        );

        return $gaptemplate->get();
    }

    #[\Override]
    public function getEditAnswerOptionsInputs(
        FileUpload $file_upload,
        Environment $environment,
        Gap $gap
    ): array {
        $answer_option = $gap->getAnswerOptions()->getAnswerOptionForPositionOrNew(0);

        $ff = $this->ui_factory->input()->field();
        return [
            'lower_limit' => $ff->numeric($this->lng->txt('lower_limit'))
                ->withStepSize($gap->getStepSize() ?? self::DEFAULT_SUB_ACTION_SIZE)
                ->withRequired(true)
                ->withValue($answer_option->getLowerLimit()),
            'upper_limit' => $ff->numeric($this->lng->txt('upper_limit'))
                ->withStepSize($gap->getStepSize() ?? self::DEFAULT_SUB_ACTION_SIZE)
                ->withValue($answer_option->getUpperLimit()),
            'step_size' => $ff->numeric($this->lng->txt('step_size'))
                ->withStepSize(0.000001)
                ->withRequired(true)
                ->withValue($gap->getStepSize() ?? self::DEFAULT_SUB_ACTION_SIZE)
        ];
    }

    #[\Override]
    public function getEditAnswerOptionsSectionConstraint(): ?Constraint
    {
        return $this->refinery->custom()->constraint(
            fn(array $vs): bool => $vs['upper_limit'] === null
                || $vs['upper_limit'] >= $vs['lower_limit']
                    && $vs['upper_limit'] >= $vs['step_size'],
            $this->lng->txt('upper_limit_bigger_than_lower')
        );
    }

    #[\Override]
    public function getEditPointsInputs(
        AnswerOptions $answer_options
    ): array {
        $inputs = $answer_options->getEditPointsInputs(
            $this->ui_factory->input()->field(),
            function (AnswerOption $v): string {
                if ($v->getUpperLimit() === null) {
                    return sprintf(
                        $this->lng->txt('equal'),
                        $v->getLowerLimit()
                    );
                }

                return sprintf(
                    $this->lng->txt('between'),
                    $v->getLowerLimit(),
                    $v->getUpperLimit()
                );
            }
        );
        return array_map(
            fn(NumericInput $v): NumericInput => $v->withRequired(true),
            $inputs
        );
    }

    #[\Override]
    public function getEditPointsSectionConstraint(): ?Constraint
    {
        return null;
    }

    #[\Override]
    public function getBuildGapTransformation(
        Gap $gap
    ): Transformation {
        return $this->refinery->custom()->transformation(
            fn(array $vs): Gap => $gap->withAnswerOptions(
                $gap->getAnswerOptions()->withAnswerOptions([
                    $gap->getAnswerOptions()->getAnswerOptionForPositionOrNew(0)
                        ->withLowerLimit($vs['lower_limit'])
                        ->withUpperLimit($vs['upper_limit'])
                ])
            )->withStepSize($vs['step_size'])
        );
    }

    #[\Override]
    public function getCombinationsSelectValues(
        Gap $gap
    ): array {
        return [
            Range::InRange->value => Range::InRange->getLabel($this->lng),
            Range::OutOfRange->value => Range::OutOfRange->getLabel($this->lng)
        ];
    }

    #[\Override]
    public function getFeedbackSelectValues(
        Gap $gap,
        bool $is_marking_required
    ): array {
        return $this->getCombinationsSelectValues($gap);
    }

    #[\Override]
    public function isValidFeedbackCondition(
        UuidFactory $uuid_factory,
        Gap $gap,
        string $condition
    ): bool {
        return Range::tryFrom($condition) !== null;
    }

    #[\Override]
    public function getLabelForValue(
        UuidFactory $uuid_factory,
        Gap $gap,
        string $value
    ): string {
        return Range::tryFrom($value)?->getLabel($this->lng) ?? '';
    }
}
