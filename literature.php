<?php
/**
 * literature
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$tpl = new Template("tpl.literature.html", true, true);

$tpl->setVariable("TXT_PAGEHEADLINE",  $lng->txt("literature_bookmarks"));
$tpl->setVariable("TXT_DESCRIPTION",  $lng->txt("description"));
$tpl->setVariable("TXT_URL", $lng->txt("url"));

$lit = $ilias->account->getLiterature();

foreach ($lit as $row)
{
	$tpl->setCurrentBlock("row");
	$tpl->setVariable("ROWCOL","tblrow".(($i%2)+1));
	$tpl->setVariable("DESC", $row["desc"]);
	$tpl->setVariable("URL", $row["url"]);
	$tpl->parseCurrentBlock();
}

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>