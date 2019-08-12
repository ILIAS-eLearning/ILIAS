<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Jens Conze
 * @version $Id$
 * @ingroup ServicesMail
 */
class ilMailAttachmentGUI
{
    /** @var ilGlobalPageTemplate */
    private $tpl;

    /** @var ilCtrl */
    private $ctrl;

    /** @var ilLanguage */
    private $lng;

    /** @var ilObjUser */
    private $user;

    /** @var ilToolbarGUI */
    private $toolbar;

    /** @var ilFormatMail */
    private $umail;

    /** @var ilFileDataMail */
    private $mfile;

    /** @var \Psr\Http\Message\ServerRequestInterface */
    private $request;

    /**
     * ilMailAttachmentGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->toolbar = $DIC->toolbar();
        $this->request = $DIC->http()->request();

        $this->ctrl->saveParameter($this, 'mobj_id');

        $this->umail = new ilFormatMail($DIC->user()->getId());
        $this->mfile = new ilFileDataMail($DIC->user()->getId());
    }

    public function executeCommand() : void
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

    public function saveAttachments() : void
    {
        $files = [];

        // Important: Do not check for uploaded files here, otherwise it is no more possible to remove files (please ignore bug reports like 10137)

        $sizeOfSelectedFiles = 0;
        $files = (array) ($this->request->getParsedBody()['filename'] ?? []);
        foreach ($files as $file) {
            if (file_exists($this->mfile->getMailPath() . '/' . basename($this->user->getId() . '_' . urldecode($file)))) {
                $files[] = urldecode($file);
                $sizeOfSelectedFiles += filesize($this->mfile->getMailPath() . '/' . basename($this->user->getId() . '_' . urldecode($file)));
            }
        }

        if (
            null !== $this->mfile->getAttachmentsTotalSizeLimit() &&
            $files && $sizeOfSelectedFiles > $this->mfile->getAttachmentsTotalSizeLimit()
        ) {
            ilUtil::sendFailure($this->lng->txt('mail_max_size_attachments_total_error') . ' ' . ilUtil::formatSize($this->mfile->getAttachmentsTotalSizeLimit()));
            $this->showAttachments();
            return;
        }

        $this->umail->saveAttachments($files);

        $this->ctrl->returnToParent($this);
    }

    public function cancelSaveAttachments() : void
    {
        $this->ctrl->setParameter($this, 'type', 'attach');
        $this->ctrl->returnToParent($this);
    }

    public function deleteAttachments() : void
    {
        $files = (array) ($this->request->getParsedBody()['filename'] ?? []);
        if (0 === count($files)) {
            ilUtil::sendFailure($this->lng->txt('mail_select_one_file'));
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

        $this->tpl->setContent($confirmation->getHtml());
        $this->tpl->printToStdout();
    }

    public function confirmDeleteAttachments() : void
    {
        $files = (array) ($this->request->getParsedBody()['filename'] ?? []);
        if (0 === count($files)) {
            ilUtil::sendInfo($this->lng->txt('mail_select_one_mail'));
            $this->showAttachments();
            return;
        }

        $decodedFiles = [];
        foreach ($files as $value) {
            $decodedFiles[] = urldecode($value);
        }

        $error = $this->mfile->unlinkFiles($decodedFiles);
        if (strlen($error) > 0) {
            ilUtil::sendFailure($this->lng->txt('mail_error_delete_file') . ' ' . $error);
        } else {
            $mailData = $this->umail->getSavedData();
            if (is_array($mailData['attachments'])) {
                $tmp = array();
                for ($i = 0; $i < count($mailData['attachments']); $i++) {
                    if (!in_array($mailData['attachments'][$i], $decodedFiles)) {
                        $tmp[] = $mailData['attachments'][$i];
                    }
                }
                $this->umail->saveAttachments($tmp);
            }

            ilUtil::sendSuccess($this->lng->txt('mail_files_deleted'));
        }

        $this->showAttachments();
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getToolbarForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $attachment = new ilFileInputGUI($this->lng->txt('upload'), 'userfile');
        $attachment->setRequired(true);
        $attachment->setSize(20);
        $form->addItem($attachment);

        return $form;
    }

    public function uploadFile() : void
    {
        if (strlen(trim($_FILES['userfile']['name']))) {
            $form = $this->getToolbarForm();
            if ($form->checkInput()) {
                $this->mfile->storeUploadedFile($_FILES['userfile']);
                ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
            } elseif ($form->getItemByPostVar('userfile')->getAlert() !== $this->lng->txt("form_msg_file_size_exceeds")) {
                ilUtil::sendFailure($form->getItemByPostVar('userfile')->getAlert());
            } else {
                ilUtil::sendFailure($this->lng->txt('mail_maxsize_attachment_error') . ' ' . ilUtil::formatSize($this->mfile->getUploadLimit()));
            }
        } else {
            ilUtil::sendFailure($this->lng->txt('mail_select_one_file'));
        }

        $this->showAttachments();
    }

    public function showAttachments() : void
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
        $data = array();
        $counter = 0;
        foreach ($files as $file) {
            $checked = false;
            if (is_array($mailData['attachments']) && in_array($file['name'], $mailData['attachments'])) {
                $checked = true;
            }

            $data[$counter] = array(
                'checked' => $checked,
                'filename' => $file['name'],
                'filesize' => (int) $file['size'],
                'filecreatedate' => (int) $file['ctime']
            );

            ++$counter;
        }
        $table->setData($data);

        $this->tpl->setContent($table->getHtml());
        $this->tpl->printToStdout();
    }
}
