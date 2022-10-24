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
 * Class ilTestRandomQuestionSetSourcePoolDefinitionFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetSourcePoolDefinitionFactoryTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetSourcePoolDefinitionFactory $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetSourcePoolDefinitionFactory::class, $this->testObj);
    }

    public function testGetSourcePoolDefinitionByOriginalPoolData(): void
    {
        $originalPoolData = [
            "qpl_id" => 2,
            'qpl_ref_id' => 4711,
            "qpl_title" => "testTitle",
            "qpl_path" => "test/path",
            "count" => 5
        ];

        $result = $this->testObj->getSourcePoolDefinitionByOriginalPoolData($originalPoolData);
        $this->assertEquals($originalPoolData["qpl_id"], $result->getPoolId());
        $this->assertEquals($originalPoolData["qpl_ref_id"], $result->getPoolRefId());
        $this->assertEquals($originalPoolData["qpl_title"], $result->getPoolTitle());
        $this->assertEquals($originalPoolData["qpl_path"], $result->getPoolPath());
        $this->assertEquals($originalPoolData["count"], $result->getPoolQuestionCount());
    }

    public function testGetEmptySourcePoolDefinition(): void
    {
        $this->assertInstanceOf(
            ilTestRandomQuestionSetSourcePoolDefinition::class,
            $this->testObj->getEmptySourcePoolDefinition()
        );
    }
}
