<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';

/** 
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS
*/
class ilECSParticipantSettingsGUI
{
	private $server_id = 0;
	private $mid = 0;
	
	private $participant = null;
	
	protected $tpl;
	protected $lng;
	protected $ctrl;
	protected $tabs;
	

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct($a_server_id, $a_mid)
	{
		global $lng,$tpl,$ilCtrl,$ilTabs;
		
		$this->server_id = $a_server_id;
		$this->mid = $a_mid;
		
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('ecs');
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;

		$this->initSettings();
		$this->initParticipant();
	}
	
	/**
	 * 
	 * @return int
	 */
	public function getServerId()
	{
		return $this->server_id;
	}
	
	/**
	 * 
	 * @return int
	 */
	public function getMid()
	{
		return $this->mid;
	}
	
	/**
	 * 
	 * @return ilTemplate
	 */
	public function getTemplate()
	{
		return $this->tpl;
	}
	
	/**
	 * 
	 * @return ilCtrl
	 */
	public function getCtrl()
	{
		return $this->ctrl;
	}
	
	/**
	 * return ilLanguage
	 */
	public function getLang()
	{
		return $this->lng;
	}
	
	/**
	 * 
	 * @return ilECSParticipantSetting
	 */
	public function getParticipant()
	{
		return $this->participant;
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
		$this->getCtrl()->saveParameter($this, 'server_id');
		$this->getCtrl()->saveParameter($this, 'mid');
		
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd('settings');

		$this->setTabs();
		switch($next_class)
		{
			default:
				$this->$cmd();
				break;
		}
		
		
		return true;
	}
	
	/**
	 * Abort editing
	 */
	protected function abort()
	{
		$this->getCtrl()->returnToParent($this);
	}


	/**
	 * Settings
	 * @param ilPropertyFormGUI $form
	 */
	protected function settings(ilPropertyFormGUI $form = null)
	{
		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initFormSettings();
		}
		$this->getTemplate()->setContent($form->getHTML());
	}
	
	/**
	 * Save settings
	 */
	protected function saveSettings()
	{
		$form = $this->initFormSettings();
		if($form->checkInput())
		{
			$this->getParticipant()->enableToken($form->getInput('token'));
			$this->getParticipant()->update();
			
			ilUtil::sendSuccess($this->getLang()->txt('settings_saved'),TRUE);
			$this->getCtrl()->redirect($this,'settings');
			return TRUE;
		}
		$form->setValuesByPost();
		ilUtil::sendFailure($this->getLang()->txt('err_check_input'));
		$this->settings($form);
	}
	
	/**
	 * Init settings form
	 */
	protected function initFormSettings()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->getCtrl()->getFormAction($this));
		$form->setTitle($this->getLang()->txt('ecs_part_settings').' '.$this->getParticipant()->getTitle());
		
		
		$token = new ilCheckboxInputGUI($this->getLang()->txt('ecs_token_mechanism'),'token');
		$token->setInfo($this->getLang()->txt('ecs_token_mechanism_info'));
		$token->setValue(1);
		$token->setChecked($this->getParticipant()->isTokenEnabled());
		$form->addItem($token);
		
		$form->addCommandButton('saveSettings', $this->getLang()->txt('save'));
		$form->addCommandButton('abort', $this->getLang()->txt('cancel'));
		return $form;
	}

	
	/**
	 * Set tabs
	 */
	protected function setTabs()
	{
		$this->tabs->clearTargets();
		$this->tabs->setBackTarget(
				$this->lng->txt('back'),
				$this->ctrl->getParentReturn($this)
		);
	}

	/**
	 * Init settings
	 *
	 * @access protected
	 */
	protected function initSettings()
	{	
		include_once('Services/WebServices/ECS/classes/class.ilECSSetting.php');
		$this->settings = ilECSSetting::getInstanceByServerId($this->getServerId());
	}
	
	/**
	 * init participant
	 */
	protected function initParticipant()
	{
		include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';
		$this->participant = new ilECSParticipantSetting($this->getServerId(),$this->getMid());
	}
}

?>