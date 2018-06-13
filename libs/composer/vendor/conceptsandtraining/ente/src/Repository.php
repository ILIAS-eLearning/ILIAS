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
 * The repository is the central location to request providers for entities.
 *
 * ARCH:
 *  - This deviates from the standard pattern for entity component model, which
 *    doesn't have a provider. The provider was introduced to render a central
 *    storage mechanism for all components unnecessary.
 */
interface Repository {
    /**
     * Get providers for an entity, possibly filtered by a component type.
     *
     * @param   Entity      $entity
     * @param   string|null $component_type
     * @return  Provider[]
     */
    public function providersForEntity(Entity $entity, $component_type = null);

	/**
	 * Get components for the entity, possibly filtered by component type.
	 *
	 * @param	Entity		$entity
	 * @param	string|null	$component_type
	 * @return	Component[]
	 */
	public function componentsForEntity(Entity $entity, $component_type = null);
}
