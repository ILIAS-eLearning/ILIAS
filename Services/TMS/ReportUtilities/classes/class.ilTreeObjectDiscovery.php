<?php

require_once 'Services/Tree/classes/class.ilTree.php';
require_once 'Services/Object/classes/class.ilObjectFactory.php';

use ILIAS\TMS\ReportUtilities\TreeObjectDiscovery;

class ilTreeObjectDiscovery implements TreeObjectDiscovery
{

	protected $g_tree;

	public function __construct(\ilTree $tree)
	{
		$this->g_tree = $tree;
	}

	/**
	 * @inheritdoc
	 */
	public function getParentOfObjectOfType(\ilObject $object, $parent_type)
	{
		assert('is_string($parent_type)');
		$ref_id = $object->getRefId();
		$data = $this->g_tree->getParentNodeData($ref_id);

		while ($parent_type !== $data['type']
			&& (string)ROOT_FOLDER_ID !== (string)$data['ref_id']
		) {
			$data = $this->g_tree->getParentNodeData($data['ref_id']);
		}
		if((string)ROOT_FOLDER_ID === (string)$data['ref_id'] && $parent_type !== 'root') {
			return null;
		}
		return \ilObjectFactory::getInstanceByRefId($data['ref_id']);
	}

	/**
	 * @inheritdoc
	 */
	public function getAllChildrenIdsByTypeOfObject(\ilObject $object, $child_type)
	{
		assert('is_string($child_type)');
		$ref_id = (int)$object->getRefId();
		$subtree_nodes_data = $this->g_tree->getSubTree(
			$this->g_tree->getNodeData($ref_id),
			true,
			$child_type
		);
		$return = [];
		foreach ($subtree_nodes_data as $node) {
			$return[] = (int)$node["obj_id"];
		}
		return array_unique($return);
	}
	
}