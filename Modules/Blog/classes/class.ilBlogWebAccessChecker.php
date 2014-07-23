<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/FileSystem/classes/class.ilFileSystemStorageWebAccessChecker.php";

/**
* Class ilBlogWebAccessChecker
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjFolder.php 25528 2010-09-03 10:37:11Z smeyer $
*
* @ingroup ModulesBlog
*/
class ilBlogWebAccessChecker extends ilFileSystemStorageWebAccessChecker
{
	protected $wsp_id; // [int]
	
	public function isValidPath(array $a_path)
	{		
		if(parent::isValidPath($a_path))		
		{	
			// personal workspace blog?
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
			$this->tree = new ilWorkspaceTree(0);		
			$node_id = $this->tree->lookupNodeId($this->object_id);

			if($node_id)
			{				
				$this->wsp_id = $node_id;
				$this->object_id = null; // force custom check
			}

			return true;
		}
	}
	
	public function checkAccess(array $a_users)
	{
		if($this->wsp_id)
		{
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";						
			foreach ($a_users as $user_id)
			{								
				$access_handler = new ilWorkspaceAccessHandler($this->tree);
				if ($access_handler->checkAccessOfUser($this->tree, $user_id, "read", "view", $this->wsp_id, "blog"))
				{
					return true;
				}
			}		
		}
	}
}

