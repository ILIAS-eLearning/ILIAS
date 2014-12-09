<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';

/** 
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilECSSettingsGUI: ilECSMappingSettingsGUI, ilECSParticipantSettingsGUI
* @ingroup ServicesWebServicesECS
*/
class ilECSSettingsGUI
{
	const MAPPING_EXPORT = 1;
	const MAPPING_IMPORT = 2;


	protected $tpl;
	protected $lng;
	protected $ctrl;
	protected $tabs_gui;
	

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct()
	{
		global $lng,$tpl,$ilCtrl,$ilTabs;
		
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('ecs');
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;

		$this->initSettings();
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		$this->setSubTabs();
		switch($next_class)
		{
			case 'ilecsmappingsettingsgui':
				include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSMappingSettingsGUI.php';
				$mapset = new ilECSMappingSettingsGUI($this, (int) $_REQUEST['server_id'], (int) $_REQUEST['mid']);
				$this->ctrl->setReturn($this,'communities');
				$this->ctrl->forwardCommand($mapset);
				break;
			
			case 'ilecsparticipantsettingsgui':
				include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettingsGUI.php';
				$part = new ilECSParticipantSettingsGUI(
						(int) $_REQUEST['server_id'],
						(int) $_REQUEST['mid']
				);
				$this->ctrl->setReturn($this,'communities');
				$this->ctrl->forwardCommand($part);
				break;
			
			default:
				if(!$cmd || $cmd == 'view')
				{
					$cmd = "overview";
				}
				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * List available servers
	 * @return void
	 * @global ilToolbar
	 * @global ilTabsGUI
	 */
	public function overview()
	{
		global $ilToolbar,$ilTabs;

		include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';

		$ilTabs->setSubTabActive('overview');
		$ilToolbar->addButton(
			$this->lng->txt('ecs_add_new_ecs'),
			$this->ctrl->getLinkTarget($this,'create')
		);

		$servers = ilECSServerSettings::getInstance();
		$servers->readInactiveServers();

		include_once './Services/WebServices/ECS/classes/class.ilECSServerTableGUI.php';
		$table = new ilECSServerTableGUI($this,'overview');
		$table->initTable();
		$table->parse($servers);
		$this->tpl->setContent($table->getHTML());
		return;
	}

	/**
	 * activate server
	 */
	protected function activate()
	{
		$this->initSettings($_REQUEST['server_id']);
		$this->settings->setEnabledStatus(true);
		$this->settings->update();
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'overview');
	}
	
	/**
	 * activate server
	 */
	protected function deactivate()
	{
		$this->initSettings($_REQUEST['server_id']);
		$this->settings->setEnabledStatus(false);
		$this->settings->update();
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'overview');
	}
	
	/**
	 * Read all importable econtent
	 *
	 * @access protected
	 */
	protected function readAll()
	{
		include_once('Services/WebServices/ECS/classes/class.ilECSConnector.php');
		include_once('Services/WebServices/ECS/classes/class.ilECSConnectorException.php');
		include_once('./Services/WebServices/ECS/classes/class.ilECSEventQueueReader.php');
		include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';

		try
		{
			foreach(ilECSServerSettings::getInstance()->getServers() as $server)
			{
				ilECSEventQueueReader::handleImportReset($server);
				ilECSEventQueueReader::handleExportReset($server);

				include_once('./Services/WebServices/ECS/classes/class.ilECSTaskScheduler.php');
				ilECSTaskScheduler::_getInstanceByServerId($server->getServerId())->startTaskExecution();

				ilUtil::sendInfo($this->lng->txt('ecs_remote_imported'));
				$this->imported();
				return true;
			}
		}
		catch(ilECSConnectorException $e1)
		{
			ilUtil::sendInfo('Cannot connect to ECS server: '.$e1->getMessage());
			$this->imported();
			return false;
		}
		catch(ilException $e2)
		{
			ilUtil::sendInfo('Update failed: '.$e1->getMessage());
			$this->imported();
			return false;
		}
	}

