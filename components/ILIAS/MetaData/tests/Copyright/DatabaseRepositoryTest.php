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
use ILIAS\MetaData\Copyright\Database\NullWrapper;
use ILIAS\MetaData\Copyright\Database\WrapperInterface;

class DatabaseRepositoryTest extends TestCase
{
    protected const NEXT_ID = 123;

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

    protected function getDBWrapper(array ...$query_results): WrapperInterface
    {
        return new class (self::NEXT_ID, $query_results) extends NullWrapper {
            public array $exposed_tables;
            public array $exposed_queries;
            public array $exposed_manipulates;
            public array $exposed_values;
            public array $exposed_wheres;

            public function __construct(
                protected int $next_id,
                protected array $query_results
            ) {
            }

            public function nextID(string $table): int
            {
                $this->exposed_tables[] = $table;
                return $this->next_id;
            }

            public function quoteInteger(int $integer): string
            {
                return '~' . $integer . '~';
            }

            public function query(string $query): \Generator
            {
                $this->exposed_queries[] = $query;
                yield from $this->query_results;
            }

            public function manipulate(string $query): void
            {
                $this->exposed_manipulates[] = $query;
            }

            public function update(string $table, array $values, array $where): void
            {
                $this->exposed_tables[] = $table;
                $this->exposed_values[] = $values;
                $this->exposed_wheres[] = $where;
            }

            public function insert(string $table, array $values): void
            {
                $this->exposed_tables[] = $table;
                $this->exposed_values[] = $values;
            }
        };
    }

    protected function getMockURI(): URI|MockObject
    {
        return $this->createMock(URI::class);
    }

