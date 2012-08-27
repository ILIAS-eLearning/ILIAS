<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for service settings (calendar, notes, comments)
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * 
 * @ilCtrl_Calls ilObjectServiceSettingsGUI:  
 * @ingroup ServicesObject
 */
class ilObjectServiceSettingsGUI 
{
	const CALENDAR_VISIBILITY = 1;
	
	private $gui = null;
	private $modes = array();
	private $obj_id = 0;
	
	/**
	 * Constructor
	 * @param type $a_parent_gui
	 */
	public function __construct($a_parent_gui, $a_obj_id, $a_modes)
	{
		$this->gui = $a_parent_gui;
		$this->modes = $a_modes;
		$this->obj_id = $a_obj_id;
	}
	
	/**
	 * Control class handling
	 * @return 
	 */
	public function executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd('editSettings');
		
		switch($next_class)
		{
			default:
				$this->$cmd();
				break;
		}
	}
	
	/**
	 * Check if minimum one setting is visible
	 * @param type $a_modes
	 * @return boolean
	 */
	public static function isVisible($a_modes = array())
	{
		$one_visible = false;
		
		if(in_array(self::CALENDAR_VISIBILITY, $a_modes))
		{
			include_once './Services/Calendar/classes/class.ilCalendarSettings.php';
			if(ilCalendarSettings::_getInstance()->isEnabled())
			{
				$one_visible = true;
			}
		}
		return $one_visible;
	}
	
	/**
	 * Get active modes
	 * @return bool
	 */
	public function getModes()
	{
		return $this->modes;
	}
	
	/**
	 * Get obj id
	 * @return type
	 */
	public function getObjId()
	{
		return $this->obj_id;
	}
	
	protected function cancel()
	{
		$GLOBALS['ilCtrl']->returnToParent($this);
	}
	
	/**
	 * Edit tool settings (calendar, news, comments, ...)
	 * @param ilPropertyFormGUI $form
	 */
	protected function editSettings(ilPropertyFormGUI $form = null)
	{
		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initSettingsForm();
		}
		$GLOBALS['tpl']->setContent($form->getHTML());
	}
	
	/**
	 * Init tool settings form
	 * @return ilPropertyFormGUI
	 */
	protected function initSettingsForm()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setShowTopButtons(false);
		$form->setFormAction($GLOBALS['ilCtrl']->getFormAction($this,'updateToolSettings'));
		$form->setTitle($GLOBALS['lng']->txt('obj_tool_settings_title'));
		$form->addCommandButton('updateToolSettings', $GLOBALS['lng']->txt('save'));
		$form->addCommandButton('cancel', $GLOBALS['lng']->txt('cancel'));
		
		if($this->isModeActive(self::CALENDAR_VISIBILITY))
		{
			$cal = new ilCheckboxInputGUI($GLOBALS['lng']->txt('obj_tool_setting_calendar'), 'calendar');
			$cal->setValue(1);
			include_once './Services/Calendar/classes/class.ilCalendarSettings.php';
			$cal->setChecked(ilCalendarSettings::lookupCalendarActivated($this->getObjId()));
			$form->addItem($cal);
		}
		
		return $form;
	}
	
	/**
	 * Update settings
	 */
	protected function updateToolSettings()
	{
		$form = $this->initSettingsForm();
		if($form->checkInput())
		{
			if($this->isModeActive(self::CALENDAR_VISIBILITY))
			{
				ilContainer::_writeContainerSetting($this->getObjId(),'show_calendar',(int) $form->getInput('calendar'));
			}
			
			ilUtil::sendSuccess($GLOBALS['lng']->txt('settings_saved'),true);
			$GLOBALS['ilCtrl']->redirect($this);
		}
		
		ilUtil::sendFailure($GLOBALS['lng']->txt('err_check_input'));
		$form->setValuesByPost();
		$this->editSettings($form);
	}
	
	/**
	 * Check if specific mode is active
	 * @param type $a_mode
	 * @return type
	 */
	protected function isModeActive($a_mode)
	{
		return in_array($a_mode, $this->getModes());
	}
	
}
?>
