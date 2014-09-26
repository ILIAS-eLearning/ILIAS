<?php

	exit();

	define('ZONEINFO','/usr/share/zoneinfo');
	define('TZ_CONVERT','tz_convert');
	define('READLINK','readlink');

	chdir('../../..');
	
	include_once('include/inc.header.php');
	include_once('Services/Calendar/classes/class.ilCalendarUtil.php');
	
	foreach(ilCalendarUtil::_getShortTimeZoneList() as $tz_name => $tmp)
	{
		$name_underscore = str_replace('/','_',$tz_name);
		
		if(is_link(ZONEINFO.'/'.$tz_name))
		{
			$name = exec(READLINK.' -f '.ZONEINFO.'/'.$tz_name);
		}
		else
		{
			$name = ZONEINFO.'/'.$tz_name;
		}
		
		exec(TZ_CONVERT.' -o Services/Calendar/zoneinfo/'.$name_underscore.'.tmp'.' '.$name);
		
		$reader = fopen('Services/Calendar/zoneinfo/'.$name_underscore.'.tmp', 'r');
		$writer = fopen('Services/Calendar/zoneinfo/'.$name_underscore.'.ics', 'w');
		
		$counter = 0;
		while($line = fgets($reader))
		{
			if(++$counter < 4)
			{
				continue;
			}
			if($counter == 5)
			{
				fputs($writer, 'TZID='.$tz_name."\n");
			}
			else
			{
				if(substr($line,0,13) === 'END:VCALENDAR')
				{
					break;
				}
				fputs($writer, $line);
			}
		}
		
		fclose($reader);
		fclose($writer);
		unlink('Services/Calendar/zoneinfo/'.$name_underscore.'.tmp');

		#echo $name_underscore.' <br />';
	}
	
	
?>
