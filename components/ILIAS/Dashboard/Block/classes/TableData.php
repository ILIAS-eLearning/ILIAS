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

namespace ILIAS\Dashboard;

use ILIAS\UI\Component\Table\OrderingRetrieval;
use ILIAS\UI\Component\Table\OrderingRowBuilder;
use Generator;
use Closure;

class TableData implements OrderingRetrieval
{
    public function __construct(private readonly Closure $proc)
    {
    }

    public function getRows(OrderingRowBuilder $row_builder, array $visible_column_ids): Generator
    {
        return ($this->proc)(...func_get_args());
    }
}
