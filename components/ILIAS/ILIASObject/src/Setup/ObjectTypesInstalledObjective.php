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

namespace ILIAS\ILIASObject\Setup;

use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\ILIASObject\Definition\ObjectType;
use ILIAS\ILIASObject\Definition\ObjectTypeFlag;

/**
 * Registers repository object types which are new to the installation: adds
 * the type to object_data, assigns the standard operations and the create
 * operation to the parent types. Types which are already registered are not
 * touched, so the permissions of existing types do not change.
 */
class ObjectTypesInstalledObjective implements Setup\Objective
{
    /**
     * @param array<string, ObjectType> $object_types
     */
    public function __construct(
        private readonly array $object_types
    ) {
    }

    public function getHash(): string
    {
        return hash("sha256", self::class . "::" . implode(",", array_keys($this->object_types)));
    }

    public function getLabel(): string
    {
        return "Register new repository object types";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseInitializedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        foreach ($this->getNewTypes($environment) as $type) {
            (new NewTypeAddedObjective($type->getId(), $type->getClassName()))->achieve($environment);

            $standard_operations = new \ilAccessRbacStandardOperationsAddedObjective($type->getId());
            if ($standard_operations->isApplicable($environment)) {
                $standard_operations->achieve($environment);
            }

            $parent_types = array_values(array_filter(
                array_keys($type->getParentTypes()),
                fn(string $parent_type): bool => isset($this->object_types[$parent_type])
            ));
            $create_operation = new \ilAccessCustomRBACOperationAddedObjective(
                "create_" . $type->getId(),
                "create " . $type->getClassName(),
                "create",
                9999,
                $parent_types
            );
            if ($create_operation->isApplicable($environment)) {
                $create_operation->achieve($environment);
            }
        }
        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        return $this->getNewTypes($environment) !== [];
    }

    /**
     * @return ObjectType[]
     */
    private function getNewTypes(Environment $environment): array
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        return array_filter(
            $this->object_types,
            static fn(ObjectType $type): bool => !$type->isLegacyPlugin()
                && $type->hasFlag(ObjectTypeFlag::RBAC)
                && $type->hasFlag(ObjectTypeFlag::REPOSITORY)
                && !$type->hasFlag(ObjectTypeFlag::SYSTEM)
                && \ilObject::_getObjectTypeIdByTitle($type->getId(), $db) === null
        );
    }
}
