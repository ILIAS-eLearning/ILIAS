<?php
require_once "include/ilias_header.inc";

$ilias->auth->logout();
session_destroy();

header("Location: index.php");
exit;
?>