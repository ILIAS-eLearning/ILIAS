<?php
/**
* literature
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.literature.html");

$lit = $ilias->account->getLiterature();

foreach ($lit as $row)
{
	$tpl->setCurrentBlock("row");
	$tpl->setVariable("ROWCOL","tblrow".(($i%2)+1));
	$tpl->setVariable("DESC", $row["desc"]);
	$tpl->setVariable("URL", $row["url"]);
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_LITERATURE",  $lng->txt("literature_bookmarks"));
$tpl->setVariable("TXT_DESCRIPTION",  $lng->txt("description"));
$tpl->setVariable("TXT_URL", $lng->txt("url"));
$tpl->parseCurrentBlock();
$tpl->show();
?>