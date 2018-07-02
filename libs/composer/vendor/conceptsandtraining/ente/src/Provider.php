<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente;

/**
 * A component provider can be queried for components of an entity.
 *
 * ARCH:
 *  - The provider was able to provide components for many entities in a
 *    previous version. This was changed to this version where a provider provides
 *    components for one entity. The use cases for the framework will be mostly
 *    entity centric, thus it makes more sense to have according providers.
 */
interface Provider {
    /**
     * Get the components of a given type.
     *
     * `$component_type` must be a class or interface name. The returned
     * components must implement that class or interface.
     *
     * For every `$component_type` not included in `providedComponentTypes`
     * this must return an empty array.
     *
     * For every `$entity` not included in `providesForEntities` this must
     * return an empty array.
     *
     * @param   string      $component_type
     * @return  Component[]
     */
    public function componentsOfType($component_type);

    /**
     * Get the component types this provider provides.
     *
     * @return  string[]
     */
    public function componentTypes();

    /**
     * Get the entity this provider provides components for.
     *
     * @return  Entity
     */
    public function entity();
}
