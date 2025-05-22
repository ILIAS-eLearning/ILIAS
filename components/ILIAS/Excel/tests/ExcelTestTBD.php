<?php

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
