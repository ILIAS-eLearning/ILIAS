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
* aicc learning module presentation script
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/

define("ILIAS_MODULE", "content");
chdir("..");
require_once "./include/inc.header.php";
$lng->loadLanguageModule("content");


// learning module presentation class does the rest
require_once "./content/classes/AICC/class.ilAICCPresentationGUI.php";
//session_start();
//$lm_locklist = Array("wa","ser");
//session_register($lm_locklist);
//	print_r($_SESSION["lm_locklist"]);
//if (($_SESSION['lm_locklist'])&&($_GET["obj_id"])) {
//	print_r($_SESSION['lm_locklist']);
//	if (in_array($_GET["obj_id"],$_SESSION['lm_locklist'][$ilias->account->login])) {
//		$locked = 1;
//	}
//}
//if ((!$locked)&&($_GET["obj_id"])) {
//	$lm_locklist[$ilias->account->login] = Array();
//	array_push($lm_locklist[$ilias->account->login], $_GET["obj_id"]);
//	session_register($lm_locklist);
	//echo $_GET["obj_id"]." wurde soeben gelockt!";
//} elseif ($locked) {
//	echo $_GET["obj_id"]." ist gesperrt fuer Login: ".$ilias->account->login;
//}
//if (!($_GET["obj_id"])) {
	$aicc_presentation = new ilAICCPresentationGUI();
//}

  //eval("$temp");





//$tpl->show();

?>
