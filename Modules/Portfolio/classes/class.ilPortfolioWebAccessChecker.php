<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/FileSystem/classes/class.ilFileSystemStorageWebAccessChecker.php";

/**
* Class ilPortfolioWebAccessChecker
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjFolder.php 25528 2010-09-03 10:37:11Z smeyer $
*
* @ingroup ModulesPortfolio
*/
class ilPortfolioWebAccessChecker extends ilFileSystemStorageWebAccessChecker
{
	protected $prtf_id; // [int]
	
	public function isValidPath(array $a_path)
	{
		if(parent::isValidPath($a_path))	
		{	
			// portfolio (not in repository)?
			if(ilObject::_lookupType($this->object_id) == "prtf")
			{
				$this->prtf_id = $this->object_id;
				$this->object_id = null; // force custom check
			}
			
			return true;
		}
	}
	
	public function checkAccess(array $a_users)
	{
		include_once "Modules/Portfolio/classes/class.ilPortfolioAccessHandler.php";
		$access_handler = new ilPortfolioAccessHandler();		
		foreach ($a_users as $user_id)
		{				
			if ($access_handler->checkAccessOfUser($user_id, "read", "view", $this->prtf_id, "prtf"))
			{
				return true;
			}
		}		
		return false;	
	}
}

