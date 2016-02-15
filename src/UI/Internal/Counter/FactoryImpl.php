<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Internal\Counter;

class FactoryImpl implements \ILIAS\UI\Factory\Counter {
    /**
     * @inheritdoc
     */
    public function status($amount) {
        return new CounterImpl(new \ILIAS\UI\Element\StatusCounterType(), $amount);
    }

    /**
     * @inheritdoc
     */
    public function novelty($amount) {
        return new CounterImpl(new \ILIAS\UI\Element\NoveltyCounterType(), $amount);
    }
}

//Force autoloading of Counter.php for counter types.
interface Force_Counter extends \ILIAS\UI\Element\Counter {}
