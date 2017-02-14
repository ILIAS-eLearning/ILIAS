<?php

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;

interface ComponentIdRegistryInterface {

	/**
	 * Register a generated ID for a component. Note: The same component can have multiple IDs,
	 * if rendered multiple times.
	 *
	 * @param Component $component
	 * @param string    $id
	 */
	public function register(Component $component, $id);


	/**
	 * Get the IDs of the given component, returns an empty array if no IDs have been registered
	 *
	 * @param Component $component
	 * @return array
	 */
	public function getIds(Component $component);

}