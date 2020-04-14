<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nhaagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as I;
use ILIAS\Refinery\Transformation;

abstract class Column implements I\Column
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var bool
     */
    protected $sortable;

    /**
     * @var bool
     */
    protected $optional;

    /**
     * @var bool
     */
    protected $initially_visible;

    /**
     * @var Transformation[]
     */
    protected $trafos = [];

    public function __construct(string $title)
    {
        if (trim($title) === '') {
            throw new \InvalidArgumentException("Column Title must not be empty.", 1);
        }
        $this->title = $title;
        $this->sortable = false;
        $this->optional = false;
        $this->initially_visible = true;

        $this->trafos[] = function ($v) {
            return $v;
        };
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getType() : string
    {
        return static::TYPE;
    }

    public function withIsSortable(bool $flag) : I\Column
    {
        $clone = clone $this;
        $clone->sortable = $flag;
        return $clone;
    }

    public function isSortable() : bool
    {
        return $this->sortable;
    }

    public function withIsOptional(bool $flag) : I\Column
    {
        $clone = clone $this;
        $clone->optional = $flag;
        return $clone;
    }

    public function isOptional() : bool
    {
        return $this->optional;
    }

    public function withIsInitiallyVisible(bool $flag) : I\Column
    {
        if ($flag === false && $this->isOptional() === false) {
            throw new \InvalidArgumentException("A mandatory column cannot be hidden.", 1);
        }
        $clone = clone $this;
        $clone->initially_visible = $flag;
        return $clone;
    }

    public function isInitiallyVisible() : bool
    {
        return $this->initially_visible;
    }

    public function withAdditionalTransformation(Transformation $trafo)
    {
        $clone = clone $this;
        $clone->trafos[] = $trafo;
        return $clone;
    }

    public function getTransformations()
    {
        return $this->trafos;
    }
}
