<?php
/**
 * editor view
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$tpl = new Template("tpl.lo_edit.html", false, false);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("lo_edit"));

$tpl->setVariable("TXT_SEQUENCES", $lng->txt("sequences"));
$tpl->setVariable("TXT_ONLINE_CHAPTER", $lng->txt("online_chapter"));
$tpl->setVariable("TXT_VISIBLE_LAYERS", $lng->txt("visible_layers"));
$tpl->setVariable("TXT_REFRESH", $lng->txt("refresh"));
$tpl->setVariable("TXT_FAQ_EXERCISE", $lng->txt("faq_exercise"));
$tpl->setVariable("TXT_CHAPTER_NUMBER", $lng->txt("chapter_number"));
$tpl->setVariable("TXT_CHAPTER_TITLE", $lng->txt("chapter_title"));
$tpl->setVariable("TXT_SEQUENCE", $lng->txt("sequence"));
$tpl->setVariable("TXT_FORUM", $lng->txt("forum"));
$tpl->setVariable("TXT_STARTPAGE", $lng->txt("startpage"));
$tpl->setVariable("TXT_NO_TITLE", $lng->txt("no_title"));

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>