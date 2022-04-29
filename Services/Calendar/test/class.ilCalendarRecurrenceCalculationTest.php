<?php

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

class ilCalendarRecurrenceCalculationTest extends TestCase
{
    protected $backupGlobals = false;
    protected Container $dic;

    protected function setUp() : void
    {
        $this->initDependencies();
        parent::setUp();
    }

    public function testCalculatorConstruct()
    {
        $entry = new ilCalendarEntry(0);
        $entry->setStart(new ilDate('2022-01-01', IL_CAL_DATE));
        $entry->setEnd(new ilDate('2022-01-01', IL_CAL_DATE));
        $entry->setFullday(true);
        $entry->setTitle('First');
        $rec = new ilCalendarRecurrence(0);

        $calc = new ilCalendarRecurrenceCalculator(
            $entry,
            $rec
        );
        $this->assertTrue($calc instanceof ilCalendarRecurrenceCalculator);
    }

    public function testYearly()
    {
        $entry = new ilCalendarEntry(0);
        $entry->setStart(new ilDate('2022-01-01', IL_CAL_DATE));
        $entry->setEnd(new ilDate('2022-01-01', IL_CAL_DATE));
        $entry->setFullday(true);

        $rec = new ilCalendarRecurrence(0);
        $rec->setFrequenceType(ilCalendarRecurrence::FREQ_YEARLY);
        $rec->setInterval(1);
        $rec->setFrequenceUntilCount(1);

        $calc = new ilCalendarRecurrenceCalculator(
            $entry,
            $rec
        );
        $dl = $calc->calculateDateList(
            new ilDateTime('2021-12-31', IL_CAL_DATE),
            new ilDate('2023-12-31', IL_CAL_DATE),
            -1
        );
        $this->assertCount(1, $dl);
        foreach ($dl as $date) {
            $this->assertTrue(strcmp($date->get(IL_CAL_DATE), '2022-01-01') === 0);
        }
    }





    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function initDependencies() : void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;

        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));
        $this->setGlobalVariable('lng', $this->createMock(ilLanguage::class));
        $this->setGlobalVariable('ilErr', $this->createMock(ilErrorHandling::class));

        $logger = $this->getMockBuilder(ilLogger::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $logger_factory = $this->getMockBuilder(ilLoggerFactory::class)
                               ->disableOriginalConstructor()
                               ->onlyMethods(['getComponentLogger'])
                               ->getMock();
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);
    }
}
