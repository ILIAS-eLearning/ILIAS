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

namespace ILIAS\MetaData\Copyright;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\Data\URI;
use ILIAS\MetaData\Elements\Data\Data;

class DatabaseRepositoryTest extends TestCase
{
    protected const ROW_DEFAULT = [
        'entry_id' => 25,
        'title' => 'entry default',
        'description' => 'description default',
        'is_default' => 1,
        'outdated' => 0,
        'position' => 0,
        'full_name' => 'full name default',
        'link' => 'link default',
        'image_link' => 'image link default',
        'image_file' => '',
        'alt_text' => 'alt text default'
    ];

    protected const ROW_1 = [
        'entry_id' => 5,
        'title' => 'entry 1',
        'description' => 'description 1',
        'is_default' => 0,
        'outdated' => 1,
        'position' => 1,
        'full_name' => 'full name 1',
        'link' => 'link 1',
        'image_link' => 'image link 1',
        'image_file' => '',
        'alt_text' => 'alt text 1'
    ];

    protected const ROW_2 = [
        'entry_id' => 67,
        'title' => 'entry 2',
        'description' => '',
        'is_default' => 0,
        'outdated' => 0,
        'position' => 0,
        'full_name' => 'full name 2',
        'link' => '',
        'image_link' => '',
        'image_file' => 'image file 2',
        'alt_text' => 'alt text 2'
    ];

    protected function getMockDB(): \ilDBInterface|MockObject
    {
        $db = $this->createMock(\ilDBInterface::class);
        $db->method('quote')->willReturnCallback(fn($v) => (string) $v);
        return $db;
    }

    protected function getMockURI(): URI|MockObject
    {
        return $this->createMock(URI::class);
    }

    protected function getRepo(\ilDBInterface $db): DatabaseRepository
    {
        $uri = $this->getMockURI();

        return new class ($db, $uri) extends DatabaseRepository {
            public function __construct(
                \ilDBInterface $db,
                protected URI|MockObject $uri
            ) {
                parent::__construct($db);
            }

            protected function getURI(string $uri): URI
            {
                $clone = clone $this->uri;
                $clone->method('__toString')->willReturn($uri);
                return $clone;
            }
        };
    }

    public function testGetEntry(): void
    {
        $db = $this->getMockDB();
        $db->expects($this->once())
           ->method('query')
           ->with('SELECT * FROM il_md_cpr_selections WHERE entry_id = 5');
        $db->expects($this->once())
           ->method('fetchAssoc')
           ->willReturn(self::ROW_1);

        $repo = $this->getRepo($db);

        $entry = $repo->getEntry(5);
        $this->assertSame(self::ROW_1['entry_id'], $entry->id());
        $this->assertSame(self::ROW_1['title'], $entry->title());
        $this->assertSame(self::ROW_1['description'], $entry->description());
        $this->assertSame((bool) self::ROW_1['is_default'], $entry->isDefault());
        $this->assertSame((bool) self::ROW_1['outdated'], $entry->isOutdated());
        $this->assertSame(self::ROW_1['position'], $entry->position());
        $this->assertSame(self::ROW_1['full_name'], $entry->copyrightData()->fullName());
        $this->assertSame(self::ROW_1['link'], (string) $entry->copyrightData()->link());
        $this->assertSame(self::ROW_1['image_link'], (string) $entry->copyrightData()->imageLink());
        $this->assertSame(self::ROW_1['image_file'], $entry->copyrightData()->imageFile());
        $this->assertSame(self::ROW_1['alt_text'], $entry->copyrightData()->altText());
        $this->assertSame((bool) self::ROW_1['is_default'], $entry->copyrightData()->fallBackToDefaultImage());
    }

    public function testGetEntryNoLinks(): void
    {
        $db = $this->getMockDB();
        $db->expects($this->once())
           ->method('query')
           ->with('SELECT * FROM il_md_cpr_selections WHERE entry_id = 67');
        $db->expects($this->once())
           ->method('fetchAssoc')
           ->willReturn(self::ROW_2);

        $repo = $this->getRepo($db);

        $entry = $repo->getEntry(67);
        $this->assertNull($entry->copyrightData()->link());
        $this->assertNull($entry->copyrightData()->imageLink());
    }

