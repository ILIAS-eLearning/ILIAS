<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Payment/classes/class.ilShopBaseGUI.php';

/**
* Class ilShopPersonalSettingsGUI
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
* @ingroup ServicesPayment
*/
class ilShopPersonalSettingsGUI extends ilShopBaseGUI
{
	public function __construct()
	{
		parent::__construct();	
	}
	
	public function executeCommand()
	{
		global $ilUser, $ilCtrl, $ilErr;
		
		// check access
		if(!(bool)$this->settings->get('topics_allow_custom_sorting'))
		{
			$ilCtrl->redirectByClass('ilshopgui','','',false, false);
		}
		
		if(ANONYMOUS_USER_ID == $ilUser->getId())
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
		}
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				if (!$cmd)
				{
					$cmd = 'showTopicsSortingTable';
				}
				$this->prepareOutput();			
				$this->$cmd();

				break;
		}
	}
	
	protected function prepareOutput()
	{
		global $ilTabs;		
		
		parent::prepareOutput();
		
		$ilTabs->setTabActive('pay_personal_settings');
	}
	
	public function saveSorting()
	{
		if(count($_POST['sorting']))
		{
			foreach($_POST['sorting'] as $topic_id => $sorting_value)
			{
				$oTopic = new ilShopTopic($topic_id);
				$oTopic->setCustomSorting((int)$sorting_value);
				$oTopic->saveCustomSorting();
			}
		}
		
		ilUtil::sendInfo($this->lng->txt('saved_successfully'));
		
		return $this->showTopicsSortingTable();
	}
	
	public function showTopicsSortingTable()
	{
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.main_view.html', 'Services/Payment');
		
		include_once 'Services/Payment/classes/class.ilShopPersonalSettingsTopicsTableGUI.php';
		$table_gui = new ilShopPersonalSettingsTopicsTableGUI($this, 'showTopicsSortingTable');
		$table_gui->setTitle($this->lng->txt('pay_manual_sorting_of_topics'));
		ilShopTopics::_getInstance()->enableCustomSorting(true);
		ilShopTopics::_getInstance()->setSortingType(ilShopTopics::TOPICS_SORT_MANUALLY);
		ilShopTopics::_getInstance()->setSortingDirection('ASC');
		ilShopTopics::_getInstance()->read();
		$table_gui->parseRecords(ilShopTopics::_getInstance()->getTopics());
		$table_gui->addCommandButton('saveSorting', $this->lng->txt('pay_save_sorting'));		
		
		$this->tpl->setVariable('TABLE', $table_gui->getHTML());
	}
}
?>
