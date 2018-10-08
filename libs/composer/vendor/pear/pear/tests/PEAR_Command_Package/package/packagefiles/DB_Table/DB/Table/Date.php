<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Baba Buehler <baba@babaz.com>                               |
// |          Pierre-Alain Joye <pajoye@php.net>                          |
// |                                                                      |
// | Stripped down to two essential methods specially for DB_Table        |
// | under PHP 5.1 by Paul M. Jones <pmjones@php.net>                     |
// +----------------------------------------------------------------------+
//
// $Id: Date.php,v 1.2 2007/03/23 10:41:13 morse Exp $

/**
 * Generic date handling class for DB_Table.
 *
 * @category Database
 * @package DB_Table
 * @author Baba Buehler <baba@babaz.com>
 * @access public
 */
class DB_Table_Date {
	
    /**
     * the year
     * @var int
     */
    var $year;
    /**
     * the month
     * @var int
     */
    var $month;
    /**
     * the day
     * @var int
     */
    var $day;
    /**
     * the hour
     * @var int
     */
    var $hour;
    /**
     * the minute
     * @var int
     */
    var $minute;
    /**
     * the second
     * @var int
     */
    var $second;
    /**
     * the parts of a second
     * @var float
     */
    var $partsecond;
    
    /**
     * Constructor
     *
     * Creates a new DB_Table_Date Object. The date should be near to
     * ISO 8601 format.
     *
     * @access public
     * @param string $date A date in ISO 8601 format.
     */
    function DB_Table_Date($date)
    {
		// This regex is very loose and accepts almost any butchered
		// format you could throw at it.  e.g. 2003-10-07 19:45:15 and
		// 2003-10071945:15 are the same thing in the eyes of this
		// regex, even though the latter is not a valid ISO 8601 date.
		preg_match('/^(\d{4})-?(\d{2})-?(\d{2})([T\s]?(\d{2}):?(\d{2}):?(\d{2})(\.\d+)?(Z|[\+\-]\d{2}:?\d{2})?)?$/i', $date, $regs);
		$this->year       = $regs[1];
		$this->month      = $regs[2];
		$this->day        = $regs[3];
		$this->hour       = isset($regs[5])?$regs[5]:0;
		$this->minute     = isset($regs[6])?$regs[6]:0;
		$this->second     = isset($regs[7])?$regs[7]:0;
		$this->partsecond = isset($regs[8])?(float)$regs[8]:(float)0;

		// if an offset is defined, convert time to UTC
		// Date currently can't set a timezone only by offset,
		// so it has to store it as UTC
		if (isset($regs[9])) {
			$this->toUTCbyOffset($regs[9]);
		}
    }
    
    
    /**
     *  Date pretty printing, similar to strftime()
     *
     *  Formats the date in the given format, much like
     *  strftime().  Most strftime() options are supported.<br><br>
     *
     *  formatting options:<br><br>
     *
     *  <code>%Y  </code>  year as decimal including century (range 0000 to 9999) <br>
     *  <code>%m  </code>  month as decimal number (range 01 to 12) <br>
     *  <code>%d  </code>  day of month (range 00 to 31) <br>
     *  <code>%H  </code>  hour as decimal number (00 to 23) <br>
     *  <code>%M  </code>  minute as a decimal number (00 to 59) <br>
     *  <code>%S  </code>  seconds as a decimal number (00 to 59) <br>
     *  <code>%%  </code>  literal '%' <br>
     * <br>
     *
     * @access public
     * @param string format the format string for returned date/time
     * @return string date/time in given format
     */
    function format($format)
    {
        $output = "";

        for($strpos = 0; $strpos < strlen($format); $strpos++) {
            $char = substr($format,$strpos,1);
            if ($char == "%") {
                $nextchar = substr($format,$strpos + 1,1);
                switch ($nextchar) {
                case "Y":
                    $output .= $this->year;
                    break;
                case "m":
                    $output .= sprintf("%02d",$this->month);
                    break;
                case "d":
                    $output .= sprintf("%02d",$this->day);
                    break;
                case "H":
                    $output .= sprintf("%02d", $this->hour);
                    break;
                case "M":
                    $output .= sprintf("%02d",$this->minute);
                    break;
                case "S":
                    $output .= sprintf("%02d", $this->second);
                    break;
                default:
                    $output .= $char.$nextchar;
                }
                $strpos++;
            } else {
                $output .= $char;
            }
        }
        return $output;

    }
}

?>
