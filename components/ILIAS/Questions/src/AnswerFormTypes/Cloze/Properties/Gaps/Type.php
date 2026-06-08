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

use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\SpecificTextFeedback;
use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\Types as TextFeedbackTypes;
use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOption;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\AnswerOptions\AnswerOptions;
use ILIAS\Questions\AnswerFormTypes\Cloze\Response\AnswerInput as AnswerInputResponse;
use ILIAS\Questions\Attempt\AdditionalAttemptData;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Definitions\Range;
use ILIAS\Data\Text\Markdown;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory as UIFactory;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

abstract class Type
{
    public function __construct(
        protected readonly Language $lng,
        protected readonly Refinery $refinery
    ) {

    }

    abstract public function getIdentifier(): string;

    abstract public function getParticipantViewLegacyInput(
        Gap $gap,
        ?AdditionalAttemptData $additional_attempt_data,
        ?Response $response_data
    ): string;

    abstract public function getEditAnswerOptionsInputs(
        FileUpload $file_upload,
        Environment $environment,
        Gap $gap
    ): array;

    abstract public function getEditAnswerOptionsSectionConstraint(): ?Constraint;

    abstract public function getEditPointsInputs(
        UIFactory $ui_factory,
        AnswerOptions $answer_options
    ): array;

    abstract public function getEditPointsSectionConstraint(
    ): ?Constraint;

    abstract public function getBuildGapTransformation(
        Gap $gap
    ): Transformation;

    abstract public function retrieveResponseFromPost(
        RequestWrapper $post_wrapper,
        UuidFactory $uuid_factory,
        Gap $gap
    ): AnswerInputResponse;

    public function getCombinationsSelectValues(
        Gap $gap
    ): array {
        return $gap->getAnswerOptions()->buildArrayForSelectInput(
            $this->refinery->random()->dontShuffle()
        );
    }

    public function getFeedbackSelectValues(
        Gap $gap,
        bool $is_marking_required
    ): array {
        $basic_select_values = $this->getCombinationsSelectValues(
            $gap
        );
        if (!$is_marking_required) {
            return $basic_select_values;
        }
        return [
            ...[
                TextFeedbackTypes::BestResponse->value => TextFeedbackTypes::BestResponse
                    ->getTranslatedOptionName($this->lng),
                TextFeedbackTypes::OtherResponse->value => TextFeedbackTypes::OtherResponse
                    ->getTranslatedOptionName($this->lng),
                TextFeedbackTypes::NoResponse->value => TextFeedbackTypes::NoResponse
                    ->getTranslatedOptionName($this->lng)
            ],
            ...$basic_select_values
        ];
    }

    public function isValidFeedbackCondition(
        UuidFactory $uuid_factory,
        Gap $gap,
        string $condition
    ): bool {
        if (TextFeedbackTypes::tryFrom($condition) !== null
            || Range::tryFrom($condition) !== null) {
            return true;
        }

        try {
            return $gap->getAnswerOptions()->getAnswerOptionById(
                $uuid_factory->fromString($condition)
            ) !== null;
        } catch (InvalidUuidStringException $e) {
            return false;
        }
    }

    public function getLabelForValue(
        UuidFactory $uuid_factory,
        Gap $gap,
        string $value
    ): string {
        $from_feedback = TextFeedbackTypes::tryFrom($value);
        if ($from_feedback !== null) {
            return $from_feedback->getTranslatedOptionName($this->lng);
        }

        return $gap->getAnswerOptions()->getAnswerOptionById(
            $uuid_factory->fromString($value)
        )->getTextValue();
    }

    public function getAddPointsTransformation(
        Gap $gap
    ): Transformation {
        return $this->refinery->custom()->transformation(
            fn(array $vs): Gap => $gap->withAnswerOptions(
                $gap->getAnswerOptions()
                    ->withAnswerOptionsWithAddedPointsFromForm($this->refinery, $vs)
            )
        );
    }

    public function getBestResponse(
        Gap $gap
    ): ?AnswerInputResponse {
        $best_answer_option = $gap->getAnswerOptions()->getBestAnswerOption();
        if ($best_answer_option === null) {
            return null;
        }

        $text = $this->retrieveResponseTextFromAnswerOption(
            $best_answer_option
        );
        if ($text !== '') {
            $best_answer_option = null;
        }

        return new AnswerInputResponse(
            $gap,
            $best_answer_option?->getAnswerOptionId(),
            $text
        );
    }

    public function isBestResponse(
        Gap $gap,
        AnswerInputResponse $response
    ): bool {
        $response_value = $response->getResponse();

        if (!($response_value instanceof Uuid)) {
            return false;
        }

        return $this->getBestResponse(
            $gap
        )->getResponse()?->compareTo(
            $response_value
        ) === 0;
    }

    public function calculateAwardedPointsForResponse(
        Gap $gap,
        Uuid|string|null $response
    ): float {
        if (!($response instanceof Uuid)) {
            return 0.0;
        }

        $answer_option = $gap->getAnswerOptions()->getAnswerOptionById($response);

        if ($answer_option === null) {
            return 0.0;
        }

        return $answer_option->getAvailablePoints() ?? 0.0;
    }

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
                    $specific_feedbacks_by_condition[TextFeedbackTypes::OtherResponse]->getRawRepresentation()
                )
            ) : null;
    }

    public function retrieveResponseFromPreviewData(
        UuidFactory $uuid_factory,
        Gap $gap,
        array $preview_data
    ): ?AnswerInputResponse {
        if ($preview_data === []) {
            return null;
        }

        return new AnswerInputResponse(
            $gap,
            isset($preview_data[AnswerInputResponse::KEY_SELECTED_ANSWER_OPTION])
                ? $uuid_factory->fromString($preview_data[AnswerInputResponse::KEY_SELECTED_ANSWER_OPTION])
                : null,
            $preview_data[AnswerInputResponse::KEY_TEXT] ?? ''
        );
    }

    protected function retrieveResponseTextFromAnswerOption(
        AnswerOption $answer_option
    ): string {
        return '';
    }

    final protected function buildGapName(
        Gap $gap
    ): string {
        return "gap_{$gap->getAnswerInputId()->toString()}";
    }

    private function getSpecificFeedbackParticipantOutputForAnswerOption(
        UIFactory $ui_factory,
        ?Markdown $specific_feedback
    ): ?Component {
        if ($specific_feedback === null) {
            return null;
        }

        return $ui_factory->legacy()->content(
            $this->refinery->string()->markdown()->toHTML()->transform(
                $specific_feedback->getRawRepresentation()
            )
        );
    }
}
