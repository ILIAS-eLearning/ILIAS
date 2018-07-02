<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente\Simple;

/**
 * Simple implementation for a provider, works in memory.
 */
class Provider implements \CaT\Ente\Provider {
    use \CaT\Ente\ProviderHelper;

    /**
     * @var Entity
     */
    private $entity;

    /**
     * @var array<string,Component>
     */
    private $components;

    public function __construct(Entity $entity) {
        $this->entity = $entity;
        $this->components = [];
    }

    /**
     * @inheritdocs
     */
    public function componentsOfType($component_type) {
        if (isset($this->components[$component_type])) {
            return $this->components[$component_type];
        }
        return [];
    }

    /**
     * @inheritdocs
     */
    public function componentTypes() {
        return array_keys($this->components);
    }

    /**
     * @inheritdocs
     */
    public function entity() {
        return $this->entity;
    }

    /**
     * Add a component to the provider.
     *
     * @param   \CaT\Ente\Component   $component
     * @throws  InvalidArgumentException if $component belongs to another entity
     * @return  self
     */
    public function addComponent(\CaT\Ente\Component $component) {
        if ($component->entity()->id() !== $this->entity()->id()) {
            $my_id = serialize($this->entity()->id());
            $other_id = serialize($component->entity()->id());
            throw new \InvalidArgumentException(
                "Cannot add component of entity '$other_id' to provider for '$my_id'.");
        }

        foreach ($this->componentTypesOf($component) as $type) {
            if (!isset($this->components[$type])) {
                $this->components[$type] = [];
            }
            $this->components[$type][] = $component;
        }
        return $this;
    }
}
