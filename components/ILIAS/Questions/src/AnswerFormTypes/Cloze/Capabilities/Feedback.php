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

use ILIAS\Questions\AnswerForm\Capabilities\Feedback\Feedback as FeedbackBase;
use ILIAS\Questions\AnswerForm\Capabilities\Feedback\SpecificFeedback;
use ILIAS\Questions\AnswerForm\Capabilities\Feedback\OverviewTable;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Question\Response;
use ILIAS\Data\Text\Factory as TextFactory;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;

class Feedback extends FeedbackBase
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
        return new FeedbackOverviewTable(
            $this->uuid_factory,
            $this->text_factory,
            new FeedbackOverviewDataRetrieval(
                $this->uuid_factory,
                $environment->getRefinery(),
                $environment->getAnswerFormProperties(),
                $this
            )
        );
    }

    #[\Override]
    public function getSpecificFeedbackParticipantOutput(
        Response $response,
        string $answer_id
    ): array {

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
                Feedback $c,
                SpecificFeedback $v
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
            function (array $c, SpecificFeedback $v): array {
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
}
