<?php

declare(strict_types=1);

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

require_once("libs/composer/vendor/autoload.php");

use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntry as Entry;
use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntries as Entries;

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Crawler\Exception\CrawlerException;

class ComponentEntriesTest extends TestCase
{
    /**
     * @var mixed
     */
    protected $entries_data;
    protected Entries $entries;
    protected Entry $entry;
    protected array $entry_data;

    protected function setUp(): void
    {
        $this->entries_data = include "tests/UI/Crawler/Fixture/EntriesFixture.php";
        $this->entries = new Entries();
        $this->entry_data = include "tests/UI/Crawler/Fixture/EntryFixture.php";
        $this->entry = new Entry($this->entry_data);
        $this->entries->addEntriesFromArray($this->entries_data);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Entries::class, $this->entries);
    }

    public function testAddEntry(): void
    {
        $entry = new Entry($this->entry_data);
        $entries = new Entries();

        $entries->addEntry($entry);
        $this->assertEquals(1, $entries->count());

        $entry->setId("2");
        $entries->addEntry($entry);
        $this->assertEquals(2, $entries->count());
    }

    public function testAddEntries(): void
    {
        $entry = new Entry($this->entry_data);

        $entries = new Entries();
        $entries->addEntry($entry);

        $entry->setId("2");
        $entries->addEntry($entry);

        $new_entries = new Entries();
        $new_entries->addEntries($this->entries);
        $this->assertEquals(2, $new_entries->count());
    }

    public function testAddFromArray(): void
    {
        $entries_emtpy = new Entries();
        $entries = new Entries();
        $this->assertEquals($entries_emtpy, $entries);
        $entries->addEntriesFromArray([]);
        $this->assertEquals($entries_emtpy, $entries);
        $entries->addEntriesFromArray($this->entries_data);
        $this->assertEquals($entries, $this->entries);
    }

    public function testGetRootEntryId(): void
    {
        $entries = new Entries();
        $this->assertEquals("root", $entries->getRootEntryId());
        $entries->setRootEntryId("root2");
        $this->assertEquals("root2", $entries->getRootEntryId());
        $this->assertEquals("Entry1", $this->entries->getRootEntryId());
    }

    public function testGetRootEntry(): void
    {
        $entries = new Entries();
        try {
            $entries->getRootEntry();
            $this->assertFalse("this should not happen");
        } catch (CrawlerException $e) {
            $this->assertTrue(true);
        }
        $this->assertEquals(new Entry($this->entries_data["Entry1"]), $this->entries->getRootEntry());
    }

    public function testGetEntryById(): void
    {
        $entries = new Entries();
        try {
            $entries->getEntryById("invalid");
            $this->assertFalse("this should not happen");
        } catch (CrawlerException $e) {
            $this->assertTrue(true);
        }
        $this->assertEquals(new Entry($this->entries_data["Entry2"]), $this->entries->getEntryById("Entry2"));
    }

    public function testGetParentsOfEntry(): void
    {
        $this->assertEquals([], $this->entries->getParentsOfEntry("Entry1"));
        $this->assertEquals(["Entry1"], $this->entries->getParentsOfEntry("Entry2"));
    }

    public function testIsParentOfEntry(): void
    {
        $this->assertEquals(false, $this->entries->isParentOfEntry("Entry2", "Entry1"));
        $this->assertEquals(true, $this->entries->isParentOfEntry("Entry1", "Entry2"));
    }

    public function testGetParentsOfEntryTitles(): void
    {
        $this->assertEquals([], $this->entries->getParentsOfEntryTitles("Entry1"));
        $this->assertEquals(['Entry1' => 'Entry1Title'], $this->entries->getParentsOfEntryTitles("Entry2"));
    }

    public function testGetDescendantsOfEntries(): void
    {
        $this->assertEquals(['Entry2'], $this->entries->getDescendantsOfEntry("Entry1"));
        $this->assertEquals([], $this->entries->getDescendantsOfEntry("Entry2"));
    }

    public function testGetDescendantsOfEntryTitles(): void
    {
        $this->assertEquals(['Entry2' => 'Entry2Title'], $this->entries->getDescendantsOfEntryTitles("Entry1"));
        $this->assertEquals([], $this->entries->getDescendantsOfEntryTitles("Entry2"));
    }

    public function testGetChildrenOfEntry(): void
    {
        $this->assertEquals([$this->entries->getEntryById("Entry2")], $this->entries->getChildrenOfEntry("Entry1"));
        $this->assertEquals([], $this->entries->getDescendantsOfEntryTitles("Entry2"));
    }

    public function testJsonSerialize(): void
    {
        $entries = new Entries();

        $this->assertEquals([], $entries->jsonSerialize());
        $this->assertEquals($this->entries_data, $this->entries->jsonSerialize());
    }
}