	/**
	 * Create new settings
	 * @global ilTabs $ilTabs 
	 */
	protected function create()
	{
		global $ilTabs;

		$this->initSettings(0);

		$ilTabs->clearTargets();
		$ilTabs->clearSubTabs();
		$ilTabs->setBackTarget($this->lng->txt('back'),$this->ctrl->getLinkTarget($this,'overview'));

		$this->initSettingsForm('create');
	 	$this->tabs_gui->setSubTabActive('ecs_settings');

		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Edit server setting
	 */
	protected function edit()
	{
		global $ilTabs;

		$this->initSettings((int) $_REQUEST['server_id']);
		$this->ctrl->saveParameter($this,'server_id',(int) $_REQUEST['server_id']);

		$ilTabs->clearTargets();
		$ilTabs->clearSubTabs();
		$ilTabs->setBackTarget($this->lng->txt('back'),$this->ctrl->getLinkTarget($this,'overview'));

		$this->initSettingsForm();
	 	$this->tabs_gui->setSubTabActive('ecs_settings');

		$this->tpl->setContent($this->form->getHTML());
	}

	protected function cp()
	{
		$this->initSettings((int) $_REQUEST['server_id']);

		$copy = clone $this->settings;
		$copy->save();

		$this->ctrl->setParameter($this,'server_id',$copy->getServerId());
		ilUtil::sendSuccess($this->lng->txt('ecs_settings_cloned'),true);
		$this->ctrl->redirect($this,'edit');
	}

	/**
	 * Delete confirmation
	 */
	protected function delete()
	{
		global $ilTabs;

		$this->initSettings((int) $_REQUEST['server_id']);

		$ilTabs->clearTargets();
		$ilTabs->clearSubTabs();
		$ilTabs->setBackTarget($this->lng->txt('back'),$this->ctrl->getLinkTarget($this,'overview'));

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setConfirm($this->lng->txt('delete'), 'doDelete');
		$confirm->setCancel($this->lng->txt('cancel'), 'overview');
		$confirm->setHeaderText($this->lng->txt('ecs_delete_setting'));

		$confirm->addItem('','',$this->settings->getServer());
		$confirm->addHiddenItem('server_id', $this->settings->getServerId());
		
		$this->tpl->setContent($confirm->getHTML());
	}

	/**
	 * Do delete
	 */
	protected function doDelete()
	{
		$this->initSettings($_REQUEST['server_id']);
		$this->settings->delete();

		// Delete communities
		include_once './Services/WebServices/ECS/classes/class.ilECSCommunitiesCache.php';
		ilECSCommunitiesCache::delete((int) $_REQUEST['server_id']);

		include_once './Services/WebServices/ECS/classes/class.ilECSDataMappingSettings.php';
		ilECSDataMappingSettings::delete((int) $_REQUEST['server_id']);

		include_once './Services/WebServices/ECS/classes/class.ilECSEventQueueReader.php';
		ilECSEventQueueReader::deleteServer((int) $_REQUEST['server_id']);

		include_once './Services/WebServices/ECS/classes/class.ilECSExport.php';
		ilECSExport::deleteByServer((int) $_REQUEST['server_id']);

		include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
		ilECSImport::deleteByServer((int) $_REQUEST['server_id']);

		include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php';
		ilECSParticipantSettings::deleteByServer((int) $_REQUEST['server_id']);

		ilUtil::sendSuccess($this->lng->txt('ecs_setting_deleted'),true);
		$this->ctrl->redirect($this,'overview');
	}


	/**
	 * show settings 
	 *
	 * @access protected
	 */
	protected function settings()
	{
		$this->initSettingsForm();
	 	$this->tabs_gui->setSubTabActive('ecs_settings');
		
		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * init settings form
	 *
	 * @access protected
	 */
	protected function initSettingsForm($a_mode = 'update')
	{
		if(is_object($this->form))
		{
			return true;
		}
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'settings'));
		$this->form->setTitle($this->lng->txt('ecs_connection_settings'));
		
		$ena = new ilCheckboxInputGUI($this->lng->txt('ecs_active'),'active');
		$ena->setChecked($this->settings->isEnabled());
		$ena->setValue(1);
		$this->form->addItem($ena);
		
		$server_title = new ilTextInputGUI($this->lng->txt('ecs_server_title'),'title');
		$server_title->setValue($this->settings->getTitle());
		$server_title->setSize(80);
		$server_title->setMaxLength(128);
		$server_title->setRequired(true);
		$this->form->addItem($server_title);

		$ser = new ilTextInputGUI($this->lng->txt('ecs_server_url'),'server');
		$ser->setValue((string) $this->settings->getServer());
		$ser->setRequired(true);
		$this->form->addItem($ser);
		
		$pro = new ilSelectInputGUI($this->lng->txt('ecs_protocol'),'protocol');
		// fixed to https
		#$pro->setOptions(array(ilECSSetting::PROTOCOL_HTTP => $this->lng->txt('http'),
		#		ilECSSetting::PROTOCOL_HTTPS => $this->lng->txt('https')));
		$pro->setOptions(array(ilECSSetting::PROTOCOL_HTTPS => 'HTTPS'));
		$pro->setValue($this->settings->getProtocol());
		$pro->setRequired(true);
		$this->form->addItem($pro);
		
		$por = new ilTextInputGUI($this->lng->txt('ecs_port'),'port');
		$por->setSize(5);
		$por->setMaxLength(5);
		$por->setValue((string) $this->settings->getPort());
		$por->setRequired(true);
		$this->form->addItem($por);

		$tcer = new ilRadioGroupInputGUI($this->lng->txt('ecs_auth_type'),'auth_type');
		$tcer->setValue($this->settings->getAuthType());
		$this->form->addItem($tcer);

		// Certificate based authentication
		$cert_based = new ilRadioOption($this->lng->txt('ecs_auth_type_cert'), ilECSSetting::AUTH_CERTIFICATE);
		$tcer->addOption($cert_based);

		$cli = new ilTextInputGUI($this->lng->txt('ecs_client_cert'),'client_cert');
		$cli->setSize(60);
		$cli->setValue((string) $this->settings->getClientCertPath());
		$cli->setRequired(true);
		$cert_based->addSubItem($cli);
		
		$key = new ilTextInputGUI($this->lng->txt('ecs_cert_key'),'key_path');
		$key->setSize(60);
		$key->setValue((string) $this->settings->getKeyPath());
		$key->setRequired(true);
		$cert_based->addSubItem($key);
		
		$cerp = new ilTextInputGUI($this->lng->txt('ecs_key_password'),'key_password');
		$cerp->setSize(12);
		$cerp->setValue((string) $this->settings->getKeyPassword());
		$cerp->setInputType('password');
		$cerp->setRequired(true);
		$cert_based->addSubItem($cerp);

		$cer = new ilTextInputGUI($this->lng->txt('ecs_ca_cert'),'ca_cert');
		$cer->setSize(60);
		$cer->setValue((string) $this->settings->getCACertPath());
		$cer->setRequired(true);
		$cert_based->addSubItem($cer);

		// Apache auth
		$apa_based = new ilRadioOption($this->lng->txt('ecs_auth_type_apache'), ilECSSetting::AUTH_APACHE);
		$tcer->addOption($apa_based);

		$user = new ilTextInputGUI($this->lng->txt('ecs_apache_user'),'auth_user');
		$user->setSize(32);
		$user->setValue((string) $this->settings->getAuthUser());
		$user->setRequired(true);
		$apa_based->addSubItem($user);

		$pass = new ilPasswordInputGUI($this->lng->txt('ecs_apache_pass'), 'auth_pass');
		$pass->setRetype(false);
		$pass->setSize(32);
		$pass->setMaxLength(128);
		$pass->setValue((string) $this->settings->getAuthPass());
		$pass->setRequired(true);
		$pass->setSkipSyntaxCheck(TRUE);
		$apa_based->addSubItem($pass);


		$ser = new ilNonEditableValueGUI($this->lng->txt('cert_serial'));
		$ser->setValue($this->settings->getCertSerialNumber() ? $this->settings->getCertSerialNumber() : $this->lng->txt('ecs_no_value'));
		$cert_based->addSubItem($ser);

		$loc = new ilFormSectionHeaderGUI();
		$loc->setTitle($this->lng->txt('ecs_local_settings'));
		$this->form->addItem($loc);
		
		$pol = new ilDurationInputGUI($this->lng->txt('ecs_polling'),'polling');
		$pol->setShowDays(false);
		$pol->setShowHours(false);
		$pol->setShowMinutes(true);
		$pol->setShowSeconds(true);
		$pol->setSeconds($this->settings->getPollingTimeSeconds());
		$pol->setMinutes($this->settings->getPollingTimeMinutes());
		$pol->setRequired(true);
		$pol->setInfo($this->lng->txt('ecs_polling_info'));
		$this->form->addItem($pol);
		
		$imp = new ilCustomInputGUI($this->lng->txt('ecs_import_id'));
		$imp->setRequired(true);
		
		$tpl = new ilTemplate('tpl.ecs_import_id_form.html',true,true,'Services/WebServices/ECS');
		$tpl->setVariable('SIZE',5);
		$tpl->setVariable('MAXLENGTH',11);
		$tpl->setVariable('POST_VAR','import_id');
		$tpl->setVariable('PROPERTY_VALUE',$this->settings->getImportId());
		
		if($this->settings->getImportId())
		{
			$tpl->setVariable('COMPLETE_PATH',$this->buildPath($this->settings->getImportId()));
		}		
		
		$imp->setHTML($tpl->get());
		$imp->setInfo($this->lng->txt('ecs_import_id_info'));
		$this->form->addItem($imp);
		
		$loc = new ilFormSectionHeaderGUI();
		$loc->setTitle($this->lng->txt('ecs_remote_user_settings'));
		$this->form->addItem($loc);
		
		$role = new ilSelectInputGUI($this->lng->txt('ecs_role'),'global_role');
		$role->setOptions($this->prepareRoleSelect());
		$role->setValue($this->settings->getGlobalRole());
		$role->setInfo($this->lng->txt('ecs_global_role_info'));
		$role->setRequired(true);
		$this->form->addItem($role);
		
		$duration = new ilDurationInputGUI($this->lng->txt('ecs_account_duration'),'duration');
		$duration->setInfo($this->lng->txt('ecs_account_duration_info'));
		$duration->setMonths($this->settings->getDuration());
		$duration->setShowSeconds(false);
		$duration->setShowMinutes(false);
		$duration->setShowHours(false);
		$duration->setShowDays(false);
		$duration->setShowMonths(true);
		$duration->setRequired(true);
		$this->form->addItem($duration);
		
		// Email recipients
		$loc = new ilFormSectionHeaderGUI();
		$loc->setTitle($this->lng->txt('ecs_notifications'));
		$this->form->addItem($loc);
		
		$rcp_user = new ilTextInputGUI($this->lng->txt('ecs_user_rcp'),'user_recipients');
		$rcp_user->setValue((string) $this->settings->getUserRecipientsAsString());
		$rcp_user->setInfo($this->lng->txt('ecs_user_rcp_info'));
		$this->form->addItem($rcp_user);

		$rcp_econ = new ilTextInputGUI($this->lng->txt('ecs_econ_rcp'),'econtent_recipients');
		$rcp_econ->setValue((string) $this->settings->getEContentRecipientsAsString());
		$rcp_econ->setInfo($this->lng->txt('ecs_econ_rcp_info'));
		$this->form->addItem($rcp_econ);

		$rcp_app = new ilTextInputGUI($this->lng->txt('ecs_approval_rcp'),'approval_recipients');
		$rcp_app->setValue((string) $this->settings->getApprovalRecipientsAsString());
		$rcp_app->setInfo($this->lng->txt('ecs_approval_rcp_info'));
		$this->form->addItem($rcp_app);

		if($a_mode == 'update')
		{
			$this->form->addCommandButton('update',$this->lng->txt('save'));
		}
		else
		{
			$this->form->addCommandButton('save',$this->lng->txt('save'));
		}
		$this->form->addCommandButton('overview',$this->lng->txt('cancel'));
	}
	
