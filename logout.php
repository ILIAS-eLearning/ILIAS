<?php
include_once "include/ilias_header.inc";


$ilias->auth->logout();
session_destroy();

header("Location: index.php");
?>
