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
require_once "Services/Contact/classes/class.ilMailingLists.php";
require_once "Services/Mail/classes/class.ilFormatMail.php";
require_once "Services/Contact/classes/class.ilAddressbook.php";

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
	
	private $form_gui = null;
	
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
		$ml_ids = ((int)$_GET['ml_id']) ? array($_GET['ml_id']) : $_POST['ml_id']; 
		if (!$ml_ids)
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

		$entries = $this->mlists->getSelected($ml_ids);		
		foreach($entries as $entry)
		{			
			$c_gui->addItem('ml_id[]', $entry->getId(), $entry->getTitle());
		}
		
		$this->tpl->setVariable('HEADER', $this->lng->txt('mail'));
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_list.html', 'Services/Contact');
		$this->tpl->setVariable('DELETE_CONFIRMATION', $c_gui->getHTML());
		
		$this->tpl->show();
	
		return true;	
	}
	
	public function performDelete()
	{
		global $ilUser;
		
		if (is_array($_POST['ml_id']))
		{			
			$counter = 0;
			foreach ($_POST['ml_id'] as $id)
			{
				if(ilMailingList::_isOwner($id, $ilUser->getId()))
				{
					$this->mlists->get(ilUtil::stripSlashes($id))->delete();
					++$counter;
				}			
			}
			if($counter)
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
		global $ilUser, $rbacsystem;	

		// check if current user may send mails
		include_once "Services/Mail/classes/class.ilMail.php";
		$mail = new ilMail($_SESSION["AccountId"]);
		$mailing_allowed = $rbacsystem->checkAccess('mail_visible',$mail->getMailObjectReferenceId());
	
		if (!$mailing_allowed)
		{
			ilUtil::sendFailure($this->lng->txt('no_permission'));
			return true;
		}

		$ml_ids = ((int)$_GET['ml_id']) ? array($_GET['ml_id']) : $_POST['ml_id']; 
		if (!$ml_ids)
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
		foreach($ml_ids as $id)
		{			
			if(ilMailingList::_isOwner($id, $ilUser->getId()) &&
			   !$this->umail->doesRecipientStillExists('#il_ml_'.$id, $mail_data['rcp_to']))
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
		global $rbacsystem;

		$this->tpl->setVariable('HEADER', $this->lng->txt('mail'));		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_list.html', 'Services/Contact');
		
		// check if current user may send mails
		include_once "Services/Mail/classes/class.ilMail.php";
		$mail = new ilMail($_SESSION["AccountId"]);
		$mailing_allowed = $rbacsystem->checkAccess('mail_visible',$mail->getMailObjectReferenceId());
				
		$tbl = new ilTable2GUI($this);
		$tbl->setFormAction($this->ctrl->getFormAction($this), 'showForm');
		$tbl->setTitle($this->lng->txt('mail_mailing_lists'));
		$tbl->setRowTemplate('tpl.mail_mailing_lists_listrow.html', 'Services/Contact');				

	 	$tbl->setDefaultOrderField('title');	
		
		$result = array();		
		
		$tbl->addColumn('', 'check', '10%', true);
	 	$tbl->addColumn($this->lng->txt('title'), 'title', '30%');
	 	$tbl->addColumn($this->lng->txt('description'), 'description', '30%');
	 	$tbl->addColumn($this->lng->txt('members'), 'members', '20%');		
		$tbl->addColumn($this->lng->txt('actions'), '', '10%');
		
		$entries = $this->mlists->getAll();
		if (count($entries))
		{
			$tbl->enable('select_all');				
			$tbl->setSelectAllCheckbox('ml_id');
			
			$counter = 0;
			
			include_once("./Services/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
			
			foreach ($entries as $entry)
			{
				$result[$counter]['check'] = ilUtil::formCheckbox(0, 'ml_id[]', $entry->getId());
				$result[$counter]['title'] = $entry->getTitle() . " [#il_ml_" . $entry->getId() . "]";
				$result[$counter]['description'] = $entry->getDescription();
				$result[$counter]['members'] = count($entry->getAssignedEntries());				
				
				$this->ctrl->setParameter($this, 'ml_id',  $entry->getId());
				//$result[$counter]['edit_text'] = $this->lng->txt("edit");
				//$result[$counter]['edit_url'] = $this->ctrl->getLinkTarget($this, "showForm");
				//$result[$counter]['members_text'] = $this->lng->txt("members");
				//$result[$counter]['members_url'] = $this->ctrl->getLinkTarget($this, "showMembersList");

				$current_selection_list = new ilAdvancedSelectionListGUI();
				$current_selection_list->setListTitle($this->lng->txt("actions"));
				$current_selection_list->setId("act_".$counter);

				$current_selection_list->addItem($this->lng->txt("edit"), '', $this->ctrl->getLinkTarget($this, "showForm"));
				$current_selection_list->addItem($this->lng->txt("members"), '', $this->ctrl->getLinkTarget($this, "showMembersList"));
				if ($mailing_allowed)
					$current_selection_list->addItem($this->lng->txt("send_mail_to"), '', $this->ctrl->getLinkTarget($this, "mailToList"));
				$current_selection_list->addItem($this->lng->txt("delete"), '', $this->ctrl->getLinkTarget($this, "confirmDelete"));
				
				$result[$counter]['COMMAND_SELECTION_LIST'] = $current_selection_list->getHTML();
				
				++$counter;
			}
			
			if ($mailing_allowed)
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
		if($this->mlists->getCurrentMailingList()->getId())
		{
			global $ilUser, $ilErr;
			
			if(!ilMailingList::_isOwner($this->mlists->getCurrentMailingList()->getId(), $ilUser->getId()))
			{
				$ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
			}
			
			$this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
			$this->initForm('edit');
		}
		else
		{
			$this->initForm();
		}
		
		if($this->form_gui->checkInput())
		{
			$this->mlists->getCurrentMailingList()->setTitle($_POST['title']);
			$this->mlists->getCurrentMailingList()->setDescription($_POST['description']);
			if($this->mlists->getCurrentMailingList()->getId())
			{
				$this->mlists->getCurrentMailingList()->setChangedate(date('Y-m-d H:i:s', time()));
				$this->mlists->getCurrentMailingList()->update();				
			}
			else
			{
				$this->mlists->getCurrentMailingList()->setCreatedate(date('Y-m-d H:i:s', time()));
				$this->mlists->getCurrentMailingList()->insert();
				$this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
				$this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'saveForm'));
			}
			
			ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		}
		
		$this->tpl->setVariable('HEADER', $this->lng->txt('mail'));		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_form.html', 'Services/Contact');
		
		$this->form_gui->setValuesByPost();
		
		$this->tpl->setVariable('FORM', $this->form_gui->getHTML());
		return $this->tpl->show();
	}
	
	private function initForm($a_type = 'create')
	{
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');		
		$this->form_gui = new ilPropertyFormGUI();
		
		$this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'saveForm'));
		$this->form_gui->setTitle($this->lng->txt('mail_mailing_list'));
		
		$titleGui = new ilTextInputGUI($this->lng->txt('title'), 'title');
		$titleGui->setRequired(true);
		$this->form_gui->addItem($titleGui);
		
		$descriptionGui = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');		
		$descriptionGui->setCols(40);
		$descriptionGui->setRows(8);
		$this->form_gui->addItem($descriptionGui);
		
		$this->form_gui->addCommandButton('saveForm',$this->lng->txt('save'));
		$this->form_gui->addCommandButton('showMailingLists',$this->lng->txt('cancel'));		
	}
	
	private function setValuesByObject()
	{
		$this->form_gui->setValuesByArray(array(
			'title' => $this->mlists->getCurrentMailingList()->getTitle(),
			'description' => $this->mlists->getCurrentMailingList()->getDescription()
		));
	}
	
	private function setDefaultValues()
	{
		$this->form_gui->setValuesByArray(array(
			'title' => '',
			'description' => ''
		));
	}
	
	public function showForm()
	{
		global $ilUser, $ilErr;
		
		$this->tpl->setVariable('HEADER', $this->lng->txt('mail'));		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_form.html', 'Services/Contact');
		
		if($this->mlists->getCurrentMailingList()->getId())
		{
			if(!ilMailingList::_isOwner($this->mlists->getCurrentMailingList()->getId(), $ilUser->getId()))
			{
				$ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
			}
			
			$this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
			$this->initForm('edit');
			$this->setValuesByObject();
		}
		else
		{
			$this->initForm();
			$this->setDefaultValues();
		}		
		
		$this->tpl->setVariable('FORM', $this->form_gui->getHTML());
		return $this->tpl->show();
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
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_members.html', 'Services/Contact');
		
		$tbl = new ilTable2GUI($this);
		$tbl->setFormAction($this->ctrl->getFormAction($this), 'showMemberForm');
		$tbl->setTitle($this->lng->txt('mail_members_of_mailing_list') . ' ' .$this->mlists->getCurrentMailingList()->getTitle());
		$tbl->setRowTemplate('tpl.mail_mailing_lists_membersrow.html', 'Services/Contact');				

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
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_members.html', 'Services/Contact');
		$this->tpl->setVariable('DELETE_CONFIRMATION', $c_gui->getHTML());
		
		$this->tpl->show();
	
		return true;	
	}
	
	public function performDeleteMembers()
	{
		global $ilUser, $ilErr;
		
		if(!ilMailingList::_isOwner($this->mlists->getCurrentMailingList()->getId(), $ilUser->getId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
		}
		
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
		global $ilUser, $ilErr;
		
		if(!ilMailingList::_isOwner($this->mlists->getCurrentMailingList()->getId(), $ilUser->getId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
		}
		
		if ($_POST['addr_id'] == '') $this->setError($this->lng->txt('mail_entry_of_addressbook'));

		if (!$this->isError())
		{
			$found = false;
			
			$all_entries = $this->abook->getEntries();
			if ((int)count($all_entries))
			{
				foreach ($all_entries as $entry)
				{
					if($entry['addr_id'] == $_POST['addr_id'])
					{
						$found = true;
						break;
					}
				}
			}
			
			if($found)
			{
				$this->mlists->getCurrentMailingList()->assignAddressbookEntry(ilUtil::stripSlashes($_POST['addr_id']));
				
				ilUtil::sendInfo($this->lng->txt('saved_successfully'));
			}
			
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
		global $ilUser, $ilErr;	
		
		if (!$this->mlists->getCurrentMailingList()->getId())
		{
			$this->showMembersList();
			
			return true;
		}
		
		if(!ilMailingList::_isOwner($this->mlists->getCurrentMailingList()->getId(), $ilUser->getId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
		}
			
		$this->tpl->setVariable('HEADER', $this->lng->txt('mail'));		
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail_mailing_lists_members_form.html', 'Services/Contact');
		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		
		$form = new ilPropertyFormGUI();
		$this->ctrl->setParameter($this, 'ml_id', $this->mlists->getCurrentMailingList()->getId());
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveForm'));
		$form->setTitle($this->lng->txt('mail_assign_entry_to_mailing_list') . ' ' . $this->mlists->getCurrentMailingList()->getTitle());
		
		$options = array();
		$options[''] = $this->lng->txt('please_select');
		
		$all_entries = $this->abook->getEntries();
		if ((int)count($all_entries))
		{
			foreach ($all_entries as $entry)
			{
				$options[$entry['addr_id']] = ($entry['login'] != '' ? $entry['login'] : $entry['email']);
			}
		}

		$assigned_entries = $this->mlists->getCurrentMailingList()->getAssignedEntries();
		if ((int)count($assigned_entries))
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
		else if(count($options) == 1 && (int)count($all_entries))
		{
			ilUtil::sendInfo($this->lng->txt('mail_mailing_lists_all_addressbook_entries_assigned'));
		}
		else if(!(int)count($all_entries))
		{			
			ilUtil::sendInfo($this->lng->txt('mail_mailing_lists_no_addressbook_entries'));
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
