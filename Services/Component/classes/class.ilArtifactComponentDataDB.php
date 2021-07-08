<?php declare(strict_types=1);

/**
 * Repository for component data implemented over artifacts.
 */
class ilArtifactComponentDataDB implements ilComponentDataDB
{
    public const COMPONENT_DATA_PATH = "Services/Component/artifacts/component_data.php";
    public const PLUGIN_DATA_PATH = "Services/Component/artifacts/plugin_data.php";

    protected array $components;
    protected array $component_id_by_type_and_name;
    protected array $pluginslot_by_id;

    public function __construct()
    {
        $component_data = $this->readComponentData();
        $this->components = [];
        $this->component_id_by_type_and_name = [
            "Modules" => [],
            "Services" => []
        ];
        $this->pluginslot_by_id = [];
        foreach ($component_data as $comp_id => list($type, $comp_name, $slot_data)) {
            $slots = [];
            $component = new ilComponentInfo(
                $comp_id,
                $type,
                $comp_name,
                $slots
            );
            foreach ($slot_data as list($slot_id, $slot_name)) {
                $slots[$slot_id] = new ilPluginSlotInfo(
                    $component,
                    $slot_id,
                    $slot_name
                );
                $this->pluginslot_by_id[$slot_id] = $slots[$slot_id];
            }
            $this->components[$comp_id] = $component;
            $this->component_id_by_type_and_name[$type][$comp_name] = $comp_id;
            unset($slots);
        }
    }

    protected function readComponentData() : array
    {
        return require self::COMPONENT_DATA_PATH;
    }

    /**
     * @inheritdocs
     */
    public function hasComponent(string $type, string $name) : bool
    {
        if (!in_array($type, ilComponentInfo::TYPES)) {
            throw new \InvalidArgumentException(
                "Unknown component type $type."
            );
        }

        return isset($this->component_id_by_type_and_name[$type][$name]);
    }

    /**
     * @inheritdocs
     */
    public function hasComponentId(string $id) : bool
    {
        return isset($this->components[$id]);
    }

    /**
     * @inheritdocs
     */
    public function getComponents() : Iterator
    {
        foreach ($this->components as $id => $comp) {
            yield $id => $comp;
        }
    }

    /**
     */
    public function getComponentById(string $id) : ilComponentInfo
    {
        if (!$this->hasComponentId($id)) {
            throw new \InvalidArgumentException(
                "Unknown component $id"
            );
        }
        return $this->components[$id];
    }

    /**
     */
    public function getComponentByTypeAndName(string $type, string $name) : ilComponentInfo
    {
        if (!$this->hasComponent($type, $name)) {
            throw new \InvalidArgumentException(
                "Unknown component $type/$name"
            );
        }
        return $this->components[$this->component_id_by_type_and_name[$type][$name]];
    }


    /**
     */
    public function hasPluginSlotId(string $id) : bool
    {
        return isset($this->pluginslot_by_id[$id]);
    }

    /**
     */
    public function getPluginSlots() : Iterator
    {
        foreach ($this->pluginslot_by_id as $id => $slot) {
            yield $id => $slot;
        }
    }

    /**
     */
    public function getPluginSlotById(string $id) : ilPluginSlotInfo
    {
        if (!$this->hasPluginslotId($id)) {
            throw new \InvalidArgumentException(
                "Unknown pluginslot $id"
            );
        }
        return $this->pluginslot_by_id[$id];
    }
}
