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
* main program
* this file shows two frames : usr_pdektop_menu.php, usr_pdesktop.php.
* adapted from INGMEDIA / ilias 2
*
* @author Pia Behr <p.behr@fh-aachen.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";


//add template for content within the linkbar
$tpl->addBlockfile("CONTENT", "content", "tpl.usr_text.html");


$txt_title = $lng->txt("ingmedia_use_title");
$txt_text = $lng->txt("ingmedia_use_info1");
$txt_text .= "<BR><BR>".$lng->txt("ingmedia_use_info2");
$txt_text .= "<BR><BR>".$lng->txt("ingmedia_use_info3");

$tpl->setCurrentBlock("using_info");
$tpl->setVariable("TXT_TITLE",$txt_title);
$tpl->setVariable("TXT_NOTES",$txt_text);
$tpl->parseCurrentBlock();

// output
$tpl->show();

?>