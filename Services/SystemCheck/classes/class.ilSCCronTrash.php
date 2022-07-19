<?php declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Purge trash by cron
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCCronTrash extends ilCronJob
{
    protected ilLanguage $lng;
    protected ilTree $tree;
    protected ilObjectDefinition $objDefinition;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $this->objDefinition = $DIC['objDefinition'];
        $this->lng->loadLanguageModule('sysc');
    }

    public function getId() : string
    {
        return 'sysc_trash';
    }

    public function getTitle() : string
    {
        return $this->lng->txt('sysc_cron_empty_trash');
    }

    public function getDescription() : string
    {
        return $this->lng->txt('sysc_cron_empty_trash_desc');
    }

    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_WEEKLY;
    }

    public function getValidScheduleTypes() : array
    {
        return array(
            self::SCHEDULE_TYPE_DAILY,
            self::SCHEDULE_TYPE_WEEKLY,
            self::SCHEDULE_TYPE_MONTHLY,
            self::SCHEDULE_TYPE_QUARTERLY,
            self::SCHEDULE_TYPE_YEARLY
        );
    }

    public function getDefaultScheduleValue() : ?int
    {
        return 1;
    }

    public function hasAutoActivation() : bool
    {
        return false;
    }

    public function hasFlexibleSchedule() : bool
    {
        return true;
    }

    public function hasCustomSettings() : bool
    {
        return true;
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form) : void
    {
        $this->lng->loadLanguageModule('sysc');

        $settings = new ilSetting('sysc');

        // limit number
        $num = new ilNumberInputGUI($this->lng->txt('sysc_trash_limit_num'), 'number');
        $num->setInfo($this->lng->txt('purge_count_limit_desc'));
        $num->setSize(10);
        $num->setMinValue(1);
        $num->setValue($settings->get('num', ''));
        $a_form->addItem($num);

        $age = new ilNumberInputGUI($this->lng->txt('sysc_trash_limit_age'), 'age');
        $age->setInfo($this->lng->txt('purge_age_limit_desc'));
        $age->setSize(4);
        $age->setMinValue(1);
        $age->setMaxLength(4);

        if ($settings->get('age', '')) {
            $age->setValue($settings->get('age', ''));
        }

        $a_form->addItem($age);

        // limit types
        $types = new ilSelectInputGUI($this->lng->txt('sysc_trash_limit_type'), 'types');
        $sub_objects = $this->tree->lookupTrashedObjectTypes();

        $options = array();
        $options[0] = '';
        foreach ($sub_objects as $obj_type) {
            if (!$this->objDefinition->isRBACObject($obj_type) || !$this->objDefinition->isAllowedInRepository($obj_type)) {
                continue;
            }
            $options[$obj_type] = $this->lng->txt('obj_' . $obj_type);
        }
        asort($options);
        $types->setOptions($options);
        $types->setValue($settings->get('types', ''));
        $a_form->addItem($types);
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form) : bool
    {
        $settings = new ilSetting('sysc');

        $settings->set('num', $a_form->getInput('number'));
        $settings->set('age', $a_form->getInput('age'));
        $settings->set('types', $a_form->getInput('types'));

        return true; // #18579
    }

    public function run() : ilCronJobResult
    {
        $trash = new ilSystemCheckTrash();
        $trash->setMode(ilSystemCheckTrash::MODE_TRASH_REMOVE);

        $settings = new ilSetting('sysc');

        $trash->setNumberLimit((int) $settings->get('num', '0'));
        $trash->setTypesLimit((array) $settings->get('types'));

        $age = (int) $settings->get('age', '0');
        if ($age) {
            $date = new ilDateTime(time(), IL_CAL_UNIX);
            $date->increment(IL_CAL_DAY, $age * -1);
            $trash->setAgeLimit($date);
        }
        $trash->start();

        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }
}
