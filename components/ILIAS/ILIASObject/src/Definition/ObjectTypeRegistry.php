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

namespace ILIAS\ILIASObject\Definition;

/**
 * Collects all object types contributed by the components. This is the only
 * source of object types, there is no definition in XML or the database.
 */
class ObjectTypeRegistry
{
    /**
     * @var array<string, ObjectType>|null
     */
    private ?array $types = null;
    /**
     * @var array<string, ObjectGroup>|null
     */
    private ?array $groups = null;

    /**
     * @param ObjectType[]       $object_types
     * @param ObjectTypeSource[] $sources
     * @param ObjectGroup[]      $object_groups
     */
    public function __construct(
        private readonly array $object_types,
        private readonly array $sources,
        private readonly array $object_groups
    ) {
    }

    /**
     * @return array<string, ObjectType>
     */
    public function getAll(): array
    {
        if ($this->types !== null) {
            return $this->types;
        }
        $types = [];
        foreach ($this->object_types as $type) {
            $this->add($types, $type);
        }
        foreach ($this->sources as $source) {
            foreach ($source->getObjectTypes() as $type) {
                $this->add($types, $type);
            }
        }
        return $this->types = $types;
    }

    public function has(string $type): bool
    {
        return isset($this->getAll()[$type]);
    }

    public function get(string $type): ObjectType
    {
        return $this->getAll()[$type]
            ?? throw new \OutOfBoundsException("Object type '$type' is not available.");
    }

    /**
     * Language modules of the contributed types. Types of sources are not
     * considered, they are resolved after the language is initialised.
     *
     * @return string[]
     */
    public function getLanguageModules(): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn(ObjectType $type): ?string => $type->getLanguageModule(),
            $this->object_types
        ))));
    }

    /**
     * @return array<string, ObjectGroup>
     */
    public function getGroups(): array
    {
        if ($this->groups !== null) {
            return $this->groups;
        }
        $groups = [];
        foreach ($this->object_groups as $group) {
            $groups[$group->getId()] = $group;
        }
        return $this->groups = $groups;
    }

    /**
     * @param array<string, ObjectType> $types
     */
    private function add(array &$types, ObjectType $type): void
    {
        if (isset($types[$type->getId()])) {
            throw new \LogicException(
                "Object type '{$type->getId()}' is contributed by '{$types[$type->getId()]->getComponent()}'"
                . " and '{$type->getComponent()}'."
            );
        }
        $types[$type->getId()] = $type;
    }
}
