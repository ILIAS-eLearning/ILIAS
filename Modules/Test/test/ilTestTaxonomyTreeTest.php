<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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
