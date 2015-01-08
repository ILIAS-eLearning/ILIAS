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
    return _value(new Stop());
}

// TODO: This could be refactored for sure!

function appendRecursive($array, $value) {
    if ($value instanceof Stop) {
        return _value($array, null);
    }
    else {
        $array[] = $value;
        return _function(1, function($a) use ($array) {
            return appendRecursive($array, $a);
        });
    }
}

function _collect() {
    static $fn = null;
    if ($fn === null) {
        $fn = _function(1, function($a) {
            return appendRecursive(array(), $a);
        });
    } 
    return $fn;
}

function _const($val) {
    static $fn = null;
    if ($fn === null) {
        $fn = _function(1, function($v) use ($val) {
            return $val;
        });
    }
    return $fn;
}

function _intval() {
    static $fn = null;
    if ($fn === null) {
        $fn = _function(1, function($val) {
            return intval($val);
        });
    }
    return $fn;
}


