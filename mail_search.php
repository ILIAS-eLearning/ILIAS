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


/**
* mail search recipients,groups
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "./include/inc.mail.php";
require_once "classes/class.ilObjUser.php";
require_once "classes/class.ilAddressbook.php";
require_once "classes/class.ilFormatMail.php";

$umail = new ilFormatMail($_SESSION["AccountId"]);

// catch hack attempts
if (!$rbacsystem->checkAccess("mail_visible",$umail->getMailObjectReferenceId()))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->WARNING);
}

$lng->loadLanguageModule("mail");

$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_search.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->setCurrentBlock("header_image");
$tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_mail_b.gif"));
$tpl->parseCurrentBlock();
$tpl->setVariable("HEADER",$lng->txt("mail"));
infoPanel();

// LOCATOR
setLocator($_GET["mobj_id"],"mail_search.php",$_SESSION["AccountId"],"");

// BUTTONS
include "./include/inc.mail_buttons.php";

$tpl->setVariable("ACTION","mail_new.php?mobj_id=$_GET[mobj_id]&type=search_res");

// BEGIN ADDRESSBOOK
if ($_GET["addressbook"])
{
	$tpl->setCurrentBlock("addr");
	$abook = new ilAddressbook($_SESSION["AccountId"]);
	$entries = $abook->searchUsers(addslashes(urldecode($_GET["search"])));

	if ($entries)
	{
		$counter = 0;
		$tpl->setCurrentBlock("addr_search");

		foreach ($entries as $entry)
		{
			$tpl->setVariable("ADDR_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
			$tpl->setVariable("ADDR_LOGIN_A",$entry["login"]);
			$tpl->setVariable("ADDR_LOGIN_B",$entry["login"]);
			$tpl->setVariable("ADDR_FIRSTNAME",$entry["firstname"]);
			$tpl->setVariable("ADDR_LASTNAME",$entry["lastname"]);
			$tpl->setVariable("ADDR_EMAIL_A",$entry["email"]);
			$tpl->setVariable("ADDR_EMAIL_B",$entry["email"]);
			$tpl->parseCurrentBlock();
		}		
	}
	else
	{
		$tpl->setCurrentBlock("addr_no_content");
		$tpl->setVariable("TXT_ADDR_NO",$lng->txt("mail_search_no"));
		$tpl->parseCurrentBlock();
	}
	
	// SET TXT VARIABLES ADDRESSBOOK
	$tpl->setVariable("TXT_ADDR",$lng->txt("mail_addressbook"));
	$tpl->setVariable("TXT_ADDR_PERSONS",$lng->txt("persons"));
	$tpl->setVariable("TXT_ADDR_LOGIN",$lng->txt("login"));
	$tpl->setVariable("TXT_ADDR_FIRSTNAME",$lng->txt("firstname"));
	$tpl->setVariable("TXT_ADDR_LASTNAME",$lng->txt("lastname"));
	$tpl->setVariable("TXT_ADDR_EMAIL",$lng->txt("email"));
	$tpl->setVariable("BUTTON_ADOPT",$lng->txt("adopt"));
	$tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
	$tpl->parseCurrentBlock();
}

if ($_GET["courses_to"])
{
	
	include_once 'classes/class.ilObjUser.php';
	include_once 'Modules/Course/classes/class.ilObjCourse.php';
	include_once 'Modules/Course/classes/class.ilCourseMembers.php';
	
	$lng->loadLanguageModule('crs');

	if (isset($_POST["course_ids"]))
	{
		$tpl->setVariable("ACTION","mail_new.php?mobj_id=$_GET[mobj_id]&type=search_res");
	}
	else 
	{
		$tpl->setVariable("ACTION",
		"mail_search.php?mobj_id=$_GET[mobj_id]&offset=$_GET[offset]".
		($_GET["courses_to"] ? "&courses_to=1" : "").
		((is_array($_POST["course_ids"]) && ($_POST["course_ids"]!=NULL)) ? "&course_ids=".implode(",", $_POST["course_ids"]) : ""));
	}
	
	
	$user = new ilObjUser($_SESSION["AccountId"]);
	$crs_ids = $user->getCourseMemberships();
	
	if ($_GET["course_ids"] != "")
	{
		$_POST["course_ids"] = explode(",", $_GET["course_ids"]);		
	}
	if (is_array($_POST["course_ids"]) && count($_POST["course_ids"]) > 0)
	{
		$tpl->setCurrentBlock("course_to");
		$tpl->setVariable("MEMBERS_TXT_COURSE",$lng->txt("course"));
		$tpl->parseCurrentBlock();
		$tpl->setVariable("MEMBERS_TXT_LOGIN",$lng->txt("login"));
		$tpl->setVariable("MEMBERS_TXT_NAME",$lng->txt("name"));
		//$tpl->setVariable("MEMBERS_TXT_IN_ADDRESSBOOK",$lng->txt("mail_in_addressbook"));

		$counter = 0;
		
		foreach($_POST["course_ids"] as $crs_id) 
		{
			$course_obj = new ilObjCourse($crs_id,false); 
			$crs_members = new ilCourseMembers($course_obj);
			
			$course_admins[$crs_id] = $crs_members->getAdmins();
			$course_tutors[$crs_id] = $crs_members->getTutors();
			$course_members[$crs_id] = $crs_members->getMembers();

			foreach ($course_admins[$crs_id] as $admin)
			{
				$name = ilObjUser::_lookupName($admin);
				$login = ilObjUser::_lookupLogin($admin);

				$tpl->setCurrentBlock("loop_members");
				$tpl->setVariable("LOOP_MEMBERS_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
				$tpl->setVariable("LOOP_MEMBERS_ID",$admin);
				$tpl->setVariable("LOOP_MEMBERS_LOGIN",$login);
				$tpl->setVariable("LOOP_MEMBERS_NAME",$name["lastname"].", ".$name["firstname"]);
				$tpl->setVariable("LOOP_MEMBERS_CRS_GRP",$course_obj->getTitle());
				$tpl->parseCurrentBlock();
			}
			foreach ($course_tutors[$crs_id] as $tutor)
			{
				$name = ilObjUser::_lookupName($tutor);
				$login = ilObjUser::_lookupLogin($tutor);

				$tpl->setCurrentBlock("loop_members");
				$tpl->setVariable("LOOP_MEMBERS_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
				$tpl->setVariable("LOOP_MEMBERS_ID",$tutor);
				$tpl->setVariable("LOOP_MEMBERS_LOGIN",$login);
				$tpl->setVariable("LOOP_MEMBERS_NAME",$name["lastname"].", ".$name["firstname"]);
				$tpl->setVariable("LOOP_MEMBERS_CRS_GRP",$course_obj->getTitle());
				$tpl->parseCurrentBlock();
			}
			foreach ($course_members[$crs_id] as $member)
			{
				$name = ilObjUser::_lookupName($member);
				$login = ilObjUser::_lookupLogin($member);

				$tpl->setCurrentBlock("loop_members");
				$tpl->setVariable("LOOP_MEMBERS_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
				$tpl->setVariable("LOOP_MEMBERS_ID",$member);
				$tpl->setVariable("LOOP_MEMBERS_LOGIN",$login);
				$tpl->setVariable("LOOP_MEMBERS_NAME",$name["lastname"].", ".$name["firstname"]);
				$tpl->setVariable("LOOP_MEMBERS_CRS_GRP",$course_obj->getTitle());
				$tpl->parseCurrentBlock();
			}
		}
		
		if ($counter == 0)
		{
			$tpl->setCurrentBlock("members_not_found");
			$tpl->setVariable("TXT_MEMBERS_NOT_FOUND",$lng->txt("mail_search_members_not_found"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("BUTTON_MAIL",$lng->txt("grp_mem_send_mail"));
		$tpl->setVariable("BUTTON_ADOPT",$lng->txt("adopt"));
	}

	else if (is_array($crs_ids = $user->getCourseMemberships()) && count($crs_ids) > 0)
	{
		$counter = 0;
			
		$tpl->setCurrentBlock("crs_grp_courses");
		$tpl->setVariable("CRS_GRP_TXT_COURSES",$lng->txt("courses"));
		$tpl->parseCurrentBlock();
		$tpl->setVariable("CRS_GRP_TXT_NO_MEMBERS",$lng->txt("crs_count_members"));
	
		foreach($crs_ids as $crs_id) 
		{
			$course_obj = new ilObjCourse($crs_id,false); 
			$crs_members = new ilCourseMembers($course_obj);

			$tpl->setCurrentBlock("loop_crs_grp");
			$tpl->setVariable("LOOP_CRS_GRP_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
			$tpl->setVariable("LOOP_CRS_GRP_ID",$course_obj->getId());
			$tpl->setVariable("LOOP_CRS_GRP_NAME",$course_obj->getTitle());
			$tpl->setVariable("LOOP_CRS_GRP_NO_MEMBERS",$crs_members->getCountMembers());
			$tpl->parseCurrentBlock();
		}

		if ($counter == 0)
		{
			$tpl->setCurrentBlock("crs_grp_not_found");
			$tpl->setVariable("TXT_CRS_GRP_NOT_FOUND",$lng->txt("mail_search_courses_not_found"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("BUTTON_MAIL",$lng->txt("mail_members"));
		$tpl->setVariable("BUTTON_LIST",$lng->txt("mail_list_members"));
	}

	$tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
}


if ($_POST["cmd"]["adopt"])
{
	
	$members = array();

	if (is_array($_POST["search_members"]))
	{
		foreach ($_POST["search_members"] as $member)
		{
			$login = ilObjUser::_lookupLogin($member);

			if (!$abook->checkEntry($login))
			{
				$name = ilObjUser::_lookupName($member);
				$email = ilObjUser::_lookupEmail($member);
				$abook->addEntry(
					$login,
					$name["firstname"],
					$name["lastname"],
					$email
				);
			}
		}
		sendInfo($lng->txt("mail_members_added_addressbook"));
	}
}


/*	
	include_once 'classes/class.ilObjUser.php';
	include_once 'Modules/Course/classes/class.ilObjCourse.php';
	include_once 'Modules/Course/classes/class.ilCourseMembers.php';
	
	$user = new ilObjUser($_SESSION["AccountId"]);

	if ($_GET["course_id"] && $_GET["course_id"]!="") 
	{
		// receive the crs_ref_id as a GET parameter
		$course_obj = new ilObjCourse($_GET["course_id"],true); 
		$crs_ids = array(0=>$course_obj->id);
	}
	// when no crs_id is given, process all user courses
	else 
	{
		$user = new ilObjUser($_SESSION["AccountId"]);
		$crs_ids = $user->getCourseMemberships();
		
		
		//var_dump($crs_ids);
	}	
	if(!count($crs_ids))
	{
		$tpl->setCurrentBlock("no_content");
		$tpl->setVariable("TXT_PERSON_NO",$lng->txt("mail_search_no"));
		$tpl->parseCurrentBlock();
	}
	else 
	{
		$counter = 0;
		
		$tpl->setCurrentBlock("system");
		$tpl->setVariable("TXT_LOGIN",$lng->txt("login"));
		$tpl->setVariable("TXT_FIRSTNAME",$lng->txt("firstname"));
		$tpl->setVariable("TXT_LASTNAME",$lng->txt("lastname"));
		$tpl->setVariable("TXT_COURSE",$lng->txt("course"));
		$tpl->setVariable("TXT_ROLE",$lng->txt("role"));
		$tpl->parseCurrentBlock();
		
		$lng->loadLanguageModule('crs');


		foreach($crs_ids as $crs_id) 
		{
			$course_obj = new ilObjCourse($crs_id,false); 
			$crs_members = new ilCourseMembers($course_obj);
			
			$course_admins[$crs_id] = $crs_members->getAdmins();
			$course_tutors[$crs_id] = $crs_members->getTutors();
			$course_members[$crs_id] = $crs_members->getMembers();
		
			$tpl->setCurrentBlock("system");
			//$tpl->setVariable("TXT_PERSONS",$lng->txt("members"));
			$tpl->parseCurrentBlock();
			

			foreach ($course_admins[$crs_id] as $admin)
			{				
				$name = ilObjUser::_lookupName($admin);
				$login = ilObjUser::_lookupLogin($admin);

				$tpl->setCurrentBlock("person_search");
				$tpl->setVariable("CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
				$tpl->setVariable("PERSON_LOGIN",$login);
				$tpl->setVariable("LOGIN",$login);
				$tpl->setVariable("FIRSTNAME",$name["firstname"]);
				$tpl->setVariable("LASTNAME",$name["lastname"]);
				$tpl->setVariable("COURSE_NAME",$course_obj->getTitle());
				$tpl->setVariable("COURSE_ROLE",$lng->txt("crs_admin"));
				$tpl->parseCurrentBlock();
			}
			
			foreach ($course_tutors[$crs_id] as $tutor)
			{
				$name = ilObjUser::_lookupName($tutor);
				$login = ilObjUser::_lookupLogin($tutor);

				$tpl->setCurrentBlock("person_search");
				$tpl->setVariable("CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
				$tpl->setVariable("PERSON_LOGIN",$login);
				$tpl->setVariable("LOGIN",$login);
				$tpl->setVariable("FIRSTNAME",$name["firstname"]);
				$tpl->setVariable("LASTNAME",$name["lastname"]);
				$tpl->setVariable("COURSE_NAME",$course_obj->getTitle());
				$tpl->setVariable("COURSE_ROLE",$lng->txt("crs_tutor"));

				$tpl->parseCurrentBlock();
			}
			
			foreach ($course_members[$crs_id] as $member)
			{
				$name = ilObjUser::_lookupName($member);
				$login = ilObjUser::_lookupLogin($member);

				$tpl->setCurrentBlock("person_search");
				$tpl->setVariable("CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
				$tpl->setVariable("PERSON_LOGIN",$login);
				$tpl->setVariable("LOGIN",$login);
				$tpl->setVariable("FIRSTNAME",$name["firstname"]);
				$tpl->setVariable("LASTNAME",$name["lastname"]);
				$tpl->setVariable("COURSE_NAME",$course_obj->getTitle());
				$tpl->setVariable("COURSE_ROLE",$lng->txt("crs_member"));

				$tpl->parseCurrentBlock();
			}	
	
		}
	}
		
	
	
	$tpl->setCurrentBlock("system");
	$tpl->setVariable("BUTTON_ADOPT",$lng->txt("adopt"));
	$tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
	$tpl->parseCurrentBlock();
*/



