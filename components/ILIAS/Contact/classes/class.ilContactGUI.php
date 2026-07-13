<?php

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

declare(strict_types=1);

use ILIAS\UI\Component\Table\Action\Action;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\URI;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Component;
use ILIAS\Contact\BuddySystem\Tables\RelationsTable;
use ILIAS\User\Profile\PublicProfileGUI;

/**
* @author Jens Conze
* @ingroup ServicesMail
* @ilCtrl_Calls ilContactGUI: ilMailSearchCoursesGUI, ilMailSearchGroupsGUI, ilMailSearchLearningSequenceGUI, ilMailingListsGUI
* @ilCtrl_Calls ilContactGUI: ilUsersGalleryGUI, ILIAS\User\Profile\PublicProfileGUI
*/
class ilContactGUI implements ilCtrlSecurityInterface
{
    final public const string CONTACTS_VIEW_GALLERY = 'buddy_view_gallery';
    final public const string CONTACTS_VIEW_TABLE = 'buddy_view_table';

    private readonly \ILIAS\HTTP\GlobalHttpState $http;
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
    protected Refinery $refinery;
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\UI\Renderer $ui_renderer;
    private readonly RelationsTable $relations_table;
    /** @var array<string, string> */
    private array $view_mode_options = [
        self::CONTACTS_VIEW_TABLE => self::CONTACTS_VIEW_TABLE,
        self::CONTACTS_VIEW_GALLERY => self::CONTACTS_VIEW_GALLERY,
    ];

    public function __construct(
        string $format_mail_class = ilFormatMail::class,
        string $relations_table_class = RelationsTable::class
    ) {
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
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->ctrl->saveParameter($this, "mobj_id");

        $this->umail = new $format_mail_class($this->user->getId());
        $this->relations_table = new $relations_table_class(
            $this->ui_factory,
            $this->lng,
            $DIC->uiService(),
            $this->http,
            $this->linkToProfile(...)
        );
        $this->lng->loadLanguageModule('buddysystem');
    }

    public function getUnsafeGetCommands(): array
    {
        return [
          'updateState'
        ];
    }

    public function getSafePostCommands(): array
    {
        return [];
    }

