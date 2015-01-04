<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * Helpers for internal use.
 */

require_once("checking.php");

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

?>
