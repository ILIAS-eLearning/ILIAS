<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

use Lechimp\Formlets\Internal\Values as V;

class Lib {
    static function collect() {
        $collector = V::fn_w(function($array, Value $v) use (&$collector) {
            if ( !($v->isError() || $v->isApplicable())
            &&   $v->get() instanceof Stop) {
                // Postprocessing of the collected values.
                // We need to check weather there are errors in the collection
                // to be able to get errors appropriately.
                $errors = array();
                $vals = array_map(function ($v) use (&$errors) {
                    $v = $v->force();
                    if ($v->isError()) {
                        $errors[] = $v;
                        return $v;
                    }
                    if ($v->isApplicable()) {
                        return $v;
                    }  
                    return $v->get();           
                }, $array->get());

                if (count($errors) > 0) {
                    return V::error("Collection contains errors.", "_collect", $errors);
                }
                return V::val($vals, "collect");
            }

            $array = $array->get();
            $array[] = $v->force(); 
            return $collector->apply(V::val($array));
        });
        return $collector->apply(V::val(array()));
    }

    /* Check weather a number is between $l and $r */
    static function inRange($l, $r) {
        return V::fn(function($value) use ($l, $r) {
            return $value >= $l && $value <= $r;
        });
    }

    /* Check weather a number is a multiple of $s */
    static function isMultipleOf($s) {
        return V::fn(function($value) use ($s) {
            return $value % $s === 0;
        });
    }
}

/* Signals that the array is completed. */
class Stop {}

?>
