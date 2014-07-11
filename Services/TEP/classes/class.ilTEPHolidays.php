<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TEP holiday helper
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 */
class ilTEPHolidays
{			
	/**
	 * Is given date a bank holiday?
	 * 
	 * @param string $a_country
	 * @param int $a_year
	 * @param int $a_month
	 * @param int $a_day
	 * @param bool $a_include_sundays
	 * @return bool	 
	 */
	public static function isBankHoliday($a_country, $a_year, $a_month, $a_day, $a_include_sundays = true)
	{		
		if(strtolower($a_country) != "de")
		{
			return;
		}
		
		$res = false;
		
		$today = mktime(0, 0, 1, $a_month, $a_day, $a_year);
		
		$day = date("m-d", $today);					
		if ($day == "01-01" || // Neujahr
			$day == "05-01" || // Tag der Arbeit
			$day == "10-03" || // Tag der deutschen Einheit
			$day == "12-25" || // Weihnachten 1
			$day == "12-26") // Weihnachten 2
		{
			$res = true;
		}
		
		if(!$res)
		{					
			// easter_date() is valid for 1970-2037 (32bit timestamp)	
			$easter = new DateTime();
			$easter->setTimestamp(easter_date($a_year));
			
			$today = new DateTime();
			$today->setTimestamp(time());
			
			$diff = $easter->diff($today);
			$diff_days = $diff->days * ($diff->invert ? -1 : 1);

			if($diff_days === -2 || // Karfreitag
				$diff_days == 0 || // Ostersonntag
				$diff_days == 1 || // Ostermontag
				$diff_days == 39 || // Christi Himmelfahrt
				$diff_days == 49 || // Pfingstsonntag
				$diff_days == 50) // Pfingstmontag
			{
				$res = true;
			}
		}
				
		if($res && 
			!(bool)$a_include_sundays && 
			date("N", $today) == 7)
		{
			return false;
		}

		return $res;	
	}
	
	/**
	 * Is given date a weekend?
	 * 
	 * @param int $a_year
	 * @param int $a_month
	 * @param int $a_day
	 * @return bool
	 */
	public static function isWeekend($a_year, $a_month, $a_day)
	{
		$tstamp = mktime(0, 0, 1, $a_month, $a_day, $a_year);
		return (date("N", $tstamp) >= 6);
	}			
}
