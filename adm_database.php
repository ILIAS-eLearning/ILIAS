<?PHP
/**
 * admin database
 * utils for updating the database and optimize it etc.
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$lng = new Language($ilias->account->data["language"]);

$tpl = new Template("tpl.adm_database.html", false, false);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("database"));

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>