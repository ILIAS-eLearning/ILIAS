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

namespace Test\tests;
use ilObjectDataCache;
use ilRepositorySelectorExplorerGUI;
use ilTestBaseTestCase;
use ilTestQuestionPoolSelectorExplorer;
use ilTestRandomQuestionSetConfigGUI;

class ilTestQuestionPoolSelectorExplorerTest extends ilTestBaseTestCase
{
    private ilTestQuestionPoolSelectorExplorer $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilSetting();
        $this->addGlobal_ilAccess();
        $this->addGlobal_ilCtrl();

        $this->testObj = new ilTestQuestionPoolSelectorExplorer(
            $this->createMock(ilTestRandomQuestionSetConfigGUI::class),
            '',
            '',
            $this->createMock(ilObjectDataCache::class)
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilRepositorySelectorExplorerGUI::class, $this->testObj);
    }

    /**
     * @dataProvider getAndSetAvailableQuestionPoolsDataProvider
     */
    public function testGetAndSetAvailableQuestionPools(array $IO): void
    {
        $this->assertEquals([], $this->testObj->getAvailableQuestionPools());
        $this->assertNull($this->testObj->setAvailableQuestionPools($IO));
        $this->assertEquals($IO, $this->testObj->getAvailableQuestionPools());
    }

    public function getAndSetAvailableQuestionPoolsDataProvider(): array
    {
        return [
            [[]],
            [[1]],
            [[1, 2]],
            [[1, 2, 3]]
        ];
    }
}