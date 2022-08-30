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

use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailingListsGUI
{
    private \ILIAS\HTTP\GlobalHttpState $http;
    private Refinery $refinery;
    private ilGlobalTemplateInterface $tpl;
    private ilCtrlInterface $ctrl;
    private ilLanguage $lng;
    private ilObjUser $user;
    private ilErrorHandling $error;
    private ilToolbarGUI $toolbar;
    private ilRbacSystem $rbacsystem;
    private ilFormatMail $umail;
    private ilMailingLists $mlists;
    private ilPropertyFormGUI $form_gui;

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

        $this->umail = new ilFormatMail($this->user->getId());
        $this->mlists = new ilMailingLists($this->user);
        $this->mlists->setCurrentMailingList(
            $this->http->wrapper()->query()->has('ml_id')
                ? $this->http->wrapper()->query()->retrieve('ml_id', $this->refinery->kindlyTo()->int())
                : 0
        );

        $this->ctrl->saveParameter($this, 'mobj_id');
        $this->ctrl->saveParameter($this, 'ref');
    }

    public function executeCommand(): bool
    {
        if (
            !ilBuddySystem::getInstance()->isEnabled() ||
            (
                0 === count(ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations()) &&
                $this->mlists->hasAny()
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

    /**
     * @return int[]
     */
    private function getMailingListIdsFromRequest(): array
    {
        $ml_ids = [];
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

        $c_gui = new ilConfirmationGUI();

        $c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDelete'));
        $c_gui->setHeaderText($this->lng->txt('mail_sure_delete_entry'));
        $c_gui->setCancel($this->lng->txt('cancel'), 'showMailingLists');
        $c_gui->setConfirm($this->lng->txt('confirm'), 'performDelete');

        $entries = $this->mlists->getSelected($ml_ids);
        foreach ($entries as $entry) {
            $c_gui->addItem('ml_id[]', (string) $entry->getId(), $entry->getTitle());
        }

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_list.html', 'Services/Contact');
        $this->tpl->setVariable('DELETE_CONFIRMATION', $c_gui->getHTML());

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
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_deleted_entry'));
            }
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_delete_error'));
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

        $mail_data = $this->umail->getSavedData();
        if (!is_array($mail_data)) {
            $this->umail->savePostData($this->user->getId(), [], '', '', '', '', '', false);
        }

        $lists = [];
        foreach ($ml_ids as $id) {
            if ($this->mlists->isOwner($id, $this->user->getId()) &&
                !$this->umail->existsRecipient('#il_ml_' . $id, (string) $mail_data['rcp_to'])) {
                $lists[] = '#il_ml_' . $id;
            }
        }

        if ($lists !== []) {
            $mail_data = $this->umail->appendSearchResult($lists, 'to');
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

        ilUtil::redirect("ilias.php?baseClass=ilMailGUI&type=search_res");

        return true;
    }

    public function showMailingLists(): bool
    {
        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_list.html', 'Services/Contact');

        // check if current user may send mails
        $mail = new ilMail($this->user->getId());
        $mailing_allowed = $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId());

        $tbl = new ilMailingListsTableGUI($this, 'showMailingLists');

        $create_btn = ilLinkButton::getInstance();
        $create_btn->setCaption('create');
        $create_btn->setUrl($this->ctrl->getLinkTarget($this, 'showForm'));
        $this->toolbar->addButtonInstance($create_btn);

        $result = [];
        $entries = $this->mlists->getAll();
        if ($entries !== []) {
            $tbl->enable('select_all');
            $counter = 0;

            foreach ($entries as $entry) {
                if ($entry->getMode() === ilMailingList::MODE_TEMPORARY) {
                    continue;
                }

                $result[$counter]['check'] = ilLegacyFormElementsUtil::formCheckbox(false, 'ml_id[]', (string) $entry->getId());
                $result[$counter]['title'] = $entry->getTitle() . " [#il_ml_" . $entry->getId() . "]";
                $result[$counter]['description'] = $entry->getDescription();
                $result[$counter]['members'] = count($entry->getAssignedEntries());

                $this->ctrl->setParameter($this, 'ml_id', $entry->getId());

                $current_selection_list = new ilAdvancedSelectionListGUI();
                $current_selection_list->setListTitle($this->lng->txt("actions"));
                $current_selection_list->setId("act_" . $counter);

                $current_selection_list->addItem(
                    $this->lng->txt("edit"),
                    '',
                    $this->ctrl->getLinkTarget($this, "showForm")
                );
                $current_selection_list->addItem(
                    $this->lng->txt("members"),
                    '',
                    $this->ctrl->getLinkTarget($this, "showMembersList")
                );
                if ($mailing_allowed) {
                    $current_selection_list->addItem(
                        $this->lng->txt("send_mail_to"),
                        '',
                        $this->ctrl->getLinkTarget($this, "mailToList")
                    );
                }
                $current_selection_list->addItem(
                    $this->lng->txt("delete"),
                    '',
                    $this->ctrl->getLinkTarget($this, "confirmDelete")
                );

                $result[$counter]['COMMAND_SELECTION_LIST'] = $current_selection_list->getHTML();
                ++$counter;
            }

            if ($mailing_allowed) {
                $tbl->addMultiCommand('mailToList', $this->lng->txt('send_mail_to'));
            }
            $tbl->addMultiCommand('confirmDelete', $this->lng->txt('delete'));
        } else {
            $tbl->disable('header');
            $tbl->disable('footer');
        }

        $tbl->setData($result);

        if (
            $this->http->wrapper()->query()->has('ref') &&
            $this->http->wrapper()->query()->retrieve('ref', $this->refinery->kindlyTo()->string()) === 'mail') {
            $tbl->addCommandButton('cancel', $this->lng->txt('cancel'));
        }

        $this->tpl->setVariable('MAILING_LISTS', $tbl->getHTML());
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
            $this->initForm();
        } else {
            $this->initForm();
        }

        if ($this->form_gui->checkInput()) {
            $this->mlists->getCurrentMailingList()->setTitle(
                $this->form_gui->getInput('title')
            );
            $this->mlists->getCurrentMailingList()->setDescription(
                $this->form_gui->getInput('description')
            );
            if ($this->mlists->getCurrentMailingList()->getId() !== 0) {
                $this->mlists->getCurrentMailingList()->setChangedate(date('Y-m-d H:i:s'));
                $this->mlists->getCurrentMailingList()->update();
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
            } else {
                $this->mlists->getCurrentMailingList()->setCreatedate(date('Y-m-d H:i:s'));
                $this->mlists->getCurrentMailingList()->insert();
                $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
                $this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'saveForm'));

                $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
                $this->ctrl->redirect($this, 'showMembersList');
            }
        }

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_form.html', 'Services/Contact');

        $this->form_gui->setValuesByPost();

        $this->tpl->setVariable('FORM', $this->form_gui->getHTML());
        $this->tpl->printToStdout();
    }

    private function initForm(): void
    {
        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'saveForm'));
        $this->form_gui->setTitle($this->lng->txt('mail_mailing_list'));
        $titleGui = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $titleGui->setRequired(true);
        $this->form_gui->addItem($titleGui);
        $descriptionGui = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
        $descriptionGui->setCols(40);
        $descriptionGui->setRows(8);
        $this->form_gui->addItem($descriptionGui);
        $this->form_gui->addCommandButton('saveForm', $this->lng->txt('save'));
        $this->form_gui->addCommandButton('showMailingLists', $this->lng->txt('cancel'));
    }

    private function setValuesByObject(): void
    {
        $this->form_gui->setValuesByArray([
            'title' => $this->mlists->getCurrentMailingList()->getTitle(),
            'description' => $this->mlists->getCurrentMailingList()->getDescription()
        ]);
    }

    private function setDefaultValues(): void
    {
        $this->form_gui->setValuesByArray([
            'title' => '',
            'description' => ''
        ]);
    }

    public function showForm(): void
    {
        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_form.html', 'Services/Contact');

        if ($this->mlists->getCurrentMailingList() && $this->mlists->getCurrentMailingList()->getId()) {
            if (!$this->mlists->isOwner($this->mlists->getCurrentMailingList()->getId(), $this->user->getId())) {
                $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
            }

            $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
            $this->initForm();
            $this->setValuesByObject();
        } else {
            $this->initForm();
            $this->setDefaultValues();
        }

        $this->tpl->setVariable('FORM', $this->form_gui->getHTML());
        $this->tpl->printToStdout();
    }

    public function showMembersList(): bool
    {
        if ($this->mlists->getCurrentMailingList()->getId() === 0) {
            $this->showMailingLists();
            return true;
        }

        $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.mail_mailing_lists_members.html',
            'Services/Contact'
        );

        $tbl = new ilMailingListsMembersTableGUI($this, 'showMembersList', $this->mlists->getCurrentMailingList());
        $result = [];

        $availale_usr_ids = array_diff(
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

        if ($availale_usr_ids !== []) {
            $create_btn = ilLinkButton::getInstance();
            $create_btn->setCaption('add');
            $create_btn->setUrl($this->ctrl->getLinkTarget($this, 'showAssignmentForm'));
            $this->toolbar->addButtonInstance($create_btn);
        }

        $assigned_entries = $this->mlists->getCurrentMailingList()->getAssignedEntries();
        if ($assigned_entries !== []) {
            $tbl->enable('select_all');
            $tbl->setSelectAllCheckbox('a_id');

            $usr_ids = [];
            foreach ($assigned_entries as $entry) {
                $usr_ids[] = $entry['usr_id'];
            }

            $names = ilUserUtil::getNamePresentation($usr_ids, false, false, '', false, false, false);

            $counter = 0;
            foreach ($assigned_entries as $entry) {
                $result[$counter]['check'] = ilLegacyFormElementsUtil::formCheckbox(false, 'a_id[]', (string) $entry['a_id']);
                $result[$counter]['user'] = $names[$entry['usr_id']];
                ++$counter;
            }

            $tbl->addMultiCommand('confirmDeleteMembers', $this->lng->txt('delete'));
        } else {
            $tbl->disable('header');
            $tbl->disable('footer');

            $tbl->setNoEntriesText($this->lng->txt('mail_search_no'));
        }

        $tbl->setData($result);

        $this->tpl->setVariable('MEMBERS_LIST', $tbl->getHTML());
        $this->tpl->printToStdout();
        return true;
    }

    public function confirmDeleteMembers(): bool
    {
        if (!$this->http->wrapper()->post()->has('a_id')) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one_entry'));
            $this->showMembersList();
            return true;
        }

        $c_gui = new ilConfirmationGUI();
        $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
        $c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDeleteMembers'));
        $c_gui->setHeaderText($this->lng->txt('mail_sure_delete_entry'));
        $c_gui->setCancel($this->lng->txt('cancel'), 'showMembersList');
        $c_gui->setConfirm($this->lng->txt('confirm'), 'performDeleteMembers');

        $assigned_entries = $this->mlists->getCurrentMailingList()->getAssignedEntries();

        $usr_ids = [];
        foreach ($assigned_entries as $entry) {
            $usr_ids[] = $entry['usr_id'];
        }

        $names = ilUserUtil::getNamePresentation($usr_ids, false, false, '', false, false, false);

        $requested_entry_ids = $this->http->wrapper()->post()->retrieve(
            'a_id',
            $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
        );

        foreach ($assigned_entries as $entry) {
            if (in_array($entry['a_id'], $requested_entry_ids, true)) {
                $c_gui->addItem('a_id[]', (string) $entry['a_id'], $names[$entry['usr_id']]);
            }
        }

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.mail_mailing_lists_members.html',
            'Services/Contact'
        );
        $this->tpl->setVariable('DELETE_CONFIRMATION', $c_gui->getHTML());

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
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_deleted_entry'));
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_delete_error'));
        }

        $this->showMembersList();

        return true;
    }


    protected function getAssignmentForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveAssignmentForm'));
        $form->setTitle($this->lng->txt('mail_assign_entry_to_mailing_list') . ' ' . $this->mlists->getCurrentMailingList()->getTitle());

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
            $formItem = new ilSelectInputGUI($this->lng->txt('mail_entry_of_contacts'), 'usr_id');
            $formItem->setRequired(true);
            $formItem->setOptions($options);
            $form->addItem($formItem);

            $form->addCommandButton('saveAssignmentForm', $this->lng->txt('assign'));
        } elseif (count($options) === 1 && count($relations) > 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_mailing_lists_all_contact_entries_assigned'), true);
            $this->ctrl->redirect($this, 'showMembersList');
        } elseif (count($relations) === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_mailing_lists_no_contact_entries'), true);
            $this->ctrl->redirect($this, 'showMembersList');
        }
        $form->addCommandButton('showMembersList', $this->lng->txt('cancel'));

        return $form;
    }


    public function saveAssignmentForm(): bool
    {
        if (!$this->mlists->isOwner($this->mlists->getCurrentMailingList()->getId(), $this->user->getId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getAssignmentForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showAssignmentForm($form);
            return true;
        }

        if (
            ilBuddyList::getInstanceByGlobalUser()->getRelationByUserId(
                $this->http->wrapper()->post()->retrieve('usr_id', $this->refinery->kindlyTo()->int())
            )->isLinked()
        ) {
            $this->mlists->getCurrentMailingList()->assignUser(
                $this->http->wrapper()->post()->retrieve('usr_id', $this->refinery->kindlyTo()->int())
            );
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('saved_successfully'));
            $this->showMembersList();
            return true;
        }

        $this->showAssignmentForm($form);
        return true;
    }


    public function showAssignmentForm(?ilPropertyFormGUI $form = null): bool
    {
        if ($this->mlists->getCurrentMailingList()->getId() === 0) {
            $this->showMembersList();
            return true;
        }

        if (!$this->mlists->isOwner($this->mlists->getCurrentMailingList()->getId(), $this->user->getId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->tpl->setTitle($this->lng->txt('mail_addressbook'));
        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.mail_mailing_lists_members_form.html',
            'Services/Contact'
        );

        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getAssignmentForm();
        }

        $this->tpl->setVariable('FORM', $form->getHTML());
        $this->tpl->printToStdout();

        return true;
    }
}
