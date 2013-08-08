<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Portfolio/classes/class.ilPortfolioPageGUI.php");

/**
 * Portfolio template page gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilPortfolioTemplatePageGUI: ilPageEditorGUI, ilEditClipboardGUI
 * @ilCtrl_Calls ilPortfolioTemplatePageGUI: ilPageObjectGUI, ilMediaPoolTargetSelector
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
	
	function showPage()
	{		
		if(!$this->getPageObject())
		{
			return;
		}
		
		switch($this->getPageObject()->getType())
		{
			case ilPortfolioTemplatePage::TYPE_BLOG_TEMPLATE:
				return $this->renderBlogTemplate();
				
			default:
				return parent::showPage();
		}		
	}
	
	protected function renderBlogTemplate()
	{
		 return "BLOG TEMPLATE :TODO:";
	}
}

?>