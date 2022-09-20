<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

require_once("libs/composer/vendor/autoload.php");

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\TriggeredSignal;
use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use PHPUnit\Framework\MockObject\MockObject;

class Triggerermock implements Component\Triggerer
{
    use Triggerer;
    use JavaScriptBindable;
    use ComponentHelper;

    public function _appendTriggeredSignal(Component\Signal $signal, string $event): Component\Triggerer
    {
        return $this->appendTriggeredSignal($signal, $event);
    }

    public function _withTriggeredSignal(Component\Signal $signal, string $event): Component\Triggerer
    {
        return $this->withTriggeredSignal($signal, $event);
    }

    public function _setTriggeredSignal(Component\Signal $signal, string $event)
    {
        $this->setTriggeredSignal($signal, $event);
    }
}

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 */
class ILIAS_UI_Component_TriggererTest extends TestCase
{
    protected Triggerermock $mock;
    protected int $signal_mock_counter = 0;

    public function setUp(): void
    {
        $this->mock = new TriggererMock();
    }

    /**
     * @return Component\Signal|mixed|MockObject
     */
    protected function getSignalMock()
    {
        $this->signal_mock_counter++;
        return $this
            ->getMockBuilder(Component\Signal::class)
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMockClassName("Signal_$this->signal_mock_counter")
            ->getMock();
    }

    public function testStartEmpty(): void
    {
        $this->assertEquals([], $this->mock->getTriggeredSignals());
    }

    public function testAppendTriggeredSignalIsImmutable(): void
    {
        $signal = $this->getSignalMock();

        $mock = $this->mock->_appendTriggeredSignal($signal, "some_event");
        $this->assertNotSame($mock, $this->mock);
    }

    public function testAppendTriggeredSignal(): void
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
        $this->assertEquals(
            [new TriggeredSignal($signal2, "some_event"), new TriggeredSignal($signal3, "some_event")],
            $mock2->getTriggeredSignals()
        );
    }

    public function testWithTriggeredSignalIsImmutable(): void
    {
        $signal = $this->getSignalMock();

        $mock = $this->mock->_withTriggeredSignal($signal, "some_event");

        $this->assertNotSame($mock, $this->mock);
    }

    public function testWithTriggeredSignal(): void
    {
        $signal1 = $this->getSignalMock();
        $signal2 = $this->getSignalMock();

        $mock = $this->mock->_withTriggeredSignal($signal1, "some_event");
        $mock2 = $mock->_withTriggeredSignal($signal2, "some_event");

        $this->assertEquals([new TriggeredSignal($signal1, "some_event")], $mock->getTriggeredSignals());
        $this->assertEquals([new TriggeredSignal($signal2, "some_event")], $mock2->getTriggeredSignals());
    }

    public function testSetTriggeredSignal(): void
    {
        $signal1 = $this->getSignalMock();
        $signal2 = $this->getSignalMock();

        $this->mock->_setTriggeredSignal($signal1, "some_event");
        $this->mock->_setTriggeredSignal($signal2, "some_event");

        $this->assertEquals([new TriggeredSignal($signal2, "some_event")], $this->mock->getTriggeredSignals());
    }

    public function testWithResetTriggeredSignalIsImmutable(): void
    {
        $this->getSignalMock();
        $mock = $this->mock->withResetTriggeredSignals();
        $this->assertNotSame($mock, $this->mock);
    }

    public function testWithResetTriggeredSignal(): void
    {
        $signal1 = $this->getSignalMock();
        $signal2 = $this->getSignalMock();

        $mock = $this->mock
            ->_appendTriggeredSignal($signal1, "some_event")
            ->_appendTriggeredSignal($signal2, "some_event")
            ->withResetTriggeredSignals();

        $this->assertEquals([], $mock->getTriggeredSignals());
    }

    public function testGetTriggeredSignalsForNonRegisteredSignal(): void
    {
        $signals = $this->mock->getTriggeredSignalsFor("some_event");
        $this->assertEquals([], $signals);
    }

    public function testGetTriggeredSignals(): void
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
