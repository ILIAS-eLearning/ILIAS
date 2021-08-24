<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetConfigTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetConfigTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetConfig $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetConfig(
            $this->createMock(ilTree::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilPluginAdmin::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetConfig::class, $this->testObj);
    }

    public function testPoolsWithHomogeneousScoredQuestionsRequired() : void
    {
        $this->testObj->setPoolsWithHomogeneousScoredQuestionsRequired(false);
        $this->assertFalse($this->testObj->arePoolsWithHomogeneousScoredQuestionsRequired());

        $this->testObj->setPoolsWithHomogeneousScoredQuestionsRequired(true);
        $this->assertTrue($this->testObj->arePoolsWithHomogeneousScoredQuestionsRequired());
    }

    public function testQuestionAmountConfigurationMode() : void
    {
        $this->testObj->setQuestionAmountConfigurationMode("test");
        $this->assertEquals("test", $this->testObj->getQuestionAmountConfigurationMode());
    }

    public function testQuestionAmountConfigurationModePerPool() : void
    {
        $this->testObj->setQuestionAmountConfigurationMode(ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST);
        $this->assertFalse($this->testObj->isQuestionAmountConfigurationModePerPool());

        $this->testObj->setQuestionAmountConfigurationMode(ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_POOL);
        $this->assertTrue($this->testObj->isQuestionAmountConfigurationModePerPool());
    }

    public function testQuestionAmountConfigurationModePerTest() : void
    {
        $this->testObj->setQuestionAmountConfigurationMode(ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_POOL);
        $this->assertFalse($this->testObj->isQuestionAmountConfigurationModePerTest());

        $this->testObj->setQuestionAmountConfigurationMode(ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST);
        $this->assertTrue($this->testObj->isQuestionAmountConfigurationModePerTest());
    }

    public function testIsValidQuestionAmountConfigurationMode() : void
    {
        $this->assertFalse($this->testObj->isValidQuestionAmountConfigurationMode(200));
        $this->assertTrue($this->testObj->isValidQuestionAmountConfigurationMode(ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_POOL));
        $this->assertTrue($this->testObj->isValidQuestionAmountConfigurationMode(ilTestRandomQuestionSetConfig::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST));
    }

    public function testQuestionAmountPerTest() : void
    {
        $this->testObj->setQuestionAmountPerTest(222);
        $this->assertEquals(222, $this->testObj->getQuestionAmountPerTest());
    }

    public function testLastQuestionSyncTimestamp() : void
    {
        $this->testObj->setLastQuestionSyncTimestamp(222);
        $this->assertEquals(222, $this->testObj->getLastQuestionSyncTimestamp());
    }
}
