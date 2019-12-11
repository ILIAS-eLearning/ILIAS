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
        global $DIC;

        $lng = $DIC['lng'];
            
        $GLOBALS['DIC']['lng']->loadLanguageModule('sysc');
        return $lng->txt('sysc_cron_empty_trash');
    }
    
    public function getDescription()
    {
        global $DIC;

        $lng = $DIC['lng'];
            
        $GLOBALS['DIC']['lng']->loadLanguageModule('sysc');
        return $lng->txt('sysc_cron_empty_trash_desc');
    }
    
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_WEEKLY;
    }
    
    /**
     * @inheritdoc
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
        return true;
    }
    
    
    /**
     * Add custom settings to form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function addCustomSettingsToForm(ilPropertyFormGUI $form)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule('sysc');
        
        include_once './Services/Administration/classes/class.ilSetting.php';
        $settings = new ilSetting('sysc');

        // limit number
        $num = new ilNumberInputGUI($lng->txt('sysc_trash_limit_num'), 'number');
        $num->setInfo($lng->txt('purge_count_limit_desc'));
        $num->setSize(10);
        $num->setMinValue(1);
        $num->setValue($settings->get('num', ''));
        $form->addItem($num);
        
        $age = new ilNumberInputGUI($lng->txt('sysc_trash_limit_age'), 'age');
        $age->setInfo($lng->txt('purge_age_limit_desc'));
        $age->setSize(4);
        $age->setMinValue(1);
        $age->setMaxLength(4);
        
        if ($settings->get('age', '')) {
            $age->setValue($settings->get('age', ''));
        }
        
        $form->addItem($age);
        
        // limit types
        $types = new ilSelectInputGUI($lng->txt('sysc_trash_limit_type'), 'types');
        $sub_objects = $GLOBALS['DIC']['tree']->lookupTrashedObjectTypes();
        
        $options = array();
        $options[0] = '';
        foreach ($sub_objects as $obj_type) {
            if (!$GLOBALS['DIC']['objDefinition']->isRBACObject($obj_type) or !$GLOBALS['DIC']['objDefinition']->isAllowedInRepository($obj_type)) {
                continue;
            }
            $options[$obj_type] = $lng->txt('obj_' . $obj_type);
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
        $settings->set('age', $a_form->getInput('age'));
        $settings->set('types', $a_form->getInput('types'));
        
        return true; // #18579
    }

    /**
     *
     */
    public function run()
    {
        include_once './Services/SystemCheck/classes/class.ilSystemCheckTrash.php';
        $trash = new ilSystemCheckTrash();
        $trash->setMode(ilSystemCheckTrash::MODE_TRASH_REMOVE);
            
        include_once './Services/Administration/classes/class.ilSetting.php';
        $settings = new ilSetting('sysc');
        
        $trash->setNumberLimit($settings->get('num', 0));
        $trash->setTypesLimit((array) $settings->get('types'));
        
        $age = $settings->get('age', 0);
        if ($age) {
            $date = new ilDateTime(time(), IL_CAL_UNIX);
            $date->increment(IL_CAL_DAY, (int) $age * -1);
            $trash->setAgeLimit($date);
        }
        $trash->start();

        include_once './Services/Cron/classes/class.ilCronJobResult.php';
        ;
        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }
}
