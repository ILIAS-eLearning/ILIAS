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

namespace ILIAS\LearningModule\Table;

use ILIAS\UI\Component\Table;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\OrderingRowBuilder;

class OrderingRetrieval implements Table\OrderingRetrieval
{
    public function __construct(
        protected RetrievalInterface $retrieval
    ) {
    }

    public function getRows(
        OrderingRowBuilder $row_builder,
        array $visible_column_ids
    ): \Generator {
        foreach ($this->retrieval->getData(
            $visible_column_ids
        ) as $data) {
            yield $row_builder->buildOrderingRow((string) $data["id"], $data);
        }
    }
}
