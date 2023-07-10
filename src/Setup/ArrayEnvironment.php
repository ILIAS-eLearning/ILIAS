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

use ILIAS\Setup;

class ArrayEnvironment implements Environment
{
    /**
     * @var	array<string,mixed>
     */
    protected array $resources;

    /**
     * @var array<string,mixed>
     */
    protected array $configs;

    public function __construct(array $resources)
    {
        $this->resources = $resources;
    }

    /**
     * @inheritdoc
     */
    public function getResource(string $id)
    {
        if (!isset($this->resources[$id])) {
            return null;
        }
        return $this->resources[$id];
    }

    /**
     * @inheritdoc
     */
    public function withResource(string $id, $resource): Environment
    {
        if (isset($this->resources[$id])) {
            throw new \RuntimeException(
                "Resource '$id' is already contained in the environment"
            );
        }
        $clone = clone $this;
        $clone->resources[$id] = $resource;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withConfigFor(string $component, $config): Environment
    {
        if (isset($this->configs[$component])) {
            throw new \RuntimeException(
                "Config for '$component' is already contained in the environment"
            );
        }
        $clone = clone $this;
        $clone->configs[$component] = $config;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getConfigFor(string $component)
    {
        if (!isset($this->configs[$component])) {
            throw new \RuntimeException(
                "Config for '$component' is not contained in the environment"
            );
        }
        return $this->configs[$component];
    }

    /**
     * @inheritdoc
     */
    public function hasConfigFor(string $component): bool
    {
        return isset($this->configs[$component]);
    }
}
