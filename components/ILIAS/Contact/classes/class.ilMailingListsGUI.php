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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Contact\MailingLists\MailingListsTable;
use ILIAS\Contact\MailingLists\MailingListsMembersTable;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;

class ilMailingListsGUI
{
    private readonly \ILIAS\HTTP\GlobalHttpState $http;
    private readonly Refinery $refinery;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilCtrlInterface $ctrl;
    private readonly ilLanguage $lng;
    private readonly ilObjUser $user;
    private readonly ilErrorHandling $error;
    private readonly ilToolbarGUI $toolbar;
    private readonly ilRbacSystem $rbacsystem;
    private readonly ilFormatMail $umail;
    private readonly ilMailingLists $mlists;
    private StandardForm $form;
    private readonly \ILIAS\UI\Factory $ui_factory;
    private readonly \ILIAS\UI\Renderer $ui_renderer;
    private readonly ilTabsGUI $tabs;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->user = $DIC['ilUser'];
        $this->error = $DIC['ilErr'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->tabs = $DIC->tabs();

        $this->umail = new ilFormatMail($this->user->getId());
        $this->mlists = new ilMailingLists($this->user);
        $this->mlists->setCurrentMailingList($this->getQueryMailingListId());

        $this->ctrl->saveParameter($this, 'mobj_id');
        $this->ctrl->saveParameter($this, 'ref');

        $this->lng->loadLanguageModule('mail');
    }

