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
		global $ilUser;
		
		// check access
		if(!(bool)$this->oGeneralSettings->get('topics_allow_custom_sorting'))
		{
			ilUtil::redirect($this->ctrl->getLinkTargetByClass('ilshopgui'));
		}
		
		if(ANONYMOUS_USER_ID == $ilUser->getId())
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
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
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shop_personal_settings_topics_list.html', 'Services/Payment');
		
		include_once 'Services/Payment/classes/class.ilShopPersonalSettingsTopicsTableGUI.php';
		$table_gui = new ilShopPersonalSettingsTopicsTableGUI($this, 'showTopicsSortingTable');
		$table_gui->setTitle($this->lng->txt('pay_manual_sorting_of_topics'));
		ilShopTopics::_getInstance()->enableCustomSorting(true);
		ilShopTopics::_getInstance()->setSortingType(ilShopTopics::TOPICS_SORT_MANUALLY);
		ilShopTopics::_getInstance()->setSortingDirection('ASC');
		ilShopTopics::_getInstance()->read();
		$table_gui->parseRecords(ilShopTopics::_getInstance()->getTopics());
		$table_gui->addCommandButton('saveSorting', $this->lng->txt('pay_save_sorting'));		
		
		$this->tpl->setVariable('TOPICS_TABLE', $table_gui->getHTML());
	}
}
?>
