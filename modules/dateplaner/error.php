<?php 
include('config/conf.interface.php');
session_destroy();
header("Location: ".$actualIliasDir."/error.php"); // Umleitung des Browsers

;?>
