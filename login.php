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
	exit();
}

//instantiate login template
$tpl->addBlockFile("CONTENT", "content", "tpl.login.html");

//language handling
if ($_GET["lang"] == "")
{
	$lang = $ilias->ini->readVariable("language","default");
}

if ($lang == "")
{
	$lang = $ilias->ini->readVariable("language","default");
}

//instantiate language
$lng = new Language($lang);

$languages = $lng->getInstalledLanguages();

foreach ($languages as $lang_key)
{
	$tpl->setCurrentBlock("languages");
	$tpl->setVariable("LANG_ID", $lang_key);
	$tpl->setVariable("LANG_DESC", $lng->txt("lang_".$lang_key));
	$tpl->setVariable("LANG_IMG", "./lang/".$lang_key.".gif");
	$tpl->parseCurrentBlock();
}

$tpl->setVariable("ILIAS_RELEASE", $ilias->getSetting("ilias_version"));
$tpl->setVariable("TXT_ILIAS_LOGIN", $lng->txt("login_to_ilias"));
$tpl->setVariable("FORMACTION", "login.php?lang=".$lang);
$tpl->setVariable("TXT_USERNAME", $lng->txt("username"));
$tpl->setVariable("TXT_PASSWORD", $lng->txt("password"));
$tpl->setVariable("TXT_SUBMIT", $lng->txt("submit"));
$tpl->setVariable("TXT_CHOOSE_LANGUAGE", $lng->txt("choose_language"));

if (!empty($ilias->auth->status))
{
	switch($ilias->auth->status)
	{
		case AUTH_EXPIRED:
			$tpl->setVariable(TXT_MSG_LOGIN_FAILED, $lng->txt("err_session_expired"));
			break;
		case AUTH_IDLED:
			$tpl->setVariable(TXT_MSG_LOGIN_FAILED, $lng->txt("err_idled"));
			break;
		case AUTH_WRONG_LOGIN:
		default:
			$tpl->setVariable(TXT_MSG_LOGIN_FAILED, $lng->txt("err_wrong_login"));
			break;
	}
}

$tpl->setCurrentBlock("content");
$tpl->setVariable("PHP_SELF", $_SERVER['PHP_SELF']);
$tpl->setVariable("USERNAME", $_GET["username"]);
$tpl->parseCurrentBlock();

$tpl->show();
?>