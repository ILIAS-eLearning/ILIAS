<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestTaxonomyFilterLabelTranslaterTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestTaxonomyFilterLabelTranslaterTest extends ilTestBaseTestCase
{
    private ilTestTaxonomyFilterLabelTranslater $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilDB();
        $this->addGlobal_lng();

        $this->testObj = new ilTestTaxonomyFilterLabelTranslater(
            $this->createMock(ilDBInterface::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestTaxonomyFilterLabelTranslater::class, $this->testObj);
    }
}
