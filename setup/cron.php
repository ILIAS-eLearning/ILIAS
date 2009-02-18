<?php
chdir(dirname(__FILE__));

include_once "./setup/classes/class.ilCronClients.php";

$cron_obj =& ilCronClients::_getInstance();
?>