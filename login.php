<?php
/**
 * login script for ilias
 * @author Sascha Hofmann <shofmann@databay.de>
 * @author Peter Gabriel <pgabriel@databay.de>
 * @version $Id$
 * @package ilias-layout
 */
include_once "include/ilias_header.inc";

//check for auth
if ($ilias->auth->getAuth())
{
	header("location: start.php");
}

//instantiate login template
$tplContent = new Template("login.html", true, true);

//language handling
if ($lang == "")
	$lang = $ilias->ini->readVariable("language","default");
//instantiate language
$lng = new Language($lang);
$langs = $lng->getAllLanguages();

foreach ($langs as $row)
{
	$tplContent->setCurrentBlock("languages");
	$tplContent->setVariable("LANG_ID", $row["id"]);
	$tplContent->setVariable("LANG_DESC", $row["name"]);
	$tplContent->setVariable("LANG_IMG", "./lang/".$row["id"].".gif");
	$tplContent->parseCurrentBlock();
}

$tplContent->setVariable("TXT_ILIAS_LOGIN", $lng->txt("login_to_ilias"));
$tplContent->setVariable("TXT_USERNAME", $lng->txt("username"));
$tplContent->setVariable("TXT_PASSWORD", $lng->txt("password"));
$tplContent->setVariable("TXT_SUBMIT", $lng->txt("submit"));
$tplContent->setVariable("TXT_CHOOSE_LANGUAGES", $lng->txt("choose_language_s"));

if (!empty($ilias->auth->status) && $ilias->auth->status == AUTH_EXPIRED)
{
	$tplContent->setVariable(TXT_MSG_LOGIN_FAILED,"Your session expired. Please login again!");
}
else if (!empty($ilias->auth->status) && $ilias->auth->status == AUTH_IDLED)
{
	$tplContent->setVariable(TXT_MSG_LOGIN_FAILED,"You have been idle for too long. Please login again!");
} 
else if (!empty ($ilias->auth->status) && $ilias->auth->status == AUTH_WRONG_LOGIN)
{
	$tplContent->setVariable(TXT_MSG_LOGIN_FAILED,"Wrong login data!");
}

$tplContent->setVariable(PHP_SELF,$_SERVER['PHP_SELF']);
$tplContent->setVariable(USERNAME,$username);

include_once "include/ilias_footer.inc";

?>