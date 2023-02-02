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
 * Class ilTestRandomQuestionSetSourcePoolDefinitionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetSourcePoolDefinitionTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetSourcePoolDefinition $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetSourcePoolDefinition::class, $this->testObj);
    }

    public function testId(): void
    {
        $this->testObj->setId(125);
        $this->assertEquals(125, $this->testObj->getId());
    }

    public function testPoolId(): void
    {
        $this->testObj->setPoolId(125);
        $this->assertEquals(125, $this->testObj->getPoolId());
    }

    public function testPoolTitle(): void
    {
        $this->testObj->setPoolTitle("test");
        $this->assertEquals("test", $this->testObj->getPoolTitle());
    }

    public function testPoolPath(): void
    {
        $this->testObj->setPoolPath("test");
        $this->assertEquals("test", $this->testObj->getPoolPath());
    }

    public function testPoolQuestionCount(): void
    {
        $this->testObj->setPoolQuestionCount(5);
        $this->assertEquals(5, $this->testObj->getPoolQuestionCount());
    }

    public function testOriginalTaxonomyFilter(): void
    {
        $expected = [
            125 => ["nodeId" => 20],
            17 => ["nodeId" => 3],
        ];
        $this->testObj->setOriginalTaxonomyFilter($expected);
        $this->assertEquals($expected, $this->testObj->getOriginalTaxonomyFilter());
    }

    public function testMappedTaxonomyFilter(): void
    {
        $expected = [
            125 => ["nodeId" => 20],
            17 => ["nodeId" => 3],
        ];
        $this->testObj->setMappedTaxonomyFilter($expected);
        $this->assertEquals($expected, $this->testObj->getMappedTaxonomyFilter());
    }

    public function testTypeFilter(): void
    {
        $expected = [
            "test",
            "hello",
            "world"
        ];
        $this->testObj->setTypeFilter($expected);
        $this->assertEquals($expected, $this->testObj->getTypeFilter());
    }

    public function testLifecycleFilter(): void
    {
        $expected = [
            "test",
            "hello",
            "world"
        ];
        $this->testObj->setLifecycleFilter($expected);
        $this->assertEquals($expected, $this->testObj->getLifecycleFilter());
    }

    public function testQuestionAmount(): void
    {
        $this->testObj->setQuestionAmount(5);
        $this->assertEquals(5, $this->testObj->getQuestionAmount());
    }

    public function testSequencePosition(): void
    {
        $this->testObj->setSequencePosition(5);
        $this->assertEquals(5, $this->testObj->getSequencePosition());
    }
}
