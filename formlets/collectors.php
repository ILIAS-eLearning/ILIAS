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
        return new MappedCollector($this, $transformation);    
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

/* A collector that does a predicate check on the input from another
 * collector and return an error if the predicate fails.
 */
final class CheckedCollector extends Collector {
    private $_collector; // Collector
    private $_predicate; // FunctionValue
    private $_error; // string

    public function __construct(Collector $collector, FunctionValue $predicate, $error) {
        guardIsString($error);
        guardHasArity($predicate, 1);
        $this->_collector = $collector;
        $this->_predicate = $predicate;
        $this->_error = $error;
    }

    public function collect($inp) {
        $res = $this->_collector->collect($inp);
        if ($res->isError()) {
            return $res;
        }

        // TODO: Maybe check for PlainValue on result before
        // doing this?
        if ($this->_predicate->apply($res)->get()) {
            return $res;
        }
        else {
            return _error($this->_error, $res);
        }
    }

    public function isNullaryCollector() {
        return false;
    }
}

/* A collector where the input is mapped by a function */
final class MappedCollector extends Collector {
    private $_collector; // Collector
    private $_transformation; // FunctionValue
    
    public function __construct(Collector $collector, FunctionValue $transformation) {
        guardHasArity($transformation, 1);
        if ($collector->isNullaryCollector()) {
            throw new TypeError("non nullary collector", typeName($collector));
        }
        $this->_collector = $collector;
        $this->_transformation = $transformation;
    }

    public function collect($inp) {
        $res = $this->_collector->collect($inp);
        if ($res->isError()) {
            return $res;
        }

        $res2 = $this->_transformation->apply($res);
        if (!$res2->isError() && !$res2->isApplicable()) {
            // rewrap ordinary values to keep origin.
            $res2 = _value($res2->get(), $res->origin());
        }
        return $res2;
    }

    public function isNullaryCollector() {
        return false;
    }
}

/* A collector that has a name. Baseclass for some other collectors. */
abstract class CollectorWithName extends Collector {
    private $_name; // string

    protected function name() {
        return $this->_name;
    }
    
    public function __construct($name) {
        guardIsString($name);
        $this->_name = $name;
    }
}

/* A collector that collects a string from input. */
final class StringCollector extends CollectorWithName {
    public function collect($inp) {
        if (!array_key_exists($this->name(), $inp)) {
            throw new MissingInputError($this->name());
        }
        guardIsString($inp[$this->name()]);
        return _value($inp[$this->name()], $this->name());
    }

    public function isNullaryCollector() {
        return false;
    }
}

/* A collector that returns true, wenn name is present in input. */
final class ExistsCollector extends CollectorWithName {
    public function collect($inp) {
        return _value(array_key_exists($this->name(), $inp));
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