    public function testGetEntryNoneFound(): void
    {
        $db = $this->getMockDB();
        $db->expects($this->once())
           ->method('query')
           ->with('SELECT * FROM il_md_cpr_selections WHERE entry_id = 5');
        $db->expects($this->once())
           ->method('fetchAssoc')
           ->willReturn(null);

        $repo = $this->getRepo($db);

        $this->assertEquals(new NullEntry(), $repo->getEntry(5));
    }

    public function testGetDefaultEntry(): void
    {
        $db = $this->getMockDB();
        $db->expects($this->once())
           ->method('query')
           ->with('SELECT * FROM il_md_cpr_selections WHERE is_default = 1');
        $db->expects($this->once())
           ->method('fetchAssoc')
           ->willReturn(self::ROW_DEFAULT);

        $repo = $this->getRepo($db);
        $this->assertInstanceOf(EntryInterface::class, $repo->getDefaultEntry());
    }

    public function testGetAllEntries(): void
    {
        $db = $this->getMockDB();
        $db->expects($this->once())
           ->method('query')
           ->with(
               'SELECT * FROM il_md_cpr_selections
            ORDER BY is_default DESC, position ASC'
           );
        $four_times = $this->exactly(4);
        $db->expects($four_times)
           ->method('fetchAssoc')
           ->willReturnCallback(function () use ($four_times) {
               return match ($four_times->getInvocationCount()) {
                   1 => self::ROW_DEFAULT,
                   2 => self::ROW_2,
                   3 => self::ROW_1,
                   4 => null
               };
           });

        $repo = $this->getRepo($db);

        $res = $repo->getAllEntries();
        $this->assertInstanceOf(EntryInterface::class, $res->current());
        $res->next();
        $this->assertInstanceOf(EntryInterface::class, $res->current());
        $res->next();
        $this->assertInstanceOf(EntryInterface::class, $res->current());
        $res->next();
        $this->assertNull($res->current());
    }

    public function testGetActiveEntries(): void
    {
        $db = $this->getMockDB();
        $db->expects($this->once())
           ->method('query')
           ->with(
               'SELECT * FROM il_md_cpr_selections
            ORDER BY is_default DESC, position ASC'
           );
        $thrice = $this->exactly(3);
        $db->expects($thrice)
           ->method('fetchAssoc')
           ->willReturnCallback(function () use ($thrice) {
               return match ($thrice->getInvocationCount()) {
                   1 => self::ROW_DEFAULT,
                   2 => self::ROW_2,
                   3 => null
               };
           });

        $repo = $this->getRepo($db);

        $res = $repo->getAllEntries();
        $this->assertInstanceOf(EntryInterface::class, $res->current());
        $res->next();
        $this->assertInstanceOf(EntryInterface::class, $res->current());
        $res->next();
        $this->assertNull($res->current());
    }

    public function testDeleteEntry(): void
    {
        $db = $this->getMockDB();
        $db->expects($this->once())
           ->method('manipulate')
           ->with('DELETE FROM il_md_cpr_selections WHERE entry_id = 5');

        $repo = $this->getRepo($db);

        $repo->deleteEntry(5);
    }

