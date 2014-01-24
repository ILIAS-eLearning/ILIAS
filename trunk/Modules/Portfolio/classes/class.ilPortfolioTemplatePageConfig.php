<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Portfolio/classes/class.ilPortfolioPageConfig.php");

/**
 * Portfolio template page configuration 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ModulesPortfolio
 */
class ilPortfolioTemplatePageConfig extends ilPortfolioPageConfig
{
	public function init()
	{
		parent::init();
				
		$this->setEnablePCType("Verification", false);
		$this->setEnablePCType("PlaceHolder", true);		
	}
	
	public function getAvailablePlaceholderTypes()
	{		
		global $ilSetting;
		
		// no questions
		$all = array(
			ilPCPlaceHolderGUI::TYPE_TEXT, 
			ilPCPlaceHolderGUI::TYPE_MEDIA			
		);		
		
		if(!$ilSetting->get('disable_wsp_certificates'))
		{
			$all[] = ilPCPlaceHolderGUI::TYPE_VERIFICATION;
		}
		
		return $all;
	}
}

?>