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
* mail
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "./include/inc.mail.php";
require_once "classes/class.ilObjUser.php";
require_once "classes/class.ilMail.php";

$tplprint = new ilTemplate("tpl.mail_print.html",true,true);
$tplprint->setVariable("JSPATH",$tpl->tplPath);

//get the mail from user
$umail = new ilMail($_SESSION["AccountId"]);
$mail_data = $umail->getMail($_GET["mail_id"]);


// SET MAIL DATA
// FROM
$tplprint->setVariable("TXT_FROM", $lng->txt("from"));

$tmp_user = new ilObjUser($mail_data["sender_id"]); 
if(!($login = $tmp_user->getFullname()))
{
	$login = $mail_data["import_name"]." (".$lng->txt("imported").")";
}
$tplprint->setVariable("FROM", $login);
// TO
$tplprint->setVariable("TXT_TO", $lng->txt("mail_to"));
$tplprint->setVariable("TO", $mail_data["rcp_to"]);

// CC
if($mail_data["rcp_cc"])
{
	$tplprint->setCurrentBlock("cc");
	$tplprint->setVariable("TXT_CC",$lng->txt("cc"));
	$tplprint->setVariable("CC",$mail_data["rcp_cc"]);
	$tplprint->parseCurrentBlock();
}
// SUBJECT
$tplprint->setVariable("TXT_SUBJECT",$lng->txt("subject"));
$tplprint->setVariable("SUBJECT",htmlspecialchars($mail_data["m_subject"]));

// DATE
$tplprint->setVariable("TXT_DATE", $lng->txt("date"));
$tplprint->setVariable("DATE", ilFormat::formatDate($mail_data["send_time"]));

// MESSAGE
$tplprint->setVariable("TXT_MESSAGE", $lng->txt("message"));
$tplprint->setVariable("MAIL_MESSAGE", nl2br(htmlspecialchars($mail_data["m_message"])));


$tplprint->show();

?>
