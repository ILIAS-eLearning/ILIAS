<?php
/**
* session expired
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* 
* @package ilias
*/

include_once "./classes/class.Language.php";
include_once "HTML/IT.php";

$lng = new Language("en");

$tpl = new IntegratedTemplate("./templates");
$tpl->loadTemplatefile("tpl.sessionexpired.html", false, false);
$tpl->show();
session_unregister("Error_Message");
?>