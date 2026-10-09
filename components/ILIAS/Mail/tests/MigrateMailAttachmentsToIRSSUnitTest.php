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

use ILIAS\Setup\Environment;
use ILIAS\Setup\AdminInteraction;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\ResourceStorage\Collection\CollectionBuilder;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\Mail\Setup\Migration\MigrateMailAttachmentsToIRSS;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

class MigrateMailAttachmentsToIRSSUnitTest extends ilMailBaseTestCase
{
    private MigrateMailAttachmentsToIRSS $migration;
    private MockObject&ilResourceStorageMigrationHelper $helper;
    private MockObject&ilDBInterface $db;
    /** @var list<string> */
    private array $messages = [];
    private string $tmp_dir;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('SYSTEM_USER_ID')) {
            define('SYSTEM_USER_ID', 6);
        }

        $this->tmp_dir = sys_get_temp_dir() . '/mail-migration-unit-' . uniqid('', true);
        mkdir($this->tmp_dir . '/mail', 0775, true);

        $this->migration = new MigrateMailAttachmentsToIRSS();
        $this->db = $this->createMock(ilDBInterface::class);
        $this->db->method('quote')->willReturnCallback(static fn(mixed $value): string => "'" . $value . "'");
        $this->db->method('supportsTransactions')->willReturn(true);

        $this->helper = $this->getMockBuilder(ilResourceStorageMigrationHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getDatabase',
                'getClientDataDir',
                'moveFilesOfPathToCollection',
                'movePathToStorage',
                'getCollectionBuilder',
            ])
            ->getMock();
        $this->helper->method('getDatabase')->willReturn($this->db);
        $this->helper->method('getClientDataDir')->willReturn($this->tmp_dir);

        $io = $this->createMock(AdminInteraction::class);
        $io->method('inform')->willReturnCallback(function (string $message): void {
            $this->messages[] = $message;
        });

        $reflection = new ReflectionClass($this->migration);
        $reflection->getProperty('helper')->setValue($this->migration, $this->helper);
        $reflection->getProperty('io')->setValue($this->migration, $io);
    }

    protected function tearDown(): void
    {
        exec('chmod -R u+rwx ' . escapeshellarg($this->tmp_dir) . ' && rm -rf ' . escapeshellarg($this->tmp_dir));
        parent::tearDown();
    }

    // ---------------------------------------------------------------------------------------------------------
    // Order: most recently sent mails first
    // ---------------------------------------------------------------------------------------------------------

    // ---------------------------------------------------------------------------------------------------------
    // Directories
    // ---------------------------------------------------------------------------------------------------------

    // ---------------------------------------------------------------------------------------------------------
    // Drafts
    // ---------------------------------------------------------------------------------------------------------

    public function testPathsOfTheMostRecentlySentMailsAreMigratedFirst(): void
    {
        $captured_sql = '';
        $this->db->expects($this->once())->method('setLimit')->with(25);
        $this->db->method('query')->willReturnCallback(
            function (string $sql) use (&$captured_sql): ilDBStatement {
                $captured_sql = $sql;

                return $this->createStub(ilDBStatement::class);
            }
        );
        $this->db->method('fetchAssoc')->willReturnOnConsecutiveCalls(['path' => 'newest'], ['path' => 'older'], null);

        $paths = $this->invokePrivate('nextUnmigratedPaths', []);

        $this->assertSame(['newest', 'older'], $paths);
        $this->assertStringContainsString('ORDER BY MAX(m.send_time) DESC', $captured_sql);
        $this->assertStringNotContainsString('LIMIT', $captured_sql);
    }

    /**
     * Regression: mails with a not yet migrated directory must never be treated as pool references
     */
    public function testDraftsOfTheMostRecentlySentMailsAreMigratedFirstAndNeverMailsWithDirectory(): void
    {
        $captured_sql = '';
        $this->db->expects($this->once())->method('setLimit')->with(50);
        $this->db->method('query')->willReturnCallback(
            function (string $sql) use (&$captured_sql): ilDBStatement {
                $captured_sql = $sql;

                return $this->createStub(ilDBStatement::class);
            }
        );
        $this->db->method('fetchAssoc')->willReturn(null);

        $this->invokePrivate('migrateSerializedMailAttachments', []);

        $this->assertStringContainsString('LEFT JOIN mail_attachment ma ON ma.mail_id = m.mail_id', $captured_sql);
        $this->assertStringContainsString('WHERE ma.mail_id IS NULL', $captured_sql);
        $this->assertStringContainsString('ORDER BY m.send_time DESC, m.mail_id DESC', $captured_sql);
    }

    public function testDirectoryIsMigratedWithTheIrssHelper(): void
    {
        $dir = $this->createDirectory('6_100', ['a.pdf' => '1234']);
        $this->db->method('query')->willReturn($this->createStub(ilDBStatement::class));
        $this->db->method('queryF')->willReturn($this->createStub(ilDBStatement::class));
        $this->db->method('in')->willReturn('mail_id IN (1,2)');
        $this->db->method('fetchAssoc')->willReturnOnConsecutiveCalls(
            // paths to migrate
            ['path' => '6_100'],
            null,
            // owner, mails of the directory
            ['sender_id' => 6],
            ['mail_id' => 1],
            ['mail_id' => 2],
            null
        );
        $this->helper->expects($this->once())
            ->method('moveFilesOfPathToCollection')
            ->willReturnCallback(function (
                string $path,
                int $resource_owner,
                int $collection_owner,
                ?Closure $file_name,
                Closure $revision_title
            ) use ($dir): ResourceCollectionIdentification {
                $this->assertSame($dir, $path);
                $this->assertSame(6, $resource_owner);
                $this->assertSame(md5('a.pdf'), $revision_title('a.pdf'));

                return new ResourceCollectionIdentification('dir-rcid');
            });
        $statements = [];
        $this->db->method('manipulateF')->willReturnCallback(
            function (string $sql, array $types, array $values) use (&$statements): int {
                $statements[] = [$sql, $values];
                return 1;
            }
        );

        $this->invokePrivate('migrateSentAttachmentDirectories', []);

        $this->assertSame(['UPDATE mail_attachment SET rcid = %s WHERE path = %s', ['dir-rcid', '6_100']], $statements[0]);
        $this->assertSame(['UPDATE mail SET attachments = %s WHERE mail_id IN (1,2)', ['dir-rcid']], $statements[1]);
    }

    public function testMissingDirectoryIsSkippedAndReported(): void
    {
        $this->db->method('query')->willReturn($this->createStub(ilDBStatement::class));
        $this->db->method('queryF')->willReturn($this->createStub(ilDBStatement::class));
        $this->db->method('fetchAssoc')->willReturnOnConsecutiveCalls(
            ['path' => '6_104'],
            null,
            ['mail_id' => 1],
            null
        );
        $this->helper->expects($this->never())->method('moveFilesOfPathToCollection');
        $this->db->expects($this->once())->method('manipulateF')->with(
            'UPDATE mail_attachment SET rcid = %s WHERE path = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['-', '6_104']
        );

        $this->invokePrivate('migrateSentAttachmentDirectories', []);

        $this->assertReported('WARNING', 'does not exist');
    }

    public function testPoolFilesAreCopiedWithTheirNameWithoutTheUserPrefix(): void
    {
        file_put_contents($this->tmp_dir . '/mail/6_Übung 1.pdf', '%PDF');
        $collection = $this->createMock(ResourceCollection::class);
        $collection->expects($this->once())->method('add');
        $collection->method('count')->willReturn(1);
        $collection->method('getIdentification')->willReturn(new ResourceCollectionIdentification('draft-rcid'));
        $collection_builder = $this->createMock(CollectionBuilder::class);
        $collection_builder->method('new')->willReturn($collection);
        $collection_builder->method('store')->willReturn(true);
        $this->helper->method('getCollectionBuilder')->willReturn($collection_builder);
        $this->helper->expects($this->once())
            ->method('movePathToStorage')
            ->willReturnCallback(function (
                string $path,
                int $owner,
                Closure $file_name,
                Closure $revision_title,
                bool $copy
            ): ResourceIdentification {
                $this->assertSame('Übung 1.pdf', $file_name());
                $this->assertSame(md5('Übung 1.pdf'), $revision_title());
                $this->assertTrue($copy, 'Pool files are copied, they stay in the pool');

                return new ResourceIdentification('rid');
            });

        $rcid = $this->invokePrivate(
            'migratePoolFilenamesToCollection',
            [['Übung 1.pdf', 'gone.pdf'], 6, $this->tmp_dir . '/mail', 42]
        );

        $this->assertSame('draft-rcid', $rcid->serialize());
        $this->assertFileExists($this->tmp_dir . '/mail/6_Übung 1.pdf');
        $this->assertReported('WARNING', 'gone.pdf');
    }

    public function testDraftGetsItsCollectionAndAMailAttachmentRow(): void
    {
        file_put_contents($this->tmp_dir . '/mail/6_exists.pdf', '%PDF');
        $collection = $this->createMock(ResourceCollection::class);
        $collection->method('count')->willReturn(1);
        $collection->method('getIdentification')->willReturn(new ResourceCollectionIdentification('draft-rcid'));
        $collection_builder = $this->createMock(CollectionBuilder::class);
        $collection_builder->method('new')->willReturn($collection);
        $collection_builder->method('store')->willReturn(true);
        $this->helper->method('getCollectionBuilder')->willReturn($collection_builder);
        $this->helper->method('movePathToStorage')->willReturn(new ResourceIdentification('rid'));
        $this->db->expects($this->once())->method('manipulateF')->with(
            'INSERT INTO mail_attachment (mail_id, path, rcid) VALUES (%s, %s, %s)',
            $this->anything(),
            [42, '', 'draft-rcid']
        );
        $this->db->expects($this->once())->method('update')->with(
            'mail',
            ['attachments' => [ilDBConstants::T_CLOB, 'draft-rcid']],
            ['mail_id' => [ilDBConstants::T_INTEGER, 42]]
        );

        $this->invokePrivate('migrateSerializedMail', [42, 6, serialize(['exists.pdf']), $this->tmp_dir . '/mail']);
    }

    public function testBrokenDraftValueIsClearedAndReported(): void
    {
        $this->db->expects($this->once())->method('update')->with(
            'mail',
            ['attachments' => [ilDBConstants::T_CLOB, '']],
            ['mail_id' => [ilDBConstants::T_INTEGER, 43]]
        );

        $this->invokePrivate('migrateSerializedMail', [43, 6, 'a:1:{broken', $this->tmp_dir . '/mail']);

        $this->assertReported('WARNING', 'could not be read');
    }

    // ---------------------------------------------------------------------------------------------------------
    // Repair, abort, reporting
    // ---------------------------------------------------------------------------------------------------------

    public function testRepairRestoresEmptiedAttachmentColumns(): void
    {
        $this->db->expects($this->once())->method('setLimit')->with(100);
        $this->db->method('query')->willReturn($this->createStub(ilDBStatement::class));
        $this->db->method('fetchAssoc')->willReturnOnConsecutiveCalls(['mail_id' => 7, 'rcid' => 'dir-rcid'], null);
        $this->db->expects($this->once())->method('update')->with(
            'mail',
            ['attachments' => [ilDBConstants::T_CLOB, 'dir-rcid']],
            ['mail_id' => [ilDBConstants::T_INTEGER, 7]]
        );

        $this->invokePrivate('repairAttachmentColumnsOfMigratedDirectories', []);
        // nothing left: the repair query is not executed again
        $this->invokePrivate('repairAttachmentColumnsOfMigratedDirectories', []);
    }

    public function testAbortIsReportedWithItsCauseAndRethrown(): void
    {
        $this->db->method('query')->willThrowException(new RuntimeException('MySQL server has gone away'));

        try {
            $this->migration->step($this->createStub(Environment::class));
            $this->fail('The exception must be rethrown');
        } catch (RuntimeException $e) {
            $this->assertSame('MySQL server has gone away', $e->getMessage());
        }

        $this->assertReported('ERROR', 'MySQL server has gone away');
    }

    public function testResolveOwnerIdForPathUsesSenderIdAndFallsBackToSystemUser(): void
    {
        $this->db->method('queryF')->willReturn($this->createStub(ilDBStatement::class));
        $this->db->method('fetchAssoc')->willReturnOnConsecutiveCalls(['sender_id' => 42], ['sender_id' => 0]);

        $this->assertSame(42, $this->invokePrivate('resolveOwnerIdForPath', ['6_1']));
        $this->assertSame(SYSTEM_USER_ID, $this->invokePrivate('resolveOwnerIdForPath', ['6_2']));
    }

    // ---------------------------------------------------------------------------------------------------------

    /**
     * @param array<string, string> $files
     */
    private function createDirectory(string $relative_path, array $files): string
    {
        $dir = $this->tmp_dir . '/mail/' . $relative_path;
        mkdir($dir, 0775, true);
        foreach ($files as $name => $content) {
            file_put_contents($dir . '/' . $name, $content);
        }

        return $dir;
    }

    private function assertReported(string $level, string $needle): void
    {
        foreach ($this->messages as $message) {
            if (str_contains($message, $level) && str_contains($message, $needle)) {
                $this->assertMatchesRegularExpression('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $message);

                return;
            }
        }

        $this->fail(sprintf('No %s containing "%s" reported, got: %s', $level, $needle, implode("\n", $this->messages)));
    }

    /**
     * @param list<mixed> $arguments
     */
    private function invokePrivate(string $method, array $arguments): mixed
    {
        return new ReflectionClass($this->migration)->getMethod($method)->invoke($this->migration, ...$arguments);
    }
}
