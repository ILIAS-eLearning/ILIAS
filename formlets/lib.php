<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 + This program is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Helpers for external use.
 */

require_once("values.php");

class Stop {
}

function stop() {
    static $val = null;
    if ($val === null) {
        $val = _val(new Stop());
    }
    return $val;
}

// TODO: This could be refactored for sure!

function appendRecursive($array, $value) {
    if ( !$value->isError() 
    &&   !$value->isApplicable() 
    &&   $value->get() instanceOf Stop) {
        $errors = array();
        $vals = array_map(function($v) use (&$errors) {
                $forced = $v->force();
                if ($forced->isError()) {
                    $errors[] = $forced;
                    return $forced;
                }
                if ($forced->isApplicable()) {
                    return $forced;
                }
                return $forced->get();
            }
            , $array
            );

        if (count($errors) > 0) {
            return _error("Collection contains errors.", "_collect", $errors);
        }
        return _val($vals, "_collect");
    }
    else {
        $array[] = $value->force();
        return _fn_w(function($a) use ($array) {
            return appendRecursive($array, $a);
        });
    }
}

function _collect() {
    static $fn = null;
    if ($fn === null) {
        $fn = _fn_w(function($a) {
            return appendRecursive(array(), $a);
        });
    } 
    return $fn;
}
