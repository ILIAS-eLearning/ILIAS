<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Portfolio/classes/class.ilPortfolioPageGUI.php");

/**
 * Portfolio template page gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilPortfolioTemplatePageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilPortfolioTemplatePageGUI: ilPageObjectGUI, ilPublicUserProfileGUI, ilObjBlogGUI, ilBlogPostingGUI
 *
 * @ingroup ModulesPortfolio
 */
class ilPortfolioTemplatePageGUI extends ilPortfolioPageGUI
{
	function getParentType()
	{
		return "prtt";
	}
	
	function initPageObject()
	{
		include_once("./Modules/Portfolio/classes/class.ilPortfolioTemplatePage.php");
		$page = new ilPortfolioTemplatePage($this->getId(), $this->getOldNr());
		$page->setPortfolioId($this->portfolio_id);
		$this->setPageObject($page);
	}
}

?>