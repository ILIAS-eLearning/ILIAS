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

use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\SpecificTextFeedback;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOptions;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOption;
use ILIAS\Questions\AnswerFormTypes\Cloze\Response\AnswerInput as AnswerInputResponse;
use ILIAS\Questions\Attempt\AdditionalAttemptData;
use ILIAS\Questions\Definitions\Range;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Field\Numeric as NumericInput;

class Numeric extends Type
{
    private const float DEFAULT_SUB_ACTION_SIZE = 0.0001;

    private const string KEY_LOWER_LIMIT = 'lower_limit';
    private const string KEY_UPPER_LIMIT = 'upper_limit';
    private const string KEY_STEP_SIZE = 'step_size';

    #[\Override]
    public function getIdentifier(): string
    {
        return 'numeric';
    }

    #[\Override]
    public function getParticipantViewLegacyInput(
        Gap $gap,
        ?AdditionalAttemptData $additional_attempt_data,
        ?Response $response_data
    ): string {
        $gaptemplate = new \ilTemplate(
            'tpl.cloze_gap_numeric.html',
            true,
            true,
            'components/ILIAS/Questions'
        );

        $gaptemplate->setVariable(
            'GAP_NAME',
            $gap->getAnswerInputId()->toString()
        );

        $response = $response_data?->getResponseForInput($gap->getAnswerInputId());
        if ($response !== null) {
            $gaptemplate->setVariable(
                'VALUE_GAP',
                ' value="' . \ilLegacyFormElementsUtil::prepareFormOutput(
                    $response
                ) . '"'
            );
        }

        return $gaptemplate->get();
    }

    #[\Override]
    public function getEditAnswerOptionsInputs(
        FileUpload $file_upload,
        Environment $environment,
        Gap $gap
    ): array {
        $answer_option = $gap->getAnswerOptions()->getAnswerOptionForPositionOrNew(0);

        $ff = $environment->getUIFactory()->input()->field();
        return [
            self::KEY_LOWER_LIMIT => $ff->numeric($environment->getLanguage()->txt('range_lower_limit'))
                ->withStepSize($gap->getStepSize() ?? self::DEFAULT_SUB_ACTION_SIZE)
                ->withRequired(true)
                ->withValue($answer_option->getLowerLimit()),
            self::KEY_UPPER_LIMIT => $ff->numeric($environment->getLanguage()->txt('range_upper_limit'))
                ->withStepSize($gap->getStepSize() ?? self::DEFAULT_SUB_ACTION_SIZE)
                ->withValue($answer_option->getUpperLimit()),
            self::KEY_STEP_SIZE => $ff->numeric($environment->getLanguage()->txt('step_size'))
                ->withStepSize(0.000001)
                ->withRequired(true)
                ->withValue($gap->getStepSize() ?? self::DEFAULT_SUB_ACTION_SIZE)
        ];
    }

    #[\Override]
    public function getEditAnswerOptionsSectionConstraint(): ?Constraint
    {
        return $this->refinery->custom()->constraint(
            fn(array $vs): bool => $vs[self::KEY_UPPER_LIMIT] === null
                || $vs[self::KEY_UPPER_LIMIT] >= $vs[self::KEY_LOWER_LIMIT]
                    && $vs[self::KEY_UPPER_LIMIT] >= $vs[self::KEY_LOWER_LIMIT],
            $this->lng->txt('upper_limit_bigger_than_lower')
        );
    }

