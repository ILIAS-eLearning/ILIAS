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

require_once "Services/Table/classes/class.ilTable2GUI.php";
require_once "Services/Mail/classes/class.ilMailingLists.php";
require_once "Services/Mail/classes/class.ilFormatMail.php";
require_once "Services/Mail/classes/class.ilAddressbook.php";

/**
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
* @ingroup ServicesMail
*/
class ilMailingListsGUI
{
	private $tpl = null;
	private $ctrl = null;
	private $lng = null;
	
	private $umail = null;	
	private $mlists = null;
	private $abook = null;
	
	private $error = array();
	
	public function __construct()
	{
		global $tpl, $ilCtrl, $lng, $ilUser;

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		
		$this->umail = new ilFormatMail($ilUser->getId());
		$this->abook = new ilAddressbook($ilUser->getId());
		
		$this->mlists = new ilMailingLists($ilUser);		
		$this->mlists->setCurrentMailingList($_GET['ml_id']);
		
		$this->ctrl->saveParameter($this, 'mobj_id');	
	}

	public function executeCommand()
	{
		$forward_class = $this->ctrl->getNextClass($this);
		switch ($forward_class)
		{
			default:
				if (!($cmd = $this->ctrl->getCmd()))
				{
					$cmd = 'showMailingLists';
				}

				$this->$cmd();
				break;
		}
		return true;
	}
	
	public function confirmDelete()
	{
		if (!isset($_POST['ml_id']))
	 	{
	 		ilUtil::sendInfo($this->lng->txt('mail_select_one_entry'));
	 		$this->showMailingLists();
	 		return true;
	 	}
	 	
	 	include_once('Services/Utilities/classes/class.ilConfirmationGUI.php');
		$c_gui = new ilConfirmationGUI();
		
		$c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDelete'));
		$c_gui->setHeaderText($this->lng->txt('mail_sure_delete_entry'));
		$c_gui->setCancel($this->lng->txt('cancel'), 'showMailingLists');
		$c_gui->setConfirm($this->lng->txt('confirm'), 'performDelete');

		$entries = $this->mlists->getSelected($_POST['ml_id']);		
		foreach($entries as $entry)
		{			
			$c_gui->addItem('ml_id[]', $entry->getId(), $entry->getTitle());
		}
		
		$this->tpl->setVariable('HEADER', $this->lng->txt('mail'));
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_list.html', 'Services/Mail');
		$this->tpl->setVariable('DELETE_CONFIRMATION', $c_gui->getHTML());
		
		$this->tpl->show();
	
		return true;	
	}
	
	public function performDelete()
	{
		if (is_array($_POST['ml_id']))
		{			
			foreach ($_POST['ml_id'] as $id)
			{
				$this->mlists->get(ilUtil::stripSlashes($id))->delete();
			}
			
			ilUtil::sendInfo($this->lng->txt('mail_deleted_entry'));			
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('mail_delete_error'));
		}
		
		$this->showMailingLists();
		
