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

class Row implements T\Row
{
    use ComponentHelper;

    protected bool $table_has_actions;
    protected array $columns;
    protected array $actions;
    protected array $disabled_actions = [];
    protected string  $id;

    public function __construct(
        bool $table_has_actions,
        array $columns,
        array $actions,
        string $id,
        array $record
    ) {
        $this->table_has_actions = $table_has_actions;
        $this->columns = $columns;
        $this->actions = $actions;
        $this->id = $id;
        $this->record = $record;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function withDisabledAction(string $action_id, bool $disable = true): T\Row
    {
        if ($disable !== true) {
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

    public function tableHasActions(): bool
    {
        return $this->table_has_actions;
    }

    public function getActions(): array
    {
        return array_filter(
            $this->actions,
            function ($id) {
                return !array_key_exists($id, $this->disabled_actions);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    public function getCellContent(string $col_id): string
    {
        if (!array_key_exists($col_id, $this->record)) {
            return '';
        }
        return $this->columns[$col_id]->format($this->record[$col_id]);
    }
}
