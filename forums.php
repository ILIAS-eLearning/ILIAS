<?php
/**
 * forums
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */

include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$tpl = new Template("tpl.forums.html", true, true);

$tpl->setVariable("TXT_PAGEHEADLINE","_Available Forums");

for ($i = 0; $i < 3; $i++)
{
	$tpl->setCurrentBlock("forum_row");
	if ($i % 2 == 0) 
	{
		$tpl->setVariable("ROWCOL","tblrow2");
	}
	else 
	{
		$tpl->setVariable("ROWCOL","tblrow1");
	}
	$tpl->setVAriable("TITLE","Title $i");
	$tpl->parseCurrentBlock();
}
$tpl->setCurrentBlock("forum");
$tpl->setVariable("TXT_FORUM_GROUP","_Forums of Your Groups");
$tpl->setVariable("TXT_TITLE","_Title");
$tpl->setVariable("TXT_OWNER","_owner");
$tpl->setVariable("TXT_LAST_CHANGE","_lastchange");
$tpl->parseCurrentBlock();
	
$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>