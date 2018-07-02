<?php

namespace CaT\Plugins\ComponentProviderExample\Settings; 

/**
 * Interface for DB handle of additional settings.
 */
interface DB {
	/**
	 * Update settings of an existing repo object.
	 *
	 * @param	ComponentProviderExample		$settings
     * @return  null
	 */
	public function update(ComponentProviderExample $settings);

	/**
	 * return ComponentProviderExample for $obj_id
	 *
	 * @param int $obj_id
	 *
	 * @return ComponentProviderExample
	 */
	public function getFor($obj_id);

	/**
	 * Delete all information of the given obj id
	 *
	 * @param 	int 	$obj_id
	 */
	public function deleteFor($obj_id);
}
