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

sendInfo("Attention: Functions to handle literature bookmarks ist not implemented yet.");

$lit = $ilias->account->getLiterature();

foreach ($lit as $row)
{
	$tpl->setCurrentBlock("row");
	$tpl->setVariable("ROWCOL","tblrow".(($i%2)+1));
	$tpl->setVariable("DESC", $row["desc"]);
	$tpl->setVariable("URL", $row["url"]);
	$tpl->parseCurrentBlock();
}

$tpl->setVariable("TXT_LITERATURE",  $lng->txt("literature_bookmarks"));
$tpl->setVariable("TXT_DESCRIPTION",  $lng->txt("description"));
$tpl->setVariable("TXT_URL", $lng->txt("url"));
$tpl->show();
?>