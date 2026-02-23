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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */

namespace ILIAS\File\Capabilities;

use ILIAS\Data\URI;

class Capability
{
    private bool $unlocked = false;
    private ?URI $uri = null;
    /**
     * @var Permissions[]
     */
    private array $permissions = [];

    public function __construct(
        private Capabilities $capability,
        Permissions ... $permissions
    ) {
        $this->permissions = $permissions;
    }

    public function withUnlocked(bool $unlocked): Capability
    {
        $this->unlocked = $unlocked;
        return $this;
    }

    public function withURI(?URI $uri): Capability
    {
        $this->uri = $uri;
        return $this;
    }

    public function isUnlocked(): bool
    {
        return $this->unlocked;
    }

    public function getUri(): ?URI
    {
        return $this->uri;
    }

    public function getCapability(): Capabilities
    {
        return $this->capability;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

}
