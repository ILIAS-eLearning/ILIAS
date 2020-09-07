<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * debugging functions
 *
 * @author Björn Heyser <bheyser@databay.de>
 * @author Sascha Hofmann <shofmann@databay.de>
 * @version $Id$
 *
 * @package ilias-develop
 */

/**
 * shortcut for var_dump with enhanced debug information
 *
 * @author Björn Heyser <bheyser@databay.de>
 * @author Sascha Hofmann <shofmann@databay.de>
 * @access	public
 * @param	mixed	any number of parameters
 */
function vd()
{
    list($file, $func) = getPhpSourceCodePositionInfo(1);
    
    $numargs = func_num_args();

    if ($numargs == 0) {
        return false;
    }
    
    $arg_list = func_get_args();
    $num = 1;

    include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
    if (ilAuthFactory::getContext() == ilAuthFactory::CONTEXT_CRON) {
        $cli = true;
    } else {
        $cli = false;
    }
    
    foreach ($arg_list as $arg) {
        if (!$cli) {
            $printbefore = "<pre style=\"text-align:left;\">";
            $printbefore .= "$file - $func - variable $num:<br/>";
            $printafter = "</pre><br/>";
        } else {
            $printbefore = "\n\n_________________________________________________" .
                            "_________________________________________________\n";
            $printbefore .= "$file - $func - variable $num:\n\n";
            $printafter = "_________________________________________________" .
                            "_________________________________________________\n\n";
        }

        echo $printbefore;
        var_dump($arg);
        echo $printafter;
        $num++;
    }
    
    // BH: php 5.3 seems to not flushing the output consequently so following redirects are still performed
    // and the output of vd() would be lost in nirvana if we not flush the output manualy
    flush();
    ob_flush();
}

/**
 * shortcut for print_r
 *
 * @author Björn Heyser <bheyser@databay.de>
 * @access	public
 * @param	mixed	any number of parameters
 * @param	string	name of variable (optional)
 */
function pr($var, $name = '')
{
    if ($name != '') {
        $name .= ' = ';
    }
    $print = $name . print_r($var, true);
    
    if (ilAuthFactory::getContext() == ilAuthFactory::CONTEXT_CRON) {
        $hr = "\n---------------------------------------------------------------\n";
        echo $hr . $print . $hr;
    } else {
        echo '<pre>' . $print . '</pre>';
    }
    
    // BH: php 5.3 seems to not flushing the output consequently so following redirects are still performed
    // and the output of vd() would be lost in nirvana if we not flush the output manualy
    flush();
    ob_flush();
}

/**
 * prints an information about the function that called
 * the function that invoked this function
 * (the optional backjump param conrols the level in backtrace)
 *
 * @author Björn Heyser <bheyser@databay.de>
 * @access	public
 * @param integer $backjumps
 */
function cf($backjumps = 1)
{
    list($fileC, $funcC) = getPhpSourceCodePositionInfo(($backjumps - 1) + 1);
    list($fileF, $funcF) = getPhpSourceCodePositionInfo(($backjumps) + 1);

    echo "<pre style=\"text-align:left;\">$fileC - $funcC\nIS CALLED FROM: $fileF - $funcF</pre>";

    // BH: php 5.3 seems to not flushing the output consequently so following redirects are still performed
    // and the output of vd() would be lost in nirvana if we not flush the output manualy
    flush();
    ob_flush();
}

/**
 * returns an array containing function information from backtrace
 * (the optional backjump param conrols the level in backtrace)
 *
 * @author Björn Heyser <bheyser@databay.de>
 * @access	public
 * @param integer $backjumps
 */
function getPhpSourceCodePositionInfo($backjumps = 0)
{
    $i = $backjumps;
    $j = $backjumps + 1;

    $e = new Exception();
    $trace = $e->getTrace();

    $file = basename($trace[$i]['file']) . ':' . $trace[$i]['line'];

    $func = '';

    if (isset($trace[$j]['class']) && strlen($trace[$j]['class'])) {
        $func = $trace[$j]['class'];

        if (isset($trace[$j]['class']) && strlen($trace[$j]['class'])) {
            $func .= $trace[$j]['type'];
        } else {
            $func .= '::';
        }
    }

    $func .= $trace[$j]['function'] . '()';

    return array($file, $func);
}
