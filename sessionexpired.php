<?php
/**
* session expired
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* 
* @package ilias
*/
require_once "./classes/class.ilLanguage.php";
require_once "HTML/IT.php";

$lng = new ilLanguage("en");

$tpl = new IntegratedTemplate("./templates");
$tpl->loadTemplatefile("tpl.sessionexpired.html", false, false);
$tpl->show();
?>