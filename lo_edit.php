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

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>