    public function testCreateWithLinkImage(): void
    {
        $db = $this->getMockDB();
        $db->expects($this->once())
           ->method('query')
           ->with(
               'SELECT MAX(position) AS max FROM il_md_cpr_selections WHERE is_default = 0'
           );
        $db->expects($this->once())
           ->method('fetchAssoc')
           ->willReturn(['max' => 5]);
        $db->expects($this->once())
           ->method('nextId')
           ->with('il_md_cpr_selections')
           ->willReturn(123);
        $db->expects($this->once())
           ->method('insert')
           ->with(
               'il_md_cpr_selections',
               [
                   'entry_id' => [\ilDBConstants::T_INTEGER, 123],
                   'title' => [\ilDBConstants::T_TEXT, 'new title'],
                   'description' => [\ilDBConstants::T_TEXT, 'new description'],
                   'is_default' => [\ilDBConstants::T_INTEGER, 0],
                   'outdated' => [\ilDBConstants::T_INTEGER, 1],
                   'position' => [\ilDBConstants::T_INTEGER, 6],
                   'full_name' => [\ilDBConstants::T_TEXT, 'new full name'],
                   'link' => [\ilDBConstants::T_TEXT, 'new link'],
                   'image_link' => [\ilDBConstants::T_TEXT, 'new image link'],
                   'image_file' => [\ilDBConstants::T_TEXT, ''],
                   'alt_text' => [\ilDBConstants::T_TEXT, 'new alt text'],
                   'migrated' => [\ilDBConstants::T_INTEGER, 1]
               ]
           );
        $uri = $this->getMockURI();
        $uri->method('__toString')->willReturn('new link');
        $img_uri = $this->getMockURI();
        $img_uri->method('__toString')->willReturn('new image link');

        $repo = $this->getRepo($db);
        $repo->createEntry(
            'new title',
            'new description',
            true,
            'new full name',
            $uri,
            $img_uri,
            'new alt text'
        );
    }

    public function testCreateWithFileImage(): void
    {
        $db = $this->getMockDB();
        $db->expects($this->once())
           ->method('query')
           ->with(
               'SELECT MAX(position) AS max FROM il_md_cpr_selections WHERE is_default = 0'
           );
        $db->expects($this->once())
           ->method('fetchAssoc')
           ->willReturn(['max' => 5]);
        $db->expects($this->once())
           ->method('nextId')
           ->with('il_md_cpr_selections')
           ->willReturn(123);
        $db->expects($this->once())
           ->method('insert')
           ->with(
               'il_md_cpr_selections',
               [
                   'entry_id' => [\ilDBConstants::T_INTEGER, 123],
                   'title' => [\ilDBConstants::T_TEXT, 'new title'],
                   'description' => [\ilDBConstants::T_TEXT, 'new description'],
                   'is_default' => [\ilDBConstants::T_INTEGER, 0],
                   'outdated' => [\ilDBConstants::T_INTEGER, 0],
                   'position' => [\ilDBConstants::T_INTEGER, 6],
                   'full_name' => [\ilDBConstants::T_TEXT, 'new full name'],
                   'link' => [\ilDBConstants::T_TEXT, ''],
                   'image_link' => [\ilDBConstants::T_TEXT, ''],
                   'image_file' => [\ilDBConstants::T_TEXT, 'new image file'],
                   'alt_text' => [\ilDBConstants::T_TEXT, 'new alt text'],
                   'migrated' => [\ilDBConstants::T_INTEGER, 1]
               ]
           );
        $uri = $this->getMockURI();
        $uri->method('__toString')->willReturn('new link');

        $repo = $this->getRepo($db);
        $repo->createEntry(
            'new title',
            'new description',
            false,
            'new full name',
            null,
            'new image file',
            'new alt text'
        );
    }

    public function testCreateEmptyTitleException(): void
    {
        $db = $this->createMock(\ilDBInterface::class);
        $repo = $this->getRepo($db);

        $this->expectException(\ilMDCopyrightException::class);
        $repo->createEntry('');
    }

    public function testUpdateWithLinkImage(): void
    {
        $db = $this->getMockDB();
        $db->expects($this->once())
           ->method('update')
           ->with(
               'il_md_cpr_selections',
               [
                   'title' => [\ilDBConstants::T_TEXT, 'new title'],
                   'description' => [\ilDBConstants::T_TEXT, 'new description'],
                   'outdated' => [\ilDBConstants::T_INTEGER, 1],
                   'full_name' => [\ilDBConstants::T_TEXT, 'new full name'],
                   'link' => [\ilDBConstants::T_TEXT, 'new link'],
                   'image_link' => [\ilDBConstants::T_TEXT, 'new image link'],
                   'image_file' => [\ilDBConstants::T_TEXT, ''],
                   'alt_text' => [\ilDBConstants::T_TEXT, 'new alt text']
               ],
               [
                   'entry_id' => [\ilDBConstants::T_INTEGER, 9]
               ]
           );
        $uri = $this->getMockURI();
        $uri->method('__toString')->willReturn('new link');
        $img_uri = $this->getMockURI();
        $img_uri->method('__toString')->willReturn('new image link');

        $repo = $this->getRepo($db);
        $repo->updateEntry(
            9,
            'new title',
            'new description',
            true,
            'new full name',
            $uri,
            $img_uri,
            'new alt text'
        );
    }

