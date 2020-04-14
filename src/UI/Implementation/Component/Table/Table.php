<?php
/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Implementation\Component\ComponentHelper;

//use ILIAS\UI\Implementation\Component\ViewControl\HasViewControls;

abstract class Table implements T\Table
{
    use ComponentHelper;
    //use HasViewControls;

    /**
     * @var string
     */
    protected $title;

    /**
     * @inheritdoc
     */
    public function withTitle(string $title) : T\Table
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }
}
