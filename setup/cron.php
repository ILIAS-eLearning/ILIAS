<?php
chdir(dirname(__FILE__));

include_once "./classes/class.ilCronClients.php";

$cron_obj =& ilCronClients::_getInstance();
?>