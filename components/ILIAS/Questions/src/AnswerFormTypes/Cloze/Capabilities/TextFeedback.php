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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Capabilities;

use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\TextFeedback as TextFeedbackBase;
use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\SpecificTextFeedback;
use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\OverviewTable;
use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\Types as TextFeedbackTypes;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Gaps\Gaps;
use ILIAS\Questions\AnswerFormTypes\Cloze\Response\AnswerInput as AnswerInputResponse;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Data\Text\Factory as TextFactory;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;

class TextFeedback extends TextFeedbackBase
{
    public function __construct(
        private readonly UuidFactory $uuid_factory,
        private readonly TextFactory $text_factory,
    ) {

    }

    #[\Override]
    public function getAdditionalInputs(
        Language $lng,
        UIFactory $ui_factory,
        bool $set_legacy_texts_as_values
    ): null {
        return null;
    }

    #[\Override]
    public function getSpecificFeedbackTable(
        Environment $environment
    ): ?OverviewTable {
        return new TextFeedbackOverviewTable(
            $this->uuid_factory,
            $this->text_factory,
            new TextFeedbackOverviewDataRetrieval(
                $environment->getLanguage(),
                $environment->getRefinery(),
                $this->uuid_factory,
                $environment->getAnswerFormProperties(),
                $this
            )
        );
    }

    #[\Override]
    public function getSpecificFeedbackParticipantOutput(
        UIFactory $ui_factory,
        Refinery $refinery,
        Properties $properties,
        ?Response $response
    ): array {
        $specific_feedbacks = $this->getSpecificFeedbacks();
        if ($response === null || $specific_feedbacks === []) {
            return [];
        }

        /** @var Gaps $gaps */
        $gaps = $properties->getGaps();

        return array_reduce(
            $this->orderFeedbacksByAnswerInputId($specific_feedbacks),
            function (
                array $c,
                array $v
            ) use (
                $ui_factory,
                $refinery,
                $gaps,
                $response
            ): array {
                $gap_id = $v[0]->getAnswerInputId();

                /** @var ?AnswerInputResponse $answer_for_gap */
                $answer_for_gap = $response?->getResponseForInput($gap_id);

                if ($answer_for_gap === null) {
                    return $this->addFeedbackForNoSelectionToParticipantOutput(
                        $ui_factory,
                        $refinery,
                        $v,
                        $c
                    );
                }

                $gap = $gaps->getGapById($gap_id);
                $output = $gap->getType()->getSpecificFeedbackParticipantOutput(
                    $ui_factory,
                    $gap,
                    $v,
                    $answer_for_gap
                );

                if ($output !== null) {
                    $c[] = $output;
                }

                return $c;
            },
            []
        );
    }

    #[\Override]
    public function specificFeedbackInputsHaveLegacyTexts(): bool
    {
        return false;
    }

    #[\Override]
    public function onAnswerFormUpdate(
        Properties $answer_form_properties
    ): static {
        $gaps = $answer_form_properties->getGaps();

        return array_reduce(
            $this->getSpecificFeedbacks(),
            function (
                TextFeedback $c,
                SpecificTextFeedback $v
            ) use ($gaps): self {
                $gap = $gaps->getGapById($v->getParentId());
                if ($gap === null
                    || !$gap->getType()->isValidFeedbackCondition(
                        $this->uuid_factory,
                        $gap,
                        $v->getCondition()
                    )
                ) {
                    return $c->withoutSpecificFeedback($v);
                }

                return $c;
            },
            clone $this
        );
    }

    public function buildSpecificFeedbackOverviewTableArray(): array
    {
        return array_reduce(
            $this->getSpecificFeedbacks(),
            function (array $c, SpecificTextFeedback $v): array {
                $gap_id = $v->getParentId()->toString();
                $key = md5($v->getFeedbackText()->getRawRepresentation());
                if (!array_key_exists($gap_id, $c)) {
                    $c[$gap_id] = [];
                }
                if (!array_key_exists($key, $c[$gap_id])) {
                    $c[$gap_id][$key] = [];
                }

                $c[$gap_id][$key][] = $v;
                return $c;
            },
            []
        );
    }

    private function orderFeedbacksByAnswerInputId(
        array $specific_feedbacks
    ): array {
        return array_reduce(
            $specific_feedbacks,
            function (array $c, SpecificTextFeedback $v): array {
                $parent_id = $v->getParentId()->toString();
                if (!array_key_exists($parent_id, $c)) {
                    $c[$parent_id] = [];
                }

                $c[$parent_id][] = $v;
                return $c;
            },
            []
        );
    }

    private function addFeedbackForNoSelectionToParticipantOutput(
        UIFactory $ui_factory,
        Refinery $refinery,
        array $specific_feedbacks,
        array $participant_output
    ): array {
        $feedback_nothing_selected = array_filter(
            $specific_feedbacks,
            fn(SpecificFeedback $v): bool => TextFeedbackTypes::tryFrom($v->getCondition()) === TextFeedbackTypes::NoResponse
        );

        if ($feedback_nothing_selected !== []) {
            $participant_output[] = $ui_factory->legacy()->content(
                $refinery->string()->markdown()->toHTML()
                    ->transform(
                        $feedback_nothing_selected[0]->getFeedbackText()
                            ->getRawRepresentation()
                    )
            );
        }

        return $participant_output;
    }
}
