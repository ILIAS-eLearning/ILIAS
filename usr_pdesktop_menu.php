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


/*
* personal desktop
* this file shows two frames: ( usr_pdesktop_menu.php, usr_pdesktop.php?cmd= )
* welcome screen of ilias with new mails and last lo , the user worked for
* adapted from ilias/ingmedia 2
*
* @author Pia Behr <p.behr@fh-aachen.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

// get skin = path to the specific template
$template_path = $ilias->tplPath.$ilias->account->getPref("skin");
// menu bar for the personal desktop
if ($_GET["cmd"] == "highest_level")
{
	//add template for content within the linkbar
	$tpl->addBlockfile("CONTENT", "content", "tpl.content_start.html");
	//add template for link table
	$tpl->addBlockFile("LTAB_LN", "ltab_ln", "tpl.ltab_ln.html");

	//$tpl->setCurrentBlock("page_init");
	$tpl->setCurrentBlock("content");
	// define first table
	include "./include/inc.personaldesktop_buttons.php";
		  
	for ( $i=0; $i<sizeof($inhalt1); $i++)
	{
		if ($inhalt1[$i][1] != "")
		{	
			{	$tpl->setCurrentBlock("ltab_cell_two_frames");
				$tpl->setVariable("LTAB_LN_REF2",$inhalt1[$i][1]);
				$tpl->setVariable("LTAB_LN_TXT",$inhalt1[$i][2]);
				$tpl->setVariable("LTAB_LN_FRAME2",$inhalt1[$i][3]);
				$tpl->setVariable("LTAB_LN_REF1",$inhalt1[$i][4]);
				$tpl->setVariable("LTAB_LN_FRAME1",$inhalt1[$i][5]);
				$tpl->parseCurrentBlock();
			}
		}
	}

	// spacer
	$tpl->touchBlock("distance");
	$tpl->parseCurrentBlock();

	// generate first table
	$tpl->setCurrentBlock("context_links_table");
	$tpl->parseCurrentBlock();

	// define second table

	$tpl->setCurrentBlock("ltab_cell_one_frame");
	$tpl->setVariable("LTAB_LN_TXT",$lng->txt("ingmedia_reg_fo"));
	$tpl->setVariable("LTAB_LN_REF","usr_pdesktop.php?cmd=reg_fo");
	$tpl->setVariable("LTAB_LN_FRAME","bottom");
	$tpl->parseCurrentBlock();

	$tpl->setCurrentBlock("ltab_cell_one_frame");
	$tpl->setVariable("LTAB_LN_TXT",$lng->txt("ingmedia_reg_le"));
	$tpl->setVariable("LTAB_LN_REF","usr_pdesktop.php?cmd=reg_le");
	$tpl->setVariable("LTAB_LN_FRAME","bottom");
	$tpl->parseCurrentBlock();

	$tpl->setCurrentBlock("ltab_cell_one_frame");
	$tpl->setVariable("LTAB_LN_TXT",$lng->txt("ingmedia_vis_le"));
	$tpl->setVariable("LTAB_LN_REF","usr_pdesktop.php?cmd=visited");
	$tpl->setVariable("LTAB_LN_FRAME","bottom");
	$tpl->parseCurrentBlock();

	$tpl->setCurrentBlock("ltab_cell_one_frame");
	$tpl->setVariable("LTAB_LN_TXT",$lng->txt("ingmedia_all_offers"));
	$tpl->setVariable("LTAB_LN_REF","lo_list.php");
	$tpl->setVariable("LTAB_LN_FRAME","bottom");
	$tpl->parseCurrentBlock();

	// generate second table
	$tpl->setCurrentBlock("context_links_table");
	$tpl->parseCurrentBlock();

	$tpl->setVariable("SHOW_PAGE_IMAGE_REF","start.php");
	$tpl->setVariable("SHOW_PAGE_IMAGE_TARGET","_top");
	$tpl->setVariable("SHOW_PAGE_IMAGE_SRC",$template_path."/images/layout/schreibtisch.gif");

	$tpl->setCurrentBlock("showemptybutton");
	$tpl->setVariable("SHOW_EMPTY_BUTTON_TXT",$lng->txt("ingmedia_level_zero"));
	$tpl->setVariable("SHOW_EMPTY_BUTTON_SRC",$template_path."/images/navigation/go_empty.gif");
	$tpl->parseCurrentBlock();

	// content schließen
	$tpl->parseCurrentBlock();
}########################################cmd=highest_level


// menu bar for the calendar
if ($_GET["cmd"] == "cal")
{
	//add template for content within the linkbar
	$tpl->addBlockfile("CONTENT", "content", "tpl.content_start.html");
	//add template for link table
	$tpl->addBlockFile("LTAB_LN", "ltab_ln", "tpl.ltab_ln.html");

	$tpl->setCurrentBlock("content");
	
	// define first table cells = calendar categories
	include "./include/inc.calendar_tabs.php";
		  
	for ( $i=0; $i<sizeof($inhalt1); $i++)
	{
		if ($inhalt1[$i][1] != "")
		{
	        	$tpl->setCurrentBlock("ltab_cell_one_frame");
			$tpl->setVariable("LTAB_LN_REF",$inhalt1[$i][1]);
			$tpl->setVariable("LTAB_LN_TXT",$inhalt1[$i][2]);
			$tpl->setVariable("LTAB_LN_FRAME",$inhalt1[$i][3]);
			$tpl->parseCurrentBlock();
		}
	}
	
	// spacer
	$tpl->touchBlock("distance");
	$tpl->parseCurrentBlock();
	
	// generate first table
	$tpl->setCurrentBlock("context_links_table");
	$tpl->parseCurrentBlock();

	// define second table cells; get lm visited
	$result = $ilias->account->getLastVisitedLessons();
	if (sizeof($result) > 0)
	{
		$tpl->setCurrentBlock("ltab_cell_le_back");
		$tpl->setVariable("LTAB_LN_TXT",$lng->txt("ingmedia_back_to_le"));
		$tpl->setVariable("LTAB_LN_REF", "lo_last.php");
		$tpl->setVariable("LTAB_LN_FRAME","_parent");
		$tpl->parseCurrentBlock();

		// generate second table
		$tpl->setCurrentBlock("context_links_table");
		$tpl->parseCurrentBlock();
	}

	$tpl->setVariable("SHOW_PAGE_IMAGE_REF","start.php");
	$tpl->setVariable("SHOW_PAGE_IMAGE_TARGET","_top");
	$tpl->setVariable("SHOW_PAGE_IMAGE_SRC",$template_path."/images/layout/schreibtisch.gif");

	// define show_up-button
	$tpl->setcurrentBlock("showupbutton");
	$tpl->setVariable("SHOW_UP_BUTTON_TXT",$lng->txt("ingmedia_level_zero"));
	$tpl->setVariable("SHOW_UP_BUTTON_SRC",$template_path."/images/navigation/go_up.gif");
	$tpl->setVariable("SHOW_UP_BUTTON_REF1","usr_pdesktop_menu.php?cmd=highest_level");
	$tpl->setVariable("SHOW_UP_BUTTON_FRAME1","left");
	$tpl->setVariable("SHOW_UP_BUTTON_REF2","usr_pdesktop.php?cmd=lvis_le");
	$tpl->setVariable("SHOW_UP_BUTTON_FRAME2","bottom");
	$tpl->parseCurrentBlock();
	
	// content schließen
	$tpl->parseCurrentBlock();
}#########################cmd=cal



// menu bar for mail
if ($_GET["cmd"] == "mail")
{
	//add template for content within the linkbar
	$tpl->addBlockfile("CONTENT", "content", "tpl.content_start.html");
	//add template for link table
	$tpl->addBlockFile("LTAB_LN", "ltab_ln", "tpl.ltab_ln.html");

	$tpl->setCurrentBlock("content");
	
	// define first table cells 
	include "./include/inc.mail_buttons.php";
		  
	for ( $i=0; $i<sizeof($inhalt1); $i++)
	{
		if ($inhalt1[$i][1] != "")
		{
			$tpl->setCurrentBlock("ltab_cell_two_frames");
			$tpl->setVariable("LTAB_LN_REF2",$inhalt1[$i][1]);
			$tpl->setVariable("LTAB_LN_TXT",$inhalt1[$i][2]);
			$tpl->setVariable("LTAB_LN_FRAME2",$inhalt1[$i][3]);
			$tpl->setVariable("LTAB_LN_REF1","usr_pdesktop_menu.php?cmd=mail");
			$tpl->setVariable("LTAB_LN_FRAME1","left");
			$tpl->parseCurrentBlock();
		}
	}
	
	// spacer
	$tpl->touchBlock("distance");
	$tpl->parseCurrentBlock();
	
	// generate first table
	$tpl->setCurrentBlock("context_links_table");
	$tpl->parseCurrentBlock();

	// define second table cells; get lm visited
	$result = $ilias->account->getLastVisitedLessons();
	if (sizeof($result) > 0)
	{
		$tpl->setCurrentBlock("ltab_cell_le_back");
		$tpl->setVariable("LTAB_LN_TXT",$lng->txt("ingmedia_back_to_le"));
		$tpl->setVariable("LTAB_LN_REF", "lo_last.php");
		$tpl->setVariable("LTAB_LN_FRAME","_parent");
		$tpl->parseCurrentBlock();

		// generate second table
		$tpl->setCurrentBlock("context_links_table");
		$tpl->parseCurrentBlock();
	}

	$tpl->setVariable("SHOW_PAGE_IMAGE_REF","start.php");
	$tpl->setVariable("SHOW_PAGE_IMAGE_TARGET","_top");
	$tpl->setVariable("SHOW_PAGE_IMAGE_SRC",$template_path."/images/layout/Mailkontakt.gif");

	// define show_up-button
	$tpl->setcurrentBlock("showupbutton");
	$tpl->setVariable("SHOW_UP_BUTTON_TXT",$lng->txt("ingmedia_level_zero"));
	$tpl->setVariable("SHOW_UP_BUTTON_SRC",$template_path."/images/navigation/go_up.gif");
	$tpl->setVariable("SHOW_UP_BUTTON_REF1","usr_pdesktop_menu.php?cmd=highest_level");
	$tpl->setVariable("SHOW_UP_BUTTON_FRAME1","left");
	$tpl->setVariable("SHOW_UP_BUTTON_REF2","usr_pdesktop.php?cmd=lvis_le");
	$tpl->setVariable("SHOW_UP_BUTTON_FRAME2","bottom");
	$tpl->parseCurrentBlock();
	
	// content schließen
	$tpl->parseCurrentBlock();
}#########################cmd=mail


// menu bar for search, feedback,groups and forum
if (($_GET["cmd"] == "search" ) or ($_GET["cmd"] == "feedback" ) or 
    ($_GET["cmd"] == "groups" ) or ($_GET["cmd"] == "forum" ))
{
	//add template for content within the linkbar
	$tpl->addBlockfile("CONTENT", "content", "tpl.content_start.html");
	//add template for link table
	$tpl->addBlockFile("LTAB_LN", "ltab_ln", "tpl.ltab_ln.html");

	$tpl->setCurrentBlock("content");
	
	// define first table cells; get lm visited
	$result = $ilias->account->getLastVisitedLessons();
	if (sizeof($result) > 0)
	{
		
		$tpl->setCurrentBlock("ltab_cell_le_back");
		$tpl->setVariable("LTAB_LN_TXT",$lng->txt("ingmedia_back_to_le"));
		$tpl->setVariable("LTAB_LN_REF", "lo_last.php");
		$tpl->setVariable("LTAB_LN_FRAME","_parent");
		$tpl->parseCurrentBlock();

		// generate second table
		$tpl->setCurrentBlock("context_links_table");
		$tpl->parseCurrentBlock();
	}

	$tpl->setVariable("SHOW_PAGE_IMAGE_REF","start.php");
	$tpl->setVariable("SHOW_PAGE_IMAGE_TARGET","_top");
	
	if ($_GET["cmd"] == "search" )
	{
	$tpl->setVariable("SHOW_PAGE_IMAGE_SRC",$template_path."/images/layout/Suchen.gif");
	}
	
	if ($_GET["cmd"] == "feedback" )
	{
	$tpl->setVariable("SHOW_PAGE_IMAGE_SRC",$template_path."/images/layout/Feedback.gif");
	}
	
	if ($_GET["cmd"] == "groups" )
	{
	$tpl->setVariable("SHOW_PAGE_IMAGE_SRC",$template_path."/images/layout/Schreibtisch.gif");
	}
	
	if ($_GET["cmd"] == "forum" )
	{
	$tpl->setVariable("SHOW_PAGE_IMAGE_SRC",$template_path."/images/layout/Forum.gif");
	}
	

	// define show_up-button
	$tpl->setcurrentBlock("showupbutton");
	$tpl->setVariable("SHOW_UP_BUTTON_TXT",$lng->txt("ingmedia_level_zero"));
	$tpl->setVariable("SHOW_UP_BUTTON_SRC",$template_path."/images/navigation/go_up.gif");
	$tpl->setVariable("SHOW_UP_BUTTON_REF1","usr_pdesktop_menu.php?cmd=highest_level");
	$tpl->setVariable("SHOW_UP_BUTTON_FRAME1","left");
	$tpl->setVariable("SHOW_UP_BUTTON_REF2","usr_pdesktop.php?cmd=lvis_le");
	$tpl->setVariable("SHOW_UP_BUTTON_FRAME2","bottom");
	$tpl->parseCurrentBlock();
	
	// content schließen
	$tpl->parseCurrentBlock();
}#########################cmd=search, or feedback,or groups or forum



// output
$tpl->show();
?>
