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
use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\Mail\Attachments\AttachmentManagement;
use ILIAS\Mail\Attachments\MailAttachmentTableGUI;
use ILIAS\Mail\Attachments\MailAttachmentCommands;

class ilMailAttachmentGUI extends AbstractCtrlAwareUploadHandler implements
    ilCtrlSecurityInterface,
    MailAttachmentCommands
{
    use FileDataRCHandling;

    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilLanguage $lng;
    private readonly ilObjUser $user;
    private readonly ilFormatMail $umail;
    private readonly ilFileDataMail $fdm;
    private readonly Refinery $refinery;
    private readonly \ILIAS\UI\Factory $ui_factory;
    private readonly \ILIAS\UI\Renderer $ui_renderer;
    private readonly ilTabsGUI $tabs;
    private AttachmentManagement $mode = AttachmentManagement::MANAGE;
    private readonly ILIAS\ResourceStorage\Services $storage;

    public function __construct()
    {
        global $DIC;

        parent::__construct();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->storage = $DIC->resourceStorage();

        $this->ctrl->saveParameter($this, 'mobj_id');

        $this->umail = new ilFormatMail($DIC->user()->getId());
        $this->fdm = new ilFileDataMail($DIC->user()->getId());
    }

    public function getUnsafeGetCommands(): array
    {
        return [
            self::CMD_HANDLE_TABLE_ACTIONS,
        ];
    }

    public function getSafePostCommands(): array
    {
        return [];
    }

    public function manage(): self
    {
        $this->mode = AttachmentManagement::MANAGE;
        return $this;
    }

    public function consume(): self
    {
        $this->mode = AttachmentManagement::CONSUME;
        return $this;
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case AbstractCtrlAwareUploadHandler::CMD_UPLOAD:
            case AbstractCtrlAwareUploadHandler::CMD_INFO:
            case AbstractCtrlAwareUploadHandler::CMD_REMOVE:
                parent::executeCommand();
                break;

            default:
                if ($cmd === null || $cmd === '' || !method_exists($this, $cmd . 'Command')) {
                    $cmd = self::DEFAULT_CMD;
                }
                $verified_command = $cmd . 'Command';
                $this->$verified_command();
                break;
        }
    }

    private function saveAttachments(): void
    {
        $files = [];

        // Important: Do not check for uploaded files here,
        // otherwise it is no more possible to remove files (please ignore bug reports like 10137)

        $size_of_affected_files = 0;
        $files_of_request = $this->http->wrapper()->query()->retrieve(
            'mail_attachments_filename',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        if ($files_of_request !== [] && $files_of_request[0] === 'ALL_OBJECTS') {
            $files_of_request = array_map(static fn(array $file): string => $file['name'], $this->fdm->getUserFilesData());
        }

        foreach ($files_of_request as $file) {
            if (is_file($this->fdm->getMailPath() . '/' . basename($this->user->getId() . '_' . urldecode((string) $file)))) {
                $files[] = urldecode((string) $file);
                $size_of_affected_files += filesize(
                    $this->fdm->getMailPath() . '/' .
                    basename($this->user->getId() . '_' . urldecode((string) $file))
                );
            }
        }

        if ($files !== [] &&
            $this->fdm->getAttachmentsTotalSizeLimit() !== null &&
            $size_of_affected_files > $this->fdm->getAttachmentsTotalSizeLimit()) {
            $this->tpl->setOnScreenMessage(
                $this->tpl::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('mail_max_size_attachments_total_error') . ' ' .
                ilUtil::formatSize((int) $this->fdm->getAttachmentsTotalSizeLimit())
            );
            $this->showAttachmentsCommand();
            return;
        }

        $rcid_for_files = $this->getIdforCollection($files);
        $this->umail->saveAttachments($rcid_for_files);

        $this->ctrl->returnToParent($this);
    }

    private function cancelSaveAttachmentsCommand(): void
    {
        $this->ctrl->setParameter($this, 'type', ilMailFormGUI::MAIL_FORM_TYPE_ATTACH);
        $this->ctrl->returnToParent($this);
    }

    private function confirmDeleteAttachments(): void
    {
        $files = $this->http->wrapper()->query()->retrieve(
            'mail_attachments_filename',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        if ($files !== [] && $files[0] === 'ALL_OBJECTS') {
            $files = array_map(static fn(array $file): string => $file['name'], $this->fdm->getUserFilesData());
        }

        if ($files === []) {
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_INFO, $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this);
        }

        $this->tpl->setTitle($this->lng->txt('mail_attachments'));

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, self::CMD_DELETE_ATTACHMENTS));
        $confirmation->setConfirm($this->lng->txt('confirm'), self::CMD_DELETE_ATTACHMENTS);
        $confirmation->setCancel($this->lng->txt('cancel'), self::CMD_SHOW_ATTACHMENTS);
        $confirmation->setHeaderText($this->lng->txt('mail_sure_delete_file'));

        foreach ($files as $filename) {
            $confirmation->addItem(
                'filename[]',
                ilUtil::stripSlashes($filename),
                ilUtil::stripSlashes(urldecode((string) $filename))
            );
        }

        $this->tpl->setContent($confirmation->getHTML());
        $this->tpl->printToStdout();
    }

    private function deleteAttachmentsCommand(): void
    {
        $files = $this->http->wrapper()->post()->retrieve(
            'filename',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        if ($files === []) {
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_INFO, $this->lng->txt('mail_select_one_mail'));
            $this->showAttachmentsCommand();
            return;
        }

        $decoded_files = [];
        foreach ($files as $value) {
            $decoded_files[] = urldecode((string) $value);
        }

        $error = $this->fdm->unlinkFiles($decoded_files);
        if ($error !== '') {
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('mail_error_delete_file') . ' ' . $error, true);
        } else {
            $mail_data = $this->umail->retrieveFromStage();
            if (!is_null($mail_data['attachments'])) {
                $files_to_legacy = $this->FilesFromIRSSToLegacy($mail_data['attachments']);
                $files = $this->handleAttachments($files_to_legacy);
                $rcid = null;
                if (is_array($files)) {
                    foreach ($files as $attachment) {
                        $tmp = [];
                        if (!in_array($attachment, $decoded_files, true)) {
                            $tmp[] = $attachment;
                        }
                        $rcid = $this->getIdforCollection($tmp);
                    }
                    $this->umail->saveAttachments($rcid);
                }
            }

            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('mail_files_deleted'), true);
        }

        $this->ctrl->redirect($this);
    }

    private function showAttachmentsCommand(): void
    {
        $this->tpl->setTitle($this->lng->txt('mail_attachments'));

        if ($this->mode === AttachmentManagement::CONSUME) {
            $this->tabs->clearTargets();
            $this->tabs->setBackTarget(
                $this->lng->txt('mail_manage_attachments_back_to_compose'),
                $this->ctrl->getLinkTarget($this, self::CMD_CANCEL_SAVE_ATTACHMENTS)
            );
        }

        $components = [];
        if ($this->mode === AttachmentManagement::MANAGE) {
            $dropzone = $this->ui_factory
                ->dropzone()
                ->file()
                ->standard(
                    $this->lng->txt('mail_manage_attachments'),
                    $this->lng->txt('mail_manage_attachments_drop_files_msg'),
                    '#',
                    $this->ui_factory->input()->field()->file(
                        $this,
                        $this->lng->txt('file')
                    )->withMaxFiles(42) // The answer to life, universe and the rest
                )
                ->withBulky(true)
                ->withUploadButton(
                    $this->ui_factory->button()->shy(
                        $this->lng->txt('select_files_from_computer'),
                        '#'
                    )
                );
            $components[] = $dropzone;
        }

        $mail_data = $this->umail->retrieveFromStage();
        $files = $this->fdm->getUserFilesData();
        $records = [];
        $checked_items = [];
        foreach ($files as $file) {
            if (is_array($mail_data['attachments']) && in_array($file['name'], $mail_data['attachments'], true)) {
                $checked_items[] = urlencode($file['name']);
            }

            $records[] = [
                'filename' => $file['name'],
                'filesize' => (int) $file['size'],
                'filecreatedate' => (int) $file['ctime'],
            ];
        }

        $table = new MailAttachmentTableGUI(
            $this,
            $this->user,
            $records,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->ctrl,
            $this->http->request(),
            new ILIAS\Data\Factory(),
            self::CMD_HANDLE_TABLE_ACTIONS,
            $this->mode
        );
        $components[] = $table->get();

        $this->tpl->setContent($this->ui_renderer->render($components));

        if ($this->mode === AttachmentManagement::CONSUME) {
            // The table above has to be rendered first, because it deselects all checkboxes
            $this->tpl->addOnLoadCode('
                const checked_items = ' . json_encode($checked_items, JSON_THROW_ON_ERROR) . ';
                for (const item of checked_items) {
                    const checkbox = document.querySelector("input[type=\'checkbox\'][value=\'" + item + "\']");
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                }
            ');
        }

        $this->tpl->printToStdout();
    }

    private function handleTableActionsCommand(): void
    {
        $query = $this->http->wrapper()->query();
        if (!$query->has('mail_attachments_table_action')) {
            return;
        }

        $action = $query->retrieve('mail_attachments_table_action', $this->refinery->to()->string());
        match ($action) {
            self::TABLE_ACTION_SAVE_ATTACHMENTS => $this->saveAttachments(),
            self::TABLE_CONFIRM_DELETE_ATTACHMENTS => $this->confirmDeleteAttachments(),
            default => $this->ctrl->redirect($this),
        };
    }

    protected function getUploadResult(): HandlerResult
    {
        $this->upload->process();
        $array = $this->upload->getResults();
        $result = end($array);

        if ($result instanceof UploadResult && $result->isOK()) {
            $identifier = $this->fdm->storeUploadedFile($result);
            $status = HandlerResult::STATUS_OK;
            $message = $this->lng->txt('saved_successfully');
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_SUCCESS, $this->lng->txt('saved_successfully'), true);
        } else {
            $status = HandlerResult::STATUS_FAILED;
            $identifier = '';
            $message = $result->getStatus()->getMessage();
        }

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

    protected function getRemoveResult(string $identifier): HandlerResult
    {
        throw new DomainException('Not necessary for this handler');
    }

    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        throw new DomainException('Not necessary for this handler');
    }

    public function getInfoForExistingFiles(array $file_ids): array
    {
        throw new DomainException('Not necessary for this handler');
    }

    public function getFileIdentifierParameterName(): string
    {
        return 'userfile';
    }
}
