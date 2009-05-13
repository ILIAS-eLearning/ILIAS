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

require_once './Services/User/classes/class.ilObjUser.php';
require_once 'Services/Mail/classes/class.ilMailbox.php';
require_once 'Services/Mail/classes/class.ilFormatMail.php';
require_once 'Services/Contact/classes/class.ilAddressbook.php';

/**
* @author Jens Conze
* @version $Id$
*
* @ingroup ServicesMail
*/
class ilMailSearchGroupsGUI
{
	private $tpl = null;
	private $ctrl = null;
	private $lng = null;
	
	private $umail = null;
	private $abook = null;

	public function __construct()
	{
		global $tpl, $ilCtrl, $lng, $ilUser;

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		
		$this->ctrl->saveParameter($this, "mobj_id");

		$this->umail = new ilFormatMail($ilUser->getId());
		$this->abook = new ilAddressbook($ilUser->getId());
	}

	public function executeCommand()
	{
		$forward_class = $this->ctrl->getNextClass($this);
		switch($forward_class)
		{
			default:
				if (!($cmd = $this->ctrl->getCmd()))
				{
					$cmd = "showMyGroups";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	function mail()
	{
		global $ilUser, $lng;

		if ($_GET["view"] == "mygroups")
		{
			if (is_array($_POST["search_grp"]))
			{
				$this->mailGroups();
			}
			else
			{
				ilUtil::sendInfo($lng->txt("mail_select_group"));
				$this->showMyGroups();
			}
		}
		else if ($_GET["view"] == "grp_members")
		{
			if (is_array($_POST["search_members"]))
			{
				$this->mailMembers();
			}
			else
			{
				ilUtil::sendInfo($lng->txt("mail_select_one_entry"));
				$this->showMembers();
			}
		}
		else
		{
			$this->showMyGroups();
		}
	}

	function mailGroups()
	{
		global $ilUser, $lng, $rbacreview;

		$members = array();

		if (!is_array($old_mail_data = $this->umail->getSavedData()))
		{
			$this->umail->savePostData(
				$ilUser->getId(),
				array(),
				"",
				"",
				"",
				"",
				"",
				"",
				"",
				""
			);
		}
		
		require_once 'classes/class.ilObject.php';
		foreach ($_POST["search_grp"] as $grp_id)
		{
			$ref_ids = ilObject::_getAllReferences($grp_id);
			foreach ($ref_ids as $ref_id)
			{
				$roles = $rbacreview->getAssignableChildRoles($ref_id);
				foreach ($roles as $role)
				{
					if (substr($role['title'], 0, 14) == 'il_grp_member_' ||
					    substr($role['title'], 0, 13) == 'il_grp_admin_')
					{
						if(isset($old_mail_data['rcp_to']) && 
						   trim($old_mail_data['rcp_to']) != '')
						{
							$rcpt = $rbacreview->getRoleMailboxAddress($role['obj_id']);
						
							if(!$this->umail->doesRecipientStillExists($rcpt, $old_mail_data['rcp_to']))							
								array_push($members, $rcpt);	
							
							unset($rcpt);
						}
						else
						{
							array_push($members, $rbacreview->getRoleMailboxAddress($role['obj_id']));
						}					
					}
				}
			}
		}
		
		if(count($members))
			$mail_data = $this->umail->appendSearchResult($members, 'to');
		else
			$mail_data = $this->umail->getSavedData();

		$this->umail->savePostData(
			$mail_data["user_id"],
			$mail_data["attachments"],
			$mail_data["rcp_to"],
			$mail_data["rcp_cc"],
			$mail_data["rcp_bcc"],
			$mail_data["m_type"],
			$mail_data["m_email"],
			$mail_data["m_subject"],
			$mail_data["m_message"],
			$mail_data["use_placeholders"]
		);
		
		ilUtil::redirect("ilias.php?baseClass=ilMailGUI&type=search_res");
	}

	function mailMembers()
	{
		$members = array();

		if (!is_array($this->umail->getSavedData()))
		{
			$this->umail->savePostData(
				$ilUser->getId(),
				array(),
				"",
				"",
				"",
				"",
				"",
				"",
				"",
				""
			);
		}
	
		foreach ($_POST["search_members"] as $member)
		{
			$login = ilObjUser::_lookupLogin($member);
			array_push($members, $login);
		}
		$mail_data = $this->umail->appendSearchResult($members,"to");

		$this->umail->savePostData(
			$mail_data["user_id"],
			$mail_data["attachments"],
			$mail_data["rcp_to"],
			$mail_data["rcp_cc"],
			$mail_data["rcp_bcc"],
			$mail_data["m_type"],
			$mail_data["m_email"],
			$mail_data["m_subject"],
			$mail_data["m_message"],
			$mail_data["use_placeholders"]
		);
	
		ilUtil::redirect("ilias.php?baseClass=ilMailGUI&type=search_res");
	}

	/**
	 * Take over course members to addressbook
	 */
	public function adoptMembers()
	{
		global $lng;

		if (is_array($_POST["search_members"]))
		{
			$members = array();
		
			foreach ($_POST["search_members"] as $member)
			{
				$login = ilObjUser::_lookupLogin($member);
	
				if (!$this->abook->checkEntry($login))
				{
					$name = ilObjUser::_lookupName($member);
					$email = '';
					if(ilObjUser::_lookupPref((int)$member, 'public_email') == 'y')
					{
						$email = ilObjUser::_lookupEmail($member);	
					}					
					$this->abook->addEntry(
						$login,
						$name["firstname"],
						$name["lastname"],
						$email
					);
				}
			}
			ilUtil::sendInfo($lng->txt("mail_members_added_addressbook"));
		}
		else
		{
			ilUtil::sendInfo($lng->txt("mail_select_one_entry"));
		}

		$this->showMembers();
	}
	
	/**
	 * Cancel action
	 */
	function cancel()
	{
		if ($_GET["view"] == "mygroups" &&
			$_GET["ref"] == "mail")
		{
			$this->ctrl->returnToParent($this);	
		}
		else
		{
			$this->showMyGroups();
		}
	}
	
	/**
	 * Show user's courses
	 */
	public function showMyGroups()
	{
		global $lng, $ilUser, $ilObjDataCache, $tree;

		include_once 'Modules/Group/classes/class.ilGroupParticipants.php';

		$this->tpl->setVariable('HEADER', $this->lng->txt('mail'));

		$searchTpl = new ilTemplate('tpl.mail_search_template.html', true, true, 'Services/Contact');
		
		$_GET['view'] = 'mygroups';

		$lng->loadLanguageModule('crs');
		
		$this->ctrl->setParameter($this, 'view', 'mygroups');
		
		include_once 'Services/Contact/classes/class.ilMailSearchCoursesTableGUI.php';
		$table = new ilMailSearchCoursesTableGUI($this, 'grp');
		
		$grp_ids = ilGroupParticipants::_getMembershipByType($ilUser->getId(), 'grp');
		
		$counter = 0;
		$tableData = array();
		if (is_array($grp_ids) &&
			count($grp_ids) > 0)
		{				
	

			foreach($grp_ids as $grp_id) 
			{
				if(ilObject::_hasUntrashedReference($grp_id))
				{
					$oGroupParticipants = ilGroupParticipants::_getInstanceByObjId($grp_id);
					$grp_members = $oGroupParticipants->getParticipants();

					foreach ($grp_members as $key => $member)
					{
						$tmp_usr = new ilObjUser($member);
						
						if($tmp_usr->checkTimeLimit()== false || $tmp_usr->getActive() == false )
						{
							unset($grp_members[$key]);
						}			
					}
					unset($tmp_usr);

					$ref_ids = ilObject::_getAllReferences($grp_id);
					$ref_id = current($ref_ids);				
					$path_arr = $tree->getPathFull($ref_id, $tree->getRootId());
					$path_counter = 0;
					$path = '';
					foreach($path_arr as $data)
					{
						if($path_counter++)
						{
							$path .= " -> ";
						}
						$path .= $data['title'];
					}
					$path = $this->lng->txt('path').': '.$path;
					
					$rowData = array
					(
						'CRS_ID' => $grp_id,
						'CRS_NAME' => $ilObjDataCache->lookupTitle($grp_id),
						'CRS_NO_MEMBERS' => count($grp_members),
						'CRS_PATH' => $path,
					);
					$counter++;
					$tableData[] = $rowData;
				}
			}
	
			if((int)$counter)
			{
				$table->addCommandButton('mail',$lng->txt('mail_members'));
				$table->addCommandButton('showMembers',$lng->txt('mail_list_members'));
			}
		}
		$table->setData($tableData);
		if($counter > 0)
		{
			$this->tpl->setVariable('TXT_MARKED_ENTRIES',$lng->txt('marked_entries'));
		}
		
		$searchTpl->setVariable('TABLE', $table->getHtml());
		$this->tpl->setContent($searchTpl->get());
		$this->tpl->show();
	}

	/**
	 * Show course members
	 */
	public function showMembers()
	{
		global $lng, $ilUser;

		if ($_GET["search_grp"] != "")
		{
			$_POST["search_grp"] = explode(",", $_GET["search_grp"]);
		}

		if (!is_array($_POST["search_grp"]) ||
			count($_POST["search_grp"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("mail_select_group"));
			$this->showMyGroups();
		}
		else
		{
			$this->tpl->setVariable("HEADER", $this->lng->txt("mail"));
			include_once 'Services/Contact/classes/class.ilMailSearchCoursesMembersTableGUI.php';
			$table = new ilMailSearchCoursesMembersTableGUI($this, 'grp');

			$lng->loadLanguageModule('crs');
	
			$counter = 0;
			$tableData = array();
			$searchTpl = new ilTemplate('tpl.mail_search_template.html', true, true, 'Services/Contact');
			
			foreach($_POST["search_grp"] as $grp_id) 
			{
				$ref_ids = ilObject::_getAllReferences($grp_id);
				$ref_id = current($ref_ids);

				if (is_object($group_obj = ilObjectFactory::getInstanceByRefId($ref_id,false)))
				{
					$grp_members = $group_obj->getGroupMemberData($group_obj->getGroupMemberIds());

					foreach($grp_members as $member)
					{ 
						$tmp_usr = new ilObjUser($member['id']);
						if($tmp_usr->checkTimeLimit()== false || $tmp_usr->getActive() == false )
						{
							unset($tmp_usr);
							continue;
						}
						unset($tmp_usr);
						
						$fullname = "";
						if(ilObjUser::_lookupPref($member['id'], 'public_profile') == 'y')
							$fullname = $member['lastname'].', '.$member['firstname'];

						$rowData = array(
							'MEMBERS_ID' => $member["id"],
							'MEMBERS_LOGIN' => $member["login"],
							'MEMBERS_NAME' => $fullname,
							'MEMBERS_CRS_GRP' => $group_obj->getTitle(),
							'MEMBERS_IN_ADDRESSBOOK' => $this->abook->checkEntryByLogin($member["login"]) ? $lng->txt("yes") : $lng->txt("no"),
						);
						$tableData[] = $rowData;
					}
				}
			}
			$table->setData($tableData);
			if (count($tableData))
			{
				$table->addCommandButton('mail', $lng->txt("grp_mem_send_mail"));
				$table->addCommandButton('adoptMembers', $lng->txt("mail_into_addressbook"));
				$searchTpl->setVariable("TXT_MARKED_ENTRIES",$lng->txt("marked_entries"));
			}
			$searchTpl->setVariable('TABLE', $table->getHtml());
			$this->tpl->setContent($searchTpl->get());
			$this->tpl->show();
		}
	}

}

?>
