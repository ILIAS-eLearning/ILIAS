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