	/**
	 * save settings
	 *
	 * @access protected
	 */
	protected function update()
	{
		$this->initSettings((int) $_REQUEST['server_id']);
		$this->loadFromPost();
		
		if(!$error = $this->settings->validate())
		{
			$this->settings->update();
			$this->initTaskScheduler();
			#$this->updateTitle();
			ilUtil::sendInfo($this->lng->txt('settings_saved'),true);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt($error));
			$this->edit();
		}
		
		$this->overview();
		return true;
	}

	/**
	 * Save settings
	 * @return <type>
	 */
	protected function save()
	{
		$this->initSettings(0);
		$this->loadFromPost();

		if(!$error = $this->settings->validate())
		{
			$this->settings->save();
			$this->initTaskScheduler();

			#$this->updateTitle();
			ilUtil::sendInfo($this->lng->txt('settings_saved'),true);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt($error));
			return $this->create();
		}
		$GLOBALS['ilCtrl']->redirect($this,'overview');
		return true;
	}

	/**
	 * Update configuration title
	 */
	protected function updateTitle()
	{
		try
		{
			include_once './Services/WebServices/ECS/classes/class.ilECSCommunityReader.php';
			$reader = ilECSCommunityReader::getInstanceByServerId($this->settings->getServerId());

		 	foreach($reader->getCommunities() as $community)
		 	{
				foreach($community->getParticipants() as $part)
				{
					$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($community,true));
					if($part->isSelf())
					{
						$this->settings->setTitle($part->getParticipantName());
						$this->settings->update();
						return true;
					}
				}
			}
		}
		catch(ilECSConnectorException $exc)
		{
			ilUtil::sendFailure($exc->getMessage());
		}
		$this->settings->setTitle('');
		$this->settings->update();
	}

	/**
	 * Load from post
	 */
	protected function loadFromPost()
	{
		$this->settings->setEnabledStatus((int) $_POST['active']);
		$this->settings->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->settings->setServer(ilUtil::stripSlashes($_POST['server']));
		$this->settings->setPort(ilUtil::stripSlashes($_POST['port']));
		$this->settings->setProtocol(ilUtil::stripSlashes($_POST['protocol']));
		$this->settings->setClientCertPath(ilUtil::stripSlashes($_POST['client_cert']));
		$this->settings->setCACertPath(ilUtil::stripSlashes($_POST['ca_cert']));
		$this->settings->setKeyPath(ilUtil::stripSlashes($_POST['key_path']));
		$this->settings->setKeyPassword(ilUtil::stripSlashes($_POST['key_password']));
		$this->settings->setImportId(ilUtil::stripSlashes($_POST['import_id']));
		$this->settings->setPollingTimeMS((int) $_POST['polling']['mm'],(int) $_POST['polling']['ss']);
		$this->settings->setServer(ilUtil::stripSlashes($_POST['server']));
		$this->settings->setGlobalRole((int) $_POST['global_role']);
		$this->settings->setDuration((int) $_POST['duration']['MM']);

		$this->settings->setUserRecipients(ilUtil::stripSlashes($_POST['user_recipients']));
		$this->settings->setEContentRecipients(ilUtil::stripSlashes($_POST['econtent_recipients']));
		$this->settings->setApprovalRecipients(ilUtil::stripSlashes($_POST['approval_recipients']));

		$this->settings->setAuthType((int) $_POST['auth_type']);
		$this->settings->setAuthPass(ilUtil::stripSlashes($_POST['auth_pass']));
		$this->settings->setAuthUser(ilUtil::stripSlashes($_POST['auth_user']));

	}
	
	/**
	 * Refresh participants
	 */
	protected function refreshParticipants()
	{
		include_once './Services/WebServices/ECS/classes/class.ilECSCommunityReader.php';
		include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';
		include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php';
		include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';
		
		$servers = ilECSServerSettings::getInstance();
		$servers->readInactiveServers();
		foreach($servers->getServers() as $server)
		{
			$creader = ilECSCommunityReader::getInstanceByServerId($server->getServerId());
			
			// read community
			foreach(ilECSParticipantSettings::getAvailabeMids($server->getServerId()) as $mid)
			{
				if(!$creader->getParticipantByMID($mid))
				{
					$GLOBALS['ilLog']->write(__METHOD__.': Deleting deprecated participant '. $server->getServerId().' '. $mid);
					
					$part = new ilECSParticipantSetting($server->getServerId(),$mid);
					$part->delete();
				}
			}
		}
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),TRUE);
		$this->ctrl->redirect($this,'communities');
	}
	
	/**
	 * show communities
	 *
	 * @access public
	 * 
	 */
	public function communities()
	{
		// add toolbar to refresh communities
		$GLOBALS['ilToolbar']->addButton(
				$this->lng->txt('ecs_refresh_participants'),
				$this->ctrl->getLinkTarget($this,'refreshParticipants')
		);
		
	 	$this->tabs_gui->setSubTabActive('ecs_communities');

	 	$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.ecs_communities.html','Services/WebServices/ECS');
	 	
	 	$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this,'updateCommunities'));
	 	$this->tpl->setVariable('TXT_SAVE',$this->lng->txt('save'));
	 	$this->tpl->setVariable('TXT_CANCEL', $this->lng->txt('cancel'));
	 	
	 	include_once('Services/WebServices/ECS/classes/class.ilECSCommunityReader.php');
	 	include_once('Services/WebServices/ECS/classes/class.ilECSCommunityTableGUI.php');

		include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';
		$settings = ilECSServerSettings::getInstance();
		#$settings->readInactiveServers();
			
		foreach($settings->getServers() as $server)
		{
			// Try to read communities
			try 
			{
				$reader = ilECSCommunityReader::getInstanceByServerId($server->getServerId());
				foreach($reader->getCommunities() as $community)
				{
					$this->tpl->setCurrentBlock('table_community');
					$table_gui = new ilECSCommunityTableGUI($server,$this,'communities',$community->getId());
					$table_gui->setTitle($community->getTitle().' ('.$community->getDescription().')');
					$table_gui->parse($community->getParticipants());
					$this->tpl->setVariable('TABLE_COMM',$table_gui->getHTML());
					$this->tpl->parseCurrentBlock();
				}
			}
			catch(ilECSConnectorException $exc)
			{
				// Maybe server is not fully configured
				continue;
			}

			// Show section for each server
			$this->tpl->setCurrentBlock('server');
			$this->tpl->setVariable('TXT_SERVER_NAME',$server->getTitle());
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	 * Validate import types
	 * @param array $import_types
	 * @return bool
	 */
	protected function validateImportTypes(&$import_types)
	{
		include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';
		
		$num_cms = 0;
		foreach((array) $import_types as $sid => $server)
		{
			foreach((array) $server as $mid => $import_type)
			{
				if($import_type == ilECSParticipantSetting::IMPORT_CMS)
				{
					++$num_cms;
				}
			}
		}
		
		if($num_cms <= 1)
		{
			return true;
		}
		// Change to import type "UNCHANGED"
		$new_types = array();
		foreach((array) $import_types as $sid => $server)
		{
			foreach((array) $server as $mid => $import_type)
			{
				if($import_type == ilECSParticipantSetting::IMPORT_CMS)
				{
					$new_types[$sid][$mid] = ilECSParticipantSetting::IMPORT_UNCHANGED;
				}
				else
				{
					$new_types[$sid][$mid] = $import_type;
				}
			}
		}
		$import_types = $new_types;
		return false;
	}
	
	/**
	 * update whitelist
	 *
	 * @access protected
	 * 
	 */
	protected function updateCommunities()
	{
		global $ilLog;

		include_once './Services/WebServices/ECS/classes/class.ilECSCommunityReader.php';
		include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';

		// @TODO: Delete deprecated communities
		$invalidImportTypes = false;
		if(!$this->validateImportTypes($_POST['import_type']))
		{
			$invalidImportTypes = true;
		}

		$servers = ilECSServerSettings::getInstance();
		foreach($servers->getServers() as $server)
		{
			try {
				// Read communities
				$cReader = ilECSCommunityReader::getInstanceByServerId($server->getServerId());

				// Update community cache
				foreach($cReader->getCommunities() as $community)
				{
					include_once './Services/WebServices/ECS/classes/class.ilECSCommunityCache.php';
					$cCache = ilECSCommunityCache::getInstance($server->getServerId(), $community->getId());
					$cCache->setCommunityName($community->getTitle());
					$cCache->setMids($community->getMids());
					$cCache->setOwnId($community->getOwnId());
					$cCache->update();
				}
			}
			catch(Exception $e)
			{
				$GLOBALS['ilLog']->write(__METHOD__.': Cannot read ecs communities: '.$e->getMessage());
			}
		}

		include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';
		foreach((array) $_POST['sci_mid'] as $sid => $tmp)
		{
			foreach((array) $_POST['sci_mid'][$sid] as $mid => $tmp)
			{
				$set = new ilECSParticipantSetting($sid, $mid);
				#$set->enableExport(array_key_exists($mid, (array) $_POST['export'][$sid]) ? true : false);
				#$set->enableImport(array_key_exists($mid, (array) $_POST['import'][$sid]) ? true : false);
				$set->setImportType($_POST['import_type'][$sid][$mid]);

				// update title/cname
				try {
					$part = ilECSCommunityReader::getInstanceByServerId($sid)->getParticipantByMID($mid);
					if($part instanceof ilECSParticipant)
					{
						$set->setTitle($part->getParticipantName());
					}
					$com = ilECSCommunityReader::getInstanceByServerId($sid)->getCommunityByMID($mid);
					if($com instanceof ilECSCommunity)
					{
						$set->setCommunityName($com->getTitle());
					}
				}
				catch(Exception $e)
				{
					$GLOBALS['ilLog']->write(__METHOD__.': Cannot read ecs communities: '.$e->getMessage());
				}

				$set->update();
			}
		}
		if($invalidImportTypes)
		{
			ilUtil::sendFailure($this->lng->txt('ecs_invalid_import_type_cms'),true);
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		}
		$GLOBALS['ilCtrl']->redirect($this,'communities');

		// TODO: Do update of remote courses and ...

		return true;
	}


	/**
	 * Handle tabs for ECS data mapping
	 * @param int $a_active
	 * @global ilTabsGUI ilTabs
	 */
	protected function setMappingTabs($a_active)
	{
		global $ilTabs;

		$ilTabs->clearTargets();
		$ilTabs->clearSubTabs();

		$ilTabs->setBackTarget(
			$this->lng->txt('ecs_settings'),
			$this->ctrl->getLinkTarget($this,'overview')
		);
		$ilTabs->addTab(
			'import',
			$this->lng->txt('ecs_tab_import'),
			$this->ctrl->getLinkTarget($this,'importMappings')
		);
		$ilTabs->addTab(
			'export',
			$this->lng->txt('ecs_tab_export'),
			$this->ctrl->getLinkTarget($this,'exportMappings')
		);


		switch($a_active)
		{
			case self::MAPPING_IMPORT:
				$ilTabs->activateTab('import');
				break;

			case self::MAPPING_EXPORT:
				$ilTabs->activateTab('export');
				break;
		}
		return true;
	}
	
	/**
	 * Show mapping settings (EContent-Data <-> (Remote)Course
	 *
	 * @access protected
	 */
	public function importMappings()
	{
		global $ilToolbar;
		
	 	include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');

		$this->setMappingTabs(self::MAPPING_IMPORT);

		$fields = ilAdvancedMDFieldDefinition::getInstancesByObjType('crs');
		if(!count($fields))
		{
			ilUtil::sendInfo($this->lng->txt('ecs_no_adv_md'));
			return true;
		}

		include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';
		$settings = ilECSServerSettings::getInstance();
		$settings->readInactiveServers();
		
		$sel_srv = (int)$_REQUEST["ecs_mapping_server"];
		if(!$sel_srv)
		{
			$sel_srv = $_SESSION["ecs_sel_srv"];
		}
		else
		{
			$_SESSION["ecs_sel_srv"] = $sel_srv;
		}
		
		// Iterate all servers
		$options = array(0 => $this->lng->txt("please_choose"));
		foreach($settings->getServers() as $server)
		{
			$title = $server->getTitle();
			if(!$title)
			{
				$title = "ECS (".$server->getServerId().")";
			}
			$options[$server->getServerId()] = $title;
		}
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";		
		$sel = new ilSelectInputGUI("", "ecs_mapping_server");
		$sel->setOptions($options);
		$sel->setValue($sel_srv);
		$ilToolbar->addInputItem($sel);
		
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this, "importMappings"));		
		$ilToolbar->addFormButton($this->lng->txt("submit"), "importMappings");
		
		if($sel_srv)
		{
			$form = $this->initMappingsForm($sel_srv, self::MAPPING_IMPORT);
			$this->tpl->setContent($form->getHTML());
		}				
		
		return true;
	}

	/**
	 * Show mapping settings (EContent-Data <-> (Remote)Course
	 *
	 * @access protected
	 */
	protected function exportMappings()
	{
		global $ilToolbar;
		
	 	include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');

		$this->setMappingTabs(self::MAPPING_EXPORT);

		$fields = ilAdvancedMDFieldDefinition::getInstancesByObjType('crs');
		if(!count($fields))
		{
			ilUtil::sendInfo($this->lng->txt('ecs_no_adv_md'));
			return true;
		}

		include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';
		$settings = ilECSServerSettings::getInstance();
		$settings->readInactiveServers();
				
		$sel_srv = (int)$_REQUEST["ecs_mapping_server"];
		if(!$sel_srv)
		{
			$sel_srv = $_SESSION["ecs_sel_srv"];
		}
		else
		{
			$_SESSION["ecs_sel_srv"] = $sel_srv;
		}
		
		// Iterate all servers
		$options = array(0 => $this->lng->txt("please_choose"));
		foreach($settings->getServers() as $server)
		{
			$title = $server->getTitle();
			if(!$title)
			{
				$title = "ECS (".$server->getServerId().")";
			}
			$options[$server->getServerId()] = $title;
		}
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";		
		$sel = new ilSelectInputGUI("", "ecs_mapping_server");
		$sel->setOptions($options);
		$sel->setValue($sel_srv);
		$ilToolbar->addInputItem($sel);
		
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this, "exportMappings"));		
		$ilToolbar->addFormButton($this->lng->txt("submit"), "exportMappings");
		
		if($sel_srv)
		{
			$form = $this->initMappingsForm($sel_srv,self::MAPPING_EXPORT);
			$this->tpl->setContent($form->getHTML());
		}
		
		return true;
	}
	
	/**
	 * Save mappings
	 *
	 * @access protected
	 * 
	 */
	protected function saveImportMappings()
	{
		foreach((array) $_POST['mapping'] as $mtype => $mappings)
		{
			foreach((array) $mappings as $ecs_field => $advmd_id)
			{
				include_once './Services/WebServices/ECS/classes/class.ilECSDataMappingSetting.php';
				$map = new ilECSDataMappingSetting(
					(int) $_REQUEST['ecs_mapping_server'],
					(int) $mtype,
					$ecs_field
				);
				$map->setAdvMDId($advmd_id);
				$map->save();
			}
		}
		
		ilUtil::sendInfo($this->lng->txt('settings_saved'),true);		
		$this->ctrl->setParameter($this, "ecs_mapping_server", (int)$_POST['ecs_mapping_server']);
		$this->ctrl->redirect($this,'importMappings');
		return true;
	}

	/**
	 * Save mappings
	 *
	 * @access protected
	 *
	 */
	protected function saveExportMappings()
	{
		foreach((array) $_POST['mapping'] as $mtype => $mappings)
		{
			foreach((array) $mappings as $ecs_field => $advmd_id)
			{
				include_once './Services/WebServices/ECS/classes/class.ilECSDataMappingSetting.php';
				$map = new ilECSDataMappingSetting(
					(int) $_POST['ecs_mapping_server'],
					(int) $mtype,
					$ecs_field
				);
				$map->setAdvMDId($advmd_id);
				$map->save();
			}
		}

		ilUtil::sendInfo($this->lng->txt('settings_saved'),true);
		$this->ctrl->setParameter($this, "ecs_mapping_server", (int)$_POST['ecs_mapping_server']);
		$this->ctrl->redirect($this,'exportMappings');
		return true;
	}

	/**
	 * init mapping form
	 *
	 * @param int $a_server_id
	 * @return ilPropertyFormGUI $form
	 *
	 * @access protected
	 */
	protected function initMappingsForm($a_server_id,$mapping_type)
	{
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');		

		include_once('./Services/WebServices/ECS/classes/class.ilECSDataMappingSettings.php');
		$mapping_settings = ilECSDataMappingSettings::getInstanceByServerId($a_server_id);
			
		$form = new ilPropertyFormGUI();

		if($mapping_type == self::MAPPING_IMPORT)
		{
			$form->setTitle($this->lng->txt('ecs_mapping_tbl'));
			$form->addCommandButton('saveImportMappings',$this->lng->txt('save'));
			$form->addCommandButton('importMappings',$this->lng->txt('cancel'));
		}
		else
		{
			$form->setTitle($this->lng->txt('ecs_mapping_exp_tbl'));
			$form->addCommandButton('saveExportMappings',$this->lng->txt('save'));
			$form->addCommandButton('exportMappings',$this->lng->txt('cancel'));
		}

		$form->setFormAction($this->ctrl->getFormAction($this,'saveMappings'));

		if($mapping_type == self::MAPPING_IMPORT)
		{
			$assignments = new ilCustomInputGUI($this->lng->txt('ecs_mapping_crs'));
			$form->addItem($assignments);
		}

	 	include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
		$fields = ilAdvancedMDFieldDefinition::getInstancesByObjType('crs');
		$options = $this->prepareFieldSelection($fields);

		// get all optional ecourse fields
		include_once('./Services/WebServices/ECS/classes/class.ilECSUtils.php');
		$optional = ilECSUtils::_getOptionalECourseFields();
		foreach($optional as $field_name)
		{
			if($mapping_type == self::MAPPING_IMPORT)
			{
				$select = new ilSelectInputGUI(
					$this->lng->txt('ecs_field_'.$field_name),
					'mapping'.'['.ilECSDataMappingSetting::MAPPING_IMPORT_CRS.']['.$field_name.']'
				);

				$select->setValue(
					$mapping_settings->getMappingByECSName(
						ilECSDataMappingSetting::MAPPING_IMPORT_CRS,
						$field_name)
				);
				$select->setOptions($options);
				$assignments->addSubItem($select);
			}
			else
			{
				$select = new ilSelectInputGUI(
					$this->lng->txt('ecs_field_'.$field_name),
					'mapping'.'['.ilECSDataMappingSetting::MAPPING_EXPORT.']['.$field_name.']'
				);
				$select->setValue(
					$mapping_settings->getMappingByECSName(
						ilECSDataMappingSetting::MAPPING_EXPORT,
						$field_name)
				);
				$select->setOptions($options);
				$form->addItem($select);
			}
		}

		$server = new ilHiddenInputGUI('ecs_mapping_server');
		$server->setValue($a_server_id);
		$form->addItem($server);

		// Remote courses
		// no remote course settings for export
		if($mapping_type == self::MAPPING_EXPORT)
		{
			return $form;
		}

		$rcrs = new ilCustomInputGUI($this->lng->txt('ecs_mapping_rcrs'));	
		$form->addItem($rcrs);

	 	include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
		$fields = ilAdvancedMDFieldDefinition::getInstancesByObjType('rcrs');
		$options = $this->prepareFieldSelection($fields);

		// get all optional econtent fields
		include_once('./Services/WebServices/ECS/classes/class.ilECSUtils.php');
		$optional = ilECSUtils::_getOptionalEContentFields();
		foreach($optional as $field_name)
		{
			$select = new ilSelectInputGUI(
				$this->lng->txt('ecs_field_'.$field_name),
				'mapping['.ilECSDataMappingSetting::MAPPING_IMPORT_RCRS.']['.$field_name.']');
			$select->setValue(
				$mapping_settings->getMappingByECSName(
					ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,
					$field_name)
			);
			$select->setOptions($options);
			$rcrs->addSubItem($select);
		}
		return $form;
	}
	
	/**
	 * Category mappings 
	 * @return
	 */
	protected function categoryMapping()
	{
		$this->tabs_gui->setSubTabActive('ecs_category_mapping');
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.category_mapping.html','Services/WebServices/ECS');
		
		$this->initRule();
		$this->initCategoryMappingForm();
		
		
		$this->tpl->setVariable('NEW_RULE_TABLE',$this->form->getHTML());
		if($html = $this->showRulesTable())
		{
			$this->tpl->setVariable('RULES_TABLE',$html);			
		}
	}
	
	/**
	 * save category mapping 
	 * @return
	 */
	protected function addCategoryMapping()
	{
		$this->initRule();
		
		$this->initCategoryMappingForm('add');
		if($this->form->checkInput())
		{			
			$this->rule->setContainerId($this->form->getInput('import_id'));
			$this->rule->setFieldName($this->form->getInput('field'));
			$this->rule->setMappingType($this->form->getInput('type'));

			switch($this->form->getInput('type'))
			{
				case ilECSCategoryMappingRule::TYPE_FIXED:
					$this->rule->setMappingValue($this->form->getInput('mapping_value'));
					break;
				
				case ilECSCategoryMappingRule::TYPE_DURATION:
					$this->rule->setDateRangeStart($this->form->getItemByPostVar('dur_begin')->getDate());
					$this->rule->setDateRangeEnd($this->form->getItemByPostVar('dur_end')->getDate());
					break;
				
				case ilECSCategoryMappingRule::TYPE_BY_TYPE:
					$this->rule->setByType($this->form->getInput('by_type'));
					break;
			}
			
			if($err = $this->rule->validate())
			{
				ilUtil::sendInfo($this->lng->txt($err));
				$this->form->setValuesByPost();
				$this->categoryMapping();
				return false;
			}
			
			$this->rule->save();
			ilUtil::sendInfo($this->lng->txt('settings_saved'));
			unset($this->rule);
			$this->categoryMapping();
			return true;
		}
		ilUtil::sendInfo($this->lng->txt('err_check_input'));
		$this->form->setValuesByPost();
		$this->categoryMapping();
		return false;
	}
	
	/**
	 * Edit category mapping
	 * @return
	 */
	protected function editCategoryMapping()
	{
		if(!$_REQUEST['rule_id'])
		{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			$this->categoryMapping();
			return false;
		}

		$this->tabs_gui->setSubTabActive('ecs_category_mapping');
		$this->ctrl->saveParameter($this,'rule_id');
		$this->initRule((int) $_REQUEST['rule_id']);
		
		$this->initCategoryMappingForm('edit');
		$this->tpl->setContent($this->form->getHTML());
		return true;
	}
	
	/**
	 * update category mapping 
	 * @return
	 */
	protected function updateCategoryMapping()
	{
		if(!$_REQUEST['rule_id'])
		{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			$this->categoryMapping();
			return false;
		}
		$this->ctrl->saveParameter($this,'rule_id');
		$this->initRule((int) $_REQUEST['rule_id']);
		$this->initCategoryMappingForm('edit');
		if($this->form->checkInput())
		{
			$this->rule->setContainerId($this->form->getInput('import_id'));			
			$this->rule->setFieldName($this->form->getInput('field'));
			$this->rule->setMappingType($this->form->getInput('type'));
			
			switch($this->form->getInput('type'))
			{
				case ilECSCategoryMappingRule::TYPE_FIXED:
					$this->rule->setMappingValue($this->form->getInput('mapping_value'));
					break;
				
				case ilECSCategoryMappingRule::TYPE_DURATION:
					$this->rule->setDateRangeStart($this->form->getItemByPostVar('dur_begin')->getDate());
					$this->rule->setDateRangeEnd($this->form->getItemByPostVar('dur_end')->getDate());
					break;
				
				case ilECSCategoryMappingRule::TYPE_BY_TYPE:
					$this->rule->setByType($this->form->getInput('by_type'));
					break;
			}
			
			if($err = $this->rule->validate())
			{				
				ilUtil::sendInfo($this->lng->txt($err));
				$this->form->setValuesByPost();
				$this->editCategoryMapping();
				return false;
			}
			
			$this->rule->update();
			ilUtil::sendInfo($this->lng->txt('settings_saved'),true);
			$this->ctrl->redirect($this,'categoryMapping');
			return true;
		}
		ilUtil::sendInfo($this->lng->txt('err_check_input'));
		$this->form->setValuesByPost();
		$this->editCategoryMapping();
		return false;
		
	}
	
	/**
	 * Delete selected category mappings 
	 */
	protected function deleteCategoryMappings()
	{
		if(!is_array($_POST['rules']) or !$_POST['rules'])
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'));
			$this->categoryMapping();
			return false;
		}
		foreach($_POST['rules'] as $rule_id)
		{
			include_once './Services/WebServices/ECS/classes/class.ilECSCategoryMappingRule.php';
			$rule = new ilECSCategoryMappingRule($rule_id);
			$rule->delete();			
		}
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->categoryMapping();
		return true;
	}
	
	/**
	 * Show rules table 
	 * @return
	 */
	protected function showRulesTable()
	{
		include_once './Services/WebServices/ECS/classes/class.ilECSCategoryMapping.php';
		
		if(!$rules = ilECSCategoryMapping::getActiveRules())
		{
			return false;
		}
		include_once './Services/WebServices/ECS/classes/class.ilECSCategoryMappingTableGUI.php';
		$rule_table = new ilECSCategoryMappingTableGUI($this,'categoryMapping');
		$rule_table->parse($rules);
		return $rule_table->getHTML();
	}
	
	/**
	 * Init rule 
	 * @param int	$rule_id	rule id
	 * @return
	 */
	protected function initRule($a_rule_id = 0)
	{
		if(is_object($this->rule))
		{
			return $this->rule;
		}
		
		include_once './Services/WebServices/ECS/classes/class.ilECSCategoryMappingRule.php';
		$this->rule = new ilECSCategoryMappingRule($a_rule_id);
	}
	
	/**
	 * Init category mapping form 
	 * @return
	 */
	protected function initCategoryMappingForm($a_mode = 'add')
	{
		global $ilDB;

		if(is_object($this->form))
		{
			return true;
		}

		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		include_once './Services/WebServices/ECS/classes/class.ilECSCategoryMappingRule.php';
		
		$this->form = new ilPropertyFormGUI();
		
		if($a_mode == 'add') 
		{
			$this->form->setTitle($this->lng->txt('ecs_new_category_mapping'));
			$this->form->setFormAction($this->ctrl->getFormAction($this,'categoryMapping'));
			$this->form->addCommandButton('addCategoryMapping',$this->lng->txt('save'));
			$this->form->addCommandButton('categoryMapping',$this->lng->txt('cancel'));
		}
		else
		{
			$this->form->setTitle($this->lng->txt('ecs_edit_category_mapping'));
			$this->form->setFormAction($this->ctrl->getFormAction($this,'editCategoryMapping'));
			$this->form->addCommandButton('updateCategoryMapping',$this->lng->txt('save'));
			$this->form->addCommandButton('categoryMapping',$this->lng->txt('cancel'));
		}
		
		$imp = new ilCustomInputGUI($this->lng->txt('ecs_import_id'),'import_id');
		$imp->setRequired(true);
		
		$tpl = new ilTemplate('tpl.ecs_import_id_form.html',true,true,'Services/WebServices/ECS');
		$tpl->setVariable('SIZE',5);
		$tpl->setVariable('MAXLENGTH',11);
		$tpl->setVariable('POST_VAR','import_id');
		$tpl->setVariable('PROPERTY_VALUE',$this->rule->getContainerId());
		
		if($this->settings->getImportId())
		{
			$tpl->setVariable('COMPLETE_PATH',$this->buildPath($this->rule->getContainerId()));
		}		
		
		$imp->setHTML($tpl->get());
		$imp->setInfo($this->lng->txt('ecs_import_id_info'));
		$this->form->addItem($imp);
		
		include_once('./Services/WebServices/ECS/classes/class.ilECSCategoryMapping.php');		
		$select = new ilSelectInputGUI($this->lng->txt('ecs_attribute_name'),'field');
		$select->setValue($this->rule->getFieldName());
		$select->setRequired(true);
		$select->setOptions(ilECSCategoryMapping::getPossibleFields());
		$this->form->addItem($select);

		//	Value
		$value = new ilRadioGroupInputGUI($this->lng->txt('ecs_cat_mapping_type'),'type');
		$value->setValue($this->rule->getMappingType());
		$value->setRequired(true);
		
		$fixed = new ilRadioOption($this->lng->txt('ecs_cat_mapping_fixed'),ilECSCategoryMappingRule::TYPE_FIXED);
		$fixed->setInfo($this->lng->txt('ecs_cat_mapping_fixed_info'));
		
			$fixed_val = new ilTextInputGUI($this->lng->txt('ecs_cat_mapping_values'),'mapping_value');
			$fixed_val->setValue($this->rule->getMappingValue());
			$fixed_val->setMaxLength(255);
			$fixed_val->setSize(40);
			$fixed_val->setRequired(true);
			$fixed->addSubItem($fixed_val);
		
		$value->addOption($fixed);

		$duration = new ilRadioOption($this->lng->txt('ecs_cat_mapping_duration'),ilECSCategoryMappingRule::TYPE_DURATION);
		$duration->setInfo($this->lng->txt('ecs_cat_mapping_duration_info'));
		
			$dur_start = new ilDateTimeInputGUI($this->lng->txt('from'),'dur_begin');
			$dur_start->setDate($this->rule->getDateRangeStart());
			$duration->addSubItem($dur_start);
			
			$dur_end = new ilDateTimeInputGUI($this->lng->txt('to'),'dur_end');
			$dur_end->setDate($this->rule->getDateRangeEnd());
			$duration->addSubItem($dur_end);
		
		$value->addOption($duration);
		
		$type = new ilRadioOption($this->lng->txt('ecs_cat_mapping_by_type'),ilECSCategoryMappingRule::TYPE_BY_TYPE);
		$type->setInfo($this->lng->txt('ecs_cat_mapping_by_type_info'));
		
			$options = ilECSUtils::getPossibleRemoteTypes(true);
		
			$types = new ilSelectInputGUI($this->lng->txt('type'), 'by_type');
			$types->setOptions($options);
			$types->setValue($this->rule->getByType());
			$types->setRequired(true);
			$type->addSubitem($types);
		
		$value->addOption($type);
		
		$this->form->addItem($value);
		
	}
	
	
	/**
	 * Show imported materials
	 *
	 * @access protected
	 */
	protected function imported()
	{
		global $ilUser, $ilToolbar;

		$this->tabs_gui->setSubTabActive('ecs_import');
	
		include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';
		if(ilECSServerSettings::getInstance()->activeServerExists())
		{
			$ilToolbar->addButton(
				$this->lng->txt('ecs_read_remote_links'),
				$this->ctrl->getLinkTarget($this,'readAll')
			);		
			
			$ilToolbar->addSeparator();
		}					
		
		
		$sel_type = $_REQUEST["otype"];
		if(!$sel_type)
		{
			$sel_type = "rcrs";
		}
		
		include_once './Services/WebServices/ECS/classes/class.ilECSUtils.php';
		$options = ilECSUtils::getPossibleRemoteTypes(true);
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";		
		$sel = new ilSelectInputGUI("", "otype");
		$sel->setOptions($options);
		$sel->setValue($sel_type);
		$ilToolbar->addInputItem($sel);
		
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this, "imported"));		
		$ilToolbar->addFormButton($this->lng->txt("submit"), "imported");
							
		$robjs = ilUtil::_getObjectsByOperations($sel_type,'visible',$ilUser->getId(),-1);	
		if(count($robjs))
		{
			$ilToolbar->addSeparator();
			
			$ilToolbar->addButton(
				$this->lng->txt('csv_export'),
				$this->ctrl->getLinkTarget($this,'exportImported')
			);
		}
		
	 	include_once('Services/WebServices/ECS/classes/class.ilECSImportedContentTableGUI.php');
 		$table_gui = new ilECSImportedContentTableGUI($this,'imported');				
 		$table_gui->setTitle($this->lng->txt('ecs_imported_content'));
 		$table_gui->parse($robjs);
		$this->tpl->setContent($table_gui->getHTML());

		return true;
	}
	
	/**
	 * csv export of imported remote courses
	 *
	 * @access protected
	 * @return
	 */
	protected function exportImported()
	{
		global $ilObjDataCache,$ilUser;
		
		// :TODO: mind resource type and move to ilRemoteObjectBase...
		
		$rcourses = ilUtil::_getObjectsByOperations('rcrs','visible',$ilUser->getId(),-1);
		
		// Read participants
		include_once('./Modules/RemoteCourse/classes/class.ilObjRemoteCourse.php');
		include_once('./Services/WebServices/ECS/classes/class.ilECSCommunityReader.php');
		try
		{
			$reader = ilECSCommunityReader::_getInstance();
		}
		catch(ilECSConnectorException $e)
		{
			$reader = null;
		}
		
		// read obj_ids
		$ilObjDataCache->preloadReferenceCache($rcourses);
		$obj_ids = array();
		foreach($rcourses as $rcrs_ref_id)
		{
			$obj_id = $ilObjDataCache->lookupObjId($rcrs_ref_id);
			$obj_ids[$obj_id] = $obj_id; 
		}

		include_once('Services/Utilities/classes/class.ilCSVWriter.php');
		$writer = new ilCSVWriter();
		
		$writer->addColumn($this->lng->txt('title'));
		$writer->addColumn($this->lng->txt('description'));
		$writer->addColumn($this->lng->txt('ecs_imported_from'));			
		$writer->addColumn($this->lng->txt('ecs_field_courseID'));			
		$writer->addColumn($this->lng->txt('ecs_field_term'));			
		$writer->addColumn($this->lng->txt('ecs_field_lecturer'));			
		$writer->addColumn($this->lng->txt('ecs_field_courseType'));			
		$writer->addColumn($this->lng->txt('ecs_field_semester_hours'));			
		$writer->addColumn($this->lng->txt('ecs_field_credits'));			
		$writer->addColumn($this->lng->txt('ecs_field_room'));			
		$writer->addColumn($this->lng->txt('ecs_field_cycle'));			
		$writer->addColumn($this->lng->txt('ecs_field_begin'));			
		$writer->addColumn($this->lng->txt('ecs_field_end'));			
		$writer->addColumn($this->lng->txt('last_update'));	
		
		include_once('./Services/WebServices/ECS/classes/class.ilECSDataMappingSettings.php');
		$settings = ilECSDataMappingSettings::_getInstance();
		
		foreach($obj_ids as $obj_id)
		{
			include_once "Services/WebServices/ECS/classes/class.ilECSUtils.php";
			$values = ilECSUtils::getAdvancedMDValuesForObjId($obj_id);
			
			$writer->addRow();
			$writer->addColumn(ilObject::_lookupTitle($obj_id));
			$writer->addColumn(ilObject::_lookupDescription($obj_id));
			
			$mid = ilObjRemoteCourse::_lookupMID($obj_id);	
			if($reader and ($participant = $reader->getParticipantByMID($mid)))
			{
				$writer->addColumn($participant->getParticipantName());
			}
			$field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'courseID');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'term');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'lecturer');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'courseType');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'semester_hours');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'credits');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'room');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'cycle');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'begin');
			$writer->addColumn(isset($values[$field]) ?  ilFormat::formatUnixTime($values[$field],true) : '');
			
			$field = $settings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'end');
			$writer->addColumn(isset($values[$field]) ?  ilFormat::formatUnixTime($values[$field],true) : '');
			
			$writer->addColumn($ilObjDataCache->lookupLastUpdate($obj_id));
		}
		ilUtil::deliverData($writer->getCSVString(), date("Y_m_d")."_ecs_import.csv", "text/csv");	
	}
	
	/**
	 * Show released materials 
	 *
	 * @access protected
	 * @return
	 */
	protected function released()
	{
		global $ilUser, $ilToolbar;
		
		$this->tabs_gui->setSubTabActive('ecs_released');
			 			
		if($this->settings->isEnabled())
		{
			$ilToolbar->addButton(
				$this->lng->txt('ecs_read_remote_links'),
				$this->ctrl->getLinkTarget($this,'readAll')
			);		
			
			$ilToolbar->addSeparator();
		}
		
		$sel_type = $_REQUEST["otype"];
		if(!$sel_type)
		{
			$sel_type = "rcrs";
		}
		
		include_once "Services/WebServices/ECS/classes/class.ilECSUtils.php";
		$options = ilECSUtils::getPossibleReleaseTypes(true);
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";		
		$sel = new ilSelectInputGUI("", "otype");
		$sel->setOptions($options);
		$sel->setValue($sel_type);
		$ilToolbar->addInputItem($sel);
		
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this, "released"));		
		$ilToolbar->addFormButton($this->lng->txt("submit"), "released");
									
		include_once('./Services/WebServices/ECS/classes/class.ilECSExport.php');
		$exported = ilECSExport::getExportedIdsByType($sel_type);
		if(count($exported))
		{
			$ilToolbar->addSeparator();
			
			$ilToolbar->addButton(
				$this->lng->txt('csv_export'),
				$this->ctrl->getLinkTarget($this,'exportReleased')
			);
		}
		
	 	include_once('Services/WebServices/ECS/classes/class.ilECSReleasedContentTableGUI.php');
		$table_gui = new ilECSReleasedContentTableGUI($this,'released');				
 		$table_gui->setTitle($this->lng->txt('ecs_released_content'));
 		$table_gui->parse($exported);
		$this->tpl->setContent($table_gui->getHTML());

		return true;	
	}
	
	/**
	 * export released
	 *
	 * @access protected
	 * @return
	 */
	protected function exportReleased()
	{
		global $ilObjDataCache;
		
		include_once('./Services/WebServices/ECS/classes/class.ilECSExport.php');
		$exported = ilECSExport::getExportedIds();
		$ilObjDataCache->preloadObjectCache($exported);
		
		include_once('Services/Utilities/classes/class.ilCSVWriter.php');
		$writer = new ilCSVWriter();
		
		$writer->addColumn($this->lng->txt('title'));
		$writer->addColumn($this->lng->txt('description'));			
		$writer->addColumn($this->lng->txt('ecs_field_courseID'));			
		$writer->addColumn($this->lng->txt('ecs_field_term'));			
		$writer->addColumn($this->lng->txt('ecs_field_lecturer'));			
		$writer->addColumn($this->lng->txt('ecs_field_courseType'));			
		$writer->addColumn($this->lng->txt('ecs_field_semester_hours'));			
		$writer->addColumn($this->lng->txt('ecs_field_credits'));			
		$writer->addColumn($this->lng->txt('ecs_field_room'));			
		$writer->addColumn($this->lng->txt('ecs_field_cycle'));			
		$writer->addColumn($this->lng->txt('ecs_field_begin'));			
		$writer->addColumn($this->lng->txt('ecs_field_end'));			
		$writer->addColumn($this->lng->txt('last_update'));	
		
		include_once('./Services/WebServices/ECS/classes/class.ilECSDataMappingSettings.php');
		$settings = ilECSDataMappingSettings::_getInstance();

		foreach($exported as $obj_id)
		{
			include_once "Services/WebServices/ECS/classes/class.ilECSUtils.php";
			$values = ilECSUtils::getAdvancedMDValuesForObjId($obj_id);
			
			$writer->addRow();
			$writer->addColumn(ilObject::_lookupTitle($obj_id));
			$writer->addColumn(ilObject::_lookupDescription($obj_id));
			
			$field = $settings->getMappingByECSName('courseID');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName('term');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName('lecturer');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName('courseType');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName('semester_hours');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName('credits');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName('room');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName('cycle');
			$writer->addColumn(isset($values[$field]) ? $values[$field] : '');
			
			$field = $settings->getMappingByECSName('begin');
			$writer->addColumn(isset($values[$field]) ?  ilFormat::formatUnixTime($values[$field],true) : '');
			
			$field = $settings->getMappingByECSName('end');
			$writer->addColumn(isset($values[$field]) ?  ilFormat::formatUnixTime($values[$field],true) : '');
			
			$writer->addColumn($ilObjDataCache->lookupLastUpdate($obj_id));
		}

		ilUtil::deliverData($writer->getCSVString(), date("Y_m_d")."_ecs_export.csv", "text/csv");	
	}
	
	
	/**
	 * get options for field selection
	 * @param array array of field objects
	 * @access protected
	 */
	protected function prepareFieldSelection($fields)
	{
		include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
		
		$options[0] = $this->lng->txt('ecs_ignore_field');
		foreach($fields as $field)
		{			
			$title = ilAdvancedMDRecord::_lookupTitle($field->getRecordId());
			$options[$field->getFieldId()] = $title.': '.$field->getTitle();		
		}
		return $options;
	}
	


	/**
	 * Init settings
	 *
	 * @access protected
	 */
	protected function initSettings($a_server_id = 1)
	{	
		include_once('Services/WebServices/ECS/classes/class.ilECSSetting.php');
		$this->settings = ilECSSetting::getInstanceByServerId($a_server_id);
	}
	
	/**
	 * set sub tabs
	 *
	 * @access protected
	 */
	protected function setSubTabs()
	{
		$this->tabs_gui->clearSubTabs();
		
		$this->tabs_gui->addSubTabTarget("overview",
			$this->ctrl->getLinkTarget($this,'overview'),
			"overview",get_class($this));
		
		// Disable all other tabs, if server hasn't been configured.
		#ilECSServerSettings::getInstance()->readInactiveServers();
		if(!ilECSServerSettings::getInstance()->serverExists())
		{
			return true;
		}

		$this->tabs_gui->addSubTabTarget("ecs_communities",
			$this->ctrl->getLinkTarget($this,'communities'),
			"communities",get_class($this));
			
		$this->tabs_gui->addSubTabTarget('ecs_mappings',
			$this->ctrl->getLinkTarget($this,'importMappings'),
			'importMappings',get_class($this));
			
		$this->tabs_gui->addSubTabTarget('ecs_category_mapping',
			$this->ctrl->getLinkTarget($this,'categoryMapping'));
			
		$this->tabs_gui->addSubTabTarget('ecs_import',
			$this->ctrl->getLinkTarget($this,'imported'));

		$this->tabs_gui->addSubTabTarget('ecs_released',
			$this->ctrl->getLinkTarget($this,'released'));

	}
	
	/**
	 * get global role array
	 *
	 * @access protected
	 */
	private function prepareRoleSelect()
	{
		global $rbacreview,$ilObjDataCache;
		
		$global_roles = ilUtil::_sortIds($rbacreview->getGlobalRoles(),
			'object_data',
			'title',
			'obj_id');
		
		$select[0] = $this->lng->txt('links_select_one');
		foreach($global_roles as $role_id)
		{
			$select[$role_id] = ilObject::_lookupTitle($role_id);
		}
		return $select;
	}
	
	private function buildPath($a_ref_id)
	{
		$loc = new ilLocatorGUI();
		$loc->setTextOnly(false);
		$loc->addContextItems($a_ref_id);
		
		return $loc->getHTML();
	}

	/**
	 * Init next task execution
	 * @global <type> $ilDB
	 * @global <type> $ilSetting
	 */
	protected function initTaskScheduler()
	{
		global $ilDB,$ilSetting;

		#$ilDB->lockTables(array('name' => 'settings', 'type' => ilDB::LOCK_WRITE));
		$setting = new ilSetting('ecs');
		$setting->set(
			'next_execution_'.$this->settings->getServerId(),
			time() + (int) $this->settings->getPollingTime()
		);
	}
	
}

?>