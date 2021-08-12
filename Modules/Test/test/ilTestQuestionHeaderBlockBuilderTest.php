<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestQuestionHeaderBlockBuilderTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionHeaderBlockBuilderTest extends ilTestBaseTestCase
{
    private ilTestQuestionHeaderBlockBuilder $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestQuestionHeaderBlockBuilder(
            $this->createMock(ilLanguage::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestQuestionHeaderBlockBuilder::class, $this->testObj);
    }
}