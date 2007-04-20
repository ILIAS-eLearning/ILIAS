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

require_once "classes/class.ilObjUser.php";
require_once "Services/Mail/classes/class.ilMailbox.php";
require_once "Services/Mail/classes/class.ilFormatMail.php";
require_once "Services/Mail/classes/class.ilAddressbook.php";

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
				$this->showGroupMembers();
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
					if (substr($role['title'],0,14) == 'il_grp_member_')
					{
						array_push($members, $rbacreview->getRoleMailboxAddress($role['obj_id']));
					}
				}
			}
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
			$mail_data["m_message"]
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
			$mail_data["m_message"]
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
					$email = ilObjUser::_lookupEmail($member);
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
		global $lng, $ilUser, $ilObjDataCache;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_addressbook_search.html", "Services/Mail");
		$this->tpl->setVariable("HEADER", $this->lng->txt("mail"));
		
		$_GET["view"] = "mygroups";

		$this->ctrl->setParameter($this, "cmd", "post");
		$this->ctrl->setParameter($this, "view", "mygroups");
		if ($_GET["ref"] != "") $this->ctrl->setParameter($this, "ref", $_GET["ref"]);
		if (is_array($_POST["search_grp"])) $this->ctrl->setParameter($this, "search_grp", implode(",", $_POST["search_grp"]));
		$this->tpl->setVariable("ACTION", $this->ctrl->getLinkTarget($this));
		$this->ctrl->clearParameters($this);

		$lng->loadLanguageModule('crs');
	
		$user = new ilObjUser($ilUser->getId());
		$grp_ids = $user->getGroupMemberships();
	
		$counter = 0;
		if (is_array($grp_ids) &&
					count($grp_ids) > 0)
		{
				
			$this->tpl->setVariable("GRP_TXT_GROUPS",$lng->txt("mail_my_groups"));
			$this->tpl->setVariable("GRP_TXT_NO_MEMBERS",$lng->txt("grp_count_members"));
		
			foreach($grp_ids as $grp_id) 
			{
/*				$ref_ids = ilObject::_getAllReferences($grp_id);
				$ref_id = current($ref_ids);

				$group_obj = new ilObjGroup($ref_id); 
				$grp_members = $group_obj->getCountMembers();*/
				$grp_members = ilObjGroup::_getMembers($grp_id);

				$this->tpl->setCurrentBlock("loop_grp");
				$this->tpl->setVariable("LOOP_GRP_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
				$this->tpl->setVariable("LOOP_GRP_ID",$grp_id);
				$this->tpl->setVariable("LOOP_GRP_NAME",$ilObjDataCache->lookupTitle($grp_id));
				$this->tpl->setVariable("LOOP_GRP_NO_MEMBERS",count($grp_members));
				$this->tpl->parseCurrentBlock();
			}
	
			$this->tpl->setVariable("BUTTON_MAIL",$lng->txt("mail_members"));
			$this->tpl->setVariable("BUTTON_LIST",$lng->txt("mail_list_members"));
		}
	
		if ($counter == 0)
		{
			$this->tpl->setCurrentBlock("grp_not_found");
			$this->tpl->setVariable("TXT_GRP_NOT_FOUND",$lng->txt("mail_search_groups_not_found"));
			$this->tpl->parseCurrentBlock();
		}

		if ($_GET["ref"] == "mail") $this->tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));

		$this->tpl->setVariable("TXT_MARKED_ENTRIES",$lng->txt("marked_entries"));
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
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_addressbook_search.html", "Services/Mail");
			$this->tpl->setVariable("HEADER", $this->lng->txt("mail"));
		
			$this->ctrl->setParameter($this, "cmd", "post");
			$this->ctrl->setParameter($this, "view", "grp_members");
			if ($_GET["ref"] != "") $this->ctrl->setParameter($this, "ref", $_GET["ref"]);
			if (is_array($_POST["search_grp"])) $this->ctrl->setParameter($this, "search_grp", implode(",", $_POST["search_grp"]));
			$this->tpl->setVariable("ACTION", $this->ctrl->getLinkTarget($this));
			$this->ctrl->clearParameters($this);

			$lng->loadLanguageModule('crs');
	
			$this->tpl->setCurrentBlock("members_group");
			$this->tpl->setVariable("MEMBERS_TXT_GROUP",$lng->txt("obj_grp"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("MEMBERS_TXT_LOGIN",$lng->txt("login"));
			$this->tpl->setVariable("MEMBERS_TXT_NAME",$lng->txt("name"));
			$this->tpl->setVariable("MEMBERS_TXT_IN_ADDRESSBOOK",$lng->txt("mail_in_addressbook"));
	
			$counter = 0;
			foreach($_POST["search_grp"] as $grp_id) 
			{
				$ref_ids = ilObject::_getAllReferences($grp_id);
				$ref_id = current($ref_ids);

				if (is_object($group_obj = ilObjectFactory::getInstanceByRefId($ref_id,false)))
				{
					$grp_members = $group_obj->getGroupMemberData($group_obj->getGroupMemberIds());
					
					foreach ($grp_members as $member)
					{
						$this->tpl->setCurrentBlock("loop_members");
						$this->tpl->setVariable("LOOP_MEMBERS_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
						$this->tpl->setVariable("LOOP_MEMBERS_ID",$member["id"]);
						$this->tpl->setVariable("LOOP_MEMBERS_LOGIN",$member["login"]);
						$this->tpl->setVariable("LOOP_MEMBERS_NAME",$member["lastname"].", ".$member["firstname"]);
						$this->tpl->setVariable("LOOP_MEMBERS_CRS_GRP",$group_obj->getTitle());
						$this->tpl->setVariable("LOOP_MEMBERS_IN_ADDRESSBOOK", $this->abook->checkEntry($member["login"]) ? $lng->txt("yes") : $lng->txt("no"));
						$this->tpl->parseCurrentBlock();
					}
				}
			}
						
			if ($counter == 0)
			{
				$this->tpl->setCurrentBlock("members_not_found");
				$this->tpl->setVariable("TXT_MEMBERS_NOT_FOUND",$lng->txt("mail_search_members_not_found"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setVariable("BUTTON_MAIL",$lng->txt("grp_mem_send_mail"));
				$this->tpl->setVariable("BUTTON_ADOPT",$lng->txt("mail_into_addressbook"));
			}

			$this->tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));

			$this->tpl->setVariable("TXT_MARKED_ENTRIES",$lng->txt("marked_entries"));
			$this->tpl->show();
		}
	}

}

?>
