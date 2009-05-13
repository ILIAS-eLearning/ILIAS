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
require_once "Services/Mail/classes/class.ilMailbox.php";
require_once "Services/Mail/classes/class.ilFormatMail.php";
require_once "Services/Contact/classes/class.ilAddressbook.php";

/**
* @author Jens Conze
* @version $Id$
*
* @ingroup ServicesMail
*/
class ilMailSearchCoursesGUI
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
					$cmd = "showMyCourses";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	function mail()
	{
		global $ilUser, $lng;

		if ($_GET["view"] == "mycourses")
		{
			if (is_array($_POST["search_crs"]))
			{
				$this->mailCourses();
			}
			else
			{
				ilUtil::sendInfo($lng->txt("mail_select_course"));
				$this->showMyCourses();
			}
		}
		else if ($_GET["view"] == "crs_members")
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
			$this->showMyCourses();
		}
	}

	function mailCourses()
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
		foreach ($_POST["search_crs"] as $crs_id)
		{
			$ref_ids = ilObject::_getAllReferences($crs_id);

			foreach ($ref_ids as $ref_id)
			{
				$roles = $rbacreview->getAssignableChildRoles($ref_id);
				foreach ($roles as $role)
				{
					if (substr($role['title'], 0, 14) == 'il_crs_member_' ||
					    substr($role['title'], 0, 13) == 'il_crs_tutor_' ||
					    substr($role['title'], 0, 13) == 'il_crs_admin_')
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

		#$this->ctrl->returnToParent($this);
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

		#$this->ctrl->returnToParent($this);
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
		if ($_GET["view"] == "mycourses" &&
			$_GET["ref"] == "mail")
		{
			$this->ctrl->returnToParent($this);	
		}
		else
		{
			$this->showMyCourses();
		}
	}
	
	/**
	 * Show user's courses
	 */
	public function showMyCourses()
	{
		global $lng, $ilUser, $ilObjDataCache, $tree, $tpl;

		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
	
		$this->tpl->setVariable('HEADER', $this->lng->txt('mail') );
		
		$searchTpl = new ilTemplate('tpl.mail_search_template.html', true, true, 'Services/Contact');
		
		$_GET['view'] = 'mycourses';

		$lng->loadLanguageModule('crs');

		include_once 'Services/Contact/classes/class.ilMailSearchCoursesTableGUI.php';
		$table = new ilMailSearchCoursesTableGUI($this);
		
		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		$crs_ids = ilCourseParticipants::_getMembershipByType($ilUser->getId(), 'crs');
		$counter = 0;
		$tableData = array();
		if (is_array($crs_ids) && count($crs_ids) > 0)
		{
			$num_courses_hidden_members = 0;
		
			foreach($crs_ids as $crs_id) 
			{		
				if(ilObject::_hasUntrashedReference($crs_id))
				{				
					$oCrsParticipants = ilCourseParticipants::_getInstanceByObjId($crs_id);
					$crs_members = $oCrsParticipants->getParticipants();

					$cnt_members = 0;
					foreach ($crs_members as $member)
					{
						$tmp_usr = new ilObjUser($member);
						
						if($tmp_usr->checkTimeLimit()== false || $tmp_usr->getActive() == false )
						{
							unset($crs_members[$cnt_members]);
						}	
						$cnt_members++;			
					}
					unset($tmp_usr);
					
					$oTmpCrs = ilObjectFactory::getInstanceByObjId($crs_id);
					$hiddenMembers = false;
					if((int)$oTmpCrs->getShowMembers() == $oTmpCrs->SHOW_MEMBERS_DISABLED)
					{
						++$num_courses_hidden_members;
						$hiddenMembers = true;
					}
					unset($oTmpCrs);
					
					$ref_ids = ilObject::_getAllReferences($crs_id);
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
						"CRS_ID" => $crs_id,
						"CRS_NAME" => $ilObjDataCache->lookupTitle($crs_id),
						"CRS_NO_MEMBERS" => count($crs_members),
						"CRS_PATH" => $path,
						"hidden_members" => $hiddenMembers,
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
			
			if($num_courses_hidden_members > 0)
			{
				$searchTpl->setCurrentBlock('caption_block');
				$searchTpl->setVariable('TXT_LIST_MEMBERS_NOT_AVAILABLE', $this->lng->txt('mail_crs_list_members_not_available'));
				$searchTpl->parseCurrentBlock();
			}
		}

		$searchTpl->setVariable('TXT_MARKED_ENTRIES', $lng->txt('marked_entries'));
		
		$table->setData($tableData);
		if($_GET['ref'] == 'mail') $this->tpl->setVariable('BUTTON_CANCEL', $lng->txt('cancel'));

		$searchTpl->setVariable('TABLE', $table->getHtml());
		$tpl->setContent($searchTpl->get());
		$tpl->show();
	}

	/**
	 * Show course members
	 */
	public function showMembers()
	{
		global $lng, $ilUser, $ilObjDataCache;

		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';

		if ($_GET["search_crs"] != "")
		{
			$_POST["search_crs"] = explode(",", $_GET["search_crs"]);
			$_GET["search_crs"] = "";
		}
		else if ($_SESSION["search_crs"] != "")
		{
			$_POST["search_crs"] = explode(",", $_SESSION["search_crs"]);
			$_SESSION["search_crs"] = "";
		}

		if (!is_array($_POST["search_crs"]) ||
			count($_POST["search_crs"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("mail_select_course"));
			$this->showMyCourses();
		}
		else
		{
			foreach($_POST['search_crs'] as $crs_id) 
			{
				$oTmpCrs = ilObjectFactory::getInstanceByObjId($crs_id);
				if($oTmpCrs->getShowMembers() == $oTmpCrs->SHOW_MEMBERS_DISABLED)
				{
					unset($_POST['search_crs']);
					ilUtil::sendInfo($lng->txt('mail_crs_list_members_not_available_for_at_least_one_crs'));
					return $this->showMyCourses();
				}
				unset($oTmpCrs);
			}			

			$this->tpl->setVariable("HEADER", $this->lng->txt("mail"));
		
			$this->ctrl->setParameter($this, "view", "crs_members");
			if ($_GET["ref"] != "") $this->ctrl->setParameter($this, "ref", $_GET["ref"]);
			if (is_array($_POST["search_crs"])) $this->ctrl->setParameter($this, "search_crs", implode(",", $_POST["search_crs"]));
			$this->tpl->setVariable("ACTION", $this->ctrl->getFormAction($this));
			$this->ctrl->clearParameters($this);

			$lng->loadLanguageModule('crs');
			include_once 'Services/Contact/classes/class.ilMailSearchCoursesMembersTableGUI.php';
			$table = new ilMailSearchCoursesMembersTableGUI($this, 'crs');
	
			$tableData = array();
			$searchTpl = new ilTemplate('tpl.mail_search_template.html', true, true, 'Services/Contact');
			foreach($_POST["search_crs"] as $crs_id) 
			{
				$members_obj = ilCourseParticipants::_getinstanceByObjId($crs_id);
				$tmp_members = $members_obj->getParticipants();
				$course_members[$crs_id] = ilUtil::_sortIds($tmp_members,'usr_data','lastname','usr_id');
					
				$cnt_members = 0;		
				foreach ($course_members[$crs_id] as $member)
				{
					$tmp_usr = new ilObjUser($member);
					if($tmp_usr->checkTimeLimit()== false || $tmp_usr->getActive() == false )
					{
						unset($course_members[$crs_id][$cnt_members]);
					}	
					$cnt_members++;						
				}
				unset($tmp_usr);
				
				foreach ($course_members[$crs_id] as $member)
				{

					$name = ilObjUser::_lookupName($member);
					$login = ilObjUser::_lookupLogin($member);
	
					$fullname = "";
					if(ilObjUser::_lookupPref($member, 'public_profile') == 'y')
						$fullname = $name['lastname'].', '.$name['firstname'];

					$rowData = array(
						'MEMBERS_ID' => $member,
						'MEMBERS_LOGIN' => $login,
						'MEMBERS_NAME' => $fullname,
						'MEMBERS_CRS_GRP' => $ilObjDataCache->lookupTitle($crs_id),
						'MEMBERS_IN_ADDRESSBOOK' => $this->abook->checkEntryByLogin($login) ? $lng->txt("yes") : $lng->txt("no"),
					);
					$tableData[] = $rowData;
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
