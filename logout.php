<?php
require_once "include/inc.header.php";

$ilias->auth->logout();
session_destroy();

header("Location: index.php");
exit;
?>