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

namespace ILIAS\Calendar\Manage;

use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use Generator;
use ilLanguage;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\DataRetrieval as DataRetrievalInterface;
use ilCalendarCategories;
use ilObject;
use ilCalendarCategory;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Table\DataRow;
use ilCalendarActions;

class DataRetrieval implements DataRetrievalInterface
{
    public function __construct(
        protected ilCalendarCategories $categories,
        protected ilCalendarActions $actions,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory
    ) {
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
        foreach ($this->categories->getCategoriesInfo() as $info) {
            $datum = [];
            $datum['title'] = $info['title'];
            $datum['type'] = $info['type'];
            $datum['obj_id'] = $info['obj_id'];
            $datum['obj_type'] =
                in_array($info['type'], [ilCalendarCategory::TYPE_OBJ, ilCalendarCategory::TYPE_BOOK], true) ?
                ilObject::_lookupType($info['obj_id']) : '';
            $datum['type_sortable'] = ilCalendarCategory::lookupCategorySortIndex($info['type']) .
                ($info['type'] === ilCalendarCategory::TYPE_OBJ ? $datum['obj_type'] : ''); // Append object type to make type sortable
            $data[$info['cat_id']] = $datum;
        }

        $order_field = array_keys($order->get())[0];
        $order_direction = $order->get()[$order_field];
        $order_field = match($order_field) {
            TableBuilder::TYPE_COLUMN => 'type_sortable',
            default => 'title'
        };
        uasort($data, fn($a, $b) => strtolower((string) $a[$order_field]) <=> strtolower((string) $b[$order_field]));
        if ($order_direction === Order::DESC) {
            $data = array_reverse($data, true);
        }
        $data = array_slice($data, $range->getStart(), $range->getLength(), true);

        $usr_icon = $this->ui_factory->symbol()->icon()->standard('usr', $this->lng->txt('obj_usr'));
        $role_icon = $this->ui_factory->symbol()->icon()->standard('role', $this->lng->txt('obj_role'));

        foreach ($data as $id => $datum) {
            $row_datum = [TableBuilder::TITLE_COLUMN => $datum['title']];
            if ($icon = $this->buildTypeIcon($datum['type'], $datum['obj_type'])) {
                $row_datum[TableBuilder::TYPE_COLUMN] = $icon;
            }
            $row = $row_builder->buildDataRow(
                (string) $id,
                $row_datum
            );
            yield $this->disableInactiveActions($id, $row);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->categories->getCategoriesInfo());
    }

    protected function buildTypeIcon(int $type, string $obj_type): ?Icon
    {
        $f = $this->ui_factory->symbol()->icon();
        $size = Icon::MEDIUM;
        return match ($type) {
            ilCalendarCategory::TYPE_GLOBAL => $f->custom(
                'assets/images/standard/icon_calg.svg',
                $this->lng->txt('cal_type_system'),
                $size
            ),
            ilCalendarCategory::TYPE_CH => $f->custom(
                'assets/images/standard/icon_calch.svg',
                $this->lng->txt('cal_ch_ch'),
                $size
            ),
            ilCalendarCategory::TYPE_USR => $f->standard(
                'usr',
                $this->lng->txt('cal_type_personal'),
                $size
            ),
            ilCalendarCategory::TYPE_OBJ => $f->standard(
                $obj_type,
                $this->lng->txt('cal_type_' . $type),
                $size
            ),
            ilCalendarCategory::TYPE_BOOK => $f->standard(
                'book',
                $this->lng->txt('cal_type_' . $type),
                $size
            ),
            default => null
        };
    }

    protected function disableInactiveActions(int $id, DataRow $row): DataRow
    {
        if (!$this->actions->checkSettingsCal($id)) {
            $row = $row->withDisabledAction(TableBuilder::ACTION_EDIT);
        }
        if (!$this->actions->checkAddEvent($id)) {
            $row = $row->withDisabledAction(TableBuilder::ACTION_IMPORT);
        }
        if (!$this->actions->checkShareCal($id)) {
            $row = $row->withDisabledAction(TableBuilder::ACTION_SHARE);
        }
        if (!$this->actions->checkSynchronizeCal($id)) {
            $row = $row->withDisabledAction(TableBuilder::ACTION_SYNCHRONIZE);
        }
        if (!$this->actions->checkDeleteCal($id)) {
            $row = $row->withDisabledAction(TableBuilder::ACTION_DELETE);
        }
        return $row;
    }
}
