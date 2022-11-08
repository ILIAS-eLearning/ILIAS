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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author       Jens Conze
 * @version      $Id$
 * @ingroup      ServicesMail
 * @ilCtrl_Calls ilMailFolderGUI: ilPublicUserProfileGUI
 */
class ilMailFolderGUI
{
    private bool $confirmTrashDeletion = false;
    private bool $errorDelete = false;
    /** @var ilGlobalTemplateInterface */
    private ilGlobalTemplateInterface $tpl;
    private ilCtrlInterface $ctrl;
    private ilLanguage $lng;
    private ilToolbarGUI $toolbar;
    private ilTabsGUI $tabs;
    private ilObjUser $user;
    public ilMail $umail;
    public ilMailbox $mbox;
    private GlobalHttpState $http;
    private Refinery $refinery;
    private int $currentFolderId = 0;
    private ilErrorHandling $error;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->error = $DIC['ilErr'];

        $this->umail = new ilMail($this->user->getId());
        $this->mbox = new ilMailbox($this->user->getId());

        $this->initFolder();
    }

    protected function initFolder(): void
    {
        if ($this->http->wrapper()->post()->has('mobj_id')) {
            $folderId = $this->http->wrapper()->post()->retrieve('mobj_id', $this->refinery->kindlyTo()->int());
        } elseif ($this->http->wrapper()->query()->has('mobj_id')) {
            $folderId = $this->http->wrapper()->query()->retrieve('mobj_id', $this->refinery->kindlyTo()->int());
        } else {
            $folderId = $this->refinery->kindlyTo()->int()->transform(ilSession::get('mobj_id'));
        }

        if (0 === $folderId || !$this->mbox->isOwnedFolder($folderId)) {
            $folderId = $this->mbox->getInboxFolder();
        }

        $this->currentFolderId = $folderId;
    }

    protected function parseCommand(string $originalCommand): string
    {
        if (preg_match('/^([a-zA-Z0-9]+?)_(\d+?)$/', $originalCommand, $matches) && 3 === count($matches)) {
            $originalCommand = $matches[1];
        }

        return $originalCommand;
    }

    protected function parseFolderIdFromCommand(string $command): int
    {
        if (
            preg_match('/^([a-zA-Z0-9]+?)_(\d+?)$/', $command, $matches) &&
            3 === count($matches) && is_numeric($matches[2])
        ) {
            return (int) $matches[2];
        }

        throw new InvalidArgumentException("Cannot parse a numeric folder id from command string!");
    }

    public function executeCommand(): void
    {
        $cmd = $this->parseCommand(
            $this->ctrl->getCmd()
        );

        $nextClass = $this->ctrl->getNextClass($this);
        switch (strtolower($nextClass)) {
            case strtolower(ilContactGUI::class):
                $this->ctrl->forwardCommand(new ilContactGUI());
                break;

            case strtolower(ilPublicUserProfileGUI::class):
                $this->tpl->setTitle($this->lng->txt('mail'));
                $userId = 0;
                if ($this->http->wrapper()->query()->has('user')) {
                    $userId = $this->http->wrapper()->query()->retrieve('user', $this->refinery->kindlyTo()->int());
                }
                $profileGui = new ilPublicUserProfileGUI($userId);

                $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
                $profileGui->setBackUrl($this->ctrl->getLinkTarget($this, 'showMail'));
                $this->ctrl->clearParameters($this);

                $ret = $this->ctrl->forwardCommand($profileGui);
                if ($ret !== '') {
                    $this->tpl->setContent($ret);
                }
                $this->tpl->printToStdout();
                break;

            default:
                if (!method_exists($this, $cmd)) {
                    $cmd = 'showFolder';
                }
                $this->{$cmd}();
                break;
        }
    }

    protected function performEmptyTrash(): void
    {
        $this->umail->deleteMailsOfFolder($this->currentFolderId);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_deleted'), true);
        $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
        $this->ctrl->redirect($this, 'showFolder');
    }

    protected function confirmEmptyTrash(): void
    {
        if ($this->umail->countMailsOfFolder($this->currentFolderId) !== 0) {
            $this->confirmTrashDeletion = true;
        }

        $this->showFolder();
    }

    /**
     * @throws ilCtrlException
     */
    protected function showUser(): void
    {
        $userId = 0;
        if ($this->http->wrapper()->query()->has('user')) {
            $userId = $this->http->wrapper()->query()->retrieve('user', $this->refinery->kindlyTo()->int());
        }
        $this->tpl->setVariable('TBL_TITLE', implode(' ', [
            $this->lng->txt('profile_of'),
            ilObjUser::_lookupLogin($userId),
        ]));
        $this->tpl->setVariable('TBL_TITLE_IMG', ilUtil::getImagePath('icon_usr.svg'));
        $this->tpl->setVariable('TBL_TITLE_IMG_ALT', $this->lng->txt('public_profile'));

        $profile_gui = new ilPublicUserProfileGUI($userId);

        $mailId = 0;
        if ($this->http->wrapper()->query()->has('mail_id')) {
            $mailId = $this->http->wrapper()->query()->retrieve('mail_id', $this->refinery->kindlyTo()->int());
        }

        $this->ctrl->setParameter(
            $this,
            'mail_id',
            $mailId
        );
        $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
        $profile_gui->setBackUrl($this->ctrl->getLinkTarget($this, 'showMail'));
        $this->ctrl->clearParameters($this);

        $this->tpl->setTitle($this->lng->txt('mail'));
        $this->tpl->setContent($this->ctrl->getHTML($profile_gui));
        $this->tpl->printToStdout();
    }

    protected function addSubFolderCommands(bool $isUserSubFolder = false): void
    {
        $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
        $this->toolbar->addButton(
            $this->lng->txt('mail_add_subfolder'),
            $this->ctrl->getLinkTarget($this, 'addSubFolder')
        );

        if ($isUserSubFolder) {
            $this->toolbar->addButton(
                $this->lng->txt('rename'),
                $this->ctrl->getLinkTarget($this, 'renameSubFolder')
            );
            $this->toolbar->addButton(
                $this->lng->txt('delete'),
                $this->ctrl->getLinkTarget($this, 'deleteSubFolder')
            );
        }
        $this->ctrl->clearParameters($this);
    }

    protected function showFolder(bool $oneConfirmationDialogueRendered = false): void
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail.html', 'Services/Mail');
        $this->tpl->setTitle($this->lng->txt('mail'));

        $isTrashFolder = $this->currentFolderId === $this->mbox->getTrashFolder();

        if (!$this->errorDelete && $isTrashFolder && 'deleteMails' === $this->parseCommand($this->ctrl->getCmd())) {
            $confirmationGui = new ilConfirmationGUI();
            $confirmationGui->setHeaderText($this->lng->txt('mail_sure_delete'));
            foreach ($this->getMailIdsFromRequest() as $mailId) {
                $confirmationGui->addHiddenItem('mail_id[]', (string) $mailId);
            }
            $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
            $confirmationGui->setFormAction($this->ctrl->getFormAction($this, 'showFolder'));
            $this->ctrl->clearParameters($this);
            $confirmationGui->setConfirm($this->lng->txt('confirm'), 'confirmDeleteMails');
            $confirmationGui->setCancel($this->lng->txt('cancel'), 'showFolder');
            $this->tpl->setVariable('CONFIRMATION', $confirmationGui->getHTML());
            $oneConfirmationDialogueRendered = true;
        }

        $mtree = new ilTree($this->user->getId());
        $mtree->setTableNames('mail_tree', 'mail_obj_data');

        $isUserSubFolder = false;
        $isUserRootFolder = false;

        $folder_d = $mtree->getNodeData($this->currentFolderId);
        if ($folder_d['m_type'] === 'user_folder') {
            $isUserSubFolder = true;
        } elseif ($folder_d['m_type'] === 'local') {
            $isUserRootFolder = true;
        }

        $mailtable = $this->getMailFolderTable();
        $mailtable->setSelectedItems($this->getMailIdsFromRequest(true));

        try {
            $mailtable->prepareHTML();
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt($e->getMessage()) !== '-' . $e->getMessage() . '-' ?
                $this->lng->txt($e->getMessage()) :
                $e->getMessage());
        }

        $table_html = $mailtable->getHTML();

        if (!$oneConfirmationDialogueRendered && !$this->confirmTrashDeletion) {
            $this->toolbar->setFormAction($this->ctrl->getFormAction($this, 'showFolder'));

            if ($isUserRootFolder || $isUserSubFolder) {
                $this->addSubFolderCommands($isUserSubFolder);
            }
        }

        if ($this->confirmTrashDeletion && $mailtable->isTrashFolder() && $mailtable->getNumberOfMails() > 0) {
            $confirmationGui = new ilConfirmationGUI();
            $confirmationGui->setHeaderText($this->lng->txt('mail_empty_trash_confirmation'));
            $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
            $confirmationGui->setFormAction($this->ctrl->getFormAction($this, 'performEmptyTrash'));
            $this->ctrl->clearParameters($this);
            $confirmationGui->setConfirm($this->lng->txt('confirm'), 'performEmptyTrash');
            $confirmationGui->setCancel($this->lng->txt('cancel'), 'showFolder');
            $this->tpl->setVariable('CONFIRMATION', $confirmationGui->getHTML());
        }

        $this->tpl->setVariable('MAIL_TABLE', $table_html);
        $this->tpl->printToStdout();
    }

    protected function deleteSubFolder(bool $a_show_confirm = true): void
    {
        if ($a_show_confirm) {
            $confirmationGui = new ilConfirmationGUI();
            $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
            $confirmationGui->setFormAction($this->ctrl->getFormAction($this, 'showFolder'));
            $this->ctrl->clearParameters($this);
            $confirmationGui->setHeaderText($this->lng->txt('mail_sure_delete_folder'));
            $confirmationGui->setCancel($this->lng->txt('cancel'), 'showFolder');
            $confirmationGui->setConfirm($this->lng->txt('confirm'), 'performDeleteSubFolder');
            $this->tpl->setVariable('CONFIRMATION', $confirmationGui->getHTML());

            $this->showFolder(true);
        } else {
            $this->showFolder();
        }
    }

    /**
     * @throws ilInvalidTreeStructureException
     */
    protected function performDeleteSubFolder(): void
    {
        $parentFolderId = $this->mbox->getParentFolderId($this->currentFolderId);
        if ($parentFolderId > 0 && $this->mbox->deleteFolder($this->currentFolderId)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_folder_deleted'), true);
            $this->ctrl->setParameterByClass(ilMailGUI::class, 'mobj_id', $parentFolderId);
            $this->ctrl->redirectByClass(ilMailGUI::class);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_error_delete'));
            $this->showFolder();
        }
    }

    protected function getSubFolderForm(string $mode = 'create'): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
        $form->setFormAction($this->ctrl->getFormAction($this, 'performAddSubFolder'));
        $this->ctrl->clearParameters($this);
        if ('edit' === $mode) {
            $form->addCommandButton('performRenameSubFolder', $this->lng->txt('save'));
            $form->setTitle($this->lng->txt('mail_rename_folder'));
        } else {
            $form->addCommandButton('performAddSubFolder', $this->lng->txt('save'));
            $form->setTitle($this->lng->txt('mail_add_folder'));
        }
        $form->addCommandButton('showFolder', $this->lng->txt('cancel'));

        $title = new ilTextInputGUI($this->lng->txt('title'), 'subfolder_title');
        $title->setRequired(true);
        $form->addItem($title);

        return $form;
    }

    protected function performAddSubFolder(): void
    {
        $form = $this->getSubFolderForm();
        $isFormValid = $form->checkInput();
        $form->setValuesByPost();
        if (!$isFormValid) {
            $this->addSubFolder($form);
            return;
        }

        $newFolderId = $this->mbox->addFolder($this->currentFolderId, $form->getInput('subfolder_title'));
        if ($newFolderId > 0) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_folder_created'), true);
            $this->ctrl->setParameterByClass(ilMailGUI::class, 'mobj_id', $newFolderId);
            $this->ctrl->redirectByClass(ilMailGUI::class);
        }

        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_folder_exists'));
        $this->addSubFolder($form);
    }

    protected function addSubFolder(ilPropertyFormGUI $form = null): void
    {
        if (null === $form) {
            $form = $this->getSubFolderForm();
        }

        $this->tpl->setTitle($this->lng->txt('mail'));
        $this->tpl->setContent($form->getHTML());
        $this->tpl->printToStdout();
    }

    protected function performRenameSubFolder(): void
    {
        $form = $this->getSubFolderForm('edit');
        $isFormValid = $form->checkInput();
        $form->setValuesByPost();
        if (!$isFormValid) {
            $this->renameSubFolder($form);
            return;
        }

        $folderData = $this->mbox->getFolderData($this->currentFolderId);
        if ($folderData['title'] === $form->getInput('subfolder_title')) {
            $this->showFolder();
            return;
        }

        if ($this->mbox->renameFolder($this->currentFolderId, $form->getInput('subfolder_title'))) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_folder_name_changed'), true);
            $this->ctrl->setParameterByClass(ilMailGUI::class, 'mobj_id', $this->currentFolderId);
            $this->ctrl->redirectByClass(ilMailGUI::class);
        }

        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_folder_exists'));
        $this->renameSubFolder($form);
    }

    protected function renameSubFolder(ilPropertyFormGUI $form = null): void
    {
        if (null === $form) {
            $form = $this->getSubFolderForm('edit');
            $form->setValuesByArray(
                ['subfolder_title' => $this->mbox->getFolderData($this->currentFolderId)['title']]
            );
        }

        $this->tpl->setTitle($this->lng->txt('mail'));
        $this->tpl->setContent($form->getHTML());
        $this->tpl->printToStdout();
    }

    /**
     * @return int[]
     */
    protected function getMailIdsFromRequest(bool $ignoreHttpGet = false): array
    {
        $mailIds = [];
        if ($this->http->wrapper()->post()->has('mail_id')) {
            $mailIds = $this->http->wrapper()->post()->retrieve(
                'mail_id',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        if ($mailIds === [] && !$ignoreHttpGet) {
            $mailId = 0;
            if ($this->http->wrapper()->query()->has('mail_id')) {
                $mailId = $this->http->wrapper()->query()->retrieve('mail_id', $this->refinery->kindlyTo()->int());
            }
            if (is_numeric($mailId)) {
                $mailIds = [$mailId];
            }
        }

        return array_filter(array_map('intval', $mailIds));
    }

    protected function markMailsRead(): void
    {
        $mailIds = $this->getMailIdsFromRequest();
        if ($mailIds !== []) {
            $this->umail->markRead($mailIds);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one'));
        }

        $this->showFolder();
    }

    protected function markMailsUnread(): void
    {
        $mailIds = $this->getMailIdsFromRequest();
        if ($mailIds !== []) {
            $this->umail->markUnread($mailIds);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one'));
        }

        $this->showFolder();
    }

    protected function moveSingleMail(): void
    {
        $mailIds = $this->getMailIdsFromRequest();
        if (1 !== count($mailIds)) {
            $this->showMail();
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one'));
            return;
        }

        $newFolderId = 0;
        if ($this->http->wrapper()->post()->has('folder_id')) {
            $newFolderId = $this->http->wrapper()->post()->retrieve(
                'folder_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $redirectFolderId = $newFolderId;

        foreach ($mailIds as $mailId) {
            $mailData = $this->umail->getMail($mailId);
            if (isset($mailData['folder_id']) &&
                is_numeric($mailData['folder_id']) &&
                (int) $mailData['folder_id'] > 0
            ) {
                $redirectFolderId = (int) $mailData['folder_id'];
                break;
            }
        }

        if ($this->umail->moveMailsToFolder($mailIds, $newFolderId)) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_moved'), true);
            $this->ctrl->setParameter($this, 'mobj_id', $redirectFolderId);
            $this->ctrl->redirect($this, 'showFolder');
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_move_error'));
            $this->showMail();
        }
    }

    protected function moveMails(): void
    {
        $mailIds = $this->getMailIdsFromRequest();
        if ($mailIds === []) {
            $this->showFolder();
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one'));
            return;
        }

        $folderId = $this->parseFolderIdFromCommand($this->ctrl->getCmd());
        if ($this->umail->moveMailsToFolder($mailIds, $folderId)) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_moved'), true);
            $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
            $this->ctrl->redirect($this, 'showFolder');
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_move_error'));
            $this->showFolder();
        }
    }

    protected function deleteMails(): void
    {
        $trashFolderId = $this->mbox->getTrashFolder();
        $mailIds = $this->getMailIdsFromRequest();

        if ($trashFolderId === $this->currentFolderId) {
            if ($mailIds === []) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one'));
                $this->errorDelete = true;
            }
        } elseif ($mailIds === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one'));
        } elseif ($this->umail->moveMailsToFolder($mailIds, $trashFolderId)) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_moved_to_trash'), true);
            $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
            $this->ctrl->redirect($this, 'showFolder');
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_move_error'));
        }

        $this->showFolder();
    }

    protected function confirmDeleteMails(): void
    {
        $mailIds = $this->getMailIdsFromRequest();
        if ($mailIds === []) {
            $this->showFolder();
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one'));
            return;
        }

        if ($this->mbox->getTrashFolder() === $this->currentFolderId) {
            $this->umail->deleteMails($mailIds);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_deleted'), true);
            $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
            $this->ctrl->redirect($this, 'showFolder');
        }

        $this->showFolder();
    }

    protected function showMail(): void
    {
        $mailId = 0;
        if ($this->http->wrapper()->query()->has('mail_id')) {
            $mailId = $this->http->wrapper()->query()->retrieve('mail_id', $this->refinery->kindlyTo()->int());
        }

        if ($mailId <= 0) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $mailData = $this->umail->getMail($mailId);
        if ($mailData === null) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->umail->markRead([$mailId]);

        $this->tpl->setTitle($this->lng->txt('mail_mails_of'));

        $this->tabs->clearTargets();
        $this->ctrl->setParameter($this, 'mobj_id', $mailData['folder_id']);
        $this->tabs->setBackTarget(
            $this->lng->txt('back_to_folder'),
            $this->ctrl->getFormAction($this, 'showFolder')
        );
        $this->ctrl->clearParameters($this);

        $this->ctrl->setParameter($this, 'mail_id', $mailId);
        $this->ctrl->setParameter($this, 'mobj_id', $mailData['folder_id']);
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, 'showMail'));
        $this->ctrl->clearParameters($this);

        $form = new ilPropertyFormGUI();
        $form->setPreventDoubleSubmission(false);
        $form->setTableWidth('100%');
        $this->ctrl->setParameter($this, 'mobj_id', $mailData['folder_id']);
        $this->ctrl->setParameter($this, 'mail_id', $mailId);
        $form->setFormAction($this->ctrl->getFormAction($this, 'showMail'));
        $this->ctrl->clearParameters($this);
        $form->setTitle($this->lng->txt('mail_mails_of'));

        /** @var ilObjUser|null $sender */
        $sender = ilObjectFactory::getInstanceByObjId($mailData['sender_id'], false);
        $replyBtn = null;
        if ($sender instanceof ilObjUser && $sender->getId() !== 0 && !$sender->isAnonymous()) {
            $replyBtn = ilLinkButton::getInstance();
            $replyBtn->setCaption('reply');
            $this->ctrl->setParameterByClass(
                ilMailFormGUI::class,
                'mobj_id',
                $mailData['folder_id']
            );
            $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'mail_id', $mailId);
            $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_REPLY);
            $replyBtn->setUrl($this->ctrl->getLinkTargetByClass(ilMailFormGUI::class));
            $this->ctrl->clearParametersByClass(ilMailFormGUI::class);
            $replyBtn->setPrimary(true);
            $this->toolbar->addStickyItem($replyBtn);
        }

        $fwdBtn = ilLinkButton::getInstance();
        $fwdBtn->setCaption('forward');
        $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'mobj_id', $mailData['folder_id']);
        $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'mail_id', $mailId);
        $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_FORWARD);
        $fwdBtn->setUrl($this->ctrl->getLinkTargetByClass(ilMailFormGUI::class));
        $this->ctrl->clearParametersByClass(ilMailFormGUI::class);
        if ($replyBtn === null) {
            $fwdBtn->setPrimary(true);
            $this->toolbar->addStickyItem($fwdBtn);
        } else {
            $this->toolbar->addButtonInstance($fwdBtn);
        }

        $printBtn = ilLinkButton::getInstance();
        $printBtn->setCaption('print');
        $this->ctrl->setParameter($this, 'mail_id', $mailId);
        $this->ctrl->setParameter($this, 'mobj_id', $mailData['folder_id']);
        $printBtn->setUrl($this->ctrl->getLinkTarget($this, 'printMail'));
        $this->ctrl->clearParameters($this);
        $printBtn->setTarget('_blank');
        $this->toolbar->addButtonInstance($printBtn);

        $deleteBtn = ilSubmitButton::getInstance();
        $deleteBtn->setCaption('delete');
        $deleteBtn->setCommand('deleteMails');
        $this->toolbar->addButtonInstance($deleteBtn);

        if ($sender && $sender->getId() && !$sender->isAnonymous()) {
            $linked_fullname = $sender->getPublicName();
            $picture = ilUtil::img(
                $sender->getPersonalPicturePath('xsmall'),
                $sender->getPublicName(),
                '',
                '',
                0,
                '',
                'ilMailAvatar'
            );

            if (in_array(ilObjUser::_lookupPref($sender->getId(), 'public_profile'), ['y', 'g'])) {
                $this->ctrl->setParameter($this, 'mail_id', $mailId);
                $this->ctrl->setParameter($this, 'mobj_id', $mailData['folder_id']);
                $this->ctrl->setParameter($this, 'user', $sender->getId());
                $linked_fullname = '<br /><a href="' . $this->ctrl->getLinkTarget(
                    $this,
                    'showUser'
                ) . '" title="' . $linked_fullname . '">' . $linked_fullname . '</a>';
                $this->ctrl->clearParameters($this);
            }

            $from = new ilCustomInputGUI($this->lng->txt('from') . ':');
            $from->setHtml($picture . ' ' . $linked_fullname);
        } elseif (!$sender || !$sender->getId()) {
            $from = new ilCustomInputGUI($this->lng->txt('from') . ':');
            $from->setHtml(trim(($mailData['import_name'] ?? '') . ' (' . $this->lng->txt('user_deleted') . ')'));
        } else {
            $from = new ilCustomInputGUI($this->lng->txt('from') . ':');
            $from->setHtml(
                ilUtil::img(
                    ilUtil::getImagePath('HeaderIconAvatar.svg'),
                    ilMail::_getIliasMailerName(),
                    '',
                    '',
                    0,
                    '',
                    'ilMailAvatar'
                ) .
                '<br />' . ilMail::_getIliasMailerName()
            );
        }
        $form->addItem($from);

        $to = new ilCustomInputGUI($this->lng->txt('mail_to') . ':');
        $to->setHtml(ilUtil::htmlencodePlainString(
            $this->umail->formatNamesForOutput($mailData['rcp_to'] ?? ''),
            false
        ));
        $form->addItem($to);

        if ($mailData['rcp_cc']) {
            $cc = new ilCustomInputGUI($this->lng->txt('cc') . ':');
            $cc->setHtml(ilUtil::htmlencodePlainString(
                $this->umail->formatNamesForOutput($mailData['rcp_cc'] ?? ''),
                false
            ));
            $form->addItem($cc);
        }

        if ($mailData['rcp_bcc']) {
            $bcc = new ilCustomInputGUI($this->lng->txt('bc') . ':');
            $bcc->setHtml(ilUtil::htmlencodePlainString(
                $this->umail->formatNamesForOutput($mailData['rcp_bcc'] ?? ''),
                false
            ));
            $form->addItem($bcc);
        }

        $subject = new ilCustomInputGUI($this->lng->txt('subject') . ':');
        $subject->setHtml(ilUtil::htmlencodePlainString($mailData['m_subject'] ?? '', true));
        $form->addItem($subject);

        $date = new ilCustomInputGUI($this->lng->txt('date') . ':');
        $date->setHtml(ilDatePresentation::formatDate(
            new ilDateTime($mailData['send_time'], IL_CAL_DATETIME)
        ));
        $form->addItem($date);

        $message = new ilCustomInputGUI($this->lng->txt('message') . ':');
        $message->setHtml(ilUtil::htmlencodePlainString($mailData['m_message'] ?? '', true));
        $form->addItem($message);

        if ($mailData['attachments']) {
            $att = new ilCustomInputGUI($this->lng->txt('attachments') . ':');

            $radiog = new ilRadioGroupInputGUI('', 'filename');
            foreach ($mailData['attachments'] as $file) {
                $radiog->addOption(new ilRadioOption($file, md5($file)));
            }

            $att->setHtml($radiog->render());
            $form->addCommandButton('deliverFile', $this->lng->txt('download'));
            $form->addItem($att);
        }

        $isTrashFolder = false;
        if ($this->mbox->getTrashFolder() === $mailData['folder_id']) {
            $isTrashFolder = true;
        }

        $currentFolderData = $this->mbox->getFolderData((int) $mailData['folder_id']);
        $actions = $this->mbox->getActions((int) $mailData['folder_id']);

        $selectOptions = [];
        foreach ($actions as $key => $action) {
            if ($key === 'moveMails') {
                $folders = $this->mbox->getSubFolders();
                foreach ($folders as $folder) {
                    if (
                        ($folder['type'] !== 'trash' || !$isTrashFolder) &&
                        $folder['obj_id'] !== $mailData['folder_id']
                    ) {
                        $optionText = $action . ' ' . $folder['title'];
                        if ($folder['type'] !== 'user_folder') {
                            $optionText = $action . ' ' . $this->lng->txt(
                                'mail_' . $folder['title']
                            ) .
                                ($folder['type'] === 'trash' ? ' (' . $this->lng->txt('delete') . ')' : '');
                        }

                        $selectOptions[$folder['obj_id']] = $optionText;
                    }
                }
            }
        }

        $folderLabel = $this->lng->txt('mail_' . $currentFolderData['title']);
        if ($currentFolderData['type'] === 'user_folder') {
            $folderLabel = $currentFolderData['title'];
        }

        $this->toolbar->addSeparator();
        $this->toolbar->addText(sprintf($this->lng->txt('current_folder'), $folderLabel));

        if (is_array($selectOptions) && $selectOptions !== []) {
            $actions = new ilSelectInputGUI('', 'folder_id');
            $actions->setOptions($selectOptions);
            $this->toolbar->addInputItem($actions);

            $moveBtn = ilSubmitButton::getInstance();
            $moveBtn->setCaption('execute');
            $moveBtn->setCommand('moveSingleMail');
            $this->toolbar->addButtonInstance($moveBtn);
        }

        $prevMail = $this->umail->getPreviousMail($mailId);
        $nextMail = $this->umail->getNextMail($mailId);
        if (is_array($prevMail) || is_array($nextMail)) {
            $this->toolbar->addSeparator();

            if ($prevMail && $prevMail['mail_id']) {
                $prevBtn = ilLinkButton::getInstance();
                $prevBtn->setCaption('previous');
                $this->ctrl->setParameter($this, 'mail_id', $prevMail['mail_id']);
                $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
                $prevBtn->setUrl($this->ctrl->getLinkTarget($this, 'showMail'));
                $this->ctrl->clearParameters($this);
                $this->toolbar->addButtonInstance($prevBtn);
            }

            if ($nextMail && $nextMail['mail_id']) {
                $nextBtn = ilLinkButton::getInstance();
                $nextBtn->setCaption('next');
                $this->ctrl->setParameter($this, 'mail_id', $nextMail['mail_id']);
                $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
                $nextBtn->setUrl($this->ctrl->getLinkTarget($this, 'showMail'));
                $this->ctrl->clearParameters($this);
                $this->toolbar->addButtonInstance($nextBtn);
            }
        }

        $this->tpl->setContent($form->getHTML());
        $this->tpl->printToStdout();
    }

    protected function printMail(): void
    {
        $tplprint = new ilTemplate('tpl.mail_print.html', true, true, 'Services/Mail');

        $mailId = 0;
        if ($this->http->wrapper()->query()->has('mail_id')) {
            $mailId = $this->http->wrapper()->query()->retrieve('mail_id', $this->refinery->kindlyTo()->int());
        }
        $mailData = $this->umail->getMail($mailId);

        $sender = ilObjectFactory::getInstanceByObjId($mailData['sender_id'], false);

        $tplprint->setVariable('TXT_FROM', $this->lng->txt('from'));
        if ($sender instanceof ilObjUser && $sender->getId() !== 0 && !$sender->isAnonymous()) {
            $tplprint->setVariable('FROM', $sender->getPublicName());
        } elseif (!$sender instanceof ilObjUser || 0 === $sender->getId()) {
            $tplprint->setVariable(
                'FROM',
                $mailData['import_name'] . ' (' . $this->lng->txt('user_deleted') . ')'
            );
        } else {
            $tplprint->setVariable('FROM', ilMail::_getIliasMailerName());
        }

        $tplprint->setVariable('TXT_TO', $this->lng->txt('mail_to'));
        $tplprint->setVariable('TO', $mailData['rcp_to']);

        if ($mailData['rcp_cc']) {
            $tplprint->setCurrentBlock('cc');
            $tplprint->setVariable('TXT_CC', $this->lng->txt('cc'));
            $tplprint->setVariable('CC', $mailData['rcp_cc']);
            $tplprint->parseCurrentBlock();
        }

        if ($mailData['rcp_bcc']) {
            $tplprint->setCurrentBlock('bcc');
            $tplprint->setVariable('TXT_BCC', $this->lng->txt('bc'));
            $tplprint->setVariable('BCC', $mailData['rcp_bcc']);
            $tplprint->parseCurrentBlock();
        }

        $tplprint->setVariable('TXT_SUBJECT', $this->lng->txt('subject'));
        $tplprint->setVariable('SUBJECT', htmlspecialchars($mailData['m_subject']));

        $tplprint->setVariable('TXT_DATE', $this->lng->txt('date'));
        $tplprint->setVariable(
            'DATE',
            ilDatePresentation::formatDate(new ilDateTime($mailData['send_time'], IL_CAL_DATETIME))
        );

        $tplprint->setVariable('TXT_MESSAGE', $this->lng->txt('message'));
        $tplprint->setVariable('MAIL_MESSAGE', nl2br(htmlspecialchars($mailData['m_message'])));

        $tplprint->show();
    }

    protected function deliverFile(): void
    {
        $mailId = 0;
        if ($this->http->wrapper()->query()->has('mail_id')) {
            $mailId = $this->http->wrapper()->query()->retrieve('mail_id', $this->refinery->kindlyTo()->int());
        }

        if ($mailId <= 0) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $filename = '';
        if ($this->http->wrapper()->post()->has('filename')) {
            $filename = $this->http->wrapper()->post()->retrieve('filename', $this->refinery->kindlyTo()->string());
        }

        if (is_string(ilSession::get('filename')) && ilSession::get('filename') !== '') {
            $filename = ilSession::get('filename');
            ilSession::set('filename', null);
        }

        try {
            if ($mailId > 0 && $filename !== '') {
                while (str_contains($filename, '..')) {
                    $filename = str_replace('..', '', $filename);
                }

                $mailFileData = new ilFileDataMail($this->user->getId());
                try {
                    $file = $mailFileData->getAttachmentPathAndFilenameByMd5Hash($filename, (int) $mailId);
                    ilFileDelivery::deliverFileLegacy($file['path'], $file['filename']);
                } catch (OutOfBoundsException $e) {
                    throw new ilMailException('mail_error_reading_attachment', $e->getCode(), $e);
                }
            } else {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_attachment'));
                $this->showMail();
            }
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt($e->getMessage()), true);
            $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
            $this->ctrl->redirect($this);
        }
    }

    protected function deliverAttachments(): void
    {
        try {
            $mailId = 0;
            if ($this->http->wrapper()->query()->has('mail_id')) {
                $mailId = $this->http->wrapper()->query()->retrieve('mail_id', $this->refinery->kindlyTo()->int());
            }

            $mailData = $this->umail->getMail((int) $mailId);
            if (null === $mailData || [] === (array) $mailData['attachments']) {
                throw new ilMailException('mail_error_reading_attachment');
            }

            $type = '';
            if ($this->http->wrapper()->query()->has('type')) {
                $type = $this->http->wrapper()->query()->retrieve('type', $this->refinery->kindlyTo()->string());
            }

            $mailFileData = new ilFileDataMail($this->user->getId());
            if (count($mailData['attachments']) === 1) {
                $attachment = current($mailData['attachments']);

                try {
                    if ('draft' === $type) {
                        if (!$mailFileData->checkFilesExist([$attachment])) {
                            throw new OutOfBoundsException('');
                        }
                        $pathToFile = $mailFileData->getAbsoluteAttachmentPoolPathByFilename($attachment);
                        $fileName = $attachment;
                    } else {
                        $file = $mailFileData->getAttachmentPathAndFilenameByMd5Hash(
                            md5($attachment),
                            (int) $mailId
                        );
                        $pathToFile = $file['path'];
                        $fileName = $file['filename'];
                    }
                    ilFileDelivery::deliverFileLegacy($pathToFile, $fileName);
                } catch (OutOfBoundsException $e) {
                    throw new ilMailException('mail_error_reading_attachment', $e->getCode(), $e);
                }
            } else {
                $mailFileData->deliverAttachmentsAsZip(
                    $mailData['m_subject'],
                    (int) $mailId,
                    $mailData['attachments'],
                    'draft' === $type
                );
            }
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt($e->getMessage()), true);
            $this->ctrl->setParameter($this, 'mobj_id', $this->currentFolderId);
            $this->ctrl->redirect($this);
        }
    }

    protected function getMailFolderTable(): ilMailFolderTableGUI
    {
        $table = new ilMailFolderTableGUI(
            $this,
            $this->currentFolderId,
            'showFolder',
            $this->currentFolderId === $this->mbox->getTrashFolder(),
            $this->currentFolderId === $this->mbox->getSentFolder(),
            $this->currentFolderId === $this->mbox->getDraftsFolder()
        );
        $table->initFilter();

        return $table;
    }

    protected function applyFilter(): void
    {
        $table = $this->getMailFolderTable();
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showFolder();
    }

    protected function resetFilter(): void
    {
        $table = $this->getMailFolderTable();
        $table->resetOffset();
        $table->resetFilter();

        $this->showFolder();
    }
}
