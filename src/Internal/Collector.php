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
use Lechimp\Formlets\Internal\Checking as C;

abstract class Collector {
    /* Expects an array. Tries to collect it's desired input from it and returns
     * it as a Value. Throws if desired content can not be found. A missing 
     * input is not to be considered as a regular error but rather points at 
     * some problem in the implementation or tampering with the input, thus we 
     * throw.
     */
    abstract public function collect($inp);
    /* Check whether Collector collects something. */
    abstract public function isNullaryCollector();

    /* Map a function over the collected value. */
    final public function map(FunctionValue $transformation) {
        return $this->wrap(V::fn(function($collector, $inp) use ($transformation) {
            $res = $collector->collect($inp)->force();
            if ($res->isError()) {
                return $res;
            }

            $res2 = $transformation->apply($res)->force();
            // If mapping was successfull, the underlying value should
            // be considered the origin of the produced value.
            if (!$res2->isError() && !$res2->isApplicable()) {
                return V::val($res2->get(), $res->origin()); 
            }
            return $res2;
        }));
    }

    /* Wrap a function around the collect function. */
    final public function wrap(FunctionValue $wrapper) {
        return new WrappedCollector($this, $wrapper);
    }

    /* Only return value when it matches predicate, return error value
       containing error message instead. */
    final public function satisfies(FunctionValue $predicate, $error) {
        C::guardIsString($error);
        C::guardHasArity($predicate, 1);
        return $this->map(V::fn_w(function($value) use ($predicate, $error) {
            if (!$predicate->apply($value)->get()) {
                return _error($error, $value->origin());
            }
            return $value;
        }));
    }

    static function combineCollectors(Collector $l, Collector $r) {
        $l_empty = $l->isNullaryCollector();
        $r_empty = $r->isNullaryCollector();
        if ($l_empty && $r_empty) 
            return new NullaryCollector();
        elseif ($r_empty)
            return $l;
        elseif ($l_empty)
            return $r;
        else
            return new ApplyCollector($l, $r);
    }
}

?>
