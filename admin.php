<?php
/**
* admin interface
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/ilias_header.inc";

$tplmain->setVariable("TXT_PAGETITLE","ILIAS - ".$lng->txt("profile"));

$tpl = new Template("tpl.admin.html", true, true);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("administration"));

//User-Administration
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("create"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("edit"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("delete"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminblock");
$tpl->setVariable("TXT_HEADLINE", strtoupper($lng->txt("user")));
$tpl->parseCurrentBlock();

//Lesson-Administration
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("create"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("edit"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("delete"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("rights"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminblock");
$tpl->setVariable("TXT_HEADLINE", strtoupper($lng->txt("los")));
$tpl->parseCurrentBlock();

//Basic-Administration
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("basic_data"));
$tpl->setVariable("LINK","adm_basicdata.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("objects"));
$tpl->setVariable("LINK","admindex.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("languages"));
$tpl->setVariable("LINK","adm_languages.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("files_location"));
$tpl->setVariable("LINK","adm_files.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("database"));
$tpl->setVariable("LINK","adm_database.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("TXT_ITEM", $lng->txt("mail"));
$tpl->setVariable("LINK","adm_mail.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminblock");
$tpl->setVariable("TXT_HEADLINE", strtoupper($lng->txt("system")));
$tpl->parseCurrentBlock();

//main table
$tpl->touchBlock("adminrow");

if ($_GET["message"])
{
	$tpl->addBlockFile("MESSAGEFILE","sys_message","tpl.message.html");
	$tpl->setCurrentBlock("sys_message");
	$tpl->setVariable("MESSAGE",urldecode($_GET["message"]));
	$tpl->parseCurrentBlock();
}

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>