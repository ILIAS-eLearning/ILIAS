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


/*
* personal desktop
* this file shows two frames: ( usr_pdesktop_menu.php, usr_pdesktop.php )
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

//add template for content within the linkbar
$tpl->addBlockfile("CONTENT", "content", "tpl.content_start.html");
//add template for link table
$tpl->addBlockFile("LTAB_LN", "ltab_ln", "tpl.ltab_ln.html");

//$tpl->setCurrentBlock("page_init");
$tpl->setCurrentBlock("content");

//$tpl->setVariable("SHOW_PAGE_IMAGE_REF","usr_pdesktop_menu.php");
$tpl->setVariable("SHOW_PAGE_IMAGE_REF","start.php");
$tpl->setVariable("SHOW_PAGE_IMAGE_TARGET","_top");
$tpl->setVariable("SHOW_PAGE_IMAGE_SRC",$template_path."/images/layout/schreibtisch.gif");


// 1. Tabellenzellen definieren
$tpl->setCurrentBlock("ltab_cell_two_frames");
$tpl->setVariable("LTAB_LN_TXT",$lng->txt("ingmedia_pers_dates"));
$tpl->setVariable("LTAB_LN_REF1","usr_pdesktop_menu2.php");
$tpl->setVariable("LTAB_LN_FRAME1","left");
$tpl->setVariable("LTAB_LN_REF2","usr_pdesktop_info.php");
$tpl->setVariable("LTAB_LN_FRAME2","bottom");
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("ltab_cell_one_frame");
$tpl->setVariable("LTAB_LN_TXT",$lng->txt("who_is_online"));
$tpl->setVariable("LTAB_LN_REF","usr_pdesktop.php?cmd=whois");
$tpl->setVariable("LTAB_LN_FRAME","bottom");
$tpl->parseCurrentBlock();
/*
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
*/
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

// spacer
$tpl->touchBlock("distance");
$tpl->parseCurrentBlock();

// 1. Tabelle generieren
$tpl->setCurrentBlock("context_links_table");
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("showemptybutton");
$tpl->setVariable("SHOW_EMPTY_BUTTON_TXT",$lng->txt("ingmedia_level_zero"));
$tpl->setVariable("SHOW_EMPTY_BUTTON_SRC",$template_path."/images/navigation/go_empty.gif");
$tpl->parseCurrentBlock();

// content schließen
$tpl->parseCurrentBlock();

// output
$tpl->show();
?>