<?php
/**
* main menu
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*/

$tplnav = new Template("tpl.main_buttons.html", true, true);

$tplnav->setCurrentBlock("userisadmin");
$tplnav->setVariable("TXT_ADMINISTRATION", $lng->txt("administration"));
$tplnav->parseCurrentBlock();

$tplnav->setCurrentBlock("userisauthor");
$tplnav->setVariable("TXT_EDITOR", $lng->txt("editor"));
$tplnav->parseCurrentBlock();

$tplnav->setVariable("TXT_PERSONAL_DESKTOP", $lng->txt("personal_desktop"));
$tplnav->setVariable("TXT_LO_OVERVIEW", $lng->txt("lo_overview"));
$tplnav->setVariable("TXT_BOOKMARKS", $lng->txt("bookmarks"));
$tplnav->setVariable("TXT_SEARCH", $lng->txt("search"));
$tplnav->setVariable("TXT_LITERATURE", $lng->txt("literature"));
$tplnav->setVariable("TXT_MAIL", $lng->txt("mail"));
$tplnav->setVariable("TXT_FORUMS", $lng->txt("forums"));
$tplnav->setVariable("TXT_GROUPS", $lng->txt("groups"));
$tplnav->setVariable("TXT_HELP", $lng->txt("help"));
$tplnav->setVariable("TXT_FEEDBACK", $lng->txt("feedback"));
$tplnav->setVariable("TXT_LOGOUT", $lng->txt("logout"));

?>