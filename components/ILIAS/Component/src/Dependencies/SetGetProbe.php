<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Component\Dependencies;

use ILIAS\Component\Component;

class SetGetProbe implements \ArrayAccess
{
    public function __construct(
        protected $set_probe,
        protected $get_probe
    ) {
        if (!is_callable($set_probe)) {
            throw new \InvalidArgumentException(
                "Expected \$probe to be callable."
            );
        }

        if (!is_callable($get_probe)) {
            throw new \InvalidArgumentException(
                "Expected \$probe to be callable."
            );
        }
    }

    public function offsetExists($offset): bool
    {
        return false;
    }

    public function offsetGet($offset): mixed
    {
        $probe = $this->get_probe;
        return $probe($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $probe = $this->set_probe;
        $probe($offset, $value);
    }

    public function offsetUnset($offset): void
    {
    }
}
