<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Internal\Counter;

class FactoryImpl implements \ILIAS\UI\Factory\Counter {
    private $novelty_type = null;
    private $status_type = null;

    public function __construct() {
        $this->novelty_type = new \ILIAS\UI\Element\NoveltyCounterType();
        $this->status_type = new \ILIAS\UI\Element\StatusCounterType();
    }

    /**
     * @inheritdoc
     */
    public function status($amount) {
        return new CounterImpl($this->status_type, $amount);
    }

    /**
     * @inheritdoc
     */
    public function novelty($amount) {
        return new CounterImpl($this->novelty_type, $amount);
    }
}

//Force autoloading of Counter.php for counter types.
interface Force_Counter extends \ILIAS\UI\Element\Counter {}
