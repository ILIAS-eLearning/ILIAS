<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntry as Entry;
use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntries as Entries;

use PHPUnit\Framework\TestCase;

class ComponentEntriesTest extends TestCase
{
    /**
     * @var Entry
     */
    protected $entry;

    /**
     * @var array
     */
    protected $entry_data;

    protected function setUp() : void
    {
        $this->entries_data = include "tests/UI/Crawler/Fixture/EntriesFixture.php";
        $this->entries = new Entries();
        $this->entry_data = include "tests/UI/Crawler/Fixture/EntryFixture.php";
        $this->entry = new Entry($this->entry_data);
        $this->entries->addEntriesFromArray($this->entries_data);
    }


    public function testConstruct()
    {
        $this->assertInstanceOf(Entries::class, $this->entries);
    }

    public function testCreateFromArray()
    {
        $entries = new Entries();
        $this->assertEquals(Entries::createFromArray([]), $entries);
        $this->assertEquals(Entries::createFromArray($this->entries_data), $this->entries);
    }

    public function testAddEntry()
    {
        $entry = new Entry($this->entry_data);
        $entries = new Entries();

        $this->assertEquals(Entries::createFromArray([]), $entries);
        $entries->addEntry($entry);
        $this->assertEquals(Entries::createFromArray([$this->entry_data]), $entries);
    }

    public function testAddEntries()
    {
        $entries = new Entries();

        $this->assertEquals(Entries::createFromArray([]), $entries);
        $entries->addEntries($this->entries);
        $this->assertEquals($this->entries, $entries);
    }

    public function testAddFromArray()
    {
        $entries_emtpy = new Entries();
        $entries = new Entries();
        $this->assertEquals($entries_emtpy, $entries);
        $entries->addEntriesFromArray([]);
        $this->assertEquals($entries_emtpy, $entries);
        $entries->addEntriesFromArray($this->entries_data);
        $this->assertEquals($entries, $this->entries);
    }

    public function testGetRootEntryId()
    {
        $entries = new Entries();
        $this->assertEquals("root", $entries->getRootEntryId());
        $entries->setRootEntryId("root2");
        $this->assertEquals("root2", $entries->getRootEntryId());
        $this->assertEquals("Entry1", $this->entries->getRootEntryId());
    }

    public function testGetRootEntry()
    {
        $entries = new Entries();
        try {
            $entries->getRootEntry();
            $this->assertFalse("this should not happen");
        } catch (\ILIAS\UI\Implementation\Crawler\Exception\CrawlerException $e) {
            $this->assertTrue(true);
        }
        $this->assertEquals(new Entry($this->entries_data["Entry1"]), $this->entries->getRootEntry());
    }

    public function testGetEntryById()
    {
        $entries = new Entries();
        try {
            $entries->getEntryById("invalid");
            $this->assertFalse("this should not happen");
        } catch (\ILIAS\UI\Implementation\Crawler\Exception\CrawlerException $e) {
            $this->assertTrue(true);
        }
        $this->assertEquals(new Entry($this->entries_data["Entry2"]), $this->entries->getEntryById("Entry2"));
    }

    public function testGetParentsOfEntry()
    {
        $this->assertEquals([], $this->entries->getParentsOfEntry("Entry1"));
        $this->assertEquals(["Entry1"], $this->entries->getParentsOfEntry("Entry2"));
    }

    public function testGetParentsOfEntryTitles()
    {
        $this->assertEquals([], $this->entries->getParentsOfEntryTitles("Entry1"));
        $this->assertEquals(['Entry1' => 'Entry1Title'], $this->entries->getParentsOfEntryTitles("Entry2"));
    }

    public function testGetDescendantsOfEntries()
    {
        $this->assertEquals(['Entry2'], $this->entries->getDescendantsOfEntry("Entry1"));
        $this->assertEquals([], $this->entries->getDescendantsOfEntry("Entry2"));
    }

    public function testGetDescendantsOfEntryTitles()
    {
        $this->assertEquals(['Entry2' => 'Entry2Title'], $this->entries->getDescendantsOfEntryTitles("Entry1"));
        $this->assertEquals([], $this->entries->getDescendantsOfEntryTitles("Entry2"));
    }

    public function testJsonSerialize()
    {
        $entries = new Entries();

        $this->assertEquals([], $entries->jsonSerialize());
        $this->assertEquals($this->entries_data, $this->entries->jsonSerialize());
    }
}
