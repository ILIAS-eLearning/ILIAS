<?php declare(strict_types=1);

/**
 * Simple value class for basic information about a component.
 */
class ilComponentInfo
{
    // TODO: to be replaced with an enum for PHP 8.1...
    public const TYPES = ["Modules", "Services"];
    public const TYPE_MODULES = "Modules";
    public const TYPE_SERVICES = "Services";

    protected string $id;
    protected string $type;
    protected string $name;
    /**
     * @var ilPluginSlotInfo[]
     */
    protected array $pluginslots;

    public function __construct(
        string $id,
        string $type,
        string $name,
        array &$pluginslots
    ) {
        if (!in_array($type, self::TYPES)) {
            throw new \InvalidArgumentException(
                "Invalid component type: $type"
            );
        }

        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
        $this->pluginslots = &$pluginslots;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getQualifiedName() : string
    {
        return $this->type . "/" . $this->name;
    }

    /**
     * @return Iterator <ilPluginSlotInfo>
     */
    public function getPluginSlots() : Iterator
    {
        foreach ($this->pluginslots as $id => $slot) {
            yield $slot->getId() => $slot;
        }
    }

    public function hasPluginSlotId(string $id) : bool
    {
        foreach ($this->pluginslots as $slot) {
            if ($slot->getId() === $id) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throw \InvalidArgumentException if there is no such slot
     */
    public function getPluginSlotById(string $id) : \ilPluginSlotInfo
    {
        foreach ($this->pluginslots as $slot) {
            if ($slot->getId() === $id) {
                return $slot;
            }
        }
        throw new \InvalidArgumentException(
            "No plugin slot $id at component {$this->getQualifiedName()}"
        );
    }

    public function hasPluginSlotName(string $name) : bool
    {
        foreach ($this->pluginslots as $slot) {
            if ($slot->getName() === $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throw \InvalidArgumentException if there is no such slot
     */
    public function getPluginSlotByName(string $name) : \ilPluginSlotInfo
    {
        foreach ($this->pluginslots as $slot) {
            if ($slot->getName() === $name) {
                return $slot;
            }
        }
        throw new \InvalidArgumentException(
            "No plugin slot $name at component {$this->getQualifiedName()}"
        );
    }
}
