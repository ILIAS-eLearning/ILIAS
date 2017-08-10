package com.yahoo.astra.utils
{		
	/**
	 * A collection of utility functions for the manipulation and inspection of date and time values.
	 * 
	 * @see Date
	 * 
	 * @author Josh Tynjala, Allen Rabinovich
	 */
	public class DateUtil
	{
		/**
		 * The names of months in English. The index in the array corresponds to the value of the month
		 * in a date object.
		 */
		public static var months:Array = [
			"January", "February", "March", "April", "May", "June", "July",
			"August", "September", "October", "November", "December"];
			
		/**
		 * The number of days in January.
		 */
		public static const DAYS_IN_JANUARY:int = 31
			
		/**
		 * The number of days in February on a standard year.
		 */
		public static const DAYS_IN_FEBRUARY:int = 28;
			
		/**
		 * The number of days in February on a leap year.
		 */
		public static const DAYS_IN_FEBRUARY_LEAP_YEAR:int = 29;
			
		/**
		 * The number of days in March.
		 */
		public static const DAYS_IN_MARCH:int = 31;
			
		/**
		 * The number of days in April.
		 */
		public static const DAYS_IN_APRIL:int = 30;
			
		/**
		 * The number of days in May.
		 */
		public static const DAYS_IN_MAY:int = 31;
			
		/**
		 * The number of days in June.
		 */
		public static const DAYS_IN_JUNE:int = 30;
			
		/**
		 * The number of days in July.
		 */
		public static const DAYS_IN_JULY:int = 31;
			
		/**
		 * The number of days in August.
		 */
		public static const DAYS_IN_AUGUST:int = 31;
			
		/**
		 * The number of days in September.
		 */
		public static const DAYS_IN_SEPTEMBER:int = 30;
			
		/**
		 * The number of days in October.
		 */
		public static const DAYS_IN_OCTOBER:int = 31;
			
		/**
		 * The number of days in November.
		 */
		public static const DAYS_IN_NOVEMBER:int = 30;
			
		/**
		 * The number of days in December.
		 */
		public static const DAYS_IN_DECEMBER:int = 31;
		
		/**
		 * The number of days in a standard year.
		 */
		public static const DAYS_IN_YEAR:int = 365;
		
		/**
		 * The number of days in a leap year.
		 */
		public static const DAYS_IN_LEAP_YEAR:int = 366;
		
		/**
		 * The number of days appearing in each month. May be used for easy index lookups.
		 * The stored value for February corresponds to a standard year--not a leap year.
		 */
		public static var daysInMonths:Array = [
			DAYS_IN_JANUARY, DAYS_IN_FEBRUARY, DAYS_IN_MARCH, DAYS_IN_APRIL,
			DAYS_IN_MAY, DAYS_IN_JUNE, DAYS_IN_JULY, DAYS_IN_AUGUST, DAYS_IN_SEPTEMBER,
			DAYS_IN_OCTOBER, DAYS_IN_NOVEMBER, DAYS_IN_DECEMBER];
		
		/**
		 * Determines the number of days between the start value and the end value. The result
		 * may contain a fractional part, so cast it to int if a whole number is desired.
		 * 
		 * @param		start	the starting date of the range
		 * @param		end		the ending date of the range
		 * @return		the number of dats between start and end
		 */
		public static function countDays(start:Date, end:Date):Number
		{
			return Math.abs(end.valueOf() - start.valueOf()) / (1000 * 60 * 60 * 24);
		}
		
		/**
		 * Determines if the input year is a leap year (with 366 days, rather than 365).
		 * 
		 * @param		year	the year value as stored in a Date object.
		 * @return		true if the year input is a leap year
		 */
		public static function isLeapYear(year:int):Boolean
		{
			if(year % 100 == 0) return year % 400 == 0;
			return year % 4 == 0;
		}
		
		/**
		 * Gets the English name of the month specified by index. This is the month value
		 * as stored in a Date object.
		 * 
		 * @param		index	the numeric value of the month
		 * @return		the string name of the month in English
		 */
		public static function getMonthName(index:int):String
		{
			return months[index];
		}
		
		/**
		 * Gets the abbreviated month name specified by index. This is the month value
		 * as stored in a Date object.
		 * 
		 * @param		index	the numeric value of the month
		 * @return		the short string name of the month in English
		 */
		public static function getShortMonthName(index:int):String
		{
			return getMonthName(index).substr(0, 3);
		}
		
		/**
		 * Rounds a Date value up to the nearest value on the specified time unit.
		 * 
		 * @see com.yahoo.astra.utils.TimeUnit
		 */
		public static function roundUp(dateToRound:Date, timeUnit:String = "day"):Date
		{
			dateToRound = new Date(dateToRound.valueOf());
			switch(timeUnit)
			{
				case TimeUnit.YEAR:
					dateToRound.year++;
					dateToRound.month = 0;
					dateToRound.date = 1;
					dateToRound.hours = 0;
					dateToRound.minutes = 0;
					dateToRound.seconds = 0;
					dateToRound.milliseconds = 0;
					break;
				case TimeUnit.MONTH:
					dateToRound.month++;
					dateToRound.date = 1;
					dateToRound.hours = 0;
					dateToRound.minutes = 0;
					dateToRound.seconds = 0;
					dateToRound.milliseconds = 0;
					break;
				case TimeUnit.DAY:
					dateToRound.date++;
					dateToRound.hours = 0;
					dateToRound.minutes = 0;
					dateToRound.seconds = 0;
					dateToRound.milliseconds = 0;
					break;
				case TimeUnit.HOURS:
					dateToRound.hours++;
					dateToRound.minutes = 0;
					dateToRound.seconds = 0;
					dateToRound.milliseconds = 0;
					break;
				case TimeUnit.MINUTES:
					dateToRound.minutes++;
					dateToRound.seconds = 0;
					dateToRound.milliseconds = 0;
					break;
				case TimeUnit.SECONDS:
					dateToRound.seconds++;
					dateToRound.milliseconds = 0;
					break;
				case TimeUnit.MILLISECONDS:
					dateToRound.milliseconds++;
					break;
			}
			return dateToRound;
		}
		
		/**
		 * Rounds a Date value down to the nearest value on the specified time unit.
		 * 
		 * @see com.yahoo.astra.utils.TimeUnit
		 */
		public static function roundDown(dateToRound:Date, timeUnit:String = "day"):Date
		{
			dateToRound = new Date(dateToRound.valueOf());
			switch(timeUnit)
			{
				case TimeUnit.YEAR:
					dateToRound.month = 0;
					dateToRound.date = 1;
					dateToRound.hours = 0;
					dateToRound.minutes = 0;
					dateToRound.seconds = 0;
					dateToRound.milliseconds = 0;
					break;
				case TimeUnit.MONTH:
					dateToRound.date = 1;
					dateToRound.hours = 0;
					dateToRound.minutes = 0;
					dateToRound.seconds = 0;
					dateToRound.milliseconds = 0;
					break;
				case TimeUnit.DAY:
					dateToRound.hours = 0;
					dateToRound.minutes = 0;
					dateToRound.seconds = 0;
					dateToRound.milliseconds = 0;
					break;
				case TimeUnit.HOURS:
					dateToRound.minutes = 0;
					dateToRound.seconds = 0;
					dateToRound.milliseconds = 0;
					break;
				case TimeUnit.MINUTES:
					dateToRound.seconds = 0;
					dateToRound.milliseconds = 0;
					break;
				case TimeUnit.SECONDS:
					dateToRound.milliseconds = 0;
					break;
			}
			return dateToRound;
		}
		
		/**
		 * Converts a time code to UTC.
		 * 
		 * @param timecode	the input timecode
		 * @return			the UTC value
		 */
		public static function timeCodeToUTC(timecode:String):String {
			switch (timecode) {
				case "GMT", "UT", "UTC", "WET":  return "UTC+0000";
				case "CET": return "UTC+0100";
				case "EET": return "UTC+0200";
				case "MSK": return "UTC+0300";
				case "IRT": return "UTC+0330";
				case "SAMT": return "UTC+0400";
				case "YEKT", "TMT", "TJT": return "UTC+0500";
				case "OMST", "NOVT", "LKT": return "UTC+0600";
				case "MMT": return "UTC+0630";
				case "KRAT", "ICT", "WIT", "WAST": return "UTC+0700";
				case "IRKT", "ULAT", "CST", "CIT", "BNT": return "UTC+0800";
				case "YAKT", "JST", "KST", "EIT": return "UTC+0900";
				case "ACST": return "UTC+0930";
				case "VLAT", "SAKT", "GST": return "UTC+1000";
				case "MAGT": return "UTC+1100";
				case "IDLE", "PETT", "NZST": return "UTC+1200";
				case "WAT": return "UTC-0100";
				case "AT": return "UTC-0200";
				case "EBT": return "UTC-0300";
				case "NT": return "UTC-0330";
				case "WBT", "AST": return "UTC-0400";
				case "EST": return "UTC-0500";
				case "CST": return "UTC-0600";
				case "MST": return "UTC-0700";
				case "PST": return "UTC-0800";
				case "YST": return "UTC-0900";
				case "AHST", "CAT", "HST": return "UTC-1000";
				case "NT": return "UTC-1100";
				case "IDLW": return "UTC-1200";
			}
			return "UTC+0000";
		}
		
		/**
		 * Determines the hours value in the range 1 - 12 for the AM/PM time format.
		 * 
		 * @param value		the input Date value
		 * @return			the calculated hours value
		 */
		public static function getHoursIn12HourFormat(value:Date):Number
		{
			var hours:Number = value.getHours();
			if(hours == 0)
			{
				return 12;
			}
			
			if(hours > 0 && hours <= 12)
			{
				return hours;
			}
			
			return hours - 12;
		}
		
		public static function getDateDifferenceByTimeUnit(minDate:Date, maxDate:Date, timeUnit:String):Number
		{
			var dateDifference:Number = 0;
			var maxDateNumber:Number;
			var minDateNumber:Number;
			
			switch(timeUnit)
			{
				case TimeUnit.YEAR:
					maxDateNumber = (maxDate.getFullYear() - minDate.getFullYear());
				break;
				
				case TimeUnit.MONTH:
					dateDifference = (12 - minDate.getMonth()) + (maxDate.getFullYear() - minDate.getFullYear() - 1)*12 + maxDate.getMonth();				
				break;		
				
				case TimeUnit.DAY:
					dateDifference = Math.round(Math.abs(maxDate.valueOf() - minDate.valueOf()) / (1000 * 60 * 60 * 24));
				break;
				
				case TimeUnit.HOURS:
					dateDifference = Math.abs(minDate.valueOf() - maxDate.valueOf()) / (60 * 60 * 1000);
				break;
				
				case TimeUnit.MINUTES:
					dateDifference = Math.abs(minDate.valueOf() - maxDate.valueOf()) / (60 * 1000);
				break;	
				
				case TimeUnit.SECONDS:
					dateDifference = Math.abs(minDate.valueOf() - maxDate.valueOf()) / 1000;
				break;
				
				case TimeUnit.MILLISECONDS:
					dateDifference = Math.abs(minDate.valueOf() - maxDate.valueOf());
				break;
			}
			return dateDifference;
		}		
	}
}
