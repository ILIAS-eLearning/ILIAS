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

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component as C;

abstract class RowBuilder
{
    protected bool $table_has_multiactions = false;

    /**
     * @var array<string, I\Table\Action\Action>
     */
    protected array $row_actions = [];

    /**
     * @var array<string, I\Table\Column\Column>
     */
    protected array $columns = [];

    public function withMultiActionsPresent(bool $flag): self
    {
        $clone = clone $this;
        $clone->table_has_multiactions = $flag;
        return $clone;
    }

    /**
     * @param array<string, I\Table\Action\Action> $row_actions
     */
    public function withSingleActions(array $row_actions): self
    {
        $clone = clone $this;
        $clone->row_actions = $row_actions;
        return $clone;
    }
    /**
     * @param array<string, I\Table\Column\Column> $columns
     */
    public function withVisibleColumns(array $columns): self
    {
        $clone = clone $this;
        $clone->columns = $columns;
        return $clone;
    }
}
