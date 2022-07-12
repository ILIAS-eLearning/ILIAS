<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTestFixedQuestionSetConfigTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestFixedQuestionSetConfigTest extends ilTestBaseTestCase
{
    private ilTestFixedQuestionSetConfig $testObj;
    /**
     * @var ilObjTest|mixed|MockObject
     */
    private $objTest_mock;

    protected function setUp() : void
    {
        parent::setUp();

        $this->objTest_mock = $this->createMock(ilObjTest::class);

        $this->testObj = new ilTestFixedQuestionSetConfig(
            $this->createMock(ilTree::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilPluginAdmin::class),
            $this->objTest_mock
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestFixedQuestionSetConfig::class, $this->testObj);
    }

    public function testIsQuestionSetConfigured() : void
    {
        $this->assertFalse($this->testObj->isQuestionSetConfigured());
    }

    public function testDoesQuestionSetRelatedDataExist() : void
    {
        $this->assertFalse($this->testObj->doesQuestionSetRelatedDataExist());
    }
}
