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
                $environment->getRefinery(),
                $this->uuid_factory,
                $environment->getAnswerFormProperties(),
                $this
            )
        );
    }

    #[\Override]
    public function onAnswerFormClone(
        UuidFactory $uuid_factory,
        Properties $old_answer_form_properties,
        Properties $new_answer_form_properties
    ): static {
        /** @var Gaps $old_gaps */
        $old_gaps = $old_answer_form_properties->getGaps();
        /** @var Gaps $new_gaps */
        $new_gaps = $new_answer_form_properties->getGaps();

        return array_reduce(
            $this->getSpecificFeedbacks(),
            function (
                TextFeedback $c,
                SpecificTextFeedback $v
            ) use ($uuid_factory, $old_gaps, $new_gaps): self {
                $old_gap = $old_gaps->getGapById($v->getParentId());
                try {
                    $answer_option_position = $old_gap
                        ->getAnswerOptions()
                        ->getAnswerOptionById(
                            $uuid_factory->fromString($v->getCondition())
                        )->getPosition();
                    $new_answer_option_id = $new_gaps
                        ->getGapByPosition($old_gap->getPosition())
                        ->getAnswerOptions()
                        ->getAnswerOptionForPositionOrNew($answer_option_position)
                        ->getAnswerOptionId();

                    return $c->withoutSpecificFeedback($v)
                        ->withSpecificFeedback(
                            $v->withCondition(
                                $new_answer_option_id->toString()
                            )
                        );
                } catch (InvalidUuidStringException $e) {
                    return $c;
                }
            },
            clone $this
        );
    }

    #[\Override]
    public function onAnswerFormUpdate(
        Properties $answer_form_properties
    ): static {
        /** @var Gaps $gaps */
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

    #[\Override]
    public function specificFeedbacksDisplayLegacyTexts(): bool
    {
        return false;
    }

    #[\Override]
    protected function getSpecificFeedbackParticipantOutput(
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
                $gap_id = $v[0]->getParentId();

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
    protected function getSpecificFeedbackClientSideEndPoint(): string
    {
        return 'il.questions.cloze.specificTextFeedback';
    }

    #[\Override]
    protected function getSpecificFeedbackForClientSideCode(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory,
        Properties $properties
    ): array {
        $specific_feedbacks = $this->getSpecificFeedbacks();
        if ($specific_feedbacks === []) {
            return [];
        }

        return array_map(
            fn(array $v): array => array_reduce(
                $v,
                function (array $c, SpecificTextFeedback $v) use ($refinery): array {
                    $c[$v->getCondition()] = $v->getFeedbackTextForPresentation(
                        $refinery
                    );
                    return $c;
                },
                []
            ),
            $this->orderFeedbacksByAnswerInputId($specific_feedbacks)
        );
    }

    public function buildSpecificFeedbackOverviewTableArray(): array
    {
        return array_reduce(
            $this->getSpecificFeedbacks(),
            function (array $c, SpecificTextFeedback $v): array {
                $gap_id = $v->getParentId()->toString();
                $key = $v->getFeedbackTextForKey();
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
            fn(SpecificTextFeedback $v): bool => TextFeedbackTypes::tryFrom($v->getCondition()) === TextFeedbackTypes::NoResponse
        );

        if ($feedback_nothing_selected !== []) {
            $participant_output[] = $ui_factory->legacy()->content(
                $feedback_nothing_selected[0]->getFeedbackTextForPresentation(
                    $refinery
                )
            );
        }

        return $participant_output;
    }
}
