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

namespace ILIAS\Session\Overview\Table;

use Generator;
use ilAccess;
use ilEventParticipants;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Table\DataRetrieval as DataRetrievalInterface;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ilObjectFactory;
use ilObjSession;
use ilObjUser;
use ilTree;

class DataRetrieval implements DataRetrievalInterface
{
    protected const string DATA_KEY_NAME = 'name';
    protected const string DATA_KEY_LOGIN = 'login';
    protected const string DATA_KEY_ROW_ID = 'row_id';
    protected const string DATA_KEY_EVENTS = 'events';

    /** @var int[] */
    protected readonly array $session_member_ids;
    protected array $items;
    protected array $event_column_names;

    public function __construct(
        protected readonly UIServices $ui,
        protected readonly ilTree $tree,
        protected readonly ilAccess $access,
        protected readonly int $crs_ref_id,
        int ...$session_member_ids
    ) {
        $this->session_member_ids = $session_member_ids;
    }

    protected function initItems(): void
    {
        if (isset($this->items)) {
            return;
        }
        $events = [];
        $event_ids = $this->tree->getSubtree($this->tree->getNodeData($this->crs_ref_id), false, ['sess']);
        foreach ($event_ids as $event_id) {
            $tmp_event = ilObjectFactory::getInstanceByRefId($event_id, false);
            if (
                $tmp_event instanceof ilObjSession &&
                $this->access->checkAccess('manage_members', '', $event_id)
            ) {
                // sort by date of 1st appointment
                $events[$tmp_event->getFirstAppointment()->getStartingTime() . '_' . $tmp_event->getId()] = $tmp_event;
            }
        }
        ksort($events);
        $event_objects = array_values($events);
        $items = [];
        foreach ($this->session_member_ids as $user_id) {
            $name = ilObjUser::_lookupName($user_id);
            $items[$user_id] = [
                self::DATA_KEY_NAME => $name['lastname'] . ', ' . $name['firstname'],
                self::DATA_KEY_LOGIN => $name['login'],
                self::DATA_KEY_ROW_ID => $user_id,
                self::DATA_KEY_EVENTS => []
            ];
        }
        foreach ($event_objects as $event_obj) {
            /** @var ilObjSession $event_obj */
            $users_of_event = ilEventParticipants::_getParticipated($event_obj->getID());
            $column_name = 'event_' . $event_obj->getId();
            foreach ($this->session_member_ids as $user_id) {
                $items[$user_id][self::DATA_KEY_EVENTS][$column_name] = array_key_exists($user_id, $users_of_event);
            }
            $this->event_column_names[$column_name] = $event_obj->getFirstAppointment()->appointmentToString();
        }
        $this->items = $items;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $this->initItems();

        [$column_name, $direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
        $comparator = match ($column_name) {
            Handler::TABLE_COL_NAME => fn(array $f1, array $f2) => strcasecmp($f1[self::DATA_KEY_NAME], $f2[self::DATA_KEY_NAME]),
            Handler::TABLE_COL_LOGIN => fn(array $f1, array $f2) => strcasecmp($f1[self::DATA_KEY_LOGIN], $f2[self::DATA_KEY_LOGIN]),
            default => fn(array $f1, array $f2) => $f1[self::DATA_KEY_EVENTS][$column_name] <=> $f2[self::DATA_KEY_EVENTS][$column_name]
        };
        $rows = $this->items;
        uasort($rows, $comparator);
        if ($direction === "DESC") {
            $rows = array_reverse($rows, true);
        }
        $rows = array_slice($rows, $range->getStart(), $range->getLength(), true);
        $icons = [
            $this->ui->factory()->symbol()->icon()->custom('assets/images/standard/icon_unchecked.svg', '', 'small'),
            $this->ui->factory()->symbol()->icon()->custom('assets/images/standard/icon_checked.svg', '', 'small')
        ];
        foreach ($rows as $row) {
            $row_data = [
                Handler::TABLE_COL_NAME => $row[self::DATA_KEY_NAME],
                Handler::TABLE_COL_LOGIN => $row[self::DATA_KEY_LOGIN]
            ];
            $event_row_entries = array_map(fn($col_value) => $icons[(int) $col_value], array_values($row[self::DATA_KEY_EVENTS]));
            $event_row_entries = array_combine(array_keys($row[self::DATA_KEY_EVENTS]), $event_row_entries);
            $row_data = array_merge($row_data, $event_row_entries);
            yield $row_builder->buildDataRow(
                $row[self::DATA_KEY_ROW_ID] . '',
                $row_data
            );
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        $this->initItems();
        return count($this->items);
    }

    public function getEventColumnNames(): array
    {
        $this->initItems();
        return $this->event_column_names;
    }
}
