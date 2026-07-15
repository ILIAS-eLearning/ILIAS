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

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('SYSTEM_USER_ID')) {
            define('SYSTEM_USER_ID', 6);
        }

        $this->migration = new MigrateMailAttachmentsToIRSS();
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
        $this->db = $this->createMock(ilDBInterface::class);

        $this->helper->method('getDatabase')->willReturn($this->db);
        $this->helper->method('getClientDataDir')->willReturn('/tmp/client');

        $reflection = new ReflectionClass($this->migration);
        $property = $reflection->getProperty('helper');
        $property->setValue($this->migration, $this->helper);
    }

    public function testResolveOwnerIdForPathUsesSenderId(): void
    {
        $statement = $this->createMock(ilDBStatement::class);
        $this->db->expects($this->once())
            ->method('queryF')
            ->willReturn($statement);
        $this->db->expects($this->once())
            ->method('fetchObject')
            ->with($statement)
            ->willReturn((object) ['sender_id' => 42]);

        $owner_id = $this->invokePrivate('resolveOwnerIdForPath', ['migtest/s1']);

        $this->assertSame(42, $owner_id);
    }

    public function testResolveOwnerIdForPathFallsBackToSystemUser(): void
    {
        $statement = $this->createMock(ilDBStatement::class);
        $this->db->method('queryF')->willReturn($statement);
        $this->db->method('fetchObject')->willReturn((object) ['sender_id' => 0]);

        $owner_id = $this->invokePrivate('resolveOwnerIdForPath', ['migtest/s5']);

        $this->assertSame(SYSTEM_USER_ID, $owner_id);
    }

    public function testMigratePoolFilenamesToCollectionSkipsMissingFiles(): void
    {
        $dir = sys_get_temp_dir() . '/mail-migration-unit-' . uniqid('', true);
        $mail_path = $dir . '/mail';
        mkdir($mail_path, 0775, true);
        file_put_contents($mail_path . '/6_present.svg', '<svg/>');

        $this->helper->method('getClientDataDir')->willReturn($dir);

        $collection = $this->createMock(ResourceCollection::class);
        $collection->method('count')->willReturn(1);
        $collection->expects($this->once())->method('add');
        $collection->method('getIdentification')->willReturn(
            new ResourceCollectionIdentification('migrated-rcid')
        );

        $collection_builder = $this->createMock(CollectionBuilder::class);
        $collection_builder->method('new')->willReturn($collection);
        $collection_builder->method('store')->willReturn(true);

        $this->helper->method('getCollectionBuilder')->willReturn($collection_builder);
        $this->helper->expects($this->once())
            ->method('movePathToStorage')
            ->willReturn(new ResourceIdentification('rid-present'));

        $rcid = $this->invokePrivate('migratePoolFilenamesToCollection', [
            ['present.svg', 'missing.svg'],
            6,
            $mail_path,
        ]);

        $this->assertInstanceOf(ResourceCollectionIdentification::class, $rcid);
        $this->assertSame('migrated-rcid', $rcid->serialize());

        unlink($mail_path . '/6_present.svg');
        rmdir($mail_path);
        rmdir($dir);
    }

    public function testMigratePoolFilenamesToCollectionReturnsNullWhenNoFilesExist(): void
    {
        $dir = sys_get_temp_dir() . '/mail-migration-unit-empty-' . uniqid('', true);
        $mail_path = $dir . '/mail';
        mkdir($mail_path, 0775, true);

        $collection = $this->createMock(ResourceCollection::class);
        $collection->method('count')->willReturn(0);

        $collection_builder = $this->createMock(CollectionBuilder::class);
        $collection_builder->method('new')->willReturn($collection);

        $this->helper->method('getCollectionBuilder')->willReturn($collection_builder);
        $this->helper->expects($this->never())->method('movePathToStorage');

        $rcid = $this->invokePrivate('migratePoolFilenamesToCollection', [
            ['missing.svg'],
            6,
            $mail_path,
        ]);

        $this->assertNull($rcid);

        rmdir($mail_path);
        rmdir($dir);
    }

    public function testUpdateMailAttachmentFieldsSkipsNonSerializedAttachments(): void
    {
        $statement = $this->createMock(ilDBStatement::class);
        $this->db->expects($this->once())->method('queryF')->willReturn($statement);
        $this->db->method('fetchObject')->willReturnOnConsecutiveCalls(
            (object) ['mail_id' => 900011, 'attachments' => '657497dc-5079-4f95-b19d-aecdaf81ff1a'],
            null
        );
        $this->db->expects($this->never())->method('update');

        $this->invokePrivate('updateMailAttachmentFields', [
            'migtest/s11',
            new ResourceCollectionIdentification('new-rcid'),
        ]);
    }

    public function testMarkPathAsSkippedWritesDashMarker(): void
    {
        $this->db->expects($this->once())
            ->method('manipulateF')
            ->with(
                'UPDATE mail_attachment SET rcid = %s WHERE path = %s',
                [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
                ['-', 'migtest/missing']
            );

        $this->invokePrivate('markPathAsSkipped', ['migtest/missing']);
    }

    /**
     * @param list<mixed> $arguments
     */
    private function invokePrivate(string $method, array $arguments): mixed
    {
        $reflection = new ReflectionClass($this->migration);

        return $reflection->getMethod($method)->invoke($this->migration, ...$arguments);
    }
}
