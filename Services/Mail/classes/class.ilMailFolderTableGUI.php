<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/Mail/classes/class.ilMailUserCache.php';
require_once 'Services/Mail/classes/class.ilMailBoxQuery.php';

/**
 * @author  Jan Posselt <jposselt@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
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
	 * @var array
	 */
	protected $filter = array();

	/**
	 * @var array
	 */
	protected $sub_filter = array();
	
	/**
	 * Constructor
	 * @access	public
	 * @param	ilObjectGUI		$a_parent_obj		   Pass an instance of ilObjectGUI
	 * @param	integer			$a_current_folder_id	Id of the current mail box folder
	 * @param	string			 $a_parent_cmd		   Command for the parent class
	 *
	 */
	public function __construct($a_parent_obj, $a_current_folder_id, $a_parent_cmd = '')
	{
		/**
		 * @var $lng	ilLanguage
		 * @var $ilCtrl ilCtrl
		 */
		global $lng, $ilCtrl;

		$this->lng  = $lng;
		$this->ctrl = $ilCtrl;

		$this->_currentFolderId = $a_current_folder_id;
		$this->_parentObject = $a_parent_obj;
		
		$this->setId('mail_folder_tbl_'.$a_current_folder_id);
		$this->setPrefix('mtable');

		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setDefaultOrderField('send_time');
		$this->setDefaultOrderDirection('desc');

		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setFormAction($this->ctrl->getFormAction($this->_parentObject, 'showFolder'));
		
		$this->setEnableTitle(true);
		$this->setSelectAllCheckbox('mail_id[]');		
		$this->setRowTemplate('tpl.mail_folder_row.html', 'Services/Mail');

		$this->setFilterCommand('applyFilter');
		$this->setResetCommand('resetFilter');
	}

	/**
	 * Call this before using getHTML()
	 * @access	public
	 * @return	ilMailFolderTableGUI
	 * @final
	 *
	 */
	final public function prepareHTML()
	{
		global $ilUser;

		$this->addColumn('', '', '1px', true);
		$this->addColumn($this->lng->txt('personal_picture'), '', '10%');
		if($this->isDraftFolder() || $this->isSentFolder())
			$this->addColumn($this->lng->txt('recipient'), 'rcp_to', '25%');
		else
			$this->addColumn($this->lng->txt('sender'), 'from', '25%');
		
		if($this->isLuceneSearchEnabled())
		{
			$this->addColumn($this->lng->txt('search_content'), '', '40%');
		}
		else
		{
			$this->addColumn($this->lng->txt('subject'), 'm_subject', '40%');
		}
		$this->addColumn($this->lng->txt('date'), 'send_time', '20%');

		// init folder data
		$mtree = new ilTree($ilUser->getId());
		$mtree->setTableNames('mail_tree', 'mail_obj_data');
		$this->_folderNode = $mtree->getNodeData($this->_currentFolderId);

		// fetch table data
		$this->fetchTableData();

		// command buttons
		$this->initCommandButtons();

		// mail actions
		$this->initMultiCommands($this->_parentObject->mbox->getActions($this->_currentFolderId));

		return $this;
	}

	/**
	 * Setter/Getter for folder status
	 * @access	public
	 * @param	mixed	$a_bool	Boolean folder status or null
	 * @return	bool|ilMailFolderTableGUI	Either an object of type ilMailFolderTableGUI or the boolean folder status
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
	 * Setter/Getter for folder status
	 * @access	public
	 * @param	mixed	$a_bool	Boolean folder status or null
	 * @return	bool|ilMailFolderTableGUI	Either an object of type ilMailFolderTableGUI or the boolean folder status
	 
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
	 * Setter/Getter for folder status
	 * @access	public
	 * @param	mixed	$a_bool	Boolean folder status or null
	 * @return	bool|ilMailFolderTableGUI	Either an object of type ilMailFolderTableGUI or the boolean folder status
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
	 * Performs special actions for folders such as user folders,
	 * trash and local folders
	 * @access	private
	 * @return	ilMailFolderTableGUI
	 *
	 */
	private function initCommandButtons()
	{
		if($this->_folderNode['m_type'] == 'trash' && $this->getNumerOfMails() > 0)
		{
			$this->addCommandButton('askForEmptyTrash', $this->lng->txt('mail_empty_trash'));
		}

		return $this;
	}

	/**
	 * initMultiCommands
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
						!$this->isTrashFolder()
					)
					{
						if($folder['type'] != 'user_folder')
						{
							$label = $action . ' ' . $this->lng->txt('mail_' . $folder['title']) .
								($folder['type'] == 'trash' ? ' (' . $this->lng->txt('delete') . ')' : '');
							$this->addMultiCommand($folder['obj_id'], $label);
						}
						else
							$this->addMultiCommand($folder['obj_id'], $action . ' ' . $folder['title']);
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
	 * Set the selected items
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
	 * Get all selected items
	 * @access	public
	 * @return	integer	selected items
	 *
	 */
	public function getSelectedItems()
	{
		return $this->_selectedItems;
	}

	/**
	 * @return bool
	 */
	protected function isLuceneSearchEnabled()
	{
		include_once 'Services/Search/classes/class.ilSearchSettings.php';
		if(ilSearchSettings::getInstance()->enabledLucene() && strlen($this->filter['mail_filter']))
		{
			return true;
		}
	}

	/**
	 * fetchTableData
	 * @access	protected
	 * @return	ilMailFolderTableGUI
	 *
	 */
	protected function fetchTableData()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		// table title
		if($this->_folderNode['m_type'] == 'user_folder')
		{
			$txt_folder = $this->_folderNode['title'];
			$img_folder = 'icon_user_folder.png';
		}
		else
		{
			$txt_folder = $this->lng->txt('mail_' . $this->_folderNode['title']);
			$img_folder = 'icon' . substr($this->_folderNode['title'], 1) . '.png';
		}

		try
		{
			if($this->isLuceneSearchEnabled())
			{
				include_once 'Services/Mail/classes/class.ilMailLuceneQueryParser.php';
				$query_parser = new ilMailLuceneQueryParser($this->filter['mail_filter']);
				$query_parser->setFields(array(
					'title'       => (bool)$this->filter['mail_filter_subject'],
					'content'     => (bool)$this->filter['mail_filter_body'],
					'mattachment' => (bool)$this->filter['mail_filter_attach'],
					'msender'     => (bool)$this->filter['mail_filter_sender'],
					'mrcp'        => (bool)$this->filter['mail_filter_recipients']
				));
				$query_parser->parse();
				
				require_once 'Services/Mail/classes/class.ilMailLuceneSearcher.php';
				require_once 'Services/Mail/classes/class.ilMailSearchResult.php';
				$result = new ilMailSearchResult();
				$searcher = new ilMailLuceneSearcher($query_parser, $result);
				$searcher->search($ilUser->getId(), $this->_currentFolderId);

				if(!$result->getIds())
				{
					throw new ilException('mail_search_empty_result');
				}
	
				ilMailBoxQuery::$filtered_ids   = $result->getIds();
				ilMailBoxQuery::$filter         = array();
			}
			else
			{
				ilMailBoxQuery::$filter         = (array)$this->filter;
			}
				
			$this->determineOffsetAndOrder();
	
			ilMailBoxQuery::$folderId       = $this->_currentFolderId;
			ilMailBoxQuery::$userId         = $ilUser->getId();
			ilMailBoxQuery::$limit          = $this->getLimit();
			ilMailBoxQuery::$offset         = $this->getOffset();
			ilMailBoxQuery::$orderDirection = $this->getOrderDirection();
			ilMailBoxQuery::$orderColumn    = $this->getOrderField();
			$data                           = ilMailBoxQuery::_getMailBoxListData();
			
			if(!count($data['set']) && $this->getOffset() > 0)
			{
				$this->resetOffset();
	
				ilMailBoxQuery::$limit  = $this->getLimit();
				ilMailBoxQuery::$offset = $this->getOffset();
				$data                   = ilMailBoxQuery::_getMailBoxListData();
			}
		}
		catch(Exception $e)
		{
			$this->setTitleData($txt_folder, 0, 0, $img_folder);

			if('mail_search_empty_result' == $e->getMessage())
			{
				$data['set'] = array();
				$data['cnt'] = 0;
			}
			else
			{
				throw $e;
			}
		}

		if(!$this->isDraftFolder() && !$this->isSentFolder())
		{
			$user_ids = array();
			foreach($data['set'] as $mail)
			{
				if($mail['sender_id'] && $mail['sender_id'] != ANONYMOUS_USER_ID)
				{
					$user_ids[$mail['sender_id']] = $mail['sender_id'];
				}
			}

			ilMailUserCache::preloadUserObjects($user_ids);
		}

		$counter = 0;
		foreach($data['set'] as $key => $mail)
		{
			++$counter;

			if(is_array($this->getSelectedItems()) && in_array($mail['mail_id'], $this->getSelectedItems()))
			{
				$mail['checked'] = ' checked="checked" ';
			}

			if($this->isDraftFolder() || $this->isSentFolder())
			{
				$mail['rcp_to'] = $mail['mail_login'] = $this->_parentObject->umail->formatNamesForOutput($mail['rcp_to']);
			}
			else
			{
				if($mail['sender_id'] == ANONYMOUS_USER_ID)
				{
					$mail['img_sender'] = ilUtil::getImagePath('HeaderIconAvatar.svg');
					$mail['from'] = $mail['mail_login'] = $mail['alt_sender'] = ilMail::_getIliasMailerName();
				}
				else
				{
					$user = ilMailUserCache::getUserObjectById($mail['sender_id']);
					if($user)
					{
						$mail['img_sender'] = $user->getPersonalPicturePath('xxsmall');
						$mail['from'] = $mail['mail_login'] = $mail['alt_sender'] = $user->getPublicName();
					}
					else
					{
						$mail['from'] = $mail['mail_login'] = $mail['import_name'] . ' (' . $this->lng->txt('user_deleted') . ')';
					}
				}
			}

			if($this->isDraftFolder())
			{
				$this->ctrl->setParameterByClass('ilmailformgui', 'mail_id', $mail['mail_id']);
				$this->ctrl->setParameterByClass('ilmailformgui', 'type', 'draft');
				$link_mark_as_read = $this->ctrl->getLinkTargetByClass('ilmailformgui');
				$this->ctrl->clearParametersByClass('ilmailformgui');
			}
			else
			{
				$this->ctrl->setParameter($this->_parentObject, 'mail_id', $mail['mail_id']);
				$link_mark_as_read = $this->ctrl->getLinkTarget($this->_parentObject, 'showMail');
				$this->ctrl->clearParameters($this->_parentObject);
			}
			$css_class = $mail['m_status'] == 'read' ? 'mailread' : 'mailunread';

			if($this->isLuceneSearchEnabled())
			{
				$search_result = array();
				foreach($result->getFields($mail['mail_id']) as $content)
				{
					if('title' == $content[0])
					{
						$mail['msr_subject_link_read'] = $link_mark_as_read;
						$mail['msr_subject_mailclass'] = $css_class;
						$mail['msr_subject']           = $content[1];
					}
					else
					{
						$search_result[] = $content[1];
					}
				}
				$mail['msr_data'] = implode('', array_map(function($value) {
					return '<p>'.$value.'</p>';
				}, $search_result));
				
				if(!$mail['msr_subject'])
				{
					$mail['msr_subject_link_read'] = $link_mark_as_read;
					$mail['msr_subject_mailclass'] = $css_class;
					$mail['msr_subject']           = htmlspecialchars($mail['m_subject']);
				}
			}
			else
			{
				$mail['mail_link_read'] = $link_mark_as_read;
				$mail['mailclass']      = $css_class;
				$mail['mail_subject']   = htmlspecialchars($mail['m_subject']);
			}

			$mail['mail_date']    = ilDatePresentation::formatDate(new ilDateTime($mail['send_time'], IL_CAL_DATETIME));

			$data['set'][$key] = $mail;
		}

		$this->setData($data['set']);
		$this->setMaxCount($data['cnt']);
		$this->setNumerOfMails($data['cnt']);

		$this->setTitleData($txt_folder, $data['cnt'], $data['cnt_unread'], $img_folder);

		return $this;
	}

	/**
	 * Function to set the table title
	 * @access	protected
	 * @param	string	 $folderLabel	  table title
	 * @param	integer	$mailCount		number of total mails of the current folder
	 * @param	integer	$unreadCount	  number of unread mails of the current folder
	 * @param	string	 $imgFolder		image path
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
	 * Set the total number of mails of the current folder
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
	 * Returns the total number of mails of the current folder
	 * @access	public
	 * @return	integer	total number of mails of the current folder
	 *
	 */
	public function getNumerOfMails()
	{
		return $this->_number_of_mails;
	}

	/**
	 * Fill row
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

	public function initFilter()
	{
		$this->filter = array();
		
		include_once 'Services/Mail/classes/Form/class.ilMailQuickFilterInputGUI.php';
		$ti = new ilMailQuickFilterInputGUI($this->lng->txt('mail_filter'), 'mail_filter');
		$ti->setSubmitFormOnEnter(false);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter['mail_filter'] = $ti->getValue();

		include_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';

		if($this->isDraftFolder() || $this->isSentFolder())
		{
			$this->sub_filter[] = $ci = new ilCheckboxInputGUI($this->lng->txt('mail_filter_recipients'), 'mail_filter_recipients');
			$ci->setOptionTitle($this->lng->txt('mail_filter_recipients'));
			$ci->setValue(1);
			$ti->addSubItem($ci);
			$ci->setParent($this);
			$ci->readFromSession();
			$this->filter['mail_filter_recipients'] = (int)$ci->getChecked();
		}
		else
		{
			$this->sub_filter[] = $ci = new ilCheckboxInputGUI($this->lng->txt('mail_filter_sender'), 'mail_filter_sender');
			$ci->setOptionTitle($this->lng->txt('mail_filter_sender'));
			$ci->setValue(1);
			$ti->addSubItem($ci);
			$ci->setParent($this);
			$ci->readFromSession();
			$this->filter['mail_filter_sender'] = (int)$ci->getChecked();
		}

		$this->sub_filter[] = $ci = new ilCheckboxInputGUI($this->lng->txt('mail_filter_subject'), 'mail_filter_subject');
		$ci->setOptionTitle($this->lng->txt('mail_filter_subject'));
		$ci->setValue(1);
		$ti->addSubItem($ci);
		$ci->setParent($this);
		$ci->readFromSession();
		$this->filter['mail_filter_subject'] = (int)$ci->getChecked();

		$this->sub_filter[] = $ci = new ilCheckboxInputGUI($this->lng->txt('mail_filter_body'), 'mail_filter_body');
		$ci->setOptionTitle($this->lng->txt('mail_filter_body'));
		$ci->setValue(1);
		$ti->addSubItem($ci);
		$ci->setParent($this);
		$ci->readFromSession();
		$this->filter['mail_filter_body'] = (int)$ci->getChecked();

		$this->sub_filter[] = $ci = new ilCheckboxInputGUI($this->lng->txt('mail_filter_attach'), 'mail_filter_attach');
		$ci->setOptionTitle($this->lng->txt('mail_filter_attach'));
		$ci->setValue(1);
		$ti->addSubItem($ci);
		$ci->setParent($this);
		$ci->readFromSession();
		$this->filter['mail_filter_attach'] = (int)$ci->getChecked();
	}

	/**
	 * Write filter values to session
	 */
	public function writeFilterToSession()
	{
		parent::writeFilterToSession();

		foreach($this->sub_filter as $item)
		{
			if($item->checkInput())
			{
				$item->setValueByArray($_POST);
				$item->writeToSession();
			}
		}
	}

	/**
	 * Reset filter
	 */
	public function resetFilter()
	{
		parent::resetFilter();

		foreach($this->sub_filter as $item)
		{
			if($item->checkInput())
			{
				$item->setValueByArray($_POST);
				$item->clearFromSession();
			}
		}
	}
} 
?>
