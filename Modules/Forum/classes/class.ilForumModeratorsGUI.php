<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Modules/Forum/classes/class.ilForumModerators.php';
include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
include_once 'Services/Table/classes/class.ilTable2GUI.php';
include_once 'Services/Search/classes/class.ilQueryParser.php';
include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';

/**
 * Class ilForumModeratorsGUI
 * @author Nadia Ahmad <nahmad@databay.de>
 * @ilCtrl_Calls ilForumModeratorsGUI: ilRepositorySearchGUI
 * @ingroup ModulesForum
 */
class ilForumModeratorsGUI
{
	/**
	 * @var ilCtrl
	 */
	private $ctrl;

	/**
	 * @var ilTemplate
	 */
	private $tpl;

	/**
	 * @var ilLanguage
	 */
	private $lng;

	/**
	 * @var ilForumModerators
	 */
	private $oForumModerators;

	public function __construct()
	{
		/**
		 * @var $ilCtrl   ilCtrl
		 * @var $tpl      ilTemplate
		 * @var $lng      ilLanguage
		 * @var $ilTabs   ilTabsGUI
		 * @var $ilAccess ilAccessHandler
		 * @var $ilias    ilias
		 *  */
		global $ilCtrl, $tpl, $lng, $ilTabs, $ilAccess, $ilias;

		$this->ctrl = $ilCtrl;
		$this->tpl  = $tpl;
		$this->lng  = $lng;

		$ilTabs->setTabActive('frm_moderators');
		$this->lng->loadLanguageModule('search');

		if(!$ilAccess->checkAccess('write', '', (int)$_GET['ref_id']))
		{
			$ilias->raiseError($this->lng->txt('permission_denied'), $ilias->error_obj->MESSAGE);
		}

		$this->oForumModerators = new ilForumModerators((int)$_GET['ref_id']);
	}

	/**
	 *
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd        = $this->ctrl->getCmd();

		switch($next_class)
		{
			case 'ilrepositorysearchgui':
				include_once 'Services/Search/classes/class.ilRepositorySearchGUI.php';
				$rep_search = new ilRepositorySearchGUI();
				$rep_search->setCallback($this, 'addModerator');
				$this->ctrl->setReturn($this, 'showModerators');
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
	}

	/**
	 *
	 */
	public function addModerator($users = array())
	{
		if(!$users)
		{
			ilUtil::sendFailure($this->lng->txt('frm_moderators_select_one'));
			return;
		}

		foreach($users as $user_id)
		{
			$this->oForumModerators->addModeratorRole((int)$user_id);
		}

		ilUtil::sendSuccess($this->lng->txt('frm_moderator_role_added_successfully'), true);
		$this->ctrl->redirect($this, 'showModerators');
	}

	/**
	 *
	 */
	public function detachModeratorRole()
	{
		if(!isset($_POST['usr_id']) || !is_array($_POST['usr_id']))
		{
			ilUtil::sendFailure($this->lng->txt('frm_moderators_select_at_least_one'));
			return $this->showModerators();
		}

		$entries = $this->oForumModerators->getCurrentModerators();
		if(count($_POST['usr_id']) == count($entries))
		{
			ilUtil::sendFailure($this->lng->txt('frm_at_least_one_moderator'));
			return $this->showModerators();
		}

		foreach($_POST['usr_id'] as $usr_id)
		{
			$this->oForumModerators->detachModeratorRole((int)$usr_id);
		}

		ilUtil::sendSuccess($this->lng->txt('frm_moderators_detached_role_successfully'));
		return $this->showModerators();
	}

	/**
	 *
	 */
	public function showModerators()
	{
		/**
		 * @var $ilToolbar ilToolbarGUI
		 * @var $lng       ilLanguage
		 */
		global $ilToolbar, $lng;

		include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
		ilRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$ilToolbar,
			array(
				'auto_complete_name' => $lng->txt('user'),
				'submit_name'        => $lng->txt('add'),
				'add_search'         => true,
				'add_from_container' => $this->oForumModerators->getRefId()
			)
		);

		$tbl = new ilTable2GUI($this);
		$tbl->setId('frm_show_mods_tbl_' . (int)$_GET['ref_id']);
		$tbl->setFormAction($this->ctrl->getFormAction($this, 'detachModeratorRole'));
		$tbl->setTitle($this->lng->txt('frm_moderators'));
		$tbl->setRowTemplate('tpl.forum_moderators_table_row.html', 'Modules/Forum');
		$tbl->setDefaultOrderField('login');

		$entries = $this->oForumModerators->getCurrentModerators();
		$num     = count($entries);
		if($num > 1)
		{
			$tbl->addColumn('', 'check', '1%', true);
			$tbl->setSelectAllCheckbox('usr_id');
			$tbl->addMultiCommand('detachModeratorRole', $this->lng->txt('frm_detach_moderator_role'));
		}
		else if(!$entries)
		{
			$tbl->setNoEntriesText($this->lng->txt('frm_moderators_not_exist_yet'));
		}
		$tbl->addColumn($this->lng->txt('login'), 'login', '30%');
		$tbl->addColumn($this->lng->txt('firstname'), 'firstname', '30%');
		$tbl->addColumn($this->lng->txt('lastname'), 'lastname', '30%');

		$result = array();
		$i      = 0;
		foreach($entries as $usr_id)
		{
			/**
			 * @var $user ilObjUser
			 */
			$user = ilObjectFactory::getInstanceByObjId($usr_id);
			if($num > 1)
			{
				$result[$i]['check'] = ilUtil::formCheckbox(false, 'usr_id[]', $user->getId());
			}
			$result[$i]['login']     = $user->getLogin();
			$result[$i]['firstname'] = $user->getFirstname();
			$result[$i]['lastname']  = $user->getLastname();
			++$i;
		}

		$tbl->setData($result);
		$this->tpl->setContent($tbl->getHTML());
	}
}