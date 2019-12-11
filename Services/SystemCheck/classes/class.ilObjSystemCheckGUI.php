<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Object/classes/class.ilObjectGUI.php';
include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
include_once './Services/SystemCheck/classes/class.ilSystemCheckTrash.php';


/**
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version           $Id$
 * @ilCtrl_Calls      ilObjSystemCheckGUI: ilPermissionGUI, ilObjectOwnershipManagementGUI, ilObjSystemFolderGUI, ilSCComponentTasksGUI
 * @ilCtrl_isCalledBy ilObjSystemCheckGUI: ilAdministrationGUI
 */
class ilObjSystemCheckGUI extends ilObjectGUI
{
    const SECTION_MAIN = 'main';
    const SECTION_GROUP = 'group';
    

    /**
     * @var ilCtrl
     */
    public $ctrl;

    /**
     * @param      $a_data
     * @param      $a_id
     * @param      $a_call_by_reference
     * @param bool $a_prepare_output
     */
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        $this->type = 'sysc';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule('sysc');
    }

    /**
     * Get language obj
     * @return ilLanguage
     */
    public function getLang()
    {
        return $this->lng;
    }
    
    /**
     * ilCtrl execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd        = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case "ilobjectownershipmanagementgui":
                $this->setSubTabs(self::SECTION_MAIN, 'no_owner');
                include_once 'Services/Object/classes/class.ilObjectOwnershipManagementGUI.php';
                $gui = new ilObjectOwnershipManagementGUI(0);
                $this->ctrl->forwardCommand($gui);
                break;
            
            case 'ilobjsystemfoldergui':
                include_once './Modules/SystemFolder/classes/class.ilObjSystemFolderGUI.php';
                $sys_folder = new ilObjSystemFolderGUI('', SYSTEM_FOLDER_ID, true);
                $this->ctrl->forwardCommand($sys_folder);
                
                $GLOBALS['DIC']['ilTabs']->clearTargets();
                
                $this->setSubTabs(self::SECTION_MAIN, 'sc');
                break;
            
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case '':
            case 'ilobjsystemcheckgui':
                if ($cmd == '' || $cmd == 'view') {
                    $cmd = 'overview';
                }
                $this->$cmd();
                break;
                
            default:
                // Forward to task handler
                include_once './Services/SystemCheck/classes/class.ilSCComponentTaskFactory.php';
                $this->ctrl->saveParameter($this, 'grp_id');
                $this->ctrl->saveParameter($this, 'task_id');
                $this->ctrl->setReturn($this, 'showGroup');
                $GLOBALS['DIC']['ilTabs']->clearTargets();
                $GLOBALS['DIC']['ilTabs']->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'showGroup'));
                $handler = ilSCComponentTaskFactory::getComponentTask((int) $_REQUEST['task_id']);
                $this->ctrl->forwardCommand($handler);
                break;
                
                
        }
    }

    /**
     * Get administration tabs
     * @param ilTabsGUI $tabs_gui
     */
    public function getAdminTabs()
    {
        /**
         * @var $rbacsystem ilRbacSystem
         */
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if ($rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('overview', $this->ctrl->getLinkTarget($this, 'overview'));
        }
        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilpermissiongui'), 'perm'), array('perm', 'info', 'owner'), 'ilpermissiongui');
        }
    }
    
    /**
     * Show overview table
     */
    protected function overview()
    {
        $this->getLang()->loadLanguageModule('sysc');
        
        
        $this->setSubTabs(self::SECTION_MAIN, 'overview');
        
        
        include_once 'Services/SystemCheck/classes/class.ilSCGroupTableGUI.php';
        
        $table = new ilSCGroupTableGUI($this, 'overview');
        $table->init();
        $table->parse();
        
        $GLOBALS['DIC']['tpl']->setContent($table->getHTML());
        return true;
    }
    
    /**
     * Show group tasks
     */
    protected function showGroup()
    {
        $this->setSubTabs(self::SECTION_GROUP, '');
        
        $grp_id = (int) $_REQUEST['grp_id'];
        $this->ctrl->saveParameter($this, 'grp_id');
        
        include_once 'Services/SystemCheck/classes/class.ilSCTaskTableGUI.php';
        $table = new ilSCTaskTableGUI($grp_id, $this, 'showGroup');
        $table->init();
        $table->parse();
        
        $GLOBALS['DIC']['tpl']->setContent($table->getHTML());
        return true;
    }
    
    
    

    /**
     * Show trash form
     * @param ilPropertyFormGUI $form
     */
    protected function trash(ilPropertyFormGUI $form = null)
    {
        $this->setSubTabs(self::SECTION_MAIN, 'trash');
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormTrash();
        }
        $GLOBALS['DIC']['tpl']->setContent($form->getHTML());
    }
    
    /**
     * Show trash restore form
     * @return ilPropertyFormGUI
     */
    protected function initFormTrash()
    {
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        
        $form->setTitle($this->lng->txt('sysc_administrate_deleted'));
        
        $action = new ilRadioGroupInputGUI($this->lng->txt('sysc_trash_action'), 'type');
        $action->setRequired(true);
        
        // Restore
        $restore = new ilRadioOption($this->lng->txt('sysc_trash_restore'), ilSystemCheckTrash::MODE_TRASH_RESTORE);
        $restore->setInfo($this->lng->txt('sysc_trash_restore_info'));
        $action->addOption($restore);
        
        // Remove
        $remove = new ilRadioOption($this->lng->txt('sysc_trash_remove'), ilSystemCheckTrash::MODE_TRASH_REMOVE);
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
        #$earlier = new ilDateTime(time(),IL_CAL_UNIX);
        #$earlier->increment(IL_CAL_MONTH,-6);
        #$age->setDate($earlier);
        $remove->addSubItem($age);
        
        // limit types
        $types = new ilSelectInputGUI($this->lng->txt('sysc_trash_limit_type'), 'types');
        /*
         * @var ilObjDefinition
         */
        $sub_objects = $GLOBALS['DIC']['tree']->lookupTrashedObjectTypes();
        
        $options = array();
        $options[0] = '';
        foreach ($sub_objects as $obj_type) {
            if (!$GLOBALS['DIC']['objDefinition']->isRBACObject($obj_type) or !$GLOBALS['DIC']['objDefinition']->isAllowedInRepository($obj_type)) {
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
    
    /**
     * Handle Trash action
     */
    protected function handleTrashAction()
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
            
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $form->setValuesByPost();
            $this->trash($form);
            return true;
        }
        
        ilUtil::sendFailure($this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->trash($form);
        return false;
    }

        
    /**
     * Set subtabs
     * @param type $a_section
     */
    protected function setSubTabs($a_section, $a_active)
    {
        switch ($a_section) {
            case self::SECTION_MAIN:
                $GLOBALS['DIC']['ilTabs']->addSubTab(
                    '',
                    $this->getLang()->txt('sysc_groups'),
                    $this->ctrl->getLinkTarget($this, 'overview')
                );
                $GLOBALS['DIC']['ilTabs']->addSubTab(
                    'trash',
                    $this->getLang()->txt('sysc_tab_trash'),
                    $this->ctrl->getLinkTarget($this, 'trash')
                );
                $GLOBALS['DIC']['ilTabs']->addSubTab(
                    'no_owner',
                    $this->getLang()->txt('system_check_no_owner'),
                    $this->ctrl->getLinkTargetByClass('ilobjectownershipmanagementgui')
                );
                break;
            
            case self::SECTION_GROUP:
                $GLOBALS['DIC']['ilTabs']->clearTargets();
                $GLOBALS['DIC']['ilTabs']->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTarget($this, 'overview')
                );
        }
        $GLOBALS['DIC']['ilTabs']->activateSubTab($a_active);
    }
}
