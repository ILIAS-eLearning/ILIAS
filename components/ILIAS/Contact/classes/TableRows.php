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

namespace ILIAS\Contact;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Implementation\Component\Input\ViewControl\Pagination;
use Generator;
use Closure;

class TableRows implements DataRetrieval
{
    /**
     * @param Closure(DataRowBuilder, string[], Range, Order, ?array, ?array): Generator $rows
     */
    public function __construct(private readonly Closure $rows)
    {
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        return ($this->rows)(...func_get_args());
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return current(Pagination::DEFAULT_LIMITS) - 1; // Disable pagination controls.
    }
}
