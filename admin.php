<?php
/**
* admin interface
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

$tpl->setVariable("PAGETITLE"," - ".$lng->txt("profile"));

$tpl->addBlockFile("CONTENT", "content", "tpl.admin.html");

//User-Administration
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("create"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("edit"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("delete"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminblock");
$tpl->setVariable("HEADLINE", strtoupper($lng->txt("user")));
$tpl->parseCurrentBlock();

//Lesson-Administration
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("create"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("edit"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("delete"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("rights"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminblock");
$tpl->setVariable("HEADLINE", strtoupper($lng->txt("los")));
$tpl->parseCurrentBlock();

//Basic-Administration
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("basic_data"));
$tpl->setVariable("LINK","adm_basicdata.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("objects"));
$tpl->setVariable("LINK","admindex.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("languages"));
$tpl->setVariable("LINK","adm_languages.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("files_location"));
$tpl->setVariable("LINK","adm_files.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("database"));
$tpl->setVariable("LINK","adm_database.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminitem");
$tpl->setVariable("ITEM", $lng->txt("mail"));
$tpl->setVariable("LINK","adm_mail.php");
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adminblock");
$tpl->setVariable("HEADLINE", strtoupper($lng->txt("system")));
$tpl->parseCurrentBlock();

//main table
$tpl->touchBlock("adminrow");

if ($_GET["message"])
{
        $tpl->addBlockFile("MSG","sys_message","tpl.message.html");
        $tpl->setCurrentBlock("sys_message");
        $tpl->setVariable("MESSAGE",urldecode($_GET["message"]));
        $tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_ADMINISTRATION", $lng->txt("administration"));
$tpl->parseCurrentBlock();

$tpl->show();

?>