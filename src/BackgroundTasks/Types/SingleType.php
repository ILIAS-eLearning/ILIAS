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

namespace ILIAS\BackgroundTasks\Types;

class SingleType implements Type, Ancestors
{
    protected \ReflectionClass $type;

    /**
     * SingleType constructor.
     * @param $fullyQualifiedClassName
     */
    public function __construct($fullyQualifiedClassName)
    {
        $this->type = new \ReflectionClass($fullyQualifiedClassName);
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->type->getName();
    }

    /**
     * @inheritdoc
     */
    public function isExtensionOf(Type $type): bool
    {
        if (!$type instanceof SingleType) {
            return false;
        }

        return $this->type->isSubclassOf($type->__toString()) || $this->__toString() === $type->__toString();
    }

    /**
     * @inheritdoc
     */
    public function getAncestors(): array
    {
        $class = $this->type;
        $ancestors = [new SingleType($class->getName())];

        while ($class = $class->getParentClass()) {
            $ancestors[] = new SingleType($class->getName());
        }

        return array_reverse($ancestors);
    }

    /**
     * @inheritdoc
     */
    public function equals(Type $otherType): bool
    {
        if (!$otherType instanceof SingleType) {
            return false;
        }

        return $this->__toString() === $otherType->__toString();
    }
}
