<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\TriggeredSignal;

class Triggerermock
{
    use \ILIAS\UI\Implementation\Component\Triggerer;

    public function _appendTriggeredSignal(Component\Signal $signal, $event)
    {
        return $this->appendTriggeredSignal($signal, $event);
    }

    public function _withTriggeredSignal(Component\Signal $signal, $event)
    {
        return $this->withTriggeredSignal($signal, $event);
    }

    public function _setTriggeredSignal(Component\Signal $signal, $event)
    {
        return $this->setTriggeredSignal($signal, $event);
    }
}

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 */
class ILIAS_UI_Component_TriggererTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mock = new TriggererMock();
    }

    protected $signal_mock_counter = 0;
    protected function getSignalMock()
    {
        $this->signal_mock_counter++;
        return $this
            ->getMockBuilder(Component\Signal::class)
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMockClassName("Signal_{$this->signal_mock_counter}")
            ->getMock();
    }

    public function testStartEmpty()
    {
        $this->assertEquals([], $this->mock->getTriggeredSignals());
    }

    public function testAppendTriggeredSignalIsImmutable()
    {
        $signal = $this->getSignalMock();

        $mock = $this->mock->_appendTriggeredSignal($signal, "some_event");
        $this->assertNotSame($mock, $this->mock);
    }

    public function testAppendTriggeredSignal()
    {
        $signal1 = $this->getSignalMock();
        $signal2 = $this->getSignalMock();
        $signal3 = $this->getSignalMock();

        $mock = $this->mock->_appendTriggeredSignal($signal1, "some_event");
        $mock2 = $this->mock
            ->_appendTriggeredSignal($signal2, "some_event")
            ->_appendTriggeredSignal($signal3, "some_event");

        $this->assertEquals([], $this->mock->getTriggeredSignals());
        $this->assertEquals([new TriggeredSignal($signal1, "some_event")], $mock->getTriggeredSignals());
        $this->assertEquals([new TriggeredSignal($signal2, "some_event"), new TriggeredSignal($signal3, "some_event")], $mock2->getTriggeredSignals());
    }

    public function testWithTriggeredSignalIsImmutable()
    {
        $signal = $this->getSignalMock();

        $mock = $this->mock->_withTriggeredSignal($signal, "some_event");

        $this->assertNotSame($mock, $this->mock);
    }

    public function testWithTriggeredSignal()
    {
        $signal1 = $this->getSignalMock();
        $signal2 = $this->getSignalMock();

        $mock = $this->mock->_withTriggeredSignal($signal1, "some_event");
        $mock2 = $mock->_withTriggeredSignal($signal2, "some_event");

        $this->assertEquals([new TriggeredSignal($signal1, "some_event")], $mock->getTriggeredSignals());
        $this->assertEquals([new TriggeredSignal($signal2, "some_event")], $mock2->getTriggeredSignals());
    }

    public function testSetTriggeredSignal()
    {
        $signal1 = $this->getSignalMock();
        $signal2 = $this->getSignalMock();

        $this->mock->_setTriggeredSignal($signal1, "some_event");
        $this->mock->_setTriggeredSignal($signal2, "some_event");

        $this->assertEquals([new TriggeredSignal($signal2, "some_event")], $this->mock->getTriggeredSignals());
    }

    public function testWithResetTriggeredSignalIsImmutable()
    {
        $signal = $this->getSignalMock();

        $mock = $this->mock->withResetTriggeredSignals();

        $this->assertNotSame($mock, $this->mock);
    }

    public function testWithResetTriggeredSignal()
    {
        $signal1 = $this->getSignalMock();
        $signal2 = $this->getSignalMock();

        $mock = $this->mock
            ->_appendTriggeredSignal($signal1, "some_event")
            ->_appendTriggeredSignal($signal2, "some_event")
            ->withResetTriggeredSignals();

        $this->assertEquals([], $mock->getTriggeredSignals());
    }

    public function testGetTriggeredSignalsForNonRegisteredSignal()
    {
        $signals = $this->mock->getTriggeredSignalsFor("some_event");
        $this->assertEquals([], $signals);
    }

    public function testGetTriggeredSignals()
    {
        $signal1 = $this->getSignalMock();
        $signal2 = $this->getSignalMock();

        $mock = $this->mock
            ->_appendTriggeredSignal($signal1, "some_event")
            ->_appendTriggeredSignal($signal2, "some_event");

        $signals = $mock->getTriggeredSignalsFor("some_event");

        $this->assertEquals([$signal1, $signal2], $signals);
    }
}
