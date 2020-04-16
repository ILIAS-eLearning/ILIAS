<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/class.ilFormatMail.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystem.php';

/**
* @author Jens Conze
* @version $Id$
*
* @ingroup ServicesMail
* @ilCtrl_Calls ilContactGUI: ilMailSearchCoursesGUI, ilMailSearchGroupsGUI, ilMailSearchLearningSequenceGUI, ilMailingListsGUI
* @ilCtrl_Calls ilContactGUI: ilMailFormGUI, ilUsersGalleryGUI, ilPublicUserProfileGUI
*/
class ilContactGUI
{
    const CONTACTS_VIEW_GALLERY = 1;
    const CONTACTS_VIEW_TABLE = 2;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTabsGUI
     */
    protected $tabs_gui;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilFormatMail
     */
    protected $umail;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var bool
     */
    protected $has_sub_tabs = false;

    /**
     * ilContactGUI constructor.
     */
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

        $this->ctrl->saveParameter($this, "mobj_id");

        $this->umail = new ilFormatMail($this->user->getId());
        $this->lng->loadLanguageModule('buddysystem');
    }

    public function executeCommand()
    {
        $this->showSubTabs();

        $forward_class = $this->ctrl->getNextClass($this);

        // delete all stored maildata
        $this->umail->savePostData($this->user->getId(), array(), '', '', '', '', '', '', '', '');
        
        switch ($forward_class) {
            case 'ilmailformgui':
                include_once 'Services/Mail/classes/class.ilMailFormGUI.php';
                $this->ctrl->forwardCommand(new ilMailFormGUI());
                break;

            case 'ilmailsearchcoursesgui':
                include_once 'Services/Contact/classes/class.ilMailSearchCoursesGUI.php';

                $this->activateTab('mail_my_courses');

                $this->ctrl->setReturn($this, "showContacts");
                $this->ctrl->forwardCommand(new ilMailSearchCoursesGUI());
                break;

            case 'ilmailsearchgroupsgui':
                include_once 'Services/Contact/classes/class.ilMailSearchGroupsGUI.php';

                $this->activateTab('mail_my_groups');

                $this->ctrl->setReturn($this, "showContacts");
                $this->ctrl->forwardCommand(new ilMailSearchGroupsGUI());
                break;
            
            case 'ilmailinglistsgui':
                if (!ilBuddySystem::getInstance()->isEnabled()) {
                    $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
                }

                include_once 'Services/Contact/classes/class.ilMailingListsGUI.php';

                $this->activateTab('mail_my_mailing_lists');

                $this->ctrl->setReturn($this, "showContacts");
                $this->ctrl->forwardCommand(new ilMailingListsGUI());
                break;

            case 'ilusersgallerygui':
                if (!ilBuddySystem::getInstance()->isEnabled()) {
                    $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
                }

                $this->tabs_gui->activateSubTab('buddy_view_gallery');
                $this->activateTab('my_contacts');
                require_once 'Services/User/Gallery/classes/class.ilUsersGalleryContacts.php';
                require_once 'Services/User/Gallery/classes/class.ilUsersGalleryGUI.php';
                $this->ctrl->forwardCommand(new ilUsersGalleryGUI(new ilUsersGalleryContacts()));
                $this->tpl->show();
                break;

            case 'ilpublicuserprofilegui':
                require_once 'Services/User/classes/class.ilPublicUserProfileGUI.php';
                $profile_gui = new ilPublicUserProfileGUI(ilUtil::stripSlashes($_GET['user']));
                $profile_gui->setBackUrl($this->ctrl->getLinkTarget($this, 'showContacts'));
                $this->ctrl->forwardCommand($profile_gui);
                $this->tpl->show();
                break;

            default:
                $this->activateTab('mail_my_entries');

                if (!($cmd = $this->ctrl->getCmd())) {
                    if (ilBuddySystem::getInstance()->isEnabled()) {
                        $cmd = 'showContacts';
                    } else {
                        $this->ctrl->redirectByClass('ilmailsearchcoursesgui');
                    }
                }

                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     *
     */
    private function showSubTabs()
    {
        if ($this->tabs_gui->hasTabs()) {
            if (ilBuddySystem::getInstance()->isEnabled()) {
                $this->tabs_gui->addSubTab('my_contacts', $this->lng->txt('my_contacts'), $this->ctrl->getLinkTarget($this));

                if (in_array(strtolower($this->ctrl->getCmdClass()), array_map('strtolower', array('ilUsersGalleryGUI', get_class($this))))) {
                    require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
                    $view_selection = new ilSelectInputGUI('', 'contacts_view');
                    $view_selection->setOptions(array(
                        self::CONTACTS_VIEW_TABLE => $this->lng->txt('buddy_view_table'),
                        self::CONTACTS_VIEW_GALLERY => $this->lng->txt('buddy_view_gallery')
                    ));
                    $view_selection->setValue(
                        strtolower($this->ctrl->getCmdClass()) == 'ilusersgallerygui' ? self::CONTACTS_VIEW_GALLERY : self::CONTACTS_VIEW_TABLE
                    );
                    $this->toolbar->addInputItem($view_selection);

                    require_once 'Services/UIComponent/Button/classes/class.ilSubmitButton.php';
                    $contact_view_btn = ilSubmitButton::getInstance();
                    $contact_view_btn->setCaption('submit');
                    $contact_view_btn->setCommand('changeContactsView');
                    $this->toolbar->addButtonInstance($contact_view_btn);
                    $this->toolbar->setFormAction($this->ctrl->getFormAction($this, 'changeContactsView'));
                }

                $this->tabs_gui->addSubTab('mail_my_mailing_lists', $this->lng->txt('mail_my_mailing_lists'), $this->ctrl->getLinkTargetByClass('ilmailinglistsgui'));
            }

            $this->tabs_gui->addSubTab('mail_my_courses', $this->lng->txt('mail_my_courses'), $this->ctrl->getLinkTargetByClass('ilmailsearchcoursesgui'));
            $this->tabs_gui->addSubTab('mail_my_groups', $this->lng->txt('mail_my_groups'), $this->ctrl->getLinkTargetByClass('ilmailsearchgroupsgui'));
            $this->has_sub_tabs = true;
        } else {
            $this->help->setScreenIdComponent('contacts');

            if (ilBuddySystem::getInstance()->isEnabled()) {
                $this->tabs_gui->addTab('my_contacts', $this->lng->txt('my_contacts'), $this->ctrl->getLinkTarget($this));

                if (in_array(strtolower($this->ctrl->getCmdClass()), array_map('strtolower', array('ilUsersGalleryGUI', get_class($this))))) {
                    $this->tabs_gui->addSubTab('buddy_view_table', $this->lng->txt('buddy_view_table'), $this->ctrl->getLinkTarget($this));
                    $this->tabs_gui->addSubTab('buddy_view_gallery', $this->lng->txt('buddy_view_gallery'), $this->ctrl->getLinkTargetByClass('ilUsersGalleryGUI'));
                }

                $this->tabs_gui->addTab('mail_my_mailing_lists', $this->lng->txt('mail_my_mailing_lists'), $this->ctrl->getLinkTargetByClass('ilmailinglistsgui'));
            }

            $this->tabs_gui->addTab('mail_my_courses', $this->lng->txt('mail_my_courses'), $this->ctrl->getLinkTargetByClass('ilmailsearchcoursesgui'));
            $this->tabs_gui->addTab('mail_my_groups', $this->lng->txt('mail_my_groups'), $this->ctrl->getLinkTargetByClass('ilmailsearchgroupsgui'));
        }
    }

    /**
     * @param string $a_id
     */
    protected function activateTab($a_id)
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
    protected function changeContactsView()
    {
        if (!ilBuddySystem::getInstance()->isEnabled()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        if (isset($_POST['contacts_view'])) {
            switch ($_POST['contacts_view']) {
                case self::CONTACTS_VIEW_GALLERY:
                    $this->ctrl->redirectByClass('ilUsersGalleryGUI');
                    break;

                case self::CONTACTS_VIEW_TABLE:
                    $this->ctrl->redirect($this);
                    break;
            }
        }

        $this->ctrl->redirect($this);
    }

    /**
     *
     */
    protected function applyContactsTableFilter()
    {
        if (!ilBuddySystem::getInstance()->isEnabled()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        require_once 'Services/Contact/BuddySystem/classes/tables/class.ilBuddySystemRelationsTableGUI.php';
        $table = new ilBuddySystemRelationsTableGUI($this, 'showContacts');

        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showContacts();
    }

    /**
     *
     */
    protected function resetContactsTableFilter()
    {
        if (!ilBuddySystem::getInstance()->isEnabled()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        require_once 'Services/Contact/BuddySystem/classes/tables/class.ilBuddySystemRelationsTableGUI.php';
        $table = new ilBuddySystemRelationsTableGUI($this, 'showContacts');

        $table->resetOffset();
        $table->resetFilter();

        $this->showContacts();
    }

    /**
     *
     */
    protected function showContacts()
    {
        if (!ilBuddySystem::getInstance()->isEnabled()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        $this->tabs_gui->activateSubTab('buddy_view_table');
        $this->activateTab('my_contacts');

        require_once 'Services/Contact/BuddySystem/classes/tables/class.ilBuddySystemRelationsTableGUI.php';
        $table = new ilBuddySystemRelationsTableGUI($this, 'showContacts');
        $table->populate();
        $this->tpl->setContent($table->getHTML());
        $this->tpl->show();
    }

    /**
     *
     */
    protected function mailToUsers()
    {
        if (!$this->rbacsystem->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId())) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        if (!isset($_POST['usr_id']) || !is_array($_POST['usr_id']) || 0 == count($_POST['usr_id'])) {
            ilUtil::sendInfo($this->lng->txt('mail_select_one_entry'));
            $this->showContacts();
            return true;
        }

        $usr_ids = (array) $_POST['usr_id'];

        $mail_data = $this->umail->getSavedData();
        if (!is_array($mail_data)) {
            $this->umail->savePostData($this->user->getId(), array(), '', '', '', '', '', '', '', '');
        }

        $logins = array();
        foreach ($usr_ids as $usr_id) {
            $logins[] = ilObjUser::_lookupLogin($usr_id);
        }
        $logins = array_filter($logins);

        if (count($logins) > 0) {
            $mail_data = $this->umail->appendSearchResult($logins, 'to');
            $this->umail->savePostData(
                $mail_data['user_id'],
                $mail_data['attachments'],
                $mail_data['rcp_to'],
                $mail_data['rcp_cc'],
                $mail_data['rcp_bcc'],
                $mail_data['m_type'],
                $mail_data['m_email'],
                $mail_data['m_subject'],
                $mail_data['m_message'],
                $mail_data['use_placeholders'],
                $mail_data['tpl_ctx_id'],
                $mail_data['tpl_ctx_params']
            );
        }

        ilUtil::redirect('ilias.php?baseClass=ilMailGUI&type=search_res');
    }

    /**
     * Last step of chat invitations
     * check access for every selected user and send invitation
     */
    public function submitInvitation()
    {
        if (!isset($_POST['usr_id']) || !strlen($_POST['usr_id'])) {
            ilUtil::sendInfo($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this);
        }

        if (!$_POST['room_id']) {
            ilUtil::sendInfo($this->lng->txt('select_one'));
            $_POST['usr_id'] = explode(',', $_POST['usr_id']);
            $this->inviteToChat();
            return;
        }

        // get selected users (comma seperated user id list)
        $usr_ids = explode(',', $_POST['usr_id']);

        // get selected chatroom from POST-String, format: "room_id , scope"
        $room_ids = explode(',', $_POST['room_id']);
        $room_id = (int) $room_ids[0];
        $scope = 0;

        if (count($room_ids) > 0) {
            $scope = (int) $room_ids[1];
        }

        include_once 'Modules/Chatroom/classes/class.ilChatroom.php';

        $room = ilChatroom::byRoomId((int) $room_id, true);
        $no_access = array();
        $no_login = array();
        $valid_users = array();
        $valid_user_to_login_map = array();

        foreach ($usr_ids as $usr_id) {
            $login = ilObjUser::_lookupLogin($usr_id);
            if (!strlen($login)) {
                $no_login[$usr_id] = $usr_id;
                continue;
            }

            $ref_id = $room->getRefIdByRoomId($room_id);

            if (
                !ilChatroom::checkPermissionsOfUser($usr_id, 'read', $ref_id) ||
                $room->isUserBanned($usr_id)
            ) {
                $no_access[$usr_id] = $login;
            } else {
                $valid_users[$usr_id] = $usr_id;
                $valid_user_to_login_map[$usr_id] = $login;
            }
        }

        if (count($no_access) || count($no_login)) {
            $message = '';

            if (count($no_access) > 0) {
                $message .= $this->lng->txt('chat_users_without_permission') . ':<br>';
                $list = '';

                foreach ($no_access as $usr_id => $login) {
                    $list .= '<li>' . $login . '</li>';
                }

                $message .= '<ul>';
                $message .= $list;
                $message .= '</ul>';
            }

            if (count($no_login)) {
                $message .= $this->lng->txt('chat_users_without_login') . ':<br>';
                $list = '';

                foreach ($no_login as $usr_id) {
                    $list .= '<li>' . $usr_id . '</li>';
                }

                $message .= '<ul>';
                $message .= $list;
                $message .= '</ul>';
            }

            ilUtil::sendFailure($message);
            $_POST['usr_id'] = $usr_ids;
            $this->inviteToChat();
            return;
        }

        $ref_id = $room->getRefIdByRoomId($room_id);

        require_once 'Services/Link/classes/class.ilLink.php';
        if ($scope) {
            $url = ilLink::_getStaticLink($ref_id, 'chtr', true, '_' . $scope);
        } else {
            $url = ilLink::_getStaticLink($ref_id, 'chtr');
        }
        $link = '<p><a target="chatframe" href="' . $url . '" title="' . $this->lng->txt('goto_invitation_chat') . '">' . $this->lng->txt('goto_invitation_chat') . '</a></p>';

        $userlist = array();
        foreach ($valid_users as $id) {
            $room->inviteUserToPrivateRoom($id, $scope);
            $room->sendInvitationNotification(
                null,
                $this->user->getId(),
                $id,
                (int) $scope,
                $url
            );
            $userlist[] = '<li>' . $valid_user_to_login_map[$id] . '</li>';
        }

        if ($userlist) {
            ilUtil::sendSuccess($this->lng->txt('chat_users_have_been_invited') . '<ul>' . implode('', $userlist) . '</ul>' . $link, true);
        }

        $this->ctrl->redirect($this);
    }

    /**
     * Send chat invitations to selected Users
     */
    protected function inviteToChat()
    {
        $this->tabs_gui->activateSubTab('buddy_view_table');
        $this->activateTab('my_contacts');

        $this->lng->loadLanguageModule('chatroom');

        if (!isset($_POST['usr_id']) || !is_array($_POST['usr_id']) || 0 == count($_POST['usr_id'])) {
            ilUtil::sendInfo($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this);
        }
        $usr_ids = $_POST['usr_id'];

        require_once 'Modules/Chatroom/classes/class.ilChatroom.php';

        $ilChatroom = new ilChatroom();
        $chat_rooms = $ilChatroom->getAccessibleRoomIdByTitleMap($this->user->getId());
        $subrooms = array();

        foreach ($chat_rooms as $room_id => $title) {
            $subrooms[] = $ilChatroom->getPrivateSubRooms($room_id, $this->user->getId());
        }

        include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('mail_invite_users_to_chat'));

        $psel = new ilSelectInputGUI($this->lng->txt('chat_select_room'), 'room_id');
        $options = array();

        asort($chat_rooms);
        foreach ($chat_rooms as $room_id => $room) {
            $ref_id = $room_id;

            if ($ilChatroom->isUserBanned($this->user->getId())) {
                continue;
            }

            $options[$ref_id] = $room;

            foreach ($subrooms as $subroom) {
                foreach ($subroom as $sub_id => $parent_id) {
                    if ($parent_id == $ref_id) {
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
        $this->tpl->show();
    }
}
