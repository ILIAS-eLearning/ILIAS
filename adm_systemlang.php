<?PHP

require_once "./include/inc.header.php";

$lng->setSystemLanguage($ilias->ini->readVariable("language", "default"));
$tpl->addBlockFile("CONTENT", "syslang", "tpl.lng_edit.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","adm_languages.php");
$tpl->setVariable("BTN_TXT", $lng->txt("back"));
$tpl->parseCurrentBlock();

$lng->getLanguageNames($lng->userLang);

foreach ($lng->LANGUAGES as $lang)
{
	$languages[$lang["id"]] = "lang_".$lang["id"];
}

$tpl->setCurrentBlock("chkbox");
$tpl->setVariable("LANG_OPTIONS",TUtil::formSelect($lng->systemLang,"lang_key",$languages));
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("syslang");
$tpl->setVariable("TXT_CHANGE_LANG", $lng->txt("set_system_language"));
$tpl->setVariable("TXT_CHANGE_LANG2", $lng->txt("system_choose_language"));
$tpl->setVariable("SUBMIT", $lng->txt("submit"));
$tpl->setVariable("FORMACTION","adm_languages.php?cmd=setsyslang");
$tpl->parseCurrentBlock();



$tpl->show();

?>