<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

#require_once "./include/inc.mail.php";
require_once "classes/class.ilObjUser.php";
require_once "Services/Mail/classes/class.ilMail.php";

/**
* Mail User Interface class. (only a start, mail scripts code should go here)
*
* @author Peter Gabriel <pgabriel@databay.de>
*
* @version $Id$
*/
class ilPDMailGUI
{

	/**
	* Get Mail HTML for Personal Desktop Mail Display
	*/
	function getPDMailHTML($a_mail_id, $a_mobj_id)
	{
		global $lng, $rbacsystem;
		
		$lng->loadLanguageModule("mail");
		
		//get the mail from user
		$umail = new ilMail($_SESSION["AccountId"]);
		
		// catch hack attempts
		if (!$rbacsystem->checkAccess("mail_visible",$umail->getMailObjectReferenceId()))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->WARNING);
		}
		
		$umail->markRead(array($a_mail_id));
		$mail_data = $umail->getMail($a_mail_id);
		
		// SET MAIL DATA
		$counter = 1;
		
		$tpl = new ilTemplate("tpl.pd_mail.html", true, true, "Services/Mail");
		
		$tmp_user = new ilObjUser($mail_data["sender_id"]);
		
		// image
		$tpl->setCurrentBlock("pers_image");
		$tpl->setVariable("IMG_SENDER", $tmp_user->getPersonalPicturePath("xsmall"));
		$tpl->setVariable("ALT_SENDER", $tmp_user->getFullname());
		$tpl->parseCurrentBlock();

		// attachments
		if($mail_data["attachments"])
		{
			foreach($mail_data["attachments"] as $file)
			{
				$tpl->setCurrentBlock("a_row");
				$tpl->setVariable("HREF_DOWNLOAD",
					"ilias.php?baseClass=ilMailGUI&type=deliverFile&mail_id=".$_GET["mail_id"].
					"&filename=".rawurlencode($file));
				$tpl->setVariable("FILE_NAME", $file);
				$tpl->setVariable("TXT_DOWNLOAD", $lng->txt("download"));
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock("attachment");
			$tpl->setVariable("TXT_ATTACHMENT", $lng->txt("attachments"));
			$tpl->parseCurrentBlock();
		}

		// FROM
		$tpl->setVariable("TXT_FROM", $lng->txt("from"));
		$tpl->setVariable("FROM", $tmp_user->getFullname());

		if(!($login = $tmp_user->getLogin()))
		{
			$login = $mail_data["import_name"]." (".$lng->txt("user_deleted").")";
		}
		$tpl->setVariable("MAIL_LOGIN",$login);

		// TO
		$tpl->setVariable("TXT_TO", $lng->txt("mail_to"));
		$tpl->setVariable("TO", $mail_data["rcp_to"]);
		
		// CC
		if($mail_data["rcp_cc"])
		{
			$tpl->setCurrentBlock("cc");
			$tpl->setVariable("TXT_CC",$lng->txt("cc"));
			$tpl->setVariable("CC",$mail_data["rcp_cc"]);
			$tpl->parseCurrentBlock();
		}
		// SUBJECT
		$tpl->setVariable("TXT_SUBJECT",$lng->txt("subject"));
		$tpl->setVariable("SUBJECT",htmlspecialchars($mail_data["m_subject"]));
		
		// DATE
		$tpl->setVariable("TXT_DATE", $lng->txt("date"));
		$tpl->setVariable("DATE", ilFormat::formatDate($mail_data["send_time"]));
		
		// ATTACHMENTS
		/*
		if($mail_data["attachments"])
		{
			$tpl->setCurrentBlock("attachment");
			$tpl->setCurrentBlock("a_row");
			$counter = 1;
			foreach($mail_data["attachments"] as $file)
			{
				$tpl->setVariable("A_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
				$tpl->setVariable("FILE",$file);
				$tpl->setVariable("FILE_NAME",$file);
				$tpl->parseCurrentBlock();
			}
			$tpl->setVariable("TXT_ATTACHMENT",$lng->txt("attachments"));
			$tpl->setVariable("TXT_DOWNLOAD",$lng->txt("download"));
			$tpl->parseCurrentBlock();
		}*/
		
		// MESSAGE
		$tpl->setVariable("TXT_MESSAGE", $lng->txt("message"));
		$tpl->setVariable("MAIL_MESSAGE", nl2br(ilUtil::makeClickable(ilUtil::secureString($mail_data["m_message"]))));
		
		return $tpl->get();
	}
}
?>
