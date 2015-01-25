<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
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
        return _val($array, "_collect");
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

function _const($val) {
    static $fn = null;
    if ($fn === null) {
        $fn = _fn(function($v) use ($val) {
            return $val;
        });
    }
    return $fn;
}

function _intval() {
    static $fn = null;
    if ($fn === null) {
        $fn = _fn(function($val) {
            return intval($val);
        });
    }
    return $fn;
}

function _id() {
    static $fn = null;
    if ($fn === null) {
        $fn = _fn(function($val) {
            return $val;
        });
    }
    return $fn;
}
