<?php declare(strict_types=1);

/**
 * Repository for component data implemented over artifacts.
 */
class ilArtifactComponentDataDB implements ilComponentDataDB
{
    public const COMPONENT_DATA_PATH = "Services/Component/artifacts/component_data.php";

    protected array $components;
    protected array $id_by_type_and_name;

    public function __construct()
    {
        $component_data = $this->readComponentData();
        $this->components = [];
        $this->id_by_type_and_name = [
            "Modules" => [],
            "Services" => []
        ];
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
            }
            $this->components[$comp_id] = $component;
            $this->id_by_type_and_name[$type][$comp_name] = $comp_id;
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

        return isset($this->id_by_type_and_name[$type][$name]);
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
        $slots = [];
        foreach ($this->components as $id => $comp) {
            yield $id => $comp;
        }
    }

    /**
     * Get a component by id.
     *
     * @throws \InvalidArgumentException if component does not exist
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
     * Get a component by type and name.
     *
     * @throws \InvalidArgumentException if component does not exist
     */
    public function getComponentByTypeAndName(string $type, string $name) : ilComponentInfo
    {
        if (!$this->hasComponent($type, $name)) {
            throw new \InvalidArgumentException(
                "Unknown component $type/$name"
            );
        }
        return $this->components[$this->id_by_type_and_name[$type][$name]];
    }
}
