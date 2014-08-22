<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
 * Date form handling helper
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesAdvancedMetaData
 */
class ilADTDateSearchUtil
{
	const MODE_DATE = 1;
	const MODE_DATETIME = 2;
	
	/**
	 * Import select post data
	 * 
	 * @param int $a_mode
	 * @param array $a_post
	 * @return int timestamp
	 */
	public static function handleSelectInputPost($a_mode, $a_post)
	{
		if(!is_array($a_post))
		{
			return;
		}
		
		if($a_mode == self::MODE_DATE)
		{
			return mktime(12, 0, 0,			
				$a_post["date"]["m"], 
				$a_post["date"]["d"], 
				$a_post["date"]["y"]);
		}
		else
		{
			return mktime(
				$a_post["time"]["h"], 
				$a_post["time"]["m"], 
				1, 
				$a_post["date"]["m"], 
				$a_post["date"]["d"], 
				$a_post["date"]["y"]);
		}
	}
	
	/**
	 * Import text input post data
	 * 
	 * @param int $a_mode
	 * @param array $a_post
	 * @return int timestamp
	 */
	public static function handleTextInputPost($a_mode, $a_post)
	{
		global $ilUser;
		
		// see ilDateTimeInputGUI::checkInput()
	
		$a_post["date"] = ilUtil::stripSlashes($a_post["date"]);
		
		if($a_post["date"])
		{
			switch($ilUser->getDateFormat())
			{
				case ilCalendarSettings::DATE_FORMAT_DMY:
					$date = explode(".", $a_post["date"]);
					$dt['mday'] = (int)$date[0];
					$dt['mon'] = (int)$date[1];
					$dt['year'] = (int)$date[2];
					break;

				case ilCalendarSettings::DATE_FORMAT_YMD:
					$date = explode("-", $a_post["date"]);
					$dt['mday'] = (int)$date[2];
					$dt['mon'] = (int)$date[1];
					$dt['year'] = (int)$date[0];
					break;

				case ilCalendarSettings::DATE_FORMAT_MDY:
					$date = explode("/", $a_post["date"]);
					$dt['mday'] = (int)$date[1];
					$dt['mon'] = (int)$date[0];
					$dt['year'] = (int)$date[2];
					break;
			}
			
			if($a_mode == self::MODE_DATE)
			{
				return mktime(12, 0, 0, $dt["mon"], $dt["mday"], $dt["year"]);
			}
			
			
			$a_post["time"] = ilUtil::stripSlashes($a_post["time"]);
			
			if($a_post["time"])
			{
				if($ilUser->getTimeFormat() == ilCalendarSettings::TIME_FORMAT_12)
				{
					$seconds = "";				
					if(preg_match("/([0-9]{1,2})\s*:\s*([0-9]{1,2})\s*".$seconds."(am|pm)/", trim(strtolower($a_post["time"])), $matches))
					{
						$dt['hours'] = (int)$matches[1];
						$dt['minutes'] = (int)$matches[2];
						if($seconds)
						{
							$dt['seconds'] = (int)$time[2];
							$ampm = $matches[4];
						}
						else
						{
							$dt['seconds'] = 0;
							$ampm = $matches[3];
						}
						if($dt['hours'] == 12)
						{
							if($ampm == "am")
							{
								$dt['hours'] = 0;
							}
						}
						else if($ampm == "pm")
						{
							$dt['hours'] += 12;
						}
					}
				}
				else
				{
					$time = explode(":", $a_post["time"]);
					$dt['hours'] = (int)$time[0];
					$dt['minutes'] = (int)$time[1];
					$dt['seconds'] = (int)$time[2];
				}						
			}
			
			return mktime($dt["hours"], $dt["minutes"], $dt["seconds"], $dt["mon"], $dt["mday"], $dt["year"]);
		}		
	}
	
	
}
