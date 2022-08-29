<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestExpressPageTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestExpressPageTest extends ilTestBaseTestCase
{
    private ilTestExpressPage $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestExpressPage();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestExpressPage::class, $this->testObj);
    }
}
