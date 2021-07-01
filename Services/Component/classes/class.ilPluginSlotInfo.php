<?php declare(strict_types=1);

/**
 * Simple value class for basic information about a pluginslot.
 */
class ilPluginSlotInfo
{
    protected ilComponentInfo $component;
    protected string $id;
    protected string $name;

    public function __construct(
        ilComponentInfo $component,
        string $id,
        string $name
    ) {
        $this->component = $component;
        $this->id = $id;
        $this->name = $name;
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
}
