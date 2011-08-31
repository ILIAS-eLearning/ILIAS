<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Table/classes/class.ilTable2GUI.php';

/** 
* 
* @author Jan Posselt <jposselt@databay.de>
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesMail
*/
class ilMailFolderTableGUI extends ilTable2GUI
{
	protected $lng = null;
	protected $ctrl = null;
	
	protected $_folderNode = array();	
	protected $_parentObject = null;	
	protected $_currentFolderId = 0;
	protected $_number_of_mails = 0;
	protected $_selectedItems = array();
	protected $_isTrashFolder = false;
	protected $_isDraftsFolder = false;
	protected $_isSentFolder = false;
	
	/**
	 * 
	 * Constructor
	 *
	 * @access	public
	 * @param	ilObjectGUI		$a_parent_obj	Pass an instance of ilObjectGUI
	 * @param	integer			$a_current_folder_id	Id of the current mail box folder
	 * @param	string			$a_parent_cmd	Command for the parent class
	 * 
	 */
	public function __construct($a_parent_obj, $a_current_folder_id, $a_parent_cmd = '')
	{
	 	global $lng, $ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;

		$this->_currentFolderId = $a_current_folder_id;
		$this->_parentObject = $a_parent_obj;
		
		$this->setId('mail_folder_tbl');	
		$this->setPrefix('mtable');
 
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setFormAction($this->ctrl->getFormAction($this->_parentObject, 'showFolder'));
		
		$this->setEnableTitle(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setDefaultOrderField('send_time');
		$this->setDefaultOrderDirection('desc');		
		$this->setSelectAllCheckbox('mail_id[]');		
		$this->setRowTemplate('tpl.mail_folder_row.html', 'Services/Mail');
	}
	
	/**
	 * 
	 * Call this before using getHTML()
	 *
	 * @access	public
	 * @return	ilMailFolderTableGUI	
	 * @final
	 * 
	 */	
	final public function prepareHTML()
	{
		global $ilUser;
		
		$this->addColumn('', 'sort', '5%', true);
		$this->addColumn($this->lng->txt('personal_picture'), '', '10%');
		if($this->isDraftFolder() || $this->isSentFolder())
			$this->addColumn($this->lng->txt('recipient'), 'rcp_to', '25%');
		else
			$this->addColumn($this->lng->txt('sender'), 'from', '25%');
		$this->addColumn($this->lng->txt('subject'), 'm_subject', '40%');
		$this->addColumn($this->lng->txt('date'), 'send_time', '20%');	
		
		// init folder data
		$mtree = new ilTree($ilUser->getId());
		$mtree->setTableNames('mail_tree', 'mail_obj_data');
		$this->_folderNode = $mtree->getNodeData($this->_currentFolderId);
		
		// command buttons
		$this->initCommandButtons();
		
		// mail actions
		$this->initMultiCommands($this->_parentObject->mbox->getActions($this->_currentFolderId));
		
		// fetch table data
		$this->fetchTableData();
		
		return $this;
	}
	
	/**
	 * 
	 * Setter/Getter for folder status
	 *
	 * @access	public
	 * @param	mixed	$a_bool	Boolean folder status or null
	 * @return	mixed	Either an object of type ilMailFolderTableGUI or the boolean folder status
	 * 
	 */	
	public function isDraftFolder($a_bool = null)
	{
		if(null === $a_bool)
		{
			return $this->_isDraftsFolder;
		}
		
		$this->_isDraftsFolder = $a_bool;
		
		return $this;
	}
	
	/**
	 * 
	 * Setter/Getter for folder status
	 *
	 * @access	public
	 * @param	mixed	$a_bool	Boolean folder status or null
	 * @return	mixed	Either an object of type ilMailFolderTableGUI or the boolean folder status
	 * 
	 */	
	public function isSentFolder($a_bool = null)
	{
		if(null === $a_bool)
		{
			return $this->_isSentFolder;
		}
		
		$this->_isSentFolder = $a_bool;
		
		return $this;
	}
	
	/**
	 * 
	 * Setter/Getter for folder status
	 *
	 * @access	public
	 * @param	mixed	$a_bool	Boolean folder status or null
	 * @return	mixed	Either an object of type ilMailFolderTableGUI or the boolean folder status
	 * 
	 */	
	public function isTrashFolder($a_bool = null)
	{
		if(null === $a_bool)
		{
			return $this->_isTrashFolder;
		}
		
		$this->_isTrashFolder = $a_bool;
		
		return $this;
	}
	
	/**
	 * 
	 * Performs special actions for folders such as user folders,
	 * trash and local folders
	 * 
	 * @access	private
	 * @return	ilMailFolderTableGUI
	 * 
	 */
	private function initCommandButtons()
	{
		if($this->_folderNode['m_type'] == 'trash')
		{
			$this->addCommandButton('askForEmptyTrash', $this->lng->txt('mail_empty_trash'));
		}
		
		return $this;
	}

	/**
	 *
	 * initMultiCommands
	 * 
	 * @access	private
	 * @param	array	$actions	array for multi commands
	 * @return	ilMailFolderTableGUI
	 * 
	 */
	private function initMultiCommands($actions)
	{
		foreach($actions as $key => $action)
		{
			if($key == 'moveMails')
			{
				$folders = $this->_parentObject->mbox->getSubFolders();
				foreach($folders as $folder)
				{
					if($folder['type'] != 'trash' || 
					   !$this->isTrashFolder())
					{
						if($folder['type'] != 'user_folder')
						{
							$label = $action.' '.$this->lng->txt('mail_'.$folder['title']).
								($folder['type'] == 'trash' ? ' ('.$this->lng->txt('delete').')' : '');
							$this->addMultiCommand($folder['obj_id'], $label);
						}
						else
							$this->addMultiCommand($folder['obj_id'], $action.' '.$folder['title']);
					}
				}
			}
			else
			{
				if($key != 'deleteMails' || $this->isTrashFolder())
					$this->addMultiCommand($key, $action);
			}
		}
		
		return $this;
	}
	
	/**
	 * 
	 * Caching method for user instances
	 *  
	 * @access	private
	 * @param	integer		$a_usr_id	Id of a user instance
	 * @return	ilObjUser
	 * 
	 */
	private function getUserInstance($a_usr_id)
	{		
		static $userObjectCache = array();
		
		if(isset($userObjectCache[$a_usr_id])) return $userObjectCache[$a_usr_id];
		
		$userObjectCache[$a_usr_id] = new ilObjUser($a_usr_id);
		
		return $userObjectCache[$a_usr_id];
	}
	
	/**
	 * 
	 * Set the selected items
	 *
	 * @access	public
	 * @param	integer	$a_selected_items	selected items	
	 * @return	ilMailFolderTableGUI
	 * 
	 */
	public function setSelectedItems($a_selected_items)
	{
		$this->_selectedItems = $a_selected_items;
		
		return $this;
	}
	
	/**
	 * 
	 * Get all selected items
	 *
	 * @access	public
	 * @return	integer	selected items	
	 * 
	 */
	public function getSelectedItems()
	{
		return $this->_selectedItems;
	}
	
	/**
	 * 
	 * fetchTableData
	 *
	 * @access	protected
	 * @return	ilMailFolderTableGUI	
	 * 
	 */
	protected function fetchTableData()
	{
		global $ilUser;
		
		$this->determineOffsetAndOrder();
		
		require_once 'Services/Mail/classes/class.ilMailBoxQuery.php';
		
		ilMailBoxQuery::$folderId = $this->_currentFolderId;
		ilMailBoxQuery::$userId = $ilUser->getId();
		ilMailBoxQuery::$limit = $this->getLimit();
		ilMailBoxQuery::$offset = $this->getOffset();
		ilMailBoxQuery::$orderDirection = $this->getOrderDirection();
		ilMailBoxQuery::$orderColumn = $this->getOrderField();		
		$data = ilMailBoxQuery::_getMailBoxListData();
		
		if(!count($data['set']) && $this->getOffset() > 0)
		{
			$this->resetOffset();
			
			ilMailBoxQuery::$limit = $this->getLimit();
			ilMailBoxQuery::$offset = $this->getOffset();			
			$data = ilMailBoxQuery::_getMailBoxListData();
		}
		
		$counter = 0;
		foreach($data['set'] as $key => $mail)
		{
			++$counter;

			if(is_array($this->getSelectedItems()) &&
			   in_array($mail['mail_id'], $this->getSelectedItems()))
			{
				$mail['checked'] = ' checked="checked" ';
			}

			// GET FULLNAME OF SENDER
			if($this->isDraftFolder() || $this->isSentFolder())
			{
				$mail['mail_login'] = $this->_parentObject->umail->formatNamesForOutput($mail['rcp_to']);
			}
			else
			{
				if($mail['sender_id'] != ANONYMOUS_USER_ID)
				{
					$tmp_user = $this->getUserInstance($mail['sender_id']); 
					if(in_array(ilObjUser::_lookupPref($mail['sender_id'], 'public_profile'), array('y', 'g')))
					{
						$mail['mail_from'] = $tmp_user->getFullname();
					}
					if(!($login = $tmp_user->getLogin()))
					{
						$login = $mail['import_name'].' ('.$this->lng->txt('user_deleted').')';
						$mail['mail_login'] = $mail['import_name'].' ('.$this->lng->txt('user_deleted').')'; 				
					}
					
					$mail['img_sender'] = $tmp_user->getPersonalPicturePath('xxsmall');
					$mail['mail_login'] = $mail['alt_sender'] = $login;
				}
				else
				{
					$tmp_user = $this->getUserInstance(ANONYMOUS_USER_ID);	
					
					$mail['img_sender'] = ilUtil::getImagePath('HeaderIcon_50.png');
					$mail['mail_login'] = $mail['alt_sender'] = ilMail::_getIliasMailerName();
				}
			}
			$mail['mailclass'] = $mail['m_status'] == 'read' ? 'mailread' : 'mailunread';
			
			// IF ACTUAL FOLDER IS DRAFT BOX, DIRECT TO COMPOSE MESSAGE
			if($this->isDraftFolder())
			{
				$this->ctrl->setParameterByClass('ilmailformgui', 'mail_id', $mail['mail_id']);
				$this->ctrl->setParameterByClass('ilmailformgui', 'type', 'draft');
				$mail['mail_link_read'] = $this->ctrl->getLinkTargetByClass('ilmailformgui');
				$this->ctrl->clearParametersByClass('ilmailformgui');
			}
			else
			{
				$this->ctrl->setParameter($this->_parentObject, 'mail_id', $mail['mail_id']);
				$this->ctrl->setParameter($this->_parentObject, 'cmd', 'showMail');
				$mail['mail_link_read'] = $this->ctrl->getLinkTarget($this->_parentObject);
				$this->ctrl->clearParameters($this->_parentObject);
			}
			
			$mail['mail_subject'] = htmlspecialchars($mail['m_subject']);
			$mail['mail_date'] = ilDatePresentation::formatDate(new ilDateTime($mail['send_time'], IL_CAL_DATETIME));
			
			$data['set'][$key] = $mail;
		}		
		
		$this->setData($data['set']);
		$this->setMaxCount($data['cnt']);
		$this->setNumerOfMails($data['cnt']);

		// table title
		$txt_folder = '';
		$img_folder = '';
		if($this->_folderNode['m_type'] == 'user_folder')
		{
			$txt_folder = $this->_folderNode['title'];
			$img_folder = 'icon_user_folder.gif';		
		}
		else
		{
			$txt_folder = $this->lng->txt('mail_'.$this->_folderNode['title']);
			$img_folder = 'icon'.substr($this->_folderNode['title'], 1).'.gif';
		}		
		$this->setTitleData($txt_folder, $data['cnt'], $data['cnt_unread'], $img_folder);
		
		return $this;
	}
	
	/**
	 * 
	 * Function to set the table title
	 *
	 * @access	protected
	 * @param	string	$folderLabel	table title
	 * @param	integer	$mailCount		number of total mails of the current folder
	 * @param	integer	$unreadCount	number of unread mails of the current folder
	 * @param	string	$imgFolder		image path
	 * @return	ilMailFolderTableGUI
	 * 
	 */
	protected function setTitleData($folderLabel, $mailCount, $unreadCount, $imgFolder)
	{
		$titleTemplate = new ilTemplate('tpl.mail_folder_title.html', true, true, 'Services/Mail');
		$titleTemplate->setVariable('TXT_FOLDER', $folderLabel);
		$titleTemplate->setVariable('MAIL_COUNT', $mailCount);
		$titleTemplate->setVariable('TXT_MAIL_S', $this->lng->txt('mail_s'));
		$titleTemplate->setVariable('MAIL_COUNT_UNREAD', $unreadCount);
		$titleTemplate->setVariable('TXT_UNREAD', $this->lng->txt('unread'));
		
		parent::setTitle($titleTemplate->get(), $imgFolder);
		
		return $this;
	}
	
	/**
	 * 
	 * Set the total number of mails of the current folder
	 *
	 * @access	public
	 * @param	integer	$a_number_of_mails	total number of mails of the current folder
	 * @return	ilMailFolderTableGUI
	 * 
	 */
	public function setNumerOfMails($a_number_of_mails)
	{
		$this->_number_of_mails = $a_number_of_mails;
		
		return $this;
	}
	
	/**
	 * 
	 * Returns the total number of mails of the current folder
	 *
	 * @access	public
	 * @return	integer	total number of mails of the current folder
	 * 
	 */
	public function getNumerOfMails()
	{
		return $this->_number_of_mails;
	}
	
	/**
	 * 
	 * Fill row
	 *
	 * @access	public
	 * @param	array	$a_set	result set array (assoc.)	
	 * 
	 */
	public function fillRow($a_set)
	{
		foreach($a_set as $key => $value)
		{
			$this->tpl->setVariable(strtoupper($key), $value);
		}		
	}	
} 
?>
