<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/interfaces/interface.ilECSCommandQueueHandler.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCmsTreeCommandQueueHandler implements ilECSCommandQueueHandler
{
	private $server = null;
	private $mid = 0;
	
	
	/**
	 * Constructor
	 */
	public function __construct(ilECSSetting $server)
	{
		$this->server = $server;
		$this->init();
	}
	
	/**
	 * Get server
	 * @return ilECSServerSetting
	 */
	public function getServer()
	{
		return $this->server;
	}


	/**
	 * Handle create
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function handleCreate(ilECSSetting $server, $a_content_id)
	{
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSDirectoryTreeConnector.php';

		try 
		{
			$dir_reader = new ilECSDirectoryTreeConnector($this->getServer());
			$nodes = $dir_reader->getDirectoryTree($a_content_id);
		}
		catch(ilECSConnectorException $e) 
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Tree creation failed  with mesage ' . $e->getMessage());
			return false;
		}
		
		$tree = new ilECSCmsTree($a_content_id);
		foreach((array) $nodes as $node)
		{
			// Add data entry
			$data = new ilECSCmsData();
			$data->setServerId($this->getServer()->getServerId());
			$data->setMid($this->mid);
			$data->setCmsId($node->id);
			$data->setTreeId($a_content_id);
			$data->setTitle($node->title);
			$data->setTerm($node->term);
			$data->save();

			// add to tree
			if($node->parent->id)
			{
				$parent_id = ilECSCmsData::lookupObjId(
					$this->getServer()->getServerId(),
					$this->mid,
					$a_content_id,
					(int) $node->parent->id
				);
				$tree->insertNode($data->getObjId(), $parent_id);
			}
			else
			{
				$tree->insertRootNode($a_content_id, $data->getObjId());
				$tree->setRootId($data->getObjId());
			}
		}
		return true;
	}

	/**
	 * Handle delete
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function handleDelete(ilECSSetting $server, $a_content_id)
	{
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
		$data = new ilECSCmsData();
		$data->setServerId($this->getServer()->getServerId());
		$data->setMid($this->mid);
		$data->setTreeId($a_content_id);
		$data->deleteTree();
		
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
		$tree = new ilECSCmsTree($a_content_id);
		$tree->deleteTree($tree->getNodeData(ilECSCmsTree::lookupRootId($a_content_id)));
		
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
		ilECSNodeMappingAssignments::deleteMappings(
			$this->getServer()->getServerId(),
			$this->mid,
			$a_content_id
		);
		return true;
	}

	/**
	 * Handle update
	 * @param ilECSSetting $server
	 * @param type $a_content_id
	 */
	public function handleUpdate(ilECSSetting $server, $a_content_id)
	{
		// 1) Delete the cms tree table entries
		// 2) Add cms tree table entries
		// 2) Replace the cms data table entries
		// 3) Remove deprecated entries
		// 4) Sync tree
		
		try 
		{
			include_once './Services/WebServices/ECS/classes/Tree/class.ilECSDirectoryTreeConnector.php';
			$dir_reader = new ilECSDirectoryTreeConnector($this->getServer());
			$nodes = $dir_reader->getDirectoryTree($a_content_id);
		}
		catch(ilECSConnectorException $e) 
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Tree creation failed  with mesage ' . $e->getMessage());
			return false;
		}

		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
		$tree = new ilECSCmsTree($a_content_id);
		$tree->deleteTree($tree->getNodeData(ilECSCmsTree::lookupRootId($a_content_id)));
		
		foreach((array) $nodes as $node)
		{
			include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
			$data_obj_id = ilECSCmsData::lookupObjId(
					$this->getServer()->getServerId(),
					$this->mid,
					$a_content_id,
					$node->id
				);
			
			// update data entry
			$data = new ilECSCmsData($data_obj_id);
			$data->setTitle($node->title);
			$data->setTerm($node->term);
			
			if($data_obj_id)
			{
				$data->update();
			}
			else
			{
				$data->setCmsId($node->id);
				$data->setMid($this->mid);
				$data->setServerId($this->getServer()->getServerId());
				$data->setTreeId($a_content_id);
				$data->save();
				
				$data_obj_id = $data->getObjId();
			}
			

			// add to tree
			if($node->parent->id)
			{
				$parent_id = ilECSCmsData::lookupObjId(
					$this->getServer()->getServerId(),
					$this->mid,
					$a_content_id,
					(int) $node->parent->id
				);
				$tree->insertNode($data->getObjId(), $parent_id);
			}
			else
			{
				$tree->insertRootNode($a_content_id, $data->getObjId());
				$tree->setRootId($data->getObjId());
			}
		}
		
		// Sync tree
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTreeSynchronizer.php';
		$sync = new ilECSCmsTreeSynchronizer(
				$this->getServer(),
				$this->mid,
				$a_content_id
			);
		$sync->sync();
		
		return true;
	}
	
	/**
	 * init handler
	 */
	private function init()
	{
		include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php';
		$this->mid = ilECSParticipantSettings::loookupCmsMid($this->getServer()->getServerId());
	}
}
?>
