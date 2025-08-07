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

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\Data\Factory as DataFactory;

class DataRowBuilder extends RowBuilder implements T\DataRowBuilder
{
    public function __construct(
        protected DataFactory $data_factory
    ) {
    }

    /**
     * @param array<string, mixed> $record
     */
    public function buildDataRow(string $id, array $record): DataRow
    {
        return new DataRow(
            $this->row_actions !== [],
            $this->table_has_multiactions,
            $this->columns,
            $this->row_actions,
            $id,
            $record
        );
    }

    public function buildSummaryRow(array $record): SummaryRow
    {
        return new SummaryRow(
            $this->row_actions !== [],
            $this->table_has_multiactions,
            $this->columns,
            $this->data_factory,
            $record
        );
    }
}
