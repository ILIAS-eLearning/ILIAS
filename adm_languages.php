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
$lng->setSystemLanguage($ilias->ini->readVariable("language", "default"));
$lng->setUserLanguage($ilias->account->data["language"]);


$tpl = new Template("tpl.adm_languages.html", true, true);
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("languages"));

if ($_POST["cmd"]=="generateall")
{
	if ($lng->generateLanguageFiles()==false)
	{
		$msg = $lng->error;
	}
}

if ($_POST["action"] == "del")
{	
	for ($i = 0; $i<count($marker); $i++)
	{
		if ($lng->deinstallLanguage($marker[$i]) == false)
		{
			$msg = $lng->error;
		}
	}
}

if ($_POST["action"] == "install")
{	
	for ($i = 0; $i<count($marker); $i++)
	{
		if ($lng->installLanguage($marker[$i]) == false)
		{
			$msg = $lng->error;
		}
	}
}

if ($_GET["cmd"] == "install")
{	
	if ($lng->installLanguage($_GET["id"]) == false)
	{
		$msg = $lng->error;
	}
}

if ($_GET["cmd"] == "del")
{	
	if ($lng->deinstallLanguage($_GET["id"]) == false)
	{
		$msg = $lng->error;
	}
}

$tpl->setCurrentBlock("message");
$tpl->setVariable("MSG", $msg);
$tpl->parseCurrentBlock();		

$langs = $lng->getAvailableLanguages();

$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","./admin.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("back"));
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl->setVariable("BUTTONS",$tplbtn->get());
unset($tplbtn);
$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","./adm_languages.php");
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
	$tpl->setVariable("ID", $row["id"]);
	$tpl->setVariable("TITLE", $row["name"]);
	$tpl->setVariable("STATUS", $lng->txt($row["status"]));
	
	if ($row["status"] == "installed")
	{
	    $link = "adm_languages.php?cmd=del&amp;id=".$row["id"];
	}

	if ($row["status"] == "not_installed")
	{
	    $link = "adm_languages.php?cmd=install&amp;id=".$row["id"];
	}
	
	$tpl->setVariable("LINK", $link);
	
	if ($row["lastchange"] != "") {
		$tpl->setVariable("LASTCHANGE", $lng->fmtDateTime($row["lastchange"]));
	}
	$tpl->parseCurrentBlock();
}

$tpl->setVariable("TXT_GENERATE", $lng->txt("languages_generate_from_file"));
$tpl->setVariable("LANGMASTERFILE", "languages.txt");
$tpl->setVariable("TXT_SELECTED", $lng->txt("selected"));
$tpl->setVariable("TXT_SUBMIT", $lng->txt("submit"));
$tpl->setVariable("FORMACTION", "adm_languages.php");

//add delete, reinstall feature
$tpl->setCurrentBlock("selaction");
$tpl->setVariable("VALUE", "del");
$tpl->setVariable("OPTION", $lng->txt("delete"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("selaction");
$tpl->setVariable("VALUE", "install");
$tpl->setVariable("OPTION", $lng->txt("install"));
$tpl->parseCurrentBlock();


$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>