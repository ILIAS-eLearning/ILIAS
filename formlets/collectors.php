<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * Base class and primitives for collectors.
 */

require_once("checking.php");
require_once("values.php");

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
        return $this->wrap(_fn(function($collector, $inp) use ($transformation) {
            $res = $collector->collect($inp);
            if ($res->isError()) {
                return $res;
            }

            $res2 = $transformation->apply($res);
            if (!$res2->isError() && !$res2->isApplicable()) {
                // rewrap ordinary values to keep origin.
                $res2 = _val($res2->get(), $res->origin());
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
        guardIsString($error);
        guardHasArity($predicate, 1);
        return $this->wrap(_fn(function($collector, $inp) use ($predicate, $error) {
            $res = $collector->collect($inp);
            if ($res->isError()) {
                return $res;
            }
            if (!$predicate->apply($res)->get()) {
                return _error($error, $res);
            }
            return $res;
        }));
    }
}

class MissingInputError extends Exception {
    private $_name; //string
    public function __construct($name) {
        $this->_name = $name;
        parent::__construct("Missing input $name.");
    }
}

/* A collector that collects nothing and will be dropped by apply collectors. */
final class NullaryCollector extends Collector {
    public function collect($inp) {
        die("NullaryCollector::collect: This should never be called.");
    }
    public function isNullaryCollector() {
        return true;
    }
}

/* A collector that always returns a constant value. */
final class ConstCollector extends Collector {
    private $_value; // Value

    public function __construct(Value $value) {
        $this->_value = $value;
    }

    public function collect($inp) {
        return $this->_value;
    }

    public function isNullaryCollector() {
        return false;
    }
}

/* A collector that applies the input from its left collector to the input
 * from its right collector.
 */
final class ApplyCollector extends Collector {
    private $_l;
    private $_r;

    public function __construct(Collector $left, Collector $right) {
        $this->_l = $left;
        $this->_r = $right;
    }

    public function collect($inp) {
        $l = $this->_l->collect($inp);
        $r = $this->_r->collect($inp);
        return $l->apply($r);
    }

    public function isNullaryCollector() {
        return false;
    }
}


/* A collector where a wrapper is around an underlying collect. */
class WrappedCollector extends Collector {
    private $_collector; // Collector
    private $_wrapper; // FunctionValue

    public function __construct(Collector $collector, FunctionValue $wrapper) {
        guardHasArity($wrapper, 2);
        if ($collector->isNullaryCollector()) {
            throw new Exception("It makes no sense to wrap around a nullary collector.");
        }
        $this->_collector = $collector;
        $this->_wrapper = $wrapper;
    }

    public function collect($inp) {
        return $this->_wrapper
                    ->apply(_val($this->_collector))
                    ->apply(_val($inp));
    }

    public function isNullaryCollector() {
        return false;
    }
}

/* A collector that collects an input by name. */
final class AnyCollector extends Collector {
    private $_name; // string

    protected function name() {
        return $this->_name;
    }
    
    public function __construct($name) {
        guardIsString($name);
        $this->_name = $name;
    }

    public function collect($inp) {
        $name = $this->name();
        if (!array_key_exists($name, $inp)) {
            throw new MissingInputError($this->name());
        }
        return _val($inp[$name], $name);
    }

    public function isNullaryCollector() {
        return false;
    }
}

function combineCollectors(Collector $l, Collector $r) {
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

?>
