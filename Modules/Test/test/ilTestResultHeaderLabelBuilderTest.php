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
 * Class ilTestResultHeaderLabelBuilderTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultHeaderLabelBuilderTest extends ilTestBaseTestCase
{
    private ilTestResultHeaderLabelBuilder $testObj;

    private $lng_mock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lng_mock = $this->createMock(ilLanguage::class);

        $this->testObj = new ilTestResultHeaderLabelBuilder(
            $this->lng_mock,
            $this->createMock(ilObjectDataCache::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestResultHeaderLabelBuilder::class, $this->testObj);
    }

    public function testObjectiveOrientedContainerId(): void
    {
        $this->testObj->setObjectiveOrientedContainerId(5);
        $this->assertEquals(5, $this->testObj->getObjectiveOrientedContainerId());
    }

    public function testTestObjId(): void
    {
        $this->testObj->setTestObjId(5);
        $this->assertEquals(5, $this->testObj->getTestObjId());
    }

    public function testTestRefId(): void
    {
        $this->testObj->setTestRefId(5);
        $this->assertEquals(5, $this->testObj->getTestRefId());
    }

    public function testUserId(): void
    {
        $this->testObj->setUserId(5);
        $this->assertEquals(5, $this->testObj->getUserId());
    }
}
