<?php
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
require_once "classes/class.ilUser.php";
require_once "classes/class.ilMail.php";

$tplprint = new ilTemplate("tpl.mail_print.html",true,true);
$tplprint->setVariable("MESSAGE","hallo");


//get the mail from user
$umail = new ilMail($_SESSION["AccountId"]);
$mail_data = $umail->getMail($_GET["mail_id"]);

// SET MAIL DATA
// FROM
$tplprint->setVariable("TXT_FROM", $lng->txt("from"));
$tmp_user = new ilUser($mail_data["sender_id"]); 
$tplprint->setVariable("FROM", $tmp_user->getFullname());
// TO
$tplprint->setVariable("TXT_TO", $lng->txt("to"));
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
$tplprint->setVariable("SUBJECT", $mail_data["m_subject"]);

// DATE
$tplprint->setVariable("TXT_DATE", $lng->txt("date"));
$tplprint->setVariable("DATE", ilFormat::formatDate($mail_data["send_time"]));

// MESSAGE
$tplprint->setVariable("TXT_MESSAGE", $lng->txt("message").":");
$tplprint->setVariable("MAIL_MESSAGE", nl2br(htmlentities($mail_data["m_message"])));


$tplprint->show();

?>
