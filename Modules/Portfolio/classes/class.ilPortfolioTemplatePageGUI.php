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
				
				// needed for placeholders
				include_once "Services/Style/classes/class.ilObjStyleSheet.php";
				$this->tpl->addCss(ilObjStyleSheet::getPlaceHolderStylePath());
				
				return parent::showPage();
		}		
	}
	
	protected function renderBlogTemplate()
	{		
		return $this->addPlaceholderInfo($this->lng->txt("obj_blog"));	
	}
	
	protected function renderProfile($a_user_id, $a_type, array $a_fields = null)
	{	
		return $this->addPlaceholderInfo(parent::renderProfile($a_user_id, $a_type, $a_fields));	
	}
	
	protected function addPlaceholderInfo($a_html)
	{
		return '<fieldset style="border: 1px dashed red; padding: 3px; margin: 5px;">'.
					'<legend style="color: red; font-style: italic;" class="small">'.
						$this->lng->txt("prtf_template_editor_placeholder_info").
					'</legend>'.
					trim($a_html).
				'</fieldset>';			
	}	
}

?>