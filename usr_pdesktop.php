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
* personal desktop
* welcome screen of ilias with new mails, last lo's etc.
* adapted from ilias 2
*
* @author Pia Behr <p.behr@fh-aachen.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.ilObjUser.php";
require_once "classes/class.ilMail.php";

//add template for content
$tpl->addBlockFile("CONTENT", "content", "tpl.usr_pdesktop.html");


// get skin = path to the specific template
$template_path = $ilias->tplPath.$ilias->account->getPref("skin");

//if the user visited lessons output the last visited lesson
if ($_GET["cmd"] == "lvis_le")
{
	// add template to greet the user
	$tpl->addBlockFile("USR_TEXT", "usr_text", "tpl.usr_text.html");

	//greetings
	$login_name = $ilias->account->login;
	$greeting = "";
	//check if morning, afternoon, evening or night
	if (date("H:i",time()) > "06:00:00" and date("H:i",time()) < "12:00:00")
		{$greeting .= $lng->txt("ingmedia_good_morning");}
	elseif (date("H:i",time()) > "12:00:00" and date("H:i",time()) < "18:00:00")
		{$greeting .= $lng->txt("ingmedia_good_afternoon");}
	elseif (date("H:i",time()) > "18:00:00" or date("H:i",time()) < "22:00:00")
		{$greeting .= $lng->txt("ingmedia_good_evening");}
	elseif (date("H:i",time()) < "06:00:00" or date("H:i",time()) > "22:00:00")
		{$greeting .= $lng->txt("ingmedia_good_night");}
	else
		{$greeting .= $lng->txt("ingmedia_hello");};
	$greeting .= " ".$login_name;
	//write these lines on the top of the frame "bottom"
	$greeting .= $lng->txt("ingmedia_welcome");

	// get all lm visited
	$result = $ilias->account->getLastVisitedLessons();
	if (sizeof($result) > 0)
	{
		$akt_date =  ilFormat::formatDate($result[0]["timestamp"],"date");
		$info ="";
		$info = $lng->txt("ingmedia_info_about_work1");
		$info .= " ".$akt_date." ";
		$info .= $lng->txt("ingmedia_info_about_work2");
		$tpl->setCurrentBlock("link_button");
		$tpl->setVariable("TXT_LINK", "content/lm_presentation.php?ref_id=".$result[0]["lm_id"]."&obj_id=".$result[0]["obj_id"]);
		$tpl->setVariable("TXT_LINK_IMG", $template_path."/images/layout/open.gif");
		$tpl->setVariable("TXT_LINK_TITLE", $result[0]["lm_title"]);
		$tpl->parseCurrentBlock();
	}
	else
	{
		// no last lo for this user found, so inform user and link back to desktop
		$info ="";
	}
	$tpl->setCurrentBlock("using_info");
	$tpl->setVariable("TXT_TITLE",$greeting);
	$tpl->setVariable("TXT_NOTES",$info);
	$tpl->parseCurrentBlock();
}


//if the user visited lessons output the last visited lesson
if ($_GET["cmd"] == "visited")
{
	// add template to greet the user
	$tpl->addBlockFile("USR_TEXT", "usr_text", "tpl.usr_text.html");

	// get all lm visited
	$result = $ilias->account->getLastVisitedLessons();
	if (sizeof($result) > 0)
	{
		for ($i=1;$i<=sizeof($result);$i++)
		{
			$tpl->setCurrentBlock("link_button");
			$tpl->setVariable("TXT_LINK", "content/lm_presentation.php?ref_id=".$result[$i-1]["lm_id"]."&obj_id=".$result[$i-1]["obj_id"]);
			$tpl->setVariable("TXT_LINK_TITLE", ilFormat::formatDate($result[$i-1]["timestamp"],"date")." - ".$result[$i-1]["lm_title"]);
			$tpl->setVariable("TXT_LINK_IMG", $template_path."/images/layout/open.gif");
			$tpl->parseCurrentBlock();
		}
	}
	// TO DO: use my own profile
	$tpl->setCurrentBlock("using_info");
	$tpl->setVariable("TXT_TITLE",$lng->txt("ingmedia_visited_le"));
	$tpl->parseCurrentBlock();
}



// output all registered lessons of the user
if ($_GET["cmd"] == "reg_le")
{
	// add template 
	$tpl->addBlockFile("USR_TEXT", "usr_text", "tpl.usr_text.html");

	// learning modules
	$lo_items = $ilias->account->getDesktopItems("lm");
	$i = 0;
	foreach ($lo_items as $lo_item)
	{
		$i++;
		$tpl->setCurrentBlock("link_button");
		$tpl->setVariable("TXT_LINK", "content/lm_presentation.php?ref_id=".$lo_item["id"].
			"&obj_id=".$lo_item["parameters"]);
		$tpl->setVariable("TXT_LINK_TITLE", $lo_item["title"]);
		$tpl->setVariable("TXT_LINK_IMG", $template_path."/images/layout/open.gif");
		$tpl->parseCurrentBlock();
	}
	if ($i == 0)
	{
		$tpl->setCurrentBlock("link_button");
		$tpl->setVariable("TXT_LINK_TITLE",$lng->txt("no_lo_in_personal_list"));
		$tpl->parseCurrentBlock();
	}
	
	$tpl->setCurrentBlock("using_info");
	$tpl->setVariable("TXT_TITLE",$lng->txt("ingmedia_reg_le"));
	$tpl->parseCurrentBlock();
}


// out all registered forms the user has access to
if ($_GET["cmd"] == "reg_fo")
{
	//forums
	$frm_obj = ilUtil::getObjectsByOperations('frm','read');
	$frmNum = count($frm_obj);
	$lastLogin = $ilias->account->getLastLogin();

	// add template 
	$tpl->addBlockFile("USR_TEXT", "usr_text", "tpl.usr_text.html");

	$frm_items = $ilias->account->getDesktopItems("frm");
	$i = 0;
	foreach ($frm_items as $frm_item)
	{
		$i++;
		$tpl->setCurrentBlock("link_button");
		$tpl->setVariable("TXT_LINK",'repository.php?ref_id='.$frm_item['id']);
		$tpl->setVariable("TXT_LINK_TITLE", $frm_item["title"]);
		$tpl->setVariable("TXT_LINK_IMG", $template_path."/images/layout/open.gif");
		$tpl->parseCurrentBlock();
	}
	if ($i == 0)
	{
		$tpl->setCurrentBlock("link_button");
		$tpl->setVariable("TXT_LINK_TITLE", $lng->txt("no_frm_in_personal_list"));
		$tpl->parseCurrentBlock();
	}
	
	$tpl->setCurrentBlock("using_info");
	$tpl->setVariable("TXT_TITLE",$lng->txt("ingmedia_reg_fo"));
	$tpl->parseCurrentBlock();
	
	$tpl->parseCurrentBlock();
}


// output
$tpl->show();
?>
