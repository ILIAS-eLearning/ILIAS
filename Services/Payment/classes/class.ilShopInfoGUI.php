<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
include_once './Services/COPage/classes/class.ilPageObjectGUI.php';
include_once 'Services/Payment/classes/class.ilShopBaseGUI.php';
include_once 'classes/class.ilTabsGUI.php';


/** Class ilShopInfoGUI
 * 
 * @author Nadia Krzywon <nkrzywon@databay.de>
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
	
	function executeCommand()
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
		global $ilUser, $rbacreview;
		
		if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$this->tpl->addBlockfile('BUTTONS', 'buttons', 'tpl.buttons.html');
			$this->tpl->setCurrentBlock('btn_cell');
			$this->tpl->setVariable('BTN_LINK', $this->ctrl->getLinkTargetByClass(array('ilpageobjectgui'), 'edit'));
			$this->tpl->setVariable('BTN_TXT', $this->lng->txt('edit_page'));
			$this->tpl->parseCurrentBlock();
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