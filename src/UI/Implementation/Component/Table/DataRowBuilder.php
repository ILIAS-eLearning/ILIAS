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

class DataRowBuilder implements T\DataRowBuilder
{
    protected bool $table_has_multiactions = false;

    /**
     * @var array<string, Action>
     */
    protected array $row_actions = [];

    /**
     * @var array<string, Column>
     */
    protected array $columns = [];

    public function withMultiActionsPresent(bool $flag): self
    {
        $clone = clone $this;
        $clone->table_has_multiactions = $flag;
        return $clone;
    }

    /**
     * @param array<string, Action> $row_actions
     */
    public function withSingleActions(array $row_actions): self
    {
        $clone = clone $this;
        $clone->row_actions = $row_actions;
        return $clone;
    }
    /**
     * @param array<string, Column> $columns
     */
    public function withVisibleColumns(array $columns): self
    {
        $clone = clone $this;
        $clone->columns = $columns;
        return $clone;
    }

    /**
     * @param array<string, mixed> $record
     */
    public function buildDataRow(string $id, array $record): T\DataRow
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
}
