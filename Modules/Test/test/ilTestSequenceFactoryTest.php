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
 * Class ilTestSequenceFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSequenceFactoryTest extends ilTestBaseTestCase
{
    private ilTestSequenceFactory $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSequenceFactory(
            $this->createMock(ilDBInterface::class),
            $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ilComponentRepository::class),
            $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock(),
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSequenceFactory::class, $this->testObj);
    }
}
