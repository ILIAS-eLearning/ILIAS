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

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("forums_available"));

for ($i = 0; $i < 3; $i++)
{
        $tpl->setCurrentBlock("forum_row");
        if ($i % 2 == 0)
        {
                $tpl->setVariable("ROWCOL", "tblrow2");
        }
        else
        {
                $tpl->setVariable("ROWCOL","tblrow1");
        }
        $tpl->setVAriable("TITLE","Title $i");
        $tpl->parseCurrentBlock();
}
$tpl->setCurrentBlock("forum");
$tpl->setVariable("TXT_FORUM_GROUP", $lng->txt("forums_of_your_groups"));
$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_OWNER", $lng->txt("owner"));
$tpl->setVariable("TXT_LAST_CHANGE", $lng->txt("last_change"));
$tpl->parseCurrentBlock();

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>