if ($_GET["groups_to"])
{
	  
}

if ($_GET["system"])
{
	include_once 'Services/Search/classes/class.ilQueryParser.php';
	include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
	include_once 'Services/Search/classes/class.ilSearchResult.php';

	$all_results = new ilSearchResult();

	$query_parser = new ilQueryParser(ilUtil::stripSlashes($_GET['search']));
	$query_parser->setCombination(QP_COMBINATION_OR);
	$query_parser->setMinWordLength(3);
	$query_parser->parse();

	$user_search =& ilObjectSearchFactory::_getUserSearchInstance($query_parser);
	$user_search->setFields(array('login'));
	$result_obj = $user_search->performSearch();
	$all_results->mergeEntries($result_obj);

	$user_search->setFields(array('firstname'));
	$result_obj = $user_search->performSearch();
	$all_results->mergeEntries($result_obj);

	
	$user_search->setFields(array('lastname'));
	$result_obj = $user_search->performSearch();
	$all_results->mergeEntries($result_obj);

	$all_results->filter(ROOT_FOLDER_ID,QP_COMBINATION_OR);

	foreach(($users = $all_results->getResults()) as $result)
	{
		global $rbacsystem;

		if($rbacsystem->checkAccess("smtp_mail",$umail->getMailObjectReferenceId()) 
		   and (ilObjUser::_lookupPref($result['obj_id'],'public_email') == 'y'))
		{
			$has_mail = true;
			$tpl->setCurrentBlock("smtp_row");
			$tpl->setVariable("PERSON_EMAIL",ilObjUser::_lookupEmail($result['obj_id']));
			$tpl->setVariable("EMAIL",ilObjUser::_lookupEmail($result['obj_id']));
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->setCurrentBlock("no_smtp_row");
			$tpl->setVariable("NO_EMAIL",'');
			$tpl->parseCurrentBlock();
		}
		
		$name = ilObjUser::_lookupName($result['obj_id']);
		$login = ilObjUser::_lookupLogin($result['obj_id']);

		$tpl->setCurrentBlock("person_search");
		$tpl->setVariable("CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
		$tpl->setVariable("PERSON_LOGIN",$login);
		$tpl->setVariable("LOGIN",$login);
		$tpl->setVariable("FIRSTNAME",$name["firstname"]);
		$tpl->setVariable("LASTNAME",$name["lastname"]);
		$tpl->parseCurrentBlock();
	}		
	if(!count($users))
	{
		$tpl->setCurrentBlock("no_content");
		$tpl->setVariable("TXT_PERSON_NO",$lng->txt("mail_search_no"));
		$tpl->parseCurrentBlock();
	}

	$groups = ilUtil::searchGroups(addslashes(urldecode($_GET["search"])));

	if ($groups)
	{
		$counter = 0;
		$tpl->setCurrentBlock("group_search");

		foreach ($groups as $group_data)
		{
			$tpl->setVariable("GROUP_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
			$tpl->setVariable("GROUP_NAME","#".$group_data["title"]);
			$tpl->setVariable("GROUP_TITLE",$group_data["title"]);
			$tpl->setVariable("GROUP_DESC",$group_data["description"]);
			$tpl->parseCurrentBlock();
		}
	}
	else
	{
		$tpl->setCurrentBlock("no_content");
		$tpl->setVariable("TXT_GROUP_NO",$lng->txt("mail_search_no"));
		$tpl->parseCurrentBlock();
	}

	if ($has_mail)
	{
		$tpl->setCurrentBlock("smtp");
		$tpl->setVariable("TXT_EMAIL",$lng->txt("email"));
		$tpl->parseCurrentBlock();
	}
	else
	{
		$tpl->touchBlock('no_smtp');
	}
		
	$tpl->setCurrentBlock("system");
	$tpl->setVariable("TXT_PERSONS",$lng->txt("persons"));
	$tpl->setVariable("TXT_LOGIN",$lng->txt("login"));
	$tpl->setVariable("TXT_FIRSTNAME",$lng->txt("firstname"));
	$tpl->setVariable("TXT_LASTNAME",$lng->txt("lastname"));
	$tpl->setVariable("TXT_GROUPS",$lng->txt("groups"));
	$tpl->setVariable("TXT_GROUP_NAME",$lng->txt("title"));
	$tpl->setVariable("TXT_GROUP_DESC",$lng->txt("description"));
	$tpl->setVariable("BUTTON_ADOPT",$lng->txt("adopt"));
	$tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
	$tpl->parseCurrentBlock();
} 		

$tpl->show();
?>
