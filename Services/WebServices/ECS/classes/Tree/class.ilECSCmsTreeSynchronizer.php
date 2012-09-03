<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignment.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesWebServicesECS
 */
class ilECSCmsTreeSynchronizer 
{
	private $server = null;
	private $mid = null;
	private $tree_id = null;
	private $tree = null;
	
	private $default_settings = array();
	
	
	/**
	 * Constructor
	 */
	public function __construct(ilECSSetting $server, $mid, $tree_id)
	{
		$this->server = $server;
		$this->mid = $mid;
		$this->tree = new ilECSCmsTree($tree_id);
		$this->tree_id = $tree_id;
	}
	
	/**
	 * Get server
	 * @return ilECSSetting
	 */
	public function getServer()
	{
		return $this->server;
	}
	
	/**
	 * @return ilECSCmsTree
	 */
	public function getTree()
	{
		return $this->tree;
	}
	
	/**
	 * Get default settings
	 * @return type
	 */
	public function getDefaultSettings()
	{
		return (array) $this->default_settings;
	}
	
	/**
	 * Synchronize tree
	 * 
	 * @return boolean
	 */
	public function sync()
	{
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';
		$this->default_settings = ilECSNodeMappingAssignments::lookupSettings(
			$this->getServer()->getServerId(),
			$this->mid,
			$this->tree_id,
			0
		);
		
		// return if setting is false => no configuration done
		if(!$this->getDefaultSettings())
		{
			return true;
		}
			
		// lookup obj id of root node
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
		
		$root = ilECSCmsTree::lookupRootId($this->tree_id);
		$this->syncNode($root,0);
	}
	
	/**
	 * Sync node
	 * @param type $cs_id
	 * @param type $setting
	 */
	protected function syncNode($tree_obj_id,$parent_id,$a_mapped = false)
	{
		$childs = $this->getTree()->getChilds($tree_obj_id);
		
		$assignment = new ilECSNodeMappingAssignment(
				$this->getServer()->getServerId(),
				$this->mid,
				$this->tree_id,
				$tree_obj_id);
		
		if($assignment->getRefId())
		{
			$parent_id = $assignment->getRefId();
		}
		
		
		// information for deeper levels
		if($assignment->isMapped())
		{
			$a_mapped = true;
		}
		
		if($a_mapped)
		{
			$parent_id = $this->syncCategory($assignment,$parent_id);
		}
				
		// iterate through childs
		foreach($childs as $node)
		{
			$this->syncNode($node['child'],$parent_id,$a_mapped);
		}
		return true;
	}
	
	/**
	 * Sync category
	 * @param ilECSNodeMappingAssignment $ass
	 */
	protected function syncCategory(ilECSNodeMappingAssignment $ass,$parent_id)
	{
		include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
		$data = new ilECSCmsData($ass->getCSId());

		// Check if node is imported => create
		// perform title update
		// perform position update
		include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
		$obj_id = ilECSImport::_lookupObjId(
				$this->getServer()->getServerId(),
				$data->getCmsId(),
				$this->mid);
		if($obj_id)
		{
			$refs = ilObject::_getAllReferences($obj_id);
			$ref_id = end($refs);
			
			
			$cat = ilObjectFactory::getInstanceByRefId($ref_id,false);
			if(($cat instanceof ilObject) and $this->default_settings['title_update'])
			{
				$GLOBALS['ilLog']->write(__METHOD__.': Updating cms category ');
				
				$cat->updateTranslation(
						$data->getTitle(),
						$cat->getLongDescription(),
						$GLOBALS['lng']->getDefaultLanguage(),
						$GLOBALS['lng']->getDefaultLanguage()
					);
			}
			else
			{
				$GLOBALS['ilLog']->write(__METHOD__.': Updating cms category -> nothing to do');
			}
			return $ref_id;
		}
		else
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Creating new cms category');
			
			// Create category
			include_once './Modules/Category/classes/class.ilObjCategory.php';
			$cat = new ilObjCategory();
			$cat->setTitle($data->getTitle());
			$cat->create(); // true for upload
			$cat->createReference();
			$cat->putInTree($parent_id);
			$cat->setPermissions($parent_id);
			$cat->updateTranslation(
					$data->getTitle(),
					$cat->getLongDescription(),
					$GLOBALS['lng']->getDefaultLanguage(),
					$GLOBALS['lng']->getDefaultLanguage()
				);
			
			// set imported
			$import = new ilECSImport(
					$this->getServer()->getServerId(),
					$cat->getId()
				);
			$import->setMID($this->mid);
			$import->setEContentId($data->getCmsId());
			$import->setImported(true);
			$import->save();
			
			return $cat->getRefId();
		}
	}
}
?>
