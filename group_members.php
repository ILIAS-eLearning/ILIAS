<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once "./include/inc.header.php";
require_once "./classes/class.ilTableGUI.php";
require_once "./classes/class.ilObjGroup.php";

$num = 0;

$newGrp = new ilObjGroup($this->ref_id,true);
$member_ids = $newGrp->getGroupMemberIds();
		
$member_arr = array();
foreach ($member_ids as $member_id)
	{
	array_push($member_arr, new ilObjUser($member_id));
	}

// output data
$this->getTemplateFile("members");
$this->tpl->setCurrentBlock("HEADER_MEMBERS");
$this->tpl->setVariable("TXT_USER", "User");
$this->tpl->setVariable("TXT_FIRSTNAME", "Firstname");
$this->tpl->setVariable("TXT_LASTNAME", "Lastname");
$this->tpl->setVariable("TXT_JOINDATE", "Join date");
$this->tpl->setVariable("TXT_ROLE", "Role");
$this->tpl->setVariable("TXT_FUNCTIONS", "Functions");

$this->tpl->parseCurrentBlock();

foreach($member_arr as $member)
	{	
	$grp_role_id = $newGrp->getGroupRoleId($member->getId());
	$newObj 	 = new ilObject($grp_role_id,false);
					
//todo: chechAccess, each user sees only the symbols belonging to his rigths
	$link_contact = "mail_new.php?mobj_id=3&type=new&mail_data[rcp_to]=".$member->getLogin();
	$link_change = "adm_object.php?cmd=editMember&ref_id=".$this->ref_id."&mem_id=".$member->getId();		
//			$link_change = "adm_object.php?cmd=perm&ref_id=".$this->ref_id."&mem_id=".$member->getId();		
	$link_leave = "adm_object.php?type=grp&cmd=leaveGrp&ref_id=".$this->ref_id."&mem_id=".$member->getId();					
	$img_contact = "pencil";
	$img_change = "change";
	$img_leave = "group_out";						
	$val_contact = ilUtil::getImageTagByType($img_contact, $this->tpl->tplPath);
	$val_change = ilUtil::getImageTagByType($img_change, $this->tpl->tplPath);
	$val_leave  = ilUtil::getImageTagByType($img_leave, $this->tpl->tplPath);
	
			// BEGIN TABLE MEMBERS
	$this->tpl->setCurrentBlock("TABLE_MEMBERS");
	$css_row = ilUtil::switchColor($num++, "tblrow1", "tblrow2");
	$this->tpl->setVariable("CSS_ROW",$css_row);
	$this->tpl->setVariable("LOGIN",$member->getLogin());
	$this->tpl->setVariable("FIRSTNAME", $member->getFirstname());
	$this->tpl->setVariable("LASTNAME", $member->getLastname());
	$this->tpl->setVariable("ANNOUNCEMENT_DATE", "Announcement Date");
	$this->tpl->setVariable("ROLENAME", $newObj->getTitle());
			
	$this->tpl->setVariable("LINK_CONTACT", $link_contact);
	$this->tpl->setVariable("CONTACT", $val_contact);
	$this->tpl->setVariable("LINK_CHANGE", $link_change);
	$this->tpl->setVariable("CHANGE", $val_change);
	$this->tpl->setVariable("LINK_LEAVE", $link_leave);
	$this->tpl->setVariable("LEAVE", $val_leave);						
	$this->tpl->parseCurrentBlock();
	// END TABLE MEMBERS
	}
?>	
