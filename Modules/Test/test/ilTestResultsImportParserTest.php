<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestResultsImportParserTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultsImportParserTest extends ilTestBaseTestCase
{
    private ilTestResultsImportParser $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $testObject = $this->createMock(ilObjTest::class);
        $this->testObj = new ilTestResultsImportParser("", $testObject);
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestResultsImportParser::class, $this->testObj);
    }
}