<?php
/**
* editor view
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/ilias_header.inc";

$tpl = new Template("tpl.lo_data.html", false, false);

//language substitutions
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("lo_edit"));

$tpl->setVariable("TXT_EDIT_DATA", $lng->txt("edit_data"));

$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_UID", $lng->txt("uid"));
$tpl->setVariable("TXT_PUBLISHING_ORGANISATION", $lng->txt("publishing_organisation"));
$tpl->setVariable("TXT_SUMMARY", $lng->txt("summary"));
$tpl->setVariable("TXT_AUTHORS", $lng->txt("authors"));
$tpl->setVariable("TXT_FIRSTNAME", $lng->txt("firstname"));
$tpl->setVariable("TXT_LASTNAME", $lng->txt("lastname"));
$tpl->setVariable("TXT_ADD_AUTHOR", $lng->txt("add_author"));
$tpl->setVariable("TXT_MEMBERS", $lng->txt("members"));
$tpl->setVariable("TXT_ADD_MEMBER", $lng->txt("add_member"));
$tpl->setVariable("TXT_KEYWORDS", $lng->txt("keywords"));
$tpl->setVariable("TXT_COMMA_SEPARATED", $lng->txt("comma_separated"));
$tpl->setVariable("TXT_LANGUAGE", $lng->txt("language"));
$tpl->setVariable("TXT_LEVEL", $lng->txt("level"));
$tpl->setVariable("TXT_CATEGORIES", $lng->txt("categories"));
$tpl->setVariable("TXT_NAME", $lng->txt("name"));
$tpl->setVariable("TXT_SUBCAT_NAME", $lng->txt("subcat_name"));
$tpl->setVariable("TXT_SAVE_AND_BACK", $lng->txt("save_and_back"));
$tpl->setVariable("TXT_NONE", $lng->txt("none"));
$tpl->setVariable("TXT_OTHER", $lng->txt("other"));

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>