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
* buttons for mail module
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/

$lng->loadLanguageModule("mail");
$tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");

$script_name = basename($_SERVER["PATH_TRANSLATED"]);

$file_name = basename($_SERVER["PATH_INFO"]);

// FOLDER
$inc_type = $script_name == "mail.php" ? "tabactive" : "tabinactive";
$inhalt1[] = array($inc_type,"mail.php?mobj_id=$_GET[mobj_id]&type=new",$lng->txt("fold"),"bottom");

// COMPOSE
$inc_type = $script_name == "mail_new.php" ? "tabactive" : "tabinactive";
$inhalt1[] = array($inc_type,"mail_new.php?mobj_id=$_GET[mobj_id]&type=new",$lng->txt("compose"),"bottom");

// ADDRESSBOOK
$inc_type = $script_name == "mail_addressbook.php" ? "tabactive" : "tabinactive";
$inhalt1[] = array($inc_type,"mail_addressbook.php?mobj_id=$_GET[mobj_id]",$lng->txt("mail_addressbook"),"bottom");


// OPTIONS
$inc_type = $script_name == "mail_options.php" ? "tabactive" : "tabinactive";
$inhalt1[] = array($inc_type,"mail_options.php?mobj_id=$_GET[mobj_id]",$lng->txt("options"),"bottom");

// FLATVIEW <-> TREEVIEW

$inc_type = $script_name == "tabinactive";
	
if (!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == "flat")
{
	$inhalt1[] = array($inc_type,"mail_frameset.php?viewmode=tree",$lng->txt("treeview"),"bottom");
}
else
{
	$inhalt1[] = array($inc_type,"mail_frameset.php?viewmode=flat",$lng->txt("flatview"),"bottom");
}
		  
for ( $i=0; $i<sizeof($inhalt1); $i++)
{
	if ($inhalt1[$i][1] != "")
	{	$tpl->setCurrentBlock("tab");
		$tpl->setVariable("TAB_TYPE",$inhalt1[$i][0]);
		$tpl->setVariable("TAB_LINK",$inhalt1[$i][1]);
		$tpl->setVariable("TAB_TEXT",$inhalt1[$i][2]);
		$tpl->setVariable("TAB_TARGET",$inhalt1[$i][3]);
		$tpl->parseCurrentBlock();
	}
}

?>
