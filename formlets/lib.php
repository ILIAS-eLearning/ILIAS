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

function appendRecursive($array, $value) {
    if ($value instanceof Stop) {
        return _value($array, null);
    }
    else {
        $array[] = $value;
        return _function(1, "appendRecursive", array($array));
    }
}

function _collect() {
    return _function(1, "appendRecursive", array(array()));
}

function cconst($val, $any) {
    return $val;
}

function _const($val) {
    return _function(1, "cconst", array($val)); 
}


