<?php
/**
* mail
*
* @author Peter Gabriel <pgabriel@databay.de>
* @author Eva Wenzl <ewenzl@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "./include/inc.mail.php";
require_once "classes/class.User.php";
require_once "classes/class.Mail.php";

//get the mail from user
$umail = new Mail($_SESSION["AccountId"]);
$umail->markRead(array($_GET["mail_id"]));

$mail_data = $umail->getMail($_GET["mail_id"]);

$tpl->addBlockFile("CONTENT", "content", "tpl.mail_read.html");

setLocator($_GET["mobj_id"],$_SESSION["AccountId"],$lng->txt("mail_mails_of"));

// DOWNLOAD FILE
if($_POST["cmd"])
{
	if($_POST["filename"])
	{
		switch($_POST["cmd"])
		{
			case $lng->txt("download"):
				require_once "classes/class.ilFileDataMail.php";
				
				$mfile = new ilFileDataMail($_SESSION["AccountId"]);
				if(!$path = $mfile->getAttachmentPath($_POST["filename"],$_GET["mail_id"]))
				{
					sendInfo("Error reading file!");
					break;
				}
				header("Content-Type: application/octet-stream");
				header("Content-Disposition: attachment; filename=\"".$_POST["filename"]."\"");
				readfile($path);
				break;
		}
	}
}
					
include "./include/inc.mail_buttons.php";

//buttons
$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","./mail_new.php?mobj_id=$_GET[mobj_id]&mail_id=$_GET[mail_id]&type=reply");
$tplbtn->setVariable("BTN_TXT", $lng->txt("reply"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail_new.php?mobj_id=$_GET[mobj_id]&mail_id=$_GET[mail_id]&type=forward");
$tplbtn->setVariable("BTN_TXT", $lng->txt("forward"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail_print.php?mail_id=".$_GET["mail_id"]);
$tplbtn->setVariable("BTN_TXT", $lng->txt("print"));
$tplbtn->setVariable("BTN_TARGET","target=\"_blank\"");
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail_addressbook.php?mobj_id=$_GET[mobj_id]&mail_id=$_GET[mail_id]&type=add");
$tplbtn->setVariable("BTN_TXT", $lng->txt("mail_add_to_addressbook"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail.php?mobj_id=$_GET[mobj_id]&mail_id=$_GET[mail_id]");
$tplbtn->setVariable("BTN_TXT", $lng->txt("delete"));
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl->setVariable("BUTTONS2",$tplbtn->get());
$tpl->setVariable("ACTION","mail_read.php?mobj_id=$_GET[mobj_id]&mail_id=$_GET[mail_id]");

// SET MAIL DATA
$counter = 1;
// FROM
$tpl->setVariable("TXT_FROM", $lng->txt("from"));
$tmp_user = new User($mail_data["sender_id"]); 
$tpl->setVariable("FROM", $tmp_user->getFullname());
$tpl->setVariable("CSSROW_FROM",++$counter%2 ? 'tblrow1' : 'tblrow2');
// TO
$tpl->setVariable("TXT_TO", $lng->txt("to"));
$tpl->setVariable("TO", $mail_data["rcp_to"]);
$tpl->setVariable("CSSROW_TO",(++$counter)%2 ? 'tblrow1' : 'tblrow2');

// CC
if($mail_data["rcp_cc"])
{
	$tpl->setCurrentBlock("cc");
	$tpl->setVariable("TXT_CC",$lng->txt("cc"));
	$tpl->setVariable("CC",$mail_data["rcp_cc"]);
	$tpl->setVariable("CSSROW_CC",(++$counter)%2 ? 'tblrow1' : 'tblrow2');
	$tpl->parseCurrentBlock();
}
// SUBJECT
$tpl->setVariable("TXT_SUBJECT",$lng->txt("subject"));
$tpl->setVariable("SUBJECT", $mail_data["m_subject"]);
$tpl->setVariable("CSSROW_SUBJ",(++$counter)%2 ? 'tblrow1' : 'tblrow2');

// DATE
$tpl->setVariable("TXT_DATE", $lng->txt("date"));
$tpl->setVariable("DATE", Format::formatDate($mail_data["send_time"]));
$tpl->setVariable("CSSROW_DATE",(++$counter)%2 ? 'tblrow1' : 'tblrow2');

// ATTACHMENTS
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
}

// MESSAGE
$tpl->setVariable("TXT_MESSAGE", $lng->txt("message"));
$tpl->setVariable("MAIL_MESSAGE", nl2br(TUtil::makeClickable(htmlentities($mail_data["m_message"]))));

$tpl->show();
?>
