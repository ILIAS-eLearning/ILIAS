<?php

namespace ILIAS\TMS\CourseCreation;

trait ChildAssistant
{
	/**
	 * Get all children by type recursive
	 *
	 * @param int 	$ref_id
	 * @param string 	$search_type
	 *
	 * @return Object 	of search type
	 */
	protected function getAllChildrenOfByType($ref_id, $search_type)
	{
		$g_tree = $this->getDIC()->repositoryTree();
		$g_objDefinition = $this->getDIC()["objDefinition"];
		$childs = $g_tree->getChilds($ref_id);
		$ret = array();

		foreach ($childs as $child) {
			$type = $child["type"];
			if ($type == $search_type) {
				$ret[] = \ilObjectFactory::getInstanceByRefId($child["child"]);
			}

			if ($g_objDefinition->isContainer($type)) {
				$rec_ret = $this->getAllChildrenOfByType($child["child"], $search_type);
				if (! is_null($rec_ret)) {
					$ret = array_merge($ret, $rec_ret);
				}
			}
		}

		return $ret;
	}

	/**
	 * Checks the access to object for current user
	 *
	 * @param string[] 	$permissions
	 * @param int 	$ref_id
	 *
	 * @return bool
	 */
	protected function checkAccess(array $permissions, $ref_id)
	{
		$access = $this->getDIC()->access();
		foreach ($permissions as $permission) {
			if (!$access->checkAccessOfUser($this->user_id, $permission, "", $ref_id)) {
				return false;
			}
		}

		return true;
	}
}
