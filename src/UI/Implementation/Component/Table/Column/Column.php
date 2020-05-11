<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as C;

abstract class Column implements C\Column
{
    const COLUMN_TYPE_TEXT = 'text';
    const COLUMN_TYPE_NUMBER = 'number';
    const COLUMN_TYPE_DATE = 'date';

    /**
     * @var string
     */
    protected $title;

    /**
     * @var bool
     */
    protected $sortable = true;

    /**
     * @var bool
     */
    protected $optional = false;

    /**
     * @var bool
     */
    protected $initially_visible = true;

    /**
     * @var int
     */
    protected $index;


    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    abstract public function getType() : string;

    public function withIsSortable(bool $flag) : C\Column
    {
        $clone = clone $this;
        $clone->sortable = $flag;
        return $clone;
    }

    public function isSortable() : bool
    {
        return $this->sortable;
    }

    public function withIsOptional(bool $flag) : C\Column
    {
        $clone = clone $this;
        $clone->optional = $flag;
        return $clone;
    }

    public function isOptional() : bool
    {
        return $this->optional;
    }

    public function withIsInitiallyVisible(bool $flag) : C\Column
    {
        $clone = clone $this;
        $clone->initially_visible = $flag;
        return $clone;
    }

    public function isInitiallyVisible() : bool
    {
        return $this->initially_visible;
    }

    public function withIndex(int $index) : C\Column
    {
        $clone = clone $this;
        $clone->index = $index;
        return $clone;
    }

    public function getIndex() : int
    {
        return $this->index;
    }
}
