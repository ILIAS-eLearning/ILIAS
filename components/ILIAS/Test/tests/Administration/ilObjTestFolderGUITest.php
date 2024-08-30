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

namespace Administration;

use ilDBInterface;
use ilDBStatement;
use ilObjectDefinition;
use ilObjTestFolderGUI;
use ilRbacSystem;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;

class ilObjTestFolderGUITest extends ilTestBaseTestCase
{
    /**
     * @throws \Exception|Exception
     */
    public function testConstruct(): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $ilDBStatement = $this->createMock(ilDBStatement::class);
            $ilDBStatement->method('numRows')->willReturn(1);
            $ilDBStatement->method('fetchRow')->willReturnOnConsecutiveCalls(
                (object) ['type' => 'xxx', 'obj_id' => 1, 'log_level' => '1', 'component_id' => 1],
                null
            );

            $array = [
                'id' => 1,
                'type' => 'xxx',
                'obj_id' => 1,
                'title' => 'test',
                'description' => 'test',
                'owner' => 1,
                'create_date' => 1,
                'last_update' => 1,
                'import_id' => 1,
                'keyword' => '',
                'value' => ''
            ];

            $mock
                ->method('fetchAssoc')
                ->willReturnOnConsecutiveCalls($array, null);

            $mock
                ->method('query')
                ->willReturn($ilDBStatement);
        });

        $this->adaptDICServiceMock(ilObjectDefinition::class, function (ilObjectDefinition|MockObject $mock) {
            $mock
                ->method('getClassName')
                ->willReturn('TestFolder');
        });

        $this->adaptDICServiceMock(ilRbacSystem::class, function (ilRbacSystem|MockObject $mock) {
            $mock
                ->method('checkAccess')
                ->willReturn(true);
        });

        $il_obj_test_folder_gui = $this->createInstanceOf(ilObjTestFolderGUI::class, ['a_data' => ['data']]);
        $this->assertInstanceOf(ilObjTestFolderGUI::class, $il_obj_test_folder_gui);
    }
}
