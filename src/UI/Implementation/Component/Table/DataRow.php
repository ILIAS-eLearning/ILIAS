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
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Component;

class DataRow implements T\DataRow
{
    use ComponentHelper;

    /**
     * @var array<string, bool>
     */
    protected array $disabled_actions = [];

    protected bool $table_has_singleactions;
    protected bool $table_has_multiactions;
    protected array $columns;
    protected array $actions;
    protected string $id;
    protected array $record;

    /**
     * The records's key is the column-id of the table.
     * Its value will be formatted by the respective colum type's format-method.
     *
     * @param array<string, T\Column\Column> $columns
     * @param array<string, T\Action\Action> $actions
     * @param array<string, mixed> $record
     */
    public function __construct(
        bool $table_has_singleactions,
        bool $table_has_multiactions,
        array $columns,
        array $actions,
        string $id,
        array $record
    ) {
        $this->table_has_singleactions = $table_has_singleactions;
        $this->table_has_multiactions = $table_has_multiactions;
        $this->columns = $columns;
        $this->actions = $actions;
        $this->id = $id;
        $this->record = $record;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function withDisabledAction(string $action_id, bool $disable = true): T\DataRow
    {
        if (!$disable) {
            return $this;
        }
        $clone = clone $this;
        $clone->disabled_actions[$action_id] = true;
        return $clone;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function tableHasSingleActions(): bool
    {
        return $this->table_has_singleactions;
    }
    public function tableHasMultiActions(): bool
    {
        return $this->table_has_multiactions;
    }

    public function getActions(): array
    {
        return array_filter(
            $this->actions,
            function (string $id): bool {
                return !array_key_exists($id, $this->disabled_actions);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    public function getCellContent(string $col_id)
    {
        if (!array_key_exists($col_id, $this->record)) {
            return '';
        }
        return $this->columns[$col_id]->format($this->record[$col_id]);
    }
}
