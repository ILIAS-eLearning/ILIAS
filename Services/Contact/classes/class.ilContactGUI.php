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
 *********************************************************************/

/**
* @author Jens Conze
* @ingroup ServicesMail
* @ilCtrl_Calls ilContactGUI: ilMailSearchCoursesGUI, ilMailSearchGroupsGUI, ilMailSearchLearningSequenceGUI, ilMailingListsGUI
* @ilCtrl_Calls ilContactGUI: ilUsersGalleryGUI, ilPublicUserProfileGUI
*/
class ilContactGUI
{
    public const CONTACTS_VIEW_GALLERY = 1;
    public const CONTACTS_VIEW_TABLE = 2;
    private \ILIAS\HTTP\GlobalHttpState $http;
    /**
     * @var int[]|null
     */
    private ?array $postUsrId = null;

    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs_gui;
    protected ilHelpGUI $help;
    protected ilToolbarGUI $toolbar;
    protected ilFormatMail $umail;
    protected ilObjUser $user;
    protected ilErrorHandling $error;
    protected ilRbacSystem $rbacsystem;
    protected bool $has_sub_tabs = false;
    protected ILIAS\Refinery\Factory $refinery;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->tabs_gui = $DIC['ilTabs'];
        $this->help = $DIC['ilHelp'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->user = $DIC['ilUser'];
        $this->error = $DIC['ilErr'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->ctrl->saveParameter($this, "mobj_id");

        $this->umail = new ilFormatMail($this->user->getId());
        $this->lng->loadLanguageModule('buddysystem');
    }

    public function executeCommand(): bool
    {
        $this->showSubTabs();

        $forward_class = $this->ctrl->getNextClass($this);

        $this->umail->savePostData($this->user->getId(), [], '', '', '', '', '', false);

        switch (strtolower($forward_class)) {
            case strtolower(ilMailSearchCoursesGUI::class):
                $this->activateTab('mail_my_courses');

                $this->ctrl->setReturn($this, "showContacts");
                $this->ctrl->forwardCommand(new ilMailSearchCoursesGUI());
                break;

            case strtolower(ilMailSearchGroupsGUI::class):
                $this->activateTab('mail_my_groups');

                $this->ctrl->setReturn($this, "showContacts");
                $this->ctrl->forwardCommand(new ilMailSearchGroupsGUI());
                break;

            case strtolower(ilMailingListsGUI::class):
                $this->activateTab('mail_my_mailing_lists');

                $this->ctrl->setReturn($this, "showContacts");
                $this->ctrl->forwardCommand(new ilMailingListsGUI());
                break;

            case strtolower(ilUsersGalleryGUI::class):
                if (!ilBuddySystem::getInstance()->isEnabled()) {
                    $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
                }

                $this->tabs_gui->activateSubTab('buddy_view_gallery');
                $this->activateTab('my_contacts');
                $this->ctrl->forwardCommand(new ilUsersGalleryGUI(new ilUsersGalleryContacts()));
                $this->tpl->printToStdout();
                break;

            case strtolower(ilPublicUserProfileGUI::class):
                $profile_gui = new ilPublicUserProfileGUI(
                    $this->http->wrapper()->query()->retrieve('user', $this->refinery->kindlyTo()->int())
                );
                $profile_gui->setBackUrl($this->ctrl->getLinkTarget($this, 'showContacts'));
                $this->ctrl->forwardCommand($profile_gui);
                $this->tpl->printToStdout();
                break;

            default:
                $this->activateTab('mail_my_entries');

                if (!($cmd = $this->ctrl->getCmd())) {
                    if (ilBuddySystem::getInstance()->isEnabled()) {
                        $cmd = 'showContacts';
                    } else {
                        $this->ctrl->redirectByClass(ilMailSearchCoursesGUI::class);
                    }
                }

                $this->$cmd();
                break;
        }
        return true;
    }


    private function showSubTabs(): void
    {
        $galleryCmdClasses = array_map('strtolower', [ilUsersGalleryGUI::class, self::class]);
        if ($this->tabs_gui->hasTabs()) {
            if (ilBuddySystem::getInstance()->isEnabled()) {
                $this->tabs_gui->addSubTab(
                    'my_contacts',
                    $this->lng->txt('my_contacts'),
                    $this->ctrl->getLinkTarget($this)
                );

                if (in_array(strtolower($this->ctrl->getCmdClass()), $galleryCmdClasses, true)) {
                    $view_selection = new ilSelectInputGUI('', 'contacts_view');
                    $view_selection->setOptions([
                        (string) self::CONTACTS_VIEW_TABLE => $this->lng->txt('buddy_view_table'),
                        (string) self::CONTACTS_VIEW_GALLERY => $this->lng->txt('buddy_view_gallery')
                    ]);
                    $view_selection->setValue(
                        strtolower($this->ctrl->getCmdClass()) === strtolower(ilUsersGalleryGUI::class)
                            ? (string) self::CONTACTS_VIEW_GALLERY
                            : (string) self::CONTACTS_VIEW_TABLE
                    );
                    $this->toolbar->addInputItem($view_selection);

                    $contact_view_btn = ilSubmitButton::getInstance();
                    $contact_view_btn->setCaption('show');
                    $contact_view_btn->setCommand('changeContactsView');
                    $this->toolbar->addButtonInstance($contact_view_btn);
                    $this->toolbar->setFormAction($this->ctrl->getFormAction($this, 'changeContactsView'));
                }

                if (
                    count(ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations()) > 0 ||
                    (new ilMailingLists($this->user))->hasAny()
                ) {
                    $this->tabs_gui->addSubTab(
                        'mail_my_mailing_lists',
                        $this->lng->txt('mail_my_mailing_lists'),
                        $this->ctrl->getLinkTargetByClass(ilMailingListsGUI::class)
                    );
                }
            }

            $this->tabs_gui->addSubTab(
                'mail_my_courses',
                $this->lng->txt('mail_my_courses'),
                $this->ctrl->getLinkTargetByClass(ilMailSearchCoursesGUI::class)
            );
            $this->tabs_gui->addSubTab(
                'mail_my_groups',
                $this->lng->txt('mail_my_groups'),
                $this->ctrl->getLinkTargetByClass(ilMailSearchGroupsGUI::class)
            );
            $this->has_sub_tabs = true;
        } else {
            $this->tpl->setTitleIcon(ilUtil::getImagePath('icon_cadm.svg'));

            $this->help->setScreenIdComponent('contacts');

            if (ilBuddySystem::getInstance()->isEnabled()) {
                $this->tabs_gui->addTab(
                    'my_contacts',
                    $this->lng->txt('my_contacts'),
                    $this->ctrl->getLinkTarget($this)
                );

                if (in_array(strtolower($this->ctrl->getCmdClass()), $galleryCmdClasses, true)) {
                    $this->tabs_gui->addSubTab(
                        'buddy_view_table',
                        $this->lng->txt('buddy_view_table'),
                        $this->ctrl->getLinkTarget($this)
                    );
                    $this->tabs_gui->addSubTab(
                        'buddy_view_gallery',
                        $this->lng->txt('buddy_view_gallery'),
                        $this->ctrl->getLinkTargetByClass(ilUsersGalleryGUI::class)
                    );
                }

                if (
                    count(ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations()) > 0 ||
                    (new ilMailingLists($this->user))->hasAny()
                ) {
                    $this->tabs_gui->addTab(
                        'mail_my_mailing_lists',
                        $this->lng->txt('mail_my_mailing_lists'),
                        $this->ctrl->getLinkTargetByClass(ilMailingListsGUI::class)
                    );
                }
            }

            $this->tabs_gui->addTab(
                'mail_my_courses',
                $this->lng->txt('mail_my_courses'),
                $this->ctrl->getLinkTargetByClass(ilMailSearchCoursesGUI::class)
            );
            $this->tabs_gui->addTab(
                'mail_my_groups',
                $this->lng->txt('mail_my_groups'),
                $this->ctrl->getLinkTargetByClass(ilMailSearchGroupsGUI::class)
            );
        }
    }

    protected function activateTab(string $a_id): void
    {
        if ($this->has_sub_tabs) {
            $this->tabs_gui->activateSubTab($a_id);
        } else {
            $this->tabs_gui->activateTab($a_id);
        }
    }

    /**
     * This method is used to switch the contacts view between gallery and table in the mail system
     */
    protected function changeContactsView(): void
    {
        if (!ilBuddySystem::getInstance()->isEnabled()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        if ($this->http->wrapper()->post()->has('contacts_view')) {
            switch ($this->http->wrapper()->post()->retrieve('contacts_view', $this->refinery->kindlyTo()->int())) {
                case self::CONTACTS_VIEW_GALLERY:
                    $this->ctrl->redirectByClass(ilUsersGalleryGUI::class);

                    // no break
                case self::CONTACTS_VIEW_TABLE:
                    $this->ctrl->redirect($this);
            }
        }

        $this->ctrl->redirect($this);
    }


    protected function applyContactsTableFilter(): void
    {
        if (!ilBuddySystem::getInstance()->isEnabled()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        $table = new ilBuddySystemRelationsTableGUI($this, 'showContacts');

        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showContacts();
    }


    protected function resetContactsTableFilter(): void
    {
        if (!ilBuddySystem::getInstance()->isEnabled()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        $table = new ilBuddySystemRelationsTableGUI($this, 'showContacts');

        $table->resetOffset();
        $table->resetFilter();

        $this->showContacts();
    }


    protected function showContacts(): void
    {
        if (!ilBuddySystem::getInstance()->isEnabled()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        $this->tabs_gui->activateSubTab('buddy_view_table');
        $this->activateTab('my_contacts');

        $table = new ilBuddySystemRelationsTableGUI($this, 'showContacts');
        $table->populate();
        $this->tpl->setContent($table->getHTML());
        $this->tpl->printToStdout();
    }

    private function showContactRequests(): void
    {
        if (!ilBuddySystem::getInstance()->isEnabled()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        $table = new ilBuddySystemRelationsTableGUI($this, 'showContacts');

        $table->resetOffset();
        $table->resetFilter();

        $table->applyFilterValue(
            ilBuddySystemRelationsTableGUI::STATE_FILTER_ELM_ID,
            ilBuddySystemRequestedRelationState::class . '_p'
        );

        $this->showContacts();
    }

    protected function mailToUsers(): void
    {
        if (!$this->rbacsystem->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId())) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        try {
            $usr_ids = $this->http->wrapper()->post()->retrieve(
                'usr_id',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );

            // TODO: Replace this with some kind of 'ArrayLengthConstraint'
            if ($usr_ids === []) {
                throw new LengthException('mail_select_one_entry');
            }
        } catch (Exception) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one_entry'));
            $this->showContacts();
            return;
        }

        $logins = [];
        $mail_data = $this->umail->getSavedData();
        foreach ($usr_ids as $usr_id) {
            $login = ilObjUser::_lookupLogin($usr_id);
            if (!$this->umail->existsRecipient($login, (string) $mail_data['rcp_to'])) {
                $logins[] = $login;
            }
        }
        $logins = array_filter($logins);

        if ($logins !== []) {
            $mail_data = $this->umail->appendSearchResult($logins, 'to');
            $this->umail->savePostData(
                (int) $mail_data['user_id'],
                $mail_data['attachments'],
                $mail_data['rcp_to'],
                $mail_data['rcp_cc'],
                $mail_data['rcp_bcc'],
                $mail_data['m_subject'],
                $mail_data['m_message'],
                $mail_data['use_placeholders'],
                $mail_data['tpl_ctx_id'],
                $mail_data['tpl_ctx_params']
            );
        }

        $this->ctrl->redirectToURL('ilias.php?baseClass=ilMailGUI&type=search_res');
    }

    /**
     * Last step of chat invitations
     * check access for every selected user and send invitation
     */
    public function submitInvitation(): void
    {
        $usr_ids = [];
        try {
            $usr_ids = $this->refinery->kindlyTo()->listOf(
                $this->refinery->kindlyTo()->int()
            )->transform(explode(',', $this->http->wrapper()->post()->retrieve(
                'usr_id',
                $this->refinery->kindlyTo()->string()
            )));

            // TODO: Replace this with some kind of 'ArrayLengthConstraint'
            if ($usr_ids === []) {
                throw new LengthException('select_one');
            }
        } catch (Exception) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this);
        }

        if (!$this->http->wrapper()->post()->has('room_id')) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'));
            $this->postUsrId = $usr_ids;
            $this->inviteToChat();
            return;
        }

        // get selected chatroom from POST-String, format: "room_id,scope"
        $room_ids = $this->refinery->kindlyTo()->listOf(
            $this->refinery->kindlyTo()->int()
        )->transform(explode(',', $this->http->wrapper()->post()->retrieve(
            'room_id',
            $this->refinery->kindlyTo()->string()
        )));

        $room_id = (int) $room_ids[0];

        $scope = 0;
        if (count($room_ids) > 1) {
            $scope = (int) $room_ids[1];
        }

        $room = ilChatroom::byRoomId($room_id, true);
        $no_access = [];
        $no_login = [];
        $valid_users = [];
        $valid_user_to_login_map = [];

        foreach ($usr_ids as $usr_id) {
            $login = ilObjUser::_lookupLogin($usr_id);
            if ($login === '') {
                $no_login[$usr_id] = $usr_id;
                continue;
            }

            $ref_id = $room->getRefIdByRoomId($room_id);

            if (
                !ilChatroom::checkPermissionsOfUser((int) $usr_id, 'read', $ref_id) ||
                $room->isUserBanned((int) $usr_id)
            ) {
                $no_access[$usr_id] = $login;
            } else {
                $valid_users[$usr_id] = $usr_id;
                $valid_user_to_login_map[$usr_id] = $login;
            }
        }

        if (count($no_access) || count($no_login)) {
            $message = '';

            if ($no_access !== []) {
                $message .= $this->lng->txt('chat_users_without_permission') . ':<br>';
                $list = '';

                foreach ($no_access as $login) {
                    $list .= '<li>' . $login . '</li>';
                }

                $message .= '<ul>';
                $message .= $list;
                $message .= '</ul>';
            }

            if ($no_login !== []) {
                $message .= $this->lng->txt('chat_users_without_login') . ':<br>';
                $list = '';

                foreach ($no_login as $usr_id) {
                    $list .= '<li>' . $usr_id . '</li>';
                }

                $message .= '<ul>';
                $message .= $list;
                $message .= '</ul>';
            }

            $this->tpl->setOnScreenMessage('failure', $message);
            $this->postUsrId = $usr_ids;
            $this->inviteToChat();
            return;
        }

        $ref_id = $room->getRefIdByRoomId($room_id);

        $url = $scope !== 0 ? ilLink::_getStaticLink($ref_id, 'chtr', true, '_' . $scope) : ilLink::_getStaticLink($ref_id, 'chtr');
        $link = '<p><a target="chatframe" href="' . $url . '" title="' . $this->lng->txt('goto_invitation_chat') . '">' . $this->lng->txt('goto_invitation_chat') . '</a></p>';

        $userlist = [];
        foreach ($valid_users as $id) {
            $room->inviteUserToPrivateRoom((int) $id, $scope);
            $room->sendInvitationNotification(
                null,
                $this->user->getId(),
                (int) $id,
                $scope,
                $url
            );
            $userlist[] = '<li>' . $valid_user_to_login_map[$id] . '</li>';
        }

        if ($userlist !== []) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('chat_users_have_been_invited') . '<ul>' . implode('', $userlist) . '</ul>' . $link, true);
        }

