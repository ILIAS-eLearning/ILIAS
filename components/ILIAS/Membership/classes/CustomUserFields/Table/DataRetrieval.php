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

namespace ILIAS\Membership\CustomUserFields\Table;

use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use Generator;
use ilCourseDefinedFieldDefinition;
use ILIAS\UI\Component\Table\DataRetrieval as BaseDataRetrieval;
use ilLanguage;
use ILIAS\UI\Factory as UIFactory;

class DataRetrieval implements BaseDataRetrieval
{
    /** @var ilCourseDefinedFieldDefinition[] */
    protected array $fields;

    public function __construct(
        protected int $obj_id,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory
    ) {
    }

    /**
     * @return ilCourseDefinedFieldDefinition[]
     */
    protected function getFields(): array
    {
        return $this->fields ??= ilCourseDefinedFieldDefinition::_getFields($this->obj_id);
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
        $fields = $this->getFields();
        $data = [];

        foreach ($fields as $def) {
            $type = '';
            switch ($def->getType()) {
                case ilCourseDefinedFieldDefinition::IL_CDF_TYPE_SELECT:
                    $type = $this->lng->txt('ps_type_select');
                    break;
                case ilCourseDefinedFieldDefinition::IL_CDF_TYPE_TEXT:
                    $type = $this->lng->txt('ps_type_text');
                    break;
            }

            $data[] = [
                'id' => (int) $def->getId(),
                'name' => $def->getName(),
                'type' => $type,
                'required' => (bool) $def->isRequired()
            ];
        }

        $order_field = array_keys($order->get())[0] ?? 'name';
        $order_direction = $order->get()[$order_field] ?? Order::ASC;

        uasort($data, function ($a, $b) use ($order_field) {
            return strnatcasecmp((string) ($a[$order_field] ?? ''), (string) ($b[$order_field] ?? ''));
        });

        if ($order_direction === Order::DESC) {
            $data = array_reverse($data, true);
        }

        $data = array_slice($data, $range->getStart(), $range->getLength(), true);

        foreach ($data as $datum) {
            yield $row_builder->buildDataRow((string) $datum['id'], $datum);
        }
    }

    /**
     * @return int[]
     */
    public function getAllIDs(): array
    {
        return ilCourseDefinedFieldDefinition::_getFieldIds($this->obj_id);
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): int {
        return count($this->getFields());
    }
}
