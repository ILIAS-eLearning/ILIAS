<?php declare(strict_types=1);

/**
 * Repository interface for component data.
 */
interface ilComponentDataDB
{
    /**
     * Check if a component exists.
     *
     * @throws \InvalidArgumentException if $type is not known
     */
    public function hasComponent(string $type, string $name) : bool;

    /**
     * Check if a component exists.
     */
    public function hasComponentId(string $id) : bool;

    /**
     * Get all components.
     *
     * Keys are the ids.
     *
     * @return Iterator <string, ilComponentInfo>
     */
    public function getComponents() : Iterator;

    /**
     * Get a component by id.
     *
     * @throws \InvalidArgumentException if component does not exist
     */
    public function getComponentById(string $id) : ilComponentInfo;

    /**
     * Get a component by type and name.
     *
     * @throws \InvalidArgumentException if component does not exist
     */
    public function getComponentByTypeAndName(string $type, string $name) : ilComponentInfo;
}
