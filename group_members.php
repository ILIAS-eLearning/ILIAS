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
require_once "./classes/class.ilUtil.php";


$num = 0;



$newGrp = new ilObjGroup($_GET["grp_id"],true);
$member_ids = $newGrp->getGroupMemberIds($_GET["grp_id"]);
		
$member_arr = array();
foreach ($member_ids as $member_id)
	{
	array_push($member_arr, new ilObjUser($member_id));
	}

//var_dump($member_ids);

// output data
$tpl->addBlockFile("CONTENT", "content", "tpl.grp_members.html");
//echo ($tpl->addBlockFile("CONTENT", "content", "tpl.obj_members.html"));
infoPanel();

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("group_members"));



$tpl->addBlockfile("GROUP_MEMBERS_TABLE", "member_table", "tpl.table.html");
// load template for table content data
$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.grp_tbl_members.html");
$num = 0;
//var_dump ($member_arr);

foreach($member_arr as $member)
	{
	$tpl->setCurrentBlock("tbl_content");	
	$grp_role_id = $newGrp->getGroupRoleId($member->getId());
	$newObj 	 = new ilObject($grp_role_id,false);
	$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
	$num++;	
				
//todo: chechAccess, each user sees only the symbols belonging to his rigths
	
	
	$link_contact = "mail_new.php?mobj_id=3&type=new&mail_data[rcp_to]=".$member->getLogin();
	$link_change = "adm_object.php?cmd=editMember&ref_id=".$this->ref_id."&mem_id=".$member->getId();		
//			$link_change = "adm_object.php?cmd=perm&ref_id=".$this->ref_id."&mem_id=".$member->getId();		
	$link_leave = "adm_object.php?type=grp&cmd=leaveGrp&ref_id=".$this->ref_id."&mem_id=".$member->getId();					
	$img_contact = "pencil";
	$img_change = "change";
	$img_leave = "group_out";						
	$val_contact = ilUtil::getImageTagByType($img_contact, $tpl->tplPath);
	$val_change = ilUtil::getImageTagByType($img_change, $tpl->tplPath);
	$val_leave  = ilUtil::getImageTagByType($img_leave, $tpl->tplPath);
	$obj_icon = "icon_usr_b.gif";
	$tpl->setVariable("IMG", $obj_icon);
	$tpl->setVariable("ALT_IMG", $lng->txt("obj_usr"));
	echo ($this->tpl->tplPath);
		
	$tpl->setVariable("CHECKBOX", ilUtil::formCheckBox(0,"id[]",$member->getId()));
	$tpl->setVariable("LOGIN",$member->getLogin());
	$tpl->setVariable("FIRSTNAME", $member->getFirstname());
	$tpl->setVariable("LASTNAME", $member->getLastname());
	$tpl->setVariable("ANNOUNCEMENT_DATE", "Announcement Date");
	$tpl->setVariable("ROLENAME", $lng->txt($newObj->getTitle()));
	$tpl->setVariable("LINK_CONTACT", $link_contact);
	$tpl->setVariable("CONTACT", $val_contact);
	$tpl->setVariable("LINK_CHANGE", $link_change);
	$tpl->setVariable("CHANGE", $val_change);
	$tpl->setVariable("LINK_LEAVE", $link_leave);
	$tpl->setVariable("LEAVE", $val_leave);						
	$tpl->parseCurrentBlock();
	// END TABLE MEMBERS
	}



$tpl->setCurrentBlock("tbl_action_btn");
$tpl->SetVariable("COLUMN_COUNTS", "6");
$tpl->setVariable("BTN_NAME", "leave");
$tpl->setVariable("BTN_VALUE", "Discharge Member");
$tpl->parseCurrentBlock();
$tpl->setVariable("BTN_NAME", "mail");
$tpl->setVariable("BTN_VALUE", "Write mail");
$tpl->parseCurrentBlock();
$tpl->setVariable("BTN_NAME", "change");
$tpl->setVariable("BTN_VALUE", "Change Status");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("tbl_action_row");
$tpl->parseCurrentBlock();

$tbl = new ilTableGUI();
$tbl->setHeaderNames(array("",$lng->txt("login"),$lng->txt("firstname"),$lng->txt("lastname")/*,$lng->txt("announcement_date")*/,$lng->txt("role_in_group"),""));
$tbl->setHeaderVars(array("checkbox","title","description","status"/*,"last_visit"*/,"last_change","context"));
$tbl->setColumnWidth(array("3%","7%","7%",/*"15%",*/"15%","6%","5%"));

// control
$tbl->setOrderColumn($_GET["sort_by"]);
$tbl->setOrderDirection($_GET["sort_order"]);
$tbl->setLimit($limit);
$tbl->setOffset($offset);
$tbl->setMaxCount($maxcount);

// footer
$tbl->setFooter("tblfooter",$lng->txt("previous"),$lng->txt("next"));
//$tbl->disable("content");
//$tbl->disable("footer");

// render table
$tbl->render();
$tpl->show();
?>

	
