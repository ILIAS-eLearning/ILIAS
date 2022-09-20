<?php

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

class ilAdvancedMDRecordObjectOrderingsTest extends TestCase
{
    protected Container $dic;

    protected function setUp(): void
    {
        $this->initRecordSortingDependencies();
        parent::setUp();
    }

    public function testConstruct(): void
    {
        $sorting = new ilAdvancedMDRecordObjectOrderings();
        $this->assertTrue($sorting instanceof ilAdvancedMDRecordObjectOrderings);
    }

    public function testGlobalRecordSorting(): void
    {
        $record_id_reflection = new ReflectionMethod(ilAdvancedMDRecord::class, 'setRecordId');
        $record_id_reflection->setAccessible(true);

        $ids = [1, 2, 3, 4, 5];
        $positions = array_reverse($ids);
        $records = [];
        foreach ($ids as $id) {
            $record = new ilAdvancedMDRecord();
            $record->setGlobalPosition(array_shift($positions));
            $record_id_reflection->invokeArgs($record, [$id]);

            $records[] = $record;
        }

        $sorting = new ilAdvancedMDRecordObjectOrderings();
        $sorted = $sorting->sortRecords($records);
        $this->assertTrue(is_array($sorted));
        foreach ($sorted as $index => $record) {
            // test reverse ordering (idx + record equals 5)
            $this->assertEquals(5, $index + $record->getRecordId());
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function initRecordSortingDependencies(): void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;
        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));
    }
}