        $this->ctrl->redirect($this);
    }

    /**
     * Send chat invitations to selected Users
     */
    protected function inviteToChat(): void
    {
        $this->tabs_gui->activateSubTab('buddy_view_table');
        $this->activateTab('my_contacts');

        $this->lng->loadLanguageModule('chatroom');

        $usr_ids = $this->postUsrId;
        if (!is_array($usr_ids)) {
            try {
                $usr_ids = $this->http->wrapper()->post()->retrieve(
                    'usr_id',
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
                );
            } catch (Exception) {
                $usr_ids = [];
            }
        }

        if (!is_array($usr_ids) || [] === $usr_ids) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this);
        }

        $usr_ids = $this->refinery->kindlyTo()->listOf(
            $this->refinery->kindlyTo()->int()
        )->transform($usr_ids);

        $ilChatroom = new ilChatroom();
        $chat_rooms = $ilChatroom->getAccessibleRoomIdByTitleMap($this->user->getId());
        $subrooms = [];

        foreach (array_keys($chat_rooms) as $room_id) {
            $subrooms[] = $ilChatroom->getPrivateSubRooms($room_id, $this->user->getId());
        }

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('mail_invite_users_to_chat'));

        $psel = new ilSelectInputGUI($this->lng->txt('chat_select_room'), 'room_id');
        $options = [];

        asort($chat_rooms);
        foreach ($chat_rooms as $room_id => $room) {
            $ref_id = $room_id;

            if ($ilChatroom->isUserBanned($this->user->getId())) {
                continue;
            }

            $options[$ref_id] = $room;

            foreach ($subrooms as $subroom) {
                foreach ($subroom as $sub_id => $parent_id) {
                    if ($parent_id === $ref_id) {
                        $title = ilChatroom::lookupPrivateRoomTitle($sub_id);
                        $options[$ref_id . ',' . $sub_id] = '+&nbsp;' . $title;
                    }
                }
            }
        }

        $psel->setOptions($options);
        $form->addItem($psel);
        $phidden = new ilHiddenInputGUI('usr_id');
        $phidden->setValue(implode(',', $usr_ids));
        $form->addItem($phidden);
        $form->addCommandButton('submitInvitation', $this->lng->txt('submit'));
        $form->addCommandButton('showContacts', $this->lng->txt('cancel'));
        $form->setFormAction($this->ctrl->getFormAction($this, 'showContacts'));

        $this->tpl->setTitle($this->lng->txt('mail_invite_users_to_chat'));
        $this->tpl->setContent($form->getHTML());
        $this->tpl->printToStdout();
    }
}
