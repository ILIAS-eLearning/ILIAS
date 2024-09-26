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

use ILIAS\Test\Results\Presentation\TitlesBuilder as ResultsTitlesBuilder;

/**
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultHeaderLabelBuilderTest extends ilTestBaseTestCase
{
    private ResultsTitlesBuilder $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ResultsTitlesBuilder(
            $this->createMock(ilLanguage::class),
            $this->createMock(ilObjectDataCache::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ResultsTitlesBuilder::class, $this->testObj);
    }

    public function testObjectiveOrientedContainerId(): void
    {
        $objectiveOrientedContainerId = 5;
        $this->testObj->setObjectiveOrientedContainerId($objectiveOrientedContainerId);
        $this->assertEquals($objectiveOrientedContainerId, $this->testObj->getObjectiveOrientedContainerId());
    }

    public function testTestObjId(): void
    {
        $testObjId = 5;
        $this->testObj->setTestObjId($testObjId);
        $this->assertEquals($testObjId, $this->testObj->getTestObjId());
    }

    public function testTestRefId(): void
    {
        $testRefId = 5;
        $this->testObj->setTestRefId($testRefId);
        $this->assertEquals($testRefId, $this->testObj->getTestRefId());
    }

    public function testUserId(): void
    {
        $userId = 5;
        $this->testObj->setUserId($userId);
        $this->assertEquals($userId, $this->testObj->getUserId());
    }
}
