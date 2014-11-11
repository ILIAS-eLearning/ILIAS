<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * Portfolio page configuration 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesPortfolio
 */
class ilPortfolioPageConfig extends ilPageConfig
{
	/**
	 * Init
	 */
	function init()
	{
		global $ilSetting, $rbacsystem;
		
		$prfa_set = new ilSetting("prfa");
		$this->setPreventHTMLUnmasking(!(bool)$prfa_set->get("mask", false));
				
		$this->setEnableInternalLinks(false);
		$this->setEnablePCType("Profile", true);
		
		if(!$ilSetting->get('disable_wsp_certificates'))
		{
			$this->setEnablePCType("Verification", true);
		}
		$skmg_set = new ilSetting("skmg");
		if($skmg_set->get("enable_skmg"))
		{
			$this->setEnablePCType("Skills", true);
		}
			
		include_once './Services/Calendar/classes/class.ilCalendarUserSettings.php';
		$settings = ilCalendarSettings::_getInstance();
		if($settings->isEnabled() &&
			$rbacsystem->checkAccess('add_consultation_hours', $settings->getCalendarSettingsId()) &&
			$settings->areConsultationHoursEnabled())
		{
			$this->setEnablePCType("ConsultationHours", true);			
		}		
		
		$prfa_set = new ilSetting("prfa");							
		if($prfa_set->get("mycrs", true))
		{
			$this->setEnablePCType("MyCourses", true);	
		}
	}	
}

?>
