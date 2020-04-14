<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\ViewControl;

use ILIAS\UI\Component\Input\Container\ViewControl as V;

/**
 * Factory for the View Control Containers
 */
class Factory implements V\Factory
{
    public function standard(array $controls) : V\Standard
    {
        return new Standard($controls);
    }
}
