<?php 
include('config/conf.dateplaner.php');
session_destroy();
header("Location: ".$actualIliasDir."/error.php"); // Umleitung des Browsers

;?>
