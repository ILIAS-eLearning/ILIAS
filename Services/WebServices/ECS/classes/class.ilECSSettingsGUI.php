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

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilECSSettingsGUI:
* @ingroup ServicesWebServicesECS
*/
class ilECSSettingsGUI
{
	protected $tpl;
	protected $lng;
	protected $ctrl;
	

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct()
	{
		global $lng,$tpl,$ilCtrl;
		
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		
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

		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "settings";
				}
				$this->$cmd();
				break;
		}
		return true;
	}
	
	/**
	 * show settings 
	 *
	 * @access protected
	 */
	protected function settings()
	{
		$this->initSettingsForm();
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.ecs_settings.html','Services/WebServices/ECS');
		$this->tpl->setVariable('SETTINGS_TABLE',$this->form->getHTML());
		
		include_once('Services/WebServices/ECS/classes/class.ilECSConnector.php');
		include_once('Services/WebServices/ECS/classes/class.ilECSConnectorException.php');
		
		try
		{
			$connector = new ilECSConnector();
			$res = $connector->getResources();
			var_dump("<pre>",$res->getResult(),"</pre>");
		}
		catch(ilECSConnectorException $exc)
		{
			var_dump("<pre>",$exc->getMessage(),"</pre>");
		}		
		
	}
	
	/**
	 * init settings form
	 *
	 * @access protected
	 */
	protected function initSettingsForm()
	{
		if(is_object($this->form))
		{
			return true;
		}
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'settings'));
		$this->form->setTitle($this->lng->txt('ecs_settings'));
		
		$ena = new ilCheckboxInputGUI($this->lng->txt('ecs_active'),'active');
		$ena->setChecked($this->settings->isEnabled());
		$ena->setValue(1);
		$this->form->addItem($ena);
		
		$ser = new ilTextInputGUI($this->lng->txt('ecs_server'),'server');
		$ser->setValue($this->settings->getServer());
		$ser->setRequired(true);
		$this->form->addItem($ser);
		
		$pro = new ilSelectInputGUI($this->lng->txt('ecs_protocol'),'protocol');
		// fixed to https
		#$pro->setOptions(array(ilECSSettings::PROTOCOL_HTTP => $this->lng->txt('http'),
		#		ilECSSettings::PROTOCOL_HTTPS => $this->lng->txt('https')));
		$pro->setOptions(array(ilECSSettings::PROTOCOL_HTTPS => 'HTTPS'));
		$pro->setValue($this->settings->getProtocol());
		$pro->setRequired(true);
		$this->form->addItem($pro);
		
		$por = new ilTextInputGUI($this->lng->txt('ecs_port'),'port');
		$por->setSize(5);
		$por->setMaxLength(5);
		$por->setValue($this->settings->getPort());
		$por->setRequired(true);
		$this->form->addItem($por);
		
		$cer = new ilTextInputGUI($this->lng->txt('ecs_client_cert'),'client_cert');
		$cer->setValue($this->settings->getClientCertPath());
		$cer->setRequired(true);
		$this->form->addItem($cer);
		
		$cer = new ilTextInputGUI($this->lng->txt('ecs_ca_cert'),'ca_cert');
		$cer->setValue($this->settings->getCACertPath());
		$cer->setRequired(true);
		$this->form->addItem($cer);

		$cer = new ilTextInputGUI($this->lng->txt('ecs_cert_key'),'key_path');
		$cer->setValue($this->settings->getKeyPath());
		$cer->setRequired(true);
		$this->form->addItem($cer);
		
		$cer = new ilTextInputGUI($this->lng->txt('ecs_key_password'),'key_password');
		$cer->setValue($this->settings->getKeyPassword());
		$cer->setInputType('password');
		$cer->setRequired(true);
		$this->form->addItem($cer);

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
		$this->form->addItem($pol);
		
		$imp = new ilTextInputGUI($this->lng->txt('import_id'),'import_id');
		$imp->setSize(5);
		$imp->setMaxLength(6);
		$imp->setValue($this->settings->getImportId() ? $this->settings->getImportId() : '');
		$this->form->addItem($imp);
		
		$this->form->addCommandButton('saveSettings',$this->lng->txt('save'));
		$this->form->addCommandButton('settings',$this->lng->txt('cancel'));
	}
	
	/**
	 * save settings
	 *
	 * @access protected
	 */
	protected function saveSettings()
	{
		$this->settings->setEnabledStatus((int) $_POST['active']);
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
		
		if($this->settings->validate())
		{
			$this->settings->save();
			ilUtil::sendInfo($this->lng->txt('settings_saved'));
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('fill_out_all_required_fields'));
		}
		$this->settings();
		return true;
	}


	/**
	 * Init settings
	 *
	 * @access protected
	 */
	protected function initSettings()
	{	
		include_once('Services/WebServices/ECS/classes/class.ilECSSettings.php');
		$this->settings = ilECSSettings::_getInstance();
	}
}

?>