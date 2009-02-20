<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once "Services/Mail/classes/class.ilFormatMail.php";
require_once "classes/class.ilFileDataMail.php";

/**
* @author Jens Conze
* @version $Id$
*
* @ingroup ServicesMail
*/
class ilMailAttachmentGUI
{
	private $tpl = null;
	private $ctrl = null;
	private $lng = null;
	
	private $umail = null;
	private $mfile = null;

	private $errorDelete = false;

	public function __construct()
	{
		global $tpl, $ilCtrl, $lng, $ilUser;

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		
		$this->ctrl->saveParameter($this, "mobj_id");

		$this->umail = new ilFormatMail($ilUser->getId());
		$this->mfile = new ilFileDataMail($ilUser->getId());
	}

	public function executeCommand()
	{
		$forward_class = $this->ctrl->getNextClass($this);
		switch($forward_class)
		{
			default:
				if (!($cmd = $this->ctrl->getCmd()))
				{
					$cmd = "showAttachments";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	public function saveAttachments()
	{
		$this->umail->saveAttachments($_POST["filename"]);
		
		$this->ctrl->returnToParent($this);
	}

	public function cancelSaveAttachments()
	{
		$this->ctrl->setParameter($this, "type", "attach");
		$this->ctrl->returnToParent($this);
	}

	public function deleteAttachments()
	{
		if(!$_POST["filename"])
		{
			ilUtil::sendInfo($this->lng->txt("mail_select_one_file"));
			$this->errorDelete = true;
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("mail_sure_delete_file"));
		}
		
		$this->showAttachments();
	}

	public function confirmDeleteAttachments()
	{
		if(!$_POST["filename"])
		{
			ilUtil::sendInfo($this->lng->txt("mail_select_one_mail"));
		}
		else if($error = $this->mfile->unlinkFiles($_POST["filename"]))
		{
			ilUtil::sendInfo($this->lng->txt("mail_error_delete_file")." ".$error);
		}
		else
		{
			$mailData = $this->umail->getSavedData();
			if (is_array($mailData["attachments"]))
			{
				$tmp = array();
				for ($i = 0; $i < count($mailData["attachments"]); $i++)
				{
					if (!in_array($mailData["attachments"][$i], $_POST["filename"]))
					{
						$tmp[] = $mailData["attachments"][$i];
					}
				}
				$mailData["attachments"] = $tmp;
				$this->umail->saveAttachments($tmp);
			}

			ilUtil::sendInfo($this->lng->txt("mail_files_deleted"));
		}

		$this->showAttachments();
	}

	public function cancelDeleteAttachments()
	{
		$this->showAttachments();
	}

	public function uploadFile()
	{
		if (trim($_FILES["userfile"]["name"]) != "")
		{
			if($this->mfile->storeUploadedFile($_FILES["userfile"]) == 1)
			{
				ilUtil::sendInfo($this->lng->txt("mail_maxsize_attachment_error")." ".$this->mfile->getUploadLimit()." K".$this->lng->txt("mail_byte"));
			}
		}
		
		$this->showAttachments();
	}

	public function showAttachments()
	{
		global $rbacsystem;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_attachment.html", "Services/Mail");
		$this->tpl->setVariable("HEADER", $this->lng->txt("mail"));
		$this->tpl->setVariable("TXT_ATTACHMENT",$this->lng->txt("attachment"));

		$mailData = $this->umail->getSavedData();

		$this->tpl->setVariable("ACTION", $this->ctrl->getFormAction($this, 'saveAttachments'));
		$this->tpl->setVariable("UPLOAD", $this->ctrl->getFormAction($this, 'uploadFile'));
		$this->ctrl->clearParameters($this);

		// BEGIN CONFIRM_DELETE
		if (isset($_POST["cmd"]["deleteAttachments"]) &&
			!$this->errorDelete &&
			!isset($_POST["cmd"]["confirm"]))
		{
			$this->tpl->setCurrentBlock("confirm_delete");
			$this->tpl->setVariable("BUTTON_CONFIRM",$this->lng->txt("confirm"));
			$this->tpl->setVariable("BUTTON_CANCEL",$this->lng->txt("cancel"));
			$this->tpl->parseCurrentBlock();
		}
		
		// SET STATIC VARIABLES ;-)
		$this->tpl->setVariable("TXT_ATTACHMENT",$this->lng->txt("attachment"));
		$this->tpl->setVariable("TXT_FILENAME",$this->lng->txt("mail_file_name"));
		$this->tpl->setVariable("TXT_FILESIZE",$this->lng->txt("mail_file_size"));
		$this->tpl->setVariable("TXT_CREATE_TIME",$this->lng->txt("create_date"));
		$this->tpl->setVariable("TXT_NEW_FILE",$this->lng->txt("mail_new_file"));
		
		// ACTIONS
		$this->tpl->setVariable("BUTTON_ATTACHMENT_ADOPT",$this->lng->txt("adopt"));
		$this->tpl->setVariable("BUTTON_ATTACHMENT_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BUTTON_ATTACHMENT_DELETE",$this->lng->txt("delete"));
		
		// BUTTONS
		$this->tpl->setVariable("BUTTON_SUBMIT",$this->lng->txt("submit"));
		$this->tpl->setVariable("BUTTON_UPLOAD",$this->lng->txt("upload"));
		
		// BEGIN FILES
		if($files = $this->mfile->getUserFilesData())
		{
			$counter = 0;
			foreach($files as $file)
			{
				$this->tpl->setCurrentBlock('files');
				if((!isset($_POST["cmd"]["deleteAttachments"]) && is_array($mailData["attachments"]) && in_array($file["name"],$mailData["attachments"])) ||
				   (isset($_POST["cmd"]["deleteAttachments"]) && is_array($_POST["filename"]) && in_array($file["name"],$_POST["filename"])))
				{
					$this->tpl->setVariable("CHECKED",'checked');
				}
				$this->tpl->setVariable('CSSROW',++$counter%2 ? 'tblrow1' : 'tblrow2');
				$this->tpl->setVariable('FILE_NAME',$file["name"]);
				$this->tpl->setVariable("NAME",$file["name"]);
				$this->tpl->setVariable("SIZE",$file["size"]);
				$this->tpl->setVariable("CTIME",$file["ctime"]);
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->tpl->setCurrentBlock("no_content");
			$this->tpl->setVariable("TXT_ATTACHMENT_NO",$this->lng->txt("mail_no_attachments_found"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TXT_MARKED_ENTRIES",$this->lng->txt("marked_entries"));

		$this->tpl->show();
	}

}

?>