    public function executeCommand(): bool
    {
        $this->showSubTabs();

        $forward_class = $this->ctrl->getNextClass($this) ?? '';

        $this->umail->persistToStage($this->user->getId(), '', '', '', '', '', null, false);

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

            case strtolower(PublicProfileGUI::class):
                $profile_gui = new PublicProfileGUI(
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

                if (in_array(strtolower($this->ctrl->getCmdClass() ?? ''), $galleryCmdClasses, true)) {
                    $mode_options = array_combine(
                        array_map(
                            fn(string $mode): string => $this->lng->txt($mode),
                            array_keys($this->view_mode_options)
                        ),
                        array_map(
                            function (string $mode): string {
                                $this->ctrl->setParameter($this, 'contacts_view', $mode);
                                $url = $this->ctrl->getFormAction($this, 'changeContactsView');
                                $this->ctrl->setParameter($this, 'contacts_view', null);

                                return $url;
                            },
                            array_keys($this->view_mode_options)
                        ),
                    );

                    $active_mode = strtolower($this->ctrl->getCmdClass() ?? '') === strtolower(ilUsersGalleryGUI::class)
                        ? self::CONTACTS_VIEW_GALLERY
                        : self::CONTACTS_VIEW_TABLE;

                    $sortViewControl = $this->ui_factory
                        ->viewControl()
                        ->mode($mode_options, $this->lng->txt($active_mode))
                        ->withActive($this->lng->txt($active_mode));
                    $this->toolbar->addComponent($sortViewControl);
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
            $this->tpl->setTitleIcon(ilUtil::getImagePath('standard/icon_cadm.svg'));

            $this->help->setScreenIdComponent('contacts');

            if (ilBuddySystem::getInstance()->isEnabled()) {
                $this->tabs_gui->addTab(
                    'my_contacts',
                    $this->lng->txt('my_contacts'),
                    $this->ctrl->getLinkTarget($this)
                );

                if (in_array(strtolower($this->ctrl->getCmdClass() ?? ''), $galleryCmdClasses, true)) {
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

        $contacts_view = $this->http->wrapper()->query()->retrieve(
            'contacts_view',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always(self::CONTACTS_VIEW_TABLE)
            ])
        );

        switch ($contacts_view) {
            case self::CONTACTS_VIEW_GALLERY:
                $this->ctrl->redirectByClass(ilUsersGalleryGUI::class);

                // no break
            case self::CONTACTS_VIEW_TABLE:
            default:
                $this->ctrl->redirect($this);
        }
    }

    protected function showContacts(): void
    {
        if (!ilBuddySystem::getInstance()->isEnabled()) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        $this->tabs_gui->activateSubTab('buddy_view_table');
        $this->activateTab('my_contacts');

        $content = $this->chatroomInvitationMessage();
        $action = $this->contactAction();
        $chat_allowed = (bool) (new ilSetting('chatroom'))->get('chat_enabled', '0');
        $mail_allowed = $this->rbacsystem->checkAccess(
            'internal_mail',
            ilMailGlobalServices::getMailObjectRefId()
        );

        $content = array_merge($content, $this->relations_table->build(array_merge(
            $chat_allowed ? ['chat' => $action('standard', 'invite_to_chat', 'inviteToChat')] : [],
            $mail_allowed ? ['mail' => $action('standard', 'send_mail', 'mailToUsers')] : [],
        ), $this->ctrl->getLinkTarget($this, 'showContacts'), $action));

        $this->tpl->setContent($this->ui_renderer->render($content));
        $this->tpl->printToStdout();
    }

    private function updateState(): void
    {
        $get = $this->http->wrapper()->query()->retrieve(...);

        $user_ids = $get('contact_user_ids', $this->refinery->byTrying([
            $this->refinery->null(),
            $this->refinery->kindlyTo()->listOf($this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
            ])),
            $this->refinery->custom()->transformation(
                fn($s): array => is_array($s) && join('', $s) === 'ALL_OBJECTS' ?
                    array_column(RelationsTable::data(), 'user_id') :
                    throw new Exception('Nope')
            ),
        ]));

        $action = $get('contact_action', $this->refinery->kindlyTo()->string());
        if (!$user_ids) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this);
        }

        if (in_array($action, ['inviteToChat', 'mailToUsers'], true)) {
            $this->$action($user_ids);
            return;
        }

        if ($action === 'unlink') {
            $this->ctrl->setParameterByClass(self::class, 'user_id', current($user_ids));
            $this->ctrl->redirectByClass(self::class, 'confirmUnlinkContact');
        }

        $this->updateRelationState(current($user_ids), $action);
    }

    private function confirmUnlinkContact(): void
    {
        $this->activateTab('my_contacts');
        $this->tabs_gui->activateSubTab('buddy_view_table');

        $user_id = $this->http->wrapper()->query()->retrieve('user_id', $this->refinery->kindlyTo()->int());

        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setHeaderText($this->lng->txt('buddy_confirm_unlink'));
        $confirmation_gui->addItem('user_id', (string) $user_id, ilObjUser::_lookupLogin($user_id));
        $confirmation_gui->setConfirm($this->lng->txt('confirm'), 'unlinkContact');
        $confirmation_gui->setCancel($this->lng->txt('cancel'), 'showContacts');
        $confirmation_gui->setFormAction($this->ctrl->getFormActionByClass(self::class, 'showContacts'));

        $this->tpl->setContent($confirmation_gui->getHTML());
        $this->tpl->printToStdout();
    }

    private function unlinkContact(): void
    {
        $user_id = $this->http->wrapper()->post()->retrieve('user_id', $this->refinery->kindlyTo()->int());

        $this->updateRelationState($user_id, 'unlink');
        $this->ctrl->redirectByClass(self::class, 'showContacts');
    }

    private function updateRelationState(int $user, string $action): void
    {
        $login = ilObjUser::_lookupLogin($user);
        if (ilObjUser::_isAnonymous($user)) {
            throw new ilBuddySystemException('You cannot perform a state transition for the anonymous user');
        }
        if (!$login) {
            throw new ilBuddySystemException(sprintf(
                'You cannot perform a state transition for a non existing user (id: %s)',
                $user
            ));
        }
        $list = ilBuddyList::getInstanceByGlobalUser();
        $relation = $list->getRelationByUserId($user);
        if (
            $relation->isUnlinked() &&
            !ilUtil::yn2tf((string) ilObjUser::_lookupPref($relation->getBuddyUsrId(), 'bs_allow_to_contact_me'))
        ) {
            throw new ilException('The requested user does not want to get contact requests');
        }

        try {
            $list->$action($relation);
        } catch (ilBuddySystemRelationStateAlreadyGivenException|ilBuddySystemRelationStateTransitionException $e) {
            $this->tpl->setOnScreenMessage('failure', sprintf($this->lng->txt($e->getMessage()), $login), true);
        } catch (Exception) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('buddy_bs_action_not_possible'), true);
        }

        $this->ctrl->redirect($this, 'showContacts');
    }

    /**
     * @param non-empty-array<int> $usr_ids
     */
    protected function mailToUsers(array $usr_ids): void
    {
        if (!$this->rbacsystem->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId())) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        $logins = [];
        $mail_data = $this->umail->retrieveFromStage();
        foreach ($usr_ids as $usr_id) {
            $login = ilObjUser::_lookupLogin($usr_id);
            if (!$this->umail->existsRecipient($login, (string) $mail_data['rcp_to'])) {
                $logins[] = $login;
            }
        }
        $logins = array_filter($logins);

        if ($logins !== []) {
            $mail_data = $this->umail->appendSearchResult($logins, 'to');
            $this->umail->persistToStage(
                (int) $mail_data['user_id'],
                $mail_data['rcp_to'],
                $mail_data['rcp_cc'],
                $mail_data['rcp_bcc'],
                $mail_data['m_subject'],
                $mail_data['m_message'],
                $mail_data['attachments'],
                $mail_data['use_placeholders'],
                $mail_data['tpl_ctx_id'],
                $mail_data['tpl_ctx_params']
            );
        }

        $this->ctrl->redirectToURL('ilias.php?baseClass=ilMailGUI&type=search_res');
    }

    public function submitInvitation(): void
    {
        try {
            $usr_ids = $this->http->wrapper()->post()->retrieve('usr_ids', $this->refinery->in()->series([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->custom()->transformation(fn(string $s) => explode(',', $s)),
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                $this->refinery->custom()->constraint(fn(array $a) => $a !== [], fn() => 'Empty array.'),
            ]));
        } catch (Exception) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this);
        }

        try {
            $room_id = $this->http->wrapper()->post()->retrieve('room_id', $this->refinery->kindlyTo()->int());
        } catch (Exception) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'));
            $this->inviteToChat($usr_ids);
            return;
        }

        $room = ilChatroom::byRoomId($room_id, true);

        $no_access = [];
        $no_login = [];
        $valid_users = [];
        $ref_id = $room->getRefIdByRoomId($room_id);

        foreach ($usr_ids as $usr_id) {
            $login = ilObjUser::_lookupLogin($usr_id);
            if ($login === '') {
                $no_login[] = $usr_id;
            } elseif (
                !ilChatroom::checkPermissionsOfUser($usr_id, 'read', $ref_id) ||
                $room->isUserBanned($usr_id)
            ) {
                $no_access[] = $login;
            } else {
                $valid_users[] = $usr_id;
            }
        }

        $message = join('', [
            $this->asErrorMessage($no_access, $this->lng->txt('chat_users_without_permission')),
            $this->asErrorMessage($no_login, $this->lng->txt('chat_users_without_login')),
        ]);

        if ($message !== '') {
            $this->tpl->setOnScreenMessage('failure', $message);
            $this->inviteToChat($usr_ids);
            return;
        }

        foreach ($valid_users as $id) {
            $room->sendInvitationNotification(
                null,
                $this->user->getId(),
                $id,
                ilLink::_getStaticLink($ref_id, 'chtr')
            );
        }

        $this->ctrl->setParameter($this, 'inv_room_ref_id', $ref_id);
        $this->ctrl->setParameter($this, 'inv_usr_ids', implode(',', $valid_users));

        $this->ctrl->redirect($this);
    }

    /**
     * @param non-empty-array<int> $usr_ids
     */
    protected function inviteToChat(array $usr_ids): void
    {
        $this->tabs_gui->activateSubTab('buddy_view_table');
        $this->activateTab('my_contacts');

        $this->lng->loadLanguageModule('chatroom');

        $chat_rooms = (new ilChatroom())->getAccessibleRoomIdByTitleMap($this->user->getId());

        $options = array_filter(
            $chat_rooms,
            fn(int $room_id) => !(ilChatroom::byRoomId($room_id))->isUserBanned($this->user->getId()),
            ARRAY_FILTER_USE_KEY
        );

        asort($options);

        $this->tpl->setTitle($this->lng->txt('mail_invite_users_to_chat'));
        $this->tpl->setContent($this->inviteToChatForm($options, $usr_ids)->getHTML());
        $this->tpl->printToStdout();
    }

    /**
     * @param list<string|int> $array
     */
    private function asErrorMessage(array $array, string $title): string
    {
        if ($array === []) {
            return '';
        }

        $items = array_map(
            fn($s) => '<li>' . htmlspecialchars((string) $s) . '</li>',
            $array
        );

        return sprintf(
            '%s<br><ul>%s</ul>',
            $title,
            join('', $items)
        );
    }

    /**
     * @param array<string, string> $options
     * @param list<int> $usr_ids
     */
    private function inviteToChatForm(array $options, array $usr_ids): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('mail_invite_users_to_chat'));
        $form->addCommandButton('submitInvitation', $this->lng->txt('submit'));
        $form->addCommandButton('showContacts', $this->lng->txt('cancel'));
        $form->setFormAction($this->ctrl->getFormAction($this, 'showContacts'));

        $sel = new ilSelectInputGUI($this->lng->txt('chat_select_room'), 'room_id');
        $sel->setOptions($options);
        $form->addItem($sel);

        $hidden = new ilHiddenInputGUI('usr_ids');
        $hidden->setValue(implode(',', $usr_ids));
        $form->addItem($hidden);

        return $form;
    }

    /**
     * @return Component[]
     */
    private function chatroomInvitationMessage(): array
    {
        $has = $this->http->wrapper()->query()->has(...);
        if (!$has('inv_room_ref_id') || !$has('inv_usr_ids')) {
            return [];
        }

        $inv_room_ref_id = $this->http->wrapper()->query()->retrieve(
            'inv_room_ref_id',
            $this->refinery->kindlyTo()->int()
        );
        $inv_usr_ids = $this->http->wrapper()->query()->retrieve(
            'inv_usr_ids',
            $this->refinery->in()->series([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->custom()->transformation(fn(string $s): array => explode(',', $s)),
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                $this->refinery->custom()->constraint(fn(array $a): bool => $a !== [], fn(): string => 'Empty array.'),
            ])
        );

        $userlist = array_map(ilObjUser::_lookupLogin(...), $inv_usr_ids);

        $url = ilLink::_getStaticLink($inv_room_ref_id, 'chtr');

        return [
            $this->ui_factory->messageBox()->success(
                $this->lng->txt('chat_users_have_been_invited') . $this->ui_renderer->render(
                    $this->ui_factory->listing()->unordered($userlist)
                )
            )->withButtons([
                $this->ui_factory->button()->standard($this->lng->txt('goto_invitation_chat'), $url)
            ])
        ];
    }

    /**
     * @return Closure(string, string, string): Action
     */
    private function contactAction(): Closure
    {
        $url = new URLBuilder(new URI(rtrim(ILIAS_HTTP_PATH, '/') . '/' . $this->ctrl->getLinkTarget($this, 'updateState')));
        [$url, $p, $token] = $url->acquireParameters(['contact'], 'action', 'user_ids');

        return fn(string $type, string $lang_var, string $param): Action => $this->ui_factory->table()->action()->$type(
            $this->lng->txt($lang_var),
            $url->withParameter($p, $param),
            $token
        );
    }

    private function linkToProfile(int $user, string $label): string
    {
        $public_profile = ilObjUser::_lookupPref($user, 'public_profile');
        if (($this->user->isAnonymous() || $public_profile !== 'y') && $public_profile !== 'g') {
            return $label;
        }

        $this->ctrl->setParameterByClass(PublicProfileGUI::class, 'user', (string) $user);
        $profile_target = $this->ctrl->getLinkTargetByClass(
            PublicProfileGUI::class,
            'getHTML'
        );

        return $this->ui_renderer->render($this->ui_factory->link()->standard($label, $profile_target));
    }
}
