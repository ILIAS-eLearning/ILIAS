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
* personal desktop - area personal data
* this file shows two frames: ( usr_pdesktop_data_menu.php, usr_pdesktop_?.php )
* adapted from ilias 2
*
* @author Pia Behr <p.behr@fh-aachen.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

// get skin
$template_path = $ilias->tplPath.$ilias->account->getPref("skin");

//add template for content within the linkbar
$tpl->addBlockfile("CONTENT", "content", "tpl.content_start.html");
//add template for link table
$tpl->addBlockFile("LTAB_LN", "ltab_ln", "tpl.ltab_ln.html");

$tpl->setCurrentBlock("content");

$tpl->setVariable("SHOW_PAGE_IMAGE_REF","usr_pdesktop_menu.php");
$tpl->setVariable("SHOW_PAGE_IMAGE_SRC",$template_path."/images/layout/schreibtisch.gif");


// define first table cells

//personal dates
$tpl->setCurrentBlock("ltab_cell_one_frame");
$tpl->setVariable("LTAB_LN_TXT",$lng->txt("ingmedia_pers_dates"));
$tpl->setVariable("LTAB_LN_REF","usr_profile.php");
$tpl->setVariable("LTAB_LN_FRAME","bottom");
$tpl->parseCurrentBlock();

// user calendar
$tpl->setCurrentBlock("ltab_cell_one_frame");
$tpl->setVariable("LTAB_LN_TXT",$lng->txt("calendar"));
$tpl->setVariable("LTAB_LN_REF","cal_month_overview.php");
$tpl->setVariable("LTAB_LN_FRAME","bottom");
$tpl->parseCurrentBlock();

// user agreement
$tpl->setCurrentBlock("ltab_cell_one_frame");
$tpl->setVariable("LTAB_LN_TXT",$lng->txt("ingmedia_user_agree"));
//$tpl->setVariable("LTAB_LN_REF","usr_agreement.php");
$tpl->setVariable("LTAB_LN_REF","./templates/ingmedia/agreement/agreement_en.html");
$tpl->setVariable("LTAB_LN_FRAME","bottom");
$tpl->parseCurrentBlock();

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
//	$tpl->setVariable("LTAB_LN_REF", "./content/lm_presentation.php?ref_id=".$result[0]["lm_id"]."&obj_id=".$result[0]["obj_id"]);
	$tpl->setVariable("LTAB_LN_REF", "lo_last.php");
	$tpl->setVariable("LTAB_LN_FRAME","_parent");
	$tpl->parseCurrentBlock();

	// generate second table
	$tpl->setCurrentBlock("context_links_table");
	$tpl->parseCurrentBlock();
}

// define show_up-button
$tpl->setcurrentBlock("showupbutton");
$tpl->setVariable("SHOW_UP_BUTTON_TXT",$lng->txt("ingmedia_level_zero"));
$tpl->setVariable("SHOW_UP_BUTTON_SRC",$template_path."/images/navigation/go_up.gif");
$tpl->setVariable("SHOW_UP_BUTTON_REF1","usr_pdesktop_menu.php");
$tpl->setVariable("SHOW_UP_BUTTON_FRAME1","left");
$tpl->setVariable("SHOW_UP_BUTTON_REF2","usr_pdesktop.php?cmd=lvis_le");
$tpl->setVariable("SHOW_UP_BUTTON_FRAME2","bottom");
$tpl->parseCurrentBlock();

// close content
$tpl->parseCurrentBlock();

// output
$tpl->show();
?>