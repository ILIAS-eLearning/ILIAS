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
* groups
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

/*$tplbtn = new ilTemplate("tpl.buttons.html", false, false);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","groups.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("back"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();


$tpl = new ilTemplate("tpl.group_new.html", false, false);
$tpl->setVariable("BUTTONS",$tplbtn->get());

//$tpl->setVariable("BUTTONS",$tplbtn->get());

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("new_group"));

$tpl->setVariable("TXT_GROUPNAME", $lng->txt("groupname"));
$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));
$tpl->setVariable("TXT_ACCESS", $lng->txt("access"));
$tpl->setVariable("TXT_GROUP_SCOPE", $lng->txt("groupscope"));
$tpl->setVariable("TXT_SAVE", $lng->txt("save"));


$tplmain = new ilTemplate("tpl.main.html", false, false);
$tplmain->setVariable("CONTENT",$tpl->get());
$tplmain->show();*/
global $rbacsystem;
	
	
	$data = array();
	$data["fields"] = array();
	$data["fields"]["group_name"] = "";
	$data["fields"]["desc"] = "";
	
	
	
	$tpl->addBlockFile("CONTENT", "content", "tpl.group_new.html");
	infoPanel();
	
	
	$tpl->setVariable("TXT_PAGEHEADLINE", "Name der Kategorie");	
	
	//$this->getTemplateFile("new","group");
	foreach ($data["fields"] as $key => $val)
	{  
		$tpl->setVariable("TXT_".strtoupper($key), $lng->txt($key));
		$tpl->setVariable(strtoupper($key), $val);
		
	}
			
	//$stati = array("group_status_public","group_status_private","group_status_closed");
	$stati = array("group_status_public","group_status_closed");
	
	//build form
	$opts = ilUtil::formSelect(0,"group_status_select",$stati);
	
	$tpl->setVariable("SELECT_OBJTYPE", $opts);
	$tpl->setVariable("TXT_GROUP_STATUS", $lng->txt("group_status"));
	
	$tpl->setVariable("FORMACTION", "adm_object.php?cmd=save"."&ref_id=".$_GET["ref_id"].
		"&new_type=".$_POST["new_type"]);
	
	$tpl->setVariable("TXT_REQUIRED_FLD", $lng->txt("required_field"));
	$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
	$tpl->parseCurrentBlock();
	$tpl->show();
?>
