<?PHP
/**
* admin database
* utils for updating the database and optimize it etc.
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

$tpl = new Template("tpl.adm_mail.html", false, false);


$tpl->setVariable("TXT_MAIL", $lng->txt("mail"));

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>