    public function testUpdateWithFileImage(): void
    {
        $db = $this->getMockDB();
        $db->expects($this->once())
           ->method('update')
           ->with(
               'il_md_cpr_selections',
               [
                   'title' => [\ilDBConstants::T_TEXT, 'new title'],
                   'description' => [\ilDBConstants::T_TEXT, 'new description'],
                   'outdated' => [\ilDBConstants::T_INTEGER, 0],
                   'full_name' => [\ilDBConstants::T_TEXT, 'new full name'],
                   'link' => [\ilDBConstants::T_TEXT, 'new link'],
                   'image_link' => [\ilDBConstants::T_TEXT, ''],
                   'image_file' => [\ilDBConstants::T_TEXT, 'new image file'],
                   'alt_text' => [\ilDBConstants::T_TEXT, 'new alt text']
               ],
               [
                   'entry_id' => [\ilDBConstants::T_INTEGER, 9]
               ]
           );
        $uri = $this->getMockURI();
        $uri->method('__toString')->willReturn('new link');
        $img_uri = $this->getMockURI();
        $img_uri->method('__toString')->willReturn('new image link');

        $repo = $this->getRepo($db);
        $repo->updateEntry(
            9,
            'new title',
            'new description',
            false,
            'new full name',
            $uri,
            'new image file',
            'new alt text'
        );
    }

    public function testUpdateEmptyTitleException(): void
    {
        $db = $this->createMock(\ilDBInterface::class);
        $repo = $this->getRepo($db);

        $this->expectException(\ilMDCopyrightException::class);
        $repo->updateEntry(21, '');
    }

    public function testReorderEntries(): void
    {
        $exp1 = [
            'il_md_cpr_selections',
            ['position' => [\ilDBConstants::T_INTEGER, 0]],
            ['entry_id' => [\ilDBConstants::T_INTEGER, 7]]
        ];
        $exp2 = [
            'il_md_cpr_selections',
            ['position' => [\ilDBConstants::T_INTEGER, 1]],
            ['entry_id' => [\ilDBConstants::T_INTEGER, 99]]
        ];
        $exp3 = [
            'il_md_cpr_selections',
            ['position' => [\ilDBConstants::T_INTEGER, 2]],
            ['entry_id' => [\ilDBConstants::T_INTEGER, 1]]
        ];

        $db = $this->getMockDB();
        $db->expects($this->once())
           ->method('query')
           ->with(
               'SELECT entry_id FROM il_md_cpr_selections WHERE is_default = 1'
           );
        $db->expects($this->once())
           ->method('fetchAssoc')
           ->willReturn(['entry_id' => 5]);
        $thrice = $this->exactly(3);
        $db->expects($thrice)
           ->method('update')
           ->willReturnCallback(function (string $table_name, array $values, array $where) use ($thrice) {
               $args = [$table_name, $values, $where];
               $exp1 = [
                   'il_md_cpr_selections',
                   ['position' => [\ilDBConstants::T_INTEGER, 0]],
                   ['entry_id' => [\ilDBConstants::T_INTEGER, 7]]
               ];
               $exp2 = [
                   'il_md_cpr_selections',
                   ['position' => [\ilDBConstants::T_INTEGER, 1]],
                   ['entry_id' => [\ilDBConstants::T_INTEGER, 99]]
               ];
               $exp3 = [
                   'il_md_cpr_selections',
                   ['position' => [\ilDBConstants::T_INTEGER, 2]],
                   ['entry_id' => [\ilDBConstants::T_INTEGER, 1]]
               ];
               match ($thrice->getInvocationCount()) {
                   1 => $this->assertEquals($exp1, $args),
                   2 => $this->assertEquals($exp2, $args),
                   3 => $this->assertEquals($exp3, $args)
               };
               return 1;
           });

        $repo = $this->getRepo($db);
        $repo->reorderEntries(7, 5, 99, 1);
    }
}
