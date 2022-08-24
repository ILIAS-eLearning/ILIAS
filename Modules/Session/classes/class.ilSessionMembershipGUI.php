<?php

declare(strict_types=1);

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
 ********************************************************************
 */

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
    protected array $requested_visible_participants = [];
    protected array $requested_participated = [];
    protected array $requested_registered = [];
    protected array $requested_contact = [];
    protected array $requested_excused = [];
    protected array $requested_mark = [];
    protected array $requested_comment = [];
    protected array $requested_notification = [];
    protected array $requested_participants = [];

    public function __construct(ilObjectGUI $repository_gui, ilObject $repository_obj)
    {
        parent::__construct($repository_gui, $repository_obj);

        if ($this->http->wrapper()->post()->has('visible_participants')) {
            $this->requested_visible_participants = (array) $this->http->wrapper()->post()->retrieve(
                'visible_participants',
                $this->refinery->custom()->transformation(function ($v) {
                    return $v;
                })
            );
        } elseif ($this->http->wrapper()->query()->has('visible_participants')) {
            $this->requested_visible_participants = (array) $this->http->wrapper()->query()->retrieve(
                'visible_participants',
                $this->refinery->custom()->transformation(function ($v) {
                    return $v;
                })
            );
        }

        if ($this->http->wrapper()->post()->has('participated')) {
            $this->requested_participated = (array) $this->http->wrapper()->post()->retrieve(
                'participated',
                $this->refinery->custom()->transformation(function ($v) {
                    return $v;
                })
            );
        }

        if ($this->http->wrapper()->post()->has('registered')) {
            $this->requested_registered = (array) $this->http->wrapper()->post()->retrieve(
                'registered',
                $this->refinery->custom()->transformation(function ($v) {
                    return $v;
                })
            );
        }

        if ($this->http->wrapper()->post()->has('contact')) {
            $this->requested_contact = (array) $this->http->wrapper()->post()->retrieve(
                'contact',
                $this->refinery->custom()->transformation(function ($v) {
                    return $v;
                })
            );
        }

        if ($this->http->wrapper()->post()->has('excused')) {
            $this->requested_excused = (array) $this->http->wrapper()->post()->retrieve(
                'excused',
                $this->refinery->custom()->transformation(function ($v) {
                    return $v;
                })
            );
        }

        if ($this->http->wrapper()->post()->has('mark')) {
            $this->requested_mark = (array) $this->http->wrapper()->post()->retrieve(
                'mark',
                $this->refinery->custom()->transformation(function ($v) {
                    return $v;
                })
            );
        }

        if ($this->http->wrapper()->post()->has('comment')) {
            $this->requested_comment = (array) $this->http->wrapper()->post()->retrieve(
                'comment',
                $this->refinery->custom()->transformation(function ($v) {
                    return $v;
                })
            );
        }

        if ($this->http->wrapper()->post()->has('notification')) {
            $this->requested_notification = (array) $this->http->wrapper()->post()->retrieve(
                'notification',
                $this->refinery->custom()->transformation(function ($v) {
                    return $v;
                })
            );
        }

        if ($this->http->wrapper()->post()->has('participants')) {
            $this->requested_participants = (array) $this->http->wrapper()->post()->retrieve(
                'participants',
                $this->refinery->custom()->transformation(function ($v) {
                    return $v;
                })
            );
        }
    }

    protected function getMailMemberRoles(): ?ilAbstractMailMemberRoles
    {
        return new ilMailMemberSessionRoles();
    }

    /**
     * No support for positions in sessions
     * Check if rbac or position access is granted.
     */
    protected function checkRbacOrPositionAccessBool(string $a_rbac_perm, string $a_pos_perm, int $a_ref_id = 0): bool
    {
        if (!$a_ref_id) {
            $a_ref_id = $this->getParentObject()->getRefId();
        }
        return $this->checkPermissionBool($a_rbac_perm, "", "", $a_ref_id);
    }

    protected function initParticipantTemplate(): void
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.sess_edit_members.html', 'Modules/Session');
    }

    protected function initWaitingList(): ilSessionWaitingList
    {
        $wait = new ilSessionWaitingList($this->getParentObject()->getId());
        return $wait;
    }

    public function getParentObject(): ilObjSession
    {
        /**
         * @var ilObjSession $parent_object
         */
        $parent_object = parent::getParentObject();
        return $parent_object;
    }

    protected function initParticipantTableGUI(): ilSessionParticipantsTableGUI
    {
        $table = new ilSessionParticipantsTableGUI(
            $this,
            $this->getParentObject(),
            'participants'
        );
        $table->init();
        return $table;
    }

    protected function initSubscriberTable(): ilSubscriberTableGUI
    {
        $subscriber = new ilSubscriberTableGUI($this, $this->getParentObject(), true, false);
        $subscriber->setTitle($this->lng->txt('group_new_registrations'));
        return $subscriber;
    }

    protected function updateMembers(): void
    {
        $this->checkPermission('manage_members');

        $part = ilParticipants::getInstance($this->getParentObject()->getRefId());


        $wait = new ilSessionWaitingList($this->getParentObject()->getId());
        $waiting = $wait->getUserIds();

        foreach ($this->requested_visible_participants as $part_id) {
            if (in_array($part_id, $waiting)) {
                // so not update users on waiting list
                continue;
            }

            $participated = (bool) ($this->requested_participated[$part_id] ?? false);
            $registered = (bool) ($this->requested_registered[$part_id] ?? false);
            $contact = (bool) ($this->requested_contact[$part_id] ?? false);
            $excused = (bool) ($this->requested_excused[$part_id] ?? false);

            $part_id = (int) $part_id;

            if ($part->isAssigned($part_id)) {
                if (!$participated && !$registered && !$contact) {
                    $part->delete($part_id);
                }
            } else {
                if ($participated || $registered || $contact) {
                    $part->add($part_id, ilParticipants::IL_SESS_MEMBER);
                }
            }
            $event_part = new ilEventParticipants($this->getParentObject()->getId());
            $event_part->setUserId($part_id);
            $event_part->setMark(ilUtil::stripSlashes($this->requested_mark[$part_id]));
            $event_part->setComment(ilUtil::stripSlashes($this->requested_comment[$part_id]));
            $event_part->setNotificationEnabled((bool) ($this->requested_notification[$part_id] ?? false));
            $event_part->setParticipated($participated);
            $event_part->setRegistered($registered);
            $event_part->setContact($contact);
            $event_part->setExcused($excused);
            $event_part->updateUser();
        }

        $this->tpl->setOnScreenMessage('success', $this->getLanguage()->txt('settings_saved'), true);
        $this->getCtrl()->redirect($this, 'participants');
    }

    protected function confirmDeleteParticipants(): void
    {
        $participants = $this->requested_participants;

        if (!count($participants)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'participants');
        }

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

    protected function deleteParticipants(): void
    {
        $this->checkPermission('manage_members');

        $participants = $this->requested_participants;

        if (!is_array($participants) || !count($participants)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
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

        $this->tpl->setOnScreenMessage('success', $this->lng->txt($this->getParentObject()->getType() . "_members_deleted"), true);
        $this->ctrl->redirect($this, "participants");
    }

    public function getPrintMemberData(array $a_members): array
    {
        return $a_members;
    }

    /**
     * @inheritdoc
     */
    protected function canAddOrSearchUsers(): bool
    {
        return false;
    }

    public function getAttendanceListUserData(int $user_id, array $filters = []): array
    {
        $data = $this->getMembersObject()->getEventParticipants()->getUser($user_id);
        $data['registered'] = (bool) ($data['registered'] ?? false);
        $data['participated'] = (bool) ($data['participated'] ?? false);

        if ($filters && $filters["registered"] && !$data["registered"]) {
            return [];
        }


        $data['registered'] = $data['registered'] ?
            $this->lng->txt('yes') :
            $this->lng->txt('no');
        $data['participated'] = $data['participated'] ?
            $this->lng->txt('yes') :
            $this->lng->txt('no');

        return $data;
    }

    protected function getMemberTabName(): string
    {
        return $this->lng->txt($this->getParentObject()->getType() . '_members');
    }

    protected function getMailContextOptions(): array
    {
        $context_options = [
            ilMailFormCall::CONTEXT_KEY => ilSessionMailTemplateParticipantContext::ID,
            'ref_id' => $this->getParentObject()->getRefId(),
            'ts' => time()
        ];

        return $context_options;
    }
}
