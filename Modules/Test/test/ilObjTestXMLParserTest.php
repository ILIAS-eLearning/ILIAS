<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjTestXMLParserTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestXMLParserTest extends ilTestBaseTestCase
{
    private ilObjTestXMLParser $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilObjTestXMLParser();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilObjTestXMLParser::class, $this->testObj);
    }
}