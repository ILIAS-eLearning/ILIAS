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

class ilMailAttachmentGUI extends AbstractCtrlAwareUploadHandler
{
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilLanguage $lng;
    private readonly ilObjUser $user;
    private readonly ilFormatMail $umail;
    private readonly ilFileDataMail $mfile;
    private readonly Refinery $refinery;
    private readonly \ILIAS\UI\Factory $ui_factory;
    private readonly \ILIAS\UI\Renderer $ui_renderer;
    private readonly ilTabsGUI $tabs;
    private AttachmentManagement $mode = AttachmentManagement::MANAGE;

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

        $this->ctrl->saveParameter($this, 'mobj_id');

        $this->umail = new ilFormatMail($DIC->user()->getId());
        $this->mfile = new ilFileDataMail($DIC->user()->getId());
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
        if (!($cmd = $this->ctrl->getCmd())) {
            $cmd = 'showAttachments';
        }

        match ($cmd) {
            AbstractCtrlAwareUploadHandler::CMD_UPLOAD,
            AbstractCtrlAwareUploadHandler::CMD_INFO,
            AbstractCtrlAwareUploadHandler::CMD_REMOVE => parent::executeCommand(),
            default => $this->$cmd()
        };
    }

    public function saveAttachments(): void
    {
        $files = [];

        // Important: Do not check for uploaded files here,
        // otherwise it is no more possible to remove files (please ignore bug reports like 10137)

        $sizeOfSelectedFiles = 0;
        $filesOfRequest = $this->http->wrapper()->query()->retrieve(
            'mail_attachments_filename',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        if ($filesOfRequest !== [] && $filesOfRequest[0] === 'ALL_OBJECTS') {
            $filesOfRequest = array_map(static function (array $file): string {
                return $file['name'];
            }, $this->mfile->getUserFilesData());
        }

        foreach ($filesOfRequest as $file) {
            if (is_file($this->mfile->getMailPath() . '/' . basename($this->user->getId() . '_' . urldecode($file)))) {
                $files[] = urldecode($file);
                $sizeOfSelectedFiles += filesize(
                    $this->mfile->getMailPath() . '/' .
                    basename($this->user->getId() . '_' . urldecode($file))
                );
            }
        }

        if ($files !== [] &&
            null !== $this->mfile->getAttachmentsTotalSizeLimit() &&
            $sizeOfSelectedFiles > $this->mfile->getAttachmentsTotalSizeLimit()) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('mail_max_size_attachments_total_error') . ' ' .
                ilUtil::formatSize((int) $this->mfile->getAttachmentsTotalSizeLimit())
            );
            $this->showAttachments();
            return;
        }

        $this->umail->saveAttachments($files);

        $this->ctrl->returnToParent($this);
    }

    public function cancelSaveAttachments(): void
    {
        $this->ctrl->setParameter($this, 'type', ilMailFormGUI::MAIL_FORM_TYPE_ATTACH);
        $this->ctrl->returnToParent($this);
    }

    public function deleteAttachments(): void
    {
        $files = $this->http->wrapper()->query()->retrieve(
            'mail_attachments_filename',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        if ($files !== [] && $files[0] === 'ALL_OBJECTS') {
            $files = array_map(static function (array $file): string {
                return $file['name'];
            }, $this->mfile->getUserFilesData());
        }

        if ($files === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this);
        }

        $this->tpl->setTitle($this->lng->txt('mail'));

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, 'confirmDeleteAttachments'));
        $confirmation->setConfirm($this->lng->txt('confirm'), 'confirmDeleteAttachments');
        $confirmation->setCancel($this->lng->txt('cancel'), 'showAttachments');
        $confirmation->setHeaderText($this->lng->txt('mail_sure_delete_file'));

        foreach ($files as $filename) {
            $confirmation->addItem(
                'filename[]',
                ilUtil::stripSlashes($filename),
                ilUtil::stripSlashes(urldecode($filename))
            );
        }

        $this->tpl->setContent($confirmation->getHTML());
        $this->tpl->printToStdout();
    }

    public function confirmDeleteAttachments(): void
    {
        $files = $this->http->wrapper()->post()->retrieve(
            'filename',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string()),
                $this->refinery->always([])
            ])
        );

        if ($files === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('mail_select_one_mail'));
            $this->showAttachments();
            return;
        }

        $decodedFiles = [];
        foreach ($files as $value) {
            $decodedFiles[] = urldecode($value);
        }

        $error = $this->mfile->unlinkFiles($decodedFiles);
        if ($error !== '') {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_error_delete_file') . ' ' . $error, true);
        } else {
            $mail_data = $this->umail->retrieveFromStage();
            if (is_array($mail_data['attachments'])) {
                $tmp = [];
                foreach ($mail_data['attachments'] as $attachment) {
                    if (!in_array($attachment, $decodedFiles, true)) {
                        $tmp[] = $attachment;
                    }
                }
                $this->umail->saveAttachments($tmp);
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_files_deleted'), true);
        }

        $this->ctrl->redirect($this);
    }

    public function showAttachments(): void
    {
        $this->tpl->setTitle($this->lng->txt('mail'));

        if ($this->mode === AttachmentManagement::CONSUME) {
            $this->tabs->clearTargets();
            $this->tabs->setBackTarget(
                $this->lng->txt('mail_manage_attachments_back_to_compose'),
                $this->ctrl->getLinkTarget($this, 'cancelSaveAttachments')
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
                        $this->lng->txt('upload'),
                        '#'
                    )
                );
            $components[] = $dropzone;
        }

        $mail_data = $this->umail->retrieveFromStage();
        $files = $this->mfile->getUserFilesData();
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
            $records,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $this->ctrl,
            $this->http->request(),
            new ILIAS\Data\Factory(),
            'handleTableActions',
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

    private function handleTableActions(): void
    {
        $query = $this->http->wrapper()->query();
        if (!$query->has('mail_attachments_table_action')) {
            return;
        }

        $action = $query->retrieve('mail_attachments_table_action', $this->refinery->to()->string());
        match ($action) {
            'saveAttachments' => $this->saveAttachments(),
            'deleteAttachments' => $this->deleteAttachments(),
            default => $this->ctrl->redirect($this),
        };
    }

    protected function getUploadResult(): HandlerResult
    {
        $this->upload->process();
        $array = $this->upload->getResults();
        $result = end($array);

        if ($result instanceof UploadResult && $result->isOK()) {
            $identifier = $this->mfile->storeUploadedFile($result);
            $status = HandlerResult::STATUS_OK;
            $message = $this->lng->txt('saved_successfully');
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
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
