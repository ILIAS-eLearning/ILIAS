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
 * Class ilTestRandomQuestionSetSourcePoolDefinitionListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetSourcePoolDefinitionListTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetSourcePoolDefinitionList $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestRandomQuestionSetSourcePoolDefinitionFactory::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetSourcePoolDefinitionList::class, $this->testObj);
    }

    public function testAddDefinition(): void
    {
        $expected = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
        $expected->setId(20);
        $this->testObj->addDefinition($expected);

        $this->assertEquals($expected, $this->testObj->getDefinition(20));
    }

    public function testSetTrashedPools(): void
    {
        $poolIds = [12, 22, 16];

        $this->testObj->setTrashedPools($poolIds);

        $this->assertEquals($poolIds, $this->testObj->getTrashedPools());
    }

    public function testIsTrashedPool(): void
    {
        $poolIds = [12, 22, 16];

        $this->testObj->setTrashedPools($poolIds);

        $this->assertTrue($this->testObj->isTrashedPool(0));
        $this->assertFalse($this->testObj->isTrashedPool(4));
    }

    public function testHasTrashedPool(): void
    {
        $poolIds = [12, 22, 16];

        $this->testObj->setTrashedPools($poolIds);

        $this->assertTrue($this->testObj->hasTrashedPool());
    }

    public function testHasDefinition(): void
    {
        $expected = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
        $expected->setId(20);
        $this->testObj->addDefinition($expected);

        $this->assertTrue($this->testObj->hasDefinition(20));
    }

    public function testGetDefinition(): void
    {
        $expected = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
        $expected->setId(20);
        $this->testObj->addDefinition($expected);

        $this->assertEquals($expected, $this->testObj->getDefinition(20));
    }

    public function testGetDefinitionBySourcePoolId(): void
    {
        $expected = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
        $expected->setId(20);
        $expected->setPoolId(11);
        $this->testObj->addDefinition($expected);

        $this->assertEquals($expected, $this->testObj->getDefinitionBySourcePoolId(11));
    }

    public function testGetDefinitionIds(): void
    {
        $expected = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
        $expected->setId(20);
        $expected->setPoolId(11);
        $this->testObj->addDefinition($expected);

        $this->assertEquals([20], $this->testObj->getDefinitionIds());
    }

    public function testGetDefinitionCount(): void
    {
        $expected = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
        $expected->setId(20);
        $expected->setPoolId(11);
        $this->testObj->addDefinition($expected);

        $this->assertEquals(1, $this->testObj->getDefinitionCount());
    }
}
