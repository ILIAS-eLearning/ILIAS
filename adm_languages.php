<?PHP
/**
* admin languages
* utils for updating the database and optimize it etc.
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/ilias_header.inc";

$lng->setSystemLanguage($ilias->ini->readVariable("language", "default"));

$tpl->addBlockFile("CONTENT", "content", "tpl.adm_languages.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

switch ($_GET["cmd"])
{
    case "install":
        $result = $lng->installLanguage($lang_key);
	break;

	case "uninstall":
		$result = $lng->uninstallLanguage($lang_key);
	break;
	
	case "refresh":
		$result = $lng->refreshLanguages();
    break;

	case "checkfiles":
		$lng->checkLanguageFiles();
    break;
	
	case "setsyslang":
		$result = $lng->setDefaultLanguage($_POST["lang_key"]);
	break;
	
	case "setuserlang":
		$result = $lng->setUserLanguage($_POST["lang_key"]);
	break;
	
    default:
	break;
}

$languages = $lng->getLanguages();

//$tpl->setCurrentBlock("message");
//$tpl->setVariable("MSG", $msg);
//$tpl->parseCurrentBlock();

//$langs = $lng->getAvailableLanguages();

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","./admin.php");
$tpl->setVariable("BTN_TXT", $lng->txt("back"));
$tpl->parseCurrentBlock();

$tpl->touchBlock("btn_row");

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","./adm_languages.php");
$tpl->setVariable("BTN_TXT", $lng->txt("refresh"));
$tpl->parseCurrentBlock();


$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","./adm_languages.php?cmd=refresh");
$tpl->setVariable("BTN_TXT", $lng->txt("update_language"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","./adm_systemlang.php");
$tpl->setVariable("BTN_TXT", $lng->txt("system_language"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","userlang.php");
$tpl->setVariable("BTN_TXT", $lng->txt("change_language"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","./adm_languages.php?cmd=checkfiles");
$tpl->setVariable("BTN_TXT", $lng->txt("check_language"));
$tpl->parseCurrentBlock();

$tpl->touchBlock("btn_row");


$tpl->setVariable("TXT_AVAILLANG", $lng->txt("available_languages"));
$tpl->setVariable("TXT_LANG", $lng->txt("language"));
$tpl->setVariable("TXT_STATUS", $lng->txt("status"));
$tpl->setVariable("TXT_LASTCHANGE", $lng->txt("last_change"));

//vd($languages);
//exit;

foreach ($languages as $lang_key => $row)
{
		$i++;
		$link = "";
		$tpl->setCurrentBlock("language_row");
		$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
		$tpl->setVariable("LNG", $row["long"]);

		if ($row["installed"]) 
		{
			$tpl->setVariable("STATUS", $lng->txt("installed"));
			//$_GET["message"] = "Systemsprache kann nicht deinstalliert werden";
			$link = "adm_languages.php?cmd=uninstall&lang_key=".$lang_key;
		}
		else
		{
			$tpl->setVariable("STATUS", $lng->txt("not_installed"));
			$link = "adm_languages.php?cmd=install&lang_key=".$lang_key;
		}
		
		// avoid uninstalling of system language and language in use
		if (($lang_key == $lng->systemLang) || ($lang_key == $lng->userLang))
		{
			if ($lang_key == $lng->systemLang)
			{
				$tpl->setVariable("STATUS", $lng->txt("system_language"));
				$link = "";
			}
			else
			{
				$tpl->setVariable("STATUS", $lng->txt("in_use"));
				$link = "";
			}
		}
		
		$tpl->setVariable("LINK", $link);
		
        if ($row["update"] != "") {
                $tpl->setVariable("LASTCHANGE", $lng->ftimestamp2datetimeDE($row["update"]));
        }
        $tpl->parseCurrentBlock();
}

// ERROR HANDLER SETS $_GET["message"] IN CASE OF $error_obj->MESSAGE
if ($_GET["message"])
{
    $tpl->addBlockFile("MESSAGE", "message", "tpl.message.html");
	$tpl->setCurrentBlock("message");
	$tpl->setVariable("MSG", urldecode($_GET["message"]));
	$tpl->parseCurrentBlock();
}
/*
$tpl->setVariable("TXT_GENERATE", $lng->txt("languages_generate_from_file"));
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

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_LANGUAGES", $lng->txt("languages"));
$tpl->parseCurrentBlock();

*/

$tpl->show();

?>