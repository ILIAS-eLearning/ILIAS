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

use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\SpecificTextFeedback;
use ILIAS\Questions\AnswerFormTypes\Cloze\Properties\Properties;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Component\Table\DataRowBuilder;

class TextFeedbackOverviewDataRetrieval implements DataRetrieval
{
    /**
     * @var array{
     *  string: array<SpecificTextFeedback>
     * }
     */
    private readonly array $feedback;

    public function __construct(
        private readonly Language $lng,
        private readonly Refinery $refinery,
        private readonly UuidFactory $uuid_factory,
        private readonly Properties $answer_form_properties,
        TextFeedback $feedback
    ) {
        $this->feedback = $feedback->buildSpecificFeedbackOverviewTableArray();
    }

    #[\Override]
    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): \Generator {
        foreach ($this->feedback as $gap_feedbacks) {
            yield from $this->buildColumnValues(
                $row_builder,
                $gap_feedbacks
            );
        }
    }

    #[\Override]
    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->feedback);
    }

    public function getSpecificFeedbacksForRowId(
        string $table_row_id
    ): array {
        $ids = explode('_', $table_row_id);
        $feedbacks_for_gap = array_reduce(
            $this->feedback[array_shift($ids)],
            fn(array $c, array $v): array => [...$c, ...$v],
            []
        );
        return array_filter(
            $feedbacks_for_gap,
            fn(SpecificTextFeedback $v): bool => in_array($v->getId(), $ids)
        );
    }

    private function buildColumnValues(
        DataRowBuilder $row_builder,
        array $gap_feedbacks
    ): \Generator {
        foreach ($gap_feedbacks as $feedbacks) {
            yield $this->buildTableRow(
                $row_builder,
                $feedbacks
            );
        }
    }

    private function buildTableRow(
        DataRowBuilder $row_builder,
        array $feedbacks
    ): DataRow {
        $gap_id = $feedbacks[0]->getParentId();
        $gap = $this->answer_form_properties->getGaps()->getGapById($gap_id);

        $keys = [$gap_id->toString()];
        $answer_options = [];
        foreach ($feedbacks as $feedback) {
            $keys[] = $feedback->getId()->toString();
            $answer_options[] = $gap->getType()->getLabelForValue(
                $this->uuid_factory,
                $gap,
                $feedback->getCondition()
            );
        }

        return $row_builder->buildDataRow(
            implode('_', $keys),
            [
                'gap' => $gap->buildShortenedGapRepresentation(),
                'answer_options' => implode('<br>', $answer_options),
                'feedback' => $this->refinery->string()->markdown()->toHTML()->transform(
                    $feedbacks[0]->getFeedbackText()
                        ->getRawRepresentation()
                )
            ]
        );
    }
}
