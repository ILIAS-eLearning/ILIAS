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

use Generator;

interface OrderingBinding
{
    /**
     * This is called by the (ordering-)table to retrieve rows;
     * map data-records to rows using the $row_builder
     * e.g. yield $row_builder->buildStandardRow($row_id, $record).
     */
    public function getRows(
        OrderingRowBuilder $row_builder
    ): Generator;

    /**
     * @param string[] $ordered the new, ordered list of record-ids
     */
    public function withOrder(array $ordered): self;
}
