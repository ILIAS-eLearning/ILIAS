<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserStartingPointGUI
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 * @ilCtrl_Calls ilUserStartingPointGUI:
 * @ingroup ServicesUser
 */

class ilUserStartingPointGUI
{
    protected $log;
    protected $lng;
    protected $tpl;
    protected $parent_ref_id;

    /**
     * Constructor
     * @access public
     */
    public function __construct($a_parent_ref_id)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->log = ilLoggerFactory::getLogger("user");
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->toolbar = $ilToolbar;
        $this->ctrl = $ilCtrl;
        $this->parent_ref_id = $a_parent_ref_id;
        $this->lng->loadLanguageModule("administration");
    }
    public function &executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $cmd = $ilCtrl->getCmd();
        if ($cmd == "roleStartingPointform" || !$cmd) {
            $cmd = "initRoleStartingPointForm";
        }

        $this->$cmd();

        return true;
    }

    /**
     * table form to set up starting points depends of user roles
     */
    public function startingPoints()
    {
        include_once "Services/User/classes/class.ilUserRoleStartingPointTableGUI.php";

        require_once "./Services/AccessControl/classes/class.ilStartingPoint.php";
        $roles_without_point = ilStartingPoint::getGlobalRolesWithoutStartingPoint();

        if (!empty($roles_without_point)) {
            $this->toolbar->addButton(
                $this->lng->txt('create_starting_point'),
                $this->ctrl->getLinkTarget($this, "roleStartingPointform")
            );
        } else {
            ilUtil::sendInfo($this->lng->txt("all_roles_has_starting_point"));
        }


        $tbl = new ilUserRoleStartingPointTableGUI($this);

        $this->tpl->setContent($tbl->getHTML());
    }

    public function initUserStartingPointForm(ilPropertyFormGUI $form = null)
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getUserStartingPointForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function initRoleStartingPointForm(ilPropertyFormGUI $form = null)
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getRoleStartingPointForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function getUserStartingPointForm()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        require_once "Services/User/classes/class.ilUserUtil.php";

        $form = new ilPropertyFormGUI();

        // starting point: personal
        $startp = new ilCheckboxInputGUI($this->lng->txt("user_chooses_starting_page"), "usr_start_pers");
        $startp->setInfo($this->lng->txt("adm_user_starting_point_personal_info"));
        $startp->setChecked(ilUserUtil::hasPersonalStartingPoint());

        $form->addItem($startp);

        $form->addCommandButton("saveUserStartingPoint", $this->lng->txt("save"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getRoleStartingPointForm()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];

        if (!$rbacsystem->checkAccess("write", $this->parent_ref_id)) {
            $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
        }

        require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        require_once "./Services/AccessControl/classes/class.ilObjRole.php";
        require_once "./Services/AccessControl/classes/class.ilStartingPoint.php";
        include_once "Services/User/classes/class.ilUserUtil.php";

        $form = new ilPropertyFormGUI();
        $ilCtrl->saveParameter($this, array("spid"));

        $spoint_id = $_REQUEST['spid'];

        //edit no default
        if ($spoint_id > 0 && $spoint_id != 'default') {
            $st_point = new ilStartingPoint($spoint_id);

            //starting point role based
            if ($st_point->getRuleType() == ilStartingPoint::ROLE_BASED && $_REQUEST['rolid']) {
                $rolid = (int) $_REQUEST['rolid'];
                if ($role = new ilObjRole($rolid)) {
                    $options[$rolid] = $role->getTitle();
                    $starting_point = $st_point->getStartingPoint();
                    $si_roles = new ilSelectInputGUI($this->lng->txt("editing_this_role"), 'role_disabled');
                    $si_roles->setOptions($options);
                    $si_roles->setDisabled(true);
                    $form->addItem($si_roles);

                    $hi = new ilHiddenInputGUI("role");
                    $hi->setValue($rolid);
                    $form->addItem($hi);

                    $hidde_sp_id = new ilHiddenInputGUI("start_point_id");
                    $hidde_sp_id->setValue($spoint_id);
                    $form->addItem($hidde_sp_id);
                }
            }
        }
        //create
        elseif (!$spoint_id || $spoint_id != 'default') {
            //starting point role based
            if (ilStartingPoint::ROLE_BASED) {
                $roles = ilStartingPoint::getGlobalRolesWithoutStartingPoint();

                foreach ($roles as $role) {
                    $options[$role['id']] = $role['title'];
                }
                $si_roles = new ilSelectInputGUI($this->lng->txt("roles_without_starting_point"), 'role');
                $si_roles->setOptions($options);
                $form->addItem($si_roles);
            }
        } else {
            $starting_point = ilUserUtil::getStartingPoint();
        }

        // starting point

        $si = new ilRadioGroupInputGUI($this->lng->txt("adm_user_starting_point"), "start_point");
        $si->setRequired(true);
        $si->setInfo($this->lng->txt("adm_user_starting_point_info"));
        $valid = array_keys(ilUserUtil::getPossibleStartingPoints());
        foreach (ilUserUtil::getPossibleStartingPoints(true) as $value => $caption) {
            $opt = new ilRadioOption($caption, $value);
            $si->addOption($opt);

            if (!in_array($value, $valid)) {
                $opt->setInfo($this->lng->txt("adm_user_starting_point_invalid_info"));
            }
        }
        $si->setValue($starting_point);
        $form->addItem($si);

        // starting point: repository object
        $repobj = new ilRadioOption($this->lng->txt("adm_user_starting_point_object"), ilUserUtil::START_REPOSITORY_OBJ);
        $repobj_id = new ilTextInputGUI($this->lng->txt("adm_user_starting_point_ref_id"), "start_object");
        $repobj_id->setRequired(true);
        $repobj_id->setSize(5);
        //$i has the starting_point value, so we are here only when edit one role or setting the default role.
        if ($si->getValue() == ilUserUtil::START_REPOSITORY_OBJ) {
            if ($st_point) {
                $start_ref_id = $st_point->getStartingObject();
            } else {
                $start_ref_id = ilUserUtil::getStartingObject();
            }

            $repobj_id->setValue($start_ref_id);
            if ($start_ref_id) {
                $start_obj_id = ilObject::_lookupObjId($start_ref_id);
                if ($start_obj_id) {
                    $repobj_id->setInfo($this->lng->txt("obj_" . ilObject::_lookupType($start_obj_id)) .
                        ": " . ilObject::_lookupTitle($start_obj_id));
                }
            }
        }
        $repobj->addSubItem($repobj_id);
        $si->addOption($repobj);

        // save and cancel commands
        $form->addCommandButton("saveStartingPoint", $this->lng->txt("save"));
        $form->addCommandButton("startingPoints", $this->lng->txt("cancel"));

        $form->setTitle($this->lng->txt("starting_point_settings"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    protected function saveUserStartingPoint()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];

        if (!$rbacsystem->checkAccess("write", $this->parent_ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->FATAL);
        }

        include_once "Services/User/classes/class.ilUserUtil.php";

        $form = $this->getUserStartingPointForm();

        if ($form->checkInput()) {
            ilUserUtil::togglePersonalStartingPoint($form->getInput('usr_start_pers'));
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "startingPoints");
        }
        ilUtil::sendFailure($this->lng->txt("msg_error"), true);
        $ilCtrl->redirect($this, "startingPoints");
    }

    /**
     * store starting point from the form
     */
    protected function saveStartingPoint()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $tree = $DIC['tree'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $tpl = $DIC['tpl'];

        if (!$rbacsystem->checkAccess("write", $this->parent_ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->FATAL);
        }

        if ((int) $_POST['start_point_id'] > 0) {
            $start_point_id = (int) $_POST['start_point_id'];
        }

        //add from form
        $form = $this->getRoleStartingPointForm();
        if ($form->checkInput()) {
            //if role
            if ($form->getInput('role')) {

                //create starting point
                if ($start_point_id) {
                    $starting_point = new ilStartingPoint($start_point_id);
                } else { //edit
                    $starting_point = new ilStartingPoint();
                }
                $starting_point->setRuleType(ilStartingPoint::ROLE_BASED);
                $starting_point->setStartingPoint($form->getInput("start_point"));
                $rules = array("role_id" => $form->getInput('role'));
                $starting_point->setRuleOptions(serialize($rules));

                $obj_id = $form->getInput('start_object');
                if ($obj_id && ($starting_point->getStartingPoint() == ilUserUtil::START_REPOSITORY_OBJ)) {
                    if (ilObject::_lookupObjId($obj_id) && !$tree->isDeleted($obj_id)) {
                        $starting_point->setStartingObject($obj_id);
                        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
                    } else {
                        ilUtil::sendFailure($this->lng->txt("obj_ref_id_not_exist"), true);
                    }
                } else {
                    $starting_point->setStartingObject(0);
                }

                if ($start_point_id) {
                    $starting_point->update();
                } else {
                    $starting_point->save();
                }
            } else {  //default
                ilUserUtil::setStartingPoint($form->getInput('start_point'), $form->getInput('start_object'));
                ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            }

            $ilCtrl->redirect($this, "startingPoints");
        }
        $tpl->setContent($form->getHTML());

        //$ilCtrl->redirect($this, "startingPoints");
    }

    public function saveOrder()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];

        if (!$rbacsystem->checkAccess("write", $this->parent_ref_id)) {
            $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
        }

        if ($_POST['position']) {
            require_once "./Services/AccessControl/classes/class.ilStartingPoint.php";

            $sp = new ilStartingPoint();
            $sp->saveOrder($_POST['position']);
        }

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "startingPoints");
    }

    /**
     * Confirm delete starting point
     */
    public function confirmDeleteStartingPoint()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($lng->txt('back_to_starting_points_list'), $ilCtrl->getLinkTarget($this, 'startingPoints'));

        include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($ilCtrl->getFormAction($this));
        $conf->setHeaderText($lng->txt('confirm_delete_starting_point'));

        //if type role based
        if ($_REQUEST['rolid'] && $_REQUEST['spid']) {
            include_once "./Services/AccessControl/classes/class.ilObjRole.php";

            $rolid = (int) $_REQUEST['rolid'];
            $spid = (int) $_REQUEST['spid'];

            $role = new ilObjRole($rolid);

            $conf->addItem('rolid', $rolid, $role->getTitle());
            $conf->addItem('spid', $spid, "");
        }

        $conf->setConfirm($lng->txt('delete'), 'deleteStartingPoint');
        $conf->setCancel($lng->txt('cancel'), 'startingPoints');

        $tpl->setContent($conf->getHTML());
    }

    /**
     * Set to 0 the starting point values
     */
    protected function deleteStartingPoint()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];

        if (!$rbacsystem->checkAccess("write", $this->parent_ref_id)) {
            $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
        }

        require_once "./Services/AccessControl/classes/class.ilObjRole.php";

        if ($rolid = $_REQUEST['rolid'] && $spid = $_REQUEST['spid']) {
            include_once("./Services/AccessControl/classes/class.ilStartingPoint.php");
            $sp = new ilStartingPoint($spid);
            $sp->delete();
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        } else {
            ilUtil::sendFailure($this->lng->txt("msg_spoint_not_modified"), true);
        }
        $ilCtrl->redirect($this, "startingPoints");
    }
}
