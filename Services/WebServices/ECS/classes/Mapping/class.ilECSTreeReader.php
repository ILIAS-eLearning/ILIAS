<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Reads and store cms tree in database
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSTreeReader
{
	private $server_id;
	private $mid;

	/**
	 * Constructor
	 * @param <type> $server_id
	 * @param <type> $mid
	 */
	public function __construct($server_id, $mid)
	{
		$this->server_id = $server_id;
		$this->mid = $mid;
	}



	/**
	 * Read trees from ecs
	 *
	 * @throws ilECSConnectorException
	 */
	public function read()
	{
		try
		{
			include_once './Services/WebServices/ECS/classes/class.ilECSDirectoryTreeConnector.php';
			$dir_reader = new ilECSDirectoryTreeConnector(
					ilECSSetting::getInstanceByServerId($this->server_id)
			);
			$trees = $dir_reader->getDirectoryTrees();

			if($trees instanceof ilECSUriList)
			{
				foreach((array) $trees->getLinkIds() as $tree_id)
				{
					include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSCmsData.php';
					include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSCmsTree.php';

					if(!ilECSCmsData::treeExists($this->server_id, $this->mid, $tree_id))
					{
						$this->storeTree($tree_id, $dir_reader->getDirectoryTree($tree_id));
					}
				}
			}
		}
		catch(ilECSConnectorException $e)
		{
			throw $e;
		}
	}

	protected function storeTree($tree_id, $a_nodes)
	{
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSCmsData.php';
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSCmsTree.php';

		$tree = new ilECSCmsTree($tree_id);
		foreach((array) $a_nodes as $node)
		{
			// Add data entry
			$data = new ilECSCmsData();
			$data->setServerId($this->server_id);
			$data->setMid($this->mid);
			$data->setCmsId($node->id);
			$data->setTreeId($tree_id);
			$data->setTitle($node->title);
			$data->setTerm($node->term);
			$data->save();

			// add to tree
			if($node->parent->id)
			{
				$parent_id = ilECSCmsData::lookupObjId(
					$this->server_id,
					$this->mid,
					$tree_id,
					(int) $node->parent->id
				);
				$tree->insertNode($data->getObjId(), $parent_id);
			}
			else
			{
				$tree->insertRootNode($tree_id, $data->getObjId());
				$tree->setRootId($data->getObjId());
			}
		}
	}
}
?>
