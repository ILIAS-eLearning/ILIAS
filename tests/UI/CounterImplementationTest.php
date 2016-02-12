<?php

require_once(__DIR__."/CounterTest.php");

/**
 * This tests the actual implementation of the factory.
 */
class CounterImplementationTest extends CounterTest {
    public function getFactoryInstance() {
        return new \ILIAS\UI\Internal\Counter\FactoryImpl();
    }
}