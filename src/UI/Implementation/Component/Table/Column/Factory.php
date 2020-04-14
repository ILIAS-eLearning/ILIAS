<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nhaagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as I;

class Factory implements I\Factory
{
    public function text(string $title) : I\Text
    {
        return new Text($title);
    }

    public function number(string $title) : I\Number
    {
        return new Number($title);
    }

    public function date(string $title, \ILIAS\Data\DateFormat $format) : I\Date
    {
        throw new \ILIAS\UI\NotImplementedException();
    }
}
