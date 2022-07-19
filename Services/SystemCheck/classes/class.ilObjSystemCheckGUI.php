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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * @author            Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_Calls      ilObjSystemCheckGUI: ilPermissionGUI, ilObjectOwnershipManagementGUI, ilObjSystemFolderGUI, ilSCComponentTasksGUI
 * @ilCtrl_isCalledBy ilObjSystemCheckGUI: ilAdministrationGUI
 */
class ilObjSystemCheckGUI extends ilObjectGUI
{
    protected const SECTION_MAIN = 'main';
    protected const SECTION_GROUP = 'group';

    protected GlobalHttpState $http;
    protected Factory $refinery;

    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        global $DIC;
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->type = 'sysc';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule('sysc');
    }

    protected function getGrpIdFromRequest() : int
    {
        if ($this->http->wrapper()->query()->has('grp_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'grp_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function getTaskIdFromRequest() : int
    {
        if ($this->http->wrapper()->query()->has('task_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'task_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    public function getLang() : ilLanguage
    {
        return $this->lng;
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilobjectownershipmanagementgui':
                $this->setSubTabs(self::SECTION_MAIN, 'no_owner');

                $gui = new ilObjectOwnershipManagementGUI(0);
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjsystemfoldergui':

                $sys_folder = new ilObjSystemFolderGUI('', SYSTEM_FOLDER_ID, true);
                $this->ctrl->forwardCommand($sys_folder);

                $this->tabs_gui->clearTargets();

                $this->setSubTabs(self::SECTION_MAIN, 'sc');
                break;

            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');

                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case '':
            case 'ilobjsystemcheckgui':
                if ($cmd === null || $cmd === '' || $cmd === 'view') {
                    $cmd = 'overview';
                }
                $this->$cmd();
                break;

            default:
                // Forward to task handler

                $this->ctrl->saveParameter($this, 'grp_id');
                $this->ctrl->saveParameter($this, 'task_id');
                $this->ctrl->setReturn($this, 'showGroup');
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'showGroup'));
                $handler = ilSCComponentTaskFactory::getComponentTask($this->getTaskIdFromRequest());
                $this->ctrl->forwardCommand($handler);
                break;

        }
    }

    public function getAdminTabs() : void
    {
        if ($this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('overview', $this->ctrl->getLinkTarget($this, 'overview'));
        }
        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilpermissiongui'), 'perm'), array('perm', 'info', 'owner'), 'ilpermissiongui');
        }
    }

    protected function overview() : bool
    {
        $this->getLang()->loadLanguageModule('sysc');

        $this->setSubTabs(self::SECTION_MAIN, 'overview');

        $table = new ilSCGroupTableGUI($this, 'overview');
        $table->init();
        $table->parse();

        $this->tpl->setContent($table->getHTML());
        return true;
    }

    protected function showGroup() : bool
    {
        $this->setSubTabs(self::SECTION_GROUP, '');

        $this->ctrl->saveParameter($this, 'grp_id');

        $table = new ilSCTaskTableGUI($this->getGrpIdFromRequest(), $this, 'showGroup');
        $table->init();
        $table->parse();

        $this->tpl->setContent($table->getHTML());
        return true;
    }

    protected function trash(ilPropertyFormGUI $form = null) : void
    {
        $this->setSubTabs(self::SECTION_MAIN, 'trash');
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormTrash();
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function initFormTrash() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $form->setTitle($this->lng->txt('sysc_administrate_deleted'));

        $action = new ilRadioGroupInputGUI($this->lng->txt('sysc_trash_action'), 'type');
        $action->setRequired(true);

        // Restore
        $restore = new ilRadioOption($this->lng->txt('sysc_trash_restore'), (string) ilSystemCheckTrash::MODE_TRASH_RESTORE);
        $restore->setInfo($this->lng->txt('sysc_trash_restore_info'));
        $action->addOption($restore);

        // Remove
        $remove = new ilRadioOption($this->lng->txt('sysc_trash_remove'), (string) ilSystemCheckTrash::MODE_TRASH_REMOVE);
        $remove->setInfo($this->lng->txt('sysc_trash_remove_info'));
        $action->addOption($remove);

        // limit number
        $num = new ilNumberInputGUI($this->lng->txt('sysc_trash_limit_num'), 'number');
        $num->setInfo($this->lng->txt('purge_count_limit_desc'));
        $num->setSize(10);
        $num->setMinValue(1);
        $remove->addSubItem($num);

        $age = new ilDateTimeInputGUI($this->lng->txt('sysc_trash_limit_age'), 'age');
        $age->setInfo($this->lng->txt('purge_age_limit_desc'));
        $age->setMinuteStepSize(15);
        $remove->addSubItem($age);

        // limit types
        $types = new ilSelectInputGUI($this->lng->txt('sysc_trash_limit_type'), 'types');

        $sub_objects = $this->tree->lookupTrashedObjectTypes();

        $options = array();
        $options[0] = '';
        foreach ($sub_objects as $obj_type) {
            if (!$this->obj_definition->isRBACObject($obj_type) || !$this->obj_definition->isAllowedInRepository($obj_type)) {
                continue;
            }
            $options[$obj_type] = $this->lng->txt('obj_' . $obj_type);
        }

        asort($options);

        $types->setOptions($options);
        $remove->addSubItem($types);

        $form->addItem($action);

        $form->addCommandButton('handleTrashAction', $this->lng->txt('start_scan'));
        $form->addCommandButton('', $this->lng->txt('cancel'));

        return $form;
    }

    protected function handleTrashAction() : bool
    {
        $form = $this->initFormTrash();
        if ($form->checkInput()) {
            $trash = new ilSystemCheckTrash();
            $trash->setMode(ilSystemCheckTrash::MODE_TRASH_REMOVE);
            $dt = $form->getItemByPostVar('age')->getDate();
            if ($dt) {
                $trash->setAgeLimit($dt);
            }
            $trash->setNumberLimit($form->getInput('number'));

            if ($form->getInput('types')) {
                $trash->setTypesLimit((array) $form->getInput('types'));
            }
            $trash->setMode($form->getInput('type'));
            $trash->start();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $form->setValuesByPost();
            $this->trash($form);
            return true;
        }

        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->trash($form);
        return false;
    }

    protected function setSubTabs(string $a_section, string $a_active) : void
    {
        switch ($a_section) {
            case self::SECTION_MAIN:
                $this->tabs_gui->addSubTab(
                    'overview',
                    $this->getLang()->txt('sysc_groups'),
                    $this->ctrl->getLinkTarget($this, 'overview')
                );
                $this->tabs_gui->addSubTab(
                    'trash',
                    $this->getLang()->txt('sysc_tab_trash'),
                    $this->ctrl->getLinkTarget($this, 'trash')
                );
                $this->tabs_gui->addSubTab(
                    'no_owner',
                    $this->getLang()->txt('system_check_no_owner'),
                    $this->ctrl->getLinkTargetByClass('ilobjectownershipmanagementgui')
                );
                break;

            case self::SECTION_GROUP:
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, 'overview')
                );
        }
        $this->tabs_gui->activateSubTab($a_active);
    }
}
