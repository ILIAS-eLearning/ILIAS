<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Mail/classes/class.ilFormatMail.php";
require_once "Services/Mail/classes/class.ilFileDataMail.php";

/**
 * @author  Jens Conze
 * @version $Id$
 * @ingroup ServicesMail
 */
class ilMailAttachmentGUI
{
	/**
	 * @var ilTemplate
	 */
	private $tpl;

	/**
	 * @var ilCtrl
	 */
	private $ctrl;

	/**
	 * @var ilLanguage
	 */
	private $lng;

	/**
	 * @var ilFormatMail
	 */
	private $umail;

	/**
	 * @var ilFileDataMail
	 */
	private $mfile;

	public function __construct()
	{
		/**
		 * @var $tpl	ilTemplate
		 * @var $ilCtrl ilCtrl
		 * @var $lng	ilLanguage
		 * @var $ilUser ilObjUser
		 */
		global $tpl, $ilCtrl, $lng, $ilUser;

		$this->tpl  = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng  = $lng;

		$this->ctrl->saveParameter($this, 'mobj_id');

		$this->umail = new ilFormatMail($ilUser->getId());
		$this->mfile = new ilFileDataMail($ilUser->getId());
	}

	public function executeCommand()
	{
		$forward_class = $this->ctrl->getNextClass($this);
		switch($forward_class)
		{
			default:
				if(!($cmd = $this->ctrl->getCmd()))
				{
					$cmd = 'showAttachments';
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	public function saveAttachments()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		$files = array();

		if(count($_POST['filename']) == 0)
		{
			ilUtil::sendFailure($this->lng->txt('mail_select_one_file'));
			return $this->showAttachments();
		}
		
		if(is_array($_POST['filename']) && count($_POST['filename']) > 0)
		{
			foreach($_POST['filename'] as $file)
			{
				if(file_exists($this->mfile->getMailPath() . '/' . basename($ilUser->getId() . '_' . urldecode($file))))
				{
					$files[] = urldecode($file);
				}
			}
		}

		$this->umail->saveAttachments($files);

		$this->ctrl->returnToParent($this);
	}

	public function cancelSaveAttachments()
	{
		$this->ctrl->setParameter($this, 'type', 'attach');
		$this->ctrl->returnToParent($this);
	}

	public function deleteAttachments()
	{
		if(!isset($_POST['filename']) || !is_array($_POST['filename']) || !$_POST['filename'])
		{
			ilUtil::sendFailure($this->lng->txt('mail_select_one_file'));
			$this->showAttachments();
			return;
		}

		$this->tpl->setTitle($this->lng->txt('mail'));

		require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirmation = new ilConfirmationGUI();
		$confirmation->setFormAction($this->ctrl->getFormAction($this, 'confirmDeleteAttachments'));
		$confirmation->setConfirm($this->lng->txt('confirm'), 'confirmDeleteAttachments');
		$confirmation->setCancel($this->lng->txt('cancel'), 'showAttachments');
		$confirmation->setHeaderText($this->lng->txt('mail_sure_delete_file'));

		foreach($_POST['filename'] as $filename)
		{
			$confirmation->addItem('filename[]', ilUtil::stripSlashes($filename), ilUtil::stripSlashes(urldecode($filename)));
		}

		$this->tpl->setContent($confirmation->getHtml());
		$this->tpl->show();
	}

	public function confirmDeleteAttachments()
	{
		if(!isset($_POST['filename']) || !is_array($_POST['filename']) || !$_POST['filename'])
		{
			ilUtil::sendInfo($this->lng->txt('mail_select_one_mail'));
			$this->showAttachments();
			return true;
		}

		$files = array();
		foreach($_POST['filename'] as $value)
		{
			$files[] = urldecode($value);
		}

		if(strlen(($error = $this->mfile->unlinkFiles($files))))
		{
			ilUtil::sendFailure($this->lng->txt('mail_error_delete_file') . ' ' . $error);
		}
		else
		{
			$mailData = $this->umail->getSavedData();
			if(is_array($mailData['attachments']))
			{
				$tmp = array();
				for($i = 0; $i < count($mailData['attachments']); $i++)
				{
					if(!in_array($mailData['attachments'][$i], $files))
					{
						$tmp[] = $mailData['attachments'][$i];
					}
				}
				$this->umail->saveAttachments($tmp);
			}

			ilUtil::sendSuccess($this->lng->txt('mail_files_deleted'));
		}

		$this->showAttachments();
	}

	public function uploadFile()
	{
		if(strlen(trim($_FILES['userfile']['name'])))
		{
			if($this->mfile->storeUploadedFile($_FILES['userfile']) == 1)
			{
				ilUtil::sendFailure($this->lng->txt('mail_maxsize_attachment_error') . ' ' . ilFormat::formatSize($this->mfile->getUploadLimit()));
			}
		}

		$this->showAttachments();
	}

	public function showAttachments()
	{
		/**
		 * @var $ilToolbar ilToolbarGUI
		 */
		global $ilToolbar;

		$this->tpl->setTitle($this->lng->txt('mail'));

		require_once 'Services/Form/classes/class.ilFileInputGUI.php';
		$attachment = new ilFileInputGUI($this->lng->txt('upload'), 'userfile');
		$attachment->setRequired(true);
		$attachment->setSize(20);
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this, 'uploadFile'), true);
		$ilToolbar->addInputItem($attachment);
		$ilToolbar->addFormButton($this->lng->txt('upload'), 'uploadFile');

		require_once 'Services/Mail/classes/class.ilMailAttachmentTableGUI.php';
		$table = new ilMailAttachmentTableGUI($this, 'showAttachments');

		$mailData = $this->umail->getSavedData();
		$files    = $this->mfile->getUserFilesData();
		$data     = array();
		$counter  = 0;
		foreach($files as $file)
		{
			$checked = false;
			if(is_array($mailData['attachments']) && in_array($file['name'], $mailData['attachments']))
			{
				$checked = true;
			}

			$data[$counter] = array(
				'checked'       => $checked,
				'filename'      => $file['name'],
				'filesize'      => (int)$file['size'],
				'filecreatedate'=> (int)$file['ctime']
			);

			++$counter;
		}
		$table->setData($data);

		$this->tpl->setContent($table->getHtml());
		$this->tpl->show();
	}
}