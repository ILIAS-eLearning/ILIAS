<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestFixedParticipantsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 * 
 * @ilCtrl_Calls ilTestFixedParticipantsGUI: ilTestParticipantsTableGUI
 * @ilCtrl_Calls ilTestFixedParticipantsGUI: ilRepositorySearchGUI
 */
class ilTestFixedParticipantsGUI
{
	/**
	 * Command/Callback Constants
	 */
	
	const CMD_SHOW = 'show';
	const CMD_SET_FILTER = 'setFilter';
	const CMD_RESET_FILTER = 'resetFilter';
	const CMD_SAVE_CLIENT_IP = 'saveClientIp';
	
	const CALLBACK_ADD_PARTICIPANT = 'addParticipants';
	
	/**
	 * @var ilObjTest
	 */
	protected $testObj;
	
	/**
	 * @var ilTestQuestionSetConfig
	 */
	protected $questionSetConfig;
	
	/**
	 * ilTestFixedParticipantsGUI constructor.
	 * @param ilObjTest $testObj
	 */
	public function __construct(ilObjTest $testObj, ilTestQuestionSetConfig $questionSetConfig)
	{
		$this->testObj = $testObj;
		$this->questionSetConfig = $questionSetConfig;
	}
	
	/**
	 * @return ilObjTest
	 */
	public function getTestObj()
	{
		return $this->testObj;
	}
	
	/**
	 * @param ilObjTest $testObj
	 */
	public function setTestObj($testObj)
	{
		$this->testObj = $testObj;
	}
	
	/**
	 * @return ilTestQuestionSetConfig
	 */
	public function getQuestionSetConfig()
	{
		return $this->questionSetConfig;
	}
	
	/**
	 * @param ilTestQuestionSetConfig $questionSetConfig
	 */
	public function setQuestionSetConfig($questionSetConfig)
	{
		$this->questionSetConfig = $questionSetConfig;
	}
	
