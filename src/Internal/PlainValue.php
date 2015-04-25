<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 *
 */

namespace Lechimp\Formlets\Internal;

use Lechimp\Formlets\IValue;
use Exception;

final class PlainValue extends Value {
    private $_value; //mixed

    public function __construct($value, $origin) {
        $this->_value = $value;
        parent::__construct($origin);
    }

    public function get() {
        return $this->_value;
    }

    public function apply(IValue $to) {
        throw new ApplyError("PlainValue", "any Value");
    }

    public function catchAndReify($exc_class) {
        return null;
    }

    public function isApplicable() {
        return false;
    }

    public function force() {
        return $this;
    }

    public function isError() {
        return false;
    }

    public function error() {
        throw new Exception("Implementation problem.");
    }
}

?>
