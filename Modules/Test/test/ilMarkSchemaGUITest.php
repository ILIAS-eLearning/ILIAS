<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMarkSchemaGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilMarkSchemaGUITest extends ilTestBaseTestCase
{
    private ilMarkSchemaGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_lng();
        $this->addGlobal_tpl();
        $this->addGlobal_ilToolbar();

        $this->testObj = new ilMarkSchemaGUI(
            $this->createMock(ilMarkSchemaAware::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilMarkSchemaGUI::class, $this->testObj);
    }
}
