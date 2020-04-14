<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Table;

use ILIAS\Data\Range;

//use ILIAS\Data\Order;

interface DataRetrieval
{
    /**
     * This is called by the table to retrieve rows;
     * map data-records to rows using $row_factory->map($record).
     */
    public function getRows(
        RowFactory $row_factory,
        Range $range,
        //Order $order,
        $order,
        array $visible_column_ids,
        array $additional_parameters
    ) : \Generator;
}
