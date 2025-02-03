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

use ILIAS\UI\Component\Table as DataTableInterface;
use ILIAS\UI\Implementation\Component\Table as DataTable;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

class ilLanguageStatisticsTable implements DataTableInterface\DataRetrieval
{
    protected ?ilObject $object = null;
    protected ILIAS\UI\Factory $ui_factory;
    protected ilLanguage $lng;

    public function __construct(
        ?ilObject $object,
        ILIAS\UI\Factory $ui_factory,
        ilLanguage $lng,
    ) {
        $this->object = $object;
        $this->ui_factory = $ui_factory;
        $this->lng = $lng;
    }

    public function getTable(): DataTable\Data
    {
        return $this->ui_factory->table()->data(
            '',
            $this->getColums(),
            $this
        );
    }

    protected function getColums(): array
    {
        $f = $this->ui_factory->table()->column();
        return [
            'module' => $f->text(ucfirst($this->lng->txt("module"))),
            'all' => $f->number($this->lng->txt("language_scope_global")),
            'changed' => $f->number($this->lng->txt("language_scope_local")),
            'unchanged' => $f->number($this->lng->txt("language_scope_unchanged")),
        ];
    }

    public function getItems(?Range $range = null, ?Order $order = null): array
    {
        $modules = ilObjLanguageExt::_getModules($this->object->key);

        $data = [];
        $total = [];
        foreach ($modules as $module) {
            $row = [];
            $row["module"] = $module;
            $row["all"] = count($this->object->getAllValues([$module]));
            $row["changed"] = count($this->object->getChangedValues([$module]));
            $row["unchanged"] = $row["all"] - $row["changed"];
            isset($total["all"]) ? $total["all"] += $row["all"] : $total["all"] = $row["all"];
            isset($total["changed"]) ? $total["changed"] += $row["changed"] : $total["changed"] = $row["changed"];
            isset($total["unchanged"]) ? $total["unchanged"] += $row["unchanged"] : $total["unchanged"] = $row["unchanged"];
            $data[] = $row;
        }
        $total["module"] = $this->lng->txt("language_all_modules");
        $total["all"] = $total["all"];
        $total["changed"] = $total["changed"];
        $total["unchanged"] = $total["unchanged"];
        array_unshift($data, $total);

        if($order) {
            list($order_field, $order_direction) = $order->join([], fn($ret, $key, $value) => [$key, $value]);
            usort(
                $data,
                static function ($a, $b) use ($order_field) {
                    switch ($order_field) {
                        case 'module':
                            $a_aspect = $a["module"];
                            $b_aspect = $b["module"];
                            break;
                        case 'all':
                            $a_aspect = $a["all"];
                            $b_aspect = $b["all"];
                            break;
                        case 'changed':
                            $a_aspect = $a["changed"];
                            $b_aspect = $b["changed"];
                            break;
                        case 'unchanged':
                            $a_aspect = $a["unchanged"];
                            $b_aspect = $b["unchanged"];
                            break;
                    }
                    return $a_aspect <=> $b_aspect;
                }
            );
            if ($order_direction === 'DESC') {
                $data = array_reverse($data);
            }
        }

        if ($range) {
            $data = array_slice($data, $range->getStart(), $range->getLength());
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getRows(
        \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        \ILIAS\Data\Range $range,
        \ILIAS\Data\Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        foreach ($this->getItems($range, $order) as $idx => $record) {
            $obj_id = (string) $idx;
            $record['module'] = $record['module'];
            $record['all'] = $record['all'];
            $record['changed'] = $record['changed'];
            $record['unchanged'] = $record['unchanged'];

            yield $row_builder->buildDataRow($obj_id, $record);
        }
    }

    /**
     * @inheritDoc
     */
    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->getItems());
    }
}
