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
 * Class ilTestTaxonomyTreeTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestTaxonomyTreeTest extends ilTestBaseTestCase
{
    private ilTestTaxonomyTree $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $db_mock = $this->createMock(ilDBInterface::class);
        $returnValue = new stdClass();
        $returnValue->child = 1;
        $db_mock->expects($this->once())
                ->method("fetchObject")
                ->willReturn($returnValue);

        $this->setGlobalVariable("ilDB", $db_mock);
        $this->addGlobal_ilAppEventHandler();

        $this->testObj = new ilTestTaxonomyTree(0);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestTaxonomyTree::class, $this->testObj);
    }
}
