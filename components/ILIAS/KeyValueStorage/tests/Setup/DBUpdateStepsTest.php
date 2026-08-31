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

namespace ILIAS\Tests\KeyValueStorage\Setup;

use ILIAS\KeyValueStorage\Internal\DatabaseRepository;
use ILIAS\KeyValueStorage\Setup\DBUpdateSteps;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DBUpdateStepsTest extends TestCase
{
    private \ilDBInterface&MockObject $db;

    private DBUpdateSteps $steps;

    protected function setUp(): void
    {
        $this->db = $this->createMock(\ilDBInterface::class);
        $this->steps = new DBUpdateSteps();
        $this->steps->prepare($this->db);
    }

    public function testTheStepCreatesTheTableTheRepositoryReadsFrom(): void
    {
        $this->db->expects($this->once())->method('tableExists')
            ->with(DatabaseRepository::TABLE)
            ->willReturn(false);

        $this->db->expects($this->once())->method('createTable')
            ->with(DatabaseRepository::TABLE, [
                'namespace' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 128,
                    'notnull' => true,
                ],
                'keyword' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 255,
                    'notnull' => true,
                ],
                'value' => [
                    'type' => \ilDBConstants::T_CLOB,
                    'notnull' => false,
                ],
            ]);

        $this->db->expects($this->once())->method('addPrimaryKey')
            ->with(DatabaseRepository::TABLE, ['namespace', 'keyword']);

        $this->steps->step_1();
    }

    public function testTheStepDoesNothingWhenTheTableIsAlreadyThere(): void
    {
        $this->db->expects($this->once())->method('tableExists')->willReturn(true);
        $this->db->expects($this->never())->method('createTable');
        $this->db->expects($this->never())->method('addPrimaryKey');

        $this->steps->step_1();
    }

    public function testTheColumnsAreWideEnoughForWhatTheValidationAllows(): void
    {
        $columns = [];
        $this->db->expects($this->once())->method('tableExists')->willReturn(false);
        $this->db->expects($this->once())->method('createTable')->willReturnCallback(
            function (string $table, array $fields) use (&$columns): bool {
                $columns = $fields;

                return true;
            }
        );

        $this->steps->step_1();

        $this->assertSame(
            \ILIAS\KeyValueStorage\StorageNamespace::MAX_LENGTH,
            $columns['namespace']['length']
        );
        $this->assertSame(
            \ILIAS\KeyValueStorage\Internal\KeyRules::MAX_LENGTH,
            $columns['keyword']['length']
        );
    }
}
