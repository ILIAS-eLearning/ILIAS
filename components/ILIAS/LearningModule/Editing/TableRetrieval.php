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

class TableRetrieval implements Table\DataRetrieval
{
    public function __construct(
        protected RetrievalInterface $retrieval
    ) {
    }

    public function getRows(
        Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        foreach ($this->retrieval->getData(
            $visible_column_ids,
            $range,
            $order,
            $filter_data ?? [],
            $additional_parameters ?? []
        ) as $data) {
            yield $row_builder->buildDataRow((string) $data["id"], $data);
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return $this->retrieval->count(
            $filter_data ?? [],
            $additional_parameters ?? []
        );
    }
}