	/**
	 * Execute Command
	 */
	public function	executeCommand()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		
		switch( $DIC->ctrl()->getNextClass($this) )
		{
			case 'ilrepositorysearchgui':
				
				require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
				require_once 'Services/Search/classes/class.ilRepositorySearchGUI.php';
				
				$gui = new ilRepositorySearchGUI();
				$gui->setCallback($this,self::CALLBACK_ADD_PARTICIPANT, array());
				
				$gui->addUserAccessFilterCallable( ilTestParticipantAccessFilter::getManageParticipantsUserFilter(
					$this->getTestObj()->getRefId()
				));
				
				
				$DIC->ctrl()->setReturn($this, self::CMD_SHOW);
				$DIC->ctrl()->forwardCommand($gui);
				
				break;
				
			default:
				
				$command = $DIC->ctrl()->getCmd(self::CMD_SHOW).'Cmd';
				$this->{$command}();
		}
	}
	
	/**
	 * @param array $a_user_ids
	 * @return bool
	 */
	public function addParticipants($a_user_ids = array())
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
		$filterCallback = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId());
		$a_user_ids = call_user_func_array($filterCallback, [$a_user_ids]);
		
		$countusers = 0;
		// add users 
		if (is_array($a_user_ids))
		{
			$i = 0;
			foreach ($a_user_ids as $user_id)
			{
				$client_ip = $_POST["client_ip"][$i];
				$this->getTestObj()->inviteUser($user_id, $client_ip);
				$countusers++;
				$i++;
			}
		}
		$message = "";
		if ($countusers)
		{
			$message = $DIC->language()->txt("tst_invited_selected_users");
		}
		if (strlen($message))
		{
			ilUtil::sendInfo($message, TRUE);
		}
		else
		{
			ilUtil::sendInfo($DIC->language()->txt("tst_invited_nobody"), TRUE);
			return false;
		}
		
		$DIC->ctrl()->redirect($this, self::CMD_SHOW);
	}
	
	/**
	 * @return ilTestParticipantsTableGUI
	 */
	protected function buildTableGUI()
	{
		require_once 'Modules/Test/classes/tables/class.ilTestParticipantsTableGUI.php';
		$tableGUI = new ilTestParticipantsTableGUI($this, self::CMD_SHOW);
		return $tableGUI;
	}
	
	/**
	 * set table filter command
	 */
	protected function setFilterCmd()
	{
		$tableGUI = $this->buildTableGUI();
		$tableGUI->writeFilterToSession();
		$tableGUI->resetOffset();
		$this->showCmd();
		
	}
	
	/**
	 * reset table filter command
	 */
	protected function resetFilterCmd()
	{
		$tableGUI = $this->buildTableGUI();
		$tableGUI->resetFilter();
		$tableGUI->resetOffset();
		$this->showCmd();
		
	}
	
	/**
	 * show command
	 */
	public function showCmd()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$participantList = $this->getTestObj()->getParticipantList()->getAccessFilteredList(
			ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId())
		);
		
		$tableGUI = $this->buildTableGUI();
		$tableGUI->setRowKeyDataField('usr_id');
		
		if( !$this->getQuestionSetConfig()->areDepenciesBroken() )
		{
			$this->addUserSearchControls($DIC->toolbar(), $DIC->language());
			$tableGUI->setManageInviteesCommandsEnabled(true);
		}
		
		$tableGUI->setAnonymity($this->getTestObj()->getAnonymity());
		$tableGUI->setDescription($DIC->language()->txt("fixed_participants_hint"));
		
		$tableGUI->initColumns();
		$tableGUI->initCommands();
		
		$tableGUI->initFilter();
		$tableGUI->setFilterCommand('participantsSetFilter');
		$tableGUI->setResetCommand('participantsResetFiler');
		
		$DIC->ui()->mainTemplate()->setContent( $DIC->ctrl()->getHTML($tableGUI) );
	}
	
	/**
	 * @param ilToolbarGUI $toolbar
	 * @param ilLanguage $lng
	 */
	protected function addUserSearchControls(ilToolbarGUI $toolbar, ilLanguage $lng)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		// search button
		include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
		ilRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$toolbar,
			array(
				'auto_complete_name'	=> $lng->txt('user'),
				'submit_name'			=> $lng->txt('add')
			)
		);
		
		require_once  'Services/UIComponent/Button/classes/class.ilLinkButton.php';
		$search_btn = ilLinkButton::getInstance();
		$search_btn->setCaption('tst_search_users');
		$search_btn->setUrl($DIC->ctrl()->getLinkTargetByClass('ilRepositorySearchGUI','start'));
		
		$toolbar->addSeparator();
		$toolbar->addButtonInstance($search_btn);
	}
	
	/**
	 * save client ip command
	 */
	protected function saveClientIpCmd()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
		$filterCallback = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId());
		$a_user_ids = call_user_func_array($filterCallback, [(array)$_POST["chbUser"]]);
		
		if (is_array($a_user_ids))
		{
			foreach ($a_user_ids as $user_id)
			{
				$this->getTestObj()->setClientIP($user_id, $_POST["clientip_".$user_id]);
			}
		}
		else
		{
			ilUtil::sendInfo($DIC->language()->txt("select_one_user"), true);
		}
		$DIC->ctrl()->redirect($this, self::CMD_SHOW);
	}
	
	/**
	 * remove participants command
	 */
	protected function removeParticipantsCmd()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
		$filterCallback = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId());
		$a_user_ids = call_user_func_array($filterCallback, [(array)$_POST["chbUser"]]);
		
		if (is_array($a_user_ids))
		{
			foreach ($a_user_ids as $user_id)
			{
				$this->getTestObj()->disinviteUser($user_id);
			}
		}
		else
		{
			ilUtil::sendInfo($DIC->language()->txt("select_one_user"), true);
		}
		
		$DIC->ctrl()->redirect($this, self::CMD_SHOW);
	}
}