<?php
/**
 * groups
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$tpl = new Template("tpl.group_new.html", false, true);

//$tpl->setVariable("BUTTONS",$tplbtn->get());

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("groups_new"));


$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>