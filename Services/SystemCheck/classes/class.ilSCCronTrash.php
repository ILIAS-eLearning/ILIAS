<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Purge trash by cron
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCCronTrash extends ilCronJob
{
	/**
	 * Get id
	 * @return string
	 */
	public function getId()
	{
		return 'sysc_trash';
	}
	
	public function getTitle()
	{
		global $lng;
			
		return $lng->txt('sysc_cron_empty_trash');
	}
	
	public function getDescription()
	{
		global $lng;
			
		return $lng->txt('sysc_cron_emtpy_trash_desc');
	}
	
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_WEEKLY;
	}
	
	/**
	 * Get all available schedule types
	 * 
	 * @return int
	 */
	public function getValidScheduleTypes()
	{
		return array(
			self::SCHEDULE_TYPE_DAILY,
			self::SCHEDULE_TYPE_WEEKLY, 
			self::SCHEDULE_TYPE_MONTHLY,
			self::SCHEDULE_TYPE_QUARTERLY, 
			self::SCHEDULE_TYPE_YEARLY
		);
	}		
	
	
	public function getDefaultScheduleValue()
	{
		return 1;
	}
	
	public function hasAutoActivation()
	{
		return false;
	}
	
	public function hasFlexibleSchedule()
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public function hasCustomSettings() 
	{
		return TRUE;
	}
	
	
	/**
	 * Add custom settings to form
	 * 
	 * @param ilPropertyFormGUI $a_form
	 */
    public function addCustomSettingsToForm(ilPropertyFormGUI $form)
	{
		global $lng;
		
		$lng->loadLanguageModule('sysc');
		
		include_once './Services/Administration/classes/class.ilSetting.php';
		$settings = new ilSetting('sysc');

		// limit number 
		$num = new ilNumberInputGUI($lng->txt('sysc_trash_limit_num'), 'number');
		$num->setInfo($lng->txt('purge_count_limit_desc'));
		$num->setSize(10);
		$num->setMinValue(1);
		$num->setValue($settings->get('num',''));
		$form->addItem($num);
		
		$age = new ilDateTimeInputGUI($lng->txt('sysc_trash_limit_age'), 'age');
		$age->setInfo($lng->txt('purge_age_limit_desc'));
		$age->setMinuteStepSize(15);
		$age->setMode(ilDateTimeInputGUI::MODE_INPUT);
		
		if($settings->get('age',''))
		{
			$dt = new ilDateTime($settings->get('age',''),IL_CAL_DATETIME,'UTC');
			$age->setDate($dt);
		}
		
		
		$form->addItem($age);
		
		// limit types
		$types = new ilSelectInputGUI($lng->txt('sysc_trash_limit_type'), 'types');
		$sub_objects = $GLOBALS['tree']->lookupTrashedObjectTypes();
		
		$options = array();
		$options[0] = '';
		foreach($sub_objects as $obj_type)
		{
			if(!$GLOBALS['objDefinition']->isRBACObject($obj_type) or !$GLOBALS['objDefinition']->isAllowedInRepository($obj_type))
			{
				continue;
			}
			$options[$obj_type] = $lng->txt('obj_'.$obj_type);
		}
		asort($options);
		$types->setOptions($options);
		$types->setValue($settings->get('types', ''));
		$form->addItem($types);
		
		return $form;
	}
	
	/**
	 * Save custom settings
	 * 
	 * @param ilPropertyFormGUI $a_form
	 * @return boolean
	 */
	public function saveCustomSettings(ilPropertyFormGUI $a_form)
	{
		include_once './Services/Administration/classes/class.ilSetting.php';
		$settings = new ilSetting('sysc');
		
		
		$settings->set('num', $a_form->getInput('number'));
		
		if($a_form->getItemByPostVar('age')->getDate() instanceof ilDateTime)
		{
			$settings->set('age', $a_form->getItemByPostVar('age')->getDate()->get(IL_CAL_DATETIME, '', UTC));
		}
		else
		{
			$settings->set('age', '');
		}
		$settings->set('types',$a_form->getInput('types'));
	}

	/**
	 * Add external settings to form
	 * 
	 * @param int $a_form_id
	 * @param array $a_fields
	 * @param bool $a_is_active
	 */
	public function addToExternalSettingsForm($a_form_id, array &$a_fields, $a_is_active)
	{
		include_once './Services/Administration/classes/class.ilSetting.php';
		$settings = new ilSetting('sysc');

		$GLOBALS['ilLog']->write(print_r($a_fields));
		
		$a_fields[$a_form_id] = $settings->get('number',10);
		
	}

	

	/**
	 * 
	 */
	public function run()
	{
		
	}
}
?>