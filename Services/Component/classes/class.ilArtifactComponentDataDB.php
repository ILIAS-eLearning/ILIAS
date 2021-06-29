<?php declare(strict_types=1);

/**
 * Repository for component data implemented over artifacts.
 */
class ilArtifactComponentDataDB implements ilComponentDataDB
{
    public const COMPONENT_DATA_PATH = "Services/Component/artifacts/component_data.php";
    public const BY_TYPE_AND_NAME = "by_type_and_name";
    public const BY_ID = "by_id";
    public const TYPES = ["Modules", "Services"];
    public const TYPE_MODULES = "Modules";
    public const TYPE_SERVICES = "Services";

    protected array $component_data;

    public function __construct()
    {
        $this->component_data = require self::COMPONENT_DATA_PATH;
    }

    /**
     * @inheritdocs
     */
    public function getComponentIds() : Iterator
    {
        foreach ($this->component_data[self::BY_ID] as $k => $_) {
            yield $k;
        }
    }

    /**
     * @inheritdocs
     */
    public function hasComponent(string $type, string $name) : bool
    {
        if (!in_array($type, self::TYPES)) {
            throw new \InvalidArgumentException(
                "Unknown component type $type."
            );
        }

        return isset($this->component_data[self::BY_TYPE_AND_NAME][$type][$name]);
    }

    /**
     * @inheritdocs
     * Check if a component exists.
     */
    public function hasComponentId(string $id) : bool
    {
        return isset($this->component_data[self::BY_ID][$id]);
    }

    /**
     * @inheritdocs
     */
    public function getComponentId(string $type, string $name) : string
    {
        if (!$this->hasComponent($type, $name)) {
            throw new \InvalidArgumentException(
                "Unknown component $type/$name"
            );
        }
        return $this->component_data[self::BY_TYPE_AND_NAME][$type][$name];
    }

    /**
     * @inheritdocs
     */
    public function getComponentType(string $id) : string
    {
        if (!$this->hasComponentId($id)) {
            throw new \InvalidArgumentException(
                "Unknown component $id"
            );
        }
        return $this->component_data[self::BY_ID][$id][0];
    }

    /**
     * @inheritdocs
     */
    public function getComponentName(string $id) : string
    {
        if (!$this->hasComponentId($id)) {
            throw new \InvalidArgumentException(
                "Unknown component $id"
            );
        }
        return $this->component_data[self::BY_ID][$id][1];
    }
}
