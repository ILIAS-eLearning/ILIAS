<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;

class RowFactory implements T\RowFactory
{
    /**
     * @var array <id, Tranformation>
     */
    protected $cell_transformations;

    public function __construct(array $cell_transformations)
    {
        $this->cell_transformations = $cell_transformations;
    }

    public function map(array $record) : array
    {
        $row = [];
        foreach (array_keys($this->cell_transformations) as $id) {
            $row[$id] = '';
            if (array_key_exists($id, $record)) {
                foreach ($this->cell_transformations[$id] as $trafo) {
                    $row[$id] = $trafo($record[$id]);
                }
            }
        }
        return $row;
    }
}
