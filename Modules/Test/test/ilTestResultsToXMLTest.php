<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestResultsToXMLTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultsToXMLTest extends ilTestBaseTestCase
{
    private ilTestResultsToXML $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestResultsToXML(
            0, false
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestResultsToXML::class, $this->testObj);
    }
}