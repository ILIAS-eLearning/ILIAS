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
class ilObjPortfolioTemplate extends ilObjPortfolioBase
{	
	protected $activation_limited; // [bool]
	protected $activation_visibility; // [bool]
	protected $activation_starting_time; // [integer]
	protected $activation_ending_time; // [integer]
	
	public function initType()
	{
		$this->type = "prtt";
	}
	
	protected function doRead()
	{	
		parent::doRead();
		
		if($this->ref_id)
		{
			include_once "./Services/Object/classes/class.ilObjectActivation.php";
			$activation = ilObjectActivation::getItem($this->ref_id);			
			switch($activation["timing_type"])
			{				
				case ilObjectActivation::TIMINGS_ACTIVATION:	
					$this->setActivationLimited(true);
					$this->setActivationStartDate($activation["timing_start"]);
					$this->setActivationEndDate($activation["timing_end"]);
					$this->setActivationVisibility($activation["visible"]);
					break;
				
				default:			
					$this->setActivationLimited(false);
					break;							
			}
		}
	}
	
	protected function doCreate()
	{
		parent::doCreate();
		$this->updateActivation();
	}
	
	protected function doUpdate()
	{
		parent::doUpdate();
		$this->updateActivation();
	}
	
	protected function deleteAllPages()
	{
		// delete pages
		include_once "Modules/Portfolio/classes/class.ilPortfolioTemplatePage.php";
		$pages = ilPortfolioTemplatePage::getAllPages($this->id);
		foreach($pages as $page)
		{
			$page_obj = new ilPortfolioTemplatePage($page["id"]);
			$page_obj->setPortfolioId($this->id);
			$page_obj->delete();
		}
	}
	
	protected function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
	{
		self::cloneBasics($this, $new_obj);
		
		// copy pages
		include_once "Modules/Portfolio/classes/class.ilPortfolioTemplatePage.php";
		foreach(ilPortfolioPage::getAllPages($this->getId()) as $page)
		{			
			// see ilObjWiki::cloneObject();
			
			$page = new ilPortfolioTemplatePage($page["id"]);
			
			$new_page = new ilPortfolioTemplatePage();
			$new_page->setPortfolioId($new_obj->getId());		
			$new_page->setTitle($page->getTitle());
			$new_page->setType($page->getType());
			$new_page->setOrderNr($page->getOrderNr());
			$new_page->create();
			
			$page->copy($new_page->getId(), "", 0, true);	
		}	
	}
	
		
	//
	// ACTIVATION
	// 		
	
	protected function updateActivation()
	{
		// moved activation to ilObjectActivation
		if($this->ref_id)
		{
			include_once "./Services/Object/classes/class.ilObjectActivation.php";		
			ilObjectActivation::getItem($this->ref_id);
			
			$item = new ilObjectActivation;			
			if(!$this->isActivationLimited())
			{
				$item->setTimingType(ilObjectActivation::TIMINGS_DEACTIVATED);
			}
			else
			{				
				$item->setTimingType(ilObjectActivation::TIMINGS_ACTIVATION);
				$item->setTimingStart($this->getActivationStartDate());
				$item->setTimingEnd($this->getActivationEndDate());
				$item->toggleVisible($this->getActivationVisibility());
			}						
			
			$item->update($this->ref_id);		
		}
	}
	
	public function isActivationLimited()
	{
	   return (bool)$this->activation_limited;
	}
	
	public function setActivationLimited($a_value)
	{
	   $this->activation_limited = (bool)$a_value;
	}
	
	public function setActivationVisibility($a_value)
	{
		$this->activation_visibility = (bool) $a_value;
	}
	
	public function getActivationVisibility()
	{
		return $this->activation_visibility;
	}
	
	public function setActivationStartDate($starting_time = NULL)
	{
		$this->activation_starting_time = $starting_time;
	}

	public function setActivationEndDate($ending_time = NULL)
	{
		$this->activation_ending_time = $ending_time;
	}
	
	public function getActivationStartDate()
	{
		return (strlen($this->activation_starting_time)) ? $this->activation_starting_time : NULL;
	}

	public function getActivationEndDate()
	{
		return (strlen($this->activation_ending_time)) ? $this->activation_ending_time : NULL;
	}
	
	
	//
	// HELPER
	//
	
	public static function getAvailablePortfolioTemplates($a_permission = "read")
	{
		global $ilUser, $ilAccess;
		
		$res = array();
		
		foreach(ilObject::_getObjectsByType("prtt") as $obj)
		{
			$has_permission = false;
			
			if($obj["owner"] == $ilUser->getId() && false)
			{
				$has_permission = true;
			}
			else
			{
				foreach(ilObject::_getAllReferences($obj["obj_id"]) as $ref_id)
				{
					if($ilAccess->checkAccess($a_permission, "", $ref_id))
					{
						$has_permission = true;
						break;
					}											
				}				
			}
			
			if($has_permission)
			{
				$res[$obj["obj_id"]] = $obj["title"];
			}			
		}
		
		asort($res);		
		return $res;
	}
}

?>