<?php
include_once "include/ilias_header.inc";


if ($ilias->auth->getAuth())
{
	header("location: start.php");
}

// braucht man ned
//$server = &$ilias->auth->_importGlobalVariable("server");

$tplContent = new Template("login.html",true,true);

if (!empty($ilias->auth->status) && $ilias->auth->status == AUTH_EXPIRED) {
	$tplContent->setVariable(LOGIN_FAILED_MSG,"Your session expired. Please login again!");
} else if (!empty($ilias->auth->status) && $ilias->auth->status == AUTH_IDLED) {
	$tplContent->setVariable(LOGIN_FAILED_MSG,"You have been idle for too long. Please login again!");
} else if (!empty ($ilias->auth->status) && $ilias->auth->status == AUTH_WRONG_LOGIN) {
	$tplContent->setVariable(LOGIN_FAILED_MSG,"Wrong login data!");
}

$tplContent->setVariable(PHP_SELF,$_SERVER['PHP_SELF']);
$tplContent->setVariable(USERNAME,$username);

include_once "include/ilias_footer.inc";
?>