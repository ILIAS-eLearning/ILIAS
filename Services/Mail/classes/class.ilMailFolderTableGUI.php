<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* 
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesMail
*/
include_once('Services/Table/classes/class.ilTable2GUI.php');

class ilMailFolderTableGUI extends ilTable2GUI
{
	protected $lng = null;
	protected $ctrl;
	protected $mbox;
	protected $current_folder;
	protected $folderNode;
	protected $parentObject;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($mbox, $a_parent_obj, $current_folder, $a_parent_cmd = '')
	{
	 	global $lng,$ilCtrl, $ilUser;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
		$this->mbox = $mbox;
		$this->current_folder = $current_folder;
		$this->parentObject = $a_parent_obj;
		
		 
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setPrefix('mtable');
		
		$this->addColumn('', 'sort', '5%', true);
		$this->addColumn($this->lng->txt('personal_picture'), '', '10%');
		$this->addColumn($this->lng->txt('sender'), 'MAIL_FROM', '25%');
		$this->addColumn($this->lng->txt('subject'), 'MAIL_SUBJECT', '40%');
		$this->addColumn($this->lng->txt('date'), 'MAIL_DATE', '20%');
		
		$this->setSelectAllCheckbox('mail_id[]');

		$this->setRowTemplate('tpl.mail_folder_row.html', 'Services/Mail');

		$ilCtrl->setParameter($a_parent_obj, $this->getNavParameter(), $_REQUEST[$this->getNavParameter()]);
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$ilCtrl->clearParameters($a_parent_obj);
		$mtree = new ilTree($ilUser->getId());
		$mtree->setTableNames('mail_tree','mail_obj_data');
		$this->folderNode = $mtree->getNodeData($_GET['mobj_id']);
		
		$this->setupFolderView();
	}
	
	/**
	* Performs special Actions for Folders such as User Folders,
	* Trash and Local Folders
	*/
	private function setupFolderView()
	{
		global $lng;
		if ($this->folderNode["type"] == 'user_folder' || $this->folderNode["type"] == 'local')
		{
			if ($this->folderNode["type"] == 'user_folder')
			{
				$this->addCommandButton('enterFolderData', $lng->txt('edit'));
			}
			$this->addCommandButton('addSubFolder', $lng->txt('mail_add_subfolder'));
		}
		else if ($this->folderNode["type"] == 'trash')
		{
			$this->addCommandButton('askForEmptyTrash', $lng->txt("mail_empty_trash"));
		}
	}

	public function setMailActions($actions)
	{
		$isTrashFolder = $this->current_folder == $this->mbox->getTrashFolder();
		foreach($actions as $key => $action)
		{
			if($key == 'moveMails')
			{
				$folders = $this->mbox->getSubFolders();
				foreach($folders as $folder)
				{
					if ($folder["type"] != 'trash' || !$isTrashFolder)
					{
						$this->tpl->setVariable("MAILACTION_VALUE", $folder["obj_id"]);
						if($folder["type"] != 'user_folder')
						{
							$label = $action . " " .
								$this->lng->txt("mail_".$folder["title"]) .
								($folder["type"] == 'trash' ? " (".$this->lng->txt("delete").")" : "");
							$this->addMultiCommand($folder["obj_id"], $label);
						}
						else
							$this->addMultiCommand($folder["obj_id"], $action." ". $folder["title"]);
					}
				}
			}
			else
			{
				if ($key != 'deleteMails' || $isTrashFolder)
					$this->addMultiCommand($key, $action);
			}
		}
	}
	
	public function setTitleData($folderLabel, $mailCount, $unreadCount, $imgFolder)
	{
		$titleTemplate = new ilTemplate('tpl.mail_folder_title.html', true, true, 'Services/Mail');
		$titleTemplate->setVariable('TXT_FOLDER', $folderLabel);
		$titleTemplate->setVariable('MAIL_COUNT', $mailCount);
		$titleTemplate->setVariable('TXT_MAIL_S', $this->lng->txt('mail_s'));
		$titleTemplate->setVariable('MAIL_COUNT_UNREAD', $unreadCount);
		$titleTemplate->setVariable('TXT_UNREAD', $this->lng->txt('unread'));
		parent::setTitle($titleTemplate->get(), $imgFolder);
	}
	
	/**
	 * Fill row
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function fillRow($a_set)
	{
		foreach ($a_set as $key => $value)
		{
			$this->tpl->setVariable(strtoupper($key), $value);
		}
		
	}	
} 
?>
