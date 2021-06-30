<?php declare(strict_types=1);

/**
 * Repository for component data implemented over artifacts.
 */
class ilArtifactComponentDataDB implements ilComponentDataDB
{
    public const COMPONENT_DATA_PATH = "Services/Component/artifacts/component_data.php";
    public const BY_TYPE_AND_NAME = "by_type_and_name";
    public const BY_ID = "by_id";

    protected array $component_data;

    public function __construct()
    {
        $this->component_data = require self::COMPONENT_DATA_PATH;
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

        return isset($this->component_data[self::BY_TYPE_AND_NAME][$type][$name]);
    }

    /**
     * @inheritdocs
     */
    public function hasComponentId(string $id) : bool
    {
        return isset($this->component_data[self::BY_ID][$id]);
    }

    /**
     * @inheritdocs
     */
    public function getComponents() : Iterator
    {
        foreach ($this->component_data[self::BY_ID] as $id => list($type, $name)) {
            yield $id => new ilComponentInfo($id, $type, $name);
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
        return new ilComponentInfo(
            $id,
            $this->component_data[self::BY_ID][$id][0],
            $this->component_data[self::BY_ID][$id][1]
        );
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
        return new ilComponentInfo(
            $this->component_data[self::BY_TYPE_AND_NAME][$type][$name],
            $type,
            $name
        );
    }
}
