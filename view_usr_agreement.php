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
* displays user agreement, when not logged in
*
* @author Nils Eiken <nils.eiken@uni-koeln.de>
*
*
* @package ilias
*/

require_once "./include/inc.header.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.view_usr_agreement.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

sendInfo();
// display infopanel if something happened
infoPanel();

// display tabs
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("usr_agreement"));
$tpl->setVariable("TXT_USR_AGREEMENT", getUserAgreement());
$tpl->setVariable("BACK", $lng->txt("back"));
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
