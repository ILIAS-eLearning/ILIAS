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
 * Class ilTestPasswordCheckerTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPasswordCheckerTest extends ilTestBaseTestCase
{
    private ilTestPasswordChecker $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestPasswordChecker(
            $this->createMock(ilRbacSystem::class),
            $this->createMock(ilObjUser::class),
            $this->createMock(ilObjTest::class),
            $this->createMock(ilLanguage::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestPasswordChecker::class, $this->testObj);
    }
}
