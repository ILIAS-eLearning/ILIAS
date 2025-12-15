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

namespace ILIAS\Test\Results\Toplist;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Test\Participants\ParticipantRepository;
use ILIAS\UI\Component\Symbol\Icon\Standard as Icon;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

class DataRetrieval implements \ILIAS\UI\Component\Table\DataRetrieval
{
    public function __construct(
        protected readonly \ilObjTest $test_obj,
        protected readonly TestTopListRepository $repository,
        protected readonly \ilLanguage $lng,
        protected readonly \ilObjUser $user,
        protected readonly UIFactory $ui_factory,
        protected readonly UIRenderer $ui_renderer,
        protected readonly DataFactory $data_factory,
        protected readonly TopListType $list_type,
        protected readonly TopListOrder $order_by,
        protected readonly ParticipantRepository $participant_repository
    ) {
    }

    public function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();
        $iconActor = $this->ui_factory->symbol()->icon()->standard(Icon::USR, 'me');

        $columns = [
            'is_actor' => $column_factory->boolean('', $iconActor, ''),
            'rank' => $column_factory->text($this->lng->txt('toplist_col_rank')),
            'participant' => $column_factory->text($this->lng->txt('toplist_col_participant')),
            'achieved' => $column_factory->date(
                $this->lng->txt('toplist_col_achieved'),
                $this->data_factory->dateFormat()->withTime24($this->data_factory->dateFormat()->standard())
            ),
            'score' => $column_factory->text($this->lng->txt('toplist_col_score')),
            'percentage' => $column_factory->number($this->lng->txt('toplist_col_percentage'))->withUnit('%'),
            'workingtime' => $column_factory->text($this->lng->txt('toplist_col_wtime')),
        ];

        $optional_columns = [
            'achieved' => $this->test_obj->getHighscoreAchievedTS(),
            'score' => $this->test_obj->getHighscoreScore(),
            'percentage' => $this->test_obj->getHighscorePercentage(),
            'workingtime' => $this->test_obj->getHighscoreWTime()
        ];

        $list = [];
        foreach ($columns as $key => $column) {
            if (isset($optional_columns[$key]) && !$optional_columns[$key]) {
                continue;
            }
            $list[$key] = $column->withIsOptional(false, true)->withIsSortable(false);
        }
        return $list;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): \Generator {
        foreach ($this->loadToplistData() as $row) {
            $item = $this->buildBasicItemFromRowArray($row);

            if (isset($row['tstamp']) && in_array('achieved', $visible_column_ids, true)) {
                $item['achieved'] = new \DateTimeImmutable('@' . $row['tstamp']);
            }
            if (isset($row['reached_points']) && in_array('score', $visible_column_ids, true)) {
                $item['score'] = $row['reached_points'] . ' / ' . $row['max_points'];
            }
            if (isset($row['percentage']) && in_array('percentage', $visible_column_ids, true)) {
                $item['percentage'] = $row['percentage'];
            }
            if (isset($row['workingtime']) && in_array('workingtime', $visible_column_ids, true)) {
                $item['workingtime'] = $this->formatTime($row['workingtime']);
            }

            yield $row_builder->buildDataRow((string) $row['rank'], $item);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        // return 0 here to avoid pagination in the table. This is the same behavior as in Ilias 8/9
        return 0;
    }

    private function loadToplistData(): \Generator
    {
        if ($this->list_type === TopListType::GENERAL) {
            return $this->repository->getGeneralToplist($this->test_obj, $this->order_by);
        }

        if ($this->order_by === TopListOrder::BY_SCORE) {
            return $this->repository->getUserToplistByPercentage($this->test_obj, $this->user->getId());
        }

        return $this->repository->getUserToplistByWorkingtime($this->test_obj, $this->user->getId());
    }

    public function formatTime(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    private function buildBasicItemFromRowArray(array $row): array
    {
        if ($row['rank'] === '...') {
            return [
                'rank' => '...',
                'is_actor' => false
            ];
        }

        return [
            'rank' => "{$row['rank']}.",
            'participant' => $this->test_obj->isHighscoreAnon() && (int) $row['usr_id'] !== $this->user->getId()
                ? '-, -'
                : $this->participant_repository->getParticipantByActiveId($this->test_obj->getTestId(), $row['active_id'])->getDisplayName($this->lng),
            'is_actor' => isset($row['usr_id']) && ((int) $row['usr_id'] === $this->user->getId())
        ];
    }
}
