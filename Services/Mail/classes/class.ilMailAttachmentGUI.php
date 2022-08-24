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
 * @author  Jens Conze
 * @version $Id$
 * @ingroup ServicesMail
 */
class ilMailAttachmentGUI
{
    private ilGlobalTemplateInterface $tpl;
    private ilCtrlInterface $ctrl;
    private ilLanguage $lng;
    private ilObjUser $user;
    private ilToolbarGUI $toolbar;
    private ilFormatMail $umail;
    private ilFileDataMail $mfile;
    private GlobalHttpState $http;
    private Refinery $refinery;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->toolbar = $DIC->toolbar();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->ctrl->saveParameter($this, 'mobj_id');

        $this->umail = new ilFormatMail($DIC->user()->getId());
        $this->mfile = new ilFileDataMail($DIC->user()->getId());
    }

    public function executeCommand(): void
    {
        $forward_class = $this->ctrl->getNextClass($this);
        switch ($forward_class) {
            default:
                if (!($cmd = $this->ctrl->getCmd())) {
                    $cmd = 'showAttachments';
                }

                $this->$cmd();
                break;
        }
    }

    public function saveAttachments(): void
    {
        $files = [];

        // Important: Do not check for uploaded files here,
        // otherwise it is no more possible to remove files (please ignore bug reports like 10137)

        $sizeOfSelectedFiles = 0;
        $filesOfRequest = [];
        if ($this->http->wrapper()->post()->has('filename')) {
            $filesOfRequest = $this->http->wrapper()->post()->retrieve(
                'filename',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }

        foreach ($filesOfRequest as $file) {
            if (is_file($this->mfile->getMailPath() . '/'
                . basename($this->user->getId() . '_' . urldecode($file)))
            ) {
                $files[] = urldecode($file);
                $sizeOfSelectedFiles += filesize(
                    $this->mfile->getMailPath() . '/' .
                    basename($this->user->getId() . '_' . urldecode($file))
                );
            }
        }

        if (
            $files !== [] &&
            null !== $this->mfile->getAttachmentsTotalSizeLimit() &&
            $sizeOfSelectedFiles > $this->mfile->getAttachmentsTotalSizeLimit()
        ) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_max_size_attachments_total_error') . ' ' .
            ilUtil::formatSize((int) $this->mfile->getAttachmentsTotalSizeLimit()));
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
        $files = [];
        if ($this->http->wrapper()->post()->has('filename')) {
            $files = $this->http->wrapper()->post()->retrieve(
                'filename',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }
        if ($files === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_select_one_file'));
            $this->showAttachments();
            return;
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
        $files = [];
        if ($this->http->wrapper()->post()->has('filename')) {
            $files = $this->http->wrapper()->post()->retrieve(
                'filename',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->string())
            );
        }

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
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_error_delete_file') . ' ' . $error);
        } else {
            $mailData = $this->umail->getSavedData();
            if (is_array($mailData['attachments'])) {
                $tmp = [];
                foreach ($mailData['attachments'] as $attachment) {
                    if (!in_array($attachment, $decodedFiles, true)) {
                        $tmp[] = $attachment;
                    }
                }
                $this->umail->saveAttachments($tmp);
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('mail_files_deleted'));
        }

        $this->showAttachments();
    }

    protected function getToolbarForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $attachment = new ilFileInputGUI($this->lng->txt('upload'), 'userfile');
        $attachment->setRequired(true);
        $attachment->setSize(20);
        $form->addItem($attachment);

        return $form;
    }

    public function uploadFile(): void
    {
        if (isset($_FILES['userfile']['name']) && trim($_FILES['userfile']['name']) !== '') {
            $form = $this->getToolbarForm();
            if ($form->checkInput()) {
                $this->mfile->storeUploadedFile($_FILES['userfile']);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
            } elseif ($form->getItemByPostVar('userfile')->getAlert() !==
                $this->lng->txt("form_msg_file_size_exceeds")
            ) {
                $this->tpl->setOnScreenMessage('failure', $form->getItemByPostVar('userfile')->getAlert());
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_maxsize_attachment_error') . ' ' .
                ilUtil::formatSize($this->mfile->getUploadLimit()));
            }
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('mail_select_one_file'));
        }

        $this->showAttachments();
    }

    public function showAttachments(): void
    {
        $this->tpl->setTitle($this->lng->txt('mail'));

        $attachment = new ilFileInputGUI($this->lng->txt('upload'), 'userfile');
        $attachment->setRequired(true);
        $attachment->setSize(20);
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, 'uploadFile'), true);
        $this->toolbar->addInputItem($attachment);
        $this->toolbar->addFormButton($this->lng->txt('upload'), 'uploadFile');

        $table = new ilMailAttachmentTableGUI($this, 'showAttachments');

        $mailData = $this->umail->getSavedData();
        $files = $this->mfile->getUserFilesData();
        $data = [];
        $counter = 0;
        foreach ($files as $file) {
            $checked = false;
            if (is_array($mailData['attachments']) && in_array($file['name'], $mailData['attachments'], true)) {
                $checked = true;
            }

            $data[$counter] = [
                'checked' => $checked,
                'filename' => $file['name'],
                'filesize' => (int) $file['size'],
                'filecreatedate' => (int) $file['ctime'],
            ];

            ++$counter;
        }
        $table->setData($data);

        $this->tpl->setContent($table->getHTML());
        $this->tpl->printToStdout();
    }
}