    protected function getRepo(WrapperInterface $wrapper): DatabaseRepository
    {
        $uri = $this->getMockURI();

        return new class ($wrapper, $uri) extends DatabaseRepository {
            public function __construct(
                WrapperInterface $wrapper,
                protected URI|MockObject $uri
            ) {
                parent::__construct($wrapper);
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
        $db = $this->getDBWrapper(self::ROW_1);
        $repo = $this->getRepo($db);

        $entry = $repo->getEntry(5);
        $this->assertSame(
            ['SELECT * ' . 'FROM il_md_cpr_selections WHERE entry_id = ~5~'],
            $db->exposed_queries
        );
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
        $db = $this->getDBWrapper(self::ROW_2);
        $repo = $this->getRepo($db);

        $entry = $repo->getEntry(67);
        $this->assertSame(
            ['SELECT * ' . 'FROM il_md_cpr_selections WHERE entry_id = ~67~'],
            $db->exposed_queries
        );
        $this->assertNull($entry->copyrightData()->link());
        $this->assertNull($entry->copyrightData()->imageLink());
    }

    public function testGetEntryNoneFound(): void
    {
        $db = $this->getDBWrapper();
        $repo = $this->getRepo($db);

        $this->assertEquals(new NullEntry(), $repo->getEntry(5));
        $this->assertSame(
            ['SELECT * ' . 'FROM il_md_cpr_selections WHERE entry_id = ~5~'],
            $db->exposed_queries
        );
    }

    public function testGetDefaultEntry(): void
    {
        $db = $this->getDBWrapper(self::ROW_DEFAULT);
        $repo = $this->getRepo($db);

        $this->assertInstanceOf(EntryInterface::class, $repo->getDefaultEntry());
        $this->assertSame(
            ['SELECT * FROM il_md_cpr_selections WHERE is_default = 1'],
            $db->exposed_queries
        );
    }

    public function testGetAllEntries(): void
    {
        $db = $this->getDBWrapper(
            self::ROW_DEFAULT,
            self::ROW_2,
            self::ROW_1
        );
        $repo = $this->getRepo($db);

        $res = iterator_to_array($repo->getAllEntries());
        $this->assertSame(
            ['SELECT * FROM il_md_cpr_selections
            ORDER BY is_default DESC, position ASC'],
            $db->exposed_queries
        );
        $this->assertCount(3, $res);
        $this->assertInstanceOf(EntryInterface::class, $res[0]);
        $this->assertInstanceOf(EntryInterface::class, $res[1]);
        $this->assertInstanceOf(EntryInterface::class, $res[2]);
    }

    public function testGetActiveEntries(): void
    {
        $db = $this->getDBWrapper(
            self::ROW_DEFAULT,
            self::ROW_2
        );
        $repo = $this->getRepo($db);

        $res = iterator_to_array($repo->getActiveEntries());
        $this->assertSame(
            ['SELECT * FROM il_md_cpr_selections WHERE outdated = 0
            ORDER BY is_default DESC, position ASC'],
            $db->exposed_queries
        );
        $this->assertCount(2, $res);
        $this->assertInstanceOf(EntryInterface::class, $res[0]);
        $this->assertInstanceOf(EntryInterface::class, $res[1]);
    }

    public function testDeleteEntry(): void
    {
        $db = $this->getDBWrapper();
        $repo = $this->getRepo($db);

        $repo->deleteEntry(5);
        $this->assertSame(
            ['DELETE ' . 'FROM il_md_cpr_selections WHERE entry_id = ~5~'],
            $db->exposed_manipulates
        );
    }

    public function testCreateWithLinkImage(): void
    {
        $db = $this->getDBWrapper(['max' => 5]);
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
        $this->assertSame(
            ['SELECT MAX(position) AS max FROM il_md_cpr_selections WHERE is_default = 0'],
            $db->exposed_queries
        );
        $this->assertSame(
            [
                'il_md_cpr_selections',
                'il_md_cpr_selections'
            ],
            $db->exposed_tables
        );
        $this->assertSame(
            [[
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
            ]],
            $db->exposed_values
        );
    }

    public function testCreateWithFileImage(): void
    {
        $db = $this->getDBWrapper(['max' => 5]);
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
        $this->assertSame(
            ['SELECT MAX(position) AS max FROM il_md_cpr_selections WHERE is_default = 0'],
            $db->exposed_queries
        );
        $this->assertSame(
            [
                'il_md_cpr_selections',
                'il_md_cpr_selections'
            ],
            $db->exposed_tables
        );
        $this->assertSame(
            [[
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
            ]],
            $db->exposed_values
        );
    }

    public function testCreateEmptyTitleException(): void
    {
        $repo = $this->getRepo($this->getDBWrapper());

        $this->expectException(\ilMDCopyrightException::class);
        $repo->createEntry('');
    }

    public function testUpdateWithLinkImage(): void
    {
        $db = $this->getDBWrapper();
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
        $this->assertSame(
            ['il_md_cpr_selections'],
            $db->exposed_tables
        );
        $this->assertSame(
            [[
                'title' => [\ilDBConstants::T_TEXT, 'new title'],
                'description' => [\ilDBConstants::T_TEXT, 'new description'],
                'outdated' => [\ilDBConstants::T_INTEGER, 1],
                'full_name' => [\ilDBConstants::T_TEXT, 'new full name'],
                'link' => [\ilDBConstants::T_TEXT, 'new link'],
                'image_link' => [\ilDBConstants::T_TEXT, 'new image link'],
                'image_file' => [\ilDBConstants::T_TEXT, ''],
                'alt_text' => [\ilDBConstants::T_TEXT, 'new alt text']
            ]],
            $db->exposed_values
        );
        $this->assertSame(
            [['entry_id' => [\ilDBConstants::T_INTEGER, 9]]],
            $db->exposed_wheres
        );
    }

    public function testUpdateWithFileImage(): void
    {
        $db = $this->getDBWrapper();
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
        $this->assertSame(
            ['il_md_cpr_selections'],
            $db->exposed_tables
        );
        $this->assertSame(
            [[
                'title' => [\ilDBConstants::T_TEXT, 'new title'],
                'description' => [\ilDBConstants::T_TEXT, 'new description'],
                'outdated' => [\ilDBConstants::T_INTEGER, 0],
                'full_name' => [\ilDBConstants::T_TEXT, 'new full name'],
                'link' => [\ilDBConstants::T_TEXT, 'new link'],
                'image_link' => [\ilDBConstants::T_TEXT, ''],
                'image_file' => [\ilDBConstants::T_TEXT, 'new image file'],
                'alt_text' => [\ilDBConstants::T_TEXT, 'new alt text']
            ]],
            $db->exposed_values
        );
        $this->assertSame(
            [['entry_id' => [\ilDBConstants::T_INTEGER, 9]]],
            $db->exposed_wheres
        );
    }

    public function testUpdateEmptyTitleException(): void
    {
        $repo = $this->getRepo($this->getDBWrapper());

        $this->expectException(\ilMDCopyrightException::class);
        $repo->updateEntry(21, '');
    }

    public function testReorderEntries(): void
    {
        $db = $this->getDBWrapper(['entry_id' => 5]);
        $repo = $this->getRepo($db);

        $repo->reorderEntries(7, 5, 99, 1);
        $this->assertSame(
            ['SELECT entry_id FROM il_md_cpr_selections WHERE is_default = 1'],
            $db->exposed_queries
        );
        $this->assertSame(
            [
                'il_md_cpr_selections',
                'il_md_cpr_selections',
                'il_md_cpr_selections'
            ],
            $db->exposed_tables,
        );
        $this->assertSame(
            [
                ['position' => [\ilDBConstants::T_INTEGER, 0]],
                ['position' => [\ilDBConstants::T_INTEGER, 1]],
                ['position' => [\ilDBConstants::T_INTEGER, 2]]
            ],
            $db->exposed_values
        );
        $this->assertSame(
            [
                ['entry_id' => [\ilDBConstants::T_INTEGER, 7]],
                ['entry_id' => [\ilDBConstants::T_INTEGER, 99]],
                ['entry_id' => [\ilDBConstants::T_INTEGER, 1]]
            ],
            $db->exposed_wheres
        );
    }
}
