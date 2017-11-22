<?php
namespace ILIAS\TMS\ReportUtilities;

/**
 * Search a tre for objects by type and relative position.
 */
interface TreeObjectDiscovery
{
	/**
	 * Get the first parent node of $object, which is of type $parent_type.
	 *
	 * @param	\ilObject	$object
	 * @param	string	$parent_type
	 * @return	\ilObject/null
	 */
	public function getParentOfObjectOfType(\ilObject $object, $parent_type);

	/**
	 * Get all child-ids of $node, which have type $child_type.
	 *
	 * @param	\ilObject	$object
	 * @param	string	$child_type
	 * @return	int[]
	 */
	public function getAllChildrenIdsByTypeOfObject(\ilObject $object, $child_type);
}