		return true;
	}
	
	public function mailToList()
	{
		global $ilUser;		
		
		if (!isset($_POST['ml_id']))
	 	{
	 		ilUtil::sendInfo($this->lng->txt('mail_select_one_entry'));
	 		$this->showMailingLists();	 		
	 		return true;
	 	}
	 	
	 	
	 	$mail_data = $this->umail->getSavedData();		
		if(!is_array($mail_data))
		{
			$this->umail->savePostData($ilUser->getId(), array(), '', '', '', '', '', '', '', '');
		}	
	
		$lists = array();
		foreach($_POST['ml_id'] as $id)
		{
			if(!$this->umail->doesRecipientStillExists('#il_ml_'.$id, $mail_data['rcp_to']))
			{
				$lists[] = '#il_ml_'.$id;			
			}
		}
		
		if(count($lists))
		{
			$mail_data = $this->umail->appendSearchResult($lists, 'to');
			$this->umail->savePostData(
				$mail_data['user_id'],
				$mail_data['attachments'],
				$mail_data['rcp_to'],
				$mail_data['rcp_cc'],
				$mail_data['rcp_bcc'],
				$mail_data['m_type'],
				$mail_data['m_email'],
				$mail_data['m_subject'],
				$mail_data['m_message'],
				$mail_data['use_placeholders']
			);
		}

		ilUtil::redirect("ilias.php?baseClass=ilMailGUI&type=search_res");
		
		return true;
	}
	
	public function showMailingLists()
	{
		$this->ctrl->setParameter($this, 'cmd', 'post');
		
		$this->tpl->setVariable('HEADER', $this->lng->txt('mail'));		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_list.html', 'Services/Mail');
						
		$tbl = new ilTable2GUI($this);
		$tbl->setFormAction($this->ctrl->getFormAction($this), 'showForm');
		$tbl->setTitle($this->lng->txt('mail_mailing_lists'));
		$tbl->setRowTemplate('tpl.mail_mailing_lists_listrow.html', 'Services/Mail');				

	 	$tbl->setDefaultOrderField('title');	
		
		$result = array();		
		
		$tbl->addColumn('', 'check', '10%');
	 	$tbl->addColumn($this->lng->txt('title'), 'title', '30%');
	 	$tbl->addColumn($this->lng->txt('description'), 'description', '30%');
	 	$tbl->addColumn($this->lng->txt('members'), 'members', '10%');		
		$tbl->addColumn('', 'edit', '20%');
		
		$entries = $this->mlists->getAll();
		if (count($entries))
		{
			$tbl->enable('select_all');				
			$tbl->setSelectAllCheckbox('ml_id');
			
			$counter = 0;
			foreach ($entries as $entry)
			{
				$result[$counter]['check'] = ilUtil::formCheckbox(0, 'ml_id[]', $entry->getId());
				$result[$counter]['title'] = $entry->getTitle() . " [#il_ml_" . $entry->getId() . "]";
				$result[$counter]['description'] = $entry->getDescription();
				$result[$counter]['members'] = count($entry->getAssignedEntries());				
				$this->ctrl->setParameter($this, 'ml_id',  $entry->getId());
				$result[$counter]['edit_text'] = $this->lng->txt("edit");
				$result[$counter]['edit_url'] = $this->ctrl->getLinkTarget($this, "showForm");
				$result[$counter]['members_text'] = $this->lng->txt("members");
				$result[$counter]['members_url'] = $this->ctrl->getLinkTarget($this, "showMembersList");

				++$counter;
			}
			
			$tbl->addMultiCommand('mailToList', $this->lng->txt('send_mail_to'));
			$tbl->addMultiCommand('confirmDelete', $this->lng->txt('delete'));			
		}
		else
		{
			$tbl->disable('header');
			$tbl->disable('footer');

			$tbl->setNoEntriesText($this->lng->txt('mail_search_no'));
		}

		$tbl->setData($result);
		
		$tbl->addCommandButton('showForm', $this->lng->txt('add'));
		
		$this->tpl->setVariable('MAILING_LISTS', $tbl->getHTML());		
		$this->tpl->show();
		
		return true;
	}
	
	public function saveForm()
	{		
		$this->mlists->getCurrentMailingList()->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->mlists->getCurrentMailingList()->setDescription(ilUtil::stripSlashes($_POST['description']));
		
		if ($_POST['title'] == '') $this->setError($this->lng->txt('title'));
		
		if (!$this->isError())
		{
			if ($this->mlists->getCurrentMailingList()->getId())
			{
				$this->mlists->getCurrentMailingList()->setChangedate(date("Y-m-d H:i:s", time()));
				$this->mlists->getCurrentMailingList()->update();
			}
			else
			{
				$this->mlists->getCurrentMailingList()->setCreatedate(date("Y-m-d H:i:s", time()));
				$this->mlists->getCurrentMailingList()->insert();
			}	
			
			ilUtil::sendInfo($this->lng->txt('saved_successfully'));
		}
		else
		{
			$mandatory = '';
			
			while ($error = $this->getError())
			{
				$mandatory .= $error;
				if ($this->isError()) $mandatory .= ', ';
			}			
			
			ilUtil::sendInfo($this->lng->txt('fill_out_all_required_fields') . ': ' . $mandatory);
		}	
		
		$this->showForm();
		
		return true;
	}
	
	public function showForm()
	{
		$this->tpl->setVariable('HEADER', $this->lng->txt('mail'));		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_form.html', 'Services/Mail');
		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		
		$form = new ilPropertyFormGUI();
		if ($this->mlists->getCurrentMailingList()->getId()) $this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveForm'));
		$form->setTitle($this->lng->txt('mail_mailing_list'));
		
		$formItem = new ilTextInputGUI($this->lng->txt('title'), 'title');		
		$formItem->setValue($this->mlists->getCurrentMailingList()->getTitle());
		$form->addItem($formItem);
		
		$formItem = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
		$formItem->setValue($this->mlists->getCurrentMailingList()->getDescription());
		$formItem->setCols(40);
		$formItem->setRows(8);
		$form->addItem($formItem);
		
		$form->addCommandButton('saveForm',$this->lng->txt('save'));
		$form->addCommandButton('showMailingLists',$this->lng->txt('cancel'));		
		
		$this->tpl->setVariable('FORM', $form->getHTML());
		$this->tpl->show();
		
		return true;	
	}	
	
	public function showMembersList()
	{
		if (!$this->mlists->getCurrentMailingList()->getId())
		{
			$this->showMailingLists();
			
			return true;
		}
		
		$this->ctrl->setParameter($this, 'cmd', 'post');
		$this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
		
		$this->tpl->setVariable('HEADER', $this->lng->txt('mail'));		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_members.html', 'Services/Mail');
		
		$tbl = new ilTable2GUI($this);
		$tbl->setFormAction($this->ctrl->getFormAction($this), 'showMemberForm');
		$tbl->setTitle($this->lng->txt('mail_members_of_mailing_list') . ' ' .$this->mlists->getCurrentMailingList()->getTitle());
		$tbl->setRowTemplate('tpl.mail_mailing_lists_membersrow.html', 'Services/Mail');				

		$this->ctrl->setParameter($this, 'cmd', 'showMembersList');

	 	$tbl->setDefaultOrderField('title');	
		
		$result = array();		
		
		$tbl->addColumn('', 'check', '10%');
	 	$tbl->addColumn($this->lng->txt('title'), 'title', '90%');
		
		$assigned_entries = $this->mlists->getCurrentMailingList()->getAssignedEntries();
		if (count($assigned_entries))
		{
			$tbl->enable('select_all');				
			$tbl->setSelectAllCheckbox('a_id');
			
			$counter = 0;
			foreach ($assigned_entries as $entry)
			{
				$result[$counter]['check'] = ilUtil::formCheckbox(0, 'a_id[]', $entry['a_id']);
				$result[$counter]['title'] = ($entry['login'] != '' ? $entry['login'] : $entry['email']);

				++$counter;
			}
			
			$tbl->addMultiCommand('confirmDeleteMembers', $this->lng->txt('delete'));
		}
		else
		{
			$tbl->disable('header');
			$tbl->disable('footer');

			$tbl->setNoEntriesText($this->lng->txt('mail_search_no'));
		}

		$tbl->setData($result);
		
		$tbl->addCommandButton('showAssignmentForm', $this->lng->txt('add'));
		$tbl->addCommandButton('showMailingLists', $this->lng->txt('back'));
	
		$this->tpl->setVariable('MEMBERS_LIST', $tbl->getHTML());
		$this->tpl->show();
	
		return true;	
	}
	
	public function confirmDeleteMembers()
	{
		if (!isset($_POST['a_id']))
	 	{
	 		ilUtil::sendInfo($this->lng->txt('mail_select_one_entry'));
	 		$this->showMembersList();
	 		return true;
	 	}
	 	
	 	include_once('Services/Utilities/classes/class.ilConfirmationGUI.php');
		$c_gui = new ilConfirmationGUI();
		$this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
		$c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDeleteMembers'));
		$c_gui->setHeaderText($this->lng->txt('mail_sure_delete_entry'));
		$c_gui->setCancel($this->lng->txt('cancel'), 'showMembersList');
		$c_gui->setConfirm($this->lng->txt('confirm'), 'performDeleteMembers');

		$assigned_entries = $this->mlists->getCurrentMailingList()->getAssignedEntries();
		if (is_array($assigned_entries))
		{
			foreach ($assigned_entries as $entry)
			{	
				if (in_array($entry['a_id'], $_POST['a_id']))
				{
					$c_gui->addItem('a_id[]', $entry['a_id'], ($entry['login'] != '' ? $entry['login'] : $entry['email']));		
				}
			}	
		}
				
		$this->tpl->setVariable('HEADER', $this->lng->txt('mail'));
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_members.html', 'Services/Mail');
		$this->tpl->setVariable('DELETE_CONFIRMATION', $c_gui->getHTML());
		
		$this->tpl->show();
	
		return true;	
	}
	
	public function performDeleteMembers()
	{
		if (is_array($_POST['a_id']))
		{			
			foreach ($_POST['a_id'] as $id)
			{
				$this->mlists->getCurrentMailingList()->deassignAddressbookEntry(ilUtil::stripSlashes($id));
			}
			
			ilUtil::sendInfo($this->lng->txt('mail_deleted_entry'));			
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('mail_delete_error'));
		}
		
		$this->showMembersList();
		
		return true;
	}
	
	public function saveAssignmentForm()
	{
		if ($_POST['addr_id'] == '') $this->setError($this->lng->txt('mail_entry_of_addressbook'));
		
		if (!$this->isError())
		{
			$this->mlists->getCurrentMailingList()->assignAddressbookEntry(ilUtil::stripSlashes($_POST['addr_id']));
			
			ilUtil::sendInfo($this->lng->txt('saved_successfully'));
			
			$this->showMembersList();
		}		
		else
		{
			$mandatory = '';
			
			while ($error = $this->getError())
			{
				$mandatory .= $error;
				if ($this->isError()) $mandatory .= ', ';
			}			
			
			ilUtil::sendInfo($this->lng->txt('fill_out_all_required_fields') . ': ' . $mandatory);
			
			$this->showAssignmentForm();	
		}
		
		return true;
	}
	
	public function showAssignmentForm()
	{	
		if (!$this->mlists->getCurrentMailingList()->getId())
		{
			$this->showMembersList();
			
			return true;
		}
			
		$this->tpl->setVariable('HEADER', $this->lng->txt('mail'));		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_members_form.html', 'Services/Mail');
		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		
		$form = new ilPropertyFormGUI();
		$this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveForm'));
		$form->setTitle($this->lng->txt('mail_assign_entry_to_mailing_list') . ' ' . $this->mlists->getCurrentMailingList()->getTitle());
		
		$options = array();
		$options[''] = $this->lng->txt('please_select');
		
		$noEntries = false;
		
		if (is_array($all_entries = $this->abook->getEntries()))
		{
			foreach ($all_entries as $entry)
			{
				$options[$entry['addr_id']] = ($entry['login'] != '' ? $entry['login'] : $entry['email']);
			}
		}
		else
		{
			$noEntries = true;
			ilUtil::sendInfo($this->lng->txt('mail_mailing_lists_no_addressbook_entries'));
		}

		if (is_array($assigned_entries = $this->mlists->getCurrentMailingList()->getAssignedEntries()))
		{
			foreach ($assigned_entries as $assigned_entry)
			{
				if (is_array($options) && array_key_exists($assigned_entry['addr_id'], $options))
				{
					unset($options[$assigned_entry['addr_id']]);
				}	
			}
		}

		if (count($options) > 1)
		{		
			$formItem = new ilSelectInputGUI($this->lng->txt('mail_entry_of_addressbook'), 'addr_id');		
			$formItem->setOptions($options);
			$formItem->setValue($this->mlists->getCurrentMailingList()->getTitle());
			$form->addItem($formItem);
			
			$form->addCommandButton('saveAssignmentForm',$this->lng->txt('assign'));			
		}
		else if (!$noEntries)
		{
			ilUtil::sendInfo($this->lng->txt('mail_mailing_lists_all_addressbook_entries_assigned'));
		}
		
		$form->addCommandButton('showMembersList',$this->lng->txt('cancel'));	
		
		$this->tpl->setVariable('FORM', $form->getHTML());
		$this->tpl->show();
		
		return true;
	}	
	
	public function setError($a_error = '')
	{
		return $this->error[] = $a_error;
	}
	public function getError()
	{
		return array_pop($this->error);
	}
	public function isError()
	{
		if (is_array($this->error) && !empty($this->error)) return true;
		
		return false;	
	}
}
?>
