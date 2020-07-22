<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilPermissionGUI
* RBAC related output
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
* @version $Id: class.ilPermissionGUI.php 20310 2009-06-23 12:57:19Z smeyer $
*
*
* @ingroup	ServicesAccessControl
*/
class ilPermission2GUI
{
    protected $gui_obj = null;
    protected $ilErr = null;
    protected $ctrl = null;
    protected $lng = null;
    const TAB_POSITION_PERMISSION_SETTINGS = "position_permission_settings";


    public function __construct($a_gui_obj)
    {
        global $DIC;

        $ilias = $DIC['ilias'];
        $objDefinition = $DIC['objDefinition'];
        $tpl = $DIC['tpl'];
        $tree = $DIC['tree'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        
        if (!isset($ilErr)) {
            $ilErr = new ilErrorHandling();
            $ilErr->setErrorHandling(PEAR_ERROR_CALLBACK, array($ilErr,'errorHandler'));
        } else {
            $this->ilErr = &$ilErr;
        }

        $this->objDefinition = &$objDefinition;
        $this->tpl = &$tpl;
        $this->lng = &$lng;
        $this->lng->loadLanguageModule("rbac");

        $this->ctrl = &$ilCtrl;

        $this->gui_obj = $a_gui_obj;
        
        $this->roles = array();
        $this->num_roles = 0;
    }
    


    

    // show owner sub tab
    public function owner()
    {
        $this->__initSubTabs("owner");
        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "owner"));
        $form->setTitle($this->lng->txt("info_owner_of_object"));
        
        $login = new ilTextInputGUI($this->lng->txt("login"), "owner");
        $login->setDataSource($this->ctrl->getLinkTargetByClass(array(get_class($this),
            'ilRepositorySearchGUI'), 'doUserAutoComplete', '', true));
        $login->setRequired(true);
        $login->setSize(50);
        $login->setInfo($this->lng->txt("chown_warning"));
        $login->setValue(ilObjUser::_lookupLogin($this->gui_obj->object->getOwner()));
        $form->addItem($login);
        
        $form->addCommandButton("changeOwner", $this->lng->txt("change_owner"));
        
        $this->tpl->setContent($form->getHTML());
    }
    
    public function changeOwner()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilObjDataCache = $DIC['ilObjDataCache'];

        if (!$user_id = ilObjUser::_lookupId($_POST['owner'])) {
            ilUtil::sendFailure($this->lng->txt('user_not_known'));
            $this->owner();
            return true;
        }
        
        // no need to change?
        if ($user_id != $this->gui_obj->object->getOwner()) {
            $this->gui_obj->object->setOwner($user_id);
            $this->gui_obj->object->updateOwner();
            $ilObjDataCache->deleteCachedEntry($this->gui_obj->object->getId());

            include_once "Services/AccessControl/classes/class.ilRbacLog.php";
            if (ilRbacLog::isActive()) {
                ilRbacLog::add(ilRbacLog::CHANGE_OWNER, $this->gui_obj->object->getRefId(), array($user_id));
            }
        }
        
        ilUtil::sendSuccess($this->lng->txt('owner_updated'), true);

        if (!$rbacsystem->checkAccess("edit_permission", $this->gui_obj->object->getRefId())) {
            $this->ctrl->redirect($this->gui_obj);
            return true;
        }

        $this->ctrl->redirect($this, 'owner');
        return true;
    }
    
    // init sub tabs
    public function __initSubTabs($a_cmd)
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];

        $perm = ($a_cmd == 'perm') ? true : false;
        $perm_positions = ($a_cmd == ilPermissionGUI::CMD_PERM_POSITIONS) ? true : false;
        $info = ($a_cmd == 'perminfo') ? true : false;
        $owner = ($a_cmd == 'owner') ? true : false;
        $log = ($a_cmd == 'log') ? true : false;

        $ilTabs->addSubTabTarget(
            "permission_settings",
            $this->ctrl->getLinkTarget($this, "perm"),
            "",
            "",
            "",
            $perm
        );

        if (ilOrgUnitGlobalSettings::getInstance()->isPositionAccessActiveForObject($this->gui_obj->object->getId())) {
            $ilTabs->addSubTabTarget(self::TAB_POSITION_PERMISSION_SETTINGS, $this->ctrl->getLinkTarget($this, ilPermissionGUI::CMD_PERM_POSITIONS), "", "", "", $perm_positions);
        }
                                 
        $ilTabs->addSubTabTarget(
            "info_status_info",
            $this->ctrl->getLinkTargetByClass(array(get_class($this),"ilobjectpermissionstatusgui"), "perminfo"),
            "",
            "",
            "",
            $info
        );
        $ilTabs->addSubTabTarget(
            "owner",
            $this->ctrl->getLinkTarget($this, "owner"),
            "",
            "",
            "",
            $owner
        );

        include_once "Services/AccessControl/classes/class.ilRbacLog.php";
        if (ilRbacLog::isActive()) {
            $ilTabs->addSubTabTarget(
                "log",
                $this->ctrl->getLinkTarget($this, "log"),
                "",
                "",
                "",
                $log
            );
        }
    }
    
    public function log()
    {
        include_once "Services/AccessControl/classes/class.ilRbacLog.php";
        if (!ilRbacLog::isActive()) {
            $this->ctrl->redirect($this, "perm");
        }

        $this->__initSubTabs("log");

        include_once "Services/AccessControl/classes/class.ilRbacLogTableGUI.php";
        $table = new ilRbacLogTableGUI($this, "log", $this->gui_obj->object->getRefId());
        $this->tpl->setContent($table->getHTML());
    }

    public function applyLogFilter()
    {
        include_once "Services/AccessControl/classes/class.ilRbacLogTableGUI.php";
        $table = new ilRbacLogTableGUI($this, "log", $this->gui_obj->object->getRefId());
        $table->resetOffset();
        $table->writeFilterToSession();
        $this->log();
    }

    public function resetLogFilter()
    {
        include_once "Services/AccessControl/classes/class.ilRbacLogTableGUI.php";
        $table = new ilRbacLogTableGUI($this, "log", $this->gui_obj->object->getRefId());
        $table->resetOffset();
        $table->resetFilter();
        $this->log();
    }
}
