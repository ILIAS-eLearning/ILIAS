<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Payment/classes/class.ilShopPageGUI.php';
include_once 'Services/Payment/classes/class.ilShopBaseGUI.php';


/** Class ilShopInfoGUI
 * 
 * @author Nadia Ahmad <nahmad@databay.de>
 * 
 * @ilCtrl_Calls ilShopInfoGUI: ilShopPageGUI
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
			case 'ilshoppagegui':
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
		include_once 'Services/Payment/classes/class.ilShopPage.php';
		include_once 'Services/Payment/classes/class.ilShopPageGUI.php';

		// if page does not exist, return nothing
		if(!ilShopPage::_exists('shop', self::SHOP_PAGE_EDITOR_PAGE_ID))
		{
			return '';
		}
		
		include_once 'Services/Style/classes/class.ilObjStyleSheet.php';
		$this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));
		
		// get page object
		$page_gui = new ilShopPageGUI(self::SHOP_PAGE_EDITOR_PAGE_ID);
		
		return $page_gui->showPage();
	}

	public function forwardToPageObject()
	{	
		global $lng, $ilTabs;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt('back'), $this->ctrl->getLinkTarget($this), '_top');

		// page object
		include_once 'Services/Payment/classes/class.ilShopPage.php';
		include_once 'Services/Payment/classes/class.ilShopPageGUI.php';

		$lng->loadLanguageModule('content');

		include_once('./Services/Style/classes/class.ilObjStyleSheet.php');
		$this->tpl->setVariable('LOCATION_CONTENT_STYLESHEET', ilObjStyleSheet::getContentStylePath(0));

		if(!ilShopPage::_exists('shop', self::SHOP_PAGE_EDITOR_PAGE_ID))
		{
			// doesn't exist -> create new one
			$new_page_object = new ilShopPage();
			$new_page_object->setParentId(0);
			$new_page_object->setId(self::SHOP_PAGE_EDITOR_PAGE_ID);
			$new_page_object->createFromXML();
		}

		$this->ctrl->setReturnByClass('ilshoppagegui', 'edit');

		$page_gui = new ilShopPageGUI(self::SHOP_PAGE_EDITOR_PAGE_ID);		

		return $this->ctrl->forwardCommand($page_gui);
	}
	
	public function showInfo()
	{
		global $ilUser, $rbacreview, $ilToolbar;
		
		if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$ilToolbar->addButton($this->lng->txt('edit_page'), $this->ctrl->getLinkTargetByClass(array('ilshoppagegui'), 'edit'));
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