<?php declare(strict_types=1);

/**
 * Repository interface for component data.
 */
interface ilComponentDataDB
{
    /**
     * Get all component ids.
     *
     * @return Iterator <string>
     */
    public function getComponentIds() : Iterator;

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
     * Get the id of a component.
     *
     * @throws \InvalidArgumentException if component does not exist
     */
    public function getComponentId(string $type, string $name) : string;

    /**
     * Get the type of a component.
     *
     * @throws \InvalidArgumentException if component does not exist
     */
    public function getComponentType(string $id) : string;

    /**
     * Get the name of a component.
     *
     * @throws \InvalidArgumentException if component does not exist
     */
    public function getComponentName(string $id) : string;
}
