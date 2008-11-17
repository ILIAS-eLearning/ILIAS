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

include_once 'Modules/Forum/classes/class.ilForumModerators.php';
include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
include_once 'Services/Table/classes/class.ilTable2GUI.php';
include_once 'Services/Search/classes/class.ilQueryParser.php';
include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';

/**
* Class ilForumModeratorsGUI
*
* @author Nadia Krzywon <nkrzywon@databay.de>
*
* @ingroup ModulesForum
*/
class ilForumModeratorsGUI
{
	private $ctrl = null;
	private $tpl = null;
	private $lng = null;
	private $oForumModerators = null;
	
	public function __construct()
	{
		global $ilCtrl, $tpl, $lng, $ilTabs, $ilAccess, $ilias;	
		
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		
		$ilTabs->setTabActive('frm_moderators');
		$this->lng->loadLanguageModule('search');
		
		if(!$ilAccess->checkAccess('edit_permission', '', (int)$_GET['ref_id']))
		{
			$ilias->raiseError($this->lng->txt('permission_denied'), $ilias->error_obj->MESSAGE);
		}		
		
		$this->oForumModerators = new ilForumModerators((int)$_GET['ref_id']);		
	}
	
	public function executeCommand()
	{	
		$cmd = $this->ctrl->getCmd();		
		switch ($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = 'showModerators';
				}
				$this->$cmd();
				break;
		}
		return true;
	}	
	
	public function addModerator()
	{
		if(!((int)$_POST['usr_id']))
		{
			ilUtil::sendInfo($this->lng->txt('frm_moderators_select_one'));
			return $this->showModeratorsSearchResult($_SESSION['frm']['moderators']['search_result']);
		}
		
		unset($_SESSION['frm']['moderators']['search_result']);
		
		$this->oForumModerators->addModeratorRole((int)$_POST['usr_id']);
		
		ilUtil::sendInfo($this->lng->txt('frm_moderator_role_added_successfully'));		
		return $this->showModerators();
	}
	
	public function showModeratorsSearchResult($users = array())
	{
		$tbl = new ilTable2GUI($this);
		
		$tbl->setTitle($this->lng->txt('users'));
		$tbl->setRowTemplate('tpl.forum_moderators_table_row.html', 'Modules/Forum'); 
					
		$tbl->addColumn('','check','1%');
	 	$tbl->addColumn($this->lng->txt('fullname'), 'fullname', '99%');
		
		$tbl->setDefaultOrderField('fullname');
		
		$result = array();		
		$counter = 0;
		foreach($users as $usr)
		{
			$oUser = ilObjectFactory::getInstanceByObjId($usr['obj_id'], false);
			if(is_object($oUser))
			{
				$result[$counter]['check'] = ilUtil::formRadioButton(0, 'usr_id', $oUser->getId());
				$result[$counter]['fullname'] = $oUser->getFullname();	
				
				++$counter;
			}
		}
		
		$tbl->setData($result);
		
		$tbl->addCommandButton('addModerator', $this->lng->txt('add'));		
		$tbl->setFormAction($this->ctrl->getFormAction($this, 'addModeratorRole'));
		
		$this->tpl->setVariable('ADM_CONTENT', $tbl->getHTML());
		return $this->tpl->show();
	}
	
	public function searchModerators()
	{
		if(!is_object($oQueryParser = $this->parseQueryString(ilUtil::stripSlashes($_POST['search_query']))))
		{
			ilUtil::sendInfo($oQueryParser);			
			return $this->searchModeratorsForm();
		}
		
		$oUserSearchFirstname = ilObjectSearchFactory::_getUserSearchInstance($oQueryParser);
		$oUserSearchFirstname->setFields(array('firstname'));
		$oSearchResult = $oUserSearchFirstname->performSearch();
		
		$oUserSearchLastname = ilObjectSearchFactory::_getUserSearchInstance($oQueryParser);
		$oUserSearchLastname->setFields(array('lastname'));
		$oSearchResultLastname = $oUserSearchLastname->performSearch();
		$oSearchResult->mergeEntries($oSearchResultLastname);	
		
		
		$oUserSearchLogin = ilObjectSearchFactory::_getUserSearchInstance($oQueryParser);
		$oUserSearchLogin->setFields(array('login'));
		$oSearchResultLogin = $oUserSearchLogin->performSearch();
		$oSearchResult->mergeEntries($oSearchResultLogin);
		
		$oSearchResult->filter(ROOT_FOLDER_ID, $oQueryParser->getCombination() == 'and');
		$search_results = $oSearchResult->getUniqueResults();	
	
		if(is_array($search_results) && count($search_results))
		{
			$_SESSION['frm']['moderators']['search_result'] = $search_results;
			return $this->showModeratorsSearchResult($search_results);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('frm_moderators_matches_in_no_results'));
			return $this->searchModeratorsForm();
		}
	}
	
	private function parseQueryString($query_string)
	{
		$oQueryParser = new ilQueryParser($query_string);
		$oQueryParser->setCombination('or');
		$oQueryParser->parse();

		if(!$oQueryParser->validate())
		{
			return $oQueryParser->getMessage();
		}
		return $oQueryParser;
	}
	
	public function searchModeratorsForm()
	{		
		$oForm = new ilPropertyFormGUI();
		$oForm->setFormAction($this->ctrl->getFormAction($this, 'searchModerators'));
		$oForm->setTitle($this->lng->txt('frm_moderators_search'));
		$oForm->setTitleIcon('');
		
		$oTextField = new ilTextInputGUI($this->lng->txt('frm_moderators_search_query'), 'search_query');		
		$oTextField->setValue(ilUtil::prepareFormOutput($_POST['search_query'], true));
		$oForm->addItem($oTextField);
		
		$oForm->addCommandButton('searchModerators', $this->lng->txt('frm_moderators_do_search'));
		$oForm->addCommandButton('showModerators', $this->lng->txt('cancel'));
		
		$this->tpl->setVariable('ADM_CONTENT', $oForm->getHTML());
		
		return $this->tpl->show();
	}
	
	public function detachModeratorRole()
	{
		if(!is_array($_POST['usr_id']))
		{
			ilUtil::sendInfo($this->lng->txt('frm_moderators_select_at_least_one'));
			return $this->showModerators();
		}
		
		foreach($_POST['usr_id'] as $usr_id)
		{
			$this->oForumModerators->detachModeratorRole((int)$usr_id);
		}
		
		ilUtil::sendInfo($this->lng->txt('frm_moderators_detached_role_successfully'));
		return $this->showModerators();
	}
	
	public function showModerators()
	{		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forum_moderators.html',	'Modules/Forum');
				
		$tbl = new ilTable2GUI($this);
		$tbl->setFormAction($this->ctrl->getFormAction($this, 'detachModeratorRole'));
		$tbl->setTitle($this->lng->txt('frm_moderators'));
		$tbl->setRowTemplate('tpl.forum_moderators_table_row.html', 'Modules/Forum'); 

		$tbl->addColumn('', 'check', '1%');
	 	$tbl->addColumn($this->lng->txt('fullname'), 'fullname', '99%');
		
		$tbl->setDefaultOrderField('fullname');

		$entries = $this->oForumModerators->getCurrentModerators();
		$result = array();
		if(count($entries))
		{
			$tbl->enable('select_all');				
			$tbl->setSelectAllCheckbox('usr_id');
			
			$counter = 0;
			foreach($entries as $usr_id)
			{
				$oUser = ilObjectFactory::getInstanceByObjId($usr_id, false);
				if(is_object($oUser))
				{
					$result[$counter]['check'] = ilUtil::formCheckbox(0, 'usr_id[]', $oUser->getId());
					$result[$counter]['fullname'] = $oUser->getFullname();	
					
					++$counter;
				}
			}
			
			$tbl->addMultiCommand('detachModeratorRole', $this->lng->txt('frm_detach_moderator_role'));
		}
		else
		{
			$tbl->disable('header');
			$tbl->disable('footer');

			$tbl->setNoEntriesText($this->lng->txt('frm_moderators_not_exist_yet'));
		}

		$tbl->setData($result);

		$tbl->addCommandButton('searchModeratorsForm', $this->lng->txt('add'));		
		
		$this->tpl->setVariable('TXT_FORUM_MODERATORS', $tbl->getHTML());		
		
		return $this->tpl->show();		
	}
}
?>