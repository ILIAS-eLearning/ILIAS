<?php
//
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
// | Authors: Shane Caraveo <Shane@Caraveo.com>   Port to PEAR and more   |
// | Authors: Dietrich Ayala <dietrich@ganx4.com> Original Author         |
// +----------------------------------------------------------------------+
//
// $Id: class.ilBMFType_hexBinary.php 13589 2007-04-03 10:43:01Z jconze $
//
class ilBMFType_hexBinary
{
    function to_bin($value)
    {
        $len = strlen($value);
        return pack('H' . $len, $value);
    }
    function to_hex($value)
    {
        return bin2hex($value);
    }
    function is_hexbin($value)
    {
        # first see if there are any invalid chars
        $l = strlen($value);

        if ($l < 1 || strspn($value, '0123456789ABCDEFabcdef') != $l) return FALSE;

        $bin = ilBMFType_hexBinary::to_bin($value);
        $hex = ilBMFType_hexBinary::to_hex($bin);
        return strcasecmp($value, $hex) == 0;
    }
}

?>