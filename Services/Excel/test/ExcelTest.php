<?php

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ExcelTest extends TestCase
{
    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function setUp(): void
    {
        parent::setUp();
        $dic = new ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;

        $languageMock = $this->getMockBuilder(ilLanguage::class)
                             ->disableOriginalConstructor()
                             ->getMock();
        $this->setGlobalVariable(
            "lng",
            $languageMock
        );
    }

    protected function tearDown(): void
    {
    }

    public function testCoordByColumnAndRow(): void
    {
        $excel = new ilExcel();

        $this->assertEquals(
            "C2",
            $excel->getCoordByColumnAndRow(2, 2)
        );
    }
}
