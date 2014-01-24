<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('Services/Tree/classes/class.ilTree.php');
require_once('Services/OrgUnit/classes/class.ilOrgUnit.php');

/**
* Organisation Unit Tree
*
* @author	Bjoern Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup ServicesOrgUnit
*/
class ilOrgUnitTree extends ilTree
{
	const TREE_ID = 1;
	
	const ROOT_UNIT_ID = 1;

	private $recursive_sub_tree = array();
	
	public function __construct()
	{
		parent::__construct( self::ROOT_UNIT_ID );

		$this->tree_id			= self::TREE_ID;

		$this->table_tree			= 'org_unit_tree';
		$this->table_obj_data		= 'org_unit_data';
		$this->table_obj_reference	= '';

		$this->tree_pk			= 'tree';
		$this->obj_pk			= 'ou_id';
		$this->ref_pk			= '';

		$this->gap = 0;
	}

	public function getRecursiveOrgUnitTree($root_node = self::ROOT_UNIT_ID)
	{
		$root_node_data = $this->getNodeData($root_node);
		$nodes = $this->getSubTree($root_node_data);

		$root_unit = null;
		$index = array();

		foreach($nodes as &$node)
		{
			$index[$node['child']] = $unit = ilOrgUnit::getInstance($node['child']);

			if($node['parent'])
			{
				$unit->setParent($index[$node['parent']]);
				$index[$node['parent']]->addChild($unit);
			}
			else $root_unit = $unit;
		}

		$root_unit->sortChilds();

		return $root_unit;
	}

}

?>
