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

namespace ILIAS\Repository\Table;

use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Table\OrderingRowBuilder;
use ILIAS\Repository\RetrievalInterface;

class OrderingRetrieval implements Table\OrderingRetrieval
{
    public function __construct(
        protected RetrievalInterface $retrieval,
        protected array $actions,
        protected ?\Closure $active_action_closure,
        protected ?\Closure $row_transformer
    ) {
    }

    public function getRows(
        OrderingRowBuilder $row_builder,
        array $visible_column_ids
    ): \Generator {
        foreach ($this->retrieval->getData(
            $visible_column_ids
        ) as $data) {
            if ($this->row_transformer) {
                $table_data = ($this->row_transformer)($data);
            } else {
                $table_data = $data;
            }
            $row = $row_builder->buildOrderingRow((string) $data["id"], $table_data);
            if ($this->active_action_closure) {
                foreach ($this->actions as $action) {
                    if (!($this->active_action_closure)($action, $data)) {
                        $row = $row->withDisabledAction($action);
                    }
                }
            }
            yield $row;
        }
    }
}
