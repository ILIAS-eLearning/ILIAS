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
use ILIAS\KeyValueStorage\Internal\KeyRules;
use ILIAS\KeyValueStorage\Internal\StorageNamespace;
use ILIAS\KeyValueStorage\Setup\DBUpdateSteps;
use ILIAS\KeyValueStorage\Subject\SubjectId;
use ILIAS\KeyValueStorage\Subject\SubjectProvider;
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
                'provider' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true,
                    'default' => '',
                ],
                'subject' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 128,
                    'notnull' => true,
                    'default' => '',
                ],
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
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 4000,
                    'notnull' => false,
                ],
            ]);

        $this->db->expects($this->once())->method('addPrimaryKey')
            ->with(DatabaseRepository::TABLE, ['provider', 'subject', 'namespace', 'keyword']);

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

        $this->assertSame(StorageNamespace::MAX_LENGTH, $columns['namespace']['length']);
        $this->assertSame(KeyRules::MAX_LENGTH, $columns['keyword']['length']);
        $this->assertSame(SubjectProvider::MAX_NAME_LENGTH, $columns['provider']['length']);
        $this->assertSame(SubjectId::MAX_LENGTH, $columns['subject']['length']);
        $this->assertSame(DatabaseRepository::MAX_VALUE_LENGTH, $columns['value']['length']);
        $this->assertSame(\ilDBConstants::T_TEXT, $columns['value']['type']);

        $indexBytes = (
            $columns['provider']['length']
            + $columns['subject']['length']
            + $columns['namespace']['length']
            + $columns['keyword']['length']
        ) * DBUpdateSteps::UTF8MB4_BYTES_PER_CHARACTER;
        $this->assertSame(2300, $indexBytes);
        $this->assertLessThanOrEqual(DBUpdateSteps::INNODB_INDEX_LIMIT_BYTES, $indexBytes);
    }
}