    #[\Override]
    public function getEditPointsInputs(
        UIFactory $ui_factory,
        AnswerOptions $answer_options
    ): array {
        $inputs = $answer_options->getEditPointsInputs(
            $ui_factory->input()->field(),
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

    #[\Override]
    public function retrieveResponseFromPost(
        RequestWrapper $post_wrapper,
        UuidFactory $uuid_factory,
        Gap $gap
    ): AnswerInputResponse {
        return new AnswerInputResponse(
            $gap,
            null,
            $post_wrapper->retrieve(
                $gap->getAnswerInputId()->toString(),
                $this->refinery->byTrying([
                    $this->refinery->in()->series([
                        $this->refinery->kindlyTo()->float(),
                        $this->refinery->kindlyTo()->string()
                    ]),
                    $this->refinery->always('')
                ])
            )
        );
    }

    #[\Override]
    public function isBestResponse(
        Gap $gap,
        AnswerInputResponse $response
    ): bool {
        if ($response->getResponse() === null) {
            return false;
        }

        /** @var ?AnswerOption $answer_option */
        $answer_options_awarding_points = $gap->getAnswerOptions()
            ->getAnswerOptionsAwardingPoints();

        $answer_option = $answer_options_awarding_points === null
            ? null
            : array_shift($answer_options_awarding_points);

        if ($answer_option === null) {
            return false;
        }

        return $this->getRangeValueFromResponseValue(
            $answer_option,
            $response->getResponse()
        ) === Range::InRange;
    }

    #[\Override]
    public function calculateAwardedPointsForResponse(
        Gap $gap,
        Uuid|string|null $response
    ): float {
        if ($response === null) {
            return 0.0;
        }

        $answer_option = $this->getAnswerOptionAwardingPoints($gap);

        if ($answer_option === null) {
            return 0.0;
        }

        if ($this->getRangeValueFromResponseValue(
            $answer_option,
            $response
        ) === Range::InRange) {
            return $answer_option->getAvailablePoints();
        }

        return 0.0;
    }

    #[\Override]
    public function buildClientSideRepresentationOfResponse(
        Gap $gap,
        AnswerInputResponse $response
    ): array {
        $answer_option = $this->getAnswerOptionAwardingPoints($gap);

        return [
            self::KEY_RESPONSE => $response->getResponse(),
            self::KEY_LOWER_LIMIT => $answer_option->getLowerLimit(),
            self::KEY_UPPER_LIMIT => $answer_option->getUpperLimit()
        ];
    }

    #[\Override]
    public function getSpecificFeedbackParticipantOutput(
        UIFactory $ui_factory,
        Gap $gap,
        array $specific_feedbacks,
        Uuid|string $answer_input_response
    ): ?Component {
        $specific_feedbacks_by_condition = array_reduce(
            $specific_feedbacks,
            function (array $c, SpecificTextFeedback $v): array {
                if (!array_key_exists($v->getCondition(), $c)) {
                    $c[$v->getCondition()] = [];
                }

                $c[$v->getCondition()] = $v->getFeedbackTextForPresentation($this->refinery);

                return $c;
            },
            []
        );

        $range_value = $this->getRangeValueFromResponseValue(
            $this->getAnswerOptionAwardingPoints($gap),
            $answer_input_response
        );

        if (isset($specific_feedbacks_by_condition[$range_value->value])) {
            return $ui_factory->legacy()->content(
                $specific_feedbacks_by_condition[$range_value->value]
            );
        }

        if (($range_value === Range::BelowRange
                || $range_value === Range::BelowRange)
            && isset($specific_feedbacks_by_condition[Range::OutOfRange->value])) {
            return $ui_factory->legacy()->content(
                $specific_feedbacks_by_condition[Range::OutOfRange->value]
            );
        }

        return null;
    }

    #[\Override]
    protected function retrieveResponseTextFromAnswerOption(
        AnswerOption $answer_option
    ): string {
        $trafo = $this->refinery->kindlyTo()->string();
        $lower_limit_string = $trafo->transform(
            $answer_option->getLowerLimit()
        );
        $upper_limit = $answer_option->getUpperLimit();
        return $upper_limit === null
            ? $lower_limit_string
            : "{$lower_limit_string} - {$trafo->transform($upper_limit)}";
    }

    private function getAnswerOptionAwardingPoints(
        Gap $gap
    ): AnswerOption {
        /** @var ?AnswerOption $answer_option */
        $answer_options_awarding_points = $gap->getAnswerOptions()
            ->getAnswerOptionsAwardingPoints();

        return $answer_options_awarding_points === null
            ? null
            : array_shift($answer_options_awarding_points);
    }

    private function getRangeValueFromResponseValue(
        AnswerOption $answer_option,
        string $response
    ): Range {
        $response_as_float = $this->refinery->kindlyTo()->float()->transform(
            $response
        );

        $upper_limit = $answer_option->getUpperLimit();
        $lower_limit = $answer_option->getLowerLimit();

        if ($response < $lower_limit) {
            return Range::BelowRange;
        }

        if ($upper_limit === null
                && $response_as_float === $lower_limit
            || $response_as_float <= $upper_limit) {
            return Range::InRange;
        }

        return Range::AboveRange;
    }
}
