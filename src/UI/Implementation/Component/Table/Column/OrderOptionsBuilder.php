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

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ilLanguage;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Table\Column;
use ILIAS\UI\Implementation\Render\ComponentRenderer;

/**
 * The Order Options Builder provides labels for the
 * Sortation View Control used in Data Table
 */
class OrderOptionsBuilder
{
    protected const SEPERATOR = ', ';

    public function __construct(
        protected ilLanguage $lng,
        protected DataFactory $data_factory
    ) {
    }

    /**
     * @param array<string, Column\Column>
     * @return array<string, Order>
     */
    public function buildFor(array $columns): array
    {
        $sort_options = [];
        foreach ($columns as $id => $col) {
            $title = $col->getTitle();
            list($internal_label_asc, $internal_label_desc) = $col->getOrderingLabels();
            list($label_asc, $label_desc) = $this->getLabelsByColumn($col);
            $asc = $internal_label_asc ?? $label_asc;
            $desc = $internal_label_desc ?? $label_desc;
            $sort_options[$title . self::SEPERATOR . $asc] = $this->data_factory->order($id, Order::ASC);
            $sort_options[$title . self::SEPERATOR . $desc] = $this->data_factory->order($id, Order::DESC);
        }
        return $sort_options;
    }

    /**
     * @return string[]
     */
    protected function getLabelsByColumn(Column\Column $column): array
    {
        switch($column->getOrderLabelType()) {
            case OrderLabelType::ALPHABETICAL:
                return [
                    $this->lng->txt('order_option_alphabetical_ascending'),
                    $this->lng->txt('order_option_alphabetical_descending')
                ];

            case OrderLabelType::NUMERIC:
                return [
                    $this->lng->txt('order_option_numerical_ascending'),
                    $this->lng->txt('order_option_numerical_descending')
                ];

            case OrderLabelType::BOOL:
                $column_value_true = $column->format(true);
                $column_value_false = $column->format(false);
                if($column_value_true instanceof Component) {
                    $column_value_true = $column_value_true->getLabel();
                }
                if($column_value_false instanceof Component) {
                    $column_value_false = $column_value_false->getLabel();
                }
                return [
                    $column_value_true . ' ' . $this->lng->txt('order_option_first'),
                    $column_value_false . ' ' . $this->lng->txt('order_option_first')
                ];

            case OrderLabelType::DATE:
                return [
                    $this->lng->txt('order_option_chronological_ascending'),
                    $this->lng->txt('order_option_chronological_descending')
                ];

            case OrderLabelType::GENERIC:
            default:
                return [
                    $this->lng->txt('order_option_generic_ascending'),
                    $this->lng->txt('order_option_generic_descending')
                ];
        }
    }
}
