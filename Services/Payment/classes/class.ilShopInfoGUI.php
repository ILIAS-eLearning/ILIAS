<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/COPage/classes/class.ilPageObjectGUI.php';
include_once 'Services/Payment/classes/class.ilShopBaseGUI.php';


/** Class ilShopInfoGUI
 * 
 * @author Nadia Ahmad <nahmad@databay.de>
 * 
 * @ilCtrl_Calls ilShopInfoGUI: ilPageObjectGUI
 * @ingroup ServicesPayment
 * 
*/
class ilShopInfoGUI extends ilShopBaseGUI
{
	const SHOP_PAGE_EDITOR_PAGE_ID = 99999998;	
	
	public function __construct()
	{
		parent::__construct();		
	}
	
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case 'ilpageobjectgui':
				$this->prepareOutput();
				$ret = $this->forwardToPageObject();
				if($ret != '')
				{
					$this->tpl->setContent($ret);
				}				
				break;
			default:
				if(!$cmd)
				{
					$cmd = 'showInfo';
				}
				$this->prepareOutput();
				$this->$cmd();

				break;
		}
				
		return true;
	}	
	
	public function getPageHTML()
	{
		// page object
		include_once 'Services/COPage/classes/class.ilPageObject.php';
		include_once 'Services/COPage/classes/class.ilPageObjectGUI.php';

		// if page does not exist, return nothing
		if(!ilPageObject::_exists('shop', self::SHOP_PAGE_EDITOR_PAGE_ID))
		{
			return '';
		}
		
		include_once 'Services/Style/classes/class.ilObjStyleSheet.php';
		$this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));
		
		// get page object
		$page_gui = new ilPageObjectGUI('shop', self::SHOP_PAGE_EDITOR_PAGE_ID);
		$page_gui->setIntLinkHelpDefault('StructureObject', self::SHOP_PAGE_EDITOR_PAGE_ID);
		$page_gui->setLinkXML('');
		$page_gui->setFileDownloadLink($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'downloadFile'));
		$page_gui->setFullscreenLink($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'displayMediaFullscreen'));
		$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'download_paragraph'));
		$page_gui->setPresentationTitle('');
		$page_gui->setTemplateOutput(false);
		$page_gui->setHeader('');
		$page_gui->setEnabledRepositoryObjects(false);
		$page_gui->setEnabledFileLists(true);
		$page_gui->setEnabledPCTabs(true);
		$page_gui->setEnabledMaps(true);
		
		return $page_gui->showPage();
	}

	public function forwardToPageObject()
	{	
		global $lng, $ilTabs;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt('back'), $this->ctrl->getLinkTarget($this), '_top');

		// page object
		include_once 'Services/COPage/classes/class.ilPageObject.php';
		include_once 'Services/COPage/classes/class.ilPageObjectGUI.php';

		$lng->loadLanguageModule('content');

		include_once('./Services/Style/classes/class.ilObjStyleSheet.php');
		$this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));

		if(!ilPageObject::_exists('shop', self::SHOP_PAGE_EDITOR_PAGE_ID))
		{
			// doesn't exist -> create new one
			$new_page_object = new ilPageObject('shop');
			$new_page_object->setParentId(0);
			$new_page_object->setId(self::SHOP_PAGE_EDITOR_PAGE_ID);
			$new_page_object->createFromXML();
		}

		$this->ctrl->setReturnByClass('ilpageobjectgui', 'edit');

		$page_gui = new ilPageObjectGUI('shop', self::SHOP_PAGE_EDITOR_PAGE_ID);		
		$page_gui->setIntLinkHelpDefault('StructureObject', self::SHOP_PAGE_EDITOR_PAGE_ID);
		$page_gui->setTemplateTargetVar('ADM_CONTENT');
		$page_gui->setLinkXML('');
		$page_gui->setFileDownloadLink($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'downloadFile'));
		$page_gui->setFullscreenLink($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'displayMediaFullscreen'));
		$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'download_paragraph'));
		$page_gui->setPresentationTitle('');
		$page_gui->setTemplateOutput(false);
		$page_gui->setHeader('');
		$page_gui->setEnabledRepositoryObjects(false);
		$page_gui->setEnabledFileLists(true);
		$page_gui->setEnabledMaps(true);
		$page_gui->setEnabledPCTabs(true);

		return $this->ctrl->forwardCommand($page_gui);
	}
	
	public function showInfo()
	{
		global $ilUser, $rbacreview, $ilToolbar;
		
		if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$ilToolbar->addButton($this->lng->txt('edit_page'), $this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'edit'));
		}
		
		$this->tpl->setVariable('ADM_CONTENT', $this->getPageHTML());
	}
	
	protected function prepareOutput()
	{
		global $ilTabs;		
		
		parent::prepareOutput();
		
		$ilTabs->setTabActive('shop_info');
	}		
}
?>