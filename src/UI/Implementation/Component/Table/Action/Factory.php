<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nhaagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table\Action;

use ILIAS\UI\Component\Table\Action as I;

class Factory implements I\Factory
{
    public function standard(string $label, string $parameter_name, $target) : I\Standard
    {
        return new Standard($label, $parameter_name, $target);
    }

    public function single(string $label, string $parameter_name, $target) : I\Single
    {
        return new Single($label, $parameter_name, $target);
    }

    public function multi(string $label, string $parameter_name, $target) : I\Multi
    {
        return new Multi($label, $parameter_name, $target);
    }
}
