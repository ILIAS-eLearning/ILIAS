<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;

class RowFactory implements T\RowFactory
{
    /**
     * @var <string, Column>
     */
    protected $columns;

    /**
     * @var <string, Column>
     */
    protected $actions;

    /**
     * @param <string, Column> $columns
     * @param <string, Action> $actions
     */
    public function __construct(array $columns, array $actions)
    {
        $this->columns = $columns;
        $this->actions = $actions;
    }

    public function standard(string $id, array $record) : T\Row
    {
        $row = new StandardRow($this->columns, $this->actions, $id, $record);
        return $row;
    }
}
