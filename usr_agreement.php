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
* display user agreement
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/

require_once "./include/inc.header.php";

// catch hack attempts
if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
{
	$ilias->raiseError($lng->txt("msg_not_available_for_anon"),$ilias->error_obj->MESSAGE);
}

$tpl->addBlockFile("CONTENT", "content", "tpl.usr_agreement.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
//$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

// set locator 
$tpl->setVariable("TXT_LOCATOR",$lng->txt("locator"));
$tpl->touchBlock("locator_separator");
$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("personal_desktop"));
$tpl->setVariable("LINK_ITEM", "usr_personaldesktop.php");
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("usr_agreement"));
$tpl->setVariable("LINK_ITEM", "usr_agreement.php");
$tpl->parseCurrentBlock();

// catch feedback message
sendInfo();
// display infopanel if something happened
infoPanel();

// display tabs
include "./include/inc.personaldesktop_buttons.php";

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("personal_desktop"));
//$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("usr_agreement"));
$tpl->setVariable("TXT_AGREEMENT", $lng->txt("usr_agreement"));
$tpl->setVariable("TXT_USR_AGREEMENT", getUserAgreement()); 
$tpl->setVariable("TXT_ACCEPT", $lng->txt("accept_usr_agreement"));
$tpl->setVariable("TXT_YES", $lng->txt("yes"));
$tpl->setVariable("TXT_NO", $lng->txt("no"));
$tpl->setVariable("TXT_SUBMIT", $lng->txt("save"));

$tpl->show();

function getUserAgreement()
{
	global $lng, $ilias;
	
	$tmpPath = getcwd();
	$agrPath = $tmpPath."/agreement";
	chdir($agrPath);

	$agreement = "agreement_".$lng->lang_user.".html";

	if ($agreement)
	{
		if ($content = file($agreement))
		{
			foreach ($content as $key => $val)
			{
				$text .= trim(nl2br($val));
			}
			return $text;
		}
		else
		{
			$ilias->raiseError($lng->txt("usr_agreement_empty"),$ilias->error_obj->MESSAGE);
		}
	}
	else
	{
		$ilias->raiseError($lng->txt("file_not_found"),$ilias->error_obj->MESSAGE);
	}	
}
?>