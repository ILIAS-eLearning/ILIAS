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

$tpl = new IntegratedTemplate($TPLPATH);
$tpl->loadTemplateFile("tpl.literature.html", true, true);

$tpl->setVariable("TXT_PAGEHEADLINE","_Literature Bookmarks");
$tpl->setVariable("TXT_DESCRIPTION","_description");
$tpl->setVariable("TXT_URL","_url");

$lit = $ilias->account->getLiterature();

foreach ($lit as $row)
{
	$tpl->setCurrentBlock("row");
	$tpl->setVariable("DESC", $row["desc"]);
	$tpl->setVariable("URL", $row["url"]);
	$tpl->parseCurrentBlock();
}

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>