<?php declare(strict_types=1);

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

    public function getComponent() : ilComponentInfo
    {
        return $this->component;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getQualifiedName() : string
    {
        return $this->component->getQualifiedName() . "/" . $this->getName();
    }

    /**
     * @return Iterator <ilPluginInfo>
     */
    public function getPlugins() : Iterator
    {
        foreach ($this->plugins as $id => $plugin) {
            yield $id => $plugin;
        }
    }

    public function hasPlugin(string $id) : bool
    {
        return isset($this->plugins[$id]);
    }

    /**
     * @throws \InvalidArgumentException if plugin does not exist
     */
    public function getPlugin(string $id) : \ilPluginInfo
    {
        if (!$this->hasPlugin($id)) {
            throw new \InvalidArgumentException(
                "No plugin $id in slot {$this->getQualifiedName()}."
            );
        }
        return $this->plugins[$id];
    }
}
