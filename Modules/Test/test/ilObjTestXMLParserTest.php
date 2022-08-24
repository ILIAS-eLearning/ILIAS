<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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
