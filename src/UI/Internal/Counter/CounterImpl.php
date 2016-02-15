<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Internal\Counter;
use ILIAS\UI\Element as E;

class CounterImpl implements \ILIAS\UI\Element\Counter {
    private $type;
    private $amount;

    public function __construct(E\CounterType $type, $amount) {
        assert('is_int($amount)');
        $this->type = $type;
        $this->amount = $amount;
    }

    public function type() {
        return $this->type;
    }

    public function amount() {
        return $this->amount;
    }

    public function to_html_string() {
        throw new \Exception("Implement me!");
    }
}
