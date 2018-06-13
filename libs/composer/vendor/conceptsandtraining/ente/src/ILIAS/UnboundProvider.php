<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente\ILIAS;

/**
 * An unbound provider is a provider that currently is not bound to an
 * entity and can thus not produce components.
 */
interface UnboundProvider {
    /**
     * @inheritdocs
     */
    public function componentTypes();

    /**
     * Build the component(s) of the given type for the given object.
     *
     * @param   string    $component_type
     * @param   Entity    $provider
     * @return  Component[]
     */
    public function buildComponentsOf($component_type, Entity $entity);

    /**
     * Get the id of this provider for the given owner.
     *
     * @param   \ilObject   $owner
     * @throws  \InvalidArgumentException if $owner is not an owner of this provider
     * @return  int
     */
    public function idFor(\ilObject $owner);

    /**
     * Get the owner object of the component.
     *
     * @return  \ilObject[]
     */
    public function owners();

    /**
     * Get the object type this binds to.
     *
     * @return  string
     */
    public function objectType();
}
