<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Modules/Portfolio/classes/class.ilObjPortfolioGUI.php');

/**
 * Portfolio template view gui class 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilPortfolioPageGUI, ilPageObjectGUI
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilNoteGUI
 *
 * @ingroup ServicesPortfolio
 */
class ilObjPortfolioTemplateGUI extends ilObjPortfolioGUI
{
	
	
	public function getType()
	{
		return "prtt";
	}	
}

?>