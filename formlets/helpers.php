<?php

/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * Helpers 
 */

function defaultTo($arg, $default) {
    if ($arg === null) {
        return $default;
    }
    return $arg;
}

function keysAndValuesToHTMLAttributes($attributes) {
    $str = "";
    foreach ($attributes as $key => $value) {
        guardIsString($key);
        if ($value !== null)
            guardIsString($value);
        $str .= " ".$key.($value !== null ? "=\"$value\"" : "");
    } 
    return $str;
}

function flatten($val) {
    $arr = array();
    _flatten($arr, $val);
    return $arr;
}

function _flatten(&$arr, $val) {
    if(is_array($val)) {
        foreach($val as $v)
            _flatten($arr, $v);
    }
    else {
        $arr[] = $val;
    }
}

function id($val) {
    return $val;
}

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

?>
