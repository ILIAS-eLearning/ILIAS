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

namespace PageEditor;

use ilTestBaseTestCase;
use ilTestPage;

class ilTestPageTest extends ilTestBaseTestCase
{
    private ilTestPage $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilUser();

        $this->testObj = new ilTestPage();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestPage::class, $this->testObj);
    }

    public function testGetParentType(): void
    {
        $this->assertEquals('tst', $this->testObj->getParentType());
    }

    /**
     * @dataProvider createPageWithNextIdDataProvider
     */
    public function testCreatePageWithNextId(int $IO): void
    {
        $ilTestPageReflection = new \ReflectionClass(ilTestPage::class);
        $property = $ilTestPageReflection->getProperty('db');
        $property->setValue($this->testObj, $this->createConfiguredMock(\ilDBInterface::class, [
            'query' => $this->createConfiguredMock(\ilDBStatement::class, [
                'fetchAssoc' => [
                    'last_id' => $IO
                ],
            ]),
        ]));

        $this->assertEquals($IO, $this->testObj->createPageWithNextId());
    }

    public function createPageWithNextIdDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }
}