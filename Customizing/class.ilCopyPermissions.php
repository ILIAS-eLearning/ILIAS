<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Copy object permissions from global/local roles after copying objects
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilCopyPermissions
{
	protected $copy_options; // [ilCopyWizardOptions]
	protected $source_root_ref_id; // [int]
	protected $role_map; // [array]
	protected $role_local_map; // [array]

	/**
	 * Constructor
	 * 
	 * @param ilCopyWizardOptions $a_cp_options
	 * @return object
	 */
	public function __construct(ilCopyWizardOptions $a_cp_options)
	{
		$this->copy_options = $a_cp_options;
		$this->source_root_ref_id = $a_cp_options->getOptions(ilCopyWizardOptions::ROOT_NODE);
		$this->source_root_ref_id = $this->source_root_ref_id[0];
		
		$this->log(__METHOD__.": Copy permissions initialized (root: ".$this->getSourceRootRefId());
	}
	
	/**
	 * Add log entry
	 *
	 * @param string $a_message
	 */
	protected function log($a_message)
	{
		global $ilLog;
		
		if(DEVMODE)
		{			
			if(is_array($a_message))
			{
				$a_message = print_r($a_message, true);
			}
			$ilLog->write($a_message);
		}
	}
	
	/**
	 * Get copy wizard options instance
	 * 
	 * @return ilCopyWizardOptions
	 */
	protected function getCopyWizardOptions()
	{
		return $this->copy_options;
	}
	
	/**
	 * Get root node of source branch
	 * 
	 * @return int
	 */
	protected function getSourceRootRefId()
	{
		return $this->source_root_ref_id;
	}
	
	/**
	 * Clone all permissions from source branch to target (after copying)
	 * 	
	 * @param int $a_source_ref_id
	 */
	public function transferPermissions($a_source_ref_id = null)
	{
		global $tree;
		
		// start from source root
		if(!$a_source_ref_id)
		{
			$a_source_ref_id = $this->getSourceRootRefId();
		}
		
		$this->log(__METHOD__.": Copy permissions: transfer init ".$a_source_ref_id);
		
		$mappings = $this->getCopyWizardOptions()->getMappings();						
		
		// has current node been copied at all?
		if(!$a_source_ref_id ||
			!array_key_exists($a_source_ref_id, $mappings))
		{
			return;
		}
			
		$target_ref_id = $mappings[$a_source_ref_id];
			
		$this->adoptPermissions($a_source_ref_id, $target_ref_id);
		
		foreach($tree->getChilds($a_source_ref_id) as $node)
		{					
			// 4.4.x: mind the role folder
			if($node["type"] != "rolf")
			{
				$this->transferPermissions($node["child"]);
			}
		}
		
		$this->log(__METHOD__.": Copy permissions: transfer end ".$a_source_ref_id);
	}		
	
	/**
	 * Clone permissions for single object (after copying)
	 * 
	 * @param type $a_source_ref_id
	 * @param type $a_target_ref_id
	 */
	protected function adoptPermissions($a_source_ref_id, $a_target_ref_id)
	{
		global $rbacreview, $rbacadmin, $tree;
		
		$this->log(__METHOD__.": Copy permissions: adopt init ".$a_source_ref_id." / ".$a_target_ref_id);
		
		// get target parent roles
		foreach ($rbacreview->getParentRoleIds($a_target_ref_id) as $role)
		{						
			// adding "parent/rolf" because names are not unique
			$this->role_map[$role["parent"]][ilObject::_lookupTitle($role["obj_id"])] = $role["obj_id"];
		}
	
		$this->log($this->role_map);
		$this->log($this->role_local_map);
		
		$mappings = $this->getCopyWizardOptions()->getMappings();
		$this->log($mappings);
		
		// process source parent roles
		foreach ($rbacreview->getParentRoleIds($a_source_ref_id) as $role)
		{									
			$source_role_id = $role["obj_id"];	
			$source_rolf = $role["parent"]; // 4.4.x: rolf vs ref_id - not needed in 5.0+
			$source_parent = $tree->getParentId($source_rolf);
			$source_title = ilObject::_lookupTitle($source_role_id);				
			
			$this->log(__METHOD__.": Copy permissions: parsing role ".$source_role_id." / ".$source_title." (source rolf: ".$source_rolf.")");			
			
			$target_role_id = null;
			
			// re-using parent (auto) local role
			if(is_array($this->role_local_map) &&
				array_key_exists($source_title, $this->role_local_map))
			{				
				$target_role_id = $this->role_local_map[$source_title];
				
				$this->log(__METHOD__.": Copy permissions: re-using local role ".$source_title);							
			}
			// role outside of copy context/branch
			else if(isset($this->role_map[$source_rolf][$source_title]))
			{
				$target_role_id = $this->role_map[$source_rolf][$source_title];			
				
				$this->log(__METHOD__.": Copy permissions: found global ".$source_title." (source rolf: ".$source_rolf.")");		
			}
			// role inside copy context/branch
			else if(array_key_exists($source_parent, $mappings))
			{
				$target_rolf = $rbacreview->getRoleFolderIdOfObject($mappings[$source_parent]);	
				
				$this->log(__METHOD__.": Copy permissions: parsing parent role ".$source_role_id." / ".$source_title." (target rolf: ".$target_rolf.")");		
				
				// non-auto role, should already exists in target branch
				if(isset($this->role_map[$target_rolf][$source_title]))
				{
					$target_role_id = $this->role_map[$target_rolf][$source_title];
					
					$this->log(__METHOD__.": Copy permissions: manual local - ".$source_title);		
				}				
				// map (auto) local role to new one
				else if(substr($source_title, 0, 3) == "il_")
				{
					$parts = explode("_", $source_title);
					array_pop($parts);
					$target_title = implode("_", $parts)."_".$a_target_ref_id;											
				
					if(isset($this->role_map[$target_rolf][$target_title]))
					{
						$target_role_id = $this->role_map[$target_rolf][$target_title];
						
						$this->log(__METHOD__.": Copy permissions: auto local - ".$source_title." / ".$target_title);		

						// keep mapped title for re-use (see above)
						$this->role_local_map[$source_title] = $target_role_id;			
					}
				}
			}
			
			if($target_role_id)
			{								
				$source_rolf = $rbacreview->getRoleFolderIdOfObject($a_source_ref_id);
				$target_rolf = $rbacreview->getRoleFolderIdOfObject($a_target_ref_id);		
				
				// Create role folder		
				if(!$target_rolf)
				{
					// see ilRbacAdmin::copyLocalRoles()
					$tmp_obj = ilObjectFactory::getInstanceByRefId($a_target_ref_id, false);
					if(is_object($tmp_obj))
					{					
						$rolf = $tmp_obj->createRoleFolder();
						$target_rolf = $rolf->getRefId();
						$this->log(__METHOD__.": Created new role folder - ".$a_target_ref_id." / ".$target_rolf);
					}
				}
				
				if($source_rolf && 
					$target_rolf)
				{
					$this->log(__METHOD__.": Copy permissions: copy role permissions - ".$source_rolf."|".$source_role_id." / ".$target_rolf."|".$target_role_id);				
					
					$rbacadmin->copyRolePermissions($source_role_id, $source_rolf, $target_rolf, $target_role_id, true);	
				}	
				else
				{
					$this->log(__METHOD__.": Copy permissions: missing role folder - src:".$source_rolf." / tgt: ".$target_rolf);
				}
			}	
			else
			{
				$this->log(__METHOD__.": Copy permissions: could not parse role ".$source_role_id." / ".$source_title);		
			}
		}	
		
		$this->log(__METHOD__.": Copy permissions: adopt end ".$a_source_ref_id." / ".$a_target_ref_id);
	}		
}