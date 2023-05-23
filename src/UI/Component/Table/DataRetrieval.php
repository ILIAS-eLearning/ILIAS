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

namespace ILIAS\UI\Component\Table;

use ILIAS\Data\Range;
use ILIAS\Data\Order;
use Generator;

interface DataRetrieval
{
    /**
     * This is called by the table to retrieve rows;
     * map data-records to rows using the $row_builder
     * e.g. yield $row_builder->buildStandardRow($row_id, $record).
     *
     * @param string[] $visible_column_ids
     */
    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data, // $DIC->uiService()->filter()->getData();
        ?array $additional_parameters
    ): Generator;

    /**
     * Mainly for the purpose of pagination-support, it is important to
     * know about the total number of records available.
     * Given the nature of a DataTable, which is, opposite to a PresentationTable,
     * rather administrative than explorative, this information will increase
     * user experience quite a bit.
     * However, you may return null, if the call is to costly, but expect
     * the View Control to look a little different in this case.
     *
     * Make sure that potential filters or user restrictions are being applied
     * to the count.
     */
    public function getTotalRowCount(
        ?array $filter_data, // $DIC->uiService()->filter()->getData();
        ?array $additional_parameters
    ): ?int;
}
