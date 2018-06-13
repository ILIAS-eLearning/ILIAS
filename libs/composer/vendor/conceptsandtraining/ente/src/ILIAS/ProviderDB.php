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
 * A database that stores ILIAS providers.
 */
interface ProviderDB {
    /**
     * Create a new separated unbound provider for the given owner.
     *
     * The provider will belong to objects above the $owner in the tree that also
     * have the type $obj_type.
     *
     * @param   \ilObject   $owner
     * @param   string      $obj_type
     * @param   string      $class_name
     * @param   string      $include_path
     * @return  SeparatedUnboundProvider
     */
    public function createSeparatedUnboundProvider(\ilObject $owner, $obj_type, $class_name, $include_path);

    /**
     * Create a new shared unbound provider for the given owner.
     *
     * The provider will be belong to objects above the $owner in the tree that also
     * have the type $obj_type.
	 *
     * @param   \ilObject   $owner
     * @param   string      $obj_type
     * @param   string      $class_name
     * @param   string      $include_path
     * @return  SharedUnboundProvider
     */
    public function createSharedUnboundProvider(\ilObject $owner, $obj_type, $class_name, $include_path);

    /**
     * Load the unbound provider with the given id.
     *
     * @param   int         $id
     * @throws  \InvalidArgumentException if the provider with the supplied id does not exist.
     * @return  UnboundProvider
     */
    public function load($id);

    /**
     * Delete a given unbound provider.
     *
     * @param   UnboundProvider    $provider
     * @param   UnboundProvider    $provider
     * @return  null
     */
    public function delete(UnboundProvider $provider, \ilObject $owner);

    /**
     * Update the given unbound provider.
     *
     * The only thing that may be updated are the components that are provided.
     *
     * @param   UnboundProvider    $provider
     * @return  null
     */
    public function update(UnboundProvider $provider);

    /**
     * Get all unbound providers of a given owner.
     *
     * @param   \ilObject   $owner
     * @return  UnboundProvider[]
     */
    public function unboundProvidersOf(\ilObject $owner);

    /**
     * Get all providers for the given object.
     *
     * @param   \ilObject   $object
     * @param   string|null $component_type
     * @return  Provider[]
     */
    public function providersFor(\ilObject $object, $component_type = null);
}
