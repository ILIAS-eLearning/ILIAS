<?php

include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$tpl = new Template("tpl.search.html", false, false);

$tpl->setVariable("TXT_PAGEHEADLINE","_Search");

$tpl->setVariable("TXT_SEARCH_LESSONS","_search in lessons");
$tpl->setVariable("TXT_KEYWORDS","_keywords");
$tpl->setVariable("TXT_FULL","_full");
$tpl->setVariable("TXT_IN_ALL","_all");

$tpl->setVariable("TXT_SEARCH_USERS", "_search for users");
$tpl->setVariable("TXT_SEARCH_GROUPS","_search in groups");

$tpl->setVariable("TXT_SEARCH","_search");

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>