    private function getQueryMailingListId(): int
    {
        return $this->http->wrapper()->query()->retrieve(
            'ml_id',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(
                    current(
                        $this->http->wrapper()->query()->retrieve(
                            'contact_mailinglist_list_ml_ids',
                            $this->refinery->byTrying([
                                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                                $this->refinery->always([0])
                            ])
                        )
                    ) ?: 0
                )
            ])
        );
    }

    public function executeCommand(): bool
    {
        if (
            !ilBuddySystem::getInstance()->isEnabled() ||
            (
                count(ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations()) === 0 &&
                !$this->mlists->hasAny()
            )
        ) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }

        if (!($cmd = $this->ctrl->getCmd())) {
            $cmd = 'showMailingLists';
        }

        $this->$cmd();

        return true;
    }

    private function handleMailingListMemberActions(): void
    {
        $action = $this->http->wrapper()->query()->retrieve(
            'contact_mailinglist_members_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );
        match ($action) {
            'confirmDeleteMembers' => $this->confirmDeleteMembers(),
            default => $this->ctrl->redirect($this, 'showMailingLists'),
        };
    }

    private function handleMailingListActions(): void
    {
        $action = $this->http->wrapper()->query()->retrieve(
            'contact_mailinglist_list_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );
        match ($action) {
            'mailToList' => $this->mailToList(),
            'confirmDelete' => $this->confirmDelete(),
            'showMembersList' => $this->showMembersList(),
            'showForm' => $this->showForm(),
            default => $this->ctrl->redirect($this, 'showMailingLists'),
        };
    }

    /**
     * @return list<int>|list<string>
     */
    private function getMailingListIdsFromRequest(): array
    {
        if ($this->http->wrapper()->query()->has('ml_id')) {
            $ml_ids = [
                $this->http->wrapper()->query()->retrieve('ml_id', $this->refinery->kindlyTo()->int())
            ];
        } elseif ($this->http->wrapper()->post()->has('ml_id')) {
            $ml_ids = $this->http->wrapper()->post()->retrieve(
                'ml_id',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        } else {
            $ml_ids = $this->http->wrapper()->query()->retrieve(
                'contact_mailinglist_list_ml_ids',
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                    $this->refinery->always([])
                ])
            );
        }

        return array_filter($ml_ids);
    }

    public function confirmDelete(): bool
    {
        $ml_ids = $this->getMailingListIdsFromRequest();
        if ($ml_ids === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one_entry'));
            $this->showMailingLists();
            return true;
        }

        if ((string) current($ml_ids) === 'ALL_OBJECTS') {
            $entries = $this->mlists->getAll();
        } else {
            $entries = $this->mlists->getSelected(
                array_map(intval(...), $ml_ids)
            );
        }

        $c_gui = new ilConfirmationGUI();

        $c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDelete'));
        $c_gui->setHeaderText($this->lng->txt('mail_sure_delete_entry'));
        $c_gui->setCancel($this->lng->txt('cancel'), 'showMailingLists');
        $c_gui->setConfirm($this->lng->txt('confirm'), 'performDelete');

        foreach ($entries as $entry) {
            $c_gui->addItem('ml_id[]', (string) $entry->getId(), $entry->getTitle());
        }

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->setContent($c_gui->getHTML());
        $this->tpl->printToStdout();

        return true;
    }

    public function performDelete(): bool
    {
        if ($this->http->wrapper()->post()->has('ml_id')) {
            $ml_ids = array_filter(
                $this->http->wrapper()->post()->retrieve(
                    'ml_id',
                    $this->refinery->kindlyTo()->listOf(
                        $this->refinery->kindlyTo()->int()
                    )
                )
            );

            $counter = 0;
            foreach ($ml_ids as $id) {
                if ($this->mlists->isOwner($id, $this->user->getId())) {
                    $this->mlists->get($id)->delete();
                    ++$counter;
                }
            }

            if ($counter !== 0) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_deleted_entry'));
            }
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_delete_error'));
        }

        $this->showMailingLists();

        return true;
    }

    public function mailToList(): bool
    {
        // check if current user may send mails
        $mail = new ilMail($this->user->getId());
        $mailing_allowed = $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId());

        if (!$mailing_allowed) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'));
            return true;
        }

        $ml_ids = $this->getMailingListIdsFromRequest();
        if ($ml_ids === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one_entry'));
            $this->showMailingLists();
            return true;
        }

        if ((string) current($ml_ids) === 'ALL_OBJECTS') {
            $entries = $this->mlists->getAll();
            $ml_ids = [];
            foreach ($entries as $entry) {
                $ml_ids[] = $entry->getId();
            }
        } else {
            $ml_ids = array_map(intval(...), $ml_ids);
        }

        $mail_data = $this->umail->retrieveFromStage();
        $lists = [];
        foreach ($ml_ids as $id) {
            if ($this->mlists->isOwner($id, $this->user->getId()) &&
                !$this->umail->existsRecipient('#il_ml_' . $id, (string) $mail_data['rcp_to'])) {
                $lists['#il_ml_' . $id] = '#il_ml_' . $id;
            }
        }

        if ($lists !== []) {
            $mail_data = $this->umail->appendSearchResult(array_values($lists), 'to');
            $this->umail->persistToStage(
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

        ilUtil::redirect('ilias.php?baseClass=ilMailGUI&type=search_res');

        return true;
    }

    public function showMailingLists(): bool
    {
        $mail = new ilMail($this->user->getId());

        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('create'),
                $this->ctrl->getLinkTarget($this, 'showForm')
            )
        );

        $tbl = new MailingListsTable(
            $this->mlists,
            $this->ctrl,
            $this->lng,
            $this->ui_factory,
            $this->http
        );
        $tbl->setMailingAllowed($this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId()));

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->setContent($this->ui_renderer->render($tbl->getComponent()));
        $this->tpl->printToStdout();

        return true;
    }

    public function cancel(): void
    {
        if (
            $this->http->wrapper()->query()->has('ref') &&
            $this->http->wrapper()->query()->retrieve('ref', $this->refinery->kindlyTo()->string()) === 'mail') {
            $this->ctrl->returnToParent($this);
        }

        $this->showMailingLists();
    }

    public function saveForm(): void
    {
        if ($this->mlists->getCurrentMailingList() && $this->mlists->getCurrentMailingList()->getId()) {
            if (!$this->mlists->isOwner($this->mlists->getCurrentMailingList()->getId(), $this->user->getId())) {
                $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
            }

            $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
        }
        $this->initForm();

        $form = $this->form->withRequest($this->http->request());

        if (!$form->getError()) {
            $data = $form->getData();
            $this->mlists->getCurrentMailingList()->setTitle(
                $data['title']
            );
            $this->mlists->getCurrentMailingList()->setDescription(
                $data['description']
            );

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
            if ($this->mlists->getCurrentMailingList()->getId() > 0) {
                $this->mlists->getCurrentMailingList()->setChangedate(date('Y-m-d H:i:s'));
                $this->mlists->getCurrentMailingList()->update();
                $this->ctrl->redirect($this, 'showMailingLists');
            } else {
                $this->mlists->getCurrentMailingList()->setCreatedate(date('Y-m-d H:i:s'));
                $this->mlists->getCurrentMailingList()->insert();

                $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
                $this->ctrl->redirect($this, 'showMembersList');
            }
        }

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->setContent($this->ui_renderer->render([
            'mailing_lists' => $form
        ]));
        $this->tpl->printToStdout();
    }

    private function initForm(): void
    {
        $title = $this->ui_factory->input()->field()->text(
            $this->lng->txt('title')
        )
        ->withRequired(true)
        ->withValue($this->mlists->getCurrentMailingList() ? $this->mlists->getCurrentMailingList()->getTitle() : '');

        $description = $this->ui_factory->input()->field()->textarea(
            $this->lng->txt('description')
        )
        ->withValue(
            $this->mlists->getCurrentMailingList() ? $this->mlists->getCurrentMailingList()->getDescription() : ''
        );

        $this->form = $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'saveForm'),
            [
                'title' => $title,
                'description' => $description
            ]
        );
    }

    public function showForm(): void
    {
        if ($this->mlists->getCurrentMailingList() && $this->mlists->getCurrentMailingList()->getId()) {
            if (!$this->mlists->isOwner($this->mlists->getCurrentMailingList()->getId(), $this->user->getId())) {
                $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
            }

            $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
        }
        $this->initForm();

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->setContent($this->ui_renderer->render([
            'mailing_lists' => $this->form
        ]));
        $this->tpl->printToStdout();
    }

    public function showMembersList(): bool
    {
        if (!$this->mlists->getCurrentMailingList() || $this->mlists->getCurrentMailingList()->getId() === 0) {
            $this->showMailingLists();

            return true;
        }

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, 'showMailingLists')
        );

        $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());

        $available_usr_ids = array_diff(
            array_map(
                static function (ilBuddySystemRelation $relation): int {
                    return $relation->getBuddyUsrId();
                },
                ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations()->toArray()
            ),
            array_map(
                static function (array $entry): int {
                    return $entry['usr_id'];
                },
                $this->mlists->getCurrentMailingList()->getAssignedEntries()
            ),
        );

        if ($available_usr_ids !== []) {
            $this->toolbar->addComponent(
                $this->ui_factory->button()->standard(
                    $this->lng->txt('add'),
                    $this->ctrl->getLinkTarget($this, 'showAssignmentForm')
                )
            );
        }

        $tbl = new MailingListsMembersTable(
            $this->mlists->getCurrentMailingList(),
            $this->ctrl,
            $this->lng,
            $this->ui_factory,
            $this->http
        );

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->setContent($this->ui_renderer->render($tbl->getComponent()));
        $this->tpl->printToStdout();

        return true;
    }

    public function confirmDeleteMembers(): bool
    {
        $requested_record_ids = $this->http->wrapper()->query()->retrieve(
            'contact_mailinglist_members_entry_ids',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        if ($requested_record_ids === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one_entry'));
            $this->showMembersList();

            return true;
        }

        if ((string) current($requested_record_ids) === 'ALL_OBJECTS') {
            $assigned_entries = $this->mlists->getCurrentMailingList()->getAssignedEntries();
            $requested_record_ids = [];
            foreach ($assigned_entries as $entry) {
                $requested_record_ids[] = $entry['a_id'];
            }
        } else {
            $requested_record_ids = array_map(intval(...), $requested_record_ids);
        }

        $c_gui = new ilConfirmationGUI();
        $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
        $c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDeleteMembers'));
        $c_gui->setHeaderText($this->lng->txt('mail_sure_remove_user'));
        $c_gui->setCancel($this->lng->txt('cancel'), 'showMembersList');
        $c_gui->setConfirm($this->lng->txt('confirm'), 'performDeleteMembers');

        $assigned_entries = $this->mlists->getCurrentMailingList()->getAssignedEntries();
        $usr_ids = array_map(static fn(array $entry): int => $entry['usr_id'], $assigned_entries);
        $names = ilUserUtil::getNamePresentation($usr_ids, false, false, '', false, false, false);

        foreach ($assigned_entries as $entry) {
            if (in_array($entry['a_id'], $requested_record_ids, true)) {
                $c_gui->addItem('a_id[]', (string) $entry['a_id'], $names[$entry['usr_id']]);
            }
        }

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->setContent($c_gui->getHTML());
        $this->tpl->printToStdout();

        return true;
    }

    public function performDeleteMembers(): bool
    {
        if (!$this->mlists->isOwner($this->mlists->getCurrentMailingList()->getId(), $this->user->getId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (
            $this->http->wrapper()->post()->has('a_id') &&
            ($requested_entry_ids = $this->http->wrapper()->post()->retrieve(
                'a_id',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            )) !== []
        ) {
            $assigned_entries = $this->mlists->getCurrentMailingList()->getAssignedEntries();
            foreach ($requested_entry_ids as $id) {
                if (isset($assigned_entries[$id])) {
                    $this->mlists->getCurrentMailingList()->deleteEntry($id);
                }
            }
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_success_removed_user'));
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_delete_error'));
        }

        $this->showMembersList();

        return true;
    }

    protected function getAssignmentForm(): ?StandardForm
    {
        $options = [];
        $options[''] = $this->lng->txt('please_select');

        $relations = ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations();
        $names = ilUserUtil::getNamePresentation(
            array_keys($relations->toArray()),
            false,
            false,
            '',
            false,
            false,
            false
        );
        foreach ($relations as $relation) {
            /** @var ilBuddySystemRelation $relation */
            $options[$relation->getBuddyUsrId()] = $names[$relation->getBuddyUsrId()];
        }

        $assigned_entries = $this->mlists->getCurrentMailingList()->getAssignedEntries();
        foreach ($assigned_entries as $assigned_entry) {
            if (array_key_exists($assigned_entry['usr_id'], $options)) {
                unset($options[$assigned_entry['usr_id']]);
            }
        }

        if (count($options) > 1) {
            $user_select = $this->ui_factory->input()->field()->select(
                $this->lng->txt('mail_entry_of_contacts'),
                $options
            );


            $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
            $form = $this->ui_factory->input()->container()->form()->standard(
                $this->ctrl->getFormAction($this, 'saveAssignmentForm'),
                ['usr_id' => $user_select]
            );

            return $form;
        }

        if (count($options) === 1 && count($relations) > 0) {
            $this->tpl->setOnScreenMessage(
                'info',
                $this->lng->txt('mail_mailing_lists_all_contact_entries_assigned'),
                true
            );
            $this->ctrl->redirect($this, 'showMembersList');
        } elseif (count($relations) === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_mailing_lists_no_contact_entries'), true);
            $this->ctrl->redirect($this, 'showMembersList');
        }

        return null;
    }

    public function saveAssignmentForm(): bool
    {
        if (!$this->mlists->isOwner($this->mlists->getCurrentMailingList()->getId(), $this->user->getId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getAssignmentForm();
        if (!$form) {
            $this->showAssignmentForm($form);

            return true;
        }

        $form = $form->withRequest($this->http->request());
        if ($form->getError()) {
            $this->showAssignmentForm($form);

            return true;
        }
        $data = $form->getData();

        if (
            ilBuddyList::getInstanceByGlobalUser()->getRelationByUserId(
                $this->refinery->kindlyTo()->int()->transform($data['usr_id'])
            )->isLinked()
        ) {
            $this->mlists->getCurrentMailingList()->assignUser(
                $this->refinery->kindlyTo()->int()->transform($data['usr_id'])
            );
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
            $this->showMembersList();

            return true;
        }

        $this->showAssignmentForm($form);

        return true;
    }

    public function showAssignmentForm(?StandardForm $form = null): bool
    {
        if (!$this->mlists->getCurrentMailingList() || $this->mlists->getCurrentMailingList()->getId() === 0) {
            $this->showMembersList();

            return true;
        }

        if (!$this->mlists->isOwner($this->mlists->getCurrentMailingList()->getId(), $this->user->getId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if (!$form instanceof StandardForm) {
            $this->ctrl->redirect($this, 'showMembersList');
        }

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->setContent($this->ui_renderer->render($form));
        $this->tpl->printToStdout();

        return true;
    }
}
