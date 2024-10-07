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

namespace ILIAS\Forum\Statistics;

use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Factory as UIFactory;
use Psr\Http\Message\ServerRequestInterface as HttpRequest;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ilLPStatus;
use ilStr;
use ilLPStatusWrapper;
use ilObjectLP;
use ilLPStatusIcons;
use ilLanguage;
use ilObjUser;
use ilForumProperties;
use ilObjForum;

class ForumStatisticsTable implements DataRetrieval
{
    private bool $has_active_lp = false;
    /** @var int[] */
    private array $completed = [];
    /** @var int[] */
    private array $failed = [];
    /** @var int[] */
    private array $in_progress = [];
    /**
     * @var list<array<string, mixed>>|null
     */
    private ?array $records = null;
    private readonly ilLPStatusIcons $icons;

    public function __construct(
        private readonly ilObjForum $forum,
        private readonly ilForumProperties $obj_properties,
        private readonly bool $has_general_lp_access,
        private readonly bool $has_rbac_or_position_access,
        private readonly ilObjUser $actor,
        private readonly UIFactory $ui_factory,
        private readonly HttpRequest $request,
        private readonly ilLanguage $lng,
    ) {
        $this->icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_LONG);

        $lp = ilObjectLP::getInstance($forum->getId());
        if ($lp->isActive()) {
            $this->has_active_lp = true;
        }

        if ($this->has_active_lp && $this->has_general_lp_access) {
            $this->lng->loadLanguageModule('trac');
            $this->completed = ilLPStatusWrapper::_lookupCompletedForObject($forum->getId());
            $this->in_progress = ilLPStatusWrapper::_lookupInProgressForObject($forum->getId());
            $this->failed = ilLPStatusWrapper::_lookupFailedForObject($forum->getId());
        }
    }

    public function getComponent(): DataTable
    {
        return $this->ui_factory
            ->table()
            ->data(
                $this->lng->txt('frm_moderators'),
                $this->getColumns(),
                $this
            )
            ->withId(self::class . '_' . $this->forum->getId())
            ->withRequest($this->request);
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Column\Column>
     */
    private function getColumns(): array
    {
        $columns = [
            'ranking' => $this->ui_factory->table()->column()->number(
                $this->lng->txt('frm_statistics_ranking')
            )->withIsSortable(true),
            'login' => $this->ui_factory->table()->column()->text(
                $this->lng->txt('login')
            )->withIsSortable(true),
            'lastname' => $this->ui_factory->table()->column()->text(
                $this->lng->txt('lastname')
            )->withIsSortable(true),
            'firstname' => $this->ui_factory->table()->column()->text(
                $this->lng->txt('firstname')
            )->withIsSortable(true),
        ];
        if ($this->has_active_lp && $this->has_general_lp_access) {
            $columns['progress'] = $this->ui_factory->table()->column()->status(
                $this->lng->txt('learning_progress')
            )->withIsSortable(false);
        }

        return $columns;
    }

    private function initRecords(): void
    {
        if ($this->records === null) {
            $this->records = [];
            $data = $this->forum->Forum->getUserStatistics($this->obj_properties->isPostActivationEnabled());
            $counter = 0;
            foreach ($data as $row) {
                $this->records[$counter]['usr_id'] = $row['usr_id'];
                $this->records[$counter]['ranking'] = $row['num_postings'];
                $this->records[$counter]['login'] = $row['login'];
                $this->records[$counter]['lastname'] = $row['lastname'];
                $this->records[$counter]['firstname'] = $row['firstname'];
                if ($this->has_active_lp && $this->has_general_lp_access) {
                    $this->records[$counter]['progress'] = $this->getProgressStatus($row['usr_id']);
                }
                ++$counter;
            }
        }
    }

    /**
     * @param list<array<string, mixed>> $records
     * @return list<array<string, mixed>>
     */
    private function sortedRecords(array $records, Order $order): array
    {
        [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);

        usort($records, static function ($left, $right) use ($order_field): int {
            if ($order_field === 'ranking') {
                return $left[$order_field] <=> $right[$order_field];
            }

            return ilStr::strCmp($left[$order_field], $right[$order_field]);
        });

        if ($order_direction === 'DESC') {
            $records = array_reverse($records);
        }

        return $records;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getRecords(Range $range, Order $order): array
    {
        $this->initRecords();
        $records = $this->sortedRecords($this->records, $order);

        return $this->limitRecords($records, $range);
    }

    /**
     * @param list<array<string, mixed>> $records
     * @return list<array<string, mixed>>
     */
    private function limitRecords(array $records, Range $range): array
    {
        return array_slice($records, $range->getStart(), $range->getLength());
    }

    public function getRows(
        \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters,
    ): \Generator {
        $records = $this->getRecords($range, $order);
        foreach ($records as $record) {
            $row_id = (string) $record['usr_id'];
            yield $row_builder->buildDataRow($row_id, $record);
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        $this->initRecords();

        return count($this->records);
    }

    private function getProgressStatus(int $user_id): string
    {
        $icon = '';
        if ($this->has_active_lp &&
            $this->has_general_lp_access &&
            ($this->has_rbac_or_position_access || $this->actor->getId() === $user_id)) {
            $icon = match (true) {
                in_array($user_id, $this->completed, false) => $this->icons->renderIconForStatus(
                    ilLPStatus::LP_STATUS_COMPLETED_NUM
                ),
                in_array($user_id, $this->in_progress, false) => $this->icons->renderIconForStatus(
                    ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
                ),
                in_array($user_id, $this->failed, false) => $this->icons->renderIconForStatus(
                    ilLPStatus::LP_STATUS_FAILED_NUM
                ),
                default => $this->icons->renderIconForStatus(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM),
            };
        }

        return $icon;
    }
}
