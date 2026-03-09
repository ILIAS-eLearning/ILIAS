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

namespace ILIAS\File\Capabilities;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class CapabilityCollection
{
    /**
     * @param Capability[] $capabilities
     */
    public function __construct(
        private array $capabilities
    ) {
    }

    public function all(): array
    {
        return $this->capabilities;
    }

    public function get(Capabilities $capability): Capability|false
    {
        foreach ($this->capabilities as $cap) {
            if ($cap->getCapability() === $capability) {
                return $cap;
            }
        }
        return false;
    }

    public function getBest(): Capability
    {
        foreach ($this->capabilities as $cap) {
            if ($cap->isUnlocked()) {
                return $cap;
            }
        }
        return new Capability(Capabilities::NONE, Permissions::NONE);
    }

}
