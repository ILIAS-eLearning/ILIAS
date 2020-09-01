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
    protected $row_actions;

    /**
     * @param <string, Column> $columns
     * @param <string, Action> $single_actions
     */
    public function __construct(array $columns, array $row_actions)
    {
        $this->columns = $columns;
        $this->row_actions = $row_actions;
    }

    public function standard(string $id, array $record) : T\Row
    {
        $row = new StandardRow($this->columns, $this->row_actions, $id, $record);
        return $row;
    }
}
