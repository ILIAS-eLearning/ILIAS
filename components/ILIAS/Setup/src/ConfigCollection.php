<?php

declare(strict_types=1);

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

namespace ILIAS\Setup;

/**
 * A collection of some configurations.
 */
class ConfigCollection implements Config
{
    /**
     * @var array<string,Config>
     */
    protected array $configs;

    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    public function getConfig(string $key): Config
    {
        if (!isset($this->configs[$key])) {
            throw new \InvalidArgumentException(
                "Unknown key '$key' for Config."
            );
        }
        return $this->configs[$key];
    }

    public function maybeGetConfig(string $key): ?Config
    {
        return $this->configs[$key] ?? null;
    }

    /**
     * @return string[]
     */
    public function getKeys(): array
    {
        return array_keys($this->configs);
    }
}
