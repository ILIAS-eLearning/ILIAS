<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntry as Entry;
use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntryDescription as Description;
use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntryRules as Rules;

use PHPUnit\Framework\TestCase;

class ComponentEntryTest extends TestCase
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
        $this->entry_data = include "tests/UI/Crawler/Fixture/EntryFixture.php";
        $this->entry = new Entry($this->entry_data);
    }


    public function testConstruct()
    {
        $this->assertInstanceOf(Entry::class, $this->entry);
    }

    public function testGetId()
    {
        $this->assertEquals($this->entry_data["id"], $this->entry->getId());
        $this->entry->setId("newId");
        $this->assertEquals("newId", $this->entry->getId());
    }

    public function testGetTitle()
    {
        $this->assertEquals($this->entry_data["title"], $this->entry->getTitle());
        $this->entry->setTitle("newTitle");
        $this->assertEquals("newTitle", $this->entry->getTitle());
    }

    public function testIsAbstract()
    {
        $this->assertEquals($this->entry_data["abstract"], $this->entry->isAbstract());
        $this->entry->setIsAbstract(false);
        $this->assertEquals(false, $this->entry->isAbstract());
    }

    public function testStatusEntry()
    {
        $this->assertEquals($this->entry_data["status_entry"], $this->entry->getStatusEntry());
        $this->entry->setStatusEntry("someStatus");
        $this->assertEquals("someStatus", $this->entry->getStatusEntry());
    }

    public function testStatusImplementation()
    {
        $this->assertEquals($this->entry_data["status_implementation"], $this->entry->getStatusImplementation());
        $this->entry->setStatusImplementation("someStatus");
        $this->assertEquals("someStatus", $this->entry->getStatusImplementation());
    }

    public function testSetDescription()
    {
        $this->assertEquals(new Description($this->entry_data["description"]), $this->entry->getDescription());
        $this->assertEquals($this->entry_data["description"], $this->entry->getDescriptionAsArray());
        $this->entry->setDescription(new Description([]));
        $this->assertEquals(new Description([]), $this->entry->getDescription());
        $this->assertEquals(array(
            'purpose' => '',
            'composition' => '',
            'effect' => '',
            'rivals' => array()), $this->entry->getDescriptionAsArray());
    }

    public function testSetBackground()
    {
        $this->assertEquals($this->entry_data["background"], $this->entry->getBackground());
        $this->entry->setBackground("someBackground");
        $this->assertEquals("someBackground", $this->entry->getBackground());
    }

    public function testContext()
    {
        $this->assertEquals($this->entry_data["context"], $this->entry->getContext());
        $this->entry->setContext([]);
        $this->assertEquals([], $this->entry->getContext());
    }

    public function testFeatureWikiReferences()
    {
        $this->assertEquals($this->entry_data["feature_wiki_references"], $this->entry->getFeatureWikiReferences());
        $this->entry->setFeatureWikiReferences([]);
        $this->assertEquals([], $this->entry->getFeatureWikiReferences());
    }

    public function testRules()
    {
        $this->assertEquals(new Rules($this->entry_data["rules"]), $this->entry->getRules());
        $this->assertEquals($this->entry_data["rules"], $this->entry->getRulesAsArray());
        $this->entry->setRules(new Rules([]));
        $this->assertEquals(new Rules([]), $this->entry->getRules());
    }

    public function testSelector()
    {
        $this->assertEquals($this->entry_data["selector"], $this->entry->getSelector());
        $this->entry->setSelector("otherSelector");
        $this->assertEquals("otherSelector", $this->entry->getSelector());
    }

    public function testLessVariables()
    {
        $this->assertEquals($this->entry_data["less_variables"], $this->entry->getLessVariables());
        $this->entry->setLessVariables([]);
        $this->assertEquals([], $this->entry->getLessVariables());
    }

    public function testPath()
    {
        $this->assertEquals($this->entry_data["path"], $this->entry->getPath());
        $this->entry->setPath("");
        $this->assertEquals("", $this->entry->getPath());
    }

    public function testParent()
    {
        $this->assertEquals($this->entry_data["parent"], $this->entry->getParent());
        $this->entry->setParent("test");
        $this->assertEquals("test", $this->entry->getParent());
        $this->entry->setParent(false);
        $this->assertEquals(false, $this->entry->getParent());
    }

    public function testChildren()
    {
        $this->assertEquals($this->entry_data["children"], $this->entry->getChildren());
        $this->entry->setChildren([]);
        $this->assertEquals([], $this->entry->getChildren());
        $this->entry->addChildren(
            array(
                0 => 'Child1',
                1 => 'Child2'
            )
        );
        $this->assertEquals(['Child1','Child2'], $this->entry->getChildren());
        $this->entry->addChild('Child3');
        $this->assertEquals(['Child1','Child2','Child3'], $this->entry->getChildren());
    }



    public function testExamplePath()
    {
        $this->assertEquals('src/UI/Factory/Entry1Title', $this->entry->getExamplesPath());
    }

    public function testExamplesNull()
    {
        $this->assertEquals(null, $this->entry->getExamples());
    }

    public function testJsonSerialize()
    {
        $this->assertEquals(include "tests/UI/Crawler/Fixture/EntryFixture.php", $this->entry->jsonSerialize());
    }
}
