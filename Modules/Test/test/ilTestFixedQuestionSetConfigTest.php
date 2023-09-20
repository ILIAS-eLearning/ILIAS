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

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTestFixedQuestionSetConfigTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestFixedQuestionSetConfigTest extends ilTestBaseTestCase
{
    private ilTestFixedQuestionSetConfig $testObj;
    /**
     * @var ilObjTest|mixed|MockObject
     */
    private $objTest_mock;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_lng();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilComponentRepository();

        $this->objTest_mock = $this->createMock(ilObjTest::class);

        $this->testObj = new ilTestFixedQuestionSetConfig(
            $DIC['tree'],
            $DIC['ilDB'],
            $DIC['lng'],
            $DIC['ilLog'],
            $DIC['component.repository'],
            $this->objTest_mock,
            $this->createMock(\ILIAS\TestQuestionPool\QuestionInfoService::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestFixedQuestionSetConfig::class, $this->testObj);
    }

    public function testIsQuestionSetConfigured(): void
    {
        $this->assertFalse($this->testObj->isQuestionSetConfigured());
    }

    public function testDoesQuestionSetRelatedDataExist(): void
    {
        $this->assertFalse($this->testObj->doesQuestionSetRelatedDataExist());
    }
}
