<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestResultHeaderLabelBuilderTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultHeaderLabelBuilderTest extends ilTestBaseTestCase
{
    private ilTestResultHeaderLabelBuilder $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestResultHeaderLabelBuilder(
            $this->createMock(ilLanguage::class),
            $this->createMock(ilObjectDataCache::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestResultHeaderLabelBuilder::class, $this->testObj);
    }
}