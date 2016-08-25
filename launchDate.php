<?php
$iso_date_time = parse_ini_file("ilias.ini.php",true)["launch_data"]["launch_datetime"];
$time_zone = parse_ini_file("ilias.ini.php",true)["server"]["timezone"];
$time_zone = new DateTimeZone($time_zone);
$date_time = new DateTime($iso_date_time,$time_zone);
$targetDate = $date_time->getTimestamp();
$now  = time();
$delta = $targetDate - $now;