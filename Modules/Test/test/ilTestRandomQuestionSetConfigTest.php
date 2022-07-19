<?php declare(strict_types=1);

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
            $this->getMockBuilder(ilTree::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilComponentRepository::class),
            $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock()
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
