<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Shane Caraveo <Shane@Caraveo.com>   Port to PEAR and more   |
// | Authors: Dietrich Ayala <dietrich@ganx4.com> Original Author         |
// +----------------------------------------------------------------------+
//
// $Id$
//
class ilBMFType_dateTime
{
    var $ereg_iso8601 = '(-?[0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]*)?(Z|[+\-][0-9]{4}|[+\-][0-9]{2}:[0-9]{2})?';
        #"(-?[0-9]{4})-".  // centuries & years CCYY-
        #"([0-9]{2})-".  // months MM-
        #"([0-9]{2})".    // days DD
        #"T".                    // separator T
        #"([0-9]{2}):".  // hours hh:
        #"([0-9]{2}):".  // minutes mm:
        #"([0-9]{2})(\.[0-9]*)?". // seconds ss.ss...
        #"(Z|[+\-][0-9]{4}|[+\-][0-9]{2}:[0-9]{2})?"; // Z to indicate UTC, -/+HH:MM:SS.SS... for local tz's
        # if no 8th reg (Z) assumes UTC
    var $timestamp = -1;
    
    function ilBMFType_dateTime($date = -1)
    {
        if ($date == -1) $date = time();

        if (gettype($date) == 'integer') {
            $this->timestamp = $date;
        } else {
            $this->timestamp = $this->toUnixtime($date);
        }
    }
    
    function toSOAP($date = NULL)
    {
        return $this->toUTC($date);
    }
    
    function toString($timestamp = 0)
    {
        if (!$timestamp) $timestamp = $this->timestamp;
        if ($timestamp < 0) return 0;

        return date('Y-m-d\TH:i:sO',$timestamp);
    }
    
    function _split($datestr)
    {
        if (!$datestr)
            $datestr = $this->toString();
        else if (gettype($datestr) == 'integer')
            $datestr = $this->toString($datestr);
            
        if (ereg($this->ereg_iso8601,$datestr,$regs)) {
            if ($regs[8] != '' && $regs[8] != 'Z') {
                $op = substr($regs[8],0,1);
                $h = substr($regs[8],1,2);
                if (strstr($regs[8],':')) {
                    $m = substr($regs[8],4,2);
                } else {
                    $m = substr($regs[8],3,2);
                }
                if ($op == '+') {
                    $regs[4] = $regs[4] - $h;
                    if ($regs[4] < 0) $regs[4] += 24;
                    $regs[5] = $regs[5] - $m;
                    if ($regs[5] < 0) $regs[5] += 60;
                } else {
                    $regs[4] = $regs[4] + $h;
                    if ($regs[4] > 23) $regs[4] -= 24;
                    $regs[5] = $regs[5] + $m;
                    if ($regs[5] > 59) $regs[5] -= 60;
                }
            }
            return $regs;
        }
        return FALSE;
    }
    
    function toUTC($datestr = NULL)
    {
        $regs = $this->_split($datestr);

        if ($regs) {
            return sprintf('%04d-%02d-%02dT%02d:%02d:%02dZ',$regs[1],$regs[2],$regs[3],$regs[4],$regs[5],$regs[6]);
        }

        return '';
    }

    function toUnixtime($datestr = NULL)
    {
        $regs = $this->_split($datestr);
        if ($regs) {
            return strtotime("$regs[1]-$regs[2]-$regs[3] $regs[4]:$regs[5]:$regs[6]Z");
        }
        return -1;
    }

    function compare($date1, $date2 = NULL)
    {
        if ($date2 === null) {
            $date2 = $date1;
            $date1 = $this->timestamp;
        }
        if (!is_numeric($date1))
            $date1 = $this->toUnixtime($date1);
        if (!is_numeric($date2))
            $date2 = $this->toUnixtime($date2);
        if ($date1 != -1 && $date2 != -1) return $date1 - $date2;
        return -1;
    }
    
    function _test($orig = '2001-04-25T09:31:41-0700')
    {
        $utc = $this->toUTC($orig);
        $ts1 = $this->toUnixtime($orig);
        $ts2 = $this->toUnixtime($utc);
        $b1 = $this->toString($ts1);
        $b2 = $this->toString($ts2);
        print "orig: $orig\n";
        print "orig toUTC: $utc\n";
        print "orig ts: $ts1\n";
        print "utc ts: $ts2\n";
        print "orig ts back: $b1\n";
        print "utc ts back: $b2\n";
        if ($b1 != $orig || $b2 != $orig) {
            echo "Error in iso8601 conversions\n";
        } else {
            echo "dateTime OK\n";
        }
    }
}

# these should all match
#$d = new ilBMFType_dateTime();
#echo $d->compare("2001-04-25T20:31:41Z","2001-04-25T20:31:41Z")."\n";
#echo $d->compare("2001-04-25T20:31:41Z","2001-04-25T12:31:41-0800")."\n";
#echo $d->compare("2001-04-25T20:31:41Z","2001-04-25T12:31:41-08:00")."\n";

?>