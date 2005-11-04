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
* adm_menu
* main script for explorer window in admin console
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/
require_once "include/inc.header.php";
require_once "classes/class.ilExplorer.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

$explorer = new ilExplorer("adm_object.php");

$explorer->setExpand($_GET["expand"]);

// hide RecoveryFolder if empty
if (!$tree->getChilds(RECOVERY_FOLDER_ID))
{
	$explorer->addFilter("recf");
}
/*
$explorer->addFilter("root");
$explorer->addFilter("cat");
$explorer->addFilter("grp");
$explorer->addFilter("crs");
$explorer->addFilter("le");
$explorer->addFilter("frm");
$explorer->addFilter("lo");
$explorer->addFilter("rolf");
$explorer->addFilter("adm");
$explorer->addFilter("lngf");
$explorer->addFilter("usrf");
$explorer->addFilter("objf");
*/
//$explorer->setFiltered(false);
$explorer->setOutput(0);

$output = $explorer->getOutput();

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_EXPLORER_HEADER", $lng->txt("all_objects"));
$tpl->setVariable("EXP_REFRESH", $lng->txt("refresh"));
$tpl->setVariable("EXPLORER",$output);
$tpl->setVariable("ACTION", "adm_menu.php?expand=".$_GET["expand"]);
$tpl->parseCurrentBlock();

$tpl->show(false);
$ilBench->save();
?>
