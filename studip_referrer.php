<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 2005 ILIAS open source, University of Cologne            |
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
* redirect script for studip-users
*
* @author Arne Schröder <schroeder@data-quest.de>
*
* @package studip-interface
*/

// start correct session and client
if (isset($_POST["sess_id"]))
	$_GET["sess_id"] = $_POST["sess_id"];
if (isset($_GET["sess_id"]))
{	
	setcookie("PHPSESSID",$_GET["sess_id"]);
	$_COOKIE["PHPSESSID"] = $_GET["sess_id"];
}

if (isset($_GET["client_id"]))
	$_POST["client_id"] = $_GET["client_id"];
if (isset($_POST["client_id"]))
{	
	setcookie("ilClientId",$_POST["client_id"]);
	$_COOKIE["ilClientId"] = $_POST["client_id"];
}

//if ($first_call == true)
{	
	$return_to = "none";
	
	// redirect to specified page
	switch($target)
	{
		case "login": 
			$return_to="";
		case "start": 
			if ($_GET["type"] == "lm")
			{
				include ("include/inc.header.php");
				ilUtil::redirect("ilias.php?baseClass=ilLMPresentationGUI&ref_id=" . $_GET["ref_id"]); 
			}
			if ($_GET["type"] == "tst")
				$return_to = "assessment/test.php?ref_id=" . $_GET["ref_id"] . "&cmd=run";
			if ($_GET["type"] == "sahs")
				$return_to = "content/sahs_presentation.php?ref_id=" . $_GET["ref_id"];
			if ($_GET["type"] == "htlm")
				$return_to = "content/fblm_presentation.php?ref_id=" . $_GET["ref_id"];
			break;
		case "new":	
			$return_to = "repository.php?ref_id=" . $_GET["ref_id"] . "&cmd=create&new_type=" . $_GET["type"];
			$_POST["new_type"] = $_GET["type"];
			break;
		case "edit": 
			if ($_GET["type"] == "lm")
				$return_to = "ilias.php?baseClass=ilLMEditorGUI&ref_id=" . $_GET["ref_id"];
			if ($_GET["type"] == "tst")
				$return_to = "assessment/test.php?ref_id=" . $_GET["ref_id"] . "&cmd=";
			if ($_GET["type"] == "sahs")
				$return_to = "content/sahs_edit.php?ref_id=" . $_GET["ref_id"];
			if ($_GET["type"] == "htlm")
				$return_to = "content/fblm_edit.php?ref_id=" . $_GET["ref_id"];
			break;
	}
}
if ($return_to != "none")
{
	$_GET["script"] = $return_to;
	include("start.php");
}
?>