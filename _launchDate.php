<?php
	//hh,mm,ss,month, day, year
    $targetDate =mktime(10, 0, 0, 9, 8, 2014); //10:00:00 08.09.2014

    $now  = time();
    $delta = $targetDate - $now;
?>
