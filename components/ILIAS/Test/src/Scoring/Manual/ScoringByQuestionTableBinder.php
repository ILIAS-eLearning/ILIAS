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

namespace ILIAS\Test\Scoring\Manual;

use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Language\Language;

class ScoringByQuestionTableBinder implements DataRetrieval
{
    private ?array $participant_data = null;
    private array $filter_data = [];

    public function __construct(
        private readonly Language $lng,
        private readonly \DateTimeZone $timezone,
        private readonly \ilTestParticipantAccessFilterFactory $participant_access_filter_factory,
        private readonly \ilObjTest $test_obj,
        private readonly int $question_id
    ) {
    }

    public function withFilterData(array $filter_data): self
    {
        $clone = clone $this;
        $clone->filter_data = $filter_data;
        return $clone;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        if ($this->participant_data === null) {
            $this->participant_data = $this->getFilteredData($this->question_id);
        }
        $this->sortData($order);
        $data = array_slice($this->participant_data, $range->getStart(), $range->getLength());
        foreach ($data as $row) {
            yield $row_builder->buildDataRow(
                array_shift($row),
                $row
            );
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        if ($this->participant_data === null) {
            $this->participant_data = $this->getFilteredData($this->question_id);
        }
        return count($this->participant_data);
    }

    public function getMaxAttempts(): int
    {
        return $this->test_obj->getMaxPassOfTest();
    }

    private function getFilteredData(int $question_id): array
    {
        $complete_feedback = $this->test_obj->getCompleteManualFeedback($question_id);
        $data = $this->test_obj->getCompleteEvaluationData();

        $participants = $data->getParticipants();
        $accessible_user_ids = call_user_func(
            $this->participant_access_filter_factory->getScoreParticipantsUserFilter($this->test_obj->getRefId()),
            $this->buildUserIdArrayFromParticipants($participants)
        );
        $accessible_participants = array_filter(
            $participants,
            static fn(\ilTestEvaluationUserData $v): bool => in_array($v->getUserID(), $accessible_user_ids)
        );

        return array_reduce(
            array_keys($accessible_participants),
            $this->getDataRowClosure($question_id, $accessible_participants, $complete_feedback),
            []
        );
    }

    private function sortData(
        Order $order
    ): void {
        $key = key($order->get());
        $direction = $order->get()[$key];
        usort(
            $this->participant_data,
            static function (array $a, array $b) use ($key, $direction): int {
                $left = $a[$key] ?? null;
                $right = $b[$key] ?? null;
                if ($direction === 'ASC') {
                    return $left <=> $right;
                }
                return $right <=> $left;
            }
        );
    }

    private function getDataRowClosure(
        int $question_id,
        array $filtered_participants,
        array $complete_feedback
    ): \Closure {
        return function (
            array $c,
            int $active_id
        ) use ($question_id, $filtered_participants, $complete_feedback): array {
            $array_of_attempts = $this->buildFilteredArrayOfAttempts(
                $question_id,
                $active_id,
                $filtered_participants,
                $complete_feedback
            );
            return [...$c, ...$array_of_attempts];
        };
    }

    private function buildFilteredArrayOfAttempts(
        int $question_id,
        int $active_id,
        array $filtered_participants,
        array $complete_feedback
    ): array {
        return array_reduce(
            $filtered_participants[$active_id]->getPasses(),
            function (
                array $c,
                \ilTestEvaluationPassData $pd
            ) use ($question_id, $active_id, $filtered_participants, $complete_feedback): array {
                $question_result = $pd->getAnsweredQuestionByQuestionId($question_id);
                $feedback_data = $complete_feedback[$active_id][$pd->getPass()][$question_id] ?? [];
                if ($this->isFilteredAttempt($pd, $question_result, $feedback_data)) {
                    return $c;
                }

                $current_participant = $filtered_participants[$active_id];

                $row = [
                    "{$active_id}_{$pd->getPass()}",
                    ScoringByQuestionTable::COLUMN_NAME => $this->buildParticipantName($current_participant),
                    ScoringByQuestionTable::COLUMN_ATTEMPT => $pd->getPass() + 1,
                    ScoringByQuestionTable::COLUMN_POINTS_REACHED => $question_result['reached'] ?? 0.0,
                    ScoringByQuestionTable::COLUMN_POINTS_AVAILABLE => $current_participant->getQuestionByAttemptAndId($pd->getPass(), $question_id)['points'] ?? 0.0,
                    ScoringByQuestionTable::COLUMN_FEEDBACK => $feedback_data['feedback'] ?? '',
                    ScoringByQuestionTable::COLUMN_FINALIZED => isset($feedback_data['finalized_evaluation']) && $feedback_data['finalized_evaluation'] === 1,
                    ScoringByQuestionTable::COLUMN_FINALIZED_BY => $this->buildFinalizedByName($feedback_data)
                ];

                if (isset($feedback_data['finalized_tstamp'])
                    && $feedback_data['finalized_tstamp'] !== 0) {
                    $row[ScoringByQuestionTable::COLUMN_FINALIZED_ON] = (new \DateTimeImmutable(
                        '@' . $feedback_data['finalized_tstamp']
                    )
                    )->setTimezone($this->timezone);
                }
                $c[] = $row;

                return $c;
            },
            []
        );
    }

    private function isFilteredAttempt(
        \ilTestEvaluationPassData $pd,
        ?array $question_info,
        array $feedback_data
    ): bool {
        if ($this->filter_data === []) {
            return false;
        }

        if ($this->filter_data[ScoringByQuestionTable::FILTER_FIELD_ONLY_ANSWERED] === '1'
                && ($question_info === null || $question_info['isAnwered'] === false)
            || $this->filter_data[ScoringByQuestionTable::COLUMN_ATTEMPT] !== ''
                && $pd->getPass() !== (int) $this->filter_data[ScoringByQuestionTable::COLUMN_ATTEMPT]
            || $this->filter_data[ScoringByQuestionTable::COLUMN_FINALIZED] === '1'
                && (!isset($feedback_data['finalized_evaluation']) || $feedback_data['finalized_evaluation'] !== 1)
            || $this->filter_data[ScoringByQuestionTable::COLUMN_FINALIZED] === '2'
                && isset($feedback_data['finalized_evaluation']) && $feedback_data['finalized_evaluation'] === 1) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param array<\ilTestEvaluationsUserData> $participants
     * @return array<int>
     */
    private function buildUserIdArrayFromParticipants(array $participants): array
    {
        return array_reduce(
            $participants,
            static function (array $c, \ilTestEvaluationUserData $v): array {
                if ($v->getUserID() === null) {
                    return $c;
                }

                $c[] = $v->getUserID();
                return $c;
            },
            []
        );
    }

    private function buildParticipantName(\ilTestEvaluationUserData $participant_data): string
    {
        if ($this->test_obj->getAnonymity()) {
            return $this->lng->txt('anonymous');
        }
        return $participant_data->getName();
    }

    private function buildFinalizedByName(array $feedback_data): string
    {
        if (isset($feedback_data['finalized_by_usr_id'])
            && $feedback_data['finalized_by_usr_id'] !== '') {
            return \ilObjUser::_lookupFullname($feedback_data['finalized_by_usr_id']);
        }
        return '';
    }
}
