<?PHP
/**
 * admin languages
 * utils for updating the database and optimize it etc.
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$lng = new Language($ilias->account->data["language"]);

$tpl = new Template("tpl.adm_languages.html", true, true);
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("languages"));

if ($_POST["func"]=="gen_langs")
{
	if ($lng->generateLanguageFiles($_POST["langfile"])==false)
	{
		$tpl->setCurrentBlock("message");
		$tpl->setVariable("MSG", $lng->error);
		$tpl->parseCurrentBlock();		
	}
}




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
$tpl->setVariable("TXT_AVAILLANG", $lng->txt("available_languages"));
$tpl->setVariable("TXT_LANG", $lng->txt("language"));
$tpl->setVariable("TXT_STATUS", $lng->txt("status"));
$tpl->setVariable("TXT_LASTCHANGE", $lng->txt("last_change"));

foreach ($langs as $row)
{
	$i++;
	$tpl->setCurrentBlock("language_row");
	$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
	$tpl->setVariable("TITLE", $row["name"]);
	$tpl->setVariable("STATUS", "_installed");
	$tpl->setVariable("LASTCHANGE", " ");
	$tpl->parseCurrentBlock();
}

$tpl->setVariable("TXT_GENERATE", "_generate from");
$tpl->setVariable("LANGMASTERFILE", "languages.txt");
$tpl->setVariable("TXT_SUBMIT", $lng->txt("submit"));

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>