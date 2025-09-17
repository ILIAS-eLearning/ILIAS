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

namespace ILIAS\Repository;

class AdditionalRepositoryObjects
{
    private array $by_id = [];
    private array $name_to_id = [];

    public function __construct(
        private array $repo_objects
    ) {
        $this->by_id = array_merge(
            ...array_map(
                fn($o) => [$o->getId() => $o],
                $repo_objects
            )
        );
        $this->name_to_id = array_merge(
            ...array_map(
                fn($o) => [$o->getName() => $o->getId()],
                $repo_objects
            )
        );
    }

    public function getAll(): array
    {
        return $this->repo_objects;
    }

    public function get(string $id): RepositoryObject
    {
        return $this->by_id[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->by_id);
    }

    public function getPluginByName(string $name): \ilPlugin
    {
        return $this->get($this->name_to_id[$name]);
    }

}
