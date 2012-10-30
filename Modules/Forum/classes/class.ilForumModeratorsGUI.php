<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Modules/Forum/classes/class.ilForumModerators.php';
include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
include_once 'Services/Table/classes/class.ilTable2GUI.php';
include_once 'Services/Search/classes/class.ilQueryParser.php';
include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';


/**
* Class ilForumModeratorsGUI
*
* @author Nadia Ahmad <nahmad@databay.de>
*
* @ilCtrl_Calls ilForumModeratorsGUI: ilRepositorySearchGUI
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
		/** 
		 * @var $ilCtrl ilCtrl
		 * @var $tpl ilTemplate
		 * @var $lng ilLanguage
		 * @var $ilTabs ilTabsGUI
		 * @var $ilAccess ilAccessHandler
		 * @var $ilias ilias
		 *  */
		global $ilCtrl, $tpl, $lng, $ilTabs, $ilAccess, $ilias;	
		
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		
		$ilTabs->setTabActive('frm_moderators');
		$this->lng->loadLanguageModule('search');
		
		if(!$ilAccess->checkAccess('write', '', (int)$_GET['ref_id']))
		{
			$ilias->raiseError($this->lng->txt('permission_denied'), $ilias->error_obj->MESSAGE);
		}		
		
		$this->oForumModerators = new ilForumModerators((int)$_GET['ref_id']);		
	}

	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
			
	
		switch ($next_class)
		{
			case 'ilrepositorysearchgui':

				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search = new ilRepositorySearchGUI();
				$rep_search->setCallback($this,
					'addModerator');
				// Set tabs
				$this->ctrl->setReturn($this,'showModerators');
				$this->ctrl->forwardCommand($rep_search);				

				break;

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
		if(!$_POST['user'])
		{
			ilUtil::sendInfo($this->lng->txt('frm_moderators_select_one'));
			return $this->showModeratorsSearchResult($_SESSION['frm']['moderators']['search_result']);
		}
		
		unset($_SESSION['frm']['moderators']['search_result']);
		foreach($_POST['user'] as $user_id)
		{
			$this->oForumModerators->addModeratorRole((int)$user_id);
		}
		
		ilUtil::sendInfo($this->lng->txt('frm_moderator_role_added_successfully'));		
		return $this->showModerators();
	}
	
	public function showModeratorsSearchResult($users = array())
	{
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
		/** @var $oSearchResult ilObjectSearchFactory */
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
	
	public function detachModeratorRole()
	{
		
		$entries = $this->oForumModerators->getCurrentModerators();
		
		if(count($_POST['usr_id']) == count($entries))
		{
			ilUtil::sendInfo($this->lng->txt('frm_at_least_one_moderator'));
			return $this->showModerators();			
		}
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
		/** 
		 * @var $ilToolbar ilToolbarGUI */
		global $ilToolbar;
		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.forum_moderators.html',	'Modules/Forum');
			// search button
		$ilToolbar->addButton($this->lng->txt("search_users"),
		$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start'));
			
		$tbl = new ilTable2GUI($this);
		$tbl->setId('frm_show_mods_tbl_'.$_GET['ref_id']);
		$tbl->setFormAction($this->ctrl->getFormAction($this, 'detachModeratorRole'));
		$tbl->setTitle($this->lng->txt('frm_moderators'));
		$tbl->setRowTemplate('tpl.forum_moderators_table_row.html', 'Modules/Forum'); 

		$tbl->addColumn('', 'check', '1%');
		$tbl->addColumn($this->lng->txt('login'),'login', '30%');
		$tbl->addColumn($this->lng->txt('firstname'),'firstname', '30%');
		$tbl->addColumn($this->lng->txt('lastname'),'lastname', '30%');
		
		$tbl->setDefaultOrderField('login');

		$entries = $this->oForumModerators->getCurrentModerators();
		$result = array();
		if(count($entries))
		{
			$counter = 0;
			foreach($entries as $usr_id)
			{
				$oUser = ilObjectFactory::getInstanceByObjId($usr_id, false);
				if(is_object($oUser))
				{
					if(count($entries) > 1)
					{
						$result[$counter]['check'] = ilUtil::formCheckbox(0, 'usr_id[]', $oUser->getId());
					}
					$result[$counter]['login'] = $oUser->getLogin();
					$result[$counter]['firstname'] = $oUser->getFirstname();
					$result[$counter]['lastname'] = $oUser->getLastname();
								
					++$counter;
				}
			}
			
			if(count($entries) > 1)
			{
				$tbl->enable('select_all');
				$tbl->setSelectAllCheckbox('usr_id');
				$tbl->addMultiCommand('detachModeratorRole', $this->lng->txt('frm_detach_moderator_role'));
			}	
		}
		else
		{
			$tbl->disable('header');
			$tbl->disable('footer');

			$tbl->setNoEntriesText($this->lng->txt('frm_moderators_not_exist_yet'));
		}

		$tbl->setData($result);
		$this->tpl->setVariable('TXT_FORUM_MODERATORS', $tbl->getHTML());
	}
}