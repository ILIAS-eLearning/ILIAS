<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Membership/classes/class.ilMembershipGUI.php';

/**
 * GUI class for membership features
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ilCtrl_Calls ilSessionMembershipGUI: ilMailMemberSearchGUI, ilUsersGalleryGUI, ilRepositorySearchGUI
 * @ilCtrl_Calls ilSessionMembershipGUI: ilSessionOverviewGUI
 * @ilCtrl_Calls ilSessionMembershipGUI: ilMemberExportGUI
 *
 */
class ilSessionMembershipGUI extends ilMembershipGUI
{
    /**
     * @return ilAbstractMailMemberRoles|null
     */
    protected function getMailMemberRoles()
    {
        return new ilMailMemberSessionRoles();
    }


    /**
     * No support for positions in sessions
     * Check if rbac or position access is granted.
     * @param string $a_rbac_perm
     * @param string $a_pos_perm
     * @param int $a_ref_id
     */
    protected function checkRbacOrPositionAccessBool($a_rbac_perm, $a_pos_perm, $a_ref_id = 0)
    {
        if (!$a_ref_id) {
            $a_ref_id = $this->getParentObject()->getRefId();
        }
        return $this->checkPermissionBool($a_rbac_perm, $a_ref_id);
    }

    
    
    /**
     * Init participant view template
     */
    protected function initParticipantTemplate()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.sess_edit_members.html', 'Modules/Session');
    }
    
    /**
     * init waiting list
     * @return ilGroupWaitingList
     */
    protected function initWaitingList()
    {
        include_once './Modules/Session/classes/class.ilSessionWaitingList.php';
        $wait = new ilSessionWaitingList($this->getParentObject()->getId());
        return $wait;
    }
    
    /**
     * @return \ilParticpantTableGUI
     */
    protected function initParticipantTableGUI()
    {
        $table = new ilSessionParticipantsTableGUI(
            $this,
            $this->getParentObject(),
            'participants'
        );
        $table->init();
        return $table;
    }

    /**
     * @return \ilSubscriberTableGUI
     */
    protected function initSubscriberTable()
    {
        $subscriber = new ilSubscriberTableGUI($this, $this->getParentObject(), true, false);
        $subscriber->setTitle($this->lng->txt('group_new_registrations'));
        return $subscriber;
    }
    
    
    /**
     * update entries from member table
     */
    protected function updateMembers()
    {
        $this->checkPermission('manage_members');
        
        $part = ilParticipants::getInstance($this->getParentObject()->getRefId());


        $wait = new ilSessionWaitingList($this->getParentObject()->getId());
        $waiting = $wait->getUserIds();

        foreach ((array) $_REQUEST['visible_participants'] as $part_id) {
            if (in_array($part_id, $waiting)) {
                // so not update users on waiting list
                continue;
            }

            $participated = (bool) $_POST['participated'][$part_id];
            $registered = (bool) $_POST['registered'][$part_id];
            $contact = (bool) $_POST['contact'][$part_id];
            
            if ($part->isAssigned($part_id)) {
                if (!$participated && !$registered && !$contact) {
                    $part->delete($part_id);
                }
            } else {
                if ($participated || $registered || $contact) {
                    $part->add($part_id, IL_SESS_MEMBER);
                }
            }
            $event_part = new ilEventParticipants($this->getParentObject()->getId());
            $event_part->setUserId($part_id);
            $event_part->setMark(ilUtil::stripSlashes($_POST['mark'][$part_id]));
            $event_part->setComment(ilUtil::stripSlashes($_POST['comment'][$part_id]));
            $event_part->setParticipated($participated);
            $event_part->setRegistered($registered);
            $event_part->setContact($contact);
            $event_part->updateUser();
        }
        
        ilUtil::sendSuccess($this->getLanguage()->txt('settings_saved'), true);
        $this->getCtrl()->redirect($this, 'participants');
    }
    
    
    /**
     * Show confirmation screen for participants deletion
     */
    protected function confirmDeleteParticipants()
    {
        $participants = (array) $_POST['participants'];
        
        if (!count($participants)) {
            ilUtil::sendFailure($this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'participants');
        }

        include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this, 'confirmDeleteParticipants'));
        $confirm->setHeaderText($this->lng->txt($this->getParentObject()->getType() . '_header_delete_members'));
        $confirm->setConfirm($this->lng->txt('confirm'), 'deleteParticipants');
        $confirm->setCancel($this->lng->txt('cancel'), 'participants');
        
        foreach ($participants as $usr_id) {
            $name = ilObjUser::_lookupName($usr_id);

            $confirm->addItem(
                'participants[]',
                $name['user_id'],
                $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']',
                ilUtil::getImagePath('icon_usr.svg')
            );
        }
        
        $this->tpl->setContent($confirm->getHTML());
    }
    
    /**
     * Delete participants
     * @global type $rbacreview
     * @global type $rbacsystem
     * @global type $ilAccess
     * @global type $ilUser
     * @return boolean
     */
    protected function deleteParticipants()
    {
        $this->checkPermission('manage_members');
                
        $participants = (array) $_POST['participants'];
        
        if (!is_array($participants) or !count($participants)) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, 'participants');
        }
        
        foreach ($participants as $part_id) {
            // delete role assignment
            $this->getMembersObject()->delete($part_id);
            // delete further settings
            $event_part = new ilEventParticipants($this->getParentObject()->getId());
            $event_part->setUserId($part_id);
            $event_part->setParticipated(false);
            $event_part->setRegistered(false);
            $event_part->setMark('');
            $event_part->setComment('');
            $event_part->updateUser();
        }
        
        ilUtil::sendSuccess($this->lng->txt($this->getParentObject()->getType() . "_members_deleted"), true);
        $this->ctrl->redirect($this, "participants");

        return true;
    }
    
    

    /**
     * @param array $a_members
     * @return array
     */
    public function getPrintMemberData($a_members)
    {
        return $a_members;
    }


    /**
     * @inheritdoc
     */
    protected function canAddOrSearchUsers()
    {
        return false;
    }

    
    
    /**
     * Callback from attendance list
     * @param int $a_user_id
     * @return array
     */
    public function getAttendanceListUserData($a_user_id, $a_filters)
    {
        $data = $this->getMembersObject()->getEventParticipants()->getUser($a_user_id);
        
        if ($a_filters && $a_filters["registered"] && !$data["registered"]) {
            return;
        }
        
        $data['registered'] = $data['registered'] ?
            $this->lng->txt('yes') :
            $this->lng->txt('no');
        $data['participated'] = $data['participated'] ?
            $this->lng->txt('yes') :
            $this->lng->txt('no');
        
        return $data;
    }

    /**
     * Get member tab name
     * @return string
     */
    protected function getMemberTabName()
    {
        return $this->lng->txt($this->getParentObject()->getType() . '_members');
    }



    /**
     * @inheritdoc
     */
    protected function getMailContextOptions()
    {
        $context_options = [];

        $context_options =
            [
                ilMailFormCall::CONTEXT_KEY => ilSessionMailTemplateParticipantContext::ID,
                'ref_id' => $this->getParentObject()->getRefId(),
                'ts' => time()
            ];
        return $context_options;
    }
}
