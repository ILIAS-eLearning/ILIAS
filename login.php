<?php
/**
* login script for ilias
*
* @author Sascha Hofmann <shofmann@databay.de>
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias-layout
*/
require_once "include/ilias_header.inc";

//check for auth
if ($ilias->auth->getAuth())
{
	header("location: start.php");
	exit;
}

//instantiate login template
$tplContent = new Template("tpl.login.html", true, true);



//language handling
if ($_GET["lang"] != "")
{
	$lang = $_GET["lang"];
}

if ($lang == "")
{
	$lang = $ilias->ini->readVariable("language","default");
}

//instantiate language
$lng = new Language($lang);
$langs = $lng->getInstalledLanguages();

foreach ($langs as $row)
{
	$tplContent->setCurrentBlock("languages");
	$tplContent->setVariable("LANG_ID", $row["id"]);
	$tplContent->setVariable("LANG_DESC", $row["name"]);
	$tplContent->setVariable("LANG_IMG", "./lang/".$row["id"].".gif");
	$tplContent->parseCurrentBlock();
}
$tplContent->setVariable("ILIAS_RELEASE", $ilias->getSetting("ilias_version"));
$tplContent->setVariable("TXT_ILIAS_LOGIN", $lng->txt("login_to_ilias"));
$tplContent->setVariable("FORMACTION", "login.php?lang=".$lang);
$tplContent->setVariable("TXT_USERNAME", $lng->txt("username"));
$tplContent->setVariable("TXT_PASSWORD", $lng->txt("password"));
$tplContent->setVariable("TXT_SUBMIT", $lng->txt("submit"));
$tplContent->setVariable("TXT_CHOOSE_LANGUAGE", $lng->txt("choose_language"));

if (!empty($ilias->auth->status) && $ilias->auth->status == AUTH_EXPIRED)
{
	$tplContent->setVariable(TXT_MSG_LOGIN_FAILED, $lng->txt("err_session_expired"));
}
else if (!empty($ilias->auth->status) && $ilias->auth->status == AUTH_IDLED)
{
	$tplContent->setVariable(TXT_MSG_LOGIN_FAILED, $lng->txt("err_idled"));
} 
else if (!empty ($ilias->auth->status) && $ilias->auth->status == AUTH_WRONG_LOGIN)
{
	$tplContent->setVariable(TXT_MSG_LOGIN_FAILED, $lng->txt("err_wrong_login"));
}

$tplContent->setVariable(PHP_SELF,$_SERVER['PHP_SELF']);
$tplContent->setVariable(USERNAME,$username);

$tplmain->setVariable("PAGECONTENT", $tplContent->get());
//$tplContent->show();
$tplmain->show();
?>
