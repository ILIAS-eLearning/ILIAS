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

class OfComponent implements \ArrayAccess
{
    protected Component $component;
    protected array $dependencies = [];

    public function __construct(Component $component, Dependency ...$ds)
    {
        $this->component = $component;

        foreach ($ds as $d) {
            if (!isset($this->dependencies[(string) $d])) {
                $this->dependencies[(string) $d] = [];
            }
            $this->dependencies[(string) $d][] = $d;
            if ($d instanceof Out) {
                $d->setComponent($this);
            }
        }
    }

    public function getComponent(): Component
    {
        return $this->component;
    }

    public function getComponentName(): string
    {
        return get_class($this->getComponent());
    }

    public function getInDependencies(): \Iterator
    {
        foreach ($this->dependencies as $d) {
            foreach ($d as $i) {
                if ($i instanceof In) {
                    yield $i;
                }
            }
        }
    }

    // ArrayAccess

    public function offsetExists($dependency_description): bool
    {
        return array_key_exists($dependency_description, $this->dependencies);
    }

    public function offsetGet($dependency_description): ?array
    {
        return $this->dependencies[$dependency_description];
    }

    public function offsetSet($offset, $value): void
    {
        throw new \LogicException(
            "Cannot modify dependencies of component."
        );
    }

    public function offsetUnset($offset): void
    {
        throw new \LogicException(
            "Cannot modify dependencies of component."
        );
    }
}
