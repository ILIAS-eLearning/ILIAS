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
require_once "classes/class.ilUserAgreement.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.view_usr_agreement.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

sendInfo();
// display infopanel if something happened
infoPanel();

	// language selection
	$languages = $lng->getInstalledLanguages();
	
		$count = (int) round(count($languages) / 2);
		$num = 1;
		
		foreach ($languages as $lang_key)
		{
			/*
			if ($num === $count)
			{
				$tpl->touchBlock("lng_new_row");
			}
			*/

			$tpl->setCurrentBlock("languages");
			$tpl->setVariable("LINK_LANG", "./view_usr_agreement.php?lang=".$lang_key."&cmd=".$_GET["cmd"]);
			$tpl->setVariable("LANG_NAME", $lng->txt("lang_".$lang_key));
			$tpl->setVariable("LANG_ICON", $lang_key);
			$tpl->setVariable("BORDER", 0);
			$tpl->setVariable("VSPACE", 0);
			$tpl->parseCurrentBlock();

			$num++;
		}
		
		/*
		if (count($languages) % 2)
		{
			$tpl->touchBlock("lng_empty_cell");
		}
		*/



// display tabs
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("usr_agreement"));
$tpl->setVariable("TXT_PAGETITLE", "ILIAS3 - ".$lng->txt("usr_agreement"));
$tpl->setVariable("TXT_USR_AGREEMENT", ilUserAgreement::_getText());

if ($_GET["cmd"] == "getAcceptance")
{
	if ($_POST["status"]=="accepted")
	{
		$ilias->account->writeAccepted();
		ilUtil::redirect("start.php");
	}
	$tpl->setVariable("FORM_ACTION", "view_usr_agreement.php?cmd=getAcceptance&lang=".$_GET["lang"]);
	$tpl->setCurrentBlock("get_acceptance");
	$tpl->setVariable("ACCEPT_CHECKBOX", ilUtil::formCheckbox(0, "status", "accepted"));
	$tpl->setVariable("ACCEPT_AGREEMENT", $lng->txt("accept_usr_agreement"));
	$tpl->setVariable("TXT_SUBMIT", $lng->txt("submit"));
	$tpl->parseCurrentBlock();
}
else
{
	$tpl->setCurrentBlock("back");
	$tpl->setVariable("BACK", $lng->txt("back"));
	$tpl->setVariable("LANG_KEY", $lng->lang_key);
	$tpl->parseCurrentBlock();
}

$tpl->show();


?>
