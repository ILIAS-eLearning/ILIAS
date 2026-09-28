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

namespace ILIAS\Calendar\Shared\Outgoing;

use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use Generator;
use ilCalendarShared;
use ilObjUser;
use ilObject;
use ilLanguage;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Calendar\Shared\DataRetrievalInterface;

class DataRetrieval implements DataRetrievalInterface
{
    protected ilCalendarShared $shared;

    public function __construct(
        protected int $calendar_id,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory
    ) {
    }

    protected function getShared(): ilCalendarShared
    {
        return $this->shared ??= new ilCalendarShared($this->calendar_id);
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
        $data = [];
        foreach ($this->getShared()->getShared() as $item) {
            $datum = [];
            $obj_id = (int) $item['obj_id'];
            switch ($item['obj_type']) {
                case ilCalendarShared::TYPE_USR:
                    $datum[TableBuilder::TYPE_COLUMN] = 'usr';
                    $name = ilObjUser::_lookupName($obj_id);
                    $datum[TableBuilder::TITLE_COLUMN] = $name['lastname'] . ', ' . $name['firstname'];
                    $datum[TableBuilder::DESCRIPTION_COLUMN] = '';
                    break;

                case ilCalendarShared::TYPE_ROLE:
                    $datum[TableBuilder::TYPE_COLUMN] = 'role';
                    $datum[TableBuilder::TITLE_COLUMN] = ilObject::_lookupTitle($obj_id);
                    $datum[TableBuilder::DESCRIPTION_COLUMN] = ilObject::_lookupDescription($obj_id);
                    break;
            }
            $datum[TableBuilder::WRITABLE_COLUMN] = (bool) $item['writable'];

            $data[$obj_id] = $datum;
        }

        $order_field = array_keys($order->get())[0];
        $order_direction = $order->get()[$order_field];
        uasort($data, fn($a, $b) => strtolower((string) $a[$order_field]) <=> strtolower((string) $b[$order_field]));
        if ($order_direction === Order::DESC) {
            $data = array_reverse($data, true);
        }
        $data = array_slice($data, $range->getStart(), $range->getLength(), true);

        $usr_icon = $this->ui_factory->symbol()->icon()->standard('usr', $this->lng->txt('obj_usr'));
        $role_icon = $this->ui_factory->symbol()->icon()->standard('role', $this->lng->txt('obj_role'));

        foreach ($data as $id => $datum) {
            $datum[TableBuilder::TYPE_COLUMN] = match ($datum[TableBuilder::TYPE_COLUMN]) {
                'usr' => $usr_icon,
                'role' => $role_icon
            };
            $datum[TableBuilder::WRITABLE_COLUMN] = match ($datum[TableBuilder::WRITABLE_COLUMN]) {
                true => $this->lng->txt('cal_shared_access_read_write'),
                false => $this->lng->txt('cal_shared_access_read_only')
            };
            yield $row_builder->buildDataRow(
                (string) $id,
                $datum
            );
        }
    }

    /**
     * @return int[]
     */
    public function getAllIDs(): array
    {
        $ids = [];
        foreach ($this->getShared()->getShared() as $item) {
            $ids[] = (int) $item['obj_id'];
        }
        return $ids;
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getShared()->getShared());
    }
}
