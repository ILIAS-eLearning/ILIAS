<?php
/**
* editor view
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.adm_basicdata.html");
$tpl->setCurrentBlock("content");
require_once("./include/inc.basicdata.php");
$tpl->parseCurrentBlock();

$tpl->show();

?>