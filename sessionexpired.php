<?php
/**
 * session expired
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */
include_once("./classes/class.Language.php");
include_once("HTML/IT.php");

$lng = new Language("en");

$tpl = new IntegratedTemplate("./templates");
$tpl->loadTemplatefile("tpl.sessionexpired.html", false, false);
$tpl->show();

?>