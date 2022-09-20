<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Component\Table;

use ILIAS\Data\Range;
use ILIAS\Data\Order;
use Generator;

interface DataRetrieval
{
    /**
     * This is called by the table to retrieve rows;
     * map data-records to rows using $row_factory->map($record).
     */
    public function getRows(
        RowFactory $row_factory,
        Range $range,
        Order $order,
        array $visible_column_ids,
        array $additional_parameters
    ): Generator;
}
