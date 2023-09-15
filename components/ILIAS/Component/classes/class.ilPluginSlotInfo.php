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
 ********************************************************************
 */

declare(strict_types=1);

/**
 * Simple value class for basic information about a pluginslot.
 */
class ilPluginSlotInfo
{
    protected ilComponentInfo $component;
    protected string $id;
    protected string $name;
    /**
     * @var ilPluginInfo[]
     */
    protected array $plugins;

    public function __construct(
        ilComponentInfo $component,
        string $id,
        string $name,
        array &$plugins
    ) {
        $this->component = $component;
        $this->id = $id;
        $this->name = $name;
        $this->plugins = &$plugins;
    }

    public function getComponent(): ilComponentInfo
    {
        return $this->component;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getQualifiedName(): string
    {
        return $this->component->getQualifiedName() . "/" . $this->getName();
    }

    public function getPath(): string
    {
        return ilComponentRepository::PLUGIN_BASE_PATH . "/" . $this->getQualifiedName();
    }

    /**
     * @return Iterator <ilPluginInfo>
     */
    public function getPlugins(): Iterator
    {
        foreach ($this->plugins as $id => $plugin) {
            yield $id => $plugin;
        }
    }

    public function hasPluginId(string $id): bool
    {
        return isset($this->plugins[$id]);
    }

    /**
     * @throws \InvalidArgumentException if plugin does not exist
     */
    public function getPluginById(string $id): \ilPluginInfo
    {
        if (!$this->hasPluginId($id)) {
            throw new \InvalidArgumentException(
                "No plugin $id in slot {$this->getQualifiedName()}."
            );
        }
        return $this->plugins[$id];
    }

    public function hasPluginName(string $name): bool
    {
        foreach ($this->getPlugins() as $plugin) {
            if ($plugin->getName() === $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throws \InvalidArgumentException if plugin does not exist
     */
    public function getPluginByName(string $name): \ilPluginInfo
    {
        foreach ($this->getPlugins() as $plugin) {
            if ($plugin->getName() === $name) {
                return $plugin;
            }
        }
        throw new \InvalidArgumentException(
            "No plugin with name $name in slot {$this->getQualifiedName()}."
        );
    }

    /**
     * @return Iterator <ilPluginInfo>
     */
    public function getActivePlugins(): Iterator
    {
        foreach ($this->getPlugins() as $id => $plugin) {
            if ($plugin->isActive()) {
                yield $id => $plugin;
            }
        }
    }

    public function hasActivePlugins(): bool
    {
        foreach ($this->getActivePlugins() as $_) {
            return true;
        }
        return false;
    }
}
