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
 * Class ilTestRandomQuestionSetPoolDeriverTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetPoolDeriverTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetPoolDeriver $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetPoolDeriver(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilComponentRepository::class),
            $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock(),
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetPoolDeriver::class, $this->testObj);
    }

    public function testTargetContainerRef(): void
    {
        $targetContainerRef = 125;
        $this->testObj->setTargetContainerRef($targetContainerRef);
        $this->assertEquals($targetContainerRef, $this->testObj->getTargetContainerRef());
    }

    public function testOwnerId(): void
    {
        $ownerId = 125;
        $this->testObj->setOwnerId($ownerId);
        $this->assertEquals($ownerId, $this->testObj->getOwnerId());
    }

    public function testSourcePoolDefinitionList(): void
    {
        $mock = $this->createMock(ilTestRandomQuestionSetSourcePoolDefinitionList::class);
        $this->testObj->setSourcePoolDefinitionList($mock);
        $this->assertEquals($mock, $this->testObj->getSourcePoolDefinitionList());
    }
}
