<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once 'Services/Payment/classes/class.ilShopBaseGUI.php';
include_once 'Services/Payment/classes/class.ilShopNewsItem.php';
include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';


/** Class ilShopNewsGUI
 * 
 * @author Nadia Ahmad <nahmad@databay.de>
 * 
 * @ingroup ServicesPayment
 * 
*/
class ilShopNewsGUI extends ilShopBaseGUI
{
	private $form_gui = null;
	private $settings_form = null;
	private $oCurrentNewsItem = null;
	
	public $ilErr = null;
	
	public function __construct()
	{
		global $ilErr;
		parent::__construct();

		$this->oCurrentNewsItem = new ilShopNewsItem((int)$_GET['news_id']);
		
		$this->lng->loadLanguageModule('news');
		$this->ilErr = $ilErr;
	}
	
	public function executeCommand()
	{ 
//		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($cmd)
		{			
			default:
				if(!$cmd)
				{
					$cmd = 'showNews';
				}
				$this->prepareOutput();
				$this->$cmd();
				break;
		}
			
		return true;
	}	

	protected function buildSubTabs()
	{
		global $ilTabs, $ilUser, $rbacreview;
		
		$ilTabs->addSubTabTarget('news', $this->ctrl->getLinkTarget($this, 'showNews'), '', '', '','showNews');
		$ilTabs->addSubTabTarget('archive', $this->ctrl->getLinkTarget($this, 'showArchive'), '', '', '','archive');
				
		if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$ilTabs->addSubTabTarget('settings', $this->ctrl->getLinkTarget($this, 'showSettings'), '', '', '','settings');
		}		
	}	
	
	public function saveSettings()
	{
		global $ilTabs, $ilSetting, $rbacreview, $ilUser;
		
		if(!$rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->MESSAGE);
		}		

		$ilTabs->setSubTabActive('settings');
		
		$this->initSettingsForm('edit');
		if($this->settings_form->checkInput())
		{
			$ilSetting->set('payment_news_archive_period', $this->settings_form->getInput('archive_period'));
			$this->getSettingsValues();
			ilUtil::sendInfo($this->lng->txt('payment_news_settings_saved'));
				
			return $this->tpl->setVariable('ADM_CONTENT', $this->settings_form->getHtml());
		}
		else
		{
			$this->form_gui->setValuesByPost();
			return $this->tpl->setVariable('ADM_CONTENT', $this->settings_form->getHtml());
		}
	}
	
	public function initSettingsForm($a_mode = 'edit')
	{		
	
		$this->settings_form = new ilPropertyFormGUI();
		$this->settings_form->setTitle($this->lng->txt('payment_news_settings'));
		
		$oSelectBox = new ilSelectInputGUI($this->lng->txt('payment_news_archive_period'), 'archive_period');
		$oSelectBox->setInfo($this->lng->txt('payment_news_archive_period_info'));
		$options = array();
		for($i = 5; $i <= 100; $i += 5)
		{
			$options[$i] = $i;
		}
		$oSelectBox->setOptions($options);
		$this->settings_form->addItem($oSelectBox);

		$this->settings_form->addCommandButton('saveSettings', $this->lng->txt('save'));
		$this->settings_form->addCommandButton('showNews', $this->lng->txt('cancel'));
		$this->settings_form->setFormAction($this->ctrl->getFormaction($this, 'saveSettings'));
	}	
	
	public function showSettings()
	{
		global $ilTabs, $ilUser, $rbacreview;
		
		if(!$rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$this->ilErr->raiseError($this->lng->txt('permission_denied'),$this->ilErr->MESSAGE);
		}

		$ilTabs->setSubTabActive('settings');
		
		$this->initSettingsForm('edit');
		$this->getSettingsValues();
		
		return $this->tpl->setVariable('ADM_CONTENT', $this->settings_form->getHtml());
	}

	public function saveNews()
	{
		global $ilUser, $rbacreview;
		
		if(!$rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$this->ilErr->raiseError($this->lng->txt('permission_denied'),$this->ilErr->MESSAGE);
		}
		
		$this->initNewsForm('create');
		if ($this->form_gui->checkInput())
		{
			$this->oCurrentNewsItem->setTitle($this->form_gui->getInput('news_title'));
			$this->oCurrentNewsItem->setContent($this->form_gui->getInput('news_content'));
			$this->oCurrentNewsItem->setVisibility($this->form_gui->getInput('news_visibility'));
			$this->oCurrentNewsItem->setUserId($ilUser->getId());
			$this->oCurrentNewsItem->create();
			
			ilUtil::sendInfo($this->lng->txt('payment_news_created'));
			return $this->showNews();
		}
		else
		{
			$this->form_gui->setValuesByPost();
			return $this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());
		}
	}
	
	public function updateArchiveNews()
	{
		return $this->update('archive');
	}
	
	public function updateNews()
	{
		return $this->update('news');
	}
		
	public function update($view)
	{		
		global $ilUser, $rbacreview;
		
		if(!$rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$this->ilErr->raiseError($this->lng->txt('permission_denied'),$this->ilErr->MESSAGE);
		}
		
		$this->initNewsForm('edit', $view);

		if ($this->form_gui->checkInput())
		{			
			$this->oCurrentNewsItem->setTitle($this->form_gui->getInput('news_title'));
			$this->oCurrentNewsItem->setContent($this->form_gui->getInput('news_content'));
			$this->oCurrentNewsItem->setVisibility($this->form_gui->getInput('news_visibility'));
			$this->oCurrentNewsItem->setUserId($ilUser->getId());

			$this->oCurrentNewsItem->update();
			
			ilUtil::sendInfo($this->lng->txt('payment_news_updated'));
			
			if($view == 'archive')
			{
				return $this->showArchive();
			}
			else
			{
				return $this->showNews();	
			}
			
		}
		else
		{
			$this->form_gui->setValuesByPost();
			return $this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());
		}
	}
	
	public function performDeleteNews()
	{
		return $this->performDelete('news');	
	}
	
	public function performDeleteArchiveNews()
	{
		return $this->performDelete('archive');
	}
	
	public function performDelete($view)
	{
		global $ilUser, $rbacreview;
		
		if(!$rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$this->ilErr->raiseError($this->lng->txt('permission_denied'),$this->ilErr->MESSAGE);
		}		
		
		if(!(int)$_POST['news_id'])
		{
			ilUtil::sendInfo($this->lng->txt('payment_news_missing_id'));
			switch($view)
			{			
				case 'archive':
					return $this->showArchive();
					break;
					
				case 'news':
				default:
					return $this->showNews();
					break;
			}
		}
		else
		{
			$this->oCurrentNewsItem->setId($_POST['news_id']);
			$this->oCurrentNewsItem->delete();
			ilUtil::sendInfo($this->lng->txt('payment_news_deleted'));			
		}		

		switch($view)
		{			
			case 'archive':
				return $this->showArchive();
				break;
				
			case 'news':
			default:
				return $this->showNews();
				break;
		}
	}
	
	public function confirmDeleteNews()
	{
		$this->confirmDelete('news');
	}
	
	public function confirmDeleteArchiveNews()
	{
		$this->confirmDelete('archive');
	}
	
	public function confirmDelete($view)
	{		
		global $ilUser, $rbacreview;
				
		if(!$rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$this->ilErr->raiseError($this->lng->txt('permission_denied'),$this->ilErr->MESSAGE);
		}
		
		if(!isset($_GET['news_id']))
	 	{
	 		ilUtil::sendInfo($this->lng->txt('payment_news_missing_id'));
			switch($view)
			{			
				case 'archive':
					return $this->showArchive();
					break;
					
				case 'news':
				default:
					return $this->showNews();
					break;
			}
	 	}
	 	
	 	include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$c_gui = new ilConfirmationGUI();				
		$c_gui->setHeaderText($this->lng->txt('payment_news_delete_sure'));		
		$c_gui->addHiddenItem('news_id', (int)$_GET['news_id']);		

		$oNewsItem = new ilShopNewsItem($_GET['news_id']);
		$title=$oNewsItem->getTitle();
		$c_gui->addItem($title, $_GET['news_id'],$title);
		

		switch($view)
		{
			case 'archive':
				$c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDeleteArchiveNews'));
				$c_gui->setConfirm($this->lng->txt('confirm'), 'performDeleteArchiveNews');
				$c_gui->setCancel($this->lng->txt('cancel'), 'showArchive');
				$this->showArchive($c_gui->getHTML());
				break;
				
			case 'news':
			default:
				$c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDeleteNews'));
				$c_gui->setConfirm($this->lng->txt('confirm'), 'performDeleteNews');
				$c_gui->setCancel($this->lng->txt('cancel'), 'showNews');
				$this->showNews($c_gui->getHTML());
				break;
		}
	
		return true;	
	}	
	
	public function initNewsForm($a_mode = 'create', $view = 'news')
	{
		global $ilTabs, $rbacreview, $ilUser;
		
		if(!$rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID))
		{
			$this->ilErr->raiseError($this->lng->txt('permission_denied'),$this->ilErr->MESSAGE);
		}

		if($view == 'archive')
		{
			$ilTabs->setSubTabActive('archive');
		}
		else
		{
			$ilTabs->setSubTabActive('news');	
		}
		
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setTitle($this->lng->txt('shopnews_settings'));
		
		// Property Title
		$text_input = new ilTextInputGUI($this->lng->txt('news_news_item_title'), 'news_title');
		$text_input->setInfo('');
		$text_input->setRequired(true);
		$text_input->setMaxLength(200);
		$text_input->setSize(93);
		
		$this->form_gui->addItem($text_input);
		
		// Property Content
		$text_area = new ilTextAreaInputGUI($this->lng->txt('news_news_item_content'), 'news_content');
		$text_area->setInfo('');
		$text_area->setRequired(false);
		$text_area->setCols(90);
		$text_area->setRows(10);
		$this->form_gui->addItem($text_area);
		
		// Property Visibility
		$radio_group = new ilRadioGroupInputGUI($this->lng->txt('news_news_item_visibility'), 'news_visibility');
		$radio_option = new ilRadioOption($this->lng->txt('news_visibility_users'), 'users');
		$radio_group->addOption($radio_option);
		$radio_option = new ilRadioOption($this->lng->txt('news_visibility_public'), 'public');
		$radio_group->addOption($radio_option);
		$radio_group->setInfo($this->lng->txt('news_news_item_visibility_info'));
		$radio_group->setRequired(false);
		$radio_group->setValue('users');
		$this->form_gui->addItem($radio_group);
		
		// save and cancel commands		
		if($a_mode == 'create')
		{
			$this->form_gui->addCommandButton('saveNews', $this->lng->txt('save'));
			$this->form_gui->addCommandButton('showNews', $this->lng->txt('cancel'));
			$this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'saveNews'));
		}
		else
		{
			$this->ctrl->setParameter($this, 'news_id', $this->oCurrentNewsItem->getId());
			
			if($view == 'archive')
			{
				$this->form_gui->addCommandButton('updateArchiveNews', $this->lng->txt('save'));
				$this->form_gui->addCommandButton('showArchive', $this->lng->txt('cancel'));
				$this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'updateArchiveNews'));
			}
			else
			{
				$this->form_gui->addCommandButton('updateNews', $this->lng->txt('save'));
				$this->form_gui->addCommandButton('showNews', $this->lng->txt('cancel'));
				$this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'updateNews'));					
			}			
		}
		
		$this->form_gui->setTitle($this->lng->txt('news_news_item_head'));		
		$this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());
	}
	
	public function addNewsForm()
	{
		$this->initNewsForm('create');
		$this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());
	}
	
	public function editNews()
	{
		return $this->editNewsForm('news');
	}
	
	public function editArchiveNews()
	{
		return $this->editNewsForm('archive');
	}
	
	public function editNewsForm($view)
	{		
		$this->initNewsForm('edit', $view);
		$this->getPropertiesValues();
		$this->tpl->setVariable('ADM_CONTENT', $this->form_gui->getHtml());
	}
	
	public function getPropertiesValues()
	{					
		$this->form_gui->setValuesByArray(array(
			'news_creation_date' => $this->oCurrentNewsItem->getCreationDate(),
			'news_title' => $this->oCurrentNewsItem->getTitle(),
			'news_content' => $this->oCurrentNewsItem->getContent(), 
			'news_visibility' =>$this->oCurrentNewsItem->getVisibility()
		));
	}
	
	public function getSettingsValues()
	{
		global $ilSetting;
		
		$this->settings_form->setValuesByArray(array(
			'archive_period' => $ilSetting->get('payment_news_archive_period')
		));
	}

	public function showNews($confirmation_gui = '')
	{
		global $ilUser, $tpl, $ilTabs, $ilSetting, $rbacreview, $ilToolbar;

		$ilTabs->setSubTabActive('news');
		
		include_once 'Services/Payment/classes/class.ilShopNewsItemList.php';
		include_once 'Services/Table/classes/class.ilTable2GUI.php';	
			
		if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID) == true)
		{
			$ilToolbar->addButton($this->lng->txt('payment_news_add_news'), $this->ctrl->getLinkTarget($this, 'addNewsForm'));
		}		

		$news_tpl = new ilTemplate('tpl.main_view.html', true, true, 'Services/Payment');

		if((int)strlen($confirmation_gui))
		{
			$news_tpl->setVariable('CONFIRMATION', $confirmation_gui);
		}

		$tbl = new ilTable2GUI($this);
		$tbl->setId('shop_news_tbl');
		$tbl->setTitle($this->lng->txt('news'), 0, $this->lng->txt('news'));		
		$tbl->setRowTemplate('tpl.shop_news_row.html', 'Services/Payment'); 
		$tbl->setFormAction($this->ctrl->getFormAction($this), 'showNews');
		$tbl->addColumn($this->lng->txt('news'), 'title', '100%');
				
		$oNewsList = ilShopNewsItemList::_getInstance();
		$oNewsList->setMode(ilShopNewsItemList::TYPE_NEWS)
				  ->setPublicSection($ilUser->getId() == ANONYMOUS_USER_ID)
				  ->setArchiveDate(time() - ($ilSetting->get('payment_news_archive_period') * 24 * 60 * 60))
				  ->read();
				 
		$result = array();		
		if($oNewsList->hasItems())
		{
			$tbl->setEnableHeader(true);
			$tbl->setEnableTitle(true);
			
			$counter = 0;
			foreach($oNewsList as $entry)
			{	
				if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID) == true)
				{	
					$this->ctrl->setParameter($this, 'news_id', $entry->getId());
					
					$result[$counter]['news_id'] = $entry->getId();
					$result[$counter]['edit_src'] = $this->ctrl->getLinkTarget($this, 'editNews');
					$result[$counter]['edit_txt'] = $this->lng->txt('edit');
					$result[$counter]['delete_src'] = $this->ctrl->getLinkTarget($this, 'confirmDeleteNews');
					$result[$counter]['delete_txt'] = $this->lng->txt('delete');
					
					$this->ctrl->clearParameters($this);
				}
								
				$result[$counter]['title'] = $entry->getTitle();
				$result[$counter]['content'] = $entry->getContent();
				$result[$counter]['user_id'] = $entry->getUserId();
				
				$result[$counter]['txt_author'] =  $this->lng->txt('author');
				$result[$counter]['author'] = $ilUser->getLogin();
				
				$result[$counter]['txt_creation_date'] = $this->lng->txt('create_date'); 
				$result[$counter]['creation_date'] = ilDatePresentation::formatDate(
					new ilDateTime($entry->getCreationDate(), IL_CAL_DATETIME));	
						
				$result[$counter]['txt_update_date'] = $this->lng->txt('last_update');
				$result[$counter]['update_date'] = ilDatePresentation::formatDate(
					new ilDateTime($entry->getUpdateDate(), IL_CAL_DATETIME));	

				$result[$counter]['txt_access'] = $this->lng->txt('access');							
				$result[$counter]['access'] = $entry->getVisibility();					
				
				++$counter;
			}
		}
		else
		{
			
			$tbl->setNoEntriesText($this->lng->txt('payment_news_no_news_items'));
		}

		$tbl->setData($result);
		$news_tpl->setVariable('TABLE', $tbl->getHTML());
		$tpl->setContent($news_tpl->get());			
	}
	
	public function showArchive($confirmation_gui = '')
	{
		global $ilUser, $tpl, $ilTabs, $ilSetting, $rbacreview;

		$ilTabs->setSubTabActive('archive');

		include_once 'Services/Payment/classes/class.ilShopNewsItemList.php';
		include_once 'Services/Table/classes/class.ilTable2GUI.php';
		
		$news_tpl = new ilTemplate('tpl.main_view.html', true, true, 'Services/Payment');

		if((int)strlen($confirmation_gui))
		{
			$news_tpl->setVariable('CONFIRMATION', $confirmation_gui);
		}
		
		$tbl = new ilTable2GUI($this);
		$tbl->setId('shop_news_archive_tbl');
		$tbl->setTitle($this->lng->txt('archive'), 0 ,$this->lng->txt('news'));	
		$tbl->setRowTemplate('tpl.shop_news_row.html', 'Services/Payment'); 
		$tbl->setFormAction($this->ctrl->getFormAction($this), 'showArchive');
	 	$tbl->addColumn($this->lng->txt('archive'), 'title', '100%');	 	

		$oNewsList = ilShopNewsItemList::_getInstance();
		$oNewsList->setMode(ilShopNewsItemList::TYPE_ARCHIVE)
				  ->setPublicSection($ilUser->getId() == ANONYMOUS_USER_ID)
				  ->setArchiveDate(time() - ($ilSetting->get('payment_news_archive_period') * 24 * 60 * 60))
				  ->read();
		$result = array();		
		if($oNewsList->hasItems())
		{
			$tbl->setEnableTitle(true);
			$tbl->setEnableHeader(true);	
			
			$counter = 0;
			foreach($oNewsList as $entry)
			{
				if($entry->getVisibility() != 'public' && $ilUser->getId() == ANONYMOUS_USER_ID) continue;
				if(strtotime($entry->getCreationDate()) > time() - ($ilSetting->get('payment_news_archive_period') * 24 * 60 * 60)) continue;
									
				if($rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID) == true)
				{	
					$this->ctrl->setParameter($this, 'news_id', $entry->getId());
					
					$result[$counter]['news_id']= $entry->getId();
					$result[$counter]['edit_src'] = $this->ctrl->getLinkTarget($this, 'editArchiveNews');
					$result[$counter]['edit_txt'] = $this->lng->txt('edit');
					$result[$counter]['delete_src'] = $this->ctrl->getLinkTarget($this, 'confirmDeleteArchiveNews');
					$result[$counter]['delete_txt'] = $this->lng->txt('delete');
					
					$this->ctrl->clearParameters($this);
				}
				
				$result[$counter]['creation_date'] = ilDatePresentation::formatDate(
					new ilDateTime($entry->getCreationDate(), IL_CAL_DATETIME));	
				$result[$counter]['title'] = $entry->getTitle();
				$result[$counter]['content'] = $entry->getContent();
				$result[$counter]['user_id'] = $entry->getUserId();

				$result[$counter]['author'] = $ilUser->getLogin();
				$result[$counter]['creation_date'] = ilDatePresentation::formatDate(
					new ilDateTime($entry->getCreationDate(), IL_CAL_DATETIME));					
				
				$result[$counter]['update_date'] = ilDatePresentation::formatDate(
					new ilDateTime($entry->getCreationDate(), IL_CAL_DATETIME));	

				$result[$counter]['txt_author'] =  $this->lng->txt('author');
				$result[$counter]['txt_creation_date'] = $this->lng->txt('create_date'); 
				$result[$counter]['txt_update_date'] = $this->lng->txt('last_update');

				$this->ctrl->clearParameters($this);
				
				++$counter;
			}
		}
		else
		{
			$tbl->setNoEntriesText($this->lng->txt('payment_news_no_news_items'));
		}
		$tbl->setData($result);
		$news_tpl->setVariable('TABLE', $tbl->getHTML());
		$tpl->setContent($news_tpl->get());			
	}

	protected function prepareOutput()
	{
		global $ilTabs;		
		
		parent::prepareOutput();

		$ilTabs->setTabActive('payment_news');

	}
}
?>