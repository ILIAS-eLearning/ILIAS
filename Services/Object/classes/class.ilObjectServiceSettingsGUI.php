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
	const CALENDAR_VISIBILITY = 'cont_show_calendar';
	const NEWS_VISIBILITY = 'cont_show_news';
	
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
	 * Init service settings form
	 * @param ilPropertyFormGUI $form
	 * @param type $services
	 */
	public static function initServiceSettingsForm($a_obj_id, ilPropertyFormGUI $form, $services)
	{
		global $ilSetting;
		
		if(in_array(self::CALENDAR_VISIBILITY, $services))
		{
			include_once './Services/Calendar/classes/class.ilObjCalendarSettings.php';
			if(ilCalendarSettings::_getInstance()->isEnabled())
			{
				// Container tools (calendar, news, ... activation)
				$cal = new ilCheckboxInputGUI('', self::CALENDAR_VISIBILITY);
				$cal->setValue(1);
				include_once './Services/Calendar/classes/class.ilObjCalendarSettings.php';
				$cal->setChecked(ilCalendarSettings::lookupCalendarActivated($a_obj_id));
				$cal->setOptionTitle($GLOBALS['lng']->txt('obj_tool_setting_calendar'));
				$form->addItem($cal);
			}
		}
		if(in_array(self::NEWS_VISIBILITY, $services))
		{
			if($ilSetting->get('block_activated_news'))
			{
				// Container tools (calendar, news, ... activation)
				$news = new ilCheckboxInputGUI('', self::NEWS_VISIBILITY);
				$news->setValue(1);
				$news->setChecked(ilContainer::_lookupContainerSetting(
						$a_obj_id,
						self::NEWS_VISIBILITY,
						$ilSetting->get('block_activated_news',true)
				));
				$news->setOptionTitle($GLOBALS['lng']->txt('obj_tool_setting_news'));
				$form->addItem($news);
			}
		}
		
		return $form;
	}
	
	/**
	 * Update service settings
	 * @param type $a_obj_id
	 * @param ilPropertyFormGUI $form
	 * @param type $services
	 */
	public static function updateServiceSettingsForm($a_obj_id, ilPropertyFormGUI $form, $services)
	{
		if(in_array(self::CALENDAR_VISIBILITY, $services))
		{
			include_once './Services/Calendar/classes/class.ilCalendarSettings.php';
			if(ilCalendarSettings::_getInstance()->isEnabled())
			{
				include_once './Services/Container/classes/class.ilContainer.php';
				ilContainer::_writeContainerSetting($a_obj_id,self::CALENDAR_VISIBILITY,(int) $form->getInput(self::CALENDAR_VISIBILITY));
			}
		}
		if(in_array(self::NEWS_VISIBILITY, $services))
		{
			include_once './Services/Container/classes/class.ilContainer.php';
			ilContainer::_writeContainerSetting($a_obj_id,self::NEWS_VISIBILITY,(int) $form->getInput(self::NEWS_VISIBILITY));
		}
		
		return true;
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
	 * Update settings
	 */
	protected function updateToolSettings()
	{
		$form = $this->initSettingsForm();
		if($form->checkInput())
		{
			include_once './Services/Calendar/classes/class.ilCalendarSettings.php';
			if(ilCalendarSettings::_getInstance()->isEnabled())
			{
				if($this->isModeActive(self::CALENDAR_VISIBILITY))
				{
					ilContainer::_writeContainerSetting($this->getObjId(),'show_calendar',(int) $form->getInput('calendar'));
				}
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
