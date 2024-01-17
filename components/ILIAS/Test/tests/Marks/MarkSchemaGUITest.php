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

use ILIAS\Test\Marks\MarkSchemaGUI;
use ILIAS\Test\Marks\MarkSchemaAware;

/**
 * @author Marvin Beym <mbeym@databay.de>
 */
class MarkSchemaGUITest extends ilTestBaseTestCase
{
    private MarkSchemaGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_lng();
        $this->addGlobal_tpl();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_ilTabs();

        $this->testObj = new MarkSchemaGUI(
            $this->createMock(MarkSchemaAware::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilCtrl::class),
            $this->createMock(ilGlobalTemplateInterface::class),
            $this->createMock(ilToolbarGUI::class),
            $this->createMock(\ilTabsGUI::class),
            $this->createMock(\ILIAS\Test\Logging\TestLogger::class),
            $this->createMock(ILIAS\HTTP\Wrapper\RequestWrapper::class),
            $this->createMock(\GuzzleHttp\Psr7\Request::class),
            $this->createMock(ILIAS\Refinery\Factory::class),
            $this->createMock(ILIAS\UI\Factory::class),
            $this->createMock(ILIAS\UI\Renderer::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(MarkSchemaGUI::class, $this->testObj);
    }
}
