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

$tpl = new Template("tpl.adm_languages.html", false, false);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("languages"));


$langs = $lng->getAllLanguages();

$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK",".php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("refresh_list"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl->setVariable("BUTTONS",$tplbtn->get());

unset($tplbtn);
$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK",".php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("refresh"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK",".php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("change"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK",".php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("check"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl->setVariable("BUTTONS2",$tplbtn->get());
$tpl->setVariable("LNG_AVAILLANG_TXT", $lng->txt("available_languages"));
$tpl->setVariable("LNG_LANG_TXT", $lng->txt("language"));
$tpl->setVariable("LNG_STATUS_TXT", $lng->txt("status"));
$tpl->setVariable("LNG_LASTCHANGE_TXT", $lng->txt("last_change"));

foreach ($langs as $row)
{
	$i++;
	$tpl->setCurrentBlock("language_row");
	$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
	$tpl->setVariable("LNG_TITLE",$row["title"]);
	$tpl->setVariable("LNG_STATUS",$row["status"]);
	$tpl->setVariable("LNG_DATE",$row["lastchange"]);
	$tpl->parseCurrentBlock();
}

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>