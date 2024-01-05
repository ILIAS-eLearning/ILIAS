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

namespace ILIAS\UI\Implementation\Component\Table;

use ilLanguage;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\Column\Column;

/**
 * The Order Options Builder provides labels for the
 * Sortation View Control used in Data Table
 */
class OrderOptionsBuilder
{
    public function __construct(
        protected ilLanguage $lng,
        protected DataFactory $data_factory,
    ) {
    }

    /**
     * @param array<string, Column>
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
            $sort_options[$title . ', ' . $asc] = $this->data_factory->order($id, 'ASC');
            $sort_options[$title . ', ' . $desc] = $this->data_factory->order($id, 'DESC');
        }
        return $sort_options;
    }

    protected function getLabelsByColumn(Column $column): array
    {

        $asc = ['', 'generic_ascending'];
        $desc = ['', 'generic_descending'];
        switch($column->getType()) {
            case 'Text':
            case 'EMail':
            case 'Link':
            case 'LinkListing':
            case 'Status':
                $asc = ['', 'alphabetical_ascending'];
                $desc = ['', 'alphabetical_descending'];
                break;
            case 'Number':
                $asc = ['', 'numerical_ascending'];
                $desc = ['', 'numerical_descending'];
                break;
            case 'Boolean':
                $asc = [$column->format(true), 'first'];
                $desc = [$column->format(false), 'first'];
                break;
            case 'Date':
            case 'TimeSpan':
                $asc = ['', 'chronological_ascending'];
                $desc = ['', 'chronological_descending'];
        }

        return [
            trim(implode(' ', [$asc[0] , $this->lng->txt('order_option_' . $asc[1])])),
            trim(implode(' ', [$desc[0] , $this->lng->txt('order_option_' . $desc[1])]))
        ];
    }
}
