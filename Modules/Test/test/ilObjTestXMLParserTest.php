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

/**
 * Class ilObjTestXMLParserTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestXMLParserTest extends ilTestBaseTestCase
{
    private ilObjTestXMLParser $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilObjTestXMLParser();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilObjTestXMLParser::class, $this->testObj);
    }

    public function testTestOBJ(): void
    {
        $objTest_mock = $this->createMock(ilObjTest::class);
        $this->assertNull($this->testObj->getTestOBJ());

        $this->testObj->setTestOBJ($objTest_mock);
        $this->assertEquals($objTest_mock, $this->testObj->getTestOBJ());
    }

    public function testImportMapping(): void
    {
        $importMapping_mock = $this->createMock(ilImportMapping::class);
        $this->assertNull($this->testObj->getImportMapping());

        $this->testObj->setImportMapping($importMapping_mock);
        $this->assertEquals($importMapping_mock, $this->testObj->getImportMapping());
    }
}
