<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Course/classes/class.ilCourseParticipantsGroupsTableGUI.php";

/**
* Class ilCourseParticipantsGroupsGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjCourseGUI.php 24234 2010-06-14 12:35:45Z smeyer $
*
* @ilCtrl_Calls ilCourseParticipantsGroupsGUI:
*
*/
class ilCourseParticipantsGroupsGUI
{
    /**
     * ref_id of parent course
     * @var int
     */
    private $ref_id = 0;
    
    public function __construct($a_ref_id)
    {
        $this->ref_id = $a_ref_id;
    }

    public function executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilErr = $DIC['ilErr'];
        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];
        
        if (!$GLOBALS['DIC']->access()->checkRbacOrPositionPermissionAccess('manage_members', 'manage_members', $this->ref_id)) {
            $ilErr->raiseError($lng->txt('permission_denied'), $ilErr->WARNING);
        }

        $cmd = $ilCtrl->getCmd();
        if (!$cmd) {
            $cmd = "show";
        }
        $this->$cmd();
    }
    
    public function show()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        
        $tbl_gui = new ilCourseParticipantsGroupsTableGUI($this, "show", $this->ref_id);
        $tpl->setContent($tbl_gui->getHTML());
    }

    public function applyFilter()
    {
        $tbl_gui = new ilCourseParticipantsGroupsTableGUI($this, "show", $this->ref_id);
        $tbl_gui->resetOffset();
        $tbl_gui->writeFilterToSession();
        $this->show();
    }

    public function resetFilter()
    {
        $tbl_gui = new ilCourseParticipantsGroupsTableGUI($this, "show", $this->ref_id);
        $tbl_gui->resetOffset();
        $tbl_gui->resetFilter();
        $this->show();
    }

    public function confirmRemove()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        
        include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($ilCtrl->getFormAction($this, 'remove'));
        $confirm->addHiddenItem("grp_id", $_GET["grp_id"]);
        $confirm->setHeaderText($lng->txt('grp_dismiss_member'));
        $confirm->setConfirm($lng->txt('confirm'), 'remove');
        $confirm->setCancel($lng->txt('cancel'), 'show');

        include_once './Services/User/classes/class.ilUserUtil.php';
      
        $confirm->addItem(
            'usr_id',
            $_GET["usr_id"],
            ilUserUtil::getNamePresentation($_GET["usr_id"], false, false, "", true),
            ilUtil::getImagePath('icon_usr.svg')
        );

        $tpl->setContent($confirm->getHTML());
    }

    /**
     * Remove user from group
     * @global type $ilObjDataCache
     * @global type $lng
     * @global type $ilCtrl
     * @return type
     */
    protected function remove()
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        if (!$GLOBALS['DIC']->access()->checkRbacOrPositionPermissionAccess('manage_members', 'manage_members', (int) $_POST['grp_id'])) {
            ilUtil::sendFailure($lng->txt("permission_denied"), true);
            $this->show();
            return;
        }
    
        include_once './Modules/Group/classes/class.ilGroupParticipants.php';
        $members_obj = ilGroupParticipants::_getInstanceByObjId($ilObjDataCache->lookupObjId((int) $_POST["grp_id"]));
        $members_obj->delete((int) $_POST["usr_id"]);

        // Send notification
        include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
        $members_obj->sendNotification(
            ilGroupMembershipMailNotification::TYPE_DISMISS_MEMBER,
            (int) $_POST["usr_id"]
        );
        
        ilUtil::sendSuccess($lng->txt("grp_msg_membership_annulled"), true);
        $ilCtrl->redirect($this, "show");
    }

    /**
     * Add user to group
     * @global type $ilErr
     * @global type $ilObjDataCache
     * @global type $lng
     * @global type $ilAccess
     * @return type
     */
    protected function add()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];

        if (sizeof($_POST["usrs"])) {
            if (!$GLOBALS['DIC']->access()->checkRbacOrPositionPermissionAccess('manage_members', 'manage_members', (int) $_POST['grp_id'])) {
                ilUtil::sendFailure($lng->txt("permission_denied"), true);
                $this->show();
                return;
            }

            include_once './Modules/Group/classes/class.ilGroupParticipants.php';
            $members_obj = ilGroupParticipants::_getInstanceByObjId($ilObjDataCache->lookupObjId((int) $_POST["grp_id"]));
            foreach ($_POST["usrs"] as $new_member) {
                if (!$members_obj->add($new_member, IL_GRP_MEMBER)) {
                    $ilErr->raiseError("An Error occured while assigning user to group !", $ilErr->MESSAGE);
                }

                include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
                $members_obj->sendNotification(
                    ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER,
                    $new_member
                );
            }
            ilUtil::sendSuccess($lng->txt("grp_msg_member_assigned"));
        }

        $this->show();
    }
}
