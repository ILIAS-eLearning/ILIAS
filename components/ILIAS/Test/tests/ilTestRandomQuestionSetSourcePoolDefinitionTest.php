<?php

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

declare(strict_types=1);

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
            $this->createMock(ilObjTest::class),
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetSourcePoolDefinition::class, $this->testObj);
    }

    public function testId(): void
    {
        $id = 125;
        $this->testObj->setId($id);
        $this->assertEquals($id, $this->testObj->getId());
    }

    public function testPoolId(): void
    {
        $poolId = 125;
        $this->testObj->setPoolId($poolId);
        $this->assertEquals($poolId, $this->testObj->getPoolId());
    }

    public function testPoolTitle(): void
    {
        $poolTitle = 'test';
        $this->testObj->setPoolTitle($poolTitle);
        $this->assertEquals($poolTitle, $this->testObj->getPoolTitle());
    }

    public function testPoolPath(): void
    {
        $poolPath = 'test';
        $this->testObj->setPoolPath($poolPath);
        $this->assertEquals($poolPath, $this->testObj->getPoolPath());
    }

    public function testPoolQuestionCount(): void
    {
        $poolQuestionCount = 5;
        $this->testObj->setPoolQuestionCount($poolQuestionCount);
        $this->assertEquals($poolQuestionCount, $this->testObj->getPoolQuestionCount());
    }

    public function testOriginalTaxonomyFilter(): void
    {
        $expected = [
            125 => ['nodeId' => 20],
            17 => ['nodeId' => 3],
        ];
        $this->testObj->setOriginalTaxonomyFilter($expected);
        $this->assertEquals($expected, $this->testObj->getOriginalTaxonomyFilter());
    }

    public function testMappedTaxonomyFilter(): void
    {
        $expected = [
            125 => ['nodeId' => 20],
            17 => ['nodeId' => 3],
        ];
        $this->testObj->setMappedTaxonomyFilter($expected);
        $this->assertEquals($expected, $this->testObj->getMappedTaxonomyFilter());
    }

    public function testTypeFilter(): void
    {
        $expected = [
            'test',
            'hello',
            'world',
        ];
        $this->testObj->setTypeFilter($expected);
        $this->assertEquals($expected, $this->testObj->getTypeFilter());
    }

    public function testLifecycleFilter(): void
    {
        $expected = [
            'test',
            'hello',
            'world',
        ];
        $this->testObj->setLifecycleFilter($expected);
        $this->assertEquals($expected, $this->testObj->getLifecycleFilter());
    }

    public function testQuestionAmount(): void
    {
        $questionAmount = 5;
        $this->testObj->setQuestionAmount($questionAmount);
        $this->assertEquals($questionAmount, $this->testObj->getQuestionAmount());
    }

    public function testSequencePosition(): void
    {
        $sequencePosition = 5;
        $this->testObj->setSequencePosition($sequencePosition);
        $this->assertEquals($sequencePosition, $this->testObj->getSequencePosition());
    }
}
