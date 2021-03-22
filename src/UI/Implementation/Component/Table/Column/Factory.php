<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nhaagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as I;

class Factory implements I\Factory
{
    public function text(string $title) : I\Text
    {
        throw new \ILIAS\UI\NotImplementedException('NYI');
    }

    public function number(string $title) : I\Number
    {
        throw new \ILIAS\UI\NotImplementedException('NYI');
    }

    public function date(string $title, \ILIAS\Data\DateFormat\DateFormat $format) //:@Todo: Does not yet exit
    {
        throw new \ILIAS\UI\NotImplementedException('NYI');
    }
}
