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
use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\Types as TextFeedbackTypes;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOptions;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOption;
use ILIAS\Questions\AnswerFormTypes\Cloze\Response\AnswerInput as AnswerInputResponse;
use ILIAS\Questions\Attempt\AdditionalAttemptData;
use ILIAS\Questions\Definitions\TextMatchingOptions;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory as UIFactory;

class Text extends Type
{
    private const TextMatchingOptions DEFAULT_TECT_MATCHING_METHOD = TextMatchingOptions::CaseInsensitive;

    #[\Override]
    public function getIdentifier(): string
    {
        return 'text';
    }

    #[\Override]
    public function getParticipantViewLegacyInput(
        Gap $gap,
        ?AdditionalAttemptData $additional_attempt_data,
        ?Response $response_data
    ): string {
        $gaptemplate = new \ilTemplate(
            'tpl.cloze_gap_text.html',
            true,
            true,
            'components/ILIAS/Questions'
        );

        $gap_size = $gap->getMaxChars();
        if ($gap_size > 0) {
            $gaptemplate->setCurrentBlock('size_and_maxlength');
            $gaptemplate->setVariable('TEXT_GAP_SIZE', $gap_size);
            $gaptemplate->parseCurrentBlock();
        }
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
        $ff = $environment->getUIFactory()->input()->field();
        return [
            'answer_options' => $ff->tag(
                $environment->getLanguage()->txt('answer_options'),
                []
            )->withRequired(true)
            ->withValue($gap->getAnswerOptions()->getTagsArrayFromAnswerOptions()),
            'matching_method' => $ff->select(
                $environment->getLanguage()->txt('text_matching_method'),
                TextMatchingOptions::buildOptionsList($environment->getLanguage())
            )->withRequired(true)
            ->withValue($gap->getTextMatchingMethod()?->value ?? self::DEFAULT_TECT_MATCHING_METHOD->value),
            'max_chars' => $ff->numeric(
                $environment->getLanguage()->txt('max_characters'),
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
        UIFactory $ui_factory,
        AnswerOptions $answer_options,
        bool $input_required
    ): array {
        return $answer_options->getEditPointsInputs(
            $ui_factory->input()->field(),
            fn(AnswerOption $v): string => $v->getTextValue()
        );
    }

    #[\Override]
    public function getEditPointsSectionConstraint(
        bool $input_required
    ): ?Constraint {
        if (!$input_required) {
            return null;
        }

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
                    $this->refinery->kindlyTo()->string(),
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
        return $this->getBestResponse(
            $gap
        )?->getResponse() === $response->getResponse();
    }

    #[\Override]
    public function calculateAwardedPointsForResponse(
        Gap $gap,
        Uuid|string|null $response
    ): float {

        $answer_option = array_filter(
            $gap->getAnswerOptions()->getAnswerOptionsAwardingPoints(),
            fn(AnswerOption $v): bool => $response === $v->getTextValue()
        );

        if ($answer_option === []) {
            return 0.0;
        }

        return array_shift($answer_option)->getAvailablePoints();
    }

    #[\Override]
    public function buildClientSideRepresentationOfResponse(
        Gap $gap,
        AnswerInputResponse $response
    ): array {
        return [
            self::KEY_RESPONSE => $response->getResponse()
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
                $c[$v->getCondition()] = $v->getFeedbackText();
                return $c;
            },
            []
        );

        if ($answer_input_response instanceof Uuid) {
            return $this->getSpecificFeedbackParticipantOutputForAnswerOption(
                $ui_factory,
                $specific_feedbacks_by_condition[$answer_input_response->toString()] ?? null
            );
        }

        if ($this->getBestResponse($gap) === null) {
            return null;
        }

        if ($this->getBestResponse($gap)->getResponse() === $answer_input_response) {
            return isset($specific_feedbacks_by_condition[TextFeedbackTypes::BestResponse->value])
                ? $ui_factory->legacy()->content(
                    $this->refinery->string()->markdown()->toHTML()->transform(
                        $specific_feedbacks_by_condition[TextFeedbackTypes::BestResponse->value]->getRawRepresentation()
                    )
                ) : null;
        }

        return isset($specific_feedbacks_by_condition[TextFeedbackTypes::OtherResponse->value])
            ? $ui_factory->legacy()->content(
                $this->refinery->string()->markdown()->toHTML()->transform(
                    $specific_feedbacks_by_condition[TextFeedbackTypes::OtherResponse->value]->getRawRepresentation()
                )
            ) : null;
    }

    #[\Override]
    protected function retrieveResponseTextFromAnswerOption(
        AnswerOption $answer_option
    ): string {
        return $answer_option->getTextValue();
    }
}
