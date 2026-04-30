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

namespace ILIAS\Bibliographic\Field;

use ILIAS\Data\Order;
use ILIAS\UI\Component\Table as I;
use ILIAS\UI\Component\Table\OrderingRowBuilder;

/**
 * Class DataRetrieval
 *
 */
class DataRetrieval implements I\OrderingRetrieval
{
    private \ilLanguage $lng;

    public function __construct(
        protected \ilBiblAdminFactoryFacadeInterface $facade,
        private bool $has_write_access
    ) {
        global $DIC;
        $this->lng = $DIC['lng'];
    }

    public function getRows(
        OrderingRowBuilder $row_builder,
        array $visible_column_ids,
    ): \Generator {
        $records = $this->getRecords();
        foreach ($records as $idx => $record) {
            $row_id = (string) $record['id'];
            $field = $this->facade->fieldFactory()->findById($record['id']);
            $record['data_type'] = $this->facade->translationFactory()->translate($field);
            $record['is_standard_field'] = $field->isStandardField() ? $this->lng->txt('standard') : $this->lng->txt('custom');
            yield $row_builder->buildOrderingRow($row_id, $record)
                ->withDisabledAction('translate', !$this->has_write_access);
        }
    }

    protected function getRecords(?Order $order = null): array
    {
        $records = $this->facade->fieldFactory()->filterAllFieldsForTypeAsArray($this->facade->type());
        if (!is_null($order)) {
            [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value): array => [$key, $value]);
            usort($records, fn($a, $b): int => $a[$order_field] <=> $b[$order_field]);
            if ($order_direction === 'DESC') {
                return array_reverse($records);
            }
        }
        return $records;
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->facade->fieldFactory()->getAvailableFieldsForObjId($this->facade->iliasObjId()));
    }
}
