<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPasswordProtectionGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPasswordProtectionGUITest extends ilTestBaseTestCase
{
    private ilTestPasswordProtectionGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestPasswordProtectionGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilTestPlayerAbstractGUI::class),
            $this->createMock(ilTestPasswordChecker::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestPasswordProtectionGUI::class, $this->testObj);
    }
}
