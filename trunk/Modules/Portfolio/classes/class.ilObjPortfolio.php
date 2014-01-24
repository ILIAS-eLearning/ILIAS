<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Modules/Portfolio/classes/class.ilObjPortfolioBase.php";

/**
 * Portfolio 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesPortfolio
 */
class ilObjPortfolio extends ilObjPortfolioBase
{
	protected $default; // [bool]	

	function initType()
	{
		$this->type = "prtf";
	}

	//
	// PROPERTIES
	// 
	
	/**
	 * Set default
	 *
	 * @param bool $a_value
	 */
	function setDefault($a_value)
	{
		$this->default = (bool)$a_value;
	}

	/**
	 * Is default?
	 *
	 * @return bool
	 */
	function isDefault()
	{
		return $this->default;
	}
	
	
	//
	// CRUD
	//
			
	protected function doReadCustom(array $a_row)
	{		
		$this->setDefault((bool)$a_row["is_default"]);		
	}
	
	protected function doUpdate()
	{		
		// must be online to be default
		if(!$this->isOnline() && $this->isDefault())
		{
			$this->setDefault(false);
		}
	
		parent::doUpdate();
	}

	protected function doUpdateCustom(array &$a_fields)
	{
		$a_fields["is_default"] = array("integer", $this->isDefault());		
	}
	
	protected function deleteAllPages()
	{
		// delete pages
		include_once "Modules/Portfolio/classes/class.ilPortfolioPage.php";
		$pages = ilPortfolioPage::getAllPages($this->id);
		foreach($pages as $page)
		{
			$page_obj = new ilPortfolioPage($page["id"]);
			$page_obj->setPortfolioId($this->id);
			$page_obj->delete();
		}
	}
	
	
	// 
	// HELPER
	// 

	/**
	 * Set the user default portfolio
	 *
	 * @param int $a_user_id
	 * @param int $a_portfolio_id
	 */
	public static function setUserDefault($a_user_id, $a_portfolio_id = null)
	{
		global $ilDB;
		
		$all = array();
		foreach(self::getPortfoliosOfUser($a_user_id) as $item)
		{
			$all[] = $item["id"];
		}
		if($all)
		{
			$ilDB->manipulate("UPDATE usr_portfolio".
				" SET is_default = ".$ilDB->quote(false, "integer").
				" WHERE ".$ilDB->in("id", $all, "", "integer"));
		}

		if($a_portfolio_id)
		{
			$ilDB->manipulate("UPDATE usr_portfolio".
				" SET is_default = ".$ilDB->quote(true, "integer").
				" WHERE id = ".$ilDB->quote($a_portfolio_id, "integer"));
		}
	}

	/**
	 * Get views of user
	 *
	 * @param int $a_user_id
	 * @return array
	 */
	static function getPortfoliosOfUser($a_user_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT up.*,od.title,od.description".
			" FROM usr_portfolio up".
			" JOIN object_data od ON (up.id = od.obj_id)".
			" WHERE od.owner = ".$ilDB->quote($a_user_id, "integer").
			" AND od.type = ".$ilDB->quote("prtf", "text").
			" ORDER BY od.title");
		$res = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$res[] = $rec;
		}
		return $res;
	}
	
	/**
	 * Get default portfolio of user
	 * 
	 * @param type $a_user_id
	 * @return int
	 */
	static function getDefaultPortfolio($a_user_id)
	{
		global $ilDB, $ilSetting;
		
		if(!$ilSetting->get('user_portfolios'))
		{
			return;
		}
			
		$set = $ilDB->query("SELECT up.id FROM usr_portfolio up".
			" JOIN object_data od ON (up.id = od.obj_id)".
			" WHERE od.owner = ".$ilDB->quote($a_user_id, "integer").
			" AND up.is_default = ".$ilDB->quote(1, "integer"));
		$res = $ilDB->fetchAssoc($set);
		if($res["id"])
		{
			return $res["id"];
		}		
	}
	
	/**
	 * Delete all portfolio data for user
	 * 
	 * @param int $a_user_id 
	 */
	public static function deleteUserPortfolios($a_user_id)
	{
		$all = self::getPortfoliosOfUser($a_user_id);
		if($all)
		{
			include_once "Modules/Portfolio/classes/class.ilPortfolioAccessHandler.php";
			$access_handler = new ilPortfolioAccessHandler();			
			
			foreach($all as $item)
			{
				$access_handler->removePermission($item["id"]);		
				
				$portfolio = new self($item["id"], false);
				$portfolio->delete();								
			}
		}
	}
	
	public function deleteImage()
	{
		if($this->id)
		{
			parent::deleteImage();		
			$this->handleQuotaUpdate();
		}
	}
	
	function uploadImage(array $a_upload)
	{
		if(parent::uploadImage($a_upload))
		{
			$this->handleQuotaUpdate();
			return true;
		}
		return false;
	}
	
	protected function handleQuotaUpdate()
	{										
		include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
		ilDiskQuotaHandler::handleUpdatedSourceObject($this->getType(), 
			$this->getId(),
			ilUtil::dirsize($this->initStorage($this->getId())), 
			array($this->getId()),
			true);	
	}
	
	public static function getAvailablePortfolioLinksForUserIds(array $a_owner_ids, $a_back_url = null)
	{
		$res = array();
		
		include_once "Modules/Portfolio/classes/class.ilPortfolioAccessHandler.php";
		$access_handler = new ilPortfolioAccessHandler();	
		
		$params = null;
		if($a_back_url)
		{
			$params = array("back_url"=>rawurlencode($a_back_url));
		}
		
		include_once "Services/Link/classes/class.ilLink.php";
		foreach($access_handler->getShardObjectsDataForUserIds($a_owner_ids) as $owner_id => $items)
		{
			foreach($items as $id => $title)
			{
				$url = ilLink::_getLink($id, 'prtf', $params);
				$res[$owner_id][$url] = $title;				
			}			
		}		
		
		return $res;
	}
}

?>