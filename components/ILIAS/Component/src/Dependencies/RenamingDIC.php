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

/**
 * A wrapper around another DIC that superficially adds a _# and passes them to an
 * underlying DIC. # is a counter over all objects that have been added, starting
 * by 0.
 */
class RenamingDIC implements \ArrayAccess
{
    protected int $counter = 0;

    public function __construct(
        protected \ArrayAccess $wrapped
    ) {
    }

    public function offsetSet($id, $value): void
    {
        $id = "{$id}_{$this->counter}";
        $this->counter++;
        $this->wrapped->offsetSet($id, $value);
    }

    public function offsetGet($id): mixed
    {
        return $this->wrapped->offsetGet($id);
    }

    public function offsetExists($id): bool
    {
        return $this->wrapped->offsetExists($id);
    }

    public function offsetUnset($id): void
    {
        $this->wrapped->offsetUnset($id);
    }
}
