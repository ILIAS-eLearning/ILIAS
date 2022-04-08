<?php declare(strict_types=0);

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
 * Class ilCourseParticipantsGroupsGUI
 * @author       Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilCourseParticipantsGroupsGUI:
 */
class ilCourseParticipantsGroupsGUI
{
    private int $ref_id = 0;

    protected ilAccessHandler $access;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;
    protected ilErrorHandling $error;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjectDataCache $objectDataCache;
    protected GlobalHttpState $http;
    protected Factory $refinery;

    public function __construct($a_ref_id)
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->error = $DIC['ilErr'];
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->objectDataCache = $DIC['ilObjDataCache'];
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->ref_id = $a_ref_id;
    }

    public function executeCommand() : void
    {
        if (!$this->access->checkRbacOrPositionPermissionAccess('manage_members', 'manage_members', $this->ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->WARNING);
        }
        $cmd = $this->ctrl->getCmd();
        if (!$cmd) {
            $cmd = "show";
        }
        $this->$cmd();
    }

    public function show() : void
    {
        $tbl_gui = new ilCourseParticipantsGroupsTableGUI($this, "show", $this->ref_id);
        $this->tpl->setContent($tbl_gui->getHTML());
    }

    public function applyFilter() : void
    {
        $tbl_gui = new ilCourseParticipantsGroupsTableGUI($this, "show", $this->ref_id);
        $tbl_gui->resetOffset();
        $tbl_gui->writeFilterToSession();
        $this->show();
    }

    public function resetFilter() : void
    {
        $tbl_gui = new ilCourseParticipantsGroupsTableGUI($this, "show", $this->ref_id);
        $tbl_gui->resetOffset();
        $tbl_gui->resetFilter();
        $this->show();
    }

    public function confirmRemove() : void
    {
        $grp_id = 0;
        if ($this->http->wrapper()->query()->has('grp_id')) {
            $grp_id = $this->http->wrapper()->query()->retrieve(
                'grp_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this, 'remove'));
        $confirm->addHiddenItem("grp_id", $grp_id);
        $confirm->setHeaderText($this->lng->txt('grp_dismiss_member'));
        $confirm->setConfirm($this->lng->txt('confirm'), 'remove');
        $confirm->setCancel($this->lng->txt('cancel'), 'show');

        $usr_id = 0;
        if ($this->http->wrapper()->query()->has('usr_id')) {
            $usr_id = $this->http->wrapper()->query()->retrieve(
                'usr_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $confirm->addItem(
            'usr_id',
            $usr_id,
            ilUserUtil::getNamePresentation($usr_id, false, false, "", true),
            ilUtil::getImagePath('icon_usr.svg')
        );

        $this->tpl->setContent($confirm->getHTML());
    }

    protected function remove() : void
    {
        $grp_id = 0;
        if ($this->http->wrapper()->post()->has('grp_id')) {
            $grp_id = $this->http->wrapper()->post()->retrieve(
                'grp_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $usr_id = 0;
        if ($this->http->wrapper()->post()->has('usr_id')) {
            $usr_id = $this->http->wrapper()->post()->retrieve(
                'usr_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        if (!$this->access->checkRbacOrPositionPermissionAccess('manage_members', 'manage_members', $grp_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->show();
            return;
        }

        $members_obj = ilGroupParticipants::_getInstanceByObjId($this->objectDataCache->lookupObjId($grp_id));
        $members_obj->delete($usr_id);

        // Send notification
        $members_obj->sendNotification(
            ilGroupMembershipMailNotification::TYPE_DISMISS_MEMBER,
            (int) $usr_id
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("grp_msg_membership_annulled"), true);
        $this->ctrl->redirect($this, "show");
    }

    protected function add() : void
    {
        $grp_id = 0;
        if ($this->http->wrapper()->post()->has('grp_id')) {
            $grp_id = $this->http->wrapper()->post()->retrieve(
                'grp_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $usr_ids = 0;
        if ($this->http->wrapper()->post()->has('usrs')) {
            $usr_ids = $this->http->wrapper()->post()->retrieve(
                'usrs',
                $this->refinery->kindlyTo()->int()
            );
        }

        if (count($usr_ids) > 0) {
            if (!$this->access->checkRbacOrPositionPermissionAccess('manage_members', 'manage_members', $grp_id)) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
                $this->show();
                return;
            }

            $members_obj = ilGroupParticipants::_getInstanceByObjId($this->objectDataCache->lookupObjId($grp_id));
            foreach ($usr_ids as $new_member) {
                if (!$members_obj->add($new_member, ilParticipants::IL_GRP_MEMBER)) {
                    $this->error->raiseError("An Error occured while assigning user to group !", $this->error->MESSAGE);
                }

                $members_obj->sendNotification(
                    ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER,
                    $new_member
                );
            }
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("grp_msg_member_assigned"));
        }
        $this->show();
    }
}
