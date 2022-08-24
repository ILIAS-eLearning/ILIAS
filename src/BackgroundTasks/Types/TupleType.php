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

class TupleType implements Type
{
    /**
     * @var Type[]
     */
    protected array $types = [];

    /**
     * SingleType constructor.
     * @param string[]|Type[] $fullyQualifiedClassNames  Give a Value Type or a Type that will be wrapped in a single type.
     */
    public function __construct(array $fullyQualifiedClassNames)
    {
        foreach ($fullyQualifiedClassNames as $fullyQualifiedClassName) {
            if (!is_a($fullyQualifiedClassName, Type::class)) {
                $fullyQualifiedClassName = new SingleType($fullyQualifiedClassName);
            }
            $this->types[] = $fullyQualifiedClassName;
        }
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return "(" . implode(", ", $this->types) . ")";
    }

    /**
     * tuple A is a subtype of tuple B, iff every element i of tuple A is a subtype of element i of
     * tuple B.
     */
    public function isExtensionOf(Type $type): bool
    {
        if (!$type instanceof TupleType) {
            return false;
        }

        $others = $type->getTypes();
        foreach ($this->types as $i => $type) {
            if (!$type->isExtensionOf($others[$i])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return \ILIAS\BackgroundTasks\Types\Type[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @inheritdoc
     */
    public function equals(Type $otherType): bool
    {
        if (!$otherType instanceof TupleType) {
            return false;
        }

        foreach ($this->types as $i => $type) {
            $otherTypes = $otherType->getTypes();
            if (!$otherTypes[$i]->equals($type)) {
                return false;
            }
        }

        return true;